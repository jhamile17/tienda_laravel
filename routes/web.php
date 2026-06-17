<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\ChatbotController;

// Público
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\WishlistController;
use App\Http\Controllers\Auth\GoogleController;

// Cliente
use App\Http\Controllers\Customer\DashboardController as CustomerDashboardController;
use App\Http\Controllers\Customer\BoletaController as CustomerBoletaController;

// Checkout
use App\Http\Controllers\CheckoutController;
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

use App\Models\Brand;
use App\Models\Category;
use App\Models\Product;

Route::bind('brand', fn($v)=>Brand::findOrFail($v));
Route::bind('category', fn($v)=>Category::findOrFail($v));
Route::bind('product', fn($v)=>Product::findOrFail($v));

/*
|--------------------------------------------------------------------------
| RUTAS PÚBLICAS
|--------------------------------------------------------------------------
*/
Route::post('/wishlist/toggle', [WishlistController::class, 'toggle'])
    ->name('wishlist.toggle');

Route::get('/chatbot', function () {
    return view('chatbot');
});

Route::post('/chatbot/send', [
    ChatbotController::class,
    'send'
]);

Route::get('/', [HomeController::class,'index'])->name('home');

Route::view('/nosotros','nosotros')->name('nosotros');
Route::view('/ubicanos','ubicanos')->name('ubicanos');

/*
|--------------------------------------------------------------------------
| CARRITO
|--------------------------------------------------------------------------
*/

Route::prefix('cart')->name('cart.')->group(function(){

    Route::get('/',[CartController::class,'index'])->name('index');

    Route::post('/add',[CartController::class,'add'])->name('add');

    Route::patch('/{rowId}',
        [CartController::class,'update']
    )->name('update');

    Route::delete('/{rowId}',
        [CartController::class,'remove']
    )->name('remove');

    Route::delete('/',
        [CartController::class,'clear']
    )->name('clear');
});

/*
|--------------------------------------------------------------------------
| GOOGLE LOGIN
|--------------------------------------------------------------------------
*/

Route::prefix('auth/google')
->name('auth.google.')
->group(function(){

    Route::get('/redirect',
        [GoogleController::class,'redirect']
    )->name('redirect');

    Route::get('/callback',
        [GoogleController::class,'callback']
    )->name('callback');

});

/*
|--------------------------------------------------------------------------
| LOGOUT
|--------------------------------------------------------------------------
*/

Route::post('/logout',function(Request $request){

    Auth::logout();

    $request->session()->invalidate();

    $request->session()->regenerateToken();

    return redirect()->route('home');

})->middleware('auth')
->name('logout');


/*
|--------------------------------------------------------------------------
| VERIFICACIÓN DE CORREO
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function(){

    // Página "Verifica tu correo"

    Route::get('/email/verify',function(){

        return view('auth.verify-email');

    })->name('verification.notice');


    // Usuario hace clic en el correo

    Route::get(
        '/email/verify/{id}/{hash}',

        function(
            EmailVerificationRequest $request
        ){

            $request->fulfill();

            return redirect()
                ->route('customer.dashboard')
                ->with(
                    'success',
                    'Correo verificado correctamente'
                );

        }

    )->middleware('signed')
     ->name('verification.verify');


    // Reenviar correo

    Route::post(
        '/email/verification-notification',

        function(Request $request){

            $request->user()
                ->sendEmailVerificationNotification();

            return back()->with(
                'message',
                'Correo reenviado'
            );

        }

    )->middleware('throttle:6,1')
    ->name('verification.send');

});


/*
|--------------------------------------------------------------------------
| CLIENTE (SOLO SI ESTÁ VERIFICADO)
|--------------------------------------------------------------------------
*/

Route::middleware([
    'auth',
    'verified'
])->group(function(){

    Route::get(
        '/cliente',
        [CustomerDashboardController::class,'index']
    )->name('customer.dashboard');

    Route::post(
        '/cliente/foto',
        [CustomerDashboardController::class,'updatePhoto']
    )->name('customer.photo.update');

    Route::get(
        '/cliente/pedidos/{order}/boleta',
        [CustomerBoletaController::class,'download']
    )->name('customer.boleta.download');

    Route::view(
        '/profile',
        'profile'
    )->name('profile');

    Route::view(
        '/mis-productos',
        'products'
    )->name('customer.products');

});


/*
|--------------------------------------------------------------------------
| MERCADO PAGO
|--------------------------------------------------------------------------
*/

Route::get(
    '/pagos/checkout',
    [MercadoPagoController::class,'checkout']
)->name('mp.checkout');

Route::post(
    '/pagos/crear-preferencia',
    [MercadoPagoController::class,'createPreference']
)->name('mp.preference');

Route::get(
    '/pagos/exito',
    [MercadoPagoController::class,'success']
)->name('mp.success');

Route::get(
    '/pagos/pendiente',
    [MercadoPagoController::class,'pending']
)->name('mp.pending');

Route::get(
    '/pagos/error',
    [MercadoPagoController::class,'failure']
)->name('mp.failure');

Route::post(
    '/webhooks/mercadopago',
    [MercadoPagoWebhookController::class,'handle']
)->name('mp.webhook');


require __DIR__.'/auth.php';