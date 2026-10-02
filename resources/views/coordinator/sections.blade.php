@extends('layouts.coordinator')

@section('title', 'Sections - Coordinator Dashboard')


@section('content')


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

            <input type="file" id="xlsxImportInput" accept=".xlsx,.xls,.xlsb,.xlsm,.csv,.ods,.tsv,.txt,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,text/csv,application/vnd.oasis.opendocument.spreadsheet" class="hidden" />

            <!-- Action Button for Sections Tab -->
            <button id="actionBtnNewSection" onclick="document.getElementById('newSectionOverlay').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition cursor-pointer">
                <x-icon name="plus" class="w-4 h-4" /> New Section
            </button>

            <!-- Action Button for Masterlist Tab (Moved per instructions) -->
            <button id="importXlsxBtn" class="hidden inline-flex items-center gap-1.5 px-3.5 py-2 text-xs font-semibold rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition shadow-sm cursor-pointer">
                <x-icon name="upload" class="w-4 h-4" /> Import Master List Sheet File
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
    <x-card title="Student Masterlist">
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
                <th class="py-2 px-3 font-medium text-right">Actions</th>
            </x-slot>

            @forelse($masterStudents as $stu)
            <tr class="masterlist-row border-b border-slate-50 hover:bg-indigo-50/30 transition cursor-pointer"
                onclick="openMasterlistStudentModal({{ json_encode($stu) }})"
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
                <td class="py-3 px-3 text-right" onclick="event.stopPropagation();">
                    <button type="button" onclick="openMasterlistStudentModal({{ json_encode($stu) }})" class="text-slate-400 hover:text-indigo-600 p-1 transition cursor-pointer mr-1" title="View / Edit Full Details"><x-icon name="edit" class="w-4 h-4" /></button>
                    <button type="button" onclick="openDeleteStudentModal('{{ $stu->db_id }}', '{{ addslashes($stu->name) }}', '{{ $stu->student_id }}')" class="text-slate-400 hover:text-rose-600 p-1 transition cursor-pointer" title="Delete Student Record"><x-icon name="trash" class="w-4 h-4" /></button>
                </td>
            </tr>
            @empty
            <tr id="masterlistEmptyRow"><td colspan="9" class="py-8 text-center text-slate-400 text-sm">No masterlist students found in database.</td></tr>
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
                    <div class="text-xs font-bold text-slate-700 uppercase tracking-wider">Compare list with Master List (All Sheet Formats)</div>
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 flex items-center gap-1">
                            <span>Class List Sheet File</span>
                            <span class="text-rose-500">*</span>
                        </div>
                        <label class="block border-2 border-dashed border-slate-200 hover:border-indigo-300 rounded-xl p-4 text-center bg-slate-50 hover:bg-slate-100/50 transition cursor-pointer">
                            <x-icon name="upload" class="w-5 h-5 text-slate-400 mx-auto" />
                            <span id="classFileLabel" class="text-xs text-slate-550 mt-1 block truncate">Upload Class List Sheet</span>
                            <input type="file" id="newSecClassFile" accept=".xlsx,.xls,.xlsb,.xlsm,.csv,.ods,.tsv,.txt,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet,application/vnd.ms-excel,text/csv,application/vnd.oasis.opendocument.spreadsheet" class="hidden" />
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
        <form id="editSectionForm" method="POST" action="" data-progress-title="Updating Section" data-progress-subtitle="Saving modified section configurations to database...">
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
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between bg-slate-50 rounded-b-2xl">
                <button type="button" id="editSecDeleteBtn" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-xl border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100 transition cursor-pointer">
                    <x-icon name="trash" class="w-4 h-4" /> Delete Section
                </button>
                <div class="flex items-center gap-3">
                    <button type="button" class="px-4 py-2 text-sm font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 transition cursor-pointer" onclick="document.getElementById('editSectionOverlay').classList.add('hidden')">Cancel</button>
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition cursor-pointer">
                        <x-icon name="check2" class="w-4 h-4" /> Save Changes
                    </button>
                </div>
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

<!-- Delete Student Confirmation Modal -->
<div id="deleteStudentOverlay" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden transition-opacity duration-200">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-md mx-4 overflow-hidden transform transition-all duration-200" id="deleteStudentModalContainer">
        <form id="deleteStudentForm" method="POST" action="" data-progress-title="Deleting Student Record" data-progress-subtitle="Removing student record from system database...">
            @csrf
            @method('DELETE')
            <div class="p-6 text-center">
                <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4">
                    <x-icon name="trash" class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-2">Delete Student Record</h3>
                <p class="text-sm text-slate-500 mb-6">
                    Are you sure you want to delete <span id="deleteStudentName" class="font-bold text-slate-800"></span> (<span id="deleteStudentId" class="font-semibold text-slate-700"></span>)?
                </p>
                <div class="flex items-center justify-center gap-3 pt-2">
                    <button type="button" onclick="closeDeleteStudentModal()" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-slate-50 transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 text-white font-semibold text-sm hover:bg-rose-700 transition shadow-sm cursor-pointer">
                        Delete Record
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Delete Section Confirmation Modal -->
<div id="deleteSectionOverlay" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden transition-opacity duration-200">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-md mx-4 overflow-hidden transform transition-all duration-200" id="deleteSectionModalContainer">
        <form id="deleteSectionForm" method="POST" action="" data-progress-title="Deleting Section" data-progress-subtitle="Removing section and unlinking enrolled students...">
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

            const delBtn = document.getElementById('editSecDeleteBtn');
            if (delBtn) {
                delBtn.onclick = function() {
                    document.getElementById('editSectionOverlay').classList.add('hidden');
                    openDeleteSectionModal(id, code);
                };
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

        window.openDeleteStudentModal = function(db_id, name, student_id) {
            const overlay = document.getElementById('deleteStudentOverlay');
            const form = document.getElementById('deleteStudentForm');
            const nameSpan = document.getElementById('deleteStudentName');
            const idSpan = document.getElementById('deleteStudentId');

            if (!overlay || !form || !nameSpan || !idSpan) return;

            form.action = "{{ route('coordinator.sections.remove_student', ['ALL', ':id']) }}".replace(':id', db_id);
            nameSpan.textContent = name;
            idSpan.textContent = student_id;

            overlay.classList.remove('hidden');
            overlay.classList.add('flex');
        };

        window.closeDeleteStudentModal = function() {
            const overlay = document.getElementById('deleteStudentOverlay');
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
                        let headerRowIndex = -1;

                        // Dynamically scan the top 35 rows to locate the actual column header row
                        for (let r = 0; r < Math.min(rows.length, 35); r++) {
                            const row = rows[r];
                            if (!row || !Array.isArray(row)) continue;

                            let tLastName = -1, tFirstName = -1, tMiddleName = -1, tName = -1, tId = -1;
                            let tDob = -1, tPob = -1, tGender = -1, tAddr = -1, tCell = -1, tEmail = -1, tProg = -1;

                            row.forEach((cell, c) => {
                                if (cell === null || cell === undefined) return;
                                const val = cell.toString().toLowerCase().trim();
                                if (!val) return;

                                if (val === 'last name' || val === 'lastname' || val === 'last_name' || val === 'last') {
                                    tLastName = c;
                                } else if (val === 'first name' || val === 'firstname' || val === 'first_name' || val === 'first') {
                                    tFirstName = c;
                                } else if (val === 'middle name' || val === 'middlename' || val === 'middle_name' || val === 'middle') {
                                    tMiddleName = c;
                                } else if (val === 'name' || val === 'student name' || val === 'full name' || val === 'student_name' || val === 'fullname' || val === 'complete name') {
                                    tName = c;
                                } else if (val.includes('name') && !val.includes('middle') && !val.includes('first') && !val.includes('last') && tName === -1) {
                                    tName = c;
                                }

                                if (val === 'student id' || val === 'student_id' || val === 'student no' || val === 'student_no' || val === 'student number' || val === 'student_number' || val === 'serial no' || val === 'serial_no' || val === 'serial number' || val === 'id' || val === 'id_number' || val === 'id number' || val === 'id_no' || val === 'id no') {
                                    tId = c;
                                } else if ((val.includes('no') || val.includes('id') || val.includes('number') || val.includes('code')) && tId === -1 && !val.includes('cell') && !val.includes('phone') && !val.includes('contact') && !val.includes('mobile')) {
                                    tId = c;
                                }

                                if (val === 'dob' || val.includes('birthday') || (val.includes('birth') && !val.includes('place') && !val.includes('pob'))) {
                                    tDob = c;
                                }
                                if (val === 'pob' || val.includes('place of birth') || val.includes('birthplace') || val.includes('pob')) {
                                    tPob = c;
                                }
                                if (val === 'gender' || val === 'sex') {
                                    tGender = c;
                                }
                                if (val.includes('address')) {
                                    tAddr = c;
                                }
                                if (val.includes('cell') || val.includes('phone') || val.includes('contact') || val.includes('mobile')) {
                                    tCell = c;
                                }
                                if (val.includes('email') || val.includes('gmail')) {
                                    tEmail = c;
                                }
                                if (val.includes('program') || val.includes('course') || val.includes('class')) {
                                    tProg = c;
                                }
                            });

                            if (tLastName !== -1 || tFirstName !== -1 || tName !== -1) {
                                lastNameIdx = tLastName;
                                firstNameIdx = tFirstName;
                                middleNameIdx = tMiddleName;
                                nameIdx = tName;
                                idIdx = tId;
                                dobIdx = tDob;
                                pobIdx = tPob;
                                genderIdx = tGender;
                                addressIdx = tAddr;
                                cellIdx = tCell;
                                emailIdx = tEmail;
                                programIdx = tProg;
                                headerRowIndex = r;
                                break;
                            }
                        }

                        // Absolute fallback ONLY if no header row was detected at all
                        if (headerRowIndex === -1) {
                            if (nameIdx === -1 && lastNameIdx === -1) nameIdx = 1;
                        }

                        const list = [];
                        rows.forEach((row, index) => {
                            if (index <= headerRowIndex) return; // skip headers and metadata
                            if (!row || !Array.isArray(row)) return;

                            let nameStr = '';
                            if (lastNameIdx !== -1 || firstNameIdx !== -1) {
                                const last = lastNameIdx !== -1 ? (row[lastNameIdx] || '').toString().trim() : '';
                                const first = firstNameIdx !== -1 ? (row[firstNameIdx] || '').toString().trim() : '';
                                const middle = middleNameIdx !== -1 ? (row[middleNameIdx] || '').toString().trim() : '';

                                if (last || first) {
                                    nameStr = last;
                                    if (first) nameStr = nameStr ? (nameStr + ', ' + first) : first;
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

                            const idStr = idIdx !== -1 ? (row[idIdx] || '').toString().trim() : '';

                            if (!nameStr && !idStr) return;

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
                                studentNo: idStr,
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
                        alert('Error parsing spreadsheet file. Please upload a valid Class List sheet file.');
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

        // =========================================================================
        // STUDENT MASTERLIST DETAILS & EDIT MODAL JS HANDLERS
        // =========================================================================
        let isMasterlistStudentEditMode = false;

        window.setMasterlistModalMode = function(isEditing) {
            isMasterlistStudentEditMode = isEditing;
            const form = document.getElementById('masterlistStudentEditForm');
            if (!form) return;

            const inputs = form.querySelectorAll('input, select');
            inputs.forEach(el => {
                if (el.id === 'editStuDbId') return;
                el.disabled = !isEditing;
                if (!isEditing) {
                    el.classList.add('bg-slate-50/80', 'cursor-not-allowed', 'border-slate-200');
                    el.classList.remove('bg-white', 'border-slate-300');
                } else {
                    el.classList.remove('bg-slate-50/80', 'cursor-not-allowed', 'border-slate-200');
                    el.classList.add('bg-white', 'border-slate-300');
                }
            });

            const btn = document.getElementById('saveMasterStudentBtn');
            if (btn) {
                if (!isEditing) {
                    btn.innerHTML = `<svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg> Edit Record`;
                    btn.className = "px-4 py-2 text-xs font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition flex items-center gap-1.5 cursor-pointer";
                } else {
                    btn.innerHTML = `<svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Save Changes`;
                    btn.className = "px-4 py-2 text-xs font-semibold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-sm transition flex items-center gap-1.5 cursor-pointer";
                }
            }
        };

        window.openMasterlistStudentModal = function(stu) {
            if (!stu) return;

            document.getElementById('editStuDbId').value = stu.db_id || '';
            document.getElementById('editStuStudentId').value = stu.raw_student_id || (stu.student_id !== 'N/A' ? stu.student_id : '');
            document.getElementById('editStuSerialNo').value = stu.raw_serial_no || (stu.serial_no !== 'N/A' ? stu.serial_no : '');
            document.getElementById('editStuFirstName').value = stu.first_name || '';
            document.getElementById('editStuMiddleName').value = stu.middle_name || '';
            document.getElementById('editStuLastName').value = stu.last_name || '';
            document.getElementById('editStuCourse').value = stu.course !== 'N/A' ? stu.course : '';
            document.getElementById('editStuComponent').value = stu.program || 'CWTS';
            document.getElementById('editStuYearLevel').value = stu.raw_year_level || 1;
            document.getElementById('editStuStatus').value = stu.status || 'Active';
            document.getElementById('editStuSex').value = stu.sex || '';
            document.getElementById('editStuDob').value = stu.dob || '';
            document.getElementById('editStuBirthPlace').value = stu.birth_place || '';
            document.getElementById('editStuContact').value = stu.cell_no || '';
            document.getElementById('editStuEmail').value = stu.email !== 'N/A' ? stu.email : '';
            document.getElementById('editStuAddress').value = stu.address || '';

            const sub = document.getElementById('modalStudentHeaderSubtitle');
            if (sub) {
                sub.textContent = stu.student_id && stu.student_id !== 'N/A' ? `Student ID: ${stu.student_id}` : `Masterlist Record #${stu.db_id}`;
            }

            // Start in View Only mode
            window.setMasterlistModalMode(false);

            const modal = document.getElementById('masterlistStudentModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
            }
        };

        window.closeMasterlistStudentModal = function() {
            const modal = document.getElementById('masterlistStudentModal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        };

        window.handleMasterlistFooterButtonClick = function() {
            if (!isMasterlistStudentEditMode) {
                window.setMasterlistModalMode(true);
            } else {
                window.submitEditMasterlistStudent();
            }
        };

        window.submitEditMasterlistStudent = function() {
            const dbId = document.getElementById('editStuDbId').value;
            if (!dbId) return;

            const firstName = document.getElementById('editStuFirstName').value.trim();
            const lastName = document.getElementById('editStuLastName').value.trim();

            if (!firstName || !lastName) {
                alert('First name and Last name are required.');
                return;
            }

            const payload = {
                student_no: document.getElementById('editStuStudentId').value.trim() || null,
                serial_no: document.getElementById('editStuSerialNo').value.trim() || null,
                first_name: firstName,
                middle_name: document.getElementById('editStuMiddleName').value.trim() || null,
                last_name: lastName,
                name: `${lastName}, ${firstName} ${document.getElementById('editStuMiddleName').value.trim()}`.trim(),
                course: document.getElementById('editStuCourse').value.trim() || null,
                program: document.getElementById('editStuCourse').value.trim() || null,
                component: document.getElementById('editStuComponent').value,
                year_level: parseInt(document.getElementById('editStuYearLevel').value) || 1,
                enrollment_status: document.getElementById('editStuStatus').value,
                gender: document.getElementById('editStuSex').value || null,
                dob: document.getElementById('editStuDob').value || null,
                birth_place: document.getElementById('editStuBirthPlace').value.trim() || null,
                cell_no: document.getElementById('editStuContact').value.trim() || null,
                email: document.getElementById('editStuEmail').value.trim() || null,
                address: document.getElementById('editStuAddress').value.trim() || null,
            };

            const saveBtn = document.getElementById('saveMasterStudentBtn');
            const origText = saveBtn ? saveBtn.innerHTML : 'Save Changes';
            if (saveBtn) {
                saveBtn.disabled = true;
                saveBtn.innerHTML = 'Saving...';
            }

            fetch(`/api/students/${dbId}`, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify(payload)
            })
            .then(res => res.json().then(data => ({ status: res.status, body: data })))
            .then(({ status, body }) => {
                if (status >= 400) {
                    throw new Error(body.message || 'Failed to update student record.');
                }

                closeMasterlistStudentModal();
                if (window.showToast) {
                    window.showToast('Student record updated successfully!', 'success', 'Updated');
                }
                setTimeout(() => window.location.reload(), 600);
            })
            .catch(err => {
                alert('Error updating student: ' + err.message);
                console.error(err);
            })
            .finally(() => {
                if (saveBtn) {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = origText;
                }
            });
        };
    </script>
@endpush

<!-- ========================================================================= -->
<!-- STUDENT MASTERLIST PROFILE & EDIT MODAL -->
<!-- ========================================================================= -->
<div id="masterlistStudentModal" class="fixed inset-0 z-50 hidden items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-2xl overflow-hidden transform transition-all my-8">
        <!-- Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                    <x-icon name="user" class="w-5 h-5" />
                </div>
                <div>
                    <h3 class="font-bold text-slate-800 text-sm sm:text-base">Student Masterlist Profile</h3>
                    <p class="text-xs text-slate-500" id="modalStudentHeaderSubtitle">View and edit student information</p>
                </div>
            </div>
            <button type="button" onclick="closeMasterlistStudentModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                <x-icon name="x" class="w-5 h-5" />
            </button>
        </div>

        <!-- Body Form -->
        <form id="masterlistStudentEditForm" onsubmit="event.preventDefault();" class="p-6 space-y-4 max-h-[72vh] overflow-y-auto">
            <input type="hidden" id="editStuDbId" />

            <!-- Section 1: Identifiers -->
            <div class="space-y-1">
                <h4 class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">1. Student Identifiers</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Student ID / Number</label>
                        <input type="text" id="editStuStudentId" placeholder="e.g. 2025-00059" class="w-full text-xs font-mono rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Serial Number</label>
                        <input type="text" id="editStuSerialNo" placeholder="e.g. ROTC-2025-1234" class="w-full text-xs font-mono rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition" />
                    </div>
                </div>
            </div>

            <!-- Section 2: Full Name -->
            <div class="space-y-1 pt-2 border-t border-slate-100">
                <h4 class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">2. Personal Name</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">First Name <span class="text-rose-500">*</span></label>
                        <input type="text" id="editStuFirstName" required placeholder="First Name" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Middle Name</label>
                        <input type="text" id="editStuMiddleName" placeholder="Middle Name" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Last Name / Surname <span class="text-rose-500">*</span></label>
                        <input type="text" id="editStuLastName" required placeholder="Last Name" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition" />
                    </div>
                </div>
            </div>

            <!-- Section 3: Academic & NSTP Program -->
            <div class="space-y-1 pt-2 border-t border-slate-100">
                <h4 class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">3. Program & Academic Info</h4>
                <div class="grid grid-cols-1 sm:grid-cols-4 gap-3 pt-1">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">College Course</label>
                        <input type="text" id="editStuCourse" placeholder="e.g. BSIT, BSFT" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">NSTP Component</label>
                        <select id="editStuComponent" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition bg-white cursor-pointer">
                            <option value="CWTS">CWTS</option>
                            <option value="LTS">LTS</option>
                            <option value="ROTC">ROTC</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Year Level</label>
                        <select id="editStuYearLevel" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition bg-white cursor-pointer">
                            <option value="1">1st Year</option>
                            <option value="2">2nd Year</option>
                            <option value="3">3rd Year</option>
                            <option value="4">4th Year</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Status</label>
                        <select id="editStuStatus" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition bg-white cursor-pointer">
                            <option value="Active">Active</option>
                            <option value="Completed">Completed</option>
                            <option value="Passed">Passed</option>
                            <option value="Failed">Failed</option>
                            <option value="Pending">Pending</option>
                            <option value="Dropped">Dropped</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Section 4: Personal Demographics -->
            <div class="space-y-1 pt-2 border-t border-slate-100">
                <h4 class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">4. Demographics</h4>
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 pt-1">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Gender / Sex</label>
                        <select id="editStuSex" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition bg-white cursor-pointer">
                            <option value="">Select Gender</option>
                            <option value="Female">Female</option>
                            <option value="Male">Male</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Date of Birth</label>
                        <input type="date" id="editStuDob" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Place of Birth</label>
                        <input type="text" id="editStuBirthPlace" placeholder="City / Hospital" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition" />
                    </div>
                </div>
            </div>

            <!-- Section 5: Contact & Address -->
            <div class="space-y-1 pt-2 border-t border-slate-100">
                <h4 class="text-[11px] font-bold uppercase tracking-wider text-indigo-600">5. Contact & Address</h4>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-1">
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Cell / Contact #</label>
                        <input type="text" id="editStuContact" placeholder="09xxxxxxxxx" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition" />
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-slate-700 mb-1">Email Address</label>
                        <input type="email" id="editStuEmail" placeholder="student@example.com" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition" />
                    </div>
                </div>
                <div class="pt-2">
                    <label class="block text-xs font-semibold text-slate-700 mb-1">Residential / Complete Address</label>
                    <input type="text" id="editStuAddress" placeholder="Purok, Barangay, City, Province" class="w-full text-xs rounded-xl border border-slate-200 px-3 py-2 focus:ring-2 focus:ring-indigo-500/20 focus:border-indigo-500 outline-none transition" />
                </div>
            </div>
        </form>

        <!-- Footer -->
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 flex items-center justify-end gap-3">
            <button type="button" onclick="closeMasterlistStudentModal()" class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 transition cursor-pointer">
                Cancel
            </button>
            <button type="button" id="saveMasterStudentBtn" onclick="handleMasterlistFooterButtonClick()" class="px-4 py-2 text-xs font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition flex items-center gap-1.5 cursor-pointer">
                Edit Record
            </button>
        </div>
    </div>
</div>

@endsection
