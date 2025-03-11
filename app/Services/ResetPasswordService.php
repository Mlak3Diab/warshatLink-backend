<?php
namespace App\Services;

use App\Models\User;
use App\Models\Resetpasswordcode;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class ResetPasswordService
{
    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }
    public function sendCode($phoneNumber)
    {
        $user = User::where('phone_number', $phoneNumber)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $verificationCode = mt_rand(1000, 9999);

        Resetpasswordcode::updateOrCreate(
            ['phone' => $user->phone_number],
            ['code' => $verificationCode, 'created_at' => now()]
        );

        $this->whatsAppService->sendMessage($user->phone_number, "Your password reset code is: $verificationCode");

        return response()->json(['message' => 'Verification code sent successfully.'], 200);
    }

    public function verifyCode($code)
    {
        $resetData = Resetpasswordcode::where('code', $code)->first();

        if (!$resetData) {
            return response()->json(['message' => 'Invalid verification code.'], 400);
        }

        return response()->json(['message' => 'Code verified successfully. Proceed to reset your password.'], 200);
    }

    public function resetPassw($newPassword)
    {
        $user = Auth::user();

        if (!$user) {
            return response()->json(['message' => 'Unauthorized.'], 401);
        }

        $user->password = Hash::make($newPassword);
        $user->save();

        return response()->json(['message' => 'Password reset successfully.'], 200);
    }
}
