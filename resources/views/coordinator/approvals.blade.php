@extends('layouts.coordinator')

@section('title', 'Approvals - Coordinator Dashboard')

@section('content')


<x-page-header title="Approvals & Documents" subtitle="Review and manage activity plans and accomplishment reports">
</x-page-header>

<div class="mt-6 flex border-b border-slate-200">
    <button id="tab-activities" class="px-6 py-3 font-semibold text-sm border-b-2 border-indigo-600 text-indigo-700 focus:outline-none cursor-pointer">Activity Plans</button>
    <button id="tab-reports" class="px-6 py-3 font-medium text-sm text-slate-500 hover:text-slate-700 transition focus:outline-none cursor-pointer">Accomplishment Reports</button>
</div>

<div class="mt-6">
    <!-- ── Activity Plans Panels ── -->
    <div id="activities-panel" class="space-y-6">
        <!-- Sub-tabs for Activity Plans -->
        <div class="flex flex-wrap gap-2">
            <button id="subtab-act-pending" class="px-4 py-2 text-xs font-semibold rounded-xl bg-indigo-600 text-white shadow-sm cursor-pointer transition-all">
                Pending ({{ $pendingActivities->count() }})
            </button>
            <button id="subtab-act-approved" class="px-4 py-2 text-xs font-medium rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all cursor-pointer">
                Approved ({{ $approvedActivities->count() }})
            </button>
            <button id="subtab-act-rejected" class="px-4 py-2 text-xs font-medium rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all cursor-pointer">
                Revisions & Rejected ({{ $rejectedActivities->count() }})
            </button>
        </div>

        <!-- Pending Activity Plans Card -->
        <div id="card-act-pending">
            <x-card title="Pending Activity Plans">
                <x-table>
                    <x-slot name="header">
                        <th class="py-2 px-3 font-medium">Activity Title</th>
                        <th class="py-2 px-3 font-medium">Instructor</th>
                        <th class="py-2 px-3 font-medium">Program / Scope</th>
                        <th class="py-2 px-3 font-medium">Date</th>
                        <th class="py-2 px-3 font-medium">Status</th>
                        <th class="py-2 px-3 font-medium text-right">Actions</th>
                    </x-slot>

                    @forelse($pendingActivities as $a)
                    <tr class="border-b border-slate-50 hover:bg-indigo-50/40 cursor-pointer transition" 
                        onclick="openPlanModal(this)"
                        data-id="{{ $a->id }}"
                        data-title="{{ $a->title }}"
                        data-instructor="{{ $a->instructor }}"
                        data-scope="{{ $a->scope }}"
                        data-date="{{ $a->date }}"
                        data-status="{{ $a->status }}"
                        data-location="{{ $a->location }}"
                        data-objectives="{{ $a->objectives }}"
                        data-description="{{ $a->description }}"
                        data-submitted="{{ $a->submitted_date }}">
                        <td class="py-3 px-3 text-slate-900 font-medium">{{ $a->title }}</td>
                        <td class="py-3 px-3 text-slate-700">{{ $a->instructor }}</td>
                        <td class="py-3 px-3 text-slate-600">
                            <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">{{ $a->scope }}</span>
                        </td>
                        <td class="py-3 px-3 text-slate-600">{{ $a->date }}</td>
                        <td class="py-3 px-3">
                            <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">
                                <x-icon name="clock" class="w-3 h-3" /> {{ $a->status }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-right" onclick="event.stopPropagation();">
                            <button onclick="openPlanModal(this.parentElement.parentElement)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition cursor-pointer">
                                Review
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-8 text-center text-slate-400 text-sm">No pending approvals.</td></tr>
                    @endforelse
                </x-table>
            </x-card>
        </div>

        <!-- Approved Activity Plans Card -->
        <div id="card-act-approved" class="hidden">
            <x-card title="Approved Activity Plans">
                <x-table>
                    <x-slot name="header">
                        <th class="py-2 px-3 font-medium">Activity Title</th>
                        <th class="py-2 px-3 font-medium">Instructor</th>
                        <th class="py-2 px-3 font-medium">Program / Scope</th>
                        <th class="py-2 px-3 font-medium">Date Scheduled</th>
                        <th class="py-2 px-3 font-medium">Status</th>
                        <th class="py-2 px-3 font-medium text-right">Actions</th>
                    </x-slot>

                    @forelse($approvedActivities as $a)
                    <tr class="border-b border-slate-50 hover:bg-indigo-50/40 cursor-pointer transition" 
                        onclick="openPlanModal(this)"
                        data-id="{{ $a->id }}"
                        data-title="{{ $a->title }}"
                        data-instructor="{{ $a->instructor }}"
                        data-scope="{{ $a->scope }}"
                        data-date="{{ $a->date }}"
                        data-status="{{ $a->status }}"
                        data-location="{{ $a->location }}"
                        data-objectives="{{ $a->objectives }}"
                        data-description="{{ $a->description }}"
                        data-submitted="{{ $a->submitted_date }}">
                        <td class="py-3 px-3 text-slate-900 font-medium">{{ $a->title }}</td>
                        <td class="py-3 px-3 text-slate-700">{{ $a->instructor }}</td>
                        <td class="py-3 px-3 text-slate-600">
                            <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">{{ $a->scope }}</span>
                        </td>
                        <td class="py-3 px-3 text-slate-600">{{ $a->date }}</td>
                        <td class="py-3 px-3">
                            <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                                <x-icon name="check2" class="w-3 h-3" /> {{ $a->status }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-right" onclick="event.stopPropagation();">
                            <button onclick="openPlanModal(this.parentElement.parentElement)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition cursor-pointer">
                                View Details
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-8 text-center text-slate-400 text-sm">No approved activity plans.</td></tr>
                    @endforelse
                </x-table>
            </x-card>
        </div>

        <!-- Rejected & Revisions Activity Plans Card -->
        <div id="card-act-rejected" class="hidden">
            <x-card title="Rejected & Revisions Activity Plans">
                <x-table>
                    <x-slot name="header">
                        <th class="py-2 px-3 font-medium">Activity Title</th>
                        <th class="py-2 px-3 font-medium">Instructor</th>
                        <th class="py-2 px-3 font-medium">Program / Scope</th>
                        <th class="py-2 px-3 font-medium">Date</th>
                        <th class="py-2 px-3 font-medium">Status</th>
                        <th class="py-2 px-3 font-medium text-right">Actions</th>
                    </x-slot>

                    @forelse($rejectedActivities as $a)
                    <tr class="border-b border-slate-50 hover:bg-indigo-50/40 cursor-pointer transition" 
                        onclick="openPlanModal(this)"
                        data-id="{{ $a->id }}"
                        data-title="{{ $a->title }}"
                        data-instructor="{{ $a->instructor }}"
                        data-scope="{{ $a->scope }}"
                        data-date="{{ $a->date }}"
                        data-status="{{ $a->status }}"
                        data-location="{{ $a->location }}"
                        data-objectives="{{ $a->objectives }}"
                        data-description="{{ $a->description }}"
                        data-submitted="{{ $a->submitted_date }}">
                        <td class="py-3 px-3 text-slate-900 font-medium">{{ $a->title }}</td>
                        <td class="py-3 px-3 text-slate-700">{{ $a->instructor }}</td>
                        <td class="py-3 px-3 text-slate-600">
                            <span class="text-xs px-2 py-0.5 rounded-full bg-slate-50 text-slate-700 border border-slate-200">{{ $a->scope }}</span>
                        </td>
                        <td class="py-3 px-3 text-slate-600">{{ $a->date }}</td>
                        <td class="py-3 px-3">
                            <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full {{ $a->status === 'Rejected' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700' }}">
                                <x-icon name="{{ $a->status === 'Rejected' ? 'trash' : 'clock' }}" class="w-3 h-3" /> {{ $a->status }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-right" onclick="event.stopPropagation();">
                            <button onclick="openPlanModal(this.parentElement.parentElement)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition cursor-pointer">
                                View Details
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-8 text-center text-slate-400 text-sm">No rejected or revision plans.</td></tr>
                    @endforelse
                </x-table>
            </x-card>
        </div>
    </div>

    <!-- ── Accomplishment Reports Panels ── -->
    <div id="reports-panel" class="space-y-6 hidden">
        <!-- Sub-tabs for Accomplishment Reports -->
        <div class="flex flex-wrap gap-2">
            <button id="subtab-rep-pending" class="px-4 py-2 text-xs font-semibold rounded-xl bg-indigo-600 text-white shadow-sm cursor-pointer transition-all">
                Pending ({{ $pendingReports->count() }})
            </button>
            <button id="subtab-rep-approved" class="px-4 py-2 text-xs font-medium rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all cursor-pointer">
                Approved ({{ $approvedReports->count() }})
            </button>
            <button id="subtab-rep-rejected" class="px-4 py-2 text-xs font-medium rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all cursor-pointer">
                Revisions & Rejected ({{ $rejectedReports->count() }})
            </button>
        </div>

        <!-- Pending Accomplishment Reports Card -->
        <div id="card-rep-pending">
            <x-card title="Pending Accomplishment Reports">
                <x-table>
                    <x-slot name="header">
                        <th class="py-2 px-3 font-medium">Report Title</th>
                        <th class="py-2 px-3 font-medium">Instructor</th>
                        <th class="py-2 px-3 font-medium">Program / Scope</th>
                        <th class="py-2 px-3 font-medium">Date Completed</th>
                        <th class="py-2 px-3 font-medium">Status</th>
                        <th class="py-2 px-3 font-medium text-right">Actions</th>
                    </x-slot>

                    @forelse($pendingReports as $r)
                    <tr class="border-b border-slate-50 hover:bg-indigo-50/40 cursor-pointer transition" 
                        onclick="openReportModal(this)"
                        data-id="{{ $r->id }}"
                        data-title="{{ $r->title }}"
                        data-instructor="{{ $r->instructor }}"
                        data-scope="{{ $r->scope }}"
                        data-date="{{ $r->date }}"
                        data-status="{{ $r->status }}"
                        data-location="{{ $r->location }}"
                        data-participants="{{ $r->participants_count }}"
                        data-accomplishments="{{ $r->accomplishments }}"
                        data-filepath="{{ $r->report_file_path }}"
                        data-submitted="{{ $r->submitted_date }}">
                        <td class="py-3 px-3 text-slate-900 font-medium">{{ $r->title }}</td>
                        <td class="py-3 px-3 text-slate-700">{{ $r->instructor }}</td>
                        <td class="py-3 px-3 text-slate-600">
                            <span class="text-xs px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-100">{{ $r->scope }}</span>
                        </td>
                        <td class="py-3 px-3 text-slate-600">{{ $r->date }}</td>
                        <td class="py-3 px-3">
                            <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-amber-50 text-amber-700">
                                <x-icon name="clock" class="w-3 h-3" /> {{ $r->status }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-right" onclick="event.stopPropagation();">
                            <button onclick="openReportModal(this.parentElement.parentElement)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-lg bg-indigo-50 text-indigo-700 hover:bg-indigo-100 transition cursor-pointer">
                                Review
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-8 text-center text-slate-400 text-sm">No pending accomplishment reports.</td></tr>
                    @endforelse
                </x-table>
            </x-card>
        </div>

        <!-- Approved (Reviewed) Accomplishment Reports Card -->
        <div id="card-rep-approved" class="hidden">
            <x-card title="Approved Accomplishment Reports">
                <x-table>
                    <x-slot name="header">
                        <th class="py-2 px-3 font-medium">Report Title</th>
                        <th class="py-2 px-3 font-medium">Instructor</th>
                        <th class="py-2 px-3 font-medium">Program / Scope</th>
                        <th class="py-2 px-3 font-medium">Date Completed</th>
                        <th class="py-2 px-3 font-medium">Status</th>
                        <th class="py-2 px-3 font-medium text-right">Actions</th>
                    </x-slot>

                    @forelse($approvedReports as $r)
                    <tr class="border-b border-slate-50 hover:bg-indigo-50/40 cursor-pointer transition" 
                        onclick="openReportModal(this)"
                        data-id="{{ $r->id }}"
                        data-title="{{ $r->title }}"
                        data-instructor="{{ $r->instructor }}"
                        data-scope="{{ $r->scope }}"
                        data-date="{{ $r->date }}"
                        data-status="{{ $r->status }}"
                        data-location="{{ $r->location }}"
                        data-participants="{{ $r->participants_count }}"
                        data-accomplishments="{{ $r->accomplishments }}"
                        data-filepath="{{ $r->report_file_path }}"
                        data-submitted="{{ $r->submitted_date }}">
                        <td class="py-3 px-3 text-slate-900 font-medium">{{ $r->title }}</td>
                        <td class="py-3 px-3 text-slate-700">{{ $r->instructor }}</td>
                        <td class="py-3 px-3 text-slate-600">
                            <span class="text-xs px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">{{ $r->scope }}</span>
                        </td>
                        <td class="py-3 px-3 text-slate-600">{{ $r->date }}</td>
                        <td class="py-3 px-3">
                            <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full bg-emerald-50 text-emerald-700 border border-emerald-100">
                                <x-icon name="check2" class="w-3 h-3" /> Reviewed
                            </span>
                        </td>
                        <td class="py-3 px-3 text-right" onclick="event.stopPropagation();">
                            <button onclick="openReportModal(this.parentElement.parentElement)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition cursor-pointer">
                                View Details
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-8 text-center text-slate-400 text-sm">No approved accomplishment reports.</td></tr>
                    @endforelse
                </x-table>
            </x-card>
        </div>

        <!-- Rejected & Revisions Accomplishment Reports Card -->
        <div id="card-rep-rejected" class="hidden">
            <x-card title="Rejected & Revisions Accomplishment Reports">
                <x-table>
                    <x-slot name="header">
                        <th class="py-2 px-3 font-medium">Report Title</th>
                        <th class="py-2 px-3 font-medium">Instructor</th>
                        <th class="py-2 px-3 font-medium">Program / Scope</th>
                        <th class="py-2 px-3 font-medium">Date Completed</th>
                        <th class="py-2 px-3 font-medium">Status</th>
                        <th class="py-2 px-3 font-medium text-right">Actions</th>
                    </x-slot>

                    @forelse($rejectedReports as $r)
                    <tr class="border-b border-slate-50 hover:bg-indigo-50/40 cursor-pointer transition" 
                        onclick="openReportModal(this)"
                        data-id="{{ $r->id }}"
                        data-title="{{ $r->title }}"
                        data-instructor="{{ $r->instructor }}"
                        data-scope="{{ $r->scope }}"
                        data-date="{{ $r->date }}"
                        data-status="{{ $r->status }}"
                        data-location="{{ $r->location }}"
                        data-participants="{{ $r->participants_count }}"
                        data-accomplishments="{{ $r->accomplishments }}"
                        data-filepath="{{ $r->report_file_path }}"
                        data-submitted="{{ $r->submitted_date }}">
                        <td class="py-3 px-3 text-slate-900 font-medium">{{ $r->title }}</td>
                        <td class="py-3 px-3 text-slate-700">{{ $r->instructor }}</td>
                        <td class="py-3 px-3 text-slate-600">
                            <span class="text-xs px-2 py-0.5 rounded-full bg-slate-50 text-slate-700 border border-slate-200">{{ $r->scope }}</span>
                        </td>
                        <td class="py-3 px-3 text-slate-600">{{ $r->date }}</td>
                        <td class="py-3 px-3">
                            <span class="inline-flex items-center gap-1 text-xs px-2 py-0.5 rounded-full {{ $r->status === 'Rejected' ? 'bg-rose-50 text-rose-700' : 'bg-amber-50 text-amber-700' }}">
                                <x-icon name="{{ $r->status === 'Rejected' ? 'trash' : 'clock' }}" class="w-3 h-3" /> {{ $r->status }}
                            </span>
                        </td>
                        <td class="py-3 px-3 text-right" onclick="event.stopPropagation();">
                            <button onclick="openReportModal(this.parentElement.parentElement)" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs rounded-lg bg-slate-100 text-slate-700 hover:bg-slate-200 transition cursor-pointer">
                                View Details
                            </button>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="py-8 text-center text-slate-400 text-sm">No rejected or revision accomplishment reports.</td></tr>
                    @endforelse
                </x-table>
            </x-card>
        </div>
    </div>
</div>

<!-- Review Activity Plan Modal -->
<div id="review-plan-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all max-w-2xl w-full border border-slate-100">
        <!-- Modal Header -->
        <div class="bg-indigo-900 px-6 py-4 flex items-center justify-between text-white">
            <div>
                <h3 class="text-lg font-bold tracking-tight" id="plan-modal-title">Review Activity Plan</h3>
                <p class="text-xs text-indigo-200 mt-0.5" id="plan-modal-instructor-section">Submitted by Instructor Name for Section</p>
            </div>
            <button type="button" onclick="closePlanModal()" class="text-indigo-200 hover:text-white transition focus:outline-none cursor-pointer">
                <x-icon name="close" class="w-5 h-5" />
            </button>
        </div>

        <!-- Modal Content -->
        <div class="px-6 py-5 space-y-4 max-h-[60vh] overflow-y-auto">


            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Scheduled Date</span>
                    <span class="text-sm font-medium text-slate-855" id="plan-modal-date">Oct 12, 2026</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Location</span>
                    <span class="text-sm font-medium text-slate-855" id="plan-modal-location">Panabo Coastal Area</span>
                </div>
            </div>

            <div>
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Objectives</span>
                <div class="mt-1.5 p-3.5 bg-slate-50 rounded-xl border border-slate-100 text-sm text-slate-700 whitespace-pre-line" id="plan-modal-objectives">
                    No objectives defined.
                </div>
            </div>

            <div id="plan-modal-file-section" class="hidden">
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Attached File (Activity Design)</span>
                <div class="mt-1.5 flex items-center justify-between p-3.5 bg-indigo-50/50 rounded-xl border border-indigo-100">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-indigo-100 text-indigo-700 rounded-lg shrink-0">
                            <x-icon name="filecheck" class="w-5 h-5" />
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-slate-800" id="plan-modal-filename">activity_design.pdf</span>
                            <span class="block text-xs text-slate-400">Activity Design File</span>
                        </div>
                    </div>
                    <a href="#" id="plan-modal-download" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-indigo-700 hover:text-indigo-850 bg-indigo-100/50 hover:bg-indigo-100 rounded-lg transition">
                        <x-icon name="download" class="w-3.5 h-3.5" /> View / Download
                    </a>
                </div>
            </div>

            <form id="plan-action-form" method="POST" action="" class="space-y-4 pt-2">
                @csrf
                @method('PATCH')
                <div>
                    <label for="plan-feedback" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Feedback / Remarks (Optional)</label>
                    <textarea id="plan-feedback" name="feedback" rows="3" class="mt-1.5 block w-full rounded-xl border-slate-200 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-indigo-200" placeholder="Add any comments, corrections, or notes for the instructor..."></textarea>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <button type="button" id="plan-delete-btn-pending" class="hidden px-4 py-2 text-sm font-medium text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-100 rounded-xl transition cursor-pointer flex items-center gap-1.5">
                        <x-icon name="trash" class="w-4 h-4" /> Delete Plan
                    </button>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="closePlanModal()" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" id="plan-reject-btn" class="px-4 py-2 text-sm font-medium text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl border border-rose-100 transition cursor-pointer">
                            Reject
                        </button>
                        <button type="submit" id="plan-revision-btn" class="px-4 py-2 text-sm font-medium text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-xl border border-amber-100 transition cursor-pointer">
                            Request Revisions
                        </button>
                        <button type="submit" id="plan-approve-btn" class="px-5 py-2 text-sm font-semibold text-white bg-indigo-600 hover:bg-indigo-700 rounded-xl shadow-md shadow-indigo-200 transition cursor-pointer">
                            Approve Plan
                        </button>
                    </div>
                </div>
            </form>

            <!-- Non-Pending Footer -->
            <div id="plan-non-pending-footer" class="flex items-center justify-between pt-3 border-t border-slate-100 hidden">
                <button type="button" id="plan-delete-btn-non-pending" class="px-4 py-2 text-sm font-medium text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-100 rounded-xl transition cursor-pointer flex items-center gap-1.5">
                    <x-icon name="trash" class="w-4 h-4" /> Delete Plan
                </button>
                <button type="button" onclick="closePlanModal()" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition cursor-pointer">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Review Accomplishment Report Modal -->
<div id="review-report-modal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all max-w-2xl w-full border border-slate-100">
        <!-- Modal Header -->
        <div class="bg-emerald-900 px-6 py-4 flex items-center justify-between text-white">
            <div>
                <h3 class="text-lg font-bold tracking-tight" id="report-modal-title">Review Accomplishment Report</h3>
                <p class="text-xs text-emerald-200 mt-0.5" id="report-modal-instructor-section">Submitted by Instructor Name for Section</p>
            </div>
            <button type="button" onclick="closeReportModal()" class="text-emerald-200 hover:text-white transition focus:outline-none cursor-pointer">
                <x-icon name="close" class="w-5 h-5" />
            </button>
        </div>

        <!-- Modal Content -->
        <div class="px-6 py-5 space-y-4 max-h-[60vh] overflow-y-auto">


            <div class="grid grid-cols-2 gap-4">
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Date Completed</span>
                    <span class="text-sm font-medium text-slate-855" id="report-modal-date">Oct 12, 2026</span>
                </div>
                <div>
                    <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Location</span>
                    <span class="text-sm font-medium text-slate-855" id="report-modal-location">Panabo Coastal Area</span>
                </div>
            </div>

            <div>
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Accomplishments Summary</span>
                <div class="mt-1.5 p-3.5 bg-slate-50 rounded-xl border border-slate-100 text-sm text-slate-700 whitespace-pre-line" id="report-modal-accomplishments">
                    No accomplishments defined.
                </div>
            </div>

            <div id="report-modal-file-section" class="hidden">
                <span class="block text-xs font-semibold text-slate-400 uppercase tracking-wider">Attached File (Report Document)</span>
                <div class="mt-1.5 flex items-center justify-between p-3.5 bg-emerald-50/50 rounded-xl border border-emerald-100">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-emerald-100 text-emerald-700 rounded-lg shrink-0">
                            <x-icon name="filecheck" class="w-5 h-5" />
                        </div>
                        <div>
                            <span class="block text-sm font-medium text-slate-800" id="report-modal-filename">report_file.pdf</span>
                            <span class="block text-xs text-slate-400">Accomplishment Report Document</span>
                        </div>
                    </div>
                    <a href="#" id="report-modal-download" target="_blank" class="inline-flex items-center gap-1.5 px-3 py-1.5 text-xs font-medium text-emerald-700 hover:text-emerald-855 bg-emerald-100/50 hover:bg-emerald-100 rounded-lg transition">
                        <x-icon name="download" class="w-3.5 h-3.5" /> View / Download
                    </a>
                </div>
            </div>

            <form id="report-action-form" method="POST" action="" class="space-y-4 pt-2">
                @csrf
                @method('PATCH')
                <div>
                    <label for="report-feedback" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Feedback / Remarks (Optional)</label>
                    <textarea id="report-feedback" name="feedback" rows="3" class="mt-1.5 block w-full rounded-xl border-slate-200 shadow-sm focus:border-emerald-500 focus:ring-emerald-500 text-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-emerald-200" placeholder="Add any comments, corrections, or needed revisions..."></textarea>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-between pt-3 border-t border-slate-100">
                    <button type="button" id="report-delete-btn-pending" class="hidden px-4 py-2 text-sm font-medium text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-100 rounded-xl transition cursor-pointer flex items-center gap-1.5">
                        <x-icon name="trash" class="w-4 h-4" /> Delete Report
                    </button>
                    <div class="flex items-center gap-3">
                        <button type="button" onclick="closeReportModal()" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition cursor-pointer">
                            Cancel
                        </button>
                        <button type="submit" id="report-reject-btn" class="px-4 py-2 text-sm font-medium text-rose-600 bg-rose-50 hover:bg-rose-100 rounded-xl border border-rose-100 transition cursor-pointer">
                            Reject
                        </button>
                        <button type="submit" id="report-revision-btn" class="px-4 py-2 text-sm font-medium text-amber-600 bg-amber-50 hover:bg-amber-100 rounded-xl border border-amber-100 transition cursor-pointer">
                            Request Revisions
                        </button>
                        <button type="submit" id="report-approve-btn" class="px-5 py-2 text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 rounded-xl shadow-md shadow-emerald-200 transition cursor-pointer">
                            Approve Report
                        </button>
                    </div>
                </div>
            </form>

            <!-- Non-Pending Footer -->
            <div id="report-non-pending-footer" class="flex items-center justify-between pt-3 border-t border-slate-100 hidden">
                <button type="button" id="report-delete-btn-non-pending" class="px-4 py-2 text-sm font-medium text-rose-600 bg-rose-50 hover:bg-rose-100 border border-rose-100 rounded-xl transition cursor-pointer flex items-center gap-1.5">
                    <x-icon name="trash" class="w-4 h-4" /> Delete Report
                </button>
                <button type="button" onclick="closeReportModal()" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition cursor-pointer">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Plan Revision Note Modal -->
<div id="plan-revision-note-modal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all max-w-md w-full border border-slate-100">
        <div class="bg-amber-900 px-6 py-4 flex items-center justify-between text-white">
            <div>
                <h3 class="text-base font-bold tracking-tight">Specify Revision Details</h3>
                <p class="text-xs text-amber-200 mt-0.5" id="revision-note-modal-subtitle">Activity Plan</p>
            </div>
            <button type="button" onclick="closePlanRevisionNoteModal()" class="text-amber-200 hover:text-white transition focus:outline-none cursor-pointer">
                <x-icon name="close" class="w-5 h-5" />
            </button>
        </div>
        <form id="plan-revision-note-form" method="POST" action="" class="p-6 space-y-4 text-sm">
            @csrf
            @method('PATCH')
            <div>
                <label for="revision-feedback-text" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Required Changes / Revision Notes</label>
                <textarea id="revision-feedback-text" name="feedback" required rows="4" class="mt-1.5 block w-full rounded-xl border-slate-200 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-200" placeholder="Please specify exactly what the instructor needs to revise (e.g., change date, update safety objectives)..."></textarea>
            </div>
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closePlanRevisionNoteModal()" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition cursor-pointer">
                    Back
                </button>
                <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-md transition cursor-pointer">
                    Submit Revision Request
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Report Revision Note Modal -->
<div id="report-revision-note-modal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/60 backdrop-blur-sm hidden p-4">
    <div class="bg-white rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all max-w-md w-full border border-slate-100">
        <div class="bg-amber-900 px-6 py-4 flex items-center justify-between text-white">
            <div>
                <h3 class="text-base font-bold tracking-tight">Specify Revision Details</h3>
                <p class="text-xs text-amber-200 mt-0.5" id="report-revision-note-modal-subtitle">Accomplishment Report</p>
            </div>
            <button type="button" onclick="closeReportRevisionNoteModal()" class="text-amber-200 hover:text-white transition focus:outline-none cursor-pointer">
                <x-icon name="close" class="w-5 h-5" />
            </button>
        </div>
        <form id="report-revision-note-form" method="POST" action="" class="p-6 space-y-4 text-sm">
            @csrf
            @method('PATCH')
            <div>
                <label for="report-revision-feedback-text" class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Required Changes / Revision Notes</label>
                <textarea id="report-revision-feedback-text" name="feedback" required rows="4" class="mt-1.5 block w-full rounded-xl border-slate-200 shadow-sm focus:border-amber-500 focus:ring-amber-500 text-sm placeholder:text-slate-400 focus:outline-none focus:ring-2 focus:ring-amber-200" placeholder="Please specify exactly what the instructor needs to revise in the report..."></textarea>
            </div>
            <div class="flex items-center justify-end gap-3 pt-3 border-t border-slate-100">
                <button type="button" onclick="closeReportRevisionNoteModal()" class="px-4 py-2 text-sm font-medium text-slate-600 bg-slate-100 hover:bg-slate-200 rounded-xl transition cursor-pointer">
                    Back
                </button>
                <button type="submit" class="px-5 py-2 text-sm font-semibold text-white bg-amber-600 hover:bg-amber-700 rounded-xl shadow-md transition cursor-pointer">
                    Submit Revision Request
                </button>
            </div>
        </form>
    </div>
</div>

<form id="global-plan-delete-form" method="POST" action="" class="hidden">
    @csrf
    @method('DELETE')
</form>

<form id="global-report-delete-form" method="POST" action="" class="hidden">
    @csrf
    @method('DELETE')
</form>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const tabAct = document.getElementById('tab-activities');
        const tabRep = document.getElementById('tab-reports');
        const panelAct = document.getElementById('activities-panel');
        const panelRep = document.getElementById('reports-panel');

        // Main Tab Switches
        tabAct.addEventListener('click', function() {
            tabAct.className = "px-6 py-3 font-semibold text-sm border-b-2 border-indigo-600 text-indigo-700 focus:outline-none cursor-pointer";
            tabRep.className = "px-6 py-3 font-medium text-sm text-slate-500 hover:text-slate-700 transition focus:outline-none cursor-pointer";
            panelAct.classList.remove('hidden');
            panelRep.classList.add('hidden');
        });

        tabRep.addEventListener('click', function() {
            tabRep.className = "px-6 py-3 font-semibold text-sm border-b-2 border-indigo-600 text-indigo-700 focus:outline-none cursor-pointer";
            tabAct.className = "px-6 py-3 font-medium text-sm text-slate-500 hover:text-slate-700 transition focus:outline-none cursor-pointer";
            panelRep.classList.remove('hidden');
            panelAct.classList.add('hidden');
        });

        // Sub-tabs configuration function
        function setupSubTabs(subtabs, cards) {
            subtabs.forEach((tab, index) => {
                if(tab) {
                    tab.addEventListener('click', () => {
                        // Update active tab styles
                        subtabs.forEach((t, i) => {
                            if (i === index) {
                                t.className = "px-4 py-2 text-xs font-semibold rounded-xl bg-indigo-600 text-white shadow-sm cursor-pointer transition-all";
                            } else {
                                t.className = "px-4 py-2 text-xs font-medium rounded-xl bg-white border border-slate-200 text-slate-600 hover:bg-slate-50 transition-all cursor-pointer";
                            }
                        });
                        // Show/hide cards
                        cards.forEach((card, i) => {
                            if (i === index) {
                                card.classList.remove('hidden');
                            } else {
                                card.classList.add('hidden');
                            }
                        });
                    });
                }
            });
        }

        // Setup Activity Plan sub-tabs
        const actSubTabs = [
            document.getElementById('subtab-act-pending'),
            document.getElementById('subtab-act-approved'),
            document.getElementById('subtab-act-rejected')
        ];
        const actCards = [
            document.getElementById('card-act-pending'),
            document.getElementById('card-act-approved'),
            document.getElementById('card-act-rejected')
        ];
        setupSubTabs(actSubTabs, actCards);

        // Setup Accomplishment Report sub-tabs
        const repSubTabs = [
            document.getElementById('subtab-rep-pending'),
            document.getElementById('subtab-rep-approved'),
            document.getElementById('subtab-rep-rejected')
        ];
        const repCards = [
            document.getElementById('card-rep-pending'),
            document.getElementById('card-rep-approved'),
            document.getElementById('card-rep-rejected')
        ];
        setupSubTabs(repSubTabs, repCards);
    });

    function openPlanModal(button) {
        const id = button.getAttribute('data-id');
        const title = button.getAttribute('data-title');
        const instructor = button.getAttribute('data-instructor');
        const scope = button.getAttribute('data-scope');
        const date = button.getAttribute('data-date');
        const location = button.getAttribute('data-location');
        const objectives = button.getAttribute('data-objectives');
        const description = button.getAttribute('data-description');
        const submitted = button.getAttribute('data-submitted');
        const status = button.getAttribute('data-status');

        document.getElementById('plan-modal-title').textContent = 'Review Activity Plan: ' + title;
        document.getElementById('plan-modal-instructor-section').textContent = 'Submitted by ' + instructor + ' (' + scope + ') • Submitted on ' + submitted;
        document.getElementById('plan-modal-date').textContent = date;
        document.getElementById('plan-modal-location').textContent = location;
        document.getElementById('plan-modal-objectives').textContent = objectives || 'No objectives defined.';

        const fileSection = document.getElementById('plan-modal-file-section');
        if (description && description.trim() !== '') {
            fileSection.classList.remove('hidden');
            document.getElementById('plan-modal-filename').textContent = description.split('/').pop();
            document.getElementById('plan-modal-download').href = '/' + description;
        } else {
            fileSection.classList.add('hidden');
        }

        // Bind Delete Handlers
        const deleteUrl = '/coordinator/approvals/plans/' + id;
        
        const executePlanDelete = function() {
            window.showConfirmModal({
                title: 'Delete Activity Plan',
                message: 'Are you sure you want to delete this activity plan? This action cannot be undone.',
                confirmText: 'Delete Plan',
                cancelText: 'Cancel',
                isDanger: true,
                onConfirm: () => {
                    if (window.showProgressModal) window.showProgressModal('Deleting Activity Plan', 'Removing plan submission from database...', 40);
                    const delForm = document.getElementById('global-plan-delete-form');
                    delForm.action = deleteUrl;
                    delForm.submit();
                }
            });
        };

        document.getElementById('plan-delete-btn-pending').onclick = function(e) {
            e.preventDefault();
            executePlanDelete();
        };

        document.getElementById('plan-delete-btn-non-pending').onclick = function(e) {
            e.preventDefault();
            executePlanDelete();
        };

        // Action Form Configuration based on status
        const form = document.getElementById('plan-action-form');
        const banner = document.getElementById('plan-status-banner');
        document.getElementById('plan-feedback').value = '';

        if (status === 'Pending') {
            form.classList.remove('hidden');
            if (banner) banner.classList.add('hidden');
            document.getElementById('plan-non-pending-footer').classList.add('hidden');

            document.getElementById('plan-approve-btn').onclick = function(e) {
                e.preventDefault();
                if (window.showProgressModal) window.showProgressModal('Approving Activity Plan', 'Saving approval status and adding to calendar...', 40);
                form.action = '/coordinator/approvals/plans/' + id + '/approve';
                form.submit();
            };

            document.getElementById('plan-reject-btn').onclick = function(e) {
                e.preventDefault();
                if (window.showProgressModal) window.showProgressModal('Rejecting Activity Plan', 'Updating activity plan status to rejected...', 40);
                form.action = '/coordinator/approvals/plans/' + id + '/reject';
                form.submit();
            };

            document.getElementById('plan-revision-btn').onclick = function(e) {
                e.preventDefault();
                document.getElementById('review-plan-modal').classList.add('hidden');
                const noteForm = document.getElementById('plan-revision-note-form');
                noteForm.action = '/coordinator/approvals/plans/' + id + '/revision';
                document.getElementById('revision-note-modal-subtitle').textContent = title;
                document.getElementById('revision-feedback-text').value = document.getElementById('plan-feedback').value;
                document.getElementById('plan-revision-note-modal').classList.remove('hidden');
            };
        } else {
            form.classList.add('hidden');
            if (banner) banner.classList.add('hidden');
            document.getElementById('plan-non-pending-footer').classList.remove('hidden');
            
            if (status === 'Approved') {
                document.getElementById('plan-delete-btn-non-pending').classList.remove('hidden');
            } else {
                document.getElementById('plan-delete-btn-non-pending').classList.add('hidden');
            }
        }

        document.getElementById('review-plan-modal').classList.remove('hidden');
    }

    function closePlanModal() {
        document.getElementById('review-plan-modal').classList.add('hidden');
    }

    function openReportModal(button) {
        const id = button.getAttribute('data-id');
        const title = button.getAttribute('data-title');
        const instructor = button.getAttribute('data-instructor');
        const scope = button.getAttribute('data-scope');
        const date = button.getAttribute('data-date');
        const location = button.getAttribute('data-location');
        const participants = button.getAttribute('data-participants');
        const accomplishments = button.getAttribute('data-accomplishments');
        const filepath = button.getAttribute('data-filepath');
        const submitted = button.getAttribute('data-submitted');
        const status = button.getAttribute('data-status');

        document.getElementById('report-modal-title').textContent = 'Review Accomplishment Report: ' + title;
        document.getElementById('report-modal-instructor-section').textContent = 'Submitted by ' + instructor + ' (' + scope + ') • Submitted on ' + submitted;
        document.getElementById('report-modal-date').textContent = date;
        document.getElementById('report-modal-location').textContent = location;
        document.getElementById('report-modal-accomplishments').textContent = accomplishments || 'No accomplishments defined.';

        const fileSection = document.getElementById('report-modal-file-section');
        if (filepath && filepath.trim() !== '') {
            fileSection.classList.remove('hidden');
            document.getElementById('report-modal-filename').textContent = filepath.split('/').pop();
            document.getElementById('report-modal-download').href = '/' + filepath;
        } else {
            fileSection.classList.add('hidden');
        }

        // Bind Delete Handlers
        const deleteUrl = '/coordinator/approvals/reports/' + id;
        
        const executeReportDelete = function() {
            window.showConfirmModal({
                title: 'Delete Accomplishment Report',
                message: 'Are you sure you want to delete this accomplishment report? This action cannot be undone.',
                confirmText: 'Delete Report',
                cancelText: 'Cancel',
                isDanger: true,
                onConfirm: () => {
                    if (window.showProgressModal) window.showProgressModal('Deleting Accomplishment Report', 'Removing report submission...', 40);
                    const delForm = document.getElementById('global-report-delete-form');
                    delForm.action = deleteUrl;
                    delForm.submit();
                }
            });
        };

        document.getElementById('report-delete-btn-pending').onclick = function(e) {
            e.preventDefault();
            executeReportDelete();
        };

        document.getElementById('report-delete-btn-non-pending').onclick = function(e) {
            e.preventDefault();
            executeReportDelete();
        };

        // Action Form Configuration based on status
        const form = document.getElementById('report-action-form');
        const banner = document.getElementById('report-status-banner');
        document.getElementById('report-feedback').value = '';

        if (status === 'Pending') {
            form.classList.remove('hidden');
            if (banner) banner.classList.add('hidden');
            document.getElementById('report-non-pending-footer').classList.add('hidden');

            document.getElementById('report-approve-btn').onclick = function(e) {
                e.preventDefault();
                if (window.showProgressModal) window.showProgressModal('Approving Accomplishment Report', 'Accepting report submission and marking reviewed...', 40);
                form.action = '/coordinator/approvals/reports/' + id + '/approve';
                form.submit();
            };

            document.getElementById('report-reject-btn').onclick = function(e) {
                e.preventDefault();
                if (window.showProgressModal) window.showProgressModal('Rejecting Accomplishment Report', 'Updating report status to rejected...', 40);
                form.action = '/coordinator/approvals/reports/' + id + '/reject';
                form.submit();
            };

            document.getElementById('report-revision-btn').onclick = function(e) {
                e.preventDefault();
                document.getElementById('review-report-modal').classList.add('hidden');
                const noteForm = document.getElementById('report-revision-note-form');
                noteForm.action = '/coordinator/approvals/reports/' + id + '/revision';
                document.getElementById('report-revision-note-modal-subtitle').textContent = title;
                document.getElementById('report-revision-feedback-text').value = document.getElementById('report-feedback').value;
                document.getElementById('report-revision-note-modal').classList.remove('hidden');
            };
        } else {
            form.classList.add('hidden');
            if (banner) banner.classList.add('hidden');
            document.getElementById('report-non-pending-footer').classList.remove('hidden');
            
            if (status === 'Reviewed') {
                document.getElementById('report-delete-btn-non-pending').classList.remove('hidden');
            } else {
                document.getElementById('report-delete-btn-non-pending').classList.add('hidden');
            }
        }

        document.getElementById('review-report-modal').classList.remove('hidden');
    }

    function closeReportModal() {
        document.getElementById('review-report-modal').classList.add('hidden');
    }

    window.closePlanRevisionNoteModal = function() {
        document.getElementById('plan-revision-note-modal').classList.add('hidden');
        document.getElementById('review-plan-modal').classList.remove('hidden');
    }

    window.closeReportRevisionNoteModal = function() {
        document.getElementById('report-revision-note-modal').classList.add('hidden');
        document.getElementById('review-report-modal').classList.remove('hidden');
    }
</script>

@endsection
