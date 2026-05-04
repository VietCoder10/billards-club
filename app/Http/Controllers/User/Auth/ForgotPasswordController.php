<?php

namespace App\Http\Controllers\User\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Inertia\Inertia;
use Inertia\Response;
use App\Repositories\Customer\CustomerInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\User\Auth\CustomerForgotPasswordRequest;

class ForgotPasswordController extends BaseController
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

        return Inertia::render('User/Auth/ForgotPassword', $this->mergeSession([
            'data' => [
                'title' => 'Quên mật khẩu',
                'request' => $request->all(),
            ],
        ]));
    }

    public function store(CustomerForgotPasswordRequest $request)
    {
        if (!$this->customer->sendResetPasswordLink($request)) {
            $this->setFlash(__('Gửi liên kết đặt lại mật khẩu thất bại.'), 'error');
            return redirect()->back()->with('error', 'Gửi liên kết đặt lại mật khẩu thất bại.');
        }

        $this->setFlash(__('Gửi liên kết đặt lại mật khẩu thành công.'));
        return redirect()->route('user.login.index')->with('success', 'Gửi liên kết đặt lại mật khẩu thành công.');
    }
}
