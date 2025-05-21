<?php
include('layouts/header.php');
include('server/connection.php');

$itemsPerPage = 8;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $itemsPerPage;

// Total de produtos
$totalResult = $conn->query("SELECT COUNT(*) as total FROM products");
$total = $totalResult->fetch_assoc()['total'];

// Buscar produtos com limite e offset
$stmt = $conn->prepare("SELECT * FROM products ORDER BY product_id DESC LIMIT ?, ?");
$stmt->bind_param("ii", $offset, $itemsPerPage);
$stmt->execute();
$products = $stmt->get_result();
?>

<section class="container my-5">
    <h1 class="mb-4">Produtos</h1>

    <div class="row">
        <?php while ($product = $products->fetch_assoc()): ?>
            <div class="col-md-3 mb-4">
                <div class="card h-100" style="cursor:pointer;" onclick="location.href='single_product.php?id=<?= $product['product_id'] ?>'">
                    <img src="<?= htmlspecialchars($product['product_image']) ?>" class="card-img-top" alt="<?= htmlspecialchars($product['product_name']) ?>">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title"><?= htmlspecialchars($product['product_name']) ?></h5>
                        <p class="card-text text-success font-weight-bold">R$ <?= number_format($product['product_price'], 2, ',', '.') ?></p>
                        <a href="single_product.php?product_id=<?= $product['product_id'] ?>" class="btn btn-primary mt-auto">Ver Detalhes</a>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>

    <!-- Paginação Bootstrap -->
    <nav aria-label="Paginação">
        <ul class="pagination justify-content-center">
            <?php
            $totalPages = ceil($total / $itemsPerPage);
            $prevPage = max($page - 1, 1);
            $nextPage = min($page + 1, $totalPages);

            $disabledPrev = $page == 1 ? "disabled" : "";
            $disabledNext = $page == $totalPages ? "disabled" : "";

            echo "<li class='page-item $disabledPrev'><a class='page-link' href='products.php?page=$prevPage'>Anterior</a></li>";

            for ($i = 1; $i <= $totalPages; $i++) {
                $active = $i == $page ? "active" : "";
                echo "<li class='page-item $active'><a class='page-link' href='products.php?page=$i'>$i</a></li>";
            }

            echo "<li class='page-item $disabledNext'><a class='page-link' href='products.php?page=$nextPage'>Próximo</a></li>";
            ?>
        </ul>
    </nav>
</section>

<?php include('layouts/footer.php'); ?>
