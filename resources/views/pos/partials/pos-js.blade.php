<script>
(() => {
  const CFG = window.__POS_CONFIG__ || {};
  const EXCHANGE_RATE = Number(CFG.exchange_rate ?? 4100);
  const TAX_RATE      = Number(CFG.tax_rate ?? 10);
  const TAX_ENABLED   = (CFG.tax_enabled ?? true) === true;
  const KHR_SYMBOL    = String(CFG.currency_symbol ?? '៛');
  const DEFAULT_CURRENCY = String(CFG.currency_default ?? 'USD').toUpperCase();

  // ✅ Receipt settings (dynamic)
  const SHOP_NAME = String(CFG.shop_name ?? CFG.app_name ?? 'My POS');
  const SHOP_LOGO = String(CFG.shop_logo ?? CFG.app_logo ?? CFG.logo ?? '');
  // If you don't have a dedicated quote setting yet, reuse footer_note as quote
  const RECEIPT_QUOTE = String(CFG.receipt_quote ?? CFG.receipt_header_quote ?? '').trim();

  const RECEIPT_FOOTER_NOTE = String(CFG.receipt_footer_note ?? 'Thank you!').trim();

  // promo preview endpoint
  const PROMO_PREVIEW_URL = '/api/pos/discounts/preview';

  const state = {
    categories: [],
    activeCategoryId: 0,
    q: '',
    items: [],
    cart: [],

    exchangeRate: EXCHANGE_RATE,
    taxRate: TAX_RATE,
    taxEnabled: TAX_ENABLED,
    khrSymbol: KHR_SYMBOL,

    showCartPanel: true,
    payCurrencyInitialized: false,

    // Discount state
    discountMode: 'amount',         // amount | percent | code
    discountCurrency: 'KHR',        // for amount only (KHR|USD)
    discountAmountInput: 0,         // numeric (USD or KHR)
    discountPercent: 0,             // 0-100
    discountCode: '',               // promo code

    // Promo preview state
    promoStatusText: '—',
    promoDiscountKhr: 0,
    promoApplied: false,
    promoLoading: false,
    promoLastSubtotalKhr: null,
    promoLastCode: null,

    // customize state
    czMode: 'add',
    czRowKey: null,
    czItem: null,
    czVariant: null,
    czCustom: { ice:null, sugar:null, quick_note:null },
  };

  // DOM
  const contentGrid = document.getElementById('contentGrid');
  const elCatPills  = document.getElementById('catPills');
  const elGrid      = document.getElementById('productsGrid');
  const elEmpty     = document.getElementById('productsEmpty');
  const elSearch    = document.getElementById('searchInput');

  const btnMobileCart         = document.getElementById('btnMobileCart');
  const btnDesktopCartToggle  = document.getElementById('btnDesktopCartToggle');
  const badgeMobile           = document.getElementById('badgeMobile');
  const badgeDesktop          = document.getElementById('badgeDesktop');

  const mobileDrawer  = document.getElementById('mobileDrawer');
  const drawerOverlay = document.getElementById('drawerOverlay');
  const drawerPanel   = document.getElementById('drawerPanel');

  // dialogs
  const dlgCustomize = document.getElementById('dlgCustomize');
  const dlgPayment   = document.getElementById('dlgPayment');

  // receipt modal (we will NOT open it anymore)
  const dlgReceipt = document.getElementById('dlgReceipt');
  const rcptBody   = document.getElementById('rcptBody');
  const btnPrint   = document.getElementById('btnPrint');

  // customize modal fields
  const czTitle = document.getElementById('czTitle');
  const czQty   = document.getElementById('czQty');
  const czNote  = document.getElementById('czNote');

  const czPrice     = document.getElementById('czPrice');
  const czUnitPrice = document.getElementById('czUnitPrice');
  const czQtyText   = document.getElementById('czQtyText');
  const czSubtotal  = document.getElementById('czSubtotal');

  const czQtyDisplay = document.getElementById('czQtyDisplay');
  const czQtyDec     = document.getElementById('czQtyDec');
  const czQtyInc     = document.getElementById('czQtyInc');
  const btnCzSave    = document.getElementById('btnCzSave');

  const czVariantsWrap = document.getElementById('czVariantsWrap');
  const czVariants     = document.getElementById('czVariants');
  const czIce          = document.getElementById('czIce');
  const czSugar        = document.getElementById('czSugar');
  const czQuickNotes   = document.getElementById('czQuickNotes');

  // ✅ NEW: customize image elements
  const czImage = document.getElementById('czImage');
  const czImageFallback = document.getElementById('czImageFallback');

  // helpers
  const toast = (m,t='primary') => { try { window.showToast && showToast(m,t); } catch(_){} };
  const fmtUSD = (n) => (Number(n||0)).toLocaleString(undefined,{style:'currency',currency:'USD'});
  const fmtKHR = (n) => (Number(n||0)).toLocaleString() + ` ${state.khrSymbol || '៛'}`;
  const escapeHtml = (s) => String(s ?? '').replace(/[&<>"']/g, m => ({'&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'}[m]));

  // ✅ KHR rounding: nearest 100 (ends with 00)
  function roundKhrTo100(n) {
    return Math.round(Number(n || 0) / 100) * 100;
  }

  // ✅ Receipt helpers
  function normalizeLogoUrl(path) {
    const p = String(path || '').trim();
    if (!p) return '';
    if (p.startsWith('http://') || p.startsWith('https://')) return p;
    if (p.startsWith('/')) return p; // /storage/... or /logo.png
    return `/storage/${p.replace(/^public\//, '')}`;
  }
  function formatCustomizations(customizations) {
    const c = customizations || {};
    const out = [];
    if (c.ice) out.push(`Ice: ${c.ice}`);
    if (c.sugar) out.push(`Sugar: ${c.sugar}`);
    if (c.quick_note) out.push(String(c.quick_note));
    return out;
  }

  // dialogs helpers
  window.openDialog  = window.openDialog  || ((dlg) => dlg && dlg.showModal && dlg.showModal());
  window.closeDialog = window.closeDialog || ((dlg) => dlg && dlg.close && dlg.close());
  const openDialog  = window.openDialog;
  const closeDialog = window.closeDialog;

  // Close dialogs by [data-close="dlgId"]
  document.addEventListener('click', (e) => {
    const btn = e.target.closest('[data-close]');
    if (!btn) return;
    const id  = btn.getAttribute('data-close');
    const dlg = document.getElementById(id);
    if (dlg) closeDialog(dlg);
  });

  // =========================
  // RECEIPT PRINT FIX
  // - No receipt modal popup
  // - Only browser print dialog
  // =========================
  function ensurePrintHost() {
    let host = document.getElementById('receiptPrintHost');
    if (host) return host;

    host = document.createElement('div');
    host.id = 'receiptPrintHost';
    host.style.position = 'fixed';
    host.style.left = '-10000px';
    host.style.top = '0';
    host.style.background = '#fff';
    host.style.color = '#000';
    host.style.zIndex = '999999';
    document.body.appendChild(host);

    if (!document.getElementById('posPrintStyle')) {
      const style = document.createElement('style');
      style.id = 'posPrintStyle';
      style.textContent = `
        @media print {
          body.pos-printing * { visibility: hidden !important; }
          #receiptPrintHost, #receiptPrintHost * { visibility: visible !important; }
          #receiptPrintHost {
            position: fixed !important;
            left: 0 !important;
            top: 0 !important;
            right: 0 !important;
            width: 80mm !important;
            max-width: 80mm !important;
            margin: 0 auto !important;
            padding: 0 !important;
          }
        }
      `;
      document.head.appendChild(style);
    }

    return host;
  }

  function cleanupAfterPrint() {
    document.body.classList.remove('pos-printing');
    const host = document.getElementById('receiptPrintHost');
    if (host) host.innerHTML = '';
  }

  window.addEventListener('afterprint', cleanupAfterPrint);

  // if you still have a print button somewhere, it prints whatever is currently mounted
  btnPrint?.addEventListener('click', () => window.print());

  // ========= Desktop cart toggle =========
  const CART_HIDDEN_KEY = "posCartHidden";
  function applyDesktopCartVisibility() {
    if (contentGrid) contentGrid.classList.toggle('cart-hidden', !state.showCartPanel);
  }
  function initCartFromStorage() {
    const saved = localStorage.getItem(CART_HIDDEN_KEY);
    if (saved === "1") state.showCartPanel = false;
    applyDesktopCartVisibility();
  }
  function toggleCartDesktop() {
    state.showCartPanel = !state.showCartPanel;
    localStorage.setItem(CART_HIDDEN_KEY, state.showCartPanel ? "0" : "1");
    applyDesktopCartVisibility();
  }
  btnDesktopCartToggle?.addEventListener('click', toggleCartDesktop);

  // ========= Mobile drawer =========
  function openMobileCart() {
    if (!mobileDrawer || !drawerPanel) return;
    mobileDrawer.classList.remove('hidden');
    requestAnimationFrame(() => drawerPanel.classList.remove('translate-x-full'));
    document.body.style.overflow = 'hidden';
  }
  function closeMobileCart() {
    if (!mobileDrawer || !drawerPanel) return;
    drawerPanel.classList.add('translate-x-full');
    document.body.style.overflow = '';
    setTimeout(() => mobileDrawer.classList.add('hidden'), 200);
  }
  btnMobileCart?.addEventListener('click', openMobileCart);
  drawerOverlay?.addEventListener('click', closeMobileCart);
  window.addEventListener('resize', () => { if (window.innerWidth >= 768) closeMobileCart(); });

  document.addEventListener('click', (e) => {
    if (!e.target.closest('[data-cart-close]')) return;
    if (window.innerWidth < 768) {
      closeMobileCart();
    } else {
      state.showCartPanel = false;
      localStorage.setItem(CART_HIDDEN_KEY, "1");
      applyDesktopCartVisibility();
    }
  });

  // ========= Discounts on items (menu item discount fields) =========
  function nowInRange(starts, ends) {
    const now = Date.now();
    const s = starts ? Date.parse(starts) : null;
    const e = ends ? Date.parse(ends) : null;
    if (s && isNaN(s)) return false;
    if (e && isNaN(e)) return false;
    if (s && now < s) return false;
    if (e && now > e) return false;
    return true;
  }
  function applyDiscount(basePrice, type, value, starts, ends) {
    const price = Number(basePrice || 0);
    if (!type || value == null) return price;
    if (!nowInRange(starts, ends)) return price;
    const v = Number(value || 0);
    if (type === 'percent') return Math.max(0, price * (1 - (v/100)));
    if (type === 'fixed')   return Math.max(0, price - v);
    return price;
  }
  function effectiveUnitPrice(item, variant) {
    if (variant) {
      const base = Number(variant.price ?? item.price ?? 0);
      return applyDiscount(base, variant.discount_type, variant.discount_value, variant.discount_starts_at, variant.discount_ends_at);
    }
    const base = Number(item.price ?? 0);
    return applyDiscount(base, item.discount_type, item.discount_value, item.discount_starts_at, item.discount_ends_at);
  }

  // ========= Promo helpers =========
  function resetPromoState(msg = '—') {
    state.promoDiscountKhr = 0;
    state.promoApplied = false;
    state.promoLoading = false;
    state.promoLastSubtotalKhr = null;
    state.promoLastCode = null;
    state.promoStatusText = msg;
    if (payPromoStatus) payPromoStatus.textContent = msg;
  }

  let __promoTimer = null;

  // ========= Discount computation (manual + promo preview) =========
  function computedDiscountKhr(subtotalKhr) {
    const sub = Math.max(0, Number(subtotalKhr || 0));
    let d = 0;

    // promo code discount from preview endpoint
    if (state.discountMode === 'code') {
      if (!state.promoApplied) return 0;
      d = Number(state.promoDiscountKhr || 0);
      d = Math.max(0, Math.min(d, sub));
      return roundKhrTo100(d); // ✅ keep KHR ending with 00
    }

    if (state.discountMode === 'percent') {
      const p = Math.max(0, Math.min(100, Number(state.discountPercent || 0)));
      d = sub * (p / 100);
      d = Math.max(0, Math.min(d, sub));
      return roundKhrTo100(d); // ✅
    }

    // amount (KHR or USD)
    const amt = Math.max(0, Number(state.discountAmountInput || 0));
    const dKhr = (state.discountCurrency === 'USD')
      ? (amt * state.exchangeRate)
      : amt;

    d = Math.max(0, Math.min(dKhr, sub));
    return roundKhrTo100(d); // ✅
  }

  // ========= Totals =========
  function cartItemsCount() { return state.cart.reduce((s,r)=>s+(r.qty||0),0); }

  function calcTotals() {
    const subtotalUsd = state.cart.reduce((s,r)=> s + (r.unit_price * r.qty), 0);

    // ✅ KHR subtotal rounds to nearest 100
    const subtotalKhr = roundKhrTo100(subtotalUsd * state.exchangeRate);

    const discountKhr = computedDiscountKhr(subtotalKhr);
    const afterDiscountKhr = Math.max(0, subtotalKhr - discountKhr);

    // ✅ KHR tax rounds to nearest 100
    const taxKhr = state.taxEnabled
      ? roundKhrTo100(afterDiscountKhr * (Number(state.taxRate||0)/100))
      : 0;

    // ✅ KHR total rounds to nearest 100
    const totalKhr = roundKhrTo100(Math.max(0, afterDiscountKhr + taxKhr));

    return { subtotalUsd, subtotalKhr, discountKhr, taxKhr, totalKhr };
  }

  // Build discount payload for backend
  function buildDiscountPayload(totals) {
    const mode = state.discountMode;

    if (mode === 'amount') {
      return {
        discount_mode: 'amount',
        discount_amount: Number(state.discountAmountInput || 0),
        discount_currency: String(state.discountCurrency || 'KHR'),
        discount_khr: totals.discountKhr,
      };
    }

    if (mode === 'percent') {
      return {
        discount_mode: 'percent',
        discount_percent: Number(state.discountPercent || 0),
        discount_khr: totals.discountKhr,
      };
    }

    if (mode === 'code') {
      return {
        discount_mode: 'code',
        discount_code: String(state.discountCode || '').trim(),
        // optional for logging/debug; backend re-validates anyway
        discount_khr: totals.discountKhr,
      };
    }

    return { discount_mode: null, discount_khr: 0 };
  }

  // =========================
  // PAYMENT MODAL
  // =========================
  const payExchange = document.getElementById('payExchange');
  const payTax      = document.getElementById('payTax');

  const payCurrencyUSD = document.getElementById('payCurrencyUSD');
  const payCurrencyKHR = document.getElementById('payCurrencyKHR');

  const payTenderUSD = document.getElementById('payTenderUSD');
  const payTenderKHR = document.getElementById('payTenderKHR');

  const payDueKhr   = document.getElementById('payDueKhr');
  const payDueUsd   = document.getElementById('payDueUsd');
  const payChange   = document.getElementById('payChange');
  const payMaxHint  = document.getElementById('payMaxHint');
  const btnPayNow   = document.getElementById('btnPayNow');

  const paySumSubtotalUsd = document.getElementById('paySumSubtotalUsd');
  const paySumSubtotalKhr = document.getElementById('paySumSubtotalKhr');
  const paySumDiscountKhr = document.getElementById('paySumDiscountKhr');
  const paySumTaxKhr      = document.getElementById('paySumTaxKhr');
  const paySumTotalKhr    = document.getElementById('paySumTotalKhr');
  const payCartSummaryList= document.getElementById('payCartSummaryList');

  // discount modal DOM
  const discountModeBtns = Array.from(document.querySelectorAll('[data-discount-mode]'));
  const boxAmount  = document.querySelector('[data-discount-amount]');
  const boxPercent = document.querySelector('[data-discount-percent]');
  const boxCode    = document.querySelector('[data-discount-code]');

  const payDiscountCurUSD      = document.getElementById('payDiscountCurUSD');
  const payDiscountCurKHR      = document.getElementById('payDiscountCurKHR');
  const payDiscountAmount      = document.getElementById('payDiscountAmount');
  const payDiscountPercent     = document.getElementById('payDiscountPercent');
  const payDiscountCode        = document.getElementById('payDiscountCode');
  const btnValidatePromo       = document.getElementById('btnValidatePromo');
  const payPromoStatus         = document.getElementById('payPromoStatus');
  const payDiscountComputedKhr = document.getElementById('payDiscountComputedKhr');
  const payDiscountComputedKhr2= document.getElementById('payDiscountComputedKhr2');

  function selectedCurrency() {
    return (payCurrencyKHR && payCurrencyKHR.checked) ? 'KHR' : 'USD';
  }
  function maxTender() {
    const t = calcTotals();
    const cur = selectedCurrency();
    if (cur === 'USD') {
      const dueUsd = t.totalKhr / state.exchangeRate;
      return Math.max(100, 2 * dueUsd);
    }
    // max tender KHR should also follow ending with 00 for nicer keypad usage
    return roundKhrTo100(Math.max(100000, 2 * t.totalKhr));
  }
  function tenderedKhr() {
    const cur = selectedCurrency();
    if (cur === 'KHR') return Math.max(0, parseInt(payTenderKHR?.value || '0', 10) || 0);
    const usd = Math.max(0, Number(payTenderUSD?.value || 0));
    return Math.round(usd * state.exchangeRate);
  }

  function updateChangeAndButton() {
    if (!btnPayNow) return;

    const t = calcTotals();
    const cur = selectedCurrency();
    const max = maxTender();

    if (payMaxHint) payMaxHint.textContent = (cur === 'USD') ? `Max tender: ${fmtUSD(max)}` : `Max tender: ${fmtKHR(max)}`;

    const changeKhr = Math.max(0, tenderedKhr() - t.totalKhr);
    if (payChange) {
      payChange.textContent = (cur === 'USD')
        ? `Change: ${fmtUSD(changeKhr / state.exchangeRate)} (${fmtKHR(changeKhr)})`
        : `Change: ${fmtKHR(changeKhr)}`;
    }

    const tender = (cur === 'KHR')
      ? (parseInt(payTenderKHR?.value || '0', 10) || 0)
      : (Number(payTenderUSD?.value || 0));

    const isPromoMode  = (state.discountMode === 'code');
    const hasPromoCode = String(state.discountCode || '').trim() !== '';
    const promoOk      = !isPromoMode || (hasPromoCode && state.promoApplied && !state.promoLoading);

    const ok = state.cart.length > 0
      && promoOk
      && tender > 0
      && tender <= max
      && tenderedKhr() >= t.totalKhr;

    btnPayNow.disabled = !ok;
  }

  // ✅ Update ONLY this part in pos-js: setDiscountMode() button styling
  function setDiscountMode(mode) {
    state.discountMode = mode;

    if (mode !== 'code') resetPromoState('—');

    boxAmount?.classList.toggle('hidden', mode !== 'amount');
    boxPercent?.classList.toggle('hidden', mode !== 'percent');
    boxCode?.classList.toggle('hidden', mode !== 'code');

    discountModeBtns.forEach(btn => {
      const active = btn.getAttribute('data-discount-mode') === mode;

      // reset first (clean slate)
      btn.classList.remove(
        'bg-purple-600','text-white','font-semibold',
        'bg-white/5','hover:bg-white/10','text-slate-200',
        'bg-slate-200/70','hover:bg-slate-200/90','text-slate-800',
        'ring-2','ring-purple-500/40'
      );

      if (active) {
        // ✅ active: strong purple + ring
        btn.classList.add('bg-purple-600','text-white','font-semibold','ring-2','ring-purple-500/40');
      } else {
        // ✅ inactive: looks good in BOTH light/dark
        btn.classList.add(
          'bg-slate-200/70','hover:bg-slate-200/90','text-slate-800',
          'dark:bg-white/5','dark:hover:bg-white/10','dark:text-slate-200'
        );
      }
    });

    syncPaymentUI();
    renderCart();
    schedulePromoPreview();
  }

  // Clear discount
  document.addEventListener('click', (e) => {
    const clr = e.target.closest('[data-discount-clear]');
    if (!clr) return;

    state.discountAmountInput = 0;
    state.discountPercent = 0;
    state.discountCode = '';
    resetPromoState('—');

    if (payDiscountAmount)  payDiscountAmount.value  = '0';
    if (payDiscountPercent) payDiscountPercent.value = '0';
    if (payDiscountCode)    payDiscountCode.value    = '';
    if (payPromoStatus)     payPromoStatus.textContent = '—';

    syncPaymentUI();
    renderCart();
  });

  // Mode buttons
  discountModeBtns.forEach(btn => {
    btn.addEventListener('click', () => setDiscountMode(btn.getAttribute('data-discount-mode')));
  });

  // Currency toggle for amount mode
  function syncDiscountCurrencyFromUI() {
    state.discountCurrency = (payDiscountCurUSD?.checked) ? 'USD' : 'KHR';
  }
  payDiscountCurUSD?.addEventListener('change', () => { syncDiscountCurrencyFromUI(); syncPaymentUI(); renderCart(); });
  payDiscountCurKHR?.addEventListener('change', () => { syncDiscountCurrencyFromUI(); syncPaymentUI(); renderCart(); });

  // Amount input
  payDiscountAmount?.addEventListener('input', () => {
    state.discountAmountInput = Math.max(0, Number(payDiscountAmount.value || 0));
    syncPaymentUI();
    renderCart();
  });

  // Percent input
  payDiscountPercent?.addEventListener('input', () => {
    const v = Number(payDiscountPercent.value || 0);
    state.discountPercent = Math.max(0, Math.min(100, v));
    syncPaymentUI();
    renderCart();
  });

  // Promo input typing invalidates previous preview and updates totals (discount becomes 0)
  payDiscountCode?.addEventListener('input', () => {
    if (state.discountMode !== 'code') return;

    state.discountCode = String(payDiscountCode.value || '').trim().toUpperCase();
    state.promoApplied = false;
    state.promoDiscountKhr = 0;
    state.promoStatusText = state.discountCode ? 'Press Apply to validate' : '—';
    if (payPromoStatus) payPromoStatus.textContent = state.promoStatusText;

    syncPaymentUI();
    renderCart();
  });

  async function previewPromo(code, subtotalKhr, { silent = false } = {}) {
    const c = String(code || '').trim().toUpperCase();
    const sub = Math.max(0, roundKhrTo100(Number(subtotalKhr || 0))); // ✅ align with rounding

    if (!c) {
      resetPromoState('—');
      return { valid: false };
    }

    state.promoLoading = true;
    state.promoStatusText = 'Checking...';
    if (payPromoStatus) payPromoStatus.textContent = state.promoStatusText;
    updateChangeAndButton();

    try {
      const res = await api(PROMO_PREVIEW_URL, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ discount_code: c, subtotal_khr: sub }),
      });

      // normalize wrapper shapes
      const payload =
        (res && res.valid !== undefined) ? res :
        (res && res.data && res.data.valid !== undefined) ? res.data :
        (res && res.data && res.data.data && res.data.data.valid !== undefined) ? res.data.data :
        (res && res.data) ? res.data :
        res;

      const d0 = Number(payload?.discount_khr || 0);
      const ok = !!payload?.valid;

      const d = ok ? roundKhrTo100(Math.max(0, d0)) : 0; // ✅ end with 00

      state.promoDiscountKhr = d;
      state.promoApplied = ok;
      state.promoLastSubtotalKhr = sub;
      state.promoLastCode = c;

      const msg = ok
        ? `Applied: ${c} (−${fmtKHR(state.promoDiscountKhr)})`
        : (payload?.message || 'Invalid promo code');

      state.promoStatusText = msg;
      if (payPromoStatus) payPromoStatus.textContent = msg;

      if (!ok && !silent) toast(msg, 'danger');

      return { valid: ok, discount_khr: state.promoDiscountKhr };
    } catch (e) {
      state.promoDiscountKhr = 0;
      state.promoApplied = false;
      state.promoLastSubtotalKhr = sub;
      state.promoLastCode = c;

      const msg = e?.data?.message || e?.message || 'Invalid promo code';
      state.promoStatusText = msg;
      if (payPromoStatus) payPromoStatus.textContent = msg;

      if (!silent) toast(msg, 'danger');
      return { valid: false };
    } finally {
      state.promoLoading = false;
      syncPaymentUI();
      renderCart();
    }
  }

  function schedulePromoPreview() {
    if (state.discountMode !== 'code') return;
    if (!state.promoApplied) return; // only auto re-check after Apply success
    if (state.promoLoading) return;

    const code = String(state.discountCode || '').trim();
    if (!code) return;

    const totals = calcTotals();
    const sub = totals.subtotalKhr;

    if (state.promoLastCode === code.toUpperCase() && state.promoLastSubtotalKhr === sub) return;

    clearTimeout(__promoTimer);
    __promoTimer = setTimeout(() => previewPromo(code, sub, { silent: true }), 300);
  }

  // Promo Apply
  btnValidatePromo?.addEventListener('click', async () => {
    const code = String(payDiscountCode?.value || '').trim().toUpperCase();

    if (!code) {
      resetPromoState('—');
      toast('Please enter promo code', 'warning');
      return;
    }

    setDiscountMode('code');
    state.discountCode = code;

    state.promoApplied = false;
    state.promoDiscountKhr = 0;

    const totals = calcTotals();
    await previewPromo(code, totals.subtotalKhr);
  });

  function syncPaymentUI() {
    const t = calcTotals();

    if (!state.payCurrencyInitialized) {
      if (DEFAULT_CURRENCY === 'KHR') {
        if (payCurrencyKHR) payCurrencyKHR.checked = true;
      } else {
        if (payCurrencyUSD) payCurrencyUSD.checked = true;
      }
      state.payCurrencyInitialized = true;
    }

    const payTaxRowTop     = document.getElementById('payTaxRowTop');
    const payTaxRowSummary = document.getElementById('payTaxRowSummary');
    const payKhrSymbol     = document.getElementById('payKhrSymbol');

    if (payKhrSymbol) payKhrSymbol.textContent = state.khrSymbol || '៛';

    if (payTaxRowTop)     payTaxRowTop.classList.toggle('hidden', !state.taxEnabled);
    if (payTaxRowSummary) payTaxRowSummary.classList.toggle('hidden', !state.taxEnabled);

    if (payExchange) payExchange.textContent = String(state.exchangeRate);

    const showTaxRate = state.taxEnabled ? state.taxRate : 0;
    if (payTax) payTax.textContent = String(showTaxRate);
    const payTaxInline = document.getElementById('payTaxInline');
    if (payTaxInline) payTaxInline.textContent = String(showTaxRate);

    // computed discount label (includes promo because computedDiscountKhr reads promo state)
    const dKhr = computedDiscountKhr(t.subtotalKhr);
    if (payDiscountComputedKhr)  payDiscountComputedKhr.textContent  = fmtKHR(dKhr);
    if (payDiscountComputedKhr2) payDiscountComputedKhr2.textContent = fmtKHR(dKhr);

    if (payPromoStatus) payPromoStatus.textContent = state.promoStatusText || '—';

    if (payDueKhr) payDueKhr.textContent = fmtKHR(t.totalKhr);
    if (payDueUsd) payDueUsd.textContent = fmtUSD(t.totalKhr / state.exchangeRate);

    if (paySumSubtotalUsd) paySumSubtotalUsd.textContent = fmtUSD(t.subtotalUsd);
    if (paySumSubtotalKhr) paySumSubtotalKhr.textContent = fmtKHR(t.subtotalKhr);
    if (paySumDiscountKhr) paySumDiscountKhr.textContent = fmtKHR(t.discountKhr);
    if (paySumTaxKhr)      paySumTaxKhr.textContent      = fmtKHR(t.taxKhr);
    if (paySumTotalKhr)    paySumTotalKhr.textContent    = fmtKHR(t.totalKhr);

    if (payCartSummaryList) {
      payCartSummaryList.innerHTML = state.cart.map(r => `
        <div class="flex items-center justify-between gap-2 text-sm rounded-2xl p-3
                    bg-white/60 dark:bg-slate-950/50
                    border border-white/40 dark:border-slate-800/80">
          <div class="min-w-0">
            <div class="font-medium truncate">${escapeHtml(r.name || '—')}</div>
            <div class="text-xs text-slate-500 dark:text-slate-400">
              ${escapeHtml(r.variant_name || '—')} • ${r.qty} × ${fmtUSD(r.unit_price)}
            </div>
          </div>
          <div class="font-semibold tabular-nums">${fmtUSD(r.unit_price * r.qty)}</div>
        </div>
      `).join('');
    }

    document.querySelectorAll('[data-tender-usd]').forEach(el => el.classList.toggle('hidden', selectedCurrency() !== 'USD'));
    document.querySelectorAll('[data-tender-khr]').forEach(el => el.classList.toggle('hidden', selectedCurrency() !== 'KHR'));

    updateChangeAndButton();
  }

  // ========= Receipt builder (mount into modal OR print host) =========
  // ========= Receipt builder (mount into modal OR print host) =========
function buildReceipt(order, cartSnapshot, paySnap, mountEl) {
  const host = mountEl || rcptBody;
  if (!host) return;

  // ---- build items (order->items or snapshot) ----
  const items = Array.isArray(order?.items) && order.items.length
    ? order.items
    : (Array.isArray(cartSnapshot)
      ? cartSnapshot.map(r => ({
          quantity: r.qty,
          price: r.unit_price,
          subtotal: r.unit_price * r.qty,
          menu_item: { name: r.name },
          menu_item_variant: r.variant_name ? { name: r.variant_name } : null,
          note: r.note || null,
          customizations: r.customizations || null,
        }))
      : []);

  const code = order.order_code || ('#' + order.id);
  const time = order.created_at
    ? new Date(order.created_at).toLocaleString()
    : new Date().toLocaleString();

  const payCur = (paySnap?.currency || 'KHR').toUpperCase();

  // ---- totals are stored in KHR on backend ----
  const exchangeRate = Number(state.exchangeRate || 0);
  const subtotalKhr  = Number(order.subtotal_khr || 0);
  const discountKhr  = Number(order.discount_khr || 0);
  const taxKhr       = Number(order.tax_khr || 0);
  const totalKhr     = Number(order.total_khr || 0);

  // convert to USD for display (Vue-style)
  const k2u = (k) => exchangeRate ? (Number(k || 0) / exchangeRate) : 0;
  const subtotalUsd = k2u(subtotalKhr);
  const discountUsd = k2u(discountKhr);
  const taxUsd      = k2u(taxKhr);
  const totalUsd    = k2u(totalKhr);

  // ---- payment / change (in KHR) ----
  const tenderKhr = (payCur === 'USD')
    ? Math.round(Number(paySnap?.tendered_usd || 0) * exchangeRate)
    : (parseInt(paySnap?.tendered_khr || 0, 10) || 0);

  const changeKhr = Math.max(0, tenderKhr - totalKhr);

  // ---- receipt config ----
  const logoUrl = normalizeLogoUrl(SHOP_LOGO);
  const shopName = String(SHOP_NAME || '').trim() || 'My POS';
  const footer = String(RECEIPT_FOOTER_NOTE || '').trim() || 'Thank you!';

  // ---- helpers ----
  const fmtMoney = (v) =>
    (Number(v || 0)).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

  const fmtUSDPlain = (v) => `$${fmtMoney(v)}`;

  // ---- build line items (Vue-like layout) ----
  const lines = items.map(it => {
    const name = it.menu_item?.name || it.menu_item_name || 'Item';
    const variant = it.menu_item_variant?.name || it.variant_name || '';

    const qty = Number(it.quantity || 0);
    const unit = Number(it.price || 0);                 // USD unit price
    const lineTotalUsd = Number(it.subtotal || (unit * qty));

    const czLines = formatCustomizations(it.customizations); // array of strings
    const note = String(it.note || '').trim();

    return `
      <div style="font-size:13px; margin-bottom:6px;">
        <div style="display:flex; justify-content:space-between; gap:8px;">
          <span style="width:42px;">x${qty}</span>

          <span style="
            flex:1;
            text-align:left;
            overflow:hidden;
            text-overflow:ellipsis;
            white-space:nowrap;
            max-width:120px;
          ">
            ${escapeHtml(name)}
          </span>

          <span style="width:90px; text-align:right; font-variant-numeric: tabular-nums;">
            ${escapeHtml(fmtUSDPlain(lineTotalUsd))}
          </span>
        </div>

        <div style="display:flex; justify-content:space-between; padding-left:8px; margin-top:2px; font-size:12px; color:#6b7280;">
          <span style="font-style:italic;">@ ${escapeHtml(fmtUSDPlain(unit))}</span>
          <span>Subtotal</span>
        </div>

        ${variant ? `
          <div style="padding-left:8px; margin-top:2px; font-size:12px; color:#6d28d9;">
            Variant: ${escapeHtml(variant)}
          </div>
        ` : ''}

        ${czLines.length ? `
          <div style="padding-left:8px; margin-top:2px; font-size:12px; color:#6d28d9;">
            ${czLines.map(s => escapeHtml(s)).join(', ')}
          </div>
        ` : ''}

        ${note ? `
          <div style="padding-left:8px; margin-top:2px; font-size:12px; color:#6b7280; font-style:italic;">
            Note: ${escapeHtml(note)}
          </div>
        ` : ''}
      </div>
    `;
  }).join('');

  // ---- output HTML ----
  host.innerHTML = `
    <div id="receiptPrintArea"
         style="
          width:80mm; max-width:80mm;
          margin:0 auto;
          background:#fff; color:#000;
          font-family:'Kantumruy Pro','Noto Sans Khmer','Khmer OS Battambang','Inter',ui-sans-serif,system-ui,-apple-system,'Segoe UI',Roboto,Arial;
          line-height:1.6;
          font-variant-numeric: tabular-nums;
          padding:16px;
          font-size:13px;
         ">

      <!-- Logo + name -->
      <div style="text-align:center; margin-bottom:8px;">
        ${logoUrl ? `
          <img src="${escapeHtml(logoUrl)}"
               alt="Logo"
               style="display:block; margin:0 auto 6px; height:48px; object-fit:contain;"
               onerror="this.style.display='none';" />
        ` : ''}

        <div style="font-size:18px; font-weight:800; letter-spacing:0.5px; text-transform:uppercase;">
          ${escapeHtml(shopName)}
        </div>

        <!-- tagline (change this text if you want) -->
        <div style="font-size:12px; color:#6b7280;">
          Fresh. Fast. Friendly.
        </div>
      </div>

      <!-- Order info -->
      <div style="text-align:center; font-size:12px; color:#6b7280; margin-bottom:8px;">
        <div>Order: ${escapeHtml(code)}</div>
        <div>${escapeHtml(time)}</div>
      </div>

      <!-- Header -->
      <div style="border-top:1px solid #d1d5db; border-bottom:1px solid #d1d5db; padding:6px 0; font-weight:700; display:flex; justify-content:space-between;">
        <span style="width:42px;">Qty</span>
        <span style="flex:1;">Item</span>
        <span style="width:90px; text-align:right;">Amount</span>
      </div>

      <!-- Lines -->
      <div style="margin-top:6px;">
        ${lines || `<div style="color:#6b7280; text-align:center; padding:10px 0;">No items</div>`}
      </div>

      <hr style="border:0; border-top:1px dashed #9ca3af; margin:10px 0;" />

      <!-- Totals (Vue-style USD, plus KHR lines) -->
      <div style="display:flex; justify-content:space-between; font-size:13px;">
        <span>Subtotal</span>
        <span>${escapeHtml(fmtUSDPlain(subtotalUsd))} USD</span>
      </div>

      ${discountKhr > 0 ? `
        <div style="display:flex; justify-content:space-between; font-size:13px; color:#16a34a;">
          <span>Discount</span>
          <span>- ${escapeHtml(fmtUSDPlain(discountUsd))} USD</span>
        </div>
      ` : ''}

      ${taxKhr > 0 ? `
        <div style="display:flex; justify-content:space-between; font-size:13px;">
          <span>Tax</span>
          <span>${escapeHtml(fmtUSDPlain(taxUsd))} USD</span>
        </div>
      ` : ''}

      ${exchangeRate ? `
        <div style="display:flex; justify-content:space-between; font-size:13px;">
          <span>Exchange Rate</span>
          <span>1 USD = ${escapeHtml(exchangeRate.toLocaleString())} ${escapeHtml(state.khrSymbol || '៛')}</span>
        </div>
      ` : ''}

      <div style="display:flex; justify-content:space-between; font-size:13px;">
        <span>Total in KHR</span>
        <span>${escapeHtml(totalKhr.toLocaleString())} ${escapeHtml(state.khrSymbol || '៛')}</span>
      </div>

      <div style="display:flex; justify-content:space-between; font-weight:800; font-size:15px; border-top:1px solid #000; margin-top:10px; padding-top:10px;">
        <span>Total</span>
        <span>${escapeHtml(fmtUSDPlain(totalUsd))} USD</span>
      </div>

      <!-- Paid / Change (optional) -->
      <div style="margin-top:10px; font-size:13px;">
        <div style="display:flex; justify-content:space-between;">
          <span>Paid (${escapeHtml(payCur)})</span>
          <span>
            ${payCur === 'USD'
              ? escapeHtml(fmtUSDPlain(paySnap?.tendered_usd || 0))
              : escapeHtml(fmtKHR(paySnap?.tendered_khr || 0))}
          </span>
        </div>

        <div style="display:flex; justify-content:space-between; font-weight:700;">
          <span>Change</span>
          <span>
            ${payCur === 'USD'
              ? `${escapeHtml(fmtUSDPlain(changeKhr / exchangeRate))} (${escapeHtml(fmtKHR(changeKhr))})`
              : `${escapeHtml(fmtKHR(changeKhr))}`
            }
          </span>
        </div>
      </div>

      <!-- Footer -->
      <div style="margin-top:14px; font-size:12px; text-align:center; color:#374151;">
        <div>Pay by: <span style="text-transform:capitalize;">cash</span></div>
        <div>Cashier: —</div>
      </div>

      <div style="margin-top:10px; font-size:12px; text-align:center; font-weight:600;">
        ${escapeHtml(footer)} ❤️
      </div>

    </div>
  `;
}


  // open payment modal
  document.addEventListener('click', (e) => {
    const checkoutBtn = e.target.closest('[data-cart-checkout]');
    if (!checkoutBtn) return;
    if (!state.cart.length) return;

    // default discount currency UI
    if (payDiscountCurKHR && payDiscountCurUSD && !payDiscountCurKHR.checked && !payDiscountCurUSD.checked) {
      payDiscountCurKHR.checked = true;
      state.discountCurrency = 'KHR';
    }

    syncPaymentUI();
    openDialog(dlgPayment);
  });

  payCurrencyUSD?.addEventListener('change', syncPaymentUI);
  payCurrencyKHR?.addEventListener('change', syncPaymentUI);
  payTenderUSD?.addEventListener('input', updateChangeAndButton);
  payTenderKHR?.addEventListener('input', updateChangeAndButton);

  // PAY NOW
  btnPayNow?.addEventListener('click', async () => {
    const cartSnap = JSON.parse(JSON.stringify(state.cart));
    const cur = selectedCurrency();

    const paySnap = {
      currency: cur,
      tendered_usd: Number(payTenderUSD?.value || 0),
      tendered_khr: parseInt(payTenderKHR?.value || '0', 10) || 0,
      exchange_rate: state.exchangeRate,
    };

    try {
      const max = maxTender();

      if (cur === 'USD') {
        const usd = Number(payTenderUSD?.value || 0);
        if (usd > max) return toast('Tendered amount exceeds max allowed.', 'danger');
      } else {
        const khr = parseInt(payTenderKHR?.value || '0', 10) || 0;
        if (khr > max) return toast('Tendered amount exceeds max allowed.', 'danger');
      }

      const totals = calcTotals();
      const discountPayload = buildDiscountPayload(totals);

      const orderPayload = {
        items: state.cart.map(r => ({
          menu_item_id: r.menu_item_id,
          menu_item_variant_id: r.menu_item_variant_id,
          quantity: r.qty,
          unit_price: r.unit_price,
          customizations: r.customizations || null,
          note: r.note || null,
        })),
        tax_rate: state.taxEnabled ? state.taxRate : 0,
        ...discountPayload,
      };

      const created = await api('/api/pos/orders', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(orderPayload)
      });

      const order = created?.order;
      if (!order?.id) throw new Error('Order create failed.');

      const paymentPayload = { method: 'cash', currency: cur };
      if (cur === 'KHR') {
        paymentPayload.tendered_khr = parseInt(payTenderKHR?.value || '0', 10) || 0;
      } else {
        paymentPayload.tendered_usd = Number(payTenderUSD?.value || 0);
      }

      const paid = await api(`/api/pos/orders/${order.id}/payments`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(paymentPayload)
      });

      const finalOrder = paid?.order || order;

      // ✅ Print receipt WITHOUT opening receipt modal
      const printHost = ensurePrintHost();
      printHost.innerHTML = '';
      buildReceipt(finalOrder, cartSnap, paySnap, printHost);

      closeDialog(dlgPayment);

      document.body.classList.add('pos-printing');
      requestAnimationFrame(() => setTimeout(() => window.print(), 80));

      // reset
      state.cart = [];
      state.discountMode = 'amount';
      state.discountCurrency = 'KHR';
      state.discountAmountInput = 0;
      state.discountPercent = 0;
      state.discountCode = '';
      resetPromoState('—');

      if (payTenderUSD) payTenderUSD.value = '';
      if (payTenderKHR) payTenderKHR.value = '';

      if (payDiscountAmount)  payDiscountAmount.value  = '0';
      if (payDiscountPercent) payDiscountPercent.value = '0';
      if (payDiscountCode)    payDiscountCode.value    = '';
      if (payPromoStatus)     payPromoStatus.textContent = '—';

      setDiscountMode('amount');
      renderCart();

      toast('Payment successful', 'success');
    } catch (e) {
      console.error(e);
      toast(e?.data?.message || e?.message || 'Payment failed', 'danger');
    }
  });

  // ========= Cart key + merge =========
  function cartKey(row) {
    const sig = JSON.stringify({
      item: row.menu_item_id,
      variant: row.menu_item_variant_id || 0,
      unit_price: Number(row.unit_price || 0),
      customizations: row.customizations || null,
      note: row.note || '',
    });
    return btoa(unescape(encodeURIComponent(sig))).slice(0, 32);
  }

  function mergeRow(row) {
    row.key = cartKey(row);
    const existing = state.cart.find(x => x.key === row.key);
    if (existing) existing.qty += row.qty;
    else state.cart.push(row);
  }

  function removeFromCart(key) {
    state.cart = state.cart.filter(x => x.key !== key);
    renderCart();
  }
  function setQty(key, qty) {
    qty = Math.max(1, Number(qty || 1));
    const row = state.cart.find(x => x.key === key);
    if (!row) return;
    row.qty = qty;
    renderCart();
  }

  function applyEditRow(origRow, newRow) {
    const origQty = origRow.qty;
    const newQty  = newRow.qty;

    state.cart = state.cart.filter(x => x.key !== origRow.key);

    if (newQty >= origQty) {
      mergeRow(newRow);
    } else {
      mergeRow({ ...origRow, qty: origQty - newQty });
      mergeRow(newRow);
    }
  }

  // ========= Render cart into BOTH cart + mcart =========
  function setBadges() {
    const n = cartItemsCount();
    if (badgeMobile)  badgeMobile.textContent  = n;
    if (badgeDesktop) badgeDesktop.textContent = n;

    const a = document.getElementById('cart_cartCount');
    const b = document.getElementById('mcart_cartCount');
    if (a) a.textContent = n;
    if (b) b.textContent = n;
  }

  function setTotalsFor(prefix, totals) {
    const set = (id, text) => {
      const el = document.getElementById(`${prefix}_${id}`);
      if (el) el.textContent = text;
    };
    set('sumSubtotalUsd', fmtUSD(totals.subtotalUsd));
    set('sumSubtotalKhr', fmtKHR(totals.subtotalKhr));
    set('sumDiscountKhr', fmtKHR(totals.discountKhr));
    set('sumTaxKhr',      fmtKHR(totals.taxKhr));
    set('sumTotalKhr',    fmtKHR(totals.totalKhr));
  }

  function renderCart() {
    setBadges();
    const tpl = document.getElementById('tplCartItem');
    const totals = calcTotals();

    ['cart','mcart'].forEach((p) => {
      const list = document.getElementById(`${p}_cartList`);
      if (!list || !tpl) return;

      list.innerHTML = '';

      if (state.cart.length === 0) {
        list.innerHTML = `
          <div class="text-sm text-slate-500 dark:text-slate-400 text-center py-10">
            Cart is empty.
          </div>
        `;
        setTotalsFor(p, totals);
        return;
      }

      state.cart.forEach((r) => {
        const node = tpl.content.firstElementChild.cloneNode(true);

        const nameEl = node.querySelector('[data-ci-name]');
        const metaEl = node.querySelector('[data-ci-meta]');
        const lineEl = node.querySelector('[data-ci-line]');

        if (nameEl) nameEl.textContent = r.name || '—';
        if (metaEl) metaEl.textContent = `${r.variant_name || '—'} • ${fmtUSD(r.unit_price)}`;
        if (lineEl) lineEl.textContent = fmtUSD(r.unit_price * r.qty);

        const tagsWrap = node.querySelector('[data-ci-tags]');
        if (tagsWrap) {
          tagsWrap.innerHTML = '';
          const tags = [];
          if (r.customizations?.ice) tags.push(r.customizations.ice);
          if (r.customizations?.sugar) tags.push('Sugar ' + r.customizations.sugar);
          if (r.customizations?.quick_note) tags.push(r.customizations.quick_note);
          tags.forEach(t => {
            const chip = document.createElement('span');
            chip.className = 'px-2 py-0.5 rounded-full text-[11px] bg-slate-200/70 dark:bg-slate-800 text-slate-700 dark:text-slate-200';
            chip.textContent = t;
            tagsWrap.appendChild(chip);
          });
        }

        const noteEl = node.querySelector('[data-ci-note]');
        if (noteEl) {
          if (r.note) {
            noteEl.classList.remove('hidden');
            noteEl.textContent = `Note: ${r.note}`;
          } else {
            noteEl.classList.add('hidden');
          }
        }

        const rmBtn  = node.querySelector('[data-ci-rm]');
        const decBtn = node.querySelector('[data-ci-dec]');
        const incBtn = node.querySelector('[data-ci-inc]');
        const qtyInp = node.querySelector('[data-ci-qty]');
        const editBtn= node.querySelector('[data-ci-edit]');

        rmBtn?.addEventListener('click', () => removeFromCart(r.key));
        decBtn?.addEventListener('click', () => setQty(r.key, Math.max(1, r.qty - 1)));
        incBtn?.addEventListener('click', () => setQty(r.key, r.qty + 1));

        if (qtyInp) {
          qtyInp.value = r.qty;
          qtyInp.addEventListener('change', () => setQty(r.key, qtyInp.value));
        }

        editBtn?.addEventListener('click', () => openEditRow(r.key));

        list.appendChild(node);
      });

      setTotalsFor(p, totals);
    });

    // if promo already applied, re-preview when cart subtotal changes
    schedulePromoPreview();
  }

  // ========= Cart buttons: Clear =========
  document.addEventListener('click', (e) => {
    const clearBtn = e.target.closest('[data-cart-clear]');
    if (clearBtn) {
      state.cart = [];

      // reset discount
      state.discountMode = 'amount';
      state.discountCurrency = 'KHR';
      state.discountAmountInput = 0;
      state.discountPercent = 0;
      state.discountCode = '';
      resetPromoState('—');
      setDiscountMode('amount');

      renderCart();
      toast('Cart cleared', 'warning');
      return;
    }
  });

  // ========= Categories =========
  function pillClass(active) {
    return [
      'px-4 py-1 rounded-full text-sm whitespace-nowrap transition-all',
      active
        ? 'bg-purple-100 dark:bg-purple-900 text-purple-700 dark:text-purple-300 font-semibold'
        : 'hover:bg-slate-200/60 dark:hover:bg-slate-800 text-slate-700 dark:text-slate-300'
    ].join(' ');
  }

  async function loadCategories() {
    const cats = await api('/api/categories');
    state.categories = Array.isArray(cats?.data) ? cats.data : (Array.isArray(cats) ? cats : []);
    state.activeCategoryId = 0;
  }

  function renderCategories() {
    if (!elCatPills) return;
    elCatPills.innerHTML = '';

    const allBtn = document.createElement('button');
    allBtn.className = pillClass(state.activeCategoryId === 0);
    allBtn.textContent = 'All';
    allBtn.addEventListener('click', () => { state.activeCategoryId = 0; refreshItems(); });
    elCatPills.appendChild(allBtn);

    state.categories.forEach((c) => {
      const b = document.createElement('button');
      b.className = pillClass(state.activeCategoryId === c.id);
      b.textContent = c.name || `#${c.id}`;
      b.addEventListener('click', () => { state.activeCategoryId = c.id; refreshItems(); });
      elCatPills.appendChild(b);
    });
  }

  // ========= Items =========
  async function refreshItems() {
    renderCategories();

    const params = new URLSearchParams();
    params.set('per_page', '100');
    params.set('visible_only', '1');
    params.set('include_variants', '1');

    if (state.activeCategoryId && state.activeCategoryId !== 0) params.set('category_id', state.activeCategoryId);
    if (state.q) params.set('q', state.q);

    const res = await api('/api/menu/items?' + params.toString());
    state.items = Array.isArray(res?.data) ? res.data : (Array.isArray(res) ? res : []);

    renderItems();
  }

  function renderItems() {
    if (!elGrid || !elEmpty) return;
    elGrid.innerHTML = '';
    elEmpty.classList.toggle('hidden', state.items.length !== 0);

    const tplCard = document.getElementById('tplProductCard');
    if (!tplCard) return;

    state.items.forEach((it) => {
      const bestPrice = effectiveUnitPrice(it, null);
      const node = tplCard.content.firstElementChild.cloneNode(true);

      const nameEl  = node.querySelector('[data-name]');
      const codeEl  = node.querySelector('[data-code]');
      const priceEl = node.querySelector('[data-price]');
      if (nameEl)  nameEl.textContent  = it.name || '—';
      if (codeEl)  codeEl.textContent  = `Code: ${it.id ?? '—'}`;
      if (priceEl) priceEl.textContent = fmtUSD(bestPrice);

      const img = node.querySelector('img');
      if (img) {
        img.src = it.image
          ? (it.image.startsWith('http') ? it.image : `/storage/${it.image}`)
          : 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085?auto=format&fit=crop&w=800&q=60';
        img.alt = it.name || 'Item';
      }

      const addBtn = node.querySelector('[data-add]');
      addBtn?.addEventListener('click', () => onAddClicked(it));

      elGrid.appendChild(node);
    });
  }

  // ========= Customize modal =========
  const ICE_OPTIONS   = ['No Ice', 'Less Ice', 'Normal Ice', 'Extra Ice'];
  const SUGAR_OPTIONS = ['0%', '25%', '50%', '75%', '100%'];
  const QUICK_NOTES   = ['Take away', 'No straw', 'Less sweet', 'More sweet'];

  function setCustomizeImage(item, row = null) {
    if (!czImage || !czImageFallback) return;

    let imgPath = item?.image || row?.image || '';
    let src = '';
    if (imgPath) {
      src = String(imgPath).startsWith('http')
        ? imgPath
        : `/storage/${imgPath}`;
    }

    if (!src) {
      czImage.classList.add('hidden');
      czImage.removeAttribute('src');
      czImage.alt = '';
      czImageFallback.classList.remove('hidden');
      return;
    }

    czImage.src = src;
    czImage.alt = item?.name ? String(item.name) : 'Item';
    czImage.classList.remove('hidden');
    czImageFallback.classList.add('hidden');

    czImage.onerror = () => {
      czImage.classList.add('hidden');
      czImageFallback.classList.remove('hidden');
    };
  }

  function renderOptionButtons(container, options, key) {
    if (!container) return;
    container.innerHTML = '';

    options.forEach(opt => {
      const active = (state.czCustom[key] === opt);

      const b = document.createElement('button');
      b.type = 'button';
      b.className = [
        'shrink-0 px-4 h-9 rounded-full text-xs font-semibold border transition whitespace-nowrap',
        active
          ? 'bg-indigo-600 text-white border-indigo-600'
          : 'bg-white/80 dark:bg-slate-900 text-slate-900 dark:text-slate-50 border-slate-200/80 dark:border-slate-700/80 hover:bg-slate-50 dark:hover:bg-slate-800/80'
      ].join(' ');
      b.textContent = opt;

      b.addEventListener('click', () => {
        state.czCustom[key] = active ? null : opt;
        renderCustomizeBody(state.czItem);
      });

      container.appendChild(b);
    });
  }

  function sizeLabelFromVariant(v) {
    const raw = String(v?.name || '').trim().toUpperCase();

    let m = raw.match(/\(([SML])\)/);
    if (m) return m[1];

    m = raw.match(/\b(S|M|L)\b/);
    if (m) return m[1];

    if (v?.size) return String(v.size).toUpperCase();

    return raw || ('V' + (v?.id ?? ''));
  }

  function renderSizeButton({ label, priceText, active, onClick }) {
    const b = document.createElement('button');
    b.type = 'button';
    b.className = [
      'h-11 rounded-2xl border transition font-semibold text-sm',
      'focus:outline-none focus:ring-2 focus:ring-indigo-500/40',
      active
        ? 'bg-indigo-600 text-white border-indigo-600'
        : 'bg-white/80 dark:bg-slate-900 text-slate-900 dark:text-slate-50 border-slate-200/80 dark:border-slate-700/80 hover:bg-slate-50 dark:hover:bg-slate-800/80'
    ].join(' ');

    b.innerHTML = `
      <div class="flex flex-col items-center justify-center leading-tight">
        <div class="text-sm font-extrabold">${escapeHtml(label)}</div>
        <div class="text-[11px] font-semibold ${active ? 'text-white/90' : 'text-slate-500 dark:text-slate-300'}">
          ${escapeHtml(priceText)}
        </div>
      </div>
    `;

    b.addEventListener('click', onClick);
    return b;
  }

  function getCzQty() {
    return Math.max(1, parseInt(czQty?.value || '1', 10) || 1);
  }

  function setCzQty(n) {
    n = Math.max(1, parseInt(String(n || 1), 10) || 1);
    if (czQty) czQty.value = String(n);
    if (czQtyDisplay) czQtyDisplay.textContent = String(n);
    if (czQtyText) czQtyText.textContent = String(n);
    renderCustomizeBody(state.czItem);
  }

  czQtyDec?.addEventListener('click', () => setCzQty(getCzQty() - 1));
  czQtyInc?.addEventListener('click', () => setCzQty(getCzQty() + 1));
  czQty?.addEventListener('change', () => setCzQty(getCzQty()));

  function updateCustomizeTotals(item) {
    if (!item) return;

    const unit = effectiveUnitPrice(item, state.czVariant);
    const qty  = getCzQty();
    const sub  = unit * qty;

    if (czUnitPrice) czUnitPrice.textContent = fmtUSD(unit);
    if (czQtyText)   czQtyText.textContent   = String(qty);
    if (czSubtotal)  czSubtotal.textContent  = fmtUSD(sub);

    if (czPrice)      czPrice.textContent      = fmtUSD(sub);
    if (czQtyDisplay) czQtyDisplay.textContent = String(qty);
  }

  function renderCustomizeBody(item) {
    if (!item) return;

    const hasVariants = Array.isArray(item?.variants) && item.variants.length > 0;
    if (czVariantsWrap) czVariantsWrap.classList.toggle('hidden', false);

    if (state.czVariant && hasVariants) {
      const stillExists = item.variants.some(v => v.id === state.czVariant.id);
      if (!stillExists) state.czVariant = null;
    }

    if (czVariants) {
      czVariants.innerHTML = '';

      const basePrice = effectiveUnitPrice(item, null);
      czVariants.appendChild(renderSizeButton({
        label: 'M',
        priceText: fmtUSD(basePrice),
        active: (state.czVariant == null),
        onClick: () => {
          state.czVariant = null;
          renderCustomizeBody(item);
        }
      }));

      const order = { S: 0, M: 1, L: 2 };
      const variants = (item.variants || [])
        .map(v => ({ v, size: sizeLabelFromVariant(v) }))
        .filter(x => x.size && x.size !== 'M')
        .sort((a,b) => (order[a.size] ?? 99) - (order[b.size] ?? 99));

      variants.forEach(({ v, size }) => {
        const active = state.czVariant && state.czVariant.id === v.id;
        const price  = effectiveUnitPrice(item, v);

        czVariants.appendChild(renderSizeButton({
          label: size,
          priceText: fmtUSD(price),
          active,
          onClick: () => {
            state.czVariant = v;
            renderCustomizeBody(item);
          }
        }));
      });
    }

    updateCustomizeTotals(item);

    renderOptionButtons(czIce, ICE_OPTIONS, 'ice');
    renderOptionButtons(czSugar, SUGAR_OPTIONS, 'sugar');
    renderOptionButtons(czQuickNotes, QUICK_NOTES, 'quick_note');
  }

  function onAddClicked(item) {
    state.czMode   = 'add';
    state.czRowKey = null;
    state.czItem   = item;

    setCustomizeImage(item);

    state.czVariant = null;
    state.czCustom  = { ice:null, sugar:null, quick_note:null };

    if (czTitle) czTitle.textContent = item.name || 'Customize';
    if (czNote)  czNote.value = '';
    setCzQty(1);

    renderCustomizeBody(item);
    openDialog(dlgCustomize);
  }

  function openEditRow(key) {
    const row = state.cart.find(x => x.key === key);
    if (!row) return;

    const item = state.items.find(i => i.id === row.menu_item_id)
      || { id: row.menu_item_id, name: row.name, price: row.unit_price, variants: [], image: row.image || null };

    state.czMode = 'edit';
    state.czRowKey = key;
    state.czItem = item;

    setCustomizeImage(item, row);

    state.czVariant = (row.menu_item_variant_id && Array.isArray(item.variants))
      ? item.variants.find(v => v.id === row.menu_item_variant_id) || null
      : null;

    state.czCustom = { ...(row.customizations || {ice:null,sugar:null,quick_note:null}) };

    if (czTitle) czTitle.textContent = item.name || 'Customize';
    if (czNote)  czNote.value = row.note || '';
    setCzQty(row.qty || 1);

    renderCustomizeBody(item);
    openDialog(dlgCustomize);
  }

  btnCzSave?.addEventListener('click', () => {
    if (!state.czItem) return;

    const qty  = getCzQty();
    const note = String(czNote?.value || '').trim();
    const unit = effectiveUnitPrice(state.czItem, state.czVariant);

    const newRow = {
      menu_item_id: state.czItem.id,
      menu_item_variant_id: state.czVariant?.id ?? null,
      name: state.czItem.name,
      image: state.czItem.image || null,
      variant_name: state.czVariant ? (sizeLabelFromVariant(state.czVariant) || state.czVariant.name) : 'M',
      qty,
      unit_price: Number(unit),
      note,
      customizations: { ...state.czCustom },
    };

    if (state.czMode === 'add') {
      mergeRow(newRow);
      toast('Added to cart', 'success');
      if (window.innerWidth < 768) openMobileCart();
    } else {
      const orig = state.cart.find(x => x.key === state.czRowKey);
      if (orig) applyEditRow(orig, newRow);
      toast('Updated item', 'success');
    }

    renderCart();
    closeDialog(dlgCustomize);
  });

  // ========= Search =========
  let __searchTimer = null;
  elSearch?.addEventListener('input', () => {
    clearTimeout(__searchTimer);
    __searchTimer = setTimeout(() => {
      state.q = String(elSearch.value || '').trim();
      refreshItems();
    }, 250);
  });

  // ========= Init =========
  (async () => {
    try {
      initCartFromStorage();

      // initialize discount UI default
      syncDiscountCurrencyFromUI();
      setDiscountMode('amount');

      await loadCategories();
      await refreshItems();
      renderCart();
    } catch (e) {
      console.error(e);
      toast(e?.message || 'Failed to load POS data', 'danger');
    }
  })();
})();
</script>
