<?php

declare(strict_types=1);

namespace ZauberCMS\Auth;

use PDO;

final class Auth
{
    public function __construct(private readonly PDO $database)
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    public function attempt(string $email, string $password): bool
    {
        $statement = $this->database->prepare(
            'SELECT id, name, email, password, role FROM users WHERE email = :email AND is_active = 1 LIMIT 1'
        );
        $statement->execute(['email' => mb_strtolower(trim($email))]);
        $user = $statement->fetch();

        if (!is_array($user) || !password_verify($password, (string) $user['password'])) {
            return false;
        }

        session_regenerate_id(true);
        $_SESSION['auth_user'] = [
            'id' => (int) $user['id'],
            'name' => (string) $user['name'],
            'email' => (string) $user['email'],
            'role' => (string) $user['role'],
        ];

        return true;
    }

    public function check(): bool
    {
        return isset($_SESSION['auth_user']['id']);
    }

    public function user(): ?array
    {
        return $this->check() ? $_SESSION['auth_user'] : null;
    }

    public function logout(): void
    {
        unset($_SESSION['auth_user']);
        session_regenerate_id(true);
    }
}
