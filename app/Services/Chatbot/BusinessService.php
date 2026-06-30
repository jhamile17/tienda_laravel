<?php

namespace App\Services\Chatbot;

class BusinessService
{
    public function answer(string $action): array
    {
        return match ($action) {

            'hours' => [
                'message' =>
                    "🕒 Nuestro horario de atención es:\n\n" .
                    "Lunes a Domingo\n" .
                    "8:00 a.m. - 10:00 p.m.",
                'products' => []
            ],

            'location' => [
                'message' =>
                    "📍 Nos encontramos en Jr. 24 de septiembre 841 - Pichanaqui, Bajo Pichanaqui, Peru",
                'products' => []
            ],

            'payments' => [
                'message' =>
                    "💳 Aceptamos:\n\n" .
                    "✅ Efectivo\n" .
                    "✅ Yape\n" .
                    "✅ Plin\n" .
                    "✅ Tarjetas de crédito y débito",
                'products' => []
            ],

            'delivery' => [
                'message' =>
                    "🛵 Sí contamos con servicio de delivery dentro de Pichanaki.",
                'products' => []
            ],

            'whatsapp' => [
                'message' =>
                    "📱 Puedes escribirnos por WhatsApp al +51 955236237.",
                'products' => []
            ],

            default => [
                'message' =>
                    "Lo siento, no tengo información sobre esa consulta.",
                'products' => []
            ]
        };
    }
}