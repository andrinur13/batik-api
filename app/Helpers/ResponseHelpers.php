<?php

namespace App\Helpers;

class ResponseHelpers
{
    public function ResponseUserFormatter($messages, $status, $code, $data)
    {
        $response = [
            'meta' => [
                'messages' => $messages,
                'status' => $status,
                'code' => $code,
            ],
            'data' => $data
        ];

        return $response;
    }

    public function UserFormatter($data)
    {
        $response = [
            'username' => $data['username'],
            'name' => $data['name'],
            'email' => $data['email']
        ];

        return $response;
    }
}
