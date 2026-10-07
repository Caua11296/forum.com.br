<?php
session_start();
require_once __DIR__ . '/funcoes.php';

$email = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !isset($_SESSION['usuario'])) {
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $senha = (string) ($_POST['senha'] ?? '');

    try {
        $usuarios = carregarXml(ARQUIVO_USUARIOS, 'usuarios');
        foreach ($usuarios->usuario as $usuario) {
            $hash = (string) $usuario->senha;
            $senhaCorreta = password_verify($senha, $hash)
                || (preg_match('/^[a-f0-9]{32}$/i', $hash) === 1 && hash_equals(strtolower($hash), md5($senha)));

            if (strcasecmp((string) $usuario->email, $email) === 0 && $senhaCorreta) {
                session_regenerate_id(true);
                $_SESSION['usuario'] = (string) $usuario->email;

                if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                    $usuario->senha = password_hash($senha, PASSWORD_DEFAULT);
                    salvarXml($usuarios, ARQUIVO_USUARIOS);
                }

                header('Location: index.php');
                exit;
            }
        }
        $erro = 'Login inválido.';
    } catch (RuntimeException $excecao) {
        $erro = $excecao->getMessage();
    }
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
            margin: 0;
            padding: 20px;
            font-family: Arial, sans-serif;
            color: #222;
            background: #f2f2f2;
        }

        .caixa {
            width: 500px;
            max-width: 100%;
            margin: 40px auto;
            padding: 24px;
            box-sizing: border-box;
            background: white;
            text-align: center;
        }

        h1 {
            margin-top: 0;
        }

        p {
            line-height: 1.5;
        }

        label {
            display: block;
            margin-top: 10px;
            text-align: left;
        }

        input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
        }

        button {
            width: 100%;
            padding: 11px 16px;
            border: 1px solid #1769aa;
            background: #1976d2;
            color: white;
            cursor: pointer;
        }

        button:hover {
            background: #155fa0;
        }

        .erro {
            color: #b00020;
        }

        .acoes {
            display: grid;
            gap: 10px;
            margin-top: 24px;
        }

        .botao {
            display: block;
            padding: 11px 16px;
            border: 1px solid #1769aa;
            background: #1976d2;
            color: white;
            text-decoration: none;
        }

        .botao:hover {
            background: #155fa0;
        }

        .botao-secundario {
            border-color: #aaa;
            background: white;
            color: #222;
        }

        .botao-secundario:hover {
            background: #f2f2f2;
        }
    </style>
</head>
<body>
    <main class="caixa">
        <h1>Bem-vindo ao Fórum</h1>
        <p>Um espaço para compartilhar ideias, conversar e participar das discussões.</p>

        <?php if (isset($_SESSION['usuario'])): ?>
            <p>Conectado como <strong><?= escapar($_SESSION['usuario']) ?></strong>.</p>
            <nav class="acoes" aria-label="Navegação principal">
                <a class="botao" href="listar.php">Ver tópicos</a>
                <a class="botao botao-secundario" href="criar_topico.php">Criar tópico</a>
            </nav>
        <?php else: ?>
            <?php if ($erro !== ''): ?>
                <p class="erro"><?= escapar($erro) ?></p>
            <?php endif; ?>
            <form method="post">
                <label for="email">E-mail</label>
                <input id="email" type="email" name="email" value="<?= escapar($email) ?>" required>

                <label for="senha">Senha</label>
                <input id="senha" type="password" name="senha" required>

                <button type="submit">Entrar</button>
            </form>
            <nav class="acoes" aria-label="Navegação principal">
                <a class="botao" href="listar.php">Explorar tópicos</a>
                <a class="botao botao-secundario" href="cadastro.php">Criar cadastro</a>
            </nav>
        <?php endif; ?>
    </main>
</body>
</html>