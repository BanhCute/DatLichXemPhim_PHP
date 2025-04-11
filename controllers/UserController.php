<?php
require_once "models/User.php";
require_once "models/Booking.php";

class UserController
{
    private $userModel;
    private $bookingModel;

    public function __construct()
    {
        $this->userModel = new User();
        $this->bookingModel = new Booking();
    }

    public function showLoginForm()
    {
        require_once "views/auth/login.php";
    }

    public function login()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $email = $_POST['email'] ?? '';
            $password = $_POST['password'] ?? '';

            // Debug
            error_log("Login attempt - Email: " . $email);

            $user = $this->userModel->getUserByEmail($email);

            if ($user) {
                error_log("User found - Verifying password");
                if (password_verify($password, $user['password'])) {
                    error_log("Password verified successfully");
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_role'] = $user['role'];
                    $_SESSION['user_name'] = $user['name'];

                    header('Location: ' . BASE_URL . 'movies');
                    exit;
                } else {
                    error_log("Password verification failed");
                }
            } else {
                error_log("User not found with email: " . $email);
            }

            $_SESSION['error'] = 'Email hoặc mật khẩu không đúng';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }
    }

    public function showRegisterForm()
    {
        require_once "views/auth/register.php";
    }

    public function register()
    {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $name = $_POST['name'] ?? '';
            $email = $_POST['email'] ?? '';
            $phone = $_POST['phone'] ?? '';
            $password = $_POST['password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Debug
            error_log("Register attempt - Name: " . $name . ", Email: " . $email);

            // Validate input
            if (empty($name) || empty($email) || empty($password) || empty($phone)) {
                error_log("Registration failed: Empty fields");
                $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin';
                header('Location: ' . BASE_URL . 'register');
                exit;
            }

            if ($password !== $confirmPassword) {
                error_log("Registration failed: Password mismatch");
                $_SESSION['error'] = 'Mật khẩu xác nhận không khớp';
                header('Location: ' . BASE_URL . 'register');
                exit;
            }

            // Check if email exists
            if ($this->userModel->getUserByEmail($email)) {
                error_log("Registration failed: Email exists - " . $email);
                $_SESSION['error'] = 'Email đã tồn tại';
                header('Location: ' . BASE_URL . 'register');
                exit;
            }

            // Create user
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
            error_log("Attempting to create user with hashed password");

            if ($this->userModel->createUser($name, $email, $hashedPassword, $phone)) {
                error_log("User created successfully");
                $_SESSION['success'] = 'Đăng ký thành công. Vui lòng đăng nhập để tiếp tục sử dụng dịch vụ';
                header('Location: ' . BASE_URL . 'login?email=' . urlencode($email));
                exit;
            } else {
                error_log("Failed to create user");
                $_SESSION['error'] = 'Có lỗi xảy ra khi tạo tài khoản';
                header('Location: ' . BASE_URL . 'register');
                exit;
            }
        }
    }

    public function logout()
    {
        session_destroy();
        header('Location: ' . BASE_URL . 'login');
        exit;
    }

    public function myBookings()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $bookings = $this->bookingModel->getUserBookings($_SESSION['user_id']);
        require 'views/booking/my-bookings.php';
    }

    public function showProfile()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $user = $this->userModel->getUserById($_SESSION['user_id']);
        if (!$user) {
            $_SESSION['error'] = 'Không tìm thấy thông tin người dùng';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        require 'views/user/profile.php';
    }

    public function showChangePassword()
    {
        if (!isset($_SESSION['user_id'])) {
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        $user = $this->userModel->getUserById($_SESSION['user_id']);
        if (!$user) {
            $_SESSION['error'] = 'Không tìm thấy thông tin người dùng';
            header('Location: ' . BASE_URL . 'login');
            exit;
        }

        require 'views/user/change-password.php';
    }

    public function updateProfile()
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                header('Location: ' . BASE_URL . 'login');
                exit;
            }

            $name = $_POST['name'] ?? '';
            $phone = $_POST['phone'] ?? '';

            if (empty($name)) {
                $_SESSION['error'] = 'Vui lòng nhập họ tên';
                header('Location: ' . BASE_URL . 'profile');
                exit;
            }

            // Debug
            error_log("Updating profile for user ID: " . $_SESSION['user_id']);
            error_log("New name: " . $name . ", New phone: " . $phone);

            if ($this->userModel->updateUser($_SESSION['user_id'], $name, $phone)) {
                $_SESSION['success'] = 'Cập nhật thông tin thành công';
                $_SESSION['user_name'] = $name; // Cập nhật tên trong session
            } else {
                $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật thông tin';
            }

            header('Location: ' . BASE_URL . 'profile');
            exit;
        } catch (Exception $e) {
            error_log("Error in updateProfile: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi cập nhật thông tin';
            header('Location: ' . BASE_URL . 'profile');
            exit;
        }
    }

    public function updatePassword()
    {
        try {
            if (!isset($_SESSION['user_id'])) {
                header('Location: ' . BASE_URL . 'login');
                exit;
            }

            $currentPassword = $_POST['current_password'] ?? '';
            $newPassword = $_POST['new_password'] ?? '';
            $confirmPassword = $_POST['confirm_password'] ?? '';

            // Validate input
            if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
                $_SESSION['error'] = 'Vui lòng điền đầy đủ thông tin';
                header('Location: ' . BASE_URL . 'profile/change-password');
                exit;
            }

            if ($newPassword !== $confirmPassword) {
                $_SESSION['error'] = 'Mật khẩu mới không khớp';
                header('Location: ' . BASE_URL . 'profile/change-password');
                exit;
            }

            // Debug
            error_log("Attempting to change password for user ID: " . $_SESSION['user_id']);

            if ($this->userModel->changePassword($_SESSION['user_id'], $currentPassword, $newPassword)) {
                $_SESSION['success'] = 'Đổi mật khẩu thành công';
            } else {
                $_SESSION['error'] = 'Mật khẩu hiện tại không đúng';
            }

            header('Location: ' . BASE_URL . 'profile/change-password');
            exit;
        } catch (Exception $e) {
            error_log("Error in updatePassword: " . $e->getMessage());
            $_SESSION['error'] = 'Có lỗi xảy ra khi đổi mật khẩu';
            header('Location: ' . BASE_URL . 'profile/change-password');
            exit;
        }
    }
}
