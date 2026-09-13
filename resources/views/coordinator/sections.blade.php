@extends('layouts.coordinator')

@section('title', 'Sections - Coordinator Dashboard')


@section('content')

@if(session('success'))
<div class="mb-5 p-4 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm flex items-start gap-2.5 shadow-sm">
    <x-icon name="check2" class="w-5 h-5 shrink-0 text-emerald-600 mt-0.5" />
    <div>
        <div class="font-bold">Success!</div>
        <div>{{ session('success') }}</div>
    </div>
</div>
@endif

@if($errors->any())
<div class="mb-5 p-4 rounded-xl bg-rose-50 border border-rose-200 text-rose-800 text-sm flex items-start gap-2.5 shadow-sm">
    <x-icon name="alertc" class="w-5 h-5 shrink-0 text-rose-600 mt-0.5" />
    <div>
        <div class="font-bold">Error submitting form:</div>
        <ul class="list-disc list-inside mt-1 space-y-0.5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
</div>
@endif

<x-page-header title="Sections & Students" subtitle="Manage CWTS, LTS, and ROTC classes and master student database">
    <x-slot name="actions">
        <div class="flex items-center gap-3">
            <!-- Tab Navigation Switcher -->
            <div class="inline-flex p-1 bg-slate-100/90 border border-slate-200/80 rounded-xl space-x-1">
                <button type="button" id="tabBtnSections" onclick="switchMainTab('sections')" class="px-4 py-1.5 text-xs font-bold rounded-lg transition-all shadow-sm bg-white text-indigo-600 cursor-pointer">
                    Sections
                </button>
                <button type="button" id="tabBtnMasterlist" onclick="switchMainTab('masterlist')" class="px-4 py-1.5 text-xs font-semibold rounded-lg transition-all text-slate-600 hover:text-slate-900 cursor-pointer">
                    Masterlist
                </button>
            </div>

            <input type="file" id="xlsxImportInput" accept=".xlsx,.xls,.csv" class="hidden" />

            <!-- Action Button for Sections Tab -->
            <button id="actionBtnNewSection" onclick="document.getElementById('newSectionOverlay').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition cursor-pointer">
                <x-icon name="plus" class="w-4 h-4" /> New Section
            </button>

            <!-- Action Button for Masterlist Tab (Moved per instructions) -->
            <button id="importXlsxBtn" class="hidden inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition shadow-sm cursor-pointer">
                <x-icon name="upload" class="w-4 h-4" /> Import Master List XLSX File
            </button>
        </div>
    </x-slot>
</x-page-header>

<!-- ========================================================================= -->
<!-- SECTIONS TAB CONTENT -->
<!-- ========================================================================= -->
<div id="sectionsTabContent" class="space-y-6 mt-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
        @foreach($progDefs as $p)
            <button data-program-filter="{{ $p['key'] }}" class="bg-white rounded-2xl p-5 shadow-sm text-left transition-all border border-slate-100 hover:border-slate-300 hover:shadow-md cursor-pointer w-full">
                <div class="flex items-center gap-3 mb-4">
                    <div class="w-10 h-10 rounded-full {{ $p['color'] }} text-white flex items-center justify-center text-base font-bold shrink-0">{{ $p['letter'] }}</div>
                    <div>
                        <div class="text-slate-900 font-semibold tracking-tight">{{ $p['label'] }}</div>
                        <div class="text-xs text-slate-500">{{ $p['full'] }}</div>
                    </div>
                </div>
                <div class="space-y-3">
                    <div>
                        <div class="flex items-center justify-between text-sm mb-1">
                            <span class="text-slate-500">Students</span>
                            <span class="text-slate-700 font-medium">{{ $p['studentCount'] }} / {{ $p['maxStudents'] }}</span>
                        </div>
                        <div class="w-full bg-slate-100 rounded-full h-1.5"><div class="{{ $p['bar'] }} h-1.5 rounded-full" style="width: {{ $p['percent'] }}%"></div></div>
                    </div>
                    <div class="text-xs text-slate-500 flex items-center justify-between">
                        <span>{{ $p['sectionCount'] }} {{ Str::plural('Section', $p['sectionCount']) }}</span>
                        <span>{{ $p['percent'] }}% Capacity</span>
                    </div>
                </div>
            </button>
        @endforeach
    </div>

    <x-card title="All Sections">
        <x-table>
            <x-slot name="header">
                <th class="py-2 px-3 font-medium">Section</th>
                <th class="py-2 px-3 font-medium">Program</th>
                <th class="py-2 px-3 font-medium">School Year</th>
                <th class="py-2 px-3 font-medium">Students</th>
                <th class="py-2 px-3 font-medium">Instructor</th>
                <th class="py-2 px-3 font-medium">Room</th>
                <th class="py-2 px-3 font-medium">Semester</th>
                <th class="py-2 px-3 font-medium text-right">Actions</th>
            </x-slot>

            @forelse($sections as $r)
            <tr class="border-b border-slate-50 hover:bg-indigo-50/40 cursor-pointer transition" data-program="{{ $r->program }}" onclick="window.location.href='{{ route('coordinator.section_students', $r->code) }}'">
                <td class="py-3 px-3 text-slate-900 font-medium">{{ $r->code }}</td>
                <td class="py-3 px-3">
                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $r->program === 'CWTS' ? 'bg-indigo-50 text-indigo-700' : ($r->program === 'LTS' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700') }}">
                        {{ $r->program }}
                    </span>
                </td>
                <td class="py-3 px-3">
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $r->schoolYear === '2025-2026' ? 'bg-violet-50 text-violet-700' : 'bg-rose-50 text-rose-700' }}">
                        {{ $r->schoolYear }}
                    </span>
                </td>
                <td class="py-3 px-3 text-slate-700 font-medium">{{ $r->students }}</td>
                <td class="py-3 px-3 text-slate-700">{{ $r->instructor }}</td>
                <td class="py-3 px-3 text-slate-700">{{ $r->room }}</td>
                <td class="py-3 px-3">
                    <span class="text-xs px-2 py-0.5 rounded-full {{ str_contains(strtolower($r->semester), '1st') ? 'bg-amber-50 text-amber-700' : 'bg-blue-50 text-blue-700' }}">
                        {{ $r->semester }}
                    </span>
                </td>
                <td class="py-3 px-3 text-right" onclick="event.stopPropagation();">
                    <button onclick="openEditSectionModal('{{ $r->id }}', '{{ $r->code }}', '{{ $r->program }}', '{{ $r->schoolYear }}', '{{ $r->room }}', '{{ $r->instructor }}', '{{ $r->semester }}')" class="text-slate-400 hover:text-indigo-600 p-1 transition cursor-pointer" title="Edit Section"><x-icon name="pencil" class="w-4 h-4" /></button>
                    <button type="button" onclick="openDeleteSectionModal('{{ $r->id }}', '{{ addslashes($r->code) }}')" class="text-slate-400 hover:text-rose-600 p-1 transition cursor-pointer" title="Delete Section"><x-icon name="trash" class="w-4 h-4" /></button>
                </td>
            </tr>
            @empty
            <tr><td colspan="8" class="py-8 text-center text-slate-400 text-sm">No sections found.</td></tr>
            @endforelse
        </x-table>
        @if($sections->hasPages())
            <div class="mt-4 px-4 py-3 border-t border-slate-100 bg-slate-50/50 rounded-b-xl">
                {{ $sections->links() }}
            </div>
        @endif
    </x-card>
</div>

<!-- ========================================================================= -->
<!-- MASTERLIST TAB CONTENT -->
<!-- ========================================================================= -->
<div id="masterlistTabContent" class="hidden space-y-6 mt-6">
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-xl font-bold text-slate-800 tracking-tight">Masterlist</h2>
            <p class="text-xs text-slate-500 mt-0.5">View and manage all NSTP students</p>
        </div>
        <div id="masterlistResultCountBadge" class="text-xs font-bold text-slate-500 bg-slate-100 px-3 py-1.5 rounded-xl border border-slate-200">
            Showing {{ count($masterStudents) }} students
        </div>
    </div>

    <!-- Multi-Filter & Search Bar -->
    <div class="bg-white rounded-2xl p-5 border border-slate-100 shadow-sm space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-6 gap-3">
            <!-- Search -->
            <div class="md:col-span-2">
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">Search Student</label>
                <div class="relative">
                    <input type="text" id="masterlistSearch" placeholder="Search Student ID, Serial No, or Name..." class="w-full pl-9 pr-3 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    <x-icon name="search" class="w-3.5 h-3.5 text-slate-400 absolute left-3 top-2.5" />
                </div>
            </div>

            <!-- Program Filter -->
            <div>
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">Program</label>
                <select id="masterlistProgFilter" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition cursor-pointer">
                    <option value="">All Programs</option>
                    <option value="CWTS">CWTS</option>
                    <option value="LTS">LTS</option>
                    <option value="ROTC">ROTC</option>
                </select>
            </div>

            <!-- Section Filter -->
            <div>
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">Section</label>
                <select id="masterlistSecFilter" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition cursor-pointer">
                    <option value="">All Sections</option>
                    @foreach($allSectionsList as $secCode)
                        <option value="{{ $secCode }}">{{ $secCode }}</option>
                    @endforeach
                </select>
            </div>

            <!-- School Year Filter -->
            <div>
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">School Year</label>
                <select id="masterlistSyFilter" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition cursor-pointer">
                    <option value="">All School Years</option>
                    @foreach($schoolYearsList as $sy)
                        <option value="{{ $sy }}">{{ $sy }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Year Level Filter -->
            <div>
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">Year Level</label>
                <select id="masterlistYlFilter" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition cursor-pointer">
                    <option value="">All Year Levels</option>
                    <option value="1st Year">1st Year</option>
                    <option value="2nd Year">2nd Year</option>
                    <option value="3rd Year">3rd Year</option>
                    <option value="4th Year">4th Year</option>
                </select>
            </div>

            <!-- Status Filter -->
            <div>
                <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1.5">Status</label>
                <select id="masterlistStatusFilter" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition cursor-pointer">
                    <option value="">All Statuses</option>
                    <option value="Active">Active</option>
                    <option value="Passed">Passed</option>
                    <option value="Failed">Failed</option>
                    <option value="Pending">Pending</option>
                    <option value="Dropped">Dropped</option>
                    <option value="Completed">Completed</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Masterlist Students Table -->
    <x-card title="Masterlist Student Database">
        <x-table>
            <x-slot name="header">
                <th class="py-2 px-3 font-medium">Student ID</th>
                <th class="py-2 px-3 font-medium">Serial No</th>
                <th class="py-2 px-3 font-medium">Student Name</th>
                <th class="py-2 px-3 font-medium">Program</th>
                <th class="py-2 px-3 font-medium">Section</th>
                <th class="py-2 px-3 font-medium">School Year</th>
                <th class="py-2 px-3 font-medium">Year Level</th>
                <th class="py-2 px-3 font-medium">Status</th>
            </x-slot>

            @forelse($masterStudents as $stu)
            <tr class="masterlist-row border-b border-slate-50 hover:bg-indigo-50/30 transition"
                data-student-id="{{ strtolower($stu->student_id) }}"
                data-serial-no="{{ strtolower($stu->serial_no) }}"
                data-name="{{ strtolower($stu->name) }}"
                data-program="{{ $stu->program }}"
                data-section="{{ $stu->section }}"
                data-sy="{{ $stu->school_year }}"
                data-yl="{{ $stu->year_level }}"
                data-status="{{ strtolower($stu->status) }}">
                
                <td class="py-3 px-3 font-mono text-xs font-semibold text-slate-700">
                    {{ $stu->student_id !== 'N/A' ? $stu->student_id : '—' }}
                </td>
                <td class="py-3 px-3 font-mono text-xs font-medium text-slate-600">
                    {{ $stu->serial_no !== 'N/A' ? $stu->serial_no : '—' }}
                </td>
                <td class="py-3 px-3 font-bold text-slate-800">{{ $stu->name }}</td>
                <td class="py-3 px-3">
                    <span class="text-xs px-2 py-0.5 rounded-full font-semibold {{ $stu->program === 'CWTS' ? 'bg-indigo-50 text-indigo-700' : ($stu->program === 'LTS' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700') }}">
                        {{ $stu->program }}
                    </span>
                </td>
                <td class="py-3 px-3 text-slate-700 font-medium">{{ $stu->section }}</td>
                <td class="py-3 px-3">
                    <span class="text-xs px-2 py-0.5 rounded-full bg-violet-50 text-violet-700 font-medium">
                        {{ $stu->school_year }}
                    </span>
                </td>
                <td class="py-3 px-3 text-slate-600 text-xs">{{ $stu->year_level }}</td>
                <td class="py-3 px-3">
                    @php
                        $st = strtolower($stu->status);
                        $statusClass = 'bg-slate-100 text-slate-600 border-slate-200';
                        if (in_array($st, ['active', 'passed', 'completed'])) {
                            $statusClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                        } elseif (in_array($st, ['failed', 'dropped'])) {
                            $statusClass = 'bg-rose-50 text-rose-700 border-rose-200';
                        } elseif ($st === 'pending') {
                            $statusClass = 'bg-amber-50 text-amber-700 border-amber-200';
                        }
                    @endphp
                    <span class="text-xs px-2.5 py-0.5 rounded-full border font-semibold {{ $statusClass }}">
                        {{ ucfirst($stu->status) }}
                    </span>
                </td>
            </tr>
            @empty
            <tr id="masterlistEmptyRow"><td colspan="8" class="py-8 text-center text-slate-400 text-sm">No masterlist students found in database.</td></tr>
            @endforelse
        </x-table>

        <!-- Masterlist Pagination Bar -->
        <div id="masterlistPaginationContainer" class="mt-4 px-4 py-3 border-t border-slate-100 bg-slate-50/50 rounded-b-xl flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
            <div id="masterlistPaginationInfo" class="text-slate-500 font-medium">
                Showing 1 to 10 of {{ count($masterStudents) }} students
            </div>
            <div class="flex items-center gap-2">
                <button type="button" id="masterlistPrevBtn" onclick="changeMasterlistPage(-1)" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed font-medium transition cursor-pointer">
                    Previous
                </button>
                <span id="masterlistPageIndicator" class="font-bold text-slate-700 px-2">
                    Page 1 of 1
                </span>
                <button type="button" id="masterlistNextBtn" onclick="changeMasterlistPage(1)" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed font-medium transition cursor-pointer">
                    Next
                </button>
            </div>
        </div>

        <!-- Professional Empty State for Filter Matches -->
        <div id="masterlistFilterEmptyState" class="hidden text-center py-12 px-4 bg-white rounded-2xl border border-slate-100 shadow-sm my-4">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                <x-icon name="alertc" class="w-6 h-6" />
            </div>
            <h3 class="text-base font-bold text-slate-800">No students found</h3>
            <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Try adjusting your search or filters to find student records.</p>
        </div>
    </x-card>
</div>

<!-- New Section Modal -->
<div id="newSectionOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-lg mx-4">
        <form id="newSectionForm" onsubmit="event.preventDefault();">
            @csrf
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <div class="text-slate-900 font-bold tracking-tight text-lg">New Section</div>
                    <div class="text-xs text-slate-500 mt-0.5">Fill in the details to add a new section</div>
                </div>
                <button type="button" id="sectionFormClose" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition cursor-pointer" onclick="document.getElementById('newSectionOverlay').classList.add('hidden')">
                    <x-icon name="close" class="w-4 h-4" />
                </button>
            </div>
            <div class="p-6 space-y-5 text-sm">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Section Code <span class="text-rose-500">*</span></div>
                        <input name="code" id="newSecCode" placeholder="e.g. CWTS-1A" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">NSTP Component <span class="text-rose-500">*</span></div>
                        <select name="program" id="newSecProgram" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition">
                            <option value="CWTS">CWTS — Civic Welfare</option>
                            <option value="LTS">LTS — Literacy Training</option>
                            <option value="ROTC">ROTC — Reserve Officers</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">School Year</div>
                        <input name="school_year" id="newSecSchoolYear" placeholder="e.g. 2025-2026" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Semester</div>
                        <input name="semester" id="newSecSemester" placeholder="e.g. 1st Semester" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Room</div>
                        <input name="room" id="newSecRoom" placeholder="e.g. B-210" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                </div>

                <div class="border-t border-slate-100 pt-4 space-y-4">
                    <div class="text-xs font-bold text-slate-700 uppercase tracking-wider">Compare list with Master List (XLSX only)</div>
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 flex items-center gap-1">
                            <span>Class List XLSX</span>
                            <span class="text-rose-500">*</span>
                        </div>
                        <label class="block border-2 border-dashed border-slate-200 hover:border-indigo-300 rounded-xl p-4 text-center bg-slate-50 hover:bg-slate-100/50 transition cursor-pointer">
                            <x-icon name="upload" class="w-5 h-5 text-slate-400 mx-auto" />
                            <span id="classFileLabel" class="text-xs text-slate-550 mt-1 block truncate">Upload Class List</span>
                            <input type="file" id="newSecClassFile" accept=".xlsx" class="hidden" />
                        </label>
                    </div>
                    <div id="compareResultContainer" class="hidden text-xs p-3 rounded-xl bg-indigo-50 border border-indigo-100 text-indigo-800">
                        <div class="font-bold flex items-center justify-between">
                            <span>Comparison Results:</span>
                            <span id="compareCountLabel" class="text-indigo-900 font-extrabold">0 matched</span>
                        </div>
                        <div class="mt-1 text-indigo-750 leading-snug">
                            Matched against the globally imported Master List. Non-matching and anonymous names have been filtered out.
                        </div>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50 rounded-b-2xl">
                <button type="button" id="sectionFormCancel" class="px-4 py-2 text-sm font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 transition cursor-pointer" onclick="document.getElementById('newSectionOverlay').classList.add('hidden')">Cancel</button>
                <button type="button" id="sectionFormCreate" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition cursor-pointer">
                    <x-icon name="users" class="w-4 h-4" /> Create Section
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Section Modal -->
<div id="editSectionOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-lg mx-4">
        <form id="editSectionForm" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <div class="text-slate-900 font-bold tracking-tight text-lg">Edit Section</div>
                    <div class="text-xs text-slate-500 mt-0.5">Modify section configurations</div>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition cursor-pointer" onclick="document.getElementById('editSectionOverlay').classList.add('hidden')">
                    <x-icon name="close" class="w-4 h-4" />
                </button>
            </div>
            <div class="p-6 space-y-5 text-sm">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Section Code <span class="text-rose-500">*</span></div>
                        <input type="text" name="code" id="editSecCode" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">NSTP Component <span class="text-rose-500">*</span></div>
                        <select name="program" id="editSecProgram" required class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition">
                            <option value="CWTS">CWTS — Civic Welfare</option>
                            <option value="LTS">LTS — Literacy Training</option>
                            <option value="ROTC">ROTC — Reserve Officers</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">School Year</div>
                        <input type="text" name="school_year" id="editSecSchoolYear" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Semester</div>
                        <input type="text" name="semester" id="editSecSemester" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Room</div>
                        <input type="text" name="room" id="editSecRoom" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                </div>
                <div>
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Assign Instructor</div>
                    <select name="instructor_name" id="editSecInstructor" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition">
                        <option value="">— Select an instructor (optional) —</option>
                        @foreach($instructors ?? [] as $inst)
                            <option value="{{ $inst->name }}">{{ $inst->name }}{{ isset($inst->dept) && $inst->dept ? ' · ' . $inst->dept : '' }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50 rounded-b-2xl">
                <button type="button" class="px-4 py-2 text-sm font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 transition cursor-pointer" onclick="document.getElementById('editSectionOverlay').classList.add('hidden')">Cancel</button>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition cursor-pointer">
                    <x-icon name="check2" class="w-4 h-4" /> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Import Result Modal -->
<div id="importResultModal"
     class="fixed inset-0 z-[100] hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm">

    <div class="bg-white w-full max-w-md mx-4 rounded-2xl shadow-2xl overflow-hidden">

        <div class="px-6 py-5 border-b border-slate-100 flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-emerald-100 flex items-center justify-center">
                <x-icon name="check2" class="w-5 h-5 text-emerald-600" />
            </div>

            <div>
                <h3 class="text-lg font-bold text-slate-900">
                    Import Successful
                </h3>
                <p class="text-xs text-slate-500">
                    Master list processing completed.
                </p>
            </div>
        </div>

        <div class="p-6">

            <p id="importResultMessage"
               class="text-sm text-slate-600 mb-5">
                Master list imported successfully.
            </p>

            <div class="grid grid-cols-3 gap-3">

                <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-4 text-center">
                    <div id="createdStudentsCount"
                         class="text-2xl font-bold text-emerald-700">
                        0
                    </div>

                    <div class="text-xs text-emerald-600 mt-1">
                        Created
                    </div>
                </div>

                <div class="bg-blue-50 border border-blue-100 rounded-xl p-4 text-center">
                    <div id="updatedStudentsCount"
                         class="text-2xl font-bold text-blue-700">
                        0
                    </div>

                    <div class="text-xs text-blue-600 mt-1">
                        Updated
                    </div>
                </div>

                <div class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-center">
                    <div id="skippedStudentsCount"
                         class="text-2xl font-bold text-amber-700">
                        0
                    </div>

                    <div class="text-xs text-amber-600 mt-1">
                        Skipped
                    </div>
                </div>

            </div>

        </div>

        <div class="px-6 py-4 bg-slate-50 border-t border-slate-100 flex justify-end">

            <button type="button"
                    id="closeImportResultModal"
                    class="px-5 py-2.5 text-sm font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition">
                Done
            </button>

        </div>

</div>
</div>

<!-- Delete Section Confirmation Modal -->
<div id="deleteSectionOverlay" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden transition-opacity duration-200">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-md mx-4 overflow-hidden transform transition-all duration-200" id="deleteSectionModalContainer">
        <form id="deleteSectionForm" method="POST" action="">
            @csrf
            @method('DELETE')
            <div class="p-6 text-center">
                <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4">
                    <x-icon name="trash" class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-2">Delete Section</h3>
                <p class="text-sm text-slate-500 mb-6">
                    Are you sure you want to delete section <span id="deleteSectionCode" class="font-bold text-slate-800"></span>? This will permanently delete the section.
                </p>
                <div class="flex items-center justify-center gap-3 pt-2">
                    <button type="button" onclick="closeDeleteSectionModal()" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-slate-50 transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 text-white font-semibold text-sm hover:bg-rose-700 transition shadow-sm cursor-pointer">
                        Delete Section
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Master List Import Confirmation Preview Modal -->
<div id="confirmMasterImportModal" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm hidden transition-opacity duration-200">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-2xl mx-4 overflow-hidden flex flex-col max-h-[90vh]" id="confirmMasterImportContainer">
        
        <!-- Modal Header -->
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
                    <x-icon name="upload" class="w-5 h-5" />
                </div>
                <div>
                    <h3 class="text-lg font-bold text-slate-800">Confirm Master List Import</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Please review the file summary before importing to the database.</p>
                </div>
            </div>
            <button type="button" onclick="closeConfirmMasterImportModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                <x-icon name="close" class="w-4 h-4" />
            </button>
        </div>

        <!-- Modal Content -->
        <div class="p-6 overflow-y-auto space-y-5 text-sm">
            <!-- File Info Summary Badges -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-3 p-4 rounded-xl bg-slate-50 border border-slate-200/80">
                <div class="overflow-hidden">
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 block mb-0.5">Selected File</span>
                    <span id="confirmImportFileName" class="font-bold text-slate-800 text-xs sm:text-sm truncate block" title="">-</span>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 block mb-0.5">File Size</span>
                    <span id="confirmImportFileSize" class="font-semibold text-slate-700 text-xs sm:text-sm block">-</span>
                </div>
                <div>
                    <span class="text-xs font-semibold uppercase tracking-wider text-slate-400 block mb-0.5">Records Found</span>
                    <span id="confirmImportRecordCount" class="font-extrabold text-emerald-700 text-xs sm:text-sm block">0 Students</span>
                </div>
            </div>

            <!-- Record Preview Table -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-bold uppercase tracking-wider text-slate-600">File Records Preview (First 5 Rows)</span>
                    <span class="text-xs text-slate-400">Verifying columns and rows</span>
                </div>

                <div class="border border-slate-200 rounded-xl overflow-x-auto bg-white shadow-inner">
                    <table class="w-full text-xs text-left">
                        <thead class="bg-slate-100 text-slate-600 font-bold border-b border-slate-200">
                            <tr>
                                <th class="py-2.5 px-3">#</th>
                                <th class="py-2.5 px-3">Student / Serial No</th>
                                <th class="py-2.5 px-3">Full Name</th>
                                <th class="py-2.5 px-3">Program</th>
                                <th class="py-2.5 px-3">NSTP</th>
                            </tr>
                        </thead>
                        <tbody id="confirmImportPreviewTable" class="divide-y divide-slate-100 text-slate-700">
                            <!-- Rows injected dynamically via JS -->
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Confirmation Notice -->
            <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-900 text-xs flex items-start gap-2.5">
                <x-icon name="check2" class="w-4 h-4 text-emerald-600 shrink-0 mt-0.5" />
                <div>
                    <span class="font-bold">Ready to Import:</span> Click <strong>Confirm & Import Master List</strong> to write these records into the global database.
                </div>
            </div>
        </div>

        <!-- Modal Footer Actions -->
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex items-center justify-end gap-3 rounded-b-2xl">
            <button type="button" onclick="closeConfirmMasterImportModal()" class="px-4 py-2.5 text-sm font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 transition cursor-pointer">
                Cancel
            </button>
            <button type="button" id="confirmMasterImportSubmitBtn" class="inline-flex items-center gap-2 px-5 py-2.5 text-sm font-semibold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition cursor-pointer">
                <x-icon name="upload" class="w-4 h-4" /> Confirm & Import Master List
            </button>
        </div>
    </div>
</div>

@push('scripts')
    @vite(['resources/js/app.js'])
    <script>
        window.switchMainTab = function(tabName) {
            const sectionsContent = document.getElementById('sectionsTabContent');
            const masterlistContent = document.getElementById('masterlistTabContent');
            const btnSections = document.getElementById('tabBtnSections');
            const btnMasterlist = document.getElementById('tabBtnMasterlist');
            const actionNewSec = document.getElementById('actionBtnNewSection');
            const actionImportXlsx = document.getElementById('importXlsxBtn');

            if (tabName === 'sections') {
                sectionsContent.classList.remove('hidden');
                masterlistContent.classList.add('hidden');

                btnSections.className = "px-4 py-1.5 text-xs font-bold rounded-lg transition-all shadow-sm bg-white text-indigo-600 cursor-pointer";
                btnMasterlist.className = "px-4 py-1.5 text-xs font-semibold rounded-lg transition-all text-slate-600 hover:text-slate-900 cursor-pointer";

                if (actionNewSec) actionNewSec.classList.remove('hidden');
                if (actionImportXlsx) actionImportXlsx.classList.add('hidden');
            } else {
                sectionsContent.classList.add('hidden');
                masterlistContent.classList.remove('hidden');

                btnSections.className = "px-4 py-1.5 text-xs font-semibold rounded-lg transition-all text-slate-600 hover:text-slate-900 cursor-pointer";
                btnMasterlist.className = "px-4 py-1.5 text-xs font-bold rounded-lg transition-all shadow-sm bg-white text-indigo-600 cursor-pointer";

                if (actionNewSec) actionNewSec.classList.add('hidden');
                if (actionImportXlsx) actionImportXlsx.classList.remove('hidden');
            }
        };

        let currentMasterlistPage = 1;
        const masterlistPageSize = 10;

        window.changeMasterlistPage = function(delta) {
            currentMasterlistPage += delta;
            if (currentMasterlistPage < 1) currentMasterlistPage = 1;
            if (window.renderMasterlistPage) {
                window.renderMasterlistPage();
            }
        };

        function initMasterlistFilterEngine() {
            const searchInput = document.getElementById('masterlistSearch');
            const progSelect = document.getElementById('masterlistProgFilter');
            const secSelect = document.getElementById('masterlistSecFilter');
            const sySelect = document.getElementById('masterlistSyFilter');
            const ylSelect = document.getElementById('masterlistYlFilter');
            const statusSelect = document.getElementById('masterlistStatusFilter');
            const rows = document.querySelectorAll('.masterlist-row');
            const emptyState = document.getElementById('masterlistFilterEmptyState');
            const countBadge = document.getElementById('masterlistResultCountBadge');

            const pagInfo = document.getElementById('masterlistPaginationInfo');
            const pagIndicator = document.getElementById('masterlistPageIndicator');
            const prevBtn = document.getElementById('masterlistPrevBtn');
            const nextBtn = document.getElementById('masterlistNextBtn');
            const pagContainer = document.getElementById('masterlistPaginationContainer');

            function filterAndPaginate() {
                const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
                const prog = progSelect ? progSelect.value : '';
                const sec = secSelect ? secSelect.value : '';
                const sy = sySelect ? sySelect.value : '';
                const yl = ylSelect ? ylSelect.value : '';
                const status = statusSelect ? statusSelect.value : '';

                const matchingRows = [];

                rows.forEach(r => {
                    const rId = r.getAttribute('data-student-id') || '';
                    const rSerial = r.getAttribute('data-serial-no') || '';
                    const rName = r.getAttribute('data-name') || '';
                    const rProg = r.getAttribute('data-program') || '';
                    const rSec = r.getAttribute('data-section') || '';
                    const rSy = r.getAttribute('data-sy') || '';
                    const rYl = r.getAttribute('data-yl') || '';
                    const rStatus = r.getAttribute('data-status') || '';

                    const matchesSearch = !query || rId.includes(query) || rSerial.includes(query) || rName.includes(query);
                    const matchesProg = !prog || rProg === prog;
                    const matchesSec = !sec || rSec === sec;
                    const matchesSy = !sy || rSy === sy;
                    const matchesYl = !yl || rYl === yl;
                    const matchesStatus = !status || rStatus.toLowerCase() === status.toLowerCase();

                    if (matchesSearch && matchesProg && matchesSec && matchesSy && matchesYl && matchesStatus) {
                        matchingRows.push(r);
                    } else {
                        r.classList.add('hidden');
                    }
                });

                const totalMatching = matchingRows.length;
                const totalPages = Math.max(1, Math.ceil(totalMatching / masterlistPageSize));

                if (currentMasterlistPage > totalPages) {
                    currentMasterlistPage = totalPages;
                }
                if (currentMasterlistPage < 1) {
                    currentMasterlistPage = 1;
                }

                const startIndex = (currentMasterlistPage - 1) * masterlistPageSize;
                const endIndex = startIndex + masterlistPageSize;

                matchingRows.forEach((r, idx) => {
                    if (idx >= startIndex && idx < endIndex) {
                        r.classList.remove('hidden');
                    } else {
                        r.classList.add('hidden');
                    }
                });

                if (countBadge) {
                    countBadge.textContent = `Showing ${totalMatching} students`;
                }

                if (pagInfo) {
                    if (totalMatching === 0) {
                        pagInfo.textContent = `Showing 0 to 0 of 0 students`;
                    } else {
                        pagInfo.textContent = `Showing ${startIndex + 1} to ${Math.min(endIndex, totalMatching)} of ${totalMatching} students`;
                    }
                }

                if (pagIndicator) {
                    pagIndicator.textContent = `Page ${currentMasterlistPage} of ${totalPages}`;
                }

                if (prevBtn) {
                    prevBtn.disabled = currentMasterlistPage <= 1;
                }
                if (nextBtn) {
                    nextBtn.disabled = currentMasterlistPage >= totalPages || totalMatching === 0;
                }

                if (pagContainer) {
                    if (totalMatching === 0) {
                        pagContainer.classList.add('hidden');
                    } else {
                        pagContainer.classList.remove('hidden');
                    }
                }

                if (emptyState) {
                    if (totalMatching === 0 && rows.length > 0) {
                        emptyState.classList.remove('hidden');
                    } else {
                        emptyState.classList.add('hidden');
                    }
                }
            }

            window.renderMasterlistPage = filterAndPaginate;

            function onFilterChange() {
                currentMasterlistPage = 1;
                filterAndPaginate();
            }

            [searchInput, progSelect, secSelect, sySelect, ylSelect, statusSelect].forEach(el => {
                if (el) {
                    el.addEventListener('input', onFilterChange);
                    el.addEventListener('change', onFilterChange);
                }
            });

            filterAndPaginate();
        }

        document.addEventListener('DOMContentLoaded', () => {
            initMasterlistFilterEngine();

            // Wait slightly for Vite to load app.js if it's deferred
            setTimeout(() => {
                if (window.attachEvents) {
                    window.attachEvents();
                }
            }, 100);

            // Program filtering logic
            const buttons = document.querySelectorAll('[data-program-filter]');
            const rows = document.querySelectorAll('tbody tr[data-program]');
            let activeFilter = null;

            buttons.forEach(btn => {
                btn.addEventListener('click', () => {
                    const program = btn.getAttribute('data-program-filter');

                    if (activeFilter === program) {
                        activeFilter = null;
                        // Reset button highlights
                        buttons.forEach(b => {
                            b.classList.remove('ring-2', 'ring-indigo-500', 'border-indigo-300');
                        });
                        // Show all rows
                        rows.forEach(r => r.classList.remove('hidden'));
                    } else {
                        activeFilter = program;
                        // Toggle active highlight class
                        buttons.forEach(b => {
                            if (b.getAttribute('data-program-filter') === program) {
                                b.classList.add('ring-2', 'ring-indigo-500', 'border-indigo-300');
                            } else {
                                b.classList.remove('ring-2', 'ring-indigo-500', 'border-indigo-300');
                            }
                        });
                        // Filter rows
                        rows.forEach(r => {
                            if (r.getAttribute('data-program') === program) {
                                r.classList.remove('hidden');
                            } else {
                                r.classList.add('hidden');
                            }
                        });
                    }
                });
            });
        });

        // Edit Section Modal function
        window.openEditSectionModal = function(id, code, program, schoolYear, room, instructor, semester) {
            const form = document.getElementById('editSectionForm');
            form.action = "{{ route('coordinator.sections.update', ':id') }}".replace(':id', id);

            document.getElementById('editSecCode').value = code;
            document.getElementById('editSecProgram').value = program;
            document.getElementById('editSecSchoolYear').value = schoolYear;
            document.getElementById('editSecRoom').value = room;
            document.getElementById('editSecSemester').value = semester || '1st Semester';

            // Match instructor in dropdown
            const instructorSelect = document.getElementById('editSecInstructor');
            instructorSelect.value = ""; // default
            for (let i = 0; i < instructorSelect.options.length; i++) {
                if (instructorSelect.options[i].value === instructor) {
                    instructorSelect.selectedIndex = i;
                    break;
                }
            }

            document.getElementById('editSectionOverlay').classList.remove('hidden');
        };

        // Delete Section Modal functions
        window.openDeleteSectionModal = function(id, code) {
            const overlay = document.getElementById('deleteSectionOverlay');
            const form = document.getElementById('deleteSectionForm');
            const codeSpan = document.getElementById('deleteSectionCode');

            if (!overlay || !form || !codeSpan) return;

            form.action = "{{ route('coordinator.sections.delete', ':id') }}".replace(':id', id);
            codeSpan.textContent = code;

            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
        };

        window.closeDeleteSectionModal = function() {
            const overlay = document.getElementById('deleteSectionOverlay');
            if (overlay) {
                overlay.classList.add('hidden');
                overlay.classList.remove('flex');
            }
        };

        window.closeConfirmMasterImportModal = function() {
            const modal = document.getElementById('confirmMasterImportModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
            const input = document.getElementById('xlsxImportInput');
            if (input) input.value = '';
        };

        // XLSX File Comparison Logic inside Add New Section Modal
        (function() {
            const classInput = document.getElementById('newSecClassFile');
            const classLabel = document.getElementById('classFileLabel');

            let classStudents = null;

            function normalizeName(name) {
                if (!name) return '';
                return name.toString().toLowerCase().replace(/[^a-z0-9]/g, '').trim();
            }

            function parseExcel(file, callback) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        const data = new Uint8Array(e.target.result);
                        const workbook = XLSX.read(data, {type: 'array'});
                        const sheet = workbook.Sheets[workbook.SheetNames[0]];
                        const rows = XLSX.utils.sheet_to_json(sheet, {header: 1});

                        let nameIdx = -1;
                        let lastNameIdx = -1;
                        let firstNameIdx = -1;
                        let middleNameIdx = -1;
                        let idIdx = -1;
                        let dobIdx = -1;
                        let pobIdx = -1;
                        let genderIdx = -1;
                        let addressIdx = -1;
                        let cellIdx = -1;
                        let emailIdx = -1;
                        let programIdx = -1;

                        // Detect column indices from the first row (headers)
                        const headerRow = rows[0] || [];
                        headerRow.forEach((cell, c) => {
                            if (!cell) return;
                            const val = cell.toString().toLowerCase().trim();
                            if (val === 'last name' || val === 'lastname' || val === 'last') {
                                lastNameIdx = c;
                            } else if (val === 'first name' || val === 'firstname' || val === 'first') {
                                firstNameIdx = c;
                            } else if (val === 'middle name' || val === 'middlename' || val === 'middle') {
                                middleNameIdx = c;
                            } else if (val.includes('name') || val.includes('student') || val.includes('full')) {
                                nameIdx = c;
                            }

                            if (val.includes('no') || val.includes('id') || val.includes('number') || val.includes('code')) {
                                idIdx = c;
                            }
                            if (val === 'dob' || val.includes('birthday') || (val.includes('birth') && !val.includes('place') && !val.includes('pob'))) {
                                dobIdx = c;
                            }
                            if (val === 'pob' || val.includes('place of birth') || val.includes('birthplace') || val.includes('pob')) {
                                pobIdx = c;
                            }
                            if (val === 'gender' || val === 'sex') {
                                genderIdx = c;
                            }
                            if (val.includes('address')) {
                                addressIdx = c;
                            }
                            if (val.includes('cell') || val.includes('phone') || val.includes('contact') || val.includes('mobile')) {
                                cellIdx = c;
                            }
                            if (val.includes('email') || val.includes('gmail')) {
                                emailIdx = c;
                            }
                            if (val.includes('program') || val.includes('course') || val.includes('class')) {
                                programIdx = c;
                            }
                        });

                        // Fallback detection if headers did not match exactly
                        if (nameIdx === -1 && lastNameIdx === -1) {
                            for (let r = 0; r < Math.min(rows.length, 5); r++) {
                                const row = rows[r];
                                if (!row) continue;
                                for (let c = 0; c < row.length; c++) {
                                    const val = (row[c] || '').toString().toLowerCase().trim();
                                    if (val.includes('last')) lastNameIdx = c;
                                    if (val.includes('first')) firstNameIdx = c;
                                    if (val.includes('middle')) middleNameIdx = c;
                                    if (val.includes('name') && nameIdx === -1) nameIdx = c;
                                    if ((val.includes('no') || val.includes('id') || val.includes('num')) && idIdx === -1) idIdx = c;
                                }
                            }
                        }

                        if (nameIdx === -1 && lastNameIdx === -1) nameIdx = 1; // absolute fallback
                        if (idIdx === -1) idIdx = 0;     // absolute fallback

                        const list = [];
                        rows.forEach((row, index) => {
                            if (index === 0) return; // skip header
                            if (!row) return;

                            let nameStr = '';
                            if (lastNameIdx !== -1 || firstNameIdx !== -1) {
                                const last = (row[lastNameIdx] || '').toString().trim();
                                const first = (row[firstNameIdx] || '').toString().trim();
                                const middle = middleNameIdx !== -1 ? (row[middleNameIdx] || '').toString().trim() : '';

                                if (last || first) {
                                    nameStr = last + ', ' + first;
                                    if (middle) {
                                        if (middle.length === 1) {
                                            nameStr += ' ' + middle + '.';
                                        } else {
                                            nameStr += ' ' + middle;
                                        }
                                    }
                                }
                            } else if (nameIdx !== -1) {
                                nameStr = (row[nameIdx] || '').toString().trim();
                            }

                            const idStr = (row[idIdx] || '').toString().trim();

                            if (!nameStr) return;

                            const normalized = nameStr.toLowerCase();
                            if (normalized === 'name' || normalized === 'student name' || normalized === 'full name' || normalized === 'last name') return;
                            if (normalized.includes('anonymous') || normalized.includes('unknown') || normalized.includes('tba') || normalized.includes('vacant')) return;

                            const dobVal = dobIdx !== -1 ? (row[dobIdx] || '').toString().trim() : '';
                            const pobVal = pobIdx !== -1 ? (row[pobIdx] || '').toString().trim() : '';
                            const genderVal = genderIdx !== -1 ? (row[genderIdx] || '').toString().trim() : 'Female';
                            const addressVal = addressIdx !== -1 ? (row[addressIdx] || '').toString().trim() : '';
                            const cellVal = cellIdx !== -1 ? (row[cellIdx] || '').toString().trim() : '';
                            const emailVal = emailIdx !== -1 ? (row[emailIdx] || '').toString().trim() : '';
                            const programVal = programIdx !== -1 ? (row[programIdx] || '').toString().trim() : '';

                            list.push({
                                name: nameStr,
                                studentNo: idStr || ('2024-' + Math.floor(10000 + Math.random() * 90000)),
                                gender: genderVal,
                                dob: dobVal,
                                birthPlace: pobVal,
                                address: addressVal,
                                cellNo: cellVal,
                                email: emailVal,
                                program: programVal
                            });
                        });

                        callback(list);
                    } catch (err) {
                        console.error(err);
                        alert('Error parsing Excel file. Please upload a valid Class List XLSX file.');
                    }
                };
                reader.readAsArrayBuffer(file);
            }

            function runComparison() {
                if (!classStudents) return;

                // Show loading state
                const container = document.getElementById('compareResultContainer');
                const label = document.getElementById('compareCountLabel');
                if (container && label) {
                    container.classList.remove('hidden');
                    label.textContent = "Matching with Master List...";
                }

                // Generate a unique token for this comparison session
                const uploadToken = 'temp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                const selectedProgram = document.getElementById('newSecProgram')?.value || 'CWTS';

                // Call backend database comparison endpoint
                fetch('/api/sections/compare-class-list', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        token: uploadToken,
                        program: selectedProgram,
                        students: classStudents
                    })
                })
                .then(response => response.json())
                .then(res => {
                    if (res.success) {
                        window.modalImportedStudents = res.matched;
                        window.modalUploadToken = uploadToken;

                        if (label) {
                            label.textContent = `${res.matched.length} matched student(s)`;
                        }
                    } else {
                        console.error("Database comparison failed:", res.message);
                        if (label) label.textContent = "Failed to compare with Master List";
                    }
                })
                .catch(err => {
                    console.error("Error connecting to database comparison endpoint:", err);
                    if (label) label.textContent = "Connection to XAMPP database failed";
                });
            }

            const newSecProgSelect = document.getElementById('newSecProgram');
            if (newSecProgSelect) {
                newSecProgSelect.addEventListener('change', () => {
                    if (classStudents) {
                        runComparison();
                    }
                });
            }

            if (classInput) {
                classInput.addEventListener('change', (e) => {
                    const file = e.target.files[0];
                    if (!file) return;
                    classLabel.textContent = file.name;
                    parseExcel(file, (list) => {
                        classStudents = list;
                        runComparison();
                    });
                });
            }
        })();
    </script>
@endpush

@endsection
