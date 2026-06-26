<!-- Left side column. contains the logo and sidebar -->
<aside class="side-bar tw-relative tw-hidden tw-h-full tw-bg-white tw-w-64 xl:tw-w-64 lg:tw-flex lg:tw-flex-col tw-shrink-0">

    <!-- sidebar: style can be found in sidebar.less -->

    {{-- <a href="{{route('home')}}" class="logo">
		<span class="logo-lg">{{ Session::get('business.name') }}</span>
	</a> --}}

    <a href="{{route('home')}}"
        class="tw-flex tw-items-center tw-justify-center tw-w-full tw-border-r tw-h-15 theme-logo-bg tw-shrink-0 tw-border-primary-500/30">
        <p class="tw-text-lg tw-font-medium tw-text-white side-bar-heading tw-text-center">
            {{ Session::get('business.name') }} <span class="tw-inline-block tw-w-3 tw-h-3 tw-bg-green-400 tw-rounded-full" title="Online"></span>
        </p>
    </a>

    <!-- Sidebar Search -->
    <div class="tw-px-3 tw-pt-2 tw-pb-1 tw-border-r tw-border-gray-200 tw-shrink-0">
        <div class="tw-flex tw-items-center tw-gap-2.5 tw-px-3 tw-py-1.5 tw-rounded-lg tw-bg-gray-100 tw-border tw-border-gray-200">
            <svg class="tw-size-4 tw-shrink-0 tw-text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                <path d="M10 10m-7 0a7 7 0 1 0 14 0a7 7 0 1 0 -14 0"/>
                <path d="M21 21l-6 -6"/>
            </svg>
            <input type="text" id="sidebar-search" placeholder="Search menu..."
                class="tw-grow tw-min-w-0 tw-bg-transparent tw-outline-none tw-border-none tw-text-sm tw-font-normal tw-text-gray-600 placeholder:tw-text-gray-400"
                autocomplete="off" />
            <button id="sidebar-search-clear" type="button" aria-label="Clear search"
                class="tw-hidden tw-shrink-0 tw-text-gray-400 hover:tw-text-gray-600 tw-transition-colors tw-duration-200">
                <svg class="tw-size-3.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                    <path stroke="none" d="M0 0h24v24H0z" fill="none"/>
                    <path d="M18 6l-12 12"/>
                    <path d="M6 6l12 12"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Sidebar Menu -->
    {!! Menu::render('admin-sidebar-menu', 'adminltecustom') !!}

    <!-- No results message -->
    <p id="sidebar-no-results" class="tw-hidden tw-px-4 tw-py-3 tw-text-xs tw-text-gray-400 tw-text-center tw-border-r tw-border-gray-200">
        No menu items found.
    </p>

    <!-- /.sidebar-menu -->
    <!-- /.sidebar -->
</aside>

