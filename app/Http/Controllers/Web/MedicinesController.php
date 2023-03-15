<?php

namespace App\Http\Controllers\Web;

use Illuminate\Http\Request;
use App\Http\Controllers\MenuController;
use App\Http\Controllers\PrivilegeController;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;

class MedicinesController extends Controller
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
                "privs" => $this->privilegeController->__invoke($request)->original["data"]
            ];

            return view('medicines/list', $data);
        } catch (\Exception $e) {
            Log::error($e->getMessage());
            return response()->json([
                'status'    => false,
                'message'   => env('APP_ENV') === 'production' ? 'Unexpected error. Please check log.' : $e->getMessage()
            ], 500);
        }
    }
}
