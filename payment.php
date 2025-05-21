<?php
session_start();
include('layouts/header.php');
include('server/connection.php');

if (!isset($_GET['order_id'])) {
    echo "<div class='alert alert-danger'>Pedido não encontrado.</div>";
    exit();
}

$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'] ?? null;

if (!$user_id) {
    echo "<div class='alert alert-danger'>Você precisa estar logado para pagar.</div>";
    exit();
}

$stmt = $conn->prepare("SELECT order_cost, order_status FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->bind_param('ii', $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "<div class='alert alert-danger'>Pedido não encontrado ou não pertence a você.</div>";
    exit();
}

$order = $result->fetch_assoc();

if ($order['order_status'] !== 'not paid') {
    echo "<div class='alert alert-info'>Este pedido já foi pago ou está processando.</div>";
    exit();
}

$amount = number_format($order['order_cost'], 2, '.', '');
?>

<div class="container mt-5">
    <h2>Pagamento do Pedido #<?= $order_id ?></h2>
    <p>Total a pagar: R$ <?= number_format($order['order_cost'], 2, ',', '.'); ?></p>

    <div id="paypal-button-container"></div>
</div>

<script src="https://www.paypal.com/sdk/js?client-id=AR_qIG9T4c0fHEDBAOoyqHlwdSQwJkZGW6NRCHtAikH8H-DDK8czuZmFgrVWxvszbS2cRqNwL7RFxu2l&currency=BRL"></script>
<script>
paypal.Buttons({
    createOrder: function(data, actions) {
        return actions.order.create({
            purchase_units: [{
                amount: {
                    value: '<?= $amount ?>'
                }
            }]
        });
    },
    onApprove: function(data, actions) {
        return actions.order.capture().then(function(orderData) {
            var transaction = orderData.purchase_units[0].payments.captures[0];
            alert('Transação ' + transaction.status + ': ' + transaction.id);

            window.location.href = "server/complete_payment.php?transaction_id=" + transaction.id + "&order_id=<?= $order_id ?>";
        });
    }
}).render('#paypal-button-container');
</script>

<?php include('layouts/footer.php'); ?>
