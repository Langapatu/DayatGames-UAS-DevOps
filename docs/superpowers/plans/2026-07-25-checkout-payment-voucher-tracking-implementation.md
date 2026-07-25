# DayatGames Checkout, Payment, Voucher, and Order Tracking Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Build an idempotent order-first checkout with admin-managed percentage vouchers, method-specific simulated payment instructions, delayed proof upload, modern order tracking, artwork-led admin verification, integrated game metadata management, and a conventional responsive footer.

**Architecture:** Extend the existing Laravel commerce models with immutable order pricing snapshots and a unique checkout token. Put voucher calculation, atomic checkout, and payment instruction mapping in focused services; keep controllers responsible for authorization, request validation, session state, and redirects. Preserve the existing order/payment enums while deriving customer-facing stages from order status, payment status, and proof presence.

**Tech Stack:** Laravel 12, PHP 8.2+, Blade, Tailwind CSS 4 through Vite, vanilla JavaScript, MySQL production database, SQLite test database, PHPUnit feature tests, Docker Compose.

## Global Constraints

- Website only; do not edit or regenerate Word, PDF, or report artifacts.
- Preserve the current `feature/dayatgames-uas` branch and existing game preview work.
- Every payment method is an academic simulation; do not integrate a real bank, gateway, QR service, external API, or secret.
- Checkout creates the order first, removes ordered cart items in the same transaction, then collects payment proof from order detail.
- Voucher scope is exactly code, percentage from 1 to 100, expiration date, active state, and derived usage count.
- Order tracking is authenticated, owner-only, newest first, searchable by order code, and filterable by status.
- Publisher and Developer remain as relational tables but disappear as standalone admin navigation tabs.
- Migrations must run on both MySQL and SQLite.
- New motion must respect `prefers-reduced-motion`, remain keyboard accessible, and avoid horizontal overflow.

---

## File Structure

### New focused files

- `database/migrations/2026_07_25_000400_create_vouchers_and_extend_orders_table.php` — voucher storage, immutable order pricing fields, checkout idempotency, and payment deadline.
- `app/Models/Voucher.php` — voucher casts, order relation, and valid query scope.
- `app/Services/VoucherService.php` — code normalization, validity lookup, and percentage calculation.
- `app/Services/CheckoutService.php` — locked authoritative checkout transaction and unique order code generation.
- `app/Services/PaymentInstructionService.php` — method label, simulated destination, and ordered steps for an order.
- `config/payments.php` — non-secret demo payment destinations and labels.
- `app/Http/Requests/Admin/VoucherRequest.php` — admin voucher validation and normalization.
- `app/Http/Requests/Customer/PaymentProofRequest.php` — proof and method-dependent reference validation.
- `app/Http/Controllers/Admin/VoucherController.php` — admin voucher CRUD.
- `resources/views/admin/vouchers/index.blade.php` — voucher search/filter/list.
- `resources/views/admin/vouchers/form.blade.php` — modern voucher create/edit form.
- `tests/Feature/VoucherWorkflowTest.php` — voucher model, calculation, admin authorization, and CRUD.
- `tests/Feature/CheckoutIdempotencyTest.php` — all payment methods, authoritative totals, cart clearing, token reuse, and pending-item protection.
- `tests/Feature/PaymentSubmissionTest.php` — proof lifecycle, expiry, cancellation, instructions, and verification guards.
- `tests/Feature/OrderTrackingTest.php` — owner-scoped search/filter/cards/timeline.
- `tests/Feature/AdminPaymentQueueTest.php` — proof-ready default queue, filters, covers, and dashboard metrics.

### Existing files with bounded changes

- `routes/web.php` — voucher, proof, cancellation, tracking, and admin voucher routes.
- `app/Models/Order.php`, `app/Models/Payment.php`, `app/Models/User.php` — fields, casts, relations, and small state helpers.
- `app/Http/Requests/Customer/CheckoutRequest.php` — only payment method and checkout token.
- `app/Http/Controllers/Customer/CheckoutController.php` — checkout session/token/voucher orchestration.
- `app/Http/Controllers/Customer/OrderController.php` — tracking filters, proof upload, cancellation, and instruction view data.
- `app/Http/Controllers/Customer/CartController.php` — active pending-order duplicate guard.
- `app/Http/Controllers/Admin/PaymentController.php` — queue scopes, proof guard, and eager-loaded artwork.
- `app/Http/Controllers/Admin/DashboardController.php` — actionable and awaiting-customer payment counts.
- `app/Http/Requests/Admin/GameRequest.php`, `app/Http/Controllers/Admin/GameController.php` — select existing or inline-create developer/publisher.
- `resources/views/customer/checkout/create.blade.php` — modern payment cards and voucher summary; no proof field.
- `resources/views/customer/orders/index.blade.php`, `show.blade.php` — tracking cards, timeline, instructions, proof/cancel actions.
- `resources/views/customer/cart/index.blade.php` — checkout wording only where needed.
- `resources/views/admin/payments/index.blade.php`, `show.blade.php`, `admin/dashboard.blade.php` — actionable filters, covers, and metrics.
- `resources/views/admin/games/form.blade.php` — inline developer/publisher fields.
- `resources/views/layouts/app.blade.php` — customer tracking link, Voucher admin link, removal of standalone metadata links, and modern footer.
- `resources/css/app.css`, `resources/js/app.js` — responsive transaction/admin/footer styling and progressive enhancement.
- `tests/Feature/TransactionWorkflowTest.php`, `AdminCatalogCrudTest.php`, `CustomerMarketplaceTest.php`, `DatabaseSchemaTest.php`, `RoleAuthorizationTest.php` — update old expectations and protect navigation/schema behavior.

---

### Task 1: Voucher and Order Data Foundation

**Files:**
- Create: `database/migrations/2026_07_25_000400_create_vouchers_and_extend_orders_table.php`
- Create: `app/Models/Voucher.php`
- Create: `app/Services/VoucherService.php`
- Modify: `app/Models/Order.php`
- Modify: `tests/Feature/DatabaseSchemaTest.php`
- Test: `tests/Feature/VoucherWorkflowTest.php`

**Interfaces:**
- Produces: `VoucherService::normalize(?string $code): ?string`
- Produces: `VoucherService::findValid(?string $code): ?Voucher`
- Produces: `VoucherService::discount(float $subtotal, Voucher $voucher): float`
- Produces: `Order::voucher(): BelongsTo` and casts for `subtotal_amount`, `voucher_discount_amount`, `payment_due_at`

- [ ] **Step 1: Write failing schema and voucher service tests**

```php
public function test_voucher_schema_and_order_snapshots_exist(): void
{
    $this->assertTrue(Schema::hasColumns('vouchers', [
        'code', 'discount_percent', 'expires_at', 'is_active',
    ]));
    $this->assertTrue(Schema::hasColumns('orders', [
        'checkout_token', 'subtotal_amount', 'voucher_id',
        'voucher_code', 'voucher_discount_amount', 'payment_due_at',
    ]));
}

public function test_service_normalizes_validates_and_calculates_percentage(): void
{
    $voucher = Voucher::create([
        'code' => 'HEMAT20',
        'discount_percent' => 20,
        'expires_at' => today()->addDay(),
        'is_active' => true,
    ]);

    $service = app(VoucherService::class);
    $this->assertTrue($service->findValid(' hemat20 ')->is($voucher));
    $this->assertSame(15000.0, $service->discount(75000, $voucher));
}
```

- [ ] **Step 2: Run the focused tests and verify failure**

Run: `docker compose exec -T app php artisan test --filter="DatabaseSchemaTest|VoucherWorkflowTest"`

Expected: FAIL because the voucher table, order columns, model, and service do not exist.

- [ ] **Step 3: Add the cross-database migration, model, and service**

```php
Schema::create('vouchers', function (Blueprint $table) {
    $table->id();
    $table->string('code', 50)->unique();
    $table->unsignedTinyInteger('discount_percent');
    $table->date('expires_at');
    $table->boolean('is_active')->default(true)->index();
    $table->timestamps();
});

Schema::table('orders', function (Blueprint $table) {
    $table->uuid('checkout_token')->nullable()->unique();
    $table->decimal('subtotal_amount', 15, 2)->default(0);
    $table->foreignId('voucher_id')->nullable()->constrained()->nullOnDelete();
    $table->string('voucher_code', 50)->nullable();
    $table->decimal('voucher_discount_amount', 15, 2)->default(0);
    $table->timestamp('payment_due_at')->nullable();
});
```

```php
final class VoucherService
{
    public function normalize(?string $code): ?string
    {
        $normalized = Str::upper(trim((string) $code));
        return $normalized === '' ? null : $normalized;
    }

    public function findValid(?string $code): ?Voucher
    {
        $normalized = $this->normalize($code);
        return $normalized
            ? Voucher::query()->valid()->where('code', $normalized)->first()
            : null;
    }

    public function discount(float $subtotal, Voucher $voucher): float
    {
        return min($subtotal, round($subtotal * $voucher->discount_percent / 100, 2));
    }
}
```

The migration `down()` must drop the order foreign key/columns before dropping `vouchers`. Existing historical orders receive `subtotal_amount = total_amount` with a data update after columns are added.

- [ ] **Step 4: Run focused tests and migration rollback**

Run: `docker compose exec -T app php artisan test --filter="DatabaseSchemaTest|VoucherWorkflowTest"`

Expected: PASS.

Run: `docker compose exec -T app php artisan migrate:fresh --seed`

Expected: migrations and existing seeders complete successfully on MySQL.

- [ ] **Step 5: Commit the data foundation**

```bash
git add database/migrations/2026_07_25_000400_create_vouchers_and_extend_orders_table.php app/Models/Voucher.php app/Models/Order.php app/Services/VoucherService.php tests/Feature/DatabaseSchemaTest.php tests/Feature/VoucherWorkflowTest.php
git commit -m "feat: add voucher and order pricing foundation"
```

### Task 2: Admin Voucher Management

**Files:**
- Create: `app/Http/Requests/Admin/VoucherRequest.php`
- Create: `app/Http/Controllers/Admin/VoucherController.php`
- Create: `resources/views/admin/vouchers/index.blade.php`
- Create: `resources/views/admin/vouchers/form.blade.php`
- Modify: `routes/web.php`
- Modify: `resources/views/layouts/app.blade.php`
- Test: `tests/Feature/VoucherWorkflowTest.php`
- Modify: `tests/Feature/RoleAuthorizationTest.php`

**Interfaces:**
- Consumes: `Voucher` model from Task 1.
- Produces: resource routes `admin.vouchers.index|create|store|edit|update|destroy`.
- Produces: normalized uppercase `VoucherRequest::validated()` payload.

- [ ] **Step 1: Add failing admin CRUD and authorization tests**

```php
public function test_admin_can_create_update_filter_and_delete_voucher(): void
{
    $admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($admin)->post(route('admin.vouchers.store'), [
        'code' => ' hemat20 ',
        'discount_percent' => 20,
        'expires_at' => today()->addMonth()->toDateString(),
        'is_active' => '1',
    ])->assertRedirect(route('admin.vouchers.index'));

    $voucher = Voucher::firstOrFail();
    $this->assertSame('HEMAT20', $voucher->code);
    $this->actingAs($admin)->get(route('admin.vouchers.index', ['state' => 'active']))
        ->assertOk()->assertSee('HEMAT20');
}

public function test_customer_cannot_manage_vouchers(): void
{
    $customer = User::factory()->create(['role' => 'customer']);
    $this->actingAs($customer)->get('/admin/vouchers')->assertForbidden();
}
```

- [ ] **Step 2: Run tests and verify missing routes**

Run: `docker compose exec -T app php artisan test --filter="VoucherWorkflowTest|RoleAuthorizationTest"`

Expected: FAIL because `admin.vouchers.*` routes and controller do not exist.

- [ ] **Step 3: Implement validated CRUD and modern views**

```php
protected function prepareForValidation(): void
{
    $this->merge([
        'code' => Str::upper(trim((string) $this->input('code'))),
        'is_active' => $this->boolean('is_active'),
    ]);
}

public function rules(): array
{
    return [
        'code' => ['required', 'string', 'max:50',
            Rule::unique('vouchers', 'code')->ignore($this->route('voucher'))],
        'discount_percent' => ['required', 'integer', 'between:1,100'],
        'expires_at' => ['required', 'date'],
        'is_active' => ['required', 'boolean'],
    ];
}
```

```php
Route::resource('vouchers', VoucherController::class)->except('show');
```

The index query must eager-count `orders`, search `code`, filter `active`/`expired`, order newest first, and paginate with query strings. The form uses the established admin panel styling and clearly labels percentage, expiration, and active status.

- [ ] **Step 4: Run voucher and authorization tests**

Run: `docker compose exec -T app php artisan test --filter="VoucherWorkflowTest|RoleAuthorizationTest"`

Expected: PASS with admin CRUD visible and customer access forbidden.

- [ ] **Step 5: Commit voucher management**

```bash
git add app/Http/Requests/Admin/VoucherRequest.php app/Http/Controllers/Admin/VoucherController.php resources/views/admin/vouchers resources/views/layouts/app.blade.php routes/web.php tests/Feature/VoucherWorkflowTest.php tests/Feature/RoleAuthorizationTest.php
git commit -m "feat: add admin voucher management"
```

### Task 3: Idempotent Order-First Checkout

**Files:**
- Create: `app/Services/CheckoutService.php`
- Create: `tests/Feature/CheckoutIdempotencyTest.php`
- Modify: `app/Http/Requests/Customer/CheckoutRequest.php`
- Modify: `app/Http/Controllers/Customer/CheckoutController.php`
- Modify: `app/Models/Order.php`
- Modify: `app/Models/User.php`
- Modify: `resources/views/customer/checkout/create.blade.php`
- Modify: `routes/web.php`
- Modify: `tests/Feature/TransactionWorkflowTest.php`

**Interfaces:**
- Consumes: `VoucherService::findValid()` and `discount()`.
- Produces: `CheckoutService::create(User $user, string $checkoutToken, string $paymentMethod, ?string $voucherCode): Order`.
- Produces: session keys `checkout_token` and `checkout_voucher_code`.
- Produces: routes `checkout.voucher.apply` and `checkout.voucher.remove`.

- [ ] **Step 1: Write failing method, idempotency, cart, and voucher tests**

```php
public function test_each_method_creates_order_without_proof_and_clears_cart(): void
{
    foreach (['virtual_account', 'bank_transfer', 'e_wallet'] as $method) {
        [$customer, $game] = $this->customerWithCart($method);
        $token = (string) Str::uuid();

        $this->actingAs($customer)->post(route('checkout.store'), [
            'checkout_token' => $token,
            'payment_method' => $method,
        ])->assertRedirect();

        $this->assertDatabaseHas('orders', ['user_id' => $customer->id, 'checkout_token' => $token]);
        $this->assertDatabaseMissing('cart_items', ['game_id' => $game->id]);
    }
}

public function test_reusing_checkout_token_returns_existing_order(): void
{
    [$customer] = $this->customerWithCart('duplicate');
    $token = (string) Str::uuid();
    $payload = ['checkout_token' => $token, 'payment_method' => 'virtual_account'];

    $this->actingAs($customer)->post(route('checkout.store'), $payload);
    $this->actingAs($customer)->post(route('checkout.store'), $payload)->assertRedirect();

    $this->assertDatabaseCount('orders', 1);
    $this->assertDatabaseCount('payments', 1);
}
```

Include this authoritative pricing assertion in the same test file:

```php
$order = Order::with('payment')->where('checkout_token', $token)->firstOrFail();
$this->assertSame('75000.00', $order->subtotal_amount);
$this->assertSame('15000.00', $order->voucher_discount_amount);
$this->assertSame('60000.00', $order->total_amount);
$this->assertSame('60000.00', $order->payment->amount);
$this->assertSame('HEMAT20', $order->voucher_code);
$this->assertTrue($order->payment_due_at->equalTo($order->ordered_at->copy()->addHours(24)));
```

- [ ] **Step 2: Run the new checkout tests and verify failure**

Run: `docker compose exec -T app php artisan test --filter="CheckoutIdempotencyTest|TransactionWorkflowTest"`

Expected: FAIL because checkout still requires proof, has no token, leaves cart items, and ignores vouchers.

- [ ] **Step 3: Implement the atomic service and thin controller**

```php
public function create(
    User $user,
    string $checkoutToken,
    string $paymentMethod,
    ?string $voucherCode,
): Order {
    if ($existing = $user->orders()->where('checkout_token', $checkoutToken)->first()) {
        return $existing;
    }

    return DB::transaction(function () use ($user, $checkoutToken, $paymentMethod, $voucherCode): Order {
        $cart = Cart::query()->where('user_id', $user->id)->lockForUpdate()->first();
        if ($existing = $user->orders()->where('checkout_token', $checkoutToken)->first()) {
            return $existing;
        }
        if (! $cart) {
            throw ValidationException::withMessages(['cart' => 'Cart kosong dan tidak dapat di-checkout.']);
        }

        $cart->load('items.game');
        if ($cart->items->isEmpty()) {
            throw ValidationException::withMessages(['cart' => 'Cart kosong dan tidak dapat di-checkout.']);
        }
        $gameIds = $cart->items->pluck('game_id');
        if ($cart->items->contains(fn ($item) => ! $item->game || $item->game->status !== 'published')) {
            throw ValidationException::withMessages(['cart' => 'Cart memuat game yang sudah tidak tersedia.']);
        }
        if ($user->libraries()->whereIn('game_id', $gameIds)->exists()) {
            throw ValidationException::withMessages(['cart' => 'Cart memuat game yang sudah dimiliki.']);
        }
        if ($user->orders()->where('status', 'pending')
            ->whereHas('items', fn ($query) => $query->whereIn('game_id', $gameIds))
            ->exists()) {
            throw ValidationException::withMessages(['cart' => 'Salah satu game masih menunggu pembayaran.']);
        }
        $subtotal = (float) $cart->items->sum(fn ($item) => $item->game->currentPrice());
        $voucher = $voucherCode ? $this->vouchers->findValid($voucherCode) : null;
        if ($voucherCode && ! $voucher) {
            throw ValidationException::withMessages(['voucher_code' => 'Voucher tidak aktif atau sudah kedaluwarsa.']);
        }
        $discount = $voucher ? $this->vouchers->discount($subtotal, $voucher) : 0.0;

        $order = $user->orders()->create([
            'checkout_token' => $checkoutToken,
            'order_code' => $this->newOrderCode(),
            'subtotal_amount' => $subtotal,
            'voucher_id' => $voucher?->id,
            'voucher_code' => $voucher?->code,
            'voucher_discount_amount' => $discount,
            'total_amount' => $subtotal - $discount,
            'status' => 'pending',
            'ordered_at' => now(),
            'payment_due_at' => now()->addHours(24),
        ]);
        foreach ($cart->items as $item) {
            $current = (float) $item->game->currentPrice();
            $original = (float) $item->game->original_price;
            $order->items()->create([
                'game_id' => $item->game_id,
                'game_title' => $item->game->title,
                'unit_price' => $original,
                'discount_amount' => max(0, $original - $current),
                'subtotal' => $current,
            ]);
        }
        $order->payment()->create([
            'payment_method' => $paymentMethod,
            'virtual_account_number' => $paymentMethod === 'virtual_account'
                ? '8808'.str_pad((string) $order->id, 12, '0', STR_PAD_LEFT)
                : null,
            'amount' => $order->total_amount,
            'status' => 'pending',
        ]);
        $cart->items()->delete();
        return $order;
    });
}
```

`CheckoutRequest` accepts only a required UUID token and one of the three method values. `CheckoutController::create()` initializes the token once, resolves the current voucher for display, and sends server totals. Apply/remove endpoints update only `checkout_voucher_code`; `store()` calls the service, forgets both checkout session keys, and redirects to the existing/new order.

- [ ] **Step 4: Run focused checkout tests**

Run: `docker compose exec -T app php artisan test --filter="CheckoutIdempotencyTest|TransactionWorkflowTest"`

Expected: PASS; three methods work without proof, one token produces one order/payment, cart clears immediately, and authoritative voucher totals are stored.

- [ ] **Step 5: Commit the checkout transaction**

```bash
git add app/Services/CheckoutService.php app/Http/Requests/Customer/CheckoutRequest.php app/Http/Controllers/Customer/CheckoutController.php app/Models/Order.php app/Models/User.php resources/views/customer/checkout/create.blade.php routes/web.php tests/Feature/CheckoutIdempotencyTest.php tests/Feature/TransactionWorkflowTest.php
git commit -m "feat: make checkout idempotent and order first"
```

### Task 4: Pending Purchase Protection and Customer Cancellation

**Files:**
- Modify: `app/Http/Controllers/Customer/CartController.php`
- Modify: `app/Http/Controllers/Customer/OrderController.php`
- Modify: `app/Models/Order.php`
- Modify: `routes/web.php`
- Modify: `resources/views/customer/orders/show.blade.php`
- Test: `tests/Feature/CheckoutIdempotencyTest.php`
- Test: `tests/Feature/PaymentSubmissionTest.php`

**Interfaces:**
- Produces: `Order::hasSubmittedProof(): bool`
- Produces: POST route `orders.cancel`.
- Consumes: order status `pending`, payment status `pending`, and nullable `payment_proof`.

- [ ] **Step 1: Add failing duplicate-cart and cancellation tests**

```php
public function test_game_in_active_pending_order_cannot_return_to_cart(): void
{
    [$customer, $game, $order] = $this->pendingOrder();
    $this->actingAs($customer)->post(route('cart.store', $game))
        ->assertSessionHas('error');
    $this->assertDatabaseCount('cart_items', 0);
}

public function test_owner_can_cancel_only_before_proof_submission(): void
{
    [$customer, , $order] = $this->pendingOrder();
    $this->actingAs($customer)->post(route('orders.cancel', $order))
        ->assertSessionHas('success');
    $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
    $this->assertDatabaseHas('payments', ['order_id' => $order->id, 'status' => 'failed']);
}
```

Define the remaining cancellation expectations explicitly:

```php
$this->actingAs($otherCustomer)->post(route('orders.cancel', $order))->assertNotFound();
$this->actingAs($customer)->post(route('orders.cancel', $order))->assertSessionHas('success');
$this->actingAs($customer)->post(route('orders.cancel', $order->fresh()))->assertSessionHas('success');

$orderWithProof->payment()->update(['payment_proof' => 'storage/payment-proofs/existing.webp']);
$this->actingAs($customer)->post(route('orders.cancel', $orderWithProof))
    ->assertSessionHasErrors('order');
$this->assertDatabaseHas('orders', ['id' => $orderWithProof->id, 'status' => 'pending']);
```

- [ ] **Step 2: Run focused tests and verify failure**

Run: `docker compose exec -T app php artisan test --filter="CheckoutIdempotencyTest|PaymentSubmissionTest"`

Expected: FAIL because pending orders do not guard Cart and no cancellation route exists.

- [ ] **Step 3: Add relational guards and atomic cancellation**

```php
$hasPendingPurchase = $request->user()->orders()
    ->where('status', 'pending')
    ->whereHas('items', fn ($query) => $query->where('game_id', $game->id))
    ->exists();

if ($hasPendingPurchase) {
    return back()->with('error', 'Game ini masih menunggu pembayaran. Buka Lacak Pesanan untuk melanjutkan.');
}
```

```php
public function cancel(Request $request, Order $order): RedirectResponse
{
    abort_unless($order->user_id === $request->user()->id, 404);
    DB::transaction(function () use ($order): void {
        $locked = Order::query()->lockForUpdate()->with('payment')->findOrFail($order->id);
        if ($locked->status === 'cancelled') {
            return;
        }
        if ($locked->status !== 'pending' || $locked->hasSubmittedProof()) {
            throw ValidationException::withMessages(['order' => 'Pesanan tidak dapat dibatalkan setelah bukti dikirim.']);
        }
        $locked->update(['status' => 'cancelled']);
        $locked->payment()->update(['status' => 'failed']);
    });
    return back()->with('success', 'Pesanan dibatalkan. Game dapat ditambahkan ke Cart kembali.');
}
```

- [ ] **Step 4: Run focused tests**

Run: `docker compose exec -T app php artisan test --filter="CheckoutIdempotencyTest|PaymentSubmissionTest|CustomerMarketplaceTest"`

Expected: PASS; active pending purchases are blocked, while cancelled games can be re-added.

- [ ] **Step 5: Commit pending-order safeguards**

```bash
git add app/Http/Controllers/Customer/CartController.php app/Http/Controllers/Customer/OrderController.php app/Models/Order.php routes/web.php resources/views/customer/orders/show.blade.php tests/Feature/CheckoutIdempotencyTest.php tests/Feature/PaymentSubmissionTest.php tests/Feature/CustomerMarketplaceTest.php
git commit -m "feat: guard pending purchases and order cancellation"
```

### Task 5: Payment Instructions and Delayed Proof Upload

**Files:**
- Create: `config/payments.php`
- Create: `app/Services/PaymentInstructionService.php`
- Create: `app/Http/Requests/Customer/PaymentProofRequest.php`
- Modify: `app/Http/Controllers/Customer/OrderController.php`
- Modify: `app/Models/Payment.php`
- Modify: `resources/views/customer/orders/show.blade.php`
- Modify: `routes/web.php`
- Test: `tests/Feature/PaymentSubmissionTest.php`

**Interfaces:**
- Produces: `PaymentInstructionService::for(Order $order): array{label:string,destination_label:string,destination:string,account_name:string,steps:array<int,string>}`.
- Produces: POST route `orders.payment.submit`.
- Produces: `PaymentProofRequest` validated fields `payment_reference` and `payment_proof`.

- [ ] **Step 1: Write failing instruction, upload, replacement, and expiry tests**

```php
public function test_order_page_shows_method_specific_simulated_instructions(): void
{
    [$customer, $order] = $this->pendingOrder('bank_transfer');
    $this->actingAs($customer)->get(route('orders.show', $order))
        ->assertOk()
        ->assertSee('Transfer Bank')
        ->assertSee('Tidak perlu mengirim uang sungguhan');
}

public function test_customer_uploads_proof_after_order_creation(): void
{
    Storage::fake('public');
    [$customer, $order] = $this->pendingOrder('e_wallet');
    $this->actingAs($customer)->post(route('orders.payment.submit', $order), [
        'payment_reference' => 'EW-20260725-001',
        'payment_proof' => UploadedFile::fake()->image('proof.webp'),
    ])->assertSessionHas('success');

    $this->assertNotNull($order->payment->fresh()->paid_at);
    Storage::disk('public')->assertExists(
        Str::after($order->payment->fresh()->payment_proof, 'storage/')
    );
}
```

Define the remaining proof tests with these concrete requests and assertions:

```php
$this->actingAs($customer)->post(route('orders.payment.submit', $bankOrder), [
    'payment_proof' => UploadedFile::fake()->image('bank.jpg'),
])->assertSessionHasErrors('payment_reference');

$this->actingAs($customer)->post(route('orders.payment.submit', $vaOrder), [])
    ->assertSessionHasErrors('payment_proof');
$this->actingAs($customer)->post(route('orders.payment.submit', $vaOrder), [
    'payment_proof' => UploadedFile::fake()->create('script.exe', 10),
])->assertSessionHasErrors('payment_proof');
$this->actingAs($otherCustomer)->post(route('orders.payment.submit', $vaOrder), [
    'payment_proof' => UploadedFile::fake()->image('other.jpg'),
])->assertNotFound();

$oldPath = Str::after($replaceOrder->payment->payment_proof, 'storage/');
$this->actingAs($customer)->post(route('orders.payment.submit', $replaceOrder), [
    'payment_proof' => UploadedFile::fake()->image('replacement.jpg'),
])->assertSessionHas('success');
Storage::disk('public')->assertMissing($oldPath);

$verifiedOrder->payment()->update(['status' => 'verified']);
$this->actingAs($customer)->post(route('orders.payment.submit', $verifiedOrder), [
    'payment_proof' => UploadedFile::fake()->image('late.jpg'),
])->assertSessionHasErrors('payment');

$expiredOrder->update(['payment_due_at' => now()->subMinute()]);
$this->actingAs($customer)->post(route('orders.payment.submit', $expiredOrder), [
    'payment_proof' => UploadedFile::fake()->image('expired.jpg'),
])->assertSessionHasErrors('payment');
$this->assertDatabaseHas('orders', ['id' => $expiredOrder->id, 'status' => 'cancelled']);
$this->assertDatabaseHas('payments', ['order_id' => $expiredOrder->id, 'status' => 'failed']);
```

- [ ] **Step 2: Run payment tests and verify failure**

Run: `docker compose exec -T app php artisan test --filter=PaymentSubmissionTest`

Expected: FAIL because instructions, proof request, upload route, and expiry handling do not exist.

- [ ] **Step 3: Implement non-secret configuration, service, request, and upload**

```php
return [
    'virtual_account' => [
        'label' => 'Virtual Account',
        'destination_label' => 'Nomor Virtual Account',
        'account_name' => 'DayatGames Demo',
        'steps' => ['Buka mobile banking atau ATM.', 'Pilih pembayaran Virtual Account.', 'Masukkan nomor VA dan pastikan nominal sesuai.', 'Simpan bukti transaksi simulasi.'],
    ],
    'bank_transfer' => [
        'label' => 'Transfer Bank',
        'destination_label' => 'Rekening demo Bank Dayat',
        'destination' => '8808 2026 0725',
        'account_name' => 'DayatGames Demo',
        'steps' => ['Pilih transfer antarbank.', 'Masukkan rekening demo.', 'Masukkan nominal tepat.', 'Simpan referensi dan bukti simulasi.'],
    ],
    'e_wallet' => [
        'label' => 'E-Wallet',
        'destination_label' => 'DayatPay Demo',
        'destination' => '0812 0000 2026',
        'account_name' => 'DayatGames Demo',
        'steps' => ['Buka aplikasi e-wallet.', 'Pilih kirim saldo.', 'Masukkan akun demo dan nominal tepat.', 'Simpan referensi dan bukti simulasi.'],
    ],
];
```

```php
public function rules(): array
{
    $requiresReference = in_array(
        $this->route('order')?->payment?->payment_method,
        ['bank_transfer', 'e_wallet'],
        true,
    );
    return [
        'payment_reference' => [
            Rule::requiredIf($requiresReference), 'nullable', 'string', 'max:100',
        ],
        'payment_proof' => ['required', 'file', 'mimes:jpg,jpeg,png,webp,pdf', 'max:4096'],
    ];
}
```

The controller must authorize ownership before using the order, lock order/payment, reject verified/non-pending records, cancel expired records, store the new file only after validation, update reference/proof/first `paid_at`, and delete only a superseded managed file after the database update succeeds.

- [ ] **Step 4: Run payment and transaction tests**

Run: `docker compose exec -T app php artisan test --filter="PaymentSubmissionTest|TransactionWorkflowTest"`

Expected: PASS for all upload states, method instructions, replacement cleanup, and expiry cancellation.

- [ ] **Step 5: Commit payment submission**

```bash
git add config/payments.php app/Services/PaymentInstructionService.php app/Http/Requests/Customer/PaymentProofRequest.php app/Http/Controllers/Customer/OrderController.php app/Models/Payment.php resources/views/customer/orders/show.blade.php routes/web.php tests/Feature/PaymentSubmissionTest.php tests/Feature/TransactionWorkflowTest.php
git commit -m "feat: add guided payment proof submission"
```

### Task 6: Modern Owner-Scoped Order Tracking

**Files:**
- Create: `tests/Feature/OrderTrackingTest.php`
- Modify: `app/Http/Controllers/Customer/OrderController.php`
- Modify: `app/Models/Order.php`
- Modify: `resources/views/customer/orders/index.blade.php`
- Modify: `resources/views/customer/orders/show.blade.php`
- Modify: `resources/views/layouts/app.blade.php`
- Modify: `resources/css/app.css`

**Interfaces:**
- Produces: customer navigation label `Lacak Pesanan` pointing to `orders.index`.
- Produces: query parameters `search` and `status`.
- Produces: `Order::trackingStage(): string` and `Order::trackingSteps(): array<int,array{label:string,state:string}>`.

- [ ] **Step 1: Add failing search, filtering, ownership, cards, and timeline tests**

```php
public function test_tracking_search_and_status_are_scoped_to_owner(): void
{
    [$customer, $mine] = $this->orderFor('DG-MINE-001', 'pending');
    [, $other] = $this->orderFor('DG-OTHER-999', 'completed');

    $this->actingAs($customer)->get(route('orders.index', [
        'search' => 'MINE', 'status' => 'pending',
    ]))->assertOk()
        ->assertSee($mine->order_code)
        ->assertDontSee($other->order_code)
        ->assertSee('data-order-card', false);
}

public function test_tracking_detail_renders_artwork_and_timeline(): void
{
    [$customer, $order] = $this->orderFor('DG-TIMELINE-1', 'pending', proof: true);
    $this->actingAs($customer)->get(route('orders.show', $order))
        ->assertOk()
        ->assertSee('Menunggu verifikasi')
        ->assertSee('data-order-timeline', false);
}
```

- [ ] **Step 2: Run tracking tests and verify failure**

Run: `docker compose exec -T app php artisan test --filter=OrderTrackingTest`

Expected: FAIL because the index ignores filters and renders a table without tracking hooks.

- [ ] **Step 3: Implement scoped query, state helpers, cards, and timeline**

```php
$orders = $request->user()->orders()
    ->with(['payment', 'items.game'])
    ->when($request->filled('search'), fn ($query) => $query
        ->where('order_code', 'like', '%'.trim((string) $request->string('search')).'%'))
    ->when(in_array($request->string('status')->toString(), ['pending', 'completed', 'cancelled'], true),
        fn ($query) => $query->where('status', $request->string('status')))
    ->latest('ordered_at')
    ->paginate(8)
    ->withQueryString();
```

`trackingStage()` maps pending/no proof, pending/proof, completed/verified, and cancelled/failed to Indonesian labels. `trackingSteps()` returns completed/current/future states used by semantic list markup. Cards show first cover plus additional count, order code, date, final total, and stage.

- [ ] **Step 4: Run tracking and marketplace tests**

Run: `docker compose exec -T app php artisan test --filter="OrderTrackingTest|CustomerMarketplaceTest"`

Expected: PASS; another customer cannot see an order in search or detail, and mobile-safe cards replace the table.

- [ ] **Step 5: Commit order tracking**

```bash
git add tests/Feature/OrderTrackingTest.php app/Http/Controllers/Customer/OrderController.php app/Models/Order.php resources/views/customer/orders/index.blade.php resources/views/customer/orders/show.blade.php resources/views/layouts/app.blade.php resources/css/app.css
git commit -m "feat: add modern customer order tracking"
```

### Task 7: Actionable Admin Payment Queue and Verification Guard

**Files:**
- Create: `tests/Feature/AdminPaymentQueueTest.php`
- Modify: `app/Http/Controllers/Admin/PaymentController.php`
- Modify: `app/Http/Controllers/Admin/DashboardController.php`
- Modify: `resources/views/admin/payments/index.blade.php`
- Modify: `resources/views/admin/payments/show.blade.php`
- Modify: `resources/views/admin/dashboard.blade.php`
- Modify: `resources/css/app.css`
- Modify: `tests/Feature/TransactionWorkflowTest.php`

**Interfaces:**
- Produces: payment queue query parameter `queue=ready|waiting|verified|failed|all`, default `ready`.
- Produces: dashboard stats `payments_ready` and `payments_waiting`.
- Consumes: `Game::coverUrl()` and proof state from Task 5.

- [ ] **Step 1: Add failing queue, artwork, metric, and guard tests**

```php
public function test_default_queue_only_shows_pending_payments_with_proof(): void
{
    [$admin, $ready, $waiting] = $this->readyAndWaitingPayments();
    $this->actingAs($admin)->get(route('admin.payments.index'))
        ->assertOk()
        ->assertSee($ready->order->order_code)
        ->assertDontSee($waiting->order->order_code)
        ->assertSee($ready->order->items->first()->game->coverUrl(), false);
}

public function test_admin_cannot_verify_payment_without_proof(): void
{
    [$admin, , $waiting] = $this->readyAndWaitingPayments();
    $this->actingAs($admin)->post(route('admin.payments.verify', $waiting))
        ->assertSessionHas('error');
    $this->assertDatabaseHas('payments', ['id' => $waiting->id, 'status' => 'pending']);
}
```

Add these exact queue, cover, and dashboard assertions:

```php
$this->actingAs($admin)->get(route('admin.payments.index', ['queue' => 'waiting']))
    ->assertOk()
    ->assertSee($waiting->order->order_code)
    ->assertDontSee($ready->order->order_code);

$detail = $this->actingAs($admin)->get(route('admin.payments.show', $ready));
foreach ($ready->order->items as $item) {
    $detail->assertSee($item->game->coverUrl(), false)->assertSee($item->game_title);
}

$this->actingAs($admin)->get(route('admin.dashboard'))
    ->assertOk()
    ->assertSee('1 pembayaran siap diverifikasi')
    ->assertSee('1 menunggu customer');
```

- [ ] **Step 2: Run admin payment tests and verify failure**

Run: `docker compose exec -T app php artisan test --filter="AdminPaymentQueueTest|TransactionWorkflowTest"`

Expected: FAIL because all payments are listed together and verification accepts missing proof.

- [ ] **Step 3: Implement eager-loaded queue scopes and locked proof guard**

```php
$queue = in_array($request->string('queue')->toString(),
    ['ready', 'waiting', 'verified', 'failed', 'all'], true)
    ? $request->string('queue')->toString()
    : 'ready';

$payments = Payment::query()
    ->with(['order.user', 'order.items.game', 'verifier'])
    ->when($queue === 'ready', fn ($q) => $q->where('status', 'pending')->whereNotNull('payment_proof'))
    ->when($queue === 'waiting', fn ($q) => $q->where('status', 'pending')->whereNull('payment_proof'))
    ->when($queue === 'verified', fn ($q) => $q->where('status', 'verified'))
    ->when($queue === 'failed', fn ($q) => $q->where('status', 'failed'));
```

Inside the existing verification transaction, after locking:

```php
if (! $payment->payment_proof) {
    throw ValidationException::withMessages([
        'payment' => 'Bukti pembayaran belum dikirim customer.',
    ]);
}
```

Remove the obsolete cart cleanup from admin verification because checkout now clears Cart. Render cover-led responsive rows/cards, additional item count, submitted time, status badge, and proof-ready actions.

- [ ] **Step 4: Run admin and transaction tests**

Run: `docker compose exec -T app php artisan test --filter="AdminPaymentQueueTest|TransactionWorkflowTest"`

Expected: PASS; the default queue is actionable, no-proof verification is blocked, and library creation remains idempotent.

- [ ] **Step 5: Commit the admin payment queue**

```bash
git add tests/Feature/AdminPaymentQueueTest.php app/Http/Controllers/Admin/PaymentController.php app/Http/Controllers/Admin/DashboardController.php resources/views/admin/payments/index.blade.php resources/views/admin/payments/show.blade.php resources/views/admin/dashboard.blade.php resources/css/app.css tests/Feature/TransactionWorkflowTest.php
git commit -m "feat: focus admin payment verification queue"
```

### Task 8: Integrate Developer and Publisher into Game CRUD

**Files:**
- Modify: `app/Http/Requests/Admin/GameRequest.php`
- Modify: `app/Http/Controllers/Admin/GameController.php`
- Modify: `resources/views/admin/games/form.blade.php`
- Modify: `resources/views/layouts/app.blade.php`
- Modify: `tests/Feature/AdminCatalogCrudTest.php`

**Interfaces:**
- Produces: optional request fields `new_developer_name` and `new_publisher_name`.
- Produces: private `GameController::resolveParty(string $modelClass, ?int $id, ?string $newName): int`.
- Keeps: existing developer/publisher routes and tables for compatibility, without sidebar exposure.

- [ ] **Step 1: Add failing inline-creation and navigation tests**

```php
public function test_admin_can_create_game_with_inline_developer_and_publisher(): void
{
    $genre = Genre::create(['name' => 'Action', 'slug' => 'action']);
    $payload = $this->gamePayloadWithoutParties($genre) + [
        'new_developer_name' => '  New Studio  ',
        'new_publisher_name' => 'New Publisher',
    ];
    $this->actingAs($this->admin)->post(route('admin.games.store'), $payload)
        ->assertSessionHasNoErrors();

    $this->assertDatabaseHas('developers', ['name' => 'New Studio', 'slug' => 'new-studio']);
    $this->assertDatabaseHas('publishers', ['name' => 'New Publisher', 'slug' => 'new-publisher']);
}

public function test_admin_sidebar_keeps_metadata_inside_game(): void
{
    $this->actingAs($this->admin)->get(route('admin.dashboard'))
        ->assertOk()
        ->assertDontSee('>Publisher<', false)
        ->assertDontSee('>Developer<', false);
}
```

Add this normalized-reuse assertion:

```php
Developer::create(['name' => 'Existing Studio', 'slug' => 'existing-studio']);
$this->actingAs($this->admin)->post(route('admin.games.store'), [
    ...$this->gamePayloadWithoutParties($genre),
    'new_developer_name' => ' Existing Studio ',
    'new_publisher_name' => 'Only Publisher',
])->assertSessionHasNoErrors();
$this->assertSame(1, Developer::where('slug', 'existing-studio')->count());
```

- [ ] **Step 2: Run catalog tests and verify failure**

Run: `docker compose exec -T app php artisan test --filter=AdminCatalogCrudTest`

Expected: FAIL because IDs are mandatory and standalone navigation links remain.

- [ ] **Step 3: Add XOR-style validation and transactional party resolution**

```php
'developer_id' => ['nullable', 'exists:developers,id', 'required_without:new_developer_name'],
'new_developer_name' => ['nullable', 'string', 'max:255', 'required_without:developer_id'],
'publisher_id' => ['nullable', 'exists:publishers,id', 'required_without:new_publisher_name'],
'new_publisher_name' => ['nullable', 'string', 'max:255', 'required_without:publisher_id'],
```

```php
private function resolveParty(string $modelClass, ?int $id, ?string $newName): int
{
    if ($id) {
        return $id;
    }
    $name = trim((string) $newName);
    $slug = Str::slug($name);
    return $modelClass::query()->firstOrCreate(['slug' => $slug], ['name' => $name])->id;
}
```

Exclude the two new fields from game mass assignment, resolve both IDs inside the existing database transaction, and present each select with a clearly separated “atau tambahkan baru” input. Remove only the two sidebar items; do not remove their controllers, routes, models, or tables.

- [ ] **Step 4: Run catalog and authorization tests**

Run: `docker compose exec -T app php artisan test --filter="AdminCatalogCrudTest|RoleAuthorizationTest"`

Expected: PASS for existing select behavior, inline creation, reuse, and sidebar removal.

- [ ] **Step 5: Commit integrated metadata**

```bash
git add app/Http/Requests/Admin/GameRequest.php app/Http/Controllers/Admin/GameController.php resources/views/admin/games/form.blade.php resources/views/layouts/app.blade.php tests/Feature/AdminCatalogCrudTest.php
git commit -m "feat: manage game studios inside game form"
```

### Task 9: Modern Checkout, Footer, and Progressive Interaction Polish

**Files:**
- Modify: `resources/views/customer/checkout/create.blade.php`
- Modify: `resources/views/customer/orders/index.blade.php`
- Modify: `resources/views/customer/orders/show.blade.php`
- Modify: `resources/views/layouts/app.blade.php`
- Modify: `resources/css/app.css`
- Modify: `resources/js/app.js`
- Modify: `tests/Feature/CustomerMarketplaceTest.php`
- Modify: `tests/Feature/OrderTrackingTest.php`

**Interfaces:**
- Produces: hooks `data-payment-method`, `data-copy-payment`, `data-order-card`, and `data-order-timeline`.
- Produces: four footer groups Brand, Shop, Customer, and Payment plus a bottom disclaimer bar.
- Consumes: routes and view data from Tasks 2–8.

- [ ] **Step 1: Add failing structural and accessibility assertions**

```php
public function test_storefront_has_tracking_navigation_and_marketplace_footer(): void
{
    $customer = User::factory()->create(['role' => 'customer']);
    $this->actingAs($customer)->get(route('home'))
        ->assertOk()
        ->assertSee('Lacak Pesanan')
        ->assertSee('Shop')
        ->assertSee('Customer')
        ->assertSee('Virtual Account')
        ->assertSee('Simulasi akademik');
}

public function test_checkout_uses_payment_cards_without_proof_input(): void
{
    [$customer] = $this->customerWithCart();
    $this->actingAs($customer)->get(route('checkout.create'))
        ->assertOk()
        ->assertSee('data-payment-method', false)
        ->assertDontSee('name="payment_proof"', false);
}
```

- [ ] **Step 2: Run UI-focused feature tests and verify failure**

Run: `docker compose exec -T app php artisan test --filter="CustomerMarketplaceTest|OrderTrackingTest|CheckoutIdempotencyTest"`

Expected: FAIL until the new navigation, footer groups, hooks, and proof-free checkout exist.

- [ ] **Step 3: Implement responsive markup, styles, and progressive JavaScript**

```js
document.querySelectorAll('[data-payment-method]').forEach((card) => {
    const input = card.querySelector('input[type="radio"]');
    const sync = () => card.classList.toggle('is-selected', input.checked);
    input.addEventListener('change', () => {
        document.querySelectorAll('[data-payment-method]').forEach((item) =>
            item.classList.remove('is-selected'));
        sync();
    });
    sync();
});

document.querySelectorAll('[data-copy-payment]').forEach((button) => {
    button.addEventListener('click', async () => {
        await navigator.clipboard.writeText(button.dataset.copyPayment);
        button.textContent = 'Tersalin';
        window.setTimeout(() => { button.textContent = 'Salin'; }, 1600);
    });
});
```

Add CSS component classes for payment cards, voucher rows, instruction panels, tracking cards/timeline, admin artwork rows, and footer columns. Use existing DayatGames tokens/aurora, `clamp()` spacing, `min-width: 0`, and mobile breakpoints. Copy controls retain selectable text when Clipboard API is unavailable. Reduced-motion media queries disable new transforms/transitions.

- [ ] **Step 4: Run feature tests and production build**

Run: `docker compose exec -T app php artisan test --filter="CustomerMarketplaceTest|OrderTrackingTest|CheckoutIdempotencyTest"`

Expected: PASS.

Run: `npm.cmd run build`

Expected: Vite production build exits 0 without CSS or JavaScript errors.

- [ ] **Step 5: Commit the modern interface**

```bash
git add resources/views/customer/checkout/create.blade.php resources/views/customer/orders/index.blade.php resources/views/customer/orders/show.blade.php resources/views/layouts/app.blade.php resources/css/app.css resources/js/app.js tests/Feature/CustomerMarketplaceTest.php tests/Feature/OrderTrackingTest.php tests/Feature/CheckoutIdempotencyTest.php
git commit -m "feat: polish checkout tracking and footer UI"
```

### Task 10: Full Regression and Browser Verification

**Files:**
- Modify only if verification exposes a defect in files already listed above.
- Do not modify: `docs/report/**`, `*.docx`, or `*.pdf`.

**Interfaces:**
- Verifies all public/admin routes and production assets; produces no new application interface.

- [ ] **Step 1: Run formatting/static change checks**

Run: `docker compose exec -T app ./vendor/bin/pint --test`

Expected: PASS with no PHP formatting violations. If it fails, run `docker compose exec -T app ./vendor/bin/pint`, inspect the diff, and rerun `--test`.

Run: `git diff --check`

Expected: no whitespace errors.

- [ ] **Step 2: Run the complete automated suite**

Run: `docker compose exec -T app php artisan test`

Expected: all feature and unit tests PASS, including existing preview gallery and seeder tests.

- [ ] **Step 3: Verify migrations and production build from a clean database**

Run: `docker compose exec -T app php artisan migrate:fresh --seed`

Expected: MySQL schema builds and the existing game/preview seed data loads.

Run: `npm.cmd run build`

Expected: build exits 0 and writes versioned assets to `public/build`.

- [ ] **Step 4: Perform desktop and mobile browser QA**

Use a customer and admin account against `http://localhost:8080` and verify:

```text
Customer: voucher apply/remove; all three checkout methods; back navigation;
cart remains empty; method instructions; copy controls; proof upload/replace;
cancel before proof; tracking search/filter/timeline; footer links.

Admin: Voucher CRUD; Game inline studio/publisher creation; Payment ready/waiting
filters; cover artwork; no-proof verification blocked; verified order reaches Library.

Viewports: 1440x900 and 390x844; no horizontal overflow; no console errors;
keyboard focus visible; reduced-motion mode remains usable.
```

Expected: all scenarios succeed and the UI matches the existing DayatGames visual language.

- [ ] **Step 5: Commit only verified corrections, then capture final status**

```bash
git add app config database resources routes tests
git commit -m "fix: resolve checkout workflow verification findings"
git status --short
git log -10 --oneline
```

If browser verification requires no corrections, skip the empty commit. Final `git status --short` must be clean except for explicitly identified pre-existing user files, and report the exact test/build results without editing Word/PDF artifacts.
