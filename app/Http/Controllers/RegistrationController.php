<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Requests;

use App\Meeting;
use App\User;
use JWTAuth;

class RegistrationController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth');
    }


    /**
     * Register for Meeting
     * @OA\Post (
     *     path="/api/v1/meeting/registration",
     *     tags={"registration"},
     *     security={{"apiAuth":{} }},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="array",
     *                       @OA\Items(
     *                      @OA\Property(
     *                          property="user_id",
     *                          type="number"
     *                      ),
     *                      @OA\Property(
     *                          property="meeting_id",
     *                          type="number"
     *                      ),
     *                     ),
     *                 ),
     *                 example={
     *                     "user_id":1,
    *                      "meeting_id":1,
     *                    }
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="success",
     *          @OA\JsonContent(
     *              @OA\Property(property="user_id", type="number", example=1),
     *              @OA\Property(property="meeting_id", type="number", example=1),
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
 *     )
 *     @OA\SecurityScheme(
    *     type="http",
 *     description="Login with email and password to get the authentication token",
 *     name="Token based Based",
 *     in="header",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="apiAuth",
 * )
    */
    public function store(Request $request)
    {
        $this->validate($request, [
            'meeting_id' => 'required',
            'user_id' => 'required',
        ]);

        $meeting_id = $request->input('meeting_id');
        $user_id = $request->input('user_id');

        $meeting = Meeting::findOrFail($meeting_id);
        $user = User::findOrFail($user_id);

        $message = [
            'msg' => 'User is already registered from meeting',
            'user' => $user,
            'meeting' => $meeting,
            'unregister' => [
                'href' => 'api/v1/meeting/registration/' . $meeting->id,
                'method' => 'DELETE',
            ]
        ];
        if ($meeting->users()->where('users.id', $user->id)->first()) {
            return response()->json($message, 404);
        };

        $user->meetings()->attach($meeting);

        $response = [
            'msg' => 'User registered for meeting',
            'meeting' => $meeting,
            'user' => $user,
            'unregister' => [
                'href' => 'api/v1/meeting/registration/' . $meeting->id,
                'method' => 'DELETE'
            ]
        ];

        return response()->json($response, 201);
    }

/**
     * unregister for meeting
     * @OA\Delete (
     *     path="/api/v1/meeting/registration/{id}",
     *     tags={"registration"},
     *     security={ {"bearer": {} }},
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="success",
     *         @OA\JsonContent(
     *             @OA\Property(property="msg", type="string", example="Employee deletion success")
     *         )
     *     ),
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
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $meeting = Meeting::findOrFail($id);
        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['msg' => 'User not found'], 404);
        }
        if (!$meeting->users()->where('users.id', $user->id)->first()) {
            return response()->json(['msg' => 'user not registered for meeting, delete operation not successful'], 401);
        };

        $meeting->users()->detach($user->id);

        $response = [
            'msg' => 'User unregistered for meeting',
            'meeting' => $meeting,
            'user' => $user,
            'register' => [
                'href' => 'api/v1/meeting/registration',
                'method' => 'POST',
                'params' => 'user_id, meeting_id'
            ]
        ];

        return response()->json($response, 200);
    }
}