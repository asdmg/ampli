<?php
ob_start();
include('header.php');

// Validação de sessão
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: products.php');
    exit;
}

$productId = intval($_GET['id']);
$errors = [];
$success = false;
$uploadDir = dirname(__DIR__) . '/assets/imgs/products/';

if (!file_exists($uploadDir)) {
    if (!mkdir($uploadDir, 0755, true)) {
        $errors[] = "Erro: não foi possível criar o diretório assets/imgs/products/";
    }
}

// Busca imagens atuais
$stmt = $conn->prepare("SELECT product_image, product_image2, product_image3, product_image4 FROM products WHERE product_id = ?");
$stmt->bind_param("i", $productId);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    header('Location: products.php');
    exit;
}

$product = $result->fetch_assoc();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $images = [
        'product_image' => $product['product_image'],
        'product_image2' => $product['product_image2'],
        'product_image3' => $product['product_image3'],
        'product_image4' => $product['product_image4'],
    ];

    function uploadImageEdit($key, $uploadDir, $oldImage) {
        if (!empty($_FILES[$key]['name'])) {
            $fileName = uniqid() . '_' . basename($_FILES[$key]['name']);
            $filePath = $uploadDir . $fileName;

            if (move_uploaded_file($_FILES[$key]['tmp_name'], $filePath)) {
                // Remove imagem antiga
                if ($oldImage && file_exists(dirname(__DIR__) . $oldImage)) {
                    @unlink(dirname(__DIR__) . $oldImage);
                }
                return "/assets/imgs/products/" . $fileName;
            } else {
                return false;
            }
        }
        return $oldImage;
    }

    foreach ($images as $key => $oldImage) {
        $newImage = uploadImageEdit($key, $uploadDir, $oldImage);
        if ($newImage === false) {
            $errors[] = "Erro ao enviar a imagem $key";
        } else {
            $images[$key] = $newImage;
        }
    }

    if (empty($errors)) {
        $stmtUpdate = $conn->prepare("UPDATE products SET product_image = ?, product_image2 = ?, product_image3 = ?, product_image4 = ? WHERE product_id = ?");
        $stmtUpdate->bind_param("ssssi", 
            $images['product_image'], 
            $images['product_image2'], 
            $images['product_image3'], 
            $images['product_image4'], 
            $productId
        );

        if ($stmtUpdate->execute()) {
            $success = true;
            // Atualiza as imagens atuais para o formulário refletir
            $product = $images;
        } else {
            $errors[] = "Erro ao salvar no banco: " . $stmtUpdate->error;
        }
    }
}
?>

<div class="d-flex">
    <?php include('sidemenu.php'); ?>
    <main class="flex-grow-1 p-3">
        <h2>Editar Imagens do Produto</h2>
        <hr>

        <?php if ($success): ?>
            <div class="alert alert-success">Imagens atualizadas com sucesso!</div>
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

        <form method="POST" enctype="multipart/form-data" class="row g-3">
            <?php for ($i = 1; $i <= 4; $i++):
                $imgKey = $i === 1 ? 'product_image' : 'product_image' . $i;
                $imgPath = $product[$imgKey] ?? null;
            ?>
                <div class="col-md-3">
                    <label>Imagem <?= $i ?></label><br>
                    <?php if ($imgPath): ?>
                        <img src="<?= htmlspecialchars($imgPath) ?>" alt="Imagem <?= $i ?>" style="max-width:100%; max-height:150px; margin-bottom:10px; border:1px solid #ccc; padding:2px;">
                    <?php else: ?>
                        <span class="text-muted">Sem imagem</span><br>
                    <?php endif; ?>
                    <input type="file" name="<?= $imgKey ?>" class="form-control">
                </div>
            <?php endfor; ?>

            <div class="col-12">
                <button type="submit" class="btn btn-primary">Atualizar Imagens</button>
                <a href="products.php" class="btn btn-secondary">Voltar</a>
            </div>
        </form>
    </main>
</div>
</body>
</html>
