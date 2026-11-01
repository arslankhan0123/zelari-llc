<?php

/**
 * HRM RBAC permission matrix — completely separate from config/permissions.php
 * (which is the ecommerce panel). Every module has the same 5 actions:
 *   list / view / create / edit / delete
 * Permission string = "hrm.<module>.<action>" (e.g. "hrm.employees.create").
 * Master Admin implicitly has every permission (Gate::before).
 */
$actions = ['list', 'view', 'create', 'edit', 'delete'];

$modules = [
    'dashboard'   => 'HR Overview',
    'employees'   => 'Employees',
    'departments' => 'Departments',
    'attendance'  => 'Attendance',
    'leave'       => 'Leave',
    'payroll'     => 'Payroll',
    'reports'     => 'Reports',
    'roles'       => 'HR Roles',
    'users'       => 'HR Users',
    'settings'    => 'HR Settings',
];

return collect($modules)->map(fn ($label) => ['label' => $label, 'actions' => $actions])->all();
