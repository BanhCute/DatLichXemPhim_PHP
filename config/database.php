<?php
class Database
{
    private $host = "localhost";
    private $db_name = "movie_booking";
    private $username = "root";
    private $password = "";
    public $conn;

    public function getConnection()
    {
        $this->conn = null;

        try {
            $this->conn = new PDO(
                "mysql:host=" . $this->host . ";port=3306;dbname=" . $this->db_name . ";charset=utf8mb4",
                $this->username,
                $this->password,
                array(PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION)
            );

            error_log("Database connection successful");

            return $this->conn;
        } catch (PDOException $e) {
            error_log("Database Connection Error: " . $e->getMessage());
            throw new Exception("Không thể kết nối đến cơ sở dữ liệu: " . $e->getMessage());
        }
    }
}
