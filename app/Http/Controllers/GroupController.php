<?php

namespace App\Http\Controllers;

use Exception;
use App\Models\Group;
use App\Models\GroupMember;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Models\User;
use Illuminate\Support\Facades\Validator;

class GroupController extends Controller
{

    public function __construct() {}

    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
			'picture' => 'required|image',
			'name' => 'required'
        ]);
        
        if ($validator->fails()) {
			return ResponseFormatter::error($validator->errors()->all(), $request->all());
        }

        try {
            $willUploadFile = ($request->file('picture'));

            $fileName = time().$willUploadFile->getClientOriginalName();
            $destinationPath = 'upload/group';
            $willUploadFile->move(storage_path($destinationPath), $fileName);

            // Generating random string for group code
            $permitted_chars = '1234567890';
            $code = substr(str_shuffle($permitted_chars), 0, 7);

            $createGroup = Group::create([
                'name' => $request->name,
                'picture' => $fileName,
                'code' => $code,
                'created_by' => $request->user->id,
            ]);

            // Registering owner as member
            GroupMember::create([
                'group_id' => $createGroup->id,
                'user_id' => $request->user->id,
            ]);
            
            return ResponseFormatter::success(
                $createGroup,
                'Create group successful'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                null,
                $error,
                500
            );
        }
    }

    public function me(Request $request) {
        $user = User::where('id', $request->user->id);
        return ResponseFormatter::success(
            $user->get()->first()->groups,
            'Group detail fetched',
            404
        );
    }

    public function groupdetail($group_id) {
        $group = Group::where('id', $group_id);

        if (!$group) {
            return ResponseFormatter::error(
                null,
                'Group not found',
                404
            );
        }
        
        return ResponseFormatter::success(
            $group->with(['members'])->get()->first(),
            'Group detail fetched',
            404
        );
    }

    public function joinbycode(Request $request) {
        $validator = Validator::make($request->all(), [
			'code' => 'required'
        ]);

        if ($validator->fails()) {
			return ResponseFormatter::validatorFailed();
        }

        $groupAvailable = Group::where('code', $request->code)->get()->first();
        if (!$groupAvailable) {
            return ResponseFormatter::error(
                null,
                'Group not found',
                200
            );
        }

        $alreadyJoined = GroupMember::where([
            'user_id' => $request->user->id,
            'group_id' => $groupAvailable->id
        ])->get()->first();
        if ($alreadyJoined) {
            return ResponseFormatter::error(
                null,
                'Already joined',
                200
            );
        }

        try {
            GroupMember::create([
                'group_id' => $groupAvailable->id,
                'user_id' => $request->user->id,
            ]);

            return ResponseFormatter::success(
                null,
                'Join group successful'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                null,
                $error->getMessage()
            );
        }
    }
}
