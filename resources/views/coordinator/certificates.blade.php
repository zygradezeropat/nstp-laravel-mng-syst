@extends('layouts.coordinator')

@section('title', 'Certificates - DNSC NSTP Portal')

@section('content')
<div class="space-y-6" id="certificates-dashboard-container">

    {{-- ── Header ──────────────────────────────────────────────────────────────── --}}
    <div class="flex items-center justify-between pb-3 border-b border-slate-100">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Certificate Overview</h1>
            <p class="text-sm text-slate-500 mt-1">Click a section card to manage and generate certificates</p>
        </div>
        <div>
            <input type="file" id="certXlsxInput" accept=".xlsx,.xls" class="hidden" />
            <button id="certXlsxBtn"
                class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border-2 border-dashed border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 hover:border-emerald-400 transition-all duration-200 text-sm font-semibold shadow-sm">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                </svg>
                Import XLSX Grades
            </button>
        </div> 
    </div>

    {{-- ── Main Grid ───────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Section Cards --}}
        <div class="lg:col-span-2 space-y-4" id="certSectionCardsContainer">
            @forelse($sections as $sec)
            <div onclick="openCertModal({{ $sec->id }}, '{{ addslashes($sec->code) }}', '{{ addslashes($sec->program) }}', {{ $sec->passed_count }})"
                class="bg-white rounded-2xl border border-slate-100 p-5 shadow-sm flex items-center gap-4 transition-all duration-200 cursor-pointer hover:border-indigo-300 hover:shadow-md group relative">

                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-600 to-blue-500 flex items-center justify-center text-white shadow shrink-0">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 017.882 5.84 50.57 50.57 0 00-2.658.813M4.26 10.147a49.621 49.621 0 0115.482 0" />
                    </svg>
                </div>

                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2">
                        <span class="text-slate-900 font-bold truncate group-hover:text-indigo-600 transition text-base">{{ $sec->code }}</span>
                        <span class="text-[10px] px-2.5 py-0.5 rounded-full font-bold {{ $sec->program === 'CWTS' ? 'bg-indigo-50 text-indigo-700' : ($sec->program === 'LTS' ? 'bg-emerald-50 text-emerald-700' : 'bg-rose-50 text-rose-700') }}">
                            {{ $sec->program }}
                        </span>
                    </div>
                    <div class="text-xs text-slate-500 mt-1 flex items-center gap-1.5 truncate">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 text-slate-400 shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        <span class="truncate font-medium text-slate-600" title="{{ $sec->filename }}">{{ $sec->filename }}</span>
                        <span class="text-slate-300">&middot;</span>
                        <span class="text-slate-400 shrink-0">{{ $sec->imported_at }}</span>
                    </div>
                    <div class="text-xs font-semibold text-emerald-600 mt-1">
                        {{ $sec->passed_count }} passed student(s) ready
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0" onclick="event.stopPropagation()">
                    <form action="{{ route('coordinator.sections.delete', $sec->id) }}" method="POST"
                        onsubmit="return confirm('Delete this section and all its student enrollments?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-1.5 rounded-lg border border-rose-200 text-rose-500 hover:bg-rose-50 hover:border-rose-400 transition" title="Delete Section">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                            </svg>
                        </button>
                    </form>
                    <button type="button"
                        onclick="openCertModal({{ $sec->id }}, '{{ addslashes($sec->code) }}', '{{ addslashes($sec->program) }}', {{ $sec->passed_count }})"
                        class="px-3.5 py-1.5 text-xs font-bold rounded-lg bg-slate-900 text-white hover:bg-slate-700 transition shadow-sm">
                        Generate
                    </button>
                </div>
            </div>
            @empty
            <div class="bg-white rounded-2xl border border-slate-100 p-10 shadow-sm flex flex-col items-center justify-center text-center text-slate-400 space-y-3">
                <div class="w-14 h-14 rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-7 h-7">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                    </svg>
                </div>
                <p class="font-bold text-slate-800 text-base">No Processed OCR Grade Sheets Found</p>
                <p class="text-xs text-slate-500 max-w-md leading-relaxed">Certificates are issued after grade sheets are uploaded and processed. Upload an XLSX or PDF grade sheet in the <strong>OCR Grade Import</strong> module to generate certificates.</p>
                <a href="{{ route('coordinator.ocr') }}" class="mt-2 inline-flex items-center gap-2 px-5 py-2.5 text-xs font-bold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white transition shadow-sm">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                    </svg>
                    Go to OCR Grade Import
                </a>
            </div>
            @endforelse
        </div>

        {{-- Right: Recently Issued Sidebar --}}
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 h-full">
                <h2 class="font-bold text-slate-900 text-base mb-4">Recently Issued</h2>
                <ul class="space-y-3" id="recentlyIssuedSidebarList">
                    @forelse($recentlyIssued as $log)
                    <li class="flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50 transition duration-200">
                        <div class="w-9 h-9 rounded-full bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm text-slate-900 truncate font-semibold">{{ $log->section }}</div>
                            <div class="text-xs text-slate-400">{{ $log->count }} stds &middot; {{ $log->performed_at }}</div>
                        </div>
                        <form action="{{ route('coordinator.certificates.log.delete', $log->id) }}" method="POST"
                            onsubmit="return confirm('Remove this log entry?')" class="shrink-0">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="p-1 rounded hover:bg-rose-50 text-slate-300 hover:text-rose-500 transition" title="Remove">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </form>
                    </li>
                    @empty
                    <li class="text-sm text-slate-400 text-center py-10 flex flex-col items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-8 h-8 text-slate-300">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <span>No certificates recently issued.</span>
                    </li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════════ --}}
{{-- CERTIFICATE GENERATION MODAL                                                  --}}
{{-- ══════════════════════════════════════════════════════════════════════════════ --}}
<div id="certModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-lg mx-auto flex flex-col max-h-[88vh] border border-slate-100">

        {{-- Header --}}
        <div class="px-6 py-5 border-b border-slate-100 flex items-start justify-between shrink-0">
            <div>
                <h2 id="certModalTitle" class="text-slate-900 font-bold text-base leading-snug">Generate Certificates</h2>
                <div class="text-xs text-slate-400 font-medium mt-0.5">
                    <span id="certModalSection" class="font-semibold text-slate-600"></span>
                    &middot; <span id="certModalProgram"></span>
                    &middot; <span id="certModalEligible" class="text-emerald-600 font-semibold"></span>
                </div>
            </div>
            <button onclick="closeCertModal()" class="p-1.5 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition shrink-0 mt-0.5">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>
        </div>

        {{-- Scrollable Body --}}
        <div class="overflow-y-auto flex-1 px-6 py-5 space-y-6">

            {{-- Template Selector --}}
            <div>
                <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-3">Select Certificate Design</div>
                <div class="grid grid-cols-3 gap-2.5" id="certRosterTemplateGrid">
                    @foreach($templates as $tmpl)
                    @php
                        $borderCls = '';
                        $bgStyle = '';
                        $textCls = 'text-indigo-950 font-serif';
                        
                        if ($tmpl->bg_image) {
                            $bgStyle = 'background-image: url(' . asset($tmpl->bg_image) . '); background-size: 100% 100%; border: none;';
                        } else {
                            $borderCls = 'border-4 border-indigo-900 double bg-white';
                        }
                    @endphp
                    <button type="button"
                        id="tmplCard-{{ $tmpl->id }}"
                        onclick="selectTemplate({{ $tmpl->id }}, this)"
                        class="group text-left p-2 rounded-xl border-2 border-slate-200 hover:border-indigo-400 hover:bg-indigo-50/30 transition flex flex-col items-stretch focus:outline-none cursor-pointer relative">
                        <div class="w-full h-12 mb-2 {{ $borderCls }} rounded flex items-center justify-center font-bold text-[6px] {{ $textCls }} select-none relative overflow-hidden" style="{{ $bgStyle }}">
                            @if(!$tmpl->bg_image)
                                {{ strtoupper($tmpl->component ?? 'CERT') }}
                            @endif
                            <span class="tmpl-check hidden absolute top-1 right-1 w-3.5 h-3.5 rounded-full bg-indigo-600 text-white flex items-center justify-center">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-2 h-2"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
                            </span>
                        </div>
                        <div class="text-[10px] font-bold text-slate-800 truncate leading-tight">{{ $tmpl->name }}</div>
                        @if($tmpl->bg_image)
                            <div class="text-[9px] text-indigo-600 font-semibold mt-0.5">Custom PNG Attached</div>
                        @else
                            <div class="text-[9px] text-slate-400 mt-0.5">Default Border</div>
                        @endif
                    </button>
                    @endforeach
                </div>
            </div>

            {{-- Roster List --}}
            <div>
                <div class="text-[10px] font-extrabold uppercase tracking-wider text-slate-400 mb-3">
                    Eligible Students
                    <span id="certRosterCount" class="ml-1 text-indigo-500"></span>
                </div>
                <ul class="space-y-1.5" id="certRosterList">
                    <li class="text-center text-slate-400 text-xs py-4 animate-pulse">Loading roster...</li>
                </ul>
            </div>
        </div>

        {{-- Footer --}}
        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between shrink-0 bg-slate-50/60">
            <button onclick="closeCertModal()" class="text-sm text-slate-400 hover:text-slate-600 font-medium transition">Cancel</button>
            <button onclick="printBatchAll()" id="btnGenerateAll"
                class="inline-flex items-center gap-1.5 px-5 py-2 text-sm font-bold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition shadow-sm disabled:opacity-50">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                Download All Certificates
            </button>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════════ --}}
{{-- SUCCESS MODAL                                                                  --}}
{{-- ══════════════════════════════════════════════════════════════════════════════ --}}
<div id="successActionModal" class="hidden fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl p-7 shadow-2xl w-full max-w-sm border border-slate-100 flex flex-col items-center text-center space-y-4">
        <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-inner">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="3" stroke="currentColor" class="w-8 h-8">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
            </svg>
        </div>
        <div>
            <h3 class="text-slate-900 font-bold text-lg" id="successModalTitle">Success!</h3>
            <p class="text-sm text-slate-500 mt-1 leading-relaxed" id="successModalMsg">The operation completed successfully.</p>
        </div>
        <button onclick="closeSuccessActionModal()" class="w-full py-2.5 px-4 rounded-xl bg-slate-900 hover:bg-slate-700 text-white font-bold text-sm shadow transition">
            Great, thanks!
        </button>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════════ --}}
{{-- PDF LOADING OVERLAY                                                            --}}
{{-- ══════════════════════════════════════════════════════════════════════════════ --}}
<div id="pdfLoadingOverlay" class="hidden fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-slate-900/70 backdrop-blur-sm p-4">
    <div class="bg-white rounded-2xl p-8 shadow-2xl w-full max-w-xs border border-slate-100 flex flex-col items-center text-center space-y-4">
        <div class="w-14 h-14 rounded-full border-4 border-indigo-100 border-t-indigo-600 animate-spin"></div>
        <div>
            <h3 class="text-slate-900 font-bold text-sm">Generating PDF…</h3>
            <p class="text-xs text-slate-400 mt-1">Please wait while the server compiles your certificate.</p>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════════════════ --}}
{{-- UPLOAD LOADING OVERLAY                                                         --}}
{{-- ══════════════════════════════════════════════════════════════════════════════ --}}
<div id="uploadLoadingOverlay" class="hidden fixed inset-0 z-[9999] flex flex-col items-center justify-center bg-slate-900/70 backdrop-blur-sm p-4 transition-all duration-300">
    <div class="bg-white rounded-3xl p-8 shadow-2xl w-full max-w-sm border border-slate-100 flex flex-col items-center text-center space-y-5 relative overflow-hidden">
        {{-- Animated background accent --}}
        <div class="absolute -top-10 -right-10 w-32 h-32 bg-emerald-100 rounded-full blur-2xl opacity-60"></div>
        <div class="absolute -bottom-10 -left-10 w-32 h-32 bg-indigo-100 rounded-full blur-2xl opacity-60"></div>

        {{-- Icon / Spinner Container --}}
        <div class="relative w-20 h-20 flex items-center justify-center">
            <div class="absolute inset-0 rounded-full border-4 border-emerald-100 animate-ping opacity-75"></div>
            <div class="absolute inset-0 rounded-full border-4 border-t-emerald-600 border-r-transparent border-b-emerald-600 border-l-transparent animate-spin"></div>
            <div class="w-12 h-12 rounded-2xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-inner">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6 animate-bounce">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
                </svg>
            </div>
        </div>

        <div class="space-y-1.5 z-10">
            <h3 class="text-slate-900 font-bold text-lg" id="uploadOverlayTitle">Uploading Grade Sheet...</h3>
            <p class="text-xs text-slate-500 max-w-xs leading-relaxed" id="uploadOverlaySubtitle">Processing Excel rows and syncing student enrollments with the server. Please wait...</p>
        </div>

        {{-- Progress Bar indicator --}}
        <div class="w-full bg-slate-100 rounded-full h-2 overflow-hidden z-10">
            <div class="bg-gradient-to-r from-emerald-500 to-teal-500 h-2 rounded-full animate-pulse w-full"></div>
        </div>
    </div>
</div>

@if(session('success'))
<script>
    document.addEventListener('DOMContentLoaded', () => {
        showSuccessActionModal('Completed!', @json(session('success')));
    });
</script>
@endif
@endsection

@push('styles')
<style>
    .tmpl-card-selected {
        border-color: #4f46e5 !important;
        background-color: #eef2ff !important;
    }
    .tmpl-card-selected .tmpl-check {
        display: flex !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
<script>
// ── State ─────────────────────────────────────────────────────────────────────
let activeSectionId      = null;
let activeSectionName    = '';
let activeSchoolYear     = '2025-2026';
let activeRosterStudents = [];
let selectedTemplateId   = null;
let shouldReloadOnClose  = false;

// ── Success Modal ──────────────────────────────────────────────────────────────
window.showSuccessActionModal = function(title, msg, reload = false) {
    document.getElementById('successModalTitle').textContent = title;
    document.getElementById('successModalMsg').innerHTML = msg;
    shouldReloadOnClose = reload;
    document.getElementById('successActionModal').classList.remove('hidden');
};
window.closeSuccessActionModal = function() {
    document.getElementById('successActionModal').classList.add('hidden');
    if (shouldReloadOnClose) location.reload();
};

// ── PDF Overlay ────────────────────────────────────────────────────────────────
window.showPdfLoadingOverlay = function() {
    const el = document.getElementById('pdfLoadingOverlay');
    if (el) el.classList.remove('hidden');
};
window.hidePdfLoadingOverlay = function() {
    const el = document.getElementById('pdfLoadingOverlay');
    if (el) el.classList.add('hidden');
};

// ── Upload Loading Overlay ──────────────────────────────────────────────────────
window.showUploadLoadingOverlay = function(title = 'Uploading Grade Sheet...', subtitle = 'Processing Excel rows and syncing student enrollments with the server. Please wait...') {
    const el = document.getElementById('uploadLoadingOverlay');
    if (el) {
        document.getElementById('uploadOverlayTitle').textContent = title;
        document.getElementById('uploadOverlaySubtitle').textContent = subtitle;
        el.classList.remove('hidden');
    }
};
window.hideUploadLoadingOverlay = function() {
    const el = document.getElementById('uploadLoadingOverlay');
    if (el) el.classList.add('hidden');
};

// ── XLSX Import ────────────────────────────────────────────────────────────────
document.getElementById('certXlsxBtn').addEventListener('click', () => {
    document.getElementById('certXlsxInput').click();
});

document.getElementById('certXlsxInput').addEventListener('change', function(e) {
    const file = e.target.files[0];
    if (!file) return;

    showUploadLoadingOverlay(`Uploading "${file.name}"...`, 'Parsing Excel rows, matching student records, and saving enrollments. Please wait...');

    const reader = new FileReader();
    reader.onerror = function() {
        hideUploadLoadingOverlay();
        alert('Error reading the selected file.');
    };
    reader.onload = function(ev) {
        try {
            const data  = new Uint8Array(ev.target.result);
            const wb    = XLSX.read(data, { type: 'array' });
            const sheet = wb.Sheets[wb.SheetNames[0]];
            const rows  = XLSX.utils.sheet_to_json(sheet, { defval: '' });
            if (!rows.length) {
                hideUploadLoadingOverlay();
                alert('The XLSX file appears to be empty.');
                return;
            }

            // Flexible Column Mapping Resolution
            const getColVal = (row, ...keys) => {
                for (const k of keys) {
                    const found = Object.keys(row).find(rk => rk.trim().toLowerCase() === k.toLowerCase());
                    if (found !== undefined && String(row[found]).trim() !== '') {
                        return String(row[found]).trim();
                    }
                }
                return '';
            };

            const classifyGrade = (rawGrade) => {
                const g = parseFloat(rawGrade);
                if (isNaN(g)) {
                    const clean = String(rawGrade || '').trim().toLowerCase();
                    if (clean === 'passed' || clean === 'pass' || clean === 'p') return 'Passed';
                    if (clean === 'failed' || clean === 'fail' || clean === 'f') return 'Failed';
                    if (clean === 'pending' || clean === 'active') return 'Pending';
                    return 'N/A';
                }
                if (g >= 1.0 && g <= 3.0) return 'Passed';
                if (g > 3.0 && g <= 5.0) return 'Failed';
                if (g >= 75.0 && g <= 100.0) return 'Passed';
                if (g >= 50.0 && g < 75.0) return 'Failed';
                return 'N/A';
            };

            const normalizeRemarks = (rawRemarks) => {
                if (!rawRemarks) return null;
                const clean = String(rawRemarks).trim().toLowerCase();
                if (clean.startsWith('pass') || clean === 'p') return 'Passed';
                if (clean.startsWith('fail') || clean === 'f') return 'Failed';
                if (clean.startsWith('pend') || clean === 'active') return 'Pending';
                return null;
            };

            let sectionCode = '';
            const parsedStudents = [];

            rows.forEach(row => {
                let name = getColVal(row, 'Student Name', 'Name', 'Full Name', 'Student', 'Lastname, Firstname', 'Student_Name', 'STUDENT NAME', 'FULLNAME');
                const grade = getColVal(row, 'Grade', 'Final Grade', 'Final_Grade', 'GWA', 'Score', 'Rating', 'Grades', 'GRADE', 'FINAL GRADE');
                const sec = getColVal(row, 'Section', 'Section Code', 'Class', 'SECTION');
                const studentNo = getColVal(row, 'Student No', 'Student Number', 'ID', 'Student_No', 'Student_Number', 'STUDENT NO', 'STUDENT NUMBER');
                const serialNo = getColVal(row, 'Serial Number', 'Serial No', 'Serial_Number', 'Serial_No', 'SERIAL NUMBER', 'SERIAL NO');
                const rawRemarks = getColVal(row, 'Remarks', 'Status', 'Remark', 'Remarks/Status', 'REMARKS', 'STATUS', 'REMARK');

                if (!name) {
                    const surname = getColVal(row, 'Surname', 'Last Name', 'Lastname', 'SURNAME', 'LAST NAME', 'FAMILY NAME');
                    const firstName = getColVal(row, 'First Name', 'FirstName', 'FIRST NAME', 'Firstname', 'GIVEN NAME');
                    const extName = getColVal(row, 'Ext Name', 'Extension Name', 'ExtName', 'Extension', 'EXT NAME', 'EXTENSION NAME', 'Suffix', 'EXT');
                    const middleName = getColVal(row, 'Middle Name', 'MiddleName', 'MIDDLE NAME', 'Middlename', 'MI', 'M.I.');

                    const cleanExt = (extName && extName.toUpperCase() !== 'N/A') ? extName : '';
                    const cleanMid = (middleName && middleName.toUpperCase() !== 'N/A') ? middleName : '';

                    if (surname || firstName) {
                        const firstParts = [firstName, cleanMid, cleanExt].filter(Boolean).join(' ');
                        if (surname && firstParts) {
                            name = `${surname}, ${firstParts}`;
                        } else {
                            name = surname || firstParts;
                        }
                    }
                }

                if (!sectionCode && sec) sectionCode = sec;
                if (!name) return; // skip headers/empty rows

                const remarksNormalized = normalizeRemarks(rawRemarks);

                parsedStudents.push({
                    name: name,
                    student_no: studentNo || null,
                    serial_no: serialNo || null,
                    grade: grade || null,
                    remarks: remarksNormalized || classifyGrade(grade)
                });
            });

            if (!parsedStudents.length) {
                hideUploadLoadingOverlay();
                alert('No valid students found. Ensure columns like "Name", "Student Name", or separate columns ("Surname", "First Name", "Ext Name", "Middle Name") exist.');
                return;
            }

            // Infer section code if missing
            if (!sectionCode) {
                sectionCode = file.name
                    .replace(/\.(xlsx|xls)$/i, '')
                    .trim()
                    .replace(/[-_\s]+/g, '-')
                    .toUpperCase() || 'IMPORTED';
            }

            const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
            fetch('/coordinator/ocr/import', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' },
                body: JSON.stringify({
                    section: sectionCode.toUpperCase(),
                    filename: file.name,
                    students: parsedStudents
                })
            })
            .then(async r => {
                const isJson = r.headers.get('content-type')?.includes('application/json');
                const data = isJson ? await r.json() : null;
                if (!r.ok) {
                    throw new Error(data?.message || `HTTP error! status: ${r.status}`);
                }
                return data;
            })
            .then(res => {
                hideUploadLoadingOverlay();
                if (res.success) {
                    const total = res.summary?.total ?? parsedStudents.length;
                    const sectionName = res.summary?.section ?? sectionCode;
                    const passed = res.summary?.passed ?? 0;
                    const failed = res.summary?.failed ?? 0;
                    showSuccessActionModal('Grades Imported!',
                        `Successfully imported <strong>${total}</strong> record(s) for section <strong>${sectionName}</strong> (${passed} passed, ${failed} failed). The page will refresh to show updated rosters.`,
                        true);
                } else {
                    alert('Import failed: ' + (res.message || 'Unknown error'));
                }
            })
            .catch(err => {
                hideUploadLoadingOverlay();
                console.error(err);
                alert('Error importing file: ' + err.message);
            });
        } catch (e) {
            hideUploadLoadingOverlay();
            console.error(e);
            alert('Error parsing Excel file: ' + e.message);
        }
    };
    reader.readAsArrayBuffer(file);
    this.value = '';
});

// ── Cert Modal Open/Close ──────────────────────────────────────────────────────
function openCertModal(sectionId, sectionCode, program, passedCount, ocrEntry = null) {
    activeSectionId      = sectionId;
    activeSectionName    = sectionCode;
    selectedTemplateId   = null;

    // Reset template cards
    document.querySelectorAll('[id^="tmplCard-"]').forEach(c => {
        c.classList.remove('tmpl-card-selected');
        c.querySelector('.tmpl-check')?.classList.add('hidden');
    });

    // Set header text
    document.getElementById('certModalTitle').textContent    = `Generate Certificates — ${sectionCode}`;
    document.getElementById('certModalSection').textContent  = sectionCode;
    document.getElementById('certModalProgram').textContent  = program;
    document.getElementById('certModalEligible').textContent = `${passedCount} eligible`;

    // Show modal
    document.getElementById('certModal').classList.remove('hidden');
    document.body.style.overflow = 'hidden';

    // Load roster
    const list = document.getElementById('certRosterList');
    list.innerHTML = '<li class="text-center text-slate-400 text-xs py-6 animate-pulse">Loading students…</li>';
    document.getElementById('certRosterCount').textContent = '';

    const handleRosterFallback = () => {
        if (ocrEntry && ocrEntry.results && ocrEntry.results.length > 0) {
            activeRosterStudents = ocrEntry.results
                .filter(r => {
                    const rem = (r.remarks || '').toLowerCase();
                    return rem === 'passed' || rem.startsWith('pass') || rem === 'p';
                })
                .map(r => ({
                    student_no: r.student_no || r.student_id || '',
                    serial_no:  r.serial_no || '',
                    name:       r.name || r.student_name || '',
                    course:     r.course || 'BSIT',
                    component:  program,
                    grade:      r.grade || '1.0'
                }));
            activeSchoolYear = '2025-2026';
            renderRosterList();
        } else {
            list.innerHTML = '<li class="text-center text-rose-400 text-xs py-6">Failed to load roster.</li>';
        }
    };

    fetch(`/coordinator/certificates/section/${encodeURIComponent(sectionId)}`)
        .then(r => r.json())
        .then(data => {
            if (data.students && data.students.length > 0) {
                activeRosterStudents = data.students;
                activeSchoolYear     = data.school_year || '2025-2026';
                renderRosterList();
            } else {
                handleRosterFallback();
            }
        })
        .catch(() => {
            handleRosterFallback();
        });
}

function closeCertModal() {
    document.getElementById('certModal').classList.add('hidden');
    document.body.style.overflow = '';
}

// ── Template Selection ─────────────────────────────────────────────────────────
function selectTemplate(templateId, cardEl) {
    selectedTemplateId = templateId;
    // Visual: deselect all, select clicked
    document.querySelectorAll('[id^="tmplCard-"]').forEach(c => {
        c.classList.remove('tmpl-card-selected');
        c.querySelector('.tmpl-check')?.classList.add('hidden');
    });
    cardEl.classList.add('tmpl-card-selected');
    cardEl.querySelector('.tmpl-check')?.classList.remove('hidden');
}

// ── Render Roster ──────────────────────────────────────────────────────────────
function renderRosterList() {
    const list = document.getElementById('certRosterList');
    const countEl = document.getElementById('certRosterCount');
    list.innerHTML = '';

    if (!activeRosterStudents.length) {
        list.innerHTML = '<li class="text-center text-slate-400 text-xs py-8">No passed students found for this section.</li>';
        countEl.textContent = '';
        return;
    }

    countEl.textContent = `(${activeRosterStudents.length})`;

    activeRosterStudents.forEach((std, i) => {
        const li = document.createElement('li');
        li.className = 'flex items-center gap-3 p-2.5 rounded-xl hover:bg-slate-50 transition border border-transparent hover:border-slate-100';
        li.innerHTML = `
            <div class="w-7 h-7 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs font-bold shrink-0">${i + 1}</div>
            <div class="min-w-0 flex-1">
                <div class="text-sm font-semibold text-slate-800 truncate">${std.name}</div>
                <div class="flex items-center gap-1.5 mt-0.5">
                    <span class="text-[9px] font-bold text-slate-400 uppercase tracking-wider">Student/Serial ID:</span>
                    <input type="text" value="${std.serial_no || std.student_no || ''}" placeholder="Enter Serial No" 
                        class="student-no-input bg-slate-50 border border-slate-200 rounded px-2 py-0.5 text-[10px] text-slate-700 font-mono font-semibold focus:outline-none focus:border-indigo-400 focus:bg-white w-32 transition" />
                </div>
            </div>
            <button onclick="printIndividualCert(${i}, this)"
                class="inline-flex items-center gap-1 px-2.5 py-1.5 text-xs font-bold rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white transition shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3 h-3"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3"/></svg>
                PDF
            </button>
        `;
        list.appendChild(li);
    });
}

// ── Individual Certificate PDF ─────────────────────────────────────────────────
function printIndividualCert(rosterIndex, btnEl) {
    const student = activeRosterStudents[rosterIndex];
    if (!student) return;
    if (!selectedTemplateId) {
        alert('Please select a certificate design template first.');
        return;
    }

    // Find custom serial number input inside the list row
    const liEl = btnEl.closest('li');
    const inputEl = liEl.querySelector('.student-no-input');
    const studentNo = inputEl ? inputEl.value.trim() : (student.student_no || '');

    const csrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    closeCertModal();
    showPdfLoadingOverlay();

    fetch('/coordinator/certificates/pdf/single', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/pdf, application/json' },
        body: JSON.stringify({
            template_id:  selectedTemplateId,
            student_name: student.name,
            student_no:   studentNo,
            section:      activeSectionName,
            school_year:  activeSchoolYear,
        }),
    })
    .then(async res => {
        hidePdfLoadingOverlay();
        if (!res.ok) {
            const err = await res.json().catch(() => ({ error: res.statusText }));
            alert('PDF failed: ' + err.error);
            return;
        }
        const blob = await res.blob();
        const url  = URL.createObjectURL(blob);
        const safeSerial = studentNo.replace(/[^a-zA-Z0-9]/g, '_');
        const safeName   = student.name.replace(/[^a-zA-Z0-9]/g, '_');
        const a          = Object.assign(document.createElement('a'), { href: url, download: safeSerial + '_' + safeName + '.pdf' });
        document.body.appendChild(a); a.click(); a.remove();
        URL.revokeObjectURL(url);

        addSidebarEntry(activeSectionName, 'Individual · 1 std');
        showSuccessActionModal('Certificate Downloaded!',
            `Certificate for <strong>${student.name}</strong> has been downloaded.`);
    })
    .catch(err => { hidePdfLoadingOverlay(); console.error(err); alert('Failed to generate PDF.'); });
}

// ── Batch Certificate PDF ──────────────────────────────────────────────────────
function printBatchAll() {
    if (!activeSectionId) return;
    if (!selectedTemplateId) {
        alert('Please select a certificate design template first.');
        return;
    }

    // Collect all student serial numbers from inline inputs
    const studentNosMap = {};
    const listEl = document.getElementById('certRosterList');
    const liEls = listEl.querySelectorAll('li');
    liEls.forEach((li, i) => {
        const inputEl = li.querySelector('.student-no-input');
        if (inputEl && activeRosterStudents[i]) {
            const originalNo = activeRosterStudents[i].student_no;
            const customNo = inputEl.value.trim();
            studentNosMap[originalNo] = customNo;
        }
    });

    const csrf  = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    const btn   = document.getElementById('btnGenerateAll');
    const oldHtml = btn.innerHTML;
    btn.innerHTML = '<span class="animate-pulse">Generating…</span>';
    btn.disabled  = true;
    closeCertModal();
    showPdfLoadingOverlay();

    fetch('/coordinator/certificates/pdf/batch', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/pdf, application/json' },
        body: JSON.stringify({ 
            template_id: selectedTemplateId, 
            section_id: activeSectionId,
            student_nos: studentNosMap
        }),
    })
    .then(async res => {
        hidePdfLoadingOverlay();
        btn.innerHTML = oldHtml;
        btn.disabled  = false;
        if (!res.ok) {
            const err = await res.json().catch(() => ({ error: res.statusText }));
            alert('Batch PDF failed: ' + err.error);
            return;
        }
        const blob = await res.blob();
        const url  = URL.createObjectURL(blob);
        const a    = Object.assign(document.createElement('a'), { href: url, download: activeSectionName.replace(/[^a-zA-Z0-9]/g,'_') + '_certificates.pdf' });
        document.body.appendChild(a); a.click(); a.remove();
        URL.revokeObjectURL(url);

        addSidebarEntry(activeSectionName, `Batch · ${activeRosterStudents.length} stds`);
        showSuccessActionModal('Batch PDF Downloaded!',
            `<strong>${activeRosterStudents.length}</strong> certificate(s) for <strong>${activeSectionName}</strong> downloaded.`);
    })
    .catch(err => {
        hidePdfLoadingOverlay();
        btn.innerHTML = oldHtml;
        btn.disabled  = false;
        console.error(err);
        alert('Failed to generate batch PDF.');
    });
}

// ── Sidebar helper ─────────────────────────────────────────────────────────────
function addSidebarEntry(sectionName, meta) {
    const logList = document.getElementById('recentlyIssuedSidebarList');
    // Remove empty placeholder if present
    logList.querySelectorAll('li.text-center').forEach(el => el.remove());
    const li = document.createElement('li');
    li.className = 'flex items-center gap-3 p-2 rounded-xl hover:bg-slate-50';
    li.innerHTML = `
        <div class="w-9 h-9 rounded-full bg-emerald-50 text-emerald-700 flex items-center justify-center shrink-0">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5"/></svg>
        </div>
        <div class="flex-1 min-w-0">
            <div class="text-sm text-slate-900 truncate font-semibold">${sectionName}</div>
            <div class="text-xs text-slate-400">${meta} &middot; Just now</div>
        </div>`;
    logList.insertBefore(li, logList.firstChild);
}

// Close modal on backdrop click
document.getElementById('certModal').addEventListener('click', function(e) {
    if (e.target === this) closeCertModal();
});
document.getElementById('successActionModal').addEventListener('click', function(e) {
    if (e.target === this) closeSuccessActionModal();
});

// ── Render OCR Grade History Cards ──────────────────────────────────────────────
function renderCertCardsFromStorage() {
    const container = document.getElementById('certSectionCardsContainer');
    if (!container) return;

    const stored = localStorage.getItem('NSTP_OCR_HISTORY');
    let history = [];
    try {
        history = stored ? JSON.parse(stored) : [];
    } catch (e) {
        history = [];
    }

    if (!history.length) {
        return; // Retain server-side cards if no client storage history exists
    }

    container.innerHTML = '';

    history.forEach((entry, idx) => {
        const secCode = entry.summary?.section || 'IMPORTED';
        const component = entry.summary?.component || 'CWTS';
        const passedCount = entry.summary?.passed !== undefined ? entry.summary.passed : (entry.summary?.total || 0);
        const filename = entry.filename || (secCode + '_grades.xlsx');
        const importedAt = entry.time || 'Recently Imported';

        let badgeClass = 'bg-indigo-50 text-indigo-700';
        if (component === 'LTS') badgeClass = 'bg-emerald-50 text-emerald-700';
        if (component === 'ROTC') badgeClass = 'bg-rose-50 text-rose-700';

        const card = document.createElement('div');
        card.className = 'bg-white rounded-2xl border border-slate-100 p-5 shadow-sm flex items-center gap-4 transition-all duration-200 cursor-pointer hover:border-indigo-300 hover:shadow-md group relative';

        const safeSecCode = escapeHtml(secCode);
        const safeComponent = escapeHtml(component);
        const safeFilename = escapeHtml(filename);
        const safeImportedAt = escapeHtml(importedAt);

        card.innerHTML = `
            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-600 to-blue-500 flex items-center justify-center text-white shadow shrink-0">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.436 60.436 0 00-.491 6.347A48.62 48.62 0 0112 20.904a48.62 48.62 0 018.232-4.41 60.46 60.46 0 00-.491-6.347m-15.482 0a50.57 50.57 0 00-2.658-.813A59.905 59.905 0 0112 3.493a59.902 59.902 0 017.882 5.84 50.57 50.57 0 00-2.658.813M4.26 10.147a49.621 49.621 0 0115.482 0" />
                </svg>
            </div>

            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="text-slate-900 font-bold truncate group-hover:text-indigo-600 transition text-base">${safeSecCode}</span>
                    <span class="text-[10px] px-2.5 py-0.5 rounded-full font-bold ${badgeClass}">
                        ${safeComponent}
                    </span>
                </div>
               
                <div class="text-xs font-semibold text-emerald-600 mt-1">
                    ${passedCount} passed student(s) ready
                </div>
            </div>

            <div class="flex items-center gap-2 shrink-0" onclick="event.stopPropagation()">
                <button type="button" onclick="deleteOcrCertCard(${idx}, '${safeSecCode}')" class="p-1.5 rounded-lg border border-rose-200 text-rose-500 hover:bg-rose-50 hover:border-rose-400 transition" title="Delete Record">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                    </svg>
                </button>
                <button type="button"
                    onclick="triggerCardModal(${idx})"
                    class="px-3.5 py-1.5 text-xs font-bold rounded-lg bg-slate-900 text-white hover:bg-slate-700 transition shadow-sm">
                    Generate
                </button>
            </div>
        `;

        card.addEventListener('click', (e) => {
            if (e.target.closest('button')) return;
            openCertModal(secCode, secCode, component, passedCount, entry);
        });

        container.appendChild(card);
    });
}

function triggerCardModal(idx) {
    const stored = localStorage.getItem('NSTP_OCR_HISTORY');
    let history = stored ? JSON.parse(stored) : [];
    const entry = history[idx];
    if (entry) {
        const secCode = entry.summary?.section || 'IMPORTED';
        const component = entry.summary?.component || 'CWTS';
        const passedCount = entry.summary?.passed !== undefined ? entry.summary.passed : (entry.summary?.total || 0);
        openCertModal(secCode, secCode, component, passedCount, entry);
    }
}

function deleteOcrCertCard(idx, secCode) {
    if (!confirm(`Delete section "${secCode}" from certificates history?`)) return;
    const stored = localStorage.getItem('NSTP_OCR_HISTORY');
    let history = stored ? JSON.parse(stored) : [];
    history.splice(idx, 1);
    localStorage.setItem('NSTP_OCR_HISTORY', JSON.stringify(history));
    renderCertCardsFromStorage();
}

function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

document.addEventListener('DOMContentLoaded', () => {
    // Server-rendered section cards from the database are displayed automatically.
});
</script>
@endpush
