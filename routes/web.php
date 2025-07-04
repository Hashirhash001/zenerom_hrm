<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\MyTaskController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\MeetingController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\EmployeeController;
use App\Http\Controllers\TeamTaskController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\KpiMetricController;
use App\Http\Controllers\TimeEntryController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ProjectUserController;
use App\Http\Controllers\ConversationController;
use App\Http\Controllers\InternalMailController;
use App\Http\Controllers\LeaveRequestController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\TaskAssignedController;
use App\Http\Controllers\CalendarEventController;
use App\Http\Controllers\ProjectUpdateController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\ProjectServiceController;
use App\Http\Controllers\CustomerContactController;
use App\Http\Controllers\StaffTaskReportController;
use App\Http\Controllers\LeaveApplicationController;
use App\Http\Controllers\ProjectMilestoneController;
use App\Http\Controllers\ProjectDescriptionController;
use App\Http\Controllers\ProjectDescriptionFileController;


// Login route as the default landing page.
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::get('/', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'attempt'])->name('login.attempt');
Route::post('/check-in', [AttendanceController::class, 'checkIn'])->name('checkIn');
Route::post('/check-out', [AttendanceController::class, 'checkOut'])->name('checkOut');
Route::post('/break', [AttendanceController::class, 'break'])->name('break');
Route::post('/sync-timer', [AttendanceController::class, 'syncTimer']);
Route::get('/attendance-status', [AttendanceController::class, 'getAttendanceStatus']);
Route::get('/fetch-attendances', [AttendanceController::class, 'fetchAttendances']);

// Dashboard (accessible after login)
// Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
Route::middleware(['check.session'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard.index');
    Route::get('/dashboard/techhead', [DashboardController::class, 'techhead'])->name('dashboard.techhead');
    Route::get('/dashboard/teamlead', [DashboardController::class, 'teamlead'])->name('dashboard.teamlead');
    Route::get('/dashboard/staff', [DashboardController::class, 'staff'])->name('dashboard.staff');
    Route::get('/dashboard/projectmanager', [DashboardController::class, 'projectmanager'])->name('dashboard.projectmanager');
    Route::get('/dashboard/interns', [DashboardController::class, 'interns'])->name('dashboard.interns');
    Route::get('/dashboard/hr', [DashboardController::class, 'hr'])->name('dashboard.hr');
    Route::get('/dashboard/nocontent', [DashboardController::class, 'nocontent'])->name('dashboard.nocontent');

    // Departments routes
    Route::get('departments/search', [DepartmentController::class, 'search'])->name('department.search');
    Route::resource('departments', DepartmentController::class)->names([
        'index'   => 'department.index',
        'create'  => 'department.create',
        'store'   => 'department.store',
        'show'    => 'department.show',
        'edit'    => 'department.edit',
        'update'  => 'department.update',
        'destroy' => 'department.destroy',
    ]);

    // Roles routes
    Route::get('roles/search', [RoleController::class, 'search'])->name('role.search');
    Route::resource('roles', RoleController::class)->names([
        'index'   => 'role.index',
        'create'  => 'role.create',
        'store'   => 'role.store',
        'show'    => 'role.show',
        'edit'    => 'role.edit',
        'update'  => 'role.update',
        'destroy' => 'role.destroy',
    ]);

    // Employee resource routes
    Route::resource('employees', EmployeeController::class)->names([
        'index'   => 'employee.index',
        'create'  => 'employee.create',
        'store'   => 'employee.store',
        'show'    => 'employee.show',
        'edit'    => 'employee.edit',
        'update'  => 'employee.update',
        'destroy' => 'employee.destroy',
    ]);

    // Additional routes
    Route::get('employees/{employee}/view', [EmployeeController::class, 'viewDetails'])->name('employee.view');
    Route::get('employees/{employee}/resign', [EmployeeController::class, 'resignForm'])->name('employee.resign.form');
    Route::post('employees/{employee}/resign', [EmployeeController::class, 'updateResignation'])->name('employee.resign');
    Route::post('employees/{employee}/toggle', [EmployeeController::class, 'toggleActivation'])->name('employee.toggle');
    Route::post('employees/account/save', [EmployeeController::class, 'saveAccount'])->name('employee.account.save');
    // Route::get('employees/search', [EmployeeController::class, 'search'])->name('employee.search');
    Route::get('employee-search', [EmployeeController::class, 'search'])->name('employee.search');

    Route::post('employees/{employee}/access', [EmployeeController::class, 'saveAccessControl'])->name('employee.access.save');

    // reset password routes
    Route::get('reset-password', [ResetPasswordController::class, 'show'])->name('reset.password');
    Route::post('reset-password', [ResetPasswordController::class, 'update'])->name('reset.password.update');


    // clients routes
    Route::get('customers/search', [CustomerController::class, 'search'])->name('customer.search');
    Route::resource('customers', CustomerController::class)->names([
        'index'   => 'customer.index',
        'create'  => 'customer.create',
        'store'   => 'customer.store',
        'show'    => 'customer.show',
        'edit'    => 'customer.edit',
        'update'  => 'customer.update',
        'destroy' => 'customer.destroy',
    ]);

    // Nested resource for customer contacts (using shallow routing)
    Route::resource('customers.contacts', CustomerContactController::class)
        ->except(['destroy'])
        ->names([
            'store'   => 'customer.contact.store',
            'edit'    => 'customer.contact.edit',
            'update'  => 'customer.contact.update',
        ]);

    // Explicit route for deleting a contact using a shallow URL
    Route::delete('contacts/{customerContact}', [CustomerContactController::class, 'destroy'])
        ->name('customer.contact.destroy');

    // Service routes
    Route::get('services/search', [ServiceController::class, 'search'])->name('service.search');
    Route::resource('services', ServiceController::class)->names([
        'index'   => 'service.index',
        'create'  => 'service.create',
        'store'   => 'service.store',
        'show'    => 'service.show',
        'edit'    => 'service.edit',
        'update'  => 'service.update',
        'destroy' => 'service.destroy',
    ]);
    // Projects
    Route::get('projects/search', [ProjectController::class, 'search'])->name('project.search');
    Route::resource('projects', ProjectController::class)->names([
        'index'   => 'project.index',
        'create'  => 'project.create',
        'store'   => 'project.store',
        'show' => 'project.show',
        'edit'    => 'project.edit',
        'update'  => 'project.update',
        'destroy' => 'project.destroy',
    ]);

    // Additional routes for project-related actions (milestones, documents, status history)
    Route::post('projects/{project}/milestones', [ProjectController::class, 'addMilestone'])->name('project.milestone.store');
    Route::post('projects/{project}/documents', [ProjectController::class, 'addDocument'])->name('project.document.store');
    Route::post('projects/{project}/status-history', [ProjectController::class, 'addStatusHistory'])->name('project.status_history.store');

    Route::resource('project-services', ProjectServiceController::class)
        ->parameters(['project-services' => 'projectService'])
        ->names([
            'store'   => 'project_service.store',
            'edit'    => 'project_service.edit',
            'update'  => 'project_service.update',
            'destroy' => 'project_service.destroy',
        ]);
    Route::resource('project-milestones', ProjectMilestoneController::class)->only([
        'store',
        'edit',
        'update',
        'destroy'
    ])->names([
        'store'   => 'project_milestone.store',
        'edit'    => 'project_milestone.edit',
        'update'  => 'project_milestone.update',
        'destroy' => 'project_milestone.destroy',
    ]);
    // Staff assignment routes (using the updated pivot table "project_users")
    Route::post('project-users', [ProjectUserController::class, 'store'])->name('project_user.store');
    Route::post('project-users/toggle', [ProjectUserController::class, 'toggle'])->name('project_user.toggle');
    Route::delete('project-users/{id}', [ProjectUserController::class, 'destroy'])->name('project_user.destroy');

    Route::post('project-descriptions/storedata', [ProjectDescriptionController::class, 'storedata'])
        ->name('project_description.storedata');

    Route::resource('project-descriptions', ProjectDescriptionController::class)->names([
        'create'  => 'project_description.create',
        'store'   => 'project_description.store',
        'show'    => 'project_description.show',
        'edit'    => 'project_description.edit',
        'update'  => 'project_description.update',
        'destroy' => 'project_description.destroy',
    ]);
    Route::post('project-description-documents', [ProjectDescriptionFileController::class, 'store'])
        ->name('project_description_document.store');

    Route::delete('project-descriptions/{projectDescription}', [ProjectDescriptionController::class, 'destroy'])->name('project_description.destroy');

    Route::resource('project-description-files', ProjectDescriptionFileController::class)->only(['destroy'])->names([
        'destroy' => 'project_description_file.destroy',
    ]);

    Route::resource('project-updates', ProjectUpdateController::class)->names([
        'index'   => 'project_update.index',
        'store'   => 'project_update.store',
        'destroy' => 'project_update.destroy',
    ]);
    Route::get('/project_update/{id}/edit', [ProjectUpdateController::class, 'edit'])->name('project_update.edit');
    Route::put('/project-updates/{id}', [ProjectUpdateController::class, 'update'])->name('project_updates.update');


    Route::get('task-search', [TaskController::class, 'search'])->name('tasks.search');
    Route::resource('tasks', TaskController::class)->except(['show']);

    // Route for assigning staffs to a task.
    //Route::post('tasks/{task}/assign-staff', [TaskAssignedController::class, 'assignStaff'])->name('tasks.assignStaff');
    // Your resource routes for tasks (excluding show if not needed)
    Route::resource('tasks', \App\Http\Controllers\TaskController::class)->except(['show']);

    Route::post('tasks/{task}/assign-staff', [App\Http\Controllers\TaskAssignedController::class, 'assignStaff'])->name('tasks.assignStaff');

    Route::get('tasks/{task}/details', [TaskAssignedController::class, 'details'])->name('tasks.details');


    Route::get('assignments/{assignment}/edit', [TaskAssignedController::class, 'editAssignment'])->name('assignments.edit');
    Route::put('assignments/{assignment}', [TaskAssignedController::class, 'updateAssignment'])->name('assignments.update');

    Route::get('assignments/{assignment}/upload-document', [TaskAssignedController::class, 'getUploadDocumentForm'])->name('assignments.getUploadDocumentForm');
    Route::post('assignments/{assignment}/upload-document', [TaskAssignedController::class, 'uploadDocument'])->name('assignments.uploadDocument');

    Route::get('assignments/{assignment}/add-comment', [TaskAssignedController::class, 'getAddCommentForm'])->name('assignments.getAddCommentForm');
    Route::post('assignments/{assignment}/add-comment', [TaskAssignedController::class, 'addComment'])->name('assignments.addComment');
    Route::delete('assignments/{assignment}', [TaskAssignedController::class, 'destroy'])->name('assignments.destroy');


    Route::get('/my-tasks', [MyTaskController::class, 'index'])->name('my_tasks.index');
    Route::get('/my-tasks/today', [MyTaskController::class, 'today'])->name('my_tasks.today');
    Route::post('/my-tasks', [MyTaskController::class, 'store'])->name('my_tasks.store');
    Route::get('/my-tasks/{task}/details', [TaskAssignedController::class, 'detailssub'])->name('my-tasks.details');



    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
    Route::post('/attendance/update', [AttendanceController::class, 'update'])->name('attendance.update');
    Route::post('/attendance/approve', [AttendanceController::class, 'approve'])->name('attendance.approve');
    Route::get('/attendance/todays-report', [AttendanceController::class, 'todaysReport'])->name('attendance.todays_report');
    Route::post('/notifications/mark-as-read', [\App\Http\Controllers\NotificationController::class, 'markAsRead'])->name('notifications.markAsRead');
    Route::get('/notifications/all', [NotificationController::class, 'allNotifications'])->name('notifications.all');

    Route::get('/staff-task-report', [StaffTaskReportController::class, 'index'])->name('staff-task.report');

    Route::get('/attendance/work-from-office', [AttendanceController::class, 'workFromOffice'])->name('attendance.workFromOffice');
    Route::get('/attendance/work-from-home', [AttendanceController::class, 'workFromHome'])->name('attendance.workFromHome');
    Route::get('/attendance/leave-report', [AttendanceController::class, 'leaveReport'])->name('attendance.leaveReport');
    Route::get('/employee/department', [EmployeeController::class, 'departmentEmployees'])->name('employees.department');


    Route::get('/my-tasks/report', [MyTaskController::class, 'report'])->name('my_tasks.report');
    Route::get('/team-tasks/report', [TeamTaskController::class, 'report'])->name('team_tasks.report');
    Route::get('/project/department', [ProjectController::class, 'departmentProjects'])->name('projects.department');

    // Leave Requests
    Route::get('/leave-requests', [LeaveRequestController::class, 'index'])->name('leave_requests.index');
    Route::get('/leave-requests/create', [LeaveRequestController::class, 'create'])->name('leave_requests.create');
    Route::post('/leave-requests', [LeaveRequestController::class, 'store'])->name('leave_requests.store');
    Route::get('/leave-requests/{leaveRequest}', [LeaveRequestController::class, 'show'])->name('leave_requests.show');
    Route::post('/leave-requests/{leaveRequest}/approve', [LeaveRequestController::class, 'approve'])->name('leave_requests.approve');
    Route::post('/leave-requests/{leaveRequest}/reject', [LeaveRequestController::class, 'reject'])->name('leave_requests.reject');
});

// Chat (staff chat)
Route::resource('chats', ChatController::class);

// Internal Mails - supports file attachments and status changes
Route::resource('internal-mails', InternalMailController::class);

// Conversations (for project/task discussion)
Route::resource('conversations', ConversationController::class);


// Time Entries (for tracking work time)
Route::resource('time-entries', TimeEntryController::class);

// Invoices
Route::resource('invoices', InvoiceController::class);

// Payments
Route::resource('payments', PaymentController::class);

// Activity Logs
Route::resource('activity-logs', ActivityLogController::class);

// Settings (system configurations)
Route::resource('settings', SettingController::class);

// Meetings
Route::resource('meetings', MeetingController::class);

// Calendar Events
Route::resource('calendar-events', CalendarEventController::class);

// Reports
Route::resource('reports', ReportController::class);

// KPI Metrics (Employee performance)
Route::resource('kpi-metrics', KpiMetricController::class);
