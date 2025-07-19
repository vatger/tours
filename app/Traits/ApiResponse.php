<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait ApiResponse
{
    protected function sendSuccess($message = '', $code = JsonResponse::HTTP_OK)
    {
        if (!$message) {
            $message = __('The action was executed successfully.');
        }
        return response()->json([
            'status' => $code,
            //            'error' => false,
            'message' => $message,
            'data' => [],
        ], $code);
    }

    protected function sendResponse($data, string $message = null, int $code = JsonResponse::HTTP_OK)
    {
        if (!$message) {
            $message = __('The action was executed successfully.');
        }
        return response()->json([
            'status' => $code,
            //            'error' => false,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function sendNoContent($message = null, $code = JsonResponse::HTTP_NO_CONTENT)
    {
        if (!$message) {
            $message = __('Not Found');
        }
        return response()->json([
            'status' => $code,
            //            'error' => false,
            'message' => $message,
            'data' => [],
        ], $code);
    }

    protected function sendNoPermission($message = null, $code = JsonResponse::HTTP_FORBIDDEN)
    {
        if (!$message) {
            $message = __('Sorry! You are not authorized to perform this action.');
        }
        return response()->json([
            'status' => $code,
            'error' => true,
            'message' => $message,
            'data' => [],
        ], $code);
    }

    protected function sendError($message = null, string $code = JsonResponse::HTTP_NOT_FOUND, $data = [])
    {
        if (!$message) {
            $message = __('There was a problem executing the action.');
        }
        return response()->json([
            'status' => $code,
            'error' => true,
            'message' => $message,
            'data' => $data,
        ], $code);
    }

    protected function sendErrorUnprocessable($data = [], $message = null, $code = JsonResponse::HTTP_UNPROCESSABLE_ENTITY)
    {
        if (!$message) {
            $message = __('There was a problem submitting the form.');
        }
        return response()->json([
            'status' => $code,
            'error' => true,
            'message' => $message,
            'errors' => $data,
        ], $code);
    }
}
