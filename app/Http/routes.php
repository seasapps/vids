<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/


Route::get('/', function () {
	return view('admin.login');
});

Route::get('/login', function () {
	return view('admin.login');
});

Route::post('/process_login', ['as'=>'Process Login','uses'=>'Admin\AuthController@process_login']);
Route::get('/logout', ['as'=>'Logout','uses'=>'Admin\AuthController@logout']);

Route::group(['middleware'=>'auth'], function () 
{
	Route::get('/dashboard', function () {
		return view('admin.dashboard');
	});

	// Route::group(['prefix'=>'/user' ,'middleware' => 'admin_auth'], function () 
	// {
	// 	$controller_in_use = 'Admin\UserController@';
	// 	Route::get('/', ['as'=>'Manage Users','uses'=>$controller_in_use.'index']);
	// 	Route::get('/create', ['as'=>'Create User','uses'=>$controller_in_use.'create']);
	// 	Route::post('/store', ['as'=>'Store User','uses'=>$controller_in_use.'store']);
	// 	Route::get('/edit/{enc_id}', ['as'=>'Edit User','uses'=>$controller_in_use.'edit']);
	// 	Route::post('/update', ['as'=>'Store User','uses'=>$controller_in_use.'update']);
	// });

	Route::group(['prefix'=>'/videos' ,'middleware' => 'admin_auth'], function () 
	{
		$controller_in_use = 'Admin\UserController@';
		Route::get('/', 			['as'=>'Manage Users','uses'=>$controller_in_use.'index']);
		Route::get('/create', 		['as'=>'Create User','uses'=>$controller_in_use.'create']);
		Route::post('/store', 		['as'=>'Store User','uses'=>$controller_in_use.'store']);
		Route::get('/delete/{id}',  ['as'=>'Delete Video','uses'=>$controller_in_use.'delete']);
	});

	Route::group(['prefix'=>'/categories' ,'middleware' => 'admin_auth'], function () 
	{
		$controller_in_use = 'Admin\CategoryController@';
		Route::get('/', ['as'=>'Manage Categories','uses'=>$controller_in_use.'index']);
		Route::get('/create', ['as'=>'Create Category','uses'=>$controller_in_use.'create']);
		Route::post('/store', ['as'=>'Store Category','uses'=>$controller_in_use.'store']);
		Route::get('/delete/{id}', ['as'=>'Delete Category','uses'=>$controller_in_use.'delete']);
		Route::get('/edit/{id}', ['as'=>'Edit Category','uses'=>$controller_in_use.'edit']);
		Route::post('/update',  ['as'=>'Update Category','uses'=>$controller_in_use.'update']);
	});

	Route::get('/edit_app_key', ['as'=>'Edit App Key','uses'=>'Admin\CategoryController@edit_app_key']);
	Route::post('/update_app_key',  ['as'=>'Update App Key','uses'=>'Admin\CategoryController@update_app_key']);
});

// API
Route::group(['prefix'=>'/api'], function () 
{
	$controller_in_use = 'Admin\ApiController@';
	Route::get('/get_categories', ['as'=>'get_categories','uses'=>$controller_in_use.'get_categories']);
	Route::get('/get_videos', ['as'=>'get_videos','uses'=>$controller_in_use.'get_videos']);
	Route::get('/get_random_videos', ['as'=>'get_random_videos','uses'=>$controller_in_use.'get_random_videos']);
});