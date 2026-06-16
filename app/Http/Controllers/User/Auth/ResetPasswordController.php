<?php

namespace App\Http\Controllers\User\Auth;

use App\Http\Requests\User\Auth\CustomerResetPasswordRequest;
use App\Http\Controllers\BaseController;
use App\Repositories\Customer\CustomerInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ResetPasswordController extends BaseController
{
    private CustomerInterface $customer;

    public function __construct(CustomerInterface $customer)
    {
        $this->customer = $customer;
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $token)
    {
        //
        if (!$this->customer->checkToken($token)) {
            $this->setFlash(__('Liên kết không hợp lệ hoặc đã hết hạn.'), 'error');
            return redirect()->route('user.login.index')->with('error', 'Liên kết không hợp lệ hoặc đã hết hạn.');
        }
        return Inertia::render('User/Auth/ResetPassword', $this->mergeSession([
            'data' => [
                'title' => 'Đặt lại mật khẩu',
                'token' => $token,
            ],
        ]));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(CustomerResetPasswordRequest $request, string $token)
    {
        //
        if (!$this->customer->checkToken($token)) {
            $this->setFlash(__('Liên kết không hợp lệ hoặc đã hết hạn.'), 'error');
            return redirect()->route('user.login.index')->with('error', 'Liên kết không hợp lệ hoặc đã hết hạn.');
        }
        if (!$this->customer->resetPassword($request, $token)) {
            $this->setFlash(__('Đặt lại mật khẩu không thành công.'), 'error');
            return redirect()->back()->with('error', __('Đặt lại mật khẩu không thành công.'));
        }
        $this->setFlash(__('Mật khẩu đã được đặt lại thành công.'));
        return redirect()->route('user.login.index')->with('success', __('Mật khẩu đã được đặt lại thành công.'));
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
