<?php

namespace App\Exceptions;

use Illuminate\Foundation\Exceptions\Handler as ExceptionHandler;
use Symfony\Component\HttpFoundation\File\Exception\AccessDeniedException;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Throwable;

class Handler extends ExceptionHandler
{
    /**
     * A list of the exception types that are not reported.
     *
     * @var array
     */
    protected $dontReport = [
        //
    ];

    /**
     * A list of the inputs that are never flashed for validation exceptions.
     *
     * @var array
     */
    protected $dontFlash = [
        'current_password',
        'password',
        'password_confirmation',
    ];

    /**
     * Register the exception handling callbacks for the application.
     *
     * @return void
     */
    public function register()
    {
        $this->reportable(function (Throwable $e) {
            dd($e);
        });

        $this->renderable(function (NotFoundHttpException $e, $request) {
            return response()->json(["message"=>"Route Not Found",
            
            "status"=>0,
            "status_code"=>404,
        ],404);
        });

        $this->renderable(function (AccessDeniedHttpException $e, $request) {
            return response()->json(["message"=>"Unauthorized",
            
            "status"=>0,
            "status_code"=>403,
        ],403);
        });
    }

    
}
