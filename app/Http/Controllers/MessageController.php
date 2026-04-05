<?php

namespace App\Http\Controllers;

use App\Mail\NewMessageNotification;
use App\Models\Message;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class MessageController extends Controller
{
    private function ownerRequiresSubscription(): bool
    {
        $user = auth()->user();
        return $user->isOwner() && !$user->isAdmin() && !$user->hasSubscription();
    }

    // Inbox: lista de conversaciones
    public function index()
    {
        if ($this->ownerRequiresSubscription()) {
            return redirect()->route('subscription.payment')
                ->with('info', 'Necesitás activar tu suscripción para leer mensajes.');
        }

        $userId = auth()->id();

        // Obtener el último mensaje de cada conversación
        $conversations = Message::where('sender_id', $userId)
            ->orWhere('receiver_id', $userId)
            ->orderByDesc('created_at')
            ->get()
            ->groupBy(function ($msg) use ($userId) {
                return $msg->sender_id === $userId ? $msg->receiver_id : $msg->sender_id;
            })
            ->map(function ($messages) {
                return $messages->first();
            })
            ->values();

        // Cargar los usuarios de cada conversación
        $otherUserIds = $conversations->map(function ($msg) use ($userId) {
            return $msg->sender_id === $userId ? $msg->receiver_id : $msg->sender_id;
        });

        $users = User::whereIn('id', $otherUserIds)->get()->keyBy('id');

        return view('messages.index', compact('conversations', 'users', 'userId'));
    }

    // Conversacion con un usuario específico
    public function conversation(User $user)
    {
        if ($this->ownerRequiresSubscription()) {
            return redirect()->route('subscription.payment')
                ->with('info', 'Necesitás activar tu suscripción para leer y responder mensajes.');
        }

        $authId  = auth()->id();
        $authUser = auth()->user();
        $reservationId = request('reservation');

        // Solo marcar como leídos si el propietario tiene suscripción activa
        $ownerBlocked = $authUser->isOwner() && !$authUser->isAdmin() && !$authUser->hasSubscription();
        if (!$ownerBlocked) {
            Message::where('sender_id', $user->id)
                ->where('receiver_id', $authId)
                ->whereNull('read_at')
                ->update(['read_at' => now()]);
        }

        $messages = Message::where(function ($q) use ($authId, $user) {
                $q->where('sender_id', $authId)->where('receiver_id', $user->id);
            })
            ->orWhere(function ($q) use ($authId, $user) {
                $q->where('sender_id', $user->id)->where('receiver_id', $authId);
            })
            ->with(['sender', 'reservation.property'])
            ->orderBy('created_at')
            ->get();

        $reservation = $reservationId
            ? auth()->user()->reservations()->with('property')->find($reservationId)
              ?? $user->reservations()->with('property')->find($reservationId)
            : null;

        $authId = auth()->id();
        $messagesData = $messages->map(fn($m) => [
            'id'             => $m->id,
            'body'           => $m->body,
            'mine'           => $m->sender_id === $authId,
            'avatar'         => $m->sender->avatar_url,
            'created_at'     => $m->created_at->format('d/m H:i'),
            'reservation_id' => $m->reservation_id,
            'read_at'        => $m->read_at,
        ])->values();

        return view('messages.conversation', compact('user', 'messages', 'messagesData', 'reservation', 'ownerBlocked'));
    }

    // Enviar mensaje
    public function store(Request $request, User $user)
    {
        $request->validate(['body' => 'required|string|max:1000']);

        // Propietarios sin suscripción no pueden enviar mensajes
        $authUser = auth()->user();
        if ($authUser->isOwner() && !$authUser->isAdmin() && !$authUser->hasSubscription()) {
            if ($request->expectsJson()) {
                return response()->json(['error' => 'Suscripción requerida para enviar mensajes.'], 403);
            }
            return back()->with('error', 'Necesitás activar tu suscripción para enviar mensajes.');
        }

        $message = Message::create([
            'sender_id'      => auth()->id(),
            'receiver_id'    => $user->id,
            'reservation_id' => $request->reservation_id ?: null,
            'body'           => $request->body,
        ]);

        try {
            Mail::to($user->email)->send(new NewMessageNotification($user, auth()->user()));
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::error('[Message] Mail failed', ['error' => $e->getMessage()]);
        }

        if ($request->expectsJson()) {
            return response()->json([
                'id'         => $message->id,
                'body'       => $message->body,
                'created_at' => $message->created_at->format('d/m H:i'),
            ]);
        }

        return back();
    }
}
