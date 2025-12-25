{{-- resources/views/pos/partials/payment-modal.blade.php --}}
<dialog id="dlgPayment"
  class="p-0 border-0 bg-transparent text-slate-100
         w-[min(64rem,calc(100vw-0.75rem))]
         max-h-[calc(100dvh-0.75rem)]
         overflow-hidden
         backdrop:bg-black/70 backdrop:backdrop-blur-sm">

  <div class="relative overflow-hidden rounded-3xl
              bg-slate-950/90 border border-white/10 shadow-2xl
              max-h-[calc(100dvh-0.75rem)] flex flex-col">

    {{-- Header --}}
    <div class="shrink-0 flex items-center justify-between gap-4
                px-4 sm:px-6 py-3
                [@media(max-height:780px)]:py-2.5
                [@media(max-height:700px)]:py-2
                border-b border-white/10 bg-slate-950/60">
      <div class="min-w-0">
        <div class="text-lg sm:text-xl font-semibold tracking-tight">Payment</div>
        <div class="text-xs text-slate-400">Complete the transaction</div>
      </div>

      <form method="dialog">
        <button type="submit"
          class="h-9 w-9 sm:h-10 sm:w-10 rounded-xl grid place-items-center
                 bg-white/5 hover:bg-white/10 border border-white/10">
          <i class="bi bi-x-lg"></i>
        </button>
      </form>
    </div>

    {{-- Body --}}
    <div class="shrink-0">
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-3 p-3 sm:p-4
                  [@media(max-height:780px)]:gap-2.5
                  [@media(max-height:780px)]:p-2.5
                  [@media(max-height:700px)]:gap-2
                  [@media(max-height:700px)]:p-2">

        {{-- LEFT --}}
        <div class="space-y-3 [@media(max-height:780px)]:space-y-2.5 [@media(max-height:700px)]:space-y-2">

          {{-- Settings --}}
          <div class="rounded-2xl px-4 py-2.5 [@media(max-height:780px)]:py-2 [@media(max-height:700px)]:py-2
                      bg-slate-900/50 border border-white/10">
            <div class="flex flex-col gap-1.5 text-sm text-slate-200">
              <div id="payTaxRowTop" class="flex items-center justify-between">
                <span class="text-slate-300">Tax Rate</span>
                <span class="font-semibold text-slate-100">
                  <span id="payTax">—</span>%
                </span>
              </div>

              <div class="flex items-center justify-between">
                <span class="text-slate-300">Exchange Rate</span>
                <span class="font-semibold text-slate-100">
                  1 USD = <span id="payExchange">—</span> <span id="payKhrSymbol">៛</span>
                </span>
              </div>
            </div>
          </div>

          {{-- Payment methods --}}
          <div class="space-y-2">
            <div class="text-base sm:text-lg font-semibold text-purple-300">Payment Methods</div>

            <div class="rounded-2xl p-3 [@media(max-height:780px)]:p-2.5 bg-slate-900/45 border border-white/10">
              <div class="flex items-center justify-between gap-3">
                <div class="text-sm font-medium text-slate-100">Cash Payment</div>

                {{-- Currency toggle --}}
                <div class="flex items-center gap-2 rounded-full bg-slate-950/70 p-1 border border-white/10">
                  <label class="cursor-pointer">
                    <input id="payCurrencyUSD" type="radio" name="payCurrency" class="peer hidden" checked>
                    <span class="px-3 py-1.5 rounded-full text-xs font-semibold
                                 text-slate-200 peer-checked:bg-purple-600 peer-checked:text-white">
                      USD $
                    </span>
                  </label>

                  <label class="cursor-pointer">
                    <input id="payCurrencyKHR" type="radio" name="payCurrency" class="peer hidden">
                    <span class="px-3 py-1.5 rounded-full text-xs font-semibold
                                 text-slate-200 peer-checked:bg-purple-600 peer-checked:text-white">
                      ៛ KHR
                    </span>
                  </label>
                </div>
              </div>

              {{-- Tender USD --}}
              <div class="mt-2.5 [@media(max-height:780px)]:mt-2" data-tender-usd>
                <input id="payTenderUSD" type="number" min="0" step="0.01" placeholder="0.00"
                  class="w-full h-10 px-4 rounded-xl
                         bg-slate-950/60 border border-white/10
                         text-slate-100 placeholder:text-slate-500
                         focus:outline-none focus:ring-2 focus:ring-purple-500/40" />
              </div>

              {{-- Tender KHR --}}
              <div class="mt-2.5 [@media(max-height:780px)]:mt-2 hidden" data-tender-khr>
                <input id="payTenderKHR" type="number" min="0" step="100" placeholder="0"
                  class="w-full h-10 px-4 rounded-xl
                         bg-slate-950/60 border border-white/10
                         text-slate-100 placeholder:text-slate-500
                         focus:outline-none focus:ring-2 focus:ring-purple-500/40" />
              </div>

              {{-- Hints --}}
              <div class="mt-2.5 [@media(max-height:780px)]:mt-2 text-xs text-slate-400 space-y-1">
                <div>
                  Max money input:
                  <span class="text-slate-100 font-semibold" id="payMaxHint">—</span>
                </div>
                <div id="payChange">—</div>
                <div id="payErr" class="text-rose-300 min-h-[0.75rem]"></div>
              </div>
            </div>
          </div>

          {{-- Discount --}}
          <div class="rounded-2xl p-3 [@media(max-height:780px)]:p-2.5 bg-slate-900/45 border border-white/10">

            {{-- Header row: Discount label + Currency toggle --}}
            <div class="flex items-center justify-between gap-3">
              <div class="text-sm font-semibold text-slate-100">Discount</div>

              <div id="payDiscountCurrencyWrap"
                   class="flex items-center gap-2 rounded-full bg-slate-950/70 p-1 border border-white/10">
                <label class="cursor-pointer">
                  <input id="payDiscountCurUSD" type="radio" name="payDiscountCurrency" class="peer hidden">
                  <span class="px-3 py-1.5 rounded-full text-xs font-semibold
                               text-slate-200 peer-checked:bg-purple-600 peer-checked:text-white">
                    USD $
                  </span>
                </label>

                <label class="cursor-pointer">
                  <input id="payDiscountCurKHR" type="radio" name="payDiscountCurrency" class="peer hidden" checked>
                  <span class="px-3 py-1.5 rounded-full text-xs font-semibold
                               text-slate-200 peer-checked:bg-purple-600 peer-checked:text-white">
                    ៛ KHR
                  </span>
                </label>
              </div>
            </div>

            {{-- ✅ Mode chips (FIXED: clear active vs inactive) --}}
            <div class="mt-2.5 [@media(max-height:780px)]:mt-2 flex flex-wrap gap-2">
              <button type="button" data-discount-mode="amount"
                class="px-4 py-1.5 rounded-full text-sm font-semibold
                       border border-white/15
                       bg-white/5 hover:bg-white/12
                       text-slate-200
                       focus:outline-none focus-visible:ring-2 focus-visible:ring-purple-500/50
                       transition active:scale-[0.99]
                       shadow-[inset_0_0_0_1px_rgba(255,255,255,0.06)]">
                Fixed Amount
              </button>

              <button type="button" data-discount-mode="percent"
                class="px-4 py-1.5 rounded-full text-sm font-semibold
                       border border-white/15
                       bg-white/5 hover:bg-white/12
                       text-slate-200
                       focus:outline-none focus-visible:ring-2 focus-visible:ring-purple-500/50
                       transition active:scale-[0.99]
                       shadow-[inset_0_0_0_1px_rgba(255,255,255,0.06)]">
                Percentage
              </button>

              <button type="button" data-discount-mode="code"
                class="px-4 py-1.5 rounded-full text-sm font-semibold
                       border border-white/15
                       bg-white/5 hover:bg-white/12
                       text-slate-200
                       focus:outline-none focus-visible:ring-2 focus-visible:ring-purple-500/50
                       transition active:scale-[0.99]
                       shadow-[inset_0_0_0_1px_rgba(255,255,255,0.06)]">
                Promo Code
              </button>
            </div>

            {{-- Fixed amount --}}
            <div class="mt-2.5 [@media(max-height:780px)]:mt-2" data-discount-amount>
              <div class="flex flex-col gap-2">
                <div class="flex flex-col sm:flex-row gap-2.5">
                  <input id="payDiscountAmount" type="number" min="0" step="0.01" value="0"
                    placeholder="Enter discount amount"
                    class="flex-1 h-10 px-4 rounded-xl
                           bg-slate-950/60 border border-white/10
                           text-slate-100 placeholder:text-slate-500
                           focus:outline-none focus:ring-2 focus:ring-purple-500/40" />
                  <button type="button" data-discount-clear
                    class="h-10 px-5 rounded-xl font-semibold
                           border border-white/10
                           bg-white/5 hover:bg-white/10 text-slate-200 transition">
                    Clear
                  </button>
                </div>

                <div class="text-xs text-slate-400">
                  Computed discount:
                  <span id="payDiscountComputedKhr" class="font-semibold text-slate-100">0 ៛</span>
                </div>
              </div>
            </div>

            {{-- Percentage --}}
            <div class="mt-2.5 [@media(max-height:780px)]:mt-2 hidden" data-discount-percent>
              <div class="flex flex-col sm:flex-row gap-2.5">
                <input id="payDiscountPercent" type="number" min="0" max="100" step="0.01" value="0"
                  placeholder="Enter percent (0 - 100)"
                  class="flex-1 h-10 px-4 rounded-xl
                         bg-slate-950/60 border border-white/10
                         text-slate-100 placeholder:text-slate-500
                         focus:outline-none focus:ring-2 focus:ring-purple-500/40" />
                <button type="button" data-discount-clear
                  class="h-10 px-5 rounded-xl font-semibold
                         border border-white/10
                         bg-white/5 hover:bg-white/10 text-slate-200 transition">
                  Clear
                </button>
              </div>

              <div class="mt-1.5 text-xs text-slate-400">
                Computed discount:
                <span id="payDiscountComputedKhr2" class="font-semibold text-slate-100">0 ៛</span>
              </div>
            </div>

            {{-- Promo code --}}
            <div class="mt-2.5 [@media(max-height:780px)]:mt-2 hidden" data-discount-code>
              <div class="flex flex-col sm:flex-row gap-2.5">
                <input id="payDiscountCode" type="text" maxlength="50"
                  placeholder="Enter promo code"
                  class="flex-1 h-10 px-4 rounded-xl
                         bg-slate-950/60 border border-white/10
                         text-slate-100 placeholder:text-slate-500
                         focus:outline-none focus:ring-2 focus:ring-purple-500/40" />
                <button type="button" id="btnValidatePromo"
                  class="h-10 px-5 rounded-xl font-semibold
                         bg-purple-600 text-white hover:bg-purple-700">
                  Apply
                </button>
              </div>

              <div class="mt-1.5 text-xs text-slate-400">
                Status: <span id="payPromoStatus" class="font-semibold text-slate-100">—</span>
              </div>
            </div>

            <div class="mt-1.5 text-xs text-slate-500">
              Rule: discount applies before tax (tax after discount).
            </div>
          </div>
        </div>

        {{-- RIGHT --}}
        <div class="rounded-2xl bg-slate-950/45 border border-white/10 overflow-hidden flex flex-col">
          <div class="px-4 sm:px-5 py-3 [@media(max-height:780px)]:py-2.5 [@media(max-height:700px)]:py-2 border-b border-white/10">
            <div class="flex items-center justify-between">
              <div class="text-base sm:text-lg font-semibold text-purple-300">Your Items</div>
              <div class="text-xs text-slate-400">
                Due: <span id="payDueKhr" class="font-semibold text-slate-100">—</span>
              </div>
            </div>
            <div class="mt-1 text-xs text-slate-400">
              Approx: <span id="payDueUsd">—</span>
            </div>
          </div>

          <div id="payCartSummaryList"
               class="px-4 sm:px-5 py-3 space-y-2.5 overflow-auto no-scrollbar
                      max-h-[clamp(8rem,20vh,12rem)]
                      lg:max-h-[clamp(9rem,24vh,14rem)]">
          </div>

          <div class="px-4 sm:px-5 pb-3 [@media(max-height:780px)]:pb-2.5 [@media(max-height:700px)]:pb-2">
            <div class="h-px bg-white/10 my-2.5 [@media(max-height:780px)]:my-2"></div>

            <div class="space-y-1.5 text-sm text-slate-200">
              <div class="flex justify-between">
                <span class="text-slate-400">Subtotal</span>
                <span id="paySumSubtotalUsd" class="font-medium">$0.00</span>
              </div>

              <div id="payTaxRowSummary" class="flex justify-between">
                <span class="text-slate-400">Tax (<span id="payTaxInline">—</span>%)</span>
                <span id="paySumTaxKhr" class="font-medium">0 ៛</span>
              </div>

              <div class="flex justify-between">
                <span class="text-slate-400">Discount</span>
                <span id="paySumDiscountKhr" class="font-medium">0 ៛</span>
              </div>

              <div class="h-px bg-white/10 my-2"></div>

              <div class="flex justify-between text-base">
                <span class="font-semibold text-purple-300">Total</span>
                <span id="paySumTotalKhr" class="font-semibold text-purple-300">0 ៛</span>
              </div>

              <span id="paySumSubtotalKhr" class="hidden">0 ៛</span>
            </div>

            <div class="mt-3 [@media(max-height:780px)]:mt-2.5 flex items-center justify-between gap-3">
              <form method="dialog" class="flex-1">
                <button type="submit"
                  class="w-full h-10 rounded-xl font-medium
                         border border-white/15 text-slate-200 hover:bg-white/5">
                  Close
                </button>
              </form>

              <button id="btnPayNow" type="button" disabled
                class="flex-1 h-10 rounded-xl font-semibold
                       bg-purple-600 text-white hover:bg-purple-700 disabled:opacity-50">
                Pay Now
              </button>
            </div>
          </div>
        </div>

      </div>
    </div>

  </div>
</dialog>
