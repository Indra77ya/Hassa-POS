{{--
  Calculator popover content.
  Layout/container styles use Tailwind (tw- prefix).
  Button variants, display overrides, and history popup styles live in calculator.css.
--}}
<div id="calculator" tabindex="-1"
     class="tw-bg-slate-50 tw-p-2 tw-box-border tw-w-full focus:tw-outline-none">

  {{-- Custom title bar — replaces Bootstrap .popover-title so mouse events reach #calc-hist-btn --}}
  <div class="tw-flex tw-items-center tw-justify-between tw-bg-slate-50 tw-border-b tw-border-slate-200 tw--mx-2 tw--mt-2 tw-mb-2 tw-px-2.5 tw-py-1.5">
    <span class="tw-text-[10px] tw-font-bold tw-tracking-[.12em] tw-uppercase tw-text-slate-500">@lang('lang_v1.calculator')</span>
    <button type="button" id="calc-hist-btn" title="History"
      class="tw-bg-transparent tw-border-0 tw-text-slate-400 tw-cursor-pointer tw-px-1.5 tw-py-0.5 tw-text-[13px] tw-leading-none tw-rounded tw-transition-colors tw-duration-100 hover:tw-text-blue-500 hover:tw-bg-blue-500/10">
      <i class="fa fa-history"></i>
    </button>
  </div>

  <form name="calc" class="tw-mb-1.5">
    <input type="text" name="result" id="calc-display" readonly>
  </form>

  <div id="calc-grid" class="tw-grid tw-grid-cols-4 tw-gap-[3px] tw-w-full">

    {{-- Row 1: AC  CE  ⌫  ÷ --}}
    <button type="button" id="allClear"  onclick="clearScreen()"        class="calc-btn calc-btn-ac">AC</button>
    <button type="button" id="clear"     onclick="clearEntry()"         class="calc-btn calc-btn-ce">CE</button>
    <button type="button" id="backspace" onclick="backspace()"          class="calc-btn calc-btn-ce">&#9003;</button>
    <button type="button" id="/"         onclick="calEnterVal(this.id)" class="calc-btn calc-btn-op">&divide;</button>

    {{-- Row 2: 7  8  9  × --}}
    <button type="button" id="7" onclick="calEnterVal(this.id)" class="calc-btn calc-btn-num">7</button>
    <button type="button" id="8" onclick="calEnterVal(this.id)" class="calc-btn calc-btn-num">8</button>
    <button type="button" id="9" onclick="calEnterVal(this.id)" class="calc-btn calc-btn-num">9</button>
    <button type="button" id="*" onclick="calEnterVal(this.id)" class="calc-btn calc-btn-op">&times;</button>

    {{-- Row 3: 4  5  6  − --}}
    <button type="button" id="4" onclick="calEnterVal(this.id)" class="calc-btn calc-btn-num">4</button>
    <button type="button" id="5" onclick="calEnterVal(this.id)" class="calc-btn calc-btn-num">5</button>
    <button type="button" id="6" onclick="calEnterVal(this.id)" class="calc-btn calc-btn-num">6</button>
    <button type="button" id="-" onclick="calEnterVal(this.id)" class="calc-btn calc-btn-op">&minus;</button>

    {{-- Row 4: 1  2  3  + --}}
    <button type="button" id="1" onclick="calEnterVal(this.id)" class="calc-btn calc-btn-num">1</button>
    <button type="button" id="2" onclick="calEnterVal(this.id)" class="calc-btn calc-btn-num">2</button>
    <button type="button" id="3" onclick="calEnterVal(this.id)" class="calc-btn calc-btn-num">3</button>
    <button type="button" id="+" onclick="calEnterVal(this.id)" class="calc-btn calc-btn-op">+</button>

    {{-- Row 5: 0  .  %  = --}}
    <button type="button" id="0" onclick="calEnterVal(this.id)" class="calc-btn calc-btn-num">0</button>
    <button type="button" id="." onclick="calEnterVal(this.id)" class="calc-btn calc-btn-num">.</button>
    <button type="button" id="%" onclick="calEnterVal(this.id)" class="calc-btn calc-btn-op">%</button>
    <button type="button" id="equals" onclick="calculate()"     class="calc-btn calc-btn-eq">=</button>

  </div>

</div>
