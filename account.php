<?php
include('layouts/header.php');
include('server/connection.php');

session_start();

if (!isset($_SESSION['logged_in'])) {
    header('Location: login.php');
    exit();
}
if (isset($_GET['logout']) && $_GET['logout'] == 1) {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }

    $_SESSION = [];
    session_destroy();

    header('Location: login.php');
    exit();
}


// Lógica de Alteração de Senha
if (isset($_POST['update_password'])) {
    $current_password = md5($_POST['current_password']);
    $new_password = md5($_POST['new_password']);
    $confirm_password = md5($_POST['confirm_password']);

    if ($new_password !== $confirm_password) {
        $error = "As novas senhas não coincidem.";
    } else {
        $user_id = $_SESSION['user_id'];
        $query = "SELECT * FROM users WHERE user_id = $user_id AND user_password = '$current_password'";
        $result = $conn->query($query);

        if ($result->num_rows === 1) {
            $query = "UPDATE users SET user_password = '$new_password' WHERE user_id = $user_id";
            if ($conn->query($query)) {
                $success = "Senha alterada com sucesso.";
            } else {
                $error = "Erro ao alterar a senha.";
            }
        } else {
            $error = "A senha atual está incorreta.";
        }
    }
}

// Busca os pedidos do usuário
$user_id = $_SESSION['user_id'];
$query = "SELECT * FROM orders WHERE user_id = $user_id";
$orders = $conn->query($query);
?>
<section id="account">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Minha Conta</h2><a href="account.php?logout=1" class="btn btn-danger">Logout</a>

        </div>
        <?php if (isset($error))
            echo "<p class='text-danger'>$error</p>"; ?>
        <?php if (isset($success))
            echo "<p class='text-success'>$success</p>"; ?>

        <div class="row g-3 d-flex align-items-stretch">
            <div class="col-md-6">
                <div class="bg-light p-3 rounded border  d-flex flex-column">
                    <h4>Dados Pessoais</h4>
                    <p><strong>Nome:</strong> <?= $_SESSION['user_name']; ?></p>
                    <p><strong>Email:</strong> <?= $_SESSION['user_email']; ?></p>
                </div>
            </div>

            <div class="col-md-6">
                <div class="bg-light p-3 rounded border ">
                    <h4>Alterar Senha</h4>
                    <form method="POST" action="account.php">
                        <div class="form-group">
                            <label>Senha Atual</label>
                            <input type="password" name="current_password" class="form-control" required>
                        </div>
                        <div class="form-group mt-3">
                            <label>Nova Senha</label>
                            <input type="password" name="new_password" class="form-control" required>
                        </div>
                        <div class="form-group mt-3">
                            <label>Confirme a Nova Senha</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                        <button type="submit" name="update_password" class="btn btn-primary mt-3">Alterar Senha</button>
                    </form>
                </div>
            </div>
        </div>

        <h4 class="mt-5">Meus Pedidos</h4>
        <table class="table table-striped">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Status</th>
                    <th>Data</th>
                    <th>Total</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($order = $orders->num_rows <= 0): ?>
                    <tr>
                        <td colspan="5" class="text-center">Nenhum pedido encontrado.</td>
                    </tr>

                <?php endif; ?>
                <?php while ($order = $orders->fetch_assoc()): ?>
                    <tr>
                        <td><?= $order['order_id']; ?></td>
                        <td><?= $order['order_status']; ?></td>
                        <td><?= $order['order_date']; ?></td>
                        <td>R$ <?= $order['order_cost']; ?></td>
                        <td><a href="order_details.php?order_id=<?= $order['order_id']; ?>"
                                class="btn btn-info btn-sm">Detalhes</a></td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</section>
<?php include('layouts/footer.php'); ?>