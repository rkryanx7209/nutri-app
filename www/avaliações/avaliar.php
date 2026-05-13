<?php
session_start();

require_once __DIR__ . '/../conexão/conexao.php';

if (empty($_SESSION['cliente_id'])) {
    header('Location: ../acesso/login.php');
    exit;
}

$clienteId = (int) $_SESSION['cliente_id'];
$feedbackPage = '../pagina/index_logado.php#avalia';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $comentario = trim($_POST['comentario'] ?? '');
    $nota = (int) ($_POST['nota'] ?? 0);

    if ($nome === '' || $comentario === '' || $nota < 1 || $nota > 5) {
        $_SESSION['avaliacao_erro'] = 'Preencha todos os campos corretamente.';
        header('Location: ' . $feedbackPage);
        exit;
    }

    try {
        $stmt = $pdo->prepare(
            'INSERT INTO avaliacoes (cliente_id, nome, comentario, nota)
             VALUES (:cliente_id, :nome, :comentario, :nota)'
        );

        $salvou = $stmt->execute([
            ':cliente_id' => $clienteId,
            ':nome' => $nome,
            ':comentario' => $comentario,
            ':nota' => $nota,
        ]);

        if ($salvou) {
            $_SESSION['avaliacao_sucesso'] = 'Avaliação enviada com sucesso.';
        } else {
            $_SESSION['avaliacao_erro'] = 'Não foi possível enviar sua avaliação.';
        }
    } catch (PDOException $e) {
        $_SESSION['avaliacao_erro'] = 'Erro ao salvar sua avaliação.';
    }
}

header('Location: ' . $feedbackPage);
exit;
