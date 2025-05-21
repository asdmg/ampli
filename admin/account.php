<?php
ob_start();
include('header.php');

if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

$errors = [];
$success = false;

// Ações: deletar usuário (se existir ?delete=ID)
if (isset($_GET['delete'])) {
    $delete_id = intval($_GET['delete']);
    if ($delete_id === $_SESSION['admin_id']) {
        $errors[] = "Você não pode excluir seu próprio usuário.";
    } else {
        $stmt = $conn->prepare("DELETE FROM users WHERE user_id = ?");
        $stmt->bind_param("i", $delete_id);
        if ($stmt->execute()) {
            $success = "Usuário excluído com sucesso.";
        } else {
            $errors[] = "Erro ao excluir usuário: " . $stmt->error;
        }
    }
}

// Buscar lista de usuários
$result = $conn->query("SELECT user_id, user_name, user_email FROM users ORDER BY user_name ASC");
?>

<div class="d-flex">
    <?php include('sidemenu.php'); ?>
    <main class="flex-grow-1 p-3">
        <h2>Usuários</h2>
        <hr>

        <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
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

        <table class="table table-striped table-bordered">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nome</th>
                    <th>Email</th>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($user = $result->fetch_assoc()): ?>
                        <tr>
                            <td><?= $user['user_id'] ?></td>
                            <td><?= htmlspecialchars($user['user_name']) ?></td>
                            <td><?= htmlspecialchars($user['user_email']) ?></td>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="text-center">Nenhum usuário encontrado.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </main>
</div>
