@extends('layouts.coordinator')

@section('title', 'Calendar - Coordinator Dashboard')

@section('content')

<x-page-header title="Program Calendar" subtitle="Manage university-wide NSTP events, official announcements, and deadlines">
    <x-slot name="actions">
        <button onclick="openCreateEventModal()" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition shadow-sm cursor-pointer">
            <x-icon name="plus" class="w-4 h-4" /> New Event
        </button>
    </x-slot>
</x-page-header>


<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
    <div class="lg:col-span-2">
        <x-card class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-slate-800">{{ $selectedMonth->format('F Y') }}</h3>
                <div class="flex items-center gap-2">
                    <a href="{{ route('coordinator.calendar', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}" class="p-2 rounded hover:bg-slate-50 text-slate-400 inline-block shrink-0" title="Previous Month">
                        <x-icon name="chevron" class="w-4 h-4 rotate-90" />
                    </a>
                    <a href="{{ route('coordinator.calendar', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}" class="p-2 rounded hover:bg-slate-50 text-slate-400 inline-block shrink-0" title="Next Month">
                        <x-icon name="chevron" class="w-4 h-4 -rotate-90" />
                    </a>
                </div>
            </div>
            
            <div class="grid grid-cols-7 gap-3 mb-2">
                @foreach(['Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'] as $dayOfWeekName)
                <div class="text-center text-[11px] font-bold text-slate-400 uppercase tracking-wider">{{ $dayOfWeekName }}</div>
                @endforeach
            </div>
            
            <div class="grid grid-cols-7 gap-3">
                @for($i = 0; $i < $selectedMonth->copy()->startOfMonth()->dayOfWeek; $i++)
                    <div class="border border-transparent p-3 min-h-[105px]"></div>
                @endfor
                @for($d = 1; $d <= $selectedMonth->daysInMonth; $d++)
                    @php
                        $dayActivities = $activities->filter(function($act) use ($d) {
                            return $act->activity_date->day == $d;
                        });
                        $isToday = ($d == now()->day && $selectedMonth->isCurrentMonth());
                        $currentDateIso = $selectedMonth->copy()->day($d)->format('Y-m-d');
                        $currentDateFormatted = $selectedMonth->copy()->day($d)->format('F d, Y');
                        
                        $maxVisible = 2;
                        $visibleActivities = $dayActivities->take($maxVisible);
                        $extraCount = $dayActivities->count() - $maxVisible;
                    @endphp
                    <div class="border {{ $isToday ? 'border-indigo-400 ring-2 ring-indigo-50/50 bg-indigo-50/10' : 'border-slate-200' }} rounded-xl p-3 min-h-[105px] bg-white hover:border-indigo-400 hover:shadow-md transition duration-205 cursor-pointer relative group flex flex-col justify-between"
                         onclick="openDayEventsModal('{{ $currentDateIso }}', '{{ $currentDateFormatted }}', this)"
                         data-events="{{ json_encode($dayActivities->values()) }}"
                         title="Click date to view events for {{ $currentDateFormatted }}">
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm font-bold {{ $isToday ? 'text-indigo-600 bg-indigo-100/70 w-6 h-6 rounded-full flex items-center justify-center -mt-1 -ml-1 text-xs shrink-0 font-extrabold' : 'text-slate-700' }}">{{ $d }}</span>
                                @if($dayActivities->count() > 0)
                                    <span class="text-[10px] font-bold text-indigo-500 bg-indigo-50 px-1.5 py-0.5 rounded-full border border-indigo-100 opacity-80 group-hover:opacity-100 transition">{{ $dayActivities->count() }} {{ Str::plural('event', $dayActivities->count()) }}</span>
                                @endif
                            </div>
                            <div class="space-y-1">
                                @foreach($visibleActivities as $act)
                                    @php
                                        if (!empty($act->is_announcement)) {
                                            $colorClass = 'bg-amber-500 text-white border-l-2 border-amber-700 hover:bg-amber-600 font-bold';
                                            $pillText = '📢 ' . $act->title;
                                        } else {
                                            $colorClass = match($act->component ?? 'General') {
                                                'CWTS' => 'bg-indigo-600 text-white border-l-2 border-indigo-700 hover:bg-indigo-700',
                                                'LTS' => 'bg-emerald-600 text-white border-l-2 border-emerald-700 hover:bg-emerald-700',
                                                'ROTC' => 'bg-rose-500 text-white border-l-2 border-rose-600 hover:bg-rose-600',
                                                default => 'bg-blue-600 text-white hover:bg-blue-700 border-l-2 border-blue-700'
                                            };
                                            $pillText = $act->title;
                                        }
                                    @endphp
                                    <div class="text-[10px] px-2 py-1 rounded truncate {{ $colorClass }} font-semibold tracking-tight shadow-sm cursor-pointer hover:shadow transition-all duration-150 active:scale-95"
                                         onclick="event.stopPropagation(); openViewEventModal(this)"
                                         data-title="{{ $act->title }}"
                                         data-component="{{ $act->component ?? 'General' }}"
                                         data-date="{{ $act->activity_date?->format('F d, Y') }}"
                                         data-location="{{ $act->location ?? 'TBA' }}"
                                         data-description="{{ $act->description ?? 'No detailed description provided.' }}"
                                         data-is-plan="{{ !empty($act->is_plan) ? '1' : '0' }}"
                                         data-is-announcement="{{ !empty($act->is_announcement) ? '1' : '0' }}"
                                         title="Click to view details for {{ $act->title }}">
                                        {{ $pillText }}
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        @if($extraCount > 0)
                            <div class="mt-1 text-[10px] font-extrabold text-indigo-700 bg-indigo-50 hover:bg-indigo-100 border border-indigo-200/80 px-2 py-0.5 rounded-lg text-center transition shadow-xs cursor-pointer">
                                +{{ $extraCount }} more {{ Str::plural('event', $extraCount) }}
                            </div>
                        @endif
                    </div>
                @endfor
            </div>
        </x-card>
    </div>
    
    <div class="lg:col-span-1 flex flex-col gap-5">
        <x-card title="Upcoming Events & Notices">
            <ul class="divide-y divide-slate-100">
                @forelse($upcomingActivities as $act)
                <li class="p-4 hover:bg-slate-50 transition cursor-pointer"
                    onclick="openViewEventModal(this)"
                    data-title="{{ $act->title }}"
                    data-component="{{ $act->component ?? 'General' }}"
                    data-date="{{ $act->activity_date?->format('F d, Y') }}"
                    data-location="{{ $act->location ?? 'TBA' }}"
                    data-description="{{ $act->description ?? 'No detailed description provided.' }}"
                    data-is-plan="{{ !empty($act->is_plan) ? '1' : '0' }}"
                    data-is-announcement="{{ !empty($act->is_announcement) ? '1' : '0' }}">
                    <div class="flex items-center justify-between">
                        <div class="text-sm font-bold text-slate-800 flex items-center gap-1.5">
                            @if(!empty($act->is_announcement))
                                <span class="text-amber-500">📢</span>
                            @endif
                            <span>{{ $act->title }}</span>
                        </div>
                        @if(!empty($act->is_announcement))
                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200">ANNOUNCEMENT</span>
                        @else
                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full {{ match($act->component ?? 'General') { 'CWTS' => 'bg-indigo-50 text-indigo-700 border border-indigo-200', 'LTS' => 'bg-emerald-50 text-emerald-700 border border-emerald-200', 'ROTC' => 'bg-rose-50 text-rose-700 border border-rose-200', default => 'bg-blue-50 text-blue-700 border border-blue-200' } }}">{{ $act->component ?? 'General' }}</span>
                        @endif
                    </div>
                    <div class="text-xs text-slate-500 mt-1 flex items-center gap-2">
                        <x-icon name="calendar" class="w-3.5 h-3.5 text-slate-400" /> {{ $act->activity_date?->format('F d, Y') }}
                    </div>
                </li>
                @empty
                <li class="p-4 text-center text-sm text-slate-400">No upcoming events or announcements.</li>
                @endforelse
            </ul>
        </x-card>
    </div>
</div>

<!-- Create Event Modal -->
<div id="eventOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden transition duration-300" onclick="if(event.target === this) closeCreateEventModal()">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-lg mx-4 overflow-hidden transform scale-95 duration-300">
        <form action="{{ route('coordinator.calendar.store') }}" method="POST" data-progress-title="Publishing Event" data-progress-subtitle="Adding activity event to the program calendar...">
            @csrf
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-indigo-600 to-blue-600 text-white">
                <div>
                    <div class="font-extrabold tracking-tight text-lg">Create Calendar Event</div>
                    <div class="text-xs text-white/80 mt-0.5">Publish an activity directly to the program calendar</div>
                </div>
                <button type="button" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer" onclick="closeCreateEventModal()">
                    <x-icon name="close" class="w-5 h-5" />
                </button>
            </div>
            
            <div class="p-6 space-y-4 text-sm max-h-[70vh] overflow-y-auto">
                <div>
                    <label class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 block">Activity Title <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" required placeholder="e.g. Nationwide Earthquake Drill / CWTS Seminar" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-300 transition" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 block">Program Component <span class="text-rose-500">*</span></label>
                        <select name="component" required class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-300 transition">
                            <option value="" disabled selected>Select Component</option>
                            <option value="General">GENERAL</option>
                            <option value="CWTS">CWTS</option>
                            <option value="LTS">LTS</option>
                            <option value="ROTC">ROTC</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 block">Scheduled Date <span class="text-rose-500">*</span></label>
                        <input type="date" name="activity_date" required class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-300 transition" />
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 block">Scheduled Time <span class="text-rose-500">*</span></label>
                        <input type="time" name="activity_time" required value="08:00" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-300 transition" />
                    </div>
                    <div>
                        <label class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 block">Venue / Location <span class="text-rose-500">*</span></label>
                        <input type="text" name="location" required placeholder="e.g. University Gymnasium" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-300 transition" />
                    </div>
                </div>

                <div>
                    <label class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 block">Description / Objectives</label>
                    <textarea name="description" rows="3" placeholder="Provide any details, objectives, or instructions..." class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-4 focus:ring-indigo-100 focus:border-indigo-300 transition"></textarea>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50 rounded-b-2xl">
                <button type="button" class="px-4 py-2 text-sm font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 transition cursor-pointer" onclick="closeCreateEventModal()">Cancel</button>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-md transition cursor-pointer">
                    <x-icon name="check2" class="w-4 h-4" /> Save Event
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Day Events Agenda Modal -->
<div id="dayEventsOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden transition duration-300" onclick="if(event.target === this) closeDayEventsModal()">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-lg mx-4 overflow-hidden transform scale-95 duration-300">
        <!-- Modal Header -->
        <div class="px-6 py-5 flex items-center justify-between bg-gradient-to-r from-slate-900 via-indigo-950 to-slate-900 text-white">
            <div>
                <div class="text-[11px] text-indigo-300 font-extrabold uppercase tracking-wider">Scheduled Events & Announcements</div>
                <h3 class="text-lg font-extrabold tracking-tight mt-0.5" id="dayEventsModalTitle">Events on Date</h3>
            </div>
            <button type="button" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer" onclick="closeDayEventsModal()">
                <x-icon name="close" class="w-5 h-5" />
            </button>
        </div>

        <!-- Modal Body: Events List -->
        <div class="p-6 space-y-3 max-h-[60vh] overflow-y-auto" id="dayEventsModalList">
            <!-- Populated dynamically via JS -->
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-between bg-slate-50 rounded-b-2xl">
            <button type="button" id="dayEventsAddBtn" onclick="addEventForSelectedDate()" class="inline-flex items-center gap-1.5 px-4 py-2 text-xs font-bold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 transition shadow-sm cursor-pointer">
                <x-icon name="plus" class="w-3.5 h-3.5" /> Add Event for Date
            </button>
            <button type="button" class="px-5 py-2 text-xs font-semibold rounded-xl bg-slate-200 text-slate-700 hover:bg-slate-300 transition cursor-pointer" onclick="closeDayEventsModal()">
                Close
            </button>
        </div>
    </div>
</div>

<!-- View Event Details Modal -->
<div id="viewEventOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden transition duration-300" onclick="if(event.target === this) closeViewEventModal()">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-lg mx-4 overflow-hidden transform scale-95 duration-300">
        <!-- Modal Header -->
        <div id="viewEventHeader" class="px-6 py-5 flex items-center justify-between bg-gradient-to-r from-indigo-600 to-blue-600 text-white">
            <div>
                <div class="flex items-center gap-2">
                    <span id="viewEventBadge" class="text-[11px] font-extrabold uppercase px-2.5 py-0.5 rounded-full bg-white/20 text-white border border-white/30 backdrop-blur-sm">CWTS</span>
                    <span id="viewEventTypeBadge" class="text-[11px] font-semibold text-white/80">Calendar Activity</span>
                </div>
                <h3 class="text-lg font-extrabold tracking-tight mt-1.5" id="viewEventTitle">Activity Title</h3>
            </div>
            <button type="button" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer" onclick="closeViewEventModal()">
                <x-icon name="close" class="w-5 h-5" />
            </button>
        </div>

        <!-- Modal Body -->
        <div class="p-6 space-y-4 text-sm max-h-[70vh] overflow-y-auto">
            <div class="grid grid-cols-2 gap-4 p-4 rounded-xl bg-slate-50 border border-slate-100">
                <div>
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider">Scheduled Date</span>
                    <span class="text-sm font-semibold text-slate-800 mt-1 flex items-center gap-1.5">
                        <x-icon name="calendar" class="w-4 h-4 text-indigo-500 shrink-0" />
                        <span id="viewEventDateText">Oct 12, 2026</span>
                    </span>
                </div>
                <div>
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider" id="viewEventLocationLabel">Location / Venue</span>
                    <span class="text-sm font-semibold text-slate-800 mt-1 flex items-center gap-1.5">
                        <x-icon name="chevron" class="w-4 h-4 text-indigo-500 shrink-0 rotate-90" />
                        <span id="viewEventLocationText">University Gym</span>
                    </span>
                </div>
            </div>

            <div>
                <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider mb-1.5">Description & Objectives</span>
                <div class="p-4 rounded-xl bg-slate-50 border border-slate-100 text-sm text-slate-700 whitespace-pre-line leading-relaxed" id="viewEventDescription">
                    No description provided for this activity.
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end bg-slate-50 rounded-b-2xl">
            <button type="button" class="px-5 py-2 text-sm font-semibold rounded-xl bg-slate-800 text-white hover:bg-slate-900 active:scale-95 transition shadow-sm cursor-pointer" onclick="closeViewEventModal()">
                Close
            </button>
        </div>
    </div>
</div>

@push('scripts')
<script>
    let selectedDayIsoDate = '';

    window.openCreateEventModal = function() {
        document.getElementById('eventOverlay').classList.remove('hidden');
    }
    window.closeCreateEventModal = function() {
        document.getElementById('eventOverlay').classList.add('hidden');
    }

    window.openDayEventsModal = function(isoDate, formattedDate, dateElement) {
        selectedDayIsoDate = isoDate;
        document.getElementById('dayEventsModalTitle').innerText = 'Events on ' + formattedDate;
        
        let events = [];
        if (dateElement && dateElement.dataset && dateElement.dataset.events) {
            try {
                events = JSON.parse(dateElement.dataset.events);
            } catch(e) {
                console.error('Error parsing events JSON', e);
            }
        }

        const container = document.getElementById('dayEventsModalList');
        container.innerHTML = '';

        if (!events || events.length === 0) {
            container.innerHTML = `
                <div class="p-8 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200">
                    <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 002-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                    </div>
                    <div class="text-sm font-bold text-slate-700">No events or announcements scheduled for this day</div>
                    <div class="text-xs text-slate-400 mt-1">Click "+ Add Event" below to schedule an activity for this date.</div>
                </div>
            `;
        } else {
            events.forEach((act) => {
                const comp = act.component || 'General';
                const isAnn = Boolean(act.is_announcement);
                const isPlan = Boolean(act.is_plan);

                let badgeClass = 'bg-blue-50 text-blue-700 border-blue-200';
                if (isAnn) {
                    badgeClass = 'bg-amber-100 text-amber-800 border-amber-300 font-extrabold';
                } else if (comp === 'ROTC') {
                    badgeClass = 'bg-rose-50 text-rose-700 border-rose-200';
                } else if (comp === 'LTS') {
                    badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                } else if (comp === 'CWTS') {
                    badgeClass = 'bg-indigo-50 text-indigo-700 border-indigo-200';
                }

                const typeBadge = isAnn ? '📢 Official Announcement' : (isPlan ? 'Approved Activity Plan' : 'Calendar Activity');
                const loc = act.location || 'TBA';

                const card = document.createElement('div');
                card.className = 'p-4 rounded-xl border border-slate-200 bg-white hover:border-indigo-400 hover:shadow-md transition duration-200 cursor-pointer space-y-2 group';
                card.onclick = function() {
                    closeDayEventsModal();
                    window.openViewEventModalFromObj(act);
                };

                card.innerHTML = `
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full border ${badgeClass}">${isAnn ? 'ANNOUNCEMENT' : comp}</span>
                            <span class="text-[10px] font-semibold ${isAnn ? 'text-amber-600 font-bold' : 'text-slate-400'}">${typeBadge}</span>
                        </div>
                        <span class="text-xs font-semibold text-indigo-600 group-hover:translate-x-1 transition flex items-center gap-1">View Details &rarr;</span>
                    </div>
                    <div class="text-sm font-bold text-slate-800 group-hover:text-indigo-600 transition">${isAnn ? '📢 ' : ''}${act.title}</div>
                    <div class="flex items-center gap-4 text-xs text-slate-500">
                        <span class="flex items-center gap-1"><svg class="w-3.5 h-3.5 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg> ${isAnn ? 'Source: ' + loc : loc}</span>
                    </div>
                `;
                container.appendChild(card);
            });
        }

        document.getElementById('dayEventsOverlay').classList.remove('hidden');
    };

    window.closeDayEventsModal = function() {
        document.getElementById('dayEventsOverlay').classList.add('hidden');
    };

    window.addEventForSelectedDate = function() {
        closeDayEventsModal();
        openCreateEventModal();
        if (selectedDayIsoDate) {
            const dateInput = document.querySelector('#eventOverlay input[name="activity_date"]');
            if (dateInput) dateInput.value = selectedDayIsoDate;
        }
    };

    window.openViewEventModalFromObj = function(act) {
        const title = act.title || 'Event Details';
        const component = act.component || 'General';
        const dateStr = act.activity_date;
        let dateFormatted = 'TBA';
        if (dateStr) {
            const parts = String(dateStr).split('T')[0].split('-');
            if (parts.length === 3) {
                const dObj = new Date(parts[0], parts[1] - 1, parts[2]);
                dateFormatted = dObj.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' });
            } else {
                dateFormatted = String(dateStr);
            }
        }
        const location = act.location || 'TBA';
        const description = act.description || 'No detailed description provided.';
        const isPlan = Boolean(act.is_plan);
        const isAnn = Boolean(act.is_announcement);

        document.getElementById('viewEventTitle').innerText = (isAnn ? '📢 ' : '') + title;
        document.getElementById('viewEventBadge').innerText = isAnn ? 'ANNOUNCEMENT' : component;
        document.getElementById('viewEventTypeBadge').innerText = isAnn ? 'Official Announcement' : (isPlan ? 'Approved Activity Plan' : 'Calendar Activity');
        document.getElementById('viewEventDateText').innerText = dateFormatted;
        
        const locLabel = document.getElementById('viewEventLocationLabel');
        if (locLabel) locLabel.innerText = isAnn ? 'Issuing Source / Office' : 'Location / Venue';

        document.getElementById('viewEventLocationText').innerText = location;
        document.getElementById('viewEventDescription').innerText = description;

        const header = document.getElementById('viewEventHeader');
        if (isAnn) {
            header.className = 'px-6 py-5 flex items-center justify-between bg-gradient-to-r from-amber-600 to-orange-600 text-white';
        } else if (component === 'LTS') {
            header.className = 'px-6 py-5 flex items-center justify-between bg-gradient-to-r from-emerald-600 to-teal-600 text-white';
        } else if (component === 'ROTC') {
            header.className = 'px-6 py-5 flex items-center justify-between bg-gradient-to-r from-rose-600 to-red-600 text-white';
        } else {
            header.className = 'px-6 py-5 flex items-center justify-between bg-gradient-to-r from-indigo-600 to-blue-600 text-white';
        }

        document.getElementById('viewEventOverlay').classList.remove('hidden');
    };

    window.openViewEventModal = function(element) {
        const title = element.getAttribute('data-title') || 'Event Details';
        const component = element.getAttribute('data-component') || 'General';
        const date = element.getAttribute('data-date') || 'TBA';
        const location = element.getAttribute('data-location') || 'TBA';
        const description = element.getAttribute('data-description') || 'No detailed description provided.';
        const isPlan = element.getAttribute('data-is-plan') === '1';
        const isAnn = element.getAttribute('data-is-announcement') === '1';

        document.getElementById('viewEventTitle').innerText = (isAnn ? '📢 ' : '') + title;
        document.getElementById('viewEventBadge').innerText = isAnn ? 'ANNOUNCEMENT' : component;
        document.getElementById('viewEventTypeBadge').innerText = isAnn ? 'Official Announcement' : (isPlan ? 'Approved Activity Plan' : 'Calendar Activity');
        document.getElementById('viewEventDateText').innerText = date;
        
        const locLabel = document.getElementById('viewEventLocationLabel');
        if (locLabel) locLabel.innerText = isAnn ? 'Issuing Source / Office' : 'Location / Venue';

        document.getElementById('viewEventLocationText').innerText = location;
        document.getElementById('viewEventDescription').innerText = description;

        const header = document.getElementById('viewEventHeader');
        if (isAnn) {
            header.className = 'px-6 py-5 flex items-center justify-between bg-gradient-to-r from-amber-600 to-orange-600 text-white';
        } else if (component === 'LTS') {
            header.className = 'px-6 py-5 flex items-center justify-between bg-gradient-to-r from-emerald-600 to-teal-600 text-white';
        } else if (component === 'ROTC') {
            header.className = 'px-6 py-5 flex items-center justify-between bg-gradient-to-r from-rose-600 to-red-600 text-white';
        } else {
            header.className = 'px-6 py-5 flex items-center justify-between bg-gradient-to-r from-indigo-600 to-blue-600 text-white';
        }

        document.getElementById('viewEventOverlay').classList.remove('hidden');
    };

    window.closeViewEventModal = function() {
        document.getElementById('viewEventOverlay').classList.add('hidden');
    };
</script>
@endpush

@endsection
