<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\OrderItem;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ChatbotController extends Controller
{
    public function index()
    {
        return view('chatbot');
    }

    public function send(Request $request): JsonResponse
    {
        $data = $request->validate([
            'message' => ['required', 'string', 'min:2', 'max:500'],
        ]);

        $message = mb_strtolower(trim($data['message']));

        if ($this->isGreeting($message)) {
            return $this->reply(
                '¡Hola! Soy el asistente de PROCAFES. Puedo ayudarte a encontrar café, mostrar los más vendidos, revisar tu carrito, explicarte pagos, envíos y horarios.'
            );
        }

        if ($this->isBestSellerQuestion($message)) {
            return $this->reply(
                'Estos son algunos de los productos más vendidos de PROCAFES:',
                $this->bestSellers()
            );
        }

        if ($this->isCartQuestion($message)) {
            $cart = $request->session()->get('cart', ['items' => []]);
            $items = $cart['items'] ?? [];

            if (empty($items)) {
                return $this->reply(
                    'Tu carrito está vacío. Puedes escribirme “más vendidos” o decirme qué tipo de café buscas.'
                );
            }

            $count = collect($items)->sum(
                fn (array $item) => (int) ($item['qty'] ?? 0)
            );

            return $this->reply(
                "Tienes {$count} producto(s) en tu carrito. Puedes usar el botón “Ver carrito” para revisarlo o “Finalizar compra” para continuar."
            );
        }

        if ($this->isPaymentQuestion($message)) {
            return $this->reply(
                'Puedes pagar con Mercado Pago usando tarjeta, Yape o Plin. También encontrarás las opciones disponibles al finalizar tu compra.'
            );
        }

        if ($this->isShippingQuestion($message)) {
            return $this->reply(
                'Durante el checkout podrás registrar tu dirección de entrega. La disponibilidad y condiciones de envío se confirman antes de finalizar el pedido.'
            );
        }

        if ($this->isScheduleQuestion($message)) {
            return $this->reply(
                'Nuestro horario de atención es de lunes a viernes, de 08:00 a 20:00. En feriados pueden existir horarios especiales.'
            );
        }

        if ($this->isOrderQuestion($message)) {
            if (! $request->user()) {
                return $this->reply(
                    'Para revisar tus pedidos necesitas iniciar sesión. Después podrás ver el estado de tus compras desde tu cuenta.'
                );
            }

            return $this->reply(
                'Puedes revisar el estado de tus pedidos desde tu panel de cliente. Si deseas comprar, también puedo mostrarte productos disponibles.'
            );
        }

        if ($this->isProductQuestion($message)) {
            $products = $this->searchProducts($message);

            return $this->reply(
                $products->isNotEmpty()
                    ? 'Estos productos podrían interesarte:'
                    : 'No encontré productos disponibles con esa búsqueda. Prueba con “más vendidos” o escribe el nombre de un café.',
                $products
            );
        }

        return $this->reply(
            'Puedo ayudarte únicamente con PROCAFES: productos, café, carrito, pedidos, envíos, pagos, horarios y compras. Puedes escribir “más vendidos”, “ver carrito” o contarme qué café buscas.'
        );
    }

    private function reply(string $message, $products = []): JsonResponse
    {
        return response()->json([
            'message' => $message,
            'products' => $products,
        ]);
    }

    private function isGreeting(string $message): bool
    {
        return in_array($message, [
            'hola',
            'buenas',
            'buenos días',
            'buenas tardes',
            'buenas noches',
            'ayuda',
        ], true);
    }

    private function isBestSellerQuestion(string $message): bool
    {
        return $this->containsAny($message, [
            'más vendido',
            'mas vendido',
            'más vendidos',
            'mas vendidos',
            'más popular',
            'mas popular',
            'más populares',
            'mas populares',
            'favoritos',
            'recomendados',
        ]);
    }

    private function isCartQuestion(string $message): bool
    {
        return $this->containsAny($message, [
            'carrito',
            'ver carrito',
            'mi compra',
            'comprar',
            'finalizar compra',
            'ir a pagar',
        ]);
    }

    private function isPaymentQuestion(string $message): bool
    {
        return $this->containsAny($message, [
            'pago',
            'pagar',
            'pagos',
            'tarjeta',
            'yape',
            'plin',
            'mercado pago',
            'mercadopago',
        ]);
    }

    private function isShippingQuestion(string $message): bool
    {
        return $this->containsAny($message, [
            'envío',
            'envio',
            'envíos',
            'envios',
            'delivery',
            'entrega',
            'dirección',
            'direccion',
        ]);
    }

    private function isScheduleQuestion(string $message): bool
    {
        return $this->containsAny($message, [
            'horario',
            'horarios',
            'atienden',
            'atención',
            'atencion',
            'abren',
            'cierran',
        ]);
    }

    private function isOrderQuestion(string $message): bool
    {
        return $this->containsAny($message, [
            'pedido',
            'pedidos',
            'mi pedido',
            'estado de pedido',
            'estado de mi pedido',
            'mis compras',
        ]);
    }

    private function isProductQuestion(string $message): bool
    {
        return $this->containsAny($message, [
            'café',
            'cafe',
            'producto',
            'productos',
            'recomienda',
            'recomiéndame',
            'recomendar',
            'mostrar',
            'muestra',
            'muéstrame',
            'muestrame',
            'busco',
            'quiero',
            'tienen',
            'tienes',
        ]);
    }

    private function containsAny(string $message, array $keywords): bool
    {
        foreach ($keywords as $keyword) {
            if (str_contains($message, $keyword)) {
                return true;
            }
        }

        return false;
    }

    private function bestSellers()
    {
        $productIds = OrderItem::query()
            ->select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->whereIn('orders.status', ['paid', 'shipped'])
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(4)
            ->pluck('product_id');

        $products = Product::query()
            ->where('status', true)
            ->where('stock', '>', 0)
            ->whereIn('id', $productIds)
            ->get();

        $ordered = $productIds
            ->map(fn ($id) => $products->firstWhere('id', $id))
            ->filter()
            ->values();

        if ($ordered->isEmpty()) {
            $ordered = Product::query()
                ->where('status', true)
                ->where('stock', '>', 0)
                ->latest()
                ->limit(4)
                ->get();
        }

        return $ordered
            ->map(fn (Product $product) => $this->productPayload($product))
            ->values();
    }

    private function searchProducts(string $message)
    {
        $search = trim(str_ireplace([
            'café',
            'cafe',
            'producto',
            'productos',
            'recomiéndame',
            'recomiendame',
            'recomienda',
            'quiero',
            'busco',
            'muéstrame',
            'muestrame',
            'mostrar',
            'muestra',
            'tienen',
            'tienes',
        ], '', $message));

        $query = Product::query()
            ->where('status', true)
            ->where('stock', '>', 0);

        if ($search !== '') {
            $query->where(function ($builder) use ($search) {
                $builder->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        return $query
            ->latest()
            ->limit(4)
            ->get()
            ->map(fn (Product $product) => $this->productPayload($product))
            ->values();
    }

    private function productPayload(Product $product): array
    {
        return [
            'id' => $product->id,
            'name' => $product->name,
            'description' => $product->description,
            'price' => 'S/ ' . number_format((float) $product->price, 2),
            'image_url' => $product->image_url,
            'available' => $product->isAvailable(),
        ];
    }
}