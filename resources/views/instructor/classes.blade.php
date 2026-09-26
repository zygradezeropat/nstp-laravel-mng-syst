@extends('layouts.instructor')

@section('title', isset($section) ? "Section {$section->section_name} - Instructor Console" : 'My Classes - Instructor Console')

@section('content')

@if(isset($section))
    <!-- DETAILED ROSTER VIEW FOR SINGLE CLASS -->
    <x-page-header title="Roster: {{ $section->section_name }}" subtitle="Manage grades and student records for this section">
        <x-slot name="actions">
            <button onclick="exportToExcel()" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm rounded-lg bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-sm font-semibold cursor-pointer">
                <x-icon name="download" class="w-4 h-4" /> Export XLSX File
            </button>
            <a href="{{ route('instructor.classes') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition shadow-sm font-semibold cursor-pointer">
                <x-icon name="chevron" class="w-4 h-4 rotate-90" /> Back to Classes
            </a>
        </x-slot>
    </x-page-header>


    <div class="grid grid-cols-1 lg:grid-cols-4 gap-6 mt-6">
        <!-- Class Meta Sidebar -->
        <div class="lg:col-span-1 space-y-5">
            <div class="premium-card p-5 bg-white border border-slate-100 rounded-2xl shadow-sm">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mb-1">Component</div>
                <div class="flex items-center gap-2">
                    <span class="text-sm font-bold text-slate-800">
                        {{ $section->component === 'CWTS' ? 'Civic Welfare Training Service' : ($section->component === 'LTS' ? 'Literacy Training Service' : 'Reserve Officers\' Training Corps') }}
                    </span>
                    <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $section->component === 'CWTS' ? 'bg-indigo-50 text-indigo-700' : ($section->component === 'LTS' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700') }}">
                        {{ $section->component }}
                    </span>
                </div>

                <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mt-5 mb-1">Room</div>
                <div class="text-slate-700 text-sm font-medium flex items-center gap-1.5">
                    <x-icon name="mappin" class="w-4 h-4 text-slate-400" />
                    {{ $section->room ?? 'TBA' }}
                </div>

                <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mt-5 mb-1">Schedule</div>
                <div class="text-slate-700 text-sm font-medium flex items-center gap-1.5">
                    <x-icon name="clock" class="w-4 h-4 text-slate-400" />
                    {{ $section->schedule ?? 'TBA' }}
                </div>

                <div class="text-xs font-bold uppercase tracking-wider text-slate-400 mt-5 mb-1">Academic Year</div>
                <div class="text-slate-700 text-sm font-medium">
                    {{ $section->semester }} Semester &middot; {{ $section->school_year }}
                </div>
            </div>
        </div>

        <!-- Student Roster Table -->
        <div class="lg:col-span-3">
            <x-card>
                <x-slot name="header">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 w-full">
                        <div>
                            <div class="text-slate-900 font-bold tracking-tight text-base">Enrolled Students ({{ $students->total() }})</div>
                            <div class="text-xs text-slate-500 mt-0.5">Roster of students currently assigned to this class</div>
                        </div>
                        
                        <!-- Search Form -->
                        <form method="GET" action="{{ route('instructor.classes') }}" class="relative w-full sm:w-64 font-medium shrink-0">
                            <input type="hidden" name="section" value="{{ $section->section_name }}" />
                            <span class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none text-slate-400">
                                <x-icon name="filter" class="w-4 h-4 text-slate-500" />
                            </span>
                            <input type="text" name="search" autocomplete="off" value="{{ $search ?? '' }}"
                                   class="w-full pl-9 pr-8 py-2 text-sm font-medium rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 focus:outline-none focus:border-indigo-300 transition-colors shadow-sm"
                                   placeholder="Search student no, name..." />
                            @if($search)
                                <a href="{{ route('instructor.classes', ['section' => $section->section_name]) }}" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 transition">
                                    <x-icon name="close" class="w-4 h-4" />
                                </a>
                            @endif
                        </form>
                    </div>
                </x-slot>

                <x-table>
                    <x-slot name="header">
                        <th class="py-3.5 px-4 font-semibold text-slate-600">Student ID</th>
                        <th class="py-3.5 px-4 font-semibold text-slate-600">Name</th>
                        <th class="py-3.5 px-4 font-semibold text-slate-600">Course</th>
                    </x-slot>

                    @forelse($students as $s)
                    <tr class="border-b border-slate-50 hover:bg-indigo-50/20 transition duration-150">
                        <td class="py-3 px-4 text-slate-900 font-semibold">{{ $s->student_no }}</td>
                        <td class="py-3 px-4 text-slate-700 font-medium">{{ $s->name }}</td>
                        <td class="py-3 px-4 text-slate-500 text-sm font-medium">{{ $s->course }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="py-12 text-center text-slate-400 text-sm">
                            <div class="w-12 h-12 bg-slate-50 rounded-full flex items-center justify-center mx-auto text-slate-400 mb-3 shadow-sm">
                                <x-icon name="users" class="w-6 h-6" />
                            </div>
                            No students match the search criteria.
                        </td>
                    </tr>
                    @endforelse
                </x-table>

                @if($students->hasPages())
                    <div class="mt-4 px-4 py-3 border-t border-slate-100 bg-slate-50/50 rounded-b-xl">
                        {{ $students->links() }}
                    </div>
                @endif
            </x-card>
        </div>
    </div>

    <!-- Record Grade Modal -->
    <div id="gradeOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden transition duration-300">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-md mx-4 overflow-hidden transform scale-95 duration-300">
            <form id="gradeForm" method="POST" action="{{ route('instructor.classes.update_grade') }}" data-progress-title="Saving Student Grade" data-progress-subtitle="Updating numerical grade and evaluation remarks in database...">
                @csrf
                <input type="hidden" name="student_id" id="gradeStudentNo" />
                <input type="hidden" name="section_code" value="{{ $section->section_name }}" />

                <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-indigo-600 to-blue-600 text-white">
                    <div>
                        <div class="font-extrabold tracking-tight text-lg">Record Student Grade</div>
                        <div class="text-xs text-white/80 mt-0.5">Section: {{ $section->section_name }}</div>
                    </div>
                    <button type="button" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer" onclick="closeGradeModal()">
                        <x-icon name="close" class="w-5 h-5" />
                    </button>
                </div>
                
                <div class="p-6 space-y-5 text-sm">
                    <div>
                        <div class="text-xs text-slate-400 font-bold uppercase tracking-wider mb-1">Student Name</div>
                        <div class="text-slate-800 font-bold text-base" id="gradeStudentName">-</div>
                    </div>

                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-2">Numerical Grade (1.00 - 5.00)</div>
                        <select name="final_grade" id="gradeFinalGrade" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-300 transition" onchange="autoSelectRemarks()">
                            <option value="">-- No Numerical Grade / TBA --</option>
                            <option value="1.00">1.00 - Excellent</option>
                            <option value="1.25">1.25</option>
                            <option value="1.50">1.50 - Very Good</option>
                            <option value="1.75">1.75</option>
                            <option value="2.00">2.00 - Good</option>
                            <option value="2.25">2.25</option>
                            <option value="2.50">2.50 - Satisfactory</option>
                            <option value="2.75">2.75</option>
                            <option value="3.00">3.00 - Passing</option>
                            <option value="5.00">5.00 - Failed</option>
                        </select>
                    </div>

                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-2">Evaluation Status / Remarks <span class="text-rose-500">*</span></div>
                        <select name="remarks" id="gradeRemarks" required class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-300 transition">
                            <option value="Pending">Pending (No Grade Yet)</option>
                            <option value="Passed">Passed (Completed)</option>
                            <option value="Failed">Failed</option>
                            <option value="Active">Active (Ongoing)</option>
                        </select>
                    </div>
                </div>

                <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50 rounded-b-2xl">
                    <button type="button" class="px-4 py-2 text-sm font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 transition cursor-pointer" onclick="closeGradeModal()">Cancel</button>
                    <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-md transition cursor-pointer">
                        <x-icon name="check2" class="w-4 h-4" /> Save Grade
                    </button>
                </div>
            </form>
        </div>
    </div>

    @push('scripts')
        <script>
            window.openGradeModal = function(studentNo, studentName, finalGrade, status) {
                document.getElementById('gradeStudentNo').value = studentNo;
                document.getElementById('gradeStudentName').innerText = studentName;
                
                // Prepopulate grade
                const gradeSelect = document.getElementById('gradeFinalGrade');
                if (finalGrade && finalGrade !== 'null' && finalGrade !== '') {
                    // Normalize float formatting to 2 decimal places matching seeder
                    const formatted = parseFloat(finalGrade).toFixed(2);
                    gradeSelect.value = formatted;
                } else {
                    gradeSelect.value = "";
                }

                // Prepopulate remarks
                const remarksSelect = document.getElementById('gradeRemarks');
                if (status === 'Passed' || status === 'Completed') {
                    remarksSelect.value = 'Passed';
                } else if (status === 'Failed') {
                    remarksSelect.value = 'Failed';
                } else if (status === 'Dropped') {
                    remarksSelect.value = 'Pending';
                } else {
                    remarksSelect.value = status;
                }

                const overlay = document.getElementById('gradeOverlay');
                overlay.classList.remove('hidden');
            }

            window.closeGradeModal = function() {
                const overlay = document.getElementById('gradeOverlay');
                overlay.classList.add('hidden');
            }

            window.autoSelectRemarks = function() {
                const gradeVal = document.getElementById('gradeFinalGrade').value;
                const remarksSelect = document.getElementById('gradeRemarks');
                if (gradeVal === "") {
                    remarksSelect.value = "Pending";
                } else if (parseFloat(gradeVal) <= 3.0) {
                    remarksSelect.value = "Passed";
                } else if (parseFloat(gradeVal) === 5.0) {
                    remarksSelect.value = "Failed";
                }
            }

            // Injected unpaginated student list for complete Excel export
            const allStudents = @json($allStudents ?? []);

            window.exportToExcel = function() {
                if (!allStudents.length) {
                    showAlertModal('No student records found to export.', 'Export Notice', 'warning');
                    return;
                }

                if (window.showProgressModal) {
                    window.showProgressModal('Exporting Class Roster', 'Compiling student grades into Excel spreadsheet...', 35);
                }

                setTimeout(() => {
                    // Prepare XLSX headers and rows
                    const rows = [
                        ['Student ID', 'Student Name', 'Course', 'Final Grade', 'Status']
                    ];

                    allStudents.forEach(s => {
                        rows.push([
                            s.student_no,
                            s.name,
                            s.course,
                            s.final_grade,
                            s.status
                        ]);
                    });

                    // Generate sheet using SheetJS
                    const wb = XLSX.utils.book_new();
                    const ws = XLSX.utils.aoa_to_sheet(rows);
                    const sectionName = "{{ $section->section_name ?? 'Class' }}";
                    
                    XLSX.utils.book_append_sheet(wb, ws, "Class List");
                    XLSX.writeFile(wb, `Class_List_${sectionName}.xlsx`);

                    if (window.finishProgressModal) {
                        window.finishProgressModal('Export Completed', 'Class roster Excel file generated and downloaded.', 'success');
                    }
                }, 600);
            }
        </script>
    @endpush

@else
    <!-- OVERVIEW LIST GRID VIEW OF ALL ASSIGNED CLASSES -->
    <x-page-header title="My Classes" subtitle="All assigned class sections for the current academic semester">
    </x-page-header>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mt-6">
        @forelse($classes as $c)
        <a href="{{ route('instructor.classes', ['section' => $c->code]) }}" class="premium-card bg-white border border-slate-100 rounded-3xl overflow-hidden shadow-sm hover:shadow-md hover:border-indigo-200 transition duration-300 flex flex-col justify-between group cursor-pointer">
            <div>
                <!-- Gradient Header -->
                <div class="px-6 py-5 bg-gradient-to-r {{ $c->accent }} text-white flex items-center justify-between">
                    <div>
                        <div class="text-lg font-black tracking-tight flex items-center gap-1.5">
                            {{ $c->code }}
                            <x-icon name="chevron" class="w-4 h-4 shrink-0 -rotate-90 text-white/70 group-hover:translate-x-1 transition-transform" />
                        </div>
                        <div class="text-xs text-white/85 font-medium mt-0.5">{{ $c->title }}</div>
                    </div>
                    <span class="inline-flex items-center gap-1 text-[10px] uppercase font-black tracking-wider px-3 py-1 rounded-full bg-white/20 text-white backdrop-blur-sm border border-white/10">
                        {{ $c->badge }}
                    </span>
                </div>

                <!-- Info Grid -->
                <div class="p-6">
                    <div class="grid grid-cols-3 gap-4 text-xs font-semibold text-slate-600">
                        <div class="flex items-center gap-1.5">
                            <x-icon name="users" class="w-4 h-4 text-slate-400 shrink-0" />
                            <span>{{ $c->students }} students</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <x-icon name="mappin" class="w-4 h-4 text-slate-400 shrink-0" />
                            <span class="truncate">{{ $c->room }}</span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <x-icon name="clock" class="w-4 h-4 text-slate-400 shrink-0" />
                            <span class="truncate" title="{{ $c->sched }}">{{ $c->sched }}</span>
                        </div>
                    </div>
                </div>
            </div>


        </a>
        @empty
        <div class="md:col-span-2 py-16 text-center bg-white border border-slate-100 rounded-3xl p-6 shadow-sm">
            <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto text-slate-400 mb-4 shadow-sm">
                <x-icon name="book" class="w-8 h-8" />
            </div>
            <div class="text-slate-900 font-bold tracking-tight text-base">No Assigned Classes</div>
            <div class="text-slate-400 text-xs mt-1 max-w-sm mx-auto">You do not currently have any class sections assigned to your account for this semester. Please contact the NSTP Coordinator.</div>
        </div>
        @endforelse
    </div>
@endif

@endsection
