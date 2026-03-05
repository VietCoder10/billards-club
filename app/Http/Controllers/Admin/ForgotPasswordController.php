<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\BaseController;
use Inertia\Inertia;
use Inertia\Response;
use App\Repositories\User\UserInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Http\Requests\Admin\Login\AdminForgotPasswordRequest;

class ForgotPasswordController extends BaseController
{
    private $user;

    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): Response|RedirectResponse
    {
        if (Auth::guard('admin')->check()) {
            return redirect(route('admin.dashboard.index'));
        }

        return Inertia::render('Admin/Auth/ForgotPassword', $this->mergeSession([
            'data' => [
                'title' => 'Quên mật khẩu',
                'request' => $request->all(),
            ],
        ]));
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
    public function store(AdminForgotPasswordRequest $request)
    {
        if (!$this->user->sendResetPasswordLink($request)) {
            $this->setFlash(__('Gửi liên kết đặt lại mật khẩu thất bại.'), 'error');
            return redirect()->back()->with('error', 'Gửi liên kết đặt lại mật khẩu thất bại.');
        }

        $this->setFlash(__('Gửi liên kết đặt lại mật khẩu thành công.'));
        return redirect()->route('admin.login.index')->with('success', 'Gửi liên kết đặt lại mật khẩu thành công.');
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
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
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
