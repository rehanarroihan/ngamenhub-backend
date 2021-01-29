<?php

namespace App\Http\Controllers;

use App\Models\Event;
use Illuminate\Http\Request;
use App\Helpers\ResponseFormatter;
use App\Models\Candidate;
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

    public function detail($event_id) {
        $result = Event::where([
			'id' => $event_id
		])->with(['transaction'])->first();
        return ResponseFormatter::success(
            $result,
            'Get event detail completed'
        );
    }

    public function delete($event_id) {
        $event = Event::find($event_id);

        if (!$event) {
            return ResponseFormatter::error(
                null,
                'Event not found',
                500
            );
        }

        try {
            $event->delete();

            return ResponseFormatter::success(
                null,
                'Event deleted successfully'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(
                null,
                $error,
                500
            );
        }
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

    public function apply(Request $request) {
        $validator = Validator::make($request->all(), [
			'event_id' => 'required'
        ]);
        
        if ($validator->fails()) {
			return ResponseFormatter::validatorFailed();
        }

        // Checking event availability
        $event = Event::find($request->event_id);
        if (!$event) {
            return ResponseFormatter::error(null, 'Event not found', 500);
        }

        // Checking duplication
        $application = Candidate::where('user_id', $request->user->id)
                    ->where('event_id', $request->event_id)
                    ->first();

        if ($application) {
            return ResponseFormatter::error(null, 'User already applied before', 500);
        }
        
        try {
            $application = Candidate::create([
                'user_id' => $request->user->id,
                'event_id' => $request->event_id,
                'status' => '0'
            ]);

            return ResponseFormatter::success(
                $application,
                'User applied event successfully'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(null, $error, 500);
        }
    }

    public function accept(Request $request) {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required',
            'candidate_id' => 'required',
        ]);
        
        if ($validator->fails()) {
            return ResponseFormatter::validatorFailed();
        }

        try {
            // Updating candidate status 
            // awaiting-confirmation=0, rejected=1, accepted=2, awaiting-payment=3, purchased=4 
            Candidate::where('event_id', $request->event_id)
                    ->where('user_id', '!=', $request->candidate_id)
                    ->update(['status' => '1']);

            Candidate::where('event_id', $request->event_id)
                    ->where('user_id', $request->candidate_id)
                    ->update(['status' => '2']);

            return ResponseFormatter::success(
                null,
                'Candidate accepted'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(null, $error, 500);
        }
    }
}
