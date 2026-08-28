<?php

namespace App\Exceptions;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * The list of the inputs that are never flashed to the session on validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
        'card_number',
        'card_cvc',
    ];

    public function register(): void
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        // Every domain exception below becomes the same {success, message,
        // errors} envelope as the rest of the API — a caller should never
        // need to special-case "was this a validation error or a business
        // rule error", and never sees a raw stack trace or internal message.
        $this->renderable(function (ValidationException $e, Request $request) {
            if ($this->wantsJson($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Validation failed.',
                    'errors' => $e->errors(),
                ], 422);
            }
        });

        $this->renderable(function (AuthenticationException $e, Request $request) {
            if ($this->wantsJson($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Unauthenticated.',
                    'errors' => (object) [],
                ], 401);
            }
        });

        $this->renderable(function (AuthorizationException $e, Request $request) {
            if ($this->wantsJson($request)) {
                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() ?: 'This action is unauthorized.',
                    'errors' => (object) [],
                ], 403);
            }
        });

        $this->renderable(function (ModelNotFoundException $e, Request $request) {
            if ($this->wantsJson($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The requested resource was not found.',
                    'errors' => (object) [],
                ], 404);
            }
        });

        $this->renderable(function (NotFoundHttpException $e, Request $request) {
            if ($this->wantsJson($request)) {
                return response()->json([
                    'success' => false,
                    'message' => 'The requested endpoint was not found.',
                    'errors' => (object) [],
                ], 404);
            }
        });

        $this->renderable(fn (InsufficientStockException $e, Request $request) => $this->domainError($e, $request, 422));
        $this->renderable(fn (InvalidCouponException $e, Request $request) => $this->domainError($e, $request, 422));
        $this->renderable(fn (InvalidBoxSelectionException $e, Request $request) => $this->domainError($e, $request, 422));
        $this->renderable(fn (CheckoutValidationException $e, Request $request) => $this->domainError($e, $request, 422));

        // Anything else, for a JSON request. Framework-level HTTP exceptions
        // (429 throttle, 413 payload-too-large, 405 method-not-allowed, ...)
        // already carry the right status code and a safe, public message —
        // that must be preserved, not flattened into a 500. Only a genuine
        // unexpected error (no HTTP status of its own) falls through to the
        // generic 500 branch, and only then do we hide the real message in
        // production (it can contain DB structure, file paths, or other internals).
        $this->renderable(function (Throwable $e, Request $request) {
            if (!$this->wantsJson($request) || $this->isHandledElsewhere($e)) {
                return null;
            }

            if ($e instanceof HttpExceptionInterface) {
                $status = $e->getStatusCode();
                $message = $status === 429
                    ? 'Too many requests. Please try again later.'
                    : ($e->getMessage() ?: 'Request failed.');

                return response()->json([
                    'success' => false,
                    'message' => $message,
                    'errors' => (object) [],
                ], $status);
            }

            $message = config('app.debug') ? $e->getMessage() : 'Something went wrong. Please try again.';

            return response()->json([
                'success' => false,
                'message' => $message,
                'errors' => (object) [],
            ], 500);
        });
    }

    private function domainError(Throwable $e, Request $request, int $status): ?JsonResponse
    {
        if (!$this->wantsJson($request)) {
            return null;
        }

        return response()->json([
            'success' => false,
            'message' => $e->getMessage(),
            'errors' => (object) [],
        ], $status);
    }

    private function wantsJson(Request $request): bool
    {
        return $request->expectsJson() || $request->is('api/*');
    }

    private function isHandledElsewhere(Throwable $e): bool
    {
        return $e instanceof ValidationException
            || $e instanceof AuthenticationException
            || $e instanceof AuthorizationException
            || $e instanceof ModelNotFoundException
            || $e instanceof NotFoundHttpException
            || $e instanceof InsufficientStockException
            || $e instanceof InvalidCouponException
            || $e instanceof InvalidBoxSelectionException
            || $e instanceof CheckoutValidationException;
    }
}
