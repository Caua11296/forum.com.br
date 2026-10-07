<?php
// Alteração: inicializa a sessão e carrega as rotinas comuns.
session_start();
require_once __DIR__ . '/funcoes.php';

$email = '';
$erro = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Alteração: valida a entrada e trata arquivo XML vazio ou inválido.
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $senha = (string) ($_POST['senha'] ?? '');
    try {
        $usuarios = carregarXml(ARQUIVO_USUARIOS, 'usuarios');
        foreach ($usuarios->usuario as $usuario) {
            $hash = (string) $usuario->senha;
            // Alteração: aceita registros antigos em MD5 e os atualiza após o login.
            $senhaCorreta = password_verify($senha, $hash)
                || (preg_match('/^[a-f0-9]{32}$/i', $hash) === 1 && hash_equals(strtolower($hash), md5($senha)));
            if (strcasecmp((string) $usuario->email, $email) === 0 && $senhaCorreta) {
                session_regenerate_id(true);
                $_SESSION['usuario'] = (string) $usuario->email;
                if (password_needs_rehash($hash, PASSWORD_DEFAULT)) {
                    $usuario->senha = password_hash($senha, PASSWORD_DEFAULT);
                    salvarXml($usuarios, ARQUIVO_USUARIOS);
                }
                // Alteração: redireciona após o POST para evitar reenvio do formulário.
                header('Location: listar.php');
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
    <title>Login</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f2f2;
        }

        .caixa {
            width: 400px;
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

        input {
            width: 100%;
            padding: 8px;
            box-sizing: border-box;
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
        <h1>Login</h1>
        <?php if ($erro !== ''): ?><p class="erro"><?= escapar($erro) ?></p><?php endif; ?>
        <form method="post">
            <label for="email">E-mail</label>
            <input id="email" type="email" name="email" value="<?= escapar($email) ?>" required>

            <label for="senha">Senha</label>
            <input id="senha" type="password" name="senha" required>

            <button type="submit">Entrar</button>
        </form>
        <p><a href="cadastro.php">Criar cadastro</a></p>
        <p><a href="listar.php">Ver tópicos</a></p>
    </main>
</body>
</html>