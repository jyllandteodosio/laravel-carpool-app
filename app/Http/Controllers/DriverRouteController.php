<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\DriverRoute;
use App\Models\JobTitle;
use App\Models\LocationPoint;
use App\Models\PassengerBooking;
use App\Models\RouteStatus;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class DriverRouteController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // GET /driver-routes

        // Authenticate - Check logged in User ID - Admin only

        // Get all records
        $driver_routes = DriverRoute::all();

        // Set default response
        $response_text = "Driver Routes retrieved successfully.";
        $response_code = Response::HTTP_OK;

        // Return response
        return response()->json([
            'driver_routes' => $driver_routes,
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
        // POST /driver-routes

        // Validate the request
        $validated = $request->validate([
            'ride_date' => 'required|date',
            'ride_time' => 'required|string',
            'seat_capacity' => 'required|integer',
            'route_status_id' => 'required|string',
        ]);

        // Set default response
        $driver_route = '';
        $response_text = "New Driver Route stored successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            // Authenticate - Check logged in User ID
            // Validate - If User ID exists
            $driver_user = User::find(Auth::id());

            // Validate - If RouteStatus ID exists
            $route_status = RouteStatus::find($validated['route_status_id']);

            if ($driver_user && $route_status) {
                $validated['driver_user_id'] = $driver_user->id;
                $validated['sequence'] = "1";
                $driver_route = DriverRoute::create($validated);
            } else {
                $response_text = "New Driver Route store error.";
                $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
            }
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "New Driver Route store error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response()->json([
            'driver_routes' => $driver_route,
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
        // GET /driver-routes/id

        // Authenticate - Check logged in User ID - Admin or User only

        // Set default response
        $response_text = "Driver Route retrieved successfully.";
        $response_code = Response::HTTP_OK;

        try {
            // Get single record
            $driver_route = DriverRoute::find($id);
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Driver Route retrieve error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response
        return response()->json([
            'driver_routes' => $driver_route,
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
        // PUT/PATCH /driver-routes/id

        // Authenticate - Check logged in User ID - Admin or User only

        // Validate the request
        $validated = $request->validate([
            'route_status_id' => 'required|string',
        ]);

        // Set default response
        $driver_routes = [];
        $response_text = "Driver Route updated successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            // Validate - If RouteStatus ID exists
            $route_status = RouteStatus::find($validated['route_status_id']);

            if ($route_status) {
                // Update record
                $driver_route = DriverRoute::find($id);
                $driver_route->route_status_id = $route_status->id;
                $driver_route->save();
            } else {
                $response_text = 'No Driver Routes found.';
                $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
            }
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Driver Route update error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response()->json([
            'driver_routes' => $driver_route,
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
        // DELETE /driver-routes/id

        // Authenticate - Check logged in User ID - Admin only

        // Set default response
        $response_text = "Driver Route deleted successfully.";
        $response_code = Response::HTTP_OK;

        // Delete record from database
        try {
            $driver_route = DriverRoute::find($id);
            $driver_route->delete();
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Driver Route delete error.";
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
    public function user_routes(Request $request)
    {
        // GET /driver-routes/user/

        // Validate the request
        $validated = $request->validate([
            'ride_date' => 'required|string',
            'ride_time' => 'required|string',
            'sort' => 'required|string',
        ]);

        // Set default response
        $driver_routes = [];
        $response_text = "Driver Routes retrieved successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            // Validate - If User ID exists
            $driver_user = User::find(Auth::id());

            if ($driver_user) {
                // Find records
                $query_array = [];

                if ($validated['ride_time'] != "0") {
                    array_push($query_array, ['ride_time', '=', $validated['ride_time']]);
                } else {
                    array_push($query_array, ['ride_time', '>=', '00:00']);
                }

                if ($validated['ride_date'] != "0") {
                    array_push($query_array, ['ride_date', '=', $validated['ride_date']]);
                }

                Log::info($query_array);

                $driver_routes = DriverRoute::where('driver_user_id', $driver_user->id)
                    ->where($query_array)
                    ->where(function ($query) {
                        $query->where('route_status_id', '1')
                            ->orWhere('route_status_id', '4');
                    })
                    ->orderBy('ride_date', $validated['sort'])
                    ->get();
                // Log::info($driver_routes);

                if ($driver_routes) {
                    foreach ($driver_routes as $key => $route) {
                        $location_points = LocationPoint::where('driver_route_id', $route->id)
                            ->get();
                        // Log::info($location_points);
                        $driver_routes[$key]['location_points'] = $location_points;

                        $route_status = RouteStatus::find($route->route_status_id);
                        $driver_routes[$key]['route_status'] = $route_status->route_status;

                        $seat_approved = PassengerBooking::where('driver_route_id', $route->id)
                            ->where('booking_status_id', '2')
                            ->count();

                        $driver_routes[$key]['seat_approved'] = $seat_approved;

                        $date = Carbon::createFromFormat('Y-m-d', $route->ride_date);
                        $time = Carbon::createFromFormat('G:i:s', $route->ride_time);

                        $driver_routes[$key]['ride_date_formatted'] = $date->format('M d, Y');
                        $driver_routes[$key]['ride_time_formatted'] = $time->format('g:i A');
                    }
                }
            } else {
                $response_text = 'No Driver Routes found.';
            }
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Driver Route retrieve error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response()->json([
            'driver_routes' => $driver_routes,
            'response_text' => $response_text,
            'response_code' => $response_code
        ]);
    }

    /**
     * Get past driver routes by user.
     *
     * @return \Illuminate\Http\Response
     */
    public function user_routes_past(Request $request)
    {
        // GET /driver-routes/user/past

        // Validate the request
        $validated = $request->validate([
            'ride_date' => 'required|string',
            'ride_time' => 'required|string',
            'sort' => 'required|string',
        ]);

        // Set default response
        $driver_routes = [];
        $response_text = "Driver Routes retrieved successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            // Validate - If User ID exists
            $driver_user = User::find(Auth::id());

            if ($driver_user) {
                // Find records

                $query_array = [];

                if ($validated['ride_time'] != "0") {
                    array_push($query_array, ['ride_time', '=', $validated['ride_time']]);
                } else {
                    array_push($query_array, ['ride_time', '>=', '00:00']);
                }

                if ($validated['ride_date'] != "0") {
                    array_push($query_array, ['ride_date', '=', $validated['ride_date']]);
                }

                // Log::info($query_array);

                $driver_routes = DriverRoute::where('driver_user_id', $driver_user->id)
                    ->where($query_array)
                    ->where(function ($query) {
                        $query->where('route_status_id', '2')
                            ->orWhere('route_status_id', '5');
                    })
                    ->orderBy('ride_date', $validated['sort'])
                    ->get();
                // Log::info($driver_routes);

                if ($driver_routes) {
                    foreach ($driver_routes as $key => $route) {
                        $location_points = LocationPoint::where('driver_route_id', $route->id)
                            ->get();
                        // Log::info($location_points);
                        $driver_routes[$key]['location_points'] = $location_points;

                        $route_status = RouteStatus::find($route->route_status_id);
                        $driver_routes[$key]['route_status'] = $route_status->route_status;

                        $seat_approved = PassengerBooking::where('driver_route_id', $route->id)
                            ->where('booking_status_id', '2')
                            ->count();

                        $driver_routes[$key]['seat_approved'] = $seat_approved;

                        $date = Carbon::createFromFormat('Y-m-d', $route->ride_date);
                        $time = Carbon::createFromFormat('G:i:s', $route->ride_time);

                        $driver_routes[$key]['ride_date_formatted'] = $date->format('M d, Y');
                        $driver_routes[$key]['ride_time_formatted'] = $time->format('g:i A');
                    }
                }
            } else {
                $response_text = 'No Driver Routes found.';
            }
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Driver Route retrieve error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response()->json([
            'driver_routes' => $driver_routes,
            'response_text' => $response_text,
            'response_code' => $response_code
        ]);
    }

    /**
     * Search driver routes by date (only active routes).
     *
     * @param  Request  $request
     * @return \Illuminate\Http\Response
     */
    public function search_routes(Request $request)
    {
        // GET /driver-routes/search

        // Authenticate - Check logged in User ID - Admin or User only

        // Validate the request
        $validated = $request->validate([
            'ride_date' => 'required|string',
            'ride_time' => 'required|string',
            'sort' => 'required|string',
        ]);

        // Set default response
        $driver_routes = [];
        $response_text = "Driver Routes retrieved successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            // Validate - If User ID exists
            $passenger_user = User::find(Auth::id());

            if ($passenger_user) {
                // Find records
                $query_array = [];

                if ($validated['ride_time'] != "0") {
                    array_push($query_array, ['ride_time', '>=', $validated['ride_time']]);
                } else {
                    array_push($query_array, ['ride_time', '>=', '00:00']);
                }

                if ($validated['ride_date'] != "0") {
                    array_push($query_array, ['ride_date', '>=', $validated['ride_date']]);
                }

                Log::debug($query_array);

                $driver_routes = DriverRoute::where('driver_user_id', '!=', $passenger_user->id) // exclude driver routes from current logged in user
                    ->where('route_status_id', '1')
                    ->where($query_array)
                    ->orderBy('ride_date', $validated['sort'])
                    ->get();

                if ($driver_routes) {
                    foreach ($driver_routes as $key => $route) {
                        $route_status = RouteStatus::find($route->route_status_id);
                        $driver_routes[$key]['route_status'] = $route_status->route_status;

                        $seat_approved = PassengerBooking::where('driver_route_id', $route->id)
                            ->where('booking_status_id', '2')
                            ->count();

                        $driver_routes[$key]['seat_approved'] = $seat_approved;

                        $date = Carbon::createFromFormat('Y-m-d', $route->ride_date);
                        $time = Carbon::createFromFormat('G:i:s', $route->ride_time);

                        $driver_routes[$key]['ride_date_formatted'] = $date->format('M d, Y');
                        $driver_routes[$key]['ride_time_formatted'] = $time->format('g:i A');

                        $location_points = LocationPoint::where('driver_route_id', $route->id)
                            ->get();
                        // Log::info($location_points);
                        $driver_routes[$key]['location_points'] = $location_points;
                    }
                }

                Log::info($driver_routes);
            }
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Driver Route retrieve error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response()->json([
            'driver_routes' => $driver_routes,
            'response_text' => $response_text,
            'response_code' => $response_code
        ]);
    }

    /**
     * Get location points of a driver route.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function driver_location_points($id)
    {
        // GET /driver-routes/id/location-points

        // Authenticate - Check logged in User ID - Admin or User only

        // Set default response
        $response_text = "Driver Route - Location Points retrieved successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            // Validate - If Driver Route ID exists
            $driver_route = DriverRoute::find($id);

            if ($driver_route) {
                // Set Driver User data
                $driver_user = User::find($driver_route->driver_user_id);

                $driver_name = $driver_user->first_name . " " . $driver_user->last_name;
                $driver_photo = asset($driver_user->photo);
                $driver_jobtitle = JobTitle::find($driver_user->job_title_id)->job_title;
                $driver_department = Department::find($driver_user->department_id)->department_name;
                $driver_vaccinated = $driver_user->vaccinated;

                $driver_route['driver_name'] = $driver_name;
                $driver_route['driver_photo'] = $driver_photo;
                $driver_route['driver_job_title'] = $driver_jobtitle;
                $driver_route['driver_department'] = $driver_department;
                $driver_route['driver_vaccinated'] = $driver_vaccinated;

                $route_status = RouteStatus::find($driver_route->route_status_id);
                $driver_route['route_status_name'] = $route_status->route_status;

                // Set Formatted Date and Time
                $date = Carbon::createFromFormat('Y-m-d', $driver_route->ride_date);
                $time = Carbon::createFromFormat('G:i:s', $driver_route->ride_time);

                $driver_route['ride_date_formatted'] = $date->format('M d, Y');
                $driver_route['ride_time_formatted'] = $time->format('g:i A');

                // Set Approved Seats
                $seat_approved = PassengerBooking::where('driver_route_id', $driver_route->id)
                    ->where('booking_status_id', '2')
                    ->count();

                $driver_route['seat_approved'] = $seat_approved;

                // Set Location Points
                $location_points = LocationPoint::where('driver_route_id', $driver_route->id)
                    ->get();
                $driver_route['location_points'] = $location_points;

                Log::debug($driver_route);
            }
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Driver Route - Location Points retrieve error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response()->json([
            'driver_route' => $driver_route,
            'response_text' => $response_text,
            'response_code' => $response_code
        ]);
    }
}
