<?php session_start(); ?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <title>Login</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="login.css">
    <link rel="icon" href="../fotos/images-removebg-preview copy.png">
</head>
<body>

<div class="login-box">
    <h2>Login</h2>

    <form action="autenticar.php" method="POST">
        <input type="email" name="email" placeholder="E-mail" required>
        <input type="password" name="senha" placeholder="Senha" required minlength="4">
        <div style="text-align: right; margin-bottom: 15px;">
            <a href="recuperar.php" style="color: #fff; font-size: 0.85rem; text-decoration: none; opacity: 0.7;">Esqueceu a senha?</a>
        </div>

        <?php if (!empty($_SESSION['erro_login'])): ?>
            <div class="erro-login">
                <?= htmlspecialchars($_SESSION['erro_login']); ?>
            </div>
        <?php unset($_SESSION['erro_login']); endif; ?>

        <?php if (!empty($_SESSION['sucesso'])): ?>
            <div class="sucesso-login">
                <?= htmlspecialchars($_SESSION['sucesso']); ?>
            </div>
        <?php unset($_SESSION['sucesso']); endif; ?>

        <div class="botoes-login">
            <a href="cadrastocliente.php" class="btn-pill verde-musgo">
                <i class="fas fa-user-plus"></i> Cadastrar
            </a>

            <button type="submit" class="btn-pill verde-musgo">
                <i class="fas fa-sign-in-alt"></i> Entrar
            </button>

            <a href="../index.html" class="btn-pill outline-verde">
                <i class="fas fa-arrow-left"></i> Voltar ao site
            </a>
        </div>
    </form>
</div>

</body>
</html>
