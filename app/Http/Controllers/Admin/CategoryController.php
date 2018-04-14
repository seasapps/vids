<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Models\CategoryModel;
use App\Models\AppkeyModel;
use App\Models\VideoModel;



use Validator;
use Session;

class CategoryController extends Controller
{
    
    public function __construct(
                                    CategoryModel $category_model,
                                    AppkeyModel $appkeymodel,
                                    VideoModel $videomodel
                                )
    {
    	$this->CategoryModel = $category_model;
    	$this->AppkeyModel   = $appkeymodel;
        $this->VideoModel    = $videomodel;

        $this->category_base_img_path   = public_path().'/uploads/category_images/';
        $this->category_public_img_path = url('/').'/uploads/category_images/';

        $this->video_base_path   = public_path().'/uploads/';
        $this->video_public_path = url('/').'/uploads/';

    	$this->module_title  = 'Category';
    	$this->module_view_folder = 'admin.categories'; 
        $this->arr_view_data = [];
        $this->module_url_path = url('categories');
    }

    public function index()
    {
        $arr_category = array();

        $obj_category = $this->CategoryModel->orderBy('id','desc')->get();

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
        }

        $this->arr_view_data['page_title']               = "Manage ".str_plural($this->module_title);
        $this->arr_view_data['module_title']             = str_plural($this->module_title);
        $this->arr_view_data['module_url_path']          = $this->module_url_path;
        $this->arr_view_data['arr_category']             = $arr_category;
        $this->arr_view_data['category_public_img_path'] = $this->category_public_img_path;

        return view($this->module_view_folder.'.manage',$this->arr_view_data);
    }

    public function create()
    {
        $this->arr_view_data['page_title']               = "Add ".str_singular($this->module_title);
        $this->arr_view_data['module_title']             = str_plural($this->module_title);
        $this->arr_view_data['module_url_path']          = $this->module_url_path;

        return view($this->module_view_folder.'.create',$this->arr_view_data);
    }

    public function edit($id)
    {	
    	$arr_category = array();

        $obj_category = $this->CategoryModel->where('id',base64_decode($id))->first();

        if($obj_category)
        {
            $arr_category = $obj_category->toArray();
        }

        $this->arr_view_data['page_title']               = "Edit ".str_singular($this->module_title);
        $this->arr_view_data['module_title']             = str_plural($this->module_title);
        $this->arr_view_data['module_url_path']          = $this->module_url_path;
        $this->arr_view_data['arr_category']          	 = $arr_category;
        $this->arr_view_data['category_public_img_path'] = $this->category_public_img_path;
        return view($this->module_view_folder.'.edit',$this->arr_view_data);
    }

    public function store(Request $request)
    {  
        $arr_rules['name'] 			 = "required|max:255";
        $arr_rules['category_image'] = "image|mimes:jpg,png,jpeg";
         
        $validator = Validator::make($request->all(),$arr_rules);

        if($validator->fails())
        {
            Session::flash('error','Please fill required fields.');
            return redirect()->back()->withErrors($validator)->withInput($request->all());
        }

        // Check for name duplication 
        $does_exists = $this->CategoryModel
                            ->where('name','=',$request->input('name'))
                            ->count();   
        if($does_exists)
        {
            Session::flash('error','Category with this name already exists.');
            return redirect()->back()->withInput($request->all());
        }    

        $file_name      = "default.jpg";

        /* Upload image */
        if($request->hasFile('category_image'))
        {    
	        $file_name      = $request->input('category_image');
	        $file_extension = strtolower($request->file('category_image')->getClientOriginalExtension()); 
	        $file_name      = sha1(uniqid().$file_name.uniqid()).'.'.$file_extension;
	        $request->file('category_image')->move($this->category_base_img_path, $file_name);  
    	}

        $arr_insert = ['name' => $request->input('name'), 'category_image' => $file_name, 'slug' =>  str_slug($request->input('name'))];
        
        $status = $this->CategoryModel->create($arr_insert);
        
        if($status)
        {
        	Session::flash('success','Category added successfully.');
        	return redirect('/categories');
        }
        else
        {
       	 	Session::flash('error','Error occured while adding the category.');
        }

        return redirect()->back();
    }

    public function update(Request $request)
    {  
        $arr_rules['name'] 		  = "required|max:255";
        $arr_rules['category_id'] = "required";
        $arr_rules['category_image'] = "image|mimes:jpg,png,jpeg";
         
        $validator = Validator::make($request->all(),$arr_rules);

        if($validator->fails())
        {
            Session::flash('error','Please fill required fields.');
            return redirect()->back()->withErrors($validator)->withInput($request->all());
        }

        $category_id = base64_decode($request->input('category_id'));
        // Check for name duplication 
        $does_exists = $this->CategoryModel
                            ->where('id','<>',$category_id)
                            ->where('name','=',$request->input('name'))
                            ->count();   
        if($does_exists)
        {
            Session::flash('error','Category with this name already exists.');
            return redirect()->back()->withInput($request->all());
        }    
        


        $arr_update = ['name' => $request->input('name')];

        /* Upload image */
        if($request->hasFile('category_image'))
        {    
            $file_name      = $request->input('category_image');
            $file_extension = strtolower($request->file('category_image')->getClientOriginalExtension()); 
            $file_name      = sha1(uniqid().$file_name.uniqid()).'.'.$file_extension;
            $request->file('category_image')->move($this->category_base_img_path, $file_name); 
            $arr_update['category_image'] = $file_name;

            $obj_category = $this->CategoryModel
                                    ->where('id','=',$category_id)
                                    ->first();  
            if($obj_category)
            {   
                if(isset($obj_category->category_image) && file_exists( $this->category_base_img_path.$obj_category->category_image) && $obj_category->category_image != 'default.png')
                {
                    $previous_image_path = $this->category_base_img_path.$obj_category->category_image;
                }
            }
        }
        
        $status = $this->CategoryModel->where('id', $category_id)->update($arr_update);
        
        if($status)
        {   
            if(isset($previous_image_path))
            {
                @unlink($previous_image_path);
            }

        	Session::flash('success','Category updated successfully.');
        	return redirect('/categories');
        }
        else
        {
       	 	Session::flash('error','Error occured while updating the category.');
        }

        return redirect()->back();
    }

    public function delete($id)
    {	
    	$status = $this->_delete($id);

    	if($status == true)
    	{
    		Session::flash('success','Category deleted successfully.');
    	}
    	else
    	{
    		Session::flash('error','Error occured while deleting the category.');
    	}

        return redirect()->back();
    }

    private function _delete($id)
    {
    	$obj_category = $this->CategoryModel->where('id',$id)->first();
    	if($obj_category)
    	{
    		$obj_category->delete();

            if(isset($obj_category->category_image) && file_exists( $this->category_base_img_path.$obj_category->category_image) && $obj_category->category_image != 'default.png')
            {
                $previous_image_path = $this->category_base_img_path.$obj_category->category_image;
                @unlink($previous_image_path);
            }
            // unlink the videos under that category
            
            $obj_videos = $this->VideoModel->where('category_id',$id)->get();
            if(count($obj_videos) > 0)
            {   
                $arr_videos = $obj_videos->toArray();
                
                foreach ($arr_videos as $key => $value) 
                {   
                    if(isset($value['enc_name']) && file_exists( $this->video_base_path.$value['enc_name']))
                    {
                        $video_path = $this->video_base_path.$value['enc_name'];
                    }

                    $status_delete = $this->VideoModel->where('id', $value['id'])->delete();
                    if($status_delete)
                    {
                        @unlink($video_path);   
                    }
                }
            }

    		return true;
    	}
    	return false;
    }

    public function edit_app_key()
    {
    	$obj_app_key = $this->AppkeyModel->where('id','1')->first();
    	if($obj_app_key)
    	{
    		$arr_app_key = $obj_app_key->toArray();
    	}

    	$this->arr_view_data['page_title']               = "Edit App Key";
    	$this->arr_view_data['arr_app_key']               = $arr_app_key;

        $this->arr_view_data['module_title']             = "Edit App Key";
        return view($this->module_view_folder.'.edit_app_key',$this->arr_view_data);
    }

    public function update_app_key(Request $request)
    {  
        $arr_rules['app_key'] 		  = "required|max:500";
         
        $validator = Validator::make($request->all(),$arr_rules);

        if($validator->fails())
        {
            Session::flash('error','Please fill required fields.');
            return redirect()->back()->withErrors($validator)->withInput($request->all());
        }

        $status = $this->AppkeyModel->updateOrCreate(['id' => 1] , ['app_key' => $request->input('app_key')]);
        
        if($status)
        {
        	Session::flash('success','App Key updated successfully.');
        }
        else
        {
       	 	Session::flash('error','Error occured while updating App Key.');
        }
        return redirect()->back();
    }
}
