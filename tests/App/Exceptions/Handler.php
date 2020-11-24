<?php

namespace Saritasa\LaravelTestbed\Tests\App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Application exception handler.
 */
class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that should not be reported.
     *
     * @var string[]
     */
    protected $dontReport = [
        AuthenticationException::class,
        AuthorizationException::class,
        HttpException::class,
        ModelNotFoundException::class,
        TokenMismatchException::class,
        ValidationException::class,
    ];

    /**
     * Convert an authentication exception into an unauthenticated response.
     *
     * @param Request $request Current HTTP request
     * @param AuthenticationException $exception Exception risen during request processing
     *
     * @return Response
     */
    protected function unauthenticated($request, AuthenticationException $exception): Response
    {
        if ($request->expectsJson()) {
            return response()->json(['error' => 'Unauthenticated.'], Response::HTTP_UNAUTHORIZED);
        }

        return redirect()->guest('login');
    }
}
