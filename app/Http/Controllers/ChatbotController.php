<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ChatbotController extends Controller
{
   public function send(Request $request)
{
    $message = $request->message;

    $apiKey = env('GEMINI_API_KEY');

    $response = Http::post(
        "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$apiKey}",
        [
            "contents" => [
                [
                    "parts" => [
                        [
                            "text" => "Responde: ".$message
                        ]
                    ]
                ]
            ]
        ]
    );

    return response()->json(
        $response->json()
    );
}
}