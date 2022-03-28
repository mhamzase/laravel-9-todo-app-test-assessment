<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResourse;
use App\Models\User;
use App\Notifications\SendVerificationCodeNotification;
use Illuminate\Http\Request;
use Notification;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\API\ResponseController as ResponseController;

class AuthController extends ResponseController
{
    /**
     * Register a new user.
     *
     * @return void
     */
    public function register(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users',
            'password' => 'required|string',
            'password_confirmation' => 'required|string|same:password',
        ]);

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        if ($user) {
            auth()->attempt(['email' => $request->email, 'password' => $request->password]);
            $token = $user->createToken('authToken')->plainTextToken;

            $verification_code = random_int(100000, 999999);

            // send verification code to user email
            Notification::send($user, new SendVerificationCodeNotification($user, $verification_code));

            $user->verification_code = $verification_code;
            $user->save();

            $response['token_type'] = 'Bearer';
            $response['token'] = $token;
            $response['user'] = new UserResourse($user);

            return $this->sendResponse($response, 'Account created successfully & a code has been sent to your email.', 201);
        } else {
            return $this->sendError('Account not created', [], 404);
        }
    }

    /**
     * Login user and create token
     *
     * @param  [string] email
     * @param  [string] password
     */
    public function login(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $data['email'])->first();

        if ($user && Hash::check($data['password'], $user->password)) {
            auth()->attempt(['email' => $request->email, 'password' => $request->password]);
            $token = auth()->user()->createToken('authToken')->plainTextToken;

            $response['token_type'] = 'Bearer';
            $response['token'] = $token;
            $response['user'] = new UserResourse(auth()->user());

            if ($user->email_verified_at !== null) {
                return $this->sendResponse($response, 'Account created successfully & a code has been sent to your email.', 200);
            } else {
                return $this->sendResponse($response, 'Please verify your account first!', 200);
            }
        } else {
            return $this->sendError('Invalid credentials', 401);
        }
    }

    /**
     * Verify user account.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verifyAccount(Request $request)
    {
        if (auth()->user()->email_verified_at == null) {
            if (auth()->user()->verification_code == $request->verification_code) {

                auth()->user()->email_verified_at = now();
                auth()->user()->save();

                return $this->sendResponse([], 'Your account has been verified.', 200);
            } else {
                return $this->sendError('Invalid verification code!', 401);
            }
        } else {
            return $this->sendError('Your account already verified', 400);
        }
    }

    /**
     * Logout user (Revoke the token)
     *
     * @return [string] message
     */
    public function logout()
    {
        auth()->user()->tokens->each(function ($token, $key) {
            $token->delete();
        });

        return $this->sendResponse([], 'Logged out successfully', 200);
    }
}
