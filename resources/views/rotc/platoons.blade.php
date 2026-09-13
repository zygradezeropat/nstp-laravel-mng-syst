@extends('layouts.rotc')

@section('title', 'Platoon Management - ROTC Command')

@section('content')

@push('styles')
<style>
    .platoon-row:hover .action-buttons {
        opacity: 1 !important;
    }
</style>
@endpush

<x-page-header title="Platoon Management Overview" subtitle="Group active cadets by their platoons, view personnel rosters, and track unassigned personnel.">
    <x-slot name="actions">
        <button onclick="document.getElementById('newPlatoonOverlay').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition cursor-pointer">
            <x-icon name="plus" class="w-4 h-4" /> New Platoon
        </button>
    </x-slot>
</x-page-header>

{{-- ── Session Alerts ── --}}
@if(session('success'))
    <div class="p-4 mb-4 text-sm text-emerald-800 rounded-lg bg-emerald-50 border border-emerald-200" role="alert">
        <span class="font-medium">Success!</span> {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div class="p-4 mb-4 text-sm text-rose-800 rounded-lg bg-rose-50 border border-rose-200" role="alert">
        <span class="font-medium">Validation errors:</span>
        <ul class="mt-1.5 list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

{{-- ── Summary Stat Cards ── --}}
<div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mt-6">
    {{-- Total Assigned Cadets --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-indigo-50 flex items-center justify-center">
            <x-icon name="users" class="w-5 h-5 text-indigo-600" />
        </div>
        <div>
            <div class="text-[11px] uppercase tracking-[0.18em] text-slate-500">Total Assigned Cadets</div>
            <div class="text-slate-900 tracking-tight text-3xl mt-0.5 font-bold">
                @php
                    $totalCadets = 0;
                    if(isset($platoons)) {
                        foreach($platoons as $name => $cadets) {
                            $totalCadets += count($cadets);
                        }
                    }
                @endphp
                {{ $totalCadets }}
            </div>
        </div>
    </div>
    {{-- Active Platoons --}}
    <div class="bg-white rounded-2xl p-5 shadow-sm border border-slate-100 flex items-center gap-4">
        <div class="w-12 h-12 rounded-full bg-amber-50 flex items-center justify-center">
            <x-icon name="shield" class="w-5 h-5 text-amber-600" />
        </div>
        <div>
            <div class="text-[11px] uppercase tracking-[0.18em] text-slate-500">Active Platoons</div>
            <div class="text-slate-900 tracking-tight text-3xl mt-0.5 font-bold">
                @php
                    $platoonsCount = 0;
                    if(isset($platoons)) {
                        foreach($platoons as $name => $cadets) {
                            if($name !== 'All Section') {
                                $platoonsCount++;
                            }
                        }
                    }
                @endphp
                {{ $platoonsCount }}
            </div>
        </div>
    </div>
</div>

{{-- ── Main Layout: Split Screen ── --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
    
    {{-- LEFT Panel: Platoon List --}}
    <div class="lg:col-span-1 space-y-4">
        <div class="bg-white border border-slate-200 rounded-md overflow-hidden">
            <div class="px-5 py-4 border-b border-slate-100 bg-slate-50">
                <div class="relative w-full">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <x-icon name="search" class="w-4 h-4 text-slate-400" />
                    </div>
                    <input type="text"
                           id="platoon-search"
                           placeholder="Search platoons..."
                           class="w-full pl-9 pr-4 py-2 text-sm border border-slate-200 rounded-md bg-white focus:bg-white focus:border-slate-400 transition placeholder:text-slate-400" />
                </div>
            </div>

            <x-table>
                <x-slot name="header">
                    <th class="px-5 py-3 text-left">Platoon Details</th>
                    <th class="px-5 py-3 text-right">Cadets</th>
                </x-slot>

                @forelse($sections ?? [] as $sec)
                @if($sec->section_name === 'All Section')
                    @continue
                @endif
                @php
                    $cadets = $platoons[$sec->section_name] ?? [];
                @endphp
                <tr class="border-b border-slate-50 hover:bg-slate-150 transition platoon-row cursor-pointer select-none group"
                    data-name="{{ $sec->section_name }}"
                    id="row-{{ str_replace(' ', '-', $sec->section_name) }}"
                    onclick="selectPlatoon('{{ $sec->section_name }}')">
                    <td class="px-5 py-4">
                        <div class="flex items-center justify-between gap-3 w-full">
                            <div class="flex items-center gap-3">
                                <div class="w-8 h-8 rounded-full bg-slate-100 flex items-center justify-center platoon-icon shrink-0">
                                    <x-icon name="shield" class="w-4 h-4 text-slate-500" />
                                </div>
                                <div class="flex flex-col">
                                    <span class="text-sm font-semibold text-slate-800">{{ $sec->section_name }}</span>
                                    <span class="text-[10px] text-slate-400 font-mono">Room: {{ $sec->room ?? 'TBA' }} · {{ $sec->instructor_name }}</span>
                                </div>
                            </div>
                            
                            {{-- Action buttons (show on hover) --}}
                            @if(isset($sec->id) && $sec->id < 9000)
                            <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition action-buttons" onclick="event.stopPropagation();">
                                <button onclick="openEditPlatoonModal('{{ $sec->id }}', '{{ $sec->section_name }}', '{{ $sec->school_year }}', '{{ $sec->room }}', '{{ $sec->instructor_id ? $sec->instructor->name : '' }}')" class="text-slate-400 hover:text-indigo-600 p-1 transition cursor-pointer animate-none" title="Edit Platoon">
                                    <x-icon name="pencil" class="w-3.5 h-3.5" />
                                </button>
                                <form action="{{ route('rotc.platoons.delete', $sec->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete platoon {{ $sec->section_name }}? This will permanently delete the platoon.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-slate-400 hover:text-rose-600 p-1 transition cursor-pointer" title="Delete Platoon">
                                        <x-icon name="trash" class="w-3.5 h-3.5" />
                                    </button>
                                </form>
                            </div>
                            @endif
                        </div>
                    </td>
                    <td class="px-5 py-4 text-sm text-slate-600 text-right font-medium shrink-0">{{ count($cadets) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="2" class="px-5 py-10 text-center text-sm text-slate-400">
                        No platoons found.
                    </td>
                </tr>
                @endforelse
            </x-table>
        </div>
    </div>

    {{-- RIGHT Panel: Platoon Roster & Unassigned --}}
    <div class="lg:col-span-2 space-y-6">
        
        {{-- Selected Platoon Details --}}
        <div id="details-empty-state" class="bg-white border border-slate-200 rounded-md p-10 text-center text-slate-400">
            <x-icon name="shield" class="w-10 h-10 mx-auto text-slate-300 mb-2" />
            <p class="text-sm font-medium">Select a Platoon on the left to view its assigned roster and manage cadets.</p>
        </div>

        @foreach($platoons ?? [] as $name => $cadets)
        @if($name === 'All Section')
            @continue
        @endif
        <div id="platoon-details-{{ str_replace(' ', '-', $name) }}" class="platoon-details-card hidden">
            <div class="bg-white border border-slate-200 rounded-md overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50 flex items-center justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-800">{{ $name }} Platoon Roster</h3>
                        <p class="text-xs text-slate-500 mt-0.5">Cadets currently allocated to this section</p>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full bg-indigo-50 text-indigo-700 font-bold tracking-wide uppercase">{{ count($cadets) }} Assigned</span>
                </div>
                
                <x-table>
                    <x-slot name="header">
                        <th class="py-2.5 px-4 text-left font-semibold text-slate-500">Cadet ID</th>
                        <th class="py-2.5 px-4 text-left font-semibold text-slate-500">Name</th>
                        <th class="py-2.5 px-4 text-right font-semibold text-slate-500 w-[100px]">Actions</th>
                    </x-slot>
                    @forelse($cadets as $c)
                        <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition">
                            <td class="py-3 px-4 font-mono text-xs text-slate-500">{{ $c->id }}</td>
                            <td class="py-3 px-4 text-slate-900 font-semibold">{{ $c->name }}</td>
                            <td class="py-3 px-4 text-right">
                                <form action="{{ route('rotc.rosters.assign') }}" method="POST" onsubmit="return confirm('Are you sure you want to unassign this cadet from {{ $name }} Platoon?');">
                                    @csrf
                                    <input type="hidden" name="student_id" value="{{ $c->id }}" />
                                    <input type="hidden" name="section_name" value="" />
                                    <button type="submit" class="text-xs text-rose-600 hover:text-rose-800 font-bold px-2.5 py-1 bg-rose-50 hover:bg-rose-100 rounded transition cursor-pointer">
                                        Unassign
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="py-8 text-center text-slate-400 text-sm">No cadets assigned to this platoon yet.</td></tr>
                    @endforelse
                </x-table>
            </div>
        </div>
        @endforeach



    </div>
</div>

{{-- ── Modals ── --}}

<!-- New Platoon Modal -->
<div id="newPlatoonOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-lg mx-4">
        <form id="newPlatoonForm" onsubmit="event.preventDefault();">
            @csrf
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <div class="text-slate-900 font-bold tracking-tight text-lg">New Platoon</div>
                    <div class="text-xs text-slate-500 mt-0.5">Fill in the details to add a new ROTC platoon</div>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition cursor-pointer" onclick="document.getElementById('newPlatoonOverlay').classList.add('hidden')">
                    <x-icon name="close" class="w-4 h-4" />
                </button>
            </div>
            <div class="p-6 space-y-5 text-sm">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Platoon Name <span class="text-rose-500">*</span></div>
                        <input name="code" id="newPlatCode" placeholder="e.g. Alpha" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">School Year</div>
                        <input name="school_year" id="newPlatSchoolYear" placeholder="e.g. 2025-2026" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                </div>
                <div>
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Room</div>
                    <input name="room" id="newPlatRoom" placeholder="e.g. Field A" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
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
                            <input type="file" id="newPlatClassFile" accept=".xlsx" class="hidden" />
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
                <button type="button" class="px-4 py-2 text-sm font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 transition cursor-pointer" onclick="document.getElementById('newPlatoonOverlay').classList.add('hidden')">Cancel</button>
                <button type="button" id="platoonFormCreate" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition cursor-pointer">
                    <x-icon name="users" class="w-4 h-4" /> Create Platoon
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Platoon Modal -->
<div id="editPlatoonOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-lg mx-4">
        <form id="editPlatoonForm" method="POST" action="">
            @csrf
            @method('PUT')
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <div class="text-slate-900 font-bold tracking-tight text-lg">Edit Platoon</div>
                    <div class="text-xs text-slate-500 mt-0.5">Modify platoon configurations</div>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition cursor-pointer" onclick="document.getElementById('editPlatoonOverlay').classList.add('hidden')">
                    <x-icon name="close" class="w-4 h-4" />
                </button>
            </div>
            <div class="p-6 space-y-5 text-sm">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Platoon Name <span class="text-rose-500">*</span></div>
                        <input type="text" name="code" id="editPlatCode" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">School Year</div>
                        <input type="text" name="school_year" id="editPlatSchoolYear" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                </div>
                <div>
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Room</div>
                    <input type="text" name="room" id="editPlatRoom" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition" />
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50 rounded-b-2xl">
                <button type="button" class="px-4 py-2 text-sm font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 transition cursor-pointer" onclick="document.getElementById('editPlatoonOverlay').classList.add('hidden')">Cancel</button>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition cursor-pointer">
                    <x-icon name="check2" class="w-4 h-4" /> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@section('scripts')
@vite(['resources/js/app.js'])
<script>
let activePlatoon = '';

function selectPlatoon(name) {
    // Hide details empty state
    const emptyState = document.getElementById('details-empty-state');
    if (emptyState) emptyState.classList.add('hidden');
    
    // Hide previous platoon cards
    document.querySelectorAll('.platoon-details-card').forEach(card => {
        card.classList.add('hidden');
    });
    
    // De-select previous rows
    document.querySelectorAll('.platoon-row').forEach(row => {
        row.classList.remove('bg-amber-300', 'hover:bg-amber-300');
        row.querySelector('.platoon-icon').classList.remove('bg-amber-400', 'text-slate-900');
        row.querySelector('.platoon-icon').classList.add('bg-slate-100');
        row.querySelector('.platoon-icon svg').classList.remove('text-slate-900');
        row.querySelector('.platoon-icon svg').classList.add('text-slate-500');
    });
    
    activePlatoon = name;
    const sanitizedId = 'platoon-details-' + name.replace(/ /g, '-');
    const targetCard = document.getElementById(sanitizedId);
    
    if (targetCard) {
        targetCard.classList.remove('hidden');
    }
    
    // Style current row
    const currentRow = document.getElementById('row-' + name.replace(/ /g, '-'));
    if (currentRow) {
        currentRow.classList.add('bg-amber-300', 'hover:bg-amber-300');
        currentRow.querySelector('.platoon-icon').classList.remove('bg-slate-100');
        currentRow.querySelector('.platoon-icon').classList.add('bg-amber-400');
        currentRow.querySelector('.platoon-icon svg').classList.remove('text-slate-500');
        currentRow.querySelector('.platoon-icon svg').classList.add('text-slate-900');
    }
}

// Edit Platoon Modal function
window.openEditPlatoonModal = function(id, code, schoolYear, room, instructor) {
    const form = document.getElementById('editPlatoonForm');
    form.action = "{{ route('rotc.platoons.update', ':id') }}".replace(':id', id);
    
    document.getElementById('editPlatCode').value = code;
    document.getElementById('editPlatSchoolYear').value = schoolYear;
    document.getElementById('editPlatRoom').value = room;
    

    
    document.getElementById('editPlatoonOverlay').classList.remove('hidden');
};

document.addEventListener('DOMContentLoaded', () => {
    // Left panel Platoon search
    const search = document.getElementById('platoon-search');
    search?.addEventListener('input', () => {
        const query = search.value.toLowerCase().trim();
        document.querySelectorAll('.platoon-row').forEach(row => {
            const name = (row.dataset.name || '').toLowerCase();
            row.style.display = name.includes(query) ? '' : 'none';
        });
    });

    // Proactively select the first platoon on page load if one exists
    const firstRow = document.querySelector('.platoon-row');
    if (firstRow) {
        const name = firstRow.dataset.name;
        if (name) {
            selectPlatoon(name);
        }
    }



    // New Platoon comparison logic
    const classInput = document.getElementById('newPlatClassFile');
    const classLabel = document.getElementById('classFileLabel');
    const platoonFormCreate = document.getElementById('platoonFormCreate');

    let classStudents = null;

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
                });
                
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

                if (nameIdx === -1 && lastNameIdx === -1) nameIdx = 1;
                if (idIdx === -1) idIdx = 0;
                
                const list = [];
                rows.forEach((row, index) => {
                    if (index === 0) return;
                    if (!row) return;
                    
                    let nameStr = '';
                    if (lastNameIdx !== -1 || firstNameIdx !== -1) {
                        const last = (row[lastNameIdx] || '').toString().trim();
                        const first = (row[firstNameIdx] || '').toString().trim();
                        const middle = middleNameIdx !== -1 ? (row[middleNameIdx] || '').toString().trim() : '';
                        
                        if (last || first) {
                            nameStr = last + ', ' + first;
                            if (middle) {
                                nameStr += middle.length === 1 ? ' ' + middle + '.' : ' ' + middle;
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

                    list.push({
                        name: nameStr,
                        studentNo: idStr || ('2024-' + Math.floor(10000 + Math.random() * 90000)),
                        gender: genderVal,
                        dob: dobVal,
                        birthPlace: pobVal,
                        address: addressVal,
                        cellNo: cellVal,
                        email: emailVal,
                        program: 'ROTC'
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

    if (classInput) {
        classInput.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) return;
            classLabel.textContent = file.name;
            parseExcel(file, (list) => {
                classStudents = list;
                
                // Run comparison with backend
                const container = document.getElementById('compareResultContainer');
                const label = document.getElementById('compareCountLabel');
                if (container && label) {
                    container.classList.remove('hidden');
                    label.textContent = "Matching with Master List...";
                }

                const uploadToken = 'temp_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);

                fetch('/api/sections/compare-class-list', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    },
                    body: JSON.stringify({
                        token: uploadToken,
                        program: 'ROTC',
                        students: classStudents
                    })
                })
                .then(response => response.json())
                .then(res => {
                    if (res.success) {
                        window.modalImportedStudents = res.matched;
                        window.modalUploadToken = uploadToken;
                        if (label) {
                            label.textContent = `${res.matched.length} matched cadet(s)`;
                        }
                    } else {
                        if (label) label.textContent = "Failed to compare with Master List";
                    }
                })
                .catch(err => {
                    console.error(err);
                    if (label) label.textContent = "Connection to database failed";
                });
            });
        });
    }

    // Submit form handler
    platoonFormCreate?.addEventListener('click', () => {
        const code = document.getElementById('newPlatCode')?.value.trim();
        const schoolYear = document.getElementById('newPlatSchoolYear')?.value.trim() || '2025-2026';
        const room = document.getElementById('newPlatRoom')?.value.trim() || 'TBA';

        if (!code) {
            alert('Platoon Name is required');
            return;
        }

        const importedStudents = window.modalImportedStudents || [];
        const originalText = platoonFormCreate.innerHTML;
        platoonFormCreate.innerHTML = 'Creating...';
        platoonFormCreate.disabled = true;

        fetch('/api/sections', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            },
            body: JSON.stringify({
                code: code,
                program: 'ROTC',
                room: room,
                school_year: schoolYear,
                instructor_name: null,
                students: importedStudents,
                upload_token: window.modalUploadToken || null
            })
        })
        .then(response => response.json().then(data => ({ status: response.status, body: data })))
        .then(({ status, body }) => {
            if (status !== 201) {
                throw new Error(body.message || 'Failed to create platoon.');
            }

            alert(`Platoon ${code} created successfully!`);
            document.getElementById('newPlatoonOverlay').classList.add('hidden');
            window.modalImportedStudents = null;
            setTimeout(() => window.location.reload(), 1000);
        })
        .catch(err => {
            alert('Error creating platoon: ' + err.message);
            console.error(err);
        })
        .finally(() => {
            platoonFormCreate.innerHTML = originalText;
            platoonFormCreate.disabled = false;
        });
    });
});
</script>
@endsection
