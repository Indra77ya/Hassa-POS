<div class="box box-solid box-primary {{$class ?? ''}}"
    @if (!empty($id)) id="{{ $id }}" @endif>
    @if (empty($header))
        @if (!empty($title) || !empty($tool))
            <div class="box-header with-border">
                {!! $icon ?? '' !!}
                <h3 class="box-title">{{ $title ?? '' }}</h3>
                {!! $tool ?? '' !!}

                @if (isset($help_text))
                    <br />
                    <small>{!! $help_text !!}</small>
                @endif
            </div>
        @endif
    @else
        <div class="box-header with-border">
            {!! $header !!}
        </div>
    @endif
    <div class="box-body">
        {{ $slot }}
    </div>
</div>
