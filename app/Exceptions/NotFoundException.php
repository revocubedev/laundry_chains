<?php

namespace App\Exceptions;

use Exception;

class NotFoundException extends Exception
{
    public function __construct($message = 'Not Found', $code = 404)
    {
        parent::__construct($message, $code);
    }

    public function render($request)
    {
        return response()->json([
            'status' => 'error',
            'message' => $this->getMessage(),
        ], $this->getCode());
    }

    public function report()
    {
        // 
    }
}
