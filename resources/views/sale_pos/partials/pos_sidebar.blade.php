<div class="box box-solid box-primary" id="pos_sidebar_box" style="margin-bottom: 10px;">
    <div class="box-header with-border" style="padding: 10px;">
        <div class="row" style="margin: 0;">
            @if (!empty($categories))
                <div class="col-md-5 col-sm-5 col-xs-6" style="padding: 0 2px 0 0;" id="product_category_div">
                    <select class="form-control select2" id="product_category" style="width: 100%;">
                        <option value="all">@lang('lang_v1.all_category')</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category['id'] }}">{{ $category['name'] }}</option>
                        @endforeach
                        @foreach ($categories as $category)
                            @if (!empty($category['sub_categories']))
                                <optgroup label="{{ $category['name'] }}">
                                    @foreach ($category['sub_categories'] as $sc)
                                        <option value="{{ $sc['id'] }}">{{ $sc['name'] }}</option>
                                    @endforeach
                                </optgroup>
                            @endif
                        @endforeach
                    </select>
                </div>
            @endif

            @if (!empty($brands))
                <div class="col-md-4 col-sm-4 col-xs-6" style="padding: 0 2px;" id="product_brand_div">
                    {!! Form::select('product_brand', $brands, null, [
                        'id' => 'product_brand',
                        'class' => 'form-control select2',
                        'placeholder' => __('brand.brands'),
                        'style' => 'width: 100%;',
                    ]) !!}
                </div>
            @endif

            <div class="col-md-3 col-sm-3 col-xs-12" style="padding: 0 0 0 2px;" id="feature_product_div">
                <button type="button" id="show_featured_products" class="btn btn-warning btn-flat btn-block btn-sm" style="height: 34px; font-weight: bold;">
                    <i class="fa fa-star"></i> <span class="hidden-xs">@lang('lang_v1.featured_products')</span>
                </button>
            </div>
        </div>
    </div>

    <!-- used in repair : filter for service/product -->
    <div class="col-md-6 hide" id="product_service_div">
        {!! Form::select(
            'is_enabled_stock',
            ['' => __('messages.all'), 'product' => __('sale.product'), 'service' => __('lang_v1.service')],
            null,
            ['id' => 'is_enabled_stock', 'class' => 'select2', 'name' => null, 'style' => 'width:100% !important'],
        ) !!}
    </div>

    <div class="box-body" style="padding: 10px 5px;">
        <input type="hidden" id="suggestion_page" value="1">
        <div id="product_list_body" style="max-height: calc(100vh - 210px); overflow-y: auto; overflow-x: hidden;">
            <div id="featured_products_box" style="display: none;">
                @if (!empty($featured_products))
                    @include('sale_pos.partials.featured_products')
                @endif
            </div>
            {{-- Hidden template: source of truth for the empty-state markup --}}
            <div id="featured_empty_state_template" style="display: none;">
                <div class="text-center text-muted" style="padding: 20px 10px;">
                    <i class="fa fa-info-circle fa-2x" style="color: #f39c12;"></i><br>
                    @if(auth()->user()->can('business_settings.access'))
                        <span>@lang('lang_v1.featured_products_empty_msg')</span>
                        <a href="{{ url('business-location') }}" target="_blank" class="btn btn-link btn-xs">
                            @lang('business.business_location') <i class="fa fa-external-link"></i>
                        </a>
                    @else
                        <span>@lang('lang_v1.no_products_to_display')</span>
                    @endif
                </div>
            </div>
            <div id="product_list_items" class="row" style="margin: 0;"></div>
        </div>
        <div class="col-md-12 text-center" id="suggestion_page_loader" style="display: none; padding: 15px;">
            <i class="fa fa-spinner fa-spin fa-2x"></i>
        </div>
    </div>
</div>
