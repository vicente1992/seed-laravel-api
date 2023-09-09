<?php

namespace Modules\Auth\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Modules\Auth\Http\Requests\LoginRequest;

class AuthController extends Controller
{

    function Login(LoginRequest $request)
    {
        $clientID = env('SECURE_CLIENT_ID');

        $query = DB::table('oauth_clients')
            ->where('password_client', 1)
            ->where('revoked', 0);

        if (!empty($clientID))
            $query = $query->where('id', $clientID);
        $client = $query->first();
        $tokenRequest = $request->create('/oauth/token', 'POST', [
            'grant_type' => 'password',
            'client_id' => $client->id,
            'client_secret' => $client->secret,
            'username' => $request->input('email'),
            'password' => $request->input('password'),
            'scope' => '',
        ]);

        $response = app()->handle($tokenRequest);
        if ($response->getStatusCode() != 200) {
            return response()->json(['message' => 'Invalid credentials'], $response->getStatusCode());
        }

        $content = json_decode($response->getContent());
        $user = User::where('email', $request->input('email'))->first();

        $result = [
            'access_token' => $content->access_token,
            'user' => $user->toArray(),
        ];

        return response()->json($result, $response->getStatusCode());
    }



    public function logout(Request $request): JsonResponse
    {
        $request->user()->token()->revoke();
        return response()->json(true, 202);
    }
}
