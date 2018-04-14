<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\CategoryModel;
use App\Models\VideoModel;
use App\Models\AppkeyModel;


class ApiController extends Controller
{
     
    public function __construct(
                                    CategoryModel $category_model,
                                    VideoModel $videomodel,
                                    AppkeyModel $appkeymodel
                                )
    {
    	$this->CategoryModel = $category_model;
    	$this->VideoModel    = $videomodel;
        $this->AppkeyModel   = $appkeymodel;

        $this->category_base_img_path   = public_path().'/uploads/category_images/';
        $this->category_public_img_path = url('/').'/uploads/category_images/';

    	$this->video_base_path   = public_path().'/uploads/';
        $this->video_public_path = url('/').'/uploads/';
    }

    public function get_categories(Request $request)
    {
        $app_key = $request->input('general_segment');

        if($this->check_secret_key($app_key)  == false)
        {
            return response()->json(['status' => 'error' , 'msg' => 'Improper response from the server.']);
        }

    	$arr_category = array();

        $obj_category = $this->CategoryModel->orderBy('id','desc')->get(['id','name','category_image']);

        if(count($obj_category) > 0)
        {
            $arr_category = $obj_category->toArray();
            foreach ($arr_category as $key => $value) 
            {
                if(isset($value['category_image']) && file_exists( $this->category_base_img_path.$value['category_image']))
                {
                    $image_name = $this->category_public_img_path.$value['category_image'];
                }
                else
                {
                    $image_name = $this->category_public_img_path.'default.png';
                }

                $arr_category[$key]['category_image'] = $image_name; 
            }

        	return response()->json(['status' => 'success', 'arr_categories' => $arr_category ,'msg' => 'Categories found.']);
        }

        return response()->json(['status' => 'error', 'arr_categories' => [] ,'msg' => 'Categories not found.' ]);
    }

    public function get_videos(Request $request)
    {
        $app_key = $request->input('general_segment');

        if($this->check_secret_key($app_key)  == false)
        {
            return response()->json(['status' => 'error' , 'msg' => 'Improper response from the server.']);
        }

    	$category_id = $request->input('category_id');

    	if(!empty($category_id))
    	{
    		$arr_videos = $this->VideoModel
                                        ->with('category_details')
                                        ->where('category_id', $category_id)
                                        ->orderBy('id','desc')
                                        ->paginate(10)
                                        ->toArray();

    		if(isset($arr_videos['data']) && count($arr_videos['data']) > 0)
    		{	
    			foreach ($arr_videos['data'] as $key => $value) 
    			{
	    			$arr_tmp = [];
	    			$arr_tmp['category_id'] = isset($value['category_id']) ?  $value['category_id'] : 0;
	    			$arr_tmp['video_id']    = isset($value['id']) ?  $value['id'] : 0;
	    			$arr_tmp['video_name']  = isset($value['original_name']) ?  $value['original_name'] : '';
	    			$arr_tmp['video_url']   = isset($value['enc_name']) ?  $this->video_public_path.$value['enc_name'] : '';
    				
    				$arr_videos['data'][$key] = $arr_tmp; 
    			}
    		}
    		
        	return response()->json(['status' => 'success', 'arr_videos' => $arr_videos,'msg' => 'Videos found.' ]);
    	}

        return response()->json(['status' => 'error', 'arr_videos' => [] ,'msg' => 'Videos not found.' ]);
    }

    public function get_random_videos(Request $request)
    {
        $app_key = $request->input('general_segment');

        if($this->check_secret_key($app_key)  == false)
        {
            return response()->json(['status' => 'error' , 'msg' => 'Improper response from the server.']);
        }

        $arr_videos = $this->VideoModel->with('category_details')->orderByRaw("RAND()")->take(30)->get();

        if(isset($arr_videos) && count($arr_videos) > 0)
        {   
            foreach ($arr_videos as $key => $value) 
            {
                $arr_tmp = [];
                $arr_tmp['category_id'] = isset($value['category_id']) ?  $value['category_id'] : 0;
                $arr_tmp['video_id'] = isset($value['id']) ?  $value['id'] : 0;
                $arr_tmp['video_name'] = isset($value['original_name']) ?  $value['original_name'] : '';
                $arr_tmp['video_url'] = isset($value['enc_name']) ?  $this->video_public_path.$value['enc_name'] : '';
                
                $arr_videos[$key] = $arr_tmp; 
            }
            return response()->json(['status' => 'success', 'arr_videos' => $arr_videos,'msg' => 'Videos found.' ]);
        }
        return response()->json(['status' => 'error', 'arr_videos' => [] ,'msg' => 'Videos not found.' ]);
    }
    
    public function check_secret_key($key = false)
    {   
        if($key != false)
        {
            $obj_key = $this->AppkeyModel->first();
            if($obj_key)
            {
                if(isset($obj_key->app_key) && $obj_key->app_key == $key)
                {
                    return true;
                }
            }
        }
        return false;
    }
}