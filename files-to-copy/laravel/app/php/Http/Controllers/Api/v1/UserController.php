<?php

namespace App\Http\Controllers\Api\v1;

use Illuminate\Http\Request;
use App\Models\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserResourceCollection;
use Illuminate\Support\Facades\Auth;


/**
 * @group  User management
 *
 * APIs for managing users
 */
class UserController extends Controller
{

    /**
     * Lists all users
     * @authenticated
     * @return App\Http\Resources\UserResourceCollection
     */
    public function index() : UserResourceCollection
    {
        return new UserResourceCollection(User::paginate());
    }

    /**
     * Login of a user
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     * @group  Account management
     * @bodyParam email string required email
     * @bodyParam password string required password
     */
    public function login(Request $request)
    {
        
        $data = $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);
    
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => ['These credentials do not match our records.']
            ], 404);
        }
        $accessToken = $user->createToken('authToken')->accessToken;
        //Passage des permissions au token
        $permissions = (array) $user->getAllPermissions()->pluck('name');
        
        $response = [
            'user' => $user,
            'access_token' => $accessToken
        ];
        return response($response, 201);
    }

    /**
     * Logout user
     * @authenticated
     * @param \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response $response
     * @group  Account management
     */
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json('logout', 201);
    }

    /** 
     * Register user account
     * @param \Illuminate\Http\Request $request 
     * @return \Illuminate\Http\Response 
     * @group  Account management
     * @bodyParam email string required email
     * @bodyParam name string required email
     * @bodyParam password string required password
     */ 
    public function register(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|unique:users|email',
            'password' => 'min:8|required_with:password_confirmation|same:password_confirmation'
        ]);
        $user = User::create($validatedData);
        $permissions = (array) $user->getAllPermissions()->pluck('name');
        $accessToken = $user->createToken('authToken')->accessToken;
        return response(['user'=> $user,'access_token' => $accessToken], 201);
    }

     /**
     * Display the current user
     *
     * @param App\Models\User $user
     * @return \App\Http\Resources
     * @throws \Exception
     * @authenticated
     */
    public function show(User $user) : UserResource
    {
        return new UserResource($user);

    }

    /**
     * Display current user info
     * @authenticated
     */
    public function showCurrent(Request $request) : UserResource
    {
        return new UserResource(Auth::user());
    }

    /**
     * Update of user information
     *
     * @param App\Models\User $user
     * @param  \Illuminate\Http\Request $request
     * @return App\Http\Resources
     * @authenticated
     * @bodyParam email string required email
     * @bodyParam name string required email
     * @bodyParam password string required password
     */
    public function update(User $user, Request $request) : UserResource
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'email' => 'required|unique:users|email',
            'password' => 'min:8|required_with:password_confirmation|same:password_confirmation'
        ]);

        $user->update($request->all());
        return new UserResource($user);
    }

    /**
     * Changement d'un mot de passe utilisateur
	 * @group  Account management
     * @param  \Illuminate\Http\Request $request
     * @return App\Http\Resources
     * @authenticated
     * @bodyParam password string required current password
     * @bodyParam new_password string required new password
	 */
	 public function changePassword(Request $request)
	 {
        $validatedData = $request->validate([
            'password' => 'min:8|required',
            'new_password' => 'min:8|required'
        ]);

        $user = User::where('email', $request->user()->email )->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response([
                'message' => ['These credentials do not match our records.']
            ], 404);
        }

        $user->password = $request->new_password;
        $user->save();
        return new UserResource($user);
     }
     
    /**
     * Delete of a user
     *
     * @param App\Models\User $user
     * @return \Illuminate\Http\JsonResponse
     * @authenticated
     */
    public function destroy(User $user)
    {
        $user->delete();
        return response()->json();
    }
}
