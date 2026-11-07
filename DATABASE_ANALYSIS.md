# Venuvia — Database Analysis (Phase 1)

Source of truth: the SQL dump supplied by the project owner
(`event_management__1.sql`, phpMyAdmin 5.2.3 export, MariaDB 10.11.16,
generated 06 Nov 2026). This file was inspected in full before any code
was written. It is identical in schema to the earlier export of the same
database, with one difference: **this export contains no data rows at
all** — every table is empty.

## Database

- **Name:** `event_management`
- **Engine / charset:** InnoDB, utf8mb4 / utf8mb4_unicode_ci
- **Note:** the dump contains no `CREATE DATABASE` / `USE` statement, so in
  phpMyAdmin you must first create (or select) a database named
  `event_management`, then import the file into it.
- `users` has `AUTO_INCREMENT=2` even though no user row is exported, which
  means one user existed when the dump was taken but its row is not in
  this file. There is **no admin, organizer or customer account** in the
  supplied data — test accounts are created by the application in Phase 2
  (registration + a documented first-admin procedure), not by editing the
  supplied tables.

## Tables (9)

### users
`id` PK AI · `name` varchar(100) · `email` varchar(150) **UNIQUE** ·
`password` varchar(255) (stores `password_hash()` output) ·
`phone` varchar(20) nullable · `role` enum('admin','organizer','customer')
default 'customer' · `status` enum('active','blocked') default 'active' ·
`created_at` timestamp

### categories
`id` PK AI · `name` varchar(100) **UNIQUE** · `description` text nullable ·
`status` enum('active','inactive') default 'active' · `created_at`

### events
`id` PK AI · `organizer_id` FK → users · `category_id` FK → categories ·
`title` varchar(200) · `description` text nullable ·
`event_date` date (**there is no `event_time` column**) ·
`start_time` time · `end_time` time nullable · `venue` varchar(255) ·
`total_seats` int · `price` decimal(10,2) default 0.00 ·
`poster` varchar(255) nullable (file path, not a blob) ·
`status` enum('pending','approved','rejected') default 'pending' ·
`created_at` · indexes on event_date and status

### bookings
`id` PK AI · `user_id` FK → users · `event_id` FK → events ·
`quantity` int · `total_price` decimal(10,2) ·
`booking_date` timestamp · `status` enum('pending','confirmed','cancelled')
default 'pending'

### payments
`id` PK AI · `booking_id` FK → bookings · `amount` decimal(10,2) ·
`method` enum('jazzcash','easypaisa','stripe','cash') ·
`transaction_id` varchar(100) **UNIQUE** nullable ·
`status` enum('pending','success','failed') default 'pending' ·
`payment_date` timestamp

### tickets
`id` PK AI · `booking_id` FK → bookings ·
`ticket_number` varchar(100) **UNIQUE** · `qr_code` varchar(255)
(stores the QR image **file name/path**, not the payload) ·
`status` enum('valid','used','cancelled') default 'valid' · `created_at`

### check_ins
`id` PK AI · `ticket_id` FK → tickets **UNIQUE** (one check-in per ticket —
this is what physically prevents a double entry) ·
`checked_by` FK → users · `check_in_time` timestamp

### event_images
`id` PK AI · `event_id` FK → events **ON DELETE CASCADE** ·
`image_path` varchar(255) · `uploaded_at`

### notifications
`id` PK AI · `user_id` FK → users **ON DELETE CASCADE** ·
`title` varchar(150) · `message` text · `is_read` tinyint(1) default 0 ·
`created_at`

All other foreign keys are RESTRICT (a user/category/event with related
records cannot be deleted — the application must deactivate instead).

## Relationships

- One organizer (users) → many events; one category → many events
- One customer (users) → many bookings; one event → many bookings
- One booking → many payments; one booking → many tickets (one per seat)
- One ticket → at most one check-in; check-in records which user scanned it
- One event → many gallery images; one user → many notifications

## How required features map onto this schema (no changes needed)

| Feature | How it is implemented with the existing columns |
|---|---|
| Available seats | **Calculated**, never stored: `total_seats − SUM(quantity of confirmed bookings)`. There is no `available_seats` column. |
| Booking reference | Derived from the booking id (`VNV-000012`). There is no reference column. |
| Event time | `start_time` / `end_time`. There is no `event_time` column — queries must not use one. |
| Secure QR token | QR payload = `ticket_number` + HMAC signature (secret in `config/app.php`). No token column is needed, and no personal data goes into the QR code. The generated QR **image path** is stored in `tickets.qr_code`. |
| Duplicate entry prevention | `tickets.status` check **plus** the UNIQUE key on `check_ins.ticket_id`. |
| Admin "Settings" page | No settings table exists, so settings are file-based (`config/app.php`) — no fake database settings will be built. |
| Payment methods in the UI | JazzCash, Easypaisa, Stripe (as specified). The enum also allows `cash` from earlier use of this database; the value is preserved, the Venuvia checkout does not offer it. |
| Remember Me | No token column exists. Phase 2 will use a long-lived session cookie only — a persistent remember-me token column is listed below as an *optional* migration, not applied. |

## Missing functionality / additions required

**None required.** Every module in the specification (auth, roles,
categories, events, approval workflow, booking with transactions, simulated
payments, per-seat QR tickets, check-in, notifications, reports) fits the
supplied 9 tables exactly.

Optional, **not applied**: a persistent remember-me token would need
`users.remember_token`. It is provided commented-out in
`database_updates.sql` so the supplied database stays untouched unless the
project owner decides otherwise.

## database_updates.sql

Ships with Phase 1 containing comments only. Running it changes nothing.
No table or column of the supplied database is created, renamed, dropped
or modified by Venuvia.
