<?php

namespace App\Http\Controllers;

use App\Events\NotificationEvent;
use App\Models\BookingStatus;
use App\Models\DriverRoute;
use App\Models\LocationPoint;
use App\Models\PassengerBooking;
use App\Models\RouteStatus;
use App\Models\User;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Mockery\Generator\StringManipulation\Pass\Pass;
use phpDocumentor\Reflection\Location;

class PassengerBookingController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // GET /passenger-bookings

        // Authenticate - Check logged in User ID - Admin only

        // Get all records
        $passenger_bookings = PassengerBooking::all();

        // Set default response
        $response_text = "Passenger Bookings retrieved successfully.";
        $response_code = Response::HTTP_OK;

        // Return response
        return response()->json([
            'passenger_bookings' => $passenger_bookings,
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
        // POST /passenger-bookings

        // Validate the request
        $validated = $request->validate([
            'driver_route_id' => 'required|string',
            'pick_up_id' => 'required|string',
            'drop_off_id' => 'required|string',
            'booking_status_id' => 'required|string',
        ]);

        // Set default response
        $passenger_booking = '';
        $response_text = "New Passenger Booking stored successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            // Authenticate - Check logged in User ID

            // Validate - If Passenger User ID exists
            $passenger_user = User::find(Auth::id());
            $validated['passenger_user_id'] = $passenger_user->id;

            // Validate - If Driver Route ID exists
            $driver_route = DriverRoute::find($validated['driver_route_id']);

            // Validate - If Location Point ID exists
            $pick_up = LocationPoint::find($validated['pick_up_id']);

            // Validate - If Location Point ID exists
            $drop_off = LocationPoint::find($validated['drop_off_id']);

            // Validate - If Booking Status ID exists
            $booking_status = BookingStatus::find($validated['booking_status_id']);

            // Validate - If Driver Route -> User ID exists
            $driver_user = User::find($driver_route->driver_user_id);

            if (
                $passenger_user &&
                $driver_route &&
                $pick_up &&
                $drop_off &&
                $booking_status
            ) {
                // Create new booking
                $passenger_booking = PassengerBooking::create($validated);

                // Prepare data for notifications
                $passenger_photo = $passenger_user->photo ? asset($passenger_user->photo) : '';
                $dropoff_point = LocationPoint::find($passenger_booking->drop_off_id);
                $passenger_booking['drop_off_point'] = $dropoff_point;

                $passenger_data = array(
                    'name' => $passenger_user->first_name . ' ' . $passenger_user->last_name,
                    'photo' => $passenger_photo,
                    'passenger_booking' => $passenger_booking
                );

                $driver_notification_data = array(
                    'type' => 'driver-request',
                    'driver_route' => $driver_route,
                    'passenger_data' => $passenger_data,
                );

                $passenger_notification_data = array(
                    'type' => 'passenger-request',
                    'passenger_booking' => $passenger_booking,
                );

                NotificationEvent::dispatch($driver_user, $driver_notification_data);
                NotificationEvent::dispatch($passenger_user, $passenger_notification_data);
            } else {
                $response_text = "New Passenger Booking store error.";
                $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
            }
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "New Passenger Booking store error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response()->json([
            'passenger_bookings' => $passenger_booking,
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
        // GET /passenger-bookings/id

        // Authenticate - Check logged in User ID - Admin or User only

        // Set default response
        $passenger_booking = "";
        $response_text = "Passenger Booking retrieved successfully.";
        $response_code = Response::HTTP_OK;

        try {
            // Get single record
            $passenger_user = User::find(Auth::id());

            if ($passenger_user) {
                // Find records
                $passenger_booking = PassengerBooking::find($id);
                $driver_route = DriverRoute::find($passenger_booking->driver_route_id);

                $booking_status = BookingStatus::find($passenger_booking->booking_status_id);
                $passenger_booking['booking_status'] = $booking_status->booking_status;

                $seat_approved = PassengerBooking::where('driver_route_id', $driver_route->id)
                    ->where('booking_status_id', '2')
                    ->count();

                $driver_route['seat_approved'] = $seat_approved;

                $date = Carbon::createFromFormat('Y-m-d', $driver_route->ride_date);
                $time = Carbon::createFromFormat('G:i:s', $driver_route->ride_time);

                $driver_route['ride_date_formatted'] = $date->format('M d, Y');
                $driver_route['ride_time_formatted'] = $time->format('g:i A');

                $passenger_booking['driver_route'] = $driver_route;

                $pickup = LocationPoint::find($passenger_booking->pick_up_id);
                $passenger_booking['pick_up_description'] = $pickup->description;

                $dropoff = LocationPoint::find($passenger_booking->drop_off_id);
                $passenger_booking['drop_off_description'] = $dropoff->description;
            }
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Passenger Booking retrieve error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response
        return response()->json([
            'passenger_booking' => $passenger_booking,
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
        // PUT/PATCH /passenger-bookings/id

        // Authenticate - Check logged in User ID - Admin or User only

        // Validate the request
        $validated = $request->validate([
            'booking_status_id' => 'required|string',
        ]);

        // Set default response
        $passenger_booking = '';
        $response_text = "Passenger Booking updated successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            // Validate - If RouteStatus ID exists
            $booking_status = BookingStatus::find($validated['booking_status_id']);

            if ($booking_status) {
                // Update record
                $passenger_booking = PassengerBooking::find($id);
                $passenger_booking->booking_status_id = $booking_status->id;
                $passenger_booking->save();
            } else {
                $response_text = "New Passenger Booking store error.";
                $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
            }
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Passenger Booking update error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response()->json([
            'passenger_bookings' => $passenger_booking,
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
        // DELETE /passenger-bookings/id

        // Authenticate - Check logged in User ID - Admin only

        // Set default response
        $response_text = "Passenger Booking deleted successfully.";
        $response_code = Response::HTTP_OK;

        // Delete record from database
        try {
            $passenger_booking = PassengerBooking::find($id);
            $passenger_booking->delete();
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Passenger Booking delete error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response($response_text, $response_code)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Get passenger bookings by user.
     *
     * @param  int  $id (User ID)
     * @return \Illuminate\Http\Response
     */
    public function user_bookings(Request $request)
    {
        // GET /passenger-bookings/user/

        // Validate the request
        $validated = $request->validate([
            'ride_date' => 'required|string',
            'ride_time' => 'required|string',
            'sort' => 'required|string',
        ]);

        // Set default response
        $passenger_bookings = [];
        $response_text = "Passenger Bookings retrieved successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            // Validate - If Passenger User ID exists
            $passenger_user = User::find(Auth::id());

            if ($passenger_user) {
                // Find records
                $query_array = [];

                if ($validated['ride_time'] != "0") {
                    array_push($query_array, ['driver_routes.ride_time', '=', $validated['ride_time']]);
                } else {
                    array_push($query_array, ['driver_routes.ride_time', '>=', '00:00']);
                }

                if ($validated['ride_date'] != "0") {
                    array_push($query_array, ['driver_routes.ride_date', '>=', $validated['ride_date']]);
                }

                Log::info($query_array);

                $passenger_bookings = PassengerBooking::where('passenger_user_id', $passenger_user->id)
                    ->join('driver_routes', 'passenger_bookings.driver_route_id', '=', 'driver_routes.id')
                    ->where($query_array)
                    ->where('driver_routes.route_status_id', '!=', '2')
                    ->where('driver_routes.route_status_id', '!=', '3')
                    ->where('driver_routes.route_status_id', '!=', '5')
                    ->select('passenger_bookings.id AS booking_id', 'passenger_bookings.*', 'driver_routes.*')
                    ->orderBy('driver_routes.ride_date', $validated['sort'])
                    ->get();

                Log::info($passenger_bookings);
            }

            if ($passenger_bookings) {
                foreach ($passenger_bookings as $key => $booking) {

                    $booking_status = BookingStatus::find($booking->booking_status_id);
                    $passenger_bookings[$key]['booking_status'] = $booking_status->booking_status;

                    $seat_approved = PassengerBooking::where('driver_route_id', $booking->driver_route_id)
                        ->where('booking_status_id', '2')
                        ->count();

                    $passenger_bookings[$key]['seat_approved'] = $seat_approved;

                    $date = Carbon::createFromFormat('Y-m-d', $booking->ride_date);
                    $time = Carbon::createFromFormat('G:i:s', $booking->ride_time);

                    $passenger_bookings[$key]['ride_date_formatted'] = $date->format('M d, Y');
                    $passenger_bookings[$key]['ride_time_formatted'] = $time->format('g:i A');

                    $pickup = LocationPoint::find($booking->pick_up_id);
                    $passenger_bookings[$key]['pick_up_description'] = $pickup->description;

                    $dropoff = LocationPoint::find($booking->drop_off_id);
                    $passenger_bookings[$key]['drop_off_description'] = $dropoff->description;
                }
            }
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Passenger Bookings retrieve error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response()->json([
            'passenger_user' => $passenger_user,
            'passenger_bookings' => $passenger_bookings,
            'response_text' => $response_text,
            'response_code' => $response_code
        ]);
    }

    /**
     * Get passenger bookings by user.
     *
     * @param  int  $id (User ID)
     * @return \Illuminate\Http\Response
     */
    public function search_bookings(Request $request)
    {
        // GET /passenger-bookings/user/

        // Validate the request
        $validated = $request->validate([
            'driver_route_id' => 'nullable|string',
        ]);

        Log::info($request);

        // Set default response
        $passenger_bookings = [];
        $response_text = "Passenger Bookings retrieved successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            // Validate - If Passenger User ID exists
            $passenger_user = User::find(Auth::id());

            if ($passenger_user) {
                // Find records
                $query_array = [];

                if ($request->has('driver_route_id')) {
                    array_push($query_array, ['driver_route_id', '=', $validated['driver_route_id']]);
                }

                Log::info($query_array);

                $passenger_bookings = PassengerBooking::where('passenger_user_id', $passenger_user->id)
                    ->join('driver_routes', 'passenger_bookings.driver_route_id', '=', 'driver_routes.id')
                    ->where($query_array)
                    ->where('driver_routes.route_status_id', '!=', '3')
                    ->select('passenger_bookings.id AS booking_id', 'passenger_bookings.*', 'driver_routes.*')
                    ->orderBy('driver_routes.ride_date', 'desc')
                    ->get();

                Log::info($passenger_bookings);
            }

            if ($passenger_bookings) {
                foreach ($passenger_bookings as $key => $booking) {

                    $booking_status = BookingStatus::find($booking->booking_status_id);
                    $passenger_bookings[$key]['booking_status'] = $booking_status->booking_status;

                    $seat_approved = PassengerBooking::where('driver_route_id', $booking->driver_route_id)
                        ->where('booking_status_id', '2')
                        ->count();

                    $passenger_bookings[$key]['seat_approved'] = $seat_approved;

                    $date = Carbon::createFromFormat('Y-m-d', $booking->ride_date);
                    $time = Carbon::createFromFormat('G:i:s', $booking->ride_time);

                    $passenger_bookings[$key]['ride_date_formatted'] = $date->format('M d, Y');
                    $passenger_bookings[$key]['ride_time_formatted'] = $time->format('g:i A');

                    $pickup = LocationPoint::find($booking->pick_up_id);
                    $passenger_bookings[$key]['pick_up_description'] = $pickup->description;

                    $dropoff = LocationPoint::find($booking->drop_off_id);
                    $passenger_bookings[$key]['drop_off_description'] = $dropoff->description;
                }
            }
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Passenger Bookings retrieve error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response()->json([
            'passenger_user' => $passenger_user,
            'passenger_bookings' => $passenger_bookings,
            'response_text' => $response_text,
            'response_code' => $response_code
        ]);
    }

    /**
     * Get passenger bookings by driver route.
     *
     * @param  Request  $request
     * @param  int  $id (DriverRoute ID)
     * @return \Illuminate\Http\Response
     */
    public function driver_bookings(Request $request, $id)
    {
        // POST /passenger-bookings/driver/id

        // Authenticate - Check logged in User ID - Admin or User only

        // Validate the request
        $validated = $request->validate([
            'booking_status_id' => 'required|string',
        ]);

        // Set default response
        $driver_bookings = [];
        $response_text = "Passenger Bookings retrieved successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            // Validate - If Driver Route ID exists
            $driver_route = DriverRoute::find($id);

            // Validate - If Booking Status ID exists
            $booking_status = RouteStatus::find($validated['booking_status_id']);

            if ($driver_route) {
                if ($validated['booking_status_id'] == "0") {
                    // Find records
                    $driver_bookings = PassengerBooking::where('driver_route_id', $driver_route->id)
                        ->get();
                    Log::info($driver_bookings);
                } else {
                    // Find records
                    $driver_bookings = PassengerBooking::where('driver_route_id', $driver_route->id)
                        ->where('booking_status_id', $booking_status->id)
                        ->get();
                    Log::info($driver_bookings);
                }

                if ($driver_bookings) {
                    foreach ($driver_bookings as $key => $booking) {

                        $driver_bookings[$key]['booking_status_name'] = $booking->booking_status;

                        $user = User::find($booking->passenger_user_id);
                        $driver_bookings[$key]['passenger_user_name'] = $user->first_name . ' ' . $user->last_name;
                        $driver_bookings[$key]['passenger_user_photo'] = asset($user->photo);
                        $driver_bookings[$key]['vaccinated'] = $user->vaccinated;

                        $pickup = LocationPoint::find($booking->pick_up_id);
                        $driver_bookings[$key]['pick_up_description'] = $pickup->description;

                        $dropoff = LocationPoint::find($booking->drop_off_id);
                        $driver_bookings[$key]['drop_off_description'] = $dropoff->description;
                    }
                }
            } else {
                $response_text = "Passenger Bookings retrieve error.";
                $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
            }
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Passenger Bookings retrieve error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response()->json([
            'passenger_bookings' => $driver_bookings,
            'response_text' => $response_text,
            'response_code' => $response_code
        ]);
    }
}
