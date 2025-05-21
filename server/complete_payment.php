<?php
session_start();
include('connection.php');

if (!isset($_GET['transaction_id']) || !isset($_GET['order_id']) || !isset($_SESSION['user_id'])) {
    echo "Parâmetros inválidos ou usuário não logado.";
    exit();
}

$transaction_id = $_GET['transaction_id'];
$order_id = intval($_GET['order_id']);
$user_id = $_SESSION['user_id'];


$stmt = $conn->prepare("SELECT order_status FROM orders WHERE order_id = ? AND user_id = ?");
$stmt->bind_param('ii', $order_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo "Pedido não encontrado.";
    exit();
}

$order = $result->fetch_assoc();
if ($order['order_status'] === 'paid') {
    echo "Pedido já pago.";
    exit();
}


$stmt_update = $conn->prepare("UPDATE orders SET order_status = 'paid' WHERE order_id = ?");
$stmt_update->bind_param('i', $order_id);
$stmt_update->execute();


$stmt_payment = $conn->prepare("INSERT INTO payments (order_id, user_id, transaction_id) VALUES (?, ?, ?)");
$stmt_payment->bind_param('iis', $order_id, $user_id, $transaction_id);
$stmt_payment->execute();

header("Location: ../order_confirmation.php?order_id=$order_id");
exit();
?>
