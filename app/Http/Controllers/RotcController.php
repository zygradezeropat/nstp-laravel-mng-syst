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
use Illuminate\Support\Facades\Auth;

class RotcController extends Controller
{
    public function dashboard()
    {
        // 1. Live Cadet Count
        $totalCadets = Student::where('component', 'ROTC')->count();

        // 2. Live Platoons (ROTC Sections)
        $rotcSections = Section::where('component', 'ROTC')->where('status', 'Active')->get();
        $sectionsCount = $rotcSections->count();
        $platoonNames = $rotcSections->pluck('section_name')->join(' · ') ?: 'None';

        // 3. Live Officer in Charge count (distinct instructors handling ROTC sections)
        $totalOICs = Section::where('component', 'ROTC')->whereNotNull('instructor_id')->distinct('instructor_id')->count();
        $oicNames = PortalUser::where('role', 'instructor')->where('dept', 'ROTC')->pluck('name')->join(' · ') ?: 'None';

        // 4. Live Reports Open (Pending Activity Plans and Accomplishment Reports)
        $pendingPlansCount = ActivityPlan::where(function ($query) {
            $query->whereHas('section', fn($q) => $q->where('component', 'ROTC'))
                  ->orWhere(function ($q) {
                      $q->whereHas('section', fn($s) => $s->where('section_name', 'All Section'))
                        ->whereHas('instructor', fn($u) => $u->where('role', 'rotc')->orWhere('dept', 'ROTC'));
                  });
        })->where('status', 'Pending')->count();

        $pendingReportsCount = AccomplishmentReport::where(function ($query) {
            $query->whereHas('section', fn($q) => $q->where('component', 'ROTC'))
                  ->orWhere(function ($q) {
                      $q->whereHas('section', fn($s) => $s->where('section_name', 'All Section'))
                        ->whereHas('instructor', fn($u) => $u->where('role', 'rotc')->orWhere('dept', 'ROTC'));
                  });
        })->where('status', 'Pending')->count();

        $reportsOpen = $pendingPlansCount + $pendingReportsCount;

        $stats = [
            ['label' => 'Total Cadets', 'value' => (string)$totalCadets, 'sub' => "+{$totalCadets} active roster"],
            ['label' => 'Active Platoons', 'value' => (string)$sectionsCount, 'sub' => $platoonNames],
            ['label' => 'Total Officer in Charge', 'value' => (string)$totalOICs, 'sub' => $oicNames],
            ['label' => 'Reports Open', 'value' => (string)$reportsOpen, 'sub' => "{$reportsOpen} awaiting review"],
        ];

        // 5. Live Accomplishment Reports and Plans List
        $plansList = ActivityPlan::with('section')
            ->where(function ($query) {
                $query->whereHas('section', fn($q) => $q->where('component', 'ROTC'))
                      ->orWhere(function ($q) {
                          $q->whereHas('section', fn($s) => $s->where('section_name', 'All Section'))
                            ->whereHas('instructor', fn($u) => $u->where('role', 'rotc')->orWhere('dept', 'ROTC'));
                      });
            })
            ->orderBy('updated_at', 'desc')
            ->limit(4)
            ->get()
            ->map(function ($plan) {
                return (object)[
                    'title' => $plan->title,
                    'status' => strtolower($plan->status),
                    'progress' => $plan->status === 'Approved' ? 100 : ($plan->status === 'Pending' ? 80 : 40),
                    'due' => $plan->status === 'Approved' ? 'Approved ' . ($plan->updated_at?->format('M d') ?? '') : ($plan->scheduled_date ? 'Scheduled ' . $plan->scheduled_date->format('M d') : 'N/A'),
                    'color' => $plan->status === 'Approved' ? 'emerald' : ($plan->status === 'Pending' ? 'indigo' : ($plan->status === 'Revision' ? 'rose' : 'slate')),
                ];
            });

        $reportsList = AccomplishmentReport::with('section')
            ->where(function ($query) {
                $query->whereHas('section', fn($q) => $q->where('component', 'ROTC'))
                      ->orWhere(function ($q) {
                          $q->whereHas('section', fn($s) => $s->where('section_name', 'All Section'))
                            ->whereHas('instructor', fn($u) => $u->where('role', 'rotc')->orWhere('dept', 'ROTC'));
                      });
            })
            ->orderBy('updated_at', 'desc')
            ->limit(4)
            ->get()
            ->map(function ($report) {
                return (object)[
                    'title' => $report->title,
                    'status' => strtolower($report->status === 'Reviewed' ? 'approved' : ($report->status === 'Revision' ? 'revisions' : $report->status)),
                    'progress' => $report->status === 'Reviewed' ? 100 : ($report->status === 'Pending' ? 90 : 50),
                    'due' => $report->status === 'Reviewed' ? 'Approved ' . ($report->updated_at?->format('M d') ?? '') : ($report->completed_date ? 'Completed ' . $report->completed_date->format('M d') : 'N/A'),
                    'color' => $report->status === 'Reviewed' ? 'emerald' : ($report->status === 'Pending' ? 'indigo' : ($report->status === 'Revision' ? 'rose' : 'slate')),
                ];
            });

        $reports = $plansList->concat($reportsList)->sortByDesc('updated_at')->take(4)->values()->toArray();

        return view('rotc.dashboard', compact('stats', 'reports'));
    }

    public function platoons()
    {
        // 1. Live unassigned cadets (ROTC component but not enrolled in any section)
        $unassigned = Student::where('component', 'ROTC')
            ->whereNotExists(function ($q) {
                $q->select(\DB::raw(1))
                  ->from('enrollments')
                  ->whereRaw('enrollments.student_id = students.id');
            })
            ->get()
            ->map(function ($s) {
                return (object)[
                    'id' => $s->student_id,
                    'name' => trim($s->last_name . ', ' . $s->first_name, ', '),
                ];
            });
        
        // 2. Live platoons and their student rosters
        $sections = Section::with('instructor')->where('component', 'ROTC')->get();
        $platoons = [];

        foreach ($sections as $sec) {
            $studentsInPlatoon = Student::join('enrollments', 'students.id', '=', 'enrollments.student_id')
                ->where('enrollments.section_id', $sec->id)
                ->select('students.*')
                ->get()
                ->map(function ($s) {
                    return (object)[
                        'id' => $s->student_id,
                        'name' => trim($s->last_name . ', ' . $s->first_name, ', '),
                    ];
                })
                ->toArray();
            
            $platoons[$sec->section_name] = $studentsInPlatoon;
        }



        $instructors = PortalUser::where('role', 'instructor')->get();

        return view('rotc.platoons', compact('unassigned', 'platoons', 'sections', 'instructors'));
    }

    public function storePlatoon(Request $request)
    {
        $data = $request->validate([
            'code'            => 'required|string|max:64|unique:sections,section_name',
            'school_year'     => 'nullable|string|max:32',
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
            'component'     => 'ROTC',
            'school_year'   => $data['school_year'] ?? '2025-2026',
            'room'          => $data['room'] ?? 'TBA',
            'instructor_id' => $instructor?->id,
            'status'        => 'Active',
        ]);

        // Record Audit Log
        $user = auth()->user();
        AuditLog::record(
            'Created Platoon',
            'Sections',
            $section->section_name,
            "Created new ROTC platoon: {$section->section_name} under school year {$section->school_year}",
            'edit',
            [
                'username' => $user ? $user->name : 'ROTC Officer',
                'email'    => $user ? $user->email : 'rotc@dnsc.edu.ph',
                'role'     => 'rotc',
            ]
        );

        return back()->with('success', "Platoon {$section->section_name} successfully created.");
    }

    public function updatePlatoon(Request $request, $id)
    {
        $section = Section::findOrFail($id);

        $data = $request->validate([
            'code'            => 'required|string|max:64|unique:sections,section_name,' . $id,
            'school_year'     => 'nullable|string|max:32',
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
            'component'     => 'ROTC',
            'school_year'   => $data['school_year'] ?? '2025-2026',
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
            'Updated Platoon',
            'Sections',
            $section->section_name,
            "Updated platoon configuration for {$oldName} (now {$section->section_name}).",
            'edit',
            [
                'username' => $user ? $user->name : 'ROTC Officer',
                'email'    => $user ? $user->email : 'rotc@dnsc.edu.ph',
                'role'     => 'rotc',
            ]
        );

        return back()->with('success', "Platoon {$section->section_name} successfully updated.");
    }

    public function deletePlatoon($id)
    {
        $section = Section::find($id);
        if (!$section) {
            $section = Section::where('section_name', $id)->first();
        }

        if (!$section) {
            return redirect()->route('rotc.platoons')->with('error', "Platoon not found or already deleted.");
        }

        $sectionName = $section->section_name;

        // Clean up from class_list_students
        \DB::table('class_list_students')->where('section_name', $sectionName)->delete();

        $section->delete();

        // Record Audit Log
        $user = auth()->user();
        AuditLog::record(
            'Deleted Platoon',
            'Sections',
            $sectionName,
            "Deleted platoon: {$sectionName}",
            'edit',
            [
                'username' => $user ? $user->name : 'ROTC Officer',
                'email'    => $user ? $user->email : 'rotc@dnsc.edu.ph',
                'role'     => 'rotc',
            ]
        );

        return redirect()->route('rotc.platoons')->with('success', "Platoon {$sectionName} successfully deleted.");
    }

    public function rosters()
    {
        // 1. Live rosters listing ROTC cadets and their assigned platoons
        $rosters = Student::leftJoin('enrollments', 'students.id', '=', 'enrollments.student_id')
            ->leftJoin('sections', 'enrollments.section_id', '=', 'sections.id')
            ->where('students.component', 'ROTC')
            ->select('students.*', 'sections.section_name as platoon_name', 'sections.semester as section_semester')
            ->get()
            ->map(function ($s) {
                // Mock rank based on course for UI visual diversity
                $rank = $s->rank;
                if (!$rank) {
                    $rank = 'Cadet';
                    if ($s->course === 'BSIT') {
                        $rank = 'Cpl';
                    } elseif ($s->course === 'BSCS') {
                        $rank = 'Sgt';
                    } else {
                        $rank = 'Pvt';
                    }
                }

                return (object)[
                    'student_id' => $s->student_id,
                    'id' => $s->student_id,
                    'name' => trim($s->last_name . ', ' . $s->first_name, ', '),
                    'rank' => $rank,
                    'platoon' => $s->platoon_name ?? 'Unassigned',
                    'spec' => $s->specialty ?? 'Infantry',
                    'status' => $s->section_semester ?? '1st Semester',
                ];
            });

        $sections = Section::where('component', 'ROTC')->get();

        return view('rotc.rosters', compact('rosters', 'sections'));
    }

    public function storeOfficer(Request $request)
    {
        $data = $request->validate([
            'student_id'   => 'required|string|max:50|unique:students,student_id',
            'rank'         => 'required|string|max:50',
            'name'         => 'required|string|max:255',
            'platoon_name' => 'nullable|string|exists:sections,section_name',
            'specialty'    => 'required|string|max:100',
        ]);

        $parsedName = Student::parseName($data['name']);

        $student = Student::create([
            'student_id'        => $data['student_id'],
            'first_name'        => $parsedName['first_name'] ?: '',
            'last_name'         => $parsedName['last_name'] ?: $data['name'],
            'component'         => 'ROTC',
            'enrollment_status' => 'Active',
            'rank'              => $data['rank'],
            'specialty'         => $data['specialty'],
        ]);

        if (!empty($data['platoon_name'])) {
            $section = Section::where('section_name', $data['platoon_name'])->firstOrFail();
            \DB::table('enrollments')->insert([
                'student_id' => $student->id,
                'section_id' => $section->id,
                'status'     => 'Pending',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Record Audit Log
        $user = auth()->user();
        AuditLog::record(
            'Created Officer',
            'Students',
            $student->student_id,
            "Officer {$user->name} manually created cadet/officer roster entry: {$student->last_name}, {$student->first_name}",
            'edit',
            [
                'username' => $user ? $user->name : 'ROTC Officer',
                'email'    => $user ? $user->email : 'rotc@dnsc.edu.ph',
                'role'     => 'rotc',
            ]
        );

        return back()->with('success', "Officer {$data['name']} successfully added to the roster.");
    }

    public function deleteOfficer($studentId)
    {
        $student = Student::where('student_id', $studentId)->firstOrFail();
        $studentName = trim($student->last_name . ', ' . $student->first_name, ', ');

        // Clean enrollments
        \DB::table('enrollments')->where('student_id', $student->id)->delete();

        // Clean class_list_students
        \DB::table('class_list_students')->where('student_id', $student->student_id)->delete();

        // Soft delete the student record itself
        $student->delete();

        // Record Audit Log
        $user = auth()->user();
        AuditLog::record(
            'Deleted Officer',
            'Students',
            $student->student_id,
            "Officer {$user->name} deleted cadet/officer roster entry: {$studentName}",
            'edit',
            [
                'username' => $user ? $user->name : 'ROTC Officer',
                'email'    => $user ? $user->email : 'rotc@dnsc.edu.ph',
                'role'     => 'rotc',
            ]
        );

        return back()->with('success', "Officer {$studentName} has been successfully deleted.");
    }

    public function assignCadet(Request $request)
    {
        $data = $request->validate([
            'original_student_id'=> 'required|string|exists:students,student_id',
            'student_id'         => 'required|string|max:50',
            'name'               => 'required|string|max:255',
            'section_name'       => 'nullable|string|exists:sections,section_name',
        ]);

        $student = Student::where('student_id', $data['original_student_id'])->firstOrFail();

        // Check if student_id is unique if it is changed
        if ($data['student_id'] !== $data['original_student_id']) {
            $exists = Student::where('student_id', $data['student_id'])->exists();
            if ($exists) {
                return back()->withErrors(['student_id' => 'The Cadet ID has already been taken.']);
            }
        }

        $parsedName = Student::parseName($data['name']);

        // Update student ID and Name
        $student->update([
            'student_id' => $data['student_id'],
            'first_name' => $parsedName['first_name'] ?: '',
            'last_name'  => $parsedName['last_name'] ?: $data['name'],
        ]);

        // Keep class_list_students updated if they exist
        \DB::table('class_list_students')
            ->where('student_id', $data['original_student_id'])
            ->update([
                'student_id' => $data['student_id'],
                'name'       => $data['name'],
            ]);

        // Update/Sync Platoon assignment
        if (!empty($data['section_name'])) {
            $section = Section::where('section_name', $data['section_name'])->firstOrFail();
            $existing = \DB::table('enrollments')
                ->where('student_id', $student->id)
                ->first();
            
            if ($existing) {
                \DB::table('enrollments')
                    ->where('id', $existing->id)
                    ->update([
                        'section_id' => $section->id,
                        'updated_at' => now(),
                    ]);
            } else {
                \DB::table('enrollments')->insert([
                    'student_id' => $student->id,
                    'section_id' => $section->id,
                    'status'     => 'Pending',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } else {
            \DB::table('enrollments')
                ->where('student_id', $student->id)
                ->delete();
        }

        // Record Audit Log
        $user = auth()->user();
        AuditLog::record(
            'Updated Cadet Assignment',
            'Students',
            $student->student_id,
            "Officer {$user->name} updated cadet/officer details for ID: {$student->student_id} (Name: {$data['name']})",
            'edit',
            [
                'username' => $user ? $user->name : 'ROTC Officer',
                'email'    => $user ? $user->email : 'rotc@dnsc.edu.ph',
                'role'     => 'rotc',
            ]
        );

        return back()->with('success', "Cadet details and platoon assignment successfully updated.");
    }

    public function designs()
    {
        // 1. Live training designs (mapped from ROTC Activity Plans, only including draft, pending, revision, & reject statuses)
        $designs = ActivityPlan::with('section')
            ->where(function ($query) {
                $query->whereHas('section', fn($q) => $q->where('component', 'ROTC'))
                      ->orWhere(function ($q) {
                          $q->whereHas('section', fn($s) => $s->where('section_name', 'All Section'))
                            ->whereHas('instructor', fn($u) => $u->where('role', 'rotc')->orWhere('dept', 'ROTC'));
                      });
            })
            ->whereIn('status', ['Draft', 'Pending', 'Revision', 'Revisions', 'Reject', 'Rejected', 'draft', 'pending', 'revision', 'revisions', 'reject', 'rejected'])
            ->get()
            ->map(function ($plan) {
                return (object)[
                    'id' => $plan->id,
                    'title' => $plan->title,
                    'section_id' => $plan->section_id,
                    'objectives' => $plan->objectives,
                    'platoon_name' => $plan->section?->section_name,
                    'phase' => $plan->location ?? 'Campus',
                    'date' => $plan->scheduled_date ? $plan->scheduled_date->format('M d, Y') : 'N/A',
                    'raw_date' => $plan->scheduled_date ? $plan->scheduled_date->format('Y-m-d') : '',
                    'duration' => '3 hrs',
                    'status' => strtolower($plan->status),
                    'description' => $plan->description,
                    'files_attached' => $plan->files_attached,
                    'feedback' => $plan->feedback,
                ];
            });

        $sections = Section::where('component', 'ROTC')->get();

        return view('rotc.designs', compact('designs', 'sections'));
    }

    public function storeDesign(Request $request)
    {
        if ($request->input('section_id') === 'all') {
            $allSection = Section::firstOrCreate([
                'section_name' => 'All Section',
            ], [
                'component'    => 'ROTC',
                'school_year'  => '2025-2026',
                'semester'     => '1st',
                'status'       => 'Active',
            ]);
            $request->merge(['section_id' => $allSection->id]);
        }

        $instructorId = Auth::id();

        $data = $request->validate([
            'title'           => 'required|string|max:255',
            'section_id'      => 'required|exists:sections,id',
            'location'        => 'required|string|max:255', // location acts as Phase / Venue
            'scheduled_date'  => 'required|date',
            'objectives'      => 'nullable|string',
            'activity_design' => 'nullable|file|mimes:pdf|max:20480',
            'status'          => 'required|in:Draft,Pending',
        ]);

        $description = '';
        $filesAttached = 0;
        if ($request->hasFile('activity_design')) {
            $file = $request->file('activity_design');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9\._-]/', '', $file->getClientOriginalName());
            
            $targetDir = public_path('uploads/activity_designs');
            $this->saveUploadedFile($file, $targetDir, $filename);
            $description = 'uploads/activity_designs/' . $filename;
            $filesAttached = 1;
        }

        $plan = ActivityPlan::create([
            'instructor_id'  => $instructorId,
            'title'          => $data['title'],
            'section_id'     => $data['section_id'],
            'location'       => $data['location'],
            'scheduled_date' => $data['scheduled_date'],
            'objectives'     => $data['objectives'],
            'description'    => $description,
            'files_attached' => $filesAttached,
            'status'         => $data['status'],
            'submitted_date' => $data['status'] === 'Pending' ? now() : null,
        ]);

        // Record Audit Log
        $user = Auth::user();
        AuditLog::record(
            'Created ROTC Activity Design',
            'Activity Plans',
            $plan->title,
            "Officer {$user->name} created training design brief '{$plan->title}' for platoon " . $plan->section?->section_name . ". Status: {$plan->status}",
            'submission',
            [
                'username' => $user->name,
                'email'    => $user->email,
                'role'     => 'rotc',
            ]
        );

        return back()->with('success', "Activity Design Brief '{$plan->title}' successfully submitted.");
    }

    public function deleteDesign($id)
    {
        $plan = ActivityPlan::findOrFail($id);
        
        if (!in_array(strtolower($plan->status ?? 'draft'), ['revision', 'revisions', 'rejected', 'reject', 'draft'])) {
            abort(403, 'Unauthorized. Only activity designs in Draft, Revision, or Rejected status can be deleted.');
        }

        $title = $plan->title;
        $plan->delete();

        // Record Audit Log
        $user = Auth::user();
        AuditLog::record(
            'Deleted ROTC Activity Design',
            'Activity Plans',
            $title,
            "Officer {$user->name} deleted activity design brief '{$title}'.",
            'edit',
            [
                'username' => $user->name,
                'email'    => $user->email,
                'role'     => 'rotc',
            ]
        );

        return back()->with('success', "Activity Design Brief successfully deleted.");
    }

    public function calendar(Request $request)
    {
        $year = $request->query('year', now()->year);
        $month = $request->query('month', now()->month);

        $selectedMonth = \Carbon\Carbon::createFromDate($year, $month, 1);
        $currentMonthStart = $selectedMonth->copy()->startOfMonth();
        $currentMonthEnd = $selectedMonth->copy()->endOfMonth();

        // 1. Fetch activities for ROTC & General
        $activitiesDb = Activity::whereBetween('activity_date', [$currentMonthStart, $currentMonthEnd])
            ->where(function ($q) {
                $q->whereIn('component', ['ROTC', 'General', 'ALL', 'All'])
                  ->orWhereNull('component');
            })
            ->get();

        // 2. Fetch approved activity plans for ROTC
        $approvedPlans = ActivityPlan::with('section')
            ->where('status', 'Approved')
            ->where(function ($query) {
                $query->whereHas('section', fn($q) => $q->where('component', 'ROTC'))
                      ->orWhere(function ($q) {
                          $q->whereHas('section', fn($s) => $s->where('section_name', 'All Section'))
                            ->whereHas('instructor', fn($u) => $u->where('role', 'rotc')->orWhere('dept', 'ROTC'));
                      });
            })
            ->whereBetween('scheduled_date', [$currentMonthStart, $currentMonthEnd])
            ->get();

        // 3. Fetch announcements for ROTC & All
        $announcementsDb = \App\Models\Announcement::whereIn('target_role', ['All', 'ROTC', 'ROTC Officer', 'Instructors', 'Instructor'])
            ->where(function ($q) use ($currentMonthStart, $currentMonthEnd) {
                $q->whereBetween('scheduled_date', [$currentMonthStart, $currentMonthEnd])
                  ->orWhere(function ($q2) use ($currentMonthStart, $currentMonthEnd) {
                      $q2->whereNull('scheduled_date')
                         ->whereBetween('created_at', [$currentMonthStart, $currentMonthEnd]);
                  });
            })
            ->get();

        // 4. Merge them into a unified list
        $activities = collect();

        foreach ($activitiesDb as $act) {
            $activities->push((object)[
                'title' => $act->title,
                'component' => $act->component ?? 'ROTC',
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
                    'component' => 'ROTC',
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
                'component' => $ann->component ?? 'ROTC',
                'activity_date' => $date,
                'location' => $ann->source ?? 'NSTP Office',
                'description' => $ann->content,
                'is_plan' => false,
                'is_announcement' => true,
                'target_role' => $ann->target_role ?? 'All',
                'is_pinned' => (bool)$ann->is_pinned,
            ]);
        }

        // 5. Fetch upcoming activities for ROTC sidebar
        $upcomingDb = Activity::where('activity_date', '>=', now()->startOfDay())
            ->where(function ($q) {
                $q->whereIn('component', ['ROTC', 'General', 'ALL', 'All'])
                  ->orWhereNull('component');
            })
            ->orderBy('activity_date', 'asc')
            ->limit(5)
            ->get();

        $upcomingPlans = ActivityPlan::with('section')
            ->where('status', 'Approved')
            ->where(function ($query) {
                $query->whereHas('section', fn($q) => $q->where('component', 'ROTC'))
                      ->orWhere(function ($q) {
                          $q->whereHas('section', fn($s) => $s->where('section_name', 'All Section'))
                            ->whereHas('instructor', fn($u) => $u->where('role', 'rotc')->orWhere('dept', 'ROTC'));
                      });
            })
            ->where('scheduled_date', '>=', now()->startOfDay())
            ->orderBy('scheduled_date', 'asc')
            ->limit(5)
            ->get();

        $upcomingAnn = \App\Models\Announcement::whereIn('target_role', ['All', 'ROTC', 'ROTC Officer', 'Instructors', 'Instructor'])
            ->where(function ($q) {
                $q->where('scheduled_date', '>=', now()->startOfDay())
                  ->orWhere(function ($q2) {
                      $q2->whereNull('scheduled_date')
                         ->where('created_at', '>=', now()->startOfDay());
                  });
            })->limit(5)->get();

        $rawUpcoming = collect();

        foreach ($upcomingDb as $act) {
            $rawUpcoming->push((object)[
                'title' => $act->title,
                'component' => $act->component ?? 'ROTC',
                'activity_date' => $act->activity_date,
                'location' => $act->location ?? 'TBA',
                'description' => $act->description ?? '',
                'is_plan' => false,
                'is_announcement' => false,
                'color' => 'bg-emerald-500',
            ]);
        }

        foreach ($upcomingPlans as $plan) {
            $exists = $rawUpcoming->contains(function ($existing) use ($plan) {
                return strtolower($existing->title) === strtolower($plan->title) &&
                       $existing->activity_date->toDateString() === $plan->scheduled_date->toDateString();
            });

            if (!$exists) {
                $rawUpcoming->push((object)[
                    'title' => $plan->title,
                    'component' => 'ROTC',
                    'activity_date' => $plan->scheduled_date,
                    'location' => $plan->location ?? 'TBA',
                    'description' => $plan->objectives ?? $plan->description ?? '',
                    'is_plan' => true,
                    'is_announcement' => false,
                    'color' => 'bg-emerald-500',
                ]);
            }
        }

        foreach ($upcomingAnn as $ann) {
            $date = $ann->scheduled_date ? $ann->scheduled_date : $ann->created_at;
            $rawUpcoming->push((object)[
                'title' => $ann->title,
                'component' => $ann->component ?? 'ROTC',
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

        $upcomingActivities = $rawUpcoming->sortBy('activity_date')->take(5)->values();

        // Precompute navigation months
        $prevMonth = $selectedMonth->copy()->subMonth();
        $nextMonth = $selectedMonth->copy()->addMonth();

        return view('rotc.calendar', compact(
            'activities', 'upcomingActivities', 'selectedMonth', 'prevMonth', 'nextMonth'
        ));
    }

    public function reports()
    {
        // 1. Live reports (mapped from ROTC Accomplishment Reports)
        $reports = AccomplishmentReport::with('section')
            ->where(function ($query) {
                $query->whereHas('section', fn($q) => $q->where('component', 'ROTC'))
                      ->orWhere(function ($q) {
                          $q->whereHas('section', fn($s) => $s->where('section_name', 'All Section'))
                            ->whereHas('instructor', fn($u) => $u->where('role', 'rotc')->orWhere('dept', 'ROTC'));
                      });
            })
            ->get()
            ->map(function ($report) {
                // Resolve activity plan dynamically by title and section
                $plan = ActivityPlan::where('title', $report->title)
                    ->where('section_id', $report->section_id)
                    ->where('status', 'Approved')
                    ->first() ?: ActivityPlan::where('title', $report->title)->where('status', 'Approved')->first();

                return (object)[
                    'id' => $report->id,
                    'title' => $report->title,
                    'due' => $report->completed_date ? $report->completed_date->format('M d, Y') : 'N/A',
                    'status' => strtolower($report->status === 'Reviewed' ? 'approved' : ($report->status === 'Revision' ? 'revisions' : $report->status)),
                    'progress' => $report->status === 'Reviewed' ? 100 : ($report->status === 'Pending' ? 90 : 50),
                    'color' => $report->status === 'Reviewed' ? 'emerald' : ($report->status === 'Pending' ? 'indigo' : ($report->status === 'Revision' ? 'rose' : 'slate')),
                    'report_file_path' => $report->report_file_path,
                    'files_attached' => $report->files_attached,
                    'activity_plan_id' => $plan ? $plan->id : '',
                    'section_id' => $report->section_id,
                    'platoon_name' => $report->section?->section_name,
                    'raw_completed_date' => $report->completed_date ? $report->completed_date->format('Y-m-d') : '',
                    'accomplishments' => $report->accomplishments,
                    'feedback' => $report->feedback,
                    'raw_status' => $report->status,
                ];
            });

        $sections = Section::where('component', 'ROTC')->get();
        
        $approvedPlans = ActivityPlan::where(function ($query) {
                $query->whereHas('section', fn($q) => $q->where('component', 'ROTC'))
                      ->orWhere(function ($q) {
                          $q->whereHas('section', fn($s) => $s->where('section_name', 'All Section'))
                            ->whereHas('instructor', fn($u) => $u->where('role', 'rotc')->orWhere('dept', 'ROTC'));
                      });
            })
            ->where('status', 'Approved')
            ->get();

        return view('rotc.reports', compact('reports', 'sections', 'approvedPlans'));
    }

    public function updateReport(Request $request, $id)
    {
        $report = AccomplishmentReport::findOrFail($id);

        // Ensure this report belongs to ROTC component
        $isRotc = ($report->section && $report->section->component === 'ROTC') ||
                  ($report->section && $report->section->section_name === 'All Section');
        
        if (!$isRotc) {
            abort(403, 'Unauthorized. This report does not belong to the ROTC component.');
        }

        if (!in_array(strtolower($report->status ?? 'draft'), ['revision', 'revisions', 'rejected', 'reject', 'draft'])) {
            abort(403, 'Unauthorized. Only accomplishment reports in Draft, Revision, or Rejected status can be modified.');
        }

        if ($request->input('section_id') === 'all') {
            $allSection = Section::firstOrCreate([
                'section_name' => 'All Section',
            ], [
                'component'    => 'ROTC',
                'school_year'  => '2025-2026',
                'semester'     => '1st',
                'status'       => 'Active',
            ]);
            $request->merge(['section_id' => $allSection->id]);
        }

        $data = $request->validate([
            'activity_plan_id' => 'required|exists:activity_plans,id',
            'section_id'       => 'required|exists:sections,id',
            'completed_date'   => 'required|date',
            'accomplishments'  => 'nullable|string',
            'report_file'      => 'nullable|file|mimes:pdf,docx,doc,png,jpg,jpeg|max:20480',
            'status'           => 'required|in:Draft,Pending',
        ]);

        // Fetch approved plan to update title & location
        $plan = ActivityPlan::where('status', 'Approved')->findOrFail($data['activity_plan_id']);

        $oldTitle = $report->title;

        $updateData = [
            'activity_plan_id' => $data['activity_plan_id'],
            'section_id'       => $data['section_id'],
            'title'            => $plan->title,
            'location'         => $plan->location,
            'completed_date'   => $data['completed_date'],
            'accomplishments'  => $data['accomplishments'],
            'status'           => $data['status'] === 'Pending' ? 'Pending' : 'Draft',
        ];

        if ($data['status'] === 'Pending') {
            $updateData['feedback'] = null; // Clear previous feedback note on resubmission
        }

        if ($request->hasFile('report_file')) {
            $file = $request->file('report_file');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9\._-]/', '', $file->getClientOriginalName());
            
            $targetDir = public_path('uploads/accomplishment_reports');
            $this->saveUploadedFile($file, $targetDir, $filename);
            $updateData['report_file_path'] = 'uploads/accomplishment_reports/' . $filename;
            $updateData['files_attached'] = 1;
        }

        if ($data['status'] === 'Pending' && in_array(strtolower($report->status ?? 'draft'), ['draft', 'revision', 'revisions', 'rejected', 'reject'])) {
            $updateData['submitted_date'] = now();
        }

        $report->update($updateData);

        // Record Audit Log
        $user = Auth::user();
        AuditLog::record(
            'Updated ROTC Accomplishment Report',
            'Accomplishment Reports',
            $report->title,
            "Officer {$user->name} updated accomplishment report '{$oldTitle}' (now '{$report->title}'). Status: {$report->status}",
            'edit',
            [
                'username' => $user->name,
                'email'    => $user->email,
                'role'     => 'rotc',
            ]
        );

        return back()->with('success', "Accomplishment Report '{$report->title}' successfully updated.");
    }

    public function storeReport(Request $request)
    {
        if ($request->input('section_id') === 'all') {
            $allSection = Section::firstOrCreate([
                'section_name' => 'All Section',
            ], [
                'component'    => 'ROTC',
                'school_year'  => '2025-2026',
                'semester'     => '1st',
                'status'       => 'Active',
            ]);
            $request->merge(['section_id' => $allSection->id]);
        }

        $instructorId = Auth::id();

        $data = $request->validate([
            'activity_plan_id' => 'required|exists:activity_plans,id',
            'section_id'       => 'required|exists:sections,id',
            'completed_date'   => 'required|date',
            'accomplishments'  => 'nullable|string',
            'report_file'      => 'nullable|file|mimes:pdf,docx,doc,png,jpg,jpeg|max:20480',
            'status'           => 'required|in:Draft,Pending',
        ]);

        $plan = ActivityPlan::findOrFail($data['activity_plan_id']);

        $reportFile = null;
        $filesAttached = 0;
        if ($request->hasFile('report_file')) {
            $file = $request->file('report_file');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9\._-]/', '', $file->getClientOriginalName());
            
            $targetDir = public_path('uploads/accomplishment_reports');
            $this->saveUploadedFile($file, $targetDir, $filename);
            $reportFile = 'uploads/accomplishment_reports/' . $filename;
            $filesAttached = 1;
        }

        $report = AccomplishmentReport::create([
            'instructor_id'      => $instructorId,
            'activity_plan_id'   => $data['activity_plan_id'],
            'section_id'         => $data['section_id'],
            'title'              => $plan->title,
            'location'           => $plan->location,
            'completed_date'     => $data['completed_date'],
            'accomplishments'    => $data['accomplishments'],
            'participants_count' => 0,
            'report_file_path'   => $reportFile,
            'files_attached'     => $filesAttached,
            'status'             => $data['status'] === 'Pending' ? 'Pending' : 'Draft',
            'submitted_date'     => $data['status'] === 'Pending' ? now() : null,
        ]);

        // Record Audit Log
        $user = Auth::user();
        AuditLog::record(
            'Created ROTC Accomplishment Report',
            'Accomplishment Reports',
            $report->title,
            "Officer {$user->name} created accomplishment report '{$report->title}' for platoon " . $report->section?->section_name . ". Status: {$report->status}",
            'submission',
            [
                'username' => $user->name,
                'email'    => $user->email,
                'role'     => 'rotc',
            ]
        );

        return back()->with('success', "Accomplishment Report '{$report->title}' successfully submitted.");
    }

    public function deleteReport($id)
    {
        $report = AccomplishmentReport::findOrFail($id);
        
        if (!in_array(strtolower($report->status ?? 'draft'), ['revision', 'revisions', 'rejected', 'reject', 'draft'])) {
            abort(403, 'Unauthorized. Only accomplishment reports in Draft, Revision, or Rejected status can be deleted.');
        }

        $title = $report->title;
        $report->delete();

        // Record Audit Log
        $user = Auth::user();
        AuditLog::record(
            'Deleted ROTC Accomplishment Report',
            'Accomplishment Reports',
            $title,
            "Officer {$user->name} deleted accomplishment report '{$title}'.",
            'edit',
            [
                'username' => $user->name,
                'email'    => $user->email,
                'role'     => 'rotc',
            ]
        );

        return back()->with('success', "Accomplishment Report successfully deleted.");
    }

    public function updateDesign(Request $request, $id)
    {
        $plan = ActivityPlan::findOrFail($id);

        // Ensure this design belongs to ROTC component
        $isRotc = ($plan->section && $plan->section->component === 'ROTC') ||
                  ($plan->section && $plan->section->section_name === 'All Section');
        
        if (!$isRotc) {
            abort(403, 'Unauthorized. This design does not belong to the ROTC component.');
        }

        if (!in_array(strtolower($plan->status ?? 'draft'), ['revision', 'revisions', 'rejected', 'reject', 'draft'])) {
            abort(403, 'Unauthorized. Only activity designs in Draft, Revision, or Rejected status can be modified.');
        }

        if ($request->input('section_id') === 'all') {
            $allSection = Section::firstOrCreate([
                'section_name' => 'All Section',
            ], [
                'component'    => 'ROTC',
                'school_year'  => '2025-2026',
                'semester'     => '1st',
                'status'       => 'Active',
            ]);
            $request->merge(['section_id' => $allSection->id]);
        }

        $data = $request->validate([
            'title'           => 'required|string|max:255',
            'section_id'      => 'required|exists:sections,id',
            'location'        => 'required|string|max:255', // Phase / Venue
            'scheduled_date'  => 'required|date',
            'objectives'      => 'nullable|string',
            'activity_design' => 'nullable|file|mimes:pdf|max:20480',
            'status'          => 'required|in:Draft,Pending',
        ]);

        $oldTitle = $plan->title;
        
        $updateData = [
            'title'          => $data['title'],
            'section_id'     => $data['section_id'],
            'location'       => $data['location'],
            'scheduled_date' => $data['scheduled_date'],
            'objectives'     => $data['objectives'],
            'status'         => $data['status'],
        ];

        if ($data['status'] === 'Pending') {
            $updateData['feedback'] = null; // Clear previous feedback note on resubmission
        }

        if ($request->hasFile('activity_design')) {
            $file = $request->file('activity_design');
            $filename = time() . '_' . preg_replace('/[^A-Za-z0-9\._-]/', '', $file->getClientOriginalName());
            
            $targetDir = public_path('uploads/activity_designs');
            $this->saveUploadedFile($file, $targetDir, $filename);
            $updateData['description'] = 'uploads/activity_designs/' . $filename;
            $updateData['files_attached'] = 1;
        }

        if ($data['status'] === 'Pending' && in_array(strtolower($plan->status ?? 'draft'), ['draft', 'revision', 'revisions', 'rejected', 'reject'])) {
            $updateData['submitted_date'] = now();
        }

        $plan->update($updateData);

        // Record Audit Log
        $user = Auth::user();
        AuditLog::record(
            'Updated ROTC Activity Design',
            'Activity Plans',
            $plan->title,
            "Officer {$user->name} updated training design brief '{$oldTitle}' (now '{$plan->title}'). Status: {$plan->status}",
            'edit',
            [
                'username' => $user->name,
                'email'    => $user->email,
                'role'     => 'rotc',
            ]
        );

        return back()->with('success', "Activity Design Brief '{$plan->title}' successfully updated.");
    }

    private function saveUploadedFile($file, $targetDir, $filename)
    {
        $targetDir = str_replace(['/', '\\'], DIRECTORY_SEPARATOR, $targetDir);
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        $targetPath = $targetDir . DIRECTORY_SEPARATOR . $filename;
        if (!@move_uploaded_file($file->getRealPath(), $targetPath)) {
            copy($file->getRealPath(), $targetPath);
        }
    }
}
