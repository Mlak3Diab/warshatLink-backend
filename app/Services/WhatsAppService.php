<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    protected string $apiUrl;
    protected string $apiPassword;

    public function __construct()
    {
        $this->apiUrl = env('WHATSAPP_API_URL', 'http://127.0.0.1:3000/whatsapp/sendmessage');
        $this->apiPassword = env('WHATSAPP_API_PASSWORD');
    }
    public function sendMessage(string $phone, string $message): bool
    {
        $response = Http::withHeaders([
            'x-password' => env('WHATSAPP_API_PASSWORD'),
        ])->post(env('WHATSAPP_API_URL', 'http://127.0.0.1:3000/whatsapp/sendmessage'), [
            'phone' => $phone,
            'message' => $message,
        ]);

        return $response->successful();
    }

}
