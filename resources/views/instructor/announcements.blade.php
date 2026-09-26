@extends('layouts.instructor')

@section('title', 'Announcements - Instructor Console')

@section('content')

<x-page-header title="Announcements" subtitle="Official updates from the NSTP & Director's offices">
    <x-slot name="actions">
        <form action="{{ route('instructor.announcements') }}" method="GET" class="relative" onsubmit="return true;">
            <x-icon name="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
            <input type="text" id="instructorSearchInput" name="search" value="{{ request('search') }}"
                   placeholder="Search announcements..."
                   oninput="handleLiveSearch(this.value)"
                   class="pl-9 pr-8 py-2 text-sm rounded-lg bg-white border border-slate-200 w-64 shadow-sm focus:outline-none focus:border-indigo-300 transition" />
            <button type="button" id="clearSearchBtn" onclick="clearLiveSearch()"
                    class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs font-bold {{ request('search') ? '' : 'hidden' }}"
                    title="Clear search">&times;</button>
        </form>
    </x-slot>
</x-page-header>

<x-card class="mt-6">
    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-base font-bold text-slate-800">Official Updates & Memos</h3>
        <div class="text-xs text-slate-500 font-semibold" id="announcementCounter">
            Showing {{ $announcements->firstItem() ?? 0 }} - {{ $announcements->lastItem() ?? 0 }} of {{ $announcements->total() }} notices
        </div>
    </div>

    <ul class="divide-y divide-slate-100" id="announcementsList">
        @forelse($announcements as $a)
        <li class="announcement-item p-6 hover:bg-slate-50/50 transition duration-150"
            data-search-text="{{ strtolower($a->title . ' ' . $a->content . ' ' . ($a->source ?? '') . ' ' . ($a->component ?? '') . ' ' . ($a->target_role ?? '')) }}">
            <div class="flex items-start gap-4">
                <div class="w-10 h-10 shrink-0 rounded-full bg-gradient-to-br from-amber-500 to-orange-500 text-white flex items-center justify-center text-xs font-bold shadow-sm">
                    {{ strtoupper(substr($a->source ?? 'NSTP', 0, 2)) }}
                </div>
                <div class="flex-1 min-w-0 space-y-1.5">
                    <div class="flex items-center gap-2 flex-wrap text-xs text-slate-500">
                        <span class="text-slate-700 font-semibold">{{ $a->source ?? 'NSTP Office' }}</span>
                        <span class="w-1 h-1 rounded-full bg-slate-300 inline-block"></span>
                        <span>{{ $a->created_at ? $a->created_at->diffForHumans() : 'Recently' }}</span>
                        
                        @if($a->is_pinned)
                            <span class="ml-auto inline-flex items-center gap-1 text-[10px] uppercase tracking-wider font-extrabold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">
                                <x-icon name="pin" class="w-3 h-3" /> Pinned
                            </span>
                        @endif

                        <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full border {{ match($a->component ?? 'General') { 'CWTS' => 'bg-indigo-50 text-indigo-700 border-indigo-200', 'LTS' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'ROTC' => 'bg-rose-50 text-rose-700 border-rose-200', default => 'bg-blue-50 text-blue-700 border-blue-200' } }}">
                            {{ $a->component ?? 'General' }}
                        </span>

                        @if($a->scheduled_date)
                            <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center gap-1">
                                <x-icon name="calendar" class="w-3 h-3" /> Scheduled: {{ $a->scheduled_date->format('M d, Y') }}
                            </span>
                        @endif
                    </div>
                    <h4 class="text-base font-bold text-slate-900 leading-snug">{{ $a->title }}</h4>
                    <p class="text-sm text-slate-600 whitespace-pre-line leading-relaxed">{{ $a->content }}</p>
                </div>
            </div>
        </li>
        @empty
        <li class="p-12 text-center text-slate-400">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                <x-icon name="send" class="w-6 h-6" />
            </div>
            <div class="text-sm font-bold text-slate-700">No announcements found</div>
            <div class="text-xs text-slate-400 mt-1">There are no official updates posted at this time.</div>
        </li>
        @endforelse

        <li id="noSearchMatches" class="p-12 text-center text-slate-400 hidden">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                <x-icon name="search" class="w-6 h-6" />
            </div>
            <div class="text-sm font-bold text-slate-700">No matching announcements found</div>
            <div class="text-xs text-slate-400 mt-1">Try typing a different keyword or title.</div>
        </li>
    </ul>

    @if($announcements->hasPages())
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 rounded-b-xl flex items-center justify-between">
            <div class="text-xs text-slate-500 font-semibold">
                Showing {{ $announcements->firstItem() }} to {{ $announcements->lastItem() }} of {{ $announcements->total() }} announcements
            </div>
            <div>
                {{ $announcements->links() }}
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

        const items = document.querySelectorAll('.announcement-item');
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

        const counter = document.getElementById('announcementCounter');
        if (counter) {
            if (term.length > 0) {
                counter.innerText = `Showing ${visibleCount} matching ${visibleCount === 1 ? 'notice' : 'notices'}`;
            } else {
                counter.innerText = `Showing {{ $announcements->firstItem() ?? 0 }} - {{ $announcements->lastItem() ?? 0 }} of {{ $announcements->total() }} notices`;
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
