<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(Request $request)
    {
        $expectJson = false;
        if ($expectJson) {
            return response()->json([
                'user' => auth()->user()
            ], 200);
        } else {
            return auth()->user();
        }
    }
}
