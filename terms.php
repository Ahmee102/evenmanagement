<?php
require_once __DIR__ . '/includes/auth.php';
start_session();
$pageTitle = 'Terms & Conditions';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/navbar.php';
?>
<main class="container py-5">
    <?php require __DIR__ . '/includes/alerts.php'; ?>
    <div class="venuvia-card p-4 p-md-5">
        <h1 class="h3 fw-bold mb-4">Terms &amp; Conditions</h1>
<p>Tickets are issued only after a successful payment. Each QR ticket can be used for entry once; a ticket that has already been scanned will be rejected at the door.</p>
    <p>Payments on this system are simulated for academic purposes. No real financial transaction takes place.</p>
    <p>Event details, dates and venues are provided by organizers. Venuvia may remove events or accounts that violate platform rules.</p>
    </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
