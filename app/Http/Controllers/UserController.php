<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Util\Notification;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\View\Factory;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function profilePage(): Factory|View|Application
    {
        return view('management.profile.index');
    }

    public function updatePassword()
    {
        $validator = Validator::make(request()->all(), [
            'old_password' => 'required',
            'password' => 'required|confirmed',
        ]);

        $validator->after(function ($validator) {
            if (!Hash::check(request('old_password'), auth()->user()->getAuthPassword())) {
                $validator->errors()->add('old_password', '旧密码不正确！');
            }
        });

        if ($validator->fails()) {
            return response()->redirectTo('/management/profile')
                ->withErrors($validator)
                ->withInput();
        }

        $user = User::query()->find(auth()->user()->getAuthIdentifier());

        if ($user->update([
            'password' => Hash::make(request('password'))
        ])) {
            Notification::flash('密码更换成功！');
            return response()->redirectTo('/management/profile');
        } else {
            abort(500);
        }
    }
}
