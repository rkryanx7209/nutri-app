<?php
session_start();

require_once __DIR__ . '/../conexão/conexao.php';
require_once __DIR__ . '/auth_helpers.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $senha = trim($_POST['senha'] ?? '');
    $idade = trim($_POST['idade'] ?? '');
    $telefone = preg_replace('/\D+/', '', $_POST['telefone'] ?? '');
    $genero = trim($_POST['genero'] ?? '');
    $cep = preg_replace('/\D+/', '', $_POST['cep'] ?? '');

    if ($nome === '' || $email === '' || $senha === '') {
        $_SESSION['erro'] = 'Nome, e-mail e senha são obrigatórios.';
        header('Location: cadrastocliente.php');
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['erro'] = 'Digite um e-mail válido.';
        header('Location: cadrastocliente.php');
        exit;
    }

    if (mb_strlen($senha) < 4) {
        $_SESSION['erro'] = 'A senha deve ter pelo menos 4 caracteres.';
        header('Location: cadrastocliente.php');
        exit;
    }

    if ($idade !== '' && (!ctype_digit($idade) || (int) $idade < 0 || (int) $idade > 120)) {
        $_SESSION['erro'] = 'Informe uma idade válida.';
        header('Location: cadrastocliente.php');
        exit;
    }

    if ($telefone !== '' && (strlen($telefone) < 10 || strlen($telefone) > 11)) {
        $_SESSION['erro'] = 'Informe um telefone válido com DDD.';
        header('Location: cadrastocliente.php');
        exit;
    }

    if ($cep !== '' && strlen($cep) !== 8) {
        $_SESSION['erro'] = 'Informe um CEP válido com 8 dígitos.';
        header('Location: cadrastocliente.php');
        exit;
    }

    $check = $pdo->prepare('SELECT id FROM clientes WHERE email = :email LIMIT 1');
    $check->execute([':email' => $email]);

    if ($check->fetch()) {
        $_SESSION['erro'] = 'Este e-mail já está cadastrado.';
        header('Location: cadrastocliente.php');
        exit;
    }

    $stmt = $pdo->prepare(
        'INSERT INTO clientes (nome, email, senha, idade, telefone, genero, cep)
         VALUES (:nome, :email, :senha, :idade, :telefone, :genero, :cep)'
    );

    try {
        $stmt->execute([
            ':nome' => $nome,
            ':email' => $email,
            ':senha' => gerarHashSenha($senha),
            ':idade' => $idade !== '' ? (int) $idade : null,
            ':telefone' => $telefone !== '' ? $telefone : null,
            ':genero' => $genero !== '' ? $genero : null,
            ':cep' => $cep !== '' ? $cep : null,
        ]);

        $_SESSION['sucesso'] = 'Cadastro realizado com sucesso. Faça login.';
        header('Location: login.php');
        exit;
    } catch (PDOException $e) {
        $_SESSION['erro'] = 'Não foi possível finalizar o cadastro agora.';
        header('Location: cadrastocliente.php');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Cadastro do Cliente</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="cadrasto.css">
    <link rel="icon" href="../fotos/images-removebg-preview copy.png">
</head>
<body>

<div class="login-box">
    <h2>Cadastro</h2>

    <form method="POST">
        <input type="text" name="nome" placeholder="Nome completo" required>
        <input type="email" name="email" placeholder="E-mail" required>

        <div class="input-row">
            <input type="number" name="idade" placeholder="Idade" min="0" max="120">
            <select name="genero" class="select-pill">
                <option value="">Gênero</option>
                <option value="Masculino">Masculino</option>
                <option value="Feminino">Feminino</option>
                <option value="Outro">Outro</option>
            </select>
        </div>

        <input type="text" name="telefone" placeholder="Telefone / WhatsApp" maxlength="11">
        <input type="text" name="cep" placeholder="CEP" maxlength="8">
        <input type="password" name="senha" placeholder="Crie uma senha" required minlength="4">

        <?php if (!empty($_SESSION['erro'])): ?>
            <div class="erro-login"><?= htmlspecialchars($_SESSION['erro']); ?></div>
        <?php unset($_SESSION['erro']); endif; ?>

        <div class="botoes-login">
            <button type="submit" class="btn-pill verde-musgo">
                <i class="fas fa-check"></i> Finalizar cadastro
            </button>
            <a href="login.php" class="btn-pill outline-verde">
                <i class="fas fa-sign-in-alt"></i> Já tenho conta
            </a>
        </div>
    </form>
</div>

</body>
</html>
