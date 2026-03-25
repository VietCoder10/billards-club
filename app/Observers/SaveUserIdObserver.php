<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class SaveUserIdObserver
{
    public function creating(Model $model)
    {
        if (Auth::guard('admin')->check()) {
            $model->created_by = Auth::guard('admin')->id();
            $model->updated_by = Auth::guard('admin')->id();
        }
    }

    public function updating(Model $model)
    {
        if (Auth::guard('admin')->check()) {
            $model->updated_by = Auth::guard('admin')->id();
        }
    }
}
