<?php

namespace App\Repositories\User;

use App\Components\CommonComponent;
use App\Enums\UserRole;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Enums\StorageFolder;
use App\Http\Requests\Admin\Login\AdminForgotPasswordRequest;
use App\Http\Requests\Admin\Login\AdminResetPasswordRequest;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\ForgotPassword;

class UserRepository implements UserInterface
{
    private User $user;

    public function __construct(User $user)
    {
        $this->user = $user;
    }

    public function get($request): LengthAwarePaginator
    {
        $newSizeLimit = CommonComponent::newListLimit($request);
        $userBuilder = $this->user;
        if (isset($request['free_word']) && $request['free_word'] != '') {
            $userBuilder = $userBuilder->where(function ($q) use ($request) {
                $q->orWhere(CommonComponent::escapeLikeSentence('name', $request['free_word']));
                $q->orWhere(CommonComponent::escapeLikeSentence('email', $request['free_word']));
            });
        }
        $users = $userBuilder->sortable(['sort_number' => 'asc'])->paginate($newSizeLimit);
        if (CommonComponent::checkPaginatorList($users)) {
            Paginator::currentPageResolver(function () {
                return 1;
            });
            $users = $userBuilder->paginate($newSizeLimit);
        }

        return $users;
    }

    public function getById(string $id): ?User
    {
        return $this->user->where('id', $id)->first();
    }

    public function store(StoreUserRequest $request): bool
    {
        $newUser = $this->user->fill($request->only([
            'name',
            'email',
        ]));
        $newUser->user_role = UserRole::USER;
        $newUser->password = Hash::make($request->password);

        if ($request->hasFile('avatar')) {
            $filename = CommonComponent::uploadFileName($request->avatar->getClientOriginalExtension());
            $folder = StorageFolder::AVATAR;
            $path = CommonComponent::uploadFile($folder, $request->avatar, $filename);
            if (!$path) {
                return false;
            }
            $newUser->avatar = $path;
        }

        return $newUser->save();
    }

    public function update(StoreUserRequest $request, string $id): bool
    {
        $user = $this->getById($id);
        if (! $user) {
            return false;
        }
        $user->fill($request->only([
            'name',
            'email',
        ]));
        $user->user_role = UserRole::USER;
        if ($request->password) {
            $user->password = Hash::make($request->password);
        }

        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                CommonComponent::deleteFile('', $user->avatar);
            }
            $filename = CommonComponent::uploadFileName($request->avatar->getClientOriginalExtension());
            $folder = StorageFolder::AVATAR;
            $path = CommonComponent::uploadFile($folder, $request->avatar, $filename);
            if (!$path) {
                return false;
            }
            $user->avatar = $path;
        } elseif ($request->has('avatar') && $request->avatar === null) {
            if ($user->avatar) {
                CommonComponent::deleteFile('', $user->avatar);
                $user->avatar = null;
            }
        }

        return $user->save();
    }

    public function destroy(string $id): bool
    {
        $user = $this->user->where('id', $id)->first();
        if (! $user) {
            return false;
        }

        return $user->delete();
    }

    public function saveLoginHistory(): bool
    {
        $userInfo = $this->user->where('id', Auth::guard('admin')->user()->id)->first();
        $userInfo->last_login_at = Carbon::now();

        return $userInfo->save();
    }

    public function checkEmail(Request $request): bool
    {
        return ! $this->user->where(function ($query) use ($request) {
            if (isset($request['id'])) {
                $query->where('id', '!=', $request['id']);
            }
            $query->where(['email' => $request['value']]);
        })->exists();
    }

    public function sendResetPasswordLink(AdminForgotPasswordRequest $request): bool
    {
        $user = $this->user->where('email', $request->email)->first();
        if (! $user) {
            return false;
        }
        $token = Str::random(64);
        $user->reset_password_token = $token;
        $user->reset_password_token_expire = Carbon::now()->addMinutes(30);

        if (! $user->save()) {
            return false;
        }
        $url = route('admin.reset-password.show', $token);
        Mail::to($request->email)->send(new ForgotPassword([
            'url' => $url,
            'name' => $user->name,
        ]));
        return true;
    }

    public function checkToken(string $token): ?User
    {
        $user = $this->user->where('reset_password_token', $token)
            ->where('reset_password_token_expire', '>', Carbon::now())
            ->first();

        return $user;
    }

    public function resetPassword(AdminResetPasswordRequest $request, string $token): bool
    {
        $user = $this->checkToken($token);
        if (! $user) {
            return false;
        }
        $user->password = Hash::make($request->password);
        $user->reset_password_token = null;
        $user->reset_password_token_expire = null;

        return $user->save();
    }
}
