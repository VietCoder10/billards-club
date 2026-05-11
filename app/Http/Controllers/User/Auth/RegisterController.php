<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Controllers\BaseController;
use App\Http\Requests\User\Auth\CustomerRegisterRequest;
use App\Repositories\Customer\CustomerInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class RegisterController extends BaseController
{
    private $customer;

    public function __construct(CustomerInterface $customer)
    {
        $this->customer = $customer;
    }

    public function index(Request $request): Response|RedirectResponse
    {
        if (Auth::guard('customer')->check()) {
            return redirect(route('user.dashboard.index'));
        }

        return Inertia::render('User/Auth/Register', $this->mergeSession([
            'data' => [
                'title' => 'Đăng ký tài khoản',
                'request' => $request->all(),
            ],
        ]));
    }

    public function store(CustomerRegisterRequest $request)
    {
        if ($this->customer->store($request)) {
            // Auto login after register
            $credentials = $request->only('email', 'password');
            Auth::guard('customer')->attempt($credentials);
            $this->customer->saveLoginHistory();

            $this->setFlash(__('Đăng ký tài khoản thành công.'), 'success');
            return redirect(route('user.dashboard.index'));
        }
        $this->setFlash(__('Có lỗi xảy ra, vui lòng thử lại.'), 'error');
        return redirect()->back();
    }
}
