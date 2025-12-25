<dialog id="dlgCustomize" class="bg-transparent p-0 w-[min(52rem,calc(100vw-1.5rem))]">
  <div
    class="rounded-2xl border border-white/60 dark:border-slate-800/80
           bg-white/95 dark:bg-slate-950/95 shadow-2xl shadow-indigo-200/40
           overflow-hidden text-slate-900 dark:text-slate-50"
  >

    <!-- Header -->
    <div
      class="px-4 md:px-5 py-3.5 border-b border-slate-100/70 dark:border-slate-800/80
             bg-gradient-to-r from-slate-900 via-slate-800 to-indigo-800
             text-slate-50 flex items-center justify-between gap-2"
    >
      <div class="min-w-0">
        <div class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-wide">
          <span class="flex h-6 w-6 items-center justify-center rounded-full
                       bg-white/10 border border-white/30 shadow-sm shadow-slate-900/40">
            <i class="bi bi-sliders2 text-[13px]"></i>
          </span>
          <span id="czTitle" class="truncate">Customize</span>
        </div>
        <p class="mt-0.5 text-[11px] text-slate-200/80">
          Choose size & options, then add to cart.
        </p>
      </div>

      <button type="button" data-close="dlgCustomize"
        class="inline-flex h-7 w-7 items-center justify-center rounded-full
               bg-white/10 hover:bg-white/20 text-slate-50 text-[11px] transition"
        aria-label="Close">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>

    <!-- Body -->
    <div class="p-4 md:p-5 grid grid-cols-1 lg:grid-cols-2 gap-4">

      <!-- LEFT -->
      <div class="space-y-4">

        <!-- Subtotal card -->
        <div class="rounded-2xl p-4
                    border border-slate-200/80 dark:border-slate-700/80
                    bg-slate-50/80 dark:bg-slate-900">
          <div class="flex items-start justify-between gap-3">
            <div class="min-w-0">
              <div class="text-xs font-medium text-slate-700 dark:text-slate-200">Subtotal</div>

              <div id="czSubtotal" class="text-3xl font-extrabold tabular-nums text-slate-900 dark:text-white mt-1">
                $0.00
              </div>

              <div class="mt-1 text-xs text-slate-500 dark:text-slate-400">
                <span id="czUnitPrice">$0.00</span> × <span id="czQtyText">1</span>
              </div>

              <div class="mt-2 text-[11px] text-slate-400 dark:text-slate-500">
                Updates when you select size or quantity.
              </div>

              <!-- compatibility -->
              <div id="czPrice" class="hidden">$0.00</div>
            </div>

            <div class="h-12 w-12 rounded-2xl overflow-hidden
            bg-slate-100 dark:bg-slate-800
            shadow-md shadow-indigo-300/40 relative">

            <!-- Item image -->
            <img id="czImage"
                src=""
                alt=""
                class="h-full w-full object-cover hidden" />

            <!-- Fallback icon (shown when no image) -->
            <div id="czImageFallback"
                class="absolute inset-0 grid place-items-center
                        bg-indigo-600 text-white">
                <i class="bi bi-receipt-cutoff text-xl"></i>
            </div>
            </div>

          </div>
        </div>

        <!-- Quantity + Note -->
        <div class="rounded-2xl p-4 space-y-3
                    border border-slate-200/80 dark:border-slate-700/80
                    bg-slate-50/80 dark:bg-slate-900">

          <div class="flex items-center justify-between">
            <div class="text-sm font-semibold text-slate-900 dark:text-white">Quantity</div>
            <div class="text-xs text-slate-500 dark:text-slate-400">Min 1</div>
          </div>

          <div class="flex items-center gap-2">
            <button type="button" id="czQtyDec"
              class="h-11 w-11 rounded-full grid place-items-center
                     border border-slate-200/80 dark:border-slate-700/80
                     bg-white/80 dark:bg-slate-950/40
                     text-slate-800 dark:text-slate-100
                     hover:bg-slate-100 dark:hover:bg-slate-800/80 transition"
              aria-label="Decrease quantity">
              <i class="bi bi-dash-lg"></i>
            </button>

            <div class="flex-1 h-11 rounded-full
                        border border-slate-200/80 dark:border-slate-700/80
                        bg-white/80 dark:bg-slate-950/40
                        flex items-center justify-center
                        text-base font-semibold tabular-nums text-slate-900 dark:text-white">
              <span id="czQtyDisplay">1</span>
            </div>

            <button type="button" id="czQtyInc"
              class="h-11 w-11 rounded-full grid place-items-center
                     border border-slate-200/80 dark:border-slate-700/80
                     bg-white/80 dark:bg-slate-950/40
                     text-slate-800 dark:text-slate-100
                     hover:bg-slate-100 dark:hover:bg-slate-800/80 transition"
              aria-label="Increase quantity">
              <i class="bi bi-plus-lg"></i>
            </button>

            <input id="czQty" type="number" min="1" value="1" class="hidden" />
          </div>

          <div>
            <label class="text-xs font-medium text-slate-700 dark:text-slate-200">Note (optional)</label>
            <textarea id="czNote" rows="3" placeholder="e.g. Less sweet, no straw..."
              class="mt-1 w-full px-3 py-2 rounded-xl
                     border border-slate-200/80 dark:border-slate-700/80
                     bg-white/80 dark:bg-slate-950/40
                     text-slate-900 dark:text-slate-50
                     placeholder:text-slate-400 dark:placeholder:text-slate-500
                     focus:outline-none focus:ring-1 focus:ring-indigo-300 focus:border-indigo-400"></textarea>
          </div>
        </div>
      </div>

      <!-- RIGHT -->
      <div class="space-y-4">
        <div class="rounded-2xl p-4 space-y-4
                    border border-slate-200/80 dark:border-slate-700/80
                    bg-slate-50/80 dark:bg-slate-900">

          <!-- Sizes -->
          <div id="czVariantsWrap">
            <div class="flex items-center justify-between">
              <div class="text-sm font-semibold text-slate-900 dark:text-white">Size</div>
              <div class="text-xs text-slate-500 dark:text-slate-400">S / M / L</div>
            </div>
            <div id="czVariants" class="mt-3 grid grid-cols-3 gap-2"></div>
            <p class="mt-2 text-[11px] text-slate-400 dark:text-slate-500">
              Only size + price are shown.
            </p>
          </div>

          <div class="h-px bg-slate-200/70 dark:bg-slate-800"></div>

          <!-- Ice -->
          <div>
            <div class="flex items-center justify-between">
              <div class="text-sm font-semibold text-slate-900 dark:text-white">Ice</div>
              <div class="text-xs text-slate-500 dark:text-slate-400">Optional</div>
            </div>
            <div id="czIce" class="mt-2 flex flex-nowrap gap-2 overflow-x-auto no-scrollbar pb-1"></div>
          </div>

          <!-- Sugar -->
          <div>
            <div class="flex items-center justify-between">
              <div class="text-sm font-semibold text-slate-900 dark:text-white">Sugar</div>
              <div class="text-xs text-slate-500 dark:text-slate-400">Optional</div>
            </div>
            <div id="czSugar" class="mt-2 flex flex-nowrap gap-2 overflow-x-auto no-scrollbar pb-1"></div>
          </div>

          <!-- Quick Notes -->
          <div>
            <div class="flex items-center justify-between">
              <div class="text-sm font-semibold text-slate-900 dark:text-white">Quick notes</div>
              <div class="text-xs text-slate-500 dark:text-slate-400">Optional</div>
            </div>
            <div id="czQuickNotes" class="mt-2 flex flex-nowrap gap-2 overflow-x-auto no-scrollbar pb-1"></div>
          </div>

        </div>
      </div>
    </div>

    <!-- Footer -->
    <div class="px-4 md:px-5 py-3.5 border-t border-slate-100/70 dark:border-slate-800/80
                flex justify-end gap-2 bg-white/70 dark:bg-slate-950/70">

      <button type="button" data-close="dlgCustomize"
        class="inline-flex items-center gap-1.5 rounded-full border border-slate-300/80
               px-4 py-2 text-xs text-slate-700 hover:bg-slate-50
               dark:border-slate-700 dark:text-slate-200 dark:hover:bg-slate-800/80">
        <i class="bi bi-arrow-left-short text-[13px]"></i>
        <span>Cancel</span>
      </button>

      <button id="btnCzSave" type="button"
        class="inline-flex items-center gap-1.5 rounded-full
               bg-gradient-to-r from-indigo-500 via-sky-500 to-cyan-400
               px-5 py-2 text-xs font-semibold text-white
               shadow-md shadow-sky-300/70 hover:shadow-sky-400/80
               disabled:opacity-60 disabled:cursor-not-allowed transition">
        <i class="bi bi-cart-check text-[12px]"></i>
        <span>Add to cart</span>
      </button>
    </div>

  </div>
</dialog>

<style>
.no-scrollbar::-webkit-scrollbar { width: 0; height: 0; }
.no-scrollbar { scrollbar-width: none; -ms-overflow-style: none; }
</style>

