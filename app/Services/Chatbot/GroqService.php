<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Http;

class GroqService
{
    private string $apiKey;
    private string $model;

    public function __construct()
    {
        $this->apiKey = config('services.groq.api_key');
        $this->model = config('services.groq.model');
    }

    public function chat(array $messages): string
    {
        $response = Http::withToken($this->apiKey)
            ->acceptJson()
            ->post('https://api.groq.com/openai/v1/chat/completions', [
                'model' => $this->model,
                'messages' => $messages,
                'temperature' => 0.4,
            ]);

        if ($response->failed()) {
            throw new \Exception($response->body());
        }

        return $response->json()['choices'][0]['message']['content'];
    }
}