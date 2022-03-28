<?php

namespace App\Http\Controllers;

use App\Models\UserCar;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class UserCarController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // GET /cars

        // Authenticate - Check logged in User ID - Admin only

        // Get all records
        $cars = UserCar::all();

        // Set default response
        $response_text = "Notifications retrieved successfully.";
        $response_code = Response::HTTP_OK;

        // Return response
        return response()->json([
            'cars' => $cars,
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
        // POST /cars

        // Validate the request
        $validated = $request->validate([
            'vehicle_model' => 'required|string',
            'vehicle_color' => 'required|string',
            'license_plate' => 'required|string',
            'seat_capacity' => 'required|integer',
            'user_id' => 'required|string',
        ]);

        // Set default response
        $car = '';
        $response_text = "New Car stored successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            // Authenticate - Check logged in User ID
            // Validate - If User ID exists
            $user = User::find($validated['user_id']);

            if ($user) {
                $car = UserCar::create($validated);
            } else {
                $response_text = "New Car store error.";
                $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
            }
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "New Car store error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response()->json([
            'cars' => $car,
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
        // GET /cars/id

        // Authenticate - Check logged in User ID - Admin or User only

        // Set default response
        $response_text = "Car retrieved successfully.";
        $response_code = Response::HTTP_OK;

        try {
            // Get single record
            $car = UserCar::find($id);
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Car retrieve error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response
        return response()->json([
            'cars' => $car,
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
        // PUT/PATCH /cars/id

        // Authenticate - Check logged in User ID - Admin or User only

        // Validate the request
        $validated = $request->validate([
            'vehicle_model' => 'required|string',
            'vehicle_color' => 'required|string',
            'license_plate' => 'required|string',
            'seat_capacity' => 'required|integer',
        ]);

        // Set default response
        $cars = [];
        $response_text = "Car updated successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            // Update record
            $cars = UserCar::find($id);
            $cars->vehicle_model = $validated['vehicle_model'];
            $cars->vehicle_color = $validated['vehicle_color'];
            $cars->license_plate = $validated['license_plate'];
            $cars->seat_capacity = $validated['seat_capacity'];
            $cars->save();
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Car update error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response()->json([
            'cars' => $cars,
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
        // DELETE /cars/id

        // Authenticate - Check logged in User ID - Admin only

        // Set default response
        $response_text = "Car deleted successfully.";
        $response_code = Response::HTTP_OK;

        // Delete record from database
        try {
            $car = UserCar::find($id);
            $car->delete();
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Car delete error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response($response_text, $response_code)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Get user car
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function user_car(Request $request)
    {
        // GET /cars/user

        // Authenticate - Check logged in User ID - Admin or User only

        // Set default response
        $response_text = "Car updated successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            // Update record
            $cars = UserCar::where('user_id', Auth::id())
                ->get();
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Car update error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response()->json([
            'cars' => $cars,
            'response_text' => $response_text,
            'response_code' => $response_code
        ]);
    }
}
