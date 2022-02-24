<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Ramsey\Uuid\Uuid;

class TransferService
{
    private $requierdData = [];


    public function __construct(array $data)
    {
        $this->requierdData = $data;
    }

    public function transfer()
    {
        $uuid = Uuid::uuid4()->toString();
        $url  = config('service.api_transfer')['value'] . $uuid;

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->withToken('Bearer token')
            ->post($url , $this->requierdData);

        return $response;
    }
}
