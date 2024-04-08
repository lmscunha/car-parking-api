<?php

namespace App\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * @group Auth
 */
class LoginController extends Controller
{
    public function __invoke(Request $request)
    {
        $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['The provided credentials are incorrect.']
            ]);
        }

        $device = substr($request->userAgent() ?? '', 0, 255);
        $expireAt = $request->remember ? null : now()->addMinutes(intval(config('session.lifetime'), 10));

        return response()->json([
            'access_token' => $user->createToken($device, expiresAt: $expireAt)->plainTextToken,
        ], Response::HTTP_CREATED);
    }
}
