<?php

namespace App\Http\Middleware;

use Closure;
use Encore\Admin\Facades\Admin;

class AuthenticateWeb
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure                 $next
     *
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        \config(['auth.defaults.guard' => 'admin']);
        $test = request();

        $redirectTo = admin_base_path(config('admin.auth.redirect_to', 'auth/login'));
        $test = $request->getRequestUri();  // Or any other value you want to pass

        // If the user is not authenticated, redirect to login and pass the $test variable as a query parameter
        if (Admin::guard()->guest() && !$this->shouldPassThrough($request)) {
            return redirect()->to($redirectTo . '?test=' . urlencode($test));
        }

        return $next($request);
    }

    /**
     * Determine if the request has a URI that should pass through verification.
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return bool
     */
    protected function shouldPassThrough($request)
    {
        // 下面的路由不验证登陆
        $excepts = config('admin.auth.excepts', []);

        array_delete($excepts, [
            '_handle_action_',
            '_handle_form_',
            '_handle_selectable_',
            '_handle_renderable_',
        ]);

        return collect($excepts)
            ->map('admin_base_path')
            ->contains(function ($except) use ($request) {
                if ($except !== '/') {
                    $except = trim($except, '/');
                }

                return $request->is($except);
            });
    }
}
