<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Facades\Session;

class ChatbotService
{
    public function __construct(
        protected IntentService $intent,
        protected ProductService $products,
        protected BusinessService $business,
        protected PromptService $prompt,
        protected GroqService $groq,
        protected ResponseService $response
    ) {
    }

    /**
     * Respuesta principal del chatbot.
     */
    public function reply(string $message): array
    {
        // Detectar la intención
        $intent = $this->intent->detect($message);

        // Decidir qué hacer
        switch ($intent['module']) {

            case 'greeting':
                return [
                    'message' => '¡Hola! 😊 ¿En qué puedo ayudarte hoy?',
                    'products' => []
                ];

            case 'business':
                return $this->business->answer(
                    $intent['action']
                );

            case 'product':

            if (isset($intent['action'])) {

                return match ($intent['action']) {

                    'cheapest' => $this->products->cheapest(),

                    'expensive' => $this->products->expensive(),

                    'available' => $this->products->available(),

                    'best_sellers' => $this->products->bestSellers(),

                    default => [

                        'message' => 'No entendí la consulta.',

                        'products' => []

                    ]

                };

            }

            $response = $this->products->search(
                $intent['filters']
            );

            Session::put('chatbot.last_filters', $intent['filters']);

            Session::put('chatbot.last_products', $response['products']);

            if (!empty($response['products'])) {

                Session::put(

                    'chatbot.selected_product',

                    $response['products'][0]

                );

            }

            return $response;

            case 'cart':

            $product = Session::get(
                'chatbot.selected_product'
            );

            if(!$product){

                return [

                    'message'=>

                    'Primero selecciona un producto.',

                    'products'=>[]

                ];

            }

            return [

                'message'=>

                "Puedes agregar {$product['name']} usando el botón 🛒 que aparece debajo del producto.",

                'products'=>[
                    $product
                ]

            ];

            case 'recommendation':

            return $this->products->recommend(

                $intent['filters'] ?? []

            );

            case 'ai':
            default:

                $messages = $this->prompt->build(
                    collect(),
                    $message
                );

                return [
                    'message' => $this->groq->chat($messages),
                    'products' => []
                ];
            case 'conversation':

            return [

                'message'=>'Estoy recordando tu búsqueda anterior. Esta función estará disponible en el siguiente paso.',

                'products'=>$this->lastProducts()

            ];

            
        }
    }

    private function lastProducts(): array
    {
        return Session::get(
            'chatbot.last_products',
            []
        );
    }

    private function lastFilters(): array
    {
        return Session::get(
            'chatbot.last_filters',
            []
        );
    }
}