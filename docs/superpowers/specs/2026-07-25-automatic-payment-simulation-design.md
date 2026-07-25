# DayatGames Automatic Payment Simulation Design

## Goal

Replace manual payment-proof submission with a safe automatic payment simulation. The checkout flow remains order-first: customers choose a payment method, create an order, review method-specific instructions, then press **Bayar sekarang** to simulate payment detection.

## Customer Flow

1. Checkout validates the cart, voucher, server-side prices, and payment method.
2. The application creates exactly one order and one pending payment for the checkout token, then clears the cart.
3. The order detail page shows the selected method, payment destination, exact amount, and simulation notice.
4. The customer presses **Bayar sekarang**.
5. The page presents a short smooth processing state such as **Mendeteksi pembayaran...**.
6. The server atomically marks the payment as verified, marks the order as completed, and creates one Library record per ordered game.
7. The customer returns to the completed order detail with a success message and a direct link to Library.

No payment-proof file or manual transaction-reference input is shown.

## Safety and Idempotency

The automatic-payment action is restricted to the order owner. It locks the order and payment rows inside a database transaction. Repeated submissions return the already-completed order without creating duplicate payment or Library rows.

The action rejects cancelled orders and expired pending orders. Library insertion uses the existing unique ownership contract so retries cannot duplicate games. The original checkout-token protection remains unchanged.

## Payment Data

Existing payment records remain compatible. New simulated payments use the current payment row and set:

- `status` to `verified`
- `paid_at` and `verified_at` to the current time
- `payment_reference` to a generated `SIM-...` reference
- `verified_by` to `null`, because detection is automatic rather than performed by an admin

Legacy proof columns remain in the database for backward compatibility, but the customer and admin interfaces no longer depend on them.

## Admin Experience

The Payment page becomes payment history rather than a proof-verification queue. It provides status filters and continues to show game covers, order code, customer, method, amount, and detected time.

Automatic payments are labeled **Terdeteksi otomatis**. Existing legacy verified or failed payments remain readable. Manual verify/reject controls and proof links are removed from the primary interface because new payments do not require admin intervention.

Dashboard wording changes from proof-oriented counts to payment-status counts.

## Tracking and Library

The tracking timeline uses these stages:

1. Pesanan dibuat
2. Menunggu pembayaran
3. Pembayaran terdeteksi
4. Selesai

Completed payments immediately expose the purchased games in Library. Customer-facing copy no longer says that an admin must verify a proof.

## Error Handling

- Empty or invalid checkout remains rejected before order creation.
- An unauthorized customer receives a not-found response for another customer's order.
- A cancelled or expired order cannot be paid.
- A database failure rolls back payment, order, and Library changes together.
- A repeated request is treated as success and redirects to the completed order.

## Testing

Feature tests cover:

- all three payment methods reaching automatic verification;
- immediate Library ownership after payment;
- repeated payment submissions remaining idempotent;
- foreign, cancelled, and expired orders being rejected;
- absence of proof-upload and manual-reference fields;
- payment history and dashboard labels;
- existing checkout-token and voucher behavior;
- the full application test suite and production asset build.

## Scope

This is an academic simulation only. It does not contact a bank, e-wallet, payment gateway, webhook, or external API, and it never transfers real money.
