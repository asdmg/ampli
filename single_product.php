<?php
include('layouts/header.php');
include('server/connection.php');

if (isset($_GET['product_id'])) {
    $product_id = $_GET['product_id'];
    $query = "SELECT * FROM products WHERE product_id = $product_id";
    $result = mysqli_query($conn, $query);
    $product = mysqli_fetch_assoc($result);
} else {
    header('Location: products.php');
    exit();
}
?>

<div class="container mt-5">
    <div class="row">
        <!-- <div class="col-md-6">
            <div class="main-image-container">
                <img id="main-image" src="assets/imgs/<?= $product['product_image']; ?>" class="img-fluid mb-3"
                    alt="<?= $product['product_name']; ?>">
            </div>
            <div class="row">
                <!-- Miniaturas das Imagens -->
        <!-- <?php
        // Array para guardar as imagens
        $images = [];
        for ($i = 1; $i <= 4; $i++):
            $image_field = ($i == 1) ? "product_image" : "product_image$i";
            if (!empty($product[$image_field])):
                $images[] = $product[$image_field];
                ?>
                        <div class="col-3 mt-3">
                            <img src="assets/imgs/<?= $product[$image_field]; ?>" class="img-thumbnail clickable-image"
                                alt="Imagem <?= $i; ?>">
                        </div>
                        <?php
            endif;
        endfor;
        ?>
            </div>
        </div> -->
        <!-- Coluna do Slider -->
        <div class="col-md-6">
            <div id="productCarousel" class="carousel carousel-dark slide" data-bs-ride="carousel">
                <!-- Indicadores -->
                <div class="carousel-indicators">
                    <?php for ($i = 0; $i < 4; $i++):
                        $image_field = $i == 0 ? "product_image" : "product_image" . ($i + 1);
                        if (!empty($product[$image_field])): ?>
                            <button type="button" data-bs-target="#productCarousel" data-bs-slide-to="<?= $i; ?>"
                                class="<?= $i === 0 ? 'active' : ''; ?>" aria-current="<?= $i === 0 ? 'true' : ''; ?>">
                            </button>
                        <?php endif; endfor; ?>
                </div>

                <!-- Slides -->
                <div class="carousel-inner">
                    <?php for ($i = 1; $i <= 4; $i++):
                        $image_field = $i == 1 ? "product_image" : "product_image$i";
                        if (!empty($product[$image_field])): ?>
                            <div class="carousel-item <?= $i === 1 ? 'active' : ''; ?>">
                                <img src="assets/imgs/<?= $product[$image_field]; ?>" class="d-block w-100"
                                    alt="Imagem <?= $i; ?>">
                            </div>
                        <?php endif; endfor; ?>
                </div>

                <!-- Controles -->
                <button class="carousel-control-prev" type="button" data-bs-target="#productCarousel"
                    data-bs-slide="prev">
                    <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Anterior</span>
                </button>
                <button class="carousel-control-next" type="button" data-bs-target="#productCarousel"
                    data-bs-slide="next">
                    <span class="carousel-control-next-icon" aria-hidden="true"></span>
                    <span class="visually-hidden">Próximo</span>
                </button>
            </div>
        </div>

        <div class="col-md-6 mt-4">
            <h2><?= $product['product_name']; ?></h2>
            <p class="text-muted"><?= $product['product_category']; ?></p>
            <p><?= $product['product_description']; ?></p>
            <h4 class="text-danger">R$ <?= $product['product_price']; ?></h4>

            <form method="POST" action="cart.php">
                <input type="hidden" name="product_id" value="<?= $product['product_id']; ?>">
                <input type="hidden" name="product_name" value="<?= $product['product_name']; ?>">
                <input type="hidden" name="product_price" value="<?= $product['product_price']; ?>">
                <div class="form-group">
                    <label for="quantity">Quantidade</label>
                    <input type="number" name="quantity" id="quantity" class="form-control" value="1" min="1">
                </div>
                <button type="submit" name="add_to_cart" class="btn btn-success mt-3">Adicionar ao Carrinho</button>
            </form>
        </div>
    </div>
</div>

<?php include('layouts/footer.php'); ?>