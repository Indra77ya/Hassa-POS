@if(!$account_exist)
<table class="table table-bordered table-striped">
    <tr>
        <td colspan="10" class="text-center" style="padding: 30px;">
            <h3 style="color: #475569; font-weight: 600;">@lang( 'accounting::lang.no_accounts' )</h3>
            <p style="color: #64748b;">@lang( 'accounting::lang.add_default_accounts_help' )</p>
            <a href="{{route('accounting.create-default-accounts')}}" class="btn btn-primary btn-sm" style="margin-top: 10px;">
                <i class="fas fa-file-import"></i> @lang( 'accounting::lang.add_default_accounts' )
            </a>
        </td>
    </tr>
</table>
@else
<div class="row" style="margin-bottom: 20px;">
    <div class="col-md-6 col-md-offset-1">
        <div class="input-group">
            <span class="input-group-addon" style="background-color: #f8fafc; border-color: #d2d6de; color: #64748b;">
                <i class="fas fa-search"></i>
            </span>
            <input type="text" class="form-control" id="accounts_tree_search" placeholder="@lang('lang_v1.search')...">
        </div>
    </div>
    <div class="col-md-4 text-right">
        <button class="btn btn-default btn-sm" id="expand_all" style="margin-right: 5px;">
            <i class="fas fa-expand-arrows-alt"></i> @lang('accounting::lang.expand_all')
        </button>
        <button class="btn btn-default btn-sm" id="collapse_all">
            <i class="fas fa-compress-arrows-alt"></i> @lang('accounting::lang.collapse_all')
        </button>
    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div id="accounts_tree_container" style="padding: 10px 0;">
            <ul>
            @foreach($account_types as $key => $value)
                <li @if($loop->index==0) data-jstree='{ "opened" : true }' @endif>
                    <strong style="color: #1e293b; font-size: 15px;">{{$value}}</strong>
                    <ul>
                        @foreach($account_sub_types->where('account_primary_type', $key)->all() as $sub_type)
                            <li @if($loop->index==0) data-jstree='{ "opened" : true }' @endif>
                                <span style="color: #334155; font-weight: 600; font-size: 14px;">{{$sub_type->account_type_name}}</span>
                                <ul>
                                @foreach($accounts->where('account_sub_type_id', $sub_type->id)->sortBy('name')->all() as $account)
                                    <li @if(count($account->child_accounts) == 0) data-jstree='{ "icon" : "fas fa-arrow-alt-circle-right" }' @endif>
                                        <span style="color: #0f172a; font-weight: 500;">{{$account->name}}</span>
                                        @if(!empty($account->gl_code))
                                            <code style="color: #2563eb; background-color: #eff6ff; padding: 1px 5px; border-radius: 3px; font-size: 12px; margin-left: 4px;">{{$account->gl_code}}</code>
                                        @endif
                                        - <span style="font-weight: 600; color: #047857;">@format_currency($account->balance)</span>
                                        @if($account->status == 'active')
                                            <span style="margin-left: 4px;"><i class="fas fa-check text-success" title="@lang( 'accounting::lang.active' )"></i></span>
                                        @elseif($account->status == 'inactive')
                                            <span style="margin-left: 4px;"><i class="fas fa-times text-danger" title="@lang( 'lang_v1.inactive' )" style="font-size: 14px;"></i></span>
                                        @endif
                                        <span class="tree-actions" style="margin-left: 10px;">
                                            <a class="btn btn-xs btn-default text-success ledger-link"
                                                title="@lang( 'accounting::lang.ledger' )"
                                                href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'ledger'], $account->id)}}">
                                                <i class="fas fa-file-alt"></i></a>
                                            <a class="btn-modal btn-xs btn-default text-primary" title="@lang('messages.edit')"
                                                href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'edit'], $account->id)}}"
                                                data-href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'edit'], $account->id)}}"
                                                data-container="#create_account_modal">
                                            <i class="fas fa-edit"></i></a>
                                            <a class="activate-deactivate-btn text-warning btn-xs btn-default"
                                                title="@if($account->status=='active') @lang('messages.deactivate') @else @lang('messages.activate') @endif"
                                                href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'activateDeactivate'], $account->id)}}">
                                                <i class="fas fa-power-off"></i>
                                            </a>
                                        </span>
                                        @if(count($account->child_accounts) > 0)
                                            <ul>
                                            @foreach($account->child_accounts as $child_account)
                                                <li data-jstree='{ "icon" : "fas fa-arrow-alt-circle-right" }'>
                                                    <span style="color: #334155;">{{$child_account->name}}</span>
                                                    @if(!empty($child_account->gl_code))
                                                        <code style="color: #475569; background-color: #f1f5f9; padding: 1px 5px; border-radius: 3px; font-size: 12px; margin-left: 4px;">{{$child_account->gl_code}}</code>
                                                    @endif
                                                    - <span style="font-weight: 600; color: #047857;">@format_currency($child_account->balance)</span>

                                                    @if($child_account->status == 'active')
                                                        <span style="margin-left: 4px;"><i class="fas fa-check text-success" title="@lang( 'accounting::lang.active' )"></i></span>
                                                    @elseif($child_account->status == 'inactive')
                                                        <span style="margin-left: 4px;"><i class="fas fa-times text-danger" title="@lang( 'lang_v1.inactive' )" style="font-size: 14px;"></i></span>
                                                    @endif
                                                    <span class="tree-actions" style="margin-left: 10px;">
                                                        <a class="btn btn-xs btn-default text-success ledger-link"
                                                            title="@lang( 'accounting::lang.ledger' )"
                                                            href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'ledger'], $child_account->id)}}">
                                                            <i class="fas fa-file-alt"></i></a>
                                                        <a class="btn-modal btn-xs btn-default text-primary" title="@lang('messages.edit')"
                                                            href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'edit'], $child_account->id)}}"
                                                            data-href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'edit'], $child_account->id)}}"
                                                            data-container="#create_account_modal">
                                                        <i class="fas fa-edit"></i></a>
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
</div>
@endif
