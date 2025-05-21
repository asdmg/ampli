<?php
    ob_start();
    include('header.php');

    if (!isset($_SESSION['admin_id'])) {
        header('Location: login.php');
        exit;
    }

    // Lógica para buscar pedidos do banco com paginação
    $itemsPerPage = 5;
    $page = isset($_GET['page']) && is_numeric($_GET['page']) ? intval($_GET['page']) : 1;
    $offset = ($page - 1) * $itemsPerPage;

    // Contar total de pedidos
    $totalResult = $conn->query("SELECT COUNT(*) as total FROM orders");
    $total = $totalResult->fetch_assoc()['total'];

    // Buscar pedidos paginados
    $stmt = $conn->prepare("SELECT order_id, user_id, order_status, order_date FROM orders ORDER BY order_id DESC LIMIT ?, ?");
    $stmt->bind_param("ii", $offset, $itemsPerPage);
    $stmt->execute();
    $pedidos = $stmt->get_result();

    if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
        $orderId = intval($_GET['id']);

        $stmt = $conn->prepare("DELETE FROM orders WHERE order_id = ?");
        $stmt->bind_param("i", $orderId);
        $stmt->execute();

        // Redireciona para evitar repetição do delete se atualizar a página
        header('Location: index.php');
        exit;
    }
?>

        <div class="d-flex">
            <?php include('sidemenu.php'); ?>
            <main class="flex-grow-1 p-3">
                <h2>Dashboard</h2>
                <hr>
                <h2>Orders</h2>
                <table class="table table-striped align-middle">
                    <thead>
                        <tr>
                            <th>Order Id</th>
                            <th>Order Status</th>
                            <th>User Id</th>
                            <th>Order Date</th>
                            <th>Edit</th>
                            <th>Delete</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($pedido = $pedidos->fetch_assoc()): ?>
                            <tr>
                                <td><?= $pedido['order_id'] ?></td>
                                <td><?= htmlspecialchars($pedido['order_status']) ?></td>
                                <td><?= htmlspecialchars($pedido['user_id']) ?></td>
                                <td><?= htmlspecialchars($pedido['order_date']) ?></td>
                                <td>
                                    <a href="edit_order.php?id=<?= $pedido['order_id'] ?>" class="btn btn-sm btn-primary">Edit</a>
                                </td>
                                <td>
                                    <a href="index.php?action=delete&id=<?= $pedido['order_id'] ?>"
                                    class="btn btn-sm btn-danger"
                                    onclick="return confirm('Tem certeza que deseja excluir este pedido?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
                
                <nav aria-label="Page navigation">
                    <ul class="pagination">
                        <?php
                        $totalPages = ceil($total / $itemsPerPage);
                        $previousPage = $page - 1;
                        $nextPage = $page + 1;

                        // Botão "Previous"
                        echo "<li class='page-item " . ($page <= 1 ? 'disabled' : '') . "'>
                                <a class='page-link' href='index.php?page=$previousPage' tabindex='-1'>Previous</a>
                            </li>";

                        // Números de páginas
                        for ($i = 1; $i <= $totalPages; $i++) {
                            $active = $i == $page ? 'active' : '';
                            echo "<li class='page-item $active'><a class='page-link' href='index.php?page=$i'>$i</a></li>";
                        }

                        // Botão "Next"
                        echo "<li class='page-item " . ($page >= $totalPages ? 'disabled' : '') . "'>
                                <a class='page-link' href='index.php?page=$nextPage'>Next</a>
                            </li>";
                        ?>
                    </ul>
                </nav>
            </main>
        </div>
    </body>
</html>
