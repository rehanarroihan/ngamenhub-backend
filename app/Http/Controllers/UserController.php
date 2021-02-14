<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use App\Models\Event;
use App\Models\Portfolio;
use App\Models\Transaction;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
    public function __construct() {}

    public function detail(Request $request) {
        $user = User::where('id', $request->user->id)->with(['portfolios', 'groups']);

        if (!$user) {
            return ResponseFormatter::error(
                null,
                'User not found',
                404
            );
        }
        
        return ResponseFormatter::success(
            $user->get()->first(),
            'User detail fetched',
            200
        );
    }

    public function userdetail($user_id) {
        // For candidate
        $user = User::where('id', $user_id)->with(['portfolios']);

        if (!$user) {
            return ResponseFormatter::error(
                null,
                'Candidate not found',
                404
            );
        }
        
        return ResponseFormatter::success(
            $user->get()->first(),
            'Candidate detail fetched',
            200
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
            User::where('id', $request->user->id)->update($updateData);

            return ResponseFormatter::success(
                User::where('id', $request->user->id)->with(['portfolios', 'groups'])->get()->first(),
                'User data updated successfully'
            ); 
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                'Update user data failed'
            ); 
        }
    }

    public function profilepict(Request $request) {
        $validator = Validator::make($request->all(), [
            'picture' => 'mimes:jpeg,jpg,png,gif|required'
        ]);

        if ($validator->fails()) {
			return ResponseFormatter::error($validator->errors()->all(), $request->all());
        }

        try {
            $willUploadFile = ($request->file('picture'));

            $fileName = time().$willUploadFile->getClientOriginalName();
            $destinationPath = 'upload/profile';
            $willUploadFile->move(storage_path($destinationPath), $fileName);

            User::where('id', $request->user->id)->update([
                'picture' => $fileName
            ]);

            return ResponseFormatter::success(
                User::where('id', $request->user->id)->get()->first(),
                'Profile picture updated successfully'
            ); 
        } catch (Exception $e) {
            return ResponseFormatter::error(
                $e->getMessage(),
                'Update profile picture failed',
                500
            );
        }
    }

    public function portfolio(Request $request) {
        $validator = Validator::make($request->all(), [
            'video' => 'mimes:mp4,mov,ogg,qt|required|max:5120'
        ]);

        if ($validator->fails()) {
			return ResponseFormatter::error($validator->errors()->all(), $request->all());
        }

        try {
            $willUploadFile = ($request->file('video'));
            $fileName = time().$willUploadFile->getClientOriginalName();
            $destinationPath = 'upload/portfolio';
            $willUploadFile->move(storage_path($destinationPath), $fileName);

            $insert = Portfolio::create([
                'user_id' => $request->user->id,
                'video_file_name' => $fileName,
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

    public function getBalance(Request $request) {
        $result = (object) array(
            'balance'     => '',
            'histories'   => array(),
        );

        $paidTransaction = Transaction::where([
            'candidate_id' => $request->user->id,
            'status' => 'SUCCESS'
        ])
        ->with(['event']);

        // Getting income (debit) of logged in user from trx table
        foreach ($paidTransaction as $paidTrx) {
            $history = (object) array(
                'amount'   => (int) $paidTrx->event->fee,
                'type'     => 'debit',
                'date'     => $paidTrx->updated_at 
            );
            array_push($result->histories, $history);
        }

        // Getting outcome (credit) of user from withdraw table
        // TODO: write the code in future

        // Counting for balance value
        $balance = 0;
        foreach ($result->histories as $hist) {
            if ($hist->type == 'debit') {
                $balance = $balance + $hist;
            } else if ($hist->type == 'credit') {
                $balance = $balance - $hist;
            }
        }

        $result->balance = $balance;


        return ResponseFormatter::success(
            $result,
            'Balance data fetched'
        );
    }
}
