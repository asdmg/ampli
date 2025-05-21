<?php
ob_start();
include('header.php');

if (isset($_SESSION['admin_id'])) {
    header('Location: index.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = isset($_POST['email']) ? trim($_POST['email']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if ($email !== '' && $password !== '') {
        $stmt = $conn->prepare("SELECT admin_id, admin_email, admin_password FROM admins WHERE admin_email = ?");
        $stmt->bind_param('s', $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $admin = $result->fetch_assoc();
            if (md5($password) === $admin['admin_password']) {
                $_SESSION['admin_id'] = $admin['admin_id'];
                $_SESSION['admin_email'] = $admin['admin_email'];
                header('Location: index.php');
                exit;
            } else {
                $error = "Usuário ou senha incorretos.";
            }
        } else {
            $error = "Usuário ou senha incorretos.";
        }
    } else {
        $error = "Preencha os campos de email e senha.";
    }
}
?>

    <div class="container d-flex justify-content-center align-items-center" style="height: calc(100vh - 56px);">
        <div class="w-100" style="max-width: 400px;">
            <h2 class="text-center mb-4">Login</h2>
            <?php if($error): ?>
                <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            <form method="POST" action="login.php">
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" name="email" id="email" class="form-control" required autofocus>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Senha</label>
                    <input type="password" name="password" id="password" class="form-control" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>
        </div>
    </div>

    </body>
</html>
