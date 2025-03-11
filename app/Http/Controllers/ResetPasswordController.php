<?php

namespace App\Http\Controllers;

use App\Services\ResetPasswordService;
use Illuminate\Http\Request;

class ResetPasswordController extends Controller
{
    protected $resetPasswordService;

    public function __construct(ResetPasswordService $resetPasswordService)
    {
        $this->resetPasswordService = $resetPasswordService;
    }

    public function sendResetCode(Request $request)
    {
        $request->validate([
            'phone_number' => 'required|numeric|regex:/^963(9[3-9]\d{7})$/',
        ]);

        return $this->resetPasswordService->sendCode($request->phone_number);
    }

    public function verifyResetCode(Request $request)
    {
        $request->validate([
            'code' => 'required|digits:4',
        ]);

        return $this->resetPasswordService->verifyCode($request->code);
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'new_password' => [
                'required',
                'min:6',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
                'confirmed',
            ],
        ]);

        return $this->resetPasswordService->resetPassw($request->new_password);
    }
}
