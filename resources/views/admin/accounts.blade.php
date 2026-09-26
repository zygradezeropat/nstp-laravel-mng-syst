@extends('layouts.shell', [
    'themeBg' => 'bg-purple-50/60',
    'themeBdr' => 'border-purple-100',
    'brand' => 'DNSC NSTP',
    'brandSub' => 'Admin Console',
    'userName' => 'System Admin',
    'userRole' => 'System Administrator',
    'userInitials' => 'AD',
    'greeting' => 'System Administrator Console',
    'context' => 'Davao Del Norte State College'
])

@section('title', 'Admin Accounts - DNSC NSTP Portal')

@section('nav')
<div class="px-3 pt-5 pb-1.5 text-[10px] font-bold uppercase tracking-wider text-slate-400/80">Administration</div>
<a href="{{ route('admin.accounts') }}" class="w-full group flex items-center gap-3 px-3 py-2.5 rounded-lg text-left transition bg-purple-600 text-white">
    <x-icon name="users" class="w-[18px] h-[18px] text-white" />
    <span class="flex-1 text-sm">Accounts</span>
</a>
@endsection

@section('content')
<div class="grid grid-cols-1 xl:grid-cols-3 gap-6 items-start">
    <div class="xl:col-span-1 space-y-6">
        <x-card class="" title="Create Account" subtitle="Register new system coordinator or instructor">
            <x-slot name="action">
                <div class="w-10 h-10 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shadow-sm absolute -top-4 -left-4 hidden">
                    <x-icon name="userplus" class="w-5 h-5" />
                </div>
            </x-slot>


            <form method="POST" action="{{ route('admin.accounts.store') }}" class="space-y-4 text-sm">
                @csrf
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Full Name</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><x-icon name="users" class="w-4 h-4" /></span>
                        <input id="newAccName" name="name" value="{{ old('name') }}" type="text" placeholder="e.g. Dr. Juan Dela Cruz" class="w-full pl-9 pr-3 py-2.5 text-sm rounded-xl border @error('name') border-rose-300 focus:border-rose-500 focus:ring-rose-50 @else border-slate-200 focus:border-purple-500 focus:ring-purple-50 @enderror focus:ring-4 focus:outline-none transition" required />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Contact Number</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><x-icon name="bell" class="w-4 h-4" /></span>
                        <input id="newAccContact" name="contact" value="{{ old('contact') }}" type="text" placeholder="e.g. +63 917 123 4567" class="w-full pl-9 pr-3 py-2.5 text-sm rounded-xl border @error('contact') border-rose-300 focus:border-rose-500 focus:ring-rose-50 @else border-slate-200 focus:border-purple-500 focus:ring-purple-50 @enderror focus:ring-4 focus:outline-none transition" />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Gmail Address (Login Email)</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><x-icon name="mail" class="w-4 h-4" /></span>
                        <input id="newAccGmail" name="email" value="{{ old('email') }}" type="email" placeholder="e.g. j.delacruz@dnsc.edu.ph" class="w-full pl-9 pr-3 py-2.5 text-sm rounded-xl border @error('email') border-rose-300 focus:border-rose-500 focus:ring-rose-50 @else border-slate-200 focus:border-purple-500 focus:ring-purple-50 @enderror focus:ring-4 focus:outline-none transition" required />
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Degree Type</label>
                        <select id="newAccDegree" name="degree" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-50 bg-white focus:outline-none transition">
                            <option value="Bachelor" {{ old('degree') == 'Bachelor' ? 'selected' : '' }}>Bachelor</option>
                            <option value="Masteral" {{ old('degree') == 'Masteral' ? 'selected' : '' }}>Masteral</option>
                            <option value="Doctoral" {{ old('degree') == 'Doctoral' ? 'selected' : '' }}>Doctoral</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Account Role</label>
                        <select id="newAccRole" name="role" class="w-full px-3 py-2.5 text-sm rounded-xl border border-slate-200 focus:border-purple-500 focus:ring-4 focus:ring-purple-50 bg-white focus:outline-none transition">
                            <option value="coordinator" {{ old('role') == 'coordinator' ? 'selected' : '' }}>Coordinator</option>
                            <option value="instructor" {{ old('role') == 'instructor' ? 'selected' : '' }}>CWTS/LTS Instructor</option>
                            <option value="rotc" {{ old('role') == 'rotc' ? 'selected' : '' }}>ROTC Officer</option>
                            <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>System Administrator</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Degree Description / Title</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><x-icon name="grad" class="w-4 h-4" /></span>
                        <input id="newAccDegreeTitle" name="degree_title" value="{{ old('degree_title') }}" type="text" placeholder="e.g. Master of Science in Information Technology" class="w-full pl-9 pr-3 py-2.5 text-sm rounded-xl border @error('degree_title') border-rose-300 focus:border-rose-500 focus:ring-rose-50 @else border-slate-200 focus:border-purple-500 focus:ring-purple-50 @enderror focus:ring-4 focus:outline-none transition" />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Password</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><x-icon name="lock" class="w-4 h-4" /></span>
                        <input id="newAccPassword" name="password" type="password" placeholder="Create a secure password" class="w-full pl-9 pr-10 py-2.5 text-sm rounded-xl border @error('password') border-rose-300 focus:border-rose-500 focus:ring-rose-50 @else border-slate-200 focus:border-purple-500 focus:ring-purple-50 @enderror focus:ring-4 focus:outline-none transition" required />
                    </div>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1">Confirm Password</label>
                    <div class="relative">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400"><x-icon name="lock" class="w-4 h-4" /></span>
                        <input id="newAccConfirmPassword" name="password_confirmation" type="password" placeholder="Confirm your password" class="w-full pl-9 pr-10 py-2.5 text-sm rounded-xl border @error('password_confirmation') border-rose-300 focus:border-rose-500 focus:ring-rose-50 @else border-slate-200 focus:border-purple-500 focus:ring-purple-50 @enderror focus:ring-4 focus:outline-none transition" required />
                    </div>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-purple-600 hover:bg-purple-700 active:scale-95 text-white font-semibold text-sm shadow-md shadow-purple-100 transition-all">
                        <x-icon name="userplus" class="w-4 h-4" /> Create Account
                    </button>
                </div>
            </form>
        </x-card>

        <div class="premium-card p-6 border border-rose-100 bg-rose-50/10">
            <div class="flex items-center gap-3 border-b border-rose-100 pb-4 mb-5">
                <div class="w-10 h-10 rounded-xl bg-rose-500 text-white flex items-center justify-center shadow-md shadow-rose-200">
                    <x-icon name="trash" class="w-5 h-5" />
                </div>
                <div>
                    <h3 class="font-bold text-rose-900 tracking-tight text-base">Targeted Portal Reset</h3>
                    <p class="text-xs text-rose-500 mt-0.5">Wipe operational portal display data</p>
                </div>
            </div>
            <div class="space-y-4 text-xs text-slate-600 leading-relaxed">
                <p>Triggers a deep administrative database wipe across CWTS, LTS, and ROTC portals. This permanently deletes:</p>
                <ul class="list-disc list-inside space-y-1 ml-1 text-slate-500">
                    <li>Non-admin user accounts (instructors, officers)</li>
                    <li>All sections, student rosters & enrollments</li>
                    <li>Attendance, submissions & logs</li>
                    <li>Calendar activities & announcements</li>
                    <li>Activity designs & plans</li>
                </ul>
                <p class="font-semibold text-rose-800 bg-rose-50/50 p-2.5 rounded-lg border border-rose-100/50">
                    ⚠ Admin accounts, migrations, cache, and system configurations are fully preserved.
                </p>
                <button class="w-full inline-flex items-center justify-center gap-2 px-4 py-3 rounded-xl bg-rose-600 hover:bg-rose-700 active:scale-95 text-white font-semibold text-sm shadow-md shadow-rose-100 transition-all cursor-pointer">
                    <x-icon name="alertc" class="w-4 h-4" /> Reset Portal Data
                </button>
            </div>
        </div>
    </div>

    <div class="xl:col-span-2">
        <x-card class="overflow-hidden" title="Registered Accounts Registry" subtitle="Manage administrative credentials & profile details">
            <x-slot name="action">
                <div class="text-xs font-semibold px-2.5 py-1 bg-slate-50 border border-slate-200 rounded-lg text-slate-600">
                    Total: {{ count($accounts) }} User(s)
                </div>
            </x-slot>

            <x-table>
                <x-slot name="header">
                    <th class="py-3.5 px-4 font-bold">User Details</th>
                    <th class="py-3.5 px-4 font-bold">Assigned Role</th>
                    <th class="py-3.5 px-4 font-bold">Contact No</th>
                    <th class="py-3.5 px-4 font-bold">Degree Info</th>
                    <th class="py-3.5 px-4 font-bold text-right">Actions</th>
                </x-slot>

                @forelse($accounts as $acc)
                <tr class="border-b border-slate-100 hover:bg-slate-50 transition duration-150">
                    <td class="py-3.5 px-4">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br {{ $acc->grad }} text-white flex items-center justify-center font-bold text-xs shrink-0 shadow-sm">
                                {{ substr(preg_replace('/^(Dr\.|Prof\.|1Lt\.|Col\.|Capt\.|Lt\.)\s+/i', '', $acc->fullName), 0, 1) }}
                            </div>
                            <div>
                                <div class="text-sm font-semibold text-slate-900">{{ $acc->fullName }}</div>
                                <div class="text-xs text-slate-500 mt-0.5">{{ $acc->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="py-3.5 px-4">
                        @php
                        $roleColor = $acc->role === 'coordinator' ? 'bg-indigo-50 text-indigo-700 border-indigo-200'
                            : ($acc->role === 'instructor' ? 'bg-emerald-50 text-emerald-700 border-emerald-200'
                            : 'bg-slate-100 text-slate-800 border-slate-200');
                        @endphp
                        <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-semibold border {{ $roleColor }}">
                            <x-icon name="{{ $acc->ico }}" class="w-3 h-3" /> {{ $acc->label }}
                        </span>
                    </td>
                    <td class="py-3.5 px-4">
                        <div class="text-xs text-slate-700 font-medium">{{ $acc->contact }}</div>
                    </td>
                    <td class="py-3.5 px-4">
                        <div class="text-xs font-semibold text-slate-800">{{ $acc->degree }}</div>
                        <div class="text-[10px] text-slate-500 truncate max-w-[150px]" title="{{ $acc->degreeTitle }}">{{ $acc->degreeTitle }}</div>
                    </td>
                    <td class="py-3.5 px-4 text-right">
                        <div class="flex items-center justify-end gap-1.5">
                            <button class="p-1.5 rounded-lg hover:bg-purple-50 text-slate-400 hover:text-purple-600 transition duration-200" title="Edit account">
                                <x-icon name="pencil" class="w-4 h-4" />
                            </button>
                            <button class="p-1.5 rounded-lg hover:bg-rose-50 text-slate-400 hover:text-rose-600 transition duration-200" title="Delete account">
                                <x-icon name="trash" class="w-4 h-4" />
                            </button>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="py-8 text-center text-slate-400 text-sm">
                        No registered accounts found.
                    </td>
                </tr>
                @endforelse
            </x-table>
        </x-card>
    </div>
</div>
@endsection

@push('scripts')
<script>
    // Component-specific JS logic goes here
    document.addEventListener('DOMContentLoaded', () => {
        console.log('Admin accounts view initialized');
    });
</script>
@endpush
