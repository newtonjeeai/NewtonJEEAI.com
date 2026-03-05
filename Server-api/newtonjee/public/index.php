<?php

declare(strict_types=1);

require_once dirname(__DIR__) . '/config/app.php';
require_once dirname(__DIR__) . '/vendor/autoload.php';

use App\Router;

// ── Session ──────────────────────────────────────────────────
ini_set('session.cookie_httponly', '1');
ini_set('session.cookie_secure',   '1');
ini_set('session.cookie_samesite', 'Lax');
ini_set('session.gc_maxlifetime',  (string) SESSION_LIFETIME);
session_name(SESSION_NAME);
session_start();

// Regenerate session ID on each new session (prevent fixation)
if (empty($_SESSION['_started'])) {
    session_regenerate_id(true);
    $_SESSION['_started'] = true;
}

// ── Routing ──────────────────────────────────────────────────
$router = new Router();

// ── Auth routes (Google OAuth — students) ────────────────────
$router->get('/auth/google',          'AuthController@redirectToGoogle');
$router->get('/auth/google/callback', 'AuthController@handleGoogleCallback');
$router->post('/auth/logout',         'AuthController@logout');

// ── Student routes ───────────────────────────────────────────
$router->get('/',                          'Student\DashboardController@index');
$router->get('/dashboard',                 'Student\DashboardController@index');
$router->get('/setup-profile',             'Student\ProfileController@setupForm');
$router->post('/setup-profile',            'Student\ProfileController@setupSave');

// Courses
$router->get('/courses',                   'Student\CourseController@catalog');
$router->get('/courses/{slug}',            'Student\CourseController@preview');
$router->post('/courses/{id}/enroll',      'Student\CourseController@enroll');
$router->get('/my-courses',                'Student\CourseController@myCourses');

// Learning
$router->get('/learn/{courseSlug}/{lessonId}', 'Student\LessonController@show');
$router->post('/learn/{lessonId}/complete',    'Student\LessonController@markComplete');

// Announcements
$router->get('/announcements',             'Student\AnnouncementController@index');
$router->post('/announcements/{id}/read',  'Student\AnnouncementController@markRead');
$router->post('/announcements/read-all',   'Student\AnnouncementController@markAllRead');

// Calendar
$router->get('/calendar',                  'Student\CalendarController@index');

// Assignments
$router->get('/assignments',               'Student\AssignmentController@index');
$router->get('/assignments/{id}',          'Student\AssignmentController@show');
$router->get('/assignments/{id}/notebook', 'Student\AssignmentController@downloadNotebook');
$router->post('/assignments/{id}/submit',  'Student\AssignmentController@submit');

// Exams
$router->get('/exams',                     'Student\ExamController@index');
$router->get('/exams/{id}/start',          'Student\ExamController@start');
$router->post('/exams/{id}/submit',        'Student\ExamController@submit');
$router->get('/exams/{id}/results',        'Student\ExamController@results');

// Certificates
$router->get('/certificates',                     'Student\CertificateController@index');
$router->get('/certificates/{token}/download',    'Student\CertificateController@download');

// Public cert verification
$router->get('/verify/{token}', 'Student\CertificateController@verify');

// Settings
$router->get('/settings',               'Student\SettingsController@index');
$router->post('/settings/profile',      'Student\SettingsController@updateProfile');

// Notifications polling (AJAX)
$router->get('/api/notifications',      'Student\NotificationController@poll');

// ── Admin routes ─────────────────────────────────────────────
$router->get('/admin',                        'Admin\DashboardController@index');
$router->get('/admin/login',                  'Admin\AuthController@loginForm');
$router->post('/admin/login',                 'Admin\AuthController@login');
$router->post('/admin/logout',                'Admin\AuthController@logout');
$router->get('/admin/forgot-password',        'Admin\AuthController@forgotForm');
$router->post('/admin/forgot-password',       'Admin\AuthController@forgotSend');
$router->get('/admin/reset-password',         'Admin\AuthController@resetForm');
$router->post('/admin/reset-password',        'Admin\AuthController@resetSave');

// Admin → Users
$router->get('/admin/users',                  'Admin\UserController@index');
$router->get('/admin/users/{id}',             'Admin\UserController@show');
$router->get('/admin/users/create',           'Admin\UserController@createForm');
$router->post('/admin/users/create',          'Admin\UserController@create');
$router->get('/admin/users/{id}/edit',        'Admin\UserController@editForm');
$router->post('/admin/users/{id}/edit',       'Admin\UserController@edit');
$router->post('/admin/users/{id}/deactivate', 'Admin\UserController@deactivate');

// Admin → Courses
$router->get('/admin/courses',                      'Admin\CourseController@index');
$router->get('/admin/courses/create',               'Admin\CourseController@createForm');
$router->post('/admin/courses/create',              'Admin\CourseController@create');
$router->get('/admin/courses/{id}/edit',            'Admin\CourseController@editForm');
$router->post('/admin/courses/{id}/edit',           'Admin\CourseController@edit');
$router->post('/admin/courses/{id}/archive',        'Admin\CourseController@archive');
$router->get('/admin/courses/{id}/modules',         'Admin\ModuleController@index');
$router->post('/admin/modules/create',              'Admin\ModuleController@create');
$router->post('/admin/modules/{id}/edit',           'Admin\ModuleController@edit');
$router->post('/admin/modules/{id}/lessons/create', 'Admin\LessonController@create');
$router->post('/admin/lessons/{id}/edit',           'Admin\LessonController@edit');

// Admin → Assignments
$router->get('/admin/assignments',                  'Admin\AssignmentController@index');
$router->get('/admin/assignments/create',           'Admin\AssignmentController@createForm');
$router->post('/admin/assignments/create',          'Admin\AssignmentController@create');
$router->get('/admin/assignments/{id}/edit',        'Admin\AssignmentController@editForm');
$router->post('/admin/assignments/{id}/edit',       'Admin\AssignmentController@edit');
$router->get('/admin/submissions',                  'Admin\AssignmentController@submissions');
$router->get('/admin/submissions/{id}',             'Admin\AssignmentController@submissionDetail');
$router->post('/admin/submissions/{id}/grade',      'Admin\AssignmentController@grade');

// Admin → Exams
$router->get('/admin/exams',                        'Admin\ExamController@index');
$router->get('/admin/exams/create',                 'Admin\ExamController@createForm');
$router->post('/admin/exams/create',                'Admin\ExamController@create');
$router->get('/admin/exams/{id}/results',           'Admin\ExamController@results');

// Admin → Certificates
$router->get('/admin/certificates',                 'Admin\CertificateController@index');
$router->post('/admin/certificates/issue',          'Admin\CertificateController@issue');
$router->post('/admin/certificates/{id}/revoke',    'Admin\CertificateController@revoke');

// Admin → Announcements
$router->get('/admin/announcements',                'Admin\AnnouncementController@index');
$router->post('/admin/announcements/create',        'Admin\AnnouncementController@create');
$router->post('/admin/announcements/{id}/delete',   'Admin\AnnouncementController@delete');

// Admin → Calendar
$router->get('/admin/calendar',                     'Admin\CalendarController@index');
$router->post('/admin/calendar/create',             'Admin\CalendarController@create');
$router->post('/admin/calendar/{id}/delete',        'Admin\CalendarController@delete');

// Admin → Analytics
$router->get('/admin/analytics',                    'Admin\AnalyticsController@index');

// Admin → Settings
$router->get('/admin/settings',                     'Admin\SettingsController@index');
$router->post('/admin/settings',                    'Admin\SettingsController@save');

// ── Mentor routes ─────────────────────────────────────────────
$router->get('/mentor',                             'Mentor\DashboardController@index');
$router->get('/mentor/login',                       'Mentor\AuthController@loginForm');
$router->post('/mentor/login',                      'Mentor\AuthController@login');
$router->post('/mentor/logout',                     'Mentor\AuthController@logout');
$router->get('/mentor/courses',                     'Mentor\CourseController@index');
$router->get('/mentor/courses/{id}/students',       'Mentor\CourseController@students');
$router->get('/mentor/submissions',                 'Mentor\SubmissionController@index');
$router->get('/mentor/submissions/{id}',            'Mentor\SubmissionController@show');
$router->post('/mentor/submissions/{id}/grade',     'Mentor\SubmissionController@grade');
$router->get('/mentor/exams/results',               'Mentor\ExamController@results');
$router->get('/mentor/announcements',               'Mentor\AnnouncementController@index');
$router->post('/mentor/announcements',              'Mentor\AnnouncementController@create');

// ── Dispatch ─────────────────────────────────────────────────
$router->dispatch($_SERVER['REQUEST_METHOD'], parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH));
