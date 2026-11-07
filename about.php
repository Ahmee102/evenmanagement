<?php
require_once __DIR__ . '/includes/auth.php';
start_session();
$pageTitle = 'About Us';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/navbar.php';
?>
<main class="container py-5">
    <?php require __DIR__ . '/includes/alerts.php'; ?>
    <div class="venuvia-card p-4 p-md-5">
        <h1 class="h3 fw-bold mb-4">About Venuvia</h1>
<p>Venuvia is an event discovery and ticket booking platform built for concerts, conferences, sports, seminars and community events.</p>
    <p>Customers can discover approved events, book tickets online, pay through a simulated payment interface and receive a secure QR ticket for entry. Organizers can publish events, track ticket sales and verify tickets at the door with a QR scanner. Administrators moderate events, manage users and review booking and revenue reports.</p>
    <p>Venuvia was developed as a university final-year project using Core PHP, PDO, MySQL and Bootstrap 5, and runs locally on XAMPP.</p>
    </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
