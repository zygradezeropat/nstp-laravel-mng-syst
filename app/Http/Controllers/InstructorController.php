<?php

namespace App\Http\Controllers;

use App\Models\PortalUser;
use App\Models\Section;
use App\Models\Student;
use App\Models\ActivityPlan;
use App\Models\AccomplishmentReport;
use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\Announcement;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class InstructorController extends Controller
{
    /**
     * GET /api/instructors
     * Returns all instructors from portal_users.
     */
    public function index(): JsonResponse
    {
        $instructors = PortalUser::where('role', 'instructor')
            ->orderBy('name')
            ->get()
            ->map(fn(PortalUser $u) => [
                'id'      => $u->id,
                'name'    => $u->name,
                'email'   => $u->email,
                'dept'    => $u->dept,
                'status'  => $u->status,
                'contact' => $u->contact,
                'sections'=> $u->sections->pluck('section_name')->join(', '),
            ]);

        return response()->json($instructors);
    }

    /**
     * POST /api/instructors
     * Add a new instructor to portal_users.
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name'    => 'required|string|max:255',
            'email'   => 'required|email|max:255|unique:portal_users,email',
            'dept'    => 'nullable|string|max:128',
            'contact' => 'nullable|string|max:64',
            'status'  => 'nullable|string|max:32',
        ]);

        $instructor = PortalUser::create([
            'name'    => $data['name'],
            'email'   => $data['email'],
            'role'    => 'instructor',
            'dept'    => $data['dept'] ?? 'General',
            'contact' => $data['contact'] ?? null,
            'status'  => $data['status'] ?? 'Active',
            'password'=> '',
        ]);

        return response()->json([
            'success'    => true,
            'instructor' => [
                'id'     => $instructor->id,
                'name'   => $instructor->name,
                'email'  => $instructor->email,
                'dept'   => $instructor->dept,
                'status' => $instructor->status,
            ],
        ], 201);
    }

    // ── Web Portal Views ────────────────────────────────────────────────────────
    
    public function dashboard()
    {
        $instructorId = Auth::id();

        // 1. Assigned Sections Count
        $sections = Section::where('instructor_id', $instructorId)->get();
        $sectionsCount = $sections->count();

        // Components subtitle
        $components = $sections->pluck('component')->unique();
        if ($components->isEmpty()) {
            $componentsStr = 'No assigned component';
        } else {
            $componentsStr = $components->join(' & ');
        }

        // 2. Total Students
        $totalStudents = DB::table('students')
            ->join('enrollments', 'students.id', '=', 'enrollments.student_id')
            ->join('sections', 'enrollments.section_id', '=', 'sections.id')
            ->where('sections.instructor_id', $instructorId)
            ->count();

        // 3. Reports Pending
        $pendingPlans = ActivityPlan::where('instructor_id', $instructorId)
            ->whereIn('status', ['Draft', 'Pending', 'Rejected'])
            ->count();
        $pendingReports = AccomplishmentReport::where('instructor_id', $instructorId)
            ->whereIn('status', ['Draft', 'Pending', 'Revision'])
            ->count();
        $reportsPending = $pendingPlans + $pendingReports;

        // 4. Approved YTD (Approved Activity Plans + Reviewed Accomplishment Reports)
        $approvedPlans = ActivityPlan::where('instructor_id', $instructorId)
            ->where('status', 'Approved')
            ->count();
        $approvedReports = AccomplishmentReport::where('instructor_id', $instructorId)
            ->where('status', 'Reviewed')
            ->count();
        $approvedCount = $approvedPlans + $approvedReports;

        $stats = [
            ['label' => 'Assigned Sections', 'value' => (string)$sectionsCount, 'sub' => $componentsStr, 'ico' => 'book', 'color' => 'from-indigo-500 to-blue-500'],
            ['label' => 'Total Students', 'value' => (string)$totalStudents, 'sub' => 'Across all sections', 'ico' => 'users', 'color' => 'from-emerald-500 to-teal-500'],
            ['label' => 'Reports Pending', 'value' => (string)$reportsPending, 'sub' => $reportsPending === 1 ? '1 due soon' : "{$reportsPending} due soon", 'ico' => 'filecheck', 'color' => 'from-amber-500 to-orange-500'],
            ['label' => 'Approved YTD', 'value' => (string)$approvedCount, 'sub' => 'Active this year', 'ico' => 'check2', 'color' => 'from-violet-500 to-fuchsia-500'],
        ];

        // Fetch actual pending activity plans
        $plansList = ActivityPlan::with('section')
            ->where('instructor_id', $instructorId)
            ->whereIn('status', ['Draft', 'Pending', 'Rejected'])
            ->get()
            ->map(function ($plan) {
                return (object)[
                    'type' => 'plan',
                    'id' => $plan->id,
                    'title' => $plan->title,
                    'status' => $plan->status,
                    'section' => $plan->section?->section_name ?? 'N/A',
                    'date' => $plan->scheduled_date ? $plan->scheduled_date->format('M d, Y') : 'N/A',
                    'updated_at' => $plan->updated_at,
                ];
            });

        // Fetch actual pending accomplishment reports
        $reportsList = AccomplishmentReport::with('section')
            ->where('instructor_id', $instructorId)
            ->whereIn('status', ['Draft', 'Pending', 'Revision'])
            ->get()
            ->map(function ($report) {
                return (object)[
                    'type' => 'report',
                    'id' => $report->id,
                    'title' => $report->title,
                    'status' => $report->status === 'Revision' ? 'Revisions' : $report->status,
                    'section' => $report->section?->section_name ?? 'N/A',
                    'date' => $report->completed_date ? $report->completed_date->format('M d, Y') : 'N/A',
                    'updated_at' => $report->updated_at,
                ];
            });

        // Merge and sort pending submissions
        $pendingSubmissions = $plansList->concat($reportsList)->sortByDesc('updated_at');

        return view('instructor.dashboard', compact('stats', 'pendingSubmissions'));
    }

    public function classes(Request $request)
    {
        $instructorId = Auth::id();

        // 1. If a specific section is requested, show details
        $sectionName = $request->query('section');
        if ($sectionName) {
            $section = Section::where('instructor_id', $instructorId)
                ->where('section_name', $sectionName)
                ->firstOrFail();

            // Query students enrolled in this section via the enrollments table
            $query = Student::join('enrollments', 'students.id', '=', 'enrollments.student_id')
                ->join('sections', 'sections.id', '=', 'enrollments.section_id')
                ->where('sections.id', $section->id)
                ->select(
                    'students.*', 
                    'enrollments.final_grade', 
                    'enrollments.status as enrollment_status'
                )
                ->orderBy('students.last_name')
                ->orderBy('students.first_name');

            // Apply search filter if present
            $search = $request->query('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('students.student_id', 'like', "%{$search}%")
                      ->orWhere('students.first_name', 'like', "%{$search}%")
                      ->orWhere('students.last_name', 'like', "%{$search}%")
                      ->orWhere('students.course', 'like', "%{$search}%");
                });
            }

            $students = $query->paginate(15)->withQueryString();

            $mappedStudents = $students->getCollection()->map(function ($s) {
                // Map display grade
                $grade = $s->grade;
                if (!$grade && isset($s->enrollment_status)) {
                    $grade = match ($s->enrollment_status) {
                        'Passed' => 'pass',
                        'Failed' => 'fail',
                        default  => null,
                    };
                }

                return (object)[
                    'db_id' => $s->id,
                    'student_no' => $s->student_id,
                    'name' => trim($s->last_name . ', ' . $s->first_name, ', '),
                    'course' => $s->course ?? 'N/A',
                    'email' => $s->email ?? 'N/A',
                    'final_grade' => $s->final_grade ?? $s->numerical_grade,
                    'status' => $s->enrollment_status ?? 'Pending',
                    'grade_enum' => $grade,
                ];
            });
            $students->setCollection($mappedStudents);

            // Fetch ALL students in the section for the full unpaginated XLSX export
            $allStudentsQuery = Student::join('enrollments', 'students.id', '=', 'enrollments.student_id')
                ->join('sections', 'sections.id', '=', 'enrollments.section_id')
                ->where('sections.id', $section->id)
                ->select(
                    'students.*', 
                    'enrollments.final_grade', 
                    'enrollments.status as enrollment_status'
                )
                ->orderBy('students.last_name')
                ->orderBy('students.first_name');

            $allStudents = $allStudentsQuery->get()->map(function ($s) {
                return (object)[
                    'student_no' => $s->student_id,
                    'name' => trim($s->last_name . ', ' . $s->first_name, ', '),
                    'course' => $s->course ?? 'N/A',
                    'final_grade' => $s->final_grade ?? $s->numerical_grade ?? '-',
                    'status' => $s->enrollment_status ?? 'Pending',
                ];
            });

            return view('instructor.classes', compact('section', 'students', 'search', 'allStudents'));
        }

        // 2. Otherwise show grid of all classes
        $dbSections = Section::where('instructor_id', $instructorId)->get();

        $classes = $dbSections->map(function ($s) {
            // Count total students enrolled
            $studentCount = DB::table('students')
                ->join('enrollments', 'students.id', '=', 'enrollments.student_id')
                ->where('enrollments.section_id', $s->id)
                ->count();

            // Count students with a recorded grade
            $gradedCount = DB::table('students')
                ->join('enrollments', 'students.id', '=', 'enrollments.student_id')
                ->where('enrollments.section_id', $s->id)
                ->where(function ($q) {
                    $q->whereNotNull('enrollments.final_grade')
                      ->orWhereIn('enrollments.status', ['Passed', 'Failed']);
                })
                ->count();

            $progress = $studentCount > 0 ? round(($gradedCount / $studentCount) * 100) : 0;

            // Map gradients and classes based on Component
            $theme = match ($s->component) {
                'LTS' => [
                    'accent' => 'from-emerald-500 to-teal-500',
                    'bc' => 'emerald',
                ],
                'ROTC' => [
                    'accent' => 'from-rose-500 to-red-500',
                    'bc' => 'rose',
                ],
                default => [
                    'accent' => 'from-indigo-500 to-blue-500',
                    'bc' => 'indigo',
                ]
            };

            $componentTitle = match ($s->component) {
                'LTS' => 'Literacy Training Service',
                'ROTC' => 'Reserve Officers\' Training Corps',
                default => 'Civic Welfare Training Service',
            };

            return (object)[
                'id' => $s->id,
                'code' => $s->section_name,
                'sec' => $s->semester . ' Sem &middot; ' . $s->school_year,
                'title' => $componentTitle,
                'students' => $studentCount,
                'room' => $s->room ?? 'TBA',
                'sched' => $s->schedule ?? 'TBA',
                'accent' => $theme['accent'],
                'bc' => $theme['bc'],
                'badge' => $s->status ?? 'Active',
                'progress' => $progress,
            ];
        });

        return view('instructor.classes', compact('classes'));
    }

    public function updateGrade(Request $request)
    {
        $data = $request->validate([
            'student_id'   => 'required|string|exists:students,student_id',
            'section_code' => 'required|string|exists:sections,section_name',
            'remarks'      => 'required|in:Passed,Failed,Pending,Active',
            'final_grade'  => 'nullable|numeric|min:1.0|max:5.0',
        ]);

        $student = Student::where('student_id', $data['student_id'])->firstOrFail();
        $section = Section::where('section_name', $data['section_code'])->firstOrFail();

        // 1. Sync the grade using Student::syncEnrollment helper
        $gradeInput = null;
        if (is_numeric($data['final_grade'])) {
            $gradeInput = $data['final_grade'];
        } else {
            $gradeInput = match ($data['remarks']) {
                'Passed' => 'pass',
                'Failed' => 'fail',
                default  => null,
            };
        }

        Student::syncEnrollment($student->student_id, $section->section_name, $gradeInput);

        // Double check updates to enrollments table status if remarks was manually specified
        $status = $data['remarks'];
        if ($status === 'Active') {
            $status = 'Pending';
        }

        $enrollmentUpdate = ['status' => $status];
        if (is_numeric($data['final_grade'])) {
            $enrollmentUpdate['final_grade'] = floatval($data['final_grade']);
        } else {
            $enrollmentUpdate['final_grade'] = null;
        }

        DB::table('enrollments')
            ->where('student_id', $student->id)
            ->where('section_id', $section->id)
            ->update($enrollmentUpdate);

        // Update student record grade enum as well
        $studentUpdate = [];
        if ($status === 'Passed') {
            $studentUpdate['grade'] = 'pass';
        } elseif ($status === 'Failed') {
            $studentUpdate['grade'] = 'fail';
        } else {
            $studentUpdate['grade'] = null;
        }
        if (is_numeric($data['final_grade'])) {
            $studentUpdate['numerical_grade'] = floatval($data['final_grade']);
        } else {
            $studentUpdate['numerical_grade'] = null;
        }

        $student->update($studentUpdate);

        // 2. Record Audit Log
        $user = Auth::user();
        AuditLog::record(
            'Updated Student Grade',
            'Grades',
            $student->student_id,
            "Instructor {$user->name} recorded grade for student " . trim($student->last_name . ', ' . $student->first_name, ', ') . " in section {$section->section_name}. Grade: " . ($data['final_grade'] ?? 'N/A') . ", Remarks: {$status}",
            'edit',
            [
                'username' => $user->name,
                'email'    => $user->email,
                'role'     => 'instructor',
            ]
        );

        return back()->with('success', "Grade for student " . trim($student->last_name . ', ' . $student->first_name, ', ') . " updated successfully.");
    }

    public function plans()
    {
        $instructorId = Auth::id();

        $plans = ActivityPlan::with('section')
            ->where('instructor_id', $instructorId)
            ->orderBy('scheduled_date', 'desc')
            ->paginate(10);

        // Fetch assigned sections for the section dropdown in modal
        $sections = Section::where('instructor_id', $instructorId)->get();

        return view('instructor.plans', compact('plans', 'sections'));
    }

    public function storePlan(Request $request)
    {
        $instructorId = Auth::id();

        if ($request->input('section_id') === 'all') {
            $user = Auth::user();
            $firstSection = Section::where('instructor_id', $instructorId)->first();
            $component = $firstSection ? $firstSection->component : (in_array($user->dept, ['CWTS', 'LTS']) ? $user->dept : 'CWTS');

            $allSection = Section::firstOrCreate([
                'section_name' => 'All Section',
            ], [
                'component'    => $component,
                'school_year'  => '2025-2026',
                'semester'     => '1st',
                'status'       => 'Active',
            ]);
            $request->merge(['section_id' => $allSection->id]);
        }

        $data = $request->validate([
            'title'           => 'required|string|max:255',
            'section_id'      => 'required|exists:sections,id',
            'location'        => 'required|string|max:255',
            'scheduled_date'  => 'required|date',
            'objectives'      => 'nullable|string',
            'activity_design' => 'required|file|mimes:pdf|max:20480',
            'status'          => 'required|in:Draft,Pending',
        ]);

        // Secure: verify that the instructor handles this section
        $section = Section::findOrFail($data['section_id']);
        if ($section->section_name !== 'All Section') {
            Section::where('instructor_id', $instructorId)->findOrFail($data['section_id']);
        }

        $description = '';
        $filesAttached = 0;
        if ($request->hasFile('activity_design')) {
            $file = $request->file('activity_design');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9\._-]/', '', $file->getClientOriginalName());
            
            // Ensure directory exists
            $targetDir = public_path('uploads/activity_designs');
            $this->saveUploadedFile($file, $targetDir, $filename);
            $description = 'uploads/activity_designs/' . $filename;
            $filesAttached = 1;
        }

        $plan = ActivityPlan::create(array_merge($data, [
            'instructor_id'  => $instructorId,
            'description'    => $description,
            'files_attached' => $filesAttached,
            'submitted_date' => $data['status'] === 'Pending' ? now() : null,
        ]));

        // Record Audit Log
        $user = Auth::user();
        AuditLog::record(
            'Created Activity Plan',
            'Activity Plans',
            $plan->title,
            "Instructor {$user->name} created activity plan '{$plan->title}' for section " . $plan->section?->section_name . ". Status: {$plan->status}",
            'submission',
            [
                'username' => $user->name,
                'email'    => $user->email,
                'role'     => 'instructor',
            ]
        );

        return back()->with('success', "Activity Plan '{$plan->title}' created successfully.");
    }

    public function updatePlan(Request $request, $id)
    {
        $instructorId = Auth::id();
        $plan = ActivityPlan::where('instructor_id', $instructorId)->findOrFail($id);

        if (!in_array(strtolower($plan->status ?? 'draft'), ['revision', 'rejected', 'draft'])) {
            abort(403, 'Only activity plans in Draft, Revision, or Rejected status can be modified.');
        }

        if ($request->input('section_id') === 'all') {
            $user = Auth::user();
            $firstSection = Section::where('instructor_id', $instructorId)->first();
            $component = $firstSection ? $firstSection->component : (in_array($user->dept, ['CWTS', 'LTS']) ? $user->dept : 'CWTS');

            $allSection = Section::firstOrCreate([
                'section_name' => 'All Section',
            ], [
                'component'    => $component,
                'school_year'  => '2025-2026',
                'semester'     => '1st',
                'status'       => 'Active',
            ]);
            $request->merge(['section_id' => $allSection->id]);
        }

        $data = $request->validate([
            'title'           => 'required|string|max:255',
            'section_id'      => 'required|exists:sections,id',
            'location'        => 'required|string|max:255',
            'scheduled_date'  => 'required|date',
            'objectives'      => 'nullable|string',
            'activity_design' => 'nullable|file|mimes:pdf|max:20480',
            'status'          => 'required|in:Draft,Pending',
        ]);

        // Secure: verify that the instructor handles this section
        $section = Section::findOrFail($data['section_id']);
        if ($section->section_name !== 'All Section') {
            Section::where('instructor_id', $instructorId)->findOrFail($data['section_id']);
        }

        $oldTitle = $plan->title;
        
        $updateData = $data;
        unset($updateData['activity_design']);

        if ($request->hasFile('activity_design')) {
            $file = $request->file('activity_design');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9\._-]/', '', $file->getClientOriginalName());
            
            $targetDir = public_path('uploads/activity_designs');
            $this->saveUploadedFile($file, $targetDir, $filename);
            $updateData['description'] = 'uploads/activity_designs/' . $filename;
            $updateData['files_attached'] = 1;
        }

        if ($data['status'] === 'Pending' && $plan->status === 'Draft') {
            $updateData['submitted_date'] = now();
        }

        $plan->update($updateData);

        // Record Audit Log
        $user = Auth::user();
        AuditLog::record(
            'Updated Activity Plan',
            'Activity Plans',
            $plan->title,
            "Instructor {$user->name} updated activity plan '{$oldTitle}' (now '{$plan->title}'). Status: {$plan->status}",
            'edit',
            [
                'username' => $user->name,
                'email'    => $user->email,
                'role'     => 'instructor',
            ]
        );

        return back()->with('success', "Activity Plan '{$plan->title}' updated successfully.");
    }

    public function deletePlan($id)
    {
        $instructorId = Auth::id();
        $plan = ActivityPlan::where('instructor_id', $instructorId)->findOrFail($id);

        if (!in_array(strtolower($plan->status ?? 'draft'), ['revision', 'rejected', 'draft'])) {
            abort(403, 'Only activity plans in Draft, Revision, or Rejected status can be deleted.');
        }

        $title = $plan->title;

        $plan->delete();

        // Record Audit Log
        $user = Auth::user();
        AuditLog::record(
            'Deleted Activity Plan',
            'Activity Plans',
            $title,
            "Instructor {$user->name} deleted activity plan '{$title}'.",
            'edit',
            [
                'username' => $user->name,
                'email'    => $user->email,
                'role'     => 'instructor',
            ]
        );

        return back()->with('success', "Activity Plan '{$title}' deleted successfully.");
    }

    public function reports()
    {
        $instructorId = Auth::id();

        $reports = AccomplishmentReport::with('section')
            ->where('instructor_id', $instructorId)
            ->orderBy('completed_date', 'desc')
            ->paginate(10);

        // Fetch assigned sections for the section dropdown in modal
        $sections = Section::where('instructor_id', $instructorId)->get();

        // Fetch APPROVED plans for reports dropdown
        $approvedPlans = ActivityPlan::where('instructor_id', $instructorId)
            ->where('status', 'Approved')
            ->get();

        return view('instructor.reports', compact('reports', 'sections', 'approvedPlans'));
    }

    public function storeReport(Request $request)
    {
        $instructorId = Auth::id();

        if ($request->input('section_id') === 'all') {
            $user = Auth::user();
            $firstSection = Section::where('instructor_id', $instructorId)->first();
            $component = $firstSection ? $firstSection->component : (in_array($user->dept, ['CWTS', 'LTS']) ? $user->dept : 'CWTS');

            $allSection = Section::firstOrCreate([
                'section_name' => 'All Section',
            ], [
                'component'    => $component,
                'school_year'  => '2025-2026',
                'semester'     => '1st',
                'status'       => 'Active',
            ]);
            $request->merge(['section_id' => $allSection->id]);
        }

        $data = $request->validate([
            'activity_plan_id'   => 'required|exists:activity_plans,id',
            'section_id'         => 'required|exists:sections,id',
            'completed_date'     => 'required|date',
            'accomplishments'    => 'nullable|string',
            'report_file'        => 'nullable|file|mimes:pdf,docx,doc,png,jpg,jpeg|max:20480',
            'status'             => 'required|in:Draft,Pending',
        ]);

        // Secure: verify that the instructor handles this section
        $section = Section::findOrFail($data['section_id']);
        if ($section->section_name !== 'All Section') {
            Section::where('instructor_id', $instructorId)->findOrFail($data['section_id']);
        }

        // Fetch the approved plan to get its title
        $plan = ActivityPlan::where('instructor_id', $instructorId)
            ->where('status', 'Approved')
            ->findOrFail($data['activity_plan_id']);

        $reportFile = null;
        $filesAttached = 0;
        if ($request->hasFile('report_file')) {
            $file = $request->file('report_file');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9\._-]/', '', $file->getClientOriginalName());
            
            // Ensure directory exists
            $targetDir = public_path('uploads/accomplishment_reports');
            $this->saveUploadedFile($file, $targetDir, $filename);
            $reportFile = 'uploads/accomplishment_reports/' . $filename;
            $filesAttached = 1;
        }

        $report = AccomplishmentReport::create(array_merge($data, [
            'instructor_id'      => $instructorId,
            'title'              => $plan->title,
            'location'           => $plan->location,
            'participants_count' => 0,
            'report_file_path'   => $reportFile,
            'files_attached'     => $filesAttached,
            'submitted_date'     => $data['status'] === 'Pending' ? now() : null,
        ]));

        // Record Audit Log
        $user = Auth::user();
        AuditLog::record(
            'Created Accomplishment Report',
            'Accomplishment Reports',
            $report->title,
            "Instructor {$user->name} created accomplishment report '{$report->title}' for section " . $report->section?->section_name . ". Status: {$report->status}",
            'submission',
            [
                'username' => $user->name,
                'email'    => $user->email,
                'role'     => 'instructor',
            ]
        );

        return back()->with('success', "Accomplishment Report '{$report->title}' created successfully.");
    }

    public function updateReport(Request $request, $id)
    {
        $instructorId = Auth::id();
        $report = AccomplishmentReport::where('instructor_id', $instructorId)->findOrFail($id);

        if ($request->input('section_id') === 'all') {
            $user = Auth::user();
            $firstSection = Section::where('instructor_id', $instructorId)->first();
            $component = $firstSection ? $firstSection->component : (in_array($user->dept, ['CWTS', 'LTS']) ? $user->dept : 'CWTS');

            $allSection = Section::firstOrCreate([
                'section_name' => 'All Section',
            ], [
                'component'    => $component,
                'school_year'  => '2025-2026',
                'semester'     => '1st',
                'status'       => 'Active',
            ]);
            $request->merge(['section_id' => $allSection->id]);
        }

        $data = $request->validate([
            'activity_plan_id'   => 'required|exists:activity_plans,id',
            'section_id'         => 'required|exists:sections,id',
            'completed_date'     => 'required|date',
            'accomplishments'    => 'nullable|string',
            'report_file'        => 'nullable|file|mimes:pdf,docx,doc,png,jpg,jpeg|max:20480',
            'status'             => 'required|in:Draft,Pending',
        ]);

        // Secure: verify that the instructor handles this section
        $section = Section::findOrFail($data['section_id']);
        if ($section->section_name !== 'All Section') {
            Section::where('instructor_id', $instructorId)->findOrFail($data['section_id']);
        }

        // Fetch approved plan
        $plan = ActivityPlan::where('instructor_id', $instructorId)
            ->where('status', 'Approved')
            ->findOrFail($data['activity_plan_id']);

        $oldTitle = $report->title;

        $updateData = array_merge($data, [
            'title'    => $plan->title,
            'location' => $plan->location,
        ]);
        unset($updateData['report_file']);

        if ($request->hasFile('report_file')) {
            $file = $request->file('report_file');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9\._-]/', '', $file->getClientOriginalName());
            
            $targetDir = public_path('uploads/accomplishment_reports');
            $this->saveUploadedFile($file, $targetDir, $filename);
            $updateData['report_file_path'] = 'uploads/accomplishment_reports/' . $filename;
            $updateData['files_attached'] = 1;
        }

        if ($data['status'] === 'Pending' && $report->status === 'Draft') {
            $updateData['submitted_date'] = now();
        }

        $report->update($updateData);

        // Record Audit Log
        $user = Auth::user();
        AuditLog::record(
            'Updated Accomplishment Report',
            'Accomplishment Reports',
            $report->title,
            "Instructor {$user->name} updated accomplishment report '{$oldTitle}' (now '{$report->title}'). Status: {$report->status}",
            'edit',
            [
                'username' => $user->name,
                'email'    => $user->email,
                'role'     => 'instructor',
            ]
        );

        return back()->with('success', "Accomplishment Report '{$report->title}' updated successfully.");
    }

    public function deleteReport($id)
    {
        $instructorId = Auth::id();
        $report = AccomplishmentReport::where('instructor_id', $instructorId)->findOrFail($id);

        if (!in_array(strtolower($report->status ?? 'draft'), ['revision', 'rejected', 'draft'])) {
            abort(403, 'Only accomplishment reports in Draft, Revision, or Rejected status can be deleted.');
        }

        $title = $report->title;

        $report->delete();

        // Record Audit Log
        $user = Auth::user();
        AuditLog::record(
            'Deleted Accomplishment Report',
            'Accomplishment Reports',
            $title,
            "Instructor {$user->name} deleted accomplishment report '{$title}'.",
            'edit',
            [
                'username' => $user->name,
                'email'    => $user->email,
                'role'     => 'instructor',
            ]
        );

        return back()->with('success', "Accomplishment Report '{$title}' deleted successfully.");
    }

    /*
    public function announcements(Request $request = null)
    {
        $request = $request ?? request();
        $query = Announcement::whereIn('target_role', ['All', 'Instructors', 'Instructor'])
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at');

        if ($search = trim((string)$request->query('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('source', 'like', "%{$search}%");
            });
        }

        $announcements = $query->paginate(10)->withQueryString();

        return view('instructor.announcements', compact('announcements'));
    }
    */

    public function calendar(Request $request)
    {
        $instructorId = Auth::id();
        $year = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);
        $selectedComponent = $request->query('component', 'all');

        // Retrieve components of sections assigned to this instructor
        $assignedComponents = Section::where('instructor_id', $instructorId)
            ->pluck('component')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $defaultComponent = !empty($assignedComponents) ? $assignedComponents[0] : 'CWTS';

        $selectedMonth = \Carbon\Carbon::createFromDate($year, $month, 1);
        $currentMonthStart = $selectedMonth->copy()->startOfMonth();
        $currentMonthEnd = $selectedMonth->copy()->endOfMonth();

        // 1. Fetch global activities in current month
        $activitiesDb = Activity::whereBetween('activity_date', [$currentMonthStart, $currentMonthEnd])->get();

        // 2. Fetch approved activity plans of this instructor in current month
        $approvedPlans = ActivityPlan::with('section')
            ->where('instructor_id', $instructorId)
            ->where('status', 'Approved')
            ->whereBetween('scheduled_date', [$currentMonthStart, $currentMonthEnd])
            ->get();

        // 3. Fetch announcements relevant to instructors in current month
        $announcementsDb = Announcement::whereIn('target_role', ['All', 'Instructors', 'Instructor'])
            ->where(function ($q) use ($currentMonthStart, $currentMonthEnd) {
                $q->whereBetween('scheduled_date', [$currentMonthStart, $currentMonthEnd])
                  ->orWhere(function ($q2) use ($currentMonthStart, $currentMonthEnd) {
                      $q2->whereNull('scheduled_date')
                         ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd]);
                  });
            })->get();

        // 4. Merge them into a single raw collection
        $rawActivities = collect();

        foreach ($activitiesDb as $act) {
            $rawActivities->push((object)[
                'title' => $act->title,
                'component' => $act->component ?? 'General',
                'activity_date' => $act->activity_date,
                'location' => $act->location ?? 'TBA',
                'description' => $act->description ?? '',
                'is_plan' => false,
                'is_announcement' => false,
            ]);
        }

        foreach ($approvedPlans as $plan) {
            $exists = $rawActivities->contains(function ($existing) use ($plan) {
                return strtolower($existing->title) === strtolower($plan->title) &&
                       $existing->activity_date->toDateString() === $plan->scheduled_date->toDateString();
            });

            if (!$exists) {
                $comp = $plan->section?->component ?? $defaultComponent;
                $rawActivities->push((object)[
                    'title' => $plan->title,
                    'component' => $comp,
                    'activity_date' => $plan->scheduled_date,
                    'location' => $plan->location ?? 'TBA',
                    'description' => $plan->objectives ?? $plan->description ?? '',
                    'is_plan' => true,
                    'is_announcement' => false,
                ]);
            }
        }

        foreach ($announcementsDb as $ann) {
            $date = $ann->scheduled_date ? $ann->scheduled_date : $ann->created_at;
            $rawActivities->push((object)[
                'title' => $ann->title,
                'component' => $ann->component ?? 'General',
                'activity_date' => $date,
                'location' => $ann->source ?? 'NSTP Office',
                'description' => $ann->content,
                'is_plan' => false,
                'is_announcement' => true,
                'target_role' => $ann->target_role ?? 'All',
                'is_pinned' => (bool)$ann->is_pinned,
            ]);
        }

        // 5. Filter merged activities based on $selectedComponent
        $activities = $rawActivities->filter(function ($act) use ($selectedComponent, $assignedComponents) {
            $actCompUpper = strtoupper(trim($act->component));
            $selUpper = strtoupper(trim($selectedComponent));

            if ($selUpper === 'ALL') {
                if (!empty($assignedComponents)) {
                    $assignedUpper = array_map('strtoupper', $assignedComponents);
                    return in_array($actCompUpper, $assignedUpper) || in_array($actCompUpper, ['GENERAL', 'ALL']);
                }
                return true;
            }

            if ($selUpper === 'GENERAL') {
                return in_array($actCompUpper, ['GENERAL', 'ALL']);
            }

            return $actCompUpper === $selUpper;
        })->values();

        // 6. Fetch upcoming activities & announcements for sidebar (starting from today)
        $upcomingDb = Activity::where('activity_date', '>=', now()->startOfDay())
            ->orderBy('activity_date', 'asc')
            ->limit(10)
            ->get();

        $upcomingPlans = ActivityPlan::with('section')
            ->where('instructor_id', $instructorId)
            ->where('status', 'Approved')
            ->where('scheduled_date', '>=', now()->startOfDay())
            ->orderBy('scheduled_date', 'asc')
            ->limit(10)
            ->get();

        $upcomingAnn = Announcement::whereIn('target_role', ['All', 'Instructors', 'Instructor'])
            ->where(function ($q) {
                $q->where('scheduled_date', '>=', now()->startOfDay())
                  ->orWhere(function ($q2) {
                      $q2->whereNull('scheduled_date')
                         ->where('created_at', '>=', now()->startOfDay());
                  });
            })->limit(5)->get();

        $rawUpcoming = collect();

        foreach ($upcomingDb as $act) {
            $comp = $act->component ?? 'General';
            $rawUpcoming->push((object)[
                'title' => $act->title,
                'component' => $comp,
                'activity_date' => $act->activity_date,
                'location' => $act->location ?? 'TBA',
                'description' => $act->description ?? '',
                'is_plan' => false,
                'is_announcement' => false,
                'color' => match(strtoupper($comp)) {
                    'CWTS' => 'bg-indigo-500',
                    'LTS' => 'bg-emerald-500',
                    'ROTC' => 'bg-rose-500',
                    default => 'bg-blue-500',
                },
            ]);
        }

        foreach ($upcomingPlans as $plan) {
            $exists = $rawUpcoming->contains(function ($existing) use ($plan) {
                return strtolower($existing->title) === strtolower($plan->title) &&
                       $existing->activity_date->toDateString() === $plan->scheduled_date->toDateString();
            });

            if (!$exists) {
                $comp = $plan->section?->component ?? $defaultComponent;
                $rawUpcoming->push((object)[
                    'title' => $plan->title,
                    'component' => $comp,
                    'activity_date' => $plan->scheduled_date,
                    'location' => $plan->location ?? 'TBA',
                    'description' => $plan->objectives ?? $plan->description ?? '',
                    'is_plan' => true,
                    'is_announcement' => false,
                    'color' => match(strtoupper($comp)) {
                        'CWTS' => 'bg-indigo-500',
                        'LTS' => 'bg-emerald-500',
                        'ROTC' => 'bg-rose-500',
                        default => 'bg-emerald-500',
                    },
                ]);
            }
        }

        foreach ($upcomingAnn as $ann) {
            $date = $ann->scheduled_date ? $ann->scheduled_date : $ann->created_at;
            $rawUpcoming->push((object)[
                'title' => $ann->title,
                'component' => $ann->component ?? 'General',
                'activity_date' => $date,
                'location' => $ann->source ?? 'NSTP Office',
                'description' => $ann->content,
                'is_plan' => false,
                'is_announcement' => true,
                'target_role' => $ann->target_role ?? 'All',
                'is_pinned' => (bool)$ann->is_pinned,
                'color' => 'bg-amber-500',
            ]);
        }

        $upcomingActivities = $rawUpcoming->filter(function ($act) use ($selectedComponent, $assignedComponents) {
            $actCompUpper = strtoupper(trim($act->component));
            $selUpper = strtoupper(trim($selectedComponent));

            if ($selUpper === 'ALL') {
                if (!empty($assignedComponents)) {
                    $assignedUpper = array_map('strtoupper', $assignedComponents);
                    return in_array($actCompUpper, $assignedUpper) || in_array($actCompUpper, ['GENERAL', 'ALL']);
                }
                return true;
            }

            if ($selUpper === 'GENERAL') {
                return in_array($actCompUpper, ['GENERAL', 'ALL']);
            }

            return $actCompUpper === $selUpper;
        })->sortBy('activity_date')->take(5)->values();

        // Precompute navigation months
        $prevMonth = $selectedMonth->copy()->subMonth();
        $nextMonth = $selectedMonth->copy()->addMonth();

        return view('instructor.calendar', compact(
            'activities', 'upcomingActivities', 'selectedMonth', 'prevMonth', 'nextMonth',
            'selectedComponent', 'assignedComponents'
        ));
    }

    /**
     * Move an uploaded file safely, bypassing Windows/OneDrive read-only folder permission issues.
     */
    private function saveUploadedFile($file, string $targetDir, string $filename): void
    {
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $filename;

        try {
            $file->move($targetDir, $filename);
        } catch (\Symfony\Component\HttpFoundation\File\Exception\FileException $e) {
            $tempPath = $file->getRealPath() ?: $file->getPathname();
            if ($tempPath && file_exists($tempPath)) {
                if (!copy($tempPath, $targetPath)) {
                    throw new \Symfony\Component\HttpFoundation\File\Exception\FileException("Failed to copy file to {$targetPath} via fallback.");
                }
                @unlink($tempPath);
            } else {
                throw $e;
            }
        }
    }
}
