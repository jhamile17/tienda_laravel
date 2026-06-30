<?php

namespace App\Services\Chatbot;

class IntentService
{
    public function detect(string $message): array
    {
        $message = mb_strtolower(trim($message));

        /*
        |--------------------------------------------------------------------------
        | Saludos
        |--------------------------------------------------------------------------
        */

        if ($this->contains($message, [
            'hola',
            'buenas',
            'buenos dias',
            'buenas tardes',
            'buenas noches'
        ])) {
            return [
                'module' => 'greeting'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Negocio
        |--------------------------------------------------------------------------
        */

        if ($this->contains($message, [
            'horario',
            'hora',
            'atienden',
            'abren',
            'cierran'
        ])) {
            return [
                'module' => 'business',
                'action' => 'hours'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Ubicación
        |--------------------------------------------------------------------------
        */

        if ($this->contains($message, [

            'ubicacion',
            'ubicación',
            'direccion',
            'dirección',
            'donde',
            'dónde',
            'ubicado',
            'ubicada',
            'encuentra',
            'local',
            'tienda',
            'sucursal'

        ])) {

            return [

                'module' => 'business',
                'action' => 'location'

            ];

        }

        if ($this->contains($message, [
            'delivery',
            'envio',
            'envío'
        ])) {
            return [
                'module' => 'business',
                'action' => 'delivery'
            ];
        }

        if ($this->contains($message, [

            'pago',
            'pagos',
            'método de pago',
            'metodo de pago',
            'métodos de pagos',
            'metodos de pagos',
            'forma de pago',
            'forma de pagos',
            'formas de pago',
            'formas de pagos',
            'cómo puedo pagar',
            'como puedo pagar',
            'aceptan yape',
            'aceptan plin',
            'aceptan tarjeta',
            'tarjeta',
            'tarjetas',
            'crédito',
            'credito',
            'débito',
            'debito',
            'yape',
            'plin',
            'efectivo'

        ])) {

            return [

                'module' => 'business',

                'action' => 'payments'

            ];

        }

        /*
        |--------------------------------------------------------------------------
        | Acciones especiales
        |--------------------------------------------------------------------------
        */

        if ($this->contains($message, [
            'más barato',
            'mas barato'
        ])) {
            return [
                'module' => 'product',
                'action' => 'cheapest'
            ];
        }

        if ($this->contains($message, [
            'más caro',
            'mas caro'
        ])) {
            return [
                'module' => 'product',
                'action' => 'expensive'
            ];
        }

        if ($this->contains($message, [
            'stock',
            'disponibles',
            'hay disponibles'
        ])) {
            return [
                'module' => 'product',
                'action' => 'available'
            ];
        }

        /*
        |--------------------------------------------------------------------------
        | Construcción de filtros
        |--------------------------------------------------------------------------
        */

        $filters = [];

        // Categorías

        if ($this->contains($message, [
            'bebida',
            'bebidas',
            'frappé',
            'frappe',
            'jugo',
            'refresco',
            'latte',
            'ice latte'
        ])) {
            $filters['category'] = 'Bebidas';
        }

        if ($this->contains($message, [
            'café',
            'cafe',
            'cafés',
            'cafes',
            'americano',
            'espresso'
        ])) {
            $filters['category'] = 'Café';
        }

        if ($this->contains($message, [
            'snack',
            'snacks',
            'yuca',
            'pan'
        ])) {
            $filters['category'] = 'Snacks';
        }

        if ($this->contains($message, [
            'postre',
            'postres'
        ])) {
            $filters['category'] = 'Postres';
        }

        // Temperatura

        if ($this->contains($message, [
            'fría',
            'fria',
            'frías',
            'frias',
            'frío',
            'frio',
            'fríos',
            'frios',
            'helado',
            'helada',
            'helados',
            'heladas',
            'bebida fría',
            'bebidas frías',
            'bebida fria',
            'bebidas frias'
        ])) {
            $filters['temperature'] = 'Fría';
        }

        if ($this->contains($message, [
            'caliente',
            'calientes',
            'bebida caliente',
            'bebidas calientes'
        ])) {
            $filters['temperature'] = 'Caliente';
        }

        /*
        |--------------------------------------------------------------------------
        | Precio dinámico
        |--------------------------------------------------------------------------
        */

        if (preg_match(
            '/(?:menos\s+(?:de|que)|menor\s+que|hasta|máximo|maximo)\s*(?:s\/\.?\s*)?(\d+)/iu',
            $message,
            $matches
        )) {

            $filters['price_max'] = (float) $matches[1];

        }

        elseif (preg_match(
            '/(?:s\/\.?\s*)?(\d+)\s*soles?/iu',
            $message,
            $matches
        )) {

            $filters['price_max'] = (float) $matches[1];

        }

        elseif ($this->contains($message, [

            'barato',
            'económico',
            'economico'

        ])) {

            $filters['price_max'] = 10;

        }

        // Ingredientes

        foreach ([
            'oreo',
            'fresa',
            'queso',
            'pollo',
            'chorizo',
            'huevo',
            'camu camu',
            'chocolate'
        ] as $keyword) {

            if (str_contains($message, $keyword)) {

                $filters['keyword'] = $keyword;

                break;

            }

        }

        /*
        |--------------------------------------------------------------------------
        | Palabras clave de productos
        |--------------------------------------------------------------------------
        */

        $productKeywords = [

            'hamburguesa',
            'pollo',
            'queso',
            'huevo',
            'chorizo',
            'yuca',
            'pan',
            'latte',
            'americano',
            'frappé',
            'frappe',
            'oreo',
            'fresa',
            'camu camu',
            'jugo',
            'mixto'

        ];

        foreach ($productKeywords as $keyword) {

            if (str_contains($message, $keyword)) {

                $filters['keyword'] = $keyword;

                break;

            }

        }

        /*
        |--------------------------------------------------------------------------
        | Preferencias del usuario
        |--------------------------------------------------------------------------
        */

        if ($this->contains($message, [

            'dulce',
            'postre',
            'postres'

        ])) {

            return [

                'module' => 'recommendation',

                'filters' => array_merge(

                    $filters,

                    [

                        'preference' => 'sweet'

                    ]

                )

            ];

        }

        if ($this->contains($message, [

            'desayuno',
            'desayunar'

        ])) {

            return [

                'module' => 'recommendation',

                'filters' => array_merge(

                    $filters,

                    [

                        'preference'=>'breakfast'

                    ]

                )

            ];

        }

        if ($this->contains($message, [

            'hambre',
            'comer'

        ])) {

            return [

                'module' => 'recommendation',

                'filters' => array_merge(

                    $filters,
    
                    [

                        'preference' => 'hungry'
                    ]
                )

            ];

        }

        if ($this->contains($message, [

            'calor',
            'caluroso',
            'refrescante'

        ])) {

            return [

                'module' => 'recommendation',

                'filters' => array_merge(

                    $filters,

                    [

                    'preference' => 'cold'

                    ]

                )

            ];

        }

        if ($this->contains($message, [

            'salado'

        ])) {

            return [

                'module' => 'recommendation',

                'filters' => array_merge(

                    $filters,

                    [

                    'preference' => 'salty'

                    ]

                )

            ];

        }

        if ($this->contains($message, [

            'recomienda',
            'recomiéndame',
            'recomiendame',
            'recomendar',
            'recomendarias',
            'recomendarías',
            'qué me recomiendas',
            'que me recomiendas'

        ])) {

            /*
            |--------------------------------------------------------------------------
            | Si el usuario no especificó nada,
            | recomendar productos variados.
            |--------------------------------------------------------------------------
            */

            if (empty($filters)) {

                return [

                    'module' => 'recommendation',

                    'filters' => []

                ];

            }

            /*
            |--------------------------------------------------------------------------
            | Si especificó categoría, temperatura o precio,
            | conservar los filtros.
            |--------------------------------------------------------------------------
            */

            return [

                'module' => 'recommendation',

                'filters' => $filters

            ];

        }

        if (!empty($filters)) {

            return [

                'module' => 'product',

                'filters' => $filters

            ];

        }

        /*
        |--------------------------------------------------------------------------
        | Carrito
        |--------------------------------------------------------------------------
        */

        if ($this->contains($message, [
            'agregar',
            'agrégalo',
            'agregalo',
            'añadir',
            'añádelo',
            'comprar',
            'lo quiero'
        ])) {

            return [
                'module' => 'cart'
            ];

        }

        if ($this->contains($message, [

            'más vendido',

            'mas vendido',

            'más vendidos',

            'mas vendidos',

            'top productos',

            'top de productos',

            'más populares',

            'mas populares',

            'producto favorito',

            'favoritos'

        ])) {

            return [

                'module' => 'product',

                'action' => 'best_sellers'

            ];

        }

        /*
        |--------------------------------------------------------------------------
        | IA
        |--------------------------------------------------------------------------
        */

        return [

            'module' => 'ai'

        ];
    }

    private function contains(string $text, array $words): bool
    {
        foreach ($words as $word) {

            if (str_contains($text, $word)) {

                return true;

            }

        }

        return false;
    }
}