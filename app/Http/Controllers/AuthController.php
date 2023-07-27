<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\Response;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => ['required', 'max:16', 'min:3'],
            'phone_number' => ['required', 'max:11', 'min:11', 'unique:users,phone_number'], //TODO:
            'password' => ['required', 'string', 'min:4', 'max:16', 'confirmed'],
            'role' => ['required', 'string', Rule::in(['barber', 'client'])],
            'gender' => ['required', 'string', Rule::in(['male', 'female'])],
            // client
            'address' => ['required_if:role,client', 'string', 'min:4', 'max:64'],
            // barber
            // 'work_time' => ['required_if:role,barber', 'string'],
            'work_experience' => ['required_if:role,barber', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json(['error' => $validator->getMessageBag()], Response::HTTP_FORBIDDEN);
        }

        $user = match ($request->role) {
            'barber' => User::create([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'gender' => $request->gender,
                // 'work_time' => $request->work_time,
                'work_experience' => $request->work_experience,
                'password' => Hash::make($request->password),
                'role' => 'barber',
            ]),
            'client' => User::create([
                'name' => $request->name,
                'phone_number' => $request->phone_number,
                'address' => $request->address,
                'gender' => $request->gender,
                'password' => Hash::make($request->password),
                'role' => 'client',
            ]),
        };

        return response()->json(
            [
                // 'user' => new UserResource($user),
                'user' => $user,
                'token' => $user->createToken('myApp')->plainTextToken,
            ], Response::HTTP_CREATED);
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'phone_number' => ['required', 'string', 'max:11', 'min:11', 'exists:users,phone_number'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json($validator->getMessageBag(), Response::HTTP_FORBIDDEN);
        }

        $user = User::where('phone_number', $request->phone_number)->first();

        if (!$user) {
            return response()->json(['error' => 'کاربری با این مشخصات وجود ندارد.'], Response::HTTP_NOT_FOUND);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['error' => 'رمزعبور اشتباه است.'], Response::HTTP_UNAUTHORIZED);
        }

        $token = $user->createToken('myApp')->plainTextToken;

        return response()->json([
            'user' => $user,
            'token' => $token,
        ]);
    }

    public function logout()
    {
        auth()->user()->tokens()->delete();
        return response()->json(['success' => 'کاربر با موفقیت از سایت خارج شد.'], Response::HTTP_OK);
    }

    public function userInfo()
    {
        return response()->json(['user' => auth()->user()]);
    }
}
