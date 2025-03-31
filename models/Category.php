<?php
class Category
{
    private $db;

    public function __construct()
    {
        // Khởi tạo kết nối database đúng cách
        require_once 'config/database.php';
        $database = new Database();
        $this->db = $database->getConnection();

        // Kiểm tra kết nối
        if (!$this->db) {
            die("Không thể kết nối đến cơ sở dữ liệu trong Category model");
        }
    }

    public function getAllCategories()
    {
        try {
            // Debug truy vấn
            $sql = "SELECT * FROM categories ORDER BY name ASC";
            error_log("Executing query: " . $sql);

            $stmt = $this->db->prepare($sql);
            $stmt->execute();
            $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

            // Debug kết quả
            error_log("Query result count: " . count($result));

            return $result;
        } catch (PDOException $e) {
            error_log("Lỗi truy vấn getAllCategories: " . $e->getMessage());
            return [];
        }
    }

    public function getCategoryById($id)
    {
        $sql = "SELECT * FROM categories WHERE id = ?";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$id]);
            return $stmt->fetch(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Lỗi truy vấn getCategoryById: " . $e->getMessage());
            return false;
        }
    }

    public function addCategory($name, $slug)
    {
        $sql = "INSERT INTO categories (name, slug) VALUES (?, ?)";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$name, $slug]);
        } catch (PDOException $e) {
            error_log("Lỗi truy vấn addCategory: " . $e->getMessage());
            return false;
        }
    }

    public function updateCategory($id, $name, $slug)
    {
        $sql = "UPDATE categories SET name = ?, slug = ? WHERE id = ?";

        try {
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$name, $slug, $id]);
        } catch (PDOException $e) {
            error_log("Lỗi truy vấn updateCategory: " . $e->getMessage());
            return false;
        }
    }

    public function deleteCategory($id)
    {
        // Kiểm tra xem thể loại có đang được sử dụng không
        $checkSql = "SELECT COUNT(*) FROM movie_categories WHERE category_id = ?";

        try {
            $stmt = $this->db->prepare($checkSql);
            $stmt->execute([$id]);
            $count = $stmt->fetchColumn();

            if ($count > 0) {
                // Nếu thể loại đang được sử dụng, xóa các liên kết trước
                $deleteLinksSql = "DELETE FROM movie_categories WHERE category_id = ?";
                $stmtLinks = $this->db->prepare($deleteLinksSql);
                $stmtLinks->execute([$id]);
            }

            // Sau đó xóa thể loại
            $sql = "DELETE FROM categories WHERE id = ?";
            $stmt = $this->db->prepare($sql);
            return $stmt->execute([$id]);
        } catch (PDOException $e) {
            error_log("Lỗi truy vấn deleteCategory: " . $e->getMessage());
            return false;
        }
    }

    public function getCategoriesByMovieId($movieId)
    {
        $sql = "SELECT c.* FROM categories c 
                JOIN movie_categories mc ON c.id = mc.category_id 
                WHERE mc.movie_id = ?";

        try {
            $stmt = $this->db->prepare($sql);
            $stmt->execute([$movieId]);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            error_log("Lỗi truy vấn getCategoriesByMovieId: " . $e->getMessage());
            return [];
        }
    }
}
