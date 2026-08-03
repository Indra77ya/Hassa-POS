<link href="{{ asset('css/tailwind/app.css?v='.$asset_v) }}" rel="stylesheet">

@php
    $themeColor = session('business.theme_color', 'primary');
    $themeColorMap = [
        'primary' => ['700' => '#004EEB', '800' => '#0040C1', '900' => '#00359E'],
        'indigo'  => ['700' => '#4338CA', '800' => '#3730A3', '900' => '#312E81'],
        'violet'  => ['700' => '#6D28D9', '800' => '#5B21B6', '900' => '#4C1D95'],
        'purple'  => ['700' => '#5925DC', '800' => '#4A1FB8', '900' => '#3E1C96'],
        'teal'    => ['700' => '#0F766E', '800' => '#115E59', '900' => '#134E4A'],
        'emerald' => ['700' => '#047857', '800' => '#065F46', '900' => '#064E3B'],
        'green'   => ['700' => '#067647', '800' => '#085D3A', '900' => '#074D31'],
        'sky'     => ['700' => '#026AA2', '800' => '#065986', '900' => '#0B4A6F'],
        'pink'    => ['700' => '#BE185D', '800' => '#9D174D', '900' => '#831843'],
        'rose'    => ['700' => '#BE123C', '800' => '#9F1239', '900' => '#881337'],
        'red'     => ['700' => '#B42318', '800' => '#912018', '900' => '#7A271A'],
        'orange'  => ['700' => '#B93815', '800' => '#932F19', '900' => '#772917'],
        'yellow'  => ['700' => '#B54708', '800' => '#93370D', '900' => '#7A2E0E'],
        'slate'   => ['700' => '#334155', '800' => '#1E293B', '900' => '#0F172A'],
    ];
    $tc = $themeColorMap[$themeColor] ?? $themeColorMap['primary'];
@endphp
<style>
    :root {
        --theme-700: {{ $tc['700'] }};
        --theme-800: {{ $tc['800'] }};
        --theme-900: {{ $tc['900'] }};
    }
    .theme-header-bg {
        background-image: linear-gradient(to right, var(--theme-800), var(--theme-900));
    }
    .theme-btn-bg {
        background-color: var(--theme-800);
    }
    .theme-btn-bg:hover {
        background-color: var(--theme-700);
    }
    .theme-btn-bg:active,
    .theme-btn-bg:focus,
    .theme-btn-bg:focus-visible {
        background-color: var(--theme-900);
        color: #fff;
        outline: 2px solid color-mix(in srgb, var(--theme-700) 40%, transparent);
        outline-offset: 0px;
    }
    .theme-logo-bg {
        background-color: var(--theme-800);
    }
    #side-bar a svg, #side-bar a i {
        color: #9ca3af;
    }
    #side-bar a:hover svg, #side-bar a:hover i,
    #side-bar a.theme-sidebar-active svg, #side-bar a.theme-sidebar-active i {
        color: var(--theme-700);
    }
    #side-bar .theme-sidebar-hover:hover,
    #side-bar .theme-sidebar-hover:active,
    #side-bar .theme-sidebar-hover:focus {
        background-color: color-mix(in srgb, var(--theme-700) 10%, transparent);
        color: var(--theme-700);
        outline: none;
    }
    #side-bar .theme-sidebar-active {
        background-color: color-mix(in srgb, var(--theme-700) 15%, transparent);
        color: var(--theme-700);
    }
    #side-bar .theme-sidebar-child-hover:hover,
    #side-bar .theme-sidebar-child-hover:active,
    #side-bar .theme-sidebar-child-hover:focus {
        color: var(--theme-700);
        outline: none;
    }
    #side-bar .theme-sidebar-child-active {
        color: var(--theme-700);
    }
</style>

<link rel="stylesheet" href="{{ asset('css/vendor.css?v='.$asset_v) }}">

@if( in_array(session()->get('user.language', config('app.locale')), config('constants.langs_rtl')) )
	<link rel="stylesheet" href="{{ asset('css/rtl.css?v='.$asset_v) }}">
@endif

@yield('css')

<!-- app css -->
<link rel="stylesheet" href="{{ asset('css/app.css?v='.$asset_v) }}">

@if(isset($pos_layout) && $pos_layout)
	<style type="text/css">
		.content{
			padding-bottom: 0px !important;
		}
	</style>
@endif
<style type="text/css">
	/*
	* Pattern lock css
	* Pattern direction
	* http://ignitersworld.com/lab/patternLock.html
	*/
	.patt-wrap {
	  z-index: 10;
	}
	.patt-circ.hovered {
	  background-color: #cde2f2;
	  border: none;
	}
	.patt-circ.hovered .patt-dots {
	  display: none;
	}
	.patt-circ.dir {
	  background-image: url("{{asset('/img/pattern-directionicon-arrow.png')}}");
	  background-position: center;
	  background-repeat: no-repeat;
	}
	.patt-circ.e {
	  -webkit-transform: rotate(0);
	  transform: rotate(0);
	}
	.patt-circ.s-e {
	  -webkit-transform: rotate(45deg);
	  transform: rotate(45deg);
	}
	.patt-circ.s {
	  -webkit-transform: rotate(90deg);
	  transform: rotate(90deg);
	}
	.patt-circ.s-w {
	  -webkit-transform: rotate(135deg);
	  transform: rotate(135deg);
	}
	.patt-circ.w {
	  -webkit-transform: rotate(180deg);
	  transform: rotate(180deg);
	}
	.patt-circ.n-w {
	  -webkit-transform: rotate(225deg);
	   transform: rotate(225deg);
	}
	.patt-circ.n {
	  -webkit-transform: rotate(270deg);
	  transform: rotate(270deg);
	}
	.patt-circ.n-e {
	  -webkit-transform: rotate(315deg);
	  transform: rotate(315deg);
	}
</style>

<style type="text/css">
    /* DataTables Export & Visibility Buttons styling (Screenshot 2026-08-03 112305.png) */
    div.dt-buttons, .dt-buttons {
        display: inline-flex !important;
        flex-wrap: wrap !important;
        gap: 10px !important;
        float: none !important;
        margin-top: 5px !important;
        margin-bottom: 5px !important;
    }

    /* Reset btn-group layout overrides so buttons are standalone and have gaps */
    div.dt-buttons.btn-group, .dt-buttons.btn-group {
        display: inline-flex !important;
        flex-wrap: wrap !important;
        gap: 10px !important;
        float: none !important;
        box-shadow: none !important;
        border: none !important;
        background: transparent !important;
    }

    div.dt-buttons.btn-group > .dt-button,
    div.dt-buttons.btn-group > a.dt-button,
    div.dt-buttons.btn-group > button.dt-button,
    .dt-buttons.btn-group > .dt-button {
        border-radius: 9999px !important;
        float: none !important;
        margin: 0 !important;
    }

    /* Style the buttons */
    div.dt-buttons .dt-button,
    div.dt-buttons a.dt-button,
    div.dt-buttons button.dt-button,
    .dt-buttons .dt-button,
    .dt-buttons a.dt-button,
    .dt-buttons button.dt-button {
        display: inline-flex !important;
        align-items: center !important;
        justify-content: center !important;
        background-color: #ffffff !important;
        background-image: none !important;
        color: #1e293b !important;
        border: 1.5px solid #1e293b !important;
        border-radius: 9999px !important;
        padding: 5px 16px !important;
        font-size: 13px !important;
        font-weight: 500 !important;
        height: auto !important;
        min-height: 30px !important;
        line-height: normal !important;
        transition: all 0.2s ease-in-out !important;
        box-shadow: none !important;
        text-shadow: none !important;
        text-decoration: none !important;
        cursor: pointer !important;
        outline: none !important;
    }

    /* Hover effect */
    div.dt-buttons .dt-button:hover,
    div.dt-buttons a.dt-button:hover,
    div.dt-buttons button.dt-button:hover,
    .dt-buttons .dt-button:hover,
    .dt-buttons a.dt-button:hover,
    .dt-buttons button.dt-button:hover {
        background-color: #1e293b !important;
        color: #ffffff !important;
        border-color: #1e293b !important;
        text-decoration: none !important;
    }

    /* Icons inside buttons */
    div.dt-buttons .dt-button i,
    .dt-buttons .dt-button i {
        margin-right: 6px !important;
        color: inherit !important;
        font-size: 13px !important;
    }

    /* Dropdown collections (e.g., PDF Portrait/Landscape, Column visibility) */
    div.dt-button-collection,
    ul.dt-button-collection.dropdown-menu {
        display: flex !important;
        flex-direction: column !important;
        gap: 4px !important;
        padding: 8px !important;
        background-color: #ffffff !important;
        background-image: none !important;
        border: 1px solid rgba(0,0,0,0.15) !important;
        border-radius: 0.5rem !important;
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1), 0 4px 6px -2px rgba(0, 0, 0, 0.05) !important;
        min-width: 180px !important;
        z-index: 2005 !important;
    }

    /* Column visibility list item buttons inside collection */
    div.dt-button-collection .dt-button,
    div.dt-button-collection a.dt-button,
    div.dt-button-collection button.dt-button,
    .dt-button-collection .dt-button {
        background-color: #ffffff !important;
        color: #1e293b !important;
        border: 1px solid transparent !important;
        border-radius: 6px !important;
        padding: 6px 12px !important;
        font-size: 12px !important;
        font-weight: 500 !important;
        justify-content: flex-start !important;
        width: 100% !important;
        min-height: auto !important;
        text-align: left !important;
    }

    /* Hover / Active inside column visibility dropdown */
    div.dt-button-collection .dt-button:hover,
    div.dt-button-collection .dt-button.active,
    div.dt-button-collection a.dt-button:hover,
    div.dt-button-collection a.dt-button.active,
    div.dt-button-collection button.dt-button:hover,
    div.dt-button-collection button.dt-button.active,
    .dt-button-collection .dt-button:hover,
    .dt-button-collection .dt-button.active {
        background-color: #1e293b !important;
        color: #ffffff !important;
        border-color: #1e293b !important;
    }
</style>

@if(!empty($__system_settings['additional_css']))
    {!! $__system_settings['additional_css'] !!}
@endif

