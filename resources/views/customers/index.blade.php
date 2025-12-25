@extends('layouts.customer')

@section('title', __('messages.customer_title') ?? 'Customer')

@php
  // All labels come from translations, fallback if missing.
  $ui = [
    'home' => [
      'title'    => __('messages.customer_home_title') ?? 'Product listing',
      'subtitle' => __('messages.customer_home_subtitle') ?? 'Browse items and order before you arrive.',
      'search'   => __('messages.search') ?? 'Search',
      'all'      => __('messages.all') ?? 'All',
      'see_all'  => __('messages.see_all') ?? 'See all',
    ],
    'cart' => [
      'title'    => __('messages.cart') ?? 'Cart',
      'your_cart'=> __('messages.your_cart') ?? 'Your cart',
      'empty'    => __('messages.cart_empty') ?? 'Your cart is empty.',
      'subtotal' => __('messages.subtotal') ?? 'Subtotal',
      'shipping' => __('messages.shipping') ?? 'Shipping',
      'total'    => __('messages.total') ?? 'Total',
      'clear'    => __('messages.clear') ?? 'Clear',
      'checkout' => __('messages.checkout') ?? 'Checkout',
    ],
    'history' => [
      'title'    => __('messages.history') ?? 'History',
      'orders'   => __('messages.order_history') ?? 'Order history',
      'empty'    => __('messages.no_orders') ?? 'No orders yet.',
    ],
    'profile' => [
      'title'   => __('messages.my_profile') ?? 'My profile',
      'logout'  => __('messages.logout') ?? 'Log out',
      'name'    => __('messages.name') ?? 'Name',
      'phone'   => __('messages.phone') ?? 'Phone',
      'email'   => __('messages.email') ?? 'Email',
      'password'=> __('messages.password') ?? 'Password',
      'birthday'=> __('messages.birthday') ?? 'Birthday',
      'save'    => __('messages.save') ?? 'Save',
    ],
    'nav' => [
      'home'    => __('messages.home') ?? 'Home',
      'cart'    => __('messages.cart') ?? 'Cart',
      'history' => __('messages.history') ?? 'History',
      'account' => __('messages.account') ?? 'Account',
    ],
    'toast' => [
      'added' => __('messages.added_to_cart') ?? 'Added to cart',
      'cleared' => __('messages.cart_cleared') ?? 'Cart cleared',
      'saved' => __('messages.saved') ?? 'Saved ✅',
    ],
  ];

  $customerConfig = [
    'ui' => $ui,
    'api' => [
      'categories' => '/api/categories',
      'items'      => '/api/menu/items',
      // later you can add real endpoints:
      // 'orders'     => '/api/customer/orders',
    ],
  ];
@endphp

@section('content')
<div class="min-h-screen bg-slate-50 text-slate-900 dark:bg-slate-950 dark:text-slate-100">
  {{-- Top bar --}}
  <header class="sticky top-0 z-20 border-b border-slate-200/70 dark:border-slate-800
                 bg-white/80 dark:bg-slate-950/80 backdrop-blur">
    <div class="mx-auto w-full max-w-[420px] px-4 pt-3 pb-2 flex items-center justify-between">
      <div class="flex items-center gap-2">
        <button id="backBtn"
                class="hidden text-slate-500 dark:text-slate-300 active:scale-95 transition"
                type="button">
          <i class="bi bi-chevron-left text-xl"></i>
        </button>
        <div>
          <h1 id="pageTitle" class="text-[15px] font-extrabold tracking-tight">
            {{ $ui['home']['title'] }}
          </h1>
          <p id="pageSub" class="text-[11px] text-slate-500 dark:text-slate-400 -mt-0.5">
            {{ $ui['home']['subtitle'] }}
          </p>
        </div>
      </div>

      <div class="flex items-center gap-2">
        {{-- Theme button: if your layout already has it, you can remove this --}}
        <button id="themeToggle"
                type="button"
                class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-800
                       bg-white dark:bg-slate-900 shadow-sm grid place-items-center"
                aria-label="Theme">
          <span id="themeIcon" class="text-sm">☾</span>
        </button>

        {{-- Cart icon --}}
        <button id="topCartBtn"
                type="button"
                class="relative w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-800
                       bg-white dark:bg-slate-900 shadow-sm grid place-items-center"
                aria-label="Cart">
          <i class="bi bi-cart3 text-lg"></i>
          <span id="cartBadge"
                class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full text-[11px] font-extrabold
                       bg-indigo-600 text-white grid place-items-center">0</span>
        </button>

        {{-- Profile icon --}}
        <button id="topProfileBtn"
                type="button"
                class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-800
                       bg-white dark:bg-slate-900 shadow-sm grid place-items-center"
                aria-label="Account">
          <i class="bi bi-person-circle text-xl"></i>
        </button>
      </div>
    </div>
  </header>

  {{-- App body (mobile width container) --}}
  <main class="mx-auto w-full max-w-[420px] px-4 pt-3 pb-24">

    {{-- Screens --}}
    @include('customers.partials.home-modal')
    @include('customers.partials.cart-modal')
    @include('customers.partials.history-modal')
    @include('customers.partials.profile-modal')

  </main>

  {{-- Bottom nav --}}
  @include('customers.partials.nav-modal')

</div>
@endsection

@push('scripts')
<script>
  // Config + translations for JS
  window.__CUSTOMER_CONFIG__ = @json($customerConfig);
</script>

<script>
(() => {
  "use strict";

  // ✅ FIX: arrow functions must use => (not =)
  const $  = (sel, root=document) => root.querySelector(sel);
  const $$ = (sel, root=document) => Array.from(root.querySelectorAll(sel));

  const CFG = window.__CUSTOMER_CONFIG__ || {};
  const UI  = CFG.ui || {};
  const API = CFG.api || {};

  // ---- storage keys ----
  const KEY_CART    = "cust_cart_v1";
  const KEY_ORDERS  = "cust_orders_v1";
  const KEY_PROFILE = "cust_profile_v1";

  const state = {
    screen: "home",
    categories: [],
    activeCategoryId: 0,
    q: "",
    items: [],
    cart: readJSON(KEY_CART, []),
    orders: readJSON(KEY_ORDERS, []),
  };

  // ---- top bar ----
  const pageTitle = $("#pageTitle");
  const pageSub   = $("#pageSub");
  const backBtn   = $("#backBtn");
  const cartBadge = $("#cartBadge");

  // ---- buttons ----
  $("#topCartBtn")?.addEventListener("click", () => setScreen("cart"));
  $("#topProfileBtn")?.addEventListener("click", () => setScreen("profile"));

  // ---- theme (simple) ----
  const htmlEl = document.documentElement;
  const themeToggle = $("#themeToggle");
  const themeIcon   = $("#themeIcon");
  function applyTheme(t) {
    if (t === "dark") {
      htmlEl.classList.add("dark");
      if (themeIcon) themeIcon.textContent = "☾";
    } else {
      htmlEl.classList.remove("dark");
      if (themeIcon) themeIcon.textContent = "☀";
    }
    try { localStorage.setItem("pos-theme", t); } catch(e) {}
  }
  applyTheme((() => { try { return localStorage.getItem("pos-theme") || "dark"; } catch(e){ return "dark"; } })());
  themeToggle?.addEventListener("click", () => {
    applyTheme(htmlEl.classList.contains("dark") ? "light" : "dark");
  });

  // ---- helpers ----
  function t(path, fallback="") {
    // path like "home.title"
    const parts = String(path).split(".");
    let cur = UI;
    for (const p of parts) cur = cur?.[p];
    return (cur == null || cur === "") ? fallback : String(cur);
  }

  function readJSON(key, fallback) {
    try { return JSON.parse(localStorage.getItem(key)) ?? fallback; }
    catch { return fallback; }
  }
  function writeJSON(key, val) {
    localStorage.setItem(key, JSON.stringify(val));
  }

  function updateBadge() {
    const count = state.cart.reduce((s, it) => s + (Number(it.qty)||0), 0);
    if (cartBadge) cartBadge.textContent = String(count);

    // also update nav badge
    const navBadge = $("#navCartBadge");
    if (navBadge) navBadge.textContent = String(count);
  }

  function money(n) {
    return "$" + Number(n || 0).toFixed(2);
  }

  async function api(url, opts = {}) {
    const res = await fetch(url, {
      credentials: "same-origin",
      headers: { "Accept": "application/json", ...(opts.headers || {}) },
      ...opts,
    });
    const text = await res.text();
    let data; try { data = text ? JSON.parse(text) : null; } catch { data = { message: text }; }
    if (!res.ok) throw Object.assign(new Error(data?.message || `HTTP ${res.status}`), { status: res.status, data });
    return data;
  }

  // ---- screens ----
  const TITLES = {
    home:    { title: t("home.title", "Product listing"), sub: t("home.subtitle","") },
    cart:    { title: t("cart.title", "Cart"), sub: "" },
    history: { title: t("history.title", "History"), sub: "" },
    profile: { title: t("profile.title", "My profile"), sub: "" },
  };

  function setScreen(name) {
    state.screen = name;

    $$(".screen").forEach(s => s.classList.add("hidden"));
    const active = $(`[data-screen="${name}"]`);
    active?.classList.remove("hidden");

    $$(".navbtn").forEach(b => b.classList.remove("is-active"));
    $(`[data-go="${name}"]`)?.classList.add("is-active");

    const meta = TITLES[name] || TITLES.home;
    if (pageTitle) pageTitle.textContent = meta.title;
    if (pageSub)   pageSub.textContent   = meta.sub || "";

    if (backBtn) {
      if (name === "home") backBtn.classList.add("hidden");
      else backBtn.classList.remove("hidden");
      backBtn.onclick = () => setScreen("home");
    }

    // refresh screen
    if (name === "home") renderItems();
    if (name === "cart") renderCart();
    if (name === "history") renderOrders();
    if (name === "profile") loadProfile();
    location.hash = "#" + name;
  }

  // ---- nav clicks (no inline onclick) ----
  $$(".navbtn").forEach(btn => {
    btn.addEventListener("click", () => {
      const go = btn.getAttribute("data-go");
      if (go) setScreen(go);
    });
  });

  // ---- Home: categories + items ----
  const catPills = $("#catPills");
  const searchInput = $("#searchInput");
  const productsGrid = $("#productsGrid");
  const productsEmpty = $("#productsEmpty");

  function pillClass(active) {
    return [
      "px-4 py-1 rounded-full text-[12px] font-bold whitespace-nowrap transition",
      active
        ? "bg-indigo-600 text-white"
        : "bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 text-slate-700 dark:text-slate-200"
    ].join(" ");
  }

  function renderCategories() {
    if (!catPills) return;
    catPills.innerHTML = "";

    const allBtn = document.createElement("button");
    allBtn.type = "button";
    allBtn.className = pillClass(state.activeCategoryId === 0);
    allBtn.textContent = t("home.all", "All");
    allBtn.addEventListener("click", () => {
      state.activeCategoryId = 0;
      refreshItems();
    });
    catPills.appendChild(allBtn);

    state.categories.forEach(c => {
      const b = document.createElement("button");
      b.type = "button";
      b.className = pillClass(state.activeCategoryId === c.id);
      b.textContent = c.name || ("#" + c.id);
      b.addEventListener("click", () => {
        state.activeCategoryId = c.id;
        refreshItems();
      });
      catPills.appendChild(b);
    });
  }

  async function loadCategories() {
    const res = await api(API.categories || "/api/categories");
    state.categories = Array.isArray(res?.data) ? res.data : (Array.isArray(res) ? res : []);
  }

  async function refreshItems() {
    renderCategories();

    const params = new URLSearchParams();
    params.set("per_page", "100");
    params.set("visible_only", "1");
    params.set("include_variants", "0");
    if (state.activeCategoryId) params.set("category_id", String(state.activeCategoryId));
    if (state.q) params.set("q", state.q);

    const res = await api((API.items || "/api/menu/items") + "?" + params.toString());
    state.items = Array.isArray(res?.data) ? res.data : (Array.isArray(res) ? res : []);
    renderItems();
  }

  function renderItems() {
    if (!productsGrid || !productsEmpty) return;
    productsGrid.innerHTML = "";
    productsEmpty.classList.toggle("hidden", state.items.length !== 0);

    state.items.forEach(it => {
      const card = document.createElement("div");
      card.className = "rounded-2xl bg-white dark:bg-slate-900 border border-slate-200 dark:border-slate-800 shadow-sm overflow-hidden";

      const img = document.createElement("div");
      img.className = "h-28 bg-slate-100 dark:bg-slate-800 flex items-center justify-center overflow-hidden";
      img.innerHTML = it.image
        ? `<img class="h-full w-full object-cover" src="${it.image.startsWith('http') ? it.image : '/storage/' + it.image}" alt="">`
        : `<div class="text-slate-400 font-extrabold text-xs">IMG</div>`;

      const body = document.createElement("div");
      body.className = "p-3";

      const name = document.createElement("p");
      name.className = "text-[12px] font-extrabold text-slate-900 dark:text-slate-50 truncate";
      name.textContent = it.name || "—";

      const row = document.createElement("div");
      row.className = "mt-2 flex items-center justify-between";

      const price = document.createElement("p");
      price.className = "text-[13px] font-extrabold text-slate-900 dark:text-slate-50";
      price.textContent = money(it.price || 0);

      const addBtn = document.createElement("button");
      addBtn.type = "button";
      addBtn.className = "w-8 h-8 rounded-xl bg-indigo-50 dark:bg-indigo-950 text-indigo-600 dark:text-indigo-300 font-extrabold active:scale-95 transition";
      addBtn.innerHTML = `<i class="bi bi-plus-lg"></i>`;
      addBtn.addEventListener("click", () => addToCart(it));

      row.appendChild(price);
      row.appendChild(addBtn);

      body.appendChild(name);
      body.appendChild(row);

      card.appendChild(img);
      card.appendChild(body);

      productsGrid.appendChild(card);
    });
  }

  let searchTimer = null;
  searchInput?.addEventListener("input", () => {
    clearTimeout(searchTimer);
    searchTimer = setTimeout(() => {
      state.q = String(searchInput.value || "").trim();
      refreshItems();
    }, 250);
  });

  // ---- Cart ----
  function addToCart(item) {
    const id = item.id;
    const existing = state.cart.find(x => x.id === id);
    if (existing) existing.qty += 1;
    else state.cart.push({ id, name: item.name, price: Number(item.price||0), qty: 1 });

    writeJSON(KEY_CART, state.cart);
    updateBadge();
    renderCart();
  }

  function inc(id) {
    const it = state.cart.find(x => x.id === id);
    if (!it) return;
    it.qty += 1;
    writeJSON(KEY_CART, state.cart);
    updateBadge();
    renderCart();
  }

  function dec(id) {
    const it = state.cart.find(x => x.id === id);
    if (!it) return;
    it.qty = Math.max(1, it.qty - 1);
    writeJSON(KEY_CART, state.cart);
    updateBadge();
    renderCart();
  }

  function removeItem(id) {
    state.cart = state.cart.filter(x => x.id !== id);
    writeJSON(KEY_CART, state.cart);
    updateBadge();
    renderCart();
  }

  function clearCart() {
    state.cart = [];
    writeJSON(KEY_CART, state.cart);
    updateBadge();
    renderCart();
  }

  function calcShipping(subtotal) {
    return subtotal >= 50 ? 0 : 5;
  }

  function renderCart() {
    const list = $("#cartList");
    if (!list) return;

    const cart = state.cart;
    if (!cart.length) {
      list.innerHTML = `<p class="text-[13px] text-slate-500 dark:text-slate-300">${t("cart.empty","Your cart is empty.")}</p>`;
    } else {
      list.innerHTML = cart.map(it => `
        <div class="flex items-center justify-between gap-3">
          <div class="min-w-0">
            <p class="text-[13px] font-extrabold text-slate-900 dark:text-slate-50 truncate">${escapeHtml(it.name)}</p>
            <p class="text-[12px] text-slate-500 dark:text-slate-300">${money(it.price)} ${t("cart.each","each")}</p>
          </div>

          <div class="flex items-center gap-2 shrink-0">
            <button class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 font-extrabold"
                    data-dec="${it.id}">-</button>

            <div class="w-10 text-center font-extrabold">${it.qty}</div>

            <button class="w-9 h-9 rounded-xl border border-slate-200 dark:border-slate-800 bg-white dark:bg-slate-950 font-extrabold"
                    data-inc="${it.id}">+</button>

            <button class="w-9 h-9 rounded-xl bg-rose-50 dark:bg-rose-950 text-rose-600 dark:text-rose-300 font-extrabold"
                    data-rm="${it.id}">
              <i class="bi bi-x-lg text-sm"></i>
            </button>
          </div>
        </div>
      `).join("");
    }

    // totals
    const subtotal = cart.reduce((s, it) => s + (it.price * it.qty), 0);
    const shipping = calcShipping(subtotal);
    const total = subtotal + shipping;

    $("#cartSubtotal").textContent = money(subtotal);
    $("#cartShipping").textContent = money(shipping);
    $("#cartTotal").textContent = money(total);
  }

  // cart events (delegation)
  document.addEventListener("click", (e) => {
    const decBtn = e.target.closest("[data-dec]");
    const incBtn = e.target.closest("[data-inc]");
    const rmBtn  = e.target.closest("[data-rm]");

    if (decBtn) return dec(Number(decBtn.getAttribute("data-dec")));
    if (incBtn) return inc(Number(incBtn.getAttribute("data-inc")));
    if (rmBtn)  return removeItem(Number(rmBtn.getAttribute("data-rm")));
  });

  $("#btnClearCart")?.addEventListener("click", clearCart);

  $("#btnCheckout")?.addEventListener("click", () => {
    if (!state.cart.length) return;

    const subtotal = state.cart.reduce((s, it) => s + (it.price * it.qty), 0);
    const shipping = calcShipping(subtotal);
    const total = subtotal + shipping;

    const order = {
      id: "ORD-" + Date.now(),
      createdAt: new Date().toISOString(),
      items: state.cart,
      subtotal, shipping, total,
      status: "Pending",
    };

    state.orders.unshift(order);
    writeJSON(KEY_ORDERS, state.orders);

    clearCart();
    setScreen("history");
  });

  // ---- History ----
  function renderOrders() {
    const wrap = $("#orderList");
    if (!wrap) return;

    if (!state.orders.length) {
      wrap.innerHTML = `<p class="text-[13px] text-slate-500 dark:text-slate-300">${t("history.empty","No orders yet.")}</p>`;
      return;
    }

    wrap.innerHTML = state.orders.map(o => {
      const dt = new Date(o.createdAt);
      const itemsCount = (o.items || []).reduce((s, it) => s + (Number(it.qty)||0), 0);

      return `
        <div class="rounded-2xl border border-slate-200 dark:border-slate-800 bg-slate-50 dark:bg-slate-950 p-3">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <p class="text-[13px] font-extrabold truncate">${escapeHtml(o.id)}</p>
              <p class="text-[12px] text-slate-500 dark:text-slate-300">${dt.toLocaleString()}</p>
            </div>
            <div class="text-right">
              <p class="text-[13px] font-extrabold">${money(o.total)}</p>
              <p class="text-[12px] text-slate-500 dark:text-slate-300">${escapeHtml(o.status || "—")}</p>
            </div>
          </div>
          <div class="mt-2 text-[12px] text-slate-500 dark:text-slate-300">
            Items: <span class="font-bold">${itemsCount}</span>
          </div>
        </div>
      `;
    }).join("");
  }

  // ---- Profile ----
  function loadProfile() {
    const p = readJSON(KEY_PROFILE, {});
    $("#profileName") && ($("#profileName").value = p.name || "");
    $("#profilePhone") && ($("#profilePhone").value = p.phone || "");
    $("#profileEmail") && ($("#profileEmail").value = p.email || "");
  }

  $("#btnSaveProfile")?.addEventListener("click", () => {
    const name  = $("#profileName")?.value || "";
    const phone = $("#profilePhone")?.value || "";
    const email = $("#profileEmail")?.value || "";
    writeJSON(KEY_PROFILE, { name, phone, email });
    alert(t("toast.saved","Saved ✅"));
  });

  $("#btnLogoutDemo")?.addEventListener("click", () => {
    // replace with real logout later
    alert("Logged out ✅ (demo)");
    setScreen("home");
  });

  function escapeHtml(s) {
    return String(s ?? "").replace(/[&<>"']/g, m => ({
      "&":"&amp;","<":"&lt;",">":"&gt;",'"':"&quot;","'":"&#39;"
    }[m]));
  }

  // ✅ Expose for debugging / if you still want inline onclick somewhere
  window.CustomerApp = { setScreen, addToCart };

  // ---- boot ----
  (async () => {
    updateBadge();

    // restore hash
    const hash = (location.hash || "#home").replace("#", "");
    const allowed = ["home","cart","history","profile"];
    state.screen = allowed.includes(hash) ? hash : "home";

    try {
      await loadCategories();
      await refreshItems();
    } catch (e) {
      console.error(e);
      // still show screen with empty state
      renderCategories();
      renderItems();
    }

    setScreen(state.screen);
  })();

  window.addEventListener("hashchange", () => {
    const h = (location.hash || "#home").replace("#","");
    if (["home","cart","history","profile"].includes(h)) setScreen(h);
  });
})();
</script>
@endpush
