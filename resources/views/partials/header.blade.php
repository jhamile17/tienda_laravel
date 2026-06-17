<header class="border-bottom bg-procafes">
  <div class="container d-flex align-items-center justify-content-between py-2">

    {{-- IZQ: Logo --}}
    <a href="{{ route('home') }}" class="d-flex align-items-center text-decoration-none">
      <img src="{{ asset('images/logo.png') }}" alt="PROCAFES" width="36" height="36" class="me-2">
      <strong class="text-dark">PROCAFES</strong>
    </a>

    {{-- CENTRO: Buscador --}}
    <form action="{{ route('home') }}" method="GET" class="flex-grow-1 mx-3" style="max-width:680px;">
      <div class="input-group">
        <input
          type="text"
          name="q"
          value="{{ request('q') }}"
          class="form-control"
          placeholder="Buscar productos...">

        <button class="btn btn-outline-secondary" type="submit">
          <i class="bi bi-search"></i>
        </button>
      </div>
    </form>

    {{-- DER: Acciones --}}
    <div class="d-flex align-items-center gap-2">

      {{-- Links informativos --}}
      <a href="{{ route('nosotros') }}"
         class="btn btn-link link-procafes text-decoration-none">
         Nosotros
      </a>

      <a href="{{ route('ubicanos') }}"
         class="btn btn-link link-procafes text-decoration-none">
         Ubícanos
      </a>

      {{-- Wishlist --}}
      @php
        $wishlistCount = auth()->check()
            ? \App\Models\Wishlist::where('user_id', auth()->id())->count()
            : 0;
      @endphp

      @auth
          <a href="{{ route('wishlist.index') }}"
             class="btn btn-sm position-relative"
             style="background:#E0CF61;border:none;color:#3E350E;">

              <i class="bi bi-heart"></i>

              <span id="wishlistCount"
                    class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">

                  {{ $wishlistCount }}

              </span>
          </a>
      @else
          <a href="{{ route('login') }}"
             class="btn btn-sm"
             style="background:#E0CF61;border:none;color:#3E350E;">

              <i class="bi bi-heart"></i>

          </a>
      @endauth


      {{-- Carrito --}}
      <button type="button"
              class="btn btn-sm position-relative"
              style="background:#E0CF61;border:none;color:#3E350E;"
              data-bs-toggle="offcanvas"
              data-bs-target="#cartOffcanvas">

        <i class="bi bi-cart"></i>

        <span id="cartBadge"
              class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-dark">
              0
        </span>
      </button>

      {{-- Autenticación --}}
      @auth

      <div class="dropdown">

          <button class="btn btn-sm dropdown-toggle"
                  data-bs-toggle="dropdown"
                  style="background:#E0CF61;border:none;color:#3E350E;">

              Hola, {{ Str::limit(auth()->user()->name,10) }}

          </button>

          <ul class="dropdown-menu dropdown-menu-end">

              <li>
                  <a class="dropdown-item"
                     href="{{ route('customer.dashboard') }}">
                      Panel
                  </a>
              </li>

              <li>
                  <a class="dropdown-item"
                     href="{{ route('wishlist.index') }}">
                      Mis favoritos
                  </a>
              </li>

              <li><hr class="dropdown-divider"></li>

              <li>
                  <form method="POST"
                        action="{{ route('logout') }}">
                      @csrf
                      <button class="dropdown-item">
                          Cerrar sesión
                      </button>
                  </form>
              </li>

          </ul>

      </div>

      @else

      <a href="{{ route('login') }}"
         class="btn btn-sm btn-outline-dark">
          Iniciar sesión
      </a>

      <a href="{{ route('register') }}"
         class="btn btn-sm btn-procafes-dark">
          Registrarse
      </a>

      @endauth

    </div>
  </div>
</header>