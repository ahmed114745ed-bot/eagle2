<?php

namespace App\Admin\Middleware;

use Encore\Admin\Middleware\Pjax as BasePjax;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\DomCrawler\Crawler;

class PjaxOverride extends BasePjax
{
    /**
     * Send a response through this middleware.
     * Override to avoid using exit() which breaks Swoole
     *
     * @param Response $response
     * @return Response
     */
    public static function respond(Response $response)
    {
        $next = function () use ($response) {
            return $response;
        };

        return (new static())->handle(Request::capture(), $next);
    }
}
