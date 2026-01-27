<?php

namespace App\Http\Controllers\Api\Auth;

use App\Helpers\Helper;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public $select;
    public function __construct()
    {
        parent::__construct();
        $this->select = ['id', 'first_name', 'last_name', 'phone', 'company_name', 'Chamber_of_Commerce_kvk_number', 'Chamber_of_Commerce', 'email', 'avatar', 'gender', 'dob', 'country', 'address'];
    }

    public function me()
    {
        $data = User::select($this->select)->find(auth('api')->user()->id);
        return Helper::jsonResponse(true, 'User details fetched successfully', 200, $data);
    }

    public function updateProfile(Request $request)
    {
        $user = auth('api')->user();

        // Validate the request
        $validatedData = $request->validate([
            'avatar' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:10240',
            'password' => 'nullable|string|min:6|confirmed',
            'address' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:20',
            'Chamber_of_Commerce' => 'nullable|string|max:255',
            'Chamber_of_Commerce_kvk_number' => 'nullable|string|max:255',
            'gender' => 'nullable|string|max:255',
            'dob' => 'nullable|date',
            'country' => 'nullable|string|max:255',
            'company_name' => 'nullable|string|max:255',
        ]);

        // Fill all request fields directly into the user model
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->phone = $request->phone;
        $user->company_name = $request->company_name;
        $user->Chamber_of_Commerce_kvk_number = $request->Chamber_of_Commerce_kvk_number;
        $user->Chamber_of_Commerce = $request->Chamber_of_Commerce;
        $user->address = $request->address;
        $user->gender = $request->gender;
        $user->dob = $request->dob;
        $user->country = $request->country;



        // Handle avatar separately
        if ($request->hasFile('avatar')) {
            if ($user->avatar) {
                Helper::fileDelete(public_path($user->getRawOriginal('avatar')));
            }
            $user->avatar = Helper::fileUpload(
                $request->file('avatar'),
                'user/avatar',
                getFileName($request->file('avatar'))
            );
        }

        // Save the user
        $user->save();

        // Return updated user data
        $data = User::select($this->select)->find($user->id);
        return Helper::jsonResponse(true, 'Profile updated successfully', 200, $data);
    }



    public function updateAvatar(Request $request)
    {
        $validatedData = $request->validate([
            'avatar' => 'required|image|mimes:jpeg,png,jpg,gif|max:10240',
        ]);
        $user = auth('api')->user();
        if (!empty($user->avatar)) {
            Helper::fileDelete(public_path($user->getRawOriginal('avatar')));
        }
        $validatedData['avatar'] = Helper::fileUpload($request->file('avatar'), 'user/avatar', getFileName($request->file('avatar')));
        $user->update($validatedData);
        $data = User::select($this->select)->find($user->id);
        return Helper::jsonResponse(true, 'Avatar updated successfully', 200, $data);
    }

    public function delete()
    {
        $user = User::findOrFail(auth('api')->id());
        if (!empty($user->avatar) && file_exists(public_path($user->avatar))) {
            Helper::fileDelete(public_path($user->avatar));
        }
        Auth::logout('api');
        $user->delete();
        return Helper::jsonResponse(true, 'Profile deleted successfully', 200);
    }

    public function destroy()
    {
        $user = User::findOrFail(auth('api')->id());
        if (!empty($user->avatar) && file_exists(public_path($user->avatar))) {
            Helper::fileDelete(public_path($user->avatar));
        }
        Auth::logout('api');
        $user->forceDelete();
        return Helper::jsonResponse(true, 'Profile deleted successfully', 200);
    }
}
