@extends('layouts.admin')
@section('title','Panel | PROCAFES')

@push('styles')
<style>
  :root{
    --pcf-primary:#f2dd6c;
    --pcf-dark:#3e350e;
    --pcf-bg:#faf8ef;
  }

  body{
    background:var(--pcf-bg);
  }

  .chip{
    display:flex;
    align-items:center;
    gap:.5rem;
    background:#fff;
    border:1px solid rgba(0,0,0,.08);
    padding:.65rem .8rem;
    border-radius:.75rem;
    white-space:nowrap;
  }

  .chip i{ color:var(--pcf-dark); }

  .stat-ico{
    width:44px;
    height:44px;
    border-radius:.75rem;
    display:grid;
    place-items:center;
    background:var(--pcf-primary);
    color:var(--pcf-dark);
  }

  .link-muted{
    color:#6c757d;
    text-decoration:none;
  }

  .link-muted:hover{
    color:#495057;
  }

  .scroll-x{
    overflow:auto;
  }

  .shadow-soft{
    box-shadow:0 .5rem 1rem rgba(0,0,0,.06);
  }

  .mini-help{
    font-size:.8rem;
    color:#6c757d;
  }
</style>
@endpush

@section('admin-content')

<div class="flex flex-col md:flex-row md:items-center md:justify-between gap-3 mb-4">
  <h1 class="text-xl font-bold">Panel</h1>

  <div class="flex flex-wrap gap-2">

    <a href="{{ route('home') }}"
       target="_blank"
       class="px-3 py-1.5 text-sm border rounded hover:bg-gray-100 flex items-center gap-1">
      <i class="bi bi-shop-window"></i> Ver tienda
    </a>

    <a href="{{ route('admin.products.create') }}"
       class="px-3 py-1.5 text-sm bg-[#3e350e] text-white rounded hover:brightness-110 flex items-center gap-1">
      <i class="bi bi-plus-lg"></i> Nuevo producto
    </a>

    <div class="relative">
      <button class="px-3 py-1.5 text-sm border rounded flex items-center gap-1"
              onclick="document.getElementById('menuRep').classList.toggle('hidden')">
        <i class="bi bi-download"></i> Reportes
      </button>

      <ul id="menuRep"
          class="absolute right-0 mt-2 w-64 bg-white border rounded shadow hidden z-50 text-sm">

        <li>
          <a class="block px-3 py-2 hover:bg-gray-100"
             href="{{ route('admin.reports.sales-by-date', [
                'from' => now()->subMonth()->toDateString(),
                'to' => now()->toDateString()
             ]) }}">
            📊 Ventas últimos 12 meses
          </a>
        </li>

        <li>
          <a class="block px-3 py-2 hover:bg-gray-100"
             href="{{ route('admin.reports.best') }}">
            🏆 Productos más vendidos
          </a>
        </li>

        <li>
          <a class="block px-3 py-2 hover:bg-gray-100"
             href="{{ route('admin.reports.least') }}">
            📉 Productos menos vendidos
          </a>
        </li>

        <li>
          <a class="block px-3 py-2 hover:bg-gray-100"
             href="{{ route('admin.reports.inventory') }}">
            📦 Inventario crítico
          </a>
        </li>

        <li>
          <a class="block px-3 py-2 hover:bg-gray-100"
             href="{{ route('admin.reports.category') }}">
            ☕ Ventas por categoría
          </a>
        </li>

      </ul>
    </div>

  </div>
</div>

{{-- MÉTRICAS --}}
<div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4 mb-4">

  <div class="bg-white rounded-lg shadow-soft p-4 flex items-center gap-3">
    <div class="stat-ico"><i class="bi bi-coin text-lg"></i></div>
    <div>
      <div class="text-sm text-gray-500">Ingresos totales</div>
      <div class="text-xl font-bold">S/ {{ number_format($stats['revenue'] ?? 0, 2) }}</div>
    </div>
  </div>

  <div class="bg-white rounded-lg shadow-soft p-4 flex items-center gap-3">
    <div class="stat-ico"><i class="bi bi-bag-check text-lg"></i></div>
    <div>
      <div class="text-sm text-gray-500">Órdenes totales</div>
      <div class="text-xl font-bold">{{ number_format($stats['orders'] ?? 0) }}</div>
    </div>
  </div>

  <div class="bg-white rounded-lg shadow-soft p-4 flex items-center gap-3">
    <div class="stat-ico"><i class="bi bi-box-seam text-lg"></i></div>
    <div class="flex-1">
      <div class="text-sm text-gray-500">Productos totales</div>
      <div class="text-xl font-bold">{{ number_format($stats['products'] ?? 0) }}</div>
    </div>
    <a href="{{ route('admin.products.index') }}" class="text-xs text-gray-500 hover:underline">
      Ver
    </a>
  </div>

  <div class="bg-white rounded-lg shadow-soft p-4 flex items-center gap-3">
    <div class="stat-ico"><i class="bi bi-people text-lg"></i></div>
    <div>
      <div class="text-sm text-gray-500">Clientes totales</div>
      <div class="text-xl font-bold">{{ number_format($stats['customers'] ?? 0) }}</div>
    </div>
  </div>

</div>

{{-- CATEGORÍAS --}}
<div class="bg-white rounded-lg shadow-soft p-4 mb-4">
  <div class="flex justify-between items-center mb-2">
    <h6 class="font-semibold">Categorías</h6>
    <a href="{{ route('admin.categories.index') }}" class="text-sm text-gray-500 hover:underline">
      Gestionar
    </a>
  </div>

  <div class="flex gap-2 overflow-x-auto">
    @forelse($chips ?? [] as $c)
      <div class="chip">
        <i class="bi {{ $c['i'] }}"></i>
        {{ $c['t'] }}
      </div>
    @empty
      <span class="text-sm text-gray-400">No hay categorías</span>
    @endforelse
  </div>
</div>

{{-- GRÁFICO + TOP PRODUCTOS --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-4">

  {{-- CHART --}}
  <div class="xl:col-span-2 bg-white rounded-lg shadow-soft p-4">
    <div class="flex flex-col md:flex-row md:justify-between md:items-center gap-3 mb-3">
      <h6 class="font-semibold">Reporte de ingresos</h6>

      <div class="flex flex-wrap items-center gap-2 text-sm">

        <select id="group" class="border rounded px-2 py-1 text-sm">
          <option value="year">Año</option>
          <option value="month" selected>Mes</option>
          <option value="week">Semana</option>
          <option value="day">Día</option>
        </select>

        <input id="from" type="date" class="border rounded px-2 py-1 text-sm">
        <span class="text-gray-400">—</span>
        <input id="to" type="date" class="border rounded px-2 py-1 text-sm">

        <button id="apply"
                class="px-3 py-1 border rounded text-sm hover:bg-gray-100">
          Aplicar
        </button>

        <button id="reset"
                class="px-3 py-1 border rounded text-sm hover:bg-gray-100">
          Últimos 12 meses
        </button>

      </div>
    </div>

    <div style="height:300px">
      <canvas id="revChart"></canvas>
    </div>

    <div id="chartEmpty" class="text-center text-gray-500 py-6 hidden">
      📊 Sin datos para mostrar
    </div>

    <div id="chartError" class="text-red-500 text-sm mt-2 hidden"></div>
  </div>

  {{-- TOP --}}
  <div class="bg-white rounded-lg shadow-soft p-4">
    <h6 class="font-semibold mb-2">Productos más vendidos</h6>

    <ul class="space-y-3">
      @forelse($best ?? [] as $b)
        <li class="flex items-center gap-3">
          <img src="{{ $b['img'] ?? 'https://via.placeholder.com/48' }}"
               class="w-12 h-12 rounded object-cover">

          <div class="flex-1">
            <div class="font-medium text-sm">{{ $b['name'] }}</div>
            <div class="text-xs text-gray-500">{{ $b['sku'] ?? '' }}</div>
          </div>

          <div class="text-right text-xs">
            <div>{{ (int)($b['orders'] ?? 0) }} unid.</div>
            <div class="font-semibold">
              S/ {{ number_format($b['total'] ?? 0, 2) }}
            </div>
          </div>
        </li>
      @empty
        <li class="text-sm text-gray-400">Sin ventas aún</li>
      @endforelse
    </ul>
  </div>

</div>

@endsection