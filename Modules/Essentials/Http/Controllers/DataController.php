<?php

namespace Modules\Essentials\Http\Controllers;

use App\Business;
use App\BusinessLocation;
use App\Category;
use App\User;
use App\Utils\ModuleUtil;
use App\Utils\TransactionUtil;
use Carbon\Carbon;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Menu;
use Modules\Essentials\Entities\DocumentShare;
use Modules\Essentials\Entities\EssentialsAllowanceAndDeduction;
use Modules\Essentials\Entities\EssentialsHoliday;
use Modules\Essentials\Entities\EssentialsLeave;
use Modules\Essentials\Entities\EssentialsTodoComment;
use Modules\Essentials\Entities\EssentialsUserAllowancesAndDeduction;
use Modules\Essentials\Entities\Reminder;
use Modules\Essentials\Entities\ToDo;

class DataController extends Controller
{
    /**
     * Parses notification message from database.
     *
     * @return array
     */
    public function parse_notification($notification)
    {
        $notification_data = [];
        if ($notification->type ==
            'Modules\Essentials\Notifications\DocumentShareNotification') {
            $notifiction_data = DocumentShare::documentShareNotificationData($notification->data);
            $notification_data = [
                'msg' => $notifiction_data['msg'],
                'icon_class' => $notifiction_data['icon'],
                'link' => $notifiction_data['link'],
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at->diffForHumans(),
            ];
        } elseif ($notification->type ==
            'Modules\Essentials\Notifications\NewMessageNotification') {
            $data = $notification->data;
            $msg = __('essentials::lang.new_message_notification', ['sender' => $data['from']]);

            $notification_data = [
                'msg' => $msg,
                'icon_class' => 'fas fa-envelope bg-green',
                'link' => action([\Modules\Essentials\Http\Controllers\EssentialsMessageController::class, 'index']),
                'read_at' => $notification->read_at,
                'created_at' => $notification->created_at->diffForHumans(),
            ];
        } elseif ($notification->type ==
            'Modules\Essentials\Notifications\NewLeaveNotification') {
            $data = $notification->data;

            $employee = User::find($data['applied_by']);

            if (! empty($employee)) {
                $msg = __('essentials::lang.new_leave_notification', ['employee' => $employee->user_full_name, 'ref_no' => $data['ref_no']]);

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'fas fa-user-times bg-green',
                    'link' => action([\Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'index']),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        } elseif ($notification->type ==
            'Modules\Essentials\Notifications\LeaveStatusNotification') {
            $data = $notification->data;

            $admin = User::find($data['changed_by']);

            if (! empty($admin)) {
                $msg = __('essentials::lang.status_change_notification', ['status' => $data['status'], 'ref_no' => $data['ref_no'], 'admin' => $admin->user_full_name]);

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'fas fa-user-times bg-green',
                    'link' => action([\Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'index']),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        } elseif ($notification->type ==
            'Modules\Essentials\Notifications\PayrollNotification') {
            $data = $notification->data;

            $month = \Carbon::createFromFormat('m', $data['month'])->format('F');

            $msg = '';

            $created_by = User::find($data['created_by']);

            if (! empty($created_by)) {
                if ($data['action'] == 'created') {
                    $msg = __('essentials::lang.payroll_added_notification', ['month_year' => $month.'/'.$data['year'], 'ref_no' => $data['ref_no'], 'created_by' => $created_by->user_full_name]);
                } elseif ($data['action'] == 'updated') {
                    $msg = __('essentials::lang.payroll_updated_notification', ['month_year' => $month.'/'.$data['year'], 'ref_no' => $data['ref_no'], 'created_by' => $created_by->user_full_name]);
                }

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'fas fa-money-bill-alt bg-green',
                    'link' => action([\Modules\Essentials\Http\Controllers\PayrollController::class, 'index']),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        } elseif ($notification->type ==
            'Modules\Essentials\Notifications\NewTaskNotification') {
            $data = $notification->data;

            $assigned_by = User::find($data['assigned_by']);

            if (! empty($assigned_by)) {
                $msg = __('essentials::lang.new_task_notification', ['assigned_by' => $assigned_by->user_full_name, 'task_id' => $data['task_id']]);

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'ion ion-clipboard bg-green',
                    'link' => action([\Modules\Essentials\Http\Controllers\ToDoController::class, 'show'], $data['id']),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        } elseif ($notification->type ==
            'Modules\Essentials\Notifications\NewTaskCommentNotification') {
            $data = $notification->data;

            $comment = EssentialsTodoComment::with(['task', 'added_by'])->find($data['comment_id']);
            if (! empty($comment) && $comment->task) {
                $msg = __('essentials::lang.new_task_comment_notification', ['added_by' => $comment->added_by->user_full_name, 'task_id' => $comment->task->task_id]);

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'fas fa-envelope bg-green',
                    'link' => action([\Modules\Essentials\Http\Controllers\ToDoController::class, 'show'], $comment->task->id),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        } elseif ($notification->type ==
            'Modules\Essentials\Notifications\NewTaskDocumentNotification') {
            $data = $notification->data;

            $uploaded_by = User::find($data['uploaded_by']);

            if (! empty($uploaded_by)) {
                $msg = __('essentials::lang.new_task_document_notification', ['uploaded_by' => $uploaded_by->user_full_name, 'task_id' => $data['task_id']]);

                $notification_data = [
                    'msg' => $msg,
                    'icon_class' => 'fas fa-file bg-green',
                    'link' => action([\Modules\Essentials\Http\Controllers\ToDoController::class, 'show'], $data['id']),
                    'read_at' => $notification->read_at,
                    'created_at' => $notification->created_at->diffForHumans(),
                ];
            }
        }

        return $notification_data;
    }

    /**
     * Defines user permissions for the module.
     *
     * @return array
     */
    public function user_permissions()
    {
        return [
            [
                'value' => 'essentials.crud_leave_type',
                'label' => __('essentials::lang.crud_leave_type'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_all_leave',
                'label' => __('essentials::lang.crud_all_leave'),
                'default' => false,
                'is_radio' => true,
                'radio_input_name' => 'leave_crud',
            ],
            [
                'value' => 'essentials.crud_own_leave',
                'label' => __('essentials::lang.crud_own_leave'),
                'default' => false,
                'is_radio' => true,
                'radio_input_name' => 'leave_crud',
            ],
            [
                'value' => 'essentials.approve_leave',
                'label' => __('essentials::lang.approve_leave'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_all_attendance',
                'label' => __('essentials::lang.crud_all_attendance'),
                'default' => false,
                'is_radio' => true,
                'radio_input_name' => 'attendance_crud',
            ],
            [
                'value' => 'essentials.view_own_attendance',
                'label' => __('essentials::lang.view_own_attendance'),
                'default' => false,
                'is_radio' => true,
                'radio_input_name' => 'attendance_crud',
            ],
            [
                'value' => 'essentials.allow_users_for_attendance_from_web',
                'label' => __('essentials::lang.allow_users_for_attendance_from_web'),
                'default' => false,
            ],
            [
                'value' => 'essentials.allow_users_for_attendance_from_api',
                'label' => __('essentials::lang.allow_users_for_attendance_from_api'),
                'default' => false,
            ],
            [
                'value' => 'essentials.view_allowance_and_deduction',
                'label' => __('essentials::lang.view_pay_component'),
                'default' => false,
            ],
            [
                'value' => 'essentials.add_allowance_and_deduction',
                'label' => __('essentials::lang.add_pay_component'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_department',
                'label' => __('essentials::lang.crud_department'),
                'default' => false,
            ],
            [
                'value' => 'essentials.crud_designation',
                'label' => __('essentials::lang.crud_designation'),
                'default' => false,
            ],

            [
                'value' => 'essentials.view_all_payroll',
                'label' => __('essentials::lang.view_all_payroll'),
                'default' => false,
            ],
            [
                'value' => 'essentials.create_payroll',
                'label' => __('essentials::lang.add_payroll'),
                'default' => false,
            ],
            [
                'value' => 'essentials.update_payroll',
                'label' => __('essentials::lang.edit_payroll'),
                'default' => false,
            ],
            [
                'value' => 'essentials.delete_payroll',
                'label' => __('essentials::lang.delete_payroll'),
                'default' => false,
            ],
            [
                'value' => 'essentials.assign_todos',
                'label' => __('essentials::lang.assign_todos'),
                'default' => false,
            ],
            [
                'value' => 'essentials.add_todos',
                'label' => __('essentials::lang.add_todos'),
                'default' => false,
            ],
            [
                'value' => 'essentials.edit_todos',
                'label' => __('essentials::lang.edit_todos'),
                'default' => false,
            ],
            [
                'value' => 'essentials.delete_todos',
                'label' => __('essentials::lang.delete_todos'),
                'default' => false,
            ],
            [
                'value' => 'essentials.create_message',
                'label' => __('essentials::lang.create_message'),
                'default' => false,
            ],
            [
                'value' => 'essentials.view_message',
                'label' => __('essentials::lang.view_message'),
                'default' => false,
            ],
            [
                'value' => 'essentials.access_sales_target',
                'label' => __('essentials::lang.access_sales_target'),
                'default' => false,
            ],
            [
                'value' => 'essentials.edit_knowledge_base',
                'label' => __('essentials::lang.edit_enowledge_base'),
                'default' => false,
            ],
            [
                'value' => 'essentials.delete_knowledge_base',
                'label' => __('essentials::lang.delete_enowledge_base'),
                'default' => false,
            ],
        ];
    }

    /**
     * Superadmin package permissions
     *
     * @return array
     */
    public function superadmin_package()
    {
        return [
            [
                'name' => 'essentials_module',
                'label' => __('essentials::lang.essentials_module'),
                'default' => false,
            ],
        ];
    }

    /**
     * Adds Essentials menus
     *
     * @return null
     */
    public function modifyAdminMenu()
    {
        $module_util = new ModuleUtil();

        $business_id = session()->get('user.business_id');
        $is_essentials_enabled = (bool) $module_util->hasThePermissionInSubscription($business_id, 'essentials_module');

        if ($is_essentials_enabled) {
            Menu::modify('admin-sidebar-menu', function ($menu) {
                $menu->url(
                        action([\Modules\Essentials\Http\Controllers\DashboardController::class, 'hrmDashboard']),
                        __('essentials::lang.hrm'),
                        ['icon' => '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-users-group"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M10 13a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M8 21v-1a2 2 0 0 1 2 -2h4a2 2 0 0 1 2 2v1" /><path d="M15 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M17 10h2a2 2 0 0 1 2 2v1" /><path d="M5 5a2 2 0 1 0 4 0a2 2 0 0 0 -4 0" /><path d="M3 13v-1a2 2 0 0 1 2 -2h2" /></svg>', 'active' => request()->segment(1) == 'hrm', 'style' => config('app.env') == 'demo' ? 'background-color: #605ca8 !important;color:white' : '']
                    )
                ->order(87);

                $menu->url(
                    action([\Modules\Essentials\Http\Controllers\ToDoController::class, 'index']),
                    __('essentials::lang.essentials'),
                    ['icon' => '<svg  xmlns="http://www.w3.org/2000/svg"  width="24"  height="24"  viewBox="0 0 24 24"  fill="none"  stroke="currentColor"  stroke-width="2"  stroke-linecap="round"  stroke-linejoin="round"  class="icon icon-tabler icons-tabler-outline icon-tabler-circle-check"><path stroke="none" d="M0 0h24v24H0z" fill="none"/><path d="M12 12m-9 0a9 9 0 1 0 18 0a9 9 0 1 0 -18 0" /><path d="M9 12l2 2l4 -4" /></svg>', 'active' => request()->segment(1) == 'essentials', 'style' => config('app.env') == 'demo' ? 'background-color: #001f3f !important;color:white' : '']
                )
                ->order(87);
            });
        }
    }

    /**
     * Function to add essential module taxonomies
     *
     * @return array
     */
    public function addTaxonomies()
    {
        return [
            'hrm_department' => [
                'taxonomy_label' => __('essentials::lang.department'),
                'heading' => __('essentials::lang.departments'),
                'sub_heading' => __('essentials::lang.manage_departments'),
                'enable_taxonomy_code' => true,
                'taxonomy_code_label' => __('essentials::lang.department_id'),
                'taxonomy_code_help_text' => __('essentials::lang.department_code_help'),
                'enable_sub_taxonomy' => false,
                'navbar' => 'essentials::layouts.nav_hrm',
            ],

            'hrm_designation' => [
                'taxonomy_label' => __('essentials::lang.designation'),
                'heading' => __('essentials::lang.designations'),
                'sub_heading' => __('essentials::lang.manage_designations'),
                'enable_taxonomy_code' => false,
                'taxonomy_code_help_text' => __('essentials::lang.designation_code_help'),
                'enable_sub_taxonomy' => false,
                'navbar' => 'essentials::layouts.nav_hrm',
            ],
        ];
    }

    /**
     * Function to generate view parts
     *
     * @param  array  $data
     */
    public function moduleViewPartials($data)
    {
        if ($data['view'] == 'manage_user.create' || $data['view'] == 'manage_user.edit') {
            $business_id = session()->get('business.id');
            $departments = Category::forDropdown($business_id, 'hrm_department');
            $designations = Category::forDropdown($business_id, 'hrm_designation');
            $pay_comoponenets = EssentialsAllowanceAndDeduction::forDropdown($business_id);

            $user = ! empty($data['user']) ? $data['user'] : null;

            $allowance_deduction_ids = [];
            if (! empty($user)) {
                $allowance_deduction_ids = EssentialsUserAllowancesAndDeduction::where('user_id', $user->id)
                                            ->pluck('allowance_deduction_id')
                                            ->toArray();
            }

            $locations = BusinessLocation::forDropdown($business_id, false, false, true, false);

            return view('essentials::partials.user_form_part', compact('departments', 'designations', 'user', 'pay_comoponenets', 'allowance_deduction_ids', 'locations'))
                ->render();
        } elseif ($data['view'] == 'manage_user.show') {
            $user = ! empty($data['user']) ? $data['user'] : null;
            $user_department = Category::find($user->essentials_department_id);
            $user_designstion = Category::find($user->essentials_designation_id);
            $work_location = BusinessLocation::find($user->location_id);

            return view('essentials::partials.user_details_part', compact('user_department', 'user_designstion', 'user', 'work_location'))
                ->render();
        }
    }

    /**
     * Function to process model after being saved
     *
     * @param  array  $data['event' => 'Event name', 'model_instance' => 'Model instance']
     */
    public function afterModelSaved($data)
    {
        if ($data['event'] = 'user_saved') {
            $user = $data['model_instance'];
            $user->essentials_department_id = request()->input('essentials_department_id');
            $user->essentials_designation_id = request()->input('essentials_designation_id');
            $user->essentials_salary = request()->input('essentials_salary');
            $user->essentials_pay_period = request()->input('essentials_pay_period');
            $user->essentials_pay_cycle = request()->input('essentials_pay_cycle');
            $user->location_id = request()->input('location_id');
            $user->save();

            $non_deleteable_pc_ids = $this->getNonDeletablePayComponents($user->business_id, $user->id);

            //delete  existing pay component
            EssentialsUserAllowancesAndDeduction::where('user_id', $user->id)
                    ->whereNotIn('allowance_deduction_id', $non_deleteable_pc_ids)
                    ->delete();

            //if pay component exist add to db
            if (! empty(request()->input('pay_components'))) {
                $pay_components = request()->input('pay_components');
                foreach ($pay_components as $key => $pay_component) {
                    EssentialsUserAllowancesAndDeduction::insert(['user_id' => $user->id, 'allowance_deduction_id' => $pay_component]);
                }
            }
        }
    }

    public function profitLossReportData($data)
    {
        $business_id = $data['business_id'];
        $location_id = ! empty($data['location_id']) ? $data['location_id'] : null;
        $start_date = ! empty($data['start_date']) ? $data['start_date'] : null;
        $end_date = ! empty($data['end_date']) ? $data['end_date'] : null;
        $user_id = ! empty($data['user_id']) ? $data['user_id'] : null;

        $total_payroll = $this->__getTotalPayroll(
            $business_id,
            $start_date,
            $end_date,
            $location_id,
            $user_id
        );

        $report_data = [
            //left side data
            [
                [
                    'value' => $total_payroll,
                    'label' => __('essentials::lang.total_payroll'),
                    'add_to_net_profit' => true,
                ],
            ],

            //right side data
            [],
        ];

        return $report_data;
    }

    /**
     * Calculates total payroll
     *
     * @param  int  $business_id
     * @param  string  $start_date = null
     * @param  string  $end_date = null
     * @param  int  $location_id = null
     * @return array
     */
    private function __getTotalPayroll(
        $business_id,
        $start_date = null,
        $end_date = null,
        $location_id = null,
        $user_id = null
        ) {
        $transactionUtil = new TransactionUtil();

        $transaction_totals = $transactionUtil->getTransactionTotals(
            $business_id,
            ['payroll'],
            $start_date,
            $end_date,
            $location_id,
            $user_id
            );

        return $transaction_totals['total_payroll'];
    }

    /**
     * Fetches all calender events for the module
     *
     * @param  array  $data
     * @return array
     */
    public function calendarEvents($data)
    {
        $events = [];
        if (in_array('todo', $data['events'])) {
            $todos = ToDo::where('business_id', $data['business_id'])
                            ->with(['users'])
                            ->where(function ($query) use ($data) {
                                $query->where('created_by', $data['user_id'])
                                    ->orWhereHas('users', function ($q) use ($data) {
                                        $q->where('user_id', $data['user_id']);
                                    });
                            })
                            ->whereBetween(DB::raw('date(date)'), [$data['start_date'], $data['end_date']])
                            ->get();

            foreach ($todos as $todo) {
                $events[] = [
                    'title' => $todo->task,
                    'start' => $todo->date,
                    'end' => $todo->end_date,
                    'url' => action([\Modules\Essentials\Http\Controllers\ToDoController::class, 'index']),
                    'backgroundColor' => '#33006F',
                    'borderColor' => '#33006F',
                    'event_type' => 'todo',
                    'allDay' => false,
                ];
            }
        }

        if (in_array('holiday', $data['events'])) {
            $holidays_query = EssentialsHoliday::where('business_id', $data['business_id']);

            if (! empty($data['user_id'])) {
                $user = User::where('business_id', $data['business_id'])->find($data['user_id']);
                $permitted_locations = $user->permitted_locations();
                if ($permitted_locations != 'all') {
                    $holidays_query->where(function ($query) use ($permitted_locations) {
                        $query->whereIn('location_id', $permitted_locations)
                            ->orWhereNull('location_id');
                    });
                }
            }

            if (! empty($data['location_id'])) {
                $holidays_query->where('location_id', $data['location_id']);
            }

            $holidays = $holidays_query->whereDate('start_date', '>=',
                            $data['start_date'])
                            ->whereDate('start_date', '<=', $data['end_date'])
                            ->get();

            foreach ($holidays as $holiday) {
                $events[] = [
                    'title' => $holiday->name,
                    'start' => $holiday->start_date,
                    'end' => $holiday->end_date,
                    'url' => action([\Modules\Essentials\Http\Controllers\EssentialsHolidayController::class, 'index']),
                    'backgroundColor' => '#568203',
                    'borderColor' => '#568203',
                    'allDay' => true,
                    'event_type' => 'holiday',
                ];
            }
        }

        if (in_array('leaves', $data['events'])) {
            $leaves_query = EssentialsLeave::where('essentials_leaves.business_id', $data['business_id'])
                        ->join('users as u', 'u.id', '=', 'essentials_leaves.user_id')
                        ->join('essentials_leave_types as lt', 'lt.id', '=', 'essentials_leaves.essentials_leave_type_id')
                        ->select([
                            'essentials_leaves.id',
                            DB::raw("CONCAT(COALESCE(u.surname, ''), ' ', COALESCE(u.first_name, ''), ' ', COALESCE(u.last_name, '')) as user"),
                            'lt.leave_type',
                            'start_date',
                            'end_date',
                        ]);

            if (! empty($data['user_id'])) {
                $leaves_query->where('essentials_leaves.user_id', $data['user_id']);
            }

            $leaves = $leaves_query->whereDate('essentials_leaves.start_date', '>=', $data['start_date'])
                            ->whereDate('essentials_leaves.start_date', '<=', $data['end_date'])
                            ->get();
            foreach ($leaves as $leave) {
                $events[] = [
                    'title' => $leave->user,
                    'title_html' => $leave->user.'<br>'.$leave->leave_type,
                    'start' => $leave->start_date,
                    'end' => $leave->end_date,
                    'url' => action([\Modules\Essentials\Http\Controllers\EssentialsLeaveController::class, 'index']),
                    'backgroundColor' => '#BA0021',
                    'borderColor' => '#BA0021',
                    'allDay' => true,
                    'event_type' => 'leaves',
                ];
            }
        }

        if (in_array('reminder', $data['events'])) {
            $reminder_events = Reminder::getReminders($data);
            $events = array_merge($events, $reminder_events);
        }

        return $events;
    }

    /**
     * List of calendar event types
     *
     * @return array
     */
    public function eventTypes()
    {
        return [
            'todo' => [
                'label' => __('essentials::lang.todo'),
                'color' => '#33006F',
            ],
            'holiday' => [
                'label' => __('essentials::lang.holidays'),
                'color' => '#568203',
            ],
            'leaves' => [
                'label' => __('essentials::lang.leaves'),
                'color' => '#BA0021',
            ],
            'reminder' => [
                'label' => __('essentials::lang.reminders'),
                'color' => '#ff851b',
            ],
        ];
    }

    /**
     * Returns addtional js, css, html and files which
     * will be included in the app layout
     *
     * @return array
     */
    public function get_additional_script()
    {
        $additional_js = '';
        $additional_css = '';
        $additional_html =
        '<div class="modal fade" id="task_modal" tabindex="-1" role="dialog" 
        aria-labelledby="gridSystemModalLabel">
        </div>';
        $additional_views = ['essentials::todo.todo_javascript'];

        return [
            'additional_js' => $additional_js,
            'additional_css' => $additional_css,
            'additional_html' => $additional_html,
            'additional_views' => $additional_views,
        ];
    }

    /**
     * Returns pay components who has applicable date
     * and assigned to given user
     *
     * @return array
     */
    public function getNonDeletablePayComponents($business_id, $user_id)
    {
        $ads = EssentialsAllowanceAndDeduction::join('essentials_user_allowance_and_deductions as euad', 'euad.allowance_deduction_id', '=', 'essentials_allowances_and_deductions.id')
                ->whereNotNull('essentials_allowances_and_deductions.applicable_date')
                ->where('business_id', $business_id)
                ->where('euad.user_id', $user_id)
                ->get();

        $ids = $ads->pluck('id')->toArray();

        return $ids;
    }

    /**
     * Returns todo dropdown
     *
     * @param $business_id
     * @return array
     */
    public function getTodosDropdown($business_id)
    {
        $todos = ToDo::where('business_id', $business_id)
                    ->select(DB::raw("CONCAT(task, ' (', task_id , ')') AS task_name"), 'id')
                    ->pluck('task_name', 'id')
                    ->toArray();

        return $todos;
    }

    /**
     * Returns task for user
     *
     * @param $user_id
     * @return array
     */
    public function getAssignedTaskForUser($user_id)
    {
        $task_ids = DB::table('essentials_todos_users')
                    ->where('user_id', $user_id)
                    ->pluck('todo_id')
                    ->toArray();

        return $task_ids;
    }

    /**
     * Inbox Report options
     *
     * @return array
     */
    public function InboxReportOptions()
    {
        return [
            [
                'name' => 'attendance_report',
                'label' => __('essentials::lang.attendance_report'),
                'default' => true,
            ],
        ];
    }

    /**
     * custom dashboard options
     *
     * @return array
     */
    public function CustomDashboardOptions()
    {
        return [
            [
                'name' => 'customdashboard_widget_attendance_reports',
                'label' => __('essentials::lang.attendance_report'),
                'size' => 100,
                'module_name' => __('essentials::lang.essentials_module'),
                'range' => true,
                'html_text' => false,
                'location' => false,
                'show_data' => true,
            ],
            [
                'name' => 'customdashboard_widget_holiday',
                'label' => __('essentials::lang.holiday'),
                'size' => 100,
                'module_name' => __('essentials::lang.essentials_module'),
                'range' => true,
                'html_text' => false,
                'location' => true,
                'show_data' => true,
            ],
            [
                'name' => 'customdashboard_widget_remainders',
                'label' => __('essentials::lang.reminders'),
                'size' => 100,
                'module_name' => __('essentials::lang.essentials_module'),
                'range' => true,
                'html_text' => false,
                'location' => false,
                'show_data' => true,
            ],
            [
                'name' => 'customdashboard_widget_leaves',
                'label' => __('essentials::lang.leaves'),
                'size' => 25,
                'module_name' => __('essentials::lang.hrm'),
                'range' => false,
                'html_text' => false,
                'location' => false,
                'show_data' => false,
            ],
        ];
    }

    /**
     * Inbox Report attendace_report
     *
     * @return array
     */
    public function customdashboard_widget_attendance_report($business)
    {
        
        $inbox_settings = json_decode($business->inbox_report_settings);
        $frequency = $inbox_settings->frequency ?? null;
        $date = Carbon::now()->subDays(1);

        if ($frequency === 'daily') {
            $users = User::leftJoin('essentials_attendances', function ($join) use ($date) {
                $join->on('users.id', '=', 'essentials_attendances.user_id')
                    ->whereDate('essentials_attendances.clock_in_time', $date);
            })
                ->leftJoin('essentials_shifts as es', 'es.id', '=', 'essentials_attendances.essentials_shift_id')
                ->where('users.business_id', $business->id)
                ->select('essentials_attendances.*',
                    DB::raw("CONCAT(COALESCE(users.surname, ''), ' ', COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                    'es.name as shift_name',
                    DB::raw("CASE WHEN essentials_attendances.id IS NOT NULL THEN 'Present' ELSE 'Absent' END as status")
                )
                ->get();

            $transactionUtil = new TransactionUtil();

            return [
                'html' => view('essentials::reports.email_report', compact('users', 'transactionUtil', 'business', 'date')),
            ];
        }

        if ($frequency === 'weekly') {
            $startDate = Carbon::now()->startOfWeek();
        }

        if ($frequency === 'monthly') {
            $startDate = Carbon::now()->startOfMonth();
        }

        $endDate = Carbon::now()->subDays(1);

        $users = User::leftJoin('essentials_attendances', function ($join) use ($startDate, $endDate) {
                            $join->on('users.id', '=', 'essentials_attendances.user_id')
                                ->whereBetween(DB::raw('DATE(essentials_attendances.clock_in_time)'), [$startDate, $endDate]);
                        })
                        ->where('users.business_id', $business->id)
                        ->select(
                            DB::raw("CONCAT(COALESCE(users.surname, ''), ' ', COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                            DB::raw("COUNT(essentials_attendances.id) as present_count"),
                            DB::raw("DATEDIFF('$endDate', '$startDate') - COUNT(essentials_attendances.id) as absent_count")
                        )
                        ->groupBy('users.id')
                        ->get();
            
            $transactionUtil = new TransactionUtil();

        return [
            'html' => view('essentials::reports.email_report_weekly_monthly', compact('users', 'endDate', 'startDate', 'transactionUtil', 'business')),
        ];
    }
    
    // customdashboard_widget_attendance_reports custom dashboard

    public function customdashboard_widget_attendance_reports($dashboard_detail)
    {
        //$size, $start_date, $end_date, $location, $heading, $range

        $business_id = session()->get('user.business_id');

        if ($dashboard_detail->show_data == 'show_loggedin_user_data') {
            $user_id = session()->get('user.id');
        }

        $business = Business::findOrFail($business_id);

        $date = $dashboard_detail->end_date;

        if ($dashboard_detail->start_date->diffInDays($dashboard_detail->end_date) == 0) {
            $users = User::leftJoin('essentials_attendances', function ($join) use ($date) {
                $join->on('users.id', '=', 'essentials_attendances.user_id')
                    ->whereDate('essentials_attendances.clock_in_time', $date);
            })
                ->leftJoin('essentials_shifts as es', 'es.id', '=', 'essentials_attendances.essentials_shift_id')
                ->where('users.business_id', $business->id);

            if (!empty($user_id)) {
                $users = $users->where('users.id', $user_id);
            }

            $users = $users->select('essentials_attendances.*',
                DB::raw("CONCAT(COALESCE(users.surname, ''), ' ', COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
                'es.name as shift_name',
                DB::raw("CASE WHEN essentials_attendances.id IS NOT NULL THEN 'Present' ELSE 'Absent' END as status")
            )
                ->get();

            $transactionUtil = new TransactionUtil();

            return [
                'html' => view('essentials::custom_dashboard.daily_attendance', compact('users', 'transactionUtil', 'business', 'date', 'dashboard_detail')),
            ];
        }

        $endDate = $dashboard_detail->end_date;
        $startDate = $dashboard_detail->start_date;

        $users = User::leftJoin('essentials_attendances', function ($join) use ($startDate, $endDate) {
            $join->on('users.id', '=', 'essentials_attendances.user_id')
                ->whereBetween(DB::raw('DATE(essentials_attendances.clock_in_time)'), [$startDate, $endDate]);
        })
            ->where('users.business_id', $business->id);
        if (!empty($user_id)) {
            $users = $users->where('users.id', $user_id);
        }
        $users = $users->select(
            DB::raw("CONCAT(COALESCE(users.surname, ''), ' ', COALESCE(users.first_name, ''), ' ', COALESCE(users.last_name, '')) as user"),
            DB::raw("COUNT(essentials_attendances.id) as present_count"),
            DB::raw("DATEDIFF('$endDate', '$startDate') - COUNT(essentials_attendances.id) as absent_count")
        )
            ->groupBy('users.id')
            ->get();

        return [
            'html' => view('essentials::custom_dashboard.range_by_attendance', compact('users', 'endDate', 'startDate', 'dashboard_detail')),
        ];
    }

    public function customdashboard_widget_holiday($dashboard_detail){

        $essentialsUtil = new \Modules\Essentials\Utils\EssentialsUtil;

        $business_id = session()->get('user.business_id');

        $permitted_locations = auth()->user()->permitted_locations();

        $endDate = $dashboard_detail->end_date;
        $startDate = $dashboard_detail->start_date;
        
        $holidays = $essentialsUtil->Gettotalholiday($business_id, $dashboard_detail->location , $startDate, $endDate, $permitted_locations);

        $holidays = $holidays->get();
    
        return [
            'html' => view('essentials::custom_dashboard.holiday', compact('holidays', 'endDate', 'startDate', 'dashboard_detail')),
        ];
    }

    public function customdashboard_widget_remainders($dashboard_detail){

        $business_id = session()->get('user.business_id');
        
        $user_id = null;
        if ($dashboard_detail->show_data == 'show_loggedin_user_data') {
            $user_id = session()->get('user.id');
        }
        $endDate = $dashboard_detail->end_date;
        $startDate = $dashboard_detail->start_date;


        // Fetch reminders for the given business and date range, optionally filtered by the currently logged-in user
        $reminders = Reminder::where('business_id', $business_id)
            ->whereBetween(DB::raw('date(date)'), [$startDate, $endDate])
            ->when($user_id, function ($query) use ($user_id) {
                return $query->where('user_id', $user_id);
            })
            ->get();

        return [
            'html' => view('essentials::custom_dashboard.remainders', compact('reminders', 'endDate', 'startDate', 'dashboard_detail')),
        ];

    }

    public function customdashboard_widget_leaves($dashboard_detail){

        $business_id = session()->get('user.business_id');
        $user_id = auth()->user()->id;

        $today = new \Carbon('today');
        $one_month_from_today = \Carbon::now()->addMonth();
        $leaves = EssentialsLeave::where('business_id', $business_id)
            ->where('status', 'approved')
            ->whereDate('end_date', '>=', $today->format('Y-m-d'))
            ->whereDate('start_date', '<=', $one_month_from_today->format('Y-m-d'))
            ->with(['user', 'leave_type'])
            ->orderBy('start_date', 'asc')
            ->get();

        $todays_leaves = [];
        $upcoming_leaves = [];

        $users_leaves = [];
        foreach ($leaves as $leave) {
            $leave_start = \Carbon::parse($leave->start_date);
            $leave_end = \Carbon::parse($leave->end_date);

            if ($today->gte($leave_start) && $today->lte($leave_end)) {
                $todays_leaves[] = $leave;

                if ($leave->user_id == $user_id) {
                    $users_leaves[] = $leave;
                }
            } elseif ($today->lt($leave_start) && $leave_start->lte($one_month_from_today)) {
                $upcoming_leaves[] = $leave;

                if ($leave->user_id == $user_id) {
                    $users_leaves[] = $leave;
                }
            }
        }
        return [
            'html' => view('essentials::custom_dashboard.leave', compact('todays_leaves', 'upcoming_leaves', 'dashboard_detail')),
        ];
    }
}
