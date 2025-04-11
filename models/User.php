<?php
require_once "config/database.php";

class User
{
    private $conn;
    private $table_name = "user"; // Changed to lowercase to match database

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

    public function createUser($name, $email, $hashedPassword, $phone)
    {
        try {
            $sql = "INSERT INTO user (name, email, password, phone) VALUES (?, ?, ?, ?)";
            $stmt = $this->conn->prepare($sql);
            return $stmt->execute([$name, $email, $hashedPassword, $phone]);
        } catch (PDOException $e) {
            error_log("Error creating user: " . $e->getMessage());
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

    public function getUserById($userId)
    {
        try {
            $sql = "SELECT id, name, email, phone, role, createdAt 
                    FROM " . $this->table_name . " 
                    WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$userId]);

            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user) {
                error_log("User found: " . json_encode($user));
                return $user;
            } else {
                error_log("No user found with ID: " . $userId);
                return null;
            }
        } catch (PDOException $e) {
            error_log("Error in getUserById: " . $e->getMessage());
            return null;
        }
    }

    public function updateUser($userId, $name, $phone)
    {
        try {
            // Check if user exists
            $user = $this->getUserById($userId);
            if (!$user) {
                error_log("User not found for update: " . $userId);
                return false;
            }

            $sql = "UPDATE " . $this->table_name . " 
                    SET name = ?, phone = ? 
                    WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([$name, $phone, $userId]);

            if ($result) {
                error_log("User updated successfully: " . $userId);
                return true;
            } else {
                error_log("Failed to update user: " . $userId);
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error in updateUser: " . $e->getMessage());
            return false;
        }
    }

    public function changePassword($userId, $currentPassword, $newPassword)
    {
        try {
            // Check if user exists and verify current password
            $sql = "SELECT password FROM " . $this->table_name . " WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $stmt->execute([$userId]);
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$user) {
                error_log("User not found for password change: " . $userId);
                return false;
            }

            // Verify current password
            if (!password_verify($currentPassword, $user['password'])) {
                error_log("Current password verification failed for user: " . $userId);
                return false;
            }

            // Update with new password
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            $sql = "UPDATE " . $this->table_name . " SET password = ? WHERE id = ?";
            $stmt = $this->conn->prepare($sql);
            $result = $stmt->execute([$hashedPassword, $userId]);

            if ($result) {
                error_log("Password changed successfully for user: " . $userId);
                return true;
            } else {
                error_log("Failed to change password for user: " . $userId);
                return false;
            }
        } catch (PDOException $e) {
            error_log("Error in changePassword: " . $e->getMessage());
            return false;
        }
    }

    public function getTotalUsers()
    {
        try {
            $sql = "SELECT COUNT(*) FROM " . $this->table_name;
            $stmt = $this->conn->prepare($sql);
            $stmt->execute();

            $result = $stmt->fetchColumn();
            error_log("Total users count: " . $result);

            return (int)$result;
        } catch (PDOException $e) {
            error_log("Error in getTotalUsers: " . $e->getMessage());
            return 0;
        }
    }
}
