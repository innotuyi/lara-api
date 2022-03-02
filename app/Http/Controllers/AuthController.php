<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;
use App\Http\Requests;
use Tymon\JWTAuth\Exceptions\JWTException;
use JWTAuth;

class AuthController extends Controller
{

/**
     * @OA\Post (
     *     path="/api/v1/user",
     *     tags={"user"},
     *     summary = "Creating new user",
     *     security={{"apiAuth":{} }},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="array",
     *                       @OA\Items(
     *                      @OA\Property(
     *                          property="name",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="email",
     *                          type="email"
     *                      ),
     *                      @OA\Property(
     *                          property="password",
     *                          type="string"
     *                      ),
     *                     ),
     *                 ),
     *                 example={
     *                     "name":"Innocent",
    *                      "email":"innocent@gmail.com",
    *                       "password":"test123",
     *                    }
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="success",
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="number", example="innocent"),
     *              @OA\Property(property="email", type="string", example="innocent@gmail.om"),
     * 
   
     * 
     *          )
     *      ),
        *      @OA\Response(
     *          response=201,
     *          description="User created",
     *          @OA\JsonContent(
     *              @OA\Property(property="name", type="number", example="innocent"),
     *              @OA\Property(property="email", type="string", example="innocent@gmail.om"),
     * 
   
     * 
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="invalid",
     *          @OA\JsonContent(
     *              @OA\Property(property="msg", type="string", example="fail"),
     *          )
     *      ),
     *      @OA\Response(
 *    response=401,
 *    description="UnAuthorized",
 *    @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="UnAuthanticated"),
 *    )
 * ),
 *  @OA\Response(
 *    response=500,
 *    description="Returns when there is server problem",
 *    @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Server Error"),
 *    )  
 * ),
 *  @OA\Response(
     *          response=419,
     *          description="CSRF Token mismatch",
     *          @OA\JsonContent(
     *              @OA\Property(property="msg", type="string", example="fail"),
     *          )
     *      ),
     *     @OA\SecurityScheme(
     *     type="http",
     *     description="Login with email and password to get the authentication token",
     *     name="Token based Based",
     *     in="header",
     *     scheme="bearer",
     *     bearerFormat="JWT",
     *     securityScheme="apiAuth",
     * )   
     * )
    */




    
    public function store(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:5'
        ]);

        $name = $request->input('name');
        $email = $request->input('email');
        $password = $request->input('password');

        $user = new User([
            'name' => $name,
            'email' => $email,
            'password' => bcrypt($password)
        ]);

        if ($user->save()) {
            $user->signin = [
                'href' => 'api/v1/user/signin',
                'method' => 'POST',
                'params' => 'email, password'
            ];
            $response = [
                'msg' => 'User created',
                'user' => $user
            ];
            return response()->json($response, 201);
        }

        $response = [
            'msg' => 'An error occurred'
        ];

        return response()->json($response, 404);
    }

/**
     * Sign in a User
     * @OA\Post (
     *     path="/api/v1/user/signin",
     *     tags={"user"},
     *     security={{"bearer":{} }},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="array",
     *                       @OA\Items(
     *                      @OA\Property(
     *                          property="email",
     *                          type="email"
     *                      ),
     *                      @OA\Property(
     *                          property="password",
     *                          type="password"
     *                      ),
     *                     ),
     *                 ),
     *                 example={
     *                     "email":"innocent@gmail.com",
    *                      "password":"innocent123",
     *                    }
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="success",
     *          @OA\JsonContent(
     *              @OA\Property(property="token", type="text", example="eyJ0eXAiOiJKV1QiLCJhbGciOiJIUzI1NiJ9.eyJpc3MiOiJodHRwOlwvXC8xMjcuMC4wLjE6ODAwMFwvYXBpXC92MVwvdXNlclwvc2lnbmluIiwiaWF0IjoxNjQ2MTY1OTUwLCJleHAiOjE2NDYxNjk1NTAsIm5iZiI6MTY0NjE2NTk1MCwianRpIjoiaU1uc3p5YlQxdGJBOXZDQSIsInN1YiI6MTMsInBydiI6Ijg3ZTBhZjFlZjlmZDE1ODEyZmRlYzk3MTUzYTE0ZTBiMDQ3NTQ2YWEifQ.ObJXwVHouaajvWmzVUJNRw4EEAXHn3ndznQF82IsxSQ"),
     *           
     * 
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="invalid",
     *          @OA\JsonContent(
     *              @OA\Property(property="msg", type="string", example="fail"),
     *          )
     *      ),
     *      @OA\Response(
 *    response=401,
 *    description="UnAuthorized",
 *    @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="UnAuthanticated"),
 *    )
 * ),
 *  @OA\Response(
 *    response=500,
 *    description="Returns when there is server problem",
 *    @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Server Error"),
 *    )  
 * ),
 *  @OA\Response(
 *          response=419,
 *          description="CSRF Token mismatch",
 *          @OA\JsonContent(
 *              @OA\Property(property="msg", type="string", example="fail"),
 *          )
 *      ),   
 * )
 * @OA\SecurityScheme(
 *     type="http",
 *     description="Login with email and password to get the authentication token",
 *     name="Token based Based",
 *     in="header",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="apiAuth",
 * )
    */
    

    public function signin(Request $request)
    {
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required'
        ]);

   
        $credentials = $request->only('email', 'password');
        
        try {
            if (! $token = JWTAuth::attempt($credentials)) {
                return response()->json(['msg' => 'Invalid credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['msg' => 'Could not create token'], 500);
        }
        return response()->json(['token' => $token]);
    }
}