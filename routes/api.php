<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WhatsAppController;

Route::post('/whatsapp/webhook', [WhatsAppController::class, 'webhook']);

