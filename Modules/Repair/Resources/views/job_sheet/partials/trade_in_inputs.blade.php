@component('components.widget', ['class' => 'box-warning', 'title' => 'Tukar Tambah (Trade-In) Perangkat Bekas'])
<div class="row">
    <div class="col-sm-3">
        <div class="form-group">
            {!! Form::label('trade_in_device_name', 'Nama Perangkat Bekas:') !!}
            {!! Form::text('trade_in[device_name]', $trade_in->device_name ?? null, ['class' => 'form-control', 'placeholder' => 'Contoh: iPhone 11 Pro']); !!}
        </div>
    </div>
    <div class="col-sm-2">
        <div class="form-group">
            {!! Form::label('trade_in_brand', 'Merek:') !!}
            {!! Form::text('trade_in[brand]', $trade_in->brand ?? null, ['class' => 'form-control', 'placeholder' => 'Contoh: Apple']); !!}
        </div>
    </div>
    <div class="col-sm-2">
        <div class="form-group">
            {!! Form::label('trade_in_model', 'Model / Tipe:') !!}
            {!! Form::text('trade_in[model]', $trade_in->model ?? null, ['class' => 'form-control', 'placeholder' => 'Contoh: A2215']); !!}
        </div>
    </div>
    <div class="col-sm-2">
        <div class="form-group">
            {!! Form::label('trade_in_serial_no', 'No Seri / IMEI:') !!}
            {!! Form::text('trade_in[serial_no]', $trade_in->serial_no ?? null, ['class' => 'form-control', 'placeholder' => 'Contoh: 356789...']); !!}
        </div>
    </div>
    <div class="col-sm-3">
        <div class="form-group">
            {!! Form::label('trade_in_value', 'Nilai Tukar Tambah (Rp):') !!}
            {!! Form::text('trade_in[trade_in_value]', isset($trade_in->trade_in_value) ? @num_format($trade_in->trade_in_value) : null, ['class' => 'form-control input_number', 'placeholder' => '0.00']); !!}
        </div>
    </div>
</div>
<div class="row">
    <div class="col-sm-4">
        <div class="form-group">
            {!! Form::label('trade_in_unit_price', 'Harga Jual Estimasi Barang Bekas (Rp):') !!}
            {!! Form::text('trade_in[unit_price]', isset($trade_in->unit_price) ? @num_format($trade_in->unit_price) : null, ['class' => 'form-control input_number', 'placeholder' => '0.00']); !!}
        </div>
    </div>
    <div class="col-sm-4">
        <div class="form-group">
            {!! Form::label('trade_in_condition', 'Kondisi Perangkat:') !!}
            {!! Form::text('trade_in[condition]', $trade_in->condition ?? null, ['class' => 'form-control', 'placeholder' => 'Mulus, Layar Baret, Batrai Normal, dll']); !!}
        </div>
    </div>
    <div class="col-sm-4">
        <div class="form-group">
            {!! Form::label('trade_in_notes', 'Catatan Kelengkapan / Fisik:') !!}
            {!! Form::text('trade_in[notes]', $trade_in->notes ?? null, ['class' => 'form-control', 'placeholder' => 'Unit + Charger Dusbook']); !!}
        </div>
    </div>
</div>
@endcomponent
