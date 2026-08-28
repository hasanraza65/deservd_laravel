<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Http\JsonResponse;

/** Keeps every API controller's JSON shape identical: {success, message, data|errors}. */
trait ApiResponses
{
    protected function success(mixed $data = null, string $message = 'Request successful.', int $status = 200): JsonResponse
    {
        return response()->json([
            'success' => true,
            'message' => $message,
            'data' => $data,
        ], $status);
    }

    protected function fail(string $message, int $status = 400, mixed $errors = null): JsonResponse
    {
        return response()->json([
            'success' => false,
            'message' => $message,
            'errors' => $errors ?? (object) [],
        ], $status);
    }
}
