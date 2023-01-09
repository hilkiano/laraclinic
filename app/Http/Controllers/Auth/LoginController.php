<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\UserController;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Log;

class LoginController extends Controller
{
    private $userController;

    public function __construct()
    {
        $this->userController = new UserController();
    }

    /**
     * Handle the incoming request.
     *
     * @param  \App\Http\Requests\Auth\LoginRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function __invoke(LoginRequest $request)
    {
        try {
            if (!$token = auth()->attempt(array('username' => $request->username, 'password' => $request->password))) {
                return response()->json([
                    'success' => false,
                    'message' => trans("message.username_password_mismatch")
                ], 401);
            }
            $cookie = cookie('jwt', $token, config('jwt.ttl'), '/');

            return response()->json([
                'status'    => true,
                'user'      => $this->userController->__invoke($request),
                'message'   => 'Login Success'
            ], 200)->withCookie($cookie);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => $e->getMessage()
            ], 500);
        }
    }
}
