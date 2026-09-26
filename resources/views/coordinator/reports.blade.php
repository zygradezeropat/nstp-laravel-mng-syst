@extends('layouts.coordinator')

@section('title', 'Reports & OCR - Coordinator Dashboard')

@section('content')

<div class="px-2 sm:px-4 lg:px-6 space-y-6">
<x-page-header title="Reports & Data Processing" subtitle="Generate program reports, student performance sheets, and process physical documents via OCR">
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
    <!-- Left Column: Filter and Report Generator Form -->
    <div class="lg:col-span-1 space-y-6">
        <x-card title="Generate Official Reports" subtitle="Export data for university submission">
            <form id="reportFilterForm" action="{{ route('coordinator.reports') }}" method="GET" class="p-4 space-y-4" data-progress-title="Filtering Report Data" data-progress-subtitle="Applying selected filters and recalculating summary statistics...">
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-600">Report Type</label>
                    <select name="report_type" id="filterReportType" onchange="this.form.submit()" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 focus:outline-none focus:border-indigo-300 shadow-sm bg-white font-medium">
                        <option value="student_performance" {{ ($reportType ?? '') === 'student_performance' ? 'selected' : '' }}>Student Performance</option>
                        <option value="terminal" {{ ($reportType ?? '') === 'terminal' ? 'selected' : '' }}>Terminal Report</option>
                        <option value="masterlist" {{ ($reportType ?? '') === 'masterlist' ? 'selected' : '' }}>Master Enrollment List</option>
                        <option value="consolidated_grades" {{ ($reportType ?? '') === 'consolidated_grades' ? 'selected' : '' }}>Consolidated Grade Sheet</option>
                        <option value="financial" {{ ($reportType ?? '') === 'financial' ? 'selected' : '' }}>Financial Summary</option>
                    </select>
                </div>
                
                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-600">Program / Component</label>
                    <select name="program" id="filterProgram" onchange="this.form.submit()" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 focus:outline-none focus:border-indigo-300 shadow-sm bg-white">
                        <option value="all" {{ ($program ?? 'all') === 'all' ? 'selected' : '' }}>All Programs (CWTS, LTS, ROTC)</option>
                        <option value="CWTS" {{ ($program ?? '') === 'CWTS' ? 'selected' : '' }}>CWTS Only</option>
                        <option value="LTS" {{ ($program ?? '') === 'LTS' ? 'selected' : '' }}>LTS Only</option>
                        <option value="ROTC" {{ ($program ?? '') === 'ROTC' ? 'selected' : '' }}>ROTC Only</option>
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-600">School Year</label>
                    <select name="school_year" id="filterSchoolYear" onchange="this.form.submit()" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 focus:outline-none focus:border-indigo-300 shadow-sm bg-white">
                        <option value="all" {{ ($schoolYear ?? 'all') === 'all' ? 'selected' : '' }}>All School Years</option>
                        @foreach($schoolYears ?? ['2025-2026', '2024-2025'] as $sy)
                            <option value="{{ $sy }}" {{ ($schoolYear ?? '') === $sy ? 'selected' : '' }}>{{ $sy }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-600">Section</label>
                    <select name="section_id" id="filterSection" onchange="this.form.submit()" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 focus:outline-none focus:border-indigo-300 shadow-sm bg-white">
                        <option value="all" {{ ($sectionId ?? 'all') === 'all' ? 'selected' : '' }}>All Sections</option>
                        @foreach($sections ?? [] as $sec)
                            <option value="{{ $sec->id }}" {{ (string)($sectionId ?? '') === (string)$sec->id || ($sectionId ?? '') === $sec->section_name ? 'selected' : '' }}>
                                {{ $sec->section_name }} ({{ $sec->component }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="space-y-1">
                    <label class="text-xs font-semibold text-slate-600">Grade / Performance Status</label>
                    <select name="status" id="filterStatus" onchange="this.form.submit()" class="w-full px-3 py-2 text-sm rounded-lg border border-slate-200 focus:outline-none focus:border-indigo-300 shadow-sm bg-white">
                        <option value="all" {{ ($status ?? 'all') === 'all' ? 'selected' : '' }}>All Statuses</option>
                        <option value="Passed" {{ ($status ?? '') === 'Passed' ? 'selected' : '' }}>Passed Only</option>
                        <option value="Failed" {{ ($status ?? '') === 'Failed' ? 'selected' : '' }}>Failed Only</option>
                        <option value="Pending" {{ ($status ?? '') === 'Pending' ? 'selected' : '' }}>Pending / In Progress</option>
                    </select>
                </div>

                <!-- Hidden Signatory Fields for PDF Export -->
                <input type="hidden" name="received_by" id="inputReceivedBy" value="FELICIDAD L. FORRO" />
                <input type="hidden" name="received_by_title" id="inputReceivedByTitle" value="Registrar III" />
                <input type="hidden" name="submitted_by" id="inputSubmittedBy" value="DODONGAN, EUGINE B. / DR. EMIL F. BRIONES" />
                <input type="hidden" name="submitted_by_title" id="inputSubmittedByTitle" value="Professor / NSTP Coordinator" />
                <input type="hidden" name="received_sig" id="inputReceivedSig" value="" />
                <input type="hidden" name="submitted_sig" id="inputSubmittedSig" value="" />

                <div class="pt-2 flex flex-col gap-2">
                    <button type="submit" class="w-full px-4 py-2 text-sm font-semibold rounded-lg bg-slate-800 text-white hover:bg-slate-900 transition shadow-sm cursor-pointer flex items-center justify-center gap-2">
                        <x-icon name="search" class="w-4 h-4" /> Filter & Preview
                    </button>
                    <div class="grid grid-cols-2 gap-2 pt-1">
                        <button type="button" onclick="exportPdfReport()" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition shadow-sm cursor-pointer">
                            <x-icon name="download" class="w-3.5 h-3.5" /> Export PDF
                        </button>
                        <button type="button" onclick="exportCsvReport()" class="inline-flex items-center justify-center gap-1.5 px-3 py-2 text-xs font-semibold rounded-lg border border-slate-300 text-slate-700 hover:bg-slate-100 transition shadow-sm cursor-pointer">
                            <x-icon name="filetext" class="w-3.5 h-3.5" /> Export CSV
                        </button>
                    </div>
                </div>
            </form>
        </x-card>
    </div>

    <!-- Right Column: Dynamic Live Preview Table & Statistics according to Report Type -->
    <div class="lg:col-span-2 space-y-6">
        @php
            $allCount = count($allStudents ?? []);
            $passedStudents = collect($allStudents ?? [])->where('remarks', 'Passed')->count();
            $failedStudents = collect($allStudents ?? [])->where('remarks', 'Failed')->count();
            $pendingStudents = collect($allStudents ?? [])->where('remarks', 'Pending')->count();
            $passRate = $allCount > 0 ? round(($passedStudents / $allCount) * 100, 1) : 0;
        @endphp

        {{-- ── 1. STUDENT PERFORMANCE ────────────────────────────────────────── --}}
        @if(($reportType ?? 'student_performance') === 'student_performance')
            <!-- Stat Summary Row -->
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Enrolled Students</div>
                    <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($allCount) }}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Section Enrollees</div>
                </div>
                <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-200/80 shadow-sm">
                    <div class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Passed</div>
                    <div class="text-2xl font-black text-emerald-800 mt-1">{{ number_format($passedStudents) }}</div>
                    <div class="text-[11px] font-semibold text-emerald-600 mt-0.5">{{ $passRate }}% Pass Rate</div>
                </div>
                <div class="p-4 rounded-xl bg-rose-50/70 border border-rose-200/80 shadow-sm">
                    <div class="text-xs font-bold text-rose-700 uppercase tracking-wider">Failed</div>
                    <div class="text-2xl font-black text-rose-800 mt-1">{{ number_format($failedStudents) }}</div>
                    <div class="text-[11px] font-semibold text-rose-600 mt-0.5">{{ $allCount > 0 ? round(($failedStudents / $allCount) * 100, 1) : 0 }}% Failure Rate</div>
                </div>
                <div class="p-4 rounded-xl bg-amber-50/70 border border-amber-200/80 shadow-sm">
                    <div class="text-xs font-bold text-amber-700 uppercase tracking-wider">Pending / Active</div>
                    <div class="text-2xl font-black text-amber-800 mt-1">{{ number_format($pendingStudents) }}</div>
                    <div class="text-[11px] font-semibold text-amber-600 mt-0.5">Ungraded / In Progress</div>
                </div>
            </div>

            <!-- Preview Card (10 records per page) -->
            <x-card title="Enrolled Student Performance Preview" subtitle="Displaying enrolled section students with course grades (10 records per page)">
                <x-slot name="actions">
                    <div class="relative w-64">
                        <input type="text" id="tableSearch" placeholder="Search student, ID, course..." class="w-full pl-9 pr-3 py-1.5 text-xs rounded-lg border border-slate-200 focus:outline-none focus:ring-2 focus:ring-indigo-200 bg-white" onkeyup="filterReportTable()" />
                        <x-icon name="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5" />
                    </div>
                </x-slot>

                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm" id="performanceReportTable">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/70 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                <th class="py-3 px-4">Student ID</th>
                                <th class="py-3 px-4">Full Name</th>
                                <th class="py-3 px-4">Course</th>
                                <th class="py-3 px-4">Program</th>
                                <th class="py-3 px-4">Enrolled Section</th>
                                <th class="py-3 px-4 text-center">Grade</th>
                                <th class="py-3 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($students ?? [] as $s)
                            <tr class="hover:bg-slate-50/80 transition report-row">
                                <td class="py-3 px-4 font-mono font-bold text-xs text-indigo-900">{{ $s->student_no }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-900">{{ $s->name }}</td>
                                <td class="py-3 px-4 text-slate-600 text-xs font-medium">{{ $s->course }}</td>
                                <td class="py-3 px-4">
                                    <span class="px-2 py-0.5 rounded text-[10px] font-extrabold uppercase {{ $s->program === 'ROTC' ? 'bg-rose-100 text-rose-800' : ($s->program === 'LTS' ? 'bg-emerald-100 text-emerald-800' : 'bg-indigo-100 text-indigo-800') }}">
                                        {{ $s->program }}
                                    </span>
                                </td>
                                <td class="py-3 px-4 text-slate-700 text-xs font-semibold">{{ $s->section }}</td>
                                <td class="py-3 px-4 text-center font-bold text-xs {{ $s->grade !== 'N/A' ? 'text-slate-900' : 'text-slate-400' }}">
                                    {{ $s->grade }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($s->remarks === 'Passed')
                                        <span class="inline-flex items-center gap-1 text-[11px] px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Passed
                                        </span>
                                    @elseif($s->remarks === 'Failed')
                                        <span class="inline-flex items-center gap-1 text-[11px] px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-700 font-bold border border-rose-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Failed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 font-bold border border-amber-200">
                                            <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> {{ $s->remarks }}
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400 text-sm">
                                    No enrolled section student records found matching the filter options.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($students->hasPages())
                <div class="mt-4 px-4 py-3 border-t border-slate-100 bg-slate-50/50 rounded-b-xl">
                    {{ $students->links() }}
                </div>
                @endif
            </x-card>

        {{-- ── 2. TERMINAL REPORT ────────────────────────────────────────────── --}}
        @elseif($reportType === 'terminal')
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Active Sections</div>
                    <div class="text-2xl font-black text-slate-900 mt-1">{{ count($sections) }}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Program Sections</div>
                </div>
                <div class="p-4 rounded-xl bg-indigo-50/70 border border-indigo-200/80 shadow-sm">
                    <div class="text-xs font-bold text-indigo-700 uppercase tracking-wider">Total Enrolled</div>
                    <div class="text-2xl font-black text-indigo-800 mt-1">{{ number_format($allCount) }}</div>
                    <div class="text-[11px] text-indigo-600 font-semibold mt-0.5">Registered Students</div>
                </div>
                <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-200/80 shadow-sm">
                    <div class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Completers</div>
                    <div class="text-2xl font-black text-emerald-800 mt-1">{{ number_format($passedStudents) }}</div>
                    <div class="text-[11px] text-emerald-600 font-semibold mt-0.5">{{ $passRate }}% Completion Rate</div>
                </div>
                <div class="p-4 rounded-xl bg-fuchsia-50/70 border border-fuchsia-200/80 shadow-sm">
                    <div class="text-xs font-bold text-fuchsia-700 uppercase tracking-wider">Terminal Status</div>
                    <div class="text-xl font-black text-fuchsia-800 mt-1">Ready</div>
                    <div class="text-[11px] text-fuchsia-600 font-semibold mt-0.5">CHED / DND Compliant</div>
                </div>
            </div>

            <x-card title="Program Terminal Completion Report" subtitle="Section-by-section completion metrics for official university submission">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/70 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                <th class="py-3 px-4">Section Code</th>
                                <th class="py-3 px-4">Program</th>
                                <th class="py-3 px-4">School Year</th>
                                <th class="py-3 px-4 text-center">Enrolled</th>
                                <th class="py-3 px-4 text-center">Passed</th>
                                <th class="py-3 px-4 text-center">Failed</th>
                                <th class="py-3 px-4 text-center">Pass Rate</th>
                                <th class="py-3 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @foreach($sections as $sec)
                                @php
                                    $secEnrolled = collect($allStudents)->where('section', $sec->section_name)->count();
                                    $secPassed = collect($allStudents)->where('section', $sec->section_name)->where('remarks', 'Passed')->count();
                                    $secFailed = collect($allStudents)->where('section', $sec->section_name)->where('remarks', 'Failed')->count();
                                    $secRate = $secEnrolled > 0 ? round(($secPassed / $secEnrolled) * 100, 1) : 0;
                                @endphp
                                <tr class="hover:bg-slate-50/80 transition">
                                    <td class="py-3 px-4 font-bold text-indigo-900">{{ $sec->section_name }}</td>
                                    <td class="py-3 px-4 font-semibold text-slate-700">{{ $sec->component }}</td>
                                    <td class="py-3 px-4 text-slate-600 text-xs">{{ $sec->school_year }}</td>
                                    <td class="py-3 px-4 text-center font-bold text-slate-800">{{ $secEnrolled }}</td>
                                    <td class="py-3 px-4 text-center font-bold text-emerald-600">{{ $secPassed }}</td>
                                    <td class="py-3 px-4 text-center font-bold text-rose-600">{{ $secFailed }}</td>
                                    <td class="py-3 px-4 text-center font-extrabold text-slate-900">{{ $secRate }}%</td>
                                    <td class="py-3 px-4 text-center">
                                        <span class="inline-flex items-center gap-1 text-[11px] px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">
                                            Completed
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </x-card>

        {{-- ── 3. MASTER ENROLLMENT LIST ─────────────────────────────────────── --}}
        @elseif($reportType === 'masterlist')
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Masterlist Total</div>
                    <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($allCount) }}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Enrolled Records</div>
                </div>
                <div class="p-4 rounded-xl bg-indigo-50/70 border border-indigo-200/80 shadow-sm">
                    <div class="text-xs font-bold text-indigo-700 uppercase tracking-wider">Active Enrollees</div>
                    <div class="text-2xl font-black text-indigo-800 mt-1">{{ number_format($allCount) }}</div>
                    <div class="text-[11px] text-indigo-600 font-semibold mt-0.5">100% Verified</div>
                </div>
                <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-200/80 shadow-sm">
                    <div class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Current SY</div>
                    <div class="text-xl font-black text-emerald-800 mt-1">{{ $schoolYear === 'all' ? '2025-2026' : $schoolYear }}</div>
                    <div class="text-[11px] text-emerald-600 font-semibold mt-0.5">Academic Period</div>
                </div>
                <div class="p-4 rounded-xl bg-amber-50/70 border border-amber-200/80 shadow-sm">
                    <div class="text-xs font-bold text-amber-700 uppercase tracking-wider">Sections</div>
                    <div class="text-2xl font-black text-amber-800 mt-1">{{ count($sections) }}</div>
                    <div class="text-[11px] text-amber-600 font-semibold mt-0.5">Active Class Units</div>
                </div>
            </div>

            <x-card title="Master Student Enrollment Registry" subtitle="Official registry of enrolled NSTP students (10 records per page)">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/70 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                <th class="py-3 px-4">Student ID</th>
                                <th class="py-3 px-4">Serial No.</th>
                                <th class="py-3 px-4">Full Name</th>
                                <th class="py-3 px-4">Course</th>
                                <th class="py-3 px-4">Component</th>
                                <th class="py-3 px-4">Section</th>
                                <th class="py-3 px-4">School Year</th>
                                <th class="py-3 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($students ?? [] as $s)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 font-mono font-bold text-xs text-indigo-900">{{ $s->student_no }}</td>
                                <td class="py-3 px-4 font-mono text-xs text-slate-500">{{ $s->serial_no }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-900">{{ $s->name }}</td>
                                <td class="py-3 px-4 text-slate-600 text-xs font-medium">{{ $s->course }}</td>
                                <td class="py-3 px-4 font-bold text-slate-700">{{ $s->program }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-800 text-xs">{{ $s->section }}</td>
                                <td class="py-3 px-4 text-slate-600 text-xs">{{ $s->school_year }}</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center gap-1 text-[11px] px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">
                                        Enrolled
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="py-12 text-center text-slate-400 text-sm">
                                    No masterlist records found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($students->hasPages())
                <div class="mt-4 px-4 py-3 border-t border-slate-100 bg-slate-50/50 rounded-b-xl">
                    {{ $students->links() }}
                </div>
                @endif
            </x-card>

        {{-- ── 4. CONSOLIDATED GRADE SHEET ───────────────────────────────────── --}}
        @elseif($reportType === 'consolidated_grades')
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Graded Enrollees</div>
                    <div class="text-2xl font-black text-slate-900 mt-1">{{ number_format($allCount) }}</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Recorded Marks</div>
                </div>
                <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-200/80 shadow-sm">
                    <div class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Passed (1.0 - 3.0)</div>
                    <div class="text-2xl font-black text-emerald-800 mt-1">{{ number_format($passedStudents) }}</div>
                    <div class="text-[11px] text-emerald-600 font-semibold mt-0.5">{{ $passRate }}% Passing Rate</div>
                </div>
                <div class="p-4 rounded-xl bg-rose-50/70 border border-rose-200/80 shadow-sm">
                    <div class="text-xs font-bold text-rose-700 uppercase tracking-wider">Failed (5.0 / INC)</div>
                    <div class="text-2xl font-black text-rose-800 mt-1">{{ number_format($failedStudents) }}</div>
                    <div class="text-[11px] text-rose-600 font-semibold mt-0.5">{{ $allCount > 0 ? round(($failedStudents / $allCount) * 100, 1) : 0 }}% Non-Pass</div>
                </div>
                <div class="p-4 rounded-xl bg-indigo-50/70 border border-indigo-200/80 shadow-sm">
                    <div class="text-xs font-bold text-indigo-700 uppercase tracking-wider">Grade Status</div>
                    <div class="text-xl font-black text-indigo-800 mt-1">Consolidated</div>
                    <div class="text-[11px] text-indigo-600 font-semibold mt-0.5">Official Ratings</div>
                </div>
            </div>

            <x-card title="Consolidated Grade Sheet Preview" subtitle="Official academic grades per student and section (10 records per page)">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/70 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                <th class="py-3 px-4">Student ID</th>
                                <th class="py-3 px-4">Full Name</th>
                                <th class="py-3 px-4">Program</th>
                                <th class="py-3 px-4">Section</th>
                                <th class="py-3 px-4 text-center">Numerical Rating</th>
                                <th class="py-3 px-4 text-center">Equivalent</th>
                                <th class="py-3 px-4 text-center">Remarks</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($students ?? [] as $s)
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 font-mono font-bold text-xs text-indigo-900">{{ $s->student_no }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-900">{{ $s->name }}</td>
                                <td class="py-3 px-4 font-bold text-slate-700">{{ $s->program }}</td>
                                <td class="py-3 px-4 font-semibold text-slate-800 text-xs">{{ $s->section }}</td>
                                <td class="py-3 px-4 text-center font-extrabold text-slate-900">{{ $s->grade }}</td>
                                <td class="py-3 px-4 text-center font-bold text-xs">
                                    {{ $s->grade !== 'N/A' && is_numeric($s->grade) && floatval($s->grade) <= 3.0 ? 'PASSED' : ($s->grade !== 'N/A' ? 'FAILED' : 'PENDING') }}
                                </td>
                                <td class="py-3 px-4 text-center">
                                    @if($s->remarks === 'Passed')
                                        <span class="inline-flex items-center gap-1 text-[11px] px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">
                                            Passed
                                        </span>
                                    @elseif($s->remarks === 'Failed')
                                        <span class="inline-flex items-center gap-1 text-[11px] px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-700 font-bold border border-rose-200">
                                            Failed
                                        </span>
                                    @else
                                        <span class="inline-flex items-center gap-1 text-[11px] px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 font-bold border border-amber-200">
                                            Pending
                                        </span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="7" class="py-12 text-center text-slate-400 text-sm">
                                    No consolidated grade records found.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                @if($students->hasPages())
                <div class="mt-4 px-4 py-3 border-t border-slate-100 bg-slate-50/50 rounded-b-xl">
                    {{ $students->links() }}
                </div>
                @endif
            </x-card>

        {{-- ── 5. FINANCIAL SUMMARY ─────────────────────────────────────────── --}}
        @elseif($reportType === 'financial')
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="p-4 rounded-xl bg-white border border-slate-200/80 shadow-sm">
                    <div class="text-xs font-bold text-slate-500 uppercase tracking-wider">Allocated Budget</div>
                    <div class="text-2xl font-black text-slate-900 mt-1">₱185,000</div>
                    <div class="text-[11px] text-slate-400 mt-0.5">Approved NSTP Fund</div>
                </div>
                <div class="p-4 rounded-xl bg-emerald-50/70 border border-emerald-200/80 shadow-sm">
                    <div class="text-xs font-bold text-emerald-700 uppercase tracking-wider">Total Expenditures</div>
                    <div class="text-2xl font-black text-emerald-800 mt-1">₱165,700</div>
                    <div class="text-[11px] text-emerald-600 font-semibold mt-0.5">89.5% Utilized</div>
                </div>
                <div class="p-4 rounded-xl bg-indigo-50/70 border border-indigo-200/80 shadow-sm">
                    <div class="text-xs font-bold text-indigo-700 uppercase tracking-wider">Remaining Balance</div>
                    <div class="text-2xl font-black text-indigo-800 mt-1">₱19,300</div>
                    <div class="text-[11px] text-indigo-600 font-semibold mt-0.5">Unexpended Reserve</div>
                </div>
                <div class="p-4 rounded-xl bg-amber-50/70 border border-amber-200/80 shadow-sm">
                    <div class="text-xs font-bold text-amber-700 uppercase tracking-wider">Fund Status</div>
                    <div class="text-xl font-black text-amber-800 mt-1">Audited</div>
                    <div class="text-[11px] text-amber-600 font-semibold mt-0.5">COA & DNSC Approved</div>
                </div>
            </div>

            <x-card title="Program Financial Summary & Operational Expenses" subtitle="Breakdown of financial allocations, community project expenses, and operational funds">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead>
                            <tr class="border-b border-slate-200 bg-slate-50/70 text-[11px] font-bold text-slate-600 uppercase tracking-wider">
                                <th class="py-3 px-4">#</th>
                                <th class="py-3 px-4">Program / Activity</th>
                                <th class="py-3 px-4">Section / Target</th>
                                <th class="py-3 px-4 text-right">Allocated Budget</th>
                                <th class="py-3 px-4 text-right">Expenses Disbursed</th>
                                <th class="py-3 px-4 text-right">Remaining Balance</th>
                                <th class="py-3 px-4 text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 text-slate-500">1</td>
                                <td class="py-3 px-4 font-bold text-indigo-900">CWTS Community Service Project</td>
                                <td class="py-3 px-4 font-semibold text-slate-700">CWTS Sections</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-800">₱50,000.00</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-800">₱42,500.00</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600">₱7,500.00</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center gap-1 text-[11px] px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">Balanced</span>
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 text-slate-500">2</td>
                                <td class="py-3 px-4 font-bold text-indigo-900">LTS Literacy & Teaching Materials</td>
                                <td class="py-3 px-4 font-semibold text-slate-700">LTS Sections</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-800">₱35,000.00</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-800">₱31,200.00</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600">₱3,800.00</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center gap-1 text-[11px] px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">Balanced</span>
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 text-slate-500">3</td>
                                <td class="py-3 px-4 font-bold text-indigo-900">ROTC Field Training & Logistics</td>
                                <td class="py-3 px-4 font-semibold text-slate-700">ROTC Platoons</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-800">₱75,000.00</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-800">₱68,000.00</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600">₱7,000.00</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center gap-1 text-[11px] px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-200">Balanced</span>
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50/80 transition">
                                <td class="py-3 px-4 text-slate-500">4</td>
                                <td class="py-3 px-4 font-bold text-indigo-900">NSTP Civic Orientation & Graduation Seminar</td>
                                <td class="py-3 px-4 font-semibold text-slate-700">All Sections</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-800">₱25,000.00</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-slate-800">₱24,000.00</td>
                                <td class="py-3 px-4 text-right font-mono font-bold text-emerald-600">₱1,000.00</td>
                                <td class="py-3 px-4 text-center">
                                    <span class="inline-flex items-center gap-1 text-[11px] px-2.5 py-0.5 rounded-full bg-blue-50 text-blue-700 font-bold border border-blue-200">Completed</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </x-card>
        @endif
    </div>
</div>

<!-- Edit PDF Signatories & E-Signatures Modal -->
<div id="pdfSignatoriesOverlay" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden transition-opacity duration-200">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-xl mx-4 overflow-hidden transform transition-all duration-200" id="pdfSignatoriesModalContainer">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center shrink-0">
                    <x-icon name="pencil" class="w-4 h-4" />
                </div>
                <div>
                    <h3 class="text-base font-bold text-slate-800">PDF Report Signatories & E-Signatures</h3>
                    <p class="text-xs text-slate-500">Configure names, official titles, and digital signatures for PDF reports</p>
                </div>
            </div>
            <button type="button" onclick="closePdfSignatoriesModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                <x-icon name="close" class="w-4 h-4" />
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 space-y-5 text-sm max-h-[75vh] overflow-y-auto">
            <!-- Received By Section -->
            <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 space-y-3">
                <div class="text-xs font-bold text-indigo-700 uppercase tracking-wider flex items-center justify-between">
                    <span class="flex items-center gap-1.5"><x-icon name="user" class="w-3.5 h-3.5" /> "Received by" Signatory (Left Box)</span>
                    <button type="button" onclick="clearSignature('received')" class="text-[10px] font-semibold text-rose-600 hover:text-rose-700 hover:underline">Clear E-Sig</button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Full Name</label>
                        <input type="text" id="modalReceivedBy" value="FELICIDAD L. FORRO" oninput="updateSignatoryPreview()" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-indigo-300 bg-white font-semibold" placeholder="e.g. FELICIDAD L. FORRO" />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Designation / Title</label>
                        <input type="text" id="modalReceivedByTitle" value="Registrar III" oninput="updateSignatoryPreview()" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-indigo-300 bg-white" placeholder="e.g. Registrar III" />
                    </div>
                </div>

                <!-- E-Signature Options for Received By -->
                <div class="pt-1">
                    <label class="text-xs font-semibold text-slate-600 block mb-1">E-Signature (Draw or Upload Image)</label>
                    <div class="flex flex-col sm:flex-row items-center gap-3">
                        <div class="relative bg-white border border-slate-200 rounded-lg p-1">
                            <canvas id="canvasReceivedSig" width="220" height="55" class="bg-white rounded cursor-crosshair touch-none border border-dashed border-slate-200 block" title="Draw signature here"></canvas>
                            <span class="text-[9px] text-slate-400 absolute bottom-1 right-2 pointer-events-none">Draw Here ✍️</span>
                        </div>
                        <div class="flex flex-col gap-1.5 w-full sm:w-auto">
                            <input type="file" id="modalReceivedSigFile" accept=".png,.jpg,.jpeg" class="hidden" />
                            <button type="button" onclick="document.getElementById('modalReceivedSigFile').click()" class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 transition cursor-pointer flex items-center justify-center gap-1.5">
                                <x-icon name="upload" class="w-3.5 h-3.5" /> Upload Image
                            </button>
                            <span class="text-[10px] text-slate-400 text-center sm:text-left">PNG / JPG file</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Submitted By Section -->
            <div class="p-4 rounded-xl border border-slate-200/80 bg-slate-50/50 space-y-3">
                <div class="text-xs font-bold text-indigo-700 uppercase tracking-wider flex items-center justify-between">
                    <span class="flex items-center gap-1.5"><x-icon name="user" class="w-3.5 h-3.5" /> "Submitted by" Signatory (Right Box)</span>
                    <button type="button" onclick="clearSignature('submitted')" class="text-[10px] font-semibold text-rose-600 hover:text-rose-700 hover:underline">Clear E-Sig</button>
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Full Name</label>
                        <input type="text" id="modalSubmittedBy" value="DODONGAN, EUGINE B. / DR. EMIL F. BRIONES" oninput="updateSignatoryPreview()" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-indigo-300 bg-white font-semibold" placeholder="e.g. DR. EMIL F. BRIONES" />
                    </div>
                    <div>
                        <label class="text-xs font-semibold text-slate-600 block mb-1">Designation / Title</label>
                        <input type="text" id="modalSubmittedByTitle" value="Professor / NSTP Coordinator" oninput="updateSignatoryPreview()" class="w-full px-3 py-2 text-xs rounded-lg border border-slate-200 focus:outline-none focus:border-indigo-300 bg-white" placeholder="e.g. NSTP Coordinator" />
                    </div>
                </div>

                <!-- E-Signature Options for Submitted By -->
                <div class="pt-1">
                    <label class="text-xs font-semibold text-slate-600 block mb-1">E-Signature (Draw or Upload Image)</label>
                    <div class="flex flex-col sm:flex-row items-center gap-3">
                        <div class="relative bg-white border border-slate-200 rounded-lg p-1">
                            <canvas id="canvasSubmittedSig" width="220" height="55" class="bg-white rounded cursor-crosshair touch-none border border-dashed border-slate-200 block" title="Draw signature here"></canvas>
                            <span class="text-[9px] text-slate-400 absolute bottom-1 right-2 pointer-events-none">Draw Here ✍️</span>
                        </div>
                        <div class="flex flex-col gap-1.5 w-full sm:w-auto">
                            <input type="file" id="modalSubmittedSigFile" accept=".png,.jpg,.jpeg" class="hidden" />
                            <button type="button" onclick="document.getElementById('modalSubmittedSigFile').click()" class="px-3 py-1.5 text-xs font-semibold rounded-lg border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 transition cursor-pointer flex items-center justify-center gap-1.5">
                                <x-icon name="upload" class="w-3.5 h-3.5" /> Upload Image
                            </button>
                            <span class="text-[10px] text-slate-400 text-center sm:text-left">PNG / JPG file</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Signature Live Preview Card -->
            <div class="p-4 rounded-xl border border-dashed border-slate-300 bg-white">
                <div class="text-[10px] font-bold text-slate-400 uppercase tracking-wider text-center mb-3">Live Signature Footer Preview</div>
                <div class="grid grid-cols-2 gap-6 text-center text-xs">
                    <div class="flex flex-col justify-end">
                        <div class="text-[10px] text-slate-400 text-left mb-1">Received by:</div>
                        <div class="h-10 flex items-center justify-center mb-0.5">
                            <img id="prevReceivedSigImg" class="max-h-10 max-w-[130px] hidden object-contain" />
                        </div>
                        <div id="prevReceivedBy" class="font-bold text-slate-800 uppercase border-t border-slate-800 pt-1 text-[11px] truncate">FELICIDAD L. FORRO</div>
                        <div id="prevReceivedByTitle" class="text-[10px] text-slate-500 truncate">Registrar III</div>
                    </div>
                    <div class="flex flex-col justify-end">
                        <div class="text-[10px] text-slate-400 text-left mb-1">Submitted by:</div>
                        <div class="h-10 flex items-center justify-center mb-0.5">
                            <img id="prevSubmittedSigImg" class="max-h-10 max-w-[130px] hidden object-contain" />
                        </div>
                        <div id="prevSubmittedBy" class="font-bold text-slate-800 uppercase border-t border-slate-800 pt-1 text-[11px] truncate">DODONGAN, EUGINE B. / DR. EMIL F. BRIONES</div>
                        <div id="prevSubmittedByTitle" class="text-[10px] text-slate-500 truncate">Professor / NSTP Coordinator</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer Actions -->
        <div class="px-6 py-3.5 border-t border-slate-100 bg-slate-50 flex items-center justify-between">
            <button type="button" onclick="resetPdfSignatories()" class="px-3.5 py-2 text-xs font-semibold rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-100 transition cursor-pointer">
                Reset Defaults
            </button>
            <div class="flex items-center gap-2">
                <button type="button" onclick="closePdfSignatoriesModal()" class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 transition cursor-pointer">
                    Cancel
                </button>
                <button type="button" onclick="saveSignatoriesAndExportPdf()" class="inline-flex items-center gap-1.5 px-5 py-2 text-xs font-bold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition cursor-pointer">
                    <x-icon name="download" class="w-3.5 h-3.5" /> Export PDF Document
                </button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    window.receivedSigData = null;
    window.submittedSigData = null;

    function initSignatureCanvas(canvasId, storageKey) {
        const canvas = document.getElementById(canvasId);
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        let drawing = false;

        function getPos(e) {
            const rect = canvas.getBoundingClientRect();
            const clientX = e.touches ? e.touches[0].clientX : e.clientX;
            const clientY = e.touches ? e.touches[0].clientY : e.clientY;
            return {
                x: (clientX - rect.left) * (canvas.width / rect.width),
                y: (clientY - rect.top) * (canvas.height / rect.height)
            };
        }

        function startDraw(e) {
            drawing = true;
            const pos = getPos(e);
            ctx.beginPath();
            ctx.moveTo(pos.x, pos.y);
            ctx.strokeStyle = '#0f172a';
            ctx.lineWidth = 2.5;
            ctx.lineCap = 'round';
            ctx.lineJoin = 'round';
        }

        function draw(e) {
            if (!drawing) return;
            e.preventDefault();
            const pos = getPos(e);
            ctx.lineTo(pos.x, pos.y);
            ctx.stroke();
            window[storageKey] = canvas.toDataURL('image/png');
            updateSignatoryPreview();
        }

        function stopDraw() {
            if (drawing) {
                drawing = false;
                window[storageKey] = canvas.toDataURL('image/png');
                updateSignatoryPreview();
            }
        }

        canvas.addEventListener('mousedown', startDraw);
        canvas.addEventListener('mousemove', draw);
        canvas.addEventListener('mouseup', stopDraw);
        canvas.addEventListener('mouseleave', stopDraw);

        canvas.addEventListener('touchstart', startDraw, { passive: false });
        canvas.addEventListener('touchmove', draw, { passive: false });
        canvas.addEventListener('touchend', stopDraw);
    }

    function initSigFileUpload(fileInputId, storageKey) {
        const input = document.getElementById(fileInputId);
        if (!input) return;
        input.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (evt) => {
                window[storageKey] = evt.target.result;
                updateSignatoryPreview();
            };
            reader.readAsDataURL(file);
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        initSignatureCanvas('canvasReceivedSig', 'receivedSigData');
        initSignatureCanvas('canvasSubmittedSig', 'submittedSigData');
        initSigFileUpload('modalReceivedSigFile', 'receivedSigData');
        initSigFileUpload('modalSubmittedSigFile', 'submittedSigData');
    });

    window.openPdfSignatoriesModal = function() {
        const overlay = document.getElementById('pdfSignatoriesOverlay');
        if (overlay) {
            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
            updateSignatoryPreview();
        }
    };

    window.closePdfSignatoriesModal = function() {
        const overlay = document.getElementById('pdfSignatoriesOverlay');
        if (overlay) {
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
        }
        syncInputsToHidden();
    };

    window.clearSignature = function(type) {
        if (type === 'received') {
            window.receivedSigData = null;
            const canvas = document.getElementById('canvasReceivedSig');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
            if (document.getElementById('modalReceivedSigFile')) {
                document.getElementById('modalReceivedSigFile').value = '';
            }
        } else {
            window.submittedSigData = null;
            const canvas = document.getElementById('canvasSubmittedSig');
            if (canvas) {
                const ctx = canvas.getContext('2d');
                ctx.clearRect(0, 0, canvas.width, canvas.height);
            }
            if (document.getElementById('modalSubmittedSigFile')) {
                document.getElementById('modalSubmittedSigFile').value = '';
            }
        }
        updateSignatoryPreview();
    };

    function syncInputsToHidden() {
        const rName = document.getElementById('modalReceivedBy')?.value || 'FELICIDAD L. FORRO';
        const rTitle = document.getElementById('modalReceivedByTitle')?.value || 'Registrar III';
        const sName = document.getElementById('modalSubmittedBy')?.value || 'DODONGAN, EUGINE B. / DR. EMIL F. BRIONES';
        const sTitle = document.getElementById('modalSubmittedByTitle')?.value || 'Professor / NSTP Coordinator';

        if (document.getElementById('inputReceivedBy')) document.getElementById('inputReceivedBy').value = rName;
        if (document.getElementById('inputReceivedByTitle')) document.getElementById('inputReceivedByTitle').value = rTitle;
        if (document.getElementById('inputSubmittedBy')) document.getElementById('inputSubmittedBy').value = sName;
        if (document.getElementById('inputSubmittedByTitle')) document.getElementById('inputSubmittedByTitle').value = sTitle;

        if (document.getElementById('inputReceivedSig')) document.getElementById('inputReceivedSig').value = window.receivedSigData || '';
        if (document.getElementById('inputSubmittedSig')) document.getElementById('inputSubmittedSig').value = window.submittedSigData || '';
    }

    window.updateSignatoryPreview = function() {
        const rName = document.getElementById('modalReceivedBy')?.value || 'FELICIDAD L. FORRO';
        const rTitle = document.getElementById('modalReceivedByTitle')?.value || 'Registrar III';
        const sName = document.getElementById('modalSubmittedBy')?.value || 'DODONGAN, EUGINE B. / DR. EMIL F. BRIONES';
        const sTitle = document.getElementById('modalSubmittedByTitle')?.value || 'Professor / NSTP Coordinator';

        if (document.getElementById('prevReceivedBy')) document.getElementById('prevReceivedBy').textContent = rName.toUpperCase();
        if (document.getElementById('prevReceivedByTitle')) document.getElementById('prevReceivedByTitle').textContent = rTitle;
        if (document.getElementById('prevSubmittedBy')) document.getElementById('prevSubmittedBy').textContent = sName.toUpperCase();
        if (document.getElementById('prevSubmittedByTitle')) document.getElementById('prevSubmittedByTitle').textContent = sTitle;

        const prevRImg = document.getElementById('prevReceivedSigImg');
        if (prevRImg) {
            if (window.receivedSigData) {
                prevRImg.src = window.receivedSigData;
                prevRImg.classList.remove('hidden');
            } else {
                prevRImg.classList.add('hidden');
            }
        }

        const prevSImg = document.getElementById('prevSubmittedSigImg');
        if (prevSImg) {
            if (window.submittedSigData) {
                prevSImg.src = window.submittedSigData;
                prevSImg.classList.remove('hidden');
            } else {
                prevSImg.classList.add('hidden');
            }
        }

        syncInputsToHidden();
    };

    window.resetPdfSignatories = function() {
        if (document.getElementById('modalReceivedBy')) document.getElementById('modalReceivedBy').value = 'FELICIDAD L. FORRO';
        if (document.getElementById('modalReceivedByTitle')) document.getElementById('modalReceivedByTitle').value = 'Registrar III';
        if (document.getElementById('modalSubmittedBy')) document.getElementById('modalSubmittedBy').value = 'DODONGAN, EUGINE B. / DR. EMIL F. BRIONES';
        if (document.getElementById('modalSubmittedByTitle')) document.getElementById('modalSubmittedByTitle').value = 'Professor / NSTP Coordinator';
        clearSignature('received');
        clearSignature('submitted');
        updateSignatoryPreview();
    };

    function exportPdfReport() {
        openPdfSignatoriesModal();
    }

    window.saveSignatoriesAndExportPdf = function() {
        closePdfSignatoriesModal();
        triggerActualPdfDownload();
    };

    function triggerActualPdfDownload() {
        syncInputsToHidden();

        if (window.showProgressModal) {
            window.showProgressModal('Exporting PDF Report', 'Building official document format and embedding e-signatures...', 30);
        }
        setTimeout(() => {
            if (window.updateProgressModal) window.updateProgressModal(80, 'Downloading PDF file...');
        }, 600);

        setTimeout(() => {
            // Create hidden form to post base64 signatures safely without GET URL limits
            const postForm = document.createElement('form');
            postForm.method = 'POST';
            postForm.action = "{{ route('coordinator.reports.export_pdf') }}";
            postForm.target = '_blank';

            const csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = '_token';
            csrfInput.value = "{{ csrf_token() }}";
            postForm.appendChild(csrfInput);

            const filterForm = document.getElementById('reportFilterForm');
            const formData = new FormData(filterForm);

            for (let [key, val] of formData.entries()) {
                const hidden = document.createElement('input');
                hidden.type = 'hidden';
                hidden.name = key;
                hidden.value = val;
                postForm.appendChild(hidden);
            }

            document.body.appendChild(postForm);
            postForm.submit();
            document.body.removeChild(postForm);

            if (window.finishProgressModal) {
                window.finishProgressModal('PDF Export Started', 'Your official PDF report is being compiled and downloaded.', 'success');
            }
        }, 1000);
    }

    function exportCsvReport() {
        if (window.showProgressModal) {
            window.showProgressModal('Exporting CSV Data', 'Extracting table records and converting to CSV...', 40);
        }
        setTimeout(() => {
            const rows = document.querySelectorAll('.report-row');
            let csvContent = "data:text/csv;charset=utf-8,Student ID,Full Name,Course,Program,Section,Grade,Status\n";

            rows.forEach(row => {
                if (row.style.display !== 'none') {
                    const cols = row.querySelectorAll('td');
                    const rowData = Array.from(cols).map(c => '"' + c.innerText.trim().replace(/"/g, '""') + '"');
                    csvContent += rowData.join(",") + "\n";
                }
            });

            const encodedUri = encodeURI(csvContent);
            const link = document.createElement("a");
            link.setAttribute("href", encodedUri);
            link.setAttribute("download", "NSTP_Report_Export.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);

            if (window.finishProgressModal) {
                window.finishProgressModal('CSV Export Complete', 'The CSV report has been generated and downloaded.', 'success');
            }
        }, 800);
    }
</script>
</div>
@endpush

@endsection
