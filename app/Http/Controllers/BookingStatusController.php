<?php

namespace App\Http\Controllers;

use App\Models\BookingStatus;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class BookingStatusController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // GET /booking-statuses

        // Return all records
        return BookingStatus::all();
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        // POST /booking-statuses

        // Validate the request
        $validated = $request->validate([
            'booking_status' => 'required|string',
        ]);

        // Set default response
        $response_text = "New Booking Status stored successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            BookingStatus::create([
                'booking_status' => $validated['booking_status'],
            ]);

        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "New Booking Status store error.";
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
        // GET /booking-statuses/id

        // Return single record
        return BookingStatus::find($id);
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
        // PUT/PATCH /booking-statuses/id

        // Validate the request
        $validated = $request->validate([
            'booking_status' => 'required|string',
        ]);

        // Set default response
        $response_text = "Booking Status updated successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            $booking = BookingStatus::find($id);
            $booking->booking_status = $validated['booking_status'];
            $booking->save();

        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Booking Status update error.";
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
        // DELETE /booking-statuses/id

        // Set default response
        $response_text = "Booking Status deleted successfully.";
        $response_code = Response::HTTP_OK;

        // Delete record from database
        try {
            $booking = BookingStatus::find($id);
            $booking->delete();

        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Booking Status delete error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response($response_text, $response_code)
        ->header('Content-Type', 'text/plain');
    }
}
