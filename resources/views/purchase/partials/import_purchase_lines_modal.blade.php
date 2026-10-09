<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        {!! Form::open(['url' => action([\App\Http\Controllers\PurchaseController::class, 'parseImportPurchaseLines']), 'method' => 'post', 'id' => 'import_purchase_lines_form', 'files' => true]) !!}
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fa fa-file-excel-o text-success"></i> Impor Produk Pembelian (CSV / Excel)</h4>
        </div>

        <div class="modal-body">
            <div class="row">
                <div class="col-md-12">
                    <div class="well">
                        <div class="row">
                            <div class="col-sm-6">
                                <h4><strong>Unduh Template File Impor:</strong></h4>
                                <p class="text-muted">Gunakan template spreadsheet di bawah ini agar kolom terpisah dengan rapi di Excel.</p>
                            </div>
                            <div class="col-sm-6 text-right">
                                <a href="{{ asset('files/import_purchase_lines_template.xlsx') }}" class="btn btn-primary btn-sm tw-mb-1" download>
                                    <i class="fa fa-download"></i> Template Excel (.xlsx)
                                </a>
                                <a href="{{ asset('files/import_purchase_lines_template.xls') }}" class="btn btn-info btn-sm tw-mb-1" download>
                                    <i class="fa fa-download"></i> Template Excel (.xls)
                                </a>
                                <a href="{{ asset('files/import_purchase_lines_template.csv') }}" class="btn btn-success btn-sm tw-mb-1" download>
                                    <i class="fa fa-download"></i> Template CSV
                                </a>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-12">
                    <div class="form-group">
                        {!! Form::label('purchase_lines_csv', 'Pilih File CSV / Excel:*') !!}
                        {!! Form::file('purchase_lines_csv', ['accept' => '.csv, .xls, .xlsx', 'required', 'id' => 'purchase_lines_csv']); !!}
                        <p class="help-block">Format file yang didukung: .xlsx, .xls, .csv</p>
                    </div>
                </div>

                <div class="col-md-12">
                    <h4><strong>Petunjuk Pengisian Kolom Template:</strong></h4>
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr class="bg-gray">
                                <th>Nama Kolom</th>
                                <th>Status</th>
                                <th>Deskripsi & Ketentuan</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td><strong>SKU</strong></td>
                                <td><span class="label label-danger">Wajib</span></td>
                                <td>Kode SKU atau Barcode produk yang sudah terdaftar dalam sistem.</td>
                            </tr>
                            <tr>
                                <td><strong>QUANTITY</strong></td>
                                <td><span class="label label-danger">Wajib</span></td>
                                <td>Jumlah unit yang dibeli (angka). Jika produk memakai Serial Number, isi 1 per baris serial number.</td>
                            </tr>
                            <tr>
                                <td><strong>UNIT COST (BEFORE DISCOUNT)</strong></td>
                                <td><span class="label label-info">Opsional</span></td>
                                <td>Harga beli satuan sebelum diskon. Jika dikosongkan, akan menggunakan harga beli default produk.</td>
                            </tr>
                            <tr>
                                <td><strong>DISCOUNT PERCENT</strong></td>
                                <td><span class="label label-info">Opsional</span></td>
                                <td>Persentase diskon per baris item (misal: 0, 5, 10). Default: 0.</td>
                            </tr>
                            <tr>
                                <td><strong>SELLING PRICE</strong></td>
                                <td><span class="label label-info">Opsional</span></td>
                                <td>Harga jual satuan termasuk pajak. Jika dikosongkan, akan menggunakan harga jual default produk.</td>
                            </tr>
                            <tr>
                                <td><strong>SERIAL NUMBER</strong></td>
                                <td><span class="label label-info">Opsional</span></td>
                                <td>Nomor Seri / IMEI untuk produk berpintasan Serial Number. Jika 1 SKU memiliki banyak serial number, buat baris terpisah dengan SKU yang sama.</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <button type="submit" class="btn btn-primary" id="btn_submit_import_purchase_lines"><i class="fa fa-upload"></i> Unggah & Proses Impor</button>
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
        {!! Form::close() !!}
    </div>
</div>
