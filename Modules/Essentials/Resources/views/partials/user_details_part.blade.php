<div class="clearfix"></div>
<hr>
<div class="col-md-12">
	<h4>@lang('essentials::lang.hrm_details'):</h4>
</div>
<div class="col-md-12">
	<p><strong>@lang('essentials::lang.department'):</strong> {{$user_department->name ?? ''}}</p>
	<p><strong>@lang('essentials::lang.designation'):</strong> {{$user_designstion->name ?? ''}}</p>
	<p>
		<strong>@lang('essentials::lang.salary'):</strong> 
		@if(!empty($user->essentials_salary) && !empty($user->essentials_pay_period))
			@format_currency($user->essentials_salary) @lang('essentials::lang.per')
			@if($user->essentials_pay_period == 'week')
				{{__('essentials::lang.week')}}
			@else
				{{__('lang_v1.'.$user->essentials_pay_period)}}
			@endif
		@endif
	</p>

	<p>
		<strong>@lang('essentials::lang.pay_cycle'):</strong>
		@if(!empty($user->essentials_pay_period))
			@if($user->essentials_pay_period == 'week')
				{{__('essentials::lang.week')}}
			@else
				{{__('lang_v1.month')}}
			@endif
		@endif
	</p>

	<p>
		<strong>@lang('lang_v1.primary_work_location'):</strong>
		@if(!empty($work_location))
			{{$work_location->name}}
		@else
			{{__('report.all_locations')}}
		@endif
	</p>
</div>

@if(\Illuminate\Support\Facades\Schema::hasTable('pjt_project_tasks') || \Illuminate\Support\Facades\Schema::hasTable('assets') || \Illuminate\Support\Facades\Schema::hasTable('crm_schedules'))
<div class="col-md-12">
	<hr>
	<h4><i class="fas fa-network-wired"></i> Integrasi Seluruh Modul:</h4>
	<div class="row">
		@if(\Illuminate\Support\Facades\Schema::hasTable('pjt_project_tasks'))
		@php
			$assigned_tasks_count = \DB::table('pjt_project_task_members')
				->where('user_id', $user->id)
				->count();
		@endphp
		<div class="col-md-4 col-sm-6">
			<div class="info-box bg-aqua">
				<span class="info-box-icon"><i class="fas fa-tasks"></i></span>
				<div class="info-box-content">
					<span class="info-box-text">Project Tasks</span>
					<span class="info-box-number">{{ $assigned_tasks_count }}</span>
					<span class="progress-description">
						Tugas Proyek Terdaftar
					</span>
				</div>
			</div>
		</div>
		@endif

		@if(\Illuminate\Support\Facades\Schema::hasTable('crm_schedules'))
		@php
			$crm_followups_count = \DB::table('crm_schedule_users')
				->where('user_id', $user->id)
				->count();
		@endphp
		<div class="col-md-4 col-sm-6">
			<div class="info-box bg-green">
				<span class="info-box-icon"><i class="fas fa-headset"></i></span>
				<div class="info-box-content">
					<span class="info-box-text">CRM Follow-ups</span>
					<span class="info-box-number">{{ $crm_followups_count }}</span>
					<span class="progress-description">
						Jadwal Follow-up CRM
					</span>
				</div>
			</div>
		</div>
		@endif

		@if(\Illuminate\Support\Facades\Schema::hasTable('repair_job_sheets'))
		@php
			$repair_jobs_count = \DB::table('repair_job_sheets')
				->where('service_staff', $user->id)
				->count();
		@endphp
		<div class="col-md-4 col-sm-6">
			<div class="info-box bg-yellow">
				<span class="info-box-icon"><i class="fas fa-tools"></i></span>
				<div class="info-box-content">
					<span class="info-box-text">Repair Job Sheets</span>
					<span class="info-box-number">{{ $repair_jobs_count }}</span>
					<span class="progress-description">
						Nota Perbaikan Teknisi
					</span>
				</div>
			</div>
		</div>
		@endif
	</div>
</div>
@endif