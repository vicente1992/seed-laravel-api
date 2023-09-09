<?php

namespace Modules\Auth\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;

class RegisterController extends Controller
{

    public function register(Request $request): JsonResponse
    {
        $valid = validator($request->only('email', 'name', 'password'), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($valid->fails()) {
            return  response()->json($valid->errors()->all(), 400);
        }

        $data = request()->only('email', 'name', 'password');

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'],
            'password' => bcrypt($data['password']),
        ]);


        $client = DB::table('oauth_clients')
            ->where('password_client', 1)->first();

        $tokenRequest = $request->create('/oauth/token', 'POST', [
            'grant_type'    => 'password',
            'client_id'     => $client->id,
            'client_secret' => $client->secret,
            'username'      => $data['email'],
            'password'      => $data['password'],
            'scope'         => null,
        ]);
        $response = app()->handle($tokenRequest);
        $content = json_decode($response->getContent());
        $result = [
            'access_token' => $content->access_token,
            'user' => $user->toArray(),
        ];

        return response()->json($result, $response->getStatusCode());
    }
}
