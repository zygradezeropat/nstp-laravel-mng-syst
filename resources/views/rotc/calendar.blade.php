@extends('layouts.rotc')

@section('title', 'Calendar - ROTC Command')

@section('content')

<x-page-header title="Training Calendar" subtitle="Schedule of field exercises, official activities, and ROTC announcements">
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
    <div class="lg:col-span-2">
        <x-card class="p-6">
            <div class="flex items-center justify-between mb-6">
                <h3 class="text-lg font-bold text-slate-800">{{ $selectedMonth->format('F Y') }}</h3>
                <div class="flex items-center gap-2">
                    <a href="{{ route('rotc.calendar', ['month' => $prevMonth->month, 'year' => $prevMonth->year]) }}" class="p-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-600 inline-block shrink-0 transition" title="Previous Month">
                        <x-icon name="chevron" class="w-4 h-4 rotate-90" />
                    </a>
                    <a href="{{ route('rotc.calendar', ['month' => $nextMonth->month, 'year' => $nextMonth->year]) }}" class="p-2 rounded-lg border border-slate-200 hover:bg-slate-50 text-slate-600 inline-block shrink-0 transition" title="Next Month">
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
                    <div class="border {{ $isToday ? 'border-emerald-500 ring-2 ring-emerald-50/50 bg-emerald-50/10' : 'border-slate-200' }} rounded-xl p-3 min-h-[105px] bg-white hover:border-emerald-400 hover:shadow-md transition duration-205 cursor-pointer relative group flex flex-col justify-between"
                         onclick="openDayEventsModal('{{ $currentDateIso }}', '{{ $currentDateFormatted }}', this)"
                         data-events="{{ json_encode($dayActivities->values()) }}"
                         title="Click date to view events for {{ $currentDateFormatted }}">
                        <div>
                            <div class="flex justify-between items-center mb-1">
                                <span class="text-sm font-bold {{ $isToday ? 'text-emerald-700 bg-emerald-100/70 w-6 h-6 rounded-full flex items-center justify-center -mt-1 -ml-1 text-xs shrink-0 font-extrabold' : 'text-slate-700' }}">{{ $d }}</span>
                                @if($dayActivities->count() > 0)
                                    <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 px-1.5 py-0.5 rounded-full border border-emerald-200 opacity-80 group-hover:opacity-100 transition">{{ $dayActivities->count() }} {{ Str::plural('event', $dayActivities->count()) }}</span>
                                @endif
                            </div>
                            <div class="space-y-1">
                                @foreach($visibleActivities as $act)
                                    @php
                                        if (!empty($act->is_announcement)) {
                                            $colorClass = 'bg-amber-500 text-white border-l-2 border-amber-700 hover:bg-amber-600 font-bold';
                                            $pillText = '📢 ' . $act->title;
                                        } else {
                                            $colorClass = match($act->component ?? 'ROTC') {
                                                'ROTC' => 'bg-emerald-600 text-white border-l-2 border-emerald-800 hover:bg-emerald-700',
                                                'CWTS' => 'bg-indigo-600 text-white border-l-2 border-indigo-700 hover:bg-indigo-700',
                                                'LTS' => 'bg-emerald-600 text-white border-l-2 border-emerald-700 hover:bg-emerald-700',
                                                default => 'bg-emerald-600 text-white border-l-2 border-emerald-700 hover:bg-emerald-700'
                                            };
                                            $pillText = $act->title;
                                        }
                                    @endphp
                                    <div class="text-[10px] px-2 py-1 rounded truncate {{ $colorClass }} font-semibold tracking-tight shadow-sm cursor-pointer hover:shadow transition-all duration-150 active:scale-95"
                                         onclick="event.stopPropagation(); openViewEventModal(this)"
                                         data-title="{{ $act->title }}"
                                         data-component="{{ $act->component ?? 'ROTC' }}"
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
                            <div class="mt-1 text-[10px] font-extrabold text-emerald-800 bg-emerald-50 hover:bg-emerald-100 border border-emerald-200/80 px-2 py-0.5 rounded-lg text-center transition shadow-xs cursor-pointer">
                                +{{ $extraCount }} more {{ Str::plural('event', $extraCount) }}
                            </div>
                        @endif
                    </div>
                @endfor
            </div>
        </x-card>
    </div>
    
    <div class="lg:col-span-1 flex flex-col gap-5">
        <x-card title="Upcoming Activities & Notices" subtitle="Your schedule for the month">
            <ul class="divide-y divide-slate-100 mt-2">
                @forelse($upcomingActivities as $a)
                <li class="p-4 hover:bg-slate-50 transition cursor-pointer flex items-center justify-between gap-3"
                    onclick="openViewEventModal(this)"
                    data-title="{{ $a->title }}"
                    data-component="{{ $a->component ?? 'ROTC' }}"
                    data-date="{{ $a->activity_date?->format('F d, Y') }}"
                    data-location="{{ $a->location ?? 'TBA' }}"
                    data-description="{{ $a->description ?? 'No detailed description provided.' }}"
                    data-is-plan="{{ !empty($a->is_plan) ? '1' : '0' }}"
                    data-is-announcement="{{ !empty($a->is_announcement) ? '1' : '0' }}">
                    <div class="flex items-center gap-3 min-w-0">
                        <div class="w-1.5 h-10 rounded-full shrink-0 {{ $a->color ?? 'bg-emerald-500' }}"></div>
                        <div class="min-w-0">
                            <div class="text-sm font-bold text-slate-800 truncate flex items-center gap-1.5">
                                @if(!empty($a->is_announcement))
                                    <span class="text-amber-500">📢</span>
                                @endif
                                <span>{{ $a->title }}</span>
                            </div>
                            <div class="text-xs text-slate-500 mt-0.5 flex items-center gap-2">
                                <x-icon name="calendar" class="w-3.5 h-3.5 text-slate-400 shrink-0" />
                                <span>{{ $a->activity_date?->format('M d, Y') }}</span>
                            </div>
                        </div>
                    </div>
                    @if(!empty($a->is_announcement))
                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full bg-amber-50 text-amber-700 border border-amber-200 shrink-0">ANNOUNCEMENT</span>
                    @else
                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full shrink-0 bg-emerald-50 text-emerald-700 border border-emerald-200">
                            {{ $a->component ?? 'ROTC' }}
                        </span>
                    @endif
                </li>
                @empty
                <li class="py-8 text-center text-slate-400 text-sm">No upcoming activities or announcements.</li>
                @endforelse
            </ul>
        </x-card>
    </div>
</div>

<!-- Day Events Agenda Modal -->
<div id="dayEventsOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden transition duration-300" onclick="if(event.target === this) closeDayEventsModal()">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-lg mx-4 overflow-hidden transform scale-95 duration-300">
        <!-- Modal Header -->
        <div class="px-6 py-5 flex items-center justify-between bg-gradient-to-r from-slate-900 via-emerald-950 to-slate-900 text-white">
            <div>
                <div class="text-[11px] text-emerald-300 font-extrabold uppercase tracking-wider">Scheduled Events & Announcements</div>
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
        <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end bg-slate-50 rounded-b-2xl">
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
        <div id="viewEventHeader" class="px-6 py-5 flex items-center justify-between bg-gradient-to-r from-emerald-600 to-teal-700 text-white">
            <div>
                <div class="flex items-center gap-2">
                    <span id="viewEventBadge" class="text-[11px] font-extrabold uppercase px-2.5 py-0.5 rounded-full bg-white/20 text-white border border-white/30 backdrop-blur-sm">ROTC</span>
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
                        <x-icon name="calendar" class="w-4 h-4 text-emerald-600 shrink-0" />
                        <span id="viewEventDateText">Oct 12, 2026</span>
                    </span>
                </div>
                <div>
                    <span class="block text-xs font-bold text-slate-400 uppercase tracking-wider" id="viewEventLocationLabel">Location / Venue</span>
                    <span class="text-sm font-semibold text-slate-800 mt-1 flex items-center gap-1.5">
                        <x-icon name="chevron" class="w-4 h-4 text-emerald-600 shrink-0 rotate-90" />
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
                </div>
            `;
        } else {
            events.forEach((act) => {
                const comp = act.component || 'ROTC';
                const isAnn = Boolean(act.is_announcement);
                const isPlan = Boolean(act.is_plan);

                let badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                if (isAnn) {
                    badgeClass = 'bg-amber-100 text-amber-800 border-amber-300 font-extrabold';
                } else if (comp === 'ROTC') {
                    badgeClass = 'bg-emerald-50 text-emerald-700 border-emerald-200';
                } else if (comp === 'CWTS') {
                    badgeClass = 'bg-indigo-50 text-indigo-700 border-indigo-200';
                } else if (comp === 'LTS') {
                    badgeClass = 'bg-teal-50 text-teal-700 border-teal-200';
                }

                const typeBadge = isAnn ? '📢 Official Announcement' : (isPlan ? 'Approved Activity Plan' : 'Calendar Activity');
                const loc = act.location || 'TBA';

                const card = document.createElement('div');
                card.className = 'p-4 rounded-xl border border-slate-200 bg-white hover:border-emerald-400 hover:shadow-md transition duration-200 cursor-pointer space-y-2 group';
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
                        <span class="text-xs font-semibold text-emerald-600 group-hover:translate-x-1 transition flex items-center gap-1">View Details &rarr;</span>
                    </div>
                    <div class="text-sm font-bold text-slate-800 group-hover:text-emerald-700 transition">${isAnn ? '📢 ' : ''}${act.title}</div>
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

    window.openViewEventModalFromObj = function(act) {
        const title = act.title || 'Event Details';
        const component = act.component || 'ROTC';
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
        } else if (component === 'CWTS') {
            header.className = 'px-6 py-5 flex items-center justify-between bg-gradient-to-r from-indigo-600 to-blue-600 text-white';
        } else {
            header.className = 'px-6 py-5 flex items-center justify-between bg-gradient-to-r from-emerald-700 to-teal-800 text-white';
        }

        document.getElementById('viewEventOverlay').classList.remove('hidden');
    };

    window.openViewEventModal = function(element) {
        const title = element.getAttribute('data-title') || 'Event Details';
        const component = element.getAttribute('data-component') || 'ROTC';
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
        } else if (component === 'CWTS') {
            header.className = 'px-6 py-5 flex items-center justify-between bg-gradient-to-r from-indigo-600 to-blue-600 text-white';
        } else {
            header.className = 'px-6 py-5 flex items-center justify-between bg-gradient-to-r from-emerald-700 to-teal-800 text-white';
        }

        document.getElementById('viewEventOverlay').classList.remove('hidden');
    };

    window.closeViewEventModal = function() {
        document.getElementById('viewEventOverlay').classList.add('hidden');
    };
</script>
@endpush

@endsection
