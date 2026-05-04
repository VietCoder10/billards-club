<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Requests\User\Auth\CustomerLoginRequest;
use App\Repositories\Customer\CustomerInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends BaseController
{
    private $customer;

    public function __construct(CustomerInterface $customer)
    {
        $this->customer = $customer;
    }

    public function index(Request $request): Response|RedirectResponse
    {
        if (Auth::guard('customer')->check()) {
            return redirect('/');
        }

        return Inertia::render('User/Auth/Login', $this->mergeSession([
            'data' => [
                'title' => 'Đăng nhập',
                'request' => $request->all(),
            ],
        ]));
    }

    public function store(CustomerLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        if (Auth::guard('customer')->attempt($credentials, $request->remember_me ?? false)) {
            if (! $this->customer->saveLoginHistory()) {
                Auth::guard('customer')->logout();
                return redirect('/');
            }
            return redirect($request->url_redirect ? $request->url_redirect : '/');
        }
        $this->setFlash(__('Tên đăng nhập và mật khẩu không khớp.'), 'error');

        return redirect()->route('user.login.index');
    }

    public function logout()
    {
        Auth::guard('customer')->logout();

        return redirect('/');
    }
}
