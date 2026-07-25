# Automatic Payment Simulation Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Replace manual payment-proof submission with an idempotent **Bayar sekarang** simulation that completes the order and adds purchased games to Library automatically.

**Architecture:** Add a focused `AutomaticPaymentService` that owns the locked database transaction for payment detection, order completion, and Library insertion. The customer order endpoint delegates to this service, while customer and admin views become simulation/status interfaces rather than upload and proof-verification interfaces.

**Tech Stack:** Laravel 12, PHP 8.2+, Blade, Eloquent/MySQL, Vite, vanilla JavaScript, Tailwind CSS utilities, PHPUnit feature tests.

## Global Constraints

- This is an academic simulation only; never call a bank, e-wallet, gateway, webhook, or external API.
- Keep checkout order-first and preserve checkout-token idempotency.
- Do not collect a payment-proof file or manual transaction reference.
- Repeated payment requests must not duplicate payments, orders, or Library rows.
- Keep legacy payment columns and records readable for backward compatibility.
- Do not modify Word, PDF, or report artifacts.

---

## File Structure

- Create `app/Services/AutomaticPaymentService.php`: atomic, owner-independent domain operation for detecting one pending simulated payment.
- Create `tests/Feature/AutomaticPaymentTest.php`: customer authorization, completion, idempotency, cancellation, and expiry coverage.
- Modify `app/Http/Controllers/Customer/OrderController.php`: replace proof upload with the automatic-pay action.
- Modify `routes/web.php`: keep the existing customer payment URL but point it at `pay`; remove admin verify/reject routes.
- Delete `app/Http/Requests/Customer/PaymentProofRequest.php`: no upload validation remains.
- Modify `app/Models/Order.php`: proof-independent tracking stages and four-step timeline.
- Modify `resources/views/customer/orders/show.blade.php`: replace upload form with the modern **Bayar sekarang** simulation card.
- Modify `resources/views/customer/library/index.blade.php`: remove admin-proof wording.
- Modify `resources/js/app.js`: give automatic payment submission a smooth, accessible processing state.
- Modify `app/Http/Controllers/Admin/PaymentController.php`: convert the queue to read-only payment history.
- Modify `app/Http/Controllers/Admin/DashboardController.php`: expose pending, detected, and failed payment counts.
- Modify `resources/views/admin/payments/index.blade.php`: status filters, detected timestamp, automatic labels, and cover images.
- Modify `resources/views/admin/payments/show.blade.php`: read-only payment details without proof links or manual actions.
- Modify `resources/views/admin/dashboard.blade.php`: status-oriented metric labels.
- Modify `tests/Feature/PaymentSubmissionTest.php`: retain cancellation and instructions coverage; remove obsolete upload tests.
- Modify `tests/Feature/AdminPaymentQueueTest.php`: assert history filters and read-only UI.
- Modify `tests/Feature/OrderTrackingTest.php`: assert automatic-detection timeline wording.
- Modify `tests/Feature/CustomerMarketplaceTest.php`: assert no proof/reference fields and presence of automatic-pay hooks.

---

### Task 1: Automatic Payment Domain Service

**Files:**
- Create: `app/Services/AutomaticPaymentService.php`
- Create: `tests/Feature/AutomaticPaymentTest.php`

**Interfaces:**
- Consumes: `App\Models\Order` with loaded/lockable `payment` and `items` relationships.
- Produces: `AutomaticPaymentService::detect(Order $order): Order`.
- Guarantees: verified payment, completed order, one Library row per valid game item, and idempotent retries.

- [ ] **Step 1: Write the failing happy-path and idempotency tests**

Create feature tests that post to `route('orders.payment.submit', $order)` and assert:

```php
$this->assertDatabaseHas('payments', [
    'order_id' => $order->id,
    'status' => 'verified',
]);
$this->assertDatabaseHas('orders', [
    'id' => $order->id,
    'status' => 'completed',
]);
$this->assertDatabaseHas('libraries', [
    'order_id' => $order->id,
    'user_id' => $customer->id,
    'game_id' => $game->id,
]);

$this->actingAs($customer)->post(route('orders.payment.submit', $order));
$this->assertDatabaseCount('payments', 1);
$this->assertDatabaseCount('libraries', 1);
```

Also assert `payment_reference` starts with `SIM-`, `paid_at` and `verified_at` are non-null, and `verified_by` is null.

- [ ] **Step 2: Run the focused tests and verify RED**

Run:

```bash
docker compose exec -T app php artisan test --filter=AutomaticPaymentTest
```

Expected: FAIL because automatic detection does not yet verify or create Library ownership.

- [ ] **Step 3: Implement the locked transaction**

Create:

```php
final class AutomaticPaymentService
{
    public function detect(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            $locked = Order::query()
                ->with(['payment', 'items'])
                ->lockForUpdate()
                ->findOrFail($order->id);

            $payment = Payment::query()
                ->where('order_id', $locked->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($locked->status === 'completed' && $payment->status === 'verified') {
                return $locked;
            }

            if ($locked->status === 'cancelled' || $payment->status === 'failed') {
                throw ValidationException::withMessages([
                    'payment' => 'Pesanan yang dibatalkan tidak dapat dibayar.',
                ]);
            }

            if ($locked->payment_due_at?->isPast()) {
                $locked->update(['status' => 'cancelled']);
                $payment->update(['status' => 'failed']);

                throw ValidationException::withMessages([
                    'payment' => 'Batas pembayaran sudah lewat. Silakan buat pesanan baru.',
                ]);
            }

            $detectedAt = now();
            $payment->update([
                'status' => 'verified',
                'payment_reference' => $payment->payment_reference
                    ?: 'SIM-'.$detectedAt->format('YmdHis').'-'.$payment->id,
                'paid_at' => $payment->paid_at ?? $detectedAt,
                'verified_at' => $payment->verified_at ?? $detectedAt,
                'verified_by' => null,
            ]);
            $locked->update(['status' => 'completed']);

            foreach ($locked->items as $item) {
                if ($item->game_id) {
                    $locked->libraries()->updateOrCreate(
                        ['user_id' => $locked->user_id, 'game_id' => $item->game_id],
                        ['purchased_at' => $detectedAt],
                    );
                }
            }

            return $locked->fresh(['payment', 'items']);
        });
    }
}
```

Handle expiry without losing the cancellation update by detecting expiry inside the transaction, committing the failed/cancelled state, and throwing the validation error after the transaction returns.

- [ ] **Step 4: Run the focused tests and verify GREEN**

Run:

```bash
docker compose exec -T app php artisan test --filter=AutomaticPaymentTest
```

Expected: PASS.

- [ ] **Step 5: Commit the domain service**

```bash
git add app/Services/AutomaticPaymentService.php tests/Feature/AutomaticPaymentTest.php
git commit -m "feat: add automatic payment simulation"
```

---

### Task 2: Customer Payment Action and Interface

**Files:**
- Modify: `app/Http/Controllers/Customer/OrderController.php`
- Modify: `routes/web.php`
- Delete: `app/Http/Requests/Customer/PaymentProofRequest.php`
- Modify: `app/Models/Order.php`
- Modify: `resources/views/customer/orders/show.blade.php`
- Modify: `resources/views/customer/library/index.blade.php`
- Modify: `resources/js/app.js`
- Modify: `tests/Feature/PaymentSubmissionTest.php`
- Modify: `tests/Feature/OrderTrackingTest.php`
- Modify: `tests/Feature/CustomerMarketplaceTest.php`

**Interfaces:**
- Consumes: `AutomaticPaymentService::detect(Order $order): Order`.
- Produces: `OrderController::pay(Request $request, Order $order): RedirectResponse`.
- Route contract: `POST /orders/{order}/payment`, named `orders.payment.submit`.

- [ ] **Step 1: Replace proof-oriented tests with automatic-payment UI tests**

Assert the pending order page contains:

```php
->assertSee('Bayar sekarang')
->assertSee('data-auto-payment', false)
->assertDontSee('name="payment_proof"', false)
->assertDontSee('name="payment_reference"', false)
->assertDontSee('menunggu verifikasi admin');
```

Assert completed tracking contains **Pembayaran terdeteksi** and **Selesai**, while pending tracking contains **Menunggu pembayaran**.

- [ ] **Step 2: Run customer and tracking tests and verify RED**

Run:

```bash
docker compose exec -T app php artisan test --filter="PaymentSubmissionTest|OrderTrackingTest|CustomerMarketplaceTest"
```

Expected: FAIL on old proof-upload fields and old tracking labels.

- [ ] **Step 3: Wire the customer endpoint to the service**

Replace `submitPayment()` and storage cleanup with:

```php
public function pay(Request $request, Order $order): RedirectResponse
{
    abort_unless($order->user_id === $request->user()->id, 404);

    $alreadyCompleted = $order->status === 'completed'
        && $order->payment?->status === 'verified';

    $this->automaticPayments->detect($order);

    return redirect()->route('orders.show', $order)->with(
        'success',
        $alreadyCompleted
            ? 'Pembayaran sudah terdeteksi sebelumnya. Library tetap aman tanpa duplikasi.'
            : 'Pembayaran simulasi terdeteksi. Game sudah masuk ke Library.',
    );
}
```

Inject both `PaymentInstructionService` and `AutomaticPaymentService`. Point the existing named route at `pay` so links and tests remain stable. Delete the unused proof request.

- [ ] **Step 4: Simplify tracking and cancellation rules**

Make `Order::trackingStage()` derive only from order/payment status. Return four steps:

```php
[
    ['label' => 'Pesanan dibuat', 'state' => 'completed'],
    ['label' => 'Menunggu pembayaran', 'state' => $waitingState],
    ['label' => 'Pembayaran terdeteksi', 'state' => $detectedState],
    ['label' => $cancelled ? 'Dibatalkan' : 'Selesai', 'state' => $finalState],
]
```

Allow cancellation only while order and payment are pending. Remove proof-based cancellation conditions and messages.

- [ ] **Step 5: Build the modern automatic-pay card**

On a valid pending order, render:

```blade
<form method="POST"
      action="{{ route('orders.payment.submit', $order) }}"
      data-auto-payment>
    @csrf
    <button data-auto-payment-button>
        <span data-auto-payment-label>Bayar sekarang</span>
        <span class="hidden" data-auto-payment-progress>Mendeteksi pembayaran...</span>
    </button>
</form>
```

Explain that this is a simulation, no money is transferred, and detection immediately adds the game to Library. For completed orders, show **Pembayaran terdeteksi otomatis** and a link to `library.index`. For expired pending orders, show that a new order is required.

- [ ] **Step 6: Add a smooth accessible processing state**

In `resources/js/app.js`, bind each `[data-auto-payment]` form once:

```js
form.addEventListener('submit', () => {
    const button = form.querySelector('[data-auto-payment-button]');
    button.disabled = true;
    button.setAttribute('aria-busy', 'true');
    form.querySelector('[data-auto-payment-label]')?.classList.add('hidden');
    form.querySelector('[data-auto-payment-progress]')?.classList.remove('hidden');
});
```

Use existing utility classes for spinner/opacity transitions and preserve normal submission when JavaScript is unavailable.

- [ ] **Step 7: Run customer and tracking tests and verify GREEN**

Run:

```bash
docker compose exec -T app php artisan test --filter="AutomaticPaymentTest|PaymentSubmissionTest|OrderTrackingTest|CustomerMarketplaceTest"
```

Expected: PASS.

- [ ] **Step 8: Commit the customer flow**

```bash
git add app/Http/Controllers/Customer/OrderController.php app/Models/Order.php routes/web.php resources/views/customer/orders/show.blade.php resources/views/customer/library/index.blade.php resources/js/app.js tests/Feature/PaymentSubmissionTest.php tests/Feature/OrderTrackingTest.php tests/Feature/CustomerMarketplaceTest.php
git rm app/Http/Requests/Customer/PaymentProofRequest.php
git commit -m "feat: replace payment proof with automatic detection"
```

---

### Task 3: Read-Only Admin Payment History

**Files:**
- Modify: `app/Http/Controllers/Admin/PaymentController.php`
- Modify: `app/Http/Controllers/Admin/DashboardController.php`
- Modify: `resources/views/admin/payments/index.blade.php`
- Modify: `resources/views/admin/payments/show.blade.php`
- Modify: `resources/views/admin/dashboard.blade.php`
- Modify: `routes/web.php`
- Modify: `tests/Feature/AdminPaymentQueueTest.php`

**Interfaces:**
- Produces filters `pending`, `verified`, `failed`, and `all`.
- Default filter: `all`.
- Payment details are read-only; legacy verifier information remains visible when present.

- [ ] **Step 1: Rewrite admin tests for history behavior**

Assert:

```php
$this->actingAs($admin)->get(route('admin.payments.index'))
    ->assertSee('Riwayat payment')
    ->assertSee($verified->order->order_code)
    ->assertSee($pending->order->order_code)
    ->assertSee('Terdeteksi otomatis')
    ->assertDontSee('Siap diverifikasi')
    ->assertDontSee('Menunggu customer');
```

On payment detail, assert every game cover is present, proof links are absent, and verify/reject forms are absent.

- [ ] **Step 2: Run admin tests and verify RED**

Run:

```bash
docker compose exec -T app php artisan test --filter=AdminPaymentQueueTest
```

Expected: FAIL because the existing UI is proof-queue oriented.

- [ ] **Step 3: Convert the controller to read-only history**

Use allowed filters:

```php
$status = in_array($request->string('status')->toString(), [
    'pending', 'verified', 'failed', 'all',
], true) ? $request->string('status')->toString() : 'all';
```

Filter directly by payment status, retain search, load covers, and remove the `verify()` and `reject()` actions. Remove their routes.

- [ ] **Step 4: Update admin views and dashboard metrics**

Use filter labels **Semua**, **Menunggu pembayaran**, **Terdeteksi**, and **Gagal / dibatalkan**. Replace **Dikirim** with **Terdeteksi pada**. Label verified payments with null `verified_by` as **Terdeteksi otomatis** and legacy admin verification as **Diverifikasi admin**.

Dashboard stats become:

```php
'pending_payments' => Payment::where('status', 'pending')->count(),
'detected_payments' => Payment::where('status', 'verified')->count(),
'failed_payments' => Payment::where('status', 'failed')->count(),
```

Keep verified revenue calculation unchanged.

- [ ] **Step 5: Run admin tests and verify GREEN**

Run:

```bash
docker compose exec -T app php artisan test --filter=AdminPaymentQueueTest
```

Expected: PASS.

- [ ] **Step 6: Commit admin history changes**

```bash
git add app/Http/Controllers/Admin/PaymentController.php app/Http/Controllers/Admin/DashboardController.php resources/views/admin/payments/index.blade.php resources/views/admin/payments/show.blade.php resources/views/admin/dashboard.blade.php routes/web.php tests/Feature/AdminPaymentQueueTest.php
git commit -m "feat: show automatic payment history in admin"
```

---

### Task 4: Regression Verification and Browser QA

**Files:**
- Verify only; modify the nearest responsible file if a regression is discovered.

**Interfaces:**
- Final behavior must satisfy the design spec and all existing marketplace contracts.

- [ ] **Step 1: Run formatting checks on changed PHP files**

Run:

```bash
docker compose exec -T app ./vendor/bin/pint --test app/Services/AutomaticPaymentService.php app/Http/Controllers/Customer/OrderController.php app/Http/Controllers/Admin/PaymentController.php app/Http/Controllers/Admin/DashboardController.php app/Models/Order.php routes/web.php tests/Feature/AutomaticPaymentTest.php tests/Feature/PaymentSubmissionTest.php tests/Feature/AdminPaymentQueueTest.php tests/Feature/OrderTrackingTest.php tests/Feature/CustomerMarketplaceTest.php
```

Expected: PASS.

- [ ] **Step 2: Run the full Laravel test suite**

Run:

```bash
docker compose exec -T app php artisan test
```

Expected: all tests PASS.

- [ ] **Step 3: Build production assets**

Run:

```bash
npm.cmd run build
```

Expected: Vite build completes without errors.

- [ ] **Step 4: Perform desktop and mobile browser QA**

Verify at 1440×900 and 390×844:

- checkout creates one order and clears Cart;
- order detail has no proof/reference input;
- **Bayar sekarang** shows a processing state;
- completion creates Library ownership immediately;
- retry does not duplicate Library;
- tracking shows **Pembayaran terdeteksi**;
- admin Payment is read-only history with covers;
- no horizontal overflow, broken images, or console errors.

- [ ] **Step 5: Check repository cleanliness and commit fixes**

Run:

```bash
git diff --check
git status --short
```

If QA required a fix, stage the automatic-payment files and commit:

```bash
git add app/Services/AutomaticPaymentService.php app/Http/Controllers/Customer/OrderController.php app/Http/Controllers/Admin/PaymentController.php app/Http/Controllers/Admin/DashboardController.php app/Models/Order.php routes/web.php resources/views/customer/orders/show.blade.php resources/views/customer/library/index.blade.php resources/views/admin/payments/index.blade.php resources/views/admin/payments/show.blade.php resources/views/admin/dashboard.blade.php resources/js/app.js tests/Feature/AutomaticPaymentTest.php tests/Feature/PaymentSubmissionTest.php tests/Feature/AdminPaymentQueueTest.php tests/Feature/OrderTrackingTest.php tests/Feature/CustomerMarketplaceTest.php
git commit -m "fix: polish automatic payment simulation"
```
