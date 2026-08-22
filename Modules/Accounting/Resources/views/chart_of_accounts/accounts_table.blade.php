<table class="table table-bordered table-striped" id="coa_tabular_table">
    <thead>
        <tr>
            <th>@lang( 'messages.action' )</th>
            <th>@lang( 'user.name' )</th>
            <th>@lang( 'accounting::lang.gl_code' )</th>
            <th>@lang( 'accounting::lang.parent_account' )</th>
            <th>@lang( 'accounting::lang.account_type' )</th>
            <th>@lang( 'accounting::lang.account_sub_type' )</th>
            <th>@lang( 'accounting::lang.detail_type' )</th>
            <th class="text-right">@lang( 'accounting::lang.primary_balance' )</th>
            <th>@lang( 'sale.status' )</th>
        </tr>
    </thead>
    <tbody>
        @foreach($accounts as $account)
            <tr style="background-color: #f8fafc; font-weight: 600;">
                <td>
                    <div class="btn-group">
                        <button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                            {{__("messages.actions")}} <span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-left" role="menu">
                            <li>
                                <a href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'ledger'], $account->id)}}">
                                    <i class="fas fa-file-alt"></i> @lang( 'accounting::lang.ledger' )
                                </a>
                            </li>
                            <li>
                                <a class="btn-modal" 
                                   href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'edit'], $account->id)}}"
                                   data-href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'edit'], $account->id)}}"
                                   data-container="#create_account_modal">
                                    <i class="fas fa-edit"></i> @lang( 'messages.edit' )
                                </a>
                            </li>
                            <li>
                                <a class="activate-deactivate-btn" 
                                   href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'activateDeactivate'], $account->id)}}">
                                    <i class="fas fa-power-off"></i>
                                    @if($account->status=='active') @lang('messages.deactivate') @else @lang('messages.activate') @endif
                                </a>
                            </li>
                        </ul>
                    </div>
                </td>
                <td style="color: #1e293b; font-weight: 600;">{{$account->name}}</td>
                <td><code style="color: #2563eb; background-color: #eff6ff; padding: 2px 6px; border-radius: 4px;">{{$account->gl_code}}</code></td>
                <td>-</td>
                <td>@if(!empty($account->account_primary_type)){{__('accounting::lang.' . $account->account_primary_type)}}@endif</td>
                <td>
                    @if(!empty($account->account_sub_type))
                        {{$account->account_sub_type->account_type_name}}
                    @endif
                </td>
                <td>
                    @if(!empty($account->detail_type))
                        {{$account->detail_type->account_type_name}}
                    @endif
                </td>
                <td class="text-right" style="white-space: nowrap; font-weight: 600;">
                    @if(!empty($account->balance)) @format_currency($account->balance) @endif
                </td>
                <td>
                    @if($account->status == 'active')
                        <span class="label bg-light-green">@lang( 'accounting::lang.active' )</span> 
                    @elseif($account->status == 'inactive') 
                        <span class="label bg-red">@lang( 'lang_v1.inactive' )</span>
                    @endif
                </td>
            </tr>
            @if(count($account->child_accounts) > 0)
                @foreach($account->child_accounts as $child_account)
                    <tr>
                        <td>
                            <div class="btn-group">
                                <button type="button" class="btn btn-info btn-xs dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                                    {{__("messages.actions")}} <span class="caret"></span><span class="sr-only">Toggle Dropdown</span>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-left" role="menu">
                                    <li>
                                        <a href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'ledger'], $child_account->id)}}">
                                            <i class="fas fa-file-alt"></i> @lang( 'accounting::lang.ledger' )
                                        </a>
                                    </li>
                                    <li>
                                        <a class="btn-modal"
                                           href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'edit'], $child_account->id)}}"
                                           data-href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'edit'], $child_account->id)}}"
                                           data-container="#create_account_modal">
                                            <i class="fas fa-edit"></i> @lang( 'messages.edit' )
                                        </a>
                                    </li>
                                    <li>
                                        <a class="activate-deactivate-btn"
                                           href="{{action([\Modules\Accounting\Http\Controllers\CoaController::class, 'activateDeactivate'], $child_account->id)}}">
                                            <i class="fas fa-power-off"></i>
                                            @if($child_account->status=='active') @lang('messages.deactivate') @else @lang('messages.activate') @endif
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </td>
                        <td style="padding-left: 28px;">
                            <span style="color: #94a3b8; margin-right: 4px;">↳</span> {{$child_account->name}}
                        </td>
                        <td><code style="color: #475569; background-color: #f1f5f9; padding: 2px 6px; border-radius: 4px;">{{$child_account->gl_code}}</code></td>
                        <td style="color: #64748b;">{{$account->name}}</td>
                        <td>@if(!empty($child_account->account_primary_type)){{__('accounting::lang.' . $child_account->account_primary_type)}}@endif</td>
                        <td>
                            @if(!empty($child_account->account_sub_type))
                                {{$child_account->account_sub_type->account_type_name}}
                            @endif
                        </td>
                        <td>
                            @if(!empty($child_account->detail_type))
                                {{$child_account->detail_type->account_type_name}}
                            @endif
                        </td>
                        <td class="text-right" style="white-space: nowrap;">
                            @if(!empty($child_account->balance)) @format_currency($child_account->balance) @endif
                        </td>
                        <td>
                            @if($child_account->status == 'active') 
                                <span class="label bg-light-green">@lang( 'accounting::lang.active' )</span> 
                            @elseif($child_account->status == 'inactive') 
                                <span class="label bg-red">@lang( 'lang_v1.inactive' )</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            @endif
        @endforeach

        @if(!$account_exist)
            <tr>
                <td colspan="9" class="text-center" style="padding: 30px;">
                    <h3 style="color: #475569; font-weight: 600;">@lang( 'accounting::lang.no_accounts' )</h3>
                    <p style="color: #64748b;">@lang( 'accounting::lang.add_default_accounts_help' )</p>
                    <a href="{{route('accounting.create-default-accounts')}}" class="btn btn-primary btn-sm" style="margin-top: 10px;">
                        <i class="fas fa-file-import"></i> @lang( 'accounting::lang.add_default_accounts' )
                    </a>
                </td>
            </tr>
        @endif
    </tbody>
</table>
