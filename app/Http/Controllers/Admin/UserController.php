<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\UserModel;
use App\Models\RoleModel;
use App\Models\RoleUserModel;
use App\Models\CategoryModel;
use App\Models\VideoModel;

use Crypt;
use Validator;
use Session;
use DB;

class UserController extends Controller
{
    public function __construct(
                                    UserModel $user_model,
                                    RoleModel $role_model,
                                    RoleUserModel $role_user_model,
                                    CategoryModel $categorymodel,
                                    VideoModel $videomodel
                                )
    {
    	$this->UserModel = $user_model;
    	$this->RoleModel = $role_model;
    	$this->RoleUserModel = $role_user_model;
        $this->CategoryModel = $categorymodel;
        $this->VideoModel = $videomodel;

    	$this->video_base_path   = public_path().'/uploads/';
        $this->video_public_path = url('/').'/uploads/';

        $this->arr_view_data = [];
    }

    public function index(Request $request)
    {       
        $arr_category = array();

        $obj_category = $this->CategoryModel->orderBy('id','desc')->get();

        if($obj_category)
        {
            $arr_category = $obj_category->toArray();
        }

        $category_id = "";
        if($request->has('category'))
        {
            $category_id = base64_decode($request->input('category'));
        }

        $arr_videos = [];
        $obj_videos = $this->VideoModel
                                    ->with('category_details');
        if($category_id != "")
        {
            $obj_videos = $obj_videos->where('category_id', $category_id);
        }

        $obj_videos = $obj_videos->get();
        if($obj_videos)
        {
            $arr_videos = $obj_videos->toArray();
        }

    	$this->arr_view_data['arr_videos'] = $arr_videos; 
        $this->arr_view_data['arr_category'] = $arr_category; 

    	return view('admin.users.manage',$this->arr_view_data);
    }

	public function create(Request $request)
    {	

        $arr_category = array();

        $obj_category = $this->CategoryModel->orderBy('id','desc')->get();

        if($obj_category)
        {
            $arr_category = $obj_category->toArray();
        }

        $this->arr_view_data['arr_categories']             = $arr_category;
    	return view('admin.users.create',$this->arr_view_data);
    }

    public function store(Request $request)
    {    
        $arr_rules['category'] = 'required';
        $validator = Validator::make($request->all(),$arr_rules);

        if($validator->fails())
        {
            Session::flash('error','Please fill valid information.');
            return redirect()->back()->withErrors($validator)->withInput($request->all());
        }

        $file_name      = "default.mp4";

        /* Upload profile picture */
        if($request->hasFile('video'))
        {    
	        $file_name      = $request->input('video');

            // Validation for video file.
            $is_file_valid = $this->validate_video_file($request->file('video'));
            if($is_file_valid !== true)
            {
                Session::flash('error','Please fill valid information.');
                return redirect()->back()->withInput($request->all());
            }

	        $file_extension        = strtolower($request->file('video')->getClientOriginalExtension());
            $file_original_name    = $request->file('video')->getClientOriginalName(); 
	        $file_name             = sha1(uniqid().$file_name.uniqid()).'.'.$file_extension;
	        $request->file('video')->move($this->video_base_path, $file_name);  
    	}
        else
        {
            Session::flash('error','Please fill valid information.');
            return redirect()->back()->withInput($request->all());   
        }
        
    	$arr_insert['enc_name']         = $file_name;
        $arr_insert['original_name']    = $file_original_name;
        $arr_insert['category_id']      = $request->input('category');

    	$obj_status = $this->VideoModel->create($arr_insert);

    	if($obj_status)
    	{	
    		Session::flash('success','Video uploaded successfully.');
            return redirect('/videos');
    	}
    	else
    	{
    		Session::flash('error','Problem occured while adding video.');
    	}

        return redirect()->back();   	
    }

    public function delete($id)
    {   
        $status = $this->_delete($id);

        if($status == true)
        {
            Session::flash('success','Video deleted successfully.');
        }
        else
        {
            Session::flash('error','Error occured while deleting the video.');
        }

        return redirect()->back();
    }

    private function _delete($id)
    {
        $obj_video = $this->VideoModel->where('id',$id)->first();
        if($obj_video)
        {
            $enc_name = isset($obj_video->enc_name) ? $obj_video->enc_name : ''; 
            if($enc_name != "" && file_exists($this->video_base_path.$enc_name))
            {
                @unlink($this->video_base_path.$enc_name);
            }
            $obj_video->delete();
            return true;
        }
        return false;
    }

    public function validate_video_file($file = false)
    {
        if($file != false)
        {
            // file size
            if($file->getClientSize() > 10498677)
            {
                return 'Please upload file with size less that 10 MB.';
            }

            $mimetype = $file->getClientMimeType();
            $arr_mime_type = ['video/mp4','video/3gpp','video/quicktime','video/x-msvideo','video/x-ms-wmv','video/x-flv'];
            $matched  = false;
            foreach ($arr_mime_type as $key => $value) 
            {   
                if($mimetype == $value)
                {
                    $matched  = true;
                }
            }

            if($matched == true)
            {
                return true;
            }
            else
            {
                return 'Invalid file to Upload.';
            }
        }
        return 'Invalid file to Upload.';
    }
}