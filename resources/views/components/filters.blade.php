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

    /* Elegant floating left accent pill */
    .pf-btn { position: relative; }
    .pf-btn::before {
        content: '';
        position: absolute;
        left: 0;
        top: 8px;
        bottom: 8px;
        width: 4px;
        background: var(--theme-700, #004EEB);
        transform: scaleY(0);
        transform-origin: center;
        transition: transform .25s cubic-bezier(.4,0,.2,1);
        border-top-right-radius: 4px;
        border-bottom-right-radius: 4px;
    }
    .pf-open .pf-btn::before { transform: scaleY(1); }
    .pf-open .pf-btn { border-bottom-color: #f3f4f6 !important; background-color: #f9fafb !important; }
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
     class="tw-bg-white tw-rounded-xl tw-border tw-border-gray-200 tw-mb-5 tw-overflow-hidden tw-transition-all tw-duration-300 {{ $isOpen ? 'pf-open' : '' }}">

    {{-- Header --}}
    <button type="button"
            id="{{ $filterId }}_btn"
            class="pf-btn tw-w-full tw-flex tw-items-center tw-justify-between tw-px-5 tw-py-3.5 tw-border-0 tw-border-b tw-border-transparent tw-bg-transparent tw-cursor-pointer tw-transition-colors tw-duration-200 hover:tw-bg-gray-50/80 tw-outline-none focus:tw-outline-none"
            onclick="pfToggle('{{ $filterId }}')"
            aria-expanded="{{ $isOpen ? 'true' : 'false' }}"
            aria-controls="{{ $filterId }}_body">

        <span class="tw-flex tw-items-center tw-gap-3">
            {{-- Icon badge --}}
            <span class="tw-inline-flex tw-items-center tw-justify-center tw-shrink-0 tw-transition-all tw-duration-300"
                  style="width: 32px; height: 32px; min-width: 32px; min-height: 32px; border-radius: 8px; background: color-mix(in srgb, var(--theme-700, #004EEB) 10%, transparent); color: var(--theme-700, #004EEB);">
                @if (!empty($icon))
                    {!! $icon !!}
                @else
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24"
                         fill="none" stroke="currentColor" stroke-width="2.5"
                         stroke-linecap="round" stroke-linejoin="round" style="display: block; width: 16px; height: 16px;">
                        <polygon points="22 3 2 3 10 12.46 10 19 14 21 14 12.46 22 3"/>
                    </svg>
                @endif
            </span>
            {{-- Title --}}
            <span class="tw-text-sm tw-font-bold tw-text-gray-800 tw-tracking-tight tw-transition-colors tw-duration-200">
                {{ $title ?? __('report.filters') }}
            </span>
        </span>

        {{-- Chevron --}}
        <svg class="pf-chevron tw-w-5 tw-h-5 tw-shrink-0 tw-transition-all tw-duration-300 {{ $isOpen ? 'tw-rotate-180' : '' }}"
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
            <div class="tw-px-6 tw-pt-4 tw-pb-5 tw-bg-gray-50/20">
                {{ $slot }}
            </div>
        </div>
    </div>

</div>
