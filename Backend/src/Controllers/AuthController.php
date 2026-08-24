<?php
namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Services\AuthService;

class AuthController
{
    public function login(Request $req): void
    {
        $email = $req->input('email'); $pass = $req->input('password');
        if (!$email || !$pass) Response::error('VALIDATION_ERROR','email and password required',422);
        $svc = new AuthService();
        $res = $svc->login($email,$pass);
        if (!$res) Response::error('UNAUTHORIZED','Invalid credentials',401);
        setcookie('refreshToken',$res['refreshToken'], time()+604800, '/', '', isset($_SERVER['HTTPS']), true);
        Response::json(['data'=>['accessToken'=>$res['accessToken'],'expiresIn'=>900,'user'=>['id'=>$res['user']['id'],'email'=>$res['user']['email'],'rol'=>$res['user']['rol']]]]);
    }
    public function refresh(Request $req): void
    {
        $raw = $req->cookie('refreshToken') ?? $req->input('refreshToken');
        if (!$raw) Response::error('UNAUTHORIZED','Missing refresh token',401);
        $svc = new AuthService();
        $res = $svc->refresh($raw);
        if (!$res) Response::error('UNAUTHORIZED','Invalid refresh token',401);
        setcookie('refreshToken',$res['refreshToken'], time()+604800, '/', '', isset($_SERVER['HTTPS']), true);
        Response::json(['data'=>['accessToken'=>$res['accessToken'],'expiresIn'=>900]]);
    }
    public function logout(Request $req): void
    {
        $raw = $req->cookie('refreshToken') ?? $req->input('refreshToken');
        if ($raw) (new AuthService())->logout($raw);
        setcookie('refreshToken','', time()-3600, '/', '', false, true);
        Response::json(['data'=>['message'=>'Logged out']]);
    }
}
