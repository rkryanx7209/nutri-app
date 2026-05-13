<?php
session_start();

require_once __DIR__ . '/../conexão/conexao.php';
require_once __DIR__ . '/auth_helpers.php';

if (isset($_GET['limpar'])) {
    unset($_SESSION['email_validado']);
    header('Location: recuperar.php');
    exit;
}

$acao = $_POST['acao'] ?? '';

if ($acao === 'verificar') {
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['erro_recuperar'] = 'Digite um e-mail válido.';
        header('Location: recuperar.php');
        exit;
    }

    $checkAdmin = $pdo->prepare('SELECT id_admin FROM admin WHERE email = :email LIMIT 1');
    $checkAdmin->execute([':email' => $email]);

    $checkCliente = $pdo->prepare('SELECT id FROM clientes WHERE email = :email LIMIT 1');
    $checkCliente->execute([':email' => $email]);

    if ($checkAdmin->fetch() || $checkCliente->fetch()) {
        $_SESSION['email_validado'] = $email;
    } else {
        $_SESSION['erro_recuperar'] = 'E-mail não encontrado.';
    }

    header('Location: recuperar.php');
    exit;
}

if ($acao === 'mudar_senha') {
    $email = $_SESSION['email_validado'] ?? '';
    $nova = trim($_POST['nova_senha'] ?? '');
    $conf = trim($_POST['confirmar_senha'] ?? '');

    if ($email === '') {
        $_SESSION['erro_recuperar'] = 'Recomece a recuperação de senha.';
        header('Location: recuperar.php');
        exit;
    }

    if (mb_strlen($nova) < 4) {
        $_SESSION['erro_recuperar'] = 'A nova senha deve ter pelo menos 4 caracteres.';
        header('Location: recuperar.php');
        exit;
    }

    if ($nova !== $conf) {
        $_SESSION['erro_recuperar'] = 'As senhas não coincidem.';
        header('Location: recuperar.php');
        exit;
    }

    $senhaHash = gerarHashSenha($nova);

    $sql1 = $pdo->prepare('UPDATE admin SET senha = :senha WHERE email = :email');
    $sql1->execute([':senha' => $senhaHash, ':email' => $email]);

    $sql2 = $pdo->prepare('UPDATE clientes SET senha = :senha WHERE email = :email');
    $sql2->execute([':senha' => $senhaHash, ':email' => $email]);

    unset($_SESSION['email_validado']);
    $_SESSION['sucesso'] = 'Senha alterada com sucesso.';
    header('Location: login.php');
    exit;
}

header('Location: recuperar.php');
exit;
