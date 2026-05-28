<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Inertia\Inertia;
use App\Repositories\User\UserInterface;
use App\Http\Requests\Admin\Login\AdminResetPasswordRequest;

class ResetPasswordController extends BaseController
{
    private $user;

    public function __construct(UserInterface $user)
    {
        $this->user = $user;
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
        if (!$this->user->checkToken($token)) {
            $this->setFlash(__('Đây là mã thông báo không hợp lệ hoặc đã hết hạn.'), 'error');
            return redirect()->route('admin.login.index')->with('error', 'Đây là mã thông báo không hợp lệ hoặc đã hết hạn.');
        }
        return Inertia::render('Admin/Auth/ResetPassword', $this->mergeSession([
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
    public function update(AdminResetPasswordRequest $request, string $token)
    {
        if (!$this->user->checkToken($token)) {
            $this->setFlash(__('Đây là mã thông báo không hợp lệ hoặc đã hết hạn.'), 'error');
            return redirect()->route('admin.login.index')->with('error', 'Đây là mã thông báo không hợp lệ hoặc đã hết hạn.');
        }
        if (!$this->user->resetPassword($request, $token)) {
            $this->setFlash(__('Đặt lại mật khẩu không thành công.'), 'error');
            return redirect()->back()->with('error', 'Đặt lại mật khẩu không thành công.');
        }
        $this->setFlash(__('Mật khẩu đã được đặt lại thành công.'));
        return redirect()->route('admin.login.index')->with('success', 'Mật khẩu đã được đặt lại thành công.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
