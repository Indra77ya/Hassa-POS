@extends('layouts.app')
@section('title', 'Inter-Company Link Settings')

@section('content')

<!-- Content Header (Page header) -->
<section class="content-header">
    <h1>Inter-Company Link Settings
        <small>Ikat beberapa bisnis untuk transaksi jual-beli otomatis</small>
    </h1>
</section>

<!-- Main content -->
<section class="content">
    @component('components.widget', ['class' => 'box-primary', 'title' => 'Tambah Ikatan Inter-Company'])
        <form id="add_intercompany_link_form" action="{{ action([\App\Http\Controllers\IntercompanyController::class, 'store']) }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="business_id">Bisnis Sumber (A):*</label>
                        <select name="business_id" id="business_id" class="form-group select2" style="width: 100%;" required>
                            <option value="">-- Pilih Bisnis A --</option>
                            @foreach($businesses as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="contact_id">Kontak di Bisnis A (Wakil Bisnis B):*</label>
                        <select name="contact_id" id="contact_id" class="form-group select2" style="width: 100%;" required disabled>
                            <option value="">-- Pilih Kontak --</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="linked_business_id">Bisnis Tujuan (B):*</label>
                        <select name="linked_business_id" id="linked_business_id" class="form-group select2" style="width: 100%;" required>
                            <option value="">-- Pilih Bisnis B --</option>
                            @foreach($businesses as $id => $name)
                                <option value="{{ $id }}">{{ $name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="linked_contact_id">Kontak di Bisnis B (Wakil Bisnis A):</label>
                        <select name="linked_contact_id" id="linked_contact_id" class="form-group select2" style="width: 100%;" disabled>
                            <option value="">-- Pilih Kontak (Opsional) --</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="row">
                <div class="col-md-12 text-right">
                    <button type="submit" class="btn btn-primary" id="save_intercompany_link_btn">Simpan Ikatan Bisnis</button>
                </div>
            </div>
        </form>
    @endcomponent

    @component('components.widget', ['class' => 'box-primary', 'title' => 'Daftar Ikatan Inter-Company'])
        <div class="table-responsive">
            <table class="table table-bordered table-striped" id="intercompany_table" style="width: 100%;">
                <thead>
                    <tr>
                        <th>Bisnis Utama</th>
                        <th>Kontak Perwakilan</th>
                        <th>Bisnis Terikat</th>
                        <th>Kontak Perwakilan Terikat</th>
                        <th>@lang('messages.action')</th>
                    </tr>
                </thead>
            </table>
        </div>
    @endcomponent
</section>

@endsection

@section('javascript')
<script type="text/javascript">
$(document).ready(function() {
    var intercompany_table = $('#intercompany_table').DataTable({
        processing: true,
        serverSide: true,
        ajax: '{{ action([\App\Http\Controllers\IntercompanyController::class, "index"]) }}',
        columns: [
            { data: 'business.name', name: 'business.name' },
            { data: 'contact.name', name: 'contact.name' },
            { data: 'linked_business.name', name: 'linked_business.name' },
            { data: 'linked_contact.name', name: 'linked_contact.name', defaultContent: '-' },
            { data: 'action', name: 'action', orderable: false, searchable: false }
        ]
    });

    $('#business_id').change(function() {
        var bus_id = $(this).val();
        $('#contact_id').empty().append('<option value="">-- Pilih Kontak --</option>').prop('disabled', true);
        if (bus_id) {
            $.get('/intercompany-links/contacts/' + bus_id, function(data) {
                $.each(data, function(idx, item) {
                    $('#contact_id').append('<option value="' + item.id + '">' + item.name + ' (' + item.type + ')</option>');
                });
                $('#contact_id').prop('disabled', false);
            });
        }
    });

    $('#linked_business_id').change(function() {
        var bus_id = $(this).val();
        $('#linked_contact_id').empty().append('<option value="">-- Pilih Kontak (Opsional) --</option>').prop('disabled', true);
        if (bus_id) {
            $.get('/intercompany-links/contacts/' + bus_id, function(data) {
                $.each(data, function(idx, item) {
                    $('#linked_contact_id').append('<option value="' + item.id + '">' + item.name + ' (' + item.type + ')</option>');
                });
                $('#linked_contact_id').prop('disabled', false);
            });
        }
    });

    $('#add_intercompany_link_form').submit(function(e) {
        e.preventDefault();
        var form = $(this);
        var data = form.serialize();
        $.ajax({
            method: 'POST',
            url: form.attr('action'),
            dataType: 'json',
            data: data,
            success: function(result) {
                if (result.success == true) {
                    toastr.success(result.msg);
                    intercompany_table.ajax.reload();
                    form[0].reset();
                    $('#contact_id, #linked_contact_id').empty().prop('disabled', true);
                    $('.select2').trigger('change');
                } else {
                    toastr.error(result.msg);
                }
            }
        });
    });

    $(document).on('click', '.delete_intercompany_button', function() {
        var url = $(this).data('href');
        swal({
            title: LANG.sure,
            icon: 'warning',
            buttons: true,
            dangerMode: true,
        }).then((willDelete) => {
            if (willDelete) {
                $.ajax({
                    method: 'DELETE',
                    url: url,
                    dataType: 'json',
                    success: function(result) {
                        if (result.success == true) {
                            toastr.success(result.msg);
                            intercompany_table.ajax.reload();
                        } else {
                            toastr.error(result.msg);
                        }
                    }
                });
            }
        });
    });
});
</script>
@endsection
