<?php

namespace App\Http\Middleware;

use Closure;
use Encore\Admin\Facades\Admin;
use Illuminate\Support\Str;

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
        $uri = $request->path();

        $user = Admin::user();

        $adminLogin = 'admin/login';
        $bdLogin = 'bd/login';
        $superadminLogin = 'superadmin/login';

        if ($user) {
            if (Str::contains($uri, $adminLogin) && $user?->type !== 'bd' && $user?->type !== 'superadmin') {
                return redirect('/admin');
            }

            if (Str::contains($uri, $bdLogin) && $user?->type == 'bd') {
                return redirect('/bd');
            }

            if (Str::contains($uri, $superadminLogin) && $user?->type == 'superadmin') {
                return redirect('/superadmin');
            }
        }
        $redirectTo = admin_base_path(config('admin.auth.redirect_to', 'auth/login'));
        $test = $request->getRequestUri();  // Or any other value you want to pass
        if (Str::contains($uri, 'bd')) {
            $redirectTo = '/bd/login';
        }

        if (Str::contains($uri, 'superadmin')) {
            $redirectTo = '/superadmin/login';
        }

        // If the user is not authenticated, redirect to login and pass the $test variable as a query parameter
        if (Admin::guard()->guest() && !$this->shouldPassThrough($request)) {
            return redirect()->to($redirectTo . '?redirect_url=' . urlencode($test));
        }


        // if (
        //     (Str::contains($uri, 'bd') && $user?->type != 'bd') ||
        //     (Str::contains($uri, 'admin') && $user?->type == 'bd')
        // ) {
        //     if ($user->type === 'bd') {
        //         $redirectUrl = url('/bd');
        //         $logoutRoute = 'bd.logout';
        //     } else {
        //         $redirectUrl = url('/admin');
        //         $logoutRoute = 'admin.logout';
        //     }

        //     return response()->view('auth.unauthorized', [
        //         'redirect_url' => $redirectUrl,
        //         'logout_route' => $logoutRoute,
        //     ], 403);
        // }

        if (
            (Str::contains($uri, 'bd') && $user?->type !== 'bd') ||
            (Str::contains($uri, 'admin') && $user?->type === 'bd') ||
            (Str::contains($uri, 'superadmin') && $user?->type !== 'superadmin')
        ) {
            if ($user->type === 'bd') {
                return redirect('/bd');
            } elseif ($user->type === 'superadmin') {
                return redirect('/superadmin');
            } else {
                return redirect('/admin');
            }
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
