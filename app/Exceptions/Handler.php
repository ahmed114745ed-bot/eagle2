<?php

namespace App\Exceptions;

use App\Helpers\Common;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Illuminate\Validation\ValidationException;
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
        // Handle missing table exceptions
        if (MissingTableHandler::isMissingTableException($e)) {
            MissingTableHandler::handle($e);

            if ($request->is('api/*')) {
                return Common::apiResponse(0, 'Service temporarily unavailable. Please try again later.', null, 503);
            }

            // For web requests, show a friendly error page or return empty response
            return response()->view('errors.503', [], 503);
        }

        if ($request->is('api/*')) {
            if ($e instanceof CValidationException) {
                return Common::apiResponse(0, $e->getMessage(), null, 422);
            }elseif ($e instanceof AuthenticationException) {

                return Common::apiResponse (false,'Unauthenticated',[],401);
            } elseif ($e instanceof ModelNotFoundException) {

                return Common::apiResponse (false,'Wrong passed data',[],422);
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
