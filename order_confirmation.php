<?php
session_start();
include('layouts/header.php');
include('server/connection.php');

// Verifica se o ID do pedido foi passado via GET ou via sessão
$order_id = null;
if (isset($_GET['order_id'])) {
    $order_id = $_GET['order_id'];
} elseif (isset($_SESSION['order_id'])) {
    $order_id = $_SESSION['order_id'];
    // Pode limpar a sessão se quiser
    unset($_SESSION['order_id']);
}

if (!$order_id) {
    // Se não tiver pedido, redireciona para página inicial ou carrinho
    header('Location: index.php');
    exit();
}

// Busca dados do pedido no banco
$query = $conn->prepare("
    SELECT o.order_id, o.order_cost, o.order_status, o.shipping_city, o.shipping_uf, o.shipping_address, o.order_date, u.user_name
    FROM orders o
    JOIN users u ON o.user_id = u.user_id
    WHERE o.order_id = ?
");
$query->bind_param('i', $order_id);
$query->execute();
$result = $query->get_result();

if ($result->num_rows == 0) {
    echo "<p>Pedido não encontrado.</p>";
    include('layouts/footer.php');
    exit();
}

$order = $result->fetch_assoc();

// Busca itens do pedido
$query_items = $conn->prepare("
    SELECT p.product_name, oi.qnt, p.product_price, (p.product_price * oi.qnt) AS subtotal
    FROM order_items oi
    JOIN products p ON oi.product_id = p.product_id
    WHERE oi.order_id = ?
");
$query_items->bind_param('i', $order_id);
$query_items->execute();
$items_result = $query_items->get_result();
?>

<section class="container mt-5">
    <h2>Pedido Confirmado</h2>
    <p>Obrigado pela sua compra, <strong><?= htmlspecialchars($order['user_name']); ?></strong>!</p>
    <p><strong>Número do pedido:</strong> <?= $order['order_id']; ?></p>
    <p><strong>Data do pedido:</strong> <?= date('d/m/Y H:i', strtotime($order['order_date'])); ?></p>
    <p><strong>Status:</strong> <?= htmlspecialchars($order['order_status']); ?></p>

    <h4>Itens do Pedido</h4>
    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Preço Unitário</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($item = $items_result->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($item['product_name']); ?></td>
                    <td><?= $item['qnt']; ?></td>
                    <td>R$ <?= number_format($item['product_price'], 2, ',', '.'); ?></td>
                    <td>R$ <?= number_format($item['subtotal'], 2, ',', '.'); ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

    <h4>Total Pago: R$ <?= number_format($order['order_cost'], 2, ',', '.'); ?></h4>

    <h4>Endereço de Entrega</h4>
    <p>
        <?= htmlspecialchars($order['shipping_address']); ?><br>
        <?= htmlspecialchars($order['shipping_city']); ?> - <?= htmlspecialchars($order['shipping_uf']); ?>
    </p>

    <a href="index.php" class="btn btn-primary mt-3">Voltar para a loja</a>
</section>

<?php include('layouts/footer.php'); ?>
