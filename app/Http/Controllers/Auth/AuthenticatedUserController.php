<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class AuthenticatedUserController extends Controller
{
    public function __invoke(Request $request)
    {
        return $request->user();
    }
}
