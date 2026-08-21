@if(!$account_exist)
<table class="table table-bordered table-striped">
    <tr>
        <td colspan="10" class="text-center">
            <h3>@lang( 'accounting::lang.no_accounts' )</h3>
            <p>@lang( 'accounting::lang.add_default_accounts_help' )</p>
            <a href="{{route('accounting.create-default-accounts')}}" class="btn btn-sm btn-primary">
                @lang( 'accounting::lang.add_default_accounts' ) <i class="fas fa-file-import"></i>
            </a>
        </td>
    </tr>
</table>
@else
<div class="row mb-12" style="margin-bottom: 20px;">
    <div class="col-md-6 col-sm-8">
        <div class="input-group">
            <span class="input-group-addon"><i class="fas fa-search text-muted"></i></span>
            <input type="text" class="form-control" id="accounts_tree_search" placeholder="@lang('lang_v1.search')...">
        </div>
    </div>
    <div class="col-md-6 col-sm-4 text-right">
        <button class="btn btn-default btn-sm" id="expand_all">
            <i class="fas fa-expand-arrows-alt"></i> @lang('accounting::lang.expand_all')
        </button>
        <button class="btn btn-default btn-sm" id="collapse_all">
            <i class="fas fa-compress-arrows-alt"></i> @lang('accounting::lang.collapse_all')
        </button>
    </div>
</div>

<div class="row">
    <div class="col-md-12" id="accounts_tree_container">
        <ul>
        @foreach($account_types as $key => $value)
            <li @if($loop->index==0) data-jstree='{ "opened" : true }' @endif>
                <strong class="text-primary">{{$value}}</strong>
                <ul>
                    @foreach($account_sub_types->where('account_primary_type', $key)->all() as $sub_type)
                        <li @if($loop->index==0) data-jstree='{ "opened" : true }' @endif>
                            <strong>{{$sub_type->account_type_name}}</strong>
                            <ul>
                            @foreach($accounts->where('account_sub_type_id', $sub_type->id)->sortBy('name')->all() as $account)
                                <li @if(count($account->child_accounts) == 0) data-jstree='{ "icon" : "fas fa-arrow-alt-circle-right text-info" }' @endif>
                                    <span style="font-weight: 500;">{{$account->name}}</span>
                                    @if(!empty($account->gl_code))
                                        <span class="text-muted">({{$account->gl_code}})</span>
                                    @endif
                                    - <span class="text-bold">@format_currency($account->balance)</span>

                                    @if($account->status == 'active')  
                                        <span class="label label-success" style="font-size: 10px; margin-left: 5px;">@lang('accounting::lang.active')</span>
                                    @elseif($account->status == 'inactive') 
                                        <span class="label label-danger" style="font-size: 10px; margin-left: 5px;">@lang('lang_v1.inactive')</span>
                                    @endif

                                    <span class="tree-actions" style="margin-left: 10px;">
                                        <a class="btn btn-xs btn-default text-success ledger-link" 
                                            title="@lang( 'accounting::lang.ledger' )"
                                            href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'ledger'], $account->id)}}">
                                            <i class="fas fa-file-alt"></i>
                                        </a>
                                        <a class="btn-modal btn-xs btn-default text-primary" title="@lang('messages.edit')"
                                            href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'edit'], $account->id)}}" 
                                            data-href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'edit'], $account->id)}}" 
                                            data-container="#create_account_modal">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a class="activate-deactivate-btn text-warning btn-xs btn-default"
                                            title="@if($account->status=='active') @lang('messages.deactivate') @else @lang('messages.activate') @endif"
                                            href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'activateDeactivate'], $account->id)}}">
                                            <i class="fas fa-power-off"></i>
                                        </a>
                                    </span>

                                    @if(count($account->child_accounts) > 0)
                                        <ul>
                                        @foreach($account->child_accounts as $child_account)
                                            <li data-jstree='{ "icon" : "fas fa-arrow-alt-circle-right text-info" }'>
                                                <span>{{$child_account->name}}</span>
                                                @if(!empty($child_account->gl_code))
                                                    <span class="text-muted">({{$child_account->gl_code}})</span>
                                                @endif
                                                 - <span class="text-bold">@format_currency($child_account->balance)</span>

                                                @if($child_account->status == 'active') 
                                                    <span class="label label-success" style="font-size: 10px; margin-left: 5px;">@lang('accounting::lang.active')</span>
                                                @elseif($child_account->status == 'inactive') 
                                                    <span class="label label-danger" style="font-size: 10px; margin-left: 5px;">@lang('lang_v1.inactive')</span>
                                                @endif

                                                <span class="tree-actions" style="margin-left: 10px;">
                                                    <a class="btn btn-xs btn-default text-success ledger-link" 
                                                        title="@lang( 'accounting::lang.ledger' )"
                                                        href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'ledger'], $child_account->id)}}">
                                                        <i class="fas fa-file-alt"></i>
                                                    </a>
                                                    <a class="btn-modal btn-xs btn-default text-primary" title="@lang('messages.edit')"
                                                        href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'edit'], $child_account->id)}}" 
                                                        data-href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'edit'], $child_account->id)}}" 
                                                        data-container="#create_account_modal">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a class="activate-deactivate-btn text-warning btn-xs btn-default"
                                                        title="@if($child_account->status=='active') @lang('messages.deactivate') @else @lang('messages.activate') @endif"
                                                        href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'activateDeactivate'], $child_account->id)}}">
                                                        <i class="fas fa-power-off"></i>
                                                    </a>
                                                </span>
                                            </li>
                                        @endforeach
                                        </ul>
                                    @endif
                                </li>
                            @endforeach
                            </ul>
                        </li>
                    @endforeach
                </ul>
            </li>
        @endforeach
        </ul>
    </div>
</div>
@endif