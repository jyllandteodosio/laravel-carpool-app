<?php

namespace App\Http\Controllers;

use App\Models\Department;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class DepartmentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // GET /departments

        // Return all records
        $departments = [];

        // Set default response
        $response_text = "Departments retrieved successfully.";
        $response_code = Response::HTTP_OK;

        try {
            $departments = Department::all();
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Department retrieve error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response()->json([
            'departments' => $departments,
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
        // POST /departments

        // Validate the request
        $validated = $request->validate([
            'department_name' => 'required|string',
        ]);

        // Set default response
        $response_text = "New Department stored successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            Department::create([
                'department_name' => $validated['department_name'],
            ]);
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "New Department store error.";
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
        // GET /departments/id

        // Return single record
        return Department::find($id);
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
        // PUT/PATCH /departments/id

        // Validate the request
        $validated = $request->validate([
            'department_name' => 'required|string',
        ]);

        // Set default response
        $response_text = "Department updated successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            $department = Department::find($id);
            $department->department_name = $validated['department_name'];
            $department->save();
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Department update error.";
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
        // DELETE /departments/id

        // Set default response
        $response_text = "Department deleted successfully.";
        $response_code = Response::HTTP_OK;

        // Delete record from database
        try {
            $department = Department::find($id);
            $department->delete();
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "Department delete error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response($response_text, $response_code)
            ->header('Content-Type', 'text/plain');
    }
}
