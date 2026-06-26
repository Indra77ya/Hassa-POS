@php
	/* Dynamic min-width based on which conditional columns are actually rendered.
	   Without this, hidden columns (Service staff, Warranty, Tax+PriceIncTax when inline_tax is off,
	   Unit Price/Discount when permission denied) leave wasted space and force a horizontal scroll. */
	$dst_fixed_cols = 40 + 220 + 160 + 110 + 130 + 140 + 110 + 50; // #, Product, Qty, UnitPrice, Discount, PriceIncTax, Subtotal, X
	if (session()->get('business.enable_inline_tax') == 1) {
		$dst_fixed_cols += 80; // Tax
	} else {
		$dst_fixed_cols -= 140; // Price inc. tax also hidden when inline tax is off
	}
	if (!empty($pos_settings['inline_service_staff'])) $dst_fixed_cols += 140;
	if (!empty($common_settings['enable_product_warranty'])) $dst_fixed_cols += 140;
	if (!auth()->user()->can('edit_product_price_from_sale_screen')) $dst_fixed_cols -= 110;
	if (!auth()->user()->can('edit_product_discount_from_sale_screen')) $dst_fixed_cols -= 130;
@endphp
<style>
	/* Scoped to direct sell only - this class does not exist on the POS screen.
	   Loaded into <head> via @yield('css') AFTER app.css so cascade order favours these rules. */
	.direct-sell-product-table { table-layout: fixed; width: 100%; min-width: {{ $dst_fixed_cols }}px; }
	/* All columns have a fixed pixel width. The table's max-width caps how much it can
	   grow on wide screens, so Product (and others) don't balloon. */
	.direct-sell-product-table > thead > tr > th.dst-col-num            { width: 40px  !important; }
	.direct-sell-product-table > thead > tr > th.dst-col-product        { width: 220px !important; }
	.direct-sell-product-table > thead > tr > th.dst-col-qty            { width: 160px !important; }
	.direct-sell-product-table > thead > tr > th.dst-col-staff          { width: 140px !important; }
	.direct-sell-product-table > thead > tr > th.dst-col-price          { width: 110px !important; }
	.direct-sell-product-table > thead > tr > th.dst-col-discount       { width: 130px !important; }
	.direct-sell-product-table > thead > tr > th.dst-col-tax            { width: 80px  !important; }
	.direct-sell-product-table > thead > tr > th.dst-col-price-inc-tax  { width: 140px !important; }
	.direct-sell-product-table > thead > tr > th.dst-col-warranty       { width: 140px !important; }
	.direct-sell-product-table > thead > tr > th.dst-col-subtotal       { width: 110px !important; }
	.direct-sell-product-table > thead > tr > th.dst-col-remove         { width: 50px  !important; }

	/* Constrain the quantity input-group so it fits inside its 160px cell.
	   Without this, the <input> has Tailwind's md:!tw-w-auto -> width: auto !important,
	   which renders at the browser default (~180px) and overflows into Unit Price. */
	.direct-sell-product-table > tbody > tr > td .input-group.input-number {
		width: 100% !important;
	}
	.direct-sell-product-table > tbody > tr > td .input-group.input-number > .form-control.input_quantity {
		width: 100% !important;
		min-width: 0 !important;
	}
	/* product_row.blade.php applies inline style="width: auto" on these two inputs,
	   which renders at the browser-default ~180px and spills into adjacent cells.
	   Force them to fit their cell. !important is required to beat the inline style. */
	.direct-sell-product-table > tbody > tr > td .form-control.pos_unit_price_inc_tax,
	.direct-sell-product-table > tbody > tr > td .form-control.pos_line_total {
		width: 100% !important;
		box-sizing: border-box;
	}
</style>
