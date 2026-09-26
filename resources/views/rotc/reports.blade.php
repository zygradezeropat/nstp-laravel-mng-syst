@extends('layouts.rotc')

@section('title', 'Report Submission - ROTC Command')

@section('content')

<x-page-header title="Report Submission Overview" subtitle="Document and submit completed ROTC activities">
</x-page-header>


<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
    {{-- LEFT: All Reports --}}
    <div class="lg:col-span-2">
        <x-card title="All Reports">
            <div class="-mx-5 -my-5 divide-y divide-slate-100">
                @forelse($reports as $r)
                @php
                    $isRevision = in_array(strtolower($r->raw_status ?? $r->status), ['revision', 'revisions']);
                @endphp
                <div class="px-5 py-4 flex items-center justify-between gap-3 hover:bg-slate-50 transition @if($isRevision) cursor-pointer bg-rose-50/10 hover:bg-rose-50/20 @endif"
                     @if($isRevision)
                     onclick="openRevisionDetailsModal({{ $r->id }})"
                     @endif>
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-9 h-9 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center shrink-0">
                            <x-icon name="filetext" class="w-4 h-4" />
                        </div>
                        <div class="min-w-0">
                            <div class="text-sm font-semibold text-slate-900 truncate">{{ $r->title }}</div>
                            <div class="text-xs text-slate-500 flex items-center gap-2 mt-0.5">
                                <span>Completed: {{ $r->due }}</span>
                                @if($r->files_attached && $r->report_file_path)
                                    <span class="text-slate-300">·</span>
                                    <a href="{{ asset($r->report_file_path) }}" target="_blank" class="inline-flex items-center gap-0.5 text-indigo-600 hover:text-indigo-800 font-medium" onclick="event.stopPropagation();">
                                        <x-icon name="download" class="w-3 h-3" /> PDF
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="flex items-center gap-4" onclick="event.stopPropagation();">
                        @if($isRevision)
                        <span onclick="openRevisionDetailsModal({{ $r->id }})"
                              class="text-xs px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-100 font-medium cursor-pointer hover:bg-rose-100 transition animate-pulse"
                              title="Click to view details and edit">
                            Revision
                        </span>
                        @else
                        <span class="text-xs px-2.5 py-0.5 rounded-full bg-{{ $r->color }}-50 text-{{ $r->color }}-700 font-medium capitalize">{{ $r->status }}</span>
                        @endif
                        
                        @if(in_array(strtolower($r->raw_status ?? $r->status), ['draft', 'revision', 'revisions', 'rejected', 'reject']))
                        <div class="flex items-center gap-1.5">
                            <button type="button" 
                                onclick="openEditReportModal({{ $r->id }})"
                                class="text-indigo-600 hover:text-indigo-850 p-1 transition" 
                                title="Edit Report">
                                <x-icon name="pencil" class="w-4 h-4" />
                            </button>
                            <form action="{{ route('rotc.reports.delete', $r->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this Accomplishment Report?');" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-600 hover:text-rose-800 p-1 transition" title="Delete Report">
                                    <x-icon name="trash" class="w-4 h-4" />
                                </button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
                @empty
                <div class="py-12 text-center text-slate-400 text-sm">No accomplishment reports found.</div>
                @endforelse
            </div>
        </x-card>
    </div>

    {{-- RIGHT: New Report Draft --}}
    <div class="lg:col-span-1">
        <x-card title="New Report Draft">
            <form action="{{ route('rotc.reports.store') }}" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                {{-- Linked Activity --}}
                <div>
                    <label class="block text-[11px] uppercase tracking-[0.18em] text-slate-500 mb-1.5">Linked Activity Plan</label>
                    <select name="activity_plan_id" required class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-900 bg-white focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-300">
                        <option value="" disabled selected>Select Approved Activity</option>
                        @foreach($approvedPlans as $plan)
                            <option value="{{ $plan->id }}">{{ $plan->title }} ({{ $plan->section?->section_name ?? 'N/A' }})</option>
                        @endforeach
                    </select>
                </div>

                {{-- Platoon Section --}}
                <div>
                    <label class="block text-[11px] uppercase tracking-[0.18em] text-slate-500 mb-1.5">Platoon Section</label>
                    <select name="section_id" required class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-900 bg-white focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-300">
                        <option value="" disabled selected>Select Platoon Section</option>
                        <option value="all">All Section</option>
                        @foreach($sections as $sec)
                            @if($sec->section_name !== 'All Section')
                                <option value="{{ $sec->id }}">{{ $sec->section_name }} Platoon</option>
                            @endif
                        @endforeach
                    </select>
                </div>

                {{-- Completed Date --}}
                <div>
                    <label class="block text-[11px] uppercase tracking-[0.18em] text-slate-500 mb-1.5">Date Completed</label>
                    <input type="date" name="completed_date" required class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-300" />
                </div>

                {{-- Narrative --}}
                <div>
                    <label class="block text-[11px] uppercase tracking-[0.18em] text-slate-500 mb-1.5">Narrative & Accomplishments</label>
                    <textarea name="accomplishments" rows="4" placeholder="Describe the activity, outputs, and impact…" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-300 resize-none"></textarea>
                </div>

                {{-- File Upload --}}
                <div>
                    <label class="block text-[11px] uppercase tracking-[0.18em] text-slate-500 mb-1.5">Attachments</label>
                    <label class="block border-2 border-dashed border-slate-200 rounded-md p-6 text-center hover:border-slate-300 transition cursor-pointer bg-slate-50/50">
                        <x-icon name="upload" class="w-6 h-6 text-slate-400 mx-auto" />
                        <span class="text-xs text-slate-500 mt-2 block" id="file-label-report">Click to upload PDF or image</span>
                        <input type="file" name="report_file" accept=".pdf,image/*" class="hidden" onchange="document.getElementById('file-label-report').textContent = this.files[0] ? this.files[0].name : 'Click to upload PDF or image'" />
                    </label>
                </div>

                {{-- Buttons --}}
                <div class="flex items-center gap-3 pt-2">
                    <button type="submit" name="status" value="Draft" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 rounded-md border border-slate-200 text-sm text-slate-700 hover:bg-slate-50 transition cursor-pointer">
                        Save Draft
                    </button>
                    <button type="submit" name="status" value="Pending" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-2 rounded-md bg-slate-900 text-white text-sm hover:bg-slate-800 transition cursor-pointer">
                        <x-icon name="send" class="w-4 h-4" /> Submit
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════════ --}}
{{-- EDIT ACCOMPLISHMENT REPORT MODAL                                               --}}
{{-- ══════════════════════════════════════════════════════════════════════════════ --}}
<div id="editReportModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-auto flex flex-col max-h-[90vh] border border-slate-100">

        {{-- Header --}}
        <div class="px-6 py-5 border-b border-slate-100 flex items-start justify-between shrink-0">
            <div>
                <h2 class="text-slate-900 font-bold text-base leading-snug">Edit Accomplishment Report</h2>
                <div class="text-xs text-slate-400 font-medium mt-0.5" id="editReportModalSubtitle">Modify your report details</div>
            </div>
            <button onclick="closeEditReportModal()" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition shrink-0 mt-0.5">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Scrollable Form Body --}}
        <form id="editReportForm" action="" method="POST" enctype="multipart/form-data" class="overflow-y-auto flex-1 px-6 py-5 space-y-4 text-sm">
            @csrf
            @method('PUT')

            {{-- Revision Note Alert --}}
            <div id="editReportModalRevisionNote" class="hidden p-4 rounded-xl bg-rose-50 border border-rose-100 text-xs text-rose-800 flex items-start gap-3">
                <x-icon name="alertc" class="w-5 h-5 shrink-0 text-rose-500" />
                <div class="space-y-1">
                    <span class="font-bold block">Revision Notes from Coordinator:</span>
                    <p id="editReportModalFeedbackText" class="leading-relaxed"></p>
                </div>
            </div>

            {{-- Linked Activity --}}
            <div>
                <label class="block text-[11px] uppercase tracking-[0.18em] text-slate-500 mb-1.5">Linked Activity Plan</label>
                <select name="activity_plan_id" id="edit_activity_plan_id" required class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-900 bg-white focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-300">
                    <option value="" disabled selected>Select Approved Activity</option>
                    @foreach($approvedPlans as $plan)
                        <option value="{{ $plan->id }}">{{ $plan->title }} ({{ $plan->section?->section_name ?? 'N/A' }})</option>
                    @endforeach
                </select>
            </div>

            {{-- Platoon Section --}}
            <div>
                <label class="block text-[11px] uppercase tracking-[0.18em] text-slate-500 mb-1.5">Platoon Section</label>
                <select name="section_id" id="edit_section_id" required class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-900 bg-white focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-300">
                    <option value="all">All Section</option>
                    @foreach($sections as $sec)
                        @if($sec->section_name !== 'All Section')
                            <option value="{{ $sec->id }}">{{ $sec->section_name }} Platoon</option>
                        @endif
                    @endforeach
                </select>
            </div>

            {{-- Completed Date --}}
            <div>
                <label class="block text-[11px] uppercase tracking-[0.18em] text-slate-500 mb-1.5">Date Completed</label>
                <input type="date" name="completed_date" id="edit_completed_date" required class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-900 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-300" />
            </div>

            {{-- Narrative --}}
            <div>
                <label class="block text-[11px] uppercase tracking-[0.18em] text-slate-500 mb-1.5">Narrative & Accomplishments</label>
                <textarea name="accomplishments" id="edit_accomplishments" rows="4" placeholder="Describe the activity, outputs, and impact…" class="w-full rounded-md border border-slate-200 px-3 py-2 text-sm text-slate-900 placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-slate-900/10 focus:border-slate-300 resize-none"></textarea>
            </div>

            {{-- File Upload --}}
            <div>
                <label class="block text-[11px] uppercase tracking-[0.18em] text-slate-500 mb-1.5">Attachments</label>
                <label class="block border-2 border-dashed border-slate-200 rounded-md p-6 text-center hover:border-slate-300 transition cursor-pointer bg-slate-50/50">
                    <x-icon name="upload" class="w-6 h-6 text-slate-400 mx-auto" />
                    <span class="text-xs text-slate-500 mt-2 block" id="edit-file-label-report-modal">Click to upload new PDF or image (optional)</span>
                    <input type="file" name="report_file" accept=".pdf,image/*" class="hidden" onchange="document.getElementById('edit-file-label-report-modal').textContent = this.files[0] ? this.files[0].name : 'Click to upload new PDF or image (optional)'" />
                </label>
            </div>

            {{-- Form Buttons --}}
            <div class="flex items-center gap-2 pt-4 border-t border-slate-100 shrink-0">
                <button type="button" onclick="closeEditReportModal()" class="px-4 py-2 text-sm rounded-lg border border-slate-200 text-slate-500 hover:bg-slate-50 transition mr-auto">Cancel</button>
                <button type="submit" name="status" value="Draft" class="px-4 py-2 text-sm rounded-lg border border-slate-200 text-slate-700 hover:bg-slate-50 transition">Save Draft</button>
                <button type="submit" name="status" value="Pending" class="px-4 py-2 text-sm rounded-lg bg-slate-900 text-white hover:bg-slate-800 transition">Resubmit Report</button>
            </div>
        </form>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════════ --}}
{{-- VIEW REVISION NOTES MODAL FOR REPORTS                                          --}}
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
                <x-icon name="pencil" class="w-4 h-4" /> Edit Report
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    const allReports = @json($reports);

    function openEditReportModal(reportId) {
        const report = allReports.find(r => r.id == reportId);
        if (!report) return;

        const form = document.getElementById('editReportForm');
        form.action = '/rotc/reports/' + report.id;

        // Populate fields
        document.getElementById('edit_activity_plan_id').value = report.activity_plan_id;
        if (report.platoon_name === 'All Section') {
            document.getElementById('edit_section_id').value = 'all';
        } else {
            document.getElementById('edit_section_id').value = report.section_id || 'all';
        }
        document.getElementById('edit_completed_date').value = report.raw_completed_date;
        document.getElementById('edit_accomplishments').value = report.accomplishments || '';

        // Reset file label
        document.getElementById('edit-file-label-report-modal').textContent = 'Click to upload new PDF or image (optional)';

        // Show/hide revision note
        const revNote = document.getElementById('editReportModalRevisionNote');
        const feedbackText = document.getElementById('editReportModalFeedbackText');
        if (report.feedback && report.feedback.trim() !== '') {
            feedbackText.textContent = report.feedback;
            revNote.classList.remove('hidden');
        } else {
            feedbackText.textContent = '';
            revNote.classList.add('hidden');
        }

        // Display modal
        document.getElementById('editReportModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closeEditReportModal() {
        document.getElementById('editReportModal').classList.add('hidden');
        document.body.style.overflow = '';
    }

    // Close on background click
    document.getElementById('editReportModal').addEventListener('click', function(e) {
        if (e.target === this) closeEditReportModal();
    });

    function openRevisionDetailsModal(reportId) {
        const report = allReports.find(r => r.id == reportId);
        if (!report) return;

        document.getElementById('revModalTitle').innerText = "Revision Notes: " + report.title;
        document.getElementById('revModalFeedback').innerText = report.feedback || "No feedback comments provided by the coordinator.";
        
        // Wire up the edit button to open the edit modal
        document.getElementById('revModalEditBtn').onclick = function() {
            closeRevisionDetailsModal();
            openEditReportModal(report.id);
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
