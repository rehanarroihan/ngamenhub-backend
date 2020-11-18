<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use Exception;
use Illuminate\Support\Facades\Validator;

class EventController extends Controller
{

    public function __construct()
    {
        
    }

    public function get(Request $request) {
        $user_id = $request->input('user_id');

        $event = Event::query();
        if ($user_id) {
            $event->where('created_by', $user_id);
        }

        return ResponseFormatter::success(
            $event->get(),
            'Event list fetch completed'
        );
    }

    public function create(Request $request) {
        // return $request->all();
        $validator = Validator::make($request->all(), [
			'name' => 'required',
			'description' => 'required',
			'type' => 'required',
			'date' => 'required|date_format:Y-m-d H:i:s',
			'fee' => 'required',
			'address' => 'required',
			'photo_urls' => 'required',
        ]);
        
        if ($validator->fails()) {
			return ResponseFormatter::error($validator->errors()->all(), $request->all());
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
                'photo_urls' => $request->photo_urls,
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

    public function upload(Request $request) {
        if (!is_array($request->file('image'))) {
            return ResponseFormatter::validatorFailed();
        }

        try {
            $file_count = count($request->file('image'));
            $a = ($request->file('image'));
            $finalArray = array();
            for ($i=0; $i < $file_count; $i++) {
                $fileName = time().$a[$i]->getClientOriginalName();
                $destinationPath = 'upload/event';
                $finalArray[$i] = $fileName;
                $a[$i]->move(storage_path($destinationPath), $fileName);
            }

            return ResponseFormatter::success(
                $finalArray,
                'File upload successful'
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
