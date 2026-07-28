<div class="modal-dialog" role="document">
  <div class="modal-content">

    {!! Form::open(['url' => action([\App\Http\Controllers\AccountController::class, 'update'],$account->id), 'method' => 'PUT', 'id' => 'edit_payment_account_form' ]) !!}

    <div class="modal-header">
      <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
      <h4 class="modal-title">@lang( 'account.edit_account' )</h4>
    </div>

    <div class="modal-body">
            <div class="form-group">
                {!! Form::label('name', __( 'lang_v1.name' ) .":*") !!}
                {!! Form::text('name', $account->name, ['class' => 'form-control', 'required','placeholder' => __( 'lang_v1.name' ) ]); !!}
            </div>

             <div class="form-group">
                {!! Form::label('account_number', __( 'account.account_number' ) .":*") !!}
                {!! Form::text('account_number', $account->account_number, ['class' => 'form-control', 'required','placeholder' => __( 'account.account_number' ) ]); !!}
            </div>

            <div class="form-group">
                {!! Form::label('account_type_id', __( 'account.account_type' ) .":") !!}
                <select name="account_type_id" class="form-control select2">
                    <option value="">@lang('messages.please_select')</option>
                    @foreach($account_types as $account_type)
                        <option value="{{$account_type->id}}" @if($account->account_type_id == $account_type->id) selected @endif >{{$account_type->name}}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group">
                {!! Form::label('normal_balance', __( 'account.balance' ) .":") !!}
                <select name="normal_balance" class="form-control select2" required>
                    <option value="">@lang('messages.please_select')</option>
                    <option value="debit" @if($account->normal_balance == 'debit') selected @endif>@lang('account.debit')</option>
                    <option value="credit" @if($account->normal_balance == 'credit') selected @endif>@lang('account.credit')</option>
                </select>
            </div>

            <label>@lang('lang_v1.account_details'):</label>
            <table class="table table-striped" id="account_details_table">
                <thead>
                    <tr>
                        <th>
                            @lang('lang_v1.label')
                        </th>
                        <th>
                            @lang('product.value')
                        </th>
                        <th style="width: 10%;">
                            @lang('messages.action')
                        </th>
                    </tr>
                </thead>
                <tbody id="account_details_tbody">
                @if(!empty($account->account_details))
                    @foreach($account->account_details as $key => $account_detail)
                        <tr>
                            <td>
                                {!! Form::text('account_details['.$key.'][label]', !empty($account->account_details[$key]['label'])? $account->account_details[$key]['label'] : null, ['class' => 'form-control']); !!}
                            </td>
                            <td>
                                {!! Form::text('account_details['.$key.'][value]', !empty($account->account_details[$key]['value'])?$account->account_details[$key]['value']:null, ['class' => 'form-control']); !!}      
                            </td>
                            <td>
                                <button type="button" class="tw-dw-btn tw-dw-btn-error tw-dw-btn-xs tw-text-white remove_account_detail_row"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    @endforeach
                @else
                    @for ($i = 0; $i < 3; $i++)
                        <tr>
                            <td>
                                {!! Form::text('account_details['.$i.'][label]', null, ['class' => 'form-control']); !!}
                            </td>
                            <td>
                                {!! Form::text('account_details['.$i.'][value]', null, ['class' => 'form-control']); !!}      
                            </td>
                            <td>
                                <button type="button" class="tw-dw-btn tw-dw-btn-error tw-dw-btn-xs tw-text-white remove_account_detail_row"><i class="fas fa-trash"></i></button>
                            </td>
                        </tr>
                    @endfor
                @endif
                </tbody>
            </table>
            <button type="button" class="tw-dw-btn tw-dw-btn-info tw-dw-btn-xs tw-text-white" id="add_account_detail_row" style="margin-bottom: 15px;"><i class="fas fa-plus"></i> Tambah Baris</button>
            
            <div class="form-group">
                {!! Form::label('note', __( 'brand.note' )) !!}
                {!! Form::textarea('note', $account->note, ['class' => 'form-control', 'placeholder' => __( 'brand.note' ), 'rows' => 4]); !!}
            </div>
    </div>

    <div class="modal-footer">
      <button type="submit" class="tw-dw-btn tw-dw-btn-primary tw-text-white">@lang( 'messages.update' )</button>
      <button type="button" class="tw-dw-btn tw-dw-btn-neutral tw-text-white" data-dismiss="modal">@lang( 'messages.close' )</button>
    </div>

    {!! Form::close() !!}

  </div><!-- /.modal-content -->
</div><!-- /.modal-dialog -->

<script type="text/javascript">
    $(document).ready(function() {
        $(document).off('click', '#add_account_detail_row').on('click', '#add_account_detail_row', function() {
            var table = $(this).closest('.modal-content').find('#account_details_tbody');
            var indices = [];
            table.find('tr').each(function() {
                var inputName = $(this).find('input').first().attr('name');
                if (inputName) {
                    var match = inputName.match(/account_details\[(\d+)\]/);
                    if (match) {
                        indices.push(parseInt(match[1]));
                    }
                }
            });
            var next_idx = indices.length > 0 ? Math.max.apply(null, indices) + 1 : 0;

            var html = '<tr>' +
                '<td><input class="form-control" name="account_details[' + next_idx + '][label]" type="text"></td>' +
                '<td><input class="form-control" name="account_details[' + next_idx + '][value]" type="text"></td>' +
                '<td><button type="button" class="tw-dw-btn tw-dw-btn-error tw-dw-btn-xs tw-text-white remove_account_detail_row"><i class="fas fa-trash"></i></button></td>' +
                '</tr>';
            table.append(html);
        });

        $(document).off('click', '.remove_account_detail_row').on('click', '.remove_account_detail_row', function() {
            $(this).closest('tr').remove();
        });
    });
</script>