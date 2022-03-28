<?php

namespace App\Http\Controllers;

use App\Models\Department;
use App\Models\JobTitle;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        // GET /users

        // Authenticate - Check logged in User ID - Admin only

        // Get all records
        $users = User::all();

        // Set default response
        $response_text = "Users retrieved successfully.";
        $response_code = Response::HTTP_OK;

        // Return response
        return response()->json([
            'users' => $users,
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
        // POST /users

        // Validate the request
        $validated = $request->validate([
            'username' => 'required|string',
            'email' => 'required|email:rfc',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'mobile_number' => 'required|string',
            'about' => 'nullable|string|max:255',
            'photo' => 'nullable|string',
            'license_number' => 'nullable|string',
            'employee_id' => 'required|string',
            'password' => 'required|string',
            'vaccinated' => 'required|boolean',
            'vaccination_details' => 'nullable|string',
        ]);

        // Set default response
        $user = '';
        $response_text = "New User stored successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            // // Validate - If Department ID exists
            // $department_id = Department::find($validated['department_id']);

            // // Validate - If Job Title ID exists
            // $job_title_id = JobTitle::find($validated['job_title_id']);

            // Validate - If User ID exists
            $existing_user = User::find($validated['email']);

            $validated['password'] = Hash::make($validated['password']);

            if (!$existing_user) {
                $user = User::create($validated);
            } else {
                $response_text = "New User store error.";
                $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
            }
        } catch (Exception $error) {
            report($error);
            Log::debug($error);
            Log::debug($validated);

            $response_text = "New User store error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response()->json([
            'users' => $user,
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
        // GET /users/id

        // Authenticate - Check logged in User ID - Admin or User only

        // Set default response
        $response_text = "User retrieved successfully.";
        $response_code = Response::HTTP_OK;

        try {
            // Get single record
            $user = User::find($id);
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "User retrieve error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response
        return response()->json([
            'users' => $user,
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
        // PUT/PATCH /users/id

        // Authenticate - Check logged in User ID - Admin or User only

        // Validate the request
        $validated = $request->validate([
            'about' => 'nullable|string|max:255',
            'vaccinated' => 'required|boolean',
            'vaccination_details' => 'nullable|string',
        ]);

        // Set default response
        $user = '';
        $response_text = "User updated successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            $existing_user = User::find($id);
            if ($existing_user) {
                // Update record
                $user = User::find($id);
                $user->about = $validated['about'];
                $user->vaccinated = $validated['vaccinated'];
                $user->vaccination_details = $validated['vaccination_details'];
                $user->save();
            } else {
                $response_text = "New User store error.";
                $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
            }
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "User update error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response
        return response()->json([
            'users' => $user,
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
        // DELETE /users/id

        // Authenticate - Check logged in User ID - Admin only

        // Set default response
        $response_text = "User deleted successfully.";
        $response_code = Response::HTTP_OK;

        // Delete record from database
        try {
            $user = User::find($id);
            $user->delete();
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "User delete error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response($response_text, $response_code)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function register(Request $request)
    {
        // POST /register

        // Validate the request
        $validated = $request->validate([
            'username' => 'required|string',
            'email' => 'required|email:rfc',
            'first_name' => 'required|string',
            'last_name' => 'required|string',
            'mobile_number' => 'required|string',
            'about' => 'nullable|string|max:255',
            'photo' => 'nullable|string',
            'employee_id' => 'required|string',        
            'department_id' => 'nullable|string',
            'job_title_id' => 'nullable|string',
            'password' => 'required|string',
            'vaccinated' => 'required|boolean',
            'vaccination_details' => 'nullable|string',
        ]);

        // Set default response
        $user = '';
        $response_text = "New User registered successfully.";
        $response_code = Response::HTTP_OK;

        // Store input to database
        try {
            // // Validate - If Department ID exists
            // $department_id = Department::find($validated['department_id']);


            // // Validate - If Job Title ID exists
            // $job_title_id = JobTitle::find($validated[4]);

            // Validate - If User ID exists
            $existing_user = User::find($validated['email']);

            $validated['password'] = Hash::make($validated['password']);
            $validated['department_id'] = 2;
            $validated['job_title_id'] = 4;
            $validated['photo'] = "/storage/documents/default.jpg";


            if (!$existing_user) {
                $user = User::create($validated);
            } else {
                $response_text = "New User store error.";
                $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
            }
        } catch (Exception $error) {
            report($error);
            Log::debug($error);
            Log::debug($validated);

            $response_text = "New User store error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response 
        return response()->json([
            'users' => $user,
            'response_text' => $response_text,
            'response_code' => $response_code
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function user_profile()
    {
        // GET /users/id

        // Authenticate - Check logged in User ID - Admin or User only

        // Set default response
        $response_text = "User retrieved successfully.";
        $response_code = Response::HTTP_OK;

        try {
            // Get single record
            $user = User::find(Auth::id());

            $user['full_name'] = $user->first_name . ' ' . $user->last_name;

            $department = Department::find($user->department_id);
            if ($department) {
                $user['department_name'] = $department->department_name;
            }

            $job_title = JobTitle::find($user->job_title_id);
            if ($job_title) {
                $user['job_title_name'] = $job_title->job_title;
            }

            $user['user_photo'] = asset($user->photo);
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "User retrieve error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response
        return response()->json([
            'users' => $user,
            'response_text' => $response_text,
            'response_code' => $response_code
        ]);
    }

    /**
     * Logout the specified resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function logout(Request $request)
    {
        // GET /logout

        // Set default response
        $response_text = "User logged out successfully.";
        $response_code = Response::HTTP_OK;

        try {
            $request->user()->token()->revoke();
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "User log out error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response
        return response()->json([
            'response_text' => $response_text,
            'response_code' => $response_code
        ]);
    }

    /**
     * Update photo of specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function user_photo(Request $request)
    {
        // POST /user/photo

        // Authenticate - Check logged in User ID - Admin or User only

        // Validate the request
        $validated = $request->validate([
            'photo' => 'required|mimes:jpg,png|max:2048',
        ]);

        // Set default response
        $user = '';
        $response_text = "User updated successfully.";
        $response_code = Response::HTTP_OK;

        Log::debug($request->file('photo'));
        Log::debug($request);

        // Store input to database
        try {
            $existing_user = User::find(Auth::id());
            if ($existing_user) {
                // Upload photo on server
                if ($request->file('photo')) {
                    $file = $request->file('photo')->store('public/documents');
                }

                // Update record
                $user = $existing_user;
                $user->photo = Storage::url($file);
                $user->save();

                $user['user_photo'] = asset($user->photo);
            } else {
                $response_text = "New User store error.";
                $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
            }
        } catch (Exception $error) {
            report($error);
            Log::debug($error);

            $response_text = "User update error.";
            $response_code = Response::HTTP_UNPROCESSABLE_ENTITY;
        }

        // Return response
        return response()->json([
            'user' => $user,
            'response_text' => $response_text,
            'response_code' => $response_code
        ]);
    }
}
