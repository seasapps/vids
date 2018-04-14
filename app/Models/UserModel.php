<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserModel extends Model
{
    protected $table = "user";

    protected $fillable = [
    						'firstname',
    						'lastname',
    						'email',
    						'password',
    						'about',
    						'skills',
    						'gender',
    						'profile_image'
    					];

    public function role_user_details()
    {
    	return $this->hasOne('App\Models\RoleUserModel','user_id','id');
    }
}
