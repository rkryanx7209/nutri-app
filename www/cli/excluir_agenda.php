<?php
require_once __DIR__ . '/../conexão/conexao.php';
session_start();

if (empty($_SESSION['cliente_id'])) {
    header('Location: ../acesso/login.php');
    exit;
}

$id = (int) ($_GET['id'] ?? 0);
$idCliente = (int) $_SESSION['cliente_id'];

$stmt = $pdo->prepare(
    "UPDATE agenda
     SET status = 'cancelado'
     WHERE id_agenda = :id
       AND fk_id_cliente = :cliente
       AND status = 'pendente'"
);
$stmt->execute([
    ':id' => $id,
    ':cliente' => $idCliente,
]);

header('Location: minha_agenda.php');
exit;
