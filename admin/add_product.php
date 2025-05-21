<?php
ob_start();
include('header.php');

// Validação de sessão
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$errors = [];
$success = false;

$uploadDir = dirname(__DIR__) . '/assets/imgs/products/';

if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        $errors[] = "Erro: não foi possível criar o diretório assets/imgs/products/";
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['product_name'];
    $category = $_POST['product_category'];
    $description = $_POST['product_description'];
    $price = $_POST['product_price'];
    $offer = isset($_POST['product_special_offer']) ? 1 : 0;
    $color = $_POST['product_color'];

    $images = [];
    for ($i = 1; $i <= 4; $i++) {
        $key = "product_image$i";
        if (!empty($_FILES[$key]['name'])) {
            $fileName = uniqid() . '_' . basename($_FILES[$key]['name']);
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES[$key]['tmp_name'], $filePath)) {
                $images[] = "/assets/imgs/products/" . $fileName;
            } else {
                $images[] = '';
                $errors[] = "Erro ao enviar imagem $i";
            }
        } else {
            $images[] = '';
        }
    }

    if (empty($errors)) {
        $stmt = $conn->prepare("INSERT INTO products (product_name, product_category, product_description, product_image, product_image2, product_image3, product_image4, product_price, product_special_offer, product_color) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssssdis", $name, $category, $description, $images[0], $images[1], $images[2], $images[3], $price, $offer, $color);

        if ($stmt->execute()) {
            $success = true;
        } else {
            $errors[] = "Erro ao salvar no banco: " . $stmt->error;
        }
    }
}
?>

<div class="d-flex">
    <?php include('sidemenu.php'); ?>
    <main class="flex-grow-1 p-3">
        <h2>Novo Produto</h2>
        <hr>

        <?php if ($success): ?>
            <div class="alert alert-success">Produto cadastrado com sucesso!</div>
        <?php endif; ?>

        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data" class="row g-3">
            <div class="col-md-6">
                <label>Nome</label>
                <input type="text" name="product_name" class="form-control" required>
            </div>

            <div class="col-md-6">
                <label>Categoria</label>
                <input type="text" name="product_category" class="form-control" required>
            </div>

            <div class="col-md-12">
                <label>Descrição</label>
                <textarea name="product_description" class="form-control" required></textarea>
            </div>

            <div class="col-md-4">
                <label>Imagem 1</label>
                <input type="file" name="product_image1" class="form-control">
            </div>
            <div class="col-md-4">
                <label>Imagem 2</label>
                <input type="file" name="product_image2" class="form-control">
            </div>
            <div class="col-md-4">
                <label>Imagem 3</label>
                <input type="file" name="product_image3" class="form-control">
            </div>
            <div class="col-md-4">
                <label>Imagem 4</label>
                <input type="file" name="product_image4" class="form-control">
            </div>

            <div class="col-md-4">
                <label>Preço</label>
                <input type="number" step="0.01" name="product_price" class="form-control" required>
            </div>

            <div class="col-md-4">
                <label>Cor</label>
                <input type="text" name="product_color" class="form-control" required>
            </div>

            <div class="col-md-4 form-check">
                <label class="form-check-label">
                    <input class="form-check-input" type="checkbox" name="product_special_offer"> Em Oferta
                </label>
            </div>

            <div class="col-12">
                <button type="submit" class="btn btn-success">Salvar Produto</button>
            </div>
        </form>
    </main>
</div>