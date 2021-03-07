<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\User;

class UserController extends Controller
{
    public function detail()
    {
        $user = User::find(1);
        return response()->json(['nama' => $user->nama], 200);
    }
}
