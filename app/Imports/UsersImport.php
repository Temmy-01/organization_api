<?php

namespace App\Imports;

use App\Models\Account;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\ToModel;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserMail;

class UsersImport implements ToModel
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {
        DB::beginTransaction();

        try {
            
            $user = new User([
                'first_name' => $row[0],  // First Name
                'last_name'  => $row[1],  // Last Name
                'email'      => $row[2],  // Email
                'phone'      => $row[3],  // Phone
                'password'   => Hash::make($row[4]),  // Password
                'user_type_id' => 3,  // Default user_type_id
                'status'     => 'active',  // Default status
            ]);
            $user->save();
            // Create the Account model
            Account::create([
                'user_id'      => $user->id,  // User's ID from above
                'company_name' => $row[5],  // Company Name
                'domain_name'  => $row[6],  // Domain Name
                'email'        => $row[7],  // Account Email
                'phone'        => $row[8],  // Account Phone
                'status'       => 'active',  // Default status for the account
            ]);



            // Assign the "Base Users" role
            $role = Role::where('name', 'Base Users')->where('guard_name', 'web')->first();
            if ($role) {
                $exists = DB::table('model_has_roles')
                ->where('role_id', $role->id)
                    ->where('model_type', 'App\Models\User')
                    ->where('model_id', $user->id)
                    ->exists();

                if (!$exists) {
                    DB::table('model_has_roles')->insert([
                        'role_id' => $role->id,
                        'model_type' => 'App\Models\User',
                        'model_id' => $user->id
                    ]);
                }
            } else {
                throw new \Exception('Role not found');
            }

            // Send email
            $mail_data = [
                'user' => $user,
                'role' => $role,
                'password' => $row[4], // Or use any password provided in the row
            ];
            Mail::to($user->email)->send(new UserMail($mail_data));

            DB::commit();

            return $user;
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

}
