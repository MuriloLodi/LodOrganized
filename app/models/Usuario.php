<?php

class Usuario
{
    public static function findById(PDO $pdo, int $id)
    {
        $stmt = $pdo->prepare("SELECT id, nome, email, senha, avatar FROM usuarios WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function emailExistsExcept(PDO $pdo, string $email, int $id)
    {
        $stmt = $pdo->prepare("SELECT id FROM usuarios WHERE email = ? AND id <> ? LIMIT 1");
        $stmt->execute([$email, $id]);
        return (bool) $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function updateProfile(PDO $pdo, int $id, string $nome, string $email)
    {
        $stmt = $pdo->prepare("UPDATE usuarios SET nome = ?, email = ? WHERE id = ?");
        return $stmt->execute([$nome, $email, $id]);
    }

    public static function updatePassword(PDO $pdo, int $id, string $hash)
    {
        $stmt = $pdo->prepare("UPDATE usuarios SET senha = ? WHERE id = ?");
        return $stmt->execute([$hash, $id]);
    }

    public static function updateAvatar(PDO $pdo, int $id, ?string $avatar)
    {
        $stmt = $pdo->prepare("UPDATE usuarios SET avatar = ? WHERE id = ?");
        return $stmt->execute([$avatar, $id]);
    }
}
