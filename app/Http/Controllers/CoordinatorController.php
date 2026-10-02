<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Student;
use App\Models\Section;
use App\Models\PortalUser;
use App\Models\ActivityPlan;
use App\Models\AccomplishmentReport;
use App\Models\Activity;
use App\Models\AuditLog;
use App\Models\Announcement;
use App\Models\CertificateTemplate;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;

class CoordinatorController extends Controller
{
    public function dashboard()
    {
        // 1. Total Students
        $totalStudents = Student::count();

        // 2. Active Sections
        $activeSections = Section::where('status', 'Active')->count();

        // 3. Pass Rate
        $totalGraded = Student::whereIn('grade', ['pass', 'fail'])->count();
        $passed = Student::where('grade', 'pass')->count();
        $passRateVal = $totalGraded > 0 ? ($passed / $totalGraded) * 100 : 0.0;
        $passRate = number_format($passRateVal, 1) . '%';

        // 4. Reports Pending
        $pendingPlans = ActivityPlan::where('status', 'Pending')->count();
        $pendingReports = AccomplishmentReport::where('status', 'Pending')->count();
        $reportsPending = $pendingPlans + $pendingReports;

        $stats = [
            (object)['key' => 'total_students', 'label' => 'Total Students', 'value' => (string)$totalStudents, 'delta' => '0%', 'up' => true, 'ico' => 'users', 'color' => 'from-indigo-500 to-blue-500'],
            (object)['key' => 'active_sections', 'label' => 'Active Sections', 'value' => (string)$activeSections, 'delta' => '0', 'up' => true, 'ico' => 'book', 'color' => 'from-emerald-500 to-teal-500'],
            (object)['key' => 'pass_rate', 'label' => 'Pass Rate', 'value' => $passRate, 'delta' => '0%', 'up' => true, 'ico' => 'trend', 'color' => 'from-violet-500 to-fuchsia-500'],
            (object)['key' => 'reports_pending', 'label' => 'Reports Pending', 'value' => (string)$reportsPending, 'delta' => '0', 'up' => false, 'ico' => 'filecheck', 'color' => 'from-amber-500 to-orange-500'],
        ];

        // 5. Incomplete profiles count
        $incompleteCount = Student::where(function($q) {
            $q->whereNull('email')
              ->orWhereNull('contact_number')
              ->orWhereNull('date_of_birth')
              ->orWhereNull('complete_address');
        })->count();

        $incompleteProfiles = Student::where(function($q) {
            $q->whereNull('email')
              ->orWhereNull('contact_number')
              ->orWhereNull('date_of_birth')
              ->orWhereNull('complete_address');
        })->limit(5)->get()->map(function($student) {
            return (object)[
                'id' => $student->student_id,
                'name' => trim($student->last_name . ', ' . $student->first_name, ', '),
                'course' => $student->course ?? 'N/A',
                'program' => $student->component ?? 'CWTS',
            ];
        });

        // 6. Recent activities
        $recentActivities = Activity::orderBy('activity_date', 'desc')
            ->limit(5)
            ->get();

        return view('coordinator.dashboard', compact('stats', 'incompleteCount', 'incompleteProfiles', 'recentActivities'));
    }

    public function sections(Request $request)
    {
        $progDefs = [
            [
                'key' => 'CWTS', 
                'letter' => 'C', 
                'label' => 'CWTS', 
                'full' => 'Civic Welfare Training Service', 
                'color' => 'bg-indigo-600', 
                'bar' => 'bg-indigo-500', 
                'maxStudents' => 800,
                'studentCount' => \DB::table('enrollments')
                    ->join('students', 'enrollments.student_id', '=', 'students.id')
                    ->join('sections', 'enrollments.section_id', '=', 'sections.id')
                    ->where('sections.component', 'CWTS')
                    ->count(),
                'sectionCount' => Section::where('component', 'CWTS')->count(),
            ],
            [
                'key' => 'LTS', 
                'letter' => 'L', 
                'label' => 'LTS', 
                'full' => 'Literacy Training Service', 
                'color' => 'bg-emerald-600', 
                'bar' => 'bg-emerald-500', 
                'maxStudents' => 400,
                'studentCount' => \DB::table('enrollments')
                    ->join('students', 'enrollments.student_id', '=', 'students.id')
                    ->join('sections', 'enrollments.section_id', '=', 'sections.id')
                    ->where('sections.component', 'LTS')
                    ->count(),
                'sectionCount' => Section::where('component', 'LTS')->count(),
            ],
            [
                'key' => 'ROTC', 
                'letter' => 'R', 
                'label' => 'ROTC', 
                'full' => "Reserve Officers' Training Corps", 
                'color' => 'bg-rose-500', 
                'bar' => 'bg-rose-400', 
                'maxStudents' => 400,
                'studentCount' => \DB::table('enrollments')
                    ->join('students', 'enrollments.student_id', '=', 'students.id')
                    ->join('sections', 'enrollments.section_id', '=', 'sections.id')
                    ->where('sections.component', 'ROTC')
                    ->count(),
                'sectionCount' => Section::where('component', 'ROTC')->count(),
            ],
        ];

        foreach ($progDefs as &$p) {
            $p['percent'] = min(100, $p['studentCount'] > 0 ? round(($p['studentCount'] / $p['maxStudents']) * 100) : 0);
        }
        unset($p);

        $sectionsQuery = Section::with('instructor');
        if ($request->has('search') && !empty(trim($request->input('search')))) {
            $search = trim($request->input('search'));
            $sectionsQuery->where(function($q) use ($search) {
                $q->where('section_name', 'like', "%{$search}%")
                  ->orWhere('component', 'like', "%{$search}%")
                  ->orWhere('school_year', 'like', "%{$search}%")
                  ->orWhere('room', 'like', "%{$search}%")
                  ->orWhere('semester', 'like', "%{$search}%")
                  ->orWhereHas('instructor', function($iq) use ($search) {
                      $iq->where('name', 'like', "%{$search}%");
                  });

                $searchLower = strtolower($search);
                if (str_contains('tba', $searchLower) || str_contains('unassigned', $searchLower)) {
                    $q->orWhereNull('instructor_id');
                }
            });
        }

        $sectionsPaginated = $sectionsQuery->orderBy('section_name')->paginate(10)->withQueryString();
        $mappedCollection = $sectionsPaginated->getCollection()->map(function ($s) {
            $studentCount = \DB::table('students')
                ->join('enrollments', 'students.id', '=', 'enrollments.student_id')
                ->join('sections as sec', 'sec.id', '=', 'enrollments.section_id')
                ->where('sec.id', $s->id)
                ->count();

            return (object)[
                'id'           => $s->id,
                'code'         => $s->section_name,
                'program'      => $s->component,
                'schoolYear'   => $s->school_year,
                'semester'     => $s->semester,
                'students'     => $studentCount,
                'instructor'   => $s->instructor_name,
                'room'         => $s->room,
            ];
        });
        $sectionsPaginated->setCollection($mappedCollection);
        $sections = $sectionsPaginated;

        $instructors = PortalUser::where('role', 'instructor')->get();

        // Fetch master student records for Masterlist tab
        $masterStudents = \App\Models\Student::leftJoin('enrollments', 'students.id', '=', 'enrollments.student_id')
            ->leftJoin('sections', 'enrollments.section_id', '=', 'sections.id')
            ->select([
                'students.id as db_id',
                'students.student_id',
                'students.serial_no',
                'students.first_name',
                'students.middle_name',
                'students.last_name',
                'students.course',
                'students.year_level',
                'students.component',
                'students.enrollment_status',
                'students.date_of_birth',
                'students.place_of_birth',
                'students.sex',
                'students.contact_number',
                'students.email',
                'students.complete_address',
                'sections.section_name',
                'sections.school_year',
                'enrollments.status as enrollment_status_override',
            ])
            ->orderBy('students.last_name')
            ->get()
            ->map(function ($s) {
                $studentId = !empty($s->student_id) ? $s->student_id : '';
                $serialNo  = !empty($s->serial_no) ? $s->serial_no : '';

                $firstName = trim($s->first_name ?? '');
                $middleName = trim($s->middle_name ?? '');
                $lastName = trim($s->last_name ?? '');

                if ($middleName && str_ends_with(strtolower($firstName), strtolower($middleName))) {
                    $cleanFirst = trim(substr($firstName, 0, strlen($firstName) - strlen($middleName)));
                } else {
                    $cleanFirst = $firstName;
                }

                $fullName = trim($lastName . ', ' . $cleanFirst . ($middleName ? ' ' . $middleName : ''));

                $status = $s->enrollment_status_override ?: ($s->enrollment_status ?: 'Active');
                $yearLevelStr = $s->year_level ? ($s->year_level . (
                    $s->year_level == 1 ? 'st' : ($s->year_level == 2 ? 'nd' : ($s->year_level == 3 ? 'rd' : 'th'))
                ) . ' Year') : '1st Year';

                return (object)[
                    'db_id'          => $s->db_id,
                    'student_id'     => $studentId ?: 'N/A',
                    'raw_student_id' => $studentId,
                    'serial_no'      => $serialNo ?: 'N/A',
                    'raw_serial_no'  => $serialNo,
                    'first_name'     => $cleanFirst,
                    'middle_name'    => $middleName,
                    'last_name'      => $lastName,
                    'name'           => $fullName,
                    'course'         => $s->course ?: '',
                    'program'        => $s->component ?: 'CWTS',
                    'section'        => $s->section_name ?: 'Unassigned',
                    'school_year'    => $s->school_year ?: '2025-2026',
                    'year_level'     => $yearLevelStr,
                    'raw_year_level' => $s->year_level ?: 1,
                    'status'         => $status,
                    'dob'            => $s->date_of_birth ? date('Y-m-d', strtotime($s->date_of_birth)) : '',
                    'birth_place'    => $s->place_of_birth ?: '',
                    'sex'            => $s->sex ?: '',
                    'cell_no'        => $s->contact_number ?: '',
                    'email'          => $s->email ?: '',
                    'address'        => $s->complete_address ?: '',
                ];
            });

        $allSectionsList = Section::orderBy('section_name')->pluck('section_name')->unique()->filter()->values()->toArray();
        $schoolYearsList = Section::orderBy('school_year', 'desc')->pluck('school_year')->unique()->filter()->values()->toArray();
        if (empty($schoolYearsList)) {
            $schoolYearsList = ['2025-2026'];
        }

        return view('coordinator.sections', compact('sections', 'instructors', 'progDefs', 'masterStudents', 'allSectionsList', 'schoolYearsList'));
    }

    public function instructors()
    {
        $instructorsPaginated = PortalUser::with('sections')
            ->where('role', 'instructor')
            ->paginate(5);

        $mappedCollection = $instructorsPaginated->getCollection()->map(function($user) {
            return (object)[
                'id' => $user->id,
                'name' => $user->name,
                'dept' => $user->dept,
                'email' => $user->email,
                'status' => $user->status,
                'sections' => $user->sections->count(),
            ];
        });
        $instructorsPaginated->setCollection($mappedCollection);
        $instructors = $instructorsPaginated;

        // Fetch all registered users in the system who are CWTS/LTS Instructors
        $registeredUsers = PortalUser::where('role', 'instructor')
            ->orderBy('name')
            ->get();

        // Fetch all active sections in the system
        $allSections = Section::orderBy('section_name')->get();

        return view('coordinator.instructors', compact('instructors', 'registeredUsers', 'allSections'));
    }

    public function approvals()
    {
        $activitiesQuery = ActivityPlan::with(['instructor', 'section'])->orderByDesc('updated_at')->get();
        
        $pendingActivities = $activitiesQuery->where('status', 'Pending')->map(fn($plan) => $this->mapPlanForApproval($plan));
        $approvedActivities = $activitiesQuery->where('status', 'Approved')->map(fn($plan) => $this->mapPlanForApproval($plan));
        $rejectedActivities = $activitiesQuery->whereIn('status', ['Rejected', 'Revision'])->map(fn($plan) => $this->mapPlanForApproval($plan));

        $reportsQuery = AccomplishmentReport::with(['instructor', 'section'])->orderByDesc('updated_at')->get();

        $pendingReports = $reportsQuery->where('status', 'Pending')->map(fn($report) => $this->mapReportForApproval($report));
        $approvedReports = $reportsQuery->where('status', 'Reviewed')->map(fn($report) => $this->mapReportForApproval($report));
        $rejectedReports = $reportsQuery->whereIn('status', ['Rejected', 'Revision'])->map(fn($report) => $this->mapReportForApproval($report));

        return view('coordinator.approvals', compact(
            'pendingActivities', 'approvedActivities', 'rejectedActivities',
            'pendingReports', 'approvedReports', 'rejectedReports'
        ));
    }

    private function mapPlanForApproval($plan)
    {
        $scope = $plan->section?->component ?? 'CWTS';
        if ($plan->section?->section_name === 'All Section') {
            if ($plan->instructor?->role === 'rotc') {
                $scope = 'ROTC';
            } elseif ($plan->instructor?->dept) {
                $scope = $plan->instructor->dept;
            }
        }

        return (object)[
            'id' => $plan->id,
            'title' => $plan->title,
            'instructor' => $plan->instructor?->name ?? 'N/A',
            'scope' => $scope,
            'date' => $plan->scheduled_date?->format('M d, Y') ?? 'N/A',
            'status' => $plan->status,
            'location' => $plan->location ?? 'N/A',
            'objectives' => $plan->objectives ?? 'N/A',
            'description' => $plan->description ?? '',
            'submitted_date' => $plan->submitted_date?->format('M. d, Y h:i A') ?? ($plan->created_at?->format('M. d, Y h:i A') ?? 'N/A'),
        ];
    }

    private function mapReportForApproval($report)
    {
        $scope = $report->section?->component ?? 'CWTS';
        if ($report->section?->section_name === 'All Section') {
            if ($report->instructor?->role === 'rotc') {
                $scope = 'ROTC';
            } elseif ($report->instructor?->dept) {
                $scope = $report->instructor->dept;
            }
        }

        return (object)[
            'id' => $report->id,
            'title' => $report->title,
            'instructor' => $report->instructor?->name ?? 'N/A',
            'scope' => $scope,
            'date' => $report->completed_date?->format('M d, Y') ?? 'N/A',
            'status' => $report->status,
            'location' => $report->location ?? 'N/A',
            'participants_count' => $report->participants_count ?? 0,
            'accomplishments' => $report->accomplishments ?? 'N/A',
            'report_file_path' => $report->report_file_path ?? '',
            'submitted_date' => $report->submitted_date?->format('M. d, Y h:i A') ?? ($report->created_at?->format('M. d, Y h:i A') ?? 'N/A'),
        ];
    }

    public function approvePlan(Request $request, $id)
    {
        $plan = ActivityPlan::with('section')->findOrFail($id);
        $plan->update([
            'status' => 'Approved',
        ]);

        // Auto-create Activity for the Calendar feature
        Activity::create([
            'title'         => $plan->title,
            'component'     => $plan->section?->component ?? 'CWTS',
            'activity_date' => $plan->scheduled_date ?? now(),
            'activity_time' => '08:00:00', // Default morning start time
            'location'      => $plan->location ?? 'TBA',
            'description'   => $plan->objectives ?? $plan->description,
            'created_at'    => now(),
        ]);

        // Notify the instructor
        \App\Models\PortalNotification::create([
            'user_id' => $plan->instructor_id,
            'type'    => 'system',
            'title'   => 'Activity Plan Approved',
            'message' => "Your activity plan \"{$plan->title}\" has been approved and added to the calendar." . ($request->feedback ? " Coordinator feedback: \"{$request->feedback}\"" : ""),
            'is_read' => false,
        ]);

        // Record Audit Log
        $user = auth()->user();
        AuditLog::record(
            'Approved Activity Plan',
            'Activity Plans',
            $plan->title,
            "Coordinator {$user->name} approved activity plan '{$plan->title}' submitted by " . ($plan->instructor?->name ?? 'Instructor') . ". Added to calendar.",
            'system',
            [
                'username' => $user->name,
                'email'    => $user->email,
                'role'     => 'coordinator',
            ]
        );

        return back()->with('success', "Activity plan \"{$plan->title}\" approved and added to the calendar successfully.");
    }

    public function rejectPlan(Request $request, $id)
    {
        $plan = ActivityPlan::findOrFail($id);
        $plan->update([
            'status' => 'Rejected',
            'feedback' => $request->feedback,
        ]);

        // Notify the instructor
        \App\Models\PortalNotification::create([
            'user_id' => $plan->instructor_id,
            'type'    => 'system',
            'title'   => 'Activity Plan Rejected',
            'message' => "Your activity plan \"{$plan->title}\" has been rejected." . ($request->feedback ? " Reason/Feedback: \"{$request->feedback}\"" : ""),
            'is_read' => false,
        ]);

        // Record Audit Log
        $user = auth()->user();
        AuditLog::record(
            'Rejected Activity Plan',
            'Activity Plans',
            $plan->title,
            "Coordinator {$user->name} rejected activity plan '{$plan->title}' submitted by " . ($plan->instructor?->name ?? 'Instructor') . ". Reason: " . ($request->feedback ?? 'No feedback provided'),
            'system',
            [
                'username' => $user->name,
                'email'    => $user->email,
                'role'     => 'coordinator',
            ]
        );

        return back()->with('success', "Activity plan \"{$plan->title}\" rejected.");
    }

    public function revisionPlan(Request $request, $id)
    {
        $plan = ActivityPlan::findOrFail($id);
        $plan->update([
            'status' => 'Revision',
            'feedback' => $request->feedback,
        ]);

        // Notify the instructor
        \App\Models\PortalNotification::create([
            'user_id' => $plan->instructor_id,
            'type'    => 'system',
            'title'   => 'Revisions Requested for Activity Plan',
            'message' => "Revisions have been requested for your activity plan \"{$plan->title}\"." . ($request->feedback ? " Needed changes: \"{$request->feedback}\"" : ""),
            'is_read' => false,
        ]);

        // Record Audit Log
        $user = auth()->user();
        AuditLog::record(
            'Requested Plan Revisions',
            'Activity Plans',
            $plan->title,
            "Coordinator {$user->name} requested revisions for plan '{$plan->title}' submitted by " . ($plan->instructor?->name ?? 'Instructor') . ". Feedback: " . ($request->feedback ?? 'No feedback provided'),
            'system',
            [
                'username' => $user->name,
                'email'    => $user->email,
                'role'     => 'coordinator',
            ]
        );

        return back()->with('success', "Revisions requested for activity plan \"{$plan->title}\".");
    }

    public function approveReport(Request $request, $id)
    {
        $report = AccomplishmentReport::findOrFail($id);
        $report->update([
            'status' => 'Reviewed',
        ]);

        // Notify the instructor
        \App\Models\PortalNotification::create([
            'user_id' => $report->instructor_id,
            'type'    => 'system',
            'title'   => 'Accomplishment Report Reviewed',
            'message' => "Your accomplishment report \"{$report->title}\" has been reviewed and accepted." . ($request->feedback ? " Coordinator feedback: \"{$request->feedback}\"" : ""),
            'is_read' => false,
        ]);

        // Record Audit Log
        $user = auth()->user();
        AuditLog::record(
            'Reviewed Accomplishment Report',
            'Accomplishment Reports',
            $report->title,
            "Coordinator {$user->name} reviewed/approved accomplishment report '{$report->title}' submitted by " . ($report->instructor?->name ?? 'Instructor'),
            'system',
            [
                'username' => $user->name,
                'email'    => $user->email,
                'role'     => 'coordinator',
            ]
        );

        return back()->with('success', "Accomplishment report \"{$report->title}\" accepted and marked as Reviewed.");
    }

    public function rejectReport(Request $request, $id)
    {
        $report = AccomplishmentReport::findOrFail($id);
        $report->update([
            'status' => 'Rejected',
            'feedback' => $request->feedback,
        ]);

        // Notify the instructor
        \App\Models\PortalNotification::create([
            'user_id' => $report->instructor_id,
            'type'    => 'system',
            'title'   => 'Accomplishment Report Rejected',
            'message' => "Your accomplishment report \"{$report->title}\" has been rejected." . ($request->feedback ? " Reason/Feedback: \"{$request->feedback}\"" : ""),
            'is_read' => false,
        ]);

        // Record Audit Log
        $user = auth()->user();
        AuditLog::record(
            'Rejected Accomplishment Report',
            'Accomplishment Reports',
            $report->title,
            "Coordinator {$user->name} rejected accomplishment report '{$report->title}' submitted by " . ($report->instructor?->name ?? 'Instructor') . ". Reason: " . ($request->feedback ?? 'No feedback provided'),
            'system',
            [
                'username' => $user->name,
                'email'    => $user->email,
                'role'     => 'coordinator',
            ]
        );

        return back()->with('success', "Accomplishment report \"{$report->title}\" rejected.");
    }

    public function revisionReport(Request $request, $id)
    {
        $report = AccomplishmentReport::findOrFail($id);
        $report->update([
            'status' => 'Revision',
            'feedback' => $request->feedback,
        ]);

        // Notify the instructor
        \App\Models\PortalNotification::create([
            'user_id' => $report->instructor_id,
            'type'    => 'system',
            'title'   => 'Revisions Requested for Accomplishment Report',
            'message' => "Revisions have been requested for your accomplishment report \"{$report->title}\"." . ($request->feedback ? " Needed changes: \"{$request->feedback}\"" : ""),
            'is_read' => false,
        ]);

        // Record Audit Log
        $user = auth()->user();
        AuditLog::record(
            'Requested Report Revisions',
            'Accomplishment Reports',
            $report->title,
            "Coordinator {$user->name} requested revisions for report '{$report->title}' submitted by " . ($report->instructor?->name ?? 'Instructor') . ". Feedback: " . ($request->feedback ?? 'No feedback provided'),
            'system',
            [
                'username' => $user->name,
                'email'    => $user->email,
                'role'     => 'coordinator',
            ]
        );

        return back()->with('success', "Revisions requested for \"{$report->title}\".");
    }

    public function deletePlan($id)
    {
        $plan = ActivityPlan::findOrFail($id);
        
        if ($plan->status !== 'Approved') {
            abort(403, 'Unauthorized. Only approved activity plans can be deleted by the coordinator.');
        }
        
        $title = $plan->title;
        
        // Also delete associated Activity on calendar if it was approved
        Activity::where('title', $title)
            ->where('activity_date', $plan->scheduled_date)
            ->delete();

        $plan->delete();

        // Record Audit Log
        $user = auth()->user();
        AuditLog::record(
            'Deleted Activity Plan',
            'Activity Plans',
            $title,
            "Coordinator {$user->name} deleted activity plan '{$title}' submitted by " . ($plan->instructor?->name ?? 'Instructor'),
            'edit',
            [
                'username' => $user->name,
                'email'    => $user->email,
                'role'     => 'coordinator',
            ]
        );

        return back()->with('success', "Activity plan \"{$title}\" successfully deleted.");
    }

    public function deleteReport($id)
    {
        $report = AccomplishmentReport::findOrFail($id);
        
        if ($report->status !== 'Reviewed') {
            abort(403, 'Unauthorized. Only reviewed accomplishment reports can be deleted by the coordinator.');
        }
        
        $title = $report->title;
        $report->delete();

        // Record Audit Log
        $user = auth()->user();
        AuditLog::record(
            'Deleted Accomplishment Report',
            'Accomplishment Reports',
            $title,
            "Coordinator {$user->name} deleted accomplishment report '{$title}' submitted by " . ($report->instructor?->name ?? 'Instructor'),
            'edit',
            [
                'username' => $user->name,
                'email'    => $user->email,
                'role'     => 'coordinator',
            ]
        );

        return back()->with('success', "Accomplishment report \"{$title}\" successfully deleted.");
    }

    public function calendar(Request $request)
    {
        $year = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);

        $selectedMonth = \Carbon\Carbon::createFromDate($year, $month, 1);
        $currentMonthStart = $selectedMonth->copy()->startOfMonth();
        $currentMonthEnd = $selectedMonth->copy()->endOfMonth();

        // 1. Fetch Calendar Activities
        $activitiesDb = Activity::whereBetween('activity_date', [$currentMonthStart, $currentMonthEnd])->get();

        // 2. Fetch Approved Activity Plans in this month
        $approvedPlans = ActivityPlan::with('section')
            ->where('status', 'Approved')
            ->whereBetween('scheduled_date', [$currentMonthStart, $currentMonthEnd])
            ->get();

        // 3. Fetch Official Announcements in this month
        $announcementsDb = Announcement::where(function ($q) use ($currentMonthStart, $currentMonthEnd) {
            $q->whereBetween('scheduled_date', [$currentMonthStart, $currentMonthEnd])
              ->orWhere(function ($q2) use ($currentMonthStart, $currentMonthEnd) {
                  $q2->whereNull('scheduled_date')
                     ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd]);
              });
        })->get();

        // 4. Merge them into a single collection
        $activities = collect();

        foreach ($activitiesDb as $act) {
            $activities->push((object)[
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
            $exists = $activities->contains(function ($existing) use ($plan) {
                return strtolower($existing->title) === strtolower($plan->title) &&
                       $existing->activity_date->toDateString() === $plan->scheduled_date->toDateString();
            });

            if (!$exists) {
                $activities->push((object)[
                    'title' => $plan->title,
                    'component' => $plan->section?->component ?? 'CWTS',
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
            $activities->push((object)[
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

        // Fetch upcoming activities (starting from today)
        $upcomingDb = Activity::where('activity_date', '>=', now()->startOfDay())
            ->orderBy('activity_date', 'asc')
            ->limit(5)
            ->get();

        $upcomingPlans = ActivityPlan::with('section')
            ->where('status', 'Approved')
            ->where('scheduled_date', '>=', now()->startOfDay())
            ->orderBy('scheduled_date', 'asc')
            ->limit(5)
            ->get();

        $upcomingAnn = Announcement::where(function ($q) {
            $q->where('scheduled_date', '>=', now()->startOfDay())
              ->orWhere(function ($q2) {
                  $q2->whereNull('scheduled_date')
                     ->where('created_at', '>=', now()->startOfDay());
              });
        })->limit(5)->get();

        $upcomingActivities = collect();
        foreach ($upcomingDb as $act) {
            $upcomingActivities->push((object)[
                'title' => $act->title,
                'component' => $act->component ?? 'General',
                'activity_date' => $act->activity_date,
                'location' => $act->location ?? 'TBA',
                'description' => $act->description ?? '',
                'is_plan' => false,
                'is_announcement' => false,
            ]);
        }
        foreach ($upcomingPlans as $plan) {
            $exists = $upcomingActivities->contains(function ($existing) use ($plan) {
                return strtolower($existing->title) === strtolower($plan->title) &&
                       $existing->activity_date->toDateString() === $plan->scheduled_date->toDateString();
            });

            if (!$exists) {
                $upcomingActivities->push((object)[
                    'title' => $plan->title,
                    'component' => $plan->section?->component ?? 'CWTS',
                    'activity_date' => $plan->scheduled_date,
                    'location' => $plan->location ?? 'TBA',
                    'description' => $plan->objectives ?? $plan->description ?? '',
                    'is_plan' => true,
                    'is_announcement' => false,
                ]);
            }
        }
        foreach ($upcomingAnn as $ann) {
            $date = $ann->scheduled_date ? $ann->scheduled_date : $ann->created_at;
            $upcomingActivities->push((object)[
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
        $upcomingActivities = $upcomingActivities->sortBy('activity_date')->take(5);

        // Precompute prev and next months
        $prevMonth = $selectedMonth->copy()->subMonth();
        $nextMonth = $selectedMonth->copy()->addMonth();

        return view('coordinator.calendar', compact(
            'activities', 'upcomingActivities', 'selectedMonth', 'prevMonth', 'nextMonth'
        ));
    }

    public function announcements(Request $request)
    {
        $query = Announcement::orderByDesc('is_pinned')
            ->orderByDesc('created_at');

        if ($search = trim((string)$request->query('search'))) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%")
                  ->orWhere('source', 'like', "%{$search}%")
                  ->orWhere('target_role', 'like', "%{$search}%")
                  ->orWhere('component', 'like', "%{$search}%");
            });
        }

        $announcements = $query->paginate(10)->withQueryString();

        $totalCount = Announcement::count();
        $pinnedCount = Announcement::where('is_pinned', true)->count();
        $calendarCount = Announcement::whereNotNull('scheduled_date')->count();

        return view('coordinator.announcements', compact('announcements', 'totalCount', 'pinnedCount', 'calendarCount'));
    }

    public function storeAnnouncement(Request $request)
    {
        $data = $request->validate([
            'title'          => 'required|string|max:255',
            'content'        => 'required|string',
            'source'         => 'nullable|string|max:100',
            'target_role'    => 'nullable|string|max:50',
            'component'      => 'nullable|string|max:50',
            'scheduled_date' => 'nullable|date',
            'is_pinned'      => 'nullable|boolean',
        ]);

        $announcement = Announcement::create([
            'title'          => $data['title'],
            'content'        => $data['content'],
            'source'         => $data['source'] ?? 'NSTP Office',
            'target_role'    => $data['target_role'] ?? 'All',
            'component'      => $data['component'] ?? 'General',
            'scheduled_date' => $data['scheduled_date'] ?? null,
            'is_pinned'      => $request->has('is_pinned') ? (bool)$request->is_pinned : false,
            'created_at'     => now(),
        ]);

        AuditLog::create([
            'user_name'    => auth()->user()?->name ?? 'Coordinator',
            'role'         => 'Coordinator',
            'action'       => 'Created Announcement',
            'details'      => "Published announcement: {$announcement->title}",
            'performed_at' => now(),
        ]);

        return back()->with('success', "Announcement '{$announcement->title}' published successfully.");
    }

    public function deleteAnnouncement($id)
    {
        $announcement = Announcement::findOrFail($id);
        $title = $announcement->title;
        $announcement->delete();

        AuditLog::create([
            'user_name'    => auth()->user()?->name ?? 'Coordinator',
            'role'         => 'Coordinator',
            'action'       => 'Deleted Announcement',
            'details'      => "Deleted announcement: {$title}",
            'performed_at' => now(),
        ]);

        return back()->with('success', "Announcement '{$title}' deleted successfully.");
    }

    public function storeActivity(Request $request)
    {
        $data = $request->validate([
            'title'         => 'required|string|max:255',
            'component'     => 'required|in:CWTS,LTS,ROTC',
            'activity_date' => 'required|date',
            'activity_time' => 'required|string|max:20',
            'location'      => 'required|string|max:255',
            'description'   => 'nullable|string',
        ]);

        $activity = Activity::create([
            'title'         => $data['title'],
            'component'     => $data['component'],
            'activity_date' => $data['activity_date'],
            'activity_time' => $data['activity_time'] . ':00', // Format as H:i:s
            'location'      => $data['location'],
            'description'   => $data['description'] ?? null,
            'created_at'    => now(),
        ]);

        // Record Audit Log
        $user = auth()->user();
        AuditLog::record(
            'Created Calendar Activity',
            'Activities',
            $activity->title,
            "Coordinator {$user->name} created a new calendar activity '{$activity->title}' for {$activity->component} scheduled on {$data['activity_date']}.",
            'create',
            [
                'username' => $user->name,
                'email'    => $user->email,
                'role'     => 'coordinator',
            ]
        );

        return back()->with('success', "Event '{$activity->title}' has been successfully created and added to the calendars.");
    }

    public function archive()
    {
        $trashedStudents = Student::onlyTrashed()->orderBy('deleted_at', 'desc')->get();
        if ($trashedStudents->isEmpty()) {
            $trashedStudents = Student::withTrashed()->orderBy('created_at', 'desc')->get();
        }

        $students = $trashedStudents->map(function($student) {
            $studentId = !empty($student->student_id) ? $student->student_id : 'N/A';
            $serialNo  = !empty($student->serial_no) ? $student->serial_no : 'N/A';

            $firstName  = trim($student->first_name ?? '');
            $middleName = trim($student->middle_name ?? '');
            $lastName   = trim($student->last_name ?? '');

            if ($middleName && !str_ends_with(strtolower($firstName), strtolower($middleName))) {
                $fullName = trim($lastName . ', ' . $firstName . ' ' . $middleName);
            } else {
                $fullName = trim($lastName . ', ' . $firstName);
            }

            $yearLevelStr = $student->year_level ? ($student->year_level . (
                $student->year_level == 1 ? 'st' : ($student->year_level == 2 ? 'nd' : ($student->year_level == 3 ? 'rd' : 'th'))
            ) . ' Year') : '1st Year';

            $dob = $student->date_of_birth ? \Carbon\Carbon::parse($student->date_of_birth)->format('M d, Y') : 'N/A';
            $archivedDate = $student->deleted_at ? \Carbon\Carbon::parse($student->deleted_at)->format('M d, Y h:i A') : 'N/A';

            return (object)[
                'db_id'          => $student->id,
                'id'             => $studentId,
                'student_id'     => $studentId,
                'serial_no'      => $serialNo,
                'first_name'     => $firstName,
                'middle_name'    => $middleName,
                'last_name'      => $lastName,
                'name'           => $fullName ?: 'N/A',
                'course'         => $student->course ?: 'N/A',
                'year_level'     => $yearLevelStr,
                'program'        => $student->component ?: 'CWTS',
                'status'         => $student->enrollment_status ?: ($student->trashed() ? 'Archived' : 'Active'),
                'sex'            => $student->sex ?: 'N/A',
                'gender'         => $student->sex ?: 'N/A',
                'email'          => $student->email ?: 'N/A',
                'contact_number' => $student->contact_number ?: 'N/A',
                'cell_no'        => $student->contact_number ?: 'N/A',
                'address'        => $student->complete_address ?: 'N/A',
                'dob'            => $dob,
                'birth_place'    => $student->place_of_birth ?: 'N/A',
                'grade'          => $student->grade ?: 'N/A',
                'numerical_grade'=> $student->numerical_grade ?: 'N/A',
                'archived_at'    => $archivedDate,
                'is_trashed'     => $student->trashed(),
            ];
        });

        return view('coordinator.archive', compact('students'));
    }

    public function restoreStudent($id)
    {
        $student = Student::withTrashed()->findOrFail($id);
        $student->restore();

        // Record Audit Log
        $user = auth()->user();
        AuditLog::record(
            'Restored Student',
            'Students',
            $student->student_id ?: $student->id,
            "Restored student {$student->last_name}, {$student->first_name} from Student Archive",
            'edit',
            [
                'username' => $user ? $user->name : 'NSTP Coordinator',
                'email'    => $user ? $user->email : 'coordinator@dnsc.edu.ph',
                'role'     => 'coordinator',
            ]
        );

        return back()->with('success', "Student {$student->first_name} {$student->last_name} restored successfully.");
    }

    public function ocr()
    {
        return view('coordinator.ocr');
    }

    private function autoSeedTemplates()
    {
        if (CertificateTemplate::withTrashed()->count() === 0) {
            CertificateTemplate::create([
                'name'            => 'Default CWTS Completion Template',
                'component'       => 'CWTS',
                'bg_theme'        => 'classic',
                'title_text'      => 'Certificate of Completion',
                'body_text'       => 'This is to certify that [STUDENT_NAME] has successfully completed the Civic Welfare Training Service (CWTS) component of the National Service Training Program (NSTP) during the school year [SCHOOL_YEAR] in section [SECTION], in compliance with Republic Act No. 9163.',
                'signatory_name'  => 'DR. EMIL F. BRIONES',
                'signatory_title' => 'NSTP Coordinator',
                'is_active'       => true,
            ]);

            CertificateTemplate::create([
                'name'            => 'Default LTS Achievement Template',
                'component'       => 'LTS',
                'bg_theme'        => 'elegant',
                'title_text'      => 'Certificate of Achievement',
                'body_text'       => 'This is to certify that [STUDENT_NAME] has successfully completed the Literacy Training Service (LTS) component of the National Service Training Program (NSTP) during the school year [SCHOOL_YEAR] in section [SECTION], in compliance with Republic Act No. 9163.',
                'signatory_name'  => 'DR. EMIL F. BRIONES',
                'signatory_title' => 'NSTP Coordinator',
                'is_active'       => true,
            ]);

            CertificateTemplate::create([
                'name'            => 'Default ROTC Military Service Template',
                'component'       => 'ROTC',
                'bg_theme'        => 'military',
                'title_text'      => 'Certificate of Military Training',
                'body_text'       => 'This is to certify that Cadet [STUDENT_NAME] has successfully completed the Reserve Officers\' Training Corps (ROTC) component of the National Service Training Program (NSTP) during the school year [SCHOOL_YEAR] in section [SECTION], in compliance with Republic Act No. 9163.',
                'signatory_name'  => 'COL. RAMON G. ALVAREZ',
                'signatory_title' => 'ROTC Commandant',
                'is_active'       => true,
            ]);
        }
    }

    public function certificates()
    {
        $this->autoSeedTemplates();

        // Fetch OCR Grade Import audit logs to base certificates on processed OCR grade sheets
        $ocrLogs = AuditLog::where('action', 'Imported Grades')
            ->orderBy('performed_at', 'desc')
            ->get();

        $ocrSectionNames = [];
        $ocrMetadata = [];

        foreach ($ocrLogs as $log) {
            $secName = trim($log->target);
            $upperSec = strtoupper($secName);
            if (!isset($ocrMetadata[$upperSec])) {
                preg_match('/from file:\s*([^\r\n\t]+?)(?:\.\s*Total:|$)/i', $log->details, $fnMatches);
                $filename = isset($fnMatches[1]) ? trim($fnMatches[1]) : ($secName . '_ocr_grades.xlsx');

                $ocrMetadata[$upperSec] = [
                    'filename'     => $filename,
                    'imported_at'  => $log->performed_at->diffForHumans(),
                    'performed_at' => $log->performed_at,
                ];
                $ocrSectionNames[] = $secName;
            }
        }

        $sectionsQuery = Section::with('instructor');
        $allSections = $sectionsQuery->get();

        $sections = $allSections->map(function($s) use ($ocrMetadata) {
            $passedCount = \DB::table('students')
                ->join('enrollments', 'students.id', '=', 'enrollments.student_id')
                ->where('enrollments.section_id', $s->id)
                ->where('enrollments.status', 'Passed')
                ->count();

            $totalStudents = \DB::table('enrollments')
                ->where('section_id', $s->id)
                ->count();

            $upperSec = strtoupper(trim($s->section_name));
            $meta = $ocrMetadata[$upperSec] ?? null;

            return (object)[
                'id'             => $s->id,
                'code'           => $s->section_name,
                'program'        => $s->component,
                'schoolYear'     => $s->school_year,
                'passed_count'   => $passedCount,
                'total_students' => $totalStudents,
                'instructor'     => $s->instructor_name,
                'filename'       => $meta['filename'] ?? ($s->section_name . '_grades.xlsx'),
                'imported_at'    => $meta['imported_at'] ?? 'Imported Grade Sheet',
                'is_ocr'         => !empty($meta),
            ];
        })->filter(function($s) {
            return $s->passed_count > 0 || $s->total_students > 0 || $s->is_ocr;
        })->values();

        $templates = CertificateTemplate::where('is_active', true)->get();

        // Recent issued list from audit logs
        $recentlyIssued = AuditLog::where('action', 'Generated Certificates')
            ->orderBy('performed_at', 'desc')
            ->limit(5)
            ->get()
            ->map(function($log) {
                // Extract count from details: e.g. "generated 12 certificate(s)"
                preg_match('/generated (\d+) certificate/i', $log->details, $matches);
                $count = isset($matches[1]) ? intval($matches[1]) : 0;

                return (object)[
                    'id'           => $log->id,
                    'section'      => $log->target,
                    'count'        => $count,
                    'performed_at' => $log->performed_at->diffForHumans(),
                    'by'           => $log->username ?? 'Coordinator',
                ];
            });

        return view('coordinator.certificates', compact('sections', 'templates', 'recentlyIssued'));
    }

    public function getSectionCertificates($sectionId)
    {
        $section = null;
        if (is_numeric($sectionId)) {
            $section = Section::find($sectionId);
        }
        if (!$section) {
            $section = Section::where('section_name', $sectionId)->first();
        }
        if (!$section) {
            $section = Section::where('section_name', 'like', $sectionId)->first();
        }

        if (!$section) {
            return response()->json([
                'success' => false,
                'message' => 'Section not found',
                'students' => []
            ], 404);
        }

        $students = \DB::table('students')
            ->join('enrollments', 'students.id', '=', 'enrollments.student_id')
            ->where('enrollments.section_id', $section->id)
            ->where('enrollments.status', 'Passed')
            ->select([
                'students.student_id',
                'students.first_name',
                'students.last_name',
                'students.course',
                'students.component',
                'enrollments.final_grade',
                'enrollments.serial_number'
            ])
            ->orderBy('students.last_name')
            ->get()
            ->map(function($std) {
                return [
                    'student_no' => $std->student_id,
                    'serial_no'  => $std->serial_number,
                    'name'       => trim($std->last_name . ', ' . $std->first_name, ', '),
                    'course'     => $std->course ?? 'BSIT',
                    'component'  => $std->component,
                    'grade'      => $std->final_grade,
                ];
            });

        return response()->json([
            'success'      => true,
            'section_id'   => $section->id,
            'section_name' => $section->section_name,
            'school_year'  => $section->school_year,
            'students'     => $students,
        ]);
    }

    public function logGeneration(Request $request)
    {
        $data = $request->validate([
            'section' => 'required|string',
            'count'   => 'required|integer',
        ]);

        $user = auth()->user();
        AuditLog::record(
            'Generated Certificates',
            'Certificates',
            $data['section'],
            "Coordinator {$user->name} generated {$data['count']} certificate(s) for section {$data['section']}.",
            'system',
            [
                'username' => $user ? $user->name : 'Coordinator',
                'email'    => $user ? $user->email : 'coordinator@dnsc.edu.ph',
                'role'     => 'coordinator',
                'count'    => $data['count'],
            ]
        );

        return response()->json(['success' => true]);
    }

    /**
     * Generate a single-student certificate as a downloaded PDF (server-side via dompdf).
     */
    public function generateCertificatePdf(Request $request)
    {
        $request->validate([
            'template_id'  => 'required|integer',
            'student_name' => 'required|string',
            'student_no'   => 'required|string',
            'section'      => 'required|string',
            'school_year'  => 'nullable|string',
        ]);

        $template    = CertificateTemplate::findOrFail($request->template_id);
        $studentName = $request->student_name;
        $studentNo   = $request->student_no;
        $sectionName = $request->section;
        $schoolYear  = $request->school_year ?? '2025-2026';
        $issuedDate  = now()->format('F d, Y');

        [$borderColor, $bgColor, $titleColor, $sigColor] = $this->resolveTheme($template->bg_theme);
        $sigInitials = $this->buildInitials($template->signatory_name);

        $bodyHtml = e($template->body_text);
        $bodyHtml = str_replace('[STUDENT_NAME]', '<span class="student-name">' . e($studentName) . '</span>', $bodyHtml);
        $bodyHtml = str_replace('[SECTION]',      '<span class="highlight">' . e($sectionName) . '</span>',  $bodyHtml);
        $bodyHtml = str_replace('[SCHOOL_YEAR]',  '<span class="highlight">' . e($schoolYear)  . '</span>',  $bodyHtml);
        $bodyHtml = str_replace('[DATE]',         '<span class="highlight">' . e($issuedDate)  . '</span>',  $bodyHtml);
        $bodyHtml = str_replace('[SERIAL_NO]',    '<span class="highlight">' . e($studentNo)   . '</span>',  $bodyHtml);
        $bodyHtml = str_replace('[STUDENT_NO]',   '<span class="highlight">' . e($studentNo)   . '</span>',  $bodyHtml);

        $students = [
            [
                'name'       => $studentName,
                'student_no' => $studentNo,
                'bodyHtml'   => $bodyHtml,
            ]
        ];

        $pdf = Pdf::loadView('coordinator.pdf.certificate', compact(
            'template', 'students', 'sigInitials',
            'issuedDate', 'borderColor', 'bgColor', 'titleColor', 'sigColor'
        ))->setPaper('letter', 'landscape');

        $user = auth()->user();
        AuditLog::record(
            'Generated Certificates', 'Certificates',
            "{$sectionName} ({$studentName})",
            "Coordinator {$user->name} generated 1 certificate for {$studentName} in section {$sectionName}.",
            'system',
            ['username' => $user->name, 'email' => $user->email, 'role' => 'coordinator', 'count' => 1]
        );

        $safeSerial = Str::slug($studentNo);
        $safeName   = Str::slug($studentName);
        return $pdf->download($safeSerial . '_' . $safeName . '.pdf');
    }

    /**
     * Generate all passed-student certificates for a section as a multi-page PDF.
     */
    public function generateBatchPdf(Request $request)
    {
        $request->validate([
            'template_id' => 'required|integer',
            'section_id'  => 'required',
            'student_nos' => 'nullable|array',
        ]);

        $template = CertificateTemplate::findOrFail($request->template_id);
        
        $secIdentifier = $request->section_id;
        $section = is_numeric($secIdentifier) ? Section::find($secIdentifier) : null;
        if (!$section) {
            $section = Section::where('section_name', $secIdentifier)->first();
        }
        if (!$section) {
            $section = Section::where('section_name', 'like', $secIdentifier)->first();
        }

        if (!$section) {
            return response()->json(['error' => 'Section not found.'], 404);
        }

        $issuedDate = now()->format('F d, Y');
        $schoolYear = $section->school_year ?? '2025-2026';

        [$borderColor, $bgColor, $titleColor, $sigColor] = $this->resolveTheme($template->bg_theme);
        $sigInitials = $this->buildInitials($template->signatory_name);

        $studentRecords = \DB::table('students')
            ->join('enrollments', 'students.id', '=', 'enrollments.student_id')
            ->where('enrollments.section_id', $section->id)
            ->where('enrollments.status', 'Passed')
            ->select(['students.student_id', 'enrollments.serial_number', 'students.first_name', 'students.last_name'])
            ->orderBy('students.last_name')
            ->get();

        if ($studentRecords->isEmpty()) {
            return response()->json(['error' => 'No passed students found.'], 422);
        }

        $customNos = $request->input('student_nos', []);
        $students = [];
        foreach ($studentRecords as $rec) {
            $studentName = trim($rec->last_name . ', ' . $rec->first_name, ', ');
            $dbSerial    = $rec->serial_number ?? $rec->student_id;
            $studentNo   = isset($customNos[$rec->student_id]) && trim($customNos[$rec->student_id]) !== '' ? trim($customNos[$rec->student_id]) : $dbSerial;

            $bodyHtml = e($template->body_text);
            $bodyHtml = str_replace('[STUDENT_NAME]', '<span class="student-name">' . e($studentName) . '</span>', $bodyHtml);
            $bodyHtml = str_replace('[SECTION]',      '<span class="highlight">' . e($section->section_name) . '</span>', $bodyHtml);
            $bodyHtml = str_replace('[SCHOOL_YEAR]',  '<span class="highlight">' . e($schoolYear) . '</span>',            $bodyHtml);
            $bodyHtml = str_replace('[DATE]',         '<span class="highlight">' . e($issuedDate) . '</span>',            $bodyHtml);
            $bodyHtml = str_replace('[SERIAL_NO]',    '<span class="highlight">' . e($studentNo)   . '</span>',            $bodyHtml);
            $bodyHtml = str_replace('[STUDENT_NO]',   '<span class="highlight">' . e($studentNo)   . '</span>',            $bodyHtml);

            $students[] = [
                'name'       => $studentName,
                'student_no' => $studentNo,
                'bodyHtml'   => $bodyHtml,
            ];
        }

        $pdf = Pdf::loadView('coordinator.pdf.certificate', compact(
            'template', 'students', 'sigInitials',
            'issuedDate', 'borderColor', 'bgColor', 'titleColor', 'sigColor'
        ))->setPaper('letter', 'landscape');

        $user  = auth()->user();
        $count = count($students);
        AuditLog::record(
            'Generated Certificates', 'Certificates',
            $section->section_name,
            "Coordinator {$user->name} generated {$count} certificate(s) for section {$section->section_name}.",
            'system',
            ['username' => $user->name, 'email' => $user->email, 'role' => 'coordinator', 'count' => $count]
        );

        return $pdf->download(Str::slug($section->section_name) . '_certificates.pdf');
    }

    /** Resolve theme colours for certificate PDF */
    private function resolveTheme(string $theme): array
    {
        return match ($theme) {
            'elegant'  => ['#064e3b', '#f0fdf4', '#064e3b', '#10b981'],
            'military' => ['#78350f', '#fffbeb', '#78350f', '#b45309'],
            'modern'   => ['#4c1d95', '#ffffff', '#4c1d95', '#8b5cf6'],
            default    => ['#0f172a', '#ffffff', '#0f172a', '#3b82f6'],
        };
    }

    /** Build signatory initials from full name */
    private function buildInitials(string $name): string
    {
        $parts = explode(' ', $name);
        return count($parts) > 1
            ? $parts[0][0] . '. ' . end($parts)
            : $name;
    }

    public function deleteGenerationLog($id)
    {
        $log = AuditLog::findOrFail($id);
        $log->delete();

        return back()->with('success', "Recently issued log successfully removed.");
    }

    public function certificateTemplates()
    {
        $this->autoSeedTemplates();

        $templates = CertificateTemplate::orderBy('created_at', 'desc')->get();
        return view('coordinator.certificate_templates', compact('templates'));
    }

    public function storeTemplate(Request $request)
    {
        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'component'       => 'required|string|in:CWTS,LTS,ROTC,ALL',
            'bg_image'        => 'nullable|image|mimes:png,jpg,jpeg|max:4096',
            'title_text'      => 'required|string|max:255',
            'body_text'       => 'required|string',
            'signatory_name'  => 'required|string|max:255',
            'signatory_title' => 'required|string|max:255',
        ]);

        $data['bg_theme'] = 'classic';

        if ($request->hasFile('bg_image')) {
            $file = $request->file('bg_image');
            $filename = time() . '_' . Str::slug($request->name) . '.' . $file->getClientOriginalExtension();
            $uploadPath = public_path('uploads/certificates');
            $uploadPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $uploadPath);
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }
            
            // Bypass Symfony's strict is_writable checks for Windows OneDrive folders
            $targetPath = $uploadPath . DIRECTORY_SEPARATOR . $filename;
            if (!@move_uploaded_file($file->getRealPath(), $targetPath)) {
                copy($file->getRealPath(), $targetPath);
            }
            
            $data['bg_image'] = '/uploads/certificates/' . $filename;
        }

        $template = CertificateTemplate::create($data);

        // Record Audit Log
        $user = auth()->user();
        AuditLog::record(
            'Created Certificate Template',
            'Certificate Templates',
            $template->name,
            "Coordinator {$user->name} created a new certificate template named '{$template->name}' for {$template->component} program.",
            'system',
            [
                'username' => $user ? $user->name : 'Coordinator',
                'email'    => $user ? $user->email : 'coordinator@dnsc.edu.ph',
                'role'     => 'coordinator',
            ]
        );

        return back()->with('success', "Certificate template \"{$template->name}\" successfully created!");
    }

    public function updateTemplate(Request $request, $id)
    {
        $template = CertificateTemplate::findOrFail($id);

        $data = $request->validate([
            'name'            => 'required|string|max:255',
            'component'       => 'required|string|in:CWTS,LTS,ROTC,ALL',
            'bg_image'        => 'nullable|image|mimes:png,jpg,jpeg|max:4096',
            'title_text'      => 'required|string|max:255',
            'body_text'       => 'required|string',
            'signatory_name'  => 'required|string|max:255',
            'signatory_title' => 'required|string|max:255',
            'is_active'       => 'nullable|boolean',
        ]);

        if ($request->hasFile('bg_image')) {
            // Delete old file if exists
            if ($template->bg_image && file_exists(public_path($template->bg_image))) {
                @unlink(public_path($template->bg_image));
            }

            $file = $request->file('bg_image');
            $filename = time() . '_' . Str::slug($request->name) . '.' . $file->getClientOriginalExtension();
            $uploadPath = public_path('uploads/certificates');
            $uploadPath = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $uploadPath);
            if (!file_exists($uploadPath)) {
                mkdir($uploadPath, 0755, true);
            }

            // Bypass Symfony's strict is_writable checks for Windows OneDrive folders
            $targetPath = $uploadPath . DIRECTORY_SEPARATOR . $filename;
            if (!@move_uploaded_file($file->getRealPath(), $targetPath)) {
                copy($file->getRealPath(), $targetPath);
            }

            $data['bg_image'] = '/uploads/certificates/' . $filename;
        }

        if (!isset($data['is_active'])) {
            $data['is_active'] = $request->has('is_active') ? true : false;
        } else {
            $data['is_active'] = (bool)$data['is_active'];
        }

        $template->update($data);

        // Record Audit Log
        $user = auth()->user();
        AuditLog::record(
            'Updated Certificate Template',
            'Certificate Templates',
            $template->name,
            "Coordinator {$user->name} updated the certificate template '{$template->name}'.",
            'edit',
            [
                'username' => $user ? $user->name : 'Coordinator',
                'email'    => $user ? $user->email : 'coordinator@dnsc.edu.ph',
                'role'     => 'coordinator',
            ]
        );

        return back()->with('success', "Certificate template \"{$template->name}\" successfully updated!");
    }

    public function destroyTemplate($id)
    {
        $template = CertificateTemplate::findOrFail($id);
        $name = $template->name;
        $template->delete();

        // Record Audit Log
        $user = auth()->user();
        AuditLog::record(
            'Deleted Certificate Template',
            'Certificate Templates',
            $name,
            "Coordinator {$user->name} deleted the certificate template '{$name}'.",
            'edit',
            [
                'username' => $user ? $user->name : 'Coordinator',
                'email'    => $user ? $user->email : 'coordinator@dnsc.edu.ph',
                'role'     => 'coordinator',
            ]
        );

        return back()->with('success', "Certificate template \"{$name}\" successfully deleted.");
    }

    public function getTemplate($id)
    {
        $template = CertificateTemplate::findOrFail($id);
        return response()->json($template);
    }

    public function audit()
    {
        $logs = AuditLog::orderBy('performed_at', 'desc')->get();

        return view('coordinator.audit', compact('logs'));
    }

    public function reports(Request $request)
    {
        $schoolYears = Section::orderBy('school_year', 'desc')
            ->pluck('school_year')
            ->unique()
            ->filter()
            ->values()
            ->toArray();
        if (empty($schoolYears)) {
            $schoolYears = ['2025-2026', '2024-2025'];
        }

        $sections = Section::orderBy('section_name')->get();

        $reportType = $request->query('report_type', 'student_performance');
        $program    = $request->query('program', 'all');
        $schoolYear = $request->query('school_year', 'all');
        $sectionId  = $request->query('section_id', 'all');
        $status     = $request->query('status', 'all');

        $allStudents = $this->getFilteredStudentPerformance($program, $schoolYear, $sectionId, $status);

        // Paginate records to 10 per page
        $currentPage = \Illuminate\Pagination\Paginator::resolveCurrentPage();
        $perPage = 10;
        $currentPageItems = $allStudents->slice(($currentPage - 1) * $perPage, $perPage)->values();
        $students = new \Illuminate\Pagination\LengthAwarePaginator(
            $currentPageItems,
            $allStudents->count(),
            $perPage,
            $currentPage,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        if ($request->wantsJson() || $request->query('format') === 'json') {
            return response()->json([
                'success'  => true,
                'count'    => $allStudents->count(),
                'students' => $students,
            ]);
        }

        return view('coordinator.reports', compact(
            'schoolYears', 'sections', 'students', 'allStudents',
            'reportType', 'program', 'schoolYear', 'sectionId', 'status'
        ));
    }

    public function exportReportPdf(Request $request)
    {
        $reportType = $request->input('report_type', $request->query('report_type', 'student_performance'));
        $program    = $request->input('program', $request->query('program', 'all'));
        $schoolYear = $request->input('school_year', $request->query('school_year', 'all'));
        $sectionId  = $request->input('section_id', $request->query('section_id', 'all'));
        $status     = $request->input('status', $request->query('status', 'all'));

        $students = $this->getFilteredStudentPerformance($program, $schoolYear, $sectionId, $status);

        $reportTitle = match ($reportType) {
            'terminal'            => 'NSTP Terminal Report',
            'masterlist'          => 'Master Student Enrollment List',
            'consolidated_grades' => 'Consolidated Grade Sheet',
            'financial'           => 'Financial Summary Report',
            default               => 'Enrolled Student Performance Report',
        };

        $generatedAt = now()->format('F d, Y h:i A');

        $passedCount  = $students->where('remarks', 'Passed')->count();
        $failedCount  = $students->where('remarks', 'Failed')->count();
        $pendingCount = $students->where('remarks', 'Pending')->count();
        $totalCount   = $students->count();

        $receivedBy       = $request->input('received_by', $request->query('received_by', 'FELICIDAD L. FORRO'));
        $receivedByTitle  = $request->input('received_by_title', $request->query('received_by_title', 'Registrar III'));
        $submittedBy      = $request->input('submitted_by', $request->query('submitted_by', 'DODONGAN, EUGINE B. / DR. EMIL F. BRIONES'));
        $submittedByTitle = $request->input('submitted_by_title', $request->query('submitted_by_title', 'Professor / NSTP Coordinator'));
        $receivedSig      = $request->input('received_sig', $request->query('received_sig', null));
        $submittedSig     = $request->input('submitted_sig', $request->query('submitted_sig', null));

        $pdf = Pdf::loadView('coordinator.pdf.report_pdf', compact(
            'reportTitle', 'reportType', 'students', 'program', 'schoolYear',
            'sectionId', 'status', 'generatedAt', 'passedCount', 'failedCount',
            'pendingCount', 'totalCount',
            'receivedBy', 'receivedByTitle', 'submittedBy', 'submittedByTitle',
            'receivedSig', 'submittedSig'
        ))->setPaper('letter', 'portrait');

        $filename = Str::slug($reportTitle) . '_' . date('Ymd_His') . '.pdf';

        // Record Audit Log
        $user = auth()->user();
        AuditLog::record(
            'Exported Report PDF',
            'Reports',
            $reportTitle,
            "Coordinator " . ($user ? $user->name : 'User') . " exported report '{$reportTitle}' PDF with {$totalCount} student records.",
            'system',
            ['username' => $user->name ?? 'Coordinator', 'email' => $user->email ?? 'coordinator@dnsc.edu.ph', 'role' => 'coordinator']
        );

        return $pdf->download($filename);
    }

    private function getFilteredStudentPerformance($program = 'all', $schoolYear = 'all', $sectionId = 'all', $status = 'all')
    {
        $query = Student::join('enrollments', 'students.id', '=', 'enrollments.student_id')
            ->join('sections', 'enrollments.section_id', '=', 'sections.id')
            ->select([
                'students.id as db_id',
                'students.student_id',
                'students.serial_no',
                'students.first_name',
                'students.middle_name',
                'students.last_name',
                'students.course',
                'students.year_level',
                'students.component as student_component',
                'students.enrollment_status',
                'students.grade as student_grade',
                'students.numerical_grade as student_num_grade',
                'students.sex',
                'students.email',
                'sections.section_name',
                'sections.component as section_component',
                'sections.school_year',
                'sections.semester',
                'enrollments.final_grade as enrollment_final_grade',
                'enrollments.status as enrollment_status_override',
            ])
            ->whereNull('students.deleted_at');

        if ($program !== 'all') {
            $query->where(function($q) use ($program) {
                $q->where('sections.component', $program)
                  ->orWhere('students.component', $program);
            });
        }

        if ($schoolYear !== 'all') {
            $query->where('sections.school_year', $schoolYear);
        }

        if ($sectionId !== 'all') {
            if (is_numeric($sectionId)) {
                $query->where('sections.id', $sectionId);
            } else {
                $query->where('sections.section_name', $sectionId);
            }
        }

        $records = $query->orderBy('students.last_name')->get();

        $results = $records->map(function ($s) {
            $studentNo = !empty($s->student_id) ? $s->student_id : 'N/A';
            $firstName = trim($s->first_name ?? '');
            $middleName = trim($s->middle_name ?? '');
            $lastName = trim($s->last_name ?? '');

            if ($middleName && !str_ends_with(strtolower($firstName), strtolower($middleName))) {
                $fullName = trim($lastName . ', ' . $firstName . ' ' . $middleName);
            } else {
                $fullName = trim($lastName . ', ' . $firstName);
            }

            $numGrade = $s->enrollment_final_grade !== null ? $s->enrollment_final_grade : $s->student_num_grade;
            $numGradeVal = is_numeric($numGrade) ? floatval($numGrade) : null;
            $numGradeFormatted = $numGradeVal !== null ? number_format($numGradeVal, 2) : 'N/A';

            $remarks = 'Pending';
            $statusOverride = $s->enrollment_status_override;
            if (in_array($statusOverride, ['Passed', 'Failed', 'Dropped', 'Pending'])) {
                $remarks = $statusOverride;
            } elseif ($s->student_grade === 'pass') {
                $remarks = 'Passed';
            } elseif ($s->student_grade === 'fail') {
                $remarks = 'Failed';
            } elseif ($numGradeVal !== null) {
                if ($numGradeVal >= 1.0 && $numGradeVal <= 3.0) {
                    $remarks = 'Passed';
                } elseif ($numGradeVal > 3.0 && $numGradeVal <= 5.0) {
                    $remarks = 'Failed';
                }
            }

            $comp = $s->section_component ?: ($s->student_component ?: 'CWTS');

            return (object)[
                'db_id'        => $s->db_id,
                'student_no'   => $studentNo,
                'serial_no'    => $s->serial_no ?: 'N/A',
                'name'         => $fullName,
                'course'       => $s->course ?: 'BSIT',
                'program'      => $comp,
                'section'      => $s->section_name ?: 'Unassigned',
                'school_year'  => $s->school_year ?: '2025-2026',
                'semester'     => $s->semester ?: '1st Semester',
                'grade'        => $numGradeFormatted,
                'num_grade'    => $numGradeVal,
                'remarks'      => $remarks,
                'email'        => $s->email ?: 'N/A',
            ];
        });

        if ($status !== 'all') {
            $results = $results->filter(function($item) use ($status) {
                return strtolower($item->remarks) === strtolower($status);
            })->values();
        }

        return $results;
    }

    public function sectionStudents($sectionCode)
    {
        $section = Section::where('section_name', $sectionCode)->firstOrFail();

        // Query students enrolled in this section via the enrollments table
        // We leftJoin class_list_students to fallback to demographic data parsed from class list spreadsheets
        $studentsPaginated = Student::join('enrollments', 'students.id', '=', 'enrollments.student_id')
            ->join('sections', 'sections.id', '=', 'enrollments.section_id')
            ->leftJoin('class_list_students', function($join) use ($sectionCode) {
                $join->on('class_list_students.student_id', '=', 'students.student_id')
                     ->where('class_list_students.section_name', '=', $sectionCode);
            })
            ->where('sections.id', $section->id)
            ->select([
                'students.*',
                'enrollments.status as enrollment_status',
                'enrollments.final_grade as enrollment_final_grade',
                'class_list_students.dob as class_list_dob',
                'class_list_students.place_of_birth as class_list_pob',
                'class_list_students.gender as class_list_gender',
                'class_list_students.address as class_list_address',
                'class_list_students.cell_no as class_list_cell_no',
                'class_list_students.email as class_list_email',
            ])
            ->paginate(10);

        $mappedCollection = $studentsPaginated->getCollection()->map(function($student) {
            // DOB fallback
            $dobSource = $student->date_of_birth ?? $student->class_list_dob;
            $dobFormatted = 'N/A';
            if ($dobSource) {
                if ($dobSource instanceof \DateTimeInterface) {
                    $dobFormatted = $dobSource->format('Y-m-d');
                } else {
                    $time = strtotime($dobSource);
                    $dobFormatted = $time ? date('Y-m-d', $time) : $dobSource;
                }
            }

            // POB fallback
            $pobFormatted = $student->place_of_birth ?? $student->class_list_pob ?? 'N/A';

            // Gender fallback
            $genderFormatted = $student->sex ?? $student->class_list_gender ?? 'N/A';

            // Address fallback
            $addressFormatted = $student->complete_address ?? $student->class_list_address ?? 'N/A';

            // Cell Number fallback
            $cellFormatted = $student->contact_number ?? $student->class_list_cell_no ?? 'N/A';

            // Email fallback
            $emailFormatted = $student->email ?? $student->class_list_email ?? 'N/A';

            return (object)[
                'db_id' => $student->id,
                'id' => $student->student_id,
                'name' => trim($student->last_name . ', ' . $student->first_name, ', '),
                'course' => $student->course ?? 'N/A',
                'program' => $student->component ?? 'CWTS',
                'email' => $emailFormatted,
                'status' => $student->enrollment_status ?? 'Active',
                'dob' => $dobFormatted,
                'birth_place' => $pobFormatted,
                'gender' => $genderFormatted,
                'address' => $addressFormatted,
                'cell_no' => $cellFormatted,
                'final_grade' => $student->enrollment_final_grade,
            ];
        });
        $studentsPaginated->setCollection($mappedCollection);
        $students = $studentsPaginated;

        return view('coordinator.section_students', compact('section', 'students'));
    }

    public function importGrades(Request $request)
    {
        $data = $request->validate([
            'section'  => 'required|string|max:64',
            'filename' => 'required|string|max:255',
            'students' => 'required|array',
        ]);

        $sectionName = $data['section'];
        $filename = $data['filename'];
        $studentsList = $data['students'];

        $totalCount = count($studentsList);
        $matchedCount = 0;
        $createdCount = 0;
        $passedCount = 0;
        $failedCount = 0;

        $results = [];

        \DB::beginTransaction();

        try {
            // 1. Find or resolve Section
            $section = Section::where('section_name', $sectionName)->first();
            if (!$section) {
                $component = 'CWTS';
                $scUpper = strtoupper($sectionName);
                if (str_contains($scUpper, 'ROTC')) {
                    $component = 'ROTC';
                } elseif (str_contains($scUpper, 'LTS')) {
                    $component = 'LTS';
                }

                $section = Section::create([
                    'section_name' => $sectionName,
                    'component'    => $component,
                    'school_year'  => '2025-2026',
                    'semester'     => '1st Semester',
                    'status'       => 'Active',
                ]);
            }

            // 2. Process each student row
            foreach ($studentsList as $row) {
                $rawName = trim($row['name'] ?? '');
                if (empty($rawName)) {
                    continue;
                }

                $rawGrade = $row['grade'] ?? null;
                $rawRemarks = trim((string)($row['remarks'] ?? ''));
                $remarks = 'N/A';
                $cleanRemarks = strtolower($rawRemarks);
                if (in_array($cleanRemarks, ['passed', 'pass', 'p']) || str_starts_with($cleanRemarks, 'pass')) {
                    $remarks = 'Passed';
                } elseif (in_array($cleanRemarks, ['failed', 'fail', 'f']) || str_starts_with($cleanRemarks, 'fail')) {
                    $remarks = 'Failed';
                } elseif (in_array($cleanRemarks, ['pending', 'active']) || str_starts_with($cleanRemarks, 'pend')) {
                    $remarks = 'Pending';
                }

                $studentNo = $row['student_no'] ?? null;
                $serialNo = $row['serial_no'] ?? null;

                // Match existing student
                // Parse first/last name
                $parsedName = Student::parseName($rawName);
                $firstName = $parsedName['first_name'];
                $lastName = $parsedName['last_name'];

                $student = null;
                if ($studentNo) {
                    $student = Student::where('student_id', $studentNo)->first();
                }
                if (!$student) {
                    // Match by first & last name (case-insensitive)
                    $student = Student::where('last_name', 'like', $lastName)
                        ->where('first_name', 'like', $firstName)
                        ->first();
                }

                // Standardize remarks from raw grade if remarks is N/A or empty
                if ($remarks === 'N/A' && $rawGrade !== null) {
                    if (is_numeric($rawGrade)) {
                        $g = floatval($rawGrade);
                        if ($g >= 1.0 && $g <= 3.0) {
                            $remarks = 'Passed';
                        } elseif ($g > 3.0 && $g <= 5.0) {
                            $remarks = 'Failed';
                        } elseif ($g >= 75.0 && $g <= 100.0) {
                            $remarks = 'Passed';
                        } elseif ($g >= 50.0 && $g < 75.0) {
                            $remarks = 'Failed';
                        }
                    } else {
                        $cleanGrade = strtolower(trim((string)$rawGrade));
                        if (in_array($cleanGrade, ['pass', 'passed', 'pass/fail', 'p']) || str_starts_with($cleanGrade, 'pass')) {
                            $remarks = 'Passed';
                        } elseif (in_array($cleanGrade, ['fail', 'failed', 'f']) || str_starts_with($cleanGrade, 'fail')) {
                            $remarks = 'Failed';
                        }
                    }
                }

                // If remarks is still N/A, try to default to existing student grade / enrollment status
                if ($remarks === 'N/A' && $student) {
                    $existingEnrollment = \DB::table('enrollments')
                        ->where('student_id', $student->id)
                        ->where('section_id', $section->id)
                        ->first();

                    if ($existingEnrollment && in_array($existingEnrollment->status, ['Passed', 'Failed'])) {
                        $remarks = $existingEnrollment->status;
                    } elseif ($student->grade !== null) {
                        $remarks = $student->grade === 'pass' ? 'Passed' : 'Failed';
                    }
                }

                $gradeEnum = $remarks === 'Passed' ? 'pass' : ($remarks === 'Failed' ? 'fail' : null);
                $enrollmentStatus = $remarks === 'Passed' ? 'Passed' : ($remarks === 'Failed' ? 'Failed' : 'Pending');

                if ($remarks === 'Passed') {
                    $passedCount++;
                } elseif ($remarks === 'Failed') {
                    $failedCount++;
                }

                $isNew = false;
                if ($student) {
                    $matchedCount++;
                    // Update student grade status and numerical grade
                    $student->update([
                        'grade' => $gradeEnum ?: $student->grade,
                        'numerical_grade' => is_numeric($rawGrade) ? floatval($rawGrade) : $student->numerical_grade,
                    ]);
                } else {
                    $createdCount++;
                    $isNew = true;
                    // Auto-generate student ID
                    $newId = $studentNo;
                    if (!$newId) {
                        do {
                            $newId = '2024-' . rand(10000, 99999);
                        } while (Student::where('student_id', $newId)->exists());
                    }

                    $student = Student::create([
                        'student_id'         => $newId,
                        'first_name'         => $firstName,
                        'last_name'          => $lastName,
                        'course'             => 'BSIT',
                        'component'          => $section->component,
                        'enrollment_status'  => 'Active',
                        'grade'              => $gradeEnum,
                        'numerical_grade'    => is_numeric($rawGrade) ? floatval($rawGrade) : null,
                        'created_at'         => now(),
                    ]);
                }

                // 3. Sync enrollment with numeric grade and status
                $gradeNum = is_numeric($rawGrade) ? floatval($rawGrade) : ($student ? (\DB::table('enrollments')->where('student_id', $student->id)->where('section_id', $section->id)->value('final_grade') ?? $student->numerical_grade) : null);

                $existingEnrollment = \DB::table('enrollments')
                    ->where('student_id', $student->id)
                    ->where('section_id', $section->id)
                    ->first();

                // Resolve serial number conflict to prevent SQL 1062 unique key violation
                $finalSerial = $serialNo;
                if (!empty($finalSerial)) {
                    $serialOwner = \DB::table('enrollments')
                        ->where('serial_number', $finalSerial)
                        ->when($existingEnrollment, function($q) use ($existingEnrollment) {
                            return $q->where('id', '!=', $existingEnrollment->id);
                        })
                        ->first();

                    if ($serialOwner) {
                        if ($serialOwner->student_id == $student->id) {
                            \DB::table('enrollments')->where('id', $serialOwner->id)->update(['serial_number' => null]);
                        } else {
                            $finalSerial = null;
                        }
                    }
                }

                if ($existingEnrollment) {
                    \DB::table('enrollments')
                        ->where('id', $existingEnrollment->id)
                        ->update([
                            'final_grade'   => $gradeNum,
                            'status'        => $enrollmentStatus,
                            'serial_number' => $finalSerial ?: $existingEnrollment->serial_number,
                            'updated_at'    => now(),
                        ]);
                } else {
                    \DB::table('enrollments')->insert([
                        'student_id'    => $student->id,
                        'section_id'    => $section->id,
                        'final_grade'   => $gradeNum,
                        'status'        => $enrollmentStatus,
                        'serial_number' => $finalSerial,
                        'created_at'    => now(),
                        'updated_at'    => now(),
                    ]);
                }

                $results[] = [
                    'student_no' => $student->student_id,
                    'name'       => trim($lastName . ', ' . $firstName, ', '),
                    'grade'      => $rawGrade,
                    'remarks'    => $remarks,
                    'is_new'     => $isNew,
                    'email'      => $student->email ?? null,
                ];
            }

            // 4. Record Audit Log
            $user = auth()->user();
            AuditLog::record(
                'Imported Grades',
                'Grades',
                $sectionName,
                "Processed OCR Grade sheet for section $sectionName from file: $filename. Total: $totalCount, Matched: $matchedCount, Created: $createdCount, Passed: $passedCount, Failed: $failedCount",
                'system',
                [
                    'username' => $user ? $user->name : 'NSTP Coordinator',
                    'email'    => $user ? $user->email : 'coordinator@dnsc.edu.ph',
                    'role'     => 'coordinator',
                ]
            );

            \DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'OCR Grade Import processed successfully.',
                'summary' => [
                    'total'     => $totalCount,
                    'matched'   => $matchedCount,
                    'created'   => $createdCount,
                    'passed'    => $passedCount,
                    'failed'    => $failedCount,
                    'section'   => $sectionName,
                    'component' => $section->component,
                ],
                'results' => $results,
            ]);

        } catch (\Exception $e) {
            \DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Failed to process OCR Grade sheet: ' . $e->getMessage(),
            ], 500);
        }
    }

    public function storeInstructor(Request $request)
    {
        $data = $request->validate([
            'name'             => 'required|string|exists:portal_users,name',
            'dept'             => 'required|string|max:128',
            'assigned_section' => 'nullable|string|exists:sections,section_name',
        ]);

        $instructor = PortalUser::where('name', $data['name'])
            ->where('role', 'instructor')
            ->firstOrFail();

        $instructor->update([
            'dept'   => $data['dept'],
            'status' => 'Active',
        ]);

        $sectionMsg = "";
        if (!empty($data['assigned_section'])) {
            $section = Section::where('section_name', $data['assigned_section'])->first();
            if ($section) {
                $section->update([
                    'instructor_id' => $instructor->id,
                ]);

                // Create a notification for the instructor
                \App\Models\PortalNotification::create([
                    'user_id' => $instructor->id,
                    'type'    => 'assignment',
                    'title'   => 'New Section Assigned',
                    'message' => "You have been assigned to handle section {$section->section_name}. Please check your My Classes page for details.",
                    'is_read' => false,
                ]);

                $sectionMsg = " and assigned to section {$section->section_name}";
            }
        }

        // Record Audit Log
        $user = auth()->user();
        AuditLog::record(
            'Configured Instructor',
            'Instructors',
            $instructor->name,
            "Configured instructor: {$instructor->name} ({$instructor->email}) in department {$instructor->dept}{$sectionMsg}",
            'edit',
            [
                'username' => $user ? $user->name : 'NSTP Coordinator',
                'email'    => $user ? $user->email : 'coordinator@dnsc.edu.ph',
                'role'     => 'coordinator',
            ]
        );

        return back()->with('success', "Instructor {$instructor->name} successfully configured{$sectionMsg}.");
    }

    public function destroyInstructor($id)
    {
        $instructor = PortalUser::where('role', 'instructor')->findOrFail($id);
        $instructorName = $instructor->name;

        // Perform the deletion
        $instructor->delete();

        // Record Audit Log
        $user = auth()->user();
        AuditLog::record(
            'Deleted Instructor',
            'Instructors',
            $instructorName,
            "Deleted instructor account: {$instructorName}",
            'edit',
            [
                'username' => $user ? $user->name : 'NSTP Coordinator',
                'email'    => $user ? $user->email : 'coordinator@dnsc.edu.ph',
                'role'     => 'coordinator',
            ]
        );

        return back()->with('success', "Instructor {$instructorName} has been successfully deleted.");
    }

    public function updateInstructor(Request $request, $id)
    {
        $instructor = PortalUser::where('role', 'instructor')->findOrFail($id);
        
        $data = $request->validate([
            'dept'             => 'required|string|max:128',
            'status'           => 'required|string|in:Active,Inactive',
            'assigned_section' => 'nullable|string|exists:sections,section_name',
        ]);

        $oldDept = $instructor->dept;
        $oldStatus = $instructor->status;

        $instructor->update([
            'dept'   => $data['dept'],
            'status' => $data['status'],
        ]);

        $sectionMsg = "";
        if (!empty($data['assigned_section'])) {
            $section = Section::where('section_name', $data['assigned_section'])->first();
            if ($section) {
                $section->update([
                    'instructor_id' => $instructor->id,
                ]);

                // Create a notification for the instructor
                \App\Models\PortalNotification::create([
                    'user_id' => $instructor->id,
                    'type'    => 'assignment',
                    'title'   => 'New Section Assigned',
                    'message' => "You have been assigned to handle section {$section->section_name}. Please check your My Classes page for details.",
                    'is_read' => false,
                ]);

                $sectionMsg = " and assigned to section {$section->section_name}";
            }
        }

        // Record Audit Log
        $user = auth()->user();
        AuditLog::record(
            'Updated Instructor',
            'Instructors',
            $instructor->name,
            "Updated instructor: {$instructor->name} ({$instructor->email}). Dept: {$oldDept} -> {$instructor->dept}, Status: {$oldStatus} -> {$instructor->status}{$sectionMsg}",
            'edit',
            [
                'username' => $user ? $user->name : 'NSTP Coordinator',
                'email'    => $user ? $user->email : 'coordinator@dnsc.edu.ph',
                'role'     => 'coordinator',
            ]
        );

        return back()->with('success', "Instructor {$instructor->name} successfully updated{$sectionMsg}.");
    }

    public function storeSection(Request $request)
    {
        $data = $request->validate([
            'code'            => 'required|string|max:64|unique:sections,section_name',
            'program'         => 'required|in:CWTS,LTS,ROTC',
            'school_year'     => 'nullable|string|max:32',
            'semester'        => 'nullable|string|max:64',
            'room'            => 'nullable|string|max:64',
            'instructor_name' => 'nullable|string|max:255',
        ]);

        $instructor = null;
        if (!empty($data['instructor_name'])) {
            $instructor = PortalUser::where('name', $data['instructor_name'])
                ->where('role', 'instructor')
                ->first();
        }

        $section = Section::create([
            'section_name'  => $data['code'],
            'component'     => $data['program'],
            'school_year'   => $data['school_year'] ?? '2025-2026',
            'semester'      => $data['semester'] ?? '1st Semester',
            'room'          => $data['room'] ?? 'TBA',
            'instructor_id' => $instructor?->id,
            'status'        => 'Active',
        ]);

        // Record Audit Log
        $user = auth()->user();
        AuditLog::record(
            'Created Section',
            'Sections',
            $section->section_name,
            "Created new section: {$section->section_name} ({$section->component}) under school year {$section->school_year}",
            'edit',
            [
                'username' => $user ? $user->name : 'NSTP Coordinator',
                'email'    => $user ? $user->email : 'coordinator@dnsc.edu.ph',
                'role'     => 'coordinator',
            ]
        );

        return back()->with('success', "Section {$section->section_name} successfully created.");
    }

    public function updateSection(Request $request, $id)
    {
        $section = Section::findOrFail($id);

        $data = $request->validate([
            'code'            => 'required|string|max:64|unique:sections,section_name,' . $id,
            'program'         => 'required|in:CWTS,LTS,ROTC',
            'school_year'     => 'nullable|string|max:32',
            'semester'        => 'nullable|string|max:64',
            'room'            => 'nullable|string|max:64',
            'instructor_name' => 'nullable|string|max:255',
        ]);

        $instructor = null;
        if (!empty($data['instructor_name'])) {
            $instructor = PortalUser::where('name', $data['instructor_name'])
                ->where('role', 'instructor')
                ->first();
        }

        $oldName = $section->section_name;

        $section->update([
            'section_name'  => $data['code'],
            'component'     => $data['program'],
            'school_year'   => $data['school_year'] ?? '2025-2026',
            'semester'      => $data['semester'] ?? '1st Semester',
            'room'          => $data['room'] ?? 'TBA',
            'instructor_id' => $instructor?->id,
        ]);

        if ($oldName !== $data['code']) {
            \DB::table('class_list_students')
                ->where('section_name', $oldName)
                ->update(['section_name' => $data['code']]);
        }

        // Record Audit Log
        $user = auth()->user();
        AuditLog::record(
            'Updated Section',
            'Sections',
            $section->section_name,
            "Updated section configuration for {$oldName} (now {$section->section_name}).",
            'edit',
            [
                'username' => $user ? $user->name : 'NSTP Coordinator',
                'email'    => $user ? $user->email : 'coordinator@dnsc.edu.ph',
                'role'     => 'coordinator',
            ]
        );

        return back()->with('success', "Section {$section->section_name} successfully updated.");
    }

    public function deleteSection($id)
    {
        $section = Section::find($id);
        if (!$section) {
            $section = Section::where('section_name', $id)->first();
        }

        if (!$section) {
            return redirect()->route('coordinator.sections')->with('error', "Section not found or already deleted.");
        }

        $sectionName = $section->section_name;

        // Clean up enrollments and class_list_students
        \DB::table('enrollments')->where('section_id', $section->id)->delete();
        \DB::table('class_list_students')->where('section_name', $sectionName)->delete();

        $section->delete();

        // Record Audit Log
        $user = auth()->user();
        AuditLog::record(
            'Deleted Section',
            'Sections',
            $sectionName,
            "Deleted section: {$sectionName}",
            'edit',
            [
                'username' => $user ? $user->name : 'NSTP Coordinator',
                'email'    => $user ? $user->email : 'coordinator@dnsc.edu.ph',
                'role'     => 'coordinator',
            ]
        );

        return redirect()->route('coordinator.sections')->with('success', "Section {$sectionName} successfully deleted.");
    }

    public function instructorInfo($id)
    {
        $instructor = PortalUser::where('role', 'instructor')->findOrFail($id);

        // Paginate handled sections with custom parameter name
        $sections = Section::where('instructor_id', $instructor->id)
            ->paginate(5, ['*'], 'sections_page');

        // Paginate handled students with custom parameter name
        $studentsPaginated = Student::join('enrollments', 'students.id', '=', 'enrollments.student_id')
            ->join('sections', 'sections.id', '=', 'enrollments.section_id')
            ->where('sections.instructor_id', $instructor->id)
            ->select('students.*', 'sections.section_name as section_code', 'enrollments.status as enrollment_status')
            ->paginate(10, ['*'], 'students_page');

        $mappedCollection = $studentsPaginated->getCollection()->map(function($student) {
            return (object)[
                'id' => $student->student_id,
                'name' => trim($student->last_name . ', ' . $student->first_name, ', '),
                'course' => $student->course ?? 'N/A',
                'section' => $student->section_code,
                'email' => $student->email ?? 'N/A',
                'status' => $student->enrollment_status ?? 'Active',
            ];
        });
        $studentsPaginated->setCollection($mappedCollection);
        $students = $studentsPaginated;

        return view('coordinator.instructor_info', compact('instructor', 'sections', 'students'));
    }

    public function updateStudentInSection(Request $request, $sectionCode, $studentId)
    {
        $section = Section::where('section_name', $sectionCode)->firstOrFail();
        $student = Student::findOrFail($studentId);

        $data = $request->validate([
            'student_no'        => 'required|string|max:64|unique:students,student_id,' . $student->id,
            'name'              => 'required|string|max:255',
            'email'             => 'nullable|email|max:255',
            'course'            => 'nullable|string|max:64',
            'enrollment_status' => 'required|in:Active,Passed,Failed,Pending,Dropped',
            'gender'            => 'nullable|string|max:16',
            'dob'               => 'nullable|date',
            'birth_place'       => 'nullable|string|max:255',
            'cell_no'           => 'nullable|string|max:32',
            'address'           => 'nullable|string|max:500',
            'final_grade'       => 'nullable|numeric|between:1.0,5.0',
        ]);

        $parsedName = Student::parseName($data['name']);
        
        $oldStudentNo = $student->student_id;
        $gradeVal = is_numeric($data['final_grade']) ? floatval($data['final_grade']) : null;

        $studentUpdate = [
            'student_id'      => $data['student_no'],
            'first_name'      => $parsedName['first_name'],
            'last_name'       => $parsedName['last_name'],
            'email'           => $data['email'] ?? null,
            'course'          => $data['course'] ?? null,
            'sex'             => $data['gender'] ?? null,
            'date_of_birth'   => $data['dob'] ?? null,
            'place_of_birth'  => $data['birth_place'] ?? null,
            'contact_number'  => $data['cell_no'] ?? null,
            'complete_address'=> $data['address'] ?? null,
            'numerical_grade' => $gradeVal,
        ];

        // Sync main student grade enum based on status
        $status = $data['enrollment_status'];
        if ($status === 'Passed') {
            $studentUpdate['grade'] = 'pass';
        } elseif ($status === 'Failed') {
            $studentUpdate['grade'] = 'fail';
        } else {
            $studentUpdate['grade'] = null;
        }

        $student->update($studentUpdate);

        // Map status value to enrollment/student properties if needed
        \DB::table('enrollments')
            ->where('student_id', $student->id)
            ->where('section_id', $section->id)
            ->update([
                'status' => $data['enrollment_status'],
                'final_grade' => $gradeVal,
                'updated_at' => now(),
            ]);

        // Keep class_list_students updated
        \DB::table('class_list_students')
            ->where('section_name', $sectionCode)
            ->where('student_id', $oldStudentNo)
            ->update([
                'student_id'     => $data['student_no'],
                'name'           => $data['name'],
                'email'          => $data['email'] ?? null,
                'course'         => $data['course'] ?? null,
                'gender'         => $data['gender'] ?? null,
                'dob'            => $data['dob'] ?? null,
                'place_of_birth' => $data['birth_place'] ?? null,
                'cell_no'        => $data['cell_no'] ?? null,
                'address'        => $data['address'] ?? null,
                'updated_at'     => now(),
            ]);

        // Record Audit Log
        $user = auth()->user();
        AuditLog::record(
            'Updated Student in Section',
            'Students',
            $student->student_id,
            "Updated student {$student->last_name}, {$student->first_name} inside section {$section->section_name}",
            'edit',
            [
                'username' => $user ? $user->name : 'NSTP Coordinator',
                'email'    => $user ? $user->email : 'coordinator@dnsc.edu.ph',
                'role'     => 'coordinator',
            ]
        );

        return back()->with('success', "Student credentials and enrollment status updated successfully.");
    }

    public function removeStudentFromSection($sectionCode, $studentId)
    {
        $student = Student::findOrFail($studentId);
        $user = auth()->user();

        if ($sectionCode === 'ALL') {
            \DB::table('enrollments')
                ->where('student_id', $student->id)
                ->delete();

            if (!empty($student->student_id)) {
                \DB::table('class_list_students')
                    ->where('student_id', $student->student_id)
                    ->delete();
            }

            // Soft delete the student record itself so they appear in the archive with deleted_at set
            $student->delete();

            // Record Audit Log
            AuditLog::record(
                'Deleted Student',
                'Students',
                $student->student_id ?: $student->id,
                "Deleted student record {$student->last_name}, {$student->first_name} from Masterlist",
                'edit',
                [
                    'username' => $user ? $user->name : 'NSTP Coordinator',
                    'email'    => $user ? $user->email : 'coordinator@dnsc.edu.ph',
                    'role'     => 'coordinator',
                ]
            );

            return back()->with('success', "Student record has been successfully deleted.");
        }

        $section = Section::where('section_name', $sectionCode)->firstOrFail();

        \DB::table('enrollments')
            ->where('student_id', $student->id)
            ->where('section_id', $section->id)
            ->delete();

        // Remove from class_list_students as well
        if (!empty($student->student_id)) {
            \DB::table('class_list_students')
                ->where('section_name', $sectionCode)
                ->where('student_id', $student->student_id)
                ->delete();
        }

        // Soft delete the student record itself so they appear in the archive with deleted_at set
        $student->delete();

        // Record Audit Log
        AuditLog::record(
            'Deleted Student',
            'Students',
            $student->student_id ?: $student->id,
            "Deleted/Unenrolled student {$student->last_name}, {$student->first_name} from section {$section->section_name}",
            'edit',
            [
                'username' => $user ? $user->name : 'NSTP Coordinator',
                'email'    => $user ? $user->email : 'coordinator@dnsc.edu.ph',
                'role'     => 'coordinator',
            ]
        );

        return back()->with('success', "Student has been successfully deleted.");
    }
}

