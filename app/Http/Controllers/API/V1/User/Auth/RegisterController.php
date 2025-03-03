<?php

namespace App\Http\Controllers\API\V1\User\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\API\V1\User\Auth\RegisterRequest;
use App\Mail\UserMail;
use App\Models\Admin;
use App\Models\SubAccount;
use App\Models\User;
use App\Models\UserType;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Hash;
use MarcinOrlowski\ResponseBuilder\ResponseBuilder;
use Spatie\Permission\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class RegisterController extends Controller
{
    /**
     * Register a new user to the application.
     * @param RegisterRequest $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function register(RegisterRequest $request)
    {
        $user = new User();
        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->email = $request->email;
        $user->phone = $request->phone;
        $user->password = Hash::make($request->password);
        $user->user_type_id = $request->user_type_id;
        $user->save();

        // Find the role by ID or name from the request
        $role = Role::where('id', $request->role)->where('guard_name', 'web')->first();
        if ($role) {
            // Assign the role to the user
            $user->assignRole($role);
        } else {
            return 'Role not found or guard does not match';
        }
        $user->password = $request->password;

        $imagePath = public_path('images/DW-Logo.png');
        dd($imagePath);
        $imageData = base64_encode(file_get_contents($imagePath));
        $mimeType = mime_content_type($imagePath);

        $base64Image = "data:$mimeType;base64,$imageData";

        $mail_data = [
            'user' => $user,
            'role' => $role,
            'password' => $request->password,
            'base64Image' => $base64Image,
        ];

        Mail::to($user->email)->send(new UserMail($mail_data));
        // event(new Registered($user));

        $token = $user->createToken($request->ip())->plainTextToken;

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_CREATED)
            ->withMessage('User registration was successful!!!')
            ->withData([
                'user' => $user,
                'token' => $token
            ])
            ->build();
    }
    public function getUser(Request $request)
    {
        $perPage = $request->per_page ?? 20;
        $search = $request->search;

        $users = User::orderBy('first_name')
            ->with(['roles', 'subAccounts'])
            // ->with(['roles.permissions']) 
            ->when($search, function ($query) use ($search) {
                $query->where(function ($query) use ($search) {
                    $query->where('first_name', 'like', '%' . $search . '%')
                        ->orWhere('first_name', 'like', '%' . $search . '%');
                });
            });

        if ($request->list) {
            $users = $users->get();
        } else {
            $users = $users->paginate($perPage);
        }

        return ResponseBuilder::asSuccess()
            ->withMessage('Users fetched successfully')
            ->withData(['users' => $users])
            ->build();
    }

    public function getAnalytic(Request $request)
    {

        $data = [

            'user_count' => User::count(),
            'role' => Role::count()
        ];
        return ResponseBuilder::asSuccess()
            ->withMessage('fetch analytic')
            ->withData(['data' => $data])
            ->build();
    }

    // Edit User Function
    public function editUser($id, Request $request)
    {
        $user = User::find($id);

        if (!$user) {
            return ResponseBuilder::asError(400)
                ->withHttpCode(Response::HTTP_NOT_FOUND)
                ->withMessage('User not found')
                ->build();
        }

        $rules = [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:255',
            'role' => 'required|exists:roles,id',
            'user_type_id' => 'required|exists:user_types,id',
        ];

        // Do not validate email on update
        if ($request->isMethod('post')) {
            $rules['email'] = 'required|email|unique:admins,email,' . $user->id;
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return ResponseBuilder::asError(400)
                ->withHttpCode(Response::HTTP_BAD_REQUEST)
                ->withMessage('Validation failed')
                ->withData(['errors' => $validator->errors()])
                ->build();
        }

        $user->first_name = $request->first_name;
        $user->last_name = $request->last_name;
        $user->phone = $request->phone;
        $user->user_type_id = $request->user_type_id;

        // Don't update the email field
        // $user->email = $request->email; // Comment this line out

        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        $role = Role::where('id', $request->role)->where('guard_name', 'web')->first();
        if ($role) {
            $user->syncRoles([$role]);
        } else {
            return ResponseBuilder::asError(400)
                ->withHttpCode(Response::HTTP_BAD_REQUEST)
                ->withMessage('Role not found or guard does not match')
                ->build();
        }

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_OK)
            ->withMessage('User update was successful!!!')
            ->withData(['user' => $user])
            ->build();
    }


    public function editUserPassword($id, Request $request)
    {

        $user = User::find($id);

        if (!$user) {
            return ResponseBuilder::asError(400)
                ->withHttpCode(Response::HTTP_NOT_FOUND)
                ->withMessage('User not found')
                ->build();
        }

        // Do not validate email on update
        if ($request->isMethod('post')) {
            $rules['email'] = 'required|email|unique:admins,email,' . $user->id;
        }

        if ($request->has('password')) {
            $user->password = Hash::make($request->password);
        }

        $user->save();

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_OK)
            ->withMessage('User update was successful!!!')
            ->withData(['user' => $user])
            ->build();
    }

    public function deleteUser($id, $sub_account_email)
    {

        $user = User::find($id);
        $sub_account = User::where('users.email', $sub_account_email);

        if (!$user) {
            return ResponseBuilder::asError(400)
                ->withHttpCode(Response::HTTP_NOT_FOUND)
                ->withMessage('User not found')
                ->build();
        }

        // Detach all roles from the user
        $user->syncRoles([]);

        // Delete the user
        $user->forceDelete();
        $sub_account->forceDelete();


        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_OK)
            ->withMessage('User deleted successfully')
            ->build();
    }

    public function getUserType()
    {

        $data = UserType::where('user_type_name', '!=', 'Base User')
            ->where('user_type_name', '!=', 'Sub Account')
            ->orderBy('user_type_name')
            ->get();

        return ResponseBuilder::asSuccess()
            ->withHttpCode(Response::HTTP_OK)->withData(['user_type' => $data])
            ->withMessage('All System User Type')
            ->build();
    }

}
