<?php
// Nova página: prepara a sessão, as funções auxiliares e os dados da listagem.
session_start();
require_once __DIR__ . '/funcoes.php';

$erro = '';
$mensagemSucesso = (string) ($_SESSION['mensagem_sucesso'] ?? '');
unset($_SESSION['mensagem_sucesso']);

try {
    $topicos = carregarXml(ARQUIVO_TOPICOS, 'topicos');
} catch (RuntimeException $excecao) {
    $erro = $excecao->getMessage();
    $topicos = new SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><topicos/>');
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Fórum</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
        }

        .pagina {
            max-width: 700px;
            margin: 20px auto;
        }

        header, article {
            background: white;
            padding: 15px;
            margin-bottom: 15px;
        }

        header {
            text-align: center;
        }

        .comentario {
            background: #f2f2f2;
            padding: 8px;
            margin: 8px 0;
        }

        label {
            display: block;
        }

        input[type="text"], textarea {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }

        textarea {
            height: 80px;
        }

        button {
            padding: 8px 12px;
            margin-top: 10px;
        }

        .botao-excluir {
            color: white;
            background: #c62828;
            border: 1px solid #a61f1f;
            border-radius: 4px;
            cursor: pointer;
        }

        .botao-excluir:hover {
            background: #a61f1f;
        }
    </style>
</head>
<body>
    <main class="pagina">
        <header>
            <h1>Tópicos do fórum</h1>
            <nav>
                <?php if (isset($_SESSION['usuario'])): ?>
                    <span>Conectado como <?= escapar($_SESSION['usuario']) ?></span>
                    | <a href="criar_topico.php">Criar tópico</a>
                <?php else: ?>
                    <a href="login.php">Entrar</a> | <a href="cadastro.php">Cadastrar</a>
                <?php endif; ?>
            </nav>
        </header>

        <?php if ($mensagemSucesso !== ''): ?>
            <p><?= escapar($mensagemSucesso) ?></p>
        <?php endif; ?>
        <?php if ($erro !== ''): ?>
            <p><?= escapar($erro) ?></p>
        <?php elseif (count($topicos->topico) === 0): ?>
            <p>Nenhum tópico foi criado ainda.</p>
        <?php endif; ?>

        <?php $id = 0; ?>
        <?php foreach ($topicos->topico as $topico): ?>
            <article>
                <h2><?= escapar($topico->titulo) ?></h2>
                <p><?= nl2br(escapar($topico->mensagem)) ?></p>
                <p><small>Autor: <?= escapar($topico->autor) ?></small></p>

                <h3>Comentários</h3>
                <?php if (count($topico->comentarios->comentario) === 0): ?>
                    <p>Este tópico ainda não possui comentários.</p>
                <?php endif; ?>

                <?php $comentarioId = 0; ?>
                <?php foreach ($topico->comentarios->comentario as $comentario): ?>
                    <section class="comentario">
                        <p><strong><?= escapar($comentario->nome) ?>:</strong> <?= nl2br(escapar($comentario->mensagem)) ?></p>
                        <?php if (isset($_SESSION['usuario']) && (string) $_SESSION['usuario'] === (string) $topico->autor): ?>
                            <form method="post" action="excluir.php">
                                <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
                                <input type="hidden" name="id" value="<?= escapar($id) ?>">
                                <input type="hidden" name="comentario" value="<?= escapar($comentarioId) ?>">
                                <button class="botao-excluir" type="submit">Excluir comentário</button>
                            </form>
                        <?php endif; ?>
                    </section>
                    <?php $comentarioId++; ?>
                <?php endforeach; ?>

                <form method="post" action="comentar.php">
                    <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">
                    <input type="hidden" name="id" value="<?= escapar($id) ?>">

                    <label for="nome-<?= escapar($id) ?>">Nome</label>
                    <input id="nome-<?= escapar($id) ?>" type="text" name="nome" required>

                    <label for="comentario-<?= escapar($id) ?>">Comentário</label>
                    <textarea id="comentario-<?= escapar($id) ?>" name="mensagem" required></textarea>

                    <button type="submit">Comentar</button>
                </form>
            </article>
            <?php $id++; ?>
        <?php endforeach; ?>
    </main>
</body>
</html>