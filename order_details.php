<?php
session_start();
include('layouts/header.php');
include('server/connection.php');

if (!isset($_POST['order_id'])) {
    echo "<div class='container mt-5'><h3>ID do pedido não informado.</h3></div>";
    include('layouts/footer.php');
    exit();
}

$order_id = intval($_POST['order_id']);

$stmt = $conn->prepare("
    SELECT p.product_name, p.product_price, oi.qnt 
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$result = $stmt->get_result();

$stmt_status = $conn->prepare("SELECT order_status FROM orders WHERE order_id = ?");
$stmt_status->bind_param("i", $order_id);
$stmt_status->execute();
$status_result = $stmt_status->get_result();
$order = $status_result->fetch_assoc();

if (!$order) {
    echo "<div class='container mt-5'><h3>Pedido não encontrado.</h3></div>";
    include('layouts/footer.php');
    exit();
}

?>

<div class="container mt-5">
    <h2>Detalhes do Pedido #<?= $order_id ?></h2>

    <table class="table table-striped">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Preço Unitário</th>
                <th>Quantidade</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($row = $result->fetch_assoc()): ?>
            <tr>
                <td><?= htmlspecialchars($row['product_name']) ?></td>
                <td>R$ <?= number_format($row['product_price'], 2, ',', '.') ?></td>
                <td><?= $row['qnt'] ?></td>
            </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <?php if ($order['order_status'] === 'not paid'): ?>
        <form method="POST" action="payment.php">
            <input type="hidden" name="order_id" value="<?= $order_id ?>">
            <button type="submit" class="btn btn-primary">Pague agora</button>
        </form>
    <?php else: ?>
        <p>Pedido já foi pago.</p>
    <?php endif; ?>
</div>

<?php include('layouts/footer.php'); ?>
