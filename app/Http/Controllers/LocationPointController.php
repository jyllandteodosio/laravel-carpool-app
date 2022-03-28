<?php

namespace App\Http\Controllers;

use App\Models\DriverRoute;
use App\Models\LocationPoint;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;


class LocationPointController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // GET /location-points

        // Authenticate - Check logged in User ID - Admin only

        // Return all records
        $location_points = LocationPoint::all();

        // Set default response
        $response_text = "Location Points retrieved successfully.";
        $response_code = Response::HTTP_OK;

        // Return response
        return response()->json([
            'location_points' => $location_points,
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
        // POST /location-points

        // Validate the request
        $validated = $request->validate([
            'description' => 'required|string',
            'points' => 'required|integer',
            'long' => 'required|string',
            'lat' => 'required|string',
            'type' => 'required|string', // origin, pickup, destination
            'route_order' => 'required|integer',
            'driver_route_id' => 'required|string',
        ]);

        // Set default response
        $location_point = '';
        $response_text = "New Location Point stored successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            // Validate - If Driver Route exists
            $driver_route = DriverRoute::find($validated['driver_route_id']);

            if ($driver_route) {
                $location_point = LocationPoint::create($validated);
            } else {
                $response_text = "New Location Point store error.";
                $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
            }
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "New Location Point store error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response()->json([
            'location_points' => $location_point,
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
        // GET /location-points/id

        // Authenticate - Check logged in User ID - Admin or User only

        // Set default response
        $response_text = "Location Point retrieved successfully.";
        $response_code = Response::HTTP_OK;

        try {
            // Get single record
            $location_points = LocationPoint::find($id);
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Location Point retrieve error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response
        return response()->json([
            'location_points' => $location_points,
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
        // PUT/PATCH /location-points/id

        // Authenticate - Check logged in User ID - Admin or User only

        // Validate the request
        $validated = $request->validate([
            'description' => 'required|string',
        ]);

        // Set default response
        $response_text = "Location Point updated successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            // Update record
            $location_point = LocationPoint::find($id);
            $location_point->description = $validated['description'];
            $location_point->save();
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Location Point update error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response()->json([
            'location_points' => $location_point,
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
        // DELETE /location-points/id

        // Authenticate - Check logged in User ID - Admin only

        // Set default response
        $response_text = "Location Point deleted successfully.";
        $response_code = Response::HTTP_OK;

        // Delete record from database
        try {
            $location_point = LocationPoint::find($id);
            $location_point->delete();
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Location Point delete error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response($response_text, $response_code)
            ->header('Content-Type', 'text/plain');
    }
}
