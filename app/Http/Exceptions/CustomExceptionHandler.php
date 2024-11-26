<?php

namespace App\Http\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Exception\RouteNotFoundException;

class CustomExceptionHandler
{
    /**
     * Handle an incoming exception and return a proper response.
     */
    public static function handle(Request $request, \Throwable $e): \Illuminate\Http\JsonResponse
    {
        $statusCode = self::getStatusCode($e);
        $errorResponse = self::buildErrorResponse($request, $e, $statusCode);

        self::logException($request, $e, $statusCode);

        return response()->json($errorResponse, $statusCode);
    }

    /**
     * Determine the HTTP status code based on the exception type.
     */
    private static function getStatusCode(\Throwable $e): int
    {
        return match (true) {
            $e instanceof ValidationException => Response::HTTP_UNPROCESSABLE_ENTITY,
            $e instanceof MethodNotAllowedHttpException => Response::HTTP_METHOD_NOT_ALLOWED,
            $e instanceof ModelNotFoundException,
            $e instanceof NotFoundHttpException,
            $e instanceof RouteNotFoundException => Response::HTTP_NOT_FOUND,
            $e instanceof AuthenticationException => Response::HTTP_UNAUTHORIZED, // 401
            $e instanceof HttpException => $e->getStatusCode(),
            default => Response::HTTP_INTERNAL_SERVER_ERROR,
        };
    }

    /**
     * Build the error response payload.
     */
    private static function buildErrorResponse(Request $request, \Throwable $e, int $statusCode): array
    {
        $errorMsg = match (true) {
            $e instanceof ValidationException => 'Validation failed.',
            $e instanceof MethodNotAllowedHttpException => 'Method not allowed.',
            $e instanceof ModelNotFoundException,
            $e instanceof NotFoundHttpException,
            $e instanceof RouteNotFoundException => $e->getMessage() ?: 'Record not found.',
            $e instanceof AuthenticationException => self::getAuthenticationErrorMessage($e),
            $e instanceof HttpException => $e->getMessage(),
            default => 'An unexpected error occurred.',
        };

        $response = [
            'errorMsg' => $errorMsg,
            'status' => $statusCode,
        ];

        if ($e instanceof ValidationException) {
            $response['errors'] = $e->errors();
        }

        if ($statusCode === Response::HTTP_INTERNAL_SERVER_ERROR) {
            $response['body'] = ['requestedUrl' => $request->getUri()];
            $response['exception'] = $e->getMessage();
        }

        return $response;
    }

    /**
     * Log exception details for debugging.
     */
    private static function logException(Request $request, \Throwable $e, int $statusCode): void
    {
        Log::error('Exception occurred', [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'status' => $statusCode,
            'url' => $request->getUri(),
            'input' => $request->all(),
            'trace' => $e->getTraceAsString(),
        ]);
    }

    /**
     * Generate a custom message for AuthenticationException based on its context.
     */
    private static function getAuthenticationErrorMessage(AuthenticationException $e): string
    {
        $guards = $e->guards();

        if (in_array('sanctum', $guards)) {
            return 'Protected by Sanctum: Invalid or missing token.';
        }

        return 'Unauthenticated.';
    }
}
