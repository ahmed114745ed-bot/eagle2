<?php

namespace App\Exceptions;

use App\Helpers\Common;
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

        if ($request->is('api/*')) {
            if ($e instanceof CValidationException) {
                return Common::apiResponse(0, $e->getMessage(), null, 422);
            }elseif ($e instanceof \Illuminate\Auth\AuthenticationException) {

                return Common::apiResponse (false,'Unauthenticated',[],401);
            }

            \Log::error('Error occurred: ' . $e->getMessage(), [
                'url' => $request->fullUrl(),
                'input' => $request->all(),
                'trace' => $e->getTraceAsString(),
            ]);
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

        $this->renderable(function (\Illuminate\Auth\AuthenticationException $e, $request) {
            if ($request->is('api/*')) {
                return Common::apiResponse (false,'Unauthenticated',[],401);
            }
        });
    }
}
