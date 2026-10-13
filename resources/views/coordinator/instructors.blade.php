@extends('layouts.coordinator')

@section('title', 'Instructors - Coordinator Dashboard')

@section('content')


<x-page-header title="Instructor Management" subtitle="Manage faculty profiles, department assignments, and contact details">
    <x-slot name="actions">
        <button onclick="document.getElementById('newInstructorOverlay').classList.remove('hidden')" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition cursor-pointer">
            <x-icon name="userplus" class="w-4 h-4" /> Add Instructor
        </button>
    </x-slot>
</x-page-header>

<div class="mt-6 mb-6 relative">
    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
        <x-icon name="search" class="w-4 h-4" />
    </span>
    <input type="text" placeholder="Search instructors..." class="w-full max-w-md pl-9 pr-3 py-2 text-sm rounded-lg border border-slate-200 bg-white focus:outline-none focus:border-indigo-300 transition shadow-sm" />
</div>

<x-card title="Registered Instructors">
    <x-table>
        <x-slot name="header">
            <th class="py-2 px-3 font-medium">Instructor Name</th>
            <th class="py-2 px-3 font-medium">Department</th>
            <th class="py-2 px-3 font-medium">Email</th>
            <th class="py-2 px-3 font-medium">No. of Sections</th>
            <th class="py-2 px-3 font-medium">Status</th>
            <th class="py-2 px-3 font-medium text-right">Actions</th>
        </x-slot>

        @forelse($instructors as $r)
        <tr class="border-b border-slate-50 hover:bg-indigo-50/40 cursor-pointer transition" onclick="window.location.href='{{ route('coordinator.instructor_info', $r->id) }}'">
            <td class="py-3 px-3 text-slate-900 font-medium">{{ $r->name }}</td>
            <td class="py-3 px-3 text-slate-600">
                <span class="text-xs px-2 py-0.5 rounded-full {{ $r->dept === 'CWTS' ? 'bg-indigo-50 text-indigo-700' : ($r->dept === 'LTS' ? 'bg-emerald-50 text-emerald-700' : 'bg-amber-50 text-amber-700') }}">
                    {{ $r->dept }}
                </span>
            </td>
            <td class="py-3 px-3 text-slate-600">{{ $r->email }}</td>
            <td class="py-3 px-3 text-slate-600 font-medium">{{ $r->sections }}</td>
            <td class="py-3 px-3">
                <span class="text-xs px-2 py-0.5 rounded-full {{ $r->status === 'Active' ? 'bg-emerald-50 text-emerald-700' : 'bg-slate-100 text-slate-600' }}">
                    {{ $r->status }}
                </span>
            </td>
            <td class="py-3 px-3 text-right" onclick="event.stopPropagation();">
                <button onclick="openEditInstructorModal({{ json_encode($r) }})" class="text-slate-400 hover:text-indigo-600 p-1 transition cursor-pointer" title="Edit Instructor">
                    <x-icon name="pencil" class="w-4 h-4" />
                </button>
                <button type="button" onclick="openDeleteInstructorModal('{{ $r->id }}', '{{ addslashes($r->name) }}')" class="text-slate-400 hover:text-rose-600 p-1 transition cursor-pointer" title="Delete Instructor">
                    <x-icon name="trash" class="w-4 h-4" />
                </button>
            </td>
        </tr>
        @empty
        <tr><td colspan="6" class="py-8 text-center text-slate-400 text-sm">No instructors found.</td></tr>
        @endforelse
    </x-table>
    @if($instructors->hasPages())
        <div class="mt-4 px-4 py-3 border-t border-slate-100 bg-slate-50/50 rounded-b-xl">
            {{ $instructors->links() }}
        </div>
    @endif
</x-card>

<!-- New Instructor Modal -->
<div id="newInstructorOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm {{ (isset($errors) && $errors->any()) ? '' : 'hidden' }}">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-lg mx-4">
        <form method="POST" action="{{ route('coordinator.instructors.store') }}" data-progress-title="Adding Instructor" data-progress-subtitle="Registering faculty account and setting up assignments...">
            @csrf
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <div class="text-slate-900 font-bold tracking-tight text-lg">Add Instructor</div>
                    <div class="text-xs text-slate-500 mt-0.5">Create a new faculty account</div>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition cursor-pointer" onclick="document.getElementById('newInstructorOverlay').classList.add('hidden')">
                    <x-icon name="close" class="w-4 h-4" />
                </button>
            </div>
            <div class="p-6 space-y-5 text-sm">
                <div>
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 flex items-center gap-1">
                        <span>Select Registered Faculty</span>
                        <span class="text-rose-500">*</span>
                    </div>
                    <select name="name" required class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition">
                        <option value="">— Choose a registered instructor account —</option>
                        @foreach($registeredUsers ?? [] as $user)
                            <option value="{{ $user->name }}" {{ old('name') === $user->name ? 'selected' : '' }}>
                                {{ $user->name }} ({{ $user->email }})
                            </option>
                        @endforeach
                    </select>
                    <div class="text-[10px] text-slate-400 mt-1.5 leading-snug">
                        Only user accounts created by the system administrator can be configured here.
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 flex items-center gap-1">
                            <span>Department / Scope</span>
                            <span class="text-rose-500">*</span>
                        </div>
                        <select name="dept" required class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition">
                            <option value="CWTS" {{ old('dept') === 'CWTS' ? 'selected' : '' }}>CWTS — Civic Welfare</option>
                            <option value="LTS" {{ old('dept') === 'LTS' ? 'selected' : '' }}>LTS — Literacy Training</option>
                            <option value="ROTC" {{ old('dept') === 'ROTC' ? 'selected' : '' }}>ROTC — Reserve Officers</option>
                            <option value="General" {{ old('dept') === 'General' ? 'selected' : '' }}>General Dept.</option>
                        </select>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Assign Section</div>
                        <select name="assigned_section" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition">
                            <option value="">— Select a section (optional) —</option>
                            @foreach($allSections ?? [] as $sec)
                                <option value="{{ $sec->section_name }}" {{ old('assigned_section') === $sec->section_name ? 'selected' : '' }}>
                                    {{ $sec->section_name }} ({{ $sec->component }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50 rounded-b-2xl">
                <button type="button" class="px-4 py-2 text-sm font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 transition cursor-pointer" onclick="document.getElementById('newInstructorOverlay').classList.add('hidden')">Cancel</button>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition cursor-pointer">
                    <x-icon name="userplus" class="w-4 h-4" /> Save Instructor
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Edit Instructor Modal -->
<div id="editInstructorOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-lg mx-4">
        <form id="editInstructorForm" method="POST" action="" data-progress-title="Updating Instructor Profile" data-progress-subtitle="Saving changes to faculty credentials and section assignment...">
            @csrf
            @method('PUT')
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between">
                <div>
                    <div class="text-slate-900 font-bold tracking-tight text-lg">Edit Instructor</div>
                    <div class="text-xs text-slate-500 mt-0.5">Modify instructor configuration</div>
                </div>
                <button type="button" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition cursor-pointer" onclick="document.getElementById('editInstructorOverlay').classList.add('hidden')">
                    <x-icon name="close" class="w-4 h-4" />
                </button>
            </div>
            <div class="p-6 space-y-5 text-sm">
                <div>
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 flex items-center gap-1">
                        <span>Instructor Name</span>
                    </div>
                    <input type="text" id="edit_instructor_name" disabled class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-slate-50 text-slate-500 cursor-not-allowed focus:outline-none" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 flex items-center gap-1">
                            <span>Department / Scope</span>
                            <span class="text-rose-500">*</span>
                        </div>
                        <select name="dept" id="edit_instructor_dept" required class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition">
                            <option value="CWTS">CWTS — Civic Welfare</option>
                            <option value="LTS">LTS — Literacy Training</option>
                            <option value="ROTC">ROTC — Reserve Officers</option>
                            <option value="General">General Dept.</option>
                        </select>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 flex items-center gap-1">
                            <span>Status</span>
                            <span class="text-rose-500">*</span>
                        </div>
                        <select name="status" id="edit_instructor_status" required class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition">
                            <option value="Active">Active</option>
                            <option value="Inactive">Inactive</option>
                        </select>
                    </div>
                </div>

                <div>
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Assign New Section</div>
                    <select name="assigned_section" id="edit_instructor_section" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-300 focus:ring-4 focus:ring-indigo-50 transition">
                        <option value="">— Select a section (optional) —</option>
                        @foreach($allSections ?? [] as $sec)
                            <option value="{{ $sec->section_name }}">
                                {{ $sec->section_name }} ({{ $sec->component }})
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50 rounded-b-2xl">
                <button type="button" class="px-4 py-2 text-sm font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 transition cursor-pointer" onclick="document.getElementById('editInstructorOverlay').classList.add('hidden')">Cancel</button>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition cursor-pointer">
                    <x-icon name="pencil" class="w-4 h-4" /> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Delete Instructor Confirmation Modal -->
<div id="deleteInstructorOverlay" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden transition-opacity duration-200">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-md mx-4 overflow-hidden transform transition-all duration-200" id="deleteInstructorModalContainer">
        <form id="deleteInstructorForm" method="POST" action="" data-progress-title="Deleting Instructor" data-progress-subtitle="Removing faculty account...">
            @csrf
            @method('DELETE')
            <div class="p-6 text-center">
                <div class="w-12 h-12 rounded-full bg-rose-100 text-rose-600 flex items-center justify-center mx-auto mb-4">
                    <x-icon name="trash" class="w-6 h-6" />
                </div>
                <h3 class="text-lg font-bold text-slate-800 mb-2">Delete Instructor</h3>
                <p class="text-sm text-slate-500 mb-6">
                    Are you sure you want to delete <span id="deleteInstructorName" class="font-bold text-slate-800"></span>? This will remove their faculty assignment.
                </p>
                <div class="flex items-center justify-center gap-3 pt-2">
                    <button type="button" onclick="closeDeleteInstructorModal()" class="px-5 py-2.5 rounded-xl border border-slate-200 text-slate-600 font-semibold text-sm hover:bg-slate-50 transition cursor-pointer">
                        Cancel
                    </button>
                    <button type="submit" class="px-5 py-2.5 rounded-xl bg-rose-600 text-white font-semibold text-sm hover:bg-rose-700 transition shadow-sm cursor-pointer">
                        Delete Instructor
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<script>
const allSectionsList = @json($allSections ?? []);
let currentEditingInstructorId = null;

function updateInstructorSectionOptions(currentDept, instructorId) {
    const select = document.getElementById('edit_instructor_section');
    if (!select) return;

    select.innerHTML = '<option value="">— Select a section (optional) —</option>';

    allSectionsList.forEach(sec => {
        // Only sections within the instructor's assigned scope should be reflected there
        const matchesScope = (currentDept === 'General' || !sec.component || sec.component.toUpperCase() === currentDept.toUpperCase());
        if (!matchesScope) return;

        const isAssignedToOther = (sec.instructor_id && sec.instructor_id != instructorId);
        const opt = document.createElement('option');
        opt.value = sec.section_name;
        
        if (isAssignedToOther) {
            opt.disabled = true;
            opt.textContent = `${sec.section_name} (${sec.component}) — Assigned to ${sec.instructor ? sec.instructor.name : 'Another Instructor'}`;
        } else if (instructorId && sec.instructor_id == instructorId) {
            opt.textContent = `${sec.section_name} (${sec.component}) — Currently Assigned`;
        } else {
            opt.textContent = `${sec.section_name} (${sec.component})`;
        }

        select.appendChild(opt);
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const deptSelect = document.getElementById('edit_instructor_dept');
    if (deptSelect) {
        deptSelect.addEventListener('change', function() {
            updateInstructorSectionOptions(this.value, currentEditingInstructorId);
        });
    }
});

function openEditInstructorModal(instructor) {
    const form = document.getElementById('editInstructorForm');
    form.action = `/coordinator/instructors/${instructor.id}`;

    currentEditingInstructorId = instructor.id;
    document.getElementById('edit_instructor_name').value = instructor.name;
    document.getElementById('edit_instructor_dept').value = instructor.dept || 'CWTS';
    document.getElementById('edit_instructor_status').value = instructor.status || 'Active';

    updateInstructorSectionOptions(instructor.dept || 'CWTS', instructor.id);

    document.getElementById('editInstructorOverlay').classList.remove('hidden');
}

function openDeleteInstructorModal(id, name) {
    const form = document.getElementById('deleteInstructorForm');
    form.action = `/coordinator/instructors/${id}`;
    document.getElementById('deleteInstructorName').textContent = name;
    document.getElementById('deleteInstructorOverlay').classList.remove('hidden');
    document.getElementById('deleteInstructorOverlay').classList.add('flex');
}

function closeDeleteInstructorModal() {
    document.getElementById('deleteInstructorOverlay').classList.add('hidden');
    document.getElementById('deleteInstructorOverlay').classList.remove('flex');
}
</script>

@endsection
