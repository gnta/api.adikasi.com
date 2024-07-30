<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Support\Facades\Context;
use Illuminate\Support\Facades\Log;

class ErrorResponse extends Exception
{
    protected $type;
    protected $data;

    public function __construct(
        $message = 'Internal Server Error',
        $code = 500,
        $type = "server",
        $data = null,
        Exception $previous = null
    ) {
        parent::__construct($message, $code, $previous);
        $this->type = $type;
        $this->data = $data;
    }

    public function report()
    {
        Log::error($this->getMessage());
        if (!empty($this->data)) Log::error($this->data);
    }

    public function render($request)
    {
        $error = [
            'type' => $this->type,
            'message' => $this->getMessage(),
            'trace_id' => Context::get("trace_id"),
        ];

        if (!empty($this->data)) $error['data'] = $this->data;
        return response()->json(['error' => $error], $this->getCode());
    }
}
