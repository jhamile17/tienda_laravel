<?php

namespace App\Services\Chatbot;

use Illuminate\Support\Collection;

class PromptService
{
    public function build(Collection $products, string $question): array
    {
        $context = "";

        foreach ($products as $product) {

            $context .=
                "Producto: {$product->name}\n".
                "Categoría: ".($product->category->name ?? 'Sin categoría')."\n".
                "Precio: S/ {$product->price}\n".
                "Temperatura: ".($product->temperature ?? 'No especificada')."\n".
                "Stock: {$product->stock}\n".
                "Descripción: {$product->description}\n\n";
        }

        return [

            [
                "role" => "system",
                "content" => "
Eres el asistente virtual de PROCAFES.

Reglas:

- Responde SIEMPRE en español.
- Usa únicamente la información proporcionada.
- Nunca inventes productos.
- Nunca inventes precios.
- Si un producto no existe, dilo amablemente.
- Si te saludan, responde cordialmente.
- Si preguntan algo fuera de PROCAFES, indica que solo puedes ayudar con información del negocio.

Información de la base de datos:

{$context}
"
            ],

            [
                "role"=>"user",
                "content"=>$question
            ]

        ];
    }
}