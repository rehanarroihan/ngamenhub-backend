<?php

namespace App\Helpers;

class ResponseFormatter
{
    protected static $response = [
        'success' => null,
        'message' => null,
        'data' => null,
    ];

    public static function success($data = null, $message = null)
    {
        self::$response['success'] = true;
        self::$response['message'] = $message;
        self::$response['data'] = $data;

        return response()->json(self::$response, 200);
    }
    
    public static function error($data = null, $message = null, $code = 400)
    {
        self::$response['success'] = false;
        self::$response['message'] = $message;
        self::$response['data'] = $data;

        return response()->json(self::$response, $code);
    }

    public static function validatorFailed()
    {
        self::$response['message'] = 'Invalid request parameter';
        self::$response['success'] = false;

        return response()->json(self::$response, 400);
    }
}