<?php

namespace App\Http\Controllers\Admin;

use App\Components\SearchQueryComponent;
use App\Enums\StatusCode;
use App\Enums\UserRole;
use App\Http\Controllers\BaseController;
use App\Http\Requests\Admin\Product\UpdateAvatarRequest;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Repositories\User\UserInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
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
                'title' => 'Quản lí nhân viên',
                'users' => $users->items(),
                'sortLinks' => $this->sortLinks('admin.user.index', [
                    ['key' => 'name', 'name' => 'Tên người dùng'],
                    ['key' => 'email', 'name' => 'Email'],
                    ['key' => 'user_role', 'name' => 'Chức vụ'],
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
                'title' => 'Thêm nhân viên',
                'userRoleOption' => UserRole::getOptions(),
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
            $this->setFlash(__('Thêm nhân viên thành công.'), 'success');

            return redirect()->route('admin.user.index');
        }
        $this->setFlash(__('Thêm nhân viên thất bại.'), 'error');

        return redirect()->route('admin.user.create');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id) {}

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $user = $this->user->getById($id);
        if (! $user) {
            $this->setFlash(__('Không tìm thấy nhân viên.'), 'error');

            return redirect()->route('admin.user.index');
        }

        return Inertia::render('Admin/User/Form', [
            'data' => [
                'title' => 'Cập nhật nhân viên',
                'user' => $user,
                'userRoleOption' => UserRole::getOptions(),
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
            $this->setFlash(__('Cập nhật thông tin thành công.'));

            if (Auth::guard('admin')->user()->user_role != UserRole::ADMIN) {
                return redirect()->route('admin.dashboard.index');
            }

            return redirect()->route('admin.user.index');
        }
        $this->setFlash(__('Cập nhật thông tin thất bại.'), 'error');

        return redirect()->route('admin.user.edit', $id);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        if ($this->user->destroy($id)) {
            return response()->json([
                'message' => 'Xóa nhân viên thành công.',
            ], StatusCode::OK);
        }

        return response()->json([
            'message' => 'Xóa nhân viên thất bại.',
        ], StatusCode::INTERNAL_ERR);
    }

    public function checkEmail(Request $request)
    {
        return response()->json([
            'valid' => $this->user->checkEmail($request),
        ], StatusCode::OK);
    }
    public function updateAvatar(UpdateAvatarRequest $request, $id)
    {
        $user = $this->user->getById($id);
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Không tìm thấy người dùng.',
            ], StatusCode::NOT_FOUND);
        }

        // $this->authorize('update', $user);

        try {
            // Delete old avatar if exists
            if ($user->avatar && Storage::disk('public')->exists($user->avatar)) {
                Storage::disk('public')->delete($user->avatar);
            }

            // Store new avatar
            $path = $request->file('avatar')->store('avatars', 'public');
            $user->avatar = $path;
            $user->save();

            return response()->json([
                'success' => true,
                'avatar' => Storage::url($path),
                'message' => 'Ảnh đại diện đã được cập nhật.',
            ], StatusCode::OK);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Đã xảy ra lỗi.',
            ], StatusCode::INTERNAL_ERR);
        }
    }
}
