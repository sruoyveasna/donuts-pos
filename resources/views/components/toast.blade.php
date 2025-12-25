{{-- resources/views/components/toast.blade.php --}}
@props([
  'id' => 'twToastWrap',
  'max' => 5,
  'timeout' => 2400,
])

<div id="{{ $id }}" class="tw-toast-wrap" aria-live="polite" aria-atomic="true"></div>

<style>
  /* ====== Container ====== */
  .tw-toast-wrap{
    position: fixed;
    top: .75rem;
    right: .75rem;
    z-index: 12000;
    display: grid;
    gap: .5rem;
    pointer-events: none; /* allows click-through except toast itself */
  }

  /* ====== Toast card ====== */
  .tw-toast{
    pointer-events: auto;
    display: grid;
    grid-template-columns: 20px 1fr 20px;
    align-items: center;
    gap: .75rem;

    min-width: 280px;
    max-width: min(420px, calc(100vw - 1.5rem));

    padding: .75rem .85rem;
    border-radius: 14px;

    /* glass look */
    background: rgba(15, 23, 42, .78);
    color: #fff;
    border: 1px solid rgba(255,255,255,.12);
    backdrop-filter: blur(14px);

    box-shadow:
      0 10px 25px rgba(0,0,0,.25),
      0 2px 10px rgba(0,0,0,.18);

    /* enter animation */
    transform: translateY(-8px) scale(.98);
    opacity: 0;
    animation: twToastIn .22s ease-out forwards;
  }

  .dark .tw-toast{
    background: rgba(2,6,23,.75);
    border-color: rgba(148,163,184,.22);
  }

  @keyframes twToastIn {
    to { transform: translateY(0) scale(1); opacity: 1; }
  }

  /* exit animation */
  .tw-toast.is-leaving{
    animation: twToastOut .18s ease-in forwards;
  }

  @keyframes twToastOut {
    to { transform: translateY(-8px) scale(.98); opacity: 0; }
  }

  /* message */
  .tw-toast .tw-toast-msg{
    font-size: 13px;
    line-height: 1.2rem;
    color: rgba(255,255,255,.92);
  }

  /* close button */
  .tw-toast .tw-toast-close{
    opacity: .75;
    transition: opacity .12s ease;
  }
  .tw-toast .tw-toast-close:hover{ opacity: 1; }

  /* ====== Variants ====== */
  .tw-toast-success{
    background: rgba(22, 163, 74, .86);
    border-color: rgba(134,239,172,.28);
  }
  .tw-toast-danger{
    background: rgba(220, 38, 38, .86);
    border-color: rgba(254,202,202,.28);
  }
  .tw-toast-warning{
    background: rgba(217, 119, 6, .88);
    border-color: rgba(253,230,138,.30);
  }
  .tw-toast-primary{
    background: rgba(236, 72, 153, .86);
    border-color: rgba(251, 207, 232, .30);
  }

  /* ====== Progress bar ====== */
  .tw-toast .tw-toast-bar{
    grid-column: 1 / -1;
    height: 2px;
    margin-top: .55rem;
    border-radius: 9999px;
    background: rgba(255,255,255,.18);
    overflow: hidden;
  }
  .tw-toast .tw-toast-bar > span{
    display: block;
    height: 100%;
    width: 100%;
    transform-origin: left;
    background: rgba(255,255,255,.75);
    animation: twToastBar linear forwards;
  }
  @keyframes twToastBar {
    from { transform: scaleX(1); }
    to   { transform: scaleX(0); }
  }

  /* pause animation when hovering */
  .tw-toast[data-paused="1"] .tw-toast-bar > span{
    animation-play-state: paused;
  }
</style>

@once
<script>
(function () {
  window.AppUI = window.AppUI || {};

  function escapeHtml(s){
    return (s ?? '').toString().replace(/[&<>"']/g, m => ({
      '&':'&amp;','<':'&lt;','>':'&gt;','"':'&quot;',"'":'&#39;'
    }[m]));
  }

  function iconClass(type){
    if (type === 'success') return 'bi-check-circle';
    if (type === 'danger')  return 'bi-x-circle';
    if (type === 'warning') return 'bi-exclamation-triangle';
    return 'bi-info-circle';
  }

  function toastClass(type){
    const map = {
      success: 'tw-toast-success',
      danger:  'tw-toast-danger',
      warning: 'tw-toast-warning',
      primary: 'tw-toast-primary',
      info:    'tw-toast-primary'
    };
    return map[type] || map.primary;
  }

  function removeWithAnim(el){
    if (!el || el.__leaving) return;
    el.__leaving = true;
    el.classList.add('is-leaving');
    // wait for CSS animation to finish
    setTimeout(() => { try { el.remove(); } catch {} }, 180);
  }

  window.AppUI.toast = function(message, type = 'primary', options = {}) {
    const wrap = document.getElementById(@json($id));
    if (!wrap) return;

    const max = Number(options.max ?? @json($max));
    const timeout = Number(options.timeout ?? @json($timeout));
    const showBar = (options.progress ?? true) && timeout > 0; // progress bar on by default

    while (wrap.children.length >= max) {
      removeWithAnim(wrap.firstElementChild);
    }

    const el = document.createElement('div');
    el.className = `tw-toast ${toastClass(type)}`;
    el.setAttribute('role', 'status');

    el.innerHTML = `
      <i class="bi ${iconClass(type)} text-[18px]"></i>
      <div class="tw-toast-msg">${escapeHtml(message)}</div>
      <button type="button" class="tw-toast-close" aria-label="Close">
        <i class="bi bi-x-lg"></i>
      </button>
      ${showBar ? `<div class="tw-toast-bar"><span style="animation-duration:${timeout}ms"></span></div>` : ``}
    `;

    const btn = el.querySelector('button');
    btn?.addEventListener('click', () => removeWithAnim(el));

    // auto close with pause-on-hover
    let timer = null;
    let remaining = timeout;
    let startedAt = null;

    function startTimer(){
      if (timeout <= 0) return;
      startedAt = Date.now();
      timer = setTimeout(() => removeWithAnim(el), remaining);
    }

    function pauseTimer(){
      if (!timer) return;
      clearTimeout(timer);
      timer = null;
      const passed = Date.now() - startedAt;
      remaining = Math.max(0, remaining - passed);
      el.dataset.paused = "1";
      // pause bar animation
      el.querySelector('.tw-toast-bar > span')?.style.setProperty('animation-play-state', 'paused');
    }

    function resumeTimer(){
      if (timeout <= 0 || remaining <= 0) return;
      el.dataset.paused = "0";
      // resume bar animation
      el.querySelector('.tw-toast-bar > span')?.style.setProperty('animation-play-state', 'running');
      startTimer();
    }

    el.addEventListener('mouseenter', pauseTimer);
    el.addEventListener('mouseleave', resumeTimer);

    wrap.appendChild(el);
    startTimer();
  };

  // backward compatible
  window.showToast = function(msg, type='primary', options={}) {
    return window.AppUI.toast(msg, type, options);
  };
})();
</script>
@endonce
