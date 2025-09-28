<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class respuestaController extends Controller
{
    protected $message = '';
    protected $status = 200;
    protected $error = false;
    protected $data = null;
    protected $token = null;

    protected function respond(): JsonResponse
    {
        return response()->json([
            'Message' => $this->message,
            'Status'  => $this->status,
            'Error'   => $this->error,
            'Data'    => $this->data,
            'token'   => $this->token
        ], $this->status);
    }
}
