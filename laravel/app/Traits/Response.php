<?php

namespace App\Traits;

use Illuminate\Http\JsonResponse;

trait Response
{
    public function response(
        mixed $data = [],
        array $metadata = null,
        int $statusCode = 200,
        string $message = null,
    ): JsonResponse {
        $response = ['data' => $data];

        if (!is_null($message)) $response['message'] = $message;
        if (!is_null($metadata)) $response['metadata'] = $metadata;

        return response()->json($response, $statusCode);
    }
}
