<?php


namespace App\Bd\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use KevinSoft\MultiLanguage\MultiLanguage;
use Illuminate\Support\Facades\Cookie;
use Encore\Admin\Controllers\AuthController as BaseAuthController;

class AuthController extends BaseAuthController
{

    public function locale()
    {
        $locale = Request::input('locale');
        $languages = MultiLanguage::config('languages');

        $cookie_name = MultiLanguage::config('cookie-name', 'locale');
        if (array_key_exists($locale, $languages)) {

            return response('ok')->cookie($cookie_name, $locale);
        }
    }

    public function showLoginForm()
    {
        $test = request()->query('redirect_url');
        $languages = MultiLanguage::config("languages");
        $cookie_name = MultiLanguage::config('cookie-name', 'locale');

        $current = MultiLanguage::config('default');
        if (Cookie::has($cookie_name)) {
            $current = Cookie::get($cookie_name);
        }
        return view("bd.auth.login", compact('languages', 'current', 'test'));
    }
  

    public function postLogin(Request $request)
    {
       $url = $request->url;

        $this->loginValidator($request->all())->validate();

        $credentials = $request->only([$this->username(), 'password']);
        $remember = $request->get('remember', false);

        if ($this->guard()->attempt($credentials, $remember)) {
            return $this->sendLoginResponse($request);
        }

        return back()->withInput()->withErrors([
            $this->username() => $this->getFailedLoginMessage(),
        ]);
    }

    public function sendLoginResponse(Request $request)
    {
        admin_toastr(trans('admin.login_successful'));

        $request->session()->regenerate();
        if ($this->guard()->user()->type === 'bd') {
            return redirect()->route('admin.bd.home');
        }

        return redirect()->intended($request->url??$this->redirectPath());
    }

    public function logout()
    {
        Auth::guard('bd')->logout();
        return redirect()->route('bd.login');
    }
}
