<?php

function ResponseUserFormatter($messages, $status, $code, $data)
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

function UserFormatter($data)
{
    $response = [
        'username' => $data['username'],
        'name' => $data['name'],
        'email' => $data['email']
    ];

    return $response;
}
