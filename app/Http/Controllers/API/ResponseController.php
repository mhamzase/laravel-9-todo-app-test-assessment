<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ResponseController extends Controller
{
    /**
     * success response method.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendResponse($result, $message = '', $code = 200)
    {
        $response = [
            'status' => 'success',
            'data'    => $result,
            'message' => $message,
        ];
        return response()->json($response, $code);
    }

    /**
     * return error response.
     *
     * @return \Illuminate\Http\Response
     */
    public function sendError($error, $code = 404)
    {
        $response = [
            'status' => 'error',
            'message' => $error,
        ];

        return response()->json($response, $code);
    }
}
