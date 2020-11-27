<?php

namespace App\Http\Controllers;

use App\Token;

class InitController extends Controller
{
    public function index()
    {
        $token = Token::all();
        return response()->json([
            "error" => false,
            "data" => (object) [
                "available_providers" => [
                    "Dialog", "Hutch", "Airtel",
                ],
            ],
        ], 201);
    }
}
