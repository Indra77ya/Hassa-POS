<div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
        <div class="modal-header">
            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
            <h4 class="modal-title"><i class="fa fa-user-shield"></i> {{ $role_name }}</h4>
        </div>
        <div class="modal-body">
            @if(!empty($role->description))
                <div class="well well-sm">
                    <strong><i class="fa fa-info-circle"></i> @lang('role.description'):</strong>
                    <p class="tw-mt-1 text-muted">{{ $role->description }}</p>
                </div>
            @endif

            <div class="row">
                <div class="col-md-12">
                    <p><strong><i class="fa fa-users"></i> @lang('role.assigned_users'):</strong> {{ $role->users->count() }}</p>
                </div>
            </div>

            <hr>

            <h4 class="tw-font-bold tw-mb-3"><i class="fa fa-key"></i> @lang('role.permissions') ({{ $role->permissions->count() }})</h4>

            @if($role->permissions->count() > 0)
                <div class="tw-flex tw-flex-wrap tw-gap-2">
                    @foreach($role->permissions as $perm)
                        <span class="label label-info tw-text-xs tw-p-1.5 tw-inline-block tw-m-1">
                            {{ __('lang_v1.' . $perm->name) !== 'lang_v1.' . $perm->name ? __('lang_v1.' . $perm->name) : $perm->name }}
                        </span>
                    @endforeach
                </div>
            @else
                <p class="text-muted">@lang('role.no_permissions_assigned')</p>
            @endif
        </div>
        <div class="modal-footer">
            <button type="button" class="btn btn-default" data-dismiss="modal">@lang('messages.close')</button>
        </div>
    </div>
</div>
