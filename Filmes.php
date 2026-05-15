<?php

$arquivo = 'filmes.json';

// Carrega filmes do JSON (ou inicia lista padrão)
function carregarFilmes(string $arquivo): array {
    if (!file_exists($arquivo)) {
        $iniciais = [
            ['id' => 1, 'titulo' => 'O Poderoso Chefão',   'ano' => 1972, 'genero' => 'Drama'],
            ['id' => 2, 'titulo' => 'Pulp Fiction',         'ano' => 1994, 'genero' => 'Crime'],
            ['id' => 3, 'titulo' => 'O Senhor dos Anéis',   'ano' => 2001, 'genero' => 'Fantasia'],
        ];
        file_put_contents($arquivo, json_encode($iniciais, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
        return $iniciais;
    }
    return json_decode(file_get_contents($arquivo), true) ?? [];
}

function salvarFilmes(array $filmes, string $arquivo): void {
    file_put_contents($arquivo, json_encode(array_values($filmes), JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
}

function proximoId(array $filmes): int {
    return $filmes ? max(array_column($filmes, 'id')) + 1 : 1;
}

// ---- Processamento das ações --------------------------------
$mensagem = '';
$edicao   = null;
$filmes   = carregarFilmes($arquivo);
$acao     = $_POST['acaikijo'] ?? $_GET['acao'] ?? '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    if ($acao === 'adicionar') {
        $titulo = trim($_POST['titulo'] ?? '');
        $ano    = (int)($_POST['ano']    ?? 0);
        $genero = trim($_POST['genero']  ?? '');
        if ($titulo && $ano && $genero) {
            $filmes[] = ['id' => proximoId($filmes), 'titulo' => $titulo, 'ano' => $ano, 'genero' => $genero];
            salvarFilmes($filmes, $arquivo);
            $mensagem = "Filme \"$titulo\" adicionado com sucesso!";
        } else {
            $mensagem = "Preencha todos os campos.";
        }
    }

    if ($acao === 'salvar_edicao') {
        $id     = (int)$_POST['id'];
        $titulo = trim($_POST['titulo'] ?? '');
        $ano    = (int)($_POST['ano']   ?? 0);
        $genero = trim($_POST['genero'] ?? '');
        foreach ($filmes as &$f) {
            if ($f['id'] === $id) {
                $f['titulo'] = $titulo;
                $f['ano']    = $ano;
                $f['genero'] = $genero;
                break;
            }
        }
        unset($f);
        salvarFilmes($filmes, $arquivo);
        $mensagem = "Filme atualizado com sucesso!";
    }
}

if ($acao === 'remover' && isset($_GET['id'])) {
    $id     = (int)$_GET['id'];
    $filmes = array_filter($filmes, fn($f) => $f['id'] !== $id);
    salvarFilmes($filmes, $arquivo);
    header('Location: filmes.php?msg=removido');
    exit;
}

if ($acao === 'editar' && isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    foreach ($filmes as $f) {
        if ($f['id'] === $id) { $edicao = $f; break; }
    }
}

if (isset($_GET['msg']) && $_GET['msg'] === 'removido') {
    $mensagem = "Filme removido com sucesso!";
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Cadastro de Filmes</title>
<style>
  @import url('https://fonts.googleapis.com/css2?family=Bebas+Neue&family=Inter:wght@400;500;600&display=swap');

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

  :root {
    --bg:       #0d0d0f;
    --surface:  #16161a;
    --border:   #2a2a32;
    --accent:   #e8b84b;
    --accent2:  #c0392b;
    --text:     #e8e8f0;
    --muted:    #6b6b80;
    --radius:   8px;
  }

  body {
    background: var(--bg);
    color: var(--text);
    font-family: 'Inter', sans-serif;
    min-height: 100vh;
    padding: 2rem 1rem;
  }

  header {
    text-align: center;
    margin-bottom: 2.5rem;
  }
  header h1 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: clamp(2.8rem, 6vw, 4.5rem);
    letter-spacing: 4px;
    color: var(--accent);
    line-height: 1;
  }
  header p { color: var(--muted); margin-top: .4rem; font-size: .9rem; }

  .container { max-width: 860px; margin: 0 auto; }

  /* Mensagem de feedback */
  .msg {
    background: #1e2a1a;
    border-left: 4px solid var(--accent);
    padding: .75rem 1.2rem;
    border-radius: var(--radius);
    margin-bottom: 1.5rem;
    font-size: .9rem;
  }

  /* Card / formulário */
  .card {
    background: var(--surface);
    border: 1px solid var(--border);
    border-radius: var(--radius);
    padding: 1.5rem;
    margin-bottom: 2rem;
  }
  .card h2 {
    font-family: 'Bebas Neue', sans-serif;
    font-size: 1.4rem;
    letter-spacing: 2px;
    color: var(--accent);
    margin-bottom: 1rem;
  }

  .form-grid {
    display: grid;
    grid-template-columns: 2fr 1fr 1fr;
    gap: .8rem;
  }
  @media(max-width: 600px) { .form-grid { grid-template-columns: 1fr; } }

  label { display: block; font-size: .78rem; color: var(--muted); margin-bottom: .3rem; font-weight: 500; text-transform: uppercase; letter-spacing: .5px; }

  input, select {
    width: 100%;
    background: var(--bg);
    border: 1px solid var(--border);
    color: var(--text);
    padding: .6rem .9rem;
    border-radius: var(--radius);
    font-size: .95rem;
    transition: border-color .2s;
  }
  input:focus, select:focus { outline: none; border-color: var(--accent); }

  .form-footer { margin-top: 1rem; display: flex; gap: .8rem; }

  button, .btn {
    display: inline-flex;
    align-items: center;
    gap: .4rem;
    padding: .6rem 1.4rem;
    border-radius: var(--radius);
    font-size: .88rem;
    font-weight: 600;
    cursor: pointer;
    border: none;
    text-decoration: none;
    transition: opacity .15s, transform .1s;
  }
  button:hover, .btn:hover { opacity: .85; transform: translateY(-1px); }

  .btn-primary { background: var(--accent);  color: #111; }
  .btn-cancel  { background: var(--border);  color: var(--text); }
  .btn-edit    { background: #1e3a5f; color: #7eb8f7; font-size: .8rem; padding: .35rem .85rem; }
  .btn-delete  { background: #3a1a1a; color: #f07070; font-size: .8rem; padding: .35rem .85rem; }

  /* Tabela */
  .table-wrap { overflow-x: auto; }
  table { width: 100%; border-collapse: collapse; }
  thead tr { border-bottom: 2px solid var(--accent); }
  th {
    font-family: 'Bebas Neue', sans-serif;
    letter-spacing: 1.5px;
    font-size: 1rem;
    color: var(--accent);
    padding: .6rem .8rem;
    text-align: left;
  }
  td { padding: .7rem .8rem; border-bottom: 1px solid var(--border); font-size: .9rem; vertical-align: middle; }
  tr:last-child td { border-bottom: none; }
  tr:hover td { background: rgba(255,255,255,.02); }

  .badge {
    display: inline-block;
    padding: .2rem .6rem;
    border-radius: 20px;
    font-size: .75rem;
    font-weight: 600;
    background: #1e2a1a;
    color: var(--accent);
    border: 1px solid rgba(232,184,75,.25);
  }

  .actions { display: flex; gap: .5rem; }
  .empty { text-align: center; color: var(--muted); padding: 2rem; font-size: .9rem; }

  .count { color: var(--muted); font-size: .85rem; margin-bottom: .8rem; }
</style>
</head>
<body>
<div class="container">

  <header>
    <h1> Cadastro de Filmes</h1>
    <p>Gerencie sua coleção — adicione, edite e remova filmes.</p>
  </header>

  <?php if ($mensagem): ?>
    <div class="msg"><?= htmlspecialchars($mensagem) ?></div>
  <?php endif; ?>

  <!-- ======= FORMULÁRIO (adicionar / editar) ======= -->
  <div class="card">
    <h2><?= $edicao ? ' Editar Filme' : ' Novo Filme' ?></h2>
    <form method="POST" action="filmes.php">
      <input type="hidden" name="acao" value="<?= $edicao ? 'salvar_edicao' : 'adicionar' ?>">
      <?php if ($edicao): ?>
        <input type="hidden" name="id" value="<?= $edicao['id'] ?>">
      <?php endif; ?>

      <div class="form-grid">
        <div>
          <label for="titulo">Título</label>
          <input type="text" id="titulo" name="titulo" placeholder="Ex: Interstellar"
                 value="<?= htmlspecialchars($edicao['titulo'] ?? '') ?>" required>
        </div>
        <div>
          <label for="ano">Ano</label>
          <input type="number" id="ano" name="ano" placeholder="Ex: 2014" min="1888" max="2099"
                 value="<?= htmlspecialchars($edicao['ano'] ?? '') ?>" required>
        </div>
        <div>
          <label for="genero">Gênero</label>
          <select id="genero" name="genero" required>
            <option value="">Selecione...</option>
            <?php
            $generos = ['Ação','Animação','Aventura','Comédia','Crime','Drama',
                        'Fantasia','Ficção Científica','Romance','Suspense','Terror'];
            $sel = $edicao['genero'] ?? '';
            foreach ($generos as $g) {
                $s = ($g === $sel) ? 'selected' : '';
                echo "<option value=\"$g\" $s>$g</option>";
            }
            ?>
          </select>
        </div>
      </div>

      <div class="form-footer">
        <button type="submit" class="btn btn-primary">
          <?= $edicao ? ' Salvar alterações' : ' Adicionar filme' ?>
        </button>
        <?php if ($edicao): ?>
          <a href="filmes.php" class="btn btn-cancel">✖ Cancelar</a>
        <?php endif; ?>
      </div>
    </form>
  </div>

  <!-- ======= LISTAGEM ======= -->
  <div class="card">
    <h2>Filmes Cadastrados</h2>
    <p class="count"><?= count($filmes) ?> filme(s) na coleção</p>

    <?php if (empty($filmes)): ?>
      <p class="empty">Nenhum filme cadastrado ainda.</p>
    <?php else: ?>
      <div class="table-wrap">
        <table>
          <thead>
            <tr>
              <th>#</th>
              <th>Título</th>
              <th>Ano</th>
              <th>Gênero</th>
              <th>Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($filmes as $f): ?>
            <tr>
              <td style="color:var(--muted)"><?= $f['id'] ?></td>
              <td><strong><?= htmlspecialchars($f['titulo']) ?></strong></td>
              <td><?= $f['ano'] ?></td>
              <td><span class="badge"><?= htmlspecialchars($f['genero']) ?></span></td>
              <td>
                <div class="actions">
                  <a href="filmes.php?acao=editar&id=<?= $f['id'] ?>" class="btn btn-edit">✏️ Editar</a>
                  <a href="filmes.php?acao=remover&id=<?= $f['id'] ?>"
                     class="btn btn-delete"
                     onclick="return confirm('Remover \"<?= htmlspecialchars($f['titulo']) ?>\"?')">
                     Remover
                  </a>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>

</div>
</body>
</html>
