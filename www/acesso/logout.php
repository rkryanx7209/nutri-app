<?php
session_start();

$_SESSION = [];
session_destroy();

$destino = $_GET['destino'] ?? 'login';
$url = $destino === 'site' ? '../index.html' : 'login.php';

header('Location: ' . $url);
exit;
