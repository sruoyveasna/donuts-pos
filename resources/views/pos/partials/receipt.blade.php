{{-- resources/views/pos/partials/receipt.blade.php --}}
{{-- ✅ Updated:
    1) Print host stays the same (JS mounts printable HTML there)
    2) Preview modal UI improved (clean card, better empty state)
    3) Print CSS hardened (80mm + safe margins)
    NOTE: Logo/name/quote/customizations/change are rendered by pos-js buildReceipt()
--}}

{{-- Hidden host for PRINT ONLY (JS injects receipt HTML here before window.print()) --}}
<div id="receiptPrintHost" class="fixed -left-[99999px] top-0 bg-white text-slate-900 z-[999999]"></div>

<dialog
  id="dlgReceipt"
  class="p-0 border-0 bg-transparent text-slate-100
         w-[min(40rem,calc(100vw-1rem))]
         backdrop:bg-black/70 backdrop:backdrop-blur-sm"
>
  <div
    class="relative overflow-hidden rounded-3xl
           bg-slate-950/90 border border-white/10 shadow-2xl
           max-h-[calc(100dvh-1rem)] flex flex-col"
  >
    {{-- Header --}}
    <div
      class="shrink-0 flex items-center justify-between gap-4
             px-4 sm:px-6 py-4
             border-b border-white/10 bg-slate-950/60"
    >
      <div class="min-w-0">
        <div class="flex items-center gap-2">
          <span class="inline-flex h-8 w-8 items-center justify-center rounded-2xl
                       bg-white/5 border border-white/10">
            <i class="bi bi-receipt-cutoff text-base"></i>
          </span>
          <div class="min-w-0">
            <div class="text-lg sm:text-xl font-semibold tracking-tight truncate">Receipt</div>
            <div class="text-xs text-slate-400 truncate">
              Print preview (Pay Now prints directly)
            </div>
          </div>
        </div>
      </div>

      <div class="flex items-center gap-2">
        <button
          id="btnPrint"
          type="button"
          class="h-9 px-3 rounded-xl text-sm font-semibold
                 bg-white/5 hover:bg-white/10 border border-white/10
                 inline-flex items-center gap-2"
        >
          <i class="bi bi-printer"></i>
          Print
        </button>

        <form method="dialog">
          <button
            type="submit"
            class="h-9 w-9 rounded-xl grid place-items-center
                   bg-white/5 hover:bg-white/10 border border-white/10"
            aria-label="Close receipt"
          >
            <i class="bi bi-x-lg"></i>
          </button>
        </form>
      </div>
    </div>

    {{-- Body --}}
    <div class="p-4 sm:p-6 overflow-auto no-scrollbar">
      {{-- JS injects preview content here (optional) --}}
      <div
        id="rcptBody"
        class="rounded-2xl bg-white text-slate-900 overflow-hidden"
      >
        {{-- Empty state (when JS has not injected receipt HTML yet) --}}
        <div class="p-6">
          <div class="rounded-2xl border border-slate-200 bg-slate-50 p-6 text-center">
            <div class="mx-auto h-12 w-12 rounded-2xl grid place-items-center
                        bg-indigo-600 text-white shadow-md shadow-indigo-200">
              <i class="bi bi-receipt text-xl"></i>
            </div>
            <div class="mt-3 font-semibold">No receipt loaded</div>
            <div class="mt-1 text-sm text-slate-500">
              Complete a payment to print automatically, or use preview mode when available.
            </div>
          </div>
        </div>
      </div>

      {{-- Optional hint --}}
      <div class="mt-3 text-[11px] text-slate-400 text-center">
        Tip: Printing uses a dedicated hidden print host so only the receipt prints.
      </div>
    </div>
  </div>
</dialog>

<style>
  /* ===== PRINT RULES (IMPORTANT) =====
     We ONLY print #receiptPrintHost (JS mounts receipt HTML there).
     This prevents the receipt modal from "popping up" just for printing.
  */
  @media print {
    body.pos-printing * { visibility: hidden !important; }

    #receiptPrintHost,
    #receiptPrintHost * { visibility: visible !important; }

    #receiptPrintHost {
      position: fixed !important;
      left: 0 !important;
      top: 0 !important;
      right: 0 !important;

      width: 80mm !important;
      max-width: 80mm !important;

      margin: 0 auto !important;
      background: #fff !important;
      color: #000 !important;

      padding: 0 !important;
      -webkit-print-color-adjust: exact !important;
      print-color-adjust: exact !important;
    }

    dialog { display: none !important; }
  }

  /* Nice preview look (not for print) */
  #rcptBody #receiptPrintArea {
    width: 100%;
    max-width: 420px;
    margin: 0 auto;
    border-radius: 16px;
    overflow: hidden;
    box-shadow: 0 20px 40px rgba(0,0,0,.08);
  }

  /* Hide scrollbars in modal body */
  .no-scrollbar::-webkit-scrollbar { width: 0; height: 0; }
  .no-scrollbar { scrollbar-width: none; -ms-overflow-style: none; }
</style>
