<?php

namespace App\Traits;

trait APIResponses
{
    public function success($message = 'Success', $data = null, $status_code = 200)
    {
        return response()->json([
            'status' => 'success',
            'message' => $message,
            'data' => $data
        ], $status_code);
    }

    public function error($message = 'Something went wrong', $data = null, $status_code = 400)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data
        ], $status_code);
    }

    public function validationError($message = 'Validation failed', $data = null, $status_code = 422)
    {
        return response()->json([
            'status' => 'error',
            'message' => $message,
            'data' => $data
        ], $status_code);
    }

    public function unauthorized($message = 'Unauthenticated', $data = null, $status_code = 401)
    {
        return $this->error($message, $data, $status_code);
    }

    public function forbidden($message = 'Forbidden', $data = null, $status_code = 403)
    {
        return $this->error($message, $data, $status_code);
    }

    public function notFound($message = 'Resource not found', $data = null, $status_code = 404)
    {
        return $this->error($message, $data, $status_code);
    }

    public function serverError($message = 'Internal server error', $data = null, $status_code = 500)
    {
        return $this->error($message, $data, $status_code);
    }
}