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
        $userType = $user?->type ?? 'admin'; // normalize null => admin

        $adminLogin = 'admin/login';
        $bdLogin = 'bd/login';
        $superadminLogin = 'superadmin/login';

        // If already logged in and tries to access login page
        if ($user) {
            if (Str::is($uri, $adminLogin) && $userType === 'admin') {
                return redirect('/admin');
            }
            if (Str::is($uri, $bdLogin) && $userType === 'bd') {
                return redirect('/bd');
            }
            if (Str::is($uri, $superadminLogin) && $userType === 'superadmin') {
                return redirect('/superadmin');
            }

            // 🚨 If role mismatch
            if (
                (Str::startsWith($uri, 'bd') && $userType !== 'bd') ||
                (Str::startsWith($uri, 'superadmin') && $userType !== 'superadmin') ||
                (Str::startsWith($uri, 'admin') && $userType !== 'admin')
            ) {
                Admin::guard()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($userType === 'bd') {
                    return redirect('/bd/login')->withErrors(['error' => 'Please login through BD portal.']);
                } elseif ($userType === 'superadmin') {
                    return redirect('/superadmin/login')->withErrors(['error' => 'Please login through Superadmin portal.']);
                } else {
                    return redirect('/admin/login')->withErrors(['error' => 'Please login through Admin portal.']);
                }
            }
        }

        // Guest handling
        $redirectTo = admin_base_path(config('admin.auth.redirect_to', 'auth/login'));
        $test = $request->getRequestUri();

        if (Str::contains($uri, 'bd')) {
            $redirectTo = '/bd/login';
        }
        if (Str::contains($uri, 'superadmin')) {
            $redirectTo = '/superadmin/login';
        }

        if (Admin::guard()->guest() && !$this->shouldPassThrough($request)) {
            return redirect()->to($redirectTo . '?redirect_url=' . urlencode($test));
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
