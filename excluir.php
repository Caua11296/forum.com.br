<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/funcoes.php';

if (!isset($_SESSION['usuario'])) {
    encerrarComErro('Você precisa estar logado.', 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    encerrarComErro('Método não permitido.', 405);
}

if (!csrfValido($_POST['csrf_token'] ?? null)) {
    encerrarComErro('Formulário expirado. Tente novamente.', 403);
}

$id = obterIndice($_POST['id'] ?? null);
$comentarioId = obterIndice($_POST['comentario'] ?? null);

if ($id === null || $comentarioId === null) {
    encerrarComErro('Comentário inválido.');
}

try {
    $topicos = carregarXml(ARQUIVO_TOPICOS, 'topicos');

    if (!isset($topicos->topico[$id])) {
        encerrarComErro('Tópico não encontrado.', 404);
    }

    $topico = $topicos->topico[$id];

    if ((string) $topico->autor !== (string) $_SESSION['usuario']) {
        encerrarComErro('Somente o autor do tópico pode excluir comentários.', 403);
    }

    if (!isset($topico->comentarios) || !isset($topico->comentarios->comentario[$comentarioId])) {
        encerrarComErro('Comentário não encontrado.', 404);
    }

    unset($topico->comentarios->comentario[$comentarioId]);

    salvarXml($topicos, ARQUIVO_TOPICOS);

    header('Location: listar.php');
    exit;
} catch (RuntimeException $excecao) {
    encerrarComErro($excecao->getMessage(), 500);
}