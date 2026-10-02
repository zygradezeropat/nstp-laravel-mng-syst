<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\CoordinatorController;
use App\Http\Controllers\InstructorController;

// ── Authentication ────────────────────────────────────────────────────────────
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);
});
Route::post('/logout', [AuthController::class, 'logout'])->name('logout')->middleware('auth');

// ── Admin Pages ───────────────────────────────────────────────────────────────
Route::middleware('auth')->group(function () {
    Route::get('/admin/accounts', [AdminController::class, 'accounts'])->name('admin.accounts');
    Route::post('/admin/accounts', [AdminController::class, 'store'])->name('admin.accounts.store');

    // ── Coordinator Pages ─────────────────────────────────────────────────────────
    Route::prefix('coordinator')->name('coordinator.')->group(function () {
        Route::get('/dashboard', [CoordinatorController::class, 'dashboard'])->name('dashboard');
        Route::get('/sections', [CoordinatorController::class, 'sections'])->name('sections');
        Route::post('/sections', [CoordinatorController::class, 'storeSection'])->name('sections.store');
        Route::put('/sections/{id}', [CoordinatorController::class, 'updateSection'])->name('sections.update');
        Route::delete('/sections/{id}', [CoordinatorController::class, 'deleteSection'])->name('sections.delete');
        Route::get('/sections/{section}/students', [CoordinatorController::class, 'sectionStudents'])->name('section_students');
        Route::put('/sections/{section}/students/{student_id}', [CoordinatorController::class, 'updateStudentInSection'])->name('sections.update_student');
        Route::delete('/sections/{section}/students/{student_id}', [CoordinatorController::class, 'removeStudentFromSection'])->name('sections.remove_student');
        Route::get('/instructors', [CoordinatorController::class, 'instructors'])->name('instructors');
        Route::post('/instructors', [CoordinatorController::class, 'storeInstructor'])->name('instructors.store');
        Route::get('/instructors/{id}', [CoordinatorController::class, 'instructorInfo'])->name('instructor_info');
        Route::put('/instructors/{id}', [CoordinatorController::class, 'updateInstructor'])->name('instructors.update');
        Route::delete('/instructors/{id}', [CoordinatorController::class, 'destroyInstructor'])->name('instructors.destroy');
        Route::get('/approvals', [CoordinatorController::class, 'approvals'])->name('approvals');
        Route::patch('/approvals/plans/{id}/approve', [CoordinatorController::class, 'approvePlan'])->name('approvals.plans.approve');
        Route::patch('/approvals/plans/{id}/reject', [CoordinatorController::class, 'rejectPlan'])->name('approvals.plans.reject');
        Route::patch('/approvals/plans/{id}/revision', [CoordinatorController::class, 'revisionPlan'])->name('approvals.plans.revision');
        Route::patch('/approvals/reports/{id}/approve', [CoordinatorController::class, 'approveReport'])->name('approvals.reports.approve');
        Route::patch('/approvals/reports/{id}/reject', [CoordinatorController::class, 'rejectReport'])->name('approvals.reports.reject');
        Route::patch('/approvals/reports/{id}/revision', [CoordinatorController::class, 'revisionReport'])->name('approvals.reports.revision');
        Route::delete('/approvals/plans/{id}', [CoordinatorController::class, 'deletePlan'])->name('approvals.plans.delete');
        Route::delete('/approvals/reports/{id}', [CoordinatorController::class, 'deleteReport'])->name('approvals.reports.delete');
        Route::get('/calendar', [CoordinatorController::class, 'calendar'])->name('calendar');
        Route::post('/calendar', [CoordinatorController::class, 'storeActivity'])->name('calendar.store');
        Route::get('/ocr', [CoordinatorController::class, 'ocr'])->name('ocr');
        Route::post('/ocr/import', [CoordinatorController::class, 'importGrades'])->name('ocr.import');
        Route::get('/certificates', [CoordinatorController::class, 'certificates'])->name('certificates');
        Route::get('/certificates/section/{sectionId}', [CoordinatorController::class, 'getSectionCertificates'])->name('certificates.section_students');
        Route::post('/certificates/log', [CoordinatorController::class, 'logGeneration'])->name('certificates.log');
        Route::delete('/certificates/log/{id}', [CoordinatorController::class, 'deleteGenerationLog'])->name('certificates.log.delete');
        Route::post('/certificates/pdf/single', [CoordinatorController::class, 'generateCertificatePdf'])->name('certificates.pdf.single');
        Route::post('/certificates/pdf/batch', [CoordinatorController::class, 'generateBatchPdf'])->name('certificates.pdf.batch');
        Route::get('/certificate-templates', [CoordinatorController::class, 'certificateTemplates'])->name('certificate_templates');
        Route::post('/certificate-templates', [CoordinatorController::class, 'storeTemplate'])->name('certificate_templates.store');
        Route::put('/certificate-templates/{id}', [CoordinatorController::class, 'updateTemplate'])->name('certificate_templates.update');
        Route::delete('/certificate-templates/{id}', [CoordinatorController::class, 'destroyTemplate'])->name('certificate_templates.destroy');
        Route::get('/certificate-templates/{id}', [CoordinatorController::class, 'getTemplate'])->name('certificate_templates.get');
        Route::get('/archive', [CoordinatorController::class, 'archive'])->name('archive');
        Route::post('/archive/{id}/restore', [CoordinatorController::class, 'restoreStudent'])->name('archive.restore');
        Route::get('/audit', [CoordinatorController::class, 'audit'])->name('audit');
        Route::get('/reports', [CoordinatorController::class, 'reports'])->name('reports');
        Route::match(['get', 'post'], '/reports/export-pdf', [CoordinatorController::class, 'exportReportPdf'])->name('reports.export_pdf');
        // Announcement Module Routes (Disabled)
        // Route::get('/announcements', [CoordinatorController::class, 'announcements'])->name('announcements');
        // Route::post('/announcements', [CoordinatorController::class, 'storeAnnouncement'])->name('announcements.store');
        // Route::delete('/announcements/{id}', [CoordinatorController::class, 'deleteAnnouncement'])->name('announcements.delete');
    });

    // ── Instructor Pages ─────────────────────────────────────────────────────────
    Route::prefix('instructor')->name('instructor.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\InstructorController::class, 'dashboard'])->name('dashboard');
        Route::get('/classes', [\App\Http\Controllers\InstructorController::class, 'classes'])->name('classes');
        Route::post('/classes/update-grade', [\App\Http\Controllers\InstructorController::class, 'updateGrade'])->name('classes.update_grade');
        // Plans CRUD
        Route::get('/plans', [\App\Http\Controllers\InstructorController::class, 'plans'])->name('plans');
        Route::post('/plans', [\App\Http\Controllers\InstructorController::class, 'storePlan'])->name('plans.store');
        Route::put('/plans/{id}', [\App\Http\Controllers\InstructorController::class, 'updatePlan'])->name('plans.update');
        Route::delete('/plans/{id}', [\App\Http\Controllers\InstructorController::class, 'deletePlan'])->name('plans.delete');

        // Accomplishment Reports CRUD
        Route::get('/reports', [\App\Http\Controllers\InstructorController::class, 'reports'])->name('reports');
        Route::post('/reports', [\App\Http\Controllers\InstructorController::class, 'storeReport'])->name('reports.store');
        Route::put('/reports/{id}', [\App\Http\Controllers\InstructorController::class, 'updateReport'])->name('reports.update');
        Route::delete('/reports/{id}', [\App\Http\Controllers\InstructorController::class, 'deleteReport'])->name('reports.delete');
        // Announcement Module Route (Disabled)
        // Route::get('/announcements', [\App\Http\Controllers\InstructorController::class, 'announcements'])->name('announcements');
        Route::get('/calendar', [\App\Http\Controllers\InstructorController::class, 'calendar'])->name('calendar');
    });

    // ── ROTC Officer Pages ────────────────────────────────────────────────────────
    Route::prefix('rotc')->name('rotc.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\RotcController::class, 'dashboard'])->name('dashboard');
        Route::get('/platoons', [\App\Http\Controllers\RotcController::class, 'platoons'])->name('platoons');
        Route::post('/platoons', [\App\Http\Controllers\RotcController::class, 'storePlatoon'])->name('platoons.store');
        Route::put('/platoons/{id}', [\App\Http\Controllers\RotcController::class, 'updatePlatoon'])->name('platoons.update');
        Route::delete('/platoons/{id}', [\App\Http\Controllers\RotcController::class, 'deletePlatoon'])->name('platoons.delete');
        Route::get('/rosters', [\App\Http\Controllers\RotcController::class, 'rosters'])->name('rosters');
        Route::post('/rosters', [\App\Http\Controllers\RotcController::class, 'storeOfficer'])->name('rosters.store');
        Route::delete('/rosters/{id}', [\App\Http\Controllers\RotcController::class, 'deleteOfficer'])->name('rosters.delete');
        Route::post('/rosters/assign', [\App\Http\Controllers\RotcController::class, 'assignCadet'])->name('rosters.assign');
        Route::get('/designs', [\App\Http\Controllers\RotcController::class, 'designs'])->name('designs');
        Route::post('/designs', [\App\Http\Controllers\RotcController::class, 'storeDesign'])->name('designs.store');
        Route::put('/designs/{id}', [\App\Http\Controllers\RotcController::class, 'updateDesign'])->name('designs.update');
        Route::delete('/designs/{id}', [\App\Http\Controllers\RotcController::class, 'deleteDesign'])->name('designs.delete');
        Route::get('/calendar', [\App\Http\Controllers\RotcController::class, 'calendar'])->name('calendar');
        Route::get('/reports', [\App\Http\Controllers\RotcController::class, 'reports'])->name('reports');
        Route::post('/reports', [\App\Http\Controllers\RotcController::class, 'storeReport'])->name('reports.store');
        Route::put('/reports/{id}', [\App\Http\Controllers\RotcController::class, 'updateReport'])->name('reports.update');
        Route::delete('/reports/{id}', [\App\Http\Controllers\RotcController::class, 'deleteReport'])->name('reports.delete');
    });

    // Redirect root to dashboard based on role
    Route::get('/', function () {
        $user = auth()->user();
        return match ($user->role) {
            'admin' => redirect()->route('admin.accounts'),
            'coordinator' => redirect()->route('coordinator.dashboard'),
            'instructor' => redirect()->route('instructor.dashboard'),
            'rotc' => redirect()->route('rotc.dashboard'),
            default => redirect()->route('login'),
        };
    })->name('portal.index');
});
// ── Audit Log API ─────────────────────────────────────────────────────────────
// These routes are called by the frontend JS via fetch().
// We exempt them from CSRF using withoutMiddleware so they work as simple
// JSON endpoints without needing a cookie-based session.
Route::prefix('api/audit')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->group(function () {
        Route::post('/',  [AuditLogController::class, 'store'])->name('audit.store');
        Route::get('/',   [AuditLogController::class, 'index'])->name('audit.index');
    });

// ── Real-time dashboard (SSE + JSON metrics) ─────────────────────────────────
Route::prefix('api/dashboard')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->group(function () {
        Route::get('/metrics', [DashboardController::class, 'metrics'])->name('dashboard.metrics');
        Route::get('/stream',  [DashboardController::class, 'stream'])->name('dashboard.stream');
    });

// ── Students API (pass/fail grade + roster CRUD) ─────────────────────────────
Route::prefix('api/students')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->group(function () {
        Route::get('/',              [StudentController::class, 'index'])->name('students.index');
        Route::post('/',             [StudentController::class, 'store'])->name('students.store');
        Route::post('/import',       [StudentController::class, 'import'])->name('students.import');
        Route::patch('/{student}',   [StudentController::class, 'update'])->name('students.update');
        Route::delete('/{student}',   [StudentController::class, 'destroy'])->name('students.destroy');
    });

// Standalone HTML form demo (pass/fail submission example)
Route::view('/students/grade-form', 'students.grade-form')->name('students.grade-form');

// ── Admin Reset API ───────────────────────────────────────────────────────────
Route::post('api/admin/reset-data', function () {
    try {
        $exitCode = \Illuminate\Support\Facades\Artisan::call('nstp:reset', ['--force' => true]);
        if ($exitCode === 0) {
            return response()->json([
                'success' => true,
                'message' => 'Portal data reset completed successfully. All Admin accounts are intact.'
            ]);
        }
        return response()->json([
            'success' => false,
            'message' => 'Artisan command failed with code ' . $exitCode
        ], 500);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Reset error: ' . $e->getMessage()
        ], 500);
    }
})->name('admin.reset-data')->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// ── Sections API ──────────────────────────────────────────────────────────────
Route::prefix('api/sections')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->group(function () {
        Route::get('/',        [SectionController::class, 'index'])->name('sections.index');
        Route::post('/',       [SectionController::class, 'store'])->name('sections.store');
        Route::post('/compare-class-list', [SectionController::class, 'compareClassList'])->name('sections.compare');
        Route::post('/assign', [SectionController::class, 'assign'])->name('sections.assign');
    });

// ── Instructors API ───────────────────────────────────────────────────────────
Route::prefix('api/instructors')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->group(function () {
        Route::get('/',  [InstructorController::class, 'index'])->name('instructors.index');
        Route::post('/', [InstructorController::class, 'store'])->name('instructors.store');
    });

// ── Notifications API ─────────────────────────────────────────────────────────
Route::prefix('api/notifications')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class])
    ->group(function () {
        Route::get('/',              [NotificationController::class, 'index'])->name('notifications.index');
        Route::post('/read-all',     [NotificationController::class, 'markAllRead'])->name('notifications.readAll');
        Route::post('/{id}/read',    [NotificationController::class, 'markRead'])->name('notifications.read');
    });
