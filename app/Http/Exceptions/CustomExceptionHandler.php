<?php

namespace App\Http\Exceptions;

use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\JsonResponse;
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
    public static function handle(Request $request, \Throwable $e): Response
    {
        $response = null;

        if ($e instanceof ValidationException) {
            $response = response()->json(
                [
                    'errorMsg' => 'Validation failed.',
                    'status' => Response::HTTP_UNPROCESSABLE_ENTITY,
                    'errors' => $e->errors(),
                ],
                Response::HTTP_UNPROCESSABLE_ENTITY
            );
        } elseif ($e instanceof MethodNotAllowedHttpException) {
            $response = response()->json(
                [
                    'errorMsg' => 'Method not allowed.',
                    'status' => Response::HTTP_METHOD_NOT_ALLOWED,
                    'body' => ['requestedUrl' => $request->getUri()],
                ],
                Response::HTTP_METHOD_NOT_ALLOWED
            );
        } elseif ($e instanceof ModelNotFoundException || $e instanceof NotFoundHttpException || $e instanceof RouteNotFoundException) {
            $response = response()->json(
                [
                    'errorMsg' => $e->getMessage() ?? 'Record not found.',
                    'status' => Response::HTTP_NOT_FOUND,
                    'body' => ['requestedUrl' => $request->getUri()],
                ],
                Response::HTTP_NOT_FOUND
            );
        } elseif ($e instanceof AuthenticationException) {
            $response = response()->json(
                [
                    'errorMsg' => 'Unauthenticated.',
                    'status' => Response::HTTP_UNAUTHORIZED,
                    'body' => ['requestedUrl' => $request->getUri()],
                ],
                Response::HTTP_UNAUTHORIZED
            );
        } elseif ($e instanceof HttpException) {
            $response = response()->json(
                [
                    'errorMsg' => $e->getMessage(),
                    'status' => $e->getStatusCode(),
                ],
                $e->getStatusCode()
            );
        } else {
            // Fallback for unsupported exception types
            $response = response()->json(
                [
                    'errorMsg' => 'An unexpected error occurred.',
                    'status' => Response::HTTP_INTERNAL_SERVER_ERROR,
                    'body' => ['requestedUrl' => $request->getUri()],
                    'exception' => $e->getMessage(),
                ],
                Response::HTTP_INTERNAL_SERVER_ERROR
            );
        }

        // Log exception details for debugging
        self::logException($request, $e);

        // Convert JsonResponse to Response if needed
        if ($response instanceof JsonResponse) {
            $response = new Response(
                $response->getContent(),
                $response->getStatusCode(),
                $response->headers->all()
            );
        }

        return $response;
    }

    private static function logException(Request $request, \Throwable $e): void
    {
        Log::error('Exception occurred', [
            'exception' => get_class($e),
            'message' => $e->getMessage(),
            'status' => method_exists($e, 'getStatusCode') ? $e->getStatusCode() : Response::HTTP_INTERNAL_SERVER_ERROR,
            'url' => $request->getUri(),
            'input' => $request->all(),
            'trace' => $e->getTraceAsString(),
        ]);
    }
}
