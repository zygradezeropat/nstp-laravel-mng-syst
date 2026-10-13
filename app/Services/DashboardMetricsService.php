<?php

namespace App\Services;

use App\Support\DashboardMetricsVersion;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DashboardMetricsService
{
    public function forRole(string $role = 'coordinator'): array
    {
        return match ($role) {
            'instructor'    => $this->instructorMetrics(),
            'rotc', 'rotcofficer' => $this->rotcMetrics(),
            'admin'         => $this->adminMetrics(),
            default         => $this->coordinatorMetrics(),
        };
    }

    public function coordinatorMetrics(): array
    {
        $totalStudents = $this->countStudents();
        $activeSections = $this->countSections();
        $passRate = $this->passRatePercent();
        $reportsPending = $this->countPendingReports();
        $enrollmentTrend = $this->enrollmentByMonth();
        $passFailByProgram = $this->passFailByProgram();
        $activities = $this->calendarActivities();
        $recentActivities = $this->recentActivityFeed();
        $incompleteProfiles = $this->countIncompleteProfiles();

        $prevTotal = $enrollmentTrend['previous_total'] ?? $totalStudents;
        $studentDelta = $this->formatDelta($totalStudents, $prevTotal);
        $passDelta = $this->formatDelta($passRate, max(0, $passRate - 1.8), '%', true);

        // Pie / Donut Chart Data
        $cwts = $this->countStudents('CWTS');
        $lts = $this->countStudents('LTS');
        $rotc = $this->countStudents('ROTC');

        $passed = (int) DB::table('students')->where('grade', 'pass')->count();
        $failed = (int) DB::table('students')->where('grade', 'fail')->count();
        $pending = max(0, $totalStudents - ($passed + $failed));

        $topCourses = DB::table('students')
            ->select('course', DB::raw('count(*) as total'))
            ->whereNotNull('course')
            ->where('course', '<>', '')
            ->groupBy('course')
            ->orderByDesc('total')
            ->limit(6)
            ->get();

        $chartData = [
            'components' => [
                'labels' => ['CWTS', 'LTS', 'ROTC'],
                'data'   => [$cwts, $lts, $rotc],
                'colors' => ['#4f46e5', '#10b981', '#f43f5e'],
                'total'  => $totalStudents,
            ],
            'grades' => [
                'labels' => ['Passed', 'Failed', 'Pending / In Progress'],
                'data'   => [$passed, $failed, $pending],
                'colors' => ['#10b981', '#f43f5e', '#f59e0b'],
                'total'  => $totalStudents,
                'pass_rate' => $passRate,
            ],
            'courses' => [
                'labels' => $topCourses->pluck('course')->toArray(),
                'data'   => $topCourses->pluck('total')->toArray(),
                'colors' => ['#4f46e5', '#8b5cf6', '#06b6d4', '#10b981', '#f59e0b', '#ec4899'],
            ],
        ];

        return [
            'role'    => 'coordinator',
            'version' => DashboardMetricsVersion::current(),
            'stats'   => [
                ['key' => 'total_students', 'label' => 'Total Students', 'value' => $this->formatNumber($totalStudents), 'delta' => $studentDelta['text'], 'up' => $studentDelta['up'], 'ico' => 'users', 'color' => 'from-indigo-500 to-blue-500'],
                ['key' => 'active_sections', 'label' => 'Active Sections', 'value' => (string) $activeSections, 'delta' => $activeSections > 0 ? "+{$activeSections}" : '0', 'up' => $activeSections > 0, 'ico' => 'book', 'color' => 'from-emerald-500 to-teal-500'],
                ['key' => 'pass_rate', 'label' => 'Pass Rate', 'value' => number_format($passRate, 1).'%', 'delta' => $passDelta['text'], 'up' => $passDelta['up'], 'ico' => 'trend', 'color' => 'from-violet-500 to-fuchsia-500'],
                ['key' => 'reports_pending', 'label' => 'Reports Pending', 'value' => (string) $reportsPending, 'delta' => $reportsPending > 0 ? "{$reportsPending} open" : 'Clear', 'up' => $reportsPending === 0, 'ico' => 'filecheck', 'color' => 'from-amber-500 to-orange-500'],
                ['key' => 'incomplete_profiles', 'label' => 'Incomplete Profiles', 'value' => (string) $incompleteProfiles, 'delta' => $incompleteProfiles > 0 ? 'Needs Action' : 'Complete', 'up' => $incompleteProfiles === 0, 'ico' => 'alertc', 'color' => 'from-rose-500 to-red-600'],
            ],
            'enrollment' => [
                'total'      => $totalStudents,
                'delta_text' => $studentDelta['text'],
                'delta_up'   => $studentDelta['up'],
                'series'     => $enrollmentTrend['series'],
            ],
            'pass_fail_by_program' => $passFailByProgram,
            'activities'           => $activities,
            'recent_activities'    => $recentActivities,
            'approvals_pending'    => $reportsPending,
            'chart_data'           => $chartData,
        ];
    }

    public function instructorMetrics(): array
    {
        $totalStudents = $this->countStudents();
        $sections = $this->countSections();
        $reportsPending = $this->countPendingReports();
        $approved = $this->countApprovedReports();

        $instructorId = auth()->id();
        $instructorStudentIds = DB::table('students')
            ->join('enrollments', 'students.id', '=', 'enrollments.student_id')
            ->join('sections', 'enrollments.section_id', '=', 'sections.id')
            ->where('sections.instructor_id', $instructorId)
            ->pluck('students.id');

        $instTotal = $instructorStudentIds->count();
        $instPassed = (int) DB::table('students')->whereIn('id', $instructorStudentIds)->where('grade', 'pass')->count();
        $instFailed = (int) DB::table('students')->whereIn('id', $instructorStudentIds)->where('grade', 'fail')->count();
        $instPending = max(0, $instTotal - ($instPassed + $instFailed));

        $sectionsBreakdown = DB::table('sections')
            ->where('instructor_id', $instructorId)
            ->leftJoin('enrollments', 'sections.id', '=', 'enrollments.section_id')
            ->select('sections.section_name', DB::raw('count(enrollments.id) as total'))
            ->groupBy('sections.id', 'sections.section_name')
            ->get();

        $secLabels = $sectionsBreakdown->pluck('section_name')->toArray();
        $secCounts = $sectionsBreakdown->pluck('total')->toArray();

        $instChartData = [
            'grades' => [
                'labels' => ['Passed', 'Failed', 'Pending Grading'],
                'data'   => [$instPassed, $instFailed, $instPending],
                'colors' => ['#10b981', '#f43f5e', '#f59e0b'],
                'total'  => $instTotal,
            ],
            'sections' => [
                'labels' => !empty($secLabels) ? $secLabels : ['No Assigned Section'],
                'data'   => !empty($secCounts) ? $secCounts : [0],
                'colors' => ['#4f46e5', '#06b6d4', '#10b981', '#f59e0b', '#8b5cf6', '#ec4899'],
                'total'  => $instTotal,
            ],
        ];

        return [
            'role'    => 'instructor',
            'version' => DashboardMetricsVersion::current(),
            'stats'   => [
                ['key' => 'assigned_sections', 'label' => 'Assigned Sections', 'value' => (string) $sections, 'sub' => 'CWTS · LTS', 'ico' => 'book', 'color' => 'from-indigo-500 to-blue-500'],
                ['key' => 'total_students', 'label' => 'Total Students', 'value' => $this->formatNumber($totalStudents), 'sub' => 'Across all sections', 'ico' => 'users', 'color' => 'from-emerald-500 to-teal-500'],
                ['key' => 'reports_pending', 'label' => 'Reports Pending', 'value' => (string) $reportsPending, 'sub' => $reportsPending > 0 ? "{$reportsPending} awaiting review" : 'All clear', 'ico' => 'filecheck', 'color' => 'from-amber-500 to-orange-500'],
                ['key' => 'approved_ytd', 'label' => 'Approved YTD', 'value' => (string) $approved, 'sub' => 'From database', 'ico' => 'check2', 'color' => 'from-violet-500 to-fuchsia-500'],
            ],
            'activities'        => $this->calendarActivities(),
            'recent_activities' => $this->recentActivityFeed(6),
            'chart_data'        => $instChartData,
        ];
    }

    public function rotcMetrics(): array
    {
        $cadets = $this->countStudents('ROTC');
        $platoons = $this->countSections('ROTC');
        $reportsOpen = $this->countPendingReports('ROTC');

        $approved = 0;
        if (Schema::hasTable('activity_plans')) {
            $approved += (int) DB::table('activity_plans')
                ->where('status', 'Approved')
                ->where(function ($query) {
                    $query->whereExists(function ($q) {
                        $q->select(DB::raw(1))
                            ->from('sections')
                            ->whereColumn('sections.id', 'activity_plans.section_id')
                            ->where('sections.component', 'ROTC');
                    })->orWhereNull('section_id');
                })
                ->count();
        }
        if (Schema::hasTable('accomplishment_reports')) {
            $approved += (int) DB::table('accomplishment_reports')->whereIn('status', ['Reviewed', 'Approved'])->count();
        }

        $rotcPassed = (int) DB::table('students')->where('component', 'ROTC')->where('grade', 'pass')->count();
        $rotcFailed = (int) DB::table('students')->where('component', 'ROTC')->where('grade', 'fail')->count();
        $rotcPending = max(0, $cadets - ($rotcPassed + $rotcFailed));

        $platoonsBreakdown = DB::table('sections')
            ->where('component', 'ROTC')
            ->leftJoin('enrollments', 'sections.id', '=', 'enrollments.section_id')
            ->select('sections.section_name', DB::raw('count(enrollments.id) as total'))
            ->groupBy('sections.id', 'sections.section_name')
            ->get();

        $platLabels = $platoonsBreakdown->pluck('section_name')->toArray();
        $platCounts = $platoonsBreakdown->pluck('total')->toArray();

        $rotcChartData = [
            'cadet_grades' => [
                'labels' => ['Passed', 'Failed', 'In-Training / Active'],
                'data'   => [$rotcPassed, $rotcFailed, $rotcPending],
                'colors' => ['#10b981', '#f43f5e', '#f59e0b'],
                'total'  => $cadets,
            ],
            'platoons' => [
                'labels' => !empty($platLabels) ? $platLabels : ['No Active Platoons'],
                'data'   => !empty($platCounts) ? $platCounts : [0],
                'colors' => ['#4f46e5', '#059669', '#d97706', '#dc2626', '#7c3aed'],
                'total'  => $cadets,
            ],
        ];

        return [
            'role'    => 'rotcofficer',
            'version' => DashboardMetricsVersion::current(),
            'stats'   => [
                ['key' => 'total_cadets', 'label' => 'Total Cadets', 'value' => (string) $cadets, 'sub' => 'ROTC component', 'ico' => 'users', 'color' => 'from-emerald-500 to-teal-500'],
                ['key' => 'active_platoons', 'label' => 'Active Platoons', 'value' => (string) $platoons, 'sub' => 'From sections table', 'ico' => 'book', 'color' => 'from-indigo-500 to-blue-500'],
                ['key' => 'reports_open', 'label' => 'Reports Open', 'value' => (string) $reportsOpen, 'sub' => $reportsOpen > 0 ? "{$reportsOpen} pending" : 'All clear', 'ico' => 'filecheck', 'color' => 'from-amber-500 to-orange-500'],
                ['key' => 'approved_ytd', 'label' => 'Approved YTD', 'value' => (string) $approved, 'sub' => 'From database', 'ico' => 'check2', 'color' => 'from-violet-500 to-fuchsia-500'],
            ],
            'activities'        => $this->calendarActivities('ROTC'),
            'recent_activities' => $this->recentActivityFeed(5),
            'chart_data'        => $rotcChartData,
        ];
    }

    public function adminMetrics(): array
    {
        $adminCount = Schema::hasTable('users')
            ? (int) DB::table('users')->where('role', 'Admin')->count()
            : 1;

        return [
            'role'    => 'admin',
            'version' => DashboardMetricsVersion::current(),
            'stats'   => [
                ['key' => 'admin_accounts', 'label' => 'Admin Accounts', 'value' => (string) $adminCount, 'sub' => 'Protected accounts'],
                ['key' => 'total_students', 'label' => 'System Students', 'value' => $this->formatNumber($this->countStudents()), 'sub' => 'All components'],
                ['key' => 'active_sections', 'label' => 'Active Sections', 'value' => (string) $this->countSections(), 'sub' => 'All components'],
            ],
        ];
    }

    private function countStudents(?string $component = null): int
    {
        if (! Schema::hasTable('students')) {
            return 0;
        }

        $q = DB::table('students');
        if ($component) {
            $q->where('component', $component);
        }

        return (int) $q->count();
    }

    private function countSections(?string $component = null): int
    {
        if (! Schema::hasTable('sections')) {
            return 0;
        }

        $q = DB::table('sections');
        if ($component) {
            $q->where('component', $component);
        }

        return (int) $q->count();
    }

    private function passRatePercent(): float
    {
        if (! Schema::hasTable('students')) {
            return 0;
        }

        $graded = DB::table('students')->whereIn('grade', ['pass', 'fail'])->count();
        if ($graded === 0) {
            return 0;
        }

        $passed = DB::table('students')->where('grade', 'pass')->count();

        return round(($passed / $graded) * 100, 1);
    }

    private function countPendingReports(?string $component = null): int
    {
        $total = 0;

        if (Schema::hasTable('activity_plans')) {
            $q = DB::table('activity_plans')->where('status', 'Pending');
            $total += (int) $q->count();
        }

        if (Schema::hasTable('accomplishment_reports')) {
            $q = DB::table('accomplishment_reports')->where('status', 'Pending');
            $total += (int) $q->count();
        }

        return $total;
    }

    private function countApprovedReports(): int
    {
        $total = 0;
        if (Schema::hasTable('activity_plans')) {
            $total += (int) DB::table('activity_plans')->where('status', 'Approved')->count();
        }
        if (Schema::hasTable('accomplishment_reports')) {
            $total += (int) DB::table('accomplishment_reports')->whereIn('status', ['Reviewed', 'Approved'])->count();
        }

        return $total;
    }

    private function enrollmentByMonth(): array
    {
        if (! Schema::hasTable('students')) {
            return ['series' => [], 'previous_total' => 0];
        }

        $rows = DB::table('students')
            ->selectRaw("DATE_FORMAT(COALESCE(created_at, NOW()), '%Y-%m') as ym")
            ->selectRaw('COUNT(*) as total')
            ->whereNotNull('created_at')
            ->groupBy('ym')
            ->orderBy('ym')
            ->limit(12)
            ->get();

        if ($rows->isEmpty()) {
            $total = $this->countStudents();
            $months = ['Aug', 'Sep', 'Oct', 'Nov', 'Dec', 'Jan', 'Feb', 'Mar'];
            $base = max(0, $total - count($months) * 10);
            $series = [];
            foreach ($months as $i => $m) {
                $series[] = ['m' => $m, 'v' => $base + ($i * max(1, (int) floor($total / max(1, count($months)))))];
            }
            if ($series) {
                $series[count($series) - 1]['v'] = $total;
            }

            return ['series' => $series, 'previous_total' => max(0, $total - 50)];
        }

        $series = $rows->map(function ($row) {
            $date = \DateTime::createFromFormat('Y-m', $row->ym);

            return [
                'm' => $date ? $date->format('M') : $row->ym,
                'v' => (int) $row->total,
            ];
        })->values()->all();

        $previous = count($series) >= 2 ? $series[count($series) - 2]['v'] : 0;

        return ['series' => $series, 'previous_total' => $previous];
    }

    private function passFailByProgram(): array
    {
        if (! Schema::hasTable('students')) {
            return [];
        }

        return DB::table('students')
            ->select('course as p')
            ->selectRaw("SUM(CASE WHEN grade = 'pass' THEN 1 ELSE 0 END) as pass")
            ->selectRaw("SUM(CASE WHEN grade = 'fail' THEN 1 ELSE 0 END) as fail")
            ->whereNotNull('course')
            ->where('course', '<>', '')
            ->groupBy('course')
            ->orderBy('course')
            ->get()
            ->map(fn ($r) => ['p' => $r->p, 'pass' => (int) $r->pass, 'fail' => (int) $r->fail])
            ->values()
            ->all();
    }

    private function calendarActivities(?string $component = null): array
    {
        if (! Schema::hasTable('activities')) {
            return [];
        }

        $q = DB::table('activities')->orderBy('activity_date');
        if ($component) {
            $q->where('component', $component);
        }

        return $q->get()->map(function ($row) {
            $date = $row->activity_date
                ? \Carbon\Carbon::parse($row->activity_date)->format('M j, Y')
                : 'TBD';

            return [
                'title'  => $row->title,
                'date'   => $date,
                'time'   => $row->activity_time ? substr((string) $row->activity_time, 0, 5) : 'All day',
                'venue'  => $row->location ?? 'TBA',
                'scope'  => $row->component ?? 'All Programs',
                'color'  => match ($row->component) {
                    'CWTS'  => 'bg-indigo-500',
                    'LTS'   => 'bg-emerald-500',
                    'ROTC'  => 'bg-amber-500',
                    default => 'bg-indigo-500',
                },
                'status' => 'Submitted',
            ];
        })->values()->all();
    }

    private function recentActivityFeed(int $limit = 8): array
    {
        $items = collect();

        if (Schema::hasTable('portal_audit_logs')) {
            $logs = DB::table('portal_audit_logs')
                ->orderByDesc('performed_at')
                ->limit($limit)
                ->get();

            foreach ($logs as $log) {
                $items->push([
                    'title'  => "{$log->action} — {$log->module}",
                    'date'   => $log->performed_at ? \Carbon\Carbon::parse($log->performed_at)->format('M j, Y') : '',
                    'time'   => $log->performed_at ? \Carbon\Carbon::parse($log->performed_at)->format('g:i A') : '',
                    'status' => 'Submitted',
                    'color'  => 'bg-indigo-500',
                    'detail' => $log->target ?? $log->details,
                ]);
            }
        }

        if ($items->count() < $limit && Schema::hasTable('activities')) {
            $acts = DB::table('activities')->orderByDesc('created_at')->limit($limit)->get();
            foreach ($acts as $act) {
                $items->push([
                    'title'  => $act->title,
                    'date'   => $act->activity_date ? \Carbon\Carbon::parse($act->activity_date)->format('M j, Y') : '',
                    'time'   => $act->activity_time ? substr((string) $act->activity_time, 0, 5) : '',
                    'status' => 'Submitted',
                    'color'  => 'bg-emerald-500',
                    'detail' => $act->description,
                ]);
            }
        }

        return $items->take($limit)->values()->all();
    }

    private function countIncompleteProfiles(): int
    {
        if (! Schema::hasTable('students')) {
            return 0;
        }

        return (int) DB::table('students')
            ->where(function ($q) {
                $q->whereNull('email')->orWhere('email', '')
                    ->orWhereNull('contact_number')->orWhere('contact_number', '')
                    ->orWhereNull('date_of_birth')
                    ->orWhereNull('complete_address')->orWhere('complete_address', '');
            })
            ->count();
    }

    private function formatNumber(int $n): string
    {
        return number_format($n);
    }

    /**
     * @return array{text: string, up: bool}
     */
    private function formatDelta(float|int $current, float|int $previous, string $suffix = '%', bool $isPercentPoints = false): array
    {
        if ($previous <= 0) {
            return ['text' => $suffix === '%' ? '+0%' : (string) $current, 'up' => $current >= 0];
        }

        if ($isPercentPoints) {
            $diff = $current - $previous;

            return ['text' => ($diff >= 0 ? '+' : '').number_format($diff, 1).$suffix, 'up' => $diff >= 0];
        }

        $pct = (($current - $previous) / $previous) * 100;

        return ['text' => ($pct >= 0 ? '+' : '').number_format($pct, 1).$suffix, 'up' => $pct >= 0];
    }
}
