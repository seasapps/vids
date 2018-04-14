<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoleUserModel extends Model
{
    protected $table = "role_users";

    protected $fillable = ['role_id','user_id'];

    public function role_details()
    {
    	return $this->belongsTo('App\Models\RoleModel','role_id','id');
    }
}