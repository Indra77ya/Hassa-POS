// ── State ─────────────────────────────────────────────────────────────────────
var CALC_HISTORY_KEY  = 'calculator_history';
var CALC_MAX_HISTORY  = 5;
var calcLastRawResult = null;  // raw number stored after = so operators can chain from it
var calcAfterEquals   = false; // true right after = — controls next keypress behaviour

// Translation helper — uses global LANG object (loaded via public/js/lang/*.js).
// Falls back to the English string if LANG isn't available or key is missing.
function _calcT(key, fallback) {
    return (typeof LANG !== 'undefined' && LANG[key]) ? LANG[key] : fallback;
}

// ── Core input ────────────────────────────────────────────────────────────────
// Appends a value; handles post-equals state so operators chain and digits start fresh
function calEnterVal(id) {
    var isOp = ['+', '-', '*', '/', '%'].indexOf(id) >= 0;

    if (calcAfterEquals) {
        calcAfterEquals = false;
        if (isOp) {
            // Continue from last raw result (not the formatted display string)
            document.calc.result.value = calcLastRawResult + id;
        } else {
            // Start a fresh expression
            document.calc.result.value = id;
        }
    } else {
        document.calc.result.value += id;
    }
}

// AC — wipe everything
function clearScreen() {
    document.calc.result.value = '';
    calcAfterEquals   = false;
    calcLastRawResult = null;
}

// CE — remove only the last entered number (keep expression before last operator)
// Example: "123+456" → CE → "123+"
function clearEntry() {
    var val = document.calc.result.value;
    var lastOp = Math.max(
        val.lastIndexOf('+'), val.lastIndexOf('-'),
        val.lastIndexOf('*'), val.lastIndexOf('/'), val.lastIndexOf('%')
    );
    document.calc.result.value = lastOp >= 0 ? val.slice(0, lastOp + 1) : '';
    calcAfterEquals   = false;
    calcLastRawResult = null;
}

// Backspace — delete last single character
function backspace() {
    var val = document.calc.result.value;
    document.calc.result.value = val.slice(0, -1);
    calcAfterEquals = false;
}

// Calculate result, format display, save to history
function calculate() {
    try {
        var expr = document.calc.result.value;
        if (!expr) return;

        var raw = eval(expr);
        calcLastRawResult = raw;
        calcAfterEquals   = true;

        // Save expression + raw result to localStorage history
        _calcSaveHistory(expr, raw);

        // Format display using app currency formatter if available; else plain number
        if (typeof __currency_trans_from_en === 'function') {
            document.calc.result.value = __currency_trans_from_en(raw, false);
        } else {
            document.calc.result.value = raw;
        }
    } catch (err) {
        document.calc.result.value = 'Error';
        calcAfterEquals   = false;
        calcLastRawResult = null;
    }
}

// ── History — localStorage, max 5 ────────────────────────────────────────────
function _calcGetHistory() {
    try { return JSON.parse(localStorage.getItem(CALC_HISTORY_KEY)) || []; }
    catch (e) { return []; }
}

function _calcSaveHistory(expr, raw) {
    var history = _calcGetHistory();
    history = history.filter(function (h) { return h.expr !== expr; }); // drop duplicate
    history.unshift({ expr: expr, result: raw });                        // newest first
    localStorage.setItem(CALC_HISTORY_KEY, JSON.stringify(history.slice(0, CALC_MAX_HISTORY)));
}

// Renders history entries into any given jQuery container ($list)
// Clicking an entry loads the full expression (Option B) and hides the popup
function _calcRenderInto($list) {
    var history = _calcGetHistory();
    $list.empty();

    if (history.length === 0) {
        $list.append('<div class="calc-hist-empty">' + _calcT('calc_no_calculations_yet', 'No calculations yet') + '</div>');
        return;
    }

    history.forEach(function (item) {
        var formatted = (typeof __currency_trans_from_en === 'function')
            ? __currency_trans_from_en(item.result, false)
            : item.result;

        var $entry = $(
            '<div class="calc-hist-entry">' +
                '<div class="calc-hist-expr">' + item.expr + '</div>' +
                '<div class="calc-hist-result">= ' + formatted + '</div>' +
            '</div>'
        );

        $entry.on('click', function () {
            document.calc.result.value = item.expr;
            calcAfterEquals   = false;
            calcLastRawResult = null;
            $('#calc-hist-popup').hide();
            $('#calculator input[name="result"]').trigger('focus');
        });

        $list.append($entry);
    });
}

// Called by the Clear button inside the popup
function _calcClearHistoryPopup() {
    localStorage.removeItem(CALC_HISTORY_KEY);
    _calcRenderInto($('#calc-hist-list'));
}

// ── Init ──────────────────────────────────────────────────────────────────────
$(document).ready(function () {
    var _ht; // hover-hide timer for history popup

    // Custom template: no .popover-title div — our #calc-titlebar in the content
    // handles the title bar so #calc-hist-btn receives mouse events properly.
    $('#btnCalculator').popover({
        template: '<div class="popover" role="tooltip"><div class="arrow"></div><div class="popover-content"></div></div>'
    });

    // ── Create floating history popup (once, appended to body) ───────────
    if ($('#calc-hist-popup').length === 0) {
        $('body').append(
            '<div id="calc-hist-popup">' +
                '<div class="calc-hist-head">' + _calcT('calc_recent_calculations', 'Recent calculations') + '</div>' +
                '<div id="calc-hist-list"></div>' +
                '<button type="button" id="calc-hist-clear">' + _calcT('calc_clear_history', 'Clear history') + '</button>' +
            '</div>'
        );
    }

    // Clear-history button (delegation — popup lives in body)
    $(document).on('click', '#calc-hist-clear', function () {
        _calcClearHistoryPopup();
    });

    // Keep popup open while hovering over it
    $(document).on('mouseenter', '#calc-hist-popup', function () { clearTimeout(_ht); });
    $(document).on('mouseleave', '#calc-hist-popup', function () {
        _ht = setTimeout(function () { $('#calc-hist-popup').hide(); }, 150);
    });

    // ── On popover open: focus display + bind history hover ──────────────
    // BS3 recreates content on every show, so we bind fresh each time.
    $('#btnCalculator').on('shown.bs.popover', function () {
        $('#calculator input[name="result"]').trigger('focus');

        $('#calc-hist-btn')
            .on('mouseenter', function () {
                clearTimeout(_ht);
                _calcRenderInto($('#calc-hist-list'));
                var rect = this.getBoundingClientRect();
                var pw = 240;
                $('#calc-hist-popup').css({
                    top  : rect.bottom + 4,
                    left : Math.max(4, rect.right - pw),
                    width: pw
                }).show();
            })
            .on('mouseleave', function () {
                _ht = setTimeout(function () { $('#calc-hist-popup').hide(); }, 150);
            });
    });

    // Hide history popup when calculator closes
    $('#btnCalculator').on('hide.bs.popover', function () {
        $('#calc-hist-popup').hide();
    });
});

// ── Physical keyboard (only when calculator has focus) ─────────────────────
// Key map:
//   0–9, ., %        → digit / decimal / percent
//   + - * /          → operator
//   Enter or =       → calculate
//   Backspace        → delete last character  (same as ⌫ button)
//   Delete           → CE — clear last entry
//   Escape           → AC — clear all
$(document).on('keydown', function (e) {
    var calc = document.getElementById('calculator');
    if (!calc || !calc.contains(document.activeElement)) return;

    var key = e.key;

    if ((key >= '0' && key <= '9') || key === '.' || key === '%') {
        e.preventDefault(); calEnterVal(key);
    } else if (key === '+' || key === '-' || key === '*' || key === '/') {
        e.preventDefault(); calEnterVal(key);
    } else if (key === 'Enter' || key === '=') {
        e.preventDefault(); calculate();
    } else if (key === 'Backspace') {
        e.preventDefault(); backspace();
    } else if (key === 'Delete') {
        e.preventDefault(); clearEntry();
    } else if (key === 'Escape') {
        e.preventDefault(); clearScreen();
    }
});
