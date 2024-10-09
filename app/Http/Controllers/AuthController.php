<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{

    /**
     * @throws ValidationException
     */
    public function register(Request $request): array
    {
        try {
            $this->validate($request, [
                'name' => 'required|String',
                'email' => 'required|email',
                'password' => 'required|min:6'
            ]);

            User::create([
                'name' => $request->input('name'),
                'email' => $request->input('email'),
                'password' => Hash::make($request->input('password')),
            ]);

            return ['code' => 201, 'msg' => 'new user created'];
        } catch (\Exception $error){
            return ['code' => 500, 'msg' => 'an error occurred while registering the user', 'error' => $error->getMessage()];
        }
    }


    /**
     * @throws ValidationException
     */
    public function login(Request $request): array
    {
        try {
            // validate data
            $this->validate($request, [
                'email' => 'required|email',
                'password' => 'required'
            ]);

            // fetch user
            $user = User::where('email', $request->input('email'))->first();

            // check if user exists
            if ($user == null){
                return ['code' => 401, 'msg' => 'user does not exist'];
            }

            // check if user entered the correct password
            if (Hash::check($request->input('password'), $user->password)){
                return ['code' => 200, 'user' => $user];
            }else {
                return ['code' => 401, 'user' => 'user entered a wrong password'];
            }
        }catch (\Exception $error){
            return ['code' => 500, 'msg' => 'an error occurred while logging in', 'error' => $error->getMessage()];
        }
    }
}
