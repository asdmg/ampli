<?php
session_start();
include('layouts/header.php');
include('server/connection.php');

// Verifica se existe carrinho, senão redireciona para página inicial
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: index.php');
    exit();
}

// Calcula total
$total_price = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_price += $item['product_price'] * $item['quantity'];
}

// Verifica se existe mensagem de erro (ex: usuário não logado)
$error = '';
if (isset($_SESSION['checkout_error'])) {
    $error = $_SESSION['checkout_error'];
    unset($_SESSION['checkout_error']);
}
?>

<section id="checkout">
    <div class="container mt-5">
        <h2>Finalizar Compra</h2>

        <!-- Itens do Carrinho -->
        <h4>Itens no Carrinho</h4>
        <table class="table table-striped">
            <thead>
                <tr>
                    <th>Produto</th>
                    <th>Preço Unitário</th>
                    <th>Quantidade</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['cart'] as $item): ?>
                    <tr>
                        <td><?= htmlspecialchars($item['product_name']); ?></td>
                        <td>R$ <?= number_format($item['product_price'], 2, ',', '.'); ?></td>
                        <td><?= $item['quantity']; ?></td>
                        <td>R$ <?= number_format($item['product_price'] * $item['quantity'], 2, ',', '.'); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <h4>Total: R$ <?= number_format($total_price, 2, ',', '.'); ?></h4>

        <?php if ($error): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error); ?> <a href="login.php">Login</a></div>
        <?php endif; ?>

        <!-- Formulário de Dados de Envio -->
        <h4 class="mt-5">Dados de Envio</h4>
        <form method="POST" action="server/place_order.php">
            <div class="form-group mt-3">
                <label>UF</label>
                <input type="text" name="shipping_uf" class="form-control" maxlength="2" required>
            </div>
            <div class="form-group mt-3">
                <label>Cidade</label>
                <input type="text" name="shipping_city" class="form-control" required>
            </div>
            <div class="form-group mt-3">
                <label>Endereço</label>
                <input type="text" name="shipping_address" class="form-control" required>
            </div>
            <button type="submit" class="btn btn-success mt-3">Finalizar Compra</button>
        </form>
    </div>
</section>

<?php include('layouts/footer.php'); ?>
