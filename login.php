<?php
include('layouts/header.php');
include('server/connection.php');

session_start();

// Verifica se o usuário já está logado
if (isset($_SESSION['logged_in'])) {
    header('Location: account.php');
    exit();
}

// Lógica de Login
if (isset($_POST['login_btn'])) {
    $email = $_POST['email'];
    $password = md5($_POST['password']);

    $query = "SELECT * FROM users WHERE user_email = '$email' AND user_password = '$password'";
    $result = $conn->query($query);

    if ($result->num_rows == 1) {
        $user = $result->fetch_assoc();
        $_SESSION['logged_in'] = true;
        $_SESSION['user_id'] = $user['user_id'];
        $_SESSION['user_name'] = $user['user_name'];
        $_SESSION['user_email'] = $user['user_email'];
        header('Location: account.php');
        exit();
    } else {
        $error = "Email ou senha inválidos.";
    }
}
?>
<section id="login">
    <div class="container mt-5">
        <div class="col-md-6 offset-md-3 bg-light p-5 rounded">
            <h2>Login</h2>
            <?php if (isset($error))
                echo "<p class='text-danger'>$error</p>"; ?>
            <form method="POST" action="login.php">
                <div class="form-group">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group mt-3">
                    <label>Senha</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button type="submit" name="login_btn" class="btn btn-primary mt-3">Entrar</button>
                <p class="mt-3">Não possui conta? <a href="register.php">Cadastre-se aqui</a></p>
            </form>
        </div>
    </div>
</section>

<?php include('layouts/footer.php'); ?>