@extends('layouts.coordinator')

@section('title', 'Student Archive - Coordinator Dashboard')

@section('content')

<x-page-header title="Student Archive" subtitle="Centralized repository of all past and present NSTP student records">
    <x-slot name="actions">
        <button type="button" onclick="exportArchiveCsv()" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition shadow-sm font-semibold cursor-pointer">
            <x-icon name="download" class="w-4 h-4" /> Export CSV
        </button>
    </x-slot>
</x-page-header>

<div class="mt-6 flex flex-wrap items-center gap-3 mb-6">
    <div class="relative flex-1 min-w-[280px]">
        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><x-icon name="search" class="w-4 h-4" /></span>
        <input type="text" id="archiveSearchInput" placeholder="Search by name, ID number, course, or serial no..." class="w-full pl-9 pr-3 py-2 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-300 transition shadow-xs" />
    </div>
    
    <select id="archiveProgramFilter" class="px-3 py-2 text-sm rounded-xl border border-slate-200 bg-white shadow-xs focus:outline-none focus:border-indigo-300 cursor-pointer font-medium text-slate-700">
        <option value="">All Programs</option>
        <option value="CWTS">CWTS</option>
        <option value="LTS">LTS</option>
        <option value="ROTC">ROTC</option>
    </select>
    
    <select id="archiveStatusFilter" class="px-3 py-2 text-sm rounded-xl border border-slate-200 bg-white shadow-xs focus:outline-none focus:border-indigo-300 cursor-pointer font-medium text-slate-700">
        <option value="">All Statuses</option>
        <option value="Completed">Completed</option>
        <option value="Active">Active</option>
        <option value="Archived">Archived</option>
        <option value="Passed">Passed</option>
        <option value="Incomplete">Incomplete</option>
        <option value="Failed">Failed</option>
    </select>
</div>

<x-card title="Archived Records ({{ count($students) }})">
    <x-table>
        <x-slot name="header">
            <th class="py-2.5 px-3 font-semibold">Student ID</th>
            <th class="py-2.5 px-3 font-semibold">Full Name</th>
            <th class="py-2.5 px-3 font-semibold">Course & Year</th>
            <th class="py-2.5 px-3 font-semibold">Program</th>
            
            <th class="py-2.5 px-3 font-semibold">Date Archived</th>
            <th class="py-2.5 px-3 font-semibold text-right">Actions</th>
        </x-slot>

        @forelse($students as $s)
        <tr class="archive-row border-b border-slate-50 hover:bg-indigo-50/40 transition cursor-pointer" 
            onclick="openViewStudentModal({{ json_encode($s) }})"
            data-id="{{ $s->id }}" 
            data-serial-no="{{ $s->serial_no }}" 
            data-name="{{ strtolower($s->name) }}" 
            data-course="{{ strtolower($s->course) }}" 
            data-program="{{ $s->program }}" 
            data-status="{{ $s->status }}">
            
            <td class="py-3 px-3 text-slate-900 font-semibold text-xs sm:text-sm">{{ $s->id }}</td>
            <td class="py-3 px-3">
                <div class="text-slate-800 font-bold text-xs sm:text-sm">{{ $s->name }}</div>
                <div class="text-[10px] text-slate-400">Serial: {{ $s->serial_no }}</div>
            </td>
            <td class="py-3 px-3">
                <div class="text-xs text-slate-700 font-medium">{{ $s->course }}</div>
                <div class="text-[10px] text-slate-500">{{ $s->year_level }}</div>
            </td>
            <td class="py-3 px-3">
                <span class="text-xs px-2.5 py-0.5 rounded-full font-semibold {{ $s->program === 'CWTS' ? 'bg-indigo-50 text-indigo-700' : ($s->program === 'LTS' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700') }}">
                    {{ $s->program }}
                </span>
            </td>
            
            <td class="py-3 px-3 text-xs text-slate-500">
                {{ $s->archived_at }}
            </td>
            <td class="py-3 px-3 text-right" onclick="event.stopPropagation();">
                <button type="button" onclick="openViewStudentModal({{ json_encode($s) }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 border border-indigo-200 transition cursor-pointer">
                    <x-icon name="eye" class="w-3.5 h-3.5" /> View Details
                </button>
            </td>
        </tr>
        @empty
        <tr id="archiveEmptyRow"><td colspan="7" class="py-8 text-center text-slate-400 text-sm">No archived student records found.</td></tr>
        @endforelse
    </x-table>

    <!-- Pagination Controls (10 records per page) -->
    <div id="archivePaginationContainer" class="mt-4 px-4 py-3 border-t border-slate-100 bg-slate-50/50 rounded-b-xl flex flex-col sm:flex-row items-center justify-between gap-3 text-xs">
        <div id="archivePaginationInfo" class="text-slate-500 font-medium">
            Showing 1 to 10 of {{ count($students) }} records
        </div>
        <div class="flex items-center gap-2">
            <button type="button" id="archivePrevBtn" onclick="changeArchivePage(-1)" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed font-medium transition cursor-pointer">
                Previous
            </button>
            <span id="archivePageIndicator" class="font-bold text-slate-700 px-2">
                Page 1 of 1
            </span>
            <button type="button" id="archiveNextBtn" onclick="changeArchivePage(1)" class="px-3 py-1.5 rounded-lg border border-slate-200 bg-white text-slate-600 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed font-medium transition cursor-pointer">
                Next
            </button>
        </div>
    </div>

    <!-- Empty State for Filter Matches -->
    <div id="archiveFilterEmptyState" class="hidden text-center py-12 px-4 bg-white rounded-2xl border border-slate-100 shadow-xs my-4">
        <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
            <x-icon name="alertc" class="w-6 h-6" />
        </div>
        <h3 class="text-base font-bold text-slate-800">No matching student records</h3>
        <p class="text-xs text-slate-500 mt-1 max-w-sm mx-auto">Try adjusting your search query or filter settings.</p>
    </div>
</x-card>

<!-- Student Comprehensive Details Modal -->
<div id="viewStudentOverlay" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden transition-opacity duration-200">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-2xl mx-4 overflow-hidden transform transition-all duration-200 flex flex-col max-h-[90vh]" id="viewStudentModalContainer">
        <!-- Modal Header -->
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full bg-indigo-100 text-indigo-700 font-bold flex items-center justify-center text-sm shrink-0" id="viewAvatarBadge">
                    ST
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h3 class="text-base font-bold text-slate-800" id="viewStudentFullName">Student Name</h3>
                        <span id="viewStatusBadge" class="text-xs px-2.5 py-0.5 rounded-full font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                            Active
                        </span>
                    </div>
                    <p class="text-xs text-slate-500 mt-0.5">
                        Student ID: <span id="viewStudentId" class="font-bold text-slate-700">2024-0001</span> | 
                        Serial No: <span id="viewSerialNo" class="font-semibold text-slate-600">N/A</span>
                    </p>
                </div>
            </div>
            <button type="button" onclick="closeViewStudentModal()" class="text-slate-400 hover:text-slate-600 p-1.5 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                <x-icon name="close" class="w-4 h-4" />
            </button>
        </div>

        <!-- Modal Body Content -->
        <div class="p-6 overflow-y-auto space-y-5 text-xs sm:text-sm">
            <!-- Personal Information Section -->
            <div class="space-y-3">
                <div class="text-xs font-bold text-indigo-700 uppercase tracking-wider flex items-center gap-1.5 border-b border-indigo-100 pb-1.5">
                    <x-icon name="user" class="w-4 h-4 text-indigo-600" /> Personal Information
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">First Name</span>
                        <span id="viewFirstName" class="font-semibold text-slate-800 text-xs sm:text-sm block">-</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Middle Name</span>
                        <span id="viewMiddleName" class="font-semibold text-slate-800 text-xs sm:text-sm block">-</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Last Name</span>
                        <span id="viewLastName" class="font-semibold text-slate-800 text-xs sm:text-sm block">-</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Gender / Sex</span>
                        <span id="viewGender" class="font-semibold text-slate-800 text-xs sm:text-sm block">-</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Date of Birth</span>
                        <span id="viewDob" class="font-semibold text-slate-800 text-xs sm:text-sm block">-</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Place of Birth</span>
                        <span id="viewBirthPlace" class="font-semibold text-slate-800 text-xs sm:text-sm block truncate" title="">-</span>
                    </div>
                </div>
            </div>

            <!-- Contact Information Section -->
            <div class="space-y-3">
                <div class="text-xs font-bold text-indigo-700 uppercase tracking-wider flex items-center gap-1.5 border-b border-indigo-100 pb-1.5">
                    <x-icon name="mail" class="w-4 h-4 text-indigo-600" /> Contact & Address Details
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3.5">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Email Address</span>
                        <span id="viewEmail" class="font-semibold text-slate-800 text-xs sm:text-sm block truncate" title="">-</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Contact Number</span>
                        <span id="viewContactNumber" class="font-semibold text-slate-800 text-xs sm:text-sm block">-</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100 sm:col-span-2">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Complete Residential Address</span>
                        <span id="viewAddress" class="font-semibold text-slate-800 text-xs sm:text-sm block">-</span>
                    </div>
                </div>
            </div>

            <!-- Academic & NSTP Enrollment Information Section -->
            <div class="space-y-3">
                <div class="text-xs font-bold text-indigo-700 uppercase tracking-wider flex items-center gap-1.5 border-b border-indigo-100 pb-1.5">
                    <x-icon name="book" class="w-4 h-4 text-indigo-600" /> Academic & NSTP Record
                </div>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5">
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">College Course</span>
                        <span id="viewCourse" class="font-semibold text-slate-800 text-xs sm:text-sm block">-</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Year Level</span>
                        <span id="viewYearLevel" class="font-semibold text-slate-800 text-xs sm:text-sm block">-</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">NSTP Component</span>
                        <span id="viewProgram" class="font-bold text-indigo-700 text-xs sm:text-sm block">-</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Final Grade</span>
                        <span id="viewGrade" class="font-bold text-emerald-700 text-xs sm:text-sm block">-</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Numerical Grade</span>
                        <span id="viewNumGrade" class="font-bold text-slate-800 text-xs sm:text-sm block">-</span>
                    </div>
                    <div class="p-3 rounded-xl bg-slate-50 border border-slate-100">
                        <span class="text-[10px] uppercase font-bold text-slate-400 block mb-0.5">Date Archived</span>
                        <span id="viewArchivedAt" class="font-semibold text-slate-700 text-xs sm:text-sm block">-</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Modal Footer Actions -->
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50 flex items-center justify-between rounded-b-2xl">
            <div id="restoreContainer">
                <form id="restoreStudentForm" method="POST" action="">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-semibold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 transition shadow-xs cursor-pointer">
                        <x-icon name="check2" class="w-4 h-4" /> Restore Student Record
                    </button>
                </form>
            </div>
            <button type="button" onclick="closeViewStudentModal()" class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 transition cursor-pointer">
                Close
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let currentArchivePage = 1;
    const archivePageSize = 10;

    window.openViewStudentModal = function(student) {
        if (!student) return;

        const overlay = document.getElementById('viewStudentOverlay');
        if (!overlay) return;

        document.getElementById('viewStudentFullName').textContent = student.name || 'N/A';
        document.getElementById('viewStudentId').textContent = student.student_id || student.id || 'N/A';
        document.getElementById('viewSerialNo').textContent = student.serial_no || 'N/A';
        document.getElementById('viewFirstName').textContent = student.first_name || 'N/A';
        document.getElementById('viewMiddleName').textContent = student.middle_name || 'N/A';
        document.getElementById('viewLastName').textContent = student.last_name || 'N/A';
        document.getElementById('viewGender').textContent = student.gender || student.sex || 'N/A';
        document.getElementById('viewDob').textContent = student.dob || 'N/A';
        document.getElementById('viewBirthPlace').textContent = student.birth_place || 'N/A';
        document.getElementById('viewEmail').textContent = student.email || 'N/A';
        document.getElementById('viewContactNumber').textContent = student.contact_number || student.cell_no || 'N/A';
        document.getElementById('viewAddress').textContent = student.address || 'N/A';
        document.getElementById('viewCourse').textContent = student.course || 'N/A';
        document.getElementById('viewYearLevel').textContent = student.year_level || 'N/A';
        document.getElementById('viewProgram').textContent = student.program || 'CWTS';
        document.getElementById('viewGrade').textContent = student.grade || 'N/A';
        document.getElementById('viewNumGrade').textContent = student.numerical_grade || 'N/A';
        document.getElementById('viewArchivedAt').textContent = student.archived_at || 'N/A';

        // Avatar initials
        const nameParts = (student.name || 'ST').split(' ');
        const initials = (nameParts[0] ? nameParts[0][0] : '') + (nameParts[1] ? nameParts[1][0] : '');
        document.getElementById('viewAvatarBadge').textContent = initials.toUpperCase() || 'ST';

        // Status Badge styling
        const statusBadge = document.getElementById('viewStatusBadge');
        if (statusBadge) {
            statusBadge.textContent = student.status || 'Archived';
            const st = (student.status || '').toLowerCase();
            if (st === 'completed' || st === 'passed' || st === 'active') {
                statusBadge.className = 'text-xs px-2.5 py-0.5 rounded-full font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200';
            } else {
                statusBadge.className = 'text-xs px-2.5 py-0.5 rounded-full font-semibold bg-rose-50 text-rose-700 border border-rose-200';
            }
        }

        // Restore Form Setup
        const restoreForm = document.getElementById('restoreStudentForm');
        if (restoreForm && student.db_id) {
            restoreForm.action = "{{ route('coordinator.archive.restore', ':id') }}".replace(':id', student.db_id);
        }

        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
    };

    window.closeViewStudentModal = function() {
        const overlay = document.getElementById('viewStudentOverlay');
        if (overlay) {
            overlay.classList.add('hidden');
            overlay.classList.remove('flex');
        }
    };

    window.changeArchivePage = function(delta) {
        currentArchivePage += delta;
        if (currentArchivePage < 1) currentArchivePage = 1;
        filterAndPaginateArchive();
    };

    function filterAndPaginateArchive() {
        const searchInput = document.getElementById('archiveSearchInput');
        const progSelect = document.getElementById('archiveProgramFilter');
        const statusSelect = document.getElementById('archiveStatusFilter');

        const query = searchInput ? searchInput.value.toLowerCase().trim() : '';
        const prog = progSelect ? progSelect.value : '';
        const status = statusSelect ? statusSelect.value : '';

        const rows = document.querySelectorAll('.archive-row');
        const emptyState = document.getElementById('archiveFilterEmptyState');
        const pagInfo = document.getElementById('archivePaginationInfo');
        const pagIndicator = document.getElementById('archivePageIndicator');
        const prevBtn = document.getElementById('archivePrevBtn');
        const nextBtn = document.getElementById('archiveNextBtn');
        const pagContainer = document.getElementById('archivePaginationContainer');

        const matchingRows = [];

        rows.forEach(r => {
            const rId = (r.getAttribute('data-id') || '').toLowerCase();
            const rSerial = (r.getAttribute('data-serial-no') || '').toLowerCase();
            const rName = (r.getAttribute('data-name') || '').toLowerCase();
            const rCourse = (r.getAttribute('data-course') || '').toLowerCase();
            const rProg = r.getAttribute('data-program') || '';
            const rStatus = r.getAttribute('data-status') || '';

            const matchesSearch = !query || rId.includes(query) || rSerial.includes(query) || rName.includes(query) || rCourse.includes(query);
            const matchesProg = !prog || rProg === prog;
            const matchesStatus = !status || rStatus.toLowerCase() === status.toLowerCase();

            if (matchesSearch && matchesProg && matchesStatus) {
                matchingRows.push(r);
            } else {
                r.classList.add('hidden');
            }
        });

        const totalMatching = matchingRows.length;
        const totalPages = Math.max(1, Math.ceil(totalMatching / archivePageSize));

        if (currentArchivePage > totalPages) currentArchivePage = totalPages;
        if (currentArchivePage < 1) currentArchivePage = 1;

        const startIndex = (currentArchivePage - 1) * archivePageSize;
        const endIndex = startIndex + archivePageSize;

        matchingRows.forEach((r, idx) => {
            if (idx >= startIndex && idx < endIndex) {
                r.classList.remove('hidden');
            } else {
                r.classList.add('hidden');
            }
        });

        if (pagInfo) {
            if (totalMatching === 0) {
                pagInfo.textContent = `Showing 0 to 0 of 0 records`;
            } else {
                pagInfo.textContent = `Showing ${startIndex + 1} to ${Math.min(endIndex, totalMatching)} of ${totalMatching} records`;
            }
        }

        if (pagIndicator) {
            pagIndicator.textContent = `Page ${currentArchivePage} of ${totalPages}`;
        }

        if (prevBtn) prevBtn.disabled = currentArchivePage <= 1;
        if (nextBtn) nextBtn.disabled = currentArchivePage >= totalPages || totalMatching === 0;

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

    window.exportArchiveCsv = function() {
        const rows = document.querySelectorAll('.archive-row');
        let csvContent = "data:text/csv;charset=utf-8,Student ID,Serial No,Full Name,Course & Year,Program,Status,Date Archived\n";

        rows.forEach(r => {
            if (!r.classList.contains('hidden')) {
                const cols = r.querySelectorAll('td');
                if (cols.length >= 6) {
                    const id = cols[0].innerText.trim();
                    const name = cols[1].innerText.replace(/\n/g, ' ').trim();
                    const course = cols[2].innerText.replace(/\n/g, ' ').trim();
                    const program = cols[3].innerText.trim();
                    const status = cols[4].innerText.trim();
                    const date = cols[5].innerText.trim();

                    const rowData = [id, '', name, course, program, status, date].map(c => '"' + c.replace(/"/g, '""') + '"');
                    csvContent += rowData.join(",") + "\n";
                }
            }
        });

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", `Student_Archive_Export_${Date.now()}.csv`);
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    };

    document.addEventListener('DOMContentLoaded', () => {
        const searchInput = document.getElementById('archiveSearchInput');
        const progSelect = document.getElementById('archiveProgramFilter');
        const statusSelect = document.getElementById('archiveStatusFilter');

        [searchInput, progSelect, statusSelect].forEach(el => {
            if (el) {
                el.addEventListener('input', () => { currentArchivePage = 1; filterAndPaginateArchive(); });
                el.addEventListener('change', () => { currentArchivePage = 1; filterAndPaginateArchive(); });
            }
        });

        filterAndPaginateArchive();
    });
</script>
@endpush

@endsection
