use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

public function render($request, Throwable $e)
{
    if ($request->expectsJson()) {

        if ($e instanceof ValidationException) {
            return response()->json([
                'error' => 'VALIDATION_ERROR',
                'message' => 'Validation failed',
                'details' => $e->errors(),
            ], 422);
        }

        if ($e instanceof NotFoundHttpException) {
            return response()->json([
                'error' => 'NOT_FOUND',
                'message' => 'Resource not found',
            ], 404);
        }

        if ($e instanceof HttpExceptionInterface && $e->getStatusCode() === 409) {
            return response()->json([
                'error' => 'BUSINESS_RULE_VIOLATION',
                'message' => $e->getMessage(),
            ], 409);
        }

        return response()->json([
            'error' => 'INTERNAL_SERVER_ERROR',
            'message' => 'Unexpected error',
        ], 500);
    }

    return parent::render($request, $e);
}

