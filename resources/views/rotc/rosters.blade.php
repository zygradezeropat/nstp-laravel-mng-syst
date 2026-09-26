@extends('layouts.rotc')

@section('title', 'Assign Officer Section - ROTC Command')

@section('content')

<x-page-header title="Assign Officer Section Overview" subtitle="Master list of officers and cadets with rank, platoon, and specialty">
    <x-slot name="actions">
        <button id="export-roster-btn" class="inline-flex items-center gap-1.5 px-3 py-2 text-sm rounded-lg border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition cursor-pointer mr-2">
            <x-icon name="download" class="w-4 h-4" /> Export Roster
        </button>
        <button onclick="document.getElementById('addOfficerOverlay').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition cursor-pointer">
            <x-icon name="plus" class="w-4 h-4" /> Add Officer
        </button>
    </x-slot>
</x-page-header>


<x-card class="mt-6">
    {{-- Search & Filters bar --}}
    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex flex-col sm:flex-row items-center gap-4">
        <div class="relative flex-1 w-full sm:max-w-md">
            <x-icon name="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input id="roster-search" placeholder="Search ID or name..." class="w-full pl-9 pr-3 py-2 text-sm rounded-lg border border-slate-200 focus:outline-none focus:border-slate-400 transition" />
        </div>
        <select id="platoon-filter" class="w-full sm:w-auto px-3 py-2 text-sm rounded-lg border border-slate-200 bg-white focus:outline-none focus:border-slate-400">
            <option value="All">All Platoons</option>
            @foreach($sections as $sec)
                @if($sec->section_name !== 'All Section')
                    <option value="{{ $sec->section_name }}">{{ $sec->section_name }} Platoon</option>
                @endif
            @endforeach
            <option value="Unassigned">Unassigned</option>
        </select>
    </div>

    {{-- Roster Table --}}
    <div class="overflow-x-auto">
        <table id="roster-table" class="w-full border-collapse">
            <thead>
                <tr class="border-b border-slate-100 text-slate-500 text-xs font-semibold uppercase tracking-wider text-left bg-slate-50/50">
                    <th class="py-3 px-5 font-semibold text-slate-500">Cadet ID</th>
                    <th class="py-3 px-5 font-semibold text-slate-500">Name</th>
                    <th class="py-3 px-5 font-semibold text-slate-500">Rank</th>
                    <th class="py-3 px-5 font-semibold text-slate-500">Platoon</th>
                    <th class="py-3 px-5 font-semibold text-slate-500">Specialty</th>
                    <th class="py-3 px-5 font-semibold text-slate-500">Semester</th>
                    <th class="py-3 px-5 font-semibold text-slate-500 text-right w-[200px] no-export">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($rosters as $r)
                <tr class="border-b border-slate-50 hover:bg-slate-50 transition roster-row" 
                    data-id="{{ $r->id }}"
                    data-name="{{ strtolower($r->name) }}"
                    data-platoon="{{ $r->platoon }}">
                    <td class="py-3.5 px-5 text-slate-600 font-mono text-xs">{{ $r->id }}</td>
                    <td class="py-3.5 px-5 text-slate-900 font-medium">{{ $r->name }}</td>
                    <td class="py-3.5 px-5 text-slate-600">{{ $r->rank }}</td>
                    <td class="py-3.5 px-5">
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-medium {{ $r->platoon == 'Unassigned' ? 'bg-slate-150 text-slate-700' : 'bg-indigo-50 text-indigo-700' }}">
                            {{ $r->platoon == 'Unassigned' ? 'Unassigned' : $r->platoon . ' Platoon' }}
                        </span>
                    </td>
                    <td class="py-3.5 px-5 text-slate-600">{{ $r->spec }}</td>
                    <td class="py-3.5 px-5">
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-medium {{ $r->status == '1st Semester' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700' }}">
                            {{ $r->status }}
                        </span>
                    </td>
                    <td class="py-3.5 px-5 text-right no-export flex items-center justify-end gap-1.5">
                        <button onclick="openAssignModal('{{ $r->id }}', '{{ addslashes($r->name) }}', '{{ $r->platoon }}')" 
                                class="inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded bg-slate-900 hover:bg-slate-800 text-white font-medium transition cursor-pointer">
                            <x-icon name="pencil" class="w-3 h-3" /> Assign
                        </button>
                        <form action="{{ route('rotc.rosters.delete', $r->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete officer/cadet {{ addslashes($r->name) }}?')" data-progress-title="Deleting Personnel" data-progress-subtitle="Removing officer/cadet record from ROTC roster...">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="inline-flex items-center gap-1 text-xs px-3 py-1.5 rounded bg-rose-600 hover:bg-rose-700 text-white font-medium transition cursor-pointer">
                                <x-icon name="trash" class="w-3 h-3" /> Delete
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr id="empty-row"><td colspan="7" class="py-12 text-center text-slate-400 text-sm">No personnel found.</td></tr>
                @endforelse
                <tr id="no-results-row" class="hidden"><td colspan="7" class="py-12 text-center text-slate-400 text-sm">No matching roster personnel found.</td></tr>
            </tbody>
        </table>
    </div>
</x-card>

{{-- Platoon Assignment Modal --}}
<div id="assignModal" class="fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-50 hidden items-center justify-center">
    <div class="bg-white rounded-xl shadow-xl border border-slate-100 max-w-md w-full overflow-hidden transition-all duration-200 scale-95 opacity-0" id="assignModalContent">
        <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
            <h3 class="font-bold text-slate-850">Assign Cadet Platoon</h3>
            <button onclick="closeAssignModal()" class="text-slate-400 hover:text-slate-600 transition cursor-pointer">
                <x-icon name="close" class="w-5 h-5" />
            </button>
        </div>
         <form action="{{ route('rotc.rosters.assign') }}" method="POST" class="p-6 space-y-4" data-progress-title="Updating Assignment" data-progress-subtitle="Assigning cadet to platoon section...">
            @csrf
            <input type="hidden" name="original_student_id" id="modal-original-student-id" />
            <div>
                <label class="block text-xs uppercase tracking-wider text-slate-400 mb-1 font-bold">Cadet ID</label>
                <input type="text" name="student_id" id="modal-student-id-input" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-slate-400 transition font-mono text-sm" />
            </div>
            <div>
                <label class="block text-xs uppercase tracking-wider text-slate-400 mb-1 font-bold">Cadet Name</label>
                <input type="text" name="name" id="modal-student-name-input" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-slate-400 transition text-sm" />
            </div>
            <div>
                <label class="block text-xs uppercase tracking-wider text-slate-400 mb-1 font-bold">Assign Platoon Section</label>
                <select name="section_name" id="modal-platoon-select" class="w-full px-3 py-2 rounded-lg border border-slate-200 bg-white focus:outline-none focus:border-slate-400 transition">
                    <option value="">Unassigned</option>
                    @foreach($sections as $sec)
                        @if($sec->section_name !== 'All Section')
                            <option value="{{ $sec->section_name }}">{{ $sec->section_name }} Platoon</option>
                        @endif
                    @endforeach
                </select>
            </div>
            <div class="flex items-center gap-3 pt-2">
                <button type="button" onclick="closeAssignModal()" class="flex-1 px-4 py-2 border border-slate-250 rounded-lg text-sm text-slate-700 hover:bg-slate-50 transition cursor-pointer">Cancel</button>
                <button type="submit" class="flex-1 px-4 py-2 bg-slate-900 text-white rounded-lg text-sm hover:bg-slate-800 transition cursor-pointer">Save Assignment</button>
            </div>
        </form>
    </div>
</div>

<!-- Add Officer Modal -->
<div id="addOfficerOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-lg mx-4">
        <form action="{{ route('rotc.rosters.store') }}" method="POST" id="addOfficerForm" data-progress-title="Adding Officer / Cadet" data-progress-subtitle="Recording personnel credentials in ROTC roster...">
            @csrf
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <div class="text-slate-900 font-bold tracking-tight text-lg">Add Officer / Cadet</div>
                    <div class="text-xs text-slate-500 mt-0.5">Create a new entry for the ROTC roster</div>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition cursor-pointer" onclick="document.getElementById('addOfficerOverlay').classList.add('hidden')">
                    <x-icon name="close" class="w-4 h-4" />
                </button>
            </div>
            <div class="p-6 space-y-5 text-sm">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Officer ID <span class="text-rose-500">*</span></div>
                        <input name="student_id" placeholder="e.g. 2024-12345" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Rank <span class="text-rose-500">*</span></div>
                        <input name="rank" placeholder="e.g. Sgt / Cadet / Cpl" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Full Name <span class="text-rose-500">*</span></div>
                        <input name="name" placeholder="e.g. Dela Cruz, Juan" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Specialty <span class="text-rose-500">*</span></div>
                        <input name="specialty" placeholder="e.g. Infantry / Medic" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                </div>
                <div>
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Platoon Section</div>
                    <select name="platoon_name" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition">
                        <option value="">— Unassigned —</option>
                        @foreach($sections as $sec)
                            @if($sec->section_name !== 'All Section')
                                <option value="{{ $sec->section_name }}">{{ $sec->section_name }} Platoon</option>
                            @endif
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50 rounded-b-2xl">
                <button type="button" class="px-4 py-2 text-sm font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 transition cursor-pointer" onclick="document.getElementById('addOfficerOverlay').classList.add('hidden')">Cancel</button>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition cursor-pointer">
                    <x-icon name="users" class="w-4 h-4" /> Save Entry
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
<script>
function openAssignModal(id, name, currentPlatoon) {
    const modal = document.getElementById('assignModal');
    const content = document.getElementById('assignModalContent');
    
    document.getElementById('modal-original-student-id').value = id;
    document.getElementById('modal-student-id-input').value = id;
    document.getElementById('modal-student-name-input').value = name;
    
    const select = document.getElementById('modal-platoon-select');
    select.value = '';
    
    for (let option of select.options) {
        if (option.value && (currentPlatoon === option.value || currentPlatoon.includes(option.value))) {
            select.value = option.value;
            break;
        }
    }
    
    modal.classList.remove('hidden');
    modal.classList.add('flex');
    setTimeout(() => {
        content.classList.remove('scale-95', 'opacity-0');
        content.classList.add('scale-100', 'opacity-100');
    }, 50);
}

function closeAssignModal() {
    const modal = document.getElementById('assignModal');
    const content = document.getElementById('assignModalContent');
    
    content.classList.remove('scale-100', 'opacity-100');
    content.classList.add('scale-95', 'opacity-0');
    setTimeout(() => {
        modal.classList.remove('flex');
        modal.classList.add('hidden');
    }, 150);
}

document.addEventListener('DOMContentLoaded', () => {
    const search = document.getElementById('roster-search');
    const platoonFilter = document.getElementById('platoon-filter');
    const rows = document.querySelectorAll('.roster-row');
    const emptyRow = document.getElementById('empty-row');
    const noResultsRow = document.getElementById('no-results-row');

    function filterTable() {
        const query = search.value.toLowerCase().trim();
        const selectedPlatoon = platoonFilter.value;
        let visibleCount = 0;

        rows.forEach(row => {
            const name = row.dataset.name || '';
            const id = (row.dataset.id || '').toLowerCase();
            const platoon = row.dataset.platoon || 'Unassigned';

            const matchesQuery = name.includes(query) || id.includes(query);
            const matchesPlatoon = (selectedPlatoon === 'All') || 
                                  (selectedPlatoon === 'Unassigned' && platoon === 'Unassigned') ||
                                  (platoon === selectedPlatoon || platoon.includes(selectedPlatoon));

            if (matchesQuery && matchesPlatoon) {
                row.style.display = '';
                visibleCount++;
            } else {
                row.style.display = 'none';
            }
        });

        if (visibleCount === 0) {
            if (rows.length > 0) {
                noResultsRow.classList.remove('hidden');
            }
        } else {
            noResultsRow.classList.add('hidden');
        }
    }

    search?.addEventListener('input', filterTable);
    platoonFilter?.addEventListener('change', filterTable);

    // Export Excel Handler
    document.getElementById('export-roster-btn')?.addEventListener('click', () => {
        if (window.showProgressModal) {
            window.showProgressModal('Exporting ROTC Roster', 'Compiling cadet records into Excel format...', 35);
        }

        setTimeout(() => {
            const aoa = [];
            // Headers
            aoa.push(["Cadet ID", "Full Name", "Rank", "Platoon Section", "Specialty", "Enrollment Semester"]);
            
            let count = 0;
            document.querySelectorAll('.roster-row').forEach(row => {
                if (row.style.display !== 'none') {
                    const cells = row.querySelectorAll('td');
                    if (cells.length >= 6) {
                        aoa.push([
                            cells[0].innerText.trim(),
                            cells[1].innerText.trim(),
                            cells[2].innerText.trim(),
                            cells[3].innerText.trim(),
                            cells[4].innerText.trim(),
                            cells[5].innerText.trim()
                        ]);
                        count++;
                    }
                }
            });

            if (count === 0) {
                if (window.closeProgressModal) window.closeProgressModal();
                if (window.showAlertModal) window.showAlertModal('No roster data is currently visible to export.', 'Export Notice', 'warning');
                else alert('No roster data is currently visible to export.');
                return;
            }
            
            const ws = XLSX.utils.aoa_to_sheet(aoa);
            const wb = XLSX.utils.book_new();
            XLSX.book_append_sheet(wb, ws, "ROTC Cadet Roster");
            XLSX.writeFile(wb, "ROTC_Cadet_Roster.xlsx");

            if (window.finishProgressModal) {
                window.finishProgressModal('Export Completed', `${count} cadet record(s) exported to Excel successfully.`, 'success');
            }
        }, 600);
    });
});
</script>
@endsection
