@if(!empty($__subscription) && env('APP_ENV') != 'demo')
<button type="button" class="tw-inline-flex tw-transition-all tw-ring-1 tw-ring-white/10 hover:tw-text-white tw-cursor-pointer tw-duration-200 tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-800 hover:tw-bg-@if(!empty(session('business.theme_color'))){{session('business.theme_color')}}@else{{'primary'}}@endif-700 tw-p-2 tw-rounded-lg tw-items-center tw-justify-center tw-text-white" aria-hidden="true" data-toggle="popover" data-html="true" title="@lang('superadmin::lang.active_package_description')" data-placement="bottom" data-trigger="hover" data-content="
    <table class='table table-condensed'>
     <tr class='text-center'> 
        <td colspan='2'>
            {{$__subscription->package_details['name'] }}
        </td>
     </tr>
     <tr class='text-center'>
        <td colspan='2'>
            {{ @format_date($__subscription->start_date) }} - {{@format_date($__subscription->end_date) }}
        </td>
     </tr>
     <tr> 
        <td colspan='2'>
            <i class='fa fa-check text-success'></i>
            @if($__subscription->package_details['location_count'] == 0)
                @lang('superadmin::lang.unlimited')
            @else
                {{$__subscription->package_details['location_count']}}
            @endif

            @lang('business.business_locations')
        </td>
     </tr>
     <tr>
        <td colspan='2'>
            <i class='fa fa-check text-success'></i>
            @if($__subscription->package_details['user_count'] == 0)
                @lang('superadmin::lang.unlimited')
            @else
                {{$__subscription->package_details['user_count']}}
            @endif

            @lang('superadmin::lang.users')
        </td>
     <tr>
     <tr>
        <td colspan='2'>
            <i class='fa fa-check text-success'></i>
            @if($__subscription->package_details['product_count'] == 0)
                @lang('superadmin::lang.unlimited')
            @else
                {{$__subscription->package_details['product_count']}}
            @endif

            @lang('superadmin::lang.products')
        </td>
     </tr>
     <tr>
        <td colspan='2'>
            <i class='fa fa-check text-success'></i>
            @if($__subscription->package_details['invoice_count'] == 0)
                @lang('superadmin::lang.unlimited')
            @else
                {{$__subscription->package_details['invoice_count']}}
            @endif

            @lang('superadmin::lang.invoices')
        </td>
     </tr>
     
    </table>                     
">
    <svg aria-hidden="true" class="tw-size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
        stroke-width="1.5" stroke="currentColor" fill="none" stroke-linecap="round"
        stroke-linejoin="round">
        <path stroke="none" d="M0 0h24v24H0z" fill="none" />
        <path d="M3 12a9 9 0 1 0 18 0a9 9 0 0 0 -18 0" />
        <path d="M12 9h.01" />
        <path d="M11 12h1v4h1" />
    </svg>
</button>
@endif