@php
    $sections = [
        [
            'title' => 'Giảng dạy',
            'groups' => [
                [
                    'label' => 'Nghiệp vụ',
                    'icon' => 'fas fa-chalkboard-teacher',
                    'active' => ['teacher.dashboard', 'teacher.profile', 'teacher.classes.*', 'teacher.schedule.*', 'teacher.notifications.*', 'teacher.materials.*', 'teacher.evaluations.*', 'teacher.attendance.*'],
                    'open' => true,
                    'items' => [
                        ['label' => 'Dashboard', 'icon' => 'fas fa-compass', 'route' => 'teacher.dashboard', 'active' => ['teacher.dashboard']],
                        ['label' => 'Lớp học của tôi', 'icon' => 'fas fa-people-roof', 'route' => 'teacher.classes.index', 'active' => ['teacher.classes.*']],
                        ['label' => 'Lịch dạy', 'icon' => 'fas fa-calendar-days', 'route' => 'teacher.schedule.index', 'active' => ['teacher.schedule.*']],
                        ['label' => 'Điểm danh', 'icon' => 'fas fa-user-check', 'route' => 'teacher.attendance.index', 'active' => ['teacher.attendance.*']],
                        ['label' => 'Tài liệu', 'icon' => 'fas fa-folder-open', 'route' => 'teacher.materials.index', 'active' => ['teacher.materials.*']],
                        ['label' => 'Báo cáo học tập', 'icon' => 'fas fa-comment-dots', 'route' => 'teacher.evaluations.index', 'active' => ['teacher.evaluations.*']],
                        ['label' => 'Thông báo', 'icon' => 'fas fa-bell', 'route' => 'teacher.notifications.index', 'active' => ['teacher.notifications.*']],
                        ['label' => 'Hồ sơ', 'icon' => 'fas fa-id-card', 'route' => 'teacher.profile', 'active' => ['teacher.profile']],
                    ],
                ],
            ],
        ],
    ];
@endphp

<x-internal.sidebar-shell
    portal-label="Cổng giáo viên"
    portal-short-label="TC"
    portal-home-route="teacher.dashboard"
    portal-accent="teacher"
    :sections="$sections" />
