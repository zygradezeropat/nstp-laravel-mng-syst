@extends('layouts.shell', [
    'themeBg' => 'bg-indigo-50/60',
    'themeBdr' => 'border-indigo-100',
    'brand' => 'DNSC NSTP',
    'brandSub' => 'Coordinator Console',
    'userName' => auth()->user() ? auth()->user()->name : 'NSTP Coordinator',
    'userRole' => 'Program Coordinator',
    'userInitials' => auth()->user() ? strtoupper(substr(auth()->user()->name, 0, 2)) : 'NC',
    'greeting' => 'Program Coordinator Console',
    'context' => 'Davao Del Norte State College'
])

@section('nav')
<a href="{{ route('coordinator.dashboard') }}" class="w-full group flex items-center gap-3 px-3 py-2.5 rounded-lg text-left transition {{ request()->routeIs('coordinator.dashboard') ? 'bg-indigo-600 text-white' : 'hover:bg-black/5 text-slate-700' }}">
    <x-icon name="dashboard" class="w-[18px] h-[18px] transition {{ request()->routeIs('coordinator.dashboard') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-600' }}" />
    <span class="flex-1 text-sm {{ request()->routeIs('coordinator.dashboard') ? '' : 'group-hover:font-medium' }}">Dashboard</span>
</a>
<a href="{{ route('coordinator.sections') }}" class="w-full group flex items-center gap-3 px-3 py-2.5 rounded-lg text-left transition {{ request()->routeIs('coordinator.sections') ? 'bg-indigo-600 text-white' : 'hover:bg-black/5 text-slate-700' }}">
    <x-icon name="users" class="w-[18px] h-[18px] transition {{ request()->routeIs('coordinator.sections') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-600' }}" />
    <span class="flex-1 text-sm {{ request()->routeIs('coordinator.sections') ? '' : 'group-hover:font-medium' }}">Sections & Students</span>
</a>
<a href="{{ route('coordinator.instructors') }}" class="w-full group flex items-center gap-3 px-3 py-2.5 rounded-lg text-left transition {{ request()->routeIs('coordinator.instructors') ? 'bg-indigo-600 text-white' : 'hover:bg-black/5 text-slate-700' }}">
    <x-icon name="grad" class="w-[18px] h-[18px] transition {{ request()->routeIs('coordinator.instructors') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-600' }}" />
    <span class="flex-1 text-sm {{ request()->routeIs('coordinator.instructors') ? '' : 'group-hover:font-medium' }}">Instructors Management</span>
</a>

<div class="px-3 pt-5 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400/80">REPORTS & PLAN</div>
<a href="{{ route('coordinator.approvals') }}" class="w-full group flex items-center gap-3 px-3 py-2.5 rounded-lg text-left transition {{ request()->routeIs('coordinator.approvals') ? 'bg-indigo-600 text-white' : 'hover:bg-black/5 text-slate-700' }}">
    <x-icon name="filecheck" class="w-[18px] h-[18px] transition {{ request()->routeIs('coordinator.approvals') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-600' }}" />
    <span class="flex-1 text-sm {{ request()->routeIs('coordinator.approvals') ? '' : 'group-hover:font-medium' }}">Report & Activity Approvals</span>
</a>
<a href="{{ route('coordinator.calendar') }}" class="w-full group flex items-center gap-3 px-3 py-2.5 rounded-lg text-left transition {{ request()->routeIs('coordinator.calendar') ? 'bg-indigo-600 text-white' : 'hover:bg-black/5 text-slate-700' }}">
    <x-icon name="calendar" class="w-[18px] h-[18px] transition {{ request()->routeIs('coordinator.calendar') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-600' }}" />
    <span class="flex-1 text-sm {{ request()->routeIs('coordinator.calendar') ? '' : 'group-hover:font-medium' }}">Activity Calendar</span>
</a>
{{-- 
<a href="{{ route('coordinator.announcements') }}" class="w-full group flex items-center gap-3 px-3 py-2.5 rounded-lg text-left transition {{ request()->routeIs('coordinator.announcements') ? 'bg-indigo-600 text-white' : 'hover:bg-black/5 text-slate-700' }}">
    <x-icon name="send" class="w-[18px] h-[18px] transition {{ request()->routeIs('coordinator.announcements') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-600' }}" />
    <span class="flex-1 text-sm {{ request()->routeIs('coordinator.announcements') ? '' : 'group-hover:font-medium' }}">Announcements</span>
</a>
--}}
<a href="{{ route('coordinator.reports') }}" class="w-full group flex items-center gap-3 px-3 py-2.5 rounded-lg text-left transition {{ request()->routeIs('coordinator.reports') ? 'bg-indigo-600 text-white' : 'hover:bg-black/5 text-slate-700' }}">
    <x-icon name="filetext" class="w-[18px] h-[18px] transition {{ request()->routeIs('coordinator.reports') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-600' }}" />
    <span class="flex-1 text-sm {{ request()->routeIs('coordinator.reports') ? '' : 'group-hover:font-medium' }}">Generate Report</span>
</a>

<div class="px-3 pt-5 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400/80">GRADES</div>
<a href="{{ route('coordinator.ocr') }}" class="w-full group flex items-center gap-3 px-3 py-2.5 rounded-lg text-left transition {{ request()->routeIs('coordinator.ocr') ? 'bg-indigo-600 text-white' : 'hover:bg-black/5 text-slate-700' }}">
    <x-icon name="scan" class="w-[18px] h-[18px] transition {{ request()->routeIs('coordinator.ocr') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-600' }}" />
    <span class="flex-1 text-sm {{ request()->routeIs('coordinator.ocr') ? '' : 'group-hover:font-medium' }}">OCR Grade</span>
</a>
<a href="{{ route('coordinator.certificates') }}" class="w-full group flex items-center gap-3 px-3 py-2.5 rounded-lg text-left transition {{ request()->routeIs('coordinator.certificates') ? 'bg-indigo-600 text-white' : 'hover:bg-black/5 text-slate-700' }}">
    <x-icon name="award" class="w-[18px] h-[18px] transition {{ request()->routeIs('coordinator.certificates') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-600' }}" />
    <span class="flex-1 text-sm {{ request()->routeIs('coordinator.certificates') ? '' : 'group-hover:font-medium' }}">Certificates</span>
</a>
<a href="{{ route('coordinator.certificate_templates') }}" class="w-full group flex items-center gap-3 px-3 py-2.5 rounded-lg text-left transition {{ request()->routeIs('coordinator.certificate_templates') ? 'bg-indigo-600 text-white' : 'hover:bg-black/5 text-slate-700' }}">
    <x-icon name="image" class="w-[18px] h-[18px] transition {{ request()->routeIs('coordinator.certificate_templates') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-600' }}" />
    <span class="flex-1 text-sm {{ request()->routeIs('coordinator.certificate_templates') ? '' : 'group-hover:font-medium' }}">Certificate Templates</span>
</a>

<div class="px-3 pt-5 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400/80">HISTORY</div>
<a href="{{ route('coordinator.archive') }}" class="w-full group flex items-center gap-3 px-3 py-2.5 rounded-lg text-left transition {{ request()->routeIs('coordinator.archive') ? 'bg-indigo-600 text-white' : 'hover:bg-black/5 text-slate-700' }}">
    <x-icon name="archive" class="w-[18px] h-[18px] transition {{ request()->routeIs('coordinator.archive') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-600' }}" />
    <span class="flex-1 text-sm {{ request()->routeIs('coordinator.archive') ? '' : 'group-hover:font-medium' }}">Student Archive</span>
</a>
<a href="{{ route('coordinator.audit') }}" class="w-full group flex items-center gap-3 px-3 py-2.5 rounded-lg text-left transition {{ request()->routeIs('coordinator.audit') ? 'bg-indigo-600 text-white' : 'hover:bg-black/5 text-slate-700' }}">
    <x-icon name="clipboard" class="w-[18px] h-[18px] transition {{ request()->routeIs('coordinator.audit') ? 'text-white' : 'text-slate-400 group-hover:text-indigo-600' }}" />
    <span class="flex-1 text-sm {{ request()->routeIs('coordinator.audit') ? '' : 'group-hover:font-medium' }}">Audit Logs</span>
</a>
@endsection
