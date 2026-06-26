@php
    $pos_form_id = $form_id ?? 'add_pos_sell_form';
@endphp
<script>
(function() {
    var POS_FORM_ID = @json($pos_form_id);

    function collapseHiddenFormRows() {
        var form = document.getElementById(POS_FORM_ID);
        if (!form) return;
        var rows = form.querySelectorAll('.box-body > .row');
        rows.forEach(function(row) {
            if (row.querySelector('.pos_product_div')) return;
            var cols = row.querySelectorAll('[class*="col-"]');
            if (cols.length === 0) return;
            var allHidden = true;
            cols.forEach(function(col) {
                if (col.parentElement !== row) return;
                if (colHasVisibleContent(col)) {
                    allHidden = false;
                    col.style.display = '';
                    col.style.padding = '';
                    col.style.margin = '';
                } else {
                    col.style.display = 'none';
                }
            });
            row.style.display = allHidden ? 'none' : '';
        });
    }

    function colHasVisibleContent(el) {
        if (el.classList.contains('hide') || el.style.display === 'none') return false;
        var children = el.children;
        for (var i = 0; i < children.length; i++) {
            var child = children[i];
            if (child.tagName === 'INPUT' && child.type === 'hidden') continue;
            if (child.classList.contains('hide') || child.style.display === 'none') continue;
            return true;
        }
        if (el.innerText && el.innerText.trim().length > 0) return true;
        return false;
    }

    var _adjustPosTimer = null;
    function adjustPosHeights() {
        var actionBar = document.querySelector('.pos-form-actions');
        if (!actionBar) return;
        var actionTop = actionBar.getBoundingClientRect().top;
        var gap = 10;
        var isMobileView = window.innerWidth <= 767;

        var cartTable = document.querySelector('.pos_product_div');
        if (cartTable) {
            if (isMobileView) {
                cartTable.style.height = '';
                cartTable.style.maxHeight = '';
                cartTable.style.overflowY = '';
                cartTable.style.overflowX = '';
            } else {
                var cartTop = cartTable.getBoundingClientRect().top;
                var totals = document.querySelector('.pos_form_totals');
                var totalsH = totals ? totals.offsetHeight : 0;
                var h = actionTop - cartTop - totalsH - gap;
                cartTable.style.height = Math.max(h, 100) + 'px';
                cartTable.style.maxHeight = '';
                cartTable.style.overflowY = 'auto';
                cartTable.style.overflowX = 'hidden';
            }
        }

        var productGrid = document.getElementById('product_list_body');
        if (productGrid) {
            var gridTop = productGrid.getBoundingClientRect().top;
            var h = actionTop - gridTop - gap;
            productGrid.style.maxHeight = Math.max(h, 100) + 'px';
            productGrid.style.overflowY = 'auto';
            productGrid.style.overflowX = 'hidden';
        }
    }

    function adjustPosHeightsDebounced() {
        clearTimeout(_adjustPosTimer);
        _adjustPosTimer = setTimeout(function() {
            collapseHiddenFormRows();
            adjustPosHeights();
        }, 80);
    }

    $(document).ready(function() {
        setTimeout(function() { collapseHiddenFormRows(); adjustPosHeights(); }, 300);
        setTimeout(function() { collapseHiddenFormRows(); adjustPosHeights(); }, 800);
        $(window).on('resize', adjustPosHeightsDebounced);

        var formArea = document.getElementById(POS_FORM_ID);
        if (formArea) {
            var formObserver = new MutationObserver(adjustPosHeightsDebounced);
            formObserver.observe(formArea, { childList: true, subtree: true, attributes: true, attributeFilter: ['style', 'class'] });
        }

        var cartBody = document.querySelector('#pos_table tbody');
        if (cartBody) {
            var cartObserver = new MutationObserver(adjustPosHeightsDebounced);
            cartObserver.observe(cartBody, { childList: true });
        }

        var totalsEl = document.querySelector('.pos_form_totals');
        if (totalsEl) {
            var totalsObserver = new ResizeObserver(adjustPosHeightsDebounced);
            totalsObserver.observe(totalsEl);
        }
    });
})();
</script>
