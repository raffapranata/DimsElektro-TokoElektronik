<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Backend authorization for the admin panel. Hiding the admin menu is not
 * authorization; every admin route is wrapped in this middleware.
 */
class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user === null) {
            abort(401);
        }

        if (! $user->isAdmin()) {
            abort(403, 'Halaman ini hanya dapat diakses oleh administrator.');
        }

        return $next($request);
    }
}
