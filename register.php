<?php
include('layouts/header.php');
include('server/connection.php');

session_start();

// Verifica se o usuário já está logado
if (isset($_SESSION['logged_in'])) {
    header('Location: account.php');
    exit();
}

// Lógica de Cadastro
if (isset($_POST['register_btn'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $password = md5($_POST['password']);
    $confirm_password = md5($_POST['confirm_password']);

    if ($password != $confirm_password) {
        $error = "As senhas não coincidem.";
    } else {
        $query = "SELECT * FROM users WHERE user_email = '$email'";
        $result = $conn->query($query);

        if ($result->num_rows > 0) {
            $error = "Email já cadastrado.";
        } else {
            // Insere o novo usuário
            $query = "INSERT INTO users (user_name, user_email, user_password) VALUES ('$name', '$email', '$password')";
            if ($conn->query($query)) {
                $_SESSION['logged_in'] = true;
                $_SESSION['user_id'] = $conn->insert_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                header('Location: account.php');
                exit();
            } else {
                $error = "Erro ao registrar usuário.";
            }
        }
    }
}
?>
<section id="register">
    <div class="container mt-5">
        <div class="col-md-6 offset-md-3 bg-light p-5 rounded">
            <h2>Cadastro</h2>
            <?php if (isset($error))
                echo "<p class='text-danger'>$error</p>"; ?>
            <form method="POST" action="register.php">
                <div class="form-group">
                    <label>Nome</label>
                    <input type="text" name="name" class="form-control" required>
                </div>
                <div class="form-group mt-3">
                    <label>Email</label>
                    <input type="email" name="email" class="form-control" required>
                </div>
                <div class="form-group mt-3">
                    <label>Senha</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <div class="form-group mt-3">
                    <label>Confirme a Senha</label>
                    <input type="password" name="confirm_password" class="form-control" required>
                </div>
                <button type="submit" name="register_btn" class="btn btn-primary mt-3">Cadastrar</button>
                <p class="mt-3">Já possui conta? <a href="login.php">Faça login</a></p>
            </form>
        </div>
    </div>
</section>

<?php include('layouts/footer.php'); ?>