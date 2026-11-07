<?php
require_once __DIR__ . '/includes/auth.php';
start_session();
$pageTitle = 'Contact';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/navbar.php';
?>
<main class="container py-5">
    <?php require __DIR__ . '/includes/alerts.php'; ?>
    <div class="venuvia-card p-4 p-md-5">
        <h1 class="h3 fw-bold mb-4">Contact Us</h1>
<p>For questions about an event, please contact the event organizer shown on the event details page. For help with a booking or a ticket, contact the Venuvia support team:</p>
    <ul class="list-unstyled">
        <li class="mb-2"><i class="bi bi-envelope me-2"></i>info@venuvia.com</li>
        <li class="mb-2"><i class="bi bi-telephone me-2"></i>+92 300 123 4567</li>
        <li class="mb-2"><i class="bi bi-geo-alt me-2"></i>Lahore, Pakistan</li>
    </ul>
    <p class="text-muted-venuvia">Support hours: Monday to Saturday, 9 AM to 6 PM (PKT).</p>
    </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
