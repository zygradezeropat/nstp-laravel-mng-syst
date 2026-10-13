@extends('layouts.coordinator')

@section('title', 'Instructor Profile - DNSC NSTP Portal')

@section('content')

<x-page-header title="Instructor Profile: {{ $instructor->name }}" subtitle="Detailed review of instructor assignments, handled sections, and active student enrollments">
    <x-slot name="actions">
        <a href="{{ route('coordinator.instructors') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition shadow-sm font-semibold">
            <x-icon name="chevron" class="w-4 h-4 rotate-90" /> Back to Instructors
        </a>
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
    <!-- Left Column: Instructor Profile Information Card -->
    <div class="lg:col-span-1">
        <x-card title="Personnel Details">
            <div class="p-5 space-y-6">
                <!-- Avatar / Visual Header -->
                <div class="flex flex-col items-center text-center">
                    <div class="w-20 h-20 rounded-full bg-indigo-50 border-2 border-indigo-100 flex items-center justify-center text-indigo-600 mb-3 shadow-inner">
                        <x-icon name="users" class="w-10 h-10" />
                    </div>
                    <h3 class="text-slate-900 font-bold text-lg tracking-tight">{{ $instructor->name }}</h3>
                    <span class="text-xs px-2.5 py-1 mt-1 rounded-full font-semibold {{ $instructor->role === 'rotc' ? 'bg-rose-50 text-rose-700 border border-rose-100' : 'bg-indigo-50 text-indigo-700 border border-indigo-100' }}">
                        {{ $instructor->role === 'rotc' ? 'ROTC Officer' : 'CWTS/LTS Instructor' }}
                    </span>
                </div>

                <div class="border-t border-slate-100 pt-5 space-y-4 text-xs">
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium uppercase tracking-wider">Email Address</span>
                        <span class="text-slate-800 font-semibold select-all">{{ $instructor->email }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium uppercase tracking-wider">Department</span>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $instructor->dept === 'CWTS' ? 'bg-indigo-50 text-indigo-700' : ($instructor->dept === 'LTS' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700') }}">
                            {{ $instructor->dept }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium uppercase tracking-wider">Phone Contact</span>
                        <span class="text-slate-800 font-semibold">{{ $instructor->contact ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-slate-500 font-medium uppercase tracking-wider">Status</span>
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $instructor->status === 'Active' ? 'bg-emerald-50 text-emerald-700 font-bold' : 'bg-slate-100 text-slate-600 font-bold' }}">
                            {{ $instructor->status }}
                        </span>
                    </div>
                </div>
            </div>
        </x-card>
    </div>

    <!-- Right Column: Handled Sections and Students -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Card 1: Handled Sections -->
        <x-card title="Handled Class Sections">
            <x-table>
                <x-slot name="header">
                    <th class="py-2 px-3 font-medium">Section Name</th>
                    <th class="py-2 px-3 font-medium">Component</th>
                    <th class="py-2 px-3 font-medium">School Year</th>
                    <th class="py-2 px-3 font-medium">Room</th>
                </x-slot>

                @forelse($sections as $sec)
                <tr class="border-b border-slate-50 hover:bg-indigo-50/40 cursor-pointer transition" onclick="window.location.href='{{ route('coordinator.section_students', $sec->section_name) }}'">
                    <td class="py-3 px-3 text-slate-900 font-semibold">{{ $sec->section_name }}</td>
                    <td class="py-3 px-3 text-slate-600">
                        <span class="text-xs px-2 py-0.5 rounded-full {{ $sec->component === 'CWTS' ? 'bg-indigo-50 text-indigo-700' : ($sec->component === 'LTS' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700') }}">
                            {{ $sec->component }}
                        </span>
                    </td>
                    <td class="py-3 px-3 text-slate-600">{{ $sec->school_year }}</td>
                    <td class="py-3 px-3 text-slate-600">{{ $sec->room }}</td>
                </tr>
                @empty
                <tr><td colspan="4" class="py-8 text-center text-slate-400 text-sm">No sections assigned to this instructor.</td></tr>
                @endforelse
            </x-table>
            @if($sections->hasPages())
                <div class="mt-4 px-4 py-3 border-t border-slate-100 bg-slate-50/50 rounded-b-xl">
                    {{ $sections->appends(request()->except('sections_page'))->links() }}
                </div>
            @endif
        </x-card>

        <!-- Card 2: Handled Students -->
        <x-card title="Handled Students ({{ $students->total() }})">
            <x-table>
                <x-slot name="header">
                    <th class="py-2 px-3 font-medium">Student ID</th>
                    <th class="py-2 px-3 font-medium">Name</th>
                    <th class="py-2 px-3 font-medium">Course</th>
                    <th class="py-2 px-3 font-medium">Section</th>
                    <th class="py-2 px-3 font-medium">Status</th>
                    <th class="py-2 px-3 font-medium text-right">Actions</th>
                </x-slot>

                @forelse($students as $s)
                <tr class="border-b border-slate-50 hover:bg-indigo-50/40 transition">
                    <td class="py-3 px-3 text-slate-900 font-semibold">{{ $s->id }}</td>
                    <td class="py-3 px-3 text-slate-700 font-medium">{{ $s->name }}</td>
                    <td class="py-3 px-3 text-slate-600">{{ $s->course }}</td>
                    <td class="py-3 px-3 text-indigo-600 font-semibold">
                        <span class="bg-indigo-50 px-2.5 py-0.5 rounded-lg border border-indigo-100 hover:bg-indigo-100 transition cursor-pointer" onclick="window.location.href='{{ route('coordinator.section_students', $s->section) }}'">
                            {{ $s->section }}
                        </span>
                    </td>
                    <td class="py-3 px-3">
                        <span class="text-xs px-2 py-0.5 rounded-full {{ in_array($s->status, ['Passed', 'Completed']) ? 'bg-emerald-50 text-emerald-700' : ($s->status === 'Failed' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700') }}">
                            {{ $s->status }}
                        </span>
                    </td>
                    <td class="py-3 px-3 text-right" onclick="event.stopPropagation();">
                        <button type="button" onclick="openEditStudentModal('{{ $s->db_id }}', '{{ $s->id }}', '{{ addslashes($s->name) }}', '{{ $s->email }}', '{{ $s->course }}', '{{ $s->section }}', '{{ $s->status }}', '{{ $s->gender }}', '{{ $s->dob }}', '{{ addslashes($s->birth_place) }}', '{{ $s->cell_no }}', '{{ addslashes($s->address) }}', '{{ $s->final_grade }}')" class="text-slate-400 hover:text-indigo-600 p-1 transition cursor-pointer" title="Edit Student Profile">
                            <x-icon name="pencil" class="w-4 h-4" />
                        </button>
                        <button type="button" onclick="openDeleteStudentModal('{{ $s->db_id }}', '{{ addslashes($s->name) }}', '{{ $s->id }}', '{{ $s->section }}')" class="text-slate-400 hover:text-rose-600 p-1 transition cursor-pointer" title="Delete Student">
                            <x-icon name="trash" class="w-4 h-4" />
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="py-8 text-center text-slate-400 text-sm">No students currently handled.</td></tr>
                @endforelse
            </x-table>
            @if($students->hasPages())
                <div class="mt-4 px-4 py-3 border-t border-slate-100 bg-slate-50/50 rounded-b-xl">
                    {{ $students->appends(request()->except('students_page'))->links() }}
                </div>
            @endif
        </x-card>
    </div>
</div>

<!-- Edit Student Modal -->
<div id="editStudentOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-lg mx-4">
        <form id="editStudentForm" method="POST" action="" data-progress-title="Updating Student Record" data-progress-subtitle="Saving changes to student profile and status...">
            @csrf
            @method('PUT')
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <div class="text-slate-900 font-bold tracking-tight text-lg">Edit Student Record</div>
                    <div class="text-xs text-slate-500 mt-0.5">Update credentials and section assignment</div>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition cursor-pointer" onclick="document.getElementById('editStudentOverlay').classList.add('hidden')">
                    <x-icon name="close" class="w-4 h-4" />
                </button>
            </div>
            <div class="p-6 space-y-4 text-sm max-h-[75vh] overflow-y-auto">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Student ID <span class="text-rose-500">*</span></div>
                        <input type="text" name="student_no" id="editStudNo" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Course / Program <span class="text-rose-500">*</span></div>
                        <input type="text" name="course" id="editStudCourse" required placeholder="e.g. BSIT" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                </div>
                <div>
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Full Name <span class="text-rose-500">*</span></div>
                    <input type="text" name="name" id="editStudName" required placeholder="e.g. Dela Cruz, Juan" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 flex items-center gap-1">
                            <span>Assigned Section</span>
                            <span class="text-rose-500">*</span>
                        </div>
                        <select name="section" id="editStudSection" required class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition">
                            @forelse($instructorAssignedSections ?? [] as $sec)
                                <option value="{{ $sec->section_name }}">{{ $sec->section_name }} ({{ $sec->component }})</option>
                            @empty
                                <option value="">No sections assigned to this instructor</option>
                            @endforelse
                        </select>
                        <div class="text-[10px] text-slate-400 mt-1 leading-tight">
                            Only sections assigned to {{ $instructor->name }} ({{ $instructor->dept }}) are selectable.
                        </div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Gender</div>
                        <select name="gender" id="editStudGender" class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Date of Birth</div>
                        <input type="date" name="dob" id="editStudDob" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Place of Birth</div>
                        <input type="text" name="birth_place" id="editStudBirthPlace" placeholder="e.g. Tagum City" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Cell Number</div>
                        <input type="text" name="cell_no" id="editStudCellNo" placeholder="e.g. 09123456789" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Email</div>
                        <input type="email" name="email" id="editStudEmail" placeholder="e.g. juan.delacruz@dnsc.edu.ph" class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                </div>
                <div>
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Residential Address</div>
                    <textarea name="address" id="editStudAddress" placeholder="e.g. Apokon, Tagum City" rows="2" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition"></textarea>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 flex items-center">Status <span class="text-rose-500 ml-0.5">*</span></div>
                        <select name="enrollment_status" id="editStudStatus" required class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition">
                            <option value="Active">Active</option>
                            <option value="Passed">Passed (Completed)</option>
                            <option value="Failed">Failed</option>
                            <option value="Pending">Pending</option>
                            <option value="Dropped">Dropped</option>
                        </select>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 flex items-center">Final Grade</div>
                        <input type="number" step="0.01" name="final_grade" id="editStudGrade" min="1.00" max="5.00" placeholder="e.g. 1.5" class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50 rounded-b-2xl">
                <button type="button" class="px-4 py-2 text-sm font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 transition cursor-pointer" onclick="document.getElementById('editStudentOverlay').classList.add('hidden')">Cancel</button>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition cursor-pointer">
                    <x-icon name="check2" class="w-4 h-4" /> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Student Confirmation Modal -->
<div id="deleteStudentOverlay" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden transition-opacity duration-200">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-md mx-4 overflow-hidden transform transition-all duration-200" id="deleteStudentModalContainer">
        <form id="deleteStudentForm" method="POST" action="" data-progress-title="Deleting Student Record" data-progress-subtitle="Removing student from instructor's section...">
            @csrf
            @method('DELETE')
            <div class="p-6 text-center">
                <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4">
                    <x-icon name="trash" class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-2">Delete Student Record</h3>
                <p class="text-sm text-slate-500 mb-6">
                    Are you sure you want to delete <span id="deleteStudentName" class="font-bold text-slate-800"></span> (<span id="deleteStudentId" class="font-semibold text-slate-700"></span>)? This will soft-delete their profile and remove their enrollment record from <span id="deleteStudentSection" class="font-bold text-slate-800"></span>.
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

@push('scripts')
<script>
    window.openEditStudentModal = function(db_id, student_no, name, email, course, section, status, gender, dob, birth_place, cell_no, address, final_grade) {
        const form = document.getElementById('editStudentForm');
        form.action = "{{ route('coordinator.sections.update_student', [':sec', ':id']) }}"
            .replace(':sec', encodeURIComponent(section))
            .replace(':id', db_id);
        
        document.getElementById('editStudNo').value = student_no;
        document.getElementById('editStudCourse').value = course === 'N/A' ? '' : course;
        document.getElementById('editStudName').value = name;
        document.getElementById('editStudEmail').value = email === 'N/A' ? '' : email;
        document.getElementById('editStudGender').value = (gender === 'N/A' || !gender) ? 'Female' : gender;
        document.getElementById('editStudDob').value = (dob === 'N/A' || !dob) ? '' : dob;
        document.getElementById('editStudBirthPlace').value = (birth_place === 'N/A' || !birth_place) ? '' : birth_place;
        document.getElementById('editStudCellNo').value = (cell_no === 'N/A' || !cell_no) ? '' : cell_no;
        document.getElementById('editStudAddress').value = (address === 'N/A' || !address) ? '' : address;
        document.getElementById('editStudGrade').value = (!final_grade || final_grade === 'null' || final_grade === 'N/A' || final_grade === '') ? '' : parseFloat(final_grade).toFixed(2);
        
        const secSelect = document.getElementById('editStudSection');
        if (secSelect) {
            secSelect.value = section;
        }

        const statusSelect = document.getElementById('editStudStatus');
        let mappedStatus = "Active";
        if (status === "Passed" || status === "Completed") mappedStatus = "Passed";
        else if (status === "Failed") mappedStatus = "Failed";
        else if (status === "Pending") mappedStatus = "Pending";
        else if (status === "Dropped") mappedStatus = "Dropped";
        else if (status === "Active") mappedStatus = "Active";
        statusSelect.value = mappedStatus;
        
        document.getElementById('editStudentOverlay').classList.remove('hidden');
    };

    window.openDeleteStudentModal = function(db_id, name, student_id, section) {
        const overlay = document.getElementById('deleteStudentOverlay');
        const form = document.getElementById('deleteStudentForm');
        const nameSpan = document.getElementById('deleteStudentName');
        const idSpan = document.getElementById('deleteStudentId');
        const secSpan = document.getElementById('deleteStudentSection');

        if (!overlay || !form || !nameSpan || !idSpan || !secSpan) return;

        form.action = "{{ route('coordinator.sections.remove_student', [':sec', ':id']) }}"
            .replace(':sec', encodeURIComponent(section))
            .replace(':id', db_id);
        nameSpan.textContent = name;
        idSpan.textContent = student_id;
        secSpan.textContent = section;

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
</script>
@endpush

@endsection
