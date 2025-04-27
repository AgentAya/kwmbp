<?php
namespace App\Models;

class User {
    public $id;
    public $name;
    public $email;
    public $password;
    public $role;
    public $created_at;

    // You can create additional methods like find(), create(), etc.
    public static function findByEmail($pdo, $email) {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
        $stmt->execute([$email]);
        return $stmt->fetch(\PDO::FETCH_ASSOC);
    }

    public static function create($pdo, $data) {
        $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        if($stmt->execute([$data['name'], $data['email'], $data['password'], $data['role']])) {
            return $pdo->lastInsertId();
        }
        return false;
    }
}
?>
