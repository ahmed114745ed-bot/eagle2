<?php

namespace App\Exceptions;

use App\Helpers\Common;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array<int, class-string<Throwable>>
     */
    protected $dontReport = [
        ValidationException::class,

    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array<int, string>
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    // Render method to handle all exceptions
    public function render($request, Throwable $e): \Illuminate\Http\Response|\Illuminate\Http\JsonResponse|\Illuminate\Http\RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {

        if ($request->is('api/*')) {
            if ($e instanceof CValidationException) {
                $statusCode = method_exists($e, 'getStatusCode') ? $e->getStatusCode() : 422;
                return Common::apiResponse(0, $e->getMessage(), null, $statusCode);
            }elseif ($e instanceof AuthenticationException) {

                return Common::apiResponse (false,'Unauthenticated',[],401);
            } elseif ($e instanceof ModelNotFoundException) {

                return Common::apiResponse (false,'Wrong passed data',[],422);
            }

            if ($e instanceof HttpException) {
                $statusCode = $e->getStatusCode();

                if ($statusCode < 100 || $statusCode > 599) {
                    $statusCode = 500;
                }

                // Handle rate limiting (429) with a clear message
                if ($statusCode === 429) {
                    $retryAfter = $e->getHeaders()['Retry-After'] ?? null;
                    $data = $retryAfter ? ['retry_after' => $retryAfter] : null;
                    return Common::apiResponse(false, __('api_responses.too_many_requests'), $data, 429);
                }

                return Common::apiResponse(false, $e->getMessage(), null, $statusCode);
            }

            return Common::apiResponse(0, $e->getMessage(), null, 500);

        }
        // Handle validation exceptions


        return parent::render($request, $e);
    }

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            //
        });

        $this->renderable(function (AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return Common::apiResponse (false,'Unauthenticated',[],401);
            }
        });
    }
}
