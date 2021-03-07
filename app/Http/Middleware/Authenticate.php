<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        if (! $request->expectsJson()) {
            return response()->json([
                'response_code' => 401,
                'status' => 'failure',
                'message' => 'Tidak ada authentikasi user',
                'error' => [],
                'token' => '',
                'user_id' => '',
                'name' => '',
                'alamat' => '',
                'chat_id' => '',
                'pin' => '',
                'email' => '',
                'nohp' => '',
                'jeniskelamin' => ''
            ],200);
        }
    }
}
