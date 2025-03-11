<?php

namespace App\Http\Controllers;


use App\Mail\sendcodeverificationemail;
use App\Models\Address;
use App\Models\email_verification_code;
use App\Models\User;
use App\Services\AuthService;
use Google_Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    protected $authService;

    public function __construct(AuthService $authService)
    {
        $this->authService = $authService;
    }

    public function userRegister(Request $request)
    {
        $result = $this->authService->registerUser($request);
        return response()->json($result);
    }
    public function verifyCode(Request $request)
    {
        $request->validate([
            'code' => 'required|numeric|digits:4',
        ]);
        $result = $this->authService->verifyUserByCode($request->code);

        if (!$result['status']) {
            return response()->json(['message' => $result['message']], 400);
        }
        return response()->json([
            'message' => $result['message'],
            'user' => $result['user']
        ]);
    }
    public function userLogin(Request $request)
    {
        return $this->authService->loginUser($request);
    }
    public function googleLogin(Request $request)
    {
        return $this->authService->googleLogin($request);
    }
}
