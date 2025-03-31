<?php
require_once "config/database.php";

class User
{
    private $conn;
    private $table_name = "User";

    public function __construct()
    {
        $database = new Database();
        $this->conn = $database->getConnection();
    }

    public function getUserByEmail($email)
    {
        $query = "SELECT * FROM " . $this->table_name . " WHERE email = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bindParam(1, $email);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function createUser($name, $email, $password)
    {
        try {
            $query = "INSERT INTO " . $this->table_name . " (name, email, password) VALUES (?, ?, ?)";
            $stmt = $this->conn->prepare($query);
            $result = $stmt->execute([$name, $email, $password]);

            if (!$result) {
                error_log("Failed to create user. Error info: " . print_r($stmt->errorInfo(), true));
            }

            return $result;
        } catch (PDOException $e) {
            error_log("Database error when creating user: " . $e->getMessage());
            return false;
        }
    }

    public function getAllUsers()
    {
        $query = "SELECT id, name, email, role FROM " . $this->table_name;
        $stmt = $this->conn->prepare($query);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function updateUserRole($id, $role)
    {
        $query = "UPDATE " . $this->table_name . " SET role = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$role, $id]);
    }

    public function deleteUser($id)
    {
        // Kiểm tra xem người dùng có đặt vé nào không
        $query = "SELECT COUNT(*) FROM Booking WHERE userId = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) {
            return false;
        }

        $query = "DELETE FROM " . $this->table_name . " WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$id]);
    }

    public function updateProfile($id, $name, $email)
    {
        $query = "UPDATE " . $this->table_name . " SET name = ?, email = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$name, $email, $id]);
    }

    public function updatePassword($id, $password)
    {
        $query = "UPDATE " . $this->table_name . " SET password = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        return $stmt->execute([$password, $id]);
    }
}
