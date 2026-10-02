@extends('layouts.coordinator')

@section('title', 'Students in Section - DNSC NSTP Portal')

@section('content')

<x-page-header title="Students in Section: {{ $section->section_name }}" subtitle="Manage and view enrolled students for this class section">
    <x-slot name="actions">
        <div class="flex items-center gap-3">
            <button type="button" onclick="openDeleteSectionModal('{{ $section->id }}', '{{ addslashes($section->section_name) }}')" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm rounded-xl border border-rose-200 bg-rose-50 text-rose-700 hover:bg-rose-100 transition shadow-xs font-semibold cursor-pointer">
                <x-icon name="trash" class="w-4 h-4" /> Delete Section
            </button>
            <a href="{{ route('coordinator.sections') }}" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition shadow-sm font-semibold">
                <x-icon name="chevron" class="w-4 h-4 rotate-90" /> Back to Sections
            </a>
        </div>
    </x-slot>
</x-page-header>


<div class="mt-6">
    <x-card title="Enrolled Students ({{ $students->total() }})">
        <x-slot name="action">
            <div class="relative w-64 sm:w-80">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none flex items-center justify-center">
                    <x-icon name="search" class="w-4 h-4" />
                </span>
                <input type="text" id="studentSearchInput" placeholder="Search students..." class="w-full pl-9 pr-8 py-2 text-xs rounded-xl border border-slate-200 bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition shadow-2xs" />
                <button type="button" id="clearSearchBtn" class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-300 hover:text-slate-600 hidden transition p-0.5 cursor-pointer" title="Clear search">
                    <x-icon name="close" class="w-3.5 h-3.5" />
                </button>
            </div>
        </x-slot>
        <x-table>
            <x-slot name="header">
                <th class="py-2 px-3 font-medium">Student ID</th>
                <th class="py-2 px-3 font-medium">Name</th>
                <th class="py-2 px-3 font-medium">Gender</th>
                <th class="py-2 px-3 font-medium">DOB / POB</th>
                <th class="py-2 px-3 font-medium">Contact / Email</th>
                <th class="py-2 px-3 font-medium">Residential Address</th>
                <th class="py-2 px-3 font-medium">Course</th>
                <th class="py-2 px-3 font-medium">NSTP Program</th>
                <th class="py-2 px-3 font-medium">Final Grade</th>
                <th class="py-2 px-3 font-medium text-right">Actions</th>
            </x-slot>

            @forelse($students as $s)
            <tr class="border-b border-slate-50 hover:bg-indigo-50/40 transition">
                <td class="py-3 px-3 text-slate-900 font-medium">{{ $s->id }}</td>
                <td class="py-3 px-3 text-slate-700 font-medium">{{ $s->name }}</td>
                <td class="py-3 px-3 text-slate-600">
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $s->gender === 'Male' ? 'bg-sky-50 text-sky-700' : ($s->gender === 'Female' ? 'bg-pink-50 text-pink-700' : 'bg-slate-100 text-slate-600') }}">
                        {{ $s->gender }}
                    </span>
                </td>
                <td class="py-3 px-3">
                    <div class="text-xs text-slate-800 font-medium">{{ $s->dob }}</div>
                    <div class="text-[10px] text-slate-400 truncate max-w-[130px]" title="{{ $s->birth_place }}">{{ $s->birth_place }}</div>
                </td>
                <td class="py-3 px-3">
                    <div class="text-xs text-slate-800">{{ $s->cell_no }}</div>
                    <div class="text-[10px] text-slate-400 truncate max-w-[150px]" title="{{ $s->email }}">{{ $s->email }}</div>
                </td>
                <td class="py-3 px-3 text-xs text-slate-500 max-w-[180px] truncate" title="{{ $s->address }}">
                    {{ $s->address }}
                </td>
                <td class="py-3 px-3 text-slate-600">{{ $s->course }}</td>
                <td class="py-3 px-3 text-slate-600">
                    <span class="text-xs px-2 py-0.5 rounded-full {{ $s->program === 'CWTS' ? 'bg-indigo-50 text-indigo-700' : ($s->program === 'LTS' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700') }}">
                        {{ $s->program }}
                    </span>
                </td>
                <td class="py-3 px-3">
                    @if(is_numeric($s->final_grade))
                        <span class="text-xs px-2 py-0.5 rounded-full font-bold {{ $s->final_grade <= 2.5 ? 'bg-emerald-50 text-emerald-700 border border-emerald-200' : 'bg-rose-50 text-rose-700 border border-rose-200' }}">
                            {{ number_format($s->final_grade, 2) }}
                        </span>
                    @elseif($s->status === 'Passed' || $s->status === 'Completed')
                        <span class="text-xs px-2 py-0.5 rounded-full font-bold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Passed
                        </span>
                    @elseif($s->status === 'Failed')
                        <span class="text-xs px-2 py-0.5 rounded-full font-bold bg-rose-50 text-rose-700 border border-rose-200">
                            Failed
                        </span>
                    @elseif($s->status === 'Dropped')
                        <span class="text-xs px-2 py-0.5 rounded-full font-medium bg-slate-100 text-slate-600 border border-slate-200">
                            Dropped
                        </span>
                    @else
                        <span class="text-xs px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">
                            Pending
                        </span>
                    @endif
                </td>
                <td class="py-3 px-3 text-right" onclick="event.stopPropagation();">
                    <button onclick="openEditStudentModal('{{ $s->db_id }}', '{{ $s->id }}', '{{ addslashes($s->name) }}', '{{ $s->email }}', '{{ $s->course }}', '{{ $s->status }}', '{{ $s->gender }}', '{{ $s->dob }}', '{{ addslashes($s->birth_place) }}', '{{ $s->cell_no }}', '{{ addslashes($s->address) }}', '{{ $s->final_grade }}')" class="text-slate-400 hover:text-indigo-600 p-1 transition cursor-pointer" title="Edit Student Profile"><x-icon name="pencil" class="w-4 h-4" /></button>
                    <button type="button" onclick="openDeleteStudentModal('{{ $s->db_id }}', '{{ addslashes($s->name) }}', '{{ $s->id }}')" class="text-slate-400 hover:text-rose-600 p-1 transition cursor-pointer" title="Delete Student"><x-icon name="trash" class="w-4 h-4" /></button>
                </td>
            </tr>
            @empty
            <tr><td colspan="10" class="py-8 text-center text-slate-400 text-sm">No students enrolled in this section.</td></tr>
            @endforelse
        </x-table>
        @if($students->hasPages())
            <div class="mt-4 px-4 py-3 border-t border-slate-100 bg-slate-50/50 rounded-b-xl">
                {{ $students->links() }}
            </div>
        @endif
    </x-card>
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
                    <div class="text-xs text-slate-500 mt-0.5">Update credentials and status within section</div>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition cursor-pointer" onclick="document.getElementById('editStudentOverlay').classList.add('hidden')">
                    <x-icon name="close" class="w-4 h-4" />
                </button>
            </div>
            <div class="p-6 space-y-5 text-sm">
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
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Full Name (Last, First) <span class="text-rose-500">*</span></div>
                    <input type="text" name="name" id="editStudName" required placeholder="e.g. Dela Cruz, Juan" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Gender</div>
                        <select name="gender" id="editStudGender" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition">
                            <option value="Male">Male</option>
                            <option value="Female">Female</option>
                        </select>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Date of Birth</div>
                        <input type="date" name="dob" id="editStudDob" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Place of Birth</div>
                        <input type="text" name="birth_place" id="editStudBirthPlace" placeholder="e.g. Tagum City" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Cell Number</div>
                        <input type="text" name="cell_no" id="editStudCellNo" placeholder="e.g. 09123456789" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                </div>
                <div>
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Residential Address</div>
                    <textarea name="address" id="editStudAddress" placeholder="e.g. Apokon, Tagum City" rows="2" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition"></textarea>
                </div>
                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 flex items-center">Email</div>
                        <input type="email" name="email" id="editStudEmail" placeholder="e.g. juan.delacruz@dnsc.edu.ph" class="w-full px-3 py-2.5 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
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
        <form id="deleteStudentForm" method="POST" action="" data-progress-title="Deleting Student Record" data-progress-subtitle="Removing student from section roster...">
            @csrf
            @method('DELETE')
            <div class="p-6 text-center">
                <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4">
                    <x-icon name="trash" class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-2">Delete Student Record</h3>
                <p class="text-sm text-slate-500 mb-6">
                    Are you sure you want to delete <span id="deleteStudentName" class="font-bold text-slate-800"></span> (<span id="deleteStudentId" class="font-semibold text-slate-700"></span>)? This will soft-delete their profile and remove their enrollment record from <span class="font-bold text-slate-800">{{ $section->section_name }}</span>.
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
                    Are you sure you want to delete section <span id="deleteSectionCode" class="font-bold text-slate-800">{{ $section->section_name }}</span>? This will permanently remove the section record.
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

@push('scripts')
    <script>
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

            form.action = "{{ route('coordinator.sections.remove_student', [$section->section_name, ':id']) }}".replace(':id', db_id);
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
        window.openEditStudentModal = function(db_id, student_no, name, email, course, status, gender, dob, birth_place, cell_no, address, final_grade) {
            const form = document.getElementById('editStudentForm');
            form.action = "{{ route('coordinator.sections.update_student', [$section->section_name, ':id']) }}".replace(':id', db_id);
            
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
            
            // Set status select dropdown matching database values (Passed, Failed, Pending, Dropped, Active)
            const statusSelect = document.getElementById('editStudStatus');
            statusSelect.value = "Active"; // default
            
            // We map view display strings to select options values
            let mappedStatus = "Active";
            if (status === "Passed" || status === "Completed") {
                mappedStatus = "Passed";
            } else if (status === "Failed") {
                mappedStatus = "Failed";
            } else if (status === "Pending") {
                mappedStatus = "Pending";
            } else if (status === "Dropped") {
                mappedStatus = "Dropped";
            } else if (status === "Active") {
                mappedStatus = "Active";
            }
            
            statusSelect.value = mappedStatus;
            
            document.getElementById('editStudentOverlay').classList.remove('hidden');
        }

        // Live Search Filter for Enrolled Students
        document.addEventListener('DOMContentLoaded', function() {
            const searchInput = document.getElementById('studentSearchInput');
            const clearBtn = document.getElementById('clearSearchBtn');

            if (searchInput) {
                searchInput.addEventListener('input', function() {
                    const query = this.value.toLowerCase().trim();

                    if (clearBtn) {
                        if (query.length > 0) {
                            clearBtn.classList.remove('hidden');
                        } else {
                            clearBtn.classList.add('hidden');
                        }
                    }

                    const rows = document.querySelectorAll('table tbody tr');
                    let visibleCount = 0;

                    rows.forEach(row => {
                        // Skip empty state row if present
                        if (row.id === 'noSearchMatchRow' || row.querySelector('td[colspan]')) return;

                        const text = row.textContent.toLowerCase();
                        if (!query || text.includes(query)) {
                            row.style.display = '';
                            visibleCount++;
                        } else {
                            row.style.display = 'none';
                        }
                    });

                    let noMatchRow = document.getElementById('noSearchMatchRow');
                    if (visibleCount === 0 && query.length > 0) {
                        if (!noMatchRow) {
                            const tbody = document.querySelector('table tbody');
                            if (tbody) {
                                noMatchRow = document.createElement('tr');
                                noMatchRow.id = 'noSearchMatchRow';
                                const safeQuery = this.value.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
                                noMatchRow.innerHTML = `<td colspan="10" class="py-8 text-center text-slate-400 text-sm">No students found matching "${safeQuery}".</td>`;
                                tbody.appendChild(noMatchRow);
                            }
                        } else {
                            noMatchRow.style.display = '';
                            const safeQuery = this.value.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;");
                            noMatchRow.querySelector('td').innerHTML = `No students found matching "${safeQuery}".`;
                        }
                    } else if (noMatchRow) {
                        noMatchRow.style.display = 'none';
                    }
                });

                if (clearBtn) {
                    clearBtn.addEventListener('click', function() {
                        searchInput.value = '';
                        searchInput.dispatchEvent(new Event('input'));
                        searchInput.focus();
                    });
                }
            }
        });
    </script>
@endpush

@endsection
