<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{

    public function __construct()
    {
        
    }

    public function create(Request $request) {
        $validator = Validator::make($request->all(), [
			'name' => 'required',
			'description' => 'required',
			'type' => 'required',
			'date' => 'required',
			'fee' => 'required',
			'address' => 'required',
			'photo_url' => 'required',
        ]);
        
        if ($validator->fails()) {
			return ResponseFormatter::validatorFailed();
        }

        try {
            $createEvent = Event::create([
                'name' => $request->name,
                'description' => $request->description,
                'type' => $request->type,
                'date' => $request->date,
                'fee' => $request->fee,
                'address' => $request->address,
                'created_by' => $request->user->id,
                'photo_url' => $request->photo_url,
            ]);
            
            return ResponseFormatter::success(
                $createEvent,
                'Create event successful'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                null,
                $error,
                500
            );
        }
    }
}