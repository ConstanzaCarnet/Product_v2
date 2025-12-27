<?php

namespace App\Exceptions;

use Throwable;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;
use App\Http\Responses\ApiResponse;

class Handler extends ExceptionHandler
{
    public function render($request, Throwable $e)
    {
        if ($request->expectsJson()) {

            if ($e instanceof ValidationException) {
                return ApiResponse::error(
                    'Validation error',
                    422,
                    $e->errors()
                );
            }

            if ($e instanceof NotFoundHttpException) {
                return ApiResponse::error(
                    'Resource not found',
                    404
                );
            }

            if ($e instanceof HttpExceptionInterface) {
                return ApiResponse::error(
                    'The request could not be processed because of conflict in the current state of the resource',
                    409
                );
            }

            return response()->json([
                'error' => 'INTERNAL_SERVER_ERROR',
                'message' => 'Unexpected error',
            ], 500);
        }

        return parent::render($request, $e);
    }
}
