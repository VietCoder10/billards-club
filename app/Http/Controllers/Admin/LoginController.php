<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\Login\AdminLoginRequest;
use App\Repositories\User\UserInterface;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class LoginController extends BaseController
{
    private $user;

    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Inertia\Response
     */
    public function index(Request $request): Response|RedirectResponse
    {
        if (Auth::guard('admin')->check()) {
            return redirect(route('admin.dashboard.index'));
        }

        return Inertia::render('Admin/Auth/Login', $this->mergeSession([
            'data' => [
                'title' => 'ログイン',
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
    public function store(AdminLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        if (Auth::guard('admin')->attempt($credentials, $request->remember_me ?? false)) {
            if (! $this->user->saveLoginHistory()) {
                Auth::guard('admin')->logout();

                return redirect('/');
            }

            return redirect($request->url_redirect ? $request->url_redirect : route('admin.dashboard.index'));
        }
        $this->setFlash(__('ログインIDとパスワードが一致しません。'), 'error');

        return redirect()->route('admin.login.index');
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

    public function logout()
    {
        Auth::guard('admin')->logout();

        return redirect(route('admin.login.index'));
    }
}
