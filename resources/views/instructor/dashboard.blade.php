@extends('layouts.instructor')

@section('title', 'Dashboard - Instructor Console')

@section('content')

<x-page-header title="Instructor Overview" subtitle="Quick snapshot of your assigned classes and pending tasks">
</x-page-header>


<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mt-6">
    @foreach($stats as $s)
    <div class="premium-card p-5 bg-white border border-slate-100 rounded-2xl shadow-sm hover:shadow-md transition duration-200">
        <div class="flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br {{ $s['color'] }} flex items-center justify-center text-white shadow shrink-0">
                <x-icon name="{{ $s['ico'] }}" class="w-6 h-6" />
            </div>
            <div class="min-w-0">
                <div class="text-xs font-bold uppercase tracking-wider text-slate-400 truncate">{{ $s['label'] }}</div>
                <div class="text-slate-900 tracking-tight text-3xl font-extrabold mt-0.5">{{ $s['value'] }}</div>
            </div>
        </div>
        <div class="text-xs text-slate-500 font-medium mt-4 pt-3.5 border-t border-slate-50 flex items-center gap-1.5">
            <span class="w-1.5 h-1.5 rounded-full bg-indigo-500 inline-block shrink-0"></span>
            {!! $s['sub'] !!}
        </div>
    </div>
    @endforeach
</div>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-6">
    <!-- Reports & Plans Tracker -->
    <div class="lg:col-span-2">
        <x-card title="Pending Submissions" subtitle="Activity plans and reports awaiting action or revisions">
            <ul class="divide-y divide-slate-100">
                @forelse($pendingSubmissions as $item)
                <li class="px-5 py-4 flex items-center justify-between gap-4 hover:bg-slate-50/50 cursor-pointer transition">
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-sm font-bold text-slate-900 truncate hover:text-indigo-600 transition">{{ $item->title }}</span>
                            <span class="capitalize text-[10px] px-2 py-0.5 rounded-md font-extrabold tracking-wider bg-indigo-50 text-indigo-600 border border-indigo-100">{{ $item->type === 'plan' ? 'Activity Plan' : 'Accomplishment Report' }}</span>
                        </div>
                        <div class="text-xs text-slate-500 mt-1.5 flex items-center gap-2.5 flex-wrap font-medium">
                            <span class="inline-flex items-center gap-1"><x-icon name="book" class="w-3.5 h-3.5 text-slate-400" />{{ $item->section }}</span>
                            <span class="w-1 h-1 rounded-full bg-slate-300 shrink-0"></span>
                            <span class="inline-flex items-center gap-1"><x-icon name="calendar" class="w-3.5 h-3.5 text-slate-400" />{{ $item->date }}</span>
                        </div>
                    </div>
                    <div class="shrink-0 flex items-center gap-3">
                        @if(in_array($item->status, ['Approved', 'Reviewed']))
                            <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full bg-emerald-50 text-emerald-700 font-semibold border border-emerald-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> {{ $item->status }}
                            </span>
                        @elseif($item->status === 'Draft')
                            <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full bg-slate-100 text-slate-600 font-semibold border border-slate-200">
                                <span class="w-1.5 h-1.5 rounded-full bg-slate-400"></span> Draft
                            </span>
                        @elseif(in_array($item->status, ['Revision', 'Revisions', 'Rejected']))
                            <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full bg-rose-50 text-rose-700 font-semibold border border-rose-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-rose-500"></span> {{ $item->status }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-xs px-2.5 py-1 rounded-full bg-amber-50 text-amber-700 font-semibold border border-amber-100">
                                <span class="w-1.5 h-1.5 rounded-full bg-amber-500"></span> {{ $item->status }}
                            </span>
                        @endif
                        <x-icon name="chevron" class="w-4 h-4 text-slate-300 -rotate-90" />
                    </div>
                </li>
                @empty
                <li class="py-16 text-center">
                    <div class="w-16 h-16 rounded-full bg-emerald-50 flex items-center justify-center mx-auto text-emerald-500 shadow-sm">
                        <x-icon name="check2" class="w-8 h-8" />
                    </div>
                    <div class="text-slate-900 font-bold tracking-tight text-base mt-4">All Caught Up!</div>
                    <div class="text-slate-400 text-xs mt-1 max-w-sm mx-auto">No pending activity plans or accomplishment reports require your attention right now. All submissions are current.</div>
                </li>
                @endforelse
            </ul>
        </x-card>
    </div>

    <!-- Calendar Widget -->
    <div class="lg:col-span-1">
        <x-card class="p-5">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <div class="text-slate-900 font-bold tracking-tight text-sm">Calendar of Activities</div>
                    <div class="text-[11px] text-slate-500 mt-0.5">Two-week preview</div>
                </div>
                <span class="inline-flex items-center gap-1 text-[11px] text-slate-500 font-medium">
                    <x-icon name="calendar" class="w-3.5 h-3.5 text-slate-400" /> May 2026
                </span>
            </div>
            
            <div class="grid grid-cols-7 gap-1.5">
                <!-- Mock calendar grid tailored to current month -->
                @foreach([
                    ['d' => 14, 'label' => 'TUE', 'events' => ['bg-indigo-500']],
                    ['d' => 15, 'label' => 'WED', 'events' => []],
                    ['d' => 16, 'label' => 'THU', 'events' => []],
                    ['d' => 17, 'label' => 'FRI', 'events' => []],
                    ['d' => 18, 'label' => 'SAT', 'events' => ['bg-emerald-500', 'bg-emerald-500']],
                    ['d' => 19, 'label' => 'SUN', 'events' => []],
                    ['d' => 20, 'label' => 'MON', 'events' => []],
                    ['d' => 21, 'label' => 'TUE', 'events' => []],
                    ['d' => 22, 'label' => 'WED', 'events' => []],
                    ['d' => 23, 'label' => 'THU', 'events' => []],
                    ['d' => 24, 'label' => 'FRI', 'events' => []],
                    ['d' => 25, 'label' => 'SAT', 'events' => ['bg-emerald-500']],
                    ['d' => 26, 'label' => 'SUN', 'events' => []],
                    ['d' => 27, 'label' => 'MON', 'events' => []],
                ] as $day)
                <div class="border border-slate-100 rounded-xl p-2 min-h-[90px] bg-slate-50/50 transition duration-200 text-center flex flex-col justify-between {{ count($day['events']) > 0 ? 'cursor-pointer hover:bg-white hover:border-indigo-200 hover:shadow-md' : '' }}">
                    <div>
                        <div class="text-[8px] uppercase tracking-wider text-slate-400 font-semibold">{{ $day['label'] }}</div>
                        <div class="text-slate-900 tracking-tight text-base font-bold mt-0.5">{{ $day['d'] }}</div>
                    </div>
                    <div class="mt-2 flex items-center justify-center gap-0.5 flex-wrap">
                        @foreach($day['events'] as $color)
                            <span class="w-2 h-2 rounded-full {{ $color }} cursor-pointer hover:scale-125 transition inline-block"></span>
                        @endforeach
                    </div>
                </div>
                @endforeach
            </div>
        </x-card>
    </div>
</div>

@endsection
