@extends('layouts.rotc')

@section('title', 'Activity Designs - ROTC Command')

@section('content')

<x-page-header title="Activity Designs Submission" subtitle="Plan drill exercises, training, and community operations">
</x-page-header>


<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
    {{-- Left: Designs List --}}
    <div class="lg:col-span-2">
        <x-card title="Designs in Cycle" subtitle="All tracked designs">
            <x-table>
                <x-slot name="header">
                    <th class="py-2 px-3 font-medium">Activity</th>
                    <th class="py-2 px-3 font-medium">Platoon Section</th>
                    <th class="py-2 px-3 font-medium">Phase</th>
                    <th class="py-2 px-3 font-medium">Date</th>
                    <th class="py-2 px-3 font-medium w-[120px]">Status</th>
                    <th class="py-2 px-3 font-medium text-right w-[80px]">Actions</th>
                </x-slot>

                @forelse($designs as $r)
                @php
                    $isRevision = in_array(strtolower($r->status), ['revision', 'revisions']);
                @endphp
                <tr class="border-b border-slate-50 hover:bg-slate-50 transition @if($isRevision) cursor-pointer bg-rose-50/10 hover:bg-rose-50/20 @endif"
                    @if($isRevision)
                    onclick="openRevisionDetailsModal({{ $r->id }})"
                    @endif>
                    <td class="py-3 px-3">
                        <div class="text-slate-900 font-medium">{{ $r->title }}</div>
                    </td>
                    <td class="py-3 px-3 text-slate-600">
                        {{ $r->platoon_name ?? 'N/A' }}
                    </td>
                    <td class="py-3 px-3 text-slate-600">{{ $r->phase }}</td>
                    <td class="py-3 px-3 text-slate-600">{{ $r->date }}</td>
                    <td class="py-3 px-3">
                        @php
                            $statusMap = [
                                'draft'     => ['color' => 'slate',   'label' => 'Draft',        'icon' => 'pencil'],
                                'review'    => ['color' => 'indigo',  'label' => 'Under Review',  'icon' => 'clock'],
                                'pending'   => ['color' => 'indigo',  'label' => 'Under Review',  'icon' => 'clock'],
                                'approved'  => ['color' => 'emerald', 'label' => 'Approved',      'icon' => 'check'],
                                'revisions' => ['color' => 'rose',    'label' => 'Revisions',     'icon' => 'alertc'],
                                'revision'  => ['color' => 'rose',    'label' => 'Revisions',     'icon' => 'alertc'],
                                'rejected'  => ['color' => 'rose',    'label' => 'Rejected',      'icon' => 'alertc'],
                                'reject'    => ['color' => 'rose',    'label' => 'Rejected',      'icon' => 'alertc'],
                            ];
                            $s = $statusMap[$r->status] ?? ['color' => 'slate', 'label' => 'Draft', 'icon' => 'pencil'];
                        @endphp
                        @if($isRevision)
                        <span onclick="openRevisionDetailsModal({{ $r->id }}); event.stopPropagation();"
                              class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-100 font-medium cursor-pointer hover:bg-rose-100 transition"
                              title="Click to view details and edit">
                            <x-icon :name="$s['icon']" class="w-3 h-3 animate-pulse text-rose-500" />
                            {{ $s['label'] }}
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-{{ $s['color'] }}-50 text-{{ $s['color'] }}-700 font-medium">
                            <x-icon :name="$s['icon']" class="w-3 h-3" />
                            {{ $s['label'] }}
                        </span>
                        @endif
                    </td>
                    <td class="py-3 px-3 text-right" onclick="event.stopPropagation();">
                        @if(in_array(strtolower($r->status), ['draft', 'revision', 'revisions', 'rejected', 'reject']))
                        <div class="flex items-center justify-end gap-1">
                            <button type="button" 
                                onclick="openEditModal({{ $r->id }})"
                                class="text-indigo-600 hover:text-indigo-850 p-1 transition" 
                                title="Edit Design">
                                <x-icon name="pencil" class="w-4 h-4" />
                            </button>
                            <form action="{{ route('rotc.designs.delete', $r->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this Activity Design?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800 p-1 transition" title="Delete Design">
                                    <x-icon name="trash" class="w-4 h-4" />
                                </button>
                            </form>
                        </div>
                        @else
                        <span class="text-slate-300">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr><td colspan="5" class="py-8 text-center text-slate-400 text-sm">No activity designs found.</td></tr>
                @endforelse
            </x-table>
        </x-card>
    </div>

    {{-- Right: New Design Form --}}
    <div class="lg:col-span-1">
        <x-card title="New Design Brief">
            <form action="{{ route('rotc.designs.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4 text-sm">
                @csrf
                <div>
                    <label class="block text-[11px] uppercase tracking-[0.18em] text-slate-500 mb-1">Activity Title</label>
                    <input type="text" name="title" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-slate-400 transition" placeholder="e.g. Tactical Drill Sequence" />
                </div>
                <div>
                    <label class="block text-[11px] uppercase tracking-[0.18em] text-slate-500 mb-1">Platoon Section</label>
                    <select name="section_id" required class="w-full px-3 py-2 rounded-lg border border-slate-200 bg-white focus:outline-none focus:border-slate-400 transition">
                        <option value="" disabled selected>Select Platoon Section</option>
                        <option value="all">All Section</option>
                        @foreach($sections as $sec)
                            @if($sec->section_name !== 'All Section')
                                <option value="{{ $sec->id }}">{{ $sec->section_name }} Platoon</option>
                            @endif
                        @endforeach
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-[11px] uppercase tracking-[0.18em] text-slate-500 mb-1">Date</label>
                        <input type="date" name="scheduled_date" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-slate-400 transition" />
                    </div>
                    <div>
                        <label class="block text-[11px] uppercase tracking-[0.18em] text-slate-500 mb-1">Phase / Venue</label>
                        <input type="text" name="location" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-slate-400 transition" placeholder="e.g. Campus Field" />
                    </div>
                </div>
                <div>
                    <label class="block text-[11px] uppercase tracking-[0.18em] text-slate-500 mb-1">Objectives</label>
                    <textarea name="objectives" rows="3" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-slate-400 transition" placeholder="Mission, expected outcomes, safety considerations..."></textarea>
                </div>
                <div>
                    <label class="block text-[11px] uppercase tracking-[0.18em] text-slate-500 mb-1">Supplementary Document</label>
                    <label class="block border-2 border-dashed border-slate-200 rounded-lg p-4 text-center bg-slate-50/50 cursor-pointer hover:bg-slate-50 hover:border-slate-300 transition">
                        <x-icon name="upload" class="w-5 h-5 text-slate-400 mx-auto" />
                        <span class="text-xs text-slate-500 mt-1 block" id="file-label">Attach PDF document</span>
                        <input type="file" name="activity_design" accept=".pdf" class="hidden" onchange="document.getElementById('file-label').textContent = this.files[0] ? this.files[0].name : 'Attach PDF document'" />
                    </label>
                </div>
                <div class="flex items-center gap-2 pt-2">
                    <button type="submit" name="status" value="Draft" class="flex-1 px-4 py-2 text-sm rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 transition">Save Draft</button>
                    <button type="submit" name="status" value="Pending" class="flex-1 px-4 py-2 text-sm rounded-lg bg-slate-900 text-white hover:bg-slate-800 transition">Submit Design</button>
                </div>
            </form>
        </x-card>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════════ --}}
{{-- EDIT TRAINING DESIGN MODAL                                                     --}}
{{-- ══════════════════════════════════════════════════════════════════════════════ --}}
<div id="editDesignModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-auto flex flex-col max-h-[90vh] border border-slate-100">

        {{-- Header --}}
        <div class="px-6 py-5 border-b border-slate-100 flex items-start justify-between shrink-0">
            <div>
                <h2 class="text-slate-900 font-bold text-base leading-snug">Edit Training Design Brief</h2>
                <div class="text-xs text-slate-400 font-medium mt-0.5" id="editModalSubtitle">Modify your training design details</div>
            </div>
            <button onclick="closeEditModal()" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition shrink-0 mt-0.5">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Scrollable Form Body --}}
        <form id="editDesignForm" action="" method="POST" enctype="multipart/form-data" class="overflow-y-auto flex-1 px-6 py-5 space-y-4 text-sm">
            @csrf
            @method('PUT')

            {{-- Revision Note Alert --}}
            <div id="editModalRevisionNote" class="hidden p-4 rounded-xl bg-rose-50 border border-rose-100 text-xs text-rose-800 flex items-start gap-3">
                <x-icon name="alertc" class="w-5 h-5 shrink-0 text-rose-500" />
                <div class="space-y-1">
                    <span class="font-bold block">Revision Notes from Coordinator:</span>
                    <p id="editModalFeedbackText" class="leading-relaxed"></p>
                </div>
            </div>

            <div>
                <label class="block text-[11px] uppercase tracking-[0.18em] text-slate-500 mb-1">Activity Title</label>
                <input type="text" id="edit_title" name="title" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-slate-400 transition" />
            </div>

            <div>
                <label class="block text-[11px] uppercase tracking-[0.18em] text-slate-500 mb-1">Platoon Section</label>
                <select id="edit_section_id" name="section_id" required class="w-full px-3 py-2 rounded-lg border border-slate-200 bg-white focus:outline-none focus:border-slate-400 transition">
                    <option value="all">All Section</option>
                    @foreach($sections as $sec)
                        @if($sec->section_name !== 'All Section')
                            <option value="{{ $sec->id }}">{{ $sec->section_name }} Platoon</option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-[11px] uppercase tracking-[0.18em] text-slate-500 mb-1">Date</label>
                    <input type="date" id="edit_scheduled_date" name="scheduled_date" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-slate-400 transition" />
                </div>
                <div>
                    <label class="block text-[11px] uppercase tracking-[0.18em] text-slate-500 mb-1">Phase / Venue</label>
                    <input type="text" id="edit_location" name="location" required class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-slate-400 transition" />
                </div>
            </div>

            <div>
                <label class="block text-[11px] uppercase tracking-[0.18em] text-slate-500 mb-1">Objectives</label>
                <textarea id="edit_objectives" name="objectives" rows="3" class="w-full px-3 py-2 rounded-lg border border-slate-200 focus:outline-none focus:border-slate-400 transition"></textarea>
            </div>

            <div>
                <label class="block text-[11px] uppercase tracking-[0.18em] text-slate-500 mb-1">Supplementary Document</label>
                <label class="block border-2 border-dashed border-slate-200 rounded-lg p-4 text-center bg-slate-50/50 cursor-pointer hover:bg-slate-50 hover:border-slate-300 transition">
                    <x-icon name="upload" class="w-5 h-5 text-slate-400 mx-auto" />
                    <span class="text-xs text-slate-500 mt-1 block" id="edit-file-label">Attach new PDF document (optional)</span>
                    <input type="file" name="activity_design" accept=".pdf" class="hidden" onchange="document.getElementById('edit-file-label').textContent = this.files[0] ? this.files[0].name : 'Attach new PDF document (optional)'" />
                </label>
            </div>

            {{-- Form Buttons inside scroll area --}}
            <div class="flex items-center gap-2 pt-4 border-t border-slate-100 shrink-0">
                <button type="button" onclick="closeEditModal()" class="px-4 py-2 text-sm rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 transition mr-auto">Cancel</button>
                <button type="submit" name="status" value="Draft" class="px-4 py-2 text-sm rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 transition">Save Draft</button>
                <button type="submit" name="status" value="Pending" class="px-4 py-2 text-sm rounded-lg bg-slate-900 text-white hover:bg-slate-800 transition">Resubmit Design</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════════ --}}
{{-- VIEW REVISION NOTES MODAL                                                      --}}
{{-- ══════════════════════════════════════════════════════════════════════════════ --}}
<div id="revisionDetailsModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden transition duration-300">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-md mx-4 overflow-hidden transform scale-95 duration-300">
        <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-rose-600 text-white">
            <div>
                <div class="font-extrabold tracking-tight text-lg" id="revModalTitle">Revision Notes</div>
                <div class="text-xs text-white/80 mt-0.5">Details of changes requested by the coordinator</div>
            </div>
            <button type="button" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer" onclick="closeRevisionDetailsModal()">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
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
            <button type="button" id="revModalEditBtn" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold rounded-xl bg-slate-900 text-white hover:bg-slate-800 shadow-md transition cursor-pointer">
                <x-icon name="pencil" class="w-4 h-4" /> Edit Design
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const allDesigns = @json($designs);

    function openEditModal(designId) {
        const design = allDesigns.find(d => d.id == designId);
        if (!design) return;

        // Set form action route dynamically
        const form = document.getElementById('editDesignForm');
        form.action = '/rotc/designs/' + design.id;

        // Populate fields
        document.getElementById('edit_title').value = design.title;
        if (design.platoon_name === 'All Section') {
            document.getElementById('edit_section_id').value = 'all';
        } else {
            document.getElementById('edit_section_id').value = design.section_id || 'all';
        }
        document.getElementById('edit_scheduled_date').value = design.raw_date;
        document.getElementById('edit_location').value = design.phase;
        document.getElementById('edit_objectives').value = design.objectives || '';

        // Reset file label
        document.getElementById('edit-file-label').textContent = 'Attach new PDF document (optional)';

        // Show/hide revision note
        const revNote = document.getElementById('editModalRevisionNote');
        const feedbackText = document.getElementById('editModalFeedbackText');
        if (design.feedback && design.feedback.trim() !== '') {
            feedbackText.textContent = design.feedback;
            revNote.classList.remove('hidden');
        } else {
            feedbackText.textContent = '';
            revNote.classList.add('hidden');
        }

        // Display modal
        document.getElementById('editDesignModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeEditModal() {
        document.getElementById('editDesignModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Close on background click
    document.getElementById('editDesignModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditModal();
    });

    function openRevisionDetailsModal(designId) {
        const design = allDesigns.find(d => d.id == designId);
        if (!design) return;

        document.getElementById('revModalTitle').innerText = "Revision Notes: " + design.title;
        document.getElementById('revModalFeedback').innerText = design.feedback || "No feedback comments provided by the coordinator.";
        
        // Wire up the edit button to open the edit modal
        document.getElementById('revModalEditBtn').onclick = function() {
            closeRevisionDetailsModal();
            openEditModal(design.id);
        };
        
        document.getElementById('revisionDetailsModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeRevisionDetailsModal() {
        document.getElementById('revisionDetailsModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Close on background click for revision modal
    document.getElementById('revisionDetailsModal').addEventListener('click', function(e) {
        if (e.target === this) closeRevisionDetailsModal();
    });
</script>
@endpush

@endsection
