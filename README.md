# Venuvia — Event Management & Ticket Booking System

**Tagline:** Discover Events. Book Experiences.

A complete event discovery and ticket booking web application built with
Core PHP 8+, PDO, MySQL and Bootstrap 5. **The system is complete: all
12 planned phases are built and tested** — public website with hero
video, authentication for three roles, admin / organizer / customer
panels, a race-safe booking engine, simulated payments, QR tickets
with PDF downloads, a gate scanner with entry verification, and
reports with charts and CSV exports. Full documentation:
`docs/PROJECT_DOCUMENTATION.md`.

- Database analysis: see `DATABASE_ANALYSIS.md`
- Local URL after installation: `http://localhost/venuvi/`

## Requirements

- XAMPP (Apache + MySQL/MariaDB + PHP 8 or newer)
- A web browser

## Installation (Phase 1)

1. Install XAMPP from https://www.apachefriends.org/ and open the XAMPP
   Control Panel.
2. Extract the ZIP so the project folder is `htdocs/venuvi`
   (for example `C:\xampp\htdocs\venuvi`).
3. Start **Apache** and **MySQL** in the XAMPP Control Panel.
4. **If an `event_management` database with the 9 project tables already
   exists on this machine (for example from the earlier project), skip
   the import completely.** The supplied dump's schema is identical to
   that database, and Venuvia works with it directly. Do NOT import over
   the existing tables — the dump uses plain `CREATE TABLE` with no
   `DROP TABLE`, so importing would fail with MySQL error 1050
   "Table already exists". (Chosen by the project owner, 2026-11-06.)
5. Only on a fresh machine with no existing database: open
   http://localhost/phpmyadmin/ and create a database named
   **`event_management`** (collation `utf8mb4_unicode_ci`), select it,
   open the **Import** tab, and import the SQL file supplied by the
   project owner (`event_management__1.sql`). The dump has no
   `CREATE DATABASE` statement, so the database must be
   created/selected first.
6. Check `config/database.php` — the defaults match a standard XAMPP
   install (host `127.0.0.1`, database `event_management`, user `root`,
   empty password). Change them only if your local MySQL is different.
7. Open **http://localhost/venuvi/** — the public homepage loads. For
   instant demo content (3 accounts, categories, events, bookings,
   tickets), import `docs/sample_data.sql` once in phpMyAdmin on the
   `event_management` database; it deletes nothing and repeat imports
   do not duplicate rows. Then log in with the test credentials below.

## What Phase 1 contains

- Full project structure (`admin/`, `organizer/`, `customer/`, `auth/`,
  `events/`, `booking/`, `payments/`, `tickets/`, `scanner/`, `includes/`,
  `config/`, `assets/`, `uploads/`, `vendor/`)
- PDO database configuration for the supplied `event_management` database
- Application settings (`config/app.php`), including the QR signing secret
- Shared helpers: output escaping, CSRF tokens, flash messages, price/date
  formatting, derived booking references (`VNV-000001`), available-seat
  calculation, and signed QR payload generation/verification
- Session and role guards (`includes/auth.php`) for later phases
- The Venuvia design system (`assets/css/venuvia.css`): Deep Navy
  `#101828`, Indigo `#4F46E5`, Coral `#F97360`, background `#F8FAFC`,
  Inter + Poppins typography
- Real public pages that exist in this phase: Home (setup check), About
  Us, Contact, Privacy Policy, Terms & Conditions
- `database_updates.sql` — no database changes are required; the file
  documents that, and nothing in the supplied database is modified

## What Phase 2 adds

- Registration (`auth/register.php`): full name, email, phone, password +
  confirm password, server-side validation, duplicate-email check,
  `password_hash()` storage. New accounts are always role `customer`.
  A successful registration logs the new customer in automatically.
- Login (`auth/login.php`): one form for all roles, generic
  "Invalid email or password" error, blocked accounts refused,
  session id regenerated on login, role-based redirect
  (admin → `admin/`, organizer → `organizer/`, customer → `customer/`).
- Remember Me extends the session cookie to 30 days (the supplied
  schema has no remember-token column, so no token table is used).
- Logout (`auth/logout.php`) fully clears the session.
- Every protected page re-checks the account status in the database,
  so an account blocked while logged in is signed out on its next
  protected page.
- The navbar shows the signed-in user's name with an
  ADMIN / ORGANIZER / CUSTOMER role badge, and each role home shows a
  "Logged in as ROLE" account card.
- Test report: `docs/TEST_REPORT_PHASE2.md` (20/20 checks passed in the
  developer's test environment).

## Test accounts (Phase 2)

The database ships with no usable accounts for this app, so create them
through the application itself:

- **Customer:** open `http://localhost/venuvi/auth/register.php` and
  register with any details (password at least 8 characters).
- **Admin (first admin):** register normally first, then in phpMyAdmin
  run this SQL on the `event_management` database and log in again:
  `UPDATE users SET role = 'admin' WHERE email = 'your@email.com';`
- **Organizer (for testing):** same procedure with
  `role = 'organizer'`. From Phase 3 onwards the admin panel manages
  organizer accounts directly.

## What Phase 3 adds (Admin Panel)

Sidebar: Dashboard, Manage Events, Event Categories, Manage Customers,
Manage Organizers, All Bookings, Payment Records, Settings, Logout.
(Reports arrive in Phase 11 and the QR Scanner in Phase 10 — the
sidebar only lists modules that already work.)

- **Dashboard:** 8 live statistics (users, organizers, events, pending,
  approved, bookings, revenue from successful payments, tickets),
  Chart.js charts (bookings last 7 days, events by status), recent
  bookings and events awaiting approval.
- **Manage Events:** search, status filter, pagination, event details
  page with sales summary, approve / reject (the organizer receives a
  notification in the `notifications` table), and delete — events with
  bookings are protected by the database foreign keys and are refused
  with an explanation instead of an error page.
- **Categories:** add, edit, activate/deactivate, delete (categories
  used by events cannot be deleted; deactivate them instead).
- **Customers:** search, booking counts, total spent, block / unblock.
- **Organizers:** search, event counts, revenue, block / unblock, and
  creating organizer accounts from the panel.
- **Bookings:** search (customer, event or reference like VNV-000001),
  status filter, and a details page with payments and tickets.
- **Payments:** all payment records with method/status filters and the
  successful-payments total. Payments are simulated.
- **Settings:** shows the real file-based configuration
  (`config/app.php`, `config/database.php`) and live database table
  counts. The supplied schema has no settings table, so this page
  honestly displays configuration instead of saving to a fake table.
- Test report: `docs/TEST_REPORT_PHASE3.md`.

## What Phase 4 adds (Organizer Panel)

Sidebar: Dashboard, Create Event, My Events, Bookings, Attendees,
Profile, Logout. (The QR Scanner joins this sidebar in Phase 10 — the
sidebar only lists modules that already work. The specification's
"My Events" and "Manage Events" are one page: the events list carries
the Edit and Delete actions.)

Everything in this panel only ever touches the signed-in organizer's
own events — every query is scoped by `events.organizer_id`, and this
isolation was verified with a second organizer account.

- **Dashboard:** own total / approved / pending events, tickets sold,
  revenue from confirmed bookings, recent bookings, and notifications
  (admin approvals land here, with mark-all-read).
- **Create Event:** title, description, category, date, start/end
  time, venue, seats, price and poster upload (JPG/PNG/WebP, max 2 MB,
  verified as a real image, saved to `uploads/events/`). New events are
  always `pending`; an organizer can never approve their own event.
- **My Events:** status tabs (All / Pending / Approved / Rejected),
  search, tickets sold per event, Edit and Delete. Events with
  bookings cannot be deleted.
- **Edit rules:** pending events fully editable; approved events
  cannot drop total seats below tickets already sold; saving a
  rejected event sends it back to `pending` for a fresh admin review.
- **Bookings:** own-events bookings with customer details, filters and
  each booking's latest payment status.
- **Attendees:** one row per ticket of confirmed bookings, searchable
  by attendee name or ticket number.
- **Profile:** update name / phone; change password with the current
  password verified first.
- Test report: `docs/TEST_REPORT_PHASE4.md`.

## What Phase 5 adds (Public Website & Discovery)

The public site is live. One rule everywhere: only **approved**
events whose date has not passed are ever visible to the public —
pending, rejected and past events cannot be listed or opened, even
by a direct details URL.

- **Homepage:** full-screen hero with the original Venuvia intro
  video (`assets/videos/venuvi_intro.mp4` — 10 seconds, 1280×720,
  muted autoplay loop, with a poster-image fallback), headline,
  working event search, and live statistics (upcoming events,
  categories, tickets sold, organizers — all from the database).
- **Featured Events:** best-selling events first.
  **Categories strip:** every active category with its live upcoming
  count. **Upcoming Events:** soonest first. Plus a "Why Venuvia"
  section and a join/explore call-to-action.
- **Explore Events** (`events/index.php`): search by title or venue,
  category filter, sorting (soonest, most popular, price low→high,
  price high→low) and pagination. Event cards show the poster,
  category, date/time, venue, price and live availability badges
  (Available / Only N seats left / Sold Out).
- **Categories page** (`events/categories.php`): all active
  categories with upcoming counts and starting prices.
- **Event Details** (`events/details.php`): poster, full description,
  organizer, date/time/venue, ticket price, seats sold / available
  with a booked-percentage bar, and related events from the same
  category.
- The public navbar and footer now carry Explore Events and
  Categories links.
- Test report: `docs/TEST_REPORT_PHASE5.md`.

## What Phase 6 adds (Customer Dashboard & Profile)

Sidebar: Dashboard, My Bookings, My Tickets, Profile, Explore Events,
Logout. Everything is scoped to the signed-in customer — another
customer's booking cannot be opened even by guessing its URL.

- **Dashboard:** total bookings, upcoming events (confirmed, future
  dates), total tickets, total spent, recent bookings, and
  notifications with mark-all-read.
- **My Bookings:** search by event title or booking reference
  (VNV-000001), status filter, pagination; each row shows the latest
  payment status.
- **Booking Details:** event information, organizer, booking summary
  (server-side totals), the full payment history and every issued
  ticket. Pending bookings clearly show that tickets are issued on
  confirmation.
- **My Tickets:** one row per issued ticket across all bookings,
  searchable by ticket number or event, filterable by
  valid / used / cancelled.
- **Profile:** update name / phone; change password with the current
  password verified first (same rules as the organizer profile).
- New bookings created by the Phase 7 booking engine will appear in
  all of these pages automatically — they read the same tables.
- Test report: `docs/TEST_REPORT_PHASE6.md`.

## What Phase 7 adds (Booking & Seats)

- **Book on the event page:** the Event Details ticket panel now has
  a quantity selector (1 to `BOOKING_MAX_TICKETS` = 10, and never
  more than the seats left) with a working **Book Now** button.
  Guests see **Login to Book** instead — and after logging in they
  are returned to the same event page (the login page only accepts
  that one internal return pattern, never an open redirect).
- **Review page** (`booking/checkout.php`): event summary, quantity
  adjustment, and the total — always calculated **on the server**
  from the price stored in the database. A forged `total_price` in
  the request is ignored (verified by test).
- **Atomic confirmation:** confirming runs in a database transaction
  that locks the event row (`SELECT ... FOR UPDATE`), re-counts held
  seats and only then inserts the booking. The booking is created as
  **pending** (awaiting payment — payments are Phase 8).
- **Seat-hold rule:** a seat is held by every active booking —
  confirmed (sold) or pending (reserved awaiting payment). That is
  why completing a payment later can never overbook an event, and it
  is the rule used by every "seats available" number in the app.
- **Confirmation page** (`booking/confirmation.php`): booking
  reference, full summary and the Pending status, visible only to
  the booking's owner.
- Verified by a real concurrency test: 4 customers confirming at the
  same moment for a 2-seat event produced exactly 2 bookings.
- Test report: `docs/TEST_REPORT_PHASE7.md`.

## Optional demo data

`docs/sample_data.sql` adds demo content for testing: 3 accounts
(admin@venuvia.test / admin123, organizer@venuvia.test /
organizer123, customer@venuvia.test / customer123), 7 categories,
12 events (approved / pending / rejected), 3 bookings, 3 payments and
5 tickets. Import it once in phpMyAdmin on the `event_management`
database if you want instant test content; it deletes nothing and
repeat imports do not duplicate the demo rows. Local demo use only.

## What Phases 8–12 add (the system is complete)

- **Phase 8 — Simulated payments** (`payments/`): JazzCash / Easypaisa
  / Stripe demo checkout with an honest success/failure simulation
  control. Attempts are always recorded; only a success confirms the
  booking and issues one ticket per seat; a failure leaves the
  booking pending with seats still reserved; free bookings confirm
  with no payment row. Paying twice is impossible.
- **Phase 9 — QR tickets & PDF:** every ticket gets a QR PNG in
  `uploads/tickets/` encoding its number plus an HMAC signature (no
  personal data). Customers download a single ticket PDF or one PDF
  with all tickets of a booking (mPDF) from My Tickets / the booking
  view.
- **Phase 10 — QR scanner** (`scanner/`): gate page for organizers
  (own events only) and admins (any event), camera scanning via
  html5-qrcode plus manual entry, with colour-coded results —
  ENTRY ALLOWED / TICKET ALREADY USED / INVALID TICKET / NOT YOUR
  EVENT — and a check-in record per ticket.
- **Phase 11 — Reports** (`admin/reports.php`): date-range summaries
  (successful-payment revenue only), 30-day revenue chart, bookings
  by status chart, top events, revenue by category, CSV exports
  (bookings / payments / events) and a print view.
- **Phase 12 — Final testing:** a fresh-database regression of every
  module (≈190 automated checks across all suites, link crawls of
  all three dashboards, the concurrency race test) — see
  `docs/TEST_REPORT_FINAL.md` and `docs/PROJECT_DOCUMENTATION.md`.

## Test credentials

See **Test accounts (Phase 2)** above — accounts are created through
the application's own registration form, with the first admin and any
test organizer promoted through one documented SQL statement. No
credentials are hardcoded anywhere in the code.

## Notes

- Payments in Venuvia are **simulated** for academic purposes. No real
  financial transaction ever takes place.
- The homepage hero video was produced for Venuvia in Phase 5 and is
  included at `assets/videos/venuvi_intro.mp4` (10 s, 1280×720, no
  audio track — the hero plays it muted on loop).
- Required libraries for later phases (PHP QR Code, mPDF, html5-qrcode)
  are introduced in the phases that use them — see `vendor/README.md`.
