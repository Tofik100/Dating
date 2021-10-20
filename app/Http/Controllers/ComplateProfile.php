<?php

namespace App\Http\Controllers;


use Illuminate\Http\Request;
use App\Models\CompleteProfile;
use DB;
use Auth;
use Validator;
use image;
use App\Models\User;

class ComplateProfile extends Controller
{
    //

    public function completeProfiles(Request $request)
    {

          

        $validate_field = $request->validate([
                'name' => 'required|string|max:255',
                'user_bio' => 'required|string|max:255',
                'user_image_uploade' => 'required|string|max:255',
                'job_title' => 'required| string|max:255',
                'univercity_name' => 'required|string|max:255',
                'gender' => 'required||Male|Female',
                'don’t_show_my_age' => 'required|integer|0|1',
                'distance_invisible' => 'required|integer|0|1',
            ]);


            $validate = Validator::make($request->input(), $validate_field);
       
         //Send failed response if request is not valid
         if ($validate->fails()) {
            return response()->json(['status' => false, 'message' => $validate->errors()->first()], 200);    
         }

            // $user = User::where(['id'=>$request->id ,'role_id'=>2])->first();
                    $comlplete_profile = new Completeprofile;
                    $comlplete_profile->name = $request->name;
                    $comlplete_profile->user_bio  = $request->user_bio;
                    $comlplete_profile->user_image_uploade  = $request->file('file')->store('public/uploads');
                    $comlplete_profile->job_title  = $request->job_title;
                    $comlplete_profile->univercity_name  = $request->univercity_name;
                    $comlplete_profile->gender  = $request->gender;
                    $comlplete_profile->user_id  = $request->user_id;
                    $comlplete_profile->don’t_show_my_age  = $request->show_age;
                    $comlplete_profile->distance_invisible  = $request->distance_invisible;
                    $result = $comlplete_profile->save();

                    if($result)
                    {
                        return  response([
                            "status" => true,
                            "message" => "Profile completed successfully!",
                            "data" => $comlplete_profile,
                        ]);
                    }
       
    }


    // Update profile Function

    public function updateProfile(Request $request, $id)
    {
        // // $image = $request->file('file')->store('public/uploads');
        // $image = $request->file('file');
        // dd( $image);

        $updateProfile = CompleteProfile::find($id);
        $updateProfile->name = $request->name;
        $updateProfile->user_bio  = $request->user_bio;
        // $updateProfile->user_image_uploade  = $request->file('file')->store();
        $updateProfile->job_title  = $request->job_title;
        $updateProfile->univercity_name  = $request->univercity_name;
        $updateProfile->gender  = $request->gender;
        $updateProfile->don’t_show_my_age  = $request->don’t_show_my_age;
        $updateProfile->distance_invisible  = $request->distance_invisible;
        $result = $updateProfile->save();

        if($result)
        {
            return response([
                "status" => true,
                "message" => "Profile Updated Successful",
            ]);
        }
    }

}
