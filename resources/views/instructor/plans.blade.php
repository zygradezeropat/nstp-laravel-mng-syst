@extends('layouts.instructor')

@section('title', 'Activity Plans - Instructor Console')

@section('content')

<x-page-header title="Activity Plans" subtitle="Submit and track activity plans for your assigned sections">
    <x-slot name="actions">
        <button onclick="openCreatePlanModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-indigo-600 text-white text-sm shadow-md hover:bg-indigo-700 hover:shadow-lg transition cursor-pointer font-semibold font-semibold">
            <x-icon name="plus" class="w-4 h-4" /> Create Activity Plan
        </button>
    </x-slot>
</x-page-header>


<x-card title="Activity Plans List" subtitle="Track and manage all submitted activity plans" class="mt-6">
    <x-table>
        <x-slot name="header">
            <th class="py-3.5 px-4 font-semibold text-slate-600">Activity Plan / Design</th>
            <th class="py-3.5 px-4 font-semibold text-slate-600">Section</th>
            <th class="py-3.5 px-4 font-semibold text-slate-600">Scheduled Date</th>
            <th class="py-3.5 px-4 font-semibold text-slate-600">Location</th>
            <th class="py-3.5 px-4 font-semibold text-slate-600">Status</th>
            <th class="py-3.5 px-4 font-semibold text-slate-600 text-right">Actions</th>
        </x-slot>

        @forelse($plans as $p)
        @php
            $isRevision = $p->status === 'Revision' || $p->status === 'revision';
        @endphp
        <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition @if($isRevision) cursor-pointer bg-rose-50/20 hover:bg-rose-50/40 @endif"
            @if($isRevision)
            onclick="openRevisionDetailsModal('{{ $p->id }}', '{{ addslashes($p->title) }}', '{{ $p->section?->section_name === 'All Section' ? 'all' : $p->section_id }}', '{{ addslashes($p->location) }}', '{{ $p->scheduled_date ? $p->scheduled_date->format('Y-m-d') : '' }}', '{{ addslashes($p->objectives) }}', '{{ addslashes($p->description) }}', '{{ $p->status }}', '{{ addslashes($p->feedback) }}')"
            @endif>
            <td class="py-3 px-4 font-bold text-slate-900">
                <div class="text-sm">{{ $p->title }}</div>
                @if($p->files_attached > 0 && $p->description)
                <a href="/{{ $p->description }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-indigo-600 hover:text-indigo-850 hover:underline mt-1 bg-indigo-50/70 px-2.5 py-1 rounded-lg border border-indigo-100 transition" onclick="event.stopPropagation();">
                    <x-icon name="download" class="w-3.5 h-3.5" /> View Activity Design File
                </a>
                @endif
            </td>
            <td class="py-3 px-4 text-slate-600 font-semibold">{{ $p->section?->section_name ?? 'N/A' }}</td>
            <td class="py-3 px-4 text-slate-500 font-medium text-sm">
                {{ $p->scheduled_date ? $p->scheduled_date->format('M d, Y') : 'N/A' }}
            </td>
            <td class="py-3 px-4 text-slate-500 font-medium text-sm">{{ $p->location }}</td>
            <td class="py-3 px-4">
                @if($p->status === 'Approved')
                    <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Approved
                    </span>
                @elseif($p->status === 'Pending')
                    <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 font-bold border border-amber-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending Approval
                    </span>
                @elseif($p->status === 'Rejected')
                    <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 font-bold border border-rose-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Rejected
                    </span>
                @elseif($p->status === 'Revision' || $p->status === 'revision')
                    <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 font-bold border border-rose-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Revision
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 font-bold border border-slate-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Draft
                    </span>
                @endif
            </td>
            <td class="py-3 px-4 text-right space-x-1.5" onclick="event.stopPropagation();">
                @if(in_array(strtolower($p->status ?? 'draft'), ['revision', 'rejected', 'draft']))
                <button onclick="openEditPlanModal('{{ $p->id }}', '{{ addslashes($p->title) }}', '{{ $p->section?->section_name === 'All Section' ? 'all' : $p->section_id }}', '{{ addslashes($p->location) }}', '{{ $p->scheduled_date ? $p->scheduled_date->format('Y-m-d') : '' }}', '{{ addslashes($p->objectives) }}', '{{ addslashes($p->description) }}', '{{ $p->status }}')" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition border border-indigo-100 cursor-pointer"
                        title="Edit Activity Plan">
                    <x-icon name="pencil" class="w-3.5 h-3.5" /> Edit
                </button>
                <form action="{{ route('instructor.plans.delete', $p->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete the activity plan \'{{ $p->title }}\'?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 transition border border-rose-100 cursor-pointer" title="Delete Plan">
                        <x-icon name="trash" class="w-3.5 h-3.5" /> Delete
                    </button>
                </form>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="6" class="py-16 text-center text-slate-400 text-sm">
                <div class="w-16 h-16 bg-slate-50 rounded-full flex items-center justify-center mx-auto text-slate-400 mb-4 shadow-sm">
                    <x-icon name="pencil" class="w-8 h-8" />
                </div>
                No activity plans created yet. Click "Create Activity Plan" to submit one.
            </td>
        </tr>
        @endforelse
    </x-table>

    @if($plans->hasPages())
        <div class="mt-4 px-4 py-3 border-t border-slate-100 bg-slate-50/50 rounded-b-xl">
            {{ $plans->links() }}
        </div>
    @endif
</x-card>

<!-- Create / Edit Plan Modal -->
<div id="planOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden transition duration-300">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-lg mx-4 overflow-hidden transform scale-95 duration-300">
        <form id="planForm" method="POST" action="{{ route('instructor.plans.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="planFormMethod" value="POST" />

            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-indigo-600 to-blue-600 text-white">
                <div>
                    <div class="font-extrabold tracking-tight text-lg" id="modalTitle">Create Activity Plan</div>
                    <div class="text-xs text-white/80 mt-0.5">Submit structured details for community activities</div>
                </div>
                <button type="button" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer" onclick="closePlanModal()">
                    <x-icon name="close" class="w-5 h-5" />
                </button>
            </div>
            
            <div class="p-6 space-y-4 text-sm max-h-[70vh] overflow-y-auto">
                <div>
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Activity Title <span class="text-rose-500">*</span></div>
                    <input type="text" name="title" id="formTitle" required placeholder="e.g. Coastal Cleanup & Waste Segregation Drive" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-300 transition" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Class Section <span class="text-rose-500">*</span></div>
                        <select name="section_id" id="formSection" required class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-300 transition">
                            <option value="" disabled selected>Select Class Section</option>
                            <option value="all">All Section</option>
                            @foreach($sections as $s)
                                @if($s->section_name !== 'All Section')
                                    <option value="{{ $s->id }}">{{ $s->section_name }} ({{ $s->component }})</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Scheduled Date <span class="text-rose-500">*</span></div>
                        <input type="date" name="scheduled_date" id="formDate" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-300 transition" />
                    </div>
                </div>

                <div>
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Venue / Location <span class="text-rose-500">*</span></div>
                    <input type="text" name="location" id="formLocation" required placeholder="e.g. Panabo City Coastal Area" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-300 transition" />
                </div>

                <div>
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Activity Objectives</div>
                    <textarea name="objectives" id="formObjectives" rows="3" placeholder="Enter objectives (one per line)..." class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-300 transition"></textarea>
                </div>

                <div>
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Submission of Activity Design (PDF Only) <span class="text-rose-500" id="fileRequiredStar">*</span></div>
                    <div class="flex items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer bg-slate-50 hover:bg-slate-100/50 hover:border-indigo-400 transition-all duration-200">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <x-icon name="upload" class="w-8 h-8 text-slate-400 mb-2" />
                                <p class="text-xs font-bold text-slate-500" id="fileUploadLabel">Upload Activity Design file</p>
                                <p class="text-[10px] text-slate-400 mt-1">PDF up to 20MB</p>
                            </div>
                            <input type="file" name="activity_design" id="formActivityDesign" class="hidden" accept="application/pdf" onchange="handleFileChange(this)" />
                        </label>
                    </div>
                    <div id="fileUploadInfo" class="hidden text-xs text-slate-600 mt-3 font-semibold flex items-center gap-1.5 bg-indigo-50 border border-indigo-100 p-2.5 rounded-xl">
                        <x-icon name="check2" class="w-4 h-4 text-emerald-500 shrink-0" />
                        <span id="fileNameDisplay" class="truncate">No file selected</span>
                    </div>
                </div>

                <div>
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Submission State <span class="text-rose-500">*</span></div>
                    <select name="status" id="formStatus" required class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-300 transition">
                        <option value="Draft">Draft (Save as work-in-progress)</option>
                        <option value="Pending">Pending Approval (Submit to Coordinator)</option>
                    </select>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50 rounded-b-2xl">
                <button type="button" class="px-4 py-2 text-sm font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 transition cursor-pointer" onclick="closePlanModal()">Cancel</button>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-md transition cursor-pointer">
                    <x-icon name="check2" class="w-4 h-4" /> Save Plan
                </button>
            </div>
        </form>
    </div>
</div>

<!-- View Revision Notes Modal -->
<div id="revisionDetailsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden transition duration-300">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-md mx-4 overflow-hidden transform scale-95 duration-300">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-rose-600 to-rose-700 bg-rose-600 text-white">
            <div>
                <div class="font-extrabold tracking-tight text-lg" id="revModalTitle">Revision Notes</div>
                <div class="text-xs text-white/80 mt-0.5">Details of changes requested by the coordinator</div>
            </div>
            <button type="button" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer" onclick="closeRevisionDetailsModal()">
                <x-icon name="close" class="w-5 h-5" />
            </button>
        </div>
        
        <div class="p-6 space-y-4 text-sm">
            <div class="bg-rose-50 border border-rose-100 rounded-xl p-4 text-rose-800 flex items-start gap-3">
                <x-icon name="alertc" class="w-5 h-5 text-rose-500 shrink-0 mt-0.5" />
                <div>
                    <span class="block text-xs font-bold text-rose-500 uppercase tracking-wider mb-1">Changes Requested</span>
                    <p class="text-sm font-medium leading-relaxed whitespace-pre-line text-slate-700" id="revModalFeedback">No notes provided.</p>
                </div>
            </div>
        </div>

        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50 rounded-b-2xl">
            <button type="button" class="px-4 py-2 text-sm font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 transition cursor-pointer" onclick="closeRevisionDetailsModal()">Close</button>
            <button type="button" id="revModalEditBtn" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-md transition cursor-pointer">
                <x-icon name="pencil" class="w-4 h-4" /> Edit Plan
            </button>
        </div>
    </div>
</div>

@push('scripts')
    <script>
        window.handleFileChange = function(input) {
            const info = document.getElementById('fileUploadInfo');
            const display = document.getElementById('fileNameDisplay');
            if (input.files && input.files.length > 0) {
                info.classList.remove('hidden');
                display.innerText = input.files[0].name;
            } else {
                info.classList.add('hidden');
            }
        }

        window.openCreatePlanModal = function() {
            document.getElementById('planForm').action = "{{ route('instructor.plans.store') }}";
            document.getElementById('planFormMethod').value = "POST";
            document.getElementById('modalTitle').innerText = "Create Activity Plan";

            document.getElementById('formTitle').value = "";
            document.getElementById('formLocation').value = "";
            document.getElementById('formDate').value = "";
            document.getElementById('formObjectives').value = "";
            document.getElementById('formStatus').value = "Draft";
            
            // File input settings for Create (required)
            document.getElementById('formActivityDesign').value = "";
            document.getElementById('formActivityDesign').required = true;
            document.getElementById('fileRequiredStar').classList.remove('hidden');
            document.getElementById('fileUploadInfo').classList.add('hidden');
            document.getElementById('fileNameDisplay').innerText = "No file selected";

            document.getElementById('planOverlay').classList.remove('hidden');
        }

        window.openEditPlanModal = function(id, title, sectionId, location, date, objectives, description, status) {
            document.getElementById('planForm').action = "{{ route('instructor.plans.update', ':id') }}".replace(':id', id);
            document.getElementById('planFormMethod').value = "PUT";
            document.getElementById('modalTitle').innerText = "Edit Activity Plan";

            document.getElementById('formTitle').value = title;
            document.getElementById('formSection').value = sectionId;
            document.getElementById('formLocation').value = location;
            document.getElementById('formDate').value = date;
            document.getElementById('formObjectives').value = objectives === 'null' ? '' : objectives;

            // File input settings for Edit (optional)
            document.getElementById('formActivityDesign').value = "";
            document.getElementById('formActivityDesign').required = false;
            document.getElementById('fileRequiredStar').classList.add('hidden');

            if (description && description !== 'null' && description.includes('uploads/')) {
                document.getElementById('fileUploadInfo').classList.remove('hidden');
                document.getElementById('fileNameDisplay').innerHTML = `<a href="/${description}" target="_blank" class="underline text-indigo-600 font-bold">Existing Activity Design File</a>`;
            } else {
                document.getElementById('fileUploadInfo').classList.add('hidden');
            }

            // Restrict status edits if it's already approved
            const statusSelect = document.getElementById('formStatus');
            statusSelect.value = (status === 'Approved' || status === 'Rejected') ? 'Pending' : status;

            document.getElementById('planOverlay').classList.remove('hidden');
        }

        window.closePlanModal = function() {
            document.getElementById('planOverlay').classList.add('hidden');
        }

        window.openRevisionDetailsModal = function(id, title, sectionId, location, date, objectives, description, status, feedback) {
            document.getElementById('revModalTitle').innerText = "Revision Notes: " + title;
            document.getElementById('revModalFeedback').innerText = feedback || "No feedback comments provided by the coordinator.";
            
            // Wire up the edit button to open the edit modal
            document.getElementById('revModalEditBtn').onclick = function() {
                closeRevisionDetailsModal();
                openEditPlanModal(id, title, sectionId, location, date, objectives, description, status);
            };
            
            document.getElementById('revisionDetailsModal').classList.remove('hidden');
        };

        window.closeRevisionDetailsModal = function() {
            document.getElementById('revisionDetailsModal').classList.add('hidden');
        };
    </script>
@endpush

@endsection
