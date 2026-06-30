<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\Chatbot\ChatbotService;

class ChatbotController extends Controller
{
    public function chat(
        Request $request,
        ChatbotService $chatbot
    ) {
        $request->validate([
            'mensaje' => ['required', 'string', 'max:500']
        ]);

        return response()->json(
            $chatbot->reply(
                $request->input('mensaje')
            )
        );
    }
}