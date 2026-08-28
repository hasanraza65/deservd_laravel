<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * This app has no 'login' web route — it's an API + a separately
     * session-guarded Blade admin panel (see Http/Controllers/Admin/AuthController).
     * Every /api/* request must get a clean 401 JSON response, never a
     * redirect attempt that blows up on route('login') not existing. Relying
     * on expectsJson() alone isn't enough: a plain curl/fetch call without an
     * explicit Accept header returns false there even though it's still an
     * API request.
     */
    protected function redirectTo(Request $request): ?string
    {
        if ($request->expectsJson() || $request->is('api/*')) {
            return null;
        }

        return route('admin.login');
    }
}
