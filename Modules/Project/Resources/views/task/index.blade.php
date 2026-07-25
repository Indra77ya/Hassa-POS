@php
    $business_id = request()->session()->get('user.business_id');
    $business = \App\Business::find($business_id);
    $prj_setting = !empty($business->prj_setting) ? json_decode($business->prj_setting, true) : [];
    $task_custom_field_1 = !empty($prj_setting['custom_fields']['custom_field1']) ? $prj_setting['custom_fields']['custom_field1'] : __('project::lang.task_custom_field_1');
    $task_custom_field_2 = !empty($prj_setting['custom_fields']['custom_field2']) ? $prj_setting['custom_fields']['custom_field2'] : __('project::lang.task_custom_field_2');
    $task_custom_field_3 = !empty($prj_setting['custom_fields']['custom_field3']) ? $prj_setting['custom_fields']['custom_field3'] : __('project::lang.task_custom_field_3');
    $task_custom_field_4 = !empty($prj_setting['custom_fields']['custom_field4']) ? $prj_setting['custom_fields']['custom_field4'] : __('project::lang.task_custom_field_4');
@endphp
<script type='text/javascript'>
    window.project_task_custom_labels = {
        custom_field_1: '{{ !empty($prj_setting["custom_fields"]["custom_field1"]) ? 1 : 0 }}',
        custom_field_2: '{{ !empty($prj_setting["custom_fields"]["custom_field2"]) ? 1 : 0 }}',
        custom_field_3: '{{ !empty($prj_setting["custom_fields"]["custom_field3"]) ? 1 : 0 }}',
        custom_field_4: '{{ !empty($prj_setting["custom_fields"]["custom_field4"]) ? 1 : 0 }}'
    };
</script>
@if($can_crud_task || $is_lead_or_admin)
<button type="button" class="tw-dw-btn tw-dw-btn-primary tw-text-white tw-dw-btn-sm task_btn pull-right m-5" data-href="{{action([\Modules\Project\Http\Controllers\TaskController::class, 'create'], ['project_id' => $project->id])}}">
    @lang('messages.add')&nbsp;
    <i class="fa fa-plus"></i>
</button>
@endif
<div class="btn-group btn-group-toggle pull-right m-5" data-toggle="buttons">
    <label class="btn btn-info btn-sm 
        @if((!empty($project->settings) && !isset($project->settings['task_view'])) || (isset($project->settings['task_view']) &&
                $project->settings['task_view'] == 'list_view'))
            active
        @endif">
        <input type="radio" name="task_view" value="list_view" class="task_view" 
           @if((!empty($project->settings) && !isset($project->settings['task_view'])) || (isset($project->settings['task_view']) &&
                $project->settings['task_view'] == 'list_view'))
                checked
            @endif>
        @lang('project::lang.list_view')
    </label>
    <label class="btn btn-info btn-sm
        @if(isset($project->settings['task_view']) &&
        $project->settings['task_view'] == 'kanban')
            active
        @endif">
        <input type="radio" name="task_view" value="kanban" class="task_view" 
            @if(isset($project->settings['task_view']) &&
            $project->settings['task_view'] == 'kanban')
                checked
            @endif>
        @lang('project::lang.kanban_board')
    </label>
</div>
<br><br>
<div class="table-responsive
    @if(isset($project->settings['task_view']) &&
        $project->settings['task_view'] != 'list_view')
        hide
    @endif">
    <table class="table table-bordered table-striped" id="project_task_table">
        <thead>
            <tr>
                <th> @lang('messages.action')</th>
                <th class="col-md-4"> @lang('project::lang.subject')</th>
                <th class="col-md-2"> @lang('project::lang.assigned_to')</th>
                <th> @lang('project::lang.priority')</th>
                <th> @lang('business.start_date')</th>
                <th>@lang('project::lang.due_date')</th>
                <th>@lang('sale.status')</th>
                <th>@lang('project::lang.assigned_by')</th>
                <th>{{ $task_custom_field_1 }}</th>
                <th>{{ $task_custom_field_2 }}</th>
                <th>{{ $task_custom_field_3 }}</th>
                <th>{{ $task_custom_field_4 }}</th>
            </tr>
        </thead>
    </table>
</div>

<div class="custom-kanban-board
    @if(isset($project->settings['task_view']) &&
    $project->settings['task_view'] != 'kanban')
        hide
    @endif">
    <div class="page">
        <div class="main">
            <div class="meta-tasks-wrapper">
                <div id="myKanban" class="meta-tasks"></div>
            </div>
        </div>
    </div>
</div>