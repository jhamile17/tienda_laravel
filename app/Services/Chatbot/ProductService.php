<?php

namespace App\Services\Chatbot;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;

class ProductService
{
        private array $preferences = [

        'dulce' => [
            'oreo',
            'fresa',
            'chocolate',
            'postre'
        ],

        'salado' => [
            'queso',
            'pollo',
            'chorizo',
            'huevo',
            'hamburguesa',
            'yuca'
        ],

        'desayuno' => [
            'pan',
            'huevo',
            'chorizo',
            'americano',
            'latte'
        ],

        'calor' => [
            'Fría'
        ],

        'frio' => [
            'Caliente'
        ]

    ];

    /**
     * Buscar productos.
     */
    public function search(array $filters): array
    {
        $query = Product::query()
            ->with('category')
            ->where('status', 1);

        $this->applyFilters($query, $filters);

        $products = $query->get();

        return [

            'message' => $this->buildMessage($filters, $products),

            'products' => $this->formatProducts($products)

        ];
    }

    /**
     * Productos recomendados.
     */
    public function recommend(array $filters = []): array
    {
        $query = Product::query()
            ->with('category')
            ->where('status', 1)
            ->where('stock', '>', 0);

        /*
        |--------------------------------------------------------------------------
        | Preferencias
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['preference'])) {

            switch ($filters['preference']) {

                /*
                |--------------------------------------------------------------
                | Algo dulce
                |--------------------------------------------------------------
                */

                case 'sweet':

                    $query->where(function ($q) {

                        $q->where('name','like','%oreo%')

                        ->orWhere('name','like','%fresa%')

                        ->orWhere('name','like','%latte%')

                        ->orWhere('description','like','%dulce%');

                    });

                break;

                /*
                |--------------------------------------------------------------
                | Desayuno
                |--------------------------------------------------------------
                */

                case 'breakfast':

                    $query->where(function ($q) {

                        $q->where('name', 'like', '%americano%')
                        ->orWhere('name', 'like', '%pan%')
                        ->orWhere('name', 'like', '%mixto%')
                        ->orWhere('name', 'like', '%tostada%');

                    });

                    break;

                /*
                |--------------------------------------------------------------
                | Hace calor
                |--------------------------------------------------------------
                */

                case 'cold':

                    $query->where('temperature', 'Fría');

                    break;

                /*
                |--------------------------------------------------------------
                | Tengo hambre
                |--------------------------------------------------------------
                */

                case 'hungry':

                    $query->whereHas('category', function ($q) {

                        $q->whereIn('name', [

                            'Snacks',
                            'Gourmet'

                        ]);

                    });

                    break;

                /*
                |--------------------------------------------------------------
                | Algo salado
                |--------------------------------------------------------------
                */

                case 'salty':

                    $query->where(function ($q) {

                        $q->where('name', 'like', '%pollo%')
                        ->orWhere('name', 'like', '%queso%')
                        ->orWhere('name', 'like', '%huevo%')
                        ->orWhere('name', 'like', '%chorizo%')
                        ->orWhere('name', 'like', '%hamburguesa%');

                    });

                    break;

            }

        }

        /*
        |--------------------------------------------------------------------------
        | Sin preferencia
        |--------------------------------------------------------------------------
        */

        elseif (

            empty($filters['category']) &&

            empty($filters['temperature']) &&

            empty($filters['price_max'])

        ) {

            $query->inRandomOrder();

        }

                /*
        |--------------------------------------------------------------------------
        | Categoría
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['category'])) {

            $query->whereHas('category', function ($q) use ($filters) {

                $q->where('name', $filters['category']);

            });

        }

        /*
        |--------------------------------------------------------------------------
        | Temperatura
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['temperature'])) {

            $query->where(

                'temperature',

                $filters['temperature']

            );

        }

        /*
        |--------------------------------------------------------------------------
        | Precio máximo
        |--------------------------------------------------------------------------
        */

        if (!empty($filters['price_max'])) {

            $query->where(

                'price',

                '<=',

                $filters['price_max']

            );

        }

        $products = $query
            ->limit(3)
            ->get();


        return [

            'message' => $this->recommendationMessage($filters),

            'products' => $this->formatProducts($products)

        ];
    }

    /**
     * Producto más barato.
     */
    public function cheapest(): array
    {
        $product = Product::with('category')
            ->where('status', 1)
            ->orderBy('price')
            ->first();

        if (!$product) {
            return [
                'message' => 'No encontré productos.',
                'products' => []
            ];
        }

        return [
            'message' => "El producto más económico es {$product->name}.",
            'products' => $this->formatProducts(collect([$product]))
        ];
    }

    /**
     * Producto más caro.
     */
    public function expensive(): array
    {
        $product = Product::with('category')
            ->where('status', 1)
            ->orderByDesc('price')
            ->first();

        if (!$product) {
            return [
                'message' => 'No encontré productos.',
                'products' => []
            ];
        }

        return [
            'message' => "El producto de mayor precio es {$product->name}.",
            'products' => $this->formatProducts(collect([$product]))
        ];
    }

    /**
     * Productos disponibles.
     */
    public function available(): array
    {
        $products = Product::with('category')
            ->where('status',1)
            ->where('stock','>',0)
            ->get();

        return [
            'message' => 'Estos son los productos disponibles.',
            'products' => $this->formatProducts($products)
        ];
    }

    /**
     * Aplicar filtros.
     */
    private function applyFilters(
        Builder $query,
        array $filters
    ): void {

        if(isset($filters['category'])){

            $query->whereHas('category',function($q)use($filters){

                $q->where(
                    'name',
                    $filters['category']
                );

            });

        }

        if(isset($filters['temperature'])){

            $query->where(
                'temperature',
                $filters['temperature']
            );

        }

        if(isset($filters['keyword'])){

            $keyword=$filters['keyword'];

            $query->where(function($q)use($keyword){

                $q->where(
                    'name',
                    'like',
                    "%{$keyword}%"
                )

                ->orWhere(
                    'description',
                    'like',
                    "%{$keyword}%"
                );

            });

        }

        if(isset($filters['price_max'])){

            $query->where(
                'price',
                '<=',
                $filters['price_max']
            );

        }

        if(isset($filters['available'])){

            $query->where(
                'stock',
                '>',
                0
            );

        }

        if (!empty($filters['recommend'])) {

            $query->inRandomOrder()
                ->limit(3);

        }

    }

    /**
     * Mensaje.
     */
    private function buildMessage(array $filters, $products): string
    {
        if ($products->isEmpty()) {

            return "Lo siento, no encontré productos con esas características.";

        }

        if (!empty($filters['recommend'])) {

            return "⭐ Te recomiendo estos productos.";

        }

        if (
            isset($filters['category']) &&
            isset($filters['temperature'])
        ) {

            return "Estas son nuestras {$filters['category']} {$filters['temperature']} disponibles.";

        }

        if (isset($filters['category'])) {

            return "Estos son nuestros {$filters['category']}.";

        }

        return "Encontré {$products->count()} productos.";
    }

    private function recommendationMessage(array $filters): string
    {
        if (!empty($filters['preference'])) {

            return match ($filters['preference']) {

                'sweet' =>
                    "🍰 Si buscas algo dulce, estas son mis mejores recomendaciones.",

                'breakfast' =>
                    "🍳 Estas opciones son ideales para un buen desayuno.",

                'cold' =>
                    "🥤 Hace calor... estas bebidas frías son una excelente opción.",

                'hungry' =>
                    "🍔 Si tienes hambre, te recomiendo estos productos.",

                'salty' =>
                    "🧂 Si prefieres algo salado, estas opciones te pueden gustar.",

                default =>
                    "⭐ Estas son mis recomendaciones."

            };

        }

        return "⭐ Estas son mis recomendaciones.";
    }

    /**
     * Formatear.
     */
    private function formatProducts($products): array
    {

        return $products->map(function($product){

            return [

                'id' => $product->id,

                'name' => $product->name,

                'description' => $product->description,

                'price' => 'S/ '.number_format($product->price,2),

                'category' => $product->category->name ?? '',

                'temperature' => $product->temperature,

                'stock' => $product->stock,

                'available' => $product->stock > 0,

                'image' => $product->image
                    ? asset('storage/'.$product->image)
                    : null

            ];

        })->values()->toArray();

    }

        private function footer(): string
    {
        return "\n\n💬 ¿Hay algo más en lo que pueda ayudarte?";
    }

    /**
     * Top 5 productos más vendidos.
     */
    public function bestSellers(): array
    {
        $products = Product::with('category')
            ->withSum('orderItems as total_sales', 'quantity')
            ->where('status', 1)
            ->orderByDesc('total_sales')
            ->limit(5)
            ->get();

        if ($products->isEmpty()) {

            return [

                'message' => 'Aún no contamos con ventas registradas.',

                'products' => []

            ];

        }

        return [

            'message' => '🏆 Estos son nuestros 5 productos más vendidos.',

            'products' => $this->formatProducts($products)

        ];
    }

}