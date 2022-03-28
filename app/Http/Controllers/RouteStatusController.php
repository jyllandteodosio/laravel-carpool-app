<?php

namespace App\Http\Controllers;

use App\Models\RouteStatus;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class RouteStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // GET /route-statuses

        // Return all records
        return RouteStatus::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        
        // POST /route-statuses

        // Set default response
        $response_text = "New Route Status stored successfully.";
        $response_code = Response::HTTP_OK;

        // Validate the request
        $request->validate([
            'route_status' => 'required|string',
        ]);

        // Assign validated input
        $route_status = $request->input('route_status');

        // Store input to database
        try {
            RouteStatus::create([
                'route_status' => $route_status,
            ]);

        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "New Route Status store error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response($response_text, $response_code)
        ->header('Content-Type', 'text/plain');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        // GET /route-statuses/id

        // Return single record
        return RouteStatus::find($id);
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
        // PUT/PATCH /route-statuses/id

        // Set default response
        $response_text = "Route Status updated successfully.";
        $response_code = Response::HTTP_OK;

        // Validate the request
        $request->validate([
            'route_status' => 'required|string',
        ]);

        // Assign validated input
        $route_status = $request->input('route_status');

        // Store input to database
        try {
            $route = RouteStatus::find($id);
            $route->route_status = $route_status;
            $route->save();

        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Route Status update error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response($response_text, $response_code)
        ->header('Content-Type', 'text/plain');
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        // DELETE /route-statuses/id

        // Set default response
        $response_text = "Route Status deleted successfully.";
        $response_code = Response::HTTP_OK;

        // Delete record from database
        try {
            $route = RouteStatus::find($id);
            $route->delete();

        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Route Status delete error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response($response_text, $response_code)
        ->header('Content-Type', 'text/plain');
    }
}
