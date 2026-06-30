<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Models\User;
use App\Notifications\UsuarioReactivacion;


// Controllers
use App\Http\Controllers\Public\WishlistController;
use App\Http\Controllers\Public\ChatbotController;
use App\Http\Controllers\Public\ProductController;

// Público
use App\Http\Controllers\Public\HomeController;
use App\Http\Controllers\Public\CartController;
use App\Http\Controllers\Auth\GoogleController;

// Cliente
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\BoletaController as CustomerBoletaController;

// Checkout / pagos
use App\Http\Controllers\Public\CheckoutController;
use App\Http\Controllers\PaymentDemoController;

// Admin
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\BrandController as AdminBrandController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\BillingController;
use App\Http\Controllers\Admin\OrderController;

// Mercado Pago
use App\Http\Controllers\Payment\MercadoPagoController;
use App\Http\Controllers\Payment\MercadoPagoWebhookController;

// Models
use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;

/*
|--------------------------------------------------------------------------
| MODEL BINDINGS
|--------------------------------------------------------------------------
*/
Route::bind('brand', fn($v) => Brand::findOrFail($v));
Route::bind('category', fn($v) => Category::findOrFail($v));
Route::bind('product', fn($v) => Product::findOrFail($v));

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS
|--------------------------------------------------------------------------
*/
Route::get('/products', [ProductController::class, 'index'])
    ->name('products');
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])
    ->name('wishlist.toggle');
Route::post('/wishlist/', [WishlistController::class, 'index'])
    ->middleware('auth')
    ->name('wishlist.index');
/*
|--------------------------------------------------------------------------
CHATBOT
|--------------------------------------------------------------------------
*/
Route::post('/chatbot', [ChatbotController::class, 'chat']);

Route::get('/', [HomeController::class, 'index'])->name('home');

Route::view('/nosotros', 'nosotros')->name('nosotros');
Route::view('/ubicanos', 'ubicanos')->name('ubicanos');

/*
|--------------------------------------------------------------------------
| CARRITO
|--------------------------------------------------------------------------
*/

Route::prefix('cart')->name('cart.')->group(function () {

    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::patch('/{productId}', [CartController::class, 'update'])->name('update');
    Route::delete('/{productId}', [CartController::class, 'remove'])->name('remove');
    Route::delete('/', [CartController::class, 'clear'])->name('clear');

});

/*
|--------------------------------------------------------------------------
| GOOGLE AUTH
|--------------------------------------------------------------------------
*/

Route::prefix('auth/google')->name('auth.google.')->group(function () {

    Route::get('/login', [GoogleController::class, 'redirectLogin'])
        ->name('login');

    Route::get('/register', [GoogleController::class, 'redirectRegister'])
        ->name('register');

    Route::get('/callback', [GoogleController::class, 'callback'])
        ->name('callback');

});

/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

Route::post('/logout', function (Request $request) {

    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('home');

})->middleware('auth')->name('logout');

/*
|--------------------------------------------------------------------------
| VERIFICACIÓN DE CORREO
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {

        $request->fulfill();

        return redirect()
            ->route('customer.dashboard')
            ->with('success', 'Correo verificado correctamente');

    })->middleware('signed')->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {

        $request->user()->sendEmailVerificationNotification();

        return back()->with('message', 'Correo reenviado');

    })->middleware('throttle:6,1')->name('verification.send');

});

Route::get('/reactivar-test', function () {

    $usuarios = User::whereNotNull('email')
        ->where('email', 'like', '%@%') // básico
        ->get();

    foreach ($usuarios as $user) {

        // Validar email correctamente
        if (!filter_var($user->email, FILTER_VALIDATE_EMAIL)) {
            continue; // saltar correos inválidos
        }

        $productos = Product::inRandomOrder()->take(3)->get();

        $user->notify(new UsuarioReactivacion($productos));
    }

    return "Correos enviados";
});

/*
|--------------------------------------------------------------------------
| CLIENTE (VERIFICADO)
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'verified'])->group(function () {

    Route::get('/cliente', [CustomerDashboardController::class, 'index'])
        ->name('customer.dashboard');

    Route::post('/cliente/foto', [CustomerDashboardController::class, 'updatePhoto'])
        ->name('customer.photo.update');

    Route::get('/cliente/pedidos/{order}/boleta', [CustomerBoletaController::class, 'download'])
        ->name('customer.boleta.download');

    Route::view('/profile', 'profile')->name('profile');

    Route::view('/mis-productos', 'customer.products')
        ->name('customer.products');

});

/*
|--------------------------------------------------------------------------
| ADMIN
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('admin.')
    ->middleware(['auth', 'verified'])
    ->group(function () {

        Route::get('/dashboard', [AdminDashboardController::class, 'index'])
            ->name('dashboard');

        Route::resource('/categories', AdminCategoryController::class);
        Route::resource('/brands', AdminBrandController::class);
        Route::resource('/products', AdminProductController::class);
        Route::resource('/users', AdminUserController::class);

        Route::get('/reports', [ReportController::class, 'index'])
            ->name('reports.index');

        Route::get('/orders', [OrderController::class, 'index'])
            ->name('orders.index');

        Route::get('/billing', [BillingController::class, 'index'])
            ->name('billing.index');
    });

/*
|--------------------------------------------------------------------------
| CHECKOUT (PUENTE PARA FRONTEND)
|--------------------------------------------------------------------------
*/

Route::get('/checkout', [CheckoutController::class, 'index'])
    ->middleware('auth', 'verified')
    ->name('checkout');

Route::post('/checkout', [CheckoutController::class, 'store'])
    ->middleware('auth', 'verified')
    ->name('checkout.store');

/*
|--------------------------------------------------------------------------
| MERCADO PAGO
|--------------------------------------------------------------------------
*/
Route::get('/pagos/mercadopago', [MercadoPagoController::class, 'index'])
    ->middleware('auth', 'verified')
    ->name('mp.checkout');
Route::post('/pagos/crear-preferencia', [MercadoPagoController::class, 'createPreference'])
    ->middleware('auth', 'verified')
    ->name('mp.preference');
Route::get('/pagos/exito', [MercadoPagoController::class, 'success'])
    ->name('mp.success');

Route::get('/pagos/pendiente', [MercadoPagoController::class, 'pending'])
    ->name('mp.pending');

Route::get('/pagos/error', [MercadoPagoController::class, 'failure'])
    ->name('mp.failure');

Route::post('/webhooks/mercadopago', [MercadoPagoWebhookController::class, 'handle'])
    ->name('mp.webhook');

/*
|--------------------------------------------------------------------------
| AUTH SYSTEM
|--------------------------------------------------------------------------
*/

require __DIR__ . '/auth.php';