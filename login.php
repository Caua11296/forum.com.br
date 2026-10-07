<?php
session_start();
    if($_SERVER["REQUEST_METHOD"] == "POST") {
        $usuarios = simplexml_load_file("usuarios.xml");
        foreach($usuarios->usuario as $usuario) {
            if($usuario->email == $_POST['email'] && $usuario->senha == md5($_POST['senha'])) {
             $_SESSION['usuario'] = (string)$usuario->email;
            echo "Login realizado com sucesso! <a href='criar_topico.php'>Criar Tópico</a>";
            exit;
        }
        }echo"login invalido!";
    }else{
        ?>
        <form method="post">
            Email:<input type="email" name="email" required><br>
            Senha:<input type="password" name="senha" required><br>
            <input type="submit">Entrar</Button>
        </form>
        <?php } ?>