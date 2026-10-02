@extends('layouts.coordinator')

@section('title', 'Coordinator Dashboard - DNSC NSTP Portal')

@section('content')
<div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-5" id="dashboard-stats-grid">
    @foreach($stats as $s)
    <div class="premium-card p-5" data-metric="{{ $s->key }}">
        <div class="flex items-start justify-between">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br {{ $s->color }} flex items-center justify-center text-white shadow">
                <x-icon name="{{ $s->ico }}" class="w-5 h-5" />
            </div>
            <span class="text-xs px-2 py-1 rounded-full {{ $s->up ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}" data-metric-delta>{{ $s->delta }}</span>
        </div>
        <div class="mt-4 text-slate-900 tracking-tight text-2xl" data-metric-value>{{ $s->value }}</div>
        <div class="text-sm text-slate-500 mt-0.5">{{ $s->label }}</div>
    </div>
    @endforeach
    <div class="premium-card p-5 cursor-pointer hover:border-rose-300 hover:shadow-md transition group" data-metric="incomplete_profiles" onclick="openIncompleteProfilesModal()">
        <div class="flex items-start justify-between">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-rose-500 to-red-600 flex items-center justify-center text-white shadow group-hover:scale-105 transition-transform">
                <x-icon name="alertc" class="w-5 h-5" />
            </div>
            <span class="text-xs px-2 py-1 rounded-full {{ $incompleteCount > 0 ? 'bg-rose-50 text-rose-600 font-bold' : 'bg-emerald-50 text-emerald-600' }}" data-metric-delta>{{ $incompleteCount > 0 ? 'Click to Review' : 'Complete' }}</span>
        </div>
        <div class="mt-4 text-slate-900 tracking-tight text-2xl" data-metric-value id="topMetricIncompleteCount">{{ $incompleteCount }}</div>
        <div class="text-sm text-slate-500 mt-0.5 flex items-center justify-between">
            <span>Incomplete Profiles</span>
            <span class="text-xs text-rose-600 font-semibold group-hover:underline">Review All &rarr;</span>
        </div>
    </div>
</div>

<div class="grid grid-cols-1 xl:grid-cols-5 gap-6 mt-6">
    <div class="xl:col-span-3 flex flex-col gap-5">
        <!-- Charts go here, can be hydrated via Alpine or JS -->
        <x-card class="h-80" title="Enrollment Trends" subtitle="Overview of student enrollment over time">
            <div class="w-full h-full flex items-center justify-center text-slate-400">
                (Chart Placeholder - Requires JS Library)
            </div>
        </x-card>
        <x-card class="h-80" title="Pass / Fail Ratio" subtitle="Success metrics across sections">
            <div class="w-full h-full flex items-center justify-center text-slate-400">
                (Chart Placeholder - Requires JS Library)
            </div>
        </x-card>
    </div>
    <div class="xl:col-span-2 flex flex-col gap-5">
        
        <!-- Calendar Mini Widget -->
        <x-card class="p-5">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <div class="text-slate-900 font-bold tracking-tight">Calendar of Activities</div>
                    <div class="text-xs text-slate-500 mt-0.5">{{ date('F Y') }}</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-indigo-50 text-indigo-500 flex items-center justify-center">
                    <x-icon name="calendar" class="w-4 h-4" />
                </div>
            </div>
            <div class="grid grid-cols-7 gap-1 mb-1">
                @foreach(['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr', 'Sa'] as $d)
                <div class="text-center text-[10px] font-medium text-slate-400 uppercase">{{ $d }}</div>
                @endforeach
            </div>
            <div class="grid grid-cols-7 gap-1">
                {{-- Mock calendar generation for visual --}}
                @for($i = 0; $i < date('w', strtotime(date('Y-m-01'))); $i++)
                    <div></div>
                @endfor
                @for($d = 1; $d <= date('t'); $d++)
                    @php $isToday = $d == date('j'); @endphp
                    <div class="w-7 h-7 flex items-center justify-center text-xs rounded-full {{ $isToday ? 'bg-indigo-600 text-white font-semibold' : 'text-slate-600 hover:bg-slate-100 cursor-pointer' }}">
                        {{ $d }}
                    </div>
                @endfor
            </div>
            <div class="mt-3 pt-3 border-t border-slate-100 flex items-center gap-4 text-xs text-slate-500">
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-indigo-600 inline-block"></span>Today</span>
                <span class="inline-flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-indigo-50 ring-1 ring-indigo-200 inline-block font-semibold"></span>Has activity</span>
            </div>
        </x-card>

        <!-- Recent Activities Widget -->
        <x-card class="p-5">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <div class="text-slate-900 tracking-tight">Recent Activities</div>
                    <div class="text-xs text-slate-500">Latest scheduled events</div>
                </div>
                <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-500 flex items-center justify-center">
                    <x-icon name="calrange" class="w-4 h-4" />
                </div>
            </div>
            <ul class="divide-y divide-slate-100 max-h-[300px] overflow-y-auto pr-1">
                @forelse($recentActivities as $act)
                <li class="py-3 flex items-center justify-between text-xs">
                    <div>
                        <div class="font-semibold text-slate-800">{{ $act->title }}</div>
                        <div class="text-[10px] text-slate-500 flex items-center gap-1 mt-0.5">
                            <x-icon name="pin" class="w-3 h-3 text-slate-400" /> {{ $act->location }}
                            &middot;
                            <span>{{ $act->activity_date?->format('M d, Y') }}</span>
                        </div>
                    </div>
                    <span class="text-[10px] text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded-full border border-indigo-100 font-semibold">{{ $act->component }}</span>
                </li>
                @empty
                <li class="py-4 text-center text-sm text-slate-400">
                    No recent activities.
                </li>
                @endforelse
            </ul>
        </x-card>

        <!-- Incomplete Profiles Widget -->
        <x-card class="p-5">
            <div class="flex items-center justify-between mb-3">
                <div>
                    <div class="text-slate-900 font-bold tracking-tight">Incomplete Student Profiles</div>
                    <div class="text-xs text-slate-500 mt-0.5">Click any record to review and complete</div>
                </div>
                <button type="button" onclick="openIncompleteProfilesModal()" class="px-2.5 py-1 text-xs font-bold rounded-full {{ $incompleteCount > 0 ? 'bg-rose-100 text-rose-700 hover:bg-rose-200' : 'bg-emerald-100 text-emerald-700' }} transition cursor-pointer">
                    <span id="widgetIncompleteBadge">{{ $incompleteCount }} Flagged</span>
                </button>
            </div>
            
            <div class="relative mb-3">
                <span class="absolute left-2.5 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"><x-icon name="search" class="w-3.5 h-3.5" /></span>
                <input type="text" id="widgetIncompleteSearch" placeholder="Search flagged students..." class="w-full pl-8 pr-3 py-1.5 text-xs rounded-lg border border-slate-200 bg-white text-slate-700 placeholder-slate-400 focus:outline-none focus:border-indigo-300 transition-colors shadow-sm" />
            </div>

            <ul id="widgetIncompleteList" class="max-h-[320px] overflow-y-auto divide-y divide-slate-50 pr-1">
                @forelse($incompleteProfiles as $p)
                <li class="widget-incomplete-item py-2.5 px-2 flex items-center justify-between text-xs hover:bg-rose-50/60 rounded-xl cursor-pointer transition group"
                    data-search-text="{{ strtolower($p->name . ' ' . $p->id . ' ' . $p->course . ' ' . implode(' ', $p->missing_fields)) }}"
                    onclick="openEditIncompleteStudentModal({{ json_encode($p) }})">
                    <div>
                        <div class="font-bold text-slate-800 group-hover:text-rose-700 transition-colors">{{ $p->name }}</div>
                        <div class="text-[10px] text-slate-400 mt-0.5">{{ $p->id }} &middot; {{ $p->course }}</div>
                        @if(!empty($p->missing_fields))
                            <div class="mt-1.5 flex flex-wrap gap-1">
                                @foreach($p->missing_fields as $mf)
                                    <span class="text-[9px] font-bold px-1.5 py-0.5 rounded bg-amber-50 text-amber-800 border border-amber-200/80">Missing {{ $mf }}</span>
                                @endforeach
                            </div>
                        @endif
                    </div>
                    <button type="button" class="text-[10px] text-rose-600 bg-rose-50 px-2.5 py-1 rounded-lg border border-rose-200 font-bold shrink-0 ml-2 group-hover:bg-rose-600 group-hover:text-white transition">
                        Complete
                    </button>
                </li>
                @empty
                <li class="text-center text-sm text-slate-400 py-8 flex flex-col items-center gap-2">
                    <div class="w-10 h-10 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center"><x-icon name="check2" class="w-5 h-5" /></div>
                    <div class="font-bold text-slate-700 text-xs">All Profiles Complete</div>
                    <div class="text-[11px] text-slate-400">All student records have complete data fields.</div>
                </li>
                @endforelse
            </ul>
        </x-card>

    </div>
</div>

<!-- ========================================================================= -->
<!-- INCOMPLETE PROFILES OVERVIEW MODAL -->
<!-- ========================================================================= -->
<div id="incompleteProfilesModal" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-3xl mx-4 overflow-hidden flex flex-col max-h-[85vh]">
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50/50">
            <div>
                <h3 class="text-base font-bold text-slate-800 tracking-tight">Flagged Incomplete Student Profiles</h3>
                <p class="text-xs text-slate-500 mt-0.5">Click "Edit & Complete" on any record to fill missing details</p>
            </div>
            <button type="button" onclick="closeIncompleteProfilesModal()" class="text-slate-400 hover:text-slate-700 p-1.5 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                <x-icon name="close" class="w-4 h-4" />
            </button>
        </div>

        <div class="p-4 border-b border-slate-100 bg-white">
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400 pointer-events-none"><x-icon name="search" class="w-4 h-4" /></span>
                <input type="text" id="modalIncompleteSearch" placeholder="Filter by student name, ID, course, or missing field..." class="w-full pl-9 pr-4 py-2 text-xs rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-400 focus:ring-2 focus:ring-indigo-100 transition shadow-2xs" />
            </div>
        </div>

        <div class="p-4 overflow-y-auto flex-1 space-y-2">
            <div id="modalIncompleteListContainer">
                @forelse($incompleteProfiles as $p)
                <div class="modal-incomplete-row p-3 rounded-xl border border-slate-100 hover:border-rose-200 hover:bg-rose-50/40 transition mb-2.5 flex items-center justify-between gap-4"
                     data-search-text="{{ strtolower($p->name . ' ' . $p->id . ' ' . $p->course . ' ' . implode(' ', $p->missing_fields)) }}">
                    <div class="space-y-1">
                        <div class="flex items-center gap-2">
                            <span class="font-bold text-slate-800 text-xs">{{ $p->name }}</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600">{{ $p->id }}</span>
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700">{{ $p->course }}</span>
                        </div>
                        <div class="flex flex-wrap items-center gap-1.5 pt-0.5">
                            <span class="text-[10px] text-slate-400 font-semibold">Missing:</span>
                            @foreach($p->missing_fields as $mf)
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-amber-50 text-amber-800 border border-amber-200/80">
                                    {{ $mf }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                    <button type="button" onclick="openEditIncompleteStudentModal({{ json_encode($p) }})" class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl bg-rose-600 text-white font-bold text-xs hover:bg-rose-700 transition shadow-xs cursor-pointer shrink-0">
                        <x-icon name="pencil" class="w-3.5 h-3.5" /> Edit & Complete
                    </button>
                </div>
                @empty
                <div class="text-center py-12 text-slate-400">
                    <x-icon name="check2" class="w-10 h-10 text-emerald-500 mx-auto mb-2" />
                    <div class="font-bold text-slate-700 text-sm">All Student Profiles Are Complete</div>
                    <div class="text-xs text-slate-500 mt-1">No missing fields were detected in the database.</div>
                </div>
                @endforelse
            </div>
        </div>

        <div class="px-6 py-3 border-t border-slate-100 bg-slate-50/50 flex justify-end">
            <button type="button" onclick="closeIncompleteProfilesModal()" class="px-4 py-2 text-xs font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 transition cursor-pointer">Close</button>
        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- EDIT & COMPLETE INCOMPLETE PROFILE MODAL -->
<!-- ========================================================================= -->
<div id="editIncompleteProfileModal" class="fixed inset-0 z-[60] flex items-center justify-center bg-slate-900/50 backdrop-blur-sm hidden">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-lg mx-4 overflow-hidden">
        <form id="editIncompleteProfileForm">
            <input type="hidden" id="incStuDbId" />
            
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-rose-50/40">
                <div>
                    <h3 class="text-base font-bold text-slate-900 tracking-tight">Complete Student Profile</h3>
                    <p class="text-xs text-slate-500 mt-0.5">Fill in missing information to remove flags</p>
                </div>
                <button type="button" onclick="closeEditIncompleteStudentModal()" class="text-slate-400 hover:text-slate-700 p-1 rounded-lg hover:bg-slate-100 transition cursor-pointer">
                    <x-icon name="close" class="w-4 h-4" />
                </button>
            </div>

            <div class="p-6 space-y-4 max-h-[75vh] overflow-y-auto text-xs">
                <!-- Missing fields banner notice -->
                <div id="incMissingAlert" class="p-3 rounded-xl bg-amber-50 border border-amber-200 text-amber-900 font-medium space-y-1">
                    <div class="font-bold flex items-center gap-1.5 text-amber-800">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        <span>Fields Needing Completion:</span>
                    </div>
                    <div id="incMissingListText" class="text-amber-800 font-normal leading-relaxed pl-5">
                        <!-- Populated dynamically -->
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1">First Name</label>
                        <input type="text" id="incFirstName" required class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-400 transition" />
                    </div>
                    <div>
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Last Name</label>
                        <input type="text" id="incLastName" required class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-400 transition" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Student ID / Serial No</label>
                        <input type="text" id="incStudentId" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-400 transition" />
                    </div>
                    <div>
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1">Course / Program</label>
                        <input type="text" id="incCourse" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-400 transition" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1 flex items-center justify-between">
                            <span>Email Address</span>
                            <span id="badgeEmailMissing" class="hidden text-[9px] font-bold text-rose-600 uppercase">Missing</span>
                        </label>
                        <input type="email" id="incEmail" placeholder="e.g. student@dnsc.edu.ph" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-400 transition" />
                    </div>
                    <div>
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1 flex items-center justify-between">
                            <span>Contact Number</span>
                            <span id="badgeCellMissing" class="hidden text-[9px] font-bold text-rose-600 uppercase">Missing</span>
                        </label>
                        <input type="text" id="incCellNo" placeholder="e.g. 09123456789" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-400 transition" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1 flex items-center justify-between">
                            <span>Date of Birth</span>
                            <span id="badgeDobMissing" class="hidden text-[9px] font-bold text-rose-600 uppercase">Missing</span>
                        </label>
                        <input type="date" id="incDob" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-400 transition" />
                    </div>
                    <div>
                        <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1 flex items-center justify-between">
                            <span>Gender / Sex</span>
                            <span id="badgeGenderMissing" class="hidden text-[9px] font-bold text-rose-600 uppercase">Missing</span>
                        </label>
                        <select id="incSex" class="w-full px-3 py-2 rounded-xl border border-slate-200 bg-white focus:outline-none focus:border-indigo-400 transition cursor-pointer">
                            <option value="Female">Female</option>
                            <option value="Male">Male</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1 flex items-center justify-between">
                        <span>Place of Birth</span>
                        <span id="badgePobMissing" class="hidden text-[9px] font-bold text-rose-600 uppercase">Missing</span>
                    </label>
                    <input type="text" id="incBirthPlace" placeholder="City / Municipality, Province" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-400 transition" />
                </div>

                <div>
                    <label class="text-[11px] font-bold text-slate-500 uppercase tracking-wider block mb-1 flex items-center justify-between">
                        <span>Complete Residential Address</span>
                        <span id="badgeAddrMissing" class="hidden text-[9px] font-bold text-rose-600 uppercase">Missing</span>
                    </label>
                    <input type="text" id="incAddress" placeholder="Barangay, City/Municipality, Province" class="w-full px-3 py-2 rounded-xl border border-slate-200 focus:outline-none focus:border-indigo-400 transition" />
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50">
                <button type="button" onclick="closeEditIncompleteStudentModal()" class="px-4 py-2 font-semibold text-slate-600 hover:bg-slate-100 rounded-xl transition cursor-pointer">Cancel</button>
                <button type="submit" id="saveIncompleteBtn" class="px-5 py-2 rounded-xl bg-indigo-600 text-white font-bold hover:bg-indigo-700 shadow-sm transition cursor-pointer flex items-center gap-2">
                    <x-icon name="check2" class="w-4 h-4" /> Save & Complete Profile
                </button>
            </div>
        </form>
    </div>
</div>

<script>
    // Modal Functions for Incomplete Profiles
    window.openIncompleteProfilesModal = function() {
        const modal = document.getElementById('incompleteProfilesModal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    };

    window.closeIncompleteProfilesModal = function() {
        const modal = document.getElementById('incompleteProfilesModal');
        if (modal) {
            modal.classList.add('hidden');
        }
    };

    let activeIncompleteStudent = null;

    window.openEditIncompleteStudentModal = function(student) {
        activeIncompleteStudent = student;

        document.getElementById('incStuDbId').value = student.db_id;
        document.getElementById('incFirstName').value = student.first_name || '';
        document.getElementById('incLastName').value = student.last_name || '';
        document.getElementById('incStudentId').value = student.id || '';
        document.getElementById('incCourse').value = student.course || '';
        document.getElementById('incEmail').value = student.email || '';
        document.getElementById('incCellNo').value = student.contact_number || '';
        document.getElementById('incDob').value = student.dob || '';
        document.getElementById('incSex').value = student.sex || 'Female';
        document.getElementById('incBirthPlace').value = student.birth_place || '';
        document.getElementById('incAddress').value = student.address || '';

        // Missing fields badges
        const missing = student.missing_fields || [];
        const missingAlert = document.getElementById('incMissingAlert');
        const missingText = document.getElementById('incMissingListText');
        
        if (missing.length > 0) {
            missingAlert.classList.remove('hidden');
            missingText.textContent = missing.join(', ');
        } else {
            missingAlert.classList.add('hidden');
        }

        // Highlight missing inputs
        const toggleMissingBadge = (elemId, isMissing) => {
            const badge = document.getElementById(elemId);
            if (badge) {
                if (isMissing) badge.classList.remove('hidden');
                else badge.classList.add('hidden');
            }
        };

        toggleMissingBadge('badgeEmailMissing', !student.email);
        toggleMissingBadge('badgeCellMissing', !student.contact_number);
        toggleMissingBadge('badgeDobMissing', !student.dob);
        toggleMissingBadge('badgeGenderMissing', !student.sex);
        toggleMissingBadge('badgePobMissing', !student.birth_place);
        toggleMissingBadge('badgeAddrMissing', !student.address);

        document.getElementById('editIncompleteProfileModal').classList.remove('hidden');
    };

    window.closeEditIncompleteStudentModal = function() {
        document.getElementById('editIncompleteProfileModal').classList.add('hidden');
    };

    // Live search in modal & widget
    document.addEventListener('DOMContentLoaded', () => {
        const modalSearch = document.getElementById('modalIncompleteSearch');
        if (modalSearch) {
            modalSearch.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase().trim();
                const rows = document.querySelectorAll('.modal-incomplete-row');
                rows.forEach(r => {
                    const txt = r.getAttribute('data-search-text') || '';
                    if (!query || txt.includes(query)) r.classList.remove('hidden');
                    else r.classList.add('hidden');
                });
            });
        }

        const widgetSearch = document.getElementById('widgetIncompleteSearch');
        if (widgetSearch) {
            widgetSearch.addEventListener('input', (e) => {
                const query = e.target.value.toLowerCase().trim();
                const items = document.querySelectorAll('.widget-incomplete-item');
                items.forEach(item => {
                    const txt = item.getAttribute('data-search-text') || '';
                    if (!query || txt.includes(query)) item.classList.remove('hidden');
                    else item.classList.add('hidden');
                });
            });
        }

        // Handle AJAX form submission to save profile edits
        const form = document.getElementById('editIncompleteProfileForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();

                const dbId = document.getElementById('incStuDbId').value;
                const saveBtn = document.getElementById('saveIncompleteBtn');
                const origBtnContent = saveBtn.innerHTML;

                saveBtn.disabled = true;
                saveBtn.innerHTML = 'Saving...';

                const payload = {
                    first_name: document.getElementById('incFirstName').value,
                    last_name: document.getElementById('incLastName').value,
                    email: document.getElementById('incEmail').value,
                    cell_no: document.getElementById('incCellNo').value,
                    dob: document.getElementById('incDob').value,
                    gender: document.getElementById('incSex').value,
                    birth_place: document.getElementById('incBirthPlace').value,
                    address: document.getElementById('incAddress').value,
                    course: document.getElementById('incCourse').value,
                };

                fetch(`/api/students/${dbId}`, {
                    method: 'PATCH',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(payload)
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success || data.student) {
                        closeEditIncompleteStudentModal();
                        if (window.showAlertModal) {
                            window.showAlertModal('Student profile updated and completed successfully!', 'Success', 'success');
                        } else {
                            alert('Student profile updated successfully!');
                        }
                        setTimeout(() => window.location.reload(), 600);
                    } else {
                        alert(data.message || 'Failed to update student profile.');
                    }
                })
                .catch(err => {
                    console.error(err);
                    alert('Error updating student profile. Please try again.');
                })
                .finally(() => {
                    saveBtn.disabled = false;
                    saveBtn.innerHTML = origBtnContent;
                });
            });
        }
    });
</script>
@endsection
