<?php

namespace App\Http\Controllers\Admin;

use App\Components\SearchQueryComponent;
use App\Enums\StatusCode;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Repositories\User\UserInterface;
use Illuminate\Http\Request;
use Inertia\Inertia;

class UserController extends BaseController
{
    private $user;

    public function __construct(UserInterface $user)
    {
        $this->user = $user;
    }

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): \Inertia\Response
    {
        $users = $this->user->get($request);
        session()->forget('admin.user.list');
        session()->push('admin.user.list', url()->full());

        return Inertia::render('Admin/User/Index', $this->mergeSession([
            'data' => [
                'title' => 'ユーザー一覧',
                'users' => $users->items(),
                'sortLinks' => $this->sortLinks('admin.user.index', [
                    ['key' => 'name', 'name' => 'ユーザー名'],
                    ['key' => 'email', 'name' => 'メールアドレス'],
                    ['key' => 'created_at', 'name' => '登録日'],
                ], $request),
                'request' => $request->all(),
                'paginator' => $this->paginator($users->appends(SearchQueryComponent::alterQuery($request))),
            ],
        ]));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('Admin/User/Form', [
            'data' => [
                'title' => 'ユーザー追加',
                'urlBack' => session()->get('admin.user.list')[0] ?? route('admin.user.index'),
            ],
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreUserRequest $request)
    {
        if ($this->user->store($request)) {
            $this->setFlash(__('ユーザー新規登録が完了しました。'));

            return redirect()->route('admin.user.index');
        }
        $this->setFlash(__('エラーが発生しました。'), 'error');

        return redirect()->route('admin.user.create');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = $this->user->getById($id);
        if (! $user) {
            $this->setFlash(__('エラーが発生しました。'), 'error');

            return redirect()->route('admin.user.index');
        }

        return Inertia::render('Admin/User/Form', [
            'data' => [
                'title' => 'ユーザー追加',
                'user' => $user,
                'isEdit' => true,
                'urlBack' => session()->get('admin.user.list')[0] ?? route('admin.user.index'),
            ],
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StoreUserRequest $request, string $id)
    {
        if ($this->user->update($request, $id)) {
            $this->setFlash(__('ユーザー編集が完了しました。'));

            return redirect()->route('admin.user.index');
        }
        $this->setFlash(__('エラーが発生しました。'), 'error');

        return redirect()->route('admin.user.edit', $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if ($this->user->destroy($id)) {
            return response()->json([
                'message' => 'ユーザーの削除が完了しました。',
            ], StatusCode::OK);
        }

        return response()->json([
            'message' => 'エラーが発生しました。',
        ], StatusCode::INTERNAL_ERR);
    }

    public function checkEmail(Request $request)
    {
        return response()->json([
            'valid' => $this->user->checkEmail($request),
        ], StatusCode::OK);
    }
}
