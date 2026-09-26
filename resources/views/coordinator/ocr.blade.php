@extends('layouts.coordinator')

@section('title', 'OCR Grade Import - DNSC NSTP Portal')

@section('content')
<div class="space-y-6" id="ocr-container">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-4">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">OCR Grade Import</h1>
            <p class="text-sm text-slate-500 mt-1">Upload XLSX/XLS grade sheets or PDF Grading Sheets to automatically record grades and enrollments in the database</p>
        </div>
    </div>

    <!-- MAIN INTERFACE STATE: UPLOAD -->
    <div id="uploadState" class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Upload Box Column -->
        <div class="lg:col-span-2 space-y-5">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-6 flex flex-col justify-between min-h-[350px]">
                <!-- Drag and Drop Zone -->
                <div id="ocrDropZone" class="group relative flex-1 flex flex-col items-center justify-center border-2 border-dashed border-slate-200 rounded-2xl p-10 text-center bg-slate-50/50 cursor-pointer hover:bg-indigo-50/30 hover:border-indigo-400 transition-all duration-300">
                    <div class="w-16 h-16 rounded-2xl bg-white border border-slate-100 text-indigo-600 flex items-center justify-center shadow-md group-hover:scale-110 group-hover:shadow-indigo-100 transition-all duration-300 mb-4">
                        <x-icon name="upload" class="w-8 h-8" />
                    </div>
                    <div class="text-slate-800 font-semibold text-base">Drop your grade sheet (Excel, PDF, or Image) here or <span class="text-indigo-600 underline group-hover:text-indigo-700 transition">click to browse</span></div>
                    <div class="text-xs text-slate-400 mt-2 font-medium">Supports Excel (.xlsx, .xls), PDF (.pdf) & Image (.png, .jpg, .jpeg) &middot; up to 25 MB</div>
                    <input id="ocrFileInput" type="file" accept=".xlsx,.xls,.pdf,.png,.jpg,.jpeg" class="hidden" />
                </div>

                <!-- Info Cards -->
                <div class="mt-6 grid grid-cols-3 gap-3 text-center text-sm">
                    <div class="p-3.5 rounded-xl bg-slate-50 border border-slate-100 hover:shadow-sm transition-all duration-200">
                        <div class="text-[10px] uppercase font-bold tracking-wider text-slate-400 mb-1">Grade Scale</div>
                        <div class="font-bold text-slate-700">1.0 - 5.0</div>
                    </div>
                    <div class="p-3.5 rounded-xl bg-emerald-50 border border-emerald-100/60 hover:shadow-sm transition-all duration-200">
                        <div class="text-[10px] uppercase font-bold tracking-wider text-emerald-500 mb-1">Pass Range</div>
                        <div class="font-bold text-emerald-600">1.0 - 3.0</div>
                    </div>
                    <div class="p-3.5 rounded-xl bg-rose-50 border border-rose-100/60 hover:shadow-sm transition-all duration-200">
                        <div class="text-[10px] uppercase font-bold tracking-wider text-rose-500 mb-1">Fail Range</div>
                        <div class="font-bold text-rose-600">5.0</div>
                    </div>
                </div>

                <!-- Alert Information -->
                <div class="mt-4 p-4 rounded-xl bg-amber-50/60 border border-amber-100 text-xs text-amber-800 flex items-start gap-3">
                    <x-icon name="alertc" class="w-5 h-5 shrink-0 text-amber-500" />
                    <div class="space-y-1">
                        <span class="font-bold block">Supported File Formats:</span>
                        <p class="leading-relaxed">Upload an <strong>Excel file</strong> (.xlsx/.xls), <strong>DNSC PDF Grading Sheet</strong> (.pdf), or <strong>Scanned Image</strong> (.png/.jpg). Files will open in a verification modal for review and editing before approving database sync.</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- History Column -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-2xl border border-slate-100 shadow-sm p-5 h-full flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-3 border-b border-slate-50 mb-4">
                        <div>
                            <h2 class="font-bold text-slate-900 text-base">Import History</h2>
                            <div class="text-xs text-slate-500 font-medium mt-0.5" id="historyCount">0 file(s) processed</div>
                        </div>
                        <div class="flex items-center gap-2">
                            <button id="exportAllHistoryBtn" class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-bold rounded-lg bg-emerald-50 text-emerald-700 hover:bg-emerald-100 border border-emerald-200/60 transition hidden" title="Export all history entries to Excel">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                Export All
                            </button>
                            <button id="clearHistoryBtn" class="text-xs text-slate-400 hover:text-rose-600 hover:underline font-medium transition hidden">Clear All</button>
                        </div>
                    </div>
                    <ul class="space-y-2 max-h-[400px] overflow-y-auto pr-1" id="ocrUploadHistory">
                        <li id="noHistoryItem" class="text-sm text-slate-400 text-center py-10 flex flex-col items-center gap-2">
                            <x-icon name="upload" class="w-8 h-8 text-slate-300 mb-1" />
                            <span>No uploads yet. Drop an XLSX file to get started.</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- VISUAL STATE: LOADING -->
    <div id="loadingState" class="hidden bg-white/80 backdrop-blur-md border border-slate-100 shadow-xl rounded-2xl p-12 text-center flex flex-col items-center justify-center min-h-[400px] max-w-2xl mx-auto space-y-6 transition-all duration-300">
        <!-- Spinner -->
        <div class="relative w-20 h-20 flex items-center justify-center">
            <div class="absolute inset-0 rounded-full border-4 border-indigo-100 animate-pulse"></div>
            <div class="absolute inset-0 rounded-full border-4 border-t-indigo-600 border-r-transparent border-b-transparent border-l-transparent animate-spin"></div>
            <x-icon name="scan" class="w-8 h-8 text-indigo-600 animate-pulse" />
        </div>
        <div class="space-y-2">
            <h3 class="text-lg font-bold text-slate-800" id="loadingTitle">Processing Grade Sheet</h3>
            <p class="text-sm text-slate-500 leading-relaxed max-w-md" id="loadingSubtitle">Reading workbook and resolving columns client-side...</p>
        </div>
        <!-- Progress Bar -->
        <div class="w-full bg-slate-100 rounded-full h-2 max-w-xs overflow-hidden">
            <div id="loadingProgressBar" class="bg-indigo-600 h-2 rounded-full transition-all duration-300" style="width: 0%"></div>
        </div>
    </div>

    <!-- VISUAL STATE: RESULTS DASHBOARD -->
    <div id="resultsState" class="hidden space-y-6 transition-all duration-300">
        <!-- Dashboard Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 pb-4 border-b border-slate-100">
            <div>
                <div class="flex items-center gap-2">
                    <span id="resultsComponentTag" class="text-xs px-2.5 py-0.5 rounded-full font-bold">CWTS</span>
                    <h2 class="text-xl font-bold text-slate-900" id="resultsSectionName">CWTS-1A Grades</h2>
                </div>
                <p class="text-xs text-slate-500 mt-1" id="resultsFilename">File: CWTS-1A_grades_import.xlsx</p>
            </div>
            <button id="closeResultsBtn" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition shadow-sm">
                <x-icon name="close" class="w-4 h-4" /> Reset & Upload New
            </button>
        </div>

        <!-- Metrics Grid -->
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <!-- Total Card -->
            <div class="bg-white border border-slate-100 p-5 rounded-2xl shadow-sm flex items-center gap-4 hover:shadow-md transition">
                <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shadow-inner">
                    <x-icon name="users" class="w-6 h-6" />
                </div>
                <div>
                    <div class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Total Students</div>
                    <div class="text-2xl font-extrabold text-slate-800 mt-0.5" id="metricTotal">0</div>
                </div>
            </div>

            <!-- Passed Card -->
            <div class="bg-white border border-slate-100 p-5 rounded-2xl shadow-sm flex items-center gap-4 hover:shadow-md transition">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shadow-inner">
                    <x-icon name="check" class="w-6 h-6" />
                </div>
                <div>
                    <div class="text-[10px] uppercase font-bold tracking-wider text-emerald-400">Passed</div>
                    <div class="text-2xl font-extrabold text-emerald-600 mt-0.5" id="metricPassed">0</div>
                </div>
            </div>

            <!-- Failed Card -->
            <div class="bg-white border border-slate-100 p-5 rounded-2xl shadow-sm flex items-center gap-4 hover:shadow-md transition">
                <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center shadow-inner">
                    <x-icon name="close" class="w-6 h-6" />
                </div>
                <div>
                    <div class="text-[10px] uppercase font-bold tracking-wider text-rose-400">Failed</div>
                    <div class="text-2xl font-extrabold text-rose-600 mt-0.5" id="metricFailed">0</div>
                </div>
            </div>

            <!-- Pass Rate Card -->
            <div class="bg-white border border-slate-100 p-5 rounded-2xl shadow-sm flex items-center gap-4 hover:shadow-md transition">
                <div class="w-12 h-12 rounded-xl bg-violet-50 text-violet-600 flex items-center justify-center shadow-inner">
                    <x-icon name="trend" class="w-6 h-6" />
                </div>
                <div>
                    <div class="text-[10px] uppercase font-bold tracking-wider text-violet-400">Pass Rate</div>
                    <div class="text-2xl font-extrabold text-violet-600 mt-0.5" id="metricPassRate">0%</div>
                </div>
            </div>
        </div>

        <!-- Roster Table Renders -->
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden">
            <!-- Search / Filter Bar -->
            <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-4 bg-slate-50/50">
                <div class="flex items-center gap-2 flex-wrap">
                    <h3 class="font-bold text-slate-800 text-base mr-2">Student Grade Roster</h3>
                    <button id="exportSelectedBtn" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl border border-slate-300 bg-white text-slate-700 hover:bg-slate-50 transition shadow-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5 text-slate-500"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg> Export Selected
                    </button>
                    <button id="exportResultsBtn" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-semibold rounded-xl border border-emerald-300 bg-emerald-50 text-emerald-700 hover:bg-emerald-100 transition shadow-xs">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-3.5 h-3.5 shrink-0">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg> Export All to Excel
                    </button>
                </div>
                <div class="flex items-center gap-3 w-full sm:w-auto">
                    <!-- Search Input -->
                    <div class="relative flex-1 sm:flex-none">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                            <x-icon name="search" class="w-4 h-4" />
                        </span>
                        <input id="rosterSearch" type="text" placeholder="Search by student name..." class="w-full sm:w-64 pl-9 pr-3 py-2 text-sm rounded-xl bg-white border border-slate-200 focus:border-indigo-300 focus:outline-none focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                </div>
            </div>

            <!-- Roster Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-left text-sm whitespace-nowrap" id="rosterTable">
                    <thead>
                        <tr class="bg-slate-50 border-b border-slate-100 text-slate-500 font-semibold">
                            <th class="py-3.5 px-4 w-10 text-center">
                                <input type="checkbox" id="selectAllRoster" class="rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer" />
                            </th>
                            <th class="py-3.5 px-4 w-10 text-center">#</th>
                            <th class="py-3.5 px-4">Student Name</th>
                            <th class="py-3.5 px-4 w-32">Student ID</th>
                            <th class="py-3.5 px-4 w-28 text-center">Final Grade</th>
                            <th class="py-3.5 px-4 w-32 text-center">Roster Remarks</th>
                            <th class="py-3.5 px-4 w-44 text-center">Database Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-50" id="rosterTableBody">
                        <!-- Dynamic list items insert here -->
                    </tbody>
                </table>
            </div>

            <!-- Roster Table Pagination -->
            <div class="px-5 py-3.5 border-t border-slate-100 flex flex-col sm:flex-row items-center justify-between gap-3 bg-slate-50/50 text-xs text-slate-500">
                <div id="rosterPaginationInfo" class="font-medium text-slate-600">Showing 1 to 10 of 0 entries</div>
                <div class="flex items-center gap-2">
                    <button id="rosterPrevBtn" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition shadow-sm">
                        <x-icon name="arrowleft" class="w-3.5 h-3.5" /> Previous
                    </button>
                    <span id="rosterPageIndicator" class="px-3 font-bold text-slate-700 bg-white border border-slate-200 py-1.5 rounded-xl shadow-xs">Page 1 of 1</span>
                    <button id="rosterNextBtn" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-200 bg-white font-semibold text-slate-700 hover:bg-slate-50 disabled:opacity-40 disabled:cursor-not-allowed transition shadow-sm">
                        Next <x-icon name="arrowright" class="w-3.5 h-3.5" />
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- PRE-SAVE GRADE VERIFICATION & REVIEW MODAL -->
<div id="ocrReviewModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl border border-slate-100 shadow-2xl w-full max-w-5xl overflow-hidden flex flex-col max-h-[90vh] animate-in fade-in zoom-in duration-200">
        <!-- Modal Header -->
        <div class="p-6 bg-slate-900 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-indigo-500/20 text-indigo-400 border border-indigo-500/30 flex items-center justify-center">
                    <x-icon name="filecheck" class="w-5 h-5" />
                </div>
                <div>
                    <h3 class="font-bold text-lg text-white">Review & Edit Parsed Grade Sheet</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Verify student names, IDs, grades, and remarks before approving database sync.</p>
                </div>
            </div>
            <button id="closeReviewModalBtn" class="w-8 h-8 rounded-full bg-white/10 text-slate-300 hover:bg-white/20 hover:text-white flex items-center justify-center transition">
                <x-icon name="close" class="w-4 h-4" />
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto space-y-5 flex-1 bg-slate-50/50">
            <!-- Metadata Row -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-white p-4 rounded-2xl border border-slate-100 shadow-xs">
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Target Section</label>
                    <input id="reviewSectionInput" type="text" placeholder="e.g. CWTS-1A" class="w-full px-3 py-2 text-sm font-semibold rounded-xl bg-slate-50 border border-slate-200 focus:border-indigo-400 focus:bg-white focus:outline-none transition" />
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Source File</label>
                    <div id="reviewFilenameBadge" class="w-full px-3 py-2 text-xs font-mono font-medium rounded-xl bg-slate-100 text-slate-700 truncate border border-slate-200">file.pdf</div>
                </div>
                <div>
                    <label class="block text-xs font-bold uppercase tracking-wider text-slate-500 mb-1">Parsed Rows</label>
                    <div id="reviewCountBadge" class="w-full px-3 py-2 text-xs font-bold rounded-xl bg-indigo-50 text-indigo-700 border border-indigo-100">0 Record(s)</div>
                </div>
            </div>

            <!-- Image Preview Container (Visible if image file uploaded) -->
            <div id="reviewImageContainer" class="hidden bg-white p-4 rounded-2xl border border-slate-100 shadow-xs">
                <div class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2">Uploaded Document Image Preview</div>
                <div class="max-h-60 overflow-auto border border-slate-200 rounded-xl bg-slate-900/5 p-2 text-center">
                    <img id="reviewImgPreview" class="max-w-full mx-auto rounded-lg shadow-sm" src="" alt="Grading Sheet Preview" />
                </div>
            </div>

            <!-- Editable Roster Table -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
                <div class="p-3.5 bg-slate-100/70 border-b border-slate-200/60 flex items-center justify-between">
                    <div class="text-xs font-bold text-slate-700 uppercase tracking-wider">Parsed Student Grade Roster</div>
                    <button id="addReviewRowBtn" class="inline-flex items-center gap-1 px-3 py-1.5 text-xs font-semibold rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white transition shadow-xs">
                        + Add Student Row
                    </button>
                </div>
                <div class="overflow-x-auto max-h-72">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100 sticky top-0 bg-slate-50">
                            <tr>
                                <th class="py-3 px-3 w-10 text-center">#</th>
                                <th class="py-3 px-3">Student Name</th>
                                <th class="py-3 px-3 w-36">Student ID</th>
                                <th class="py-3 px-3 w-28 text-center">Grade</th>
                                <th class="py-3 px-3 w-36 text-center">Remarks</th>
                                <th class="py-3 px-3 w-16 text-center">Remove</th>
                            </tr>
                        </thead>
                        <tbody id="reviewTableBody" class="divide-y divide-slate-100">
                            <!-- Dynamic editable rows inserted here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 bg-white border-t border-slate-100 flex items-center justify-between">
            <button id="cancelReviewBtn" class="px-5 py-2.5 text-sm font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
                Cancel & Reset
            </button>
            <button id="confirmSyncBtn" class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-md shadow-emerald-600/20">
                <x-icon name="check2" class="w-4 h-4" /> Approve & Sync
            </button>
        </div>
    </div>
</div>

<!-- EXPORT HISTORY CONFIRMATION MODAL -->
<div id="exportConfirmModal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/60 backdrop-blur-xs flex items-center justify-center p-4">
    <div class="bg-white rounded-3xl border border-slate-100 shadow-2xl w-full max-w-3xl overflow-hidden flex flex-col max-h-[90vh] animate-in fade-in zoom-in duration-200">
        <!-- Modal Header -->
        <div class="p-6 bg-slate-900 text-white flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 border border-emerald-500/30 flex items-center justify-center">
                    <x-icon name="download" class="w-5 h-5" />
                </div>
                <div>
                    <h3 class="font-bold text-lg text-white">Confirm History Excel Export</h3>
                    <p class="text-xs text-slate-400 mt-0.5">Please review the list of imported files below before exporting to Excel.</p>
                </div>
            </div>
            <button id="closeExportConfirmModalBtn" class="w-8 h-8 rounded-full bg-white/10 text-slate-300 hover:bg-white/20 hover:text-white flex items-center justify-center transition">
                <x-icon name="close" class="w-4 h-4" />
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 overflow-y-auto space-y-5 flex-1 bg-slate-50/50">
            <!-- Stats Summary Cards -->
            <div class="grid grid-cols-2 gap-4">
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-xs flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center font-bold">
                        <x-icon name="document" class="w-5 h-5" />
                    </div>
                    <div>
                        <div class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Total Section Files</div>
                        <div id="exportModalFileCount" class="text-xl font-extrabold text-slate-800">0 File(s)</div>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-xs flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                        <x-icon name="users" class="w-5 h-5" />
                    </div>
                    <div>
                        <div class="text-[10px] uppercase font-bold tracking-wider text-slate-400">Total Student Records</div>
                        <div id="exportModalStudentCount" class="text-xl font-extrabold text-slate-800">0 Record(s)</div>
                    </div>
                </div>
            </div>

            <!-- List of Files to Export -->
            <div class="bg-white rounded-2xl border border-slate-100 shadow-xs overflow-hidden">
                <div class="p-3.5 bg-slate-100/70 border-b border-slate-200/60 flex items-center justify-between">
                    <div class="text-xs font-bold text-slate-700 uppercase tracking-wider">Files & Sections Included in Export</div>
                    <span class="text-xs font-semibold text-emerald-700 bg-emerald-50 px-2.5 py-0.5 rounded-full border border-emerald-200">Multi-Sheet XLSX</span>
                </div>
                <div class="overflow-x-auto max-h-64">
                    <table class="w-full text-left text-sm whitespace-nowrap">
                        <thead class="bg-slate-50 text-slate-500 font-semibold border-b border-slate-100 sticky top-0 bg-slate-50">
                            <tr>
                                <th class="py-3 px-4 w-10 text-center">#</th>
                                <th class="py-3 px-4">Component</th>
                                <th class="py-3 px-4">Section Code</th>
                                <th class="py-3 px-4">Source Filename</th>
                                <th class="py-3 px-4 text-center">Processed Time</th>
                                <th class="py-3 px-4 text-center">Students</th>
                            </tr>
                        </thead>
                        <tbody id="exportConfirmTableBody" class="divide-y divide-slate-100">
                            <!-- Dynamic rows injected here -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="p-4 bg-white border-t border-slate-100 flex items-center justify-between">
            <button id="cancelExportModalBtn" class="px-5 py-2.5 text-sm font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-50 transition">
                Cancel
            </button>
            <button id="confirmExportModalBtn" class="inline-flex items-center gap-2 px-6 py-2.5 text-sm font-bold rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white transition shadow-md shadow-emerald-600/20">
                <x-icon name="download" class="w-4 h-4" /> Confirm & Download Excel
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>
@vite(['resources/js/app.js'])
<script>
    if (window.pdfjsLib) {
        window.pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';
    }

    document.addEventListener('DOMContentLoaded', () => {
        // CHED Official Export Schema Configuration
        const PRIVACY_CONSENT_HEADER = "In accordance with RA 10173 or Data Privacy Act of 2012, I consent to the following terms and conditions on the collection, use, processing and disclosure of my personal data. I am aware that the Davao del Norte State College has collected and stored my personal data upon accomplishment of this form. These data include my full name, birthday, contact details like addresses, landline/mobile numbers, email address and program. I express my consent for the Davao del Norte State College to collect, store my personal information. I hereby affirm my right to be informed, object to processing, access, and rectify and to suspend or withdraw my personal data pursuant to the provisions of the RA 10173 and its implementing rules and regulations. I warrant that I have read, understood all of the above provisions, and agreed with its full implementation. ";
        const PRIVACY_CONSENT_VALUE = "I agree and give my consent.";

        const CHED_EXCEL_HEADERS = [
            'Timestamp',
            'Email Address',
            PRIVACY_CONSENT_HEADER,
            'NSTP Component ',
            'Region: ',
            'Provincial Address:',
            'SURNAME',
            'FIRST NAME',
            'EXTENSION NAME Note: Please write N/A if not applicable. ',
            'MIDDLE NAME',
            'GENDER',
            'BIRTHDAY (YYYY/MM/DD) Follow the format ex. 2004/12/25',
            'CITY ADDRESS ',
            'PROGRAM NAME',
            'EMAIL ADDRESS: ',
            'CONTACT NUMBER '
        ];

        const parseFullName = (rawName) => {
            if (!rawName) return { surname: 'N/A', firstName: 'N/A', middleName: 'N/A', extName: 'N/A' };
            let str = rawName.trim();
            let surname = 'N/A';
            let firstName = 'N/A';
            let middleName = 'N/A';
            let extName = 'N/A';

            const extensions = ['JR', 'JR.', 'SR', 'SR.', 'III', 'IV', 'II', 'V', 'VI'];

            if (str.includes(',')) {
                const parts = str.split(',').map(p => p.trim());
                surname = parts[0] || 'N/A';
                
                let restTokens = parts[1] ? parts[1].split(/\s+/).filter(Boolean) : [];
                if (restTokens.length > 1) {
                    const lastTokenUpper = restTokens[restTokens.length - 1].toUpperCase();
                    if (extensions.includes(lastTokenUpper)) {
                        extName = restTokens.pop();
                    }
                }
                if (restTokens.length > 0) {
                    firstName = restTokens[0];
                    middleName = restTokens.slice(1).join(' ') || 'N/A';
                }
            } else {
                const tokens = str.split(/\s+/).filter(Boolean);
                if (tokens.length === 1) {
                    firstName = tokens[0];
                } else if (tokens.length === 2) {
                    surname = tokens[1];
                    firstName = tokens[0];
                } else {
                    surname = tokens[tokens.length - 1];
                    firstName = tokens[0];
                    middleName = tokens.slice(1, -1).join(' ') || 'N/A';
                }
            }

            return { surname, firstName, middleName, extName };
        };

        const formatChedExcelRow = (std, sectionName = '', componentName = '', timeStr = '') => {
            const parsed = parseFullName(std.name);
            const email = (std.email && std.email !== 'N/A' && std.email.trim() !== '') ? std.email.trim() : 'N/A';
            const now = new Date();
            const defaultTimestamp = `${now.getFullYear()}/${String(now.getMonth() + 1).padStart(2, '0')}/${String(now.getDate()).padStart(2, '0')} ${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')}:${String(now.getSeconds()).padStart(2, '0')}`;
            const timestamp = std.created_at || timeStr || defaultTimestamp;

            return [
                timestamp,
                email,
                PRIVACY_CONSENT_VALUE,
                componentName || std.component || 'CWTS',
                std.region || 'Region XI',
                std.province || 'Davao del Norte',
                parsed.surname.toUpperCase(),
                parsed.firstName.toUpperCase(),
                parsed.extName,
                parsed.middleName.toUpperCase(),
                std.gender || 'N/A',
                std.birthday || 'N/A',
                std.city_address || std.address || 'Panabo City',
                std.program || sectionName || 'BSIT',
                email,
                std.contact_number || std.phone || 'N/A'
            ];
        };

        const getChedColsWidths = () => [
            { wch: 22 }, // Timestamp
            { wch: 28 }, // Email Address
            { wch: 35 }, // Privacy Consent
            { wch: 18 }, // NSTP Component
            { wch: 15 }, // Region
            { wch: 22 }, // Provincial Address
            { wch: 20 }, // SURNAME
            { wch: 20 }, // FIRST NAME
            { wch: 18 }, // EXTENSION NAME
            { wch: 20 }, // MIDDLE NAME
            { wch: 10 }, // GENDER
            { wch: 24 }, // BIRTHDAY
            { wch: 22 }, // CITY ADDRESS
            { wch: 18 }, // PROGRAM NAME
            { wch: 28 }, // EMAIL ADDRESS:
            { wch: 20 }  // CONTACT NUMBER
        ];

        // Elements configuration
        const uploadState = document.getElementById('uploadState');
        const loadingState = document.getElementById('loadingState');
        const resultsState = document.getElementById('resultsState');

        const ocrDropZone = document.getElementById('ocrDropZone');
        const ocrFileInput = document.getElementById('ocrFileInput');
        const ocrUploadHistory = document.getElementById('ocrUploadHistory');
        const noHistoryItem = document.getElementById('noHistoryItem');
        const historyCount = document.getElementById('historyCount');
        const clearHistoryBtn = document.getElementById('clearHistoryBtn');
        const exportAllHistoryBtn = document.getElementById('exportAllHistoryBtn');

        const exportConfirmModal = document.getElementById('exportConfirmModal');
        const closeExportConfirmModalBtn = document.getElementById('closeExportConfirmModalBtn');
        const cancelExportModalBtn = document.getElementById('cancelExportModalBtn');
        const confirmExportModalBtn = document.getElementById('confirmExportModalBtn');
        const exportModalFileCount = document.getElementById('exportModalFileCount');
        const exportModalStudentCount = document.getElementById('exportModalStudentCount');
        const exportConfirmTableBody = document.getElementById('exportConfirmTableBody');

        const loadingTitle = document.getElementById('loadingTitle');
        const loadingSubtitle = document.getElementById('loadingSubtitle');
        const loadingProgressBar = document.getElementById('loadingProgressBar');

        const resultsSectionName = document.getElementById('resultsSectionName');
        const resultsFilename = document.getElementById('resultsFilename');
        const resultsComponentTag = document.getElementById('resultsComponentTag');
        const closeResultsBtn = document.getElementById('closeResultsBtn');

        const metricTotal = document.getElementById('metricTotal');
        const metricPassed = document.getElementById('metricPassed');
        const metricFailed = document.getElementById('metricFailed');
        const metricPassRate = document.getElementById('metricPassRate');

        const rosterSearch = document.getElementById('rosterSearch');
        const rosterTableBody = document.getElementById('rosterTableBody');
        const rosterPaginationInfo = document.getElementById('rosterPaginationInfo');
        const rosterPageIndicator = document.getElementById('rosterPageIndicator');
        const rosterPrevBtn = document.getElementById('rosterPrevBtn');
        const rosterNextBtn = document.getElementById('rosterNextBtn');

        // Current active result roster cache & pagination state
        let currentRoster = [];
        let filteredRoster = [];
        let currentPage = 1;
        const itemsPerPage = 10;

        // 1. History Persistence Management (Local Storage)
        const loadHistoryFromStorage = () => {
            const stored = localStorage.getItem('NSTP_OCR_HISTORY');
            let history = [];
            try {
                history = stored ? JSON.parse(stored) : [];
            } catch (e) {
                history = [];
            }

            // Remove no history item if there are history entries
            if (history.length > 0) {
                if (noHistoryItem) noHistoryItem.classList.add('hidden');
                if (clearHistoryBtn) clearHistoryBtn.classList.remove('hidden');
                if (exportAllHistoryBtn) exportAllHistoryBtn.classList.remove('hidden');
                
                // Remove existing history elements except noHistoryItem
                const items = ocrUploadHistory.querySelectorAll('.history-entry');
                items.forEach(el => el.remove());

                // Loop and inject
                history.forEach((entry, idx) => {
                    const li = document.createElement('li');
                    li.className = 'history-entry flex items-center justify-between p-3.5 rounded-xl border border-slate-50 hover:border-indigo-100 hover:bg-indigo-50/20 cursor-pointer transition duration-200 group';
                    
                    let componentClass = entry.summary.component === 'ROTC' 
                        ? 'bg-rose-50 text-rose-700' 
                        : (entry.summary.component === 'LTS' ? 'bg-emerald-50 text-emerald-700' : 'bg-indigo-50 text-indigo-700');
                    
                    li.innerHTML = `
                        <div class="flex items-center gap-3 min-w-0">
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded ${componentClass}">${entry.summary.component || 'CWTS'}</span>
                            <div class="min-w-0">
                                <div class="text-sm font-bold text-slate-800 truncate">${entry.summary.section}</div>
                                <div class="text-[10px] text-slate-400 font-medium truncate mt-0.5">${entry.filename} &middot; ${entry.time}</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 shrink-0">
                            <span class="text-xs font-bold text-slate-500 group-hover:text-indigo-600 transition">${entry.summary.total} stds</span>
                            <button class="delete-history-btn p-1.5 rounded-lg text-slate-300 hover:text-rose-600 hover:bg-rose-50 opacity-0 group-hover:opacity-100 transition duration-200" data-idx="${idx}" title="Delete Record">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                                </svg>
                            </button>
                        </div>
                    `;

                    // Bind item click to display historical results
                    li.addEventListener('click', (e) => {
                        if (e.target.closest('.delete-history-btn')) return;
                        displayResults(entry);
                    });

                    ocrUploadHistory.appendChild(li);
                });

                // Attach delete button events
                document.querySelectorAll('.delete-history-btn').forEach(btn => {
                    btn.addEventListener('click', (e) => {
                        e.stopPropagation();
                        const idx = parseInt(btn.dataset.idx);
                        deleteHistoryItem(idx);
                    });
                });

                historyCount.textContent = `${history.length} file(s) processed`;
            } else {
                if (noHistoryItem) noHistoryItem.classList.remove('hidden');
                if (clearHistoryBtn) clearHistoryBtn.classList.add('hidden');
                if (exportAllHistoryBtn) exportAllHistoryBtn.classList.add('hidden');
                historyCount.textContent = '0 file(s) processed';
                
                // Clear any existing list items
                const items = ocrUploadHistory.querySelectorAll('.history-entry');
                items.forEach(el => el.remove());
            }
        };

        const saveHistoryToStorage = (entry) => {
            const stored = localStorage.getItem('NSTP_OCR_HISTORY');
            let history = [];
            try {
                history = stored ? JSON.parse(stored) : [];
            } catch (e) {
                history = [];
            }

            // Remove existing history item with same section and filename to prevent duplicates
            history = history.filter(h => !(h.summary.section === entry.summary.section && h.filename === entry.filename));

            history.unshift(entry);
            localStorage.setItem('NSTP_OCR_HISTORY', JSON.stringify(history));
            loadHistoryFromStorage();
        };

        const deleteHistoryItem = (idx) => {
            const stored = localStorage.getItem('NSTP_OCR_HISTORY');
            let history = stored ? JSON.parse(stored) : [];
            history.splice(idx, 1);
            localStorage.setItem('NSTP_OCR_HISTORY', JSON.stringify(history));
            loadHistoryFromStorage();
            if (window.showToast) window.showToast('Import record deleted from history.', 'info', 'Record Removed');
        };

        if (clearHistoryBtn) {
            clearHistoryBtn.addEventListener('click', () => {
                if (confirm("Are you sure you want to clear your entire import history? This does not delete students from the database.")) {
                    localStorage.removeItem('NSTP_OCR_HISTORY');
                    loadHistoryFromStorage();
                    if (window.showToast) window.showToast('Import history cleared.', 'success', 'History Cleared');
                }
            });
        }

        const openExportConfirmModal = () => {
            const stored = localStorage.getItem('NSTP_OCR_HISTORY');
            let history = [];
            try {
                history = stored ? JSON.parse(stored) : [];
            } catch (e) {
                history = [];
            }

            if (!history.length) {
                if (window.showToast) window.showToast('No history entries available to export.', 'warning', 'Export Empty');
                return;
            }

            let totalStudents = 0;
            if (exportConfirmTableBody) exportConfirmTableBody.innerHTML = '';

            history.forEach((entry, idx) => {
                const stdCount = (entry.results || []).length;
                totalStudents += stdCount;

                if (exportConfirmTableBody) {
                    const tr = document.createElement('tr');
                    tr.className = 'border-b border-slate-100 hover:bg-slate-50 transition text-xs';
                    
                    let componentClass = entry.summary.component === 'ROTC' 
                        ? 'bg-rose-50 text-rose-700' 
                        : (entry.summary.component === 'LTS' ? 'bg-emerald-50 text-emerald-700' : 'bg-indigo-50 text-indigo-700');

                    tr.innerHTML = `
                        <td class="py-3 px-4 text-center font-bold text-slate-400">${idx + 1}</td>
                        <td class="py-3 px-4">
                            <span class="font-bold px-2 py-0.5 rounded ${componentClass}">${entry.summary.component || 'CWTS'}</span>
                        </td>
                        <td class="py-3 px-4 font-bold text-slate-800">${entry.summary.section || 'N/A'}</td>
                        <td class="py-3 px-4 font-mono text-slate-600 truncate max-w-xs">${entry.filename || 'N/A'}</td>
                        <td class="py-3 px-4 text-center text-slate-400">${entry.time || 'N/A'}</td>
                        <td class="py-3 px-4 text-center font-bold text-indigo-600">${stdCount}</td>
                    `;
                    exportConfirmTableBody.appendChild(tr);
                }
            });

            if (exportModalFileCount) exportModalFileCount.textContent = `${history.length} File(s)`;
            if (exportModalStudentCount) exportModalStudentCount.textContent = `${totalStudents} Record(s)`;

            if (exportConfirmModal) exportConfirmModal.classList.remove('hidden');
        };

        const closeExportConfirmModal = () => {
            if (exportConfirmModal) exportConfirmModal.classList.add('hidden');
        };

        if (closeExportConfirmModalBtn) closeExportConfirmModalBtn.addEventListener('click', closeExportConfirmModal);
        if (cancelExportModalBtn) cancelExportModalBtn.addEventListener('click', closeExportConfirmModal);

        if (exportAllHistoryBtn) {
            exportAllHistoryBtn.addEventListener('click', openExportConfirmModal);
        }

        if (confirmExportModalBtn) {
            confirmExportModalBtn.addEventListener('click', () => {
                closeExportConfirmModal();
                executeAllHistoryExport();
            });
        }

        const executeAllHistoryExport = () => {
            if (!window.XLSX) {
                alert('SheetJS library is still loading. Please try again in a moment.');
                return;
            }

            const stored = localStorage.getItem('NSTP_OCR_HISTORY');
            let history = [];
            try {
                history = stored ? JSON.parse(stored) : [];
            } catch (e) {
                history = [];
            }

            if (!history.length) {
                if (window.showToast) window.showToast('No history entries available to export.', 'warning', 'Export Empty');
                return;
            }

            try {
                const wb = window.XLSX.utils.book_new();
                let totalRecordsCount = 0;
                const usedSheetNames = new Set();

                const masterRows = [ CHED_EXCEL_HEADERS ];

                history.forEach((entry) => {
                    const sectionName = entry.summary ? entry.summary.section : 'Imported';
                    const component = entry.summary ? entry.summary.component : 'CWTS';
                    const students = entry.results || [];

                    students.forEach((std) => {
                        totalRecordsCount++;
                        masterRows.push(formatChedExcelRow(std, sectionName, component, entry.time));
                    });

                    if (students.length > 0) {
                        const sectionRows = [ CHED_EXCEL_HEADERS ];
                        students.forEach((std) => {
                            sectionRows.push(formatChedExcelRow(std, sectionName, component, entry.time));
                        });

                        const wsSection = window.XLSX.utils.aoa_to_sheet(sectionRows);
                        wsSection['!cols'] = getChedColsWidths();

                        // Ensure unique sheet name in Excel (max 31 chars)
                        let baseSheetName = sectionName.replace(/[:\\/?*\[\]]/g, '').slice(0, 25) || 'Section';
                        let uniqueSheetName = baseSheetName;
                        let counter = 1;
                        while (usedSheetNames.has(uniqueSheetName)) {
                            uniqueSheetName = `${baseSheetName}_${counter}`;
                            counter++;
                        }
                        usedSheetNames.add(uniqueSheetName);

                        window.XLSX.utils.book_append_sheet(wb, wsSection, uniqueSheetName);
                    }
                });

                // Master summary sheet
                const wsMaster = window.XLSX.utils.aoa_to_sheet(masterRows);
                wsMaster['!cols'] = getChedColsWidths();

                let masterSheetName = 'All History Imports';
                let mCounter = 1;
                while (usedSheetNames.has(masterSheetName)) {
                    masterSheetName = `All History Imports_${mCounter}`;
                    mCounter++;
                }
                window.XLSX.utils.book_append_sheet(wb, wsMaster, masterSheetName);

                const fileName = `CHED_NSTP_All_Import_History_${Date.now().toString().slice(-6)}.xlsx`;
                window.XLSX.writeFile(wb, fileName);

                if (window.showToast) window.showToast(`Exported ${totalRecordsCount} student record(s) across ${history.length} section file(s).`, 'success', 'History Export Completed');
            } catch (err) {
                console.error('Export Error:', err);
                alert('Export failed: ' + err.message);
            }
        };

        // Initialize history list
        loadHistoryFromStorage();

        // 2. Drag & Drop Actions Setup
        if (ocrDropZone) {
            ocrDropZone.addEventListener('click', () => ocrFileInput && ocrFileInput.click());
            ocrDropZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                ocrDropZone.classList.add('border-indigo-400', 'bg-indigo-50/40');
            });
            ocrDropZone.addEventListener('dragleave', () => {
                ocrDropZone.classList.remove('border-indigo-400', 'bg-indigo-50/40');
            });
            ocrDropZone.addEventListener('drop', (e) => {
                e.preventDefault();
                ocrDropZone.classList.remove('border-indigo-400', 'bg-indigo-50/40');
                if (e.dataTransfer.files.length) {
                    processFile(e.dataTransfer.files[0]);
                }
            });
        }

        if (ocrFileInput) {
            ocrFileInput.addEventListener('click', e => e.stopPropagation());
            ocrFileInput.addEventListener('change', (e) => {
                if (e.target.files.length) {
                    processFile(e.target.files[0]);
                }
            });
        }

        // Process File via SheetJS / PDF.js / Image Reader
        const processFile = (file) => {
            const ext = file.name.split('.').pop().toLowerCase();
            if (!['xlsx', 'xls', 'pdf', 'png', 'jpg', 'jpeg'].includes(ext)) {
                if (window.showToast) window.showToast('Only Excel, PDF, and Image (PNG/JPG) formats are supported.', 'error', 'Unsupported File');
                return;
            }

            if (file.size > 25 * 1024 * 1024) {
                if (window.showToast) window.showToast('File size exceeds the 25 MB limit.', 'error', 'File Too Large');
                return;
            }

            if (ext === 'pdf') {
                processPdfFile(file);
                return;
            }

            if (['png', 'jpg', 'jpeg'].includes(ext)) {
                processImageFile(file);
                return;
            }

            // Enter loading visual state for Excel
            uploadState.classList.add('hidden');
            loadingState.classList.remove('hidden');
            updateProgress(25, 'Reading workbook...', 'Loading file contents...');

            const reader = new FileReader();
            reader.onload = (evt) => {
                try {
                    updateProgress(50, 'Parsing grade rows...', 'Extracting columns from sheet...');

                    const wb = window.XLSX.read(evt.target.result, { type: 'array' });
                    const ws = wb.Sheets[wb.SheetNames[0]];
                    const rows = window.XLSX.utils.sheet_to_json(ws, { defval: '' });

                    if (!rows.length) {
                        throw new Error('The Excel sheet appears to be empty.');
                    }

                    const getColVal = (row, ...keys) => {
                        for (const k of keys) {
                            const found = Object.keys(row).find(rk => rk.trim().toLowerCase() === k.toLowerCase());
                            if (found !== undefined && String(row[found]).trim() !== '') {
                                return String(row[found]).trim();
                            }
                        }
                        return '';
                    };

                    let sectionCode = '';
                    const parsedStudents = [];

                    rows.forEach(row => {
                        const name = getColVal(row, 'Student Name', 'Name', 'Full Name', 'Student', 'Lastname, Firstname', 'Student_Name', 'STUDENT NAME', 'FULLNAME');
                        const grade = getColVal(row, 'Grade', 'Final Grade', 'Final_Grade', 'GWA', 'Score', 'Rating', 'Grades', 'GRADE', 'FINAL GRADE');
                        const sec = getColVal(row, 'Section', 'Section Code', 'Class', 'SECTION');
                        const studentNo = getColVal(row, 'Student No', 'Student Number', 'ID', 'Student_No', 'Student_Number', 'STUDENT NO', 'STUDENT NUMBER');
                        const rawRemarks = getColVal(row, 'Remarks', 'Status', 'Remark', 'Remarks/Status', 'REMARKS', 'STATUS', 'REMARK');

                        if (!sectionCode && sec) sectionCode = sec;
                        if (!name) return;

                        let remarks = 'N/A';
                        if (rawRemarks) {
                            const cleanR = rawRemarks.toLowerCase();
                            if (cleanR.startsWith('pass') || cleanR === 'p') remarks = 'Passed';
                            else if (cleanR.startsWith('fail') || cleanR === 'f' || cleanR.startsWith('drop') || cleanR === 'drp') remarks = 'Failed';
                            else if (cleanR.startsWith('pend') || cleanR === 'inc') remarks = 'Pending';
                        }
                        if (remarks === 'N/A' && grade) {
                            const g = parseFloat(grade);
                            if (!isNaN(g)) {
                                if (g >= 1.0 && g <= 3.0) remarks = 'Passed';
                                else if (g > 3.0) remarks = 'Failed';
                            }
                        }

                        parsedStudents.push({
                            name,
                            student_no: studentNo || null,
                            grade: grade || null,
                            remarks: remarks !== 'N/A' ? remarks : 'Passed'
                        });
                    });

                    if (!parsedStudents.length) {
                        throw new Error('No valid student rows found in the Excel file.');
                    }

                    if (!sectionCode) {
                        sectionCode = file.name
                            .replace(/\.(xlsx|xls)$/i, '')
                            .replace(/[_\s]+/g, '-')
                            .split('-').slice(0, 2).join('-')
                            .toUpperCase() || 'IMPORTED';
                    }

                    loadingState.classList.add('hidden');
                    openReviewModal(file.name, sectionCode, parsedStudents, null);

                } catch (err) {
                    handleError(err.message);
                }
            };

            reader.onerror = () => handleError('Could not read Excel file.');
            reader.readAsArrayBuffer(file);
        };

        // Process PDF Grading Sheets
        const processPdfFile = (file) => {
            uploadState.classList.add('hidden');
            loadingState.classList.remove('hidden');
            updateProgress(25, 'Reading PDF Document...', 'Loading PDF pages...');

            const reader = new FileReader();
            reader.onload = async (evt) => {
                try {
                    updateProgress(50, 'Extracting PDF text...', 'Parsing text layers from PDF pages...');
                    
                    const arrayBuffer = evt.target.result;
                    const loadingTask = window.pdfjsLib.getDocument({ data: arrayBuffer });
                    const pdf = await loadingTask.promise;

                    let fullTextLines = [];
                    let detectedSubject = '';
                    let detectedCourse = '';

                    for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
                        const page = await pdf.getPage(pageNum);
                        const textContent = await page.getTextContent();
                        
                        const items = textContent.items;
                        const lineMap = new Map();

                        items.forEach(item => {
                            if (!item.str || !item.str.trim()) return;
                            const y = Math.round(item.transform[5] / 4) * 4;
                            if (!lineMap.has(y)) {
                                lineMap.set(y, []);
                            }
                            lineMap.get(y).push({
                                x: item.transform[4],
                                text: item.str.trim()
                            });
                        });

                        const sortedYs = Array.from(lineMap.keys()).sort((a, b) => b - a);

                        sortedYs.forEach(y => {
                            const lineItems = lineMap.get(y).sort((a, b) => a.x - b.x);
                            const lineStr = lineItems.map(i => i.text).join(' ');
                            fullTextLines.push(lineStr);

                            if (lineStr.includes('Subject No:') || lineStr.includes('Subject:')) {
                                const m = lineStr.match(/(?:Subject No:|Subject:)\s*([A-Za-z0-9_\-\s]+)/i);
                                if (m && m[1]) detectedSubject = m[1].trim().split(/\s+/)[0];
                            }
                            if (lineStr.includes('Course/Year:')) {
                                const m = lineStr.match(/Course\/Year:\s*([A-Za-z0-9_\-\s]+)/i);
                                if (m && m[1]) detectedCourse = m[1].trim().split(/\s+/)[0];
                            }
                        });
                    }

                    const parsedStudents = [];
                    const idRegex = /(\d{4}-\d{5})/;
                    const remarkOptions = ['PASSED', 'FAILED', 'DROPPED', 'PASS', 'FAIL', 'DRP', 'PENDING', 'ACTIVE', 'INC'];

                    fullTextLines.forEach(line => {
                        const idMatch = line.match(idRegex);
                        if (!idMatch) return;

                        const studentNo = idMatch[1];
                        const parts = line.split(/\s+/);
                        const idIdx = parts.findIndex(p => p.includes(studentNo));
                        if (idIdx === -1) return;

                        const afterIdTokens = parts.slice(idIdx + 1);
                        const len = afterIdTokens.length;
                        if (len < 3) return;

                        let foundRemarks = afterIdTokens[len - 1].toUpperCase();
                        let foundGrade = null;
                        let nameEndIdx = len - 1;

                        const isLastTokenRemark = remarkOptions.includes(foundRemarks);

                        if (isLastTokenRemark) {
                            foundGrade = afterIdTokens[len - 3];
                            nameEndIdx = len - 3;
                            
                            const possiblePercentage = afterIdTokens[len - 4];
                            if (possiblePercentage && (isPercentageGrade(possiblePercentage) || possiblePercentage.toUpperCase() === 'DRP')) {
                                nameEndIdx = len - 4;
                            }
                        } else {
                            for (let i = len - 1; i >= 0; i--) {
                                const token = afterIdTokens[i].toUpperCase();
                                if (!foundRemarks && remarkOptions.includes(token)) {
                                    foundRemarks = token;
                                } else if (foundGrade === null && (isNumericGrade(token) || token === 'DRP')) {
                                    foundGrade = afterIdTokens[i];
                                } else if (foundRemarks || foundGrade !== null) {
                                    nameEndIdx = i + 1;
                                    break;
                                }
                            }
                        }

                        const name = afterIdTokens.slice(0, nameEndIdx).join(' ').trim();
                        if (!name) return;

                        let remarks = 'N/A';
                        if (foundRemarks === 'PASSED' || foundRemarks === 'PASS') remarks = 'Passed';
                        else if (foundRemarks === 'FAILED' || foundRemarks === 'FAIL' || foundRemarks === 'DROPPED' || foundRemarks === 'DRP') remarks = 'Failed';
                        else if (foundRemarks === 'PENDING' || foundRemarks === 'ACTIVE' || foundRemarks === 'INC') remarks = 'Pending';
                        else if (foundGrade) {
                            const g = parseFloat(foundGrade);
                            if (!isNaN(g)) {
                                if (g >= 1.0 && g <= 3.0) remarks = 'Passed';
                                else if (g > 3.0) remarks = 'Failed';
                            }
                        }

                        parsedStudents.push({
                            name: name,
                            student_no: studentNo,
                            serial_no: null,
                            grade: foundGrade,
                            remarks: remarks
                        });
                    });

                    if (!parsedStudents.length) {
                        throw new Error('No student records with valid IDs (e.g. 2025-00195) found in the PDF.');
                    }

                    let sectionCode = '';
                    if (detectedSubject && detectedCourse) {
                        sectionCode = `${detectedSubject}-${detectedCourse}`.toUpperCase();
                    } else if (detectedSubject) {
                        sectionCode = detectedSubject.toUpperCase();
                    } else if (detectedCourse) {
                        sectionCode = detectedCourse.toUpperCase();
                    } else {
                        sectionCode = file.name
                            .replace(/\.pdf$/i, '')
                            .replace(/[_\s]+/g, '-')
                            .toUpperCase();
                    }

                    loadingState.classList.add('hidden');
                    openReviewModal(file.name, sectionCode, parsedStudents, null);

                } catch (err) {
                    handleError(err.message || 'Failed to read PDF file.');
                }
            };

            reader.onerror = () => handleError('Could not read PDF file.');
            reader.readAsArrayBuffer(file);
        };

        // Process Scanned Image File (PNG / JPG / JPEG)
        const processImageFile = (file) => {
            uploadState.classList.add('hidden');
            loadingState.classList.add('hidden');
            
            const reader = new FileReader();
            reader.onload = (evt) => {
                const imgDataUrl = evt.target.result;
                const sectionCode = file.name.replace(/\.[^/.]+$/, '').replace(/[_\s]+/g, '-').toUpperCase();

                // Initial sample rows for coordinator to review/edit
                const initialRows = [
                    { name: 'ENTER STUDENT NAME', student_no: '2025-00001', grade: '1.0', remarks: 'Passed' }
                ];

                openReviewModal(file.name, sectionCode, initialRows, imgDataUrl);
            };

            reader.onerror = () => handleError('Could not read Image file.');
            reader.readAsDataURL(file);
        };

        // 4. Pre-Save Grade Verification & Review Modal Handlers
        let pendingFile = { filename: '', section: '', students: [] };

        const openReviewModal = (filename, sectionCode, students, imgUrl = null) => {
            pendingFile = { filename, section: sectionCode, students };

            const reviewModal = document.getElementById('ocrReviewModal');
            const reviewSectionInput = document.getElementById('reviewSectionInput');
            const reviewFilenameBadge = document.getElementById('reviewFilenameBadge');
            const reviewCountBadge = document.getElementById('reviewCountBadge');
            const reviewImageContainer = document.getElementById('reviewImageContainer');
            const reviewImgPreview = document.getElementById('reviewImgPreview');

            if (reviewSectionInput) reviewSectionInput.value = sectionCode;
            if (reviewFilenameBadge) reviewFilenameBadge.textContent = filename;
            if (reviewCountBadge) reviewCountBadge.textContent = `${students.length} Record(s)`;

            if (imgUrl) {
                if (reviewImgPreview) reviewImgPreview.src = imgUrl;
                if (reviewImageContainer) reviewImageContainer.classList.remove('hidden');
            } else {
                if (reviewImageContainer) reviewImageContainer.classList.add('hidden');
            }

            renderReviewTableRows();
            if (reviewModal) reviewModal.classList.remove('hidden');
        };

        const closeReviewModal = () => {
            const reviewModal = document.getElementById('ocrReviewModal');
            if (reviewModal) reviewModal.classList.add('hidden');
            if (uploadState) uploadState.classList.remove('hidden');
            if (ocrFileInput) ocrFileInput.value = '';
        };

        const renderReviewTableRows = () => {
            const reviewTableBody = document.getElementById('reviewTableBody');
            const reviewCountBadge = document.getElementById('reviewCountBadge');
            if (!reviewTableBody) return;

            reviewTableBody.innerHTML = '';
            if (reviewCountBadge) reviewCountBadge.textContent = `${pendingFile.students.length} Record(s)`;

            pendingFile.students.forEach((std, i) => {
                const tr = document.createElement('tr');
                tr.className = 'border-b border-slate-100 hover:bg-slate-50 transition';

                tr.innerHTML = `
                    <td class="py-2 px-3 text-center text-xs font-bold text-slate-400">${i + 1}</td>
                    <td class="py-2 px-3">
                        <input type="text" value="${std.name || ''}" data-idx="${i}" data-field="name" class="review-input w-full px-2.5 py-1 text-xs font-semibold rounded-lg border border-slate-200 focus:border-indigo-400 focus:bg-white focus:outline-none transition" placeholder="Student Full Name" />
                    </td>
                    <td class="py-2 px-3">
                        <input type="text" value="${std.student_no || ''}" data-idx="${i}" data-field="student_no" class="review-input w-full px-2.5 py-1 text-xs font-mono text-slate-700 rounded-lg border border-slate-200 focus:border-indigo-400 focus:bg-white focus:outline-none transition" placeholder="e.g. 2025-00195" />
                    </td>
                    <td class="py-2 px-3 text-center">
                        <input type="text" value="${std.grade !== null ? std.grade : ''}" data-idx="${i}" data-field="grade" class="review-input w-20 text-center px-2 py-1 text-xs font-bold rounded-lg border border-slate-200 focus:border-indigo-400 focus:bg-white focus:outline-none transition" placeholder="1.25" />
                    </td>
                    <td class="py-2 px-3 text-center">
                        <select data-idx="${i}" data-field="remarks" class="review-select text-xs font-bold px-2 py-1 rounded-lg border border-slate-200 focus:outline-none transition">
                            <option value="Passed" ${std.remarks === 'Passed' ? 'selected' : ''}>Passed</option>
                            <option value="Failed" ${std.remarks === 'Failed' ? 'selected' : ''}>Failed</option>
                            <option value="Pending" ${std.remarks === 'Pending' ? 'selected' : ''}>Pending</option>
                        </select>
                    </td>
                    <td class="py-2 px-3 text-center">
                        <button data-remove-idx="${i}" class="btn-remove-row text-xs text-rose-500 hover:text-rose-700 font-bold px-2 py-1 rounded hover:bg-rose-50 transition">
                            &times;
                        </button>
                    </td>
                `;
                reviewTableBody.appendChild(tr);
            });

            // Bind input update events inside modal table
            reviewTableBody.querySelectorAll('.review-input').forEach(inp => {
                inp.addEventListener('input', (e) => {
                    const idx = parseInt(e.target.dataset.idx);
                    const field = e.target.dataset.field;
                    if (pendingFile.students[idx]) {
                        pendingFile.students[idx][field] = e.target.value;
                    }
                });
            });

            reviewTableBody.querySelectorAll('.review-select').forEach(sel => {
                sel.addEventListener('change', (e) => {
                    const idx = parseInt(e.target.dataset.idx);
                    if (pendingFile.students[idx]) {
                        pendingFile.students[idx].remarks = e.target.value;
                    }
                });
            });

            reviewTableBody.querySelectorAll('.btn-remove-row').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const idx = parseInt(e.target.dataset.removeIdx);
                    pendingFile.students.splice(idx, 1);
                    renderReviewTableRows();
                });
            });
        };

        // Modal Action Buttons
        const closeReviewModalBtn = document.getElementById('closeReviewModalBtn');
        const cancelReviewBtn = document.getElementById('cancelReviewBtn');
        const addReviewRowBtn = document.getElementById('addReviewRowBtn');
        const confirmSyncBtn = document.getElementById('confirmSyncBtn');
        const reviewSectionInput = document.getElementById('reviewSectionInput');

        if (closeReviewModalBtn) closeReviewModalBtn.addEventListener('click', closeReviewModal);
        if (cancelReviewBtn) cancelReviewBtn.addEventListener('click', closeReviewModal);

        if (addReviewRowBtn) {
            addReviewRowBtn.addEventListener('click', () => {
                pendingFile.students.push({
                    name: 'NEW STUDENT',
                    student_no: '',
                    grade: '1.0',
                    remarks: 'Passed'
                });
                renderReviewTableRows();
            });
        }

        if (confirmSyncBtn) {
            confirmSyncBtn.addEventListener('click', () => {
                const targetSection = (reviewSectionInput ? reviewSectionInput.value.trim() : '') || pendingFile.section || 'IMPORTED';

                if (!pendingFile.students.length) {
                    alert('Please add at least one student row before approving.');
                    return;
                }

                // Show loading state and send AJAX request
                const reviewModal = document.getElementById('ocrReviewModal');
                if (reviewModal) reviewModal.classList.add('hidden');
                
                uploadState.classList.add('hidden');
                loadingState.classList.remove('hidden');
                updateProgress(75, 'Synchronizing Database...', 'Saving approved grades to DNSC portal database...');

                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                
                fetch('/coordinator/ocr/import', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({
                        section: targetSection.toUpperCase(),
                        filename: pendingFile.filename,
                        students: pendingFile.students
                    })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.json().then(err => { throw new Error(err.message || 'Database sync error.'); });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        updateProgress(100, 'Import & Sync Completed!', 'Approved grades recorded successfully.');
                        
                        const now = new Date();
                        const ampm = now.getHours() >= 12 ? 'PM' : 'AM';
                        const hours = now.getHours() % 12 || 12;
                        const timeStr = `Today ${hours}:${String(now.getMinutes()).padStart(2, '0')} ${ampm}`;
                        
                        const historyEntry = {
                            filename: pendingFile.filename,
                            time: timeStr,
                            summary: data.summary,
                            results: data.results
                        };

                        saveHistoryToStorage(historyEntry);

                        setTimeout(() => {
                            loadingState.classList.add('hidden');
                            displayResults(historyEntry);
                        }, 500);

                        if (window.finishProgressModal) {
                            window.finishProgressModal('OCR Sync Complete', `Successfully synced ${data.summary.total} student grades for section ${data.summary.section}.`, 'success');
                        } else if (window.showToast) {
                            window.showToast(`Successfully synced ${data.summary.total} student grades for section <strong>${data.summary.section}</strong>.`, 'success', 'Sync Successful');
                        }
                    } else {
                        throw new Error(data.message || 'Database sync failure.');
                    }
                })
                .catch(err => handleError(err.message || 'Failed to sync approved grades.'));
            });
        }

        const isNumericGrade = (val) => {
            const num = parseFloat(val);
            return !isNaN(num) && ((num >= 1.0 && num <= 5.0) || (num >= 50 && num <= 100));
        };

        const isPercentageGrade = (val) => {
            const num = parseFloat(val);
            return !isNaN(num) && num >= 50 && num <= 100;
        };

        const updateProgress = (pct, title, sub) => {
            loadingProgressBar.style.width = `${pct}%`;
            loadingTitle.textContent = title;
            loadingSubtitle.textContent = sub;
        };

        const handleError = (msg) => {
            console.error('OCR Error:', msg);
            loadingState.classList.add('hidden');
            uploadState.classList.remove('hidden');
            if (ocrFileInput) ocrFileInput.value = '';
            
            alert(`OCR Import Failed: ${msg}`);
            if (window.showToast) window.showToast(msg, 'error', 'Import Failed');
        };

        // 5. Display Roster Results Visual State
        const displayResults = (entry) => {
            uploadState.classList.add('hidden');
            loadingState.classList.add('hidden');
            resultsState.classList.remove('hidden');

            resultsSectionName.textContent = `${entry.summary.section} Grades`;
            resultsFilename.textContent = `File: ${entry.filename} \u00B7 Processed: ${entry.time}`;

            // Component Tag Styling
            resultsComponentTag.textContent = entry.summary.component || 'CWTS';
            resultsComponentTag.className = 'text-xs px-2.5 py-0.5 rounded-full font-bold';
            if (entry.summary.component === 'ROTC') {
                resultsComponentTag.classList.add('bg-rose-50', 'text-rose-700');
            } else if (entry.summary.component === 'LTS') {
                resultsComponentTag.classList.add('bg-emerald-50', 'text-emerald-700');
            } else {
                resultsComponentTag.classList.add('bg-indigo-50', 'text-indigo-700');
            }

            // Summary Metrics
            metricTotal.textContent = entry.summary.total;
            metricPassed.textContent = entry.summary.passed;
            metricFailed.textContent = entry.summary.failed;
            
            const rate = entry.summary.total > 0 
                ? Math.round((entry.summary.passed / entry.summary.total) * 100) 
                : 0;
            metricPassRate.textContent = `${rate}%`;

            // Cache current list
            currentRoster = entry.results;
            filteredRoster = currentRoster;
            currentPage = 1;
            
            // Render Table Rows with Pagination
            updateRosterPagination();
            
            // Clear search field
            if (rosterSearch) rosterSearch.value = '';
        };

        const updateRosterPagination = () => {
            const totalItems = filteredRoster.length;
            const totalPages = Math.max(1, Math.ceil(totalItems / itemsPerPage));
            if (currentPage > totalPages) currentPage = totalPages;
            if (currentPage < 1) currentPage = 1;

            const startIdx = (currentPage - 1) * itemsPerPage;
            const endIdx = Math.min(startIdx + itemsPerPage, totalItems);
            const pageItems = filteredRoster.slice(startIdx, endIdx);

            renderRosterRows(pageItems, startIdx);

            if (rosterPaginationInfo) {
                if (totalItems === 0) {
                    rosterPaginationInfo.textContent = 'Showing 0 entries';
                } else {
                    rosterPaginationInfo.textContent = `Showing ${startIdx + 1} to ${endIdx} of ${totalItems} entries`;
                }
            }
            if (rosterPageIndicator) {
                rosterPageIndicator.textContent = `Page ${currentPage} of ${totalPages}`;
            }
            if (rosterPrevBtn) {
                rosterPrevBtn.disabled = currentPage <= 1;
            }
            if (rosterNextBtn) {
                rosterNextBtn.disabled = currentPage >= totalPages;
            }
        };

        const selectAllRoster = document.getElementById('selectAllRoster');
        const exportSelectedBtn = document.getElementById('exportSelectedBtn');
        const exportResultsBtn = document.getElementById('exportResultsBtn');

        if (selectAllRoster) {
            selectAllRoster.addEventListener('change', (e) => {
                const chks = document.querySelectorAll('.roster-chk');
                chks.forEach(c => c.checked = e.target.checked);
            });
        }

        const renderRosterRows = (list, offset = 0) => {
            if (!rosterTableBody) return;
            rosterTableBody.innerHTML = '';
            if (selectAllRoster) selectAllRoster.checked = false;

            if (list.length === 0) {
                rosterTableBody.innerHTML = `<tr><td colspan="7" class="py-8 text-center text-slate-400 text-sm">No matching records found.</td></tr>`;
                return;
            }

            list.forEach((std, i) => {
                const tr = document.createElement('tr');
                tr.className = 'border-b border-slate-50 hover:bg-slate-50/50 transition duration-150';

                // 3-Color Remarks Badges: Green (Passed), Red (Failed), Amber (Pending)
                let remarksBadge = `<span class="inline-flex items-center justify-center text-xs font-bold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">Pending</span>`;
                if (std.remarks === 'Passed') {
                    remarksBadge = `<span class="inline-flex items-center justify-center text-xs font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-200">Passed</span>`;
                } else if (std.remarks === 'Failed' || std.remarks === 'Dropped') {
                    remarksBadge = `<span class="inline-flex items-center justify-center text-xs font-bold px-2.5 py-0.5 rounded-full bg-rose-50 text-rose-700 border border-rose-200">Failed</span>`;
                }

                // Database Status Badge
                let syncBadge = std.is_new
                    ? `<span class="inline-flex items-center justify-center text-[10px] font-semibold px-2 py-0.5 rounded border border-violet-200 bg-violet-50 text-violet-700">Newly Registered</span>`
                    : `<span class="inline-flex items-center justify-center text-[10px] font-semibold px-2 py-0.5 rounded border border-emerald-200 bg-emerald-50 text-emerald-700">Matched</span>`;

                tr.innerHTML = `
                    <td class="py-3 px-4 text-center">
                        <input type="checkbox" class="roster-chk rounded border-slate-300 text-indigo-600 focus:ring-indigo-500 cursor-pointer" data-idx="${offset + i}" />
                    </td>
                    <td class="py-3 px-4 text-center font-medium text-slate-400">${offset + i + 1}</td>
                    <td class="py-3 px-4 font-semibold text-slate-800">${std.name}</td>
                    <td class="py-3 px-4 font-mono text-xs text-slate-500">${std.student_no || 'Pending'}</td>
                    <td class="py-3 px-4 text-center font-bold text-slate-700">${std.grade !== null ? std.grade : '-'}</td>
                    <td class="py-3 px-4 text-center">${remarksBadge}</td>
                    <td class="py-3 px-4 text-center">${syncBadge}</td>
                `;
                rosterTableBody.appendChild(tr);
            });
        };

        // Pagination Buttons
        if (rosterPrevBtn) {
            rosterPrevBtn.addEventListener('click', () => {
                if (currentPage > 1) {
                    currentPage--;
                    updateRosterPagination();
                }
            });
        }
        if (rosterNextBtn) {
            rosterNextBtn.addEventListener('click', () => {
                const totalPages = Math.ceil(filteredRoster.length / itemsPerPage);
                if (currentPage < totalPages) {
                    currentPage++;
                    updateRosterPagination();
                }
            });
        }

        // Search filtering logic
        if (rosterSearch) {
            rosterSearch.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase().trim();
                currentPage = 1;
                if (!query) {
                    filteredRoster = currentRoster;
                } else {
                    filteredRoster = currentRoster.filter(std => 
                        std.name.toLowerCase().includes(query) || 
                        (std.student_no && std.student_no.toLowerCase().includes(query))
                    );
                }
                updateRosterPagination();
            });
        }

        // Helper function to trigger Excel export using SheetJS
        const triggerExcelExport = (studentsToExport, exportTitle = 'Exported') => {
            if (!studentsToExport || studentsToExport.length === 0) {
                if (window.showAlertModal) {
                    window.showAlertModal('No student records selected or available for export.', 'Export Notice', 'warning');
                } else if (window.showToast) {
                    window.showToast('No student records selected or available for export.', 'warning', 'Export Unavailable');
                }
                return;
            }

            if (window.showProgressModal) {
                window.showProgressModal('Exporting Roster Excel', `Formatting ${studentsToExport.length} student grade records...`, 35);
            }

            setTimeout(() => {
                const sectionName = resultsSectionName ? resultsSectionName.textContent.replace(' Grades', '').trim() : 'Imported';
                const component = resultsComponentTag ? resultsComponentTag.textContent.trim() : 'CWTS';
                
                const wb = window.XLSX.utils.book_new();
                const rows = [ CHED_EXCEL_HEADERS ];
                
                studentsToExport.forEach((std) => {
                    rows.push(formatChedExcelRow(std, sectionName, component));
                });
                
                const ws = window.XLSX.utils.aoa_to_sheet(rows);
                ws['!cols'] = getChedColsWidths();
                
                const safeSheetName = sectionName.replace(/[:\\/?*\[\]]/g, '').slice(0, 30) || 'Section';
                window.XLSX.utils.book_append_sheet(wb, ws, safeSheetName);
                
                const fileName = `CHED_NSTP_Grades_${sectionName}_${exportTitle}_${Date.now().toString().slice(-6)}.xlsx`;
                window.XLSX.writeFile(wb, fileName);
                
                if (window.finishProgressModal) {
                    window.finishProgressModal('Export Completed', `${studentsToExport.length} student record(s) exported to Excel successfully.`, 'success');
                } else if (window.showToast) {
                    window.showToast(`${studentsToExport.length} student record(s) exported successfully.`, 'success', 'Export Completed');
                }
            }, 600);
        };

        // Export All
        if (exportResultsBtn) {
            exportResultsBtn.addEventListener('click', () => {
                triggerExcelExport(currentRoster, 'All');
            });
        }

        // Export Selected
        if (exportSelectedBtn) {
            exportSelectedBtn.addEventListener('click', () => {
                const checkedBoxes = document.querySelectorAll('.roster-chk:checked');
                if (!checkedBoxes.length) {
                    alert('Please select at least one student checkbox in the table to export.');
                    return;
                }
                const selectedStudents = [];
                checkedBoxes.forEach(chk => {
                    const idx = parseInt(chk.dataset.idx);
                    if (filteredRoster[idx]) {
                        selectedStudents.push(filteredRoster[idx]);
                    }
                });
                triggerExcelExport(selectedStudents, 'Selected');
            });
        }

        // Close results and return to upload state
        if (closeResultsBtn) {
            closeResultsBtn.addEventListener('click', () => {
                resultsState.classList.add('hidden');
                uploadState.classList.remove('hidden');
                if (ocrFileInput) ocrFileInput.value = '';
            });
        }
    });
</script>
@endpush
