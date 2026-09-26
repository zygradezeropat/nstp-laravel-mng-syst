@extends('layouts.coordinator')

@section('title', 'Announcements - Coordinator Console')

@section('content')

<x-page-header title="Official Announcements" subtitle="Publish campus updates, official memos, and notices across NSTP programs">
    <x-slot name="actions">
        <div class="flex items-center gap-3">
            <form action="{{ route('coordinator.announcements') }}" method="GET" class="relative" onsubmit="return true;">
                <x-icon name="search" class="w-4 h-4 text-slate-400 absolute left-3 top-1/2 -translate-y-1/2" />
                <input type="text" id="announcementSearchInput" name="search" value="{{ request('search') }}"
                       placeholder="Search announcements..."
                       oninput="handleLiveSearch(this.value)"
                       class="pl-9 pr-8 py-2 text-sm rounded-lg bg-white border border-slate-200 w-64 shadow-sm focus:outline-none focus:border-indigo-300 transition" />
                <button type="button" id="clearSearchBtn" onclick="clearLiveSearch()"
                        class="absolute right-2.5 top-1/2 -translate-y-1/2 text-slate-400 hover:text-slate-600 text-xs font-bold {{ request('search') ? '' : 'hidden' }}"
                        title="Clear search">&times;</button>
            </form>

            <button onclick="openCreateAnnouncementModal()" class="inline-flex items-center gap-1.5 px-4 py-2 text-sm font-semibold rounded-lg bg-indigo-600 text-white hover:bg-indigo-700 transition shadow-sm cursor-pointer shrink-0">
                <x-icon name="plus" class="w-4 h-4" /> New Announcement
            </button>
        </div>
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 md:grid-cols-3 gap-5 mt-6">
    <x-card class="p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0">
            <x-icon name="send" class="w-6 h-6" />
        </div>
        <div>
            <div class="text-2xl font-extrabold text-slate-800">{{ $totalCount ?? $announcements->total() }}</div>
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-0.5">Total Announcements</div>
        </div>
    </x-card>
    
    <x-card class="p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
            <x-icon name="pin" class="w-6 h-6" />
        </div>
        <div>
            <div class="text-2xl font-extrabold text-slate-800">{{ $pinnedCount ?? 0 }}</div>
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-0.5">Pinned Memos</div>
        </div>
    </x-card>

    <x-card class="p-5 flex items-center gap-4">
        <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
            <x-icon name="calendar" class="w-6 h-6" />
        </div>
        <div>
            <div class="text-2xl font-extrabold text-slate-800">{{ $calendarCount ?? 0 }}</div>
            <div class="text-xs font-semibold text-slate-500 uppercase tracking-wider mt-0.5">Calendar Announcements</div>
        </div>
    </x-card>
</div>

<x-card class="mt-6">
    <div class="p-6 border-b border-slate-100 flex items-center justify-between">
        <h3 class="text-base font-bold text-slate-800">Published Announcements</h3>
        <div class="text-xs text-slate-500 font-semibold" id="announcementCounter">
            Showing {{ $announcements->firstItem() ?? 0 }} - {{ $announcements->lastItem() ?? 0 }} of {{ $announcements->total() }} notices
        </div>
    </div>

    <ul class="divide-y divide-slate-100" id="announcementsList">
        @forelse($announcements as $a)
        <li class="announcement-item p-6 hover:bg-slate-50/50 transition duration-150"
            data-search-text="{{ strtolower($a->title . ' ' . $a->content . ' ' . ($a->source ?? '') . ' ' . ($a->component ?? '') . ' ' . ($a->target_role ?? '')) }}">
            <div class="flex items-start justify-between gap-4">
                <div class="flex items-start gap-4 min-w-0">
                    <div class="w-10 h-10 shrink-0 rounded-full bg-gradient-to-br from-amber-500 to-orange-500 text-white flex items-center justify-center text-xs font-bold shadow-sm">
                        <x-icon name="send" class="w-4 h-4" />
                    </div>
                    <div class="min-w-0 space-y-1.5">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-xs font-bold text-slate-700">{{ $a->source ?? 'NSTP Office' }}</span>
                            <span class="w-1 h-1 rounded-full bg-slate-300 inline-block"></span>
                            <span class="text-xs text-slate-400">{{ $a->created_at ? $a->created_at->diffForHumans() : 'Recently' }}</span>
                            
                            @if($a->is_pinned)
                                <span class="inline-flex items-center gap-1 text-[10px] uppercase tracking-wider font-extrabold text-amber-700 bg-amber-50 border border-amber-200 px-2 py-0.5 rounded-full">
                                    <x-icon name="pin" class="w-3 h-3" /> Pinned
                                </span>
                            @endif
                            
                            <span class="text-[10px] font-extrabold px-2 py-0.5 rounded-full border {{ match($a->component ?? 'General') { 'CWTS' => 'bg-indigo-50 text-indigo-700 border-indigo-200', 'LTS' => 'bg-emerald-50 text-emerald-700 border-emerald-200', 'ROTC' => 'bg-rose-50 text-rose-700 border-rose-200', default => 'bg-blue-50 text-blue-700 border-blue-200' } }}">
                                {{ $a->component ?? 'General' }}
                            </span>
                            
                            <span class="text-[10px] font-semibold px-2 py-0.5 rounded-full bg-slate-100 text-slate-600 border border-slate-200">
                                Target: {{ $a->target_role ?? 'All' }}
                            </span>

                            @if($a->scheduled_date)
                                <span class="text-[10px] font-bold px-2 py-0.5 rounded-full bg-indigo-50 text-indigo-700 border border-indigo-200 flex items-center gap-1">
                                    <x-icon name="calendar" class="w-3 h-3" /> Calendar Date: {{ $a->scheduled_date->format('M d, Y') }}
                                </span>
                            @endif
                        </div>
                        
                        <h4 class="text-base font-bold text-slate-900 leading-snug">{{ $a->title }}</h4>
                        <div class="text-sm text-slate-600 whitespace-pre-line leading-relaxed">{{ $a->content }}</div>
                    </div>
                </div>

                <div class="flex items-center gap-2 shrink-0">
                    <form action="{{ route('coordinator.announcements.delete', $a->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this announcement?');" data-progress-title="Deleting Announcement" data-progress-subtitle="Removing update notice...">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="p-2 rounded-lg text-slate-400 hover:text-rose-600 hover:bg-rose-50 transition cursor-pointer" title="Delete Announcement">
                            <x-icon name="trash" class="w-4 h-4" />
                        </button>
                    </form>
                </div>
            </div>
        </li>
        @empty
        <li class="p-12 text-center text-slate-400">
            <div class="w-12 h-12 rounded-full bg-slate-100 text-slate-400 flex items-center justify-center mx-auto mb-3">
                <x-icon name="send" class="w-6 h-6" />
            </div>
            <div class="text-sm font-bold text-slate-700">No announcements published yet</div>
            <div class="text-xs text-slate-400 mt-1">Click "+ New Announcement" above to post official updates.</div>
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
        <div class="px-6 py-4 border-t border-slate-100 bg-slate-50/50 rounded-b-2xl flex items-center justify-between">
            <div class="text-xs text-slate-500 font-semibold">
                Showing {{ $announcements->firstItem() }} to {{ $announcements->lastItem() }} of {{ $announcements->total() }} announcements
            </div>
            <div>
                {{ $announcements->links() }}
            </div>
        </div>
    @endif
</x-card>

<!-- Create Announcement Modal -->
<div id="createAnnouncementOverlay" class="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden transition duration-300" onclick="if(event.target === this) closeCreateAnnouncementModal()">
    <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-lg mx-4 overflow-hidden transform scale-95 duration-300">
        <form action="{{ route('coordinator.announcements.store') }}" method="POST" data-progress-title="Publishing Announcement" data-progress-subtitle="Notifying instructors and program users...">
            @csrf
            <div class="px-6 py-5 border-b border-slate-100 flex items-center justify-between bg-gradient-to-r from-amber-600 to-orange-600 text-white">
                <div>
                    <div class="font-extrabold tracking-tight text-lg">New Announcement</div>
                    <div class="text-xs text-white/80 mt-0.5">Publish an official update across NSTP programs & calendar</div>
                </div>
                <button type="button" class="text-white/70 hover:text-white p-1 rounded-lg hover:bg-white/10 transition cursor-pointer" onclick="closeCreateAnnouncementModal()">
                    <x-icon name="close" class="w-5 h-5" />
                </button>
            </div>
            
            <div class="p-6 space-y-4 text-sm max-h-[70vh] overflow-y-auto">
                <div>
                    <label class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 block">Announcement Title <span class="text-rose-500">*</span></label>
                    <input type="text" name="title" required placeholder="e.g. Midterm Examination Schedule / Campus Clean-Up Drive" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-4 focus:ring-amber-100 focus:border-amber-400 transition" />
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 block">Target Audience</label>
                        <select name="target_role" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-4 focus:ring-amber-100 focus:border-amber-400 transition">
                            <option value="All" selected>All Roles (Everyone)</option>
                            <option value="Instructors">Instructors Only</option>
                            <option value="ROTC Officers">ROTC Officers Only</option>
                            <option value="Students">Students Only</option>
                        </select>
                    </div>
                    <div>
                        <label class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 block">Program Component</label>
                        <select name="component" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-4 focus:ring-amber-100 focus:border-amber-400 transition">
                            <option value="General" selected>General (All Programs)</option>
                            <option value="CWTS">CWTS</option>
                            <option value="LTS">LTS</option>
                            <option value="ROTC">ROTC</option>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 block">Calendar Scheduled Date <span class="text-slate-400 font-normal">(Optional)</span></label>
                        <input type="date" name="scheduled_date" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-4 focus:ring-amber-100 focus:border-amber-400 transition" />
                    </div>
                    <div>
                        <label class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 block">Source / Issuing Office</label>
                        <input type="text" name="source" value="NSTP Director's Office" class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-4 focus:ring-amber-100 focus:border-amber-400 transition" />
                    </div>
                </div>

                <div>
                    <label class="text-xs text-slate-500 font-bold uppercase tracking-wider mb-1.5 block">Announcement Content / Body <span class="text-rose-500">*</span></label>
                    <textarea name="content" rows="4" required placeholder="Write the full announcement body, instructions, or details..." class="w-full px-3 py-2.5 rounded-xl border border-slate-200 focus:outline-none focus:ring-4 focus:ring-amber-100 focus:border-amber-400 transition"></textarea>
                </div>

                <div class="flex items-center gap-2 pt-2">
                    <input type="checkbox" name="is_pinned" id="is_pinned" value="1" class="w-4 h-4 rounded text-amber-600 focus:ring-amber-500 border-slate-300 cursor-pointer" />
                    <label for="is_pinned" class="text-xs font-bold text-slate-700 cursor-pointer">Pin this announcement to top</label>
                </div>
            </div>

            <div class="px-6 py-4 border-t border-slate-100 flex items-center justify-end gap-3 bg-slate-50 rounded-b-2xl">
                <button type="button" class="px-4 py-2 text-sm font-semibold rounded-xl border border-slate-200 text-slate-700 hover:bg-slate-100 transition cursor-pointer" onclick="closeCreateAnnouncementModal()">Cancel</button>
                <button type="submit" class="inline-flex items-center gap-2 px-5 py-2 text-sm font-semibold rounded-xl bg-amber-600 text-white hover:bg-amber-700 shadow-md transition cursor-pointer">
                    <x-icon name="send" class="w-4 h-4" /> Publish Announcement
                </button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
    window.openCreateAnnouncementModal = function() {
        document.getElementById('createAnnouncementOverlay').classList.remove('hidden');
    };
    window.closeCreateAnnouncementModal = function() {
        document.getElementById('createAnnouncementOverlay').classList.add('hidden');
    };

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
        const input = document.getElementById('announcementSearchInput');
        if (input) {
            input.value = '';
            handleLiveSearch('');
        }
    };
</script>
@endpush

@endsection
