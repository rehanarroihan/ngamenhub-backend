<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Group;
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

    public function getJobs(Request $request) {
        $userAppliedEvent = Candidate::where('user_id', $request->user->id)->get();

        $result = array();
        foreach ($userAppliedEvent as $cnd) {
            $eventDetail = Event::where('id', $cnd->event_id)->get()->first();

            $job = array(
                'event_id'  => $eventDetail->id,
                'status'    => $cnd->status,
                'type'      => $eventDetail->type,
                'date'      => $eventDetail->date,
                'name'      => $eventDetail->name
            );

            array_push($result, $job);
        }
        
        return ResponseFormatter::success(
            $result,
            'Job list fetch completed'
        );
    }

    public function detail($event_id) {
        $result = Event::where([
			'id' => $event_id
        ])->with(['transaction'])->first();
        
        if (count($result->candidates) > 0) {
            for ($i = 0; $i<count($result->candidates); $i++) {
                if ($result->candidates[$i]->group_id != null) {
                    $result->candidates[$i]->group = Group::where([
                        'id' => $result->candidates[$i]->group_id
                    ])->without(['members'])->first();
                } else {
                    $result->candidates[$i]->group = null;
                }
            }
        }

        return ResponseFormatter::success(
            $result,
            'Get event detail completed'
        );
    }

    public function delete(Request $request, $event_id) {
        $event = Event::find($event_id);

        if (!$event) {
            return ResponseFormatter::error(
                null,
                'Event not found',
                500
            );
        }

        if ($event->created_by != $request->user->id) {
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

        // Checking duplication (user)
        $application = Candidate::where('user_id', $request->user->id)
                    ->where('event_id', $request->event_id)
                    ->first();

        if ($application) {
            return ResponseFormatter::error(null, 'User already applied before', 500);
        }

        // Checking duplication (group)
        if ($request->group_id) {
            $applicationGroup = Candidate::where('group_id', $request->group_id)
                    ->where('event_id', $request->event_id)
                    ->first();

            if ($applicationGroup) {
                return ResponseFormatter::error(null, 'Group already applied before', 500);
            }
        }
        
        try {
            $application = Candidate::create([
                'user_id' => $request->user->id,
                'group_id' => $request->group_id,
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
            // awaiting-confirmation=0, rejected=1, accepted=2, awaiting-payment=3, purchased=4,finished=5
            Candidate::where('event_id', $request->event_id)
                    ->where('id', '!=', $request->candidate_id)
                    ->update(['status' => '1']);

            Candidate::where('event_id', $request->event_id)
                    ->where('id', $request->candidate_id)
                    ->update(['status' => '2']);

            return ResponseFormatter::success(
                null,
                'Candidate accepted'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(null, $error, 500);
        }
    }

    public function finish(Request $request) {
        $validator = Validator::make($request->all(), [
            'event_id' => 'required',
        ]);
        
        if ($validator->fails()) {
            return ResponseFormatter::validatorFailed();
        }

        try {
            $event = Event::findOne('id', $request->event_id);

            if (!$event) {
                return ResponseFormatter::error(
                    null,
                    'Event not found',
                    500
                );
            }

            if ($event->created_by != $request->user->id) {
                return ResponseFormatter::error(
                    null,
                    'Event not found',
                    500
                );
            }

            $candidate = Candidate::where('event_id', $request->event_id)
                            ->where('status', '4')->first();

            if (!$candidate) {
                return ResponseFormatter::error(
                    null,
                    'Action cannot be undone',
                    500
                );
            }

            if ($candidate->status == '5') {
                return ResponseFormatter::error(
                    null,
                    'Event and candidate has finished already',
                    500
                );
            }

            $candidate->status = 5;

            $candidate->save();

            return ResponseFormatter::success(
                null,
                'Finish event action successful'
            );
        } catch (Exception $error) {
            return ResponseFormatter::error(null, $error, 500);
        }
    }
}
