<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class NotificationController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // GET /notifications

        // Authenticate - Check logged in User ID - Admin only

        // Get all records
        $notifications = Notification::all();

        // Set default response
        $response_text = "Notifications retrieved successfully.";
        $response_code = Response::HTTP_OK;

        // Return response
        return response()->json([
            'notifications' => $notifications,
            'response_text' => $response_text,
            'response_code' => $response_code
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // POST /notifications

        // Validate the request
        $validated = $request->validate([
            'message' => 'required|string',
            'link' => 'required|string',
            'viewed' => 'required|boolean',
            'user_id' => 'required|string',
        ]);

        // Set default response
        $notification = '';
        $response_text = "New Notification stored successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            // Authenticate - Check logged in User ID
            // Validate - If User ID exists
            Log::debug($validated['user_id']);
            if ($validated['user_id'] == 0) {
                $user = User::find(Auth::id());
            } else {
                $user = User::find($validated['user_id']);
            }

            if ($user) {
                $validated['user_id'] = $user->id;
                $notification = Notification::create($validated);
            } else {
                $response_text = "New Notification store error.";
                $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
            }
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "New Notification store error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response()->json([
            'notifications' => $notification,
            'response_text' => $response_text,
            'response_code' => $response_code
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // GET /notifications/id

        // Authenticate - Check logged in User ID - Admin or User only

        // Set default response
        $response_text = "Notification retrieved successfully.";
        $response_code = Response::HTTP_OK;

        try {
            // Get single record
            $notification = Notification::find($id);
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Notification retrieve error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response
        return response()->json([
            'notifications' => $notification,
            'response_text' => $response_text,
            'response_code' => $response_code
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        // PUT/PATCH /notifications/id

        // Authenticate - Check logged in User ID - Admin or User only

        // Validate the request
        $validated = $request->validate([
            'viewed' => 'required|boolean',
        ]);

        // Set default response
        $notifications = [];
        $response_text = "Notification updated successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            // Update record
            $notifications = Notification::find($id);
            $notifications->viewed = $validated['viewed'];
            $notifications->save();
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Notification update error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response()->json([
            'notifications' => $notifications,
            'response_text' => $response_text,
            'response_code' => $response_code
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // DELETE /notifications/id

        // Authenticate - Check logged in User ID - Admin only

        // Set default response
        $response_text = "Notification deleted successfully.";
        $response_code = Response::HTTP_OK;

        // Delete record from database
        try {
            $notification = Notification::find($id);
            $notification->delete();
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Notification delete error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response($response_text, $response_code)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Get driver routes by user.
     *
     * @return \Illuminate\Http\Response
     */
    public function user_notifications()
    {
        // GET /notifications/user/

        // Authenticate - Check logged in User ID - Admin or User only

        // Set default response
        $notifications = [];
        $response_text = "Notifications retrieved successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            // Validate - If User ID exists
            $user = User::find(Auth::id());

            if ($user) {
                // Find records
                $notifications = Notification::where('user_id', $user->id)
                    ->orderBy('created_at', 'desc')
                    ->get();
                Log::info($notifications);
            } else {
                $response_text = 'No Notifications found.';
            }
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Notification retrieve error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response()->json([
            'notifications' => $notifications,
            'response_text' => $response_text,
            'response_code' => $response_code
        ]);
    }
}
