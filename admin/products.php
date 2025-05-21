<?php
ob_start();
include('header.php');

// Validação de sessão
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$itemsPerPage = 5;
$page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
$offset = ($page - 1) * $itemsPerPage;

// total de produtos
$totalResult = $conn->query("SELECT COUNT(*) as total FROM products");
$total = $totalResult->fetch_assoc()['total'];

// buscar produtos
$stmt = $conn->prepare("SELECT * FROM products ORDER BY product_id DESC LIMIT ?, ?");
$stmt->bind_param("ii", $offset, $itemsPerPage);
$stmt->execute();
$produtos = $stmt->get_result();

if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $productId = intval($_GET['id']);

        $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $productId);
        $stmt->execute();

        // Redireciona para evitar repetição do delete se atualizar a página
        header('Location: products.php');
        exit;
    }
?>

<div class="d-flex">
    <?php include('sidemenu.php'); ?>

    <main class="flex-grow-1 p-4">
        <h1>Produtos</h1>

        <div class="table-responsive">
            <table class="table table-striped align-middle">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nome</th>
                        <th>Categoria</th>
                        <th>Preço</th>
                        <th>Oferta (%)</th>
                        <th>Cor</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($produto = $produtos->fetch_assoc()): ?>
                    <tr>
                        <td><?= $produto['product_id'] ?></td>
                        <td><?= htmlspecialchars($produto['product_name']) ?></td>
                        <td><?= htmlspecialchars($produto['product_category']) ?></td>
                        <td>R$ <?= number_format($produto['product_price'], 2, ',', '.') ?></td>
                        <td><?= $produto['product_special_offer'] ?></td>
                        <td><?= htmlspecialchars($produto['product_color']) ?></td>
                        <td>
                            <a href="edit_images.php?id=<?= $produto['product_id'] ?>" class="btn btn-sm btn-warning">Editar Imagens</a>
                            <a href="edit_product.php?id=<?= $produto['product_id'] ?>" class="btn btn-sm btn-primary">Editar Produto</a>
                            <a href="products.php?action=delete&id=<?= $produto['product_id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Excluir produto?')">Excluir</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>

        <!-- Paginação bootstrap -->
        <nav aria-label="Paginação">
            <ul class="pagination">
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
    </main>
</div>

</body>
</html>
