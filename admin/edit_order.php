<?php
ob_start();
include('header.php');

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID do pedido inválido.");
}

$id = intval($_GET['id']);
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $status = $_POST['status'] ?? '';

    // Validar status
    $valid_status = ['on_hold', 'paid', 'shipped', 'delivered'];
    if (!in_array($status, $valid_status)) {
        $error = "Status inválido.";
    } else {
        // Atualizar no banco
        $stmt = $conn->prepare("UPDATE orders SET order_status = ? WHERE order_id = ?");
        $stmt->bind_param('si', $status, $id);
        if ($stmt->execute()) {
            $success = "Status atualizado com sucesso!";
        } else {
            $error = "Erro ao atualizar o status.";
        }
    }
}

// Buscar pedido para exibir
$stmt = $conn->prepare("SELECT * FROM orders WHERE order_id = ?");
$stmt->bind_param('i', $id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows !== 1) {
    die("Pedido não encontrado.");
}
$order = $result->fetch_assoc();
?>

<div class="container mt-4">
    <h2>Editar Pedido #<?= $order['order_id'] ?></h2>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
    <?php endif; ?>

    <?php if ($success): ?>
        <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
    <?php endif; ?>

    <form method="POST">
        <div class="mb-3">
            <label for="status" class="form-label">Status</label>
            <select name="status" id="status" class="form-select" required>
                <option value="on_hold" <?= $order['order_status'] === 'on_hold' ? 'selected' : '' ?>>Em análise</option>
                <option value="paid" <?= $order['order_status'] === 'paid' ? 'selected' : '' ?>>Pago</option>
                <option value="shipped" <?= $order['order_status'] === 'shipped' ? 'selected' : '' ?>>Enviado</option>
                <option value="delivered" <?= $order['order_status'] === 'delivered' ? 'selected' : '' ?>>Entregue</option>
            </select>
        </div>
        <button type="submit" class="btn btn-primary">Salvar</button>
        <a href="index.php" class="btn btn-secondary">Voltar</a>
    </form>
</div>

</body>
</html>
