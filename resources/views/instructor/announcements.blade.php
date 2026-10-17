@extends('layouts.instructor')

@section('title', 'Announcement - Instructor Console')

@section('content')

@php
    $scopeString = !empty($assignedComponents) ? implode(', ', $assignedComponents) : 'No Scope Assigned';
@endphp

<x-page-header title="Announcement" subtitle="Official activities and notices restricted to your authorized scope ({{ $scopeString }})">
    <x-slot name="actions">
        <form action="{{ route('instructor.announcements') }}" method="GET" class="relative" onsubmit="return true;">
            @if(request('component'))
                <input type="hidden" name="component" value="{{ request('component') }}">
            @endif
            <x-icon name="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input type="text" id="instructorSearchInput" name="search" value="{{ request('search') }}"
                   placeholder="Search activities..."
                   oninput="handleLiveSearch(this.value)"
                   class="pl-9 pr-8 py-2 text-sm rounded-lg bg-white border border-slate-200 w-64 shadow-sm focus:outline-none focus:border-indigo-300 transition" />
            <button type="button" id="clearSearchBtn" onclick="clearLiveSearch()"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs font-bold {{ request('search') ? '' : 'hidden' }}"
                    title="Clear search">&times;</button>
        </form>
    </x-slot>
</x-page-header>

{{-- Scope & Metrics Cards --}}
<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-6">
    <x-card class="p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
            <x-icon name="clipboard" class="w-6 h-6" />
        </div>
        <div>
            <div class="text-2xl font-extrabold text-slate-800">{{ $activities->total() }}</div>
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-0.5">Scope Activities</div>
        </div>
    </x-card>
    
    <x-card class="p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
            <x-icon name="calendar" class="w-6 h-6" />
        </div>
        <div>
            <div class="text-2xl font-extrabold text-slate-800">{{ $upcomingCount ?? 0 }}</div>
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-0.5">Upcoming Activities</div>
        </div>
    </x-card>

    <x-card class="p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
            <x-icon name="shield" class="w-6 h-6" />
        </div>
        <div class="min-w-0">
            <div class="text-base font-extrabold text-slate-800 truncate">{{ $scopeString }}</div>
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-0.5">Authorized Scope</div>
        </div>
    </x-card>
</div>

{{-- Activities List Card --}}
<x-card class="mt-6">
    <div class="p-5 border-b border-slate-100 flex flex-col sm:flex-row sm:items-center justify-between gap-3">
        <div>
            <h3 class="text-base font-bold text-slate-800">Assigned Activities & Notices</h3>
            <p class="text-xs text-slate-400 mt-0.5">Filtered specifically to your assigned NSTP component: <span class="font-bold text-slate-600">{{ $scopeString }}</span></p>
        </div>

        {{-- Component Tabs (if instructor handles multiple scopes) --}}
        @if(count($assignedComponents) > 1)
        <div class="flex items-center gap-1.5 p-1 bg-slate-100 rounded-xl">
            <a href="{{ route('instructor.announcements', ['search' => request('search')]) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ empty($selectedComponent) ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                All Scopes
            </a>
            @foreach($assignedComponents as $comp)
            <a href="{{ route('instructor.announcements', ['component' => $comp, 'search' => request('search')]) }}"
               class="px-3 py-1.5 rounded-lg text-xs font-bold transition {{ ($selectedComponent === $comp) ? 'bg-white text-slate-800 shadow-sm' : 'text-slate-500 hover:text-slate-700' }}">
                {{ $comp }}
            </a>
            @endforeach
        </div>
        @endif
    </div>

    <ul class="divide-y divide-slate-100" id="activitiesList">
        @forelse($activities as $a)
        @php
            $actDate = $a->activity_date ? \Carbon\Carbon::parse($a->activity_date) : null;
            $isUpcoming = $actDate ? ($actDate->isFuture() || $actDate->isToday()) : false;
            $compColor = match(strtoupper($a->component ?? '')) {
                'CWTS' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                'LTS' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                'ROTC' => 'bg-rose-50 text-rose-700 border-rose-200',
                default => 'bg-slate-50 text-slate-700 border-slate-200'
            };
            $compBadgeColor = match(strtoupper($a->component ?? '')) {
                'CWTS' => 'bg-indigo-600 text-white',
                'LTS' => 'bg-emerald-600 text-white',
                'ROTC' => 'bg-rose-600 text-white',
                default => 'bg-slate-700 text-white'
            };
        @endphp
        <li class="activity-item p-6 hover:bg-slate-50/50 transition duration-150"
            data-search-text="{{ strtolower($a->title . ' ' . $a->description . ' ' . $a->location . ' ' . $a->component) }}">
            <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                {{-- Date Tile --}}
                <div class="shrink-0 flex sm:flex-col items-center justify-center w-auto sm:w-16 px-3 sm:px-0 py-2 sm:py-2.5 rounded-2xl border border-slate-200 bg-slate-50/70 text-center shadow-xs">
                    <span class="text-[11px] font-extrabold uppercase tracking-wider text-slate-400">
                        {{ $actDate ? $actDate->format('M') : 'TBA' }}
                    </span>
                    <span class="text-xl sm:text-2xl font-black text-slate-800 leading-none sm:mt-0.5 ml-2 sm:ml-0">
                        {{ $actDate ? $actDate->format('d') : '--' }}
                    </span>
                    <span class="text-[10px] text-slate-400 font-semibold hidden sm:block mt-0.5">
                        {{ $actDate ? $actDate->format('Y') : '' }}
                    </span>
                </div>

                {{-- Activity Details --}}
                <div class="flex-1 min-w-0 space-y-2">
                    {{-- Scope & Status Badges --}}
                    <div class="flex items-center gap-2 flex-wrap">
                        {{-- Component Badge --}}
                        <span class="text-[11px] font-black px-2.5 py-0.5 rounded-full border {{ $compColor }}">
                            {{ $a->component }}
                        </span>

                        {{-- Scope restriction pill --}}
                        <span class="inline-flex items-center gap-1 text-[10px] uppercase tracking-wider font-extrabold text-indigo-700 bg-indigo-50 border border-indigo-100 px-2 py-0.5 rounded-full">
                            <x-icon name="shield" class="w-3 h-3 text-indigo-500" />
                            Restricted to {{ $a->component }} Instructors
                        </span>

                        {{-- Upcoming / Past Pill --}}
                        @if($isUpcoming)
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-emerald-700 bg-emerald-50 border border-emerald-200 px-2 py-0.5 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Upcoming Activity
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-[10px] font-bold text-slate-500 bg-slate-100 border border-slate-200 px-2 py-0.5 rounded-full">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Concluded
                            </span>
                        @endif

                        @if($a->created_at)
                            <span class="text-xs text-slate-400 ml-auto">
                                Posted {{ $a->created_at->diffForHumans() }}
                            </span>
                        @endif
                    </div>

                    {{-- Activity Title --}}
                    <h4 class="text-lg font-bold text-slate-900 tracking-tight leading-snug">
                        {{ $a->title }}
                    </h4>

                    {{-- Meta Info (Time & Location) --}}
                    <div class="flex items-center gap-4 flex-wrap text-xs text-slate-500 font-medium pt-0.5">
                        @if($a->activity_time)
                        <span class="inline-flex items-center gap-1.5 text-slate-600 font-semibold">
                            <x-icon name="clock" class="w-3.5 h-3.5 text-slate-400" />
                            {{ date('h:i A', strtotime($a->activity_time)) }}
                        </span>
                        @endif

                        @if($a->location)
                        <span class="inline-flex items-center gap-1.5 text-slate-600 font-semibold">
                            <x-icon name="mappin" class="w-3.5 h-3.5 text-rose-500" />
                            {{ $a->location }}
                        </span>
                        @endif
                    </div>

                    {{-- Description --}}
                    @if($a->description)
                    <div class="mt-2 text-sm text-slate-600 bg-slate-50/70 border border-slate-100 rounded-xl p-3 leading-relaxed whitespace-pre-line">
                        {{ $a->description }}
                    </div>
                    @endif
                </div>
            </div>
        </li>
        @empty
        <li class="p-16 text-center text-slate-400">
            <div class="w-14 h-14 rounded-full bg-slate-50 border border-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3 shadow-sm">
                <x-icon name="megaphone" class="w-7 h-7 text-slate-400" />
            </div>
            <div class="text-base font-bold text-slate-700">No activities found for your scope</div>
            <div class="text-xs text-slate-400 mt-1 max-w-md mx-auto">
                @if(!empty($assignedComponents))
                    There are no coordinator activities currently scheduled for your assigned component (<strong>{{ $scopeString }}</strong>). Newly created activities matching your scope will appear here automatically.
                @else
                    You currently have no assigned component or sections. Please contact your coordinator to assign your classes.
                @endif
            </div>
        </li>
        @endforelse

        <li id="noSearchMatches" class="p-14 text-center text-slate-400 hidden">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                <x-icon name="search" class="w-6 h-6" />
            </div>
            <div class="text-sm font-bold text-slate-700">No matching activities found</div>
            <div class="text-xs text-slate-400 mt-1">Try typing a different keyword, title, or location.</div>
        </li>
    </ul>

    @if($activities->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 rounded-b-xl flex items-center justify-between">
            <div class="text-xs text-slate-500 font-semibold">
                Showing {{ $activities->firstItem() }} to {{ $activities->lastItem() }} of {{ $activities->total() }} activities
            </div>
            <div>
                {{ $activities->links() }}
            </div>
        </div>
    @endif
</x-card>

@push('scripts')
<script>
    window.handleLiveSearch = function(query) {
        const term = query.toLowerCase().trim();
        const clearBtn = document.getElementById('clearSearchBtn');
        if (clearBtn) {
            if (term.length > 0) clearBtn.classList.remove('hidden');
            else clearBtn.classList.add('hidden');
        }

        const items = document.querySelectorAll('.activity-item');
        let visibleCount = 0;

        items.forEach((item) => {
            const text = item.getAttribute('data-search-text') || '';
            if (!term || text.includes(term)) {
                item.classList.remove('hidden');
                visibleCount++;
            } else {
                item.classList.add('hidden');
            }
        });

        const noMatches = document.getElementById('noSearchMatches');
        if (noMatches) {
            if (visibleCount === 0 && items.length > 0) {
                noMatches.classList.remove('hidden');
            } else {
                noMatches.classList.add('hidden');
            }
        }
    };

    window.clearLiveSearch = function() {
        const input = document.getElementById('instructorSearchInput');
        if (input) {
            input.value = '';
            handleLiveSearch('');
        }
    };
</script>
@endpush

@endsection
