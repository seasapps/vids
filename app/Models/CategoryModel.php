<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CategoryModel extends Model
{
    protected $table = "categories";

    protected $fillable = [
    						'name',
    						'slug',
    						'category_image'
    					];
}
