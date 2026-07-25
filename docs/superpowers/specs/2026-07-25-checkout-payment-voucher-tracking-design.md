# DayatGames Checkout, Payment, Voucher, and Order Tracking Design

**Date:** 2026-07-25  
**Status:** Approved design, pending implementation plan  
**Scope:** Website only. Word, PDF, and report artifacts are excluded.

## 1. Goal

Repair the current checkout and payment workflow, prevent duplicate orders and payments, add method-specific payment instructions, add simple admin-managed voucher codes, provide customer order tracking, integrate publisher/developer management into Game CRUD, improve payment verification with game artwork, and replace the footer with a conventional modern marketplace footer.

## 2. Confirmed Product Decisions

- Checkout creates the order before payment is submitted.
- Cart items are removed immediately after a successful order transaction.
- Payment proof is uploaded from the order detail page, not from checkout.
- Every payment method is a clearly labeled academic simulation.
- Voucher management is simple: code, percentage, expiration date, and active state.
- Order tracking is authenticated and restricted to the order owner.
- Publisher and Developer disappear from the admin sidebar and are managed from the Game form.
- The design must match the existing modern DayatGames dark visual system and remain responsive.

## 3. Root Causes in the Current Application

### 3.1 Bank transfer and e-wallet failure

`CheckoutRequest` requires `payment_proof` for `bank_transfer` and `e_wallet` during checkout. A customer who expects to receive payment instructions before paying is rejected before the order can be created.

### 3.2 Duplicate orders and payments

Cart items remain until an admin verifies payment. Returning to Cart therefore shows the same items and permits the same checkout again. Checkout also has no unique submission token, so repeated submissions create a new order and one new payment each time.

### 3.3 Admin payment clutter

The admin page lists every payment record in one table without distinguishing “customer has not paid” from “proof is ready for verification.” Repeated checkout records make this queue appear much larger than the actual verification workload.

## 4. Recommended Architecture

The implementation will extend the existing Laravel models and controllers instead of rebuilding the transaction system.

The main bounded units will be:

- `CheckoutService`: owns cart locking, server-side pricing, idempotent order creation, snapshots, and cart clearing.
- `VoucherService`: normalizes voucher codes, validates active/expiration rules, and calculates percentage discounts.
- `PaymentInstructionService`: returns presentation-ready simulated destination information and steps for the selected method.
- Existing customer and admin controllers: remain thin coordinators around these services.

This keeps pricing, voucher rules, and order creation out of Blade templates and makes each behavior directly testable.

## 5. Data Model

### 5.1 Vouchers

Create a `vouchers` table with:

- `id`
- `code`, unique and stored uppercase
- `discount_percent`, integer from 1 to 100
- `expires_at`, end-of-day expiration in the application timezone
- `is_active`, boolean
- timestamps

Voucher codes are reusable by customers while active and unexpired. There is no quota, minimum purchase, maximum discount, or per-user redemption limit in this simple version.

### 5.2 Order snapshots and idempotency

Add to `orders`:

- `checkout_token`, unique UUID
- `subtotal_amount`
- `voucher_id`, nullable foreign key with `nullOnDelete`
- `voucher_code`, nullable snapshot
- `voucher_discount_amount`, default zero
- `payment_due_at`, set to 24 hours after order creation

`total_amount` remains the final amount after voucher discount.

The voucher snapshot remains readable even when the voucher is later edited or removed.

### 5.3 Payment lifecycle

Keep the existing payment statuses:

- `pending`
- `verified`
- `failed`

The customer-facing stage is derived safely:

- `pending` + no proof: Menunggu pembayaran
- `pending` + proof exists: Menunggu verifikasi
- `verified`: Pembayaran diterima
- `failed`: Pembayaran ditolak

This avoids an unsafe enum migration while still supporting a clear timeline.

## 6. Idempotent Checkout Flow

### 6.1 Checkout page

The page displays:

- game list and cover artwork
- subtotal
- applied voucher and discount
- final total
- modern selectable payment method cards
- one primary “Buat pesanan” button

There is no payment proof field on this page.

### 6.2 Checkout token

Opening checkout creates a UUID stored in the session and rendered as a hidden field. The token is not regenerated during validation redirects.

During POST:

1. Look for an existing order belonging to the same user and `checkout_token`.
2. If found, redirect to that existing order.
3. Otherwise lock the user’s cart and load its games.
4. Revalidate availability, ownership, active pending purchases, prices, and the voucher.
5. Create the order, item snapshots, and one payment record in one database transaction.
6. Delete the ordered cart items in the same transaction.
7. Redirect to the order detail page.

The database unique constraint on `checkout_token` is the final defense against simultaneous duplicate requests.

### 6.3 Active pending game protection

A customer cannot add a game to Cart when the same game already belongs to one of their `pending` orders. The UI directs them to Lacak Pesanan instead. Cancelled orders do not block a new purchase.

The customer may cancel a pending order before submitting payment proof. Cancellation marks the order `cancelled` and its payment `failed`, after which the games may be added to Cart again. Proof submission is rejected after `payment_due_at`; the expired order and payment are cancelled during that attempted submission. The order detail clearly presents the expiry state and the option to purchase again. This keeps expiry behavior deterministic without requiring a background scheduler.

### 6.4 Existing duplicate records

Historical orders and payments are not automatically deleted or silently rewritten. The new admin filters separate payments awaiting customer action from payments requiring verification, so old unsubmitted records do not clutter the default work queue. Verified and failed history remains auditable.

## 7. Voucher Flow

### 7.1 Customer

Checkout includes a code input with “Gunakan” and “Hapus” actions.

Applying a voucher:

1. normalizes the code to uppercase
2. validates existence, active state, and expiration
3. stores the accepted code in the checkout session
4. recalculates the displayed totals

Order creation always validates and calculates the voucher again on the server. Client-displayed amounts are never trusted.

Percentage discount is calculated from the current server subtotal and cannot reduce the total below zero.

### 7.2 Admin

Add a Voucher sidebar item and CRUD screens with:

- search by code
- filter active/expired
- create and edit form
- active state toggle
- usage count derived from associated orders

Deleting a voucher must not damage order snapshots.

## 8. Payment Instructions and Proof Submission

### 8.1 Simulated configuration

Create a dedicated configuration file for simulated payment destinations:

- Virtual Account: generated VA number using the existing prefix
- Bank Transfer: a fixed demo bank, account number, and account name
- E-Wallet: a fixed demo provider, phone/account number, and account name

All instruction panels explicitly state that no real money should be sent.

### 8.2 Method-specific instructions

Virtual Account instructions show:

- generated VA number
- exact final amount
- the stored `payment_due_at` deadline
- ATM/mobile banking steps

Bank Transfer instructions show:

- demo bank and account
- exact final amount
- transfer steps
- instruction to save the transaction reference

E-Wallet instructions show:

- demo provider and account
- exact final amount
- wallet transfer steps
- instruction to save the transaction reference

### 8.3 Upload proof

Add an owner-protected POST endpoint on the order:

- allowed only when order is pending and payment is not verified
- rejected after `payment_due_at`, while atomically cancelling the expired order/payment
- accepts JPG, JPEG, PNG, WebP, or PDF up to 4 MB
- requires a transaction reference for bank transfer and e-wallet
- requires payment proof for all three simulated methods
- replaces an earlier unverified proof safely, deleting the superseded managed file
- sets `paid_at` when proof is first submitted

The order page then displays “Menunggu verifikasi admin.”

Add a separate owner-protected cancellation endpoint:

- allowed only while order is pending and no proof has been submitted
- atomically marks the order cancelled and payment failed
- is idempotent when the same cancelled order is submitted again
- never deletes historical order snapshots

### 8.4 Admin verification guards

Admin cannot verify a payment without a proof file. Verification remains transaction-locked and idempotent. Successful verification:

- marks payment verified
- marks order completed
- creates Library records without duplication

Rejection marks payment failed and order cancelled. Rejected/cancelled orders can be purchased again.

## 9. Order Tracking

Add **Lacak Pesanan** to the authenticated customer navigation and conventional footer links.

The route uses the existing customer order index and supports:

- exact or partial order-code search
- status filter
- newest-first pagination
- responsive order cards instead of a wide mobile table

Each card shows game artwork, order code, date, final total, and current stage.

The detail page shows a timeline:

1. Pesanan dibuat
2. Menunggu pembayaran
3. Bukti pembayaran dikirim
4. Menunggu verifikasi
5. Selesai or Ditolak

Authorization remains owner-only and returns 404 for another customer’s order.

## 10. Admin Information Architecture

### 10.1 Game form

Remove Publisher and Developer from the admin sidebar.

The Game create/edit form keeps existing selections and adds an inline option to create a new publisher or developer by name. On submit:

- existing ID may be selected, or
- a new name may be supplied
- new names are trimmed, assigned a unique normalized slug, and reused when an exact normalized match already exists

The underlying Publisher and Developer models/tables remain because storefront relations depend on them. Standalone routes may remain for compatibility, but they are not presented as admin tabs.

### 10.2 Payment queue

The default Payment page shows payments requiring verification: `pending` with proof.

Additional filters expose:

- Menunggu customer: pending without proof
- Verified
- Failed
- All

Rows show:

- first game cover and additional-item count
- order code
- customer
- method
- amount
- submitted time
- status

The detail view shows a visual card for every ordered game, including cover, title, and snapshot price.

### 10.3 Dashboard

The payment workload metric counts only pending payments with submitted proof. Awaiting-customer records are reported separately and do not inflate the verification queue.

## 11. Footer

Replace the current disclaimer-only footer with a responsive marketplace footer:

- brand logo, DayatGames name, and short description
- Shop: Beranda, Jelajahi Game, Wishlist
- Customer: Cart, Lacak Pesanan, Library, Profile
- Payment: Virtual Account, Transfer Bank, E-Wallet
- bottom bar with copyright and the academic simulation disclaimer

Desktop uses four columns. Tablet and mobile collapse cleanly without horizontal overflow.

## 12. Error Handling

- Invalid, inactive, or expired vouchers return a clear field-level message.
- Reusing a checkout token redirects to the already-created order.
- Empty/stale carts fail before any order is inserted.
- Database transactions roll back order, items, payment, and cart deletion together.
- Failed proof uploads do not alter the existing payment.
- Cancelling an order that already has proof or is already verified is rejected.
- Unauthorized order/payment actions return 404 or 403 according to existing conventions.
- Admin verification without proof is rejected with a visible message.
- Payment instructions always have a fallback configuration value and never expose secrets.

## 13. UI and Motion

The new interfaces reuse the existing DayatGames colors, rounded panels, aurora background, typography, and smooth page motion.

Specific modern UI elements:

- selectable payment method cards with clear active state
- compact voucher input with live summary feedback
- payment instruction card with copyable identifiers
- order timeline with distinct completed/current/future states
- artwork-led admin payment rows and item cards
- consistent empty states and status badges

Motion respects `prefers-reduced-motion`. Form controls remain keyboard accessible and touch targets remain suitable for mobile.

## 14. Test Strategy

### Automated feature tests

- all three payment methods create an order without requiring proof at checkout
- successful checkout clears ordered cart items
- repeated checkout token creates exactly one order and one payment
- active pending games cannot be added to Cart again
- server prices and voucher discounts are authoritative
- inactive, expired, and unknown vouchers are rejected
- order snapshots preserve voucher code and discount
- proof upload validates ownership, method fields, type, and size
- expired proof submission cancels the pending order/payment
- customer cancellation is allowed only before proof submission
- admin cannot verify without proof
- verification remains idempotent and creates Library entries once
- tracking search/filter is owner-scoped
- Voucher CRUD is admin-only
- Game form can use existing or inline-created publisher/developer
- admin payment pages render game covers
- footer and navigation expose required destinations

### Browser QA

Verify desktop and mobile:

- voucher application/removal
- payment method switching
- order creation and back-navigation without duplicate Cart items
- method-specific instructions
- proof upload states
- tracking timeline
- admin payment filters and artwork
- Game form inline publisher/developer creation
- Voucher CRUD
- footer layout
- no console errors or horizontal overflow

## 15. Delivery Constraints

- Do not edit or regenerate Word/PDF/report files.
- Preserve the current feature branch and existing game preview work.
- Use migrations that work in both MySQL and the SQLite test database.
- Do not integrate a real bank, payment gateway, QR service, or external secret.
