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
            $this->setFlash(__('無効なまたは期限切れのトークンです。'), 'error');
            return redirect()->route('admin.login.index')->with('error', '無効なまたは期限切れのトークンです。');
        }
        return Inertia::render('Admin/Auth/ResetPassword', $this->mergeSession([
            'data' => [
                'title' => 'パスワードをリセットする',
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
            $this->setFlash(__('無効なまたは期限切れのトークンです。'), 'error');
            return redirect()->route('admin.login.index')->with('error', '無効なまたは期限切れのトークンです。');
        }
        if (!$this->user->resetPassword($request, $token)) {
            $this->setFlash(__('パスワードのリセットに失敗しました。'), 'error');
            return redirect()->back()->with('error', 'パスワードのリセットに失敗しました。');
        }
        $this->setFlash(__('パスワードが正常にリセットされました。'));
        return redirect()->route('admin.login.index')->with('success', 'パスワードが正常にリセットされました。');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
