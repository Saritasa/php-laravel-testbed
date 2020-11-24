<?php

namespace Saritasa\LaravelTestbed\Tests\App\Http\Middleware;

use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate
{
    /**
     * Handle an incoming request.
     *
     * @param Request $request current request
     * @param Closure $next next middleware function
     * @param string|null $guard Name of security guard to check user permissions
     *
     * @return mixed
     *
     * @throws AuthenticationException
     */
    public function handle(Request $request, Closure $next, ?string $guard = null)
    {
        if (Auth::guard($guard)->guest()) {
            throw new AuthenticationException();
        }

        return $next($request);
    }
}
