<?php

namespace App\Traits;

trait ResponseWithHttpStatus
{
    public function getSuccessResponse($data = [])
    {
        $data = [
            'code' => 200,
            'message' => 'Successfully Get Data',
            'data' => $data,
        ];
        return response()->json($data, 200);
    }

    public function postSuccessResponse($message = '', $data = [])
    {
        $data = [
            'code' => 200,
            'message' => $message ?? 'Successfully Post Data',
            'data' => $data,
        ];
        return response()->json($data, 200);
    }

    public function failedResponse($message = 'Failed Process Data', $data = null, $code = 422)
    {
        if ($data != null) {
            $data = [
                'code' => $code,
                'message' => $message,
                'data' => $data,
            ];
        } else {
            $data = [
                'code' => $code,
                'message' => $message,
            ];
        }
        return response()->json($data, $code);
    }

    public function notFoundResponse($data = [])
    {
        $data = [
            'code' => 404,
            'message' => 'Data Not Found',
            'data' => $data,
        ];
        return response()->json($data, 404);
    }

    protected function errorValidationResponse($validator)
    {
        return response()->json([
            'code'  => 422,
            'success' => false,
            'data' => null,
            'error' => $validator->errors()->first()
        ], 422);
    }
}
