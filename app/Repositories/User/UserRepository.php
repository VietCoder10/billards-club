<?php

namespace App\Repositories\User;

use App\Components\CommonComponent;
use App\Http\Requests\Admin\User\StoreUserRequest;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
        $users = $userBuilder->sortable(['updated_at' => 'desc'])->paginate($newSizeLimit);
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
        $newUser = $this->user->fill($request->only(['name', 'email']));
        $newUser->password = Hash::make($request->password);

        return $newUser->save();
    }

    public function update(StoreUserRequest $request, string $id): bool
    {
        $user = $this->getById($id);
        if (! $user) {
            return false;
        }
        $user->fill($request->only(['name', 'email']));
        if ($request->password) {
            $newUser->password = Hash::make($request->password);
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
}
