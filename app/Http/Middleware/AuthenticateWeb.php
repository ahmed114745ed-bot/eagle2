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
        // dd( $user);
        $userType = $user?->type ?? 'admin';
        
        $adminLogin = 'admin/login';
        $bdLogin = 'bd/login';
        $superadminLogin = 'superadmin/login';
        $areaManagerLogin = 'areaManager/login';

        if ($user) {
            if (Str::is($uri, $adminLogin) && $userType === 'admin') {
                return redirect('/admin');
            }
            if (Str::is($uri, $bdLogin) && $userType === 'bd') {
                return redirect('/bd');
            }
            if (
                Str::is($uri, $superadminLogin) && in_array($userType, ['superadmin', 'sub_super_admin'], true)
            ) {
                return redirect('/superadmin');
            }
            if (Str::is($uri, $areaManagerLogin) && in_array($userType, ['area-manager', 'sub_area_manager'], true)) {
                return redirect('/areaManager');
            }
             //dd($uri,$userType,$areaManagerLogin);
            if (
                (Str::startsWith($uri, 'bd') && $userType !== 'bd') ||
                (Str::startsWith($uri, 'superadmin') && $userType !== 'superadmin') ||
                (Str::startsWith($uri, 'areaManager') && $userType !== 'area-manager') ||
                (Str::startsWith($uri, 'areaManager') && $userType !== 'sub_area_manager') ||
                (Str::startsWith($uri, 'admin') && $userType !== 'admin')
            ) {
                Admin::guard()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                if ($userType === 'bd') {
                    return redirect('/bd/login')->withErrors(['error' => 'Please login through BD portal.']);
                } elseif ($userType === 'superadmin') {
                    return redirect('/superadmin/login')->withErrors(['error' => 'Please login through Superadmin portal.']);
                } elseif ($userType === 'area-manager' || $userType === 'sub_area_manager') {
                    return redirect('/areaManager/login')->withErrors(['error' => 'Please login through Area Manager portal.']);
                } elseif ($userType === 'sub_super_admin') {
                    return redirect('/superadmin/login')->withErrors(['error' => 'Please login through Superadmin portal.']);
                } else {
                    return redirect('/admin/login')->withErrors(['error' => 'Please login through Admin portal.']);
                }
            }
        }

        $redirectTo = admin_base_path(config('admin.auth.redirect_to', 'auth/login'));
        $test = $request->getRequestUri();

        if (Str::contains($uri, 'bd')) {
            $redirectTo = '/bd/login';
        }
        if (Str::contains($uri, 'superadmin')) {
            $redirectTo = '/superadmin/login';
        }
        if (Str::contains($uri, 'areaManager')) {
            $redirectTo = '/areaManager/login';
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
