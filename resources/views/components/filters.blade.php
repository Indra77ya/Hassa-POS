@php
    $filterId = 'pf_' . uniqid();
    $isOpen   = false; // default closed
@endphp

@once
<style>
    /* grid-trick for smooth height animation — can't do this with Tailwind utilities */
    .pf-body { display: grid; grid-template-rows: 0fr; transition: grid-template-rows .3s cubic-bezier(.4,0,.2,1); }
    .pf-body--open { grid-template-rows: 1fr; }
    .pf-inner { overflow: hidden; }
    /* left accent bar via pseudo-element */
    .pf-btn { position: relative; }
    .pf-btn::before {
        content: '';
        position: absolute;
        left: 0; top: 0; bottom: 0;
        width: 3px;
        background: var(--theme-700, #004EEB);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform .25s cubic-bezier(.4,0,.2,1);
    }
    .pf-open .pf-btn::before { transform: scaleX(1); }
    .pf-open .pf-btn { border-bottom-color: #e5e7eb !important; }
</style>
@endonce

@once
<script>
function pfToggle(id) {
    var card    = document.getElementById(id);
    var body    = document.getElementById(id + '_body');
    var chevron = card.querySelector('.pf-chevron');
    var btn     = card.querySelector('.pf-btn');
    var open    = card.classList.contains('pf-open');

    card.classList.toggle('pf-open', !open);
    body.classList.toggle('pf-body--open', !open);
    chevron.classList.toggle('tw-rotate-180', !open);
    btn.setAttribute('aria-expanded', open ? 'false' : 'true');
}
</script>
@endonce

<div id="{{ $filterId }}"
     class="tw-bg-white tw-rounded-xl tw-shadow-sm tw-ring-1 tw-ring-gray-200 tw-mb-4 tw-overflow-hidden tw-transition-shadow tw-duration-200 hover:tw-shadow-md {{ $isOpen ? 'pf-open' : '' }}">

    {{-- Header --}}
    <button type="button"
            id="{{ $filterId }}_btn"
            class="pf-btn tw-w-full tw-flex tw-items-center tw-justify-between tw-px-4 tw-py-3 tw-border-0 tw-border-b tw-border-transparent tw-bg-transparent tw-cursor-pointer tw-transition-colors tw-duration-150 hover:tw-bg-gray-50"
            onclick="pfToggle('{{ $filterId }}')"
            aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
            aria-controls="{{ $filterId }}_body">

        <span class="tw-flex tw-items-center tw-gap-2.5">
            {{-- Icon badge --}}
            <span class="tw-flex tw-items-center tw-justify-center tw-w-7 tw-h-7 tw-rounded-lg tw-shrink-0 tw-text-xs tw-transition-colors tw-duration-200"
                  style="background: color-mix(in srgb, var(--theme-700, #004EEB) 10%, transparent); color: var(--theme-700, #004EEB);">
                @if (!empty($icon))
                    {!! $icon !!}
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="13" height="13" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.2"
                         stroke-linecap="round" stroke-linejoin="round">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                    </svg>
                @endif
            </span>
            {{-- Title --}}
            <span class="tw-text-sm tw-font-semibold tw-text-gray-900 tw-tracking-tight">
                {{ $title ?? __('report.filters') }}
            </span>
        </span>

        {{-- Chevron --}}
        <svg class="pf-chevron tw-w-4 tw-h-4 tw-shrink-0 tw-transition-all tw-duration-300 {{ $isOpen ? 'tw-rotate-180' : '' }}"
             style="color: var(--theme-700, #004EEB);"
             xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24"
             fill="none" stroke="currentColor" stroke-width="2.5"
             stroke-linecap="round" stroke-linejoin="round">
            <polyline points="6 9 12 15 18 9"/>
        </svg>
    </button>

    {{-- Collapsible body --}}
    <div id="{{ $filterId }}_body" class="pf-body {{ $isOpen ? 'pf-body--open' : '' }}">
        <div class="pf-inner">
            <div class="tw-px-4 tw-pt-3 tw-pb-4">
                {{ $slot }}
            </div>
        </div>
    </div>

</div>
