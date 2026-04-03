<?php

namespace App\Http\Middleware;

use App\Models\Setting;
use Closure;
use Illuminate\Http\Request;

class EnsureAvatarSet
{
    public function handle(Request $request, Closure $next)
    {
        if (
            auth()->check() &&
            !auth()->user()->avatar &&
            Setting::get('avatar_required', '1') === '1'
        ) {
            return redirect()->route('avatar.setup');
        }

        return $next($request);
    }
}
