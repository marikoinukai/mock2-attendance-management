<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceListController;
use App\Http\Controllers\AttendanceDetailController;
use App\Http\Controllers\AttendanceCorrectionRequestController;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\AdminAttendanceController;
use App\Http\Controllers\AdminCorrectionRequestController;
use App\Http\Controllers\AdminStaffController;
use App\Http\Controllers\AttendanceReportController;

Route::get('/', function () {
    return view('welcome');
});

/*
|--------------------------------------------------------------------------
| 管理者ログイン
|--------------------------------------------------------------------------
*/
Route::get('/admin/login', [AdminAuthController::class, 'showLogin'])->name('admin.login.show');
Route::post('/admin/login', [AdminAuthController::class, 'login'])->name('admin.login');

/*
|--------------------------------------------------------------------------
| ログアウト
|--------------------------------------------------------------------------
*/
Route::post('/user/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('login');
})->middleware('auth')->name('user.logout');

Route::post('/admin/logout', function (Request $request) {
    Auth::logout();

    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()->route('admin.login.show');
})->middleware('auth')->name('admin.logout');

/*
|--------------------------------------------------------------------------
| 一般ユーザー
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified'])->group(function () {
    Route::middleware('not_admin')->group(function () {
        Route::get('/attendance', [AttendanceController::class, 'index'])->name('attendance.index');
        Route::post('/attendance/clock-in', [AttendanceController::class, 'clockIn'])->name('attendance.clock_in');
        Route::post('/attendance/clock-out', [AttendanceController::class, 'clockOut'])->name('attendance.clock_out');
        Route::post('/attendance/break-start', [AttendanceController::class, 'breakStart'])->name('attendance.break_start');
        Route::post('/attendance/break-end', [AttendanceController::class, 'breakEnd'])->name('attendance.break_end');

        Route::get('/attendance/list', [AttendanceListController::class, 'index'])->name('attendance.list');
        Route::get('/attendance/report', [AttendanceReportController::class, 'index'])
            ->name('attendance.report');
        Route::get('/attendance/detail/{id}', [AttendanceDetailController::class, 'show'])->name('attendance.detail');
        Route::post('/attendance/detail/{id}/correction', [AttendanceCorrectionRequestController::class, 'store'])->name('attendance.correction.store');
    });

    Route::get('/stamp_correction_request/list', [AttendanceCorrectionRequestController::class, 'index'])->name('attendance_correction_requests.index');
});

/*
|--------------------------------------------------------------------------
| 管理者
|--------------------------------------------------------------------------
*/
Route::middleware(['auth', 'verified', 'admin'])->group(function () {
    Route::get('/admin/attendance/list', [AdminAttendanceController::class, 'index'])
        ->name('admin.attendance.index');

    Route::get('/admin/attendance/{id}', [AdminAttendanceController::class, 'show'])
        ->name('admin.attendance.show');

    Route::patch('/admin/attendance/{id}', [AdminAttendanceController::class, 'update'])
        ->name('admin.attendance.update');

    Route::get('/admin/staff/list', [AdminStaffController::class, 'index'])
        ->name('admin.staff.index');

    Route::get('/admin/attendance/staff/{id}', [AdminStaffController::class, 'attendance'])
        ->name('admin.staff.attendance');

    Route::get('/admin/attendance/staff/{id}/csv', [AdminStaffController::class, 'csv'])
        ->name('admin.staff.attendance.csv');

    Route::get('/stamp_correction_request/approve/{id}', [AdminCorrectionRequestController::class, 'show'])
        ->name('stamp_correction_request.approve.show');

    Route::patch('/stamp_correction_request/approve/{id}', [AdminCorrectionRequestController::class, 'approve'])
        ->name('stamp_correction_request.approve.update');
});
