@extends('layouts.rotc')

@section('title', 'Overview - ROTC Command')

@section('content')

<x-page-header title="Stand-to, {{ auth()->user()->name ?? 'Officer' }}" subtitle="Command overview and operational readiness at a glance">
</x-page-header>

{{-- ── Stat Boxes ── --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
    <div class="bg-white border border-slate-200 rounded-md p-5">
        <div class="text-[11px] uppercase tracking-[0.18em] text-slate-500">Total Cadets</div>
        <div class="text-slate-900 tracking-tight text-3xl mt-2">{{ $stats[0]['value'] ?? '—' }}</div>
        <div class="text-xs text-slate-500 mt-1">{!! $stats[0]['sub'] ?? '' !!}</div>
    </div>
    <div class="bg-white border border-slate-200 rounded-md p-5">
        <div class="text-[11px] uppercase tracking-[0.18em] text-slate-500">Active Platoons</div>
        <div class="text-slate-900 tracking-tight text-3xl mt-2">{{ $stats[1]['value'] ?? '—' }}</div>
        <div class="text-xs text-slate-500 mt-1">{!! $stats[1]['sub'] ?? '' !!}</div>
    </div>
    <div class="bg-white border border-slate-200 rounded-md p-5">
        <div class="text-[11px] uppercase tracking-[0.18em] text-slate-500">Total Officer in Charge</div>
        <div class="text-slate-900 tracking-tight text-3xl mt-2">{{ $stats[2]['value'] ?? '—' }}</div>
        <div class="text-xs text-slate-500 mt-1">{!! $stats[2]['sub'] ?? '' !!}</div>
    </div>
    <div class="bg-white border border-slate-200 rounded-md p-5">
        <div class="text-[11px] uppercase tracking-[0.18em] text-slate-500">Reports Open</div>
        <div class="text-slate-900 tracking-tight text-3xl mt-2">{{ $stats[3]['value'] ?? '—' }}</div>
        <div class="text-xs text-slate-500 mt-1">{!! $stats[3]['sub'] ?? '' !!}</div>
    </div>
</div>

{{-- ── Cadet Analytics & Platoon Charts ── --}}
<div class="grid grid-cols-1 lg:grid-cols-2 gap-4 mt-6">
    <div class="bg-white border border-slate-200 rounded-md p-5 shadow-xs">
        <div class="text-slate-900 tracking-tight font-medium">Cadet Evaluation & Readiness Ratio</div>
        <div class="text-xs text-slate-500 mt-0.5 mb-3">Overall standing and pass/fail evaluation of ROTC cadets</div>
        <div class="flex flex-col sm:flex-row items-center justify-between gap-5 py-2">
            <div class="relative w-44 h-44 sm:w-48 sm:h-48 shrink-0 flex items-center justify-center">
                <canvas id="rotcCadetChart"></canvas>
            </div>
            <div id="rotcCadetLegend" class="flex-1 w-full space-y-1.5 min-w-[180px]">
                <!-- Populated dynamically via JS -->
            </div>
        </div>
    </div>

    <div class="bg-white border border-slate-200 rounded-md p-5 shadow-xs">
        <div class="text-slate-900 tracking-tight font-medium">Platoon Strength & Roster Distribution</div>
        <div class="text-xs text-slate-500 mt-0.5 mb-3">Cadet deployment across active platoons and companies</div>
        <div class="flex flex-col sm:flex-row items-center justify-between gap-5 py-2">
            <div class="relative w-44 h-44 sm:w-48 sm:h-48 shrink-0 flex items-center justify-center">
                <canvas id="rotcPlatoonChart"></canvas>
            </div>
            <div id="rotcPlatoonLegend" class="flex-1 w-full space-y-1.5 min-w-[180px]">
                <!-- Populated dynamically via JS -->
            </div>
        </div>
    </div>
</div>

{{-- ── Main Content: Reports + Calendar ── --}}
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 mt-6">

    {{-- LEFT — Accomplishment Reports --}}
    <div class="xl:col-span-2">
        <div class="bg-white border border-slate-200 rounded-md overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-100">
                <div class="text-slate-900 tracking-tight font-medium">Documentation / Accomplishment Reports</div>
                <div class="text-xs text-slate-500 mt-0.5">Track submission progress and deadlines</div>
            </div>
            <div class="divide-y divide-slate-100">
                @forelse($reports ?? [] as $report)
                @php
                    $statusMap = [
                        'draft'     => ['bg' => 'slate',   'bar' => 'slate'],
                        'review'    => ['bg' => 'indigo',  'bar' => 'indigo'],
                        'pending'   => ['bg' => 'indigo',  'bar' => 'indigo'],
                        'approved'  => ['bg' => 'emerald', 'bar' => 'emerald'],
                        'revisions' => ['bg' => 'rose',    'bar' => 'rose'],
                    ];
                    $style = $statusMap[$report->status ?? 'draft'] ?? $statusMap['draft'];
                @endphp
                <div class="px-6 py-4 hover:bg-slate-50">
                    <div class="flex items-center justify-between gap-3">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded bg-slate-100 flex items-center justify-center text-slate-500 shrink-0">
                                <x-icon name="filetext" class="w-4 h-4" />
                            </div>
                            <div class="min-w-0">
                                <div class="text-sm text-slate-900 truncate">{{ $report->title }}</div>
                                <div class="text-xs text-slate-500">{{ $report->due ?? '' }}</div>
                            </div>
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded-full bg-{{ $style['bg'] }}-50 text-{{ $style['bg'] }}-700 capitalize">{{ $report->status }}</span>
                    </div>
                    <div class="mt-3 flex items-center gap-3">
                        <div class="flex-1 h-1.5 rounded-full bg-slate-100 overflow-hidden">
                            <div class="h-full rounded-full bg-{{ $style['bar'] }}-500" style="width:{{ $report->progress ?? 0 }}%"></div>
                        </div>
                        <div class="text-[11px] text-slate-500 w-10 text-right tabular-nums">{{ $report->progress ?? 0 }}%</div>
                    </div>
                </div>
                @empty
                <div class="px-6 py-10 text-center text-sm text-slate-400">
                    No reports to display yet.
                </div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- RIGHT — Mini Calendar --}}
    <div class="xl:col-span-1">
        @php
            $now = now();
            $year = $now->year;
            $month = $now->month;
            $monthName = $now->format('F');
            $firstDay = $now->copy()->startOfMonth()->dayOfWeek;
            $daysInMonth = $now->daysInMonth;
            $today = $now->day;
        @endphp
        <div class="bg-white border border-slate-200 rounded-md overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100">
                <div class="text-slate-900 tracking-tight font-medium">Calendar of Activities</div>
                <div class="text-xs text-slate-500 mt-0.5">{{ $monthName }} {{ $year }}</div>
            </div>
            <div class="p-5">
                {{-- Day name headers --}}
                <div class="grid grid-cols-7 text-center text-[10px] uppercase tracking-wider text-slate-400 mb-2">
                    @foreach(['Su','Mo','Tu','We','Th','Fr','Sa'] as $dayName)
                        <div class="py-1">{{ $dayName }}</div>
                    @endforeach
                </div>
                {{-- Day cells --}}
                <div class="grid grid-cols-7 text-center text-sm">
                    {{-- Empty cells before 1st --}}
                    @for($i = 0; $i < $firstDay; $i++)
                        <div class="py-1.5"></div>
                    @endfor
                    {{-- Day numbers --}}
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        @if($d === $today)
                            <div class="py-1.5">
                                <span class="inline-flex items-center justify-center w-7 h-7 rounded-full bg-indigo-600 text-white font-semibold text-xs">{{ $d }}</span>
                            </div>
                        @else
                            <div class="py-1.5 text-slate-700 text-xs">{{ $d }}</div>
                        @endif
                    @endfor
                </div>
                {{-- Legend --}}
                <div class="mt-4 pt-3 border-t border-slate-100 flex items-center gap-4 text-[10px] text-slate-500">
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600"></span> Today
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Training Day
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500"></span> Deadline
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

@push('scripts')
<script>
    window.__ROTC_CHART_DATA = @json($chartData ?? null);
    document.addEventListener('DOMContentLoaded', () => {
        if (typeof window.initDashboardCharts === 'function') {
            window.initDashboardCharts();
        }
    });
</script>
@endpush

@endsection
