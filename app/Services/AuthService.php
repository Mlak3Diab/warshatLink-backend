<?php

namespace App\Services;

use App\Models\phone_verification_code;
use App\Models\Resetpasswordcode;
use App\Models\User;
use App\Models\Address;

use Exception;
use Google_Client;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\sendcodeverificationemail;
use Illuminate\Support\Str;

class AuthService
{
    protected $whatsAppService;

    public function __construct(WhatsAppService $whatsAppService)
    {
        $this->whatsAppService = $whatsAppService;
    }
    public function registerUser(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone_number' => 'required|numeric|regex:/^963(9[3-9]\d{7})$/',
            'national_number' => 'required|numeric|digits:11',
            'email' => 'required|email|unique:users',
            'password' => [
                'required',
                'min:6',
                'regex:/[a-z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*#?&]/',
                'confirmed',
            ],
            'city' => 'required|string',
            'town' => 'required|string',
        ]);

        $user = new User();
        $user->name = $request->name;
        $user->email = $request->email;
        $user->password = Hash::make($request->password);
        $user->phone_number = $request->phone_number;
        $user->national_number = $request->national_number;
        $user->save();

        Address::create([
            'city' => $request->input('city'),
            'town' => $request->input('town'),
            'user_id' => $user->id,
        ]);

        $accessToken = $user->createToken('MyApp')->accessToken;

        $verificationCode = mt_rand(1000, 9999);
        phone_verification_code::create([
            'code' => $verificationCode,
            'phone' => $user->phone_number,
        ]);
        $phoneNumber = $request->phone_number;
        $message = "Your verification code is: $verificationCode";
        try {
            $this->whatsAppService->sendMessage($phoneNumber, $message);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'User registered but failed to send WhatsApp verification.',
                'error' => $e->getMessage(),
            ], 500);
        }
        return [
            'message' => 'User registered successfully. Please verify your email by code.',
            'user' => $user,
            'access_token' => $accessToken,
        ];
    }
    public function verifyUserByCode($code)
        {
            $verification = phone_verification_code::where('code', $code)->first();
            if (!$verification) {
                return ['status' => false, 'message' => 'Invalid verification code'];
            }
            $user = User::where('phone_number', $verification->phone)->first();
            if (!$user) {
                return ['status' => false, 'message' => 'User not found'];
            }
            $user->email_verified_at = Carbon::now();
            $user->save();
            $verification->delete();
            return ['status' => true, 'message' => 'Account verified successfully', 'user' => $user];
        }

    public function loginUser(Request $request)
    {

        $request->validate([
            'phone_number' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('phone_number', $request->phone_number)->first();

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        if ($user->email_verified_at == null) {
            return response()->json([
                'message' => 'Your email address is not verified',
            ], 403);
        }

        if (Auth::guard('web')->attempt($request->only('phone_number', 'password'))) {
            config(['auth.guards.api.provider' => 'users']);
            $user = User::query()->select('users.*')->find(Auth::guard('web')->user()->id);
            $success = $user;
            $success['token'] = $user->createToken('MyApp', ['user'])->accessToken;

            return response()->json([
                'status' => 200,
                'data' => $success
            ], 200);
        } else {
            return response()->json(['message' => 'Unauthorized'], 401);
        }
    }
    public function googleLogin(Request $request)
    {
        $request->validate([
            'idToken' => 'required|string',
        ]);

        // Prepare Google Client
        $googleClient = new Google_Client(['client_id' => env('GOOGLE_CLIENT_ID')]);

        // Verify idToken
        try {
            $payload = $googleClient->verifyIdToken($request->idToken);
        } catch (Exception $e) {
            return response()->json(['error' => 'Invalid ID Token'], 400);
        }

        if ($payload) {
            // Check if the user exists, else create a new one
            $user = User::where('email', $payload['email'])->first();

            if (!$user) {
                $user = User::create([
                    'name' => $payload['name'],
                    'email' => $payload['email'],
                    'password' => bcrypt(Str::random(24)),  // Generate a random password
                ]);
            }

            // Log the user in
            Auth::login($user);

            // Create a new token for the user
            $token = $user->createToken('MyApp')->accessToken;

            return response()->json([
                'message' => 'User logged in successfully',
                'user' => $user,
                'token' => $token
            ], 200);
        }

        return response()->json(['error' => 'Invalid ID Token'], 400);
    }
}
