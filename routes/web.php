<?php

use App\Http\Controllers\HomeController;
use App\Http\Controllers\PropertyController;
use App\Http\Controllers\ReservationController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ReviewController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AvatarSetupController;
use App\Http\Controllers\FavoriteController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\Owner\DashboardController as OwnerDashboard;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\SuggestionController;
use App\Http\Controllers\SupportController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\GeoController;
use Illuminate\Support\Facades\Route;

// Geo API (public)
Route::get('/geo/partidos', [GeoController::class, 'partidos'])->name('geo.partidos');
Route::get('/geo/localidades', [GeoController::class, 'localidades'])->name('geo.localidades');

// Public routes
Route::get('/', [HomeController::class, 'index'])->name('home');
Route::get('/propiedades', [PropertyController::class, 'index'])->name('properties.index');
Route::get('/propiedades/{propiedad:slug}', [PropertyController::class, 'show'])->name('properties.show');
Route::get('/api/cities', [PropertyController::class, 'citiesByProvince'])->name('api.cities');
Route::get('/favoritos/{propiedad:slug}/guardar', [FavoriteController::class, 'loginAndSave'])->name('favorites.login-and-save');

// Reservar (público — guarda en sesión si no está logueado)
Route::post('/propiedades/{propiedad:slug}/reservar', [ReservationController::class, 'store'])->name('reservations.store');

// Payment webhook (no auth needed)
Route::post('/webhooks/mercadopago', [PaymentController::class, 'webhook'])->name('payments.webhook');

// Avatar setup — auth required but NO avatar check (this IS the setup page)
Route::middleware('auth')->group(function () {
    Route::get('/completar-perfil', [AvatarSetupController::class, 'show'])->name('avatar.setup');
    Route::post('/completar-perfil', [AvatarSetupController::class, 'store'])->name('avatar.store');
});

// Authenticated user routes (avatar required)
Route::middleware(['auth', 'avatar'])->group(function () {
    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/perfil', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Reservations
    Route::get('/propiedades/{propiedad:slug}/reservar', [ReservationController::class, 'create'])->name('reservations.create');
Route::get('/mis-reservas', [ReservationController::class, 'myReservations'])->name('reservations.index');

    Route::middleware('can:viewOwn,reservation')->group(function () {
        Route::get('/mis-reservas/{reservation}', [ReservationController::class, 'show'])->name('reservations.show');
        Route::get('/mis-reservas/{reservation}/pago', [ReservationController::class, 'payment'])->name('reservations.payment');
        Route::post('/mis-reservas/{reservation}/cancelar', [ReservationController::class, 'cancel'])->name('reservations.cancel');
        Route::patch('/mis-reservas/{reservation}/servicios', [ReservationController::class, 'updateServices'])->name('reservations.services.update');
        Route::post('/mis-reservas/{reservation}/crear-preferencia', [PaymentController::class, 'createPreference'])->name('payments.create-preference');
        Route::get('/mis-reservas/{reservation}/pago/exito', [PaymentController::class, 'success'])->name('payments.success');
        Route::get('/mis-reservas/{reservation}/pago/fallo', [PaymentController::class, 'failure'])->name('payments.failure');
        Route::get('/mis-reservas/{reservation}/pago/pendiente', [PaymentController::class, 'pending'])->name('payments.pending');
    });

    // Favorites
    Route::get('/favoritos', [FavoriteController::class, 'index'])->name('favorites.index');
    Route::post('/favoritos/{propiedad:slug}', [FavoriteController::class, 'toggle'])->name('favorites.toggle');

    // Messages
    Route::get('/mensajes', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/mensajes/{user}', [MessageController::class, 'conversation'])->name('messages.conversation');
    Route::post('/mensajes/{user}', [MessageController::class, 'store'])->name('messages.store');

    // Reviews
    Route::post('/mis-reservas/{reservation}/resena', [ReviewController::class, 'store'])->middleware('can:viewOwn,reservation')->name('reviews.store');

    // Suggestions
    Route::get('/sugerencias', [SuggestionController::class, 'create'])->name('suggestions.create');
    Route::post('/sugerencias', [SuggestionController::class, 'store'])->name('suggestions.store');
});

// Subscription routes (owner/admin only)
Route::middleware(['auth', 'avatar'])->prefix('usuario')->name('subscription.')->group(function () {
    Route::get('/suscripcion/pagar', [SubscriptionController::class, 'redirectToMP'])->name('pay');
    Route::get('/suscripcion', [SubscriptionController::class, 'show'])->name('payment');
    Route::post('/suscripcion/crear-preferencia', [SubscriptionController::class, 'createPreference'])->name('create-preference');
    Route::get('/suscripcion/exito', [SubscriptionController::class, 'success'])->name('success');
    Route::get('/suscripcion/fallo', [SubscriptionController::class, 'failure'])->name('failure');
    Route::get('/suscripcion/pendiente', [SubscriptionController::class, 'pending'])->name('pending');
});

// Owner routes
Route::middleware(['auth', 'avatar'])->prefix('usuario')->name('owner.')->group(function () {
    Route::get('/panel', [OwnerDashboard::class, 'index'])->name('dashboard');
    Route::get('/propiedades', [OwnerDashboard::class, 'propiedadesList'])->name('properties.index');
    Route::get('/reservas', [OwnerDashboard::class, 'reservations'])->name('reservations');
    Route::get('/reservas/crear', [OwnerDashboard::class, 'createReservation'])->name('reservations.create');
    Route::post('/reservas', [OwnerDashboard::class, 'storeReservation'])->name('reservations.store');
    Route::get('/reservas/{reservation}', [OwnerDashboard::class, 'showReservation'])->name('reservations.show');
    Route::patch('/reservas/{reservation}/estado', [OwnerDashboard::class, 'updateReservation'])->name('reservations.update');
    Route::get('/propiedades/crear', [PropertyController::class, 'create'])->name('properties.create');
    Route::post('/propiedades', [PropertyController::class, 'store'])->name('properties.store');
    Route::delete('/imagenes/{image}', [PropertyController::class, 'destroyImage'])->name('properties.images.destroy');
    Route::get('/propiedades/{propiedad}/editar', [PropertyController::class, 'edit'])->name('properties.edit');
    Route::put('/propiedades/{propiedad}', [PropertyController::class, 'update'])->name('properties.update');
    Route::delete('/propiedades/{propiedad}', [PropertyController::class, 'destroy'])->name('properties.destroy');
    Route::patch('/propiedades/{propiedad}/toggle', [PropertyController::class, 'toggleStatus'])->name('properties.toggle');
});

// Admin routes
Route::middleware(['auth', 'avatar', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/panel', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/propiedades', [AdminController::class, 'propiedades'])->name('properties');
    Route::patch('/propiedades/{propiedad}/aprobar', [AdminController::class, 'approvePropiedad'])->name('properties.approve');
    Route::patch('/propiedades/{propiedad}/rechazar', [AdminController::class, 'rejectPropiedad'])->name('properties.reject');
    Route::get('/usuarios', [AdminController::class, 'users'])->name('users');
    Route::get('/resenas', [AdminController::class, 'reviews'])->name('reviews');
    Route::patch('/resenas/{review}/aprobar', [AdminController::class, 'approveReview'])->name('reviews.approve');
    Route::delete('/resenas/{review}', [AdminController::class, 'destroyReview'])->name('reviews.destroy');
    Route::get('/configuracion', [AdminController::class, 'settings'])->name('settings');
    Route::post('/configuracion', [AdminController::class, 'updateSettings'])->name('settings.update');
    Route::get('/suscripciones', [AdminController::class, 'subscriptionPayments'])->name('subscription-payments');
    Route::get('/soporte', [AdminController::class, 'supportTickets'])->name('support');
    Route::patch('/soporte/{ticket}/status', [AdminController::class, 'updateTicketStatus'])->name('support.status');
    Route::get('/sugerencias', [AdminController::class, 'suggestions'])->name('suggestions');
    Route::patch('/sugerencias/{suggestion}/status', [AdminController::class, 'updateSuggestionStatus'])->name('suggestions.status');
});

// Dashboard redirect
Route::get('/panel', function () {
    $user = auth()->user();
    if ($user->isAdmin()) return redirect()->route('admin.dashboard');
    if ($user->isOwner()) return redirect()->route('owner.dashboard');
    return redirect()->route('reservations.index');
})->middleware(['auth', 'avatar'])->name('dashboard');

// Redirect old English URLs to Spanish (SEO 301 redirects)
Route::redirect('/properties', '/propiedades', 301);
Route::redirect('/reservations', '/mis-reservas', 301);
Route::redirect('/favorites', '/favoritos', 301);
Route::redirect('/messages', '/mensajes', 301);
Route::redirect('/profile', '/perfil', 301);
Route::redirect('/dashboard', '/panel', 301);

// Support
Route::get('/soporte', [SupportController::class, 'create'])->name('support');
Route::post('/soporte', [SupportController::class, 'store'])->name('support.store');

require __DIR__.'/auth.php';
