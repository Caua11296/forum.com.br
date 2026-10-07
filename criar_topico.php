<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/funcoes.php';

if (!isset($_SESSION['usuario'])) {
    encerrarComErro('Você precisa estar logado para criar um tópico.', 403);
}

$titulo = '';
$mensagem = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!csrfValido($_POST['csrf_token'] ?? null)) {
        encerrarComErro('Formulário expirado. Tente novamente.', 403);
    }

    $titulo = trim((string) ($_POST['titulo'] ?? ''));
    $mensagem = trim((string) ($_POST['mensagem'] ?? ''));

    if ($titulo === '' || $mensagem === '') {
        $erro = 'Informe o título e a mensagem.';
    } else {
        try {
            $topicos = carregarXml(ARQUIVO_TOPICOS, 'topicos');
            $novo = $topicos->addChild('topico');
            adicionarTextoXml($novo, 'autor', (string) $_SESSION['usuario']);
            adicionarTextoXml($novo, 'titulo', $titulo);
            adicionarTextoXml($novo, 'mensagem', $mensagem);
            $novo->addChild('comentarios');

            salvarXml($topicos, ARQUIVO_TOPICOS);

            $_SESSION['mensagem_sucesso'] = 'Tópico criado com sucesso!';
            header('Location: listar.php');
            exit;
        } catch (RuntimeException $excecao) {
            $erro = $excecao->getMessage();
        }
    }
}
?>
<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Criar tópico</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
        }

        .caixa {
            width: 500px;
            max-width: 100%;
            margin: 30px auto;
            padding: 15px;
            background: white;
        }

        h1 {
            text-align: center;
        }

        label {
            display: block;
            margin-top: 10px;
        }

        input,
        textarea {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }

        textarea {
            height: 100px;
        }

        button {
            width: 100%;
            padding: 10px;
            margin-top: 10px;
        }

        .erro {
            color: #b00020;
        }
    </style>
</head>
<body>
    <main class="caixa">
        <h1>Criar tópico</h1>
        <?php if ($erro !== ''): ?>
            <p class="erro"><?= escapar($erro) ?></p>
        <?php endif; ?>

        <form method="post">
            <input type="hidden" name="csrf_token" value="<?= escapar(tokenCsrf()) ?>">

            <label for="titulo">Título</label>
            <input id="titulo" type="text" name="titulo" value="<?= escapar($titulo) ?>" required>

            <label for="mensagem">Mensagem</label>
            <textarea id="mensagem" name="mensagem" required><?= escapar($mensagem) ?></textarea>

            <button type="submit">Criar tópico</button>
        </form>

        <p><a href="listar.php">Voltar aos tópicos</a></p>
    </main>
</body>
</html>