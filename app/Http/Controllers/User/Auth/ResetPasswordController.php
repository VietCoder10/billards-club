<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\BaseController;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use App\Repositories\Customer\CustomerInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\User\Auth\CustomerResetPasswordRequest;

class ResetPasswordController extends BaseController
{
    private $customer;

    public function __construct(CustomerInterface $customer)
    {
        $this->customer = $customer;
    }

    public function show(string $token): Response|RedirectResponse
    {
        if (Auth::guard('customer')->check()) {
            return redirect('/');
        }
        $user = $this->customer->checkToken($token);
        if (!$user) {
            $this->setFlash(__('Liên kết không tồn tại hoặc đã hết hạn.'), 'error');
            return redirect()->route('user.forgot-password.index')->with('error', 'Liên kết không tồn tại hoặc đã hết hạn.');
        }

        return Inertia::render('User/Auth/ResetPassword', $this->mergeSession([
            'data' => [
                'title' => 'Cài đặt lại mật khẩu',
                'token' => $token,
            ],
        ]));
    }

    public function store(CustomerResetPasswordRequest $request, string $token)
    {
        if ($this->customer->resetPassword($request, $token)) {
            $this->setFlash(__('Cài đặt lại mật khẩu thành công.'));
            return redirect()->route('user.login.index')->with('success', 'Cài đặt lại mật khẩu thành công.');
        }

        $this->setFlash(__('Liên kết không tồn tại hoặc đã hết hạn.'), 'error');
        return redirect()->route('user.forgot-password.index')->with('error', 'Liên kết không tồn tại hoặc đã hết hạn.');
    }
}
