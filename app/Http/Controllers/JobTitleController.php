<?php

namespace App\Http\Controllers;

use App\Models\JobTitle;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class JobTitleController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // GET /job-titles

        // Return all records

        // Return all records
        $job_titles = [];

        // Set default response
        $response_text = "Job Titles retrieved successfully.";
        $response_code = Response::HTTP_OK;

        try {
            $job_titles = JobTitle::all();
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Job Title retrieve error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response()->json([
            'job_titles' => $job_titles,
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
        // POST /job-titles

        // Validate the request
        $validated = $request->validate([
            'job_title' => 'required|string',
        ]);

        // Set default response
        $response_text = "New Job Title stored successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            JobTitle::create([
                'job_title' => $validated['job_title'],
            ]);
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "New Job Title store error.";
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
        // GET /job-titles/id

        // Return single record
        return JobTitle::find($id);
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
        // PUT/PATCH /job-titles/id

        // Validate the request
        $validated = $request->validate([
            'job_title' => 'required|string',
        ]);

        // Set default response
        $response_text = "Job Title updated successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            $job = JobTitle::find($id);
            $job->job_title = $validated['job_title'];
            $job->save();
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Job Title update error.";
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
        // DELETE /job-titles/id

        // Set default response
        $response_text = "Job Title deleted successfully.";
        $response_code = Response::HTTP_OK;

        // Delete record from database
        try {
            $job = JobTitle::find($id);
            $job->delete();
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Job Title delete error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response($response_text, $response_code)
            ->header('Content-Type', 'text/plain');
    }
}
