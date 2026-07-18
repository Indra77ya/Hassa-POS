@php
    $all_notifications = auth()->user()->notifications;
    $unread_notifications = $all_notifications->where('read_at', null);
    $total_unread = count($unread_notifications);
@endphp
<!-- Notifications: style can be found in dropdown.less -->
<li class="dropdown notifications-menu tw-list-none">
    <style>
        .notifications_count:empty {
            display: none !important;
        }
        .notifications_count i {
            font-size: 10px !important;
            margin: 0 !important;
            padding: 0 !important;
            width: auto !important;
            height: auto !important;
        }
    </style>
    <a type="button"
        class="dropdown-toggle load_notifications tw-inline-flex tw-items-center tw-ring-1 tw-ring-white/10 tw-justify-center tw-text-sm tw-font-medium tw-text-white hover:tw-text-white tw-transition-all tw-duration-200 theme-btn-bg tw-p-1.5 tw-rounded-lg"
        style="position: relative;"
        data-toggle="dropdown" id="show_unread_notifications" data-loaded="false">
        <span class="tw-sr-only">
            Notifications
        </span>
        <svg aria-hidden="true" class="tw-size-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-width="1.5"
            stroke="currentColor" fill="none" stroke-linecap="round" stroke-linejoin="round">
            <path stroke="none" d="M0 0h24v24H0z" fill="none" />
            <path d="M10 5a2 2 0 1 1 4 0a7 7 0 0 1 4 6v3a4 4 0 0 0 2 3h-16a4 4 0 0 0 2 -3v-3a7 7 0 0 1 4 -6" />
            <path d="M9 17v1a3 3 0 0 0 6 0v-1" />
        </svg>
        <span class="notifications_count" style="position: absolute; top: -4px; right: -4px; background-color: #e11d48; color: white; border-radius: 9999px; height: 18px; width: 18px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: bold; line-height: 1; box-shadow: 0 2px 5px rgba(0,0,0,0.2); z-index: 10;">@if (!empty($total_unread)){{$total_unread}}@endif</span>
    </a>
    <ul class="dropdown-menu !tw-p-2 !tw-w-80 tw-absolute !tw-right-0 !tw-z-10 !tw-mt-2 !tw-origin-top-right !tw-bg-white !tw-rounded-lg !tw-shadow-lg !tw-ring-1 !tw-ring-gray-200 !focus:tw-outline-none" style="left: auto !important; max-height: 350px; overflow-y: auto;">
        <!-- <li class="header">You have 10 unread notifications</li> -->
        <li>
            <!-- inner menu: contains the actual data -->

            <ul class="menu" id="notifications_list">
            </ul>
        </li>

        @if (count($all_notifications) > 10)
            <li class="footer load_more_li">
                <a href="#" class="load_more_notifications">@lang('lang_v1.load_more')</a>
            </li>
        @endif
    </ul>
</li>

<input type="hidden" id="notification_page" value="1">
