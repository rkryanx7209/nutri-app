<?php
session_start();

if (!isset($_SESSION['admin_id'])) {
    header('Location: ../acesso/login.php');
    exit;
}

$clienteId = (int) ($_GET['cliente_id'] ?? $_POST['cliente_id'] ?? 0);

if ($clienteId <= 0) {
    header('Location: admin_clientes.php');
    exit;
}

header('Location: admin_dieta.php?cliente_id=' . $clienteId);
exit;
