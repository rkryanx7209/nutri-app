<?php
session_start();

require_once __DIR__ . '/../conexão/conexao.php';
require_once __DIR__ . '/../acesso/auth_helpers.php';

if (!isset($_SESSION['cliente_id'])) {
    header('Location: ../acesso/login.php');
    exit;
}

$id = (int) $_SESSION['cliente_id'];
$msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = trim($_POST['nome'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $cep = preg_replace('/\D+/', '', $_POST['cep'] ?? '');
    $telefone = preg_replace('/\D+/', '', $_POST['telefone'] ?? '');
    $idade = (int) ($_POST['idade'] ?? 0);
    $genero = trim($_POST['genero'] ?? '');
    $senhaNova = trim($_POST['senha'] ?? '');

    if ($nome === '' || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = 'Preencha nome e e-mail corretamente.';
    } elseif ($cep !== '' && strlen($cep) !== 8) {
        $msg = 'Informe um CEP válido.';
    } elseif ($telefone !== '' && (strlen($telefone) < 10 || strlen($telefone) > 11)) {
        $msg = 'Informe um telefone válido com DDD.';
    } elseif ($idade < 0 || $idade > 120) {
        $msg = 'Informe uma idade válida.';
    } elseif ($senhaNova !== '' && mb_strlen($senhaNova) < 4) {
        $msg = 'A nova senha deve ter pelo menos 4 caracteres.';
    } else {
        try {
            $stmtEmail = $pdo->prepare('SELECT id FROM clientes WHERE email = :email AND id != :id LIMIT 1');
            $stmtEmail->execute([
                ':email' => $email,
                ':id' => $id,
            ]);

            if ($stmtEmail->fetch()) {
                $msg = 'Este e-mail já está sendo usado por outra conta.';
            } else {
                $sql = 'UPDATE clientes SET nome = :nome, email = :email, cep = :cep, telefone = :telefone, idade = :idade, genero = :genero WHERE id = :id';
                $params = [
                    ':nome' => $nome,
                    ':email' => $email,
                    ':cep' => $cep !== '' ? $cep : null,
                    ':telefone' => $telefone !== '' ? $telefone : null,
                    ':idade' => $idade > 0 ? $idade : null,
                    ':genero' => $genero !== '' ? $genero : null,
                    ':id' => $id,
                ];

                if ($senhaNova !== '') {
                    $sql = 'UPDATE clientes SET nome = :nome, email = :email, cep = :cep, telefone = :telefone, idade = :idade, genero = :genero, senha = :senha WHERE id = :id';
                    $params[':senha'] = gerarHashSenha($senhaNova);
                }

                $stmt = $pdo->prepare($sql);
                $stmt->execute($params);

                $_SESSION['cliente_nome'] = $nome;
                $_SESSION['cliente_telefone'] = $telefone;
                $_SESSION['cliente_idade'] = $idade > 0 ? $idade : null;
                $_SESSION['cliente_genero'] = $genero !== '' ? $genero : null;
                $_SESSION['cliente_cep'] = $cep !== '' ? $cep : null;

                $msg = 'Cadastro atualizado com sucesso.';
            }
        } catch (PDOException $e) {
            $msg = 'Não foi possível atualizar os dados agora.';
        }
    }
}

$stmt = $pdo->prepare('SELECT * FROM clientes WHERE id = :id LIMIT 1');
$stmt->execute([':id' => $id]);
$user = $stmt->fetch();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Cadastro | Daniele França</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="editar_cadrasto.css">
    <link rel="icon" href="../fotos/images-removebg-preview copy.png">
</head>
<body>

<section class="secao">
    <div class="sobre-card">
        <h2 class="titulo-dieta"><i class="fas fa-user-cog"></i> Editar cadastro</h2>

        <?php if ($msg !== ''): ?>
            <p class="msg-sucesso"><?= htmlspecialchars($msg) ?></p>
        <?php endif; ?>

        <form class="form-estilizado" method="POST">
            <div class="campo-grupo">
                <label>Nome completo:</label>
                <input type="text" name="nome" value="<?= htmlspecialchars($user['nome']) ?>" required>
            </div>

            <div class="campo-grupo">
                <label>E-mail:</label>
                <input type="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
            </div>

            <div class="form-row">
                <div class="campo-grupo">
                    <label>CEP:</label>
                    <input type="text" name="cep" value="<?= htmlspecialchars($user['cep'] ?? '') ?>" placeholder="00000000" maxlength="8">
                </div>
                <div class="campo-grupo">
                    <label>Telefone:</label>
                    <input type="text" name="telefone" value="<?= htmlspecialchars($user['telefone'] ?? '') ?>" placeholder="11999999999" maxlength="11">
                </div>
            </div>

            <div class="form-row">
                <div class="campo-grupo">
                    <label>Idade:</label>
                    <input type="number" name="idade" value="<?= htmlspecialchars($user['idade'] ?? '') ?>" min="0" max="120">
                </div>
                <div class="campo-grupo">
                    <label>Gênero:</label>
                    <select name="genero">
                        <option value="">Selecione</option>
                        <option value="Masculino" <?= ($user['genero'] ?? '') === 'Masculino' ? 'selected' : '' ?>>Masculino</option>
                        <option value="Feminino" <?= ($user['genero'] ?? '') === 'Feminino' ? 'selected' : '' ?>>Feminino</option>
                        <option value="Outro" <?= ($user['genero'] ?? '') === 'Outro' ? 'selected' : '' ?>>Outro</option>
                    </select>
                </div>
            </div>

            <div class="campo-grupo">
                <label>Nova senha (deixe em branco para não mudar):</label>
                <input type="password" name="senha" placeholder="Digite apenas se quiser mudar" minlength="4">
            </div>

            <button type="submit" class="btn-padrao">
                <i class="fas fa-check"></i> Salvar alterações
            </button>

            <a href="../pagina/index.php" class="btn-padrao btn-voltar">
                <i class="fas fa-arrow-left"></i> Voltar ao perfil
            </a>
        </form>
    </div>
</section>

</body>
</html>
