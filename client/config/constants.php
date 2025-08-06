<?php
define('SITE_NAME', 'Bệnh Viện ABC');
define('SITE_DESCRIPTION', 'Hệ thống quản lý bệnh viện hiện đại');
define('SITE_ADDRESS', '123 Đường ABC, Quận 1, TP.HCM');
define('SITE_PHONE', '(028) 1234 5678');
define('SITE_EMAIL', 'info@benhvienabc.com');

// Navigation menu items
define('NAV_ITEMS', [
    [
        'title' => 'Bệnh Nhân',
        'url' => '/patients',
        'icon' => 'fas fa-user-injured'
    ],
    [
        'title' => 'Bác Sĩ',
        'url' => '/doctors',
        'icon' => 'fas fa-user-md'
    ],
    [
        'title' => 'Lịch Khám',
        'url' => '/appointments',
        'icon' => 'fas fa-calendar-alt'
    ],
    [
        'title' => 'Đơn Thuốc',
        'url' => '/prescriptions',
        'icon' => 'fas fa-prescription'
    ],
    [
        'title' => 'Báo Cáo',
        'url' => '/reports',
        'icon' => 'fas fa-chart-bar'
    ]
]);