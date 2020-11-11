<?php

namespace App\Helpers;

class ResponseFormatter
{
    protected static $response = [
        'status' => null,
        'message' => null,
        'data' => null,
    ];

    public static function success($data = null, $message = null)
    {
        self::$response['status'] = true;
        self::$response['message'] = $message;
        self::$response['data'] = $data;

        return response()->json(self::$response, 200);
    }
    
    public static function error($data = null, $message = null, $code = 400)
    {
        self::$response['status'] = false;
        self::$response['message'] = $message;
        self::$response['data'] = $data;

        return response()->json(self::$response, $code);
    }

    public static function validatorFailed()
    {
        self::$response['status'] = false;
        self::$response['message'] = 'Invalid request parameter';
        self::$response['data'] = null;

        return response()->json(self::$response, 400);
    }
}