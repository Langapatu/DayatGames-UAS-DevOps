# Traceability Matrix

Status awal seluruh item adalah Planned. Lokasi implementasi merupakan kontrak target dan akan diperbarui bila struktur aktual berubah.

| FR | Backlog | Route/controller/view target | Tabel | Test target | Screenshot target | Status |
|---|---|---|---|---|---|---|
| FR-01 | PB-01 | `register`; Auth controller; `auth/register` | users | RegistrationTest | 07-login-customer atau registrasi tambahan | Planned |
| FR-02 | PB-02 | `login`, `logout`; Auth controller; auth views | users | AuthenticationTest | 06-login-admin, 07-login-customer | Planned |
| FR-03 | PB-03 | `games.index`; CatalogController; `games/index` | games, game_genre, genres | CatalogTest | 17-katalog-customer | Planned |
| FR-04 | PB-04 | `games.index`; CatalogController | games, genres | CatalogFilterTest | 19-search-filter | Planned |
| FR-05 | PB-05 | `games.show`; CatalogController; `games/show` | games, images, reviews, developers, publishers | GameDetailTest | 18-detail-game | Planned |
| FR-06 | PB-06 | wishlist store/destroy; WishlistController | wishlists, games, users | WishlistTest | 20-wishlist | Planned |
| FR-07 | PB-07 | cart index/store/destroy; CartController | carts, cart_items, games | CartTest | 21-cart | Planned |
| FR-08 | PB-08 | checkout create/store; CheckoutController | orders, order_items, payments, cart_items | CheckoutTest | 22-checkout, 23-order | Planned |
| FR-09 | PB-09 | payment update/upload; PaymentController | payments, orders | PaymentSubmissionTest | 24-payment | Planned |
| FR-10 | PB-10 | orders index/show; OrderController | orders, order_items, payments | OrderAuthorizationTest | 23-order | Planned |
| FR-11 | PB-11 | library index; LibraryController | libraries, games, orders | PaymentVerificationTest | 26-library | Planned |
| FR-12 | PB-12 | reviews store/update; ReviewController | reviews, libraries | ReviewTest | 27-review | Planned |
| FR-13 | PB-13 | admin genres resource; Admin GenreController/views | genres, game_genre | AdminGenreTest | 15-crud-genre | Planned |
| FR-14 | PB-14 | admin publishers resource; Admin PublisherController/views | publishers, games | AdminPublisherTest | 16-crud-publisher | Planned |
| FR-15 | PB-15 | admin developers resource; Admin DeveloperController/views | developers, games | AdminDeveloperTest | Screenshot tambahan | Planned |
| FR-16 | PB-16 | admin games resource; Admin GameController/views | games, game_genre, game_images | AdminGameTest | 09–14 CRUD game | Planned |
| FR-17 | PB-17 | admin orders index/show/update; Admin OrderController | orders, order_items, payments | AdminOrderTest | 23-order | Planned |
| FR-18 | PB-18 | admin payments verify/reject; Admin PaymentController | payments, orders, libraries, carts | PaymentVerificationTest | 25-admin-verify-payment | Planned |
| FR-19 | PB-19 | admin reviews update; Admin ReviewController | reviews | AdminReviewTest | 27-review | Planned |
| FR-20 | PB-20 | admin dashboard; DashboardController/view | users, games, orders, payments, order_items | AdminDashboardTest | 08-dashboard-admin | Planned |

