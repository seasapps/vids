<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class VideoModel extends Model
{
    protected $table = "videos";

    protected $fillable = [
    						'enc_name',
    						'original_name',
    						'category_id'
    					];

    public function category_details()
    {
    	return $this->hasOne('App\Models\CategoryModel','id','category_id');
    }
}
