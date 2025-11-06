<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class AuthController extends BaseController
{
	use AuthorizesRequests, ValidatesRequests;

	public function login(Request $request)
	{
		return response()->json([
			'message' => 'Login works!',
			'data' => $request->all()
		]);
	}
}