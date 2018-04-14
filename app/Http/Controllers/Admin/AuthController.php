<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\RoleUserModel;

use Crypt;
use Validator;
use Session;

class AuthController extends Controller
{
    public function __construct(
                                    UserModel $user_model,
                                    RoleModel $role_model,
                                    RoleUserModel $role_user_model
                                )
    {
    	$this->UserModel = $user_model;
    	$this->RoleModel = $role_model;
    	$this->RoleUserModel = $role_user_model;
    }

    public function process_login(Request $request)
    {
    	$arr_rules['email']         = "required|email";
        $arr_rules['password']      = "required";
        
        $validator = Validator::make($request->all(),$arr_rules);

        if($validator->fails())
        {
            Session::flash('error','Please enter all the mandatory fields.');
            return redirect()->back()->withErrors($validator)->withInput($request->all());
        }

        $password     = $request->input('password');
        $enc_password = Crypt::encrypt($password);

	    $obj_user = $this->UserModel
				    			->where('email',$request->input('email'))
				    			//->where('password',$enc_password)
				    			->whereHas('role_user_details.role_details', function () {})
				    			->with('role_user_details.role_details')
				    			->first();
		if($obj_user)
		{
			$arr_user = $obj_user->toArray();

			// Password is compared seperately because every time encryped value is different.

			if(isset($arr_user['password']) && $arr_user['password'] != "")
			{
				$user_password = Crypt::decrypt($arr_user['password']);

				if($user_password == $password)
				{		
					if(isset($arr_user['role_user_details']['role_details']['role_slug']) && isset($arr_user['email'])  )
					{
						$arr_auth_user = [];
						$arr_auth_user['email'] 	= $arr_user['email'];
						$arr_auth_user['role_slug'] = $arr_user['role_user_details']['role_details']['role_slug'];
						$arr_auth_user['role_name'] = $arr_user['role_user_details']['role_details']['role_name'];

						Session::put('arr_auth_user', $arr_auth_user);
						Session::flash('success','Login successful.');
					
						return redirect('/dashboard');
					}
				}
				else
				{	
					Session::flash('error','Please enter correct password.');		
				}
			}
			else
			{
				Session::flash('error','Please enter correct password.');
			}
		}
		else
		{
			Session::flash('error','Please enter valid email and password to login.');
		}
		
        return redirect()->back()->withInput($request->all());
    }

    public function logout()
    {
    	Session::flush();
    	return redirect('/login');
    }
}
