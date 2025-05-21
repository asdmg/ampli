<?php
session_start();
include('connection.php');

// Verifica se usuário está logado
if (!isset($_SESSION['user_id'])) {
    $_SESSION['checkout_error'] = "Você deve fazer login antes de finalizar a compra.";
    header('Location: checkout.php');
    exit();
}

// Verifica se existe carrinho, senão redireciona
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: index.php');
    exit();
}

// Recebe dados do formulário
$shipping_uf = $_POST['shipping_uf'] ?? '';
$shipping_city = $_POST['shipping_city'] ?? '';
$shipping_address = $_POST['shipping_address'] ?? '';
$user_id = $_SESSION['user_id'];
$order_date = date('Y-m-d H:i:s');

// Calcula total do pedido
$total_price = 0;
foreach ($_SESSION['cart'] as $item) {
    $total_price += $item['product_price'] * $item['quantity'];
}

// Insere pedido na tabela orders
$query = "INSERT INTO orders (user_id, order_cost, order_status, shipping_city, shipping_address, shipping_uf, order_date) 
          VALUES (?, ?, 'processing', ?, ?, ?, ?)";

$stmt = $conn->prepare($query);
$stmt->bind_param('idssss', $user_id, $total_price, $shipping_city, $shipping_address, $shipping_uf, $order_date);

if ($stmt->execute()) {
    $order_id = $stmt->insert_id;

    // Insere itens na tabela order_items
    $stmt_item = $conn->prepare("INSERT INTO order_items (order_id, product_id, user_id, qnt, order_date) VALUES (?, ?, ?, ?, ?)");
    
    foreach ($_SESSION['cart'] as $item) {
        $product_id = $item['product_id'];
        $quantity = $item['quantity'];
        $stmt_item->bind_param('iiiis', $order_id, $product_id, $user_id, $quantity, $order_date);
        $stmt_item->execute();
    }
    
    // Salva o id do pedido numa sessão separada
    $_SESSION['last_order_id'] = $order_id;

    // Limpa carrinho
    unset($_SESSION['cart']);

    // Redireciona para página de confirmação
    header("Location: ../order_confirmation.php?order_id=$order_id");
    exit();
} else {
    // Em caso de erro, volta para checkout com mensagem
    $_SESSION['checkout_error'] = "Erro ao processar o pedido. Por favor, tente novamente.";
    header('Location: checkout.php');
    exit();
}
?>
