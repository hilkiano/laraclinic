<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PrivilegeController;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class StocksController extends Controller
{
    private $userData;
    private $menuController;
    private $privilegeController;

    public function __construct()
    {
        $this->userData = auth()->user();
        $this->menuController = new MenuController();
        $this->privilegeController = new PrivilegeController();
    }

    public function index(Request $request)
    {
        try {
            $data = [
                "user"  => $this->userData,
                "menus" => $this->menuController->__invoke($request)->original,
                "privs" => $this->privilegeController->__invoke($request)->original["data"],
                "request" => $request->all(),
                "uuid"  => Str::uuid()
            ];

            return view('/stocks/stocks', $data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => $e->getMessage()
            ], 500);
        }
    }
}
