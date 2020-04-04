<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\helpers\ResponseHelper;

class AuthController extends Controller
{


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'nickname' => 'required|max:100',
                'email' => 'required|unique:users|max:150',
                'password' => 'required',
                'confirm_password' => 'required|same:password',
            ]);
        if ($validator->fails()) {
            return ResponseHelper::fail($validator->errors()->first(), ResponseHelper::UNPROCESSABLE_ENTITY_EXPLAINED);
        }

        $user = new User;
        $user->nickname = $request->nickname;
        $user->email = $request->email;
        $user->password = bcrypt($request->password);
        $user->save();

        $user->createToken('Personal Access Token')->accessToken;
        $tokens = $this->get_token($request->email, $request->password);

        $data = array(
            'user' => $user,
            'tokens' => $tokens,
        );

        return ResponseHelper::success($data);

    }


    /**
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'email' => 'required|max:150',
                'password' => 'required',
            ]);

        if ($validator->fails()) {
            return ResponseHelper::fail($validator->errors()->first(), ResponseHelper::UNPROCESSABLE_ENTITY_EXPLAINED);
        }

        $user = User::where('email', $request->email)->first();

        if ($user) {
            if (Hash::check($request->password, $user->password)) {

                $user->createToken('Personal Access Token')->accessToken;
                $tokens = $this->get_token($request->email, $request->password);

                $resp = array(
                    "user" => $user,
                    "tokens" => $tokens
                );

                return ResponseHelper::success($resp);
            }
        }
        return ResponseHelper::fail("Wrong Credentials", ResponseHelper::UNPROCESSABLE_ENTITY_EXPLAINED);
    }

    public function update(Request $request)
    {
        $validator = Validator::make($request->all(),
            [
                'nickname' => 'required|max:100',
                'password' => 'required',
                'confirm_password' => 'required|same:password',
            ]);
        if ($validator->fails()) {
            return ResponseHelper::fail($validator->errors()->first(), ResponseHelper::UNPROCESSABLE_ENTITY_EXPLAINED);
        }

        $user = User::find(Auth::user()->id);
        $user->nickname = $request->nickname;
        $user->password = bcrypt($request->password);
        $user->save();

        $data = array(
            'user' => $user,
        );

        return ResponseHelper::success($data);
    }

    /**
     * @param Request $request
     * @return mixed
     */
    public function refreshToken(Request $request)
    {
        $http = new \GuzzleHttp\Client;
        $response = $http->post(url('oauth/token'), [
            'form_params' => [
                'grant_type' => 'refresh_token',
                'refresh_token' => $request->refresh_token,
                'client_id' => env('PASS_GRAND_TOKEN_ID'),
                'client_secret' => env('PASS_GRAND_TOKEN_SECRET'),
            ],
        ]);

        return json_decode((string)$response->getBody(), true);
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function logout()
    {
        $token = Auth::guard('api')->user()->token();
        $token->revoke();

        return ResponseHelper::success(array());
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function getUser()
    {
        $user = Auth::guard('api')->user();
        return ResponseHelper::success($user);
    }

    /**
     * @param $username
     * @param $password
     * @return mixed
     */
    private function get_token($username, $password)
    {
        $http = new \GuzzleHttp\Client;
        $response = $http->post(url('oauth/token'), [
            'form_params' => [
                'grant_type' => 'password',
                'client_id' => env('PASS_GRAND_TOKEN_ID'),
                'client_secret' => env('PASS_GRAND_TOKEN_SECRET'),
                'username' => $username,
                'password' => $password,
                'scope' => '',
            ],
        ]);
        return json_decode((string)$response->getBody(), true);
    }

}
