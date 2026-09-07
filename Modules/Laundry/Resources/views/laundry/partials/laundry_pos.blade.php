@php
    $order_sheets = $order_sheets ?? ($view_data['order_sheets'] ?? []);
@endphp
<div class="col-md-6 col-sm-12">
    <div class="form-group tw-mb-3">
        <div class="input-group tw-border tw-border-slate-200 tw-rounded-xl tw-overflow-hidden tw-shadow-sm tw-transition-all focus-within:tw-border-indigo-400 focus-within:tw-ring-1 focus-within:tw-ring-indigo-400/20" style="display: flex; width: 100% !important; align-items: center;">
            <span class="input-group-addon !tw-bg-slate-50 !tw-border-0 !tw-text-slate-400 !tw-px-3" style="border: 0; vertical-align: middle; flex: 0 0 auto; display: flex; align-items: center; justify-content: center; height: 36px;">
                <i class="fa fa-shopping-basket"></i>
            </span>
            <div style="flex: 1 1 auto; min-width: 0;">
                {!! Form::select('laundry_order_sheet_id', $order_sheets, null, ['class' => 'form-control select2 !tw-border-0 !tw-shadow-none !tw-h-9 !tw-text-xs !tw-bg-transparent focus:tw-outline-none', 'placeholder' => __('laundry::lang.select_order_sheet'), 'id' => 'laundry_order_sheet_id', 'style' => 'height: 36px; border: 0; box-shadow: none; width: 100%;']) !!}
            </div>
            <span class="input-group-btn" style="border: 0; vertical-align: middle; flex: 0 0 auto; display: flex; align-items: center;">
                <button type="button" class="btn btn-default bg-white btn-flat !tw-border-0 !tw-h-9 !tw-text-indigo-600 hover:!tw-bg-indigo-50/50 active:tw-scale-95 tw-transition-transform" id="add_laundry_order_sheet_quick_btn" title="@lang('laundry::lang.add_order_sheet')" style="border: 0; background: transparent; height: 36px; display: inline-flex; align-items: center; justify-content: center; padding: 0 8px;">
                    <i class="fa fa-plus-circle fa-lg"></i>
                </button>
                <button type="button" class="btn btn-default bg-white btn-flat !tw-border-0 !tw-h-9 !tw-text-amber-600 hover:!tw-bg-amber-50/50 active:tw-scale-95 tw-transition-transform" id="edit_laundry_order_sheet_btn" title="@lang('laundry::lang.edit_order_sheet')" style="border: 0; background: transparent; height: 36px; display: inline-flex; align-items: center; justify-content: center; padding: 0 8px;">
                    <i class="fa fa-pencil-alt fa-lg"></i>
                </button>
                <button type="button" class="btn btn-default bg-white btn-flat !tw-border-0 !tw-h-9 !tw-text-sky-600 hover:!tw-bg-sky-50/50 active:tw-scale-95 tw-transition-transform" id="show_laundry_order_sheet_btn" title="@lang('laundry::lang.order_sheet_detail')" style="border: 0; background: transparent; height: 36px; display: inline-flex; align-items: center; justify-content: center; padding: 0 8px;">
                    <i class="fa fa-eye fa-lg"></i>
                </button>
                <button type="button" class="btn btn-default bg-white btn-flat !tw-border-0 !tw-h-9 !tw-text-emerald-600 hover:!tw-bg-emerald-50/50 active:tw-scale-95 tw-transition-transform" id="add_laundry_to_cart_btn" title="Masukkan ke Keranjang" style="border: 0; background: transparent; height: 36px; display: inline-flex; align-items: center; justify-content: center; padding: 0 8px;">
                    <i class="fa fa-shopping-cart fa-lg"></i>
                </button>
            </span>
        </div>
    </div>
</div>
