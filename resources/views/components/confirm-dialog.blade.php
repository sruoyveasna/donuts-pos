{{-- resources/views/components/confirm-dialog.blade.php --}}
@props([
  'id' => 'appConfirmDialog',
])

<dialog id="{{ $id }}" class="tw-confirm-dialog">
  <div class="tw-confirm-card">
    <div class="tw-confirm-head">
      <div class="tw-confirm-row">
        <div id="{{ $id }}Icon" class="tw-confirm-icon">
          <i class="bi bi-exclamation-triangle"></i>
        </div>

        <div class="tw-confirm-text">
          <div id="{{ $id }}Title" class="tw-confirm-title">Confirm</div>
          <div id="{{ $id }}Message" class="tw-confirm-message">Are you sure?</div>
        </div>
      </div>
    </div>

    <div class="tw-confirm-actions">
      <button type="button" data-close class="tw-confirm-btn tw-confirm-btn-cancel">
        Cancel
      </button>

      <button type="button" id="{{ $id }}ConfirmBtn" class="tw-confirm-btn tw-confirm-btn-confirm">
        Confirm
      </button>
    </div>
  </div>
</dialog>

<style>
  /* ====== dialog base ====== */
  .tw-confirm-dialog{
    border: 0;
    padding: 0;
    width: min(28rem, calc(100vw - 2rem));
    border-radius: 18px;
    background: transparent;
    overflow: visible;
  }

  /* overlay */
  .tw-confirm-dialog::backdrop{
    background: rgba(2, 6, 23, .45);
    backdrop-filter: blur(3px);
  }

  /* card */
  .tw-confirm-card{
    border-radius: 18px;
    overflow: hidden;

    background: rgba(255,255,255,.92);
    border: 1px solid rgba(255,255,255,.55);
    backdrop-filter: blur(18px);

    box-shadow:
      0 18px 50px rgba(0,0,0,.25),
      0 6px 16px rgba(0,0,0,.15);

    transform: translateY(10px) scale(.98);
    opacity: 0;
    transition: transform .18s ease-out, opacity .18s ease-out;
  }

  .dark .tw-confirm-card{
    background: rgba(2,6,23,.72);
    border-color: rgba(148,163,184,.20);
  }

  /* when open => animate in */
  dialog[open].tw-confirm-dialog .tw-confirm-card{
    transform: translateY(0) scale(1);
    opacity: 1;
  }

  .tw-confirm-head{ padding: 18px 18px 14px; }
  .tw-confirm-row{ display: flex; gap: 12px; align-items: flex-start; }

  .tw-confirm-icon{
    height: 42px; width: 42px;
    border-radius: 9999px;
    display: grid;
    place-items: center;
    font-size: 18px;
  }

  .tw-confirm-title{
    font-weight: 700;
    font-size: 16px;
    color: rgb(15 23 42);
  }
  .dark .tw-confirm-title{ color: rgb(226 232 240); }

  .tw-confirm-message{
    margin-top: 4px;
    font-size: 13px;
    line-height: 1.25rem;
    color: rgb(71 85 105);
  }
  .dark .tw-confirm-message{ color: rgb(148 163 184); }

  .tw-confirm-actions{
    padding: 14px 18px 18px;
    display: flex;
    justify-content: flex-end;
    gap: 10px;
    border-top: 1px solid rgba(148,163,184,.18);
  }

  .tw-confirm-btn{
    border-radius: 9999px;
    padding: 9px 14px;
    font-size: 13px;
    font-weight: 600;
    transition: transform .08s ease, opacity .12s ease, background-color .12s ease;
    user-select: none;
  }
  .tw-confirm-btn:active{ transform: scale(.98); }

  .tw-confirm-btn-cancel{
    background: transparent;
    border: 1px solid rgba(100,116,139,.35);
    color: rgb(30 41 59);
  }
  .dark .tw-confirm-btn-cancel{
    border-color: rgba(148,163,184,.28);
    color: rgb(226 232 240);
  }
  .tw-confirm-btn-cancel:hover{ opacity: .9; background: rgba(148,163,184,.10); }

  .tw-confirm-btn-confirm{
    color: #fff;
    border: 1px solid rgba(255,255,255,.15);
    background: rgb(225 29 72); /* danger default */
  }
  .tw-confirm-btn-confirm:hover{ opacity: .95; }
</style>

@once
<script>
(function () {
  window.AppUI = window.AppUI || {};
  const dialogId = @json($id);

  function setVariant(dlg, variant){
    const iconWrap = document.getElementById(dialogId + 'Icon');
    const okBtn = document.getElementById(dialogId + 'ConfirmBtn');

    // reset base
    iconWrap.className = 'tw-confirm-icon';
    okBtn.className = 'tw-confirm-btn tw-confirm-btn-confirm';

    // default danger
    let iconBg = 'rgba(254, 202, 202, .55)';  // rose-200-ish
    let iconFg = 'rgb(190, 18, 60)';         // rose-700-ish
    let btnBg  = 'rgb(225 29 72)';           // rose-600

    if (variant === 'warning') {
      iconBg = 'rgba(253, 230, 138, .55)';   // amber
      iconFg = 'rgb(180, 83, 9)';
      btnBg  = 'rgb(217 119 6)';
    }

    if (variant === 'primary') {
      iconBg = 'rgba(251, 207, 232, .55)';   // pink-ish
      iconFg = 'rgb(190, 24, 93)';
      btnBg  = 'rgb(236 72 153)';
    }

    iconWrap.style.background = iconBg;
    iconWrap.style.color = iconFg;
    okBtn.style.background = btnBg;

    // dark mode tweak (keep it readable)
    if (document.documentElement.classList.contains('dark')) {
      iconWrap.style.background = 'rgba(255,255,255,.06)';
      iconWrap.style.color = (variant === 'warning')
        ? 'rgb(253 230 138)'
        : (variant === 'primary')
          ? 'rgb(251 207 232)'
          : 'rgb(254 202 202)';
    }
  }

  window.AppUI.confirm = function (opts = {}) {
    const dlg = document.getElementById(dialogId);
    if (!dlg) return Promise.resolve(false);

    // close any open confirm
    if (dlg.open) { try { dlg.close('cancel'); } catch {} }

    const titleEl  = document.getElementById(dialogId + 'Title');
    const msgEl    = document.getElementById(dialogId + 'Message');
    const okBtn    = document.getElementById(dialogId + 'ConfirmBtn');
    const cancelBtn= dlg.querySelector('[data-close]');

    titleEl.textContent = opts.title ?? 'Confirm';
    msgEl.textContent   = opts.message ?? 'Are you sure?';
    okBtn.textContent   = opts.confirmText ?? 'Confirm';
    if (cancelBtn) cancelBtn.textContent = opts.cancelText ?? 'Cancel';

    setVariant(dlg, opts.variant ?? 'danger');

    return new Promise((resolve) => {
      const onClose = () => {
        dlg.removeEventListener('close', onClose);
        resolve(dlg.returnValue === 'ok');
      };
      dlg.addEventListener('close', onClose);

      // confirm click
      const onOk = () => {
        okBtn.removeEventListener('click', onOk);
        try { dlg.close('ok'); } catch { resolve(true); }
      };
      okBtn.addEventListener('click', onOk);

      // open (use your helper so click-outside + esc works consistently)
      if (typeof window.openDialog === 'function') window.openDialog(dlg);
      else dlg.showModal();
    });
  };
})();
</script>
@endonce
