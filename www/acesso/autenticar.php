<?php
session_start();

require_once __DIR__ . '/../conexão/conexao.php';
require_once __DIR__ . '/auth_helpers.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    $_SESSION['erro_login'] = 'Acesso inválido.';
    header('Location: login.php');
    exit;
}

$email = trim($_POST['email'] ?? '');
$senha = trim($_POST['senha'] ?? '');

if ($email === '' || $senha === '') {
    $_SESSION['erro_login'] = 'Preencha e-mail e senha.';
    header('Location: login.php');
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $_SESSION['erro_login'] = 'E-mail inválido.';
    header('Location: login.php');
    exit;
}

if (mb_strlen($senha) < 4) {
    $_SESSION['erro_login'] = 'A senha deve ter pelo menos 4 caracteres.';
    header('Location: login.php');
    exit;
}

$stmtAdmin = $pdo->prepare('SELECT id_admin, nome, senha FROM admin WHERE email = :email LIMIT 1');
$stmtAdmin->execute([':email' => $email]);
$admin = $stmtAdmin->fetch();

if ($admin && senhaValidaCompat($senha, $admin['senha'])) {
    if (senhaPrecisaAtualizar($admin['senha'])) {
        atualizarHashSenha($pdo, 'admin', 'id_admin', (int) $admin['id_admin'], $senha);
    }

    session_regenerate_id(true);
    $_SESSION['admin_id'] = (int) $admin['id_admin'];
    $_SESSION['admin_nome'] = $admin['nome'];

    header('Location: ../nutrii/painel.php');
    exit;
}

$stmtCliente = $pdo->prepare(
    'SELECT id, nome, senha, idade, telefone, genero, cep
     FROM clientes
     WHERE email = :email
     LIMIT 1'
);
$stmtCliente->execute([':email' => $email]);
$cliente = $stmtCliente->fetch();

if ($cliente && senhaValidaCompat($senha, $cliente['senha'])) {
    if (senhaPrecisaAtualizar($cliente['senha'])) {
        atualizarHashSenha($pdo, 'clientes', 'id', (int) $cliente['id'], $senha);
    }

    session_regenerate_id(true);
    $_SESSION['cliente_id'] = (int) $cliente['id'];
    $_SESSION['cliente_nome'] = $cliente['nome'];
    $_SESSION['cliente_idade'] = $cliente['idade'];
    $_SESSION['cliente_telefone'] = $cliente['telefone'];
    $_SESSION['cliente_genero'] = $cliente['genero'];
    $_SESSION['cliente_cep'] = $cliente['cep'];

    header('Location: ../pagina/index_logado.php');
    exit;
}

$_SESSION['erro_login'] = 'E-mail ou senha inválidos.';
header('Location: login.php');
exit;
