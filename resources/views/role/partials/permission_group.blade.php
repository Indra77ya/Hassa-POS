@php
  $title_label = $title ?? '';
  $items_list = $items ?? [];
  $checked_perms = $role_permissions ?? [];
@endphp

<div class="panel panel-default check_group tw-mb-4 tw-shadow-sm">
  <div class="panel-heading tw-bg-gray-100 tw-py-2 tw-px-4">
    <div class="row tw-flex tw-items-center">
      <div class="col-xs-8 col-sm-9">
        <h4 class="tw-m-0 tw-font-bold tw-text-gray-800">
          <i class="fa {{ $icon ?? 'fa-check-square' }} tw-mr-1 tw-text-primary"></i> {{ $title_label }}
        </h4>
      </div>
      <div class="col-xs-4 col-sm-3 text-right">
        <label class="tw-cursor-pointer tw-text-sm tw-font-normal tw-m-0 text-muted">
          <input type="checkbox" class="check_all input-icheck"> <strong>@lang('role.select_all')</strong>
        </label>
      </div>
    </div>
  </div>
  <div class="panel-body tw-p-4">
    <div class="row">
      @foreach($items_list as $item)
        @php
          $perm_name = is_array($item) ? $item['value'] : $item;
          $perm_label = is_array($item) ? $item['label'] : (
            __('role.' . $perm_name) !== 'role.' . $perm_name ? __('role.' . $perm_name) : (
              __('lang_v1.' . $perm_name) !== 'lang_v1.' . $perm_name ? __('lang_v1.' . $perm_name) : $perm_name
            )
          );
          $is_checked = in_array($perm_name, $checked_perms);
          $is_radio = is_array($item) && !empty($item['is_radio']);
          $radio_group = is_array($item) && !empty($item['radio_group']) ? $item['radio_group'] : null;
        @endphp
        <div class="col-md-4 col-sm-6 tw-mb-2">
          <div class="checkbox tw-m-0">
            <label class="tw-cursor-pointer">
              @if($is_radio && $radio_group)
                {!! Form::radio('radio_option[' . $radio_group . ']', $perm_name, $is_checked, ['class' => 'input-icheck']) !!} {{ $perm_label }}
              @else
                {!! Form::checkbox('permissions[]', $perm_name, $is_checked, ['class' => 'input-icheck']) !!} {{ $perm_label }}
              @endif
            </label>
          </div>
        </div>
      @endforeach
    </div>
  </div>
</div>
