// resources/js/cart.js
window.addEventListener('DOMContentLoaded', () => {
  const ROUTES = window.Laravel?.routes || {};
  const getCsrfToken = () =>
  document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
  const APP    = window.App || { isAuth:false, routes:{} };

  const badge       = document.getElementById('cartBadge');
  const itemsBox    = document.getElementById('cartItems');
  const totalBox    = document.getElementById('cartTotal');
  const offcanvasEl = document.getElementById('cartOffcanvas');
  const offcanvas   = offcanvasEl ? new bootstrap.Offcanvas(offcanvasEl) : null;

  // ---- helpers ----
  function currency(n){
    const val = Number.isFinite(n) ? n : 0;
    try { return new Intl.NumberFormat('es-PE',{style:'currency',currency:'PEN'}).format(val); }
    catch { return `S/ ${val.toFixed(2)}`; }
  }
  function isJsonResponse(res){ return (res.headers.get('content-type')||'').includes('application/json'); }

  async function api(url, method='GET', data=null){
    const res = await fetch(url, {
      method,
  headers: {
    'Accept': 'application/json',
    'X-CSRF-TOKEN': getCsrfToken(),
    ...(data ? {'Content-Type': 'application/json'} : {})
  },      
  body: data ? JSON.stringify(data) : null
    });
    if (!isJsonResponse(res)) throw new Error(`Respuesta no JSON (${res.status})`);
    if (!res.ok) throw new Error((await res.json()).message || `HTTP ${res.status}`);
    return await res.json();
  }

  function render(cart){
    if (badge)    badge.textContent = cart?.count ?? 0;
    if (totalBox) totalBox.textContent = currency(cart?.total ?? 0);
    if (!itemsBox) return;

    itemsBox.innerHTML = '';
    const entries = Object.values(cart?.items || {});
    if (!entries.length){ itemsBox.innerHTML = '<div class="text-muted small">Tu carrito está vacío.</div>'; return; }

    entries.forEach(it=>{
      const price = Number(it.price)||0, qty = Number(it.qty)||0;
      const div = document.createElement('div');
      div.className = 'list-group-item py-3';
      div.innerHTML = `
        <div class="d-flex gap-2">
          <img src="${it.image ?? 'https://via.placeholder.com/60'}" class="rounded" width="60" height="60" alt="">
          <div class="flex-grow-1">
            <a href="${it.url ?? '#'}" class="text-decoration-none fw-semibold">${it.name}</a>
            <div class="small text-muted">Precio: ${currency(price)}</div>
            <div class="d-flex align-items-center gap-2 mt-2">
              <button class="btn btn-sm btn-outline-secondary btn-dec" data-id="${it.rowId}">-</button>
              <input class="form-control form-control-sm qty-input" data-id="${it.rowId}" style="width:64px" type="number" min="1" value="${qty}">
              <button class="btn btn-sm btn-outline-secondary btn-inc" data-id="${it.rowId}">+</button>
              <span class="ms-auto fw-semibold">${currency(price * qty)}</span>
              <button class="btn btn-sm btn-outline-danger ms-2 btn-remove" data-id="${it.rowId}">
                <i class="bi bi-trash"></i>
              </button>
            </div>
          </div>
        </div>`;
      itemsBox.appendChild(div);
    });
  }

  // Toast minimal con Bootstrap 5
  function showToast({title='Listo', body='', actions=[]}){
    const container = document.getElementById('toastContainer');
    if (!container) return;
    const id = 't'+Date.now();
    const div = document.createElement('div');
    div.innerHTML = `
      <div id="${id}" class="toast align-items-center border-0 shadow" role="alert" aria-live="assertive" aria-atomic="true">
        <div class="toast-header">
          <strong class="me-auto">${title}</strong>
          <small>ahora</small>
          <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Cerrar"></button>
        </div>
        <div class="toast-body">
          <div class="mb-2">${body}</div>
          <div class="d-flex gap-2">
            ${actions.map(a=>`<a href="${a.href || '#'}" class="btn btn-sm ${a.class || 'btn-primary'}" ${a.dismiss ? 'data-bs-dismiss="toast"':''}>${a.label}</a>`).join('')}
          </div>
        </div>
      </div>`;
    const toastEl = div.firstElementChild;
    container.appendChild(toastEl);
    const toast = new bootstrap.Toast(toastEl, { delay: 2500 });
    toast.show();
    toastEl.addEventListener('hidden.bs.toast', ()=> toastEl.remove());
  }

  // ---- API pública para otros componentes, como el chatbot ----
    window.refreshCart = async function () {
      if (!ROUTES.index) {
        return;
      }

      try {
        const cart = await api(ROUTES.index);
        render(cart);
      } catch (err) {
        console.error('[CART] refresh error:', err);
      }
    };

    // init
    window.refreshCart();

  // ---- ADD ----
  document.addEventListener('click', async (e)=>{
    const btn = e.target.closest('.btn-add-to-cart');
    if (!btn || !ROUTES.add) return;
    e.preventDefault(); e.stopPropagation();

    // loading UI
    const prevHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = `<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Añadiendo...`;

    const rawVariant = btn.dataset.variant; let variant = null;
    if (rawVariant){ try{ variant=JSON.parse(rawVariant);}catch{ variant=rawVariant; } }

    const payload = {
     product_id: Number.parseInt(btn.dataset.id,10),
     quantity:Math.max(1, Number.parseInt(btn.dataset.qty || '1',10)),
    };

    try {
      const cart = await api(ROUTES.add,'POST',payload);
      render(cart);

      offcanvas?.show();
    } catch (err) {
      console.error('[CART] add error:', err);
      showToast({ title:'Ups', body:'No se pudo agregar al carrito.', actions:[] });
    } finally {
      btn.disabled = false;
      btn.innerHTML = prevHtml;
    }
  });

  // ---- item actions ----
  itemsBox?.addEventListener('click', async (e)=>{
    const inc = e.target.closest('.btn-inc');
    const dec = e.target.closest('.btn-dec');
    const rm  = e.target.closest('.btn-remove');
    try {
      if ((inc||dec) && ROUTES.base){
        const id = (inc||dec).dataset.id;
        const input = itemsBox.querySelector(`.qty-input[data-id="${id}"]`);
        let qty = parseInt(input?.value || '1',10);
        qty = inc ? qty+1 : Math.max(1, qty-1);
        const cart = await api(`${ROUTES.base}/${id}`,'PATCH',{ qty });
        render(cart);
      }
      if (rm && ROUTES.base){
        const id = rm.dataset.id;
        const cart = await api(`${ROUTES.base}/${id}`,'DELETE');
        render(cart);
      }
    } catch (err) { console.error('[CART] item action error:', err); }
  });

  itemsBox?.addEventListener('change', async (e)=>{
    const input = e.target.closest('.qty-input'); if (!input || !ROUTES.base) return;
    let qty = Math.max(1, parseInt(input.value || '1',10));
    try {
      const cart = await api(`${ROUTES.base}/${input.dataset.id}`,'PATCH',{ qty });
      render(cart);
    } catch (err) { console.error('[CART] qty change error:', err); }
  });

  document.getElementById('btnClearCart')?.addEventListener('click', async (e)=>{
    e.preventDefault();
    if (!ROUTES.clear) return;
    try { const cart = await api(ROUTES.clear,'DELETE'); render(cart); }
    catch (err) { console.error('[CART] clear error:', err); }
  });
    // ---- WISHLIST ----
  document.addEventListener('click', async (e) => {
    const btn = e.target.closest('.btn-wishlist');
    if (!btn) return;

    e.preventDefault();

    try {
      const response = await api('/wishlist/toggle', 'POST', {
        product_id: btn.dataset.id
      });

      if (response.added) {
        btn.classList.remove('btn-outline-danger');
        btn.classList.add('btn-danger');
        btn.innerHTML = '<i class="bi bi-heart-fill me-1"></i> En favoritos';
      } else {
        btn.classList.remove('btn-danger');
        btn.classList.add('btn-outline-danger');
        btn.innerHTML = '<i class="bi bi-heart me-1"></i> Favoritos';
      }
    } catch (err) {
      console.error('[WISHLIST] error:', err);

      if (err.message.includes('401')) {
        window.location.href = APP.routes.login;
        return;
      }

      showToast({
        title: 'Ups',
        body: 'No se pudo actualizar favoritos.',
        actions: []
      });
    }
  });
});
