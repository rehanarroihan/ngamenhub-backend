<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Portfolio;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;

class UserController extends Controller
{
    public function __construct() {}

    public function detail(Request $request) {
        $user = User::where('id', $request->user->id)->first();

        if (!$user) {
            return ResponseFormatter::error(
                null,
                'User not found',
                404
            );
        }
        
        return ResponseFormatter::success(
            $user,
            'User detail detail fetched',
            404
        );
    }

    public function update(Request $request) {
        $picture = $request->picture;
        $skills = $request->skills;
        $full_name = $request->full_name;
        $bio = $request->bio;

        $updateData = array();

        if ($picture) {
            $updateData['picture'] = $picture;
        }

        if ($skills) {
            $updateData['skills'] = $skills;
        }

        if ($full_name) {
            $updateData['full_name'] = $full_name;
        }

        if ($bio) {
            $updateData['bio'] = $bio;
        }

        try {
            $user = User::where('id', $request->user->id)->update($updateData);

            return ResponseFormatter::success(
                $user,
                'User data updated successfully'
            ); 
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                'Update user data failed'
            ); 
        }
    }

    public function portfolio(Request $request) {
        if (!is_array($request->file('video'))) {
            return ResponseFormatter::validatorFailed();
        }

        try {
            $file_count = count($request->file('video'));
            $a = ($request->file('video'));
            $finalArray = array();
            for ($i=0; $i < $file_count; $i++) {
                $fileName = time().$a[$i]->getClientOriginalName();
                $destinationPath = 'upload/portfolio';
                $finalArray[$i] = $fileName;
                $a[$i]->move(storage_path($destinationPath), $fileName);
            }

            $insert = Portfolio::create([
                'user_id' => $request->user->id,
                'video_file_name' => $request->description,
            ]);

            return ResponseFormatter::success(
                $insert,
                'Portfolio created'
            );
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                'Create portfolio failed',
                500
            );
        }
    }
}
