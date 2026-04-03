<?php

namespace App\Http\Controllers;

use App\Models\SupportTicket;
use Illuminate\Http\Request;

class SupportController extends Controller
{
    public function create()
    {
        return view('support.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email'   => 'required|email|max:150',
            'phone'   => 'nullable|string|max:30',
            'subject' => 'required|string|max:150',
            'message' => 'required|string|min:10|max:3000',
        ]);

        SupportTicket::create([
            'user_id' => auth()->id(),
            'email'   => $data['email'],
            'phone'   => $data['phone'] ?? null,
            'subject' => $data['subject'],
            'message' => $data['message'],
        ]);

        return redirect()->route('support')
            ->with('success', 'Tu mensaje fue enviado. Te responderemos a la brevedad.');
    }
}
