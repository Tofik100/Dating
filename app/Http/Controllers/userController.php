<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\CompleteProfile;
use App\Models\UserPost;
use App\Models\SocialLogin;
use App\Models\FavoriteUser;
use App\Models\personal_access_token;
use App\Models\user_gallery;
use App\Models\UserAccountSetting;
use JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use App\Models\DeviceData;
use Illuminate\Support\Facades\Mail;
use App\Mail\EmailVerification;
use Symfony\Component\HttpFoundation\Response;
use Hash;
use DB;
use Carbon\Carbon;
use image;
use Auth;
use Validator;

class userController extends Controller
{
    //
    // public function show($id)
    // {
    //     return $user = User::find($id);
    // }


    public function Register(Request $request)
    {
         
        // Api Field Validation
        if($request->signup_type == 'normal' || $request->signup_type == 'NORMAL'){
        $validate_field = array(
            'name'=>'required|string',
            'numbers'=>'required',
            'email'=>'required|email|unique:users,email',
            // 'password'=>'required',
            'signup_type' => 'required|in:NORMAL,GOOGLE,FACEBOOK',
            );
        }else
        {
            $validate_field = array(
            'name'=>'required|string',
            'email'=>'required|email',
            // 'social_token' => 'required',
            'signup_type' => 'required|in:NORMAL,GOOGLE,FACEBOOK',
            );
        }
        // Api Field Validation End

        $validator = Validator::make($request->input(), $validate_field);
        // dd($validator);
        //Send failed response if request is not valid
        if ($validator->fails()) {
             return response()->json(['status' => false, 'message' => $validator->errors()->first()], 201);    
        }
        // Insert data in database according field with password Hashing
        if($request->signup_type == 'normal' || $request->signup_type == 'NORMAL')
        {
            $user = User::create([
                'name' => $request->name,
                'numbers' => $request->numbers,
                'email' => $request->email,
                'role_id' => '2',
                'password' => Hash::make($request->password),
                'signup_type' => $request->signup_type,
            ]);


            // $user->sendEmailVerificationNotification();

            

            //   Mail::to($request->email)->send(new EmailVerification($request->name));           
            // if($user){
            //     //create new customer
            //     $customer = CompleteProfile::create([
            //         'user_id' => $user->id,
            //         'name' => $request->name,
            //     ]);
            // }
        }
        else
        {
            $user = User::where(['email' => $request->email, 'role_id' => 2 ])->first();
            if($user){
                //update user info
                $user->name = $request->name;
                $user->email = $request->email;
                $user->save();
                //update customer info
                $socilaUser = SocialLogin::where('user_id',$user->id)->first();
                $signup_type = $request->signup_type;
                if($signup_type == 'google' || $request->signup_type == 'GOOGLE'){
                    $socilaUser->google_id = $request->social_token;
                }elseif($signup_type == 'facebook' || $request->signup_type == 'FACEBOOK'){
                     $socilaUser->facebook_id = $request->social_token;
                }
                $socilaUser->save();
            }
            else
            {

                //create new user
                $user = User::create([
                    'name' => $request->name,
                    'numbers' => $request->numbers,
                    'email' => $request->email,
                    'role_id' => 2,
                    'status' => 1,
                    'password' => Hash::make($request->password),
                    'signup_type' => $request->signup_type,
                ]);
                if($user)
                {
                    //create new customer
                    $customer = SocialLogin::create([
                        'user_id' => $user->id,
                    ]);
                    $signup_type = $request->signup_type;
                    if($signup_type == 'google' || $request->signup_type == 'GOOGLE'){
                    $customer->google_id = $request->social_token;
                    }elseif($signup_type == 'facebook' || $request->signup_type == 'FACEBOOK'){
                         $customer->facebook_id = $request->social_token;
                    }
                    $customer->save();

                    //$device = DeviceData::where('user_id', $user->id)->first();
                   

                    //update user device info

                    if($device)
                    {
                        $device->device_key = $request->device_key;
                        $device->device_token = $request->device_token;
                        $device->latitude = $request->latitude;
                        $device->longitude = $request->longitude;
                        $device->save();
                    }
                    else
                    {
                        $device_data = DeviceData::create([
                            'device_key' => $request->device_key,
                            'device_token' => $request->device_token,
                            'latitude' => $request->latitude,
                            'longitude' => $request->longitude,
                            'user_id' => $user->id,
                        ]);
                    }

                }

            }
        }
        

        // Insert data in database according field End

        if($user)
        {
            // create Token
             $token = $user->createToken('userToken')->plainTextToken;
            // $tokenResult = JWTAuth::fromUser($user);
            // create Token end
            return response()->json([
                'status'=> true,
                "message"=>"User created successfully.",
                //'user' =>  $user,
                // 'token' => $token
            ],Response::HTTP_OK);
        }
        else
        {
            return response([
                "status" => false,
                "message"=>"You Are Not Register"
            ]);
        }
        
        
    }

    // Logout User Token through

    // public function logout() {

    //     auth('sanctum')->user()->tokens()->delete();
    //     return response()->json([
    //         'message' => 'You Are Logged out successfully'
    //     ],Response::HTTP_OK);
    // }

    public function logout(Request $request)
    {
        //valid credential
        $validator = Validator::make($request->only('token'), [
            'token' => 'required'
        ]);

        //Send failed response if request is not valid
        if ($validator->fails()) {
             return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);    
        }

        //Request is validated, do logout        
        try {
        JWTAuth::invalidate($request->token);
           Auth::guard( 'api' )->logout();
            return response()->json([
                'status' => true,
                'message' => 'User logged out'
            ]);
        } catch (JWTException $exception) {
            return response()->json([
                'status' => false,
                'message' => 'Sorry, User cannot be logged out'
            ], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    // Logout User Token through end


    // User Login Api

    
    public function login(Request $request)
    {  
        $inputVal = $request->all();
   
        $this->validate($request, [
            'email' => 'required|email',
            'password' => 'required',
            'device_key' => 'required',
            'device_token' => 'required',
            'latitude' => 'required',
            'longitude' => 'required'
        ]);


        // For User login time

        $user = User::where(['email' => $request->email,'role_id'=>2])->first();
        $device = DeviceData::where('user_id', $user->id)->first();

        if($device)
        {
            $device->device_key = $request->device_key;
            $device->device_token = $request->device_token;
            $device->latitude = $request->latitude;
            $device->longitude = $request->longitude;
            $device->save();
        }
        else
        {
            $device_data = DeviceData::create([
                'device_key' => $request->device_key,
                'device_token' => $request->device_token,
                'latitude' => $request->latitude,
                'longitude' => $request->longitude,
                'user_id' => $user->id,
                ]);
        }
   
        if(auth()->attempt(array('email' => $inputVal['email'], 'password' => $inputVal['password']))){
            if (auth()->user()->role_id == 1) {

                $user = $request->user();
                $tokenResult = JWTAuth::fromUser($user);
                $tokentable = personal_access_token::where("tokenable_id",Auth::id());
                // $tokenResult = $user->createToken('Admin Access Token')->plainTextToken;
                return response([
                    'status' => true,
                    'message' => 'Admin login successfully',
                    'data' => $user,
                    'token' => $tokenResult,
                ],Response::HTTP_OK);

            }
            elseif(auth()->user()->role_id == 2)
            {   
                $user = $request->user();
                $tokenResult = JWTAuth::fromUser($user);
                $tokentable = personal_access_token::where("tokenable_id",Auth::id());
                // if($tokentable)
                // {
                //     // $tokentable->$tokentable = $request->id;
                //     $tokentable->token = $tokenResult;
                //     $tokentable->save();
                // }
                // else
                // {
                //     $tokenAccessTable = personal_access_token::create([
                //     'token' => $tokenResult,
                //     ]);
                // }

                
                //$tokenResult = $user->createToken('User Access Token')->plainTextToken;
                return response([
                    'status' => true,
                    'message' => 'User login successfully',
                    'data' => $user,
                    'token' => $tokenResult,
                ],Response::HTTP_OK);
            }
        }else{
            
                return response([
                    'status' => false,
                    'message'=>'Email & Password are incorrect.'
                ]);
        }  
    }
     // User Login Api End


    //  Complate Profile Api


    
    public function complete_profile(Request $request)
    {
        // $res = $request->file('user_default_image	')->store('public/uploads');
        
        // dd($res);

        // $images = $request->file('user_default_image	');
        // $imageName = '';

        // foreach($images as $image)
        // {
        //     $new_name = rand(). '.' .$image->getClientOriginalExtension();
        //     $image->move(public_path('uploade'),$new_name);
        //     $imageName = $imageName.$new_name.',';
        // }

        // $imagedb = $imageName;

        // CompleteProfile::create([
        //     'user_default_image	' => $imagedb
        // ]);

        // return response()->json([
        //     "Message" => "Image Uploaded",
        //     "path" => $imagedb
        // ]); 


        

        //  $test = user_gallery::all();
        //  return $test;

        $validation_field = array(
             'id' => 'required',
             'name' => 'required|string',
             'user_bio' => 'required',
             'status' => 'require|in:0,1',
            // 'user_default_image	' => 'required|mimes:jpeg,jpg,png,gif,csv,txt,pdf|max:2048',
             'job_title' => 'required',
             'company' => 'required|string',
             'univercity_name' => 'required',
             'don’t_show_my_age' => 'required',
             'distance_invisible' => 'required',
             'email' => 'required|email|unique:users,email,'.$request->id,
             'gender' => 'required',
            //  'numbers' => 'required|unique:users,numbers,'.$request->id,
            //  'signup_type' => 'required|in:NORMAL,GOOGLE,FACEBOOK',
            //  'device_token' => 'required',
            //  'device_key' => 'required',
        );
        $validator = Validator::make($request->input(), $validation_field);
        //Send failed response if request is not valid
        if ($validator->fails()) {
             return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);    
        }
        $user = User::where(['id'=>$request->id ,'role_id'=>2])->first();
        $complete_profile = CompleteProfile::where('user_id',$request->id)->first();
        // dd($complete_profile);
        if($user){
            
            //update user data
            $user->name = $request->name;
            $user->email = $request->email;
            $user->numbers = $request->numbers;
            $user->profile_completed = 'yes';
            $user->save();
            //update customer data
            
            // $complete_profile = CompleteProfile::where('user_id',$request->id)->first();
            // dd($complete_profile);
            // $user_gallery = user_gallery::where('user_id',$request->id)->first();
            // $user_gallery = user_gallery::where(['user_id', 'users.status' => 1])->first();
            // $query = DB::table('users');
            // $query->where(['users.role_id' => 2, 'users.status' => 1])->get();

            // $activeusers = $query->get();
            // dd($activeusers);

            // dd($user_gallery);
            // Multiple image store in database using API

            $allowedfileExtension=['pdf','jpg','png'];
            $files = $request->file('user_default_image'); 
            $errors = [];

            

            // if($activeusers)
            // {
            //     foreach($activeusers as $key => $active)
            //     {
            //         $gallery = user_gallery::where('user_id', $active->id)->get()->first();
                     
            //         if($gallery){
            //             $activeusers[$key]->image_gallery = $gallery->image_gallery;
            //         }else{
            //             $activeusers[$key]->status = '0';
            //         }
            //     }
               
            // //    dd($test);

            // }
         
            foreach ($files as $file) {      
         
                $extension = $file->getClientOriginalExtension();
                $check = in_array($extension,$allowedfileExtension);
                $test = $user->status;
                // dd($check);
                //store image file into directory and db
                     $images = $request->file('user_default_image');
                     $imageName =  implode(",",$images);
                     $defaultImage =  trim("\\",$imageName);
                if($check) {
                    foreach($request->user_default_image as $mediaFiles) {
                        $name =  rand(). '.' .$mediaFiles->getClientOriginalExtension();
                        
                          $mediaFiles->storeAs('uploads', $name);

                        

                        //  return response()->json([
                        //      'status' => true,
                        //      'message' => 'Profile completed successfully!',
                        //     //   'data' => $user_data,
                        //     //   'token' => $token,
                        //  ], Response::HTTP_OK);
                    }

                    $save = new user_gallery();
                     $save->image_gallery = $name;
                     $save->status =  $test;
                     $save->user_id = $user->id;
                     $save->save();

                     

                    //store image file into directory and db
                    // $images = $request->file('user_default_image');
                    // $imageName =  implode(",",$images);
                    // $defaultImage =  trim("\\",$imageName);

                    // foreach($images as $image)
                    // {
                    //     $new_name = rand(). '.' .$image->getClientOriginalExtension();
                    //     $image->move(public_path('uploads'),$new_name);
                    //      $defaultImage = $defaultImage.$new_name.',';
                    // }

                    
                 }
                 
                 if($complete_profile)
            
                {
                $complete_profile->name = $request->name;
                $complete_profile->user_bio = $request->user_bio;
                // $complete_profile->user_bio = $request->user_bio;
                //  $user_gallery->user_default_image=  $images;
                $complete_profile->job_title = $request->job_title;
                $complete_profile->company = $request->company;
                $complete_profile->univercity_name = $request->univercity_name;
                $complete_profile->don’t_show_my_age = $request->don’t_show_my_age;
                $complete_profile->distance_invisible = $request->distance_invisible;
                $complete_profile->gender = $request->gender;
                $complete_profile->save();
                }
                else
                {
                // Multiple image store in database using API

                // $images = $request->file('user_default_image	');
                // $imageName = ['name','id'];
                // foreach($images as $image)
                // {
                //     $new_name = rand(). '.' .$image->getClientOriginalExtension();
                //     $image->move(public_path('uploade'),$new_name);
                //     $imageName = $imageName.$new_name.',';
                // }
                // $imagedb[] = $imageName;
                // Multiple image store in database using API END
                
                $CreateUserProfile = CompleteProfile::create([
                    'name' => $request->name,
                    'user_bio' => $request->user_bio,
                    'job_title' => $request->job_title,
                        'company' => $request->company,
                        // 'user_default_image	' => $imagedb,
                        'univercity_name' => $request->univercity_name,
                        'don’t_show_my_age' => $request->don’t_show_my_age,
                        'distance_invisible' => $request->distance_invisible,
                        'gender' => $request->gender,
                        'user_id' => $user->id,
                    ]);
                    // dd($CreateUserProfile);
                }
            
                }
                // $images = $request->file('user_default_image');
                // $imageName =  implode(",",$images);
                // $imageNames =  trim("\\",$imageName);
                // foreach($images as $image)
                // {
                //     $new_name = rand(). '.' .$image->getClientOriginalExtension();
                //     $image->move(public_path('uploads'),$new_name);
                //     // $imageNames = $imageNames.$new_name.',';
                // }
                // $imagedb=  $new_name;
                //dd($imagedb);
                //dd($imagedb);
            
            // Multiple image store in database using API END

            // $images = $request->file('user_default_image');
            // $imageName =  implode(",",$images);
            // $defaultImage =  trim("\\",$imageName);

            // $new_name = rand(). '.' .$file->getClientOriginalExtension();
            // $file->move(public_path('uploads'),$new_name);
            // $defaultImage = $defaultImage.$new_name.','; 
            // dd($defaultImage);   

            // $save = new user_gallery();
            // $save->image_gallery = $name;
            // $save->user_default_image =  $new_name;
            // $save->user_id = $user->id;
            // $save->save();

            // $complete_profile = CompleteProfile::where('user_id',$request->id)->first();
            // dd($complete_profile);
            // $user_gallery = user_gallery::where('user_id',$request->id)->first();
            
            
            // $device = DeviceData::where('user_id', $user->id)->first();
            //update user device info
            // if($device){
            //     $device->device_token = $request->device_token;
            //     $device->device_key = $request->device_key;
            //     $device->latitude = $request->latitude;
            //     $device->longitude = $request->longitude;
            //     $device->save();
            // }else{
            //     $device_data = DeviceData::create([
            //         'device_token' => $request->device_token,
            //         'device_key' => $request->device_key,
            //         'latitude' => $request->latitude,
            //         'longitude' => $request->longitude,
            //         'user_id' => $user->id,
            //     ]);
            // }
            //user data for response
            $user_data = array(
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'numbers' => $user->numbers,
                // 'gender' => $complete_profile->gender,
                'profile_completed' => $user->profile_completed,
                'email_verified' => $user->email_verified,
                'mobile_verified' => $user->mobile_verified,
                'signup_type' =>  $user->signup_type,
            );
            //Return response
            if($user->signup_type == 'NORMAL' || $user->signup_type == 'normal'){
                $token = $token = JWTAuth::fromUser($user);
            }else{
                $token = JWTAuth::fromUser($user);
            }
            return response()->json([
                'status' => true,
                'message' => 'Profile completed successfully!',
                // 'data' => $user_data,
                // 'token' => $token,
            ], Response::HTTP_OK);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Invalid User id!',
            ], Response::HTTP_OK);
        }
    }

    

     //  Complate Profile end


    //  GET PROFILE API

     public function get_user_profile_info(Request $request){
        $validation_field = array(
             'user_id' => 'required',
            //  'token' => 'required'
         );
         $validator = Validator::make($request->input(), $validation_field);
         //Send failed response if request is not valid
         if ($validator->fails()) {
              return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);    
         }
         $user = User::where(['id'=>$request->user_id ,'role_id'=>2])->first();
         $complete_profile = CompleteProfile::where('user_id',$request->user_id)->first();
         
         $user_gallery = user_gallery::where('user_id',$request->user_id)->first();

        //  $imagedb = array([
        //     'name' => $complete_profile->user_default_image	,
        //     'id' => $user->id
        // ]);

         $profileImages = DB::table('users')
         ->where('user_gallerys.user_id',$user->id)
        ->join('user_gallerys', 'users.id' , '=','user_gallerys.user_id')
        ->select('user_gallerys.id', 'user_gallerys.image_gallery')->get();
       
        //  ->select('user_gallerys.id', 'user_gallerys.image_gallery', 'user_gallerys.user_id')->get();
        //  return $test;
        // foreach($test['image_gallery'] as $profiles_images)
        // {
        //     $data = $profiles_images->image_gallery;
        //     dd($data);
        // }
        // $users = DB::table('user_gallerys')->get()->toJson();
        // echo '<pre>';
        // return $users;
        //  $profile_loop = array($user_gallery->image_gallery, $user_gallery->user_id, $user_gallery->id);
        //  //dd($profile_loop);

        //  foreach($profile_loop as $profiles)
         {
            // dd($profiles);
            //   return DB::table('users')
            //     ->join('user_gallerys', 'user_gallerys.id' , '=','users.id')
            //      ->select('user_gallerys.user_id', 'user_gallerys.image_gallery')->get();
                // ->where('users.id', '=' , 'user_gallerys.user_id') ->get();
         }
            //  $images = $request->file('user_default_image	');
            // $imageName =  implode(",",$images);
            // $imageNames =  trim("\\",$imageName);
            //$test = $data;
        //  dd($imagedb);
        
         if($user){
             $user_data = array(
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'numbers' => $user->numbers,
                // 'user_default_image	' => $user_gallery->user_default_image,
                 'user_default_image' => array([
                         'default image' => $user_gallery->image_gallery,
                         'user_id' => $user->id,
                     ]),
                'user_profile' => $profileImages,
                'gender' => $complete_profile->gender,
                'profile_completed' => $user->profile_completed,
                'job_title' => $complete_profile->job_title,
                'company'=> $complete_profile->company,
                // 'company'=> $complete_profile->user_default_image	,
                'univercity_name' => $complete_profile->univercity_name,
                'don’t_show_my_age'=> $complete_profile->don’t_show_my_age,
                'distance_invisible'=> $complete_profile->distance_invisible,
                // 'gender' => $complete_profile->gender,
                // 'email_verified' => $user->email_verified,
                // 'mobile_verified' => $user->mobile_verified,
                'signup_type' =>  $user->signup_type,
             );
             return response()->json([
                 'status' => true,
                 'data' => $user_data,
                 'image_base_url' => env('IMAGE_BASE_URL')."/app/uploads/"
             ]);
         }else{
             return response()->json([
                 'status' => false,
                 'message' => "Invalid User id!",
             ]); 
         }
     }



     // GET PROFILE API  END

    // User Account Setting

    public function user_accound_settings(Request $request)
    {
        $validate_field = array(
             'numbers' => 'required',
            'latitude' => 'required',
            'longitude' => 'required',
            // 'gender' => 'required',
            'show_me_sos' => 'required',
            'show_me_distance' => 'required|in:Km,Mi',
            'maximum_distance' => 'required',
            // 'age_range' => 'required',
            // 'min_age' => 'required',
            // 'max_age' => 'required',
        );


        

        $validate = Validator::make($request->input(), $validate_field);
       
         //Send failed response if request is not valid
         if ($validate->fails()) {
            return response()->json(['status' => false, 'message' => $validate->errors()->first()], 200);    
         }

            $user = User::where(['id' => $request->user_id,'role_id'=>2])->first();
            $accountSetting = UserAccountSetting::where('user_id',$request->user_id)->first();
            if($accountSetting)
            {   

                $accountSetting->numbers = $request->numbers;
                // $accountSetting->current_location = $request->current_location;
                $accountSetting->latitude = $request->latitude;
                $accountSetting->longitude = $request->longitude;
                // $accountSetting->gender = $request->gender;
                $accountSetting->min_age = $request->min_age;
                $accountSetting->max_age = $request->max_age;
                $accountSetting->show_me_sos = $request->show_me_sos;
                $accountSetting->show_me_distance = $request->show_me_distance;
                $accountSetting->maximum_distance = $request->maximum_distance;
                // $accountSetting->age_range = $request->age_range;
                $accountSetting->save();

                return response([
                    'status' => true,
                    'message' => "Account-setting Updated!",
                    // 'data' => $setting
                ], Response::HTTP_OK);
            }
            else
            {
                $setting = UserAccountSetting::create([
                    'numbers' => $user->numbers,
                    'latitude' => $request->latitude,
                    'longitude' => $request->longitude,
                    'min_age' => $request->min_age,
                    'max_age' => $request->max_age,
                    // 'gender' => $request->gender,
                    'show_me_sos' => $request->show_me_sos,
                    'show_me_distance' => $request->show_me_distance,
                    'maximum_distance' => $request->maximum_distance,
                    'age_range' => $request->age_range,
                    'user_id' => $user->id,
                ]);

                return response([
                    'status' => true,
                    'message' => "Account-setting Updated!",
                    // 'data' => $setting
                ], Response::HTTP_OK);

                
            }
    }
    

    // User Account Setting End

        // User Account Setting update

        public function get_account_setting(Request $request)
        {

            $validation_field = array(
                'user_id' => 'required',
                // 'token' => 'required'
            );
            $validator = Validator::make($request->input(), $validation_field);
            //Send failed response if request is not valid
            if ($validator->fails()) {
                 return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);    
            }



            $user = User::where(['id'=>$request->user_id ,'role_id'=>2])->first();
            $account_setting = UserAccountSetting::where('user_id',$request->user_id)->first();
            
             $latitudeFloateValue = $account_setting->latitude;
             $latitudeValue = round($latitudeFloateValue,4);
             
             $longitudeFloateValue = $account_setting->longitude;
             $longitudeValue = round($longitudeFloateValue,4);
            
            //  dd($value);
            if($user){
               
             $user_data = array(
                    'id' => $user->id,
                    'numbers' =>$account_setting->numbers,
                    'latitude' => $latitudeValue,
                    'longitude' => $longitudeValue,
                    'gender' => $account_setting->gender,
                    'show_me_sos' => $account_setting->show_me_sos,
                    'show_me_distance' => $account_setting->show_me_distance,
                    'maximum_distance' => $account_setting->maximum_distance,
                    'age_range' => $account_setting->age_range,
                    'user_id' => $user->id,
                    'min_age' => $account_setting->min_age,
                    'max_age' => $account_setting->max_age,
             );
                return response()->json([
                    'status' => true,
                    'data' => $user_data,
                    // 'image_base_url' => env('IMAGE_BASE_URL')."/assets/"
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => "Invalid User id!",
                ]); 
            }
        }
        
    
        // Forgot Password



        Public function forgot_password(Request $request){
            $validation_field = array(
              'email' => 'required'
            );
            // if($request->email == 'EMAIL'){
            //   $validation_field['email'] = 'required|email';
            // }
            $validator = Validator::make($request->all(), $validation_field );
             if ($validator->fails()) {
                 return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);                 
             }
             
            //  if($request->email == 'EMAIL'){
                 $user = User::where(['email' => $request->email,'role_id'=>2])->first();
                //  dd($user);
                 if($user){
                     $password = $this->random_alphanumeric_string(8);
                     $email = $user->email;
                     $param = array('name'=>$user->name, 'password'=>$password,'email'=>$email);
                     try{
                         Mail::send('emails.forgot_password', $param, function ($message) use ($email) {
                             $message->from('ctindiabkn@gmail.com', 'Datting App');
                             $message->subject('Forgot password');
                             $message->to($email);
                         });
                         $user->password = bcrypt($password);
                         $user->save();
                         return response()->json([
                             'status' => true,
                             'password' =>  $password,
                             'message' => 'New password sent to your registered email address. Please check your inbox!',
                         ]);  
                     }
                     catch(\Exception $e ){
                         $error = $e->getMessage();
                         return response()->json([
                             'status' => false,
                             'message' => $error,
                         ]); 
                     }
                 }else{
                     $error ="Email address  does not exist!";
                     return response()->json([
                         'status' => false,
                         'message' => $error,
                     ]);  
                 }
            //  }
         }



        // Forgot Password End
        
   
        public function random_alphanumeric_string($length) {
            $chars = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
            return substr(str_shuffle($chars), 0, $length);
        }

        // User Dashboard Api

        public function userpostImage(Request $request)
        {  
            
            
            // $request->validate([
            //     'status' => 1,
            //     // 'token' => 'required'
            // ]);
            
            //    $validator = Validator::make($request->input(), $validation_field);
            //    //Send failed response if request is not valid
            //    if ($validator->fails()) {
            //         return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);    
            //    }
        
              $user = User::where(['status' => $request->status])->first();
            //   return $user;
              $account_setting = UserAccountSetting::where('user_id',$user->id)->first();
              $user_gallery = user_gallery::select('image_gallery','id')->first();

                $profileImages = DB::table('users')
                  ->where('user_gallerys.user_id', $user->id)
                 ->join('user_gallerys', 'users.id' ,'user_gallerys.user_id')
                 ->join('complete_profiles', 'users.id' ,'complete_profiles.user_id')
                 ->select('user_gallerys.id', 'user_gallerys.image_gallery', 'user_gallerys.id')->get();
                 
                //  dd($profileImages);
                
                 

              if($user)
              {
                  $user_data = array(
                      'id' => $user->id,
                      'name' => $user->name,
                      'distance' => $account_setting->show_me_distance,
                      'user_default_image' => array([
                               'default image' => $user_gallery->image_gallery,
                               'user_id' => $user->id,
                          ]),
                     'user_profile' => $profileImages
                  );

                //   foreach($user_data as $test)
                //   {
                //         return  $test->$user_data->id;
                //   }

                  
                  return response()->json([
                      'status' => true,
                      'data' => $user_data,
                            //  'image_base_url' => env('IMAGE_BASE_URL')."/app/uploads/"
                     ]);
                 }
                 else{
                     return response()->json([
                         'status' => false,
                         'message' => "Invalid User id!",
                     ]); 
                 }

        }

        // User Dashboard Api End

        // Favorite Post Images Api 

        public function favoriteImage(Request $request)
        {
            //  return FavoriteUser::all();

            $validation_field = array(
                'user_id' => 'required',
                'token' => 'required'
              );

              $id = Auth::id();
              if($id)
              {
                $user_id = $request->user()->id;
                // return $user_id;
                $favorite_id = Auth::id();

              $favariteUserID = FavoriteUser::where(['favorite_user_id' => $favorite_id, 'user_id' =>$user_id]);
              if($favariteUserID )
              {
                $favoriteUser = new FavoriteUser;
                $favoriteUser->user_id = $id;
                $favoriteUser->favorite_user_id = $request->favorite_user_id;
                $favoriteUser->save();

                return response()->json([
                    'status' => true,
                    'message' => "Add You Fevorite",
                ]); 
              }
              
            }else
            {
                return response()->json([
                    'status' => false,
                    'message' => "Don't Favorite",
                ]); 
            }


        }

        // Favorite Post Images Api End


        // Get Favorite Api 

 public function getFavoriteUser(Request $request)
 {
     // return FavoriteUser::all();

     $validation_field = array(
         'user_id' => 'required',
         // 'token' => 'required'
       );
     
       $validator = Validator::make($request->input(), $validation_field);
       //Send failed response if request is not valid
       if ($validator->fails()) {
            return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);    
       }

       $user = User::where(['id'=>$request->user_id ,'role_id'=>2])->first();
     //   $favariteUserID = FavoriteUser::where(['favorite_user_id' => $favorite_id, 'user_id' =>$user_id]);
         $complete_profile = CompleteProfile::where('user_id',$request->user_id)->first();

            $profileImages = DB::table('users')
            ->where('favorite_user.user_id',$user->id)
            ->join('favorite_user', 'users.id' , '=','favorite_user.favorite_user_id')
            ->join('complete_profiles as cp', 'users.name' , '=','cp.name')
            ->select('favorite_user.favorite_user_id','cp.name', 
                'cp.user_bio','cp.job_title','cp.company')->get();
   

            if($user){
                $user_data = array(
                    'id' => $user->id,
                    'name' => $user->name,
                    'Favorite_User_Profile' => $profileImages,
                );
                return response()->json([
                    'status' => true,
                    'data' => $user_data,
                    // 'image_base_url' => env('IMAGE_BASE_URL')."/app/uploads/"
                ]);
            }else{
                return response()->json([
                    'status' => false,
                    'message' => "Your favarite user does no exist!",
                ]); 
            }

 }

// Get Favorite Api  End


// Remove(Delete) Favorite Api


    public function removeFavorite(Request $request)
    {
        $validation_field = array(
            'remove_favorite_user' => 'required',
            // 'token' => 'required'
          );
        
          $validator = Validator::make($request->input(), $validation_field);
          //Send failed response if request is not valid
          if ($validator->fails()) {
               return response()->json(['status' => false, 'message' => $validator->errors()->first()], 200);    
          }
         $favoriteUser = FavoriteUser::where(['favorite_user_id' => $request->remove_favorite_user]);
        $result = $favoriteUser->delete();
        if( $result )
        {
            return [
                'status' => true,
                'Message' => 'Remove Your Favorite List Successfully'
            ];
        }
        else
        {
            return [
                'status' => false,
                'Message' => 'Not Remove Your Favorite List'
            ];
        }

    }

// Remove(Delete) Favorite Api End


}
