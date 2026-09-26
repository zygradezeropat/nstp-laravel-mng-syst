@extends('layouts.instructor')

@section('title', 'Accomplishment Reports - Instructor Console')

@section('content')

<x-page-header title="Accomplishment Reports" subtitle="Submit and track accomplishment reports for your assigned sections">
    <x-slot name="actions">
        <button onclick="openCreateReportModal()" class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-emerald-600 text-white text-sm shadow-md hover:bg-emerald-700 hover:shadow-lg transition cursor-pointer font-semibold">
            <x-icon name="plus" class="w-4 h-4" /> New Accomplishment Report
        </button>
    </x-slot>
</x-page-header>


<x-card title="Accomplishment Reports List" subtitle="Track and manage all submitted accomplishment reports" class="mt-6">
    <x-table>
        <x-slot name="header">
            <th class="py-3.5 px-4 font-semibold text-slate-600">Report Title</th>
            <th class="py-3.5 px-4 font-semibold text-slate-600">Section</th>
            <th class="py-3.5 px-4 font-semibold text-slate-600">Completed Date</th>
            <th class="py-3.5 px-4 font-semibold text-slate-600">Participants</th>
            <th class="py-3.5 px-4 font-semibold text-slate-600">Status</th>
            <th class="py-3.5 px-4 font-semibold text-slate-600 text-right">Actions</th>
        </x-slot>

        @forelse($reports as $r)
        <tr class="border-b border-slate-50 hover:bg-slate-50/50 transition">
            <td class="py-3 px-4 font-bold text-slate-900">
                <div class="text-sm">{{ $r->title }}</div>
                @if(!empty($r->feedback))
                <div class="text-xs text-rose-600 mt-1.5 bg-rose-50 border border-rose-100 rounded-lg p-2 flex items-start gap-1 font-medium">
                    <x-icon name="alertc" class="w-3.5 h-3.5 mt-0.5 shrink-0" />
                    <span><strong>Revision Note:</strong> {{ $r->feedback }}</span>
                </div>
                @endif
                @if($r->report_file_path)
                <a href="/{{ $r->report_file_path }}" target="_blank" class="inline-flex items-center gap-1 text-[11px] font-bold text-emerald-600 hover:text-emerald-850 hover:underline mt-1 bg-emerald-50/70 px-2.5 py-1 rounded-lg border border-emerald-100 transition">
                    <x-icon name="download" class="w-3.5 h-3.5" /> View Accomplishment Report File
                </a>
                @endif
                @if($r->accomplishments)
                <div class="text-xs text-slate-400 font-medium mt-0.5 truncate max-w-md">{{ $r->accomplishments }}</div>
                @endif
            </td>
            <td class="py-3 px-4 text-slate-600 font-semibold">{{ $r->section?->section_name ?? 'N/A' }}</td>
            <td class="py-3 px-4 text-slate-500 font-medium text-sm">
                {{ $r->completed_date ? $r->completed_date->format('M d, Y') : 'N/A' }}
            </td>
            <td class="py-3 px-4 text-slate-500 font-medium text-sm">{{ $r->participants_count }} participants</td>
            <td class="py-3 px-4">
                @if($r->status === 'Reviewed')
                    <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-bold border border-emerald-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Approved / Reviewed
                    </span>
                @elseif($r->status === 'Pending')
                    <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 font-bold border border-amber-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> Pending Review
                    </span>
                @elseif($r->status === 'Revision')
                    <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 font-bold border border-rose-100">
                        <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> Needs Revision
                    </span>
                @else
                    <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 font-bold border border-slate-200">
                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Draft
                    </span>
                @endif
            </td>
            <td class="py-3 px-4 text-right space-x-1.5" onclick="event.stopPropagation();">
                @if(strtolower($r->status ?? '') !== 'reviewed')
                <button onclick="openEditReportModal('{{ $r->id }}', '{{ addslashes($r->title) }}', '{{ $r->section?->section_name === 'All Section' ? 'all' : $r->section_id }}', '{{ addslashes($r->location) }}', '{{ $r->completed_date ? $r->completed_date->format('Y-m-d') : '' }}', '{{ $r->participants_count }}', '{{ addslashes($r->accomplishments) }}', '{{ addslashes($r->report_file_path) }}', '{{ $r->status }}')" 
                        class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition border border-indigo-100 cursor-pointer"
                        title="Edit Accomplishment Report">
                    <x-icon name="pencil" class="w-3.5 h-3.5" /> Edit
                </button>
                @endif
                @if(in_array(strtolower($r->status ?? 'draft'), ['draft', 'revision', 'rejected', 'reviewed']))
                <form action="{{ route('instructor.reports.delete', $r->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Are you sure you want to delete the accomplishment report \'{{ $r->title }}\'?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-lg bg-rose-50 text-rose-700 hover:bg-rose-100 transition border border-rose-100 cursor-pointer" title="Delete Report">
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
                    <x-icon name="filecheck" class="w-8 h-8" />
                </div>
                No accomplishment reports created yet. Click "New Accomplishment Report" to submit one.
            </td>
        </tr>
        @endforelse
    </x-table>

    @if($reports->hasPages())
        <div class="mt-4 px-4 py-3 border-t border-slate-100 bg-slate-50/50 rounded-b-xl">
            {{ $reports->links() }}
        </div>
    @endif
</x-card>

<!-- Create / Edit Report Modal -->
<div id="reportOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden transition duration-300">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-lg mx-4 overflow-hidden transform scale-95 duration-300">
        <form id="reportForm" method="POST" action="{{ route('instructor.reports.store') }}" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="reportFormMethod" value="POST" />

            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-emerald-600 to-teal-600 text-white">
                <div>
                    <div class="font-extrabold tracking-tight text-lg" id="modalTitle">New Accomplishment Report</div>
                    <div class="text-xs text-white/80 mt-0.5">Submit completion metrics and outcomes for community activities</div>
                </div>
                <button type="button" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer" onclick="closeReportModal()">
                    <x-icon name="close" class="w-5 h-5" />
                </button>
            </div>
            
            <div class="p-6 space-y-4 text-sm max-h-[70vh] overflow-y-auto">
                <!-- Helper Banner if empty approved plans -->
                @if($approvedPlans->isEmpty())
                <div class="p-3.5 bg-amber-50 text-amber-800 border border-amber-200 rounded-xl text-xs flex items-start gap-2 mb-2 font-medium">
                    <x-icon name="alertc" class="w-4 h-4 shrink-0 mt-0.5 text-amber-600" />
                    <div>
                        <strong class="font-bold">No Approved Activity Plans Found!</strong>
                        <p class="mt-0.5 text-amber-700">You must have an **Approved** Activity Plan first before you can submit an Accomplishment Report. Create a plan and wait for Coordinator approval.</p>
                    </div>
                </div>
                @endif

                <div>
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Approved Activity Plan <span class="text-rose-500">*</span></div>
                    <select name="activity_plan_id" id="formPlan" required onchange="autoFillPlanDetails(this)" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-300 transition">
                        <option value="">-- Select Approved Activity Plan --</option>
                        @foreach($approvedPlans as $ap)
                            <option value="{{ $ap->id }}" data-location="{{ $ap->location }}" data-section="{{ $ap->section?->section_name === 'All Section' ? 'all' : $ap->section_id }}">{{ $ap->title }} ({{ $ap->section?->section_name }})</option>
                        @endforeach
                    </select>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Class Section <span class="text-rose-500">*</span></div>
                        <select name="section_id" id="formSection" required class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-300 transition">
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
                        <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Completed Date <span class="text-rose-500">*</span></div>
                        <input type="date" name="completed_date" id="formDate" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-300 transition" />
                    </div>
                </div>



                <div>
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Key Accomplishments achieved</div>
                    <textarea name="accomplishments" id="formAccomplishments" rows="4" placeholder="Describe workshop outcomes, activities completed, or items distributed..." class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-300 transition"></textarea>
                </div>

                <div>
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Submission of Accomplishment Report (PDF/Word/Image) <span class="text-rose-500" id="fileRequiredStar">*</span></div>
                    <div class="flex items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-slate-300 rounded-xl cursor-pointer bg-slate-50 hover:bg-slate-100/50 hover:border-emerald-400 transition-all duration-200">
                            <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                <x-icon name="upload" class="w-8 h-8 text-slate-400 mb-2" />
                                <p class="text-xs font-bold text-slate-500" id="fileUploadLabel">Upload Accomplishment Report file</p>
                                <p class="text-[10px] text-slate-400 mt-1">PDF, DOCX, PNG, JPG up to 20MB</p>
                            </div>
                            <input type="file" name="report_file" id="formReportFile" class="hidden" accept=".pdf,.doc,.docx,.png,.jpg,.jpeg" onchange="handleReportFileChange(this)" />
                        </label>
                    </div>
                    <div id="reportFileUploadInfo" class="hidden text-xs text-slate-600 mt-3 font-semibold flex items-center gap-1.5 bg-emerald-50 border border-emerald-100 p-2.5 rounded-xl">
                        <x-icon name="check2" class="w-4 h-4 text-emerald-500 shrink-0" />
                        <span id="reportFileNameDisplay" class="truncate">No file selected</span>
                    </div>
                </div>

                <div>
                    <div class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5">Submission State <span class="text-rose-500">*</span></div>
                    <select name="status" id="formStatus" required class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-4 focus:ring-emerald-100 focus:border-emerald-300 transition">
                        <option value="Draft">Draft (Save as work-in-progress)</option>
                        <option value="Pending">Pending Review (Submit to Coordinator)</option>
                    </select>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50 rounded-b-2xl">
                <button type="button" class="px-4 py-2 text-sm font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 transition cursor-pointer" onclick="closeReportModal()">Cancel</button>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold rounded-xl bg-emerald-600 text-white hover:bg-emerald-700 shadow-md transition cursor-pointer">
                    <x-icon name="send" class="w-4 h-4" /> Submit Report
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
    <script>
        window.autoFillPlanDetails = function(select) {
            const selectedOption = select.options[select.selectedIndex];
            if (selectedOption && selectedOption.value !== "") {
                const sec = selectedOption.getAttribute('data-section');
                if (sec) document.getElementById('formSection').value = sec;
            }
        }

        window.handleReportFileChange = function(input) {
            const info = document.getElementById('reportFileUploadInfo');
            const display = document.getElementById('reportFileNameDisplay');
            if (input.files && input.files.length > 0) {
                const file = input.files[0];
                const allowedExtensions = /(\.pdf|\.doc|\.docx|\.png|\.jpg|\.jpeg)$/i;
                if (!allowedExtensions.exec(file.name)) {
                    alert('Invalid file type. Only PDF, Word Documents (DOC/DOCX), and Images (PNG/JPG/JPEG) are accepted.');
                    input.value = '';
                    info.classList.add('hidden');
                    return false;
                }
                info.classList.remove('hidden');
                display.innerText = file.name;
            } else {
                info.classList.add('hidden');
            }
        }

        window.openCreateReportModal = function() {
            document.getElementById('reportForm').action = "{{ route('instructor.reports.store') }}";
            document.getElementById('reportFormMethod').value = "POST";
            document.getElementById('modalTitle').innerText = "New Accomplishment Report";

            document.getElementById('formPlan').value = "";
            document.getElementById('formDate').value = "";
            document.getElementById('formAccomplishments').value = "";
            document.getElementById('formStatus').value = "Draft";

            // File input settings for Create (required)
            document.getElementById('formReportFile').value = "";
            document.getElementById('formReportFile').required = true;
            document.getElementById('fileRequiredStar').classList.remove('hidden');
            document.getElementById('reportFileUploadInfo').classList.add('hidden');
            document.getElementById('reportFileNameDisplay').innerText = "No file selected";

            document.getElementById('reportOverlay').classList.remove('hidden');
        }

        window.openEditReportModal = function(id, title, sectionId, location, date, participants, accomplishments, reportFilePath, status) {
            document.getElementById('reportForm').action = "{{ route('instructor.reports.update', ':id') }}".replace(':id', id);
            document.getElementById('reportFormMethod').value = "PUT";
            document.getElementById('modalTitle').innerText = "Edit Accomplishment Report";

            // Map title string back to the plan option index
            const select = document.getElementById('formPlan');
            select.value = "";
            for (let i = 0; i < select.options.length; i++) {
                if (select.options[i].text.includes(title)) {
                    select.selectedIndex = i;
                    break;
                }
            }

            document.getElementById('formSection').value = sectionId;
            document.getElementById('formDate').value = date;
            document.getElementById('formAccomplishments').value = accomplishments === 'null' ? '' : accomplishments;

            // File input settings for Edit (optional)
            document.getElementById('formReportFile').value = "";
            document.getElementById('formReportFile').required = false;
            document.getElementById('fileRequiredStar').classList.add('hidden');

            if (reportFilePath && reportFilePath !== 'null' && reportFilePath.includes('uploads/')) {
                document.getElementById('reportFileUploadInfo').classList.remove('hidden');
                document.getElementById('reportFileNameDisplay').innerHTML = `<a href="/${reportFilePath}" target="_blank" class="underline text-emerald-600 font-bold">Existing Accomplishment Report File</a>`;
            } else {
                document.getElementById('reportFileUploadInfo').classList.add('hidden');
            }

            // Restrict status edits if it's already approved
            const statusSelect = document.getElementById('formStatus');
            statusSelect.value = (status === 'Reviewed' || status === 'Revision') ? 'Pending' : status;

            document.getElementById('reportOverlay').classList.remove('hidden');
        }

        window.closeReportModal = function() {
            document.getElementById('reportOverlay').classList.add('hidden');
        }
    </script>
@endpush

@endsection
