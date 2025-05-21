<?php
session_start();

// Lógica para atualizar as quantidades
if (isset($_POST['update_quantity'])) {
    $product_key = $_POST['product_key'];
    $new_quantity = $_POST['quantity'];
    echo $new_quantity . " - " . $product_key;

    if ($new_quantity > 0) {
        $_SESSION['cart'][$product_key]['quantity'] = $new_quantity;
    }
}

if (isset($_POST['add_to_cart'])) {
    $product_id = $_POST['product_id'];
    $product_name = $_POST['product_name'];
    $product_price = $_POST['product_price'];
    $quantity = $_POST['quantity'];

    // Inicializa o carrinho, se não existir
    if (!isset($_SESSION['cart'])) {
        $_SESSION['cart'] = [];
    }

    // Verifica se o produto já está no carrinho
    //se já estiver, incrementa a quantidade
    $product_ids = array_column($_SESSION['cart'], 'product_id');
    if (in_array($product_id, $product_ids)) {
        foreach ($_SESSION['cart'] as &$item) {
            if ($item['product_id'] == $product_id) {
                $item['quantity'] += $quantity;
                break;
            }
        }
    } else {
        // Adiciona um novo produto ao carrinho
        $_SESSION['cart'][] = [
            'product_id' => $product_id,
            'product_name' => $product_name,
            'product_price' => $product_price,
            'quantity' => $quantity
        ];
    }
    header('Location: cart.php');

}


if (isset($_POST['product_key_delete'])) {
    $product_key = $_POST['product_key_delete'];

    // Remove o item do carrinho
    unset($_SESSION['cart'][$product_key]);

    // Reindexa os índices do carrinho
    $_SESSION['cart'] = array_values($_SESSION['cart']);
    header('Location: cart.php');
}
// Lógica para calcular o total
$total_price = 0;
if (isset($_SESSION['cart'])) {
    foreach ($_SESSION['cart'] as $item) {
        $total_price += $item['product_price'] * $item['quantity'];
    }
}

include('layouts/header.php');
?>
<section id="cart">
    <div class="container mt-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h2>Meu Carrinho</h2>

            <?php if (!empty($_SESSION['cart'])): ?>
            <a href="products.php" class="btn btn-primary">Continuar Comprando</a>
            <?php endif; ?>

        </div>
        <?php if (!empty($_SESSION['cart'])): ?>
        <table class="table table-striped">
            <thead class="table-dark">
                <tr>
                    <th>Produto</th>
                    <th>Preço Unitário</th>
                    <th>Quantidade</th>
                    <th>Subtotal</th>
                    <th>Ações</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($_SESSION['cart'] as $key => $item): ?>
                <tr>
                    <td><?= $item['product_name']; ?></td>
                    <td>R$ <?= number_format($item['product_price'], 2, ',', '.'); ?></td>
                    <td>
                        <!-- Formulário para alterar a quantidade -->
                        <form method="POST" action="cart.php" class="d-flex">
                            <input type="hidden" name="product_key" value="<?= $key; ?>">
                            <input type="number" name="quantity" class="form-control w-25 me-2"
                                value="<?= $item['quantity']; ?>" min="1">
                            <button type="submit" name="update_quantity"
                                class="btn btn-primary btn-sm">Atualizar</button>
                        </form>
                    </td>
                    <td>R$ <?= number_format($item['product_price'] * $item['quantity'], 2, ',', '.'); ?></td>
                    <td>
                        <form method="POST" action="cart.php">
                            <input type="hidden" name="product_key_delete" value="<?= $key; ?>">
                            <button type="submit" class="btn btn-danger btn-sm">Remover</button>
                        </form>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
        <div class="text-end">
            <h4 class="my-3">Total: R$ <?= number_format($total_price, 2, ',', '.'); ?></h4>
            <a href="checkout.php" class="btn btn-success">Finalizar Compra</a>
        </div>
        <?php else: ?>
        <p class="text-muted">Seu carrinho está vazio.</p>
        <a href="products.php" class="btn btn-primary">Continuar Comprando</a>
        <?php endif; ?>
    </div>
</section>

<?php include('layouts/footer.php'); ?>