<?php
require_once '../config/config.php';

// Kiểm tra đăng nhập và quyền Admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] != 1) {
    header("Location: dashboard.php");
    exit();
}

$username = $fullname = $email = $role = "";
$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $fullname = trim($_POST['fullname']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $role = $_POST['role'];

    if (empty($username) || empty($fullname) || empty($email) || empty($password)) {
        $error = "Vui lòng nhập đầy đủ thông tin bắt buộc.";
    } else {
        $sql_check = "SELECT id FROM users WHERE username = ? OR email = ?";
        if ($stmt_check = mysqli_prepare($conn, $sql_check)) {
            mysqli_stmt_bind_param($stmt_check, "ss", $username, $email);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_store_result($stmt_check);
            
            if (mysqli_stmt_num_rows($stmt_check) > 0) {
                $error = "Tên đăng nhập hoặc Email đã tồn tại.";
            } else {
                $sql = "INSERT INTO users (username, password, fullname, email, role) VALUES (?, ?, ?, ?, ?)";
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    mysqli_stmt_bind_param($stmt, "ssssi", $username, $hashed_password, $fullname, $email, $role);
                    
                    if (mysqli_stmt_execute($stmt)) {
                        $_SESSION['message'] = "Thêm người dùng mới thành công!";
                        header("Location: dashboard.php");
                        exit();
                    } else {
                        $error = "Có lỗi xảy ra. Vui lòng thử lại.";
                    }
                    mysqli_stmt_close($stmt);
                }
            }
            mysqli_stmt_close($stmt_check);
        }
    }
}
?>

<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <title>Thêm Người Dùng</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="../css/style.css">
</head>
<body class="dashboard-bg">

<div class="container mt-5">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="dashboard-card">
                <h4 class="mb-4 text-center" style="background: var(--success-gradient); -webkit-background-clip: text; -webkit-text-fill-color: transparent;">Thêm Người Dùng Mới</h4>
                <div class="card-body p-0">
                    <?php if(!empty($error)): ?>
                        <div class="alert alert-danger"><?php echo $error; ?></div>
                    <?php endif; ?>
                    
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Tên đăng nhập *</label>
                                <input type="text" name="username" class="form-control" required value="<?php echo htmlspecialchars($username); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Họ và tên *</label>
                                <input type="text" name="fullname" class="form-control" required value="<?php echo htmlspecialchars($fullname); ?>">
                            </div>
                        </div>
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label">Email *</label>
                                <input type="email" name="email" class="form-control" required value="<?php echo htmlspecialchars($email); ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Mật khẩu *</label>
                                <input type="password" name="password" class="form-control" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label">Vai trò</label>
                            <select name="role" class="form-select">
                                <option value="0" <?php echo ($role === '0') ? 'selected' : ''; ?>>Người dùng (User)</option>
                                <option value="1" <?php echo ($role === '1') ? 'selected' : ''; ?>>Quản trị viên (Admin)</option>
                            </select>
                        </div>
                        
                        <div class="d-flex justify-content-between">
                            <a href="dashboard.php" class="btn btn-secondary">Quay lại</a>
                            <button type="submit" class="btn btn-success">Lưu thông tin</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

</body>
</html>
