@php
    $role_permissions = $role_permissions ?? [];
@endphp

@foreach($groups as $groupKey => $group)
    <div class="panel panel-default check_group" style="border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); margin-bottom: 20px;">
        <div class="panel-heading" style="background-color: #f8fafc; border-bottom: 1px solid #e2e8f0; border-top-left-radius: 8px; border-top-right-radius: 8px; padding: 12px 18px;">
            <div class="row" style="display: flex; align-items: center; justify-content: space-between;">
                <div class="col-md-8 col-xs-7">
                    <h4 style="margin: 0; font-weight: 600; color: #1e293b; font-size: 16px;">
                        <i class="{{ $group['icon'] ?? 'fas fa-check-circle' }}" style="margin-right: 8px; color: #3b82f6;"></i>
                        {{ $group['title'] }}
                    </h4>
                </div>
                <div class="col-md-4 col-xs-5 text-right">
                    <label class="checkbox-inline" style="font-weight: 600; color: #475569; margin: 0; cursor: pointer;">
                        <input type="checkbox" class="check_all input-icheck">
                        <span style="margin-left: 4px;">{{ __('role.select_all') }}</span>
                    </label>
                </div>
            </div>
        </div>
        <div class="panel-body" style="padding: 18px 20px; background-color: #ffffff; border-bottom-left-radius: 8px; border-bottom-right-radius: 8px;">
            <div class="row">
                @foreach($group['permissions'] as $perm)
                    @php
                        $permName = is_array($perm) ? $perm['name'] : $perm;
                        $labelKey = is_array($perm) ? ($perm['label'] ?? 'role.' . $permName) : 'role.' . $permName;
                        $isRadio = is_array($perm) && !empty($perm['is_radio']);
                        $radioName = is_array($perm) ? ($perm['radio_name'] ?? '') : '';
                        $isChecked = in_array($permName, $role_permissions);
                    @endphp
                    <div class="col-md-4 col-sm-6 col-xs-12" style="margin-bottom: 12px;">
                        <div class="checkbox" style="margin-top: 4px; margin-bottom: 4px;">
                            <label style="font-weight: 400; color: #334155; cursor: pointer; display: flex; align-items: center;">
                                @if($isRadio)
                                    {!! Form::radio('radio_option[' . $radioName . ']', $permName, $isChecked, ['class' => 'input-icheck']) !!}
                                @else
                                    {!! Form::checkbox('permissions[]', $permName, $isChecked, ['class' => 'input-icheck']) !!}
                                @endif
                                <span style="margin-left: 8px;">{{ __($labelKey) }}</span>
                            </label>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>
@endforeach
