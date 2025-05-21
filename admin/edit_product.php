<?php
ob_start();
include('header.php');

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$product_id = intval($_GET['id']);
$errors = [];
$success = false;

// Buscar produto para preencher formulário
$stmt = $conn->prepare("SELECT product_name, product_category, product_description, product_price, product_special_offer, product_color FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: products.php');
    exit;
}

$product = $result->fetch_assoc();

// Processar submissão do formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['product_name']);
    $category = trim($_POST['product_category']);
    $description = trim($_POST['product_description']);
    $price = floatval($_POST['product_price']);
    $offer = isset($_POST['product_special_offer']) ? 1 : 0;
    $color = trim($_POST['product_color']);

    // Validações simples
    if (empty($name)) $errors[] = "Nome do produto é obrigatório.";
    if (empty($category)) $errors[] = "Categoria é obrigatória.";
    if (empty($description)) $errors[] = "Descrição é obrigatória.";
    if ($price <= 0) $errors[] = "Preço deve ser maior que zero.";
    if (empty($color)) $errors[] = "Cor é obrigatória.";

    if (empty($errors)) {
        $stmt = $conn->prepare("UPDATE products SET product_name=?, product_category=?, product_description=?, product_price=?, product_special_offer=?, product_color=? WHERE product_id=?");
        $stmt->bind_param("sssdisi", $name, $category, $description, $price, $offer, $color, $product_id);

        if ($stmt->execute()) {
            $success = true;
            // Atualiza os dados do produto para o formulário
            $product['product_name'] = $name;
            $product['product_category'] = $category;
            $product['product_description'] = $description;
            $product['product_price'] = $price;
            $product['product_special_offer'] = $offer;
            $product['product_color'] = $color;
        } else {
            $errors[] = "Erro ao atualizar produto: " . $stmt->error;
        }
    }
}
?>

<div class="d-flex">
    <?php include('sidemenu.php'); ?>
    <main class="flex-grow-1 p-3">
        <h2>Editar Produto</h2>
        <hr>

        <?php if ($success): ?>
            <div class="alert alert-success">Produto atualizado com sucesso!</div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" class="row g-3">
            <div class="col-md-6">
                <label>Nome</label>
                <input type="text" name="product_name" class="form-control" required value="<?= htmlspecialchars($product['product_name']) ?>">
            </div>

            <div class="col-md-6">
                <label>Categoria</label>
                <input type="text" name="product_category" class="form-control" required value="<?= htmlspecialchars($product['product_category']) ?>">
            </div>

            <div class="col-md-12">
                <label>Descrição</label>
                <textarea name="product_description" class="form-control" required><?= htmlspecialchars($product['product_description']) ?></textarea>
            </div>

            <div class="col-md-4">
                <label>Preço</label>
                <input type="number" step="0.01" name="product_price" class="form-control" required value="<?= htmlspecialchars($product['product_price']) ?>">
            </div>

            <div class="col-md-4">
                <label>Cor</label>
                <input type="text" name="product_color" class="form-control" required value="<?= htmlspecialchars($product['product_color']) ?>">
            </div>

            <div class="col-md-4 form-check">
                <input type="checkbox" name="product_special_offer" id="offer" class="form-check-input" <?= $product['product_special_offer'] ? 'checked' : '' ?>>
                <label for="offer" class="form-check-label">Em Oferta</label>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">Salvar Alterações</button>
                <a href="products.php" class="btn btn-secondary">Voltar</a>
            </div>
        </form>
    </main>
</div>
