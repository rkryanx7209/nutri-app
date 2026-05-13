<?php
require_once __DIR__ . '/../conexão/conexao.php';
session_start();

if (empty($_SESSION['cliente_id'])) {
    header('Location: ../acesso/login.php');
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
$clienteId = (int) $_SESSION['cliente_id'];

$stmt = $pdo->prepare('DELETE FROM avaliacoes WHERE id_avaliacao = :id AND cliente_id = :cliente_id');
$stmt->execute([
    ':id' => $id,
    ':cliente_id' => $clienteId,
]);

header('Location: minha_avalia.php');
exit;
