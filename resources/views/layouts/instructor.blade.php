@extends('layouts.shell', [
    'themeBg' => 'bg-[#f4faf7]',
    'themeBdr' => 'border-emerald-150',
    'brand' => 'DNSC NSTP',
    'brandSub' => 'Instructor Console',
    'userName' => auth()->user() ? auth()->user()->name : 'NSTP Instructor',
    'userRole' => 'Course Instructor',
    'userInitials' => auth()->user() ? strtoupper(substr(auth()->user()->name, 0, 2)) : 'NI',
    'greeting' => 'Instructor Console',
    'context' => 'Davao Del Norte State College'
])

@section('nav')
@php
    $instructorId = auth()->id();
    $plansCount = \App\Models\ActivityPlan::where('instructor_id', $instructorId)
        ->whereIn('status', ['Draft', 'Pending', 'Revision', 'Rejected', 'draft', 'pending', 'revision', 'rejected'])
        ->count();
    $reportsCount = \App\Models\AccomplishmentReport::where('instructor_id', $instructorId)
        ->whereIn('status', ['Draft', 'Pending', 'Revision', 'draft', 'pending', 'revision'])
        ->count();
@endphp
<div class="space-y-6">
    {{-- Core Menu --}}
    <div class="space-y-1">
        <a href="{{ route('instructor.dashboard') }}" class="w-full group flex items-center gap-3 px-3 py-2 rounded-lg text-left transition {{ request()->routeIs('instructor.dashboard') ? 'bg-[#0d9472] text-white shadow-sm font-semibold' : 'text-slate-600 hover:bg-[#ebf5f0]' }}">
            <x-icon name="grid" class="w-[18px] h-[18px] transition {{ request()->routeIs('instructor.dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-[#0d9472]' }}" />
            <span class="flex-1 text-sm font-medium">Overview</span>
        </a>
        <a href="{{ route('instructor.classes') }}" class="w-full group flex items-center gap-3 px-3 py-2 rounded-lg text-left transition {{ request()->routeIs('instructor.classes') ? 'bg-[#0d9472] text-white shadow-sm font-semibold' : 'text-slate-600 hover:bg-[#ebf5f0]' }}">
            <x-icon name="book" class="w-[18px] h-[18px] transition {{ request()->routeIs('instructor.classes') ? 'text-white' : 'text-slate-400 group-hover:text-[#0d9472]' }}" />
            <span class="flex-1 text-sm font-medium">My Classes</span>
        </a>
    </div>

    {{-- Planning & Reports --}}
    <div>
        <div class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400/80">Planning & Reports</div>
        <div class="space-y-1">
            <a href="{{ route('instructor.plans') }}" class="w-full group flex items-center gap-3 px-3 py-2 rounded-lg text-left transition {{ request()->routeIs('instructor.plans') ? 'bg-[#0d9472] text-white shadow-sm font-semibold' : 'text-slate-600 hover:bg-[#ebf5f0]' }}">
                <x-icon name="clipboard" class="w-[18px] h-[18px] transition {{ request()->routeIs('instructor.plans') ? 'text-white' : 'text-slate-400 group-hover:text-[#0d9472]' }}" />
                <span class="flex-1 text-sm font-medium">Activity Plans</span>
                <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded border {{ request()->routeIs('instructor.plans') ? 'border-emerald-500 bg-[#077d5f] text-white' : 'border-slate-200 bg-white text-slate-400' }}">{{ $plansCount }}</span>
            </a>
            <a href="{{ route('instructor.reports') }}" class="w-full group flex items-center gap-3 px-3 py-2 rounded-lg text-left transition {{ request()->routeIs('instructor.reports') ? 'bg-[#0d9472] text-white shadow-sm font-semibold' : 'text-slate-600 hover:bg-[#ebf5f0]' }}">
                <x-icon name="filetext" class="w-[18px] h-[18px] transition {{ request()->routeIs('instructor.reports') ? 'text-white' : 'text-slate-400 group-hover:text-[#0d9472]' }}" />
                <span class="flex-1 text-sm font-medium">Accomplishment Reports</span>
                <span class="px-1.5 py-0.5 text-[10px] font-semibold rounded border {{ request()->routeIs('instructor.reports') ? 'border-emerald-500 bg-[#077d5f] text-white' : 'border-slate-200 bg-white text-slate-400' }}">{{ $reportsCount }}</span>
            </a>
        </div>
    </div>

    {{-- Updates --}}
    <div>
        <div class="px-3 pb-2 text-[10px] font-bold uppercase tracking-wider text-slate-400/80">Updates</div>
        <div class="space-y-1">
{{-- 
            <a href="{{ route('instructor.announcements') }}" class="w-full group flex items-center gap-3 px-3 py-2 rounded-lg text-left transition {{ request()->routeIs('instructor.announcements') ? 'bg-[#0d9472] text-white shadow-sm font-semibold' : 'text-slate-600 hover:bg-[#ebf5f0]' }}">
                <x-icon name="send" class="w-[18px] h-[18px] transition {{ request()->routeIs('instructor.announcements') ? 'text-white' : 'text-slate-400 group-hover:text-[#0d9472]' }}" />
                <span class="flex-1 text-sm font-medium">Announcements</span>
            </a>
--}}
            <a href="{{ route('instructor.calendar') }}" class="w-full group flex items-center gap-3 px-3 py-2 rounded-lg text-left transition {{ request()->routeIs('instructor.calendar') ? 'bg-[#0d9472] text-white shadow-sm font-semibold' : 'text-slate-600 hover:bg-[#ebf5f0]' }}">
                <x-icon name="calendar" class="w-[18px] h-[18px] transition {{ request()->routeIs('instructor.calendar') ? 'text-white' : 'text-slate-400 group-hover:text-[#0d9472]' }}" />
                <span class="flex-1 text-sm font-medium">Calendar</span>
            </a>
        </div>
    </div>
</div>
@endsection
