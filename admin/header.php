<?php 
    session_start();
    include('../server/connection.php');

    if (isset($_GET['logout'])) {
        session_destroy();
        unset($_SESSION['admin_id']);
        unset($_SESSION['admin_email']);
        header('Location: login.php');
        exit;
    }
?>

<!doctype html>
<html lang="en">
    <head>
        <meta charset="UTF-8">
        <title>Admin Dashboard</title>
        <link rel="canonical" href="https://getbootstrap.com/docs/5.1/examples/dashboard/">
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/css/bootstrap.min.css" rel="stylesheet" crossorigin="anonymous">
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.1/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    </head>
    <body>
        <header class="navbar navbar-dark sticky-top bg-dark flex-md-nowrap p-0 shadow">
            <div class="container-fluid">
                <span class="navbar-brand mb-0 h1">Admin</span>
                <?php if (isset($_SESSION['admin_id'])): ?>
                    <a href="?logout=1" class="btn btn-link btn-sm text-decoration-none text-light">Sign out</a>
                <?php endif; ?>
            </div>
        </header>