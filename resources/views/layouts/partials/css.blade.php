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
@if(!empty($__system_settings['additional_css']))
    {!! $__system_settings['additional_css'] !!}
@endif

