<?php
require_once __DIR__ . '/includes/auth.php';
start_session();
$pageTitle = 'Privacy Policy';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/navbar.php';
?>
<main class="container py-5">
    <?php require __DIR__ . '/includes/alerts.php'; ?>
    <div class="venuvia-card p-4 p-md-5">
        <h1 class="h3 fw-bold mb-4">Privacy Policy</h1>
<p>Venuvia stores only the account and booking information needed to operate the platform: your name, email address, phone number, bookings, payments (simulated) and tickets.</p>
    <p>Passwords are stored as secure hashes and are never shown in plain text. QR codes on tickets contain only a signed ticket token - they do not contain your name, email or payment details.</p>
    <p>Your information is not sold to third parties. Organizers can only see the booking information for their own events.</p>
    </div>
</main>
<?php require __DIR__ . '/includes/footer.php'; ?>
