<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

// Público / Base
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\Auth\GoogleController;

// Cliente autenticado
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\BoletaController as CustomerBoletaController;

// Checkout propio
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\PaymentDemoController;

// Admin
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\BrandController     as AdminBrandController;
use App\Http\Controllers\Admin\ProductController   as AdminProductController;
use App\Http\Controllers\Admin\UserController      as AdminUserController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\Admin\OrderController;



// Mercado Pago (namespace correcto)
use App\Http\Controllers\Payment\MercadoPagoController;
use App\Http\Controllers\Payment\MercadoPagoWebhookController;

// Bindings (clave primaria flexible)
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;

Route::bind('brand',    fn ($v) => Brand::query()->whereKey($v)->firstOrFail());
Route::bind('category', fn ($v) => Category::query()->whereKey($v)->firstOrFail());
Route::bind('product',  fn ($v) => Product::query()->whereKey($v)->firstOrFail());

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS
|--------------------------------------------------------------------------
*/
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::view('/nosotros', 'nosotros')->name('nosotros');
Route::view('/ubicanos', 'ubicanos')->name('ubicanos');

// Carrito (por sesión)
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/',           [CartController::class, 'index'])->name('index');
    Route::post('/add',       [CartController::class, 'add'])->name('add');
    Route::patch('/{rowId}',  [CartController::class, 'update'])->name('update');
    Route::delete('/{rowId}', [CartController::class, 'remove'])->name('remove');
    Route::delete('/',        [CartController::class, 'clear'])->name('clear');
});

// Google OAuth
Route::prefix('auth/google')->name('auth.google.')->group(function () {
    Route::get('/redirect', [GoogleController::class, 'redirect'])->name('redirect');
    Route::get('/callback', [GoogleController::class, 'callback'])->name('callback');
});

// Logout
Route::post('/logout', function (Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect()->route('home');
})->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| RUTAS AUTENTICADAS (CLIENTE)
|--------------------------------------------------------------------------
*/
Route::middleware('auth')->group(function () {
    Route::get('/cliente', [CustomerDashboardController::class, 'index'])
        ->name('customer.dashboard');

    Route::post('/cliente/foto', [CustomerDashboardController::class, 'updatePhoto'])
        ->name('customer.photo.update');

    // ✅ BOLETA (CLIENTE)
    Route::get('/cliente/pedidos/{order}/boleta', [CustomerBoletaController::class, 'download'])
        ->name('customer.boleta.download');

    Route::view('/profile', 'profile')->name('profile');
    Route::view('/mis-productos', 'products')->name('customer.products');

    Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
    Route::post('/wishlist/add/{product}',      [WishlistController::class, 'store'])->name('wishlist.add');
    Route::delete('/wishlist/remove/{product}', [WishlistController::class, 'destroy'])->name('wishlist.remove');
    Route::post('/wishlist/toggle',             [WishlistController::class, 'toggle'])->name('wishlist.toggle');

    Route::get('/checkout',  [CheckoutController::class, 'index'])->name('checkout');
    Route::post('/checkout', [CheckoutController::class, 'process'])->name('checkout.process');

    Route::get('/payments/redirect', [PaymentDemoController::class, 'redirect'])->name('payments.redirect');
    Route::get('/payments/response', [PaymentDemoController::class, 'response'])->name('payments.response');
});


/*
|--------------------------------------------------------------------------
| ÁREA ADMIN
|--------------------------------------------------------------------------
*/
Route::middleware(['auth','admin'])->prefix('admin')->name('admin.')->group(function () {

    // Dashboard
    Route::get('/dashboard', [AdminDashboardController::class, 'index'])->name('dashboard');

    // CRUDs
    Route::resource('categories', AdminCategoryController::class)->except(['show']);
    Route::resource('brands',     AdminBrandController::class)->except(['show']);
    Route::resource('products',   AdminProductController::class)->except(['show']);
    Route::resource('users',      AdminUserController::class)->except(['show']);

// Reportes Excel + JSON
Route::prefix('reports')->name('reports.')->group(function () {

    // gráfico dashboard
    Route::get(
        '/revenue.json',
        [ReportController::class, 'revenueJson']
    )->name('revenue.json');

    // ventas por fechas
    Route::get(
        '/sales-by-date',
        [ReportController::class, 'salesByDate']
    )->name('sales-by-date');

    // productos más vendidos
    Route::get(
        '/best',
        [ReportController::class, 'bestSellersExcel']
    )->name('best');

    // productos menos vendidos
    Route::get(
        '/least',
        [ReportController::class, 'leastSellers']
    )->name('least');

    // inventario crítico
    Route::get(
        '/inventory',
        [ReportController::class, 'criticalInventory']
    )->name('inventory');

    // ventas por categoría
    Route::get(
        '/category',
        [ReportController::class, 'salesByCategory']
    )->name('category');
});

    // Billing (Boletas / Facturas)
    Route::get('/billing',         [BillingController::class, 'index'])->name('billing.index');
    Route::post('/billing/lookup', [BillingController::class, 'lookup'])->name('billing.lookup');
    Route::post('/billing/pdf',    [BillingController::class, 'pdf'])->name('billing.pdf');

    // Órdenes
    Route::resource('orders', OrderController::class)->only(['index','show'])->names('orders');
    Route::patch('/orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
});

/*
|--------------------------------------------------------------------------
| MERCADO PAGO (Checkout Pro / Preferencias / Back URLs / Webhook)
|--------------------------------------------------------------------------
*/

// Página de checkout con el botón
Route::get('/pagos/checkout', [MercadoPagoController::class, 'checkout'])->name('mp.checkout');

// Crear preferencia (POST)
Route::post('/pagos/crear-preferencia', [MercadoPagoController::class, 'createPreference'])->name('mp.preference');

// Retornos (back_urls)
Route::get('/pagos/exito',     [MercadoPagoController::class, 'success'])->name('mp.success');
Route::get('/pagos/pendiente', [MercadoPagoController::class, 'pending'])->name('mp.pending');
Route::get('/pagos/error',     [MercadoPagoController::class, 'failure'])->name('mp.failure');

// Webhook (notifications) — público
// Si usas CSRF, recuerda excluir esta ruta en VerifyCsrfToken::$except.
Route::post('/webhooks/mercadopago', [MercadoPagoWebhookController::class, 'handle'])->name('mp.webhook');

use Illuminate\Foundation\Auth\EmailVerificationRequest;

/*
|--------------------------------------------------------------------------
| VERIFICACIÓN DE CORREO
|--------------------------------------------------------------------------
*/

Route::middleware(['auth','verified'])->group(function () {

    // Pantalla: "Verifica tu correo"
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    // Cuando el usuario hace clic en el enlace del correo
    Route::get('/email/verify/{id}/{hash}',
        function (EmailVerificationRequest $request) {

            $request->fulfill();

            return redirect()->route('customer.dashboard');

    })->middleware(['signed'])
      ->name('verification.verify');

    // Reenviar correo
    Route::post('/email/verification-notification',
        function (Request $request) {

            $request->user()
                ->sendEmailVerificationNotification();

            return back()->with(
                'message',
                'Correo reenviado correctamente'
            );

    })->middleware(['throttle:6,1'])
      ->name('verification.send');
});

/*
|--------------------------------------------------------------------------
| Auth scaffolding (Breeze/Fortify/etc.)
|--------------------------------------------------------------------------
*/
require __DIR__.'/auth.php';
