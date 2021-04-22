<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\HttpFoundation\Response;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Facades\JWTFactory;
use Tymon\JWTAuth\JWTAuth as JWTAuthJWTAuth;

class UserController extends Controller
{

    // ini untuk formatter
    public function ResponseUserFormatter($messages, $status, $code, $data)
    {
        $response = [
            'meta' => [
                'messages' => $messages,
                'status' => $status,
                'code' => $code,
            ],
            'data' => $data
        ];

        return $response;
    }


    public function UserFormatter($data)
    {
        $response = [
            'username' => $data['username'],
            'name' => $data['name'],
            'email' => $data['email']
        ];

        return $response;
    }


    // register user
    public function store(Request $request)
    {
        // untuk register user
        $validator = Validator::make($request->all(), [
            'username' => ['required'],
            'name' => ['required'],
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);


        // jika validator gagal dijalankan
        if ($validator->fails()) {
            $response = $this->ResponseUserFormatter('failed validation data', 'failed', Response::HTTP_UNPROCESSABLE_ENTITY, $validator->errors());
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }


        // tangkap request
        $userReq = [
            'username' => $request->username,
            'name' => $request->name,
            'email' => $request->email,
            'password' => $request->password
        ];

        // check apakah ada email atau username yang sama
        $sameUsername = User::where('username', $userReq['username'])->take(1)->get();
        $sameEmail = User::where('email', $userReq['email'])->take(1)->get();

        if (!$sameEmail->isEmpty() || !$sameUsername->isEmpty()) {
            $response = $this->ResponseUserFormatter('username or email must be unique', 'failed', Response::HTTP_UNPROCESSABLE_ENTITY, null);
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        // apabila email atau username belum pernah didaftarkan
        // enkrip dulu passwordnya
        try {
            $userReq['password'] = password_hash($userReq['password'], PASSWORD_DEFAULT);
            $registeredUser = User::create($userReq);

            $response = $this->ResponseUserFormatter('user success to registered', 'success', Response::HTTP_OK, $registeredUser);
            return response()->json($response, Response::HTTP_OK);
        } catch (QueryException $e) {
            $response = $this->ResponseUserFormatter('user failed to registered', 'failed', Response::HTTP_UNPROCESSABLE_ENTITY, $e->errorInfo);
            return response()->json($response, Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


    public function show(User $user)
    {
        //
    }


    public function edit(User $user)
    {
        //
    }


    public function update(Request $request, User $user)
    {
        //
    }


    public function destroy(User $user)
    {
        //
    }

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required']
        ]);

        $userReq = $request->only('email', 'password');

        $isLogin = User::where('email', $userReq['email'])->take(1)->get();

        // check login
        if ($isLogin->isEmpty()) {
            $response = $this->ResponseUserFormatter('failed login', 'failed', Response::HTTP_UNAUTHORIZED, null);
            return response()->json($response, Response::HTTP_UNAUTHORIZED);
        } else {
            // check password
            if (password_verify($userReq['password'], $isLogin[0]['password'])) {
                $isLogin = $isLogin[0];

                // custom claims jwt
                $claims = [
                    'username' => $isLogin['username'],
                    'email' => $isLogin['email'],
                    'name' => $isLogin['name'],
                ];
                // create token jwt
                try {
                    if (!$token = JWTAuth::customClaims($claims)->attempt($userReq)) {
                        return response()->json([
                            'success' => false,
                            'message' => 'Login credentials are invalid.',
                        ], 400);
                    }
                } catch (JWTException $e) {
                    return $userReq;
                    return response()->json([
                        'success' => false,
                        'message' => 'Could not create token.',
                    ], 500);
                }
                
                $userLogedIn = [
                    'username' => $isLogin['username'],
                    'email' => $isLogin['email'],
                    'name' => $isLogin['name'],
                    'token' => $token
                ];

                $response = $this->ResponseUserFormatter('success login', 'success', Response::HTTP_OK, $userLogedIn);
                return response()->json($response, Response::HTTP_OK);
            } else {
                $response = $this->ResponseUserFormatter('failed login', 'failed', Response::HTTP_UNAUTHORIZED, null);
                return response()->json($response, Response::HTTP_UNAUTHORIZED);
            }
        }
    }
}
