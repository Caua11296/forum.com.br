
<?php
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuarios = simplexml_load_file('usuarios.xml');
    $novo = $usuarios->addChild('usuario');
    $novo->addChild('nome', $_POST['nome']);
    $novo->addChild('email', $_POST['email']);
    $novo->addChild('senha', $_POST['senha']);
    $usuarios->asXML('usuarios.xml');
    echo "Usuario cadastrado com sucesso! <a href='login.php'>Fazer login</a>";
} else {
?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Document</title>
    </head>
    <body>
        <form method="post">
        Nome: <input type="text" name="nome" required><br>
        Celular: <input type="text" name="celular" required><br>
        Email: <input type="email" name="email" required><br>
        Senha: <input type="password" name="senha" required><br>
        <button type="submit">Cadastrar</button>
    </form>
    </body>
    </html>
<?php
}
?>