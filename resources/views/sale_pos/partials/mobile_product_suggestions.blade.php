<div class="modal fade" id="mobile_product_suggestion_modal" tabindex="-1" role="dialog" aria-labelledby="gridSystemModalLabel">
	<!-- Edit Order tax Modal -->
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content bg-gray">
			<div class="modal-header">
				<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
			</div>
			<div class="modal-body">
				@include('sale_pos.partials.pos_sidebar', ['drawer_id_suffix' => '_modal'])
			</div>
			<div class="modal-footer">
			    <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang('messages.close')</button>
			</div>
		</div><!-- /.modal-content -->
	</div><!-- /.modal-dialog -->
</div>

{{-- ═══════════════════════════════════════════════════════════════
     APPLIES TO: MOBILE ONLY (≤ 767px)
     Fix: Category/Brand drawer cut off on the right side.
     Root causes:
       1. Bootstrap modal-dialog's CSS transform makes position:fixed
          children treat the modal-dialog (not the screen) as their
          viewport — drawer-side is narrower than the real screen.
       2. pos-sidebar-root overflow:hidden + border-radius clips
          position:fixed children in iOS Safari.
     Fix: full-width modal on mobile + release the overflow clip.
═══════════════════════════════════════════════════════════════ --}}
<style>
@media (max-width: 767px) {
    /* Make modal fill the full screen width — this also makes the
       fixed drawer-side (whose containing block is the modal-dialog
       due to Bootstrap's transform) span the full viewport. */
    #mobile_product_suggestion_modal .modal-dialog {
        margin: 0 !important;
        width: 100% !important;
        max-width: 100% !important;
    }
    #mobile_product_suggestion_modal .modal-content {
        border-radius: 0 !important;
        min-height: 100dvh;
        min-height: 100vh;
    }

    /* Release iOS Safari's overflow:hidden + border-radius clipping
       of position:fixed children (pos-sidebar-root). */
    #mobile_product_suggestion_modal .pos-sidebar-root {
        overflow: visible !important;
    }

    /* Ensure the DaisyUI drawer-side spans the full viewport width
       and is not constrained by its parent column. */
    #mobile_product_suggestion_modal .tw-dw-drawer-side {
        inset-inline-start: 0 !important;
        width: 100vw !important;
        max-width: 100vw !important;
    }

    /* Make the drawer panel fill the full available screen width
       instead of using 96vw relative to the old (narrower) container. */
    #mobile_product_suggestion_modal .pos-drawer-panel {
        width: 100vw !important;
        max-width: 100vw !important;
    }
}
</style>
