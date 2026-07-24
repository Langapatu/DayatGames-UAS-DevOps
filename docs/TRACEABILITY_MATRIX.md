# Traceability Matrix

| FR | Backlog | Implementasi aktual | Tabel | Test aktual | Screenshot | Status |
|---|---|---|---|---|---|---|
| FR-01 | PB-01 | register; `RegisteredUserController`; auth view | users | AuthenticationTest | 07 | Done |
| FR-02 | PB-02 | login/logout; `AuthenticatedSessionController` | users | AuthenticationTest | 06–07 | Done |
| FR-03 | PB-03 | `catalog.index`; `HomeController`, `CatalogController` | games, game_genre, genres | CustomerMarketplaceTest | 05, 17 | Done |
| FR-04 | PB-04 | search/genre/price/sort pada `CatalogController` | games, genres | CustomerMarketplaceTest | 19 | Done |
| FR-05 | PB-05 | `catalog.show`; detail/related/review | games, images, reviews, developers, publishers | CustomerMarketplaceTest | 18 | Done |
| FR-06 | PB-06 | `WishlistController` | wishlists, games, users | CustomerMarketplaceTest | 20 | Done |
| FR-07 | PB-07 | `CartController` | carts, cart_items, games | CustomerMarketplaceTest | 21 | Done |
| FR-08 | PB-08 | `CheckoutController`, `CheckoutRequest` | orders, order_items, payments, cart_items | TransactionWorkflowTest | 22–23 | Done |
| FR-09 | PB-09 | tiga metode, VA, proof upload | payments, orders | TransactionWorkflowTest | 24 | Done |
| FR-10 | PB-10 | `Customer\OrderController` owner scope | orders, order_items, payments | TransactionWorkflowTest | 23 | Done |
| FR-11 | PB-11 | `LibraryController`; verify transaction | libraries, games, orders | TransactionWorkflowTest | 26 | Done |
| FR-12 | PB-12 | `Customer\ReviewController`, `ReviewRequest` | reviews, libraries | TransactionWorkflowTest | 27 | Done |
| FR-13 | PB-13 | admin genres resource | genres, game_genre | AdminCatalogCrudTest | 15 | Done |
| FR-14 | PB-14 | admin publishers resource | publishers, games | AdminCatalogCrudTest | 16 | Done |
| FR-15 | PB-15 | admin developers resource | developers, games | AdminCatalogCrudTest | 09 | Done |
| FR-16 | PB-16 | admin games resource + upload/relasi/harga | games, game_genre, game_images | AdminCatalogCrudTest | 09–14 | Done |
| FR-17 | PB-17 | `Admin\OrderController` | orders, order_items, payments | TransactionWorkflowTest | 23, 30 | Done |
| FR-18 | PB-18 | `Admin\PaymentController` verify/reject | payments, orders, libraries, carts | TransactionWorkflowTest | 25 | Done |
| FR-19 | PB-19 | `Admin\ReviewController` | reviews | TransactionWorkflowTest | 27 | Done |
| FR-20 | PB-20 | `DashboardController` stats/top/recent | users, games, orders, payments, order_items | RoleAuthorizationTest + browser QA | 08 | Done |
