<?php

namespace App\Services\Chatbot;

class ResponseService
{
    public function products(string $title): string
    {
        return
            "😊 {$title}\n\n".
            "Puedes revisar los productos que aparecen a continuación.\n\n".
            "💬 ¿Hay algo más en lo que pueda ayudarte?";
    }

    public function recommendation(): string
    {
        return
            "⭐ Estas son algunas recomendaciones especialmente para ti.\n\n".
            "Espero que encuentres una opción que te guste.\n\n".
            "💬 Si deseas otra recomendación, estaré encantado de ayudarte.";
    }

    public function empty(): string
    {
        return
            "😔 No encontré productos que coincidan con tu búsqueda.\n\n".
            "Puedes intentar buscar por categoría, precio o ingrediente.\n\n".
            "💬 Estoy aquí para ayudarte.";
    }

    public function location(): string
    {
        return
            "📍 ¡Con mucho gusto!\n\n".
            "Nuestro local está ubicado en:\n\n".
            "📌 Pichanaki, Junín - Perú.\n\n".
            "☕ ¡Será un placer recibirte!\n\n".
            "💬 ¿Deseas conocer nuestro horario o nuestros productos?";
    }

    public function hours(): string
    {
        return
            "🕒 Nuestro horario de atención es:\n\n".
            "📅 Lunes a Domingo\n".
            "⏰ 8:00 a.m. - 10:00 p.m.\n\n".
            "☕ ¡Te esperamos en PROCAFES!\n\n".
            "💬 ¿Hay algo más en lo que pueda ayudarte?";
    }

    public function payments(): string
    {
        return
            "💳 Aceptamos:\n\n".
            "✅ Efectivo\n".
            "✅ Yape\n".
            "✅ Plin\n".
            "✅ Tarjetas credito o debito.\n\n".
            "💬 ¿Deseas realizar alguna consulta sobre nuestros productos?";
    }

    public function delivery(): string
    {
        return
            "🛵 Sí contamos con servicio de delivery.\n\n".
            "Consulta la cobertura según tu ubicación.\n\n".
            "💬 ¿Te gustaría ver nuestro menú?";
    }

    public function fallback(): string
    {
        return
            "😊 Disculpa, no entendí muy bien tu consulta.\n\n".
            "Puedo ayudarte con:\n\n".
            "☕ Cafés\n".
            "🥤 Bebidas\n".
            "🍔 Snacks\n".
            "🍰 Postres\n".
            "📍 Ubicación\n".
            "🕒 Horario\n".
            "🛵 Delivery\n".
            "💳 Métodos de pago\n\n".
            "¿Qué te gustaría conocer?";
    }
}