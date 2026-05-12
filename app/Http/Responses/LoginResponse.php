<?php

namespace App\Http\Responses;
use Illuminate\Http\JsonResponse;
use Laravel\Fortify\Contracts\LoginResponse as LoginResponseContract;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        $user = auth()->user();

        $redirectRoute = match ($user->role) {
            'rlqh' => route('rlqh.tasks.index'),
            'admin', 'user' => route('dashboard'),
            default => route('login'),
        };

        return $request->wantsJson()
            ? new JsonResponse(['redirect' => $redirectRoute], 200)
            : redirect()->intended($redirectRoute); 
    }
}
