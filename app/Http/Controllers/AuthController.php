<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;


class AuthController extends Controller
{

    public function register(Request $request){
        $user = User::create([
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'password' => bcrypt($request->input('password'))
        ]);
        return $user;
    }

    public function user(){
        if(Auth::user()->getStatus() == 'enabled'){
            return Auth::user();
        }
    }

    // public function login(Request $request){
    //     if(!Auth::attempt($request->only('email','password'))){
    //         return response(['message'=>'Invalid Credentials!'],Response::HTTP_UNAUTHORIZED);
    //     }
    //     $user = Auth::user();
    //     $token = $user -> createToken('JwtToken')->plainTextToken;
    //     // $cookie = cookie('jwt',$token,60*24);
    //     // return response(['message' => 'Success','jwt' => $token])->withCookie($cookie) ; 
    //     return response(['user' => $user,'jwt' => $token]); 
    // }


    public function login(Request $request) {
        if (!Auth::attempt($request->only('email', 'password'))) {
            return response(['message' => 'Invalid Credentials!'], Response::HTTP_UNAUTHORIZED);
        }
    
        $user = Auth::user();
        $token = $user->createToken('JwtToken')->plainTextToken;
    
        // Get expiration time in minutes from config/sanctum.php
        $expirationMinutes = config('sanctum.expiration');
    
        // Calculate the expiration timestamp (null if no expiration is set)
        $expiresAt = $expirationMinutes ? now()->addMinutes($expirationMinutes) : null;
    
        return response([
            'user' => $user,
            'jwt' => $token,
            'exp' => $expiresAt ? $expiresAt->timestamp : null, // Return UNIX timestamp
        ]);
    }
    





    public function logout(Request $request): RedirectResponse 
        {
            Auth::logout();
        
            $request->session()->invalidate();
        
            $request->session()->regenerateToken();
        
            return redirect('/');
        }
}
