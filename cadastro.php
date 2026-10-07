<?php
// Alteração: usa as funções seguras de leitura, gravação e escape.
require_once __DIR__ . '/funcoes.php';

// Alteração: mantém os dados preenchidos e apresenta erros de validação.
$nome = '';
$celular = '';
$email = '';
$erro = '';
$cadastrado = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Alteração: normaliza e valida todos os campos antes de gravá-los.
    $nome = trim((string) ($_POST['nome'] ?? ''));
    $celular = trim((string) ($_POST['celular'] ?? ''));
    $email = strtolower(trim((string) ($_POST['email'] ?? '')));
    $senha = (string) ($_POST['senha'] ?? '');

    if ($nome === '' || $celular === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $erro = 'Preencha nome, celular e um e-mail válido.';
    } elseif (strlen($senha) < 6) {
        $erro = 'A senha deve possuir pelo menos 6 caracteres.';
    } else {
        try {
            $usuarios = carregarXml(ARQUIVO_USUARIOS, 'usuarios');
            // Alteração: impede o cadastro duplicado do mesmo e-mail.
            foreach ($usuarios->usuario as $usuario) {
                if (strcasecmp((string) $usuario->email, $email) === 0) {
                    $erro = 'Este e-mail já está cadastrado.';
                    break;
                }
            }
            if ($erro === '') {
                $novo = $usuarios->addChild('usuario');
                adicionarTextoXml($novo, 'nome', $nome);
                adicionarTextoXml($novo, 'celular', $celular);
                adicionarTextoXml($novo, 'email', $email);
                // Alteração: substitui MD5 pelo algoritmo seguro de hash de senha do PHP.
                adicionarTextoXml($novo, 'senha', password_hash($senha, PASSWORD_DEFAULT));
                salvarXml($usuarios, ARQUIVO_USUARIOS);
                $cadastrado = true;
            }
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
    <title>Cadastro</title>
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
        <?php if ($cadastrado): ?>
            <h1>Cadastro realizado!</h1>
            <p>Usuário cadastrado com sucesso.</p>
            <p><a href="login.php">Fazer login</a></p>
        <?php else: ?>
            <h1>Cadastro</h1>
            <?php if ($erro !== ''): ?><p class="erro"><?= escapar($erro) ?></p><?php endif; ?>
            <form method="post">
                <label for="nome">Nome</label>
                <input id="nome" type="text" name="nome" value="<?= escapar($nome) ?>" required>

                <label for="celular">Celular</label>
                <input id="celular" type="text" name="celular" value="<?= escapar($celular) ?>" required>

                <label for="email">E-mail</label>
                <input id="email" type="email" name="email" value="<?= escapar($email) ?>" required>

                <label for="senha">Senha</label>
                <input id="senha" type="password" name="senha" minlength="6" required>

                <button type="submit">Cadastrar</button>
            </form>
            <p><a href="login.php">Já tenho cadastro</a></p>
        <?php endif; ?>
    </main>
</body>
</html>