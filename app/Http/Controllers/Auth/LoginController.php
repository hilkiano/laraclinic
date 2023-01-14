<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Auth\UserController;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

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
            if (!$token = auth()->attempt(
                array('username' => $request->username, 'password' => $request->password),
                ['exp' => $request->rememberMe ? Carbon::now()->addYears(2)->timestamp : Carbon::now()->addMinute()->timestamp]
            )) {
                return response()->json([
                    'status'  => false,
                    'message' => "Username or password not matched."
                ], 401);
            }

            $cookie = cookie(
                'jwt',
                $token,
                $request->rememberMe ? config('jwt.refresh_ttl') : config('jwt.ttl'),
                '/',
                null,
                null,
                false
            );

            return response()->json([
                'status'    => true,
                'user'      => $this->userController->__invoke($request)->original,
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
