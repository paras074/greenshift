<?php

// config/permissions.php
// To add a new CRM module:
// 1. Add it to 'modules' array below
// 2. assign-permissions per role in 'roles' array


# Day-to-day — add/remove permissions only, NEVER touches role assignments
// php artisan crm:sync-permissions

# First time setup only — sets everything from config
// php artisan crm:sync-permissions --fresh

# When you WANT to reset roles from config (asks for confirmation)
// php artisan crm:sync-permissions --sync-roles

# Force reset without confirmation prompt
// php artisan crm:sync-permissions --fresh

return [

    'modules' => [

        'dashboard' => [
            'label' =>'Dashboard',
            'permissions' => ['view', 'view-timeline'],
        ],

        'users' => [
            'label' =>'User Management',
            'permissions' => ['view', 'create', 'edit', 'delete', 'assign-roles'],
        ],

        'roles' => [
            'label' =>'Role & Permission Management',
            'permissions' => ['view', 'create', 'edit', 'delete', 'assign-permissions'],
        ],

        'customers' => [
            'label' =>'Customer Management',
            'permissions' => ['view', 'create', 'edit', 'delete'],
        ],

        'leads' => [
            'label' =>'Lead Management',
            'permissions' => ['view', 'view-own', 'create', 'edit', 'delete', 'assign', 'manage-lead-funnel', 'fetch-leads'],
        ],

        'templates' => [
            'label' => 'Templates',
            'permissions' => ['view', 'create', 'edit', 'delete', 'manage'],
        ],

        'deals' => [
            'label' =>'Deals',
            'permissions' => ['view', 'create', 'edit', 'delete'],
        ],
    
        'tasks' => [
            'label' =>'Tasks',
            'permissions' => ['view', 'view-own', 'create', 'edit', 'delete', 'assign'],
        ],

        'invoices' => [
            'label' =>'Invoices',
            'permissions' => ['view', 'create', 'edit', 'delete'],
        ],

        'reports' => [
            'label' =>'Reports',
            'permissions' => ['view'],
        ],

        'settings' => [
            'label' =>'Settings',
            'permissions' => ['view', 'edit'],
        ],

        'lead-sources' => [
            'label' =>'Lead Sources',
            'permissions' => ['view', 'create', 'edit', 'delete'],
        ],

        'lead-statuses' => [
            'label' =>'Lead Statuses',
            'permissions' => ['view', 'create', 'edit', 'delete'],
        ],

        'priority-statuses' => [
            'label' =>'Priority Statuses',
            'permissions' => ['view', 'create', 'edit', 'delete'],
        ],

        'kanban' => [
            'label' =>'Manage Kanban',
            'permissions' => ['manage'],
        ],

        'dialer' => [
            'label' =>'Manage Dialer',
            'permissions' => ['manage'],
        ],

        'loa' => [
            'label'       =>'LOA Management',
            'permissions' => ['create','approve'],
        ],

        'rfq' => [
            'label'       =>'RFQ Management',
            'permissions' => ['create','manage','view','edit','delete'],
        ],

    ],

    'roles' => [

        'superadmin' =>'*',

        'admin' => [
            'dashboard'             => ['view', 'view-timeline'],
            'users'                 => ['view', 'create', 'edit', 'delete', 'assign-roles'],
            'roles'                 => ['view', 'create', 'edit', 'delete', 'assign-permissions'],
            'customers'             => ['view', 'create', 'edit', 'delete'],
            'leads'                 => ['view', 'view-own', 'create', 'edit', 'delete', 'assign', 'manage-lead-funnel', 'fetch-leads'],
            'deals'                 => ['view', 'create', 'edit', 'delete'],
            'tasks'                 => ['view', 'view-own', 'create', 'edit', 'delete', 'assign'],
            'invoices'              => ['view', 'create', 'edit', 'delete'],
            'templates' => ['view', 'create', 'edit', 'delete', 'manage'],
            'reports'               => ['view'],
            'settings'              => ['view', 'edit'],
            'lead-sources'          => ['view', 'create', 'edit', 'delete'],
            'lead-statuses'         => ['view', 'create', 'edit', 'delete'],
            'priority-statuses'     => ['view', 'create', 'edit', 'delete'],
            'kanban'                => ['manage'],
            'dialer'                => ['manage'],
            'loa'                   => ['create','approve'],
            'rfq'                   => ['create','manage','view','edit','delete'],
        ],

        'manager' => [
            'dashboard'             => ['view', 'view-timeline'],
            'users'                 => ['view', 'create', 'edit', 'delete', 'assign-roles'],
            'roles'                 => ['view', 'create', 'edit', 'delete', 'assign-permissions'],
            'customers'             => ['view', 'create', 'edit', 'delete'],
            'leads'                 => ['view', 'view-own', 'create', 'edit', 'delete', 'assign', 'manage-lead-funnel', 'fetch-leads'],
            'deals'                 => ['view', 'create', 'edit', 'delete'],
            'tasks'                 => ['view', 'view-own', 'create', 'edit', 'delete', 'assign'],
            'invoices'              => ['view', 'create', 'edit', 'delete'],
            'templates' => ['view', 'create', 'edit', 'delete', 'manage'],
            'reports'               => ['view'],
            'settings'              => ['view', 'edit'],
            'lead-sources'          => ['view', 'create', 'edit', 'delete'],
            'lead-statuses'         => ['view', 'create', 'edit', 'delete'],
            'priority-statuses'     => ['view', 'create', 'edit', 'delete'],
            'kanban'                => ['manage'],
            'dialer'                => ['manage'],
            'loa'                   => ['create','approve'],
            'rfq'                   => ['create','manage','view','edit','delete'],
        ],

        'sales_manager' => [
            'dashboard'             => ['view', 'view-timeline'],
            'users'                 => ['view', 'create', 'edit', 'delete', 'assign-roles'],
            'roles'                 => ['view', 'create', 'edit', 'delete', 'assign-permissions'],
            'customers'             => ['view', 'create', 'edit', 'delete'],
            'leads'                 => ['view', 'view-own', 'create', 'edit', 'delete', 'assign', 'manage-lead-funnel', 'fetch-leads'],
            'deals'                 => ['view', 'create', 'edit', 'delete'],
            'tasks'                 => ['view', 'view-own', 'create', 'edit', 'delete', 'assign'],
            'invoices'              => ['view', 'create', 'edit', 'delete'],
            'templates' => ['view', 'create', 'edit', 'delete', 'manage'],
            'reports'               => ['view'],
            'settings'              => ['view', 'edit'],
            'lead-sources'          => ['view', 'create', 'edit', 'delete'],
            'lead-statuses'         => ['view', 'create', 'edit', 'delete'],
            'priority-statuses'     => ['view', 'create', 'edit', 'delete'],
            'kanban'                => ['manage'],
            'dialer'                => ['manage'],
            'loa'                   => ['create','approve'],
            'rfq'                   => ['create','manage','view','edit','delete'],
        ],

        'sales_executive' => [
            'dashboard'             => ['view'],
            'users'                 => ['view', 'create', 'edit', 'delete', 'assign-roles'],
            'roles'                 => ['view', 'create', 'edit', 'delete', 'assign-permissions'],
            'customers'             => ['view', 'create', 'edit', 'delete'],
            'leads'                 => ['view', 'view-own', 'create', 'edit', 'delete', 'assign', 'manage-lead-funnel', 'fetch-leads'],
            'deals'                 => ['view', 'create', 'edit', 'delete'],
            'tasks'                 => ['view', 'view-own', 'create', 'edit', 'delete', 'assign'],
            'invoices'              => ['view', 'create', 'edit', 'delete'],
            'templates' => ['view', 'create', 'edit', 'delete', 'manage'],
            'reports'               => ['view'],
            'settings'              => ['view', 'edit'],
            'lead-sources'          => ['view', 'create', 'edit', 'delete'],
            'lead-statuses'         => ['view', 'create', 'edit', 'delete'],
            'priority-statuses'     => ['view', 'create', 'edit', 'delete'],
            'kanban'                => ['manage'],
            'dialer'                => ['manage'],
            'loa'                   => ['create','approve'],
            'rfq'                   => ['create','manage','view','edit','delete']
        ],
    ],

];
