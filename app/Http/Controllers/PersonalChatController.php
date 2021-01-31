<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\User;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;

class PersonalChatController extends Controller
{
    public function room(Request $request) {
        $validator = Validator::make($request->all(), [
			'creator_user_id' => 'required',
			'target_user_id' => 'required'
        ]);

        if ($validator->fails()) {
			return ResponseFormatter::validatorFailed();
        }

        $isChatRoomAvail = $this->get(
            $request->creator_user_id,
            $request->target_user_id,
        );

        if (count($isChatRoomAvail) == 0) {
            $isChatRoomAvail = $this->insert(
                $request->creator_user_id,
                $request->target_user_id,
                date("ymd").rand(pow(10, 3-1), pow(10, 3)-1)
            );
        }

        if ($isChatRoomAvail == null) {
            return ResponseFormatter::error(
                null,
                'Failed to fetch chat room'
            );
        }

        $result = array(
            'users' => array(
                User::where('id', $isChatRoomAvail[0]->creator)->first(),
                User::where('id', $isChatRoomAvail[0]->target)->first(),
            ),
            'room_id' => $isChatRoomAvail[0]->firebase_chat_room_id
        );

        return ResponseFormatter::success(
            $result,
            'Chat room fetched'
        );
    }

    private function get($creator, $target) {
        return DB::table('personal_chat_rooms as rc')
            ->whereRaw('(rc.creator = ? AND rc.target = ?) OR (rc.creator = ? AND rc.target = ?)', [
                $creator,
                $target,
                $target,
                $creator,
            ])
            ->get();
    }

    private function insert($creator, $target, $roomId) {
        try {
            DB::table('personal_chat_rooms')->insert(array(
                'creator' => $creator,
                'target' => $target,
                'firebase_chat_room_id' => $roomId,
            ));
            
            $isChatRoomAvailAfter = $this->get(
                $creator,
                $target,
            );

            return $isChatRoomAvailAfter;
        } catch (Exception $error) {
            return null;
        }
    }
}