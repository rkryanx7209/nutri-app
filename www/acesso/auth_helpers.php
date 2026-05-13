<?php

function senhaEstaHash(string $valor): bool
{
    $info = password_get_info($valor);

    return !empty($info['algo']);
}

function gerarHashSenha(string $senha): string
{
    return password_hash($senha, PASSWORD_DEFAULT);
}

function senhaValidaCompat(string $senhaInformada, string $senhaSalva): bool
{
    if ($senhaSalva === '') {
        return false;
    }

    if (senhaEstaHash($senhaSalva)) {
        return password_verify($senhaInformada, $senhaSalva);
    }

    return hash_equals($senhaSalva, $senhaInformada);
}

function senhaPrecisaAtualizar(string $senhaSalva): bool
{
    if (!senhaEstaHash($senhaSalva)) {
        return true;
    }

    return password_needs_rehash($senhaSalva, PASSWORD_DEFAULT);
}

function atualizarHashSenha(PDO $pdo, string $tabela, string $colunaId, int $id, string $novaSenha): void
{
    $tabelasPermitidas = ['admin', 'clientes'];
    $colunasPermitidas = ['id_admin', 'id'];

    if (!in_array($tabela, $tabelasPermitidas, true) || !in_array($colunaId, $colunasPermitidas, true)) {
        throw new InvalidArgumentException('Tabela ou coluna inválida para atualização de senha.');
    }

    $sql = sprintf('UPDATE %s SET senha = :senha WHERE %s = :id', $tabela, $colunaId);
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':senha' => gerarHashSenha($novaSenha),
        ':id' => $id,
    ]);
}
