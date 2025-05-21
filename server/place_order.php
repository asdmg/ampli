<?php
session_start();
include('connection.php');

if (!isset($_SESSION['user_id'])) {
    $_SESSION['checkout_error'] = "Você deve fazer login antes de finalizar a compra.";
    header('Location: ../checkout.php');
    exit();
}

if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: ../index.php');
    exit();
}

$shipping_uf = $_POST['shipping_uf'] ?? '';
$shipping_city = $_POST['shipping_city'] ?? '';
$shipping_address = $_POST['shipping_address'] ?? '';
$user_id = $_SESSION['user_id'];
$order_date = date('Y-m-d H:i:s');


$total_price = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_price += $item['product_price'] * $item['quantity'];
}

$query = "INSERT INTO orders (user_id, order_cost, order_status, shipping_city, shipping_address, shipping_uf, order_date) 
          VALUES (?, ?, 'not paid', ?, ?, ?, ?)";

$stmt = $conn->prepare($query);
$stmt->bind_param('idssss', $user_id, $total_price, $shipping_city, $shipping_address, $shipping_uf, $order_date);

if ($stmt->execute()) {
    $order_id = $stmt->insert_id;

    $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, user_id, qnt, order_date) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($_SESSION['cart'] as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        $stmt_item->bind_param('iiiis', $order_id, $product_id, $user_id, $quantity, $order_date);
        $stmt_item->execute();
    }
    
    $_SESSION['last_order_id'] = $order_id;

    unset($_SESSION['cart']);

    header("Location: ../payment.php?order_id=$order_id");
    exit();
} else {
    $_SESSION['checkout_error'] = "Erro ao processar o pedido. Por favor, tente novamente.";
    header('Location: ../checkout.php');
    exit();
}
?>
