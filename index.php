<?php
// Venuvia - public homepage (Phase 5).
// Public listings only ever show APPROVED events whose date has not
// passed; pending / rejected / past events never appear here.

require_once __DIR__ . '/includes/auth.php';
start_session();
require_once __DIR__ . '/includes/event-card.php';
$pdo = db();

$publicCols = "ev.*, c.name AS category_name,
    (SELECT COALESCE(SUM(b.quantity), 0) FROM bookings b WHERE b.event_id = ev.id AND b.status = 'confirmed') AS tickets_sold,
    (ev.total_seats - (SELECT COALESCE(SUM(b.quantity), 0) FROM bookings b WHERE b.event_id = ev.id AND b.status IN ('pending', 'confirmed'))) AS seats_left";
$publicFrom = " FROM events ev JOIN categories c ON c.id = ev.category_id
    WHERE ev.status = 'approved' AND ev.event_date >= CURDATE()";

// NOTE (owner's direction): NO platform statistics/numbers anywhere
// on the public site or in customer/organizer dashboards - totals
// live only inside the admin panel.

// Featured = best sellers first; Upcoming = soonest first.
$featured = $pdo->query('SELECT ' . $publicCols . $publicFrom . ' ORDER BY tickets_sold DESC, ev.event_date ASC LIMIT 4')->fetchAll();
$upcoming = $pdo->query('SELECT ' . $publicCols . $publicFrom . ' ORDER BY ev.event_date ASC, ev.start_time ASC LIMIT 6')->fetchAll();

$categories = $pdo->query(
    "SELECT c.id, c.name,
            (SELECT COUNT(*) FROM events ev WHERE ev.category_id = c.id AND ev.status = 'approved' AND ev.event_date >= CURDATE()) AS upcoming_count
     FROM categories c WHERE c.status = 'active' ORDER BY c.name"
)->fetchAll();

$pageTitle = 'Discover Events. Book Experiences.';
require __DIR__ . '/includes/header.php';
require __DIR__ . '/includes/navbar.php';
?>

<!-- ============================ HERO ============================ -->
<header class="hero-section">
    <img class="hero-media" src="<?= e(APP_URL) ?>/assets/images/hero-poster.jpg" alt="A live concert crowd at a Venuvia event">
    <div class="hero-overlay"></div>
    <button type="button" class="hero-play d-none d-lg-flex" data-bs-toggle="modal" data-bs-target="#promoModal" aria-label="Watch the Venuvia promo video">
        <i class="bi bi-play-fill"></i>
    </button>
    <div class="container hero-content">
        <span class="hero-kicker"><i class="bi bi-ticket-perforated me-1"></i> Event Booking Platform</span>
        <h1 class="hero-title">Find Your Next<br>Unforgettable <span class="hero-grad-text">Experience.</span></h1>
        <p class="hero-subtitle">Explore exciting events, discover new experiences, and secure your tickets in just a few clicks.</p>
        <div class="d-flex gap-3 mt-4 flex-wrap">
            <a class="btn btn-hero-primary btn-lg" href="<?= e(APP_URL) ?>/events/index.php">Explore Events <i class="bi bi-arrow-right ms-1"></i></a>
            <button type="button" class="btn btn-hero-ghost btn-lg" data-bs-toggle="modal" data-bs-target="#promoModal"><i class="bi bi-play-circle me-1"></i> Watch Promo</button>
        </div>
        <div class="hero-features mt-5">
            <span><i class="bi bi-qr-code me-1"></i> Instant QR Tickets</span>
            <span><i class="bi bi-lightning-charge me-1"></i> Book in Minutes</span>
            <span><i class="bi bi-patch-check me-1"></i> Approved Organizers</span>
        </div>
    </div>
</header>

<!-- ====================== CATEGORIES STRIP ====================== -->
<section class="cat-strip">
    <div class="container cat-strip-inner">
        <div class="cat-strip-label">Browse<br class="d-none d-md-block"> Categories:</div>
        <div class="cat-strip-items">
            <?php foreach ($categories as $i => $cat): ?>
                <a class="cat-strip-item" href="<?= e(APP_URL) ?>/events/index.php?category_id=<?= e($cat['id']) ?>">
                    <span class="cat-strip-icon ci-<?= e(($i % 7) + 1) ?>"><i class="bi <?= e(category_icon($cat['name'])) ?>"></i></span>
                    <span><?= e($cat['name']) ?></span>
                </a>
            <?php endforeach; ?>
        </div>
        <a class="cat-strip-view" href="<?= e(APP_URL) ?>/events/categories.php">View All Categories <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
</section>

<!-- ======================= FEATURED EVENTS ======================= -->
<section class="container py-5">
    <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-4">
        <div>
            <h2 class="section-title"><i class="bi bi-star-fill section-head-star me-2"></i>Featured Events</h2>
            <p class="text-muted-venuvia mb-0">The events everyone is booking right now.</p>
        </div>
        <a class="view-all-link" href="<?= e(APP_URL) ?>/events/index.php">View All Events <i class="bi bi-arrow-right ms-1"></i></a>
    </div>
    <div class="row g-4">
        <?php if (!$featured): ?>
            <p class="text-muted-venuvia">No events are live yet. Please check back soon.</p>
        <?php endif; ?>
        <?php foreach ($featured as $ev): ?>
            <?= event_card($ev, 'col-md-6 col-xl-3') ?>
        <?php endforeach; ?>
    </div>
</section>

<!-- ======================= UPCOMING EVENTS ======================= -->
<section class="container py-5">
    <div class="d-flex justify-content-between align-items-end flex-wrap gap-2 mb-4">
        <div>
            <h2 class="section-title">Upcoming Events</h2>
            <p class="text-muted-venuvia mb-0">Happening soon — grab your seats before they are gone.</p>
        </div>
        <a class="btn btn-venuvia-outline" href="<?= e(APP_URL) ?>/events/index.php">View All Events</a>
    </div>
    <div class="row g-4">
        <?php if (!$upcoming): ?>
            <p class="text-muted-venuvia">No upcoming events right now.</p>
        <?php endif; ?>
        <?php foreach ($upcoming as $ev): ?>
            <?= event_card($ev) ?>
        <?php endforeach; ?>
    </div>
</section>

<!-- ========================= WHY VENUVIA ========================= -->
<section class="container pb-5">
    <h2 class="section-title mb-4">Why Book with Venuvia?</h2>
    <div class="row g-3">
        <div class="col-md-6 col-lg-3">
            <div class="venuvia-card p-4 h-100 text-center">
                <span class="stat-icon mb-3"><i class="bi bi-shield-check"></i></span>
                <h5 class="fw-semibold">Verified Events</h5>
                <p class="text-muted-venuvia mb-0">Every event is reviewed and approved by our admin team before it goes live.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="venuvia-card p-4 h-100 text-center">
                <span class="stat-icon mb-3"><i class="bi bi-lightning-charge"></i></span>
                <h5 class="fw-semibold">Live Seat Availability</h5>
                <p class="text-muted-venuvia mb-0">Seat counts update in real time, so the seat you see is the seat you get.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="venuvia-card p-4 h-100 text-center">
                <span class="stat-icon mb-3"><i class="bi bi-qr-code"></i></span>
                <h5 class="fw-semibold">QR Entry Tickets</h5>
                <p class="text-muted-venuvia mb-0">Every booking produces secure QR tickets for quick, paperless entry.</p>
            </div>
        </div>
        <div class="col-md-6 col-lg-3">
            <div class="venuvia-card p-4 h-100 text-center">
                <span class="stat-icon mb-3"><i class="bi bi-tags"></i></span>
                <h5 class="fw-semibold">Transparent Pricing</h5>
                <p class="text-muted-venuvia mb-0">The price on the event card is the price you pay. No surprises at checkout.</p>
            </div>
        </div>
    </div>
</section>

<!-- ============================ CTA ============================ -->
<section class="container pb-5">
    <div class="cta-band p-5 text-center">
        <h2 class="fw-bold text-white">Your next experience is one search away</h2>
        <p class="text-white-50 mb-4">Join Venuvia and never miss a concert, conference or match again.</p>
        <?php if (is_logged_in()): ?>
            <a class="btn btn-light btn-lg" href="<?= e(APP_URL) ?>/events/index.php">Explore Events</a>
        <?php else: ?>
            <a class="btn btn-light btn-lg" href="<?= e(APP_URL) ?>/auth/register.php">Create Free Account</a>
            <a class="btn btn-outline-light btn-lg ms-2" href="<?= e(APP_URL) ?>/events/index.php">Explore Events</a>
        <?php endif; ?>
    </div>
</section>

<!-- ======================= PROMO VIDEO MODAL ======================= -->
<div class="modal fade promo-modal" id="promoModal" tabindex="-1" aria-label="Venuvia promo video" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-semibold"><i class="bi bi-play-circle me-2"></i>Venuvia Promo</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-0">
                <video id="promoVideo" class="w-100 d-block" controls preload="none" poster="<?= e(APP_URL) ?>/assets/images/hero-poster.jpg">
                    <source src="<?= e(APP_URL) ?>/assets/videos/venuvi_intro.mp4" type="video/mp4">
                </video>
            </div>
        </div>
    </div>
</div>
<script>
(function () {
    var modal = document.getElementById('promoModal');
    var video = document.getElementById('promoVideo');
    if (!modal || !video) { return; }
    modal.addEventListener('shown.bs.modal', function () { video.play(); });
    modal.addEventListener('hidden.bs.modal', function () { video.pause(); });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
