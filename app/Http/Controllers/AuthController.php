<?php

namespace App\Http\Controllers;


use App\Mail\sendcodeverificationemail;
use App\Models\Address;
use App\Models\email_verification_code;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;

class AuthController extends Controller
{
    public function userRegister(Request $request)
    {
        $request->validate([
            'name'=>'required',
            'phone_number' => 'required|numeric',
            'national_number' => 'required|numeric|integer',
            'email'=>'required|email|unique:users',

            'password' => ['required',
                'min:6',             // must be at least 10 characters in length
                'regex:/[a-z]/', // must contain at least one lowercase letter
                'regex:/[0-9]/',      // must contain at least one digit
                'regex:/[@$!%*#?&]/', // must contain a special character
                'confirmed',
            ],
            'city' => 'required|string',
            'town' => 'required|string',
        ]);
        $user=new User();
        $user->name=$request->name;
        $user->email=$request->email;
        $user->password=bcrypt($request->password);
        $user->phone_number=$request->phone_number;
        $user->national_number=$request->national_number;
        $user->save();
        Address::create([
            'city' => $request->input('city'),
            'town' => $request->input('town'),
            'user_id' => $user->id,
        ]);
        $accesstoken= $user->createToken('MyApp')->accessToken;

        //generate random code
        $data['code']= mt_rand(1000 , 9999);
        $data['email']=$user->email;

        // create a new code
        $codeData= email_verification_code::query()->create($data);

        //send email to user
        Mail::to($user->email)->send(new sendcodeverificationemail($codeData['code']));

        return response()->json([
            'messsage' =>'User registered successfully. Please verify your email by code ',
            'user'=>$user,
            'access_Token' => $accesstoken,
        ]);
    }

    public function userLogin(Request $request){
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

    public function userCheckCodeemailverification(Request $request){
        $request->validate([
            'code'=>'required|string|exists:email_verification_codes',
        ]);
        //find the code
        $verifyemail= email_verification_code::query()->firstWhere('code',$request['code']);
        $user = User::where('email', $verifyemail['email'])->first();


        //check if it is not expired the is one hour
        if($verifyemail['created_at'] > now()->addHour()){
            $verifyemail->delete();
            return response()->json([
                'message' => trans('code.is.expire')
            ],422);
        }
        else{
            $user->email_verified_at=now();
            $user->save();
            return response()->json([
                'code' => $verifyemail['code'],
                'message' => trans('code.is.valid'),
            ]);}
    }

}
