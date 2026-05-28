<?php
 
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Carbon\Carbon;
use App\Exports\ArrayExports;
use Maatwebsite\Excel\Facades\Excel;


class ReportController extends Controller
{
    /**
     * Devuelve el nombre de la columna de total en orders.
     */
    protected function orderTotalField(): ?string
    {
        if (Schema::hasColumn('orders', 'total_price')) return 'total_price';
        if (Schema::hasColumn('orders', 'total'))       return 'total';
        return null;
    }

    /**
     * Estados que consideramos como venta efectiva.
     */
    protected function paidStatuses(): array
    {
        return ['paid', 'shipped', 'completed', 'success'];
    }

    // ===== Ingresos últimos 12 meses (Excel) =====
    public function revenueExcel(): BinaryFileResponse
    {
        $orderTotalField = $this->orderTotalField();

        $months = collect(range(0, 11))
            ->map(fn ($i) => Carbon::now()->startOfMonth()->subMonths(11 - $i));

        $rows = [['Mes','Ingresos (S/)']];

        foreach ($months as $start) {
            $end = (clone $start)->addMonth();
            $sum = $orderTotalField
                ? (float) Order::whereBetween('created_at', [$start, $end])
                    ->whereIn('status', $this->paidStatuses())
                    ->sum($orderTotalField)
                : 0.0;

            $rows[] = [$start->isoFormat('YYYY-MM'), number_format($sum, 2, '.', '')];
        }

        return $this->downloadExcel('revenue_last_12m.xlsx', $rows);
    }

    // ===== Productos más vendidos (Top 100) =====
    public function bestSellersExcel(): BinaryFileResponse
    {
        $productPk   = Schema::hasColumn('products','product_id') ? 'product_id' : 'id';
        $oiProductFk = Schema::hasColumn('order_items','product_id') ? 'product_id'
                    : (Schema::hasColumn('order_items','products_id') ? 'products_id' : null);
        $unitPrice   = Schema::hasColumn('order_items','unit_price') ? 'unit_price'
                    : (Schema::hasColumn('order_items','price') ? 'price' : null);

        $rows = [['ProductoID','Producto','Unidades','Importe (S/)']];

        if ($oiProductFk && $unitPrice) {
            $items = DB::table('order_items as oi')
                ->join('products as p', "p.$productPk", '=', "oi.$oiProductFk")
                ->select([
                    "p.$productPk as product_id",
                    'p.name',
                    DB::raw('SUM(oi.quantity) as qty_sold'),
                    DB::raw("SUM(oi.quantity * oi.$unitPrice) as amount"),
                ])
                ->groupBy("p.$productPk", 'p.name')
                ->orderByDesc('qty_sold')
                ->limit(100)
                ->get();

            foreach ($items as $row) {
                $rows[] = [
                    $row->product_id,
                    $row->name,
                    (int) $row->qty_sold,
                    round((float) $row->amount, 2)
                ];
            }
        }

        return $this->downloadExcel('best_sellers.xlsx', $rows);
    }

    // ===== Inventario de productos =====
    public function productsCsv(): BinaryFileResponse
    {
        $pk    = Schema::hasColumn('products','product_id') ? 'product_id' : 'id';
        $cols  = ['name','slug','price','stock','brand_id','category_id','created_at'];
        $exists= collect($cols)->filter(fn ($c) => Schema::hasColumn('products',$c))->values()->all();

        $rows = [['ProductoID', ...array_map('ucfirst', $exists)]];

        Product::query()
            ->select(array_merge([$pk], $exists))
            ->orderBy($pk)
            ->chunk(500, function ($chunk) use (&$rows, $pk, $exists) {
                foreach ($chunk as $p) {
                    $line = [$p->{$pk}];
                    foreach ($exists as $c) $line[] = (string) $p->{$c};
                    $rows[] = $line;
                }
            });

        return $this->downloadExcel('productos_inventario.xlsx', $rows);
    }

    // ===== Órdenes por rango (YYYY-MM-DD a YYYY-MM-DD) =====
    public function ordersCsv(Request $request): BinaryFileResponse
    {
        $from = $request->query('from', Carbon::now()->subDays(30)->toDateString());
        $to   = $request->query('to',   Carbon::now()->toDateString());

        $total = $this->orderTotalField();

        $rows = [['OrderID','Status','Total','Fecha']];

        Order::query()
            ->when($from && $to, fn($q) => $q->whereBetween('created_at', [$from.' 00:00:00', $to.' 23:59:59']))
            ->orderByDesc('created_at')
            ->chunk(500, function ($chunk) use (&$rows, $total) {
                foreach ($chunk as $o) {
                    $rows[] = [
                        $o->id ?? $o->order_id,
                        $o->status,
                        $total ? number_format((float)$o->{$total}, 2, '.', '') : '0.00',
                        $o->created_at?->toDateTimeString(),
                    ];
                }
            });

        return $this->downloadExcel("orders_{$from}_{$to}.xlsx", $rows);
    }

    /**
     * Ventas por rango de fechas (YYYY-MM-DD a YYYY-MM-DD)
     * GET /admin/reports/sales.csv?from=YYYY-MM-DD&to=
     */
        public function salesByDate(): BinaryFileResponse
        {
            $from = now()
                ->subMonths(11)
                ->startOfMonth();

            $to = now()
                ->endOfMonth();

            $sales = DB::table('orders as o')
                ->join(
                    'users as u',
                    'u.id',
                    '=',
                    'o.user_id'
                )
                ->join(
                    'order_items as oi',
                    'oi.order_id',
                    '=',
                    'o.id'
                )
                ->join(
                    'products as p',
                    'p.id',
                    '=',
                    'oi.product_id'
                )
                ->select([
                    'u.name as cliente',
                    'p.name as producto',
                    'oi.quantity',
                    'oi.unit_price',
                    'oi.subtotal',
                    'o.created_at'
                ])
                ->whereBetween('o.created_at', [
                    $from,
                    $to
                ])
                ->whereIn('o.status', [
                    'paid',
                    'shipped'
                ])
                ->orderByDesc('o.created_at')
                ->get();

            $rows = [[
                'Cliente',
                'Producto',
                'Cantidad',
                'Precio Unitario',
                'Subtotal',
                'Fecha'
            ]];

            $totalVentas = 0;

            foreach ($sales as $sale) {

                $totalVentas += $sale->subtotal;

                $rows[] = [
                    $sale->cliente,
                    $sale->producto,
                    $sale->quantity,
                    'S/ ' . number_format(
                        $sale->unit_price,
                        2
                    ),
                    'S/ ' . number_format(
                        $sale->subtotal,
                        2
                    ),
                    Carbon::parse(
                        $sale->created_at
                    )->format('d/m/Y H:i')
                ];
            }

            // TOTAL GENERAL
            $rows[] = [
                'TOTAL GENERAL',
                '',
                '',
                '',
                'S/ ' . number_format(
                    $totalVentas,
                    2
                ),
                ''
            ];

            return $this->downloadExcel(
                'ventas_detalladas.xlsx',
                $rows
            );
        }

    /**
     * Productos más vendidos
     * Top 100 productos por cantidad vendida en un rango de fechas (YYYY-MM-DD a YYYY-MM-DD)
     * GET /admin/reports/best-sellers.csv?from=YYYY-MM-DD&
     */
        public function leastSellers(): BinaryFileResponse
        {
            $productPk = Schema::hasColumn(
                'products',
                'product_id'
            ) ? 'product_id' : 'id';

            $oiProductFk = Schema::hasColumn(
                'order_items',
                'product_id'
            )
            ? 'product_id'
            : 'products_id';

            $rows = [[
                'ProductoID',
                'Producto',
                'Unidades vendidas'
            ]];

            $items = DB::table('order_items as oi')
                ->join(
                    'products as p',
                    "p.$productPk",
                    '=',
                    "oi.$oiProductFk"
                )
                ->select([
                    "p.$productPk as product_id",
                    'p.name',
                    DB::raw(
                        'SUM(oi.quantity) as qty_sold'
                    ),
                ])
                ->groupBy(
                    "p.$productPk",
                    'p.name'
                )
                ->orderBy('qty_sold')
                ->limit(20)
                ->get();

            $totalVendidos = 0;

            foreach ($items as $row) {

                $totalVendidos += $row->qty_sold;

                $rows[] = [
                    $row->product_id,
                    $row->name,
                    $row->qty_sold
                ];
            }

            $rows[] = [
                'TOTAL',
                '',
                $totalVendidos
            ];

            return $this->downloadExcel(
                'productos_menos_vendidos.xlsx',
                $rows
            );
        }

    /**
     * Inventario critico de productos
     */
        public function criticalInventory(): BinaryFileResponse
        {
            $rows = [[
                'Producto',
                'Stock',
                'Estado'
            ]];

            $totalStock = 0;

            Product::query()
                ->where('stock', '<=', 10)
                ->orderBy('stock')
                ->get()
                ->each(function ($p) use (&$rows, &$totalStock) {

                    $totalStock += $p->stock;

                    $estado = match (true) {
                        $p->stock <= 3 => 'Crítico',
                        $p->stock <= 7 => 'Bajo',
                        default => 'Normal'
                    };

                    $rows[] = [
                        $p->name,
                        $p->stock,
                        $estado
                    ];
                });

            $rows[] = [
                'TOTAL STOCK',
                $totalStock,
                ''
            ];

            return $this->downloadExcel(
                'inventario_critico.xlsx',
                $rows
            );
        }

    /**
     * Ventas por categoría
     * Total vendido por cada categoría en un rango de fechas (YYYY-MM-DD a YYYY-MM
     */
        public function salesByCategory(): BinaryFileResponse
        {
            $productPk = Schema::hasColumn(
                'products',
                'product_id'
            ) ? 'product_id' : 'id';

            $categoryPk = Schema::hasColumn(
                'categories',
                'categories_id'
            ) ? 'categories_id' : 'id';

            $productCategoryFk = Schema::hasColumn(
                'products',
                'category_id'
            ) ? 'category_id' : 'categories_id';

            $oiProductFk = Schema::hasColumn(
                'order_items',
                'product_id'
            ) ? 'product_id' : 'products_id';

            $unitPrice = Schema::hasColumn(
                'order_items',
                'unit_price'
            ) ? 'unit_price' : 'price';

            $rows = [[
                'Categoría',
                'Unidades vendidas',
                'Ingresos (S/)'
            ]];

            $categories = DB::table('order_items as oi')
                ->join(
                    'products as p',
                    "p.$productPk",
                    '=',
                    "oi.$oiProductFk"
                )
                ->join(
                    'categories as c',
                    "c.$categoryPk",
                    '=',
                    "p.$productCategoryFk"
                )
                ->select([
                    'c.name',
                    DB::raw(
                        'SUM(oi.quantity) as total_qty'
                    ),
                    DB::raw(
                        "SUM(oi.quantity * oi.$unitPrice) as total_amount"
                    )
                ])
                ->groupBy('c.name')
                ->orderByDesc('total_amount')
                ->get();

            $totalQty = 0;
            $totalAmount = 0;

            foreach ($categories as $c) {

                $totalQty += $c->total_qty;
                $totalAmount += $c->total_amount;

                $rows[] = [
                    $c->name,
                    $c->total_qty,
                    number_format(
                        $c->total_amount,
                        2,
                        '.',
                        ''
                    )
                ];
            }

            $rows[] = [
                'TOTAL GENERAL',
                $totalQty,
                number_format(
                    $totalAmount,
                    2,
                    '.',
                    ''
                )
            ];

            return $this->downloadExcel(
                'ventas_categoria.xlsx',
                $rows
            );
        }
    /**
     * ===== JSON para el gráfico del Dashboard =====
     * GET /admin/reports/revenue.json?group=year|month|week|day&from=YYYY-MM-DD&to=YYYY-MM-DD
     */
    public function revenueJson(Request $request)
    {
        $group = $request->query('group', 'month'); // year|month|week|day
        $to    = Carbon::now();
        $from  = (clone $to)->subMonths(12);

        if ($request->filled('from')) $from = Carbon::parse($request->query('from'));
        if ($request->filled('to'))   $to   = Carbon::parse($request->query('to'));

        // Expresión SQL por agrupación (MySQL/MariaDB)
        switch ($group) {
            case 'year':
                $bucketExpr = "YEAR(created_at)";
                $labelExpr  = "DATE_FORMAT(created_at, '%Y')";
                break;

            case 'week':
                // ISO week: YEARWEEK(..., 3)
                $bucketExpr = "YEARWEEK(created_at, 3)";
                $labelExpr  = "CONCAT(YEAR(created_at), '-W', LPAD(WEEK(created_at, 3), 2, '0'))";
                break;

            case 'day':
                $bucketExpr = "DATE(created_at)";
                $labelExpr  = "DATE_FORMAT(created_at, '%Y-%m-%d')";
                break;

            case 'month':
            default:
                $bucketExpr = "DATE_FORMAT(created_at, '%Y-%m')";
                $labelExpr  = "DATE_FORMAT(created_at, '%Y-%m')";
                break;
        }

        $totalField = $this->orderTotalField();

        $rows = DB::table('orders')
            ->selectRaw("$bucketExpr as bucket, $labelExpr as label, SUM($totalField) as revenue")
            ->whereBetween('created_at', [$from->startOfDay(), $to->endOfDay()])
            ->when($totalField === null, fn($q) => $q->selectRaw('0 as revenue'))
            ->whereIn('status', $this->paidStatuses())
            ->groupBy('bucket', 'label')
            ->orderBy('bucket')
            ->get();

        $labels = $rows->pluck('label')->all();
        $data   = $rows->pluck('revenue')->map(fn($v) => round((float)$v, 2))->all();
        $total  = array_sum($data);

        return response()->json([
            'ok'     => true,
            'group'  => $group,
            'from'   => $from->toDateString(),
            'to'     => $to->toDateString(),
            'labels' => $labels,
            'data'   => $data,
            'total'  => round($total, 2),
        ]);
    }

    protected function downloadExcel(
        string $filename,
        array $rows
    ): BinaryFileResponse {
        return Excel::download(
            new ArrayExports($rows),
            $filename
        );
    }
}
