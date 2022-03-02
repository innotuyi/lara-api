<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;

use App\Meeting;
use JWTAuth;

class MeetingController extends Controller
{
    public function __construct()
    {
        $this->middleware('jwt.auth', ['only' => [
            'update', 'store', 'destroy'
        ]]);
    }
    
/**
    * @OA\Get(
        *     path="/api/v1/meeting",
        *     tags={"meeting"},
        *     summary="List of all Meetings",
*    @OA\Response(
 *    response=401,
 *    description="UnAuthorized",
 *    @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="UnAuthanticated"),
 *    )
 * ),
 *   @OA\Response(
 *    response=500,
 *    description="Returns when there is server problem",
 *    @OA\JsonContent(
 *       @OA\Property(property="message", type="string", example="Server Error"),
 *    )  
 * ),
 *   @OA\Response(
*     response="200",
*     description="Returns Meeting list",
*     @OA\JsonContent(
*       type="array",
*       @OA\Items(
 *           @OA\Property(
 *                         property="title",
 *                         type="string",
 *                         example="UI/ux Meeting"
 *                      ),
 *                      @OA\Property(
 *                         property="description",
 *                         type="string",
 *                         example="all about ui/ux meeting"
 *                      ),
 *                      @OA\Property(
 *                         property="time",
 *                         type="date",
 *                         example="2022-01-30 13:30:00"
 *                      ),
* )
*     )
*   )
     * )

 */
    public function index()

    {
        $meetings = Meeting::all();
        foreach ($meetings as $meeting) {
            $meeting->view_meeting = [
                'href' => 'api/v1/meeting/' . $meeting->id,
                'method' => 'GET'
            ];
        }

        $response = [
            'msg' => 'List of all Meetings',
            'meetings' => $meetings
        ];
        return response()->json($response, 200);
    }

 /**
     * Create an Meeting
     * @OA\Post (
     *     path="/api/v1/meeting",
     *     tags={"meeting"}, 
     *      security={ {"apiAuth": {} }},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="array",
     *                       @OA\Items(
     *                      @OA\Property(
     *                          property="title",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="description",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="time",
     *                          type="date"
     *                      ),
     *                     ),
     *                 ),
  
     *                 example={
     *                     "time":"202201301330CET",
    *                      "title":"Cool meeting",
    *                       "description":"cool meeting today"
     *                    }
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="success",
     *          @OA\JsonContent(
     *              @OA\Property(property="title", type="number", example="one meeting"),
     *              @OA\Property(property="description", type="string", example="another meeting"),
     *              @OA\Property(property="time", type="string", example="202201301330CET"),
     * 
     *          )
     *      ),
     *           @OA\Response(
     *          response=201,
     *          description="Meeting created",
     *          @OA\JsonContent(
     *              @OA\Property(property="title", type="number", example="one meeting"),
     *              @OA\Property(property="description", type="string", example="another meeting"),
     *              @OA\Property(property="time", type="string", example="202201301330CET"),
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
     * 
 *     @OA\SecurityScheme(
 *     type="http",
 *     description="Login with email and password to get the authentication token",
 *     name="Token based Based",
 *     in="header",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     securityScheme="apiAuth",
 * )
  )
    */


    
    public function store(Request $request)
    {
        $this->validate($request, [
            'title' => 'required',
            'description' => 'required',
            'time' => 'required|date_format:YmdHie'
        ]);

        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['msg' => 'User not found'], 404);
        }

        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $user->id;

        $meeting = new Meeting([
            'time' => Carbon::createFromFormat('YmdHie', $time),
            'title' => $title,
            'description' => $description
        ]);
        if ($meeting->save()) {
            $meeting->users()->attach($user_id);
            $meeting->view_meeting = [
                'href' => 'api/v1/meeting/' . $meeting->id,
                'method' => 'GET'
            ];
            $message = [
                'msg' => 'Meeting created',
                'meeting' => $meeting
            ];
            return response()->json($message, 201);
        }

        $response = [
            'msg' => 'Error during creation'
        ];

        return response()->json($response, 404);
    }
 /**
    * Display specific meeting.
    *
    * @return \Illuminate\Http\Response
    * @param  int  $id
     * @OA\Get (
     *     path="/api/v1/meeting/{id}",
     *     tags={"meeting"},
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
     *              @OA\Property(property="title", type="number", example=1),
     *              @OA\Property(property="description", type="string", example="title"),
     *              @OA\Property(property="time", type="string", example="content@gmil.com"),
     *           
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
 * )   
     * )
     */


    
    public function show($id)
    {
        $meeting = Meeting::with('users')->where('id', $id)->firstOrFail();
        $meeting->view_meetings = [
            'href' => 'api/v1/meeting',
            'method' => 'GET'
        ];

        $response = [
            'msg' => 'Meeting information',
            'meeting' => $meeting
        ];
        return response()->json($response, 200);
    }


   /**
     * Update an Meeting
     * @OA\Put (
     *     path="/api/v1/meeting/{id}",
     *     tags={"meeting"},
     *     security={ {"bearer": {} }},
     *     @OA\Parameter(
     *         in="path",
     *         name="id",
     *         required=true,
     *         @OA\Schema(type="string")
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="object",
     *                      @OA\Property(
     *                          property="title",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="description",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="time",
     *                          type="date"
     *                      ),                
     *                 ),
     *                 example={
     *                     "title":"Meeting one",
     *                     "description":"Meeting one description",
     *                     "date":"202201301330CET",
     *                }
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="success",
     *          @OA\JsonContent(
     *              @OA\Property(property="title", type="number", example=1),
     *              @OA\Property(property="description", type="string", example="title"),
     *              @OA\Property(property="time", type="string", example="content@gmil.com"),
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
     */



    
    public function update(Request $request, $id)
    {
        $this->validate($request, [
            'title' => 'required',
            'description' => 'required',
            'time' => 'required|date_format:YmdHie'
        ]);

        if (! $user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['msg' => 'User not found'], 404);
        }

        $title = $request->input('title');
        $description = $request->input('description');
        $time = $request->input('time');
        $user_id = $user->id;

        $meeting = Meeting::with('users')->findOrFail($id);

        if (!$meeting->users()->where('users.id', $user_id)->first()) {
            return response()->json(['msg' => 'user not registered for meeting, update not successful'], 401);
        };
        $meeting->time = Carbon::createFromFormat('YmdHie', $time);
        $meeting->title = $title;
        $meeting->description = $description;
        if (!$meeting->update()) {
            return response()->json(['msg' => 'Error during updating'], 404);
        }

        $meeting->view_meeting = [
            'href' => 'api/v1/meeting/' . $meeting->id,
            'method' => 'GET'
        ];

        $response = [
            'msg' => 'Meeting updated',
            'meeting' => $meeting
        ];

        return response()->json($response, 200);
    }

/**
     * Delete meeting
     * @OA\Delete (
     *     path="/api/v1/meeting/{id}",
     *     tags={"meeting"},
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
            return response()->json(['msg' => 'user not registered for meeting, update not successful'], 401);
        };
        $users = $meeting->users;
        $meeting->users()->detach();
        if (!$meeting->delete()) {
            foreach ($users as $user) {
                $meeting->users()->attach($user);
            }
            return response()->json(['msg' => 'deletion failed'], 404);
        }

        $response = [
            'msg' => 'Meeting deleted',
            'create' => [
                'href' => 'api/v1/meeting',
                'method' => 'POST',
                'params' => 'title, description, time'
            ]
        ];

        return response()->json($response, 200);
    }
}