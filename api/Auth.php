<?php
class Auth
{
    public static function requireLogin(): void
    {
        if (!isset($_SESSION['user'])) {
            http_response_code(401);
            echo json_encode(['error' => 'Unauthorized. Please login to access the LIS API.']);
            exit;
        }
    }

    public static function currentUser(): array
    {
        return $_SESSION['user'] ?? [];
    }
}
