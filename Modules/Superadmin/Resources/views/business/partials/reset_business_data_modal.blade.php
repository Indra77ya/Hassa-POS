<!-- Reset Business Data Modal -->
<div class="modal fade" id="reset_business_data_modal" tabindex="-1" role="dialog" aria-labelledby="resetBusinessDataModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            {!! Form::open(['url' => '#', 'method' => 'post', 'id' => 'reset_business_data_form']) !!}
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                <h4 class="modal-title text-danger tw-font-bold" id="resetBusinessDataModalLabel">
                    <i class="fa fa-warning"></i> Reset Data Bisnis: <span id="reset_business_name"></span>
                </h4>
            </div>

            <div class="modal-body">
                <div class="alert alert-danger tw-mb-5">
                    <h4 class="tw-font-bold"><i class="icon fa fa-ban"></i> Peringatan Sangat Penting!</h4>
                    <p>Tindakan ini bersifat merusak (destructive) dan data yang dihapus <strong>TIDAK DAPAT DIKEMBALIKAN</strong> dengan cara apa pun. Silakan pastikan Anda memilih kategori data dengan benar sebelum melanjutkan.</p>
                </div>

                <div class="row">
                    <!-- Column 1: Transaksi -->
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading tw-font-bold">
                                <label class="tw-mb-0">
                                    <input type="checkbox" id="select_all_transactions" name="select_all_transactions" value="1">
                                    <span class="tw-ml-1">Semua Data Transaksi</span>
                                </label>
                            </div>
                            <div class="panel-body">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="transaction-checkbox" name="reset_transactions[]" value="sales">
                                        Data Penjualan (Sales, Invoices, POS, Draft, Quotation)
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="transaction-checkbox" name="reset_transactions[]" value="purchases">
                                        Data Pembelian (Purchases, Purchase Orders, Requisitions)
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="transaction-checkbox" name="reset_transactions[]" value="expenses">
                                        Data Pengeluaran/Biaya (Expenses)
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="transaction-checkbox" name="reset_transactions[]" value="registers">
                                        Data Log Register & Kasir (Cash Registers)
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="transaction-checkbox" name="reset_transactions[]" value="stock_adjustments">
                                        Data Penyesuaian & Transfer Stok (Stock Adjustments & Transfers)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Column 2: Data Master -->
                    <div class="col-md-6">
                        <div class="panel panel-default">
                            <div class="panel-heading tw-font-bold">
                                <label class="tw-mb-0">
                                    <input type="checkbox" id="select_all_master" name="select_all_master" value="1">
                                    <span class="tw-ml-1">Semua Data Master</span>
                                </label>
                            </div>
                            <div class="panel-body">
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="master-checkbox" name="reset_master[]" value="products">
                                        Produk & Variasi (Products, Variations, Price Groups)
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="master-checkbox" name="reset_master[]" value="contacts">
                                        Kontak Pelanggan & Supplier (Contacts, Customers, Suppliers)
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="master-checkbox" name="reset_master[]" value="categories">
                                        Kategori Produk (Categories)
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="master-checkbox" name="reset_master[]" value="brands">
                                        Merek/Brand (Brands)
                                    </label>
                                </div>
                                <div class="checkbox">
                                    <label>
                                        <input type="checkbox" class="master-checkbox" name="reset_master[]" value="custom_taxes">
                                        Pajak Khusus (Custom Taxes/Tax Rates)
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="form-group tw-mt-4">
                    <label class="text-danger tw-font-bold">Ketik kata "RESET" di bawah ini untuk mengonfirmasi:</label>
                    <input type="text" id="confirm_reset_text" name="confirm_reset_text" class="form-control" placeholder="RESET" autocomplete="off">
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="tw-dw-btn tw-dw-btn-outline" data-dismiss="modal">Batal</button>
                <button type="submit" id="submit_reset_btn" class="tw-dw-btn tw-dw-btn-error tw-text-white" disabled>Reset Sekarang</button>
            </div>
            {!! Form::close() !!}
        </div>
    </div>
</div>

<script type="text/javascript">
    $(document).ready(function() {
        // Toggle all transactions check box
        $('#select_all_transactions').change(function() {
            var checked = $(this).is(':checked');
            $('.transaction-checkbox').prop('checked', checked).prop('disabled', checked);
            validateResetForm();
        });

        // Toggle all master data check box
        $('#select_all_master').change(function() {
            var checked = $(this).is(':checked');
            $('.master-checkbox').prop('checked', checked).prop('disabled', checked);
            validateResetForm();
        });

        $('.transaction-checkbox, .master-checkbox, #confirm_reset_text').on('change keyup input', function() {
            validateResetForm();
        });

        function validateResetForm() {
            var hasCheckboxSelected = false;
            if ($('#select_all_transactions').is(':checked') || $('#select_all_master').is(':checked')) {
                hasCheckboxSelected = true;
            }
            if (!hasCheckboxSelected) {
                $('.transaction-checkbox, .master-checkbox').each(function() {
                    if ($(this).is(':checked')) {
                        hasCheckboxSelected = true;
                    }
                });
            }

            var confirmText = $('#confirm_reset_text').val().trim();
            if (hasCheckboxSelected && confirmText === 'RESET') {
                $('#submit_reset_btn').prop('disabled', false);
            } else {
                $('#submit_reset_btn').prop('disabled', true);
            }
        }
    });
</script>
