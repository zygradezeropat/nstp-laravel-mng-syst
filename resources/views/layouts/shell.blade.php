<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'DNSC NSTP Portal')</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <script src="https://cdn.tailwindcss.com"></script>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/xlsx/0.18.5/xlsx.full.min.js"></script>

    <link rel="stylesheet" href="{{ asset('css/styles.css') }}">
    @stack('styles')
</head>
<body class="bg-slate-50 text-slate-800 min-h-screen">
    @php
        $isMilitary = $themeMilitary ?? false;
        $sidebarTextColor = $isMilitary ? 'text-white' : 'text-slate-900';
        $sidebarSubColor = $isMilitary ? 'text-slate-400' : 'text-slate-500';
        $avatarClasses = $isMilitary
            ? 'rounded-md w-9 h-9 bg-slate-800 ring-1 ring-amber-300 text-amber-300 flex items-center justify-center text-sm shrink-0'
            : 'rounded-full w-9 h-9 bg-gradient-to-br from-amber-400 to-rose-400 text-white flex items-center justify-center text-sm shrink-0';
        $logoutBtnColor = $isMilitary ? 'text-slate-400 hover:text-white' : 'text-slate-400 hover:text-slate-700';
        $brandUppercase = $isMilitary ? 'uppercase text-sm' : '';
        $subUppercase = $isMilitary ? 'uppercase tracking-wider' : '';
        $contextUppercase = $isMilitary ? 'uppercase tracking-wider' : '';
    @endphp
    <div id="app" class="min-h-screen w-full flex">
        <!-- Sidebar -->
        <aside class="transition-all duration-300 ease-in-out shrink-0 {{ $themeBg ?? 'bg-indigo-50/60' }} border-r {{ $themeBdr ?? 'border-indigo-100' }} flex flex-col h-screen sticky top-0 w-64">
            <div class="px-6 py-6 border-b {{ $themeBdr ?? 'border-indigo-100' }}">
                <div class="flex items-center gap-3">
                    <img src="/images/DSNC.png" class="w-10 h-10 object-contain" alt="DNSC Logo" />
                    <div>
                        <div class="tracking-tight {{ $brandUppercase }} {{ $sidebarTextColor }}">{{ $brand ?? 'DNSC NSTP' }}</div>
                        <div class="text-[11px] {{ $subUppercase }} {{ $sidebarSubColor }}">{{ $brandSub ?? 'Portal' }}</div>
                    </div>
                </div>
            </div>
            <nav class="flex-1 px-3 py-5 space-y-1 sidebar-nav">
                @yield('nav')
            </nav>
            <div class="px-3 py-4 border-t {{ $themeBdr ?? 'border-indigo-100' }}">
                <div class="flex items-center gap-1 rounded-md hover:bg-black/5 transition group">
                    <button class="flex items-center gap-3 px-3 py-2 flex-1 min-w-0 text-left">
                        <div class="{{ $avatarClasses }}">
                            {{ $userInitials ?? 'AD' }}
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-sm truncate {{ $sidebarTextColor }} group-hover:underline">{{ $userName ?? 'Admin' }}</div>
                            <div class="text-[11px] truncate {{ $subUppercase }} {{ $sidebarSubColor }}">{{ $userRole ?? 'System Administrator' }}</div>
                        </div>
                    </button>
                    <form method="POST" action="{{ route('logout') }}" class="shrink-0 flex">
                        @csrf
                        <button type="submit" class="{{ $logoutBtnColor }} p-2" title="Logout">
                            <x-icon name="logout" class="w-4 h-4" />
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <div class="flex-1 min-w-0 flex flex-col">
            <header class="flex items-center justify-between gap-6 px-8 py-5 bg-white/70 backdrop-blur border-b border-slate-200 sticky top-0 z-10">
                <div class="flex items-center gap-4">
                    <button id="sidebarToggleBtn" class="p-2 rounded-lg hover:bg-slate-100 text-slate-500 hover:text-slate-700 transition duration-200 focus:outline-none flex items-center justify-center shrink-0">
                        <x-icon name="menu" class="w-5 h-5" />
                    </button>
                    <div>
                        <div class="text-xs text-slate-500 {{ $contextUppercase }}">{{ $context ?? 'Davao Del Norte State College' }}</div>
                        <div class="text-slate-900 tracking-tight text-lg">{{ $greeting ?? 'Welcome back' }}</div>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <div class="relative hidden md:block">
                        <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-400">
                            <x-icon name="search" class="w-4 h-4" />
                        </span>
                        <input type="text" placeholder="Search..." class="w-72 pl-9 pr-3 py-2 text-sm rounded-lg bg-slate-100 border border-transparent focus:bg-white focus:border-slate-300 focus:outline-none" />
                    </div>
                    <button class="w-9 h-9 rounded-lg bg-white border border-slate-200 flex items-center justify-center text-slate-500 hover:text-slate-700 relative">
                        <x-icon name="bell" class="w-[18px] h-[18px]" />
                    </button>
                </div>
            </header>
            <main class="flex-1 px-8 py-7 space-y-6">
                @yield('content')
            </main>
        </div>
    </div>

    <!-- Global Custom Alert Modal -->
    <div id="globalAlertOverlay" class="fixed inset-0 z-[100] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden transition-all duration-300" onclick="if(event.target === this) closeGlobalAlertModal()">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-md mx-4 overflow-hidden transform transition-all duration-300 scale-100">
            <div class="p-6 text-center">
                <div id="globalAlertIconContainer" class="w-14 h-14 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm border border-amber-100/50">
                    <svg id="globalAlertIconWarning" class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <svg id="globalAlertIconError" class="w-7 h-7 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <svg id="globalAlertIconInfo" class="w-7 h-7 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <svg id="globalAlertIconSuccess" class="w-7 h-7 hidden" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 tracking-tight" id="globalAlertTitle">Notice</h3>
                <p class="text-sm text-slate-500 mt-2 leading-relaxed" id="globalAlertMessage">Message</p>
            </div>
            <div class="px-6 py-4 bg-slate-50/80 border-t border-slate-100 flex justify-end">
                <button type="button" onclick="closeGlobalAlertModal()" class="px-6 py-2.5 text-sm font-semibold rounded-xl bg-slate-900 text-white hover:bg-slate-800 active:scale-95 transition-all shadow-md shadow-slate-900/10 cursor-pointer">
                    OK
                </button>
            </div>
        </div>
    </div>

    <!-- Global Custom Confirmation Modal -->
    <div id="globalConfirmOverlay" class="fixed inset-0 z-[120] flex items-center justify-center bg-slate-900/40 backdrop-blur-sm hidden transition-all duration-300" onclick="if(event.target === this) closeGlobalConfirmModal(false)">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-md mx-4 overflow-hidden transform transition-all duration-300 scale-100">
            <div class="p-6 text-center">
                <div id="globalConfirmIconContainer" class="w-14 h-14 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm border border-rose-100/50">
                    <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-slate-800 tracking-tight" id="globalConfirmTitle">Confirm Action</h3>
                <p class="text-sm text-slate-500 mt-2 leading-relaxed" id="globalConfirmMessage">Are you sure you want to proceed?</p>
            </div>
            <div class="px-6 py-4 bg-slate-50/80 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" id="globalConfirmCancelBtn" onclick="closeGlobalConfirmModal(false)" class="px-5 py-2.5 text-sm font-semibold rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition-all cursor-pointer">
                    Cancel
                </button>
                <button type="button" id="globalConfirmOkBtn" onclick="closeGlobalConfirmModal(true)" class="px-5 py-2.5 text-sm font-semibold rounded-xl bg-rose-600 text-white hover:bg-rose-700 active:scale-95 transition-all shadow-md shadow-rose-600/20 cursor-pointer">
                    Confirm
                </button>
            </div>
        </div>
    </div>

    <!-- Global Progress Execution Modal -->
    <div id="globalProgressOverlay" class="fixed inset-0 z-[110] flex items-center justify-center bg-slate-900/50 backdrop-blur-md hidden transition-all duration-300">
        <div class="bg-white rounded-2xl shadow-2xl border border-slate-100 w-full max-w-md mx-4 p-6 text-center transform transition-all duration-300 scale-100 space-y-5">
            <div class="relative w-16 h-16 mx-auto flex items-center justify-center">
                <div class="absolute inset-0 rounded-full border-4 border-indigo-100 animate-pulse"></div>
                <div class="absolute inset-0 rounded-full border-4 border-t-indigo-600 border-r-transparent border-b-transparent border-l-transparent animate-spin"></div>
                <svg class="w-7 h-7 text-indigo-600 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                </svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-slate-800 tracking-tight" id="globalProgressTitle">Processing Execution...</h3>
                <p class="text-xs text-slate-500 mt-1" id="globalProgressSubtitle">Please wait while the system processes your request.</p>
            </div>
            <!-- Progress Bar -->
            <div class="space-y-1.5">
                <div class="w-full bg-slate-100 rounded-full h-2.5 overflow-hidden border border-slate-100">
                    <div id="globalProgressBarFill" class="bg-gradient-to-r from-indigo-500 to-blue-600 h-2.5 rounded-full transition-all duration-300" style="width: 15%"></div>
                </div>
                <div class="flex items-center justify-between text-[11px] font-semibold text-slate-500">
                    <span id="globalProgressStatusText">Initializing action...</span>
                    <span id="globalProgressPercentText">15%</span>
                </div>
            </div>
        </div>
    </div>

    <script>
        let currentAlertConfirmCallback = null;
        let currentConfirmCallback = null;

        window.showAlertModal = function(message, title = 'Notice', type = 'warning', onConfirm = null) {
            currentAlertConfirmCallback = onConfirm;
            const overlay = document.getElementById('globalAlertOverlay');
            if (!overlay) {
                console.warn(message);
                if (typeof onConfirm === 'function') onConfirm();
                return;
            }
            document.getElementById('globalAlertTitle').innerText = title || 'Notice';
            document.getElementById('globalAlertMessage').innerText = message;

            const iconContainer = document.getElementById('globalAlertIconContainer');
            const iconWarning = document.getElementById('globalAlertIconWarning');
            const iconError = document.getElementById('globalAlertIconError');
            const iconInfo = document.getElementById('globalAlertIconInfo');
            const iconSuccess = document.getElementById('globalAlertIconSuccess');

            iconWarning.classList.add('hidden');
            iconError.classList.add('hidden');
            iconInfo.classList.add('hidden');
            iconSuccess.classList.add('hidden');

            if (type === 'error') {
                iconContainer.className = 'w-14 h-14 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm border border-rose-100/50';
                iconError.classList.remove('hidden');
            } else if (type === 'info') {
                iconContainer.className = 'w-14 h-14 bg-sky-50 text-sky-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm border border-sky-100/50';
                iconInfo.classList.remove('hidden');
            } else if (type === 'success') {
                iconContainer.className = 'w-14 h-14 bg-emerald-50 text-emerald-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm border border-emerald-100/50';
                iconSuccess.classList.remove('hidden');
            } else {
                iconContainer.className = 'w-14 h-14 bg-amber-50 text-amber-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm border border-amber-100/50';
                iconWarning.classList.remove('hidden');
            }

            overlay.classList.remove('hidden');
        };

        window.closeGlobalAlertModal = function() {
            const overlay = document.getElementById('globalAlertOverlay');
            if (overlay) overlay.classList.add('hidden');
            if (typeof currentAlertConfirmCallback === 'function') {
                const cb = currentAlertConfirmCallback;
                currentAlertConfirmCallback = null;
                cb();
            }
        };

        window.showConfirmModal = function(optionsOrMessage, onConfirm = null, onCancel = null) {
            let message = '';
            let title = 'Confirm Action';
            let confirmText = 'Confirm';
            let cancelText = 'Cancel';
            let isDanger = true;

            if (typeof optionsOrMessage === 'object' && optionsOrMessage !== null) {
                message = optionsOrMessage.message || '';
                title = optionsOrMessage.title || title;
                confirmText = optionsOrMessage.confirmText || confirmText;
                cancelText = optionsOrMessage.cancelText || cancelText;
                if (optionsOrMessage.isDanger === false) isDanger = false;
                if (optionsOrMessage.onConfirm) onConfirm = optionsOrMessage.onConfirm;
                if (optionsOrMessage.onCancel) onCancel = optionsOrMessage.onCancel;
            } else {
                message = String(optionsOrMessage || '');
            }

            currentConfirmCallback = { onConfirm, onCancel };

            const overlay = document.getElementById('globalConfirmOverlay');
            if (!overlay) {
                if (confirm(message)) {
                    if (typeof onConfirm === 'function') onConfirm();
                } else {
                    if (typeof onCancel === 'function') onCancel();
                }
                return;
            }

            document.getElementById('globalConfirmTitle').innerText = title;
            document.getElementById('globalConfirmMessage').innerText = message;
            
            const okBtn = document.getElementById('globalConfirmOkBtn');
            const cancelBtn = document.getElementById('globalConfirmCancelBtn');
            const iconContainer = document.getElementById('globalConfirmIconContainer');
            
            if (okBtn) okBtn.innerText = confirmText;
            if (cancelBtn) cancelBtn.innerText = cancelText;

            if (isDanger) {
                if (okBtn) okBtn.className = 'px-5 py-2.5 text-sm font-semibold rounded-xl bg-rose-600 text-white hover:bg-rose-700 active:scale-95 transition-all shadow-md shadow-rose-600/20 cursor-pointer';
                if (iconContainer) iconContainer.className = 'w-14 h-14 bg-rose-50 text-rose-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm border border-rose-100/50';
            } else {
                if (okBtn) okBtn.className = 'px-5 py-2.5 text-sm font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 active:scale-95 transition-all shadow-md shadow-indigo-600/20 cursor-pointer';
                if (iconContainer) iconContainer.className = 'w-14 h-14 bg-indigo-50 text-indigo-500 rounded-2xl flex items-center justify-center mx-auto mb-4 shadow-sm border border-indigo-100/50';
            }

            overlay.classList.remove('hidden');
        };

        window.closeGlobalConfirmModal = function(confirmed) {
            const overlay = document.getElementById('globalConfirmOverlay');
            if (overlay) overlay.classList.add('hidden');
            if (currentConfirmCallback) {
                const { onConfirm, onCancel } = currentConfirmCallback;
                currentConfirmCallback = null;
                if (confirmed) {
                    if (typeof onConfirm === 'function') onConfirm();
                } else {
                    if (typeof onCancel === 'function') onCancel();
                }
            }
        };

        window.showProgressModal = function(title = 'Processing Execution...', subtitle = 'Please wait while the system processes your request.', initialPercent = 25) {
            const overlay = document.getElementById('globalProgressOverlay');
            if (!overlay) return;
            document.getElementById('globalProgressTitle').innerText = title;
            document.getElementById('globalProgressSubtitle').innerText = subtitle;
            window.updateProgressModal(initialPercent, 'Executing requested action...');
            overlay.classList.remove('hidden');
        };

        window.updateProgressModal = function(percent, statusMessage) {
            const fill = document.getElementById('globalProgressBarFill');
            const percentTxt = document.getElementById('globalProgressPercentText');
            const statusTxt = document.getElementById('globalProgressStatusText');
            if (fill) fill.style.width = Math.min(100, Math.max(0, percent)) + '%';
            if (percentTxt) percentTxt.innerText = Math.min(100, Math.max(0, percent)) + '%';
            if (statusTxt && statusMessage) statusTxt.innerText = statusMessage;
        };

        window.closeProgressModal = function() {
            const overlay = document.getElementById('globalProgressOverlay');
            if (overlay) overlay.classList.add('hidden');
        };

        window.finishProgressModal = function(title, message, type = 'success', onConfirm = null) {
            window.updateProgressModal(100, 'Execution completed!');
            setTimeout(() => {
                window.closeProgressModal();
                window.showAlertModal(message, title, type, onConfirm);
            }, 300);
        };

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeGlobalAlertModal();
                closeGlobalConfirmModal(false);
                closeProgressModal();
            }
        });

        // Intercept native form submit with onsubmit="return confirm(...)"
        document.addEventListener('submit', function(e) {
            const form = e.target;
            if (!form) return;
            if (form.dataset && form.dataset.confirming) {
                delete form.dataset.confirming;
                return;
            }
            const onsubmitAttr = form.getAttribute('onsubmit');
            if (onsubmitAttr && onsubmitAttr.includes('confirm(')) {
                let msg = 'Are you sure you want to perform this action?';
                const match = onsubmitAttr.match(/confirm\s*\(\s*(['"])(.*?)\1\s*\)/);
                if (match) {
                    msg = match[2].replace(/\\'/g, "'").replace(/\\"/g, '"');
                }
                e.preventDefault();
                e.stopImmediatePropagation();
                window.showConfirmModal({
                    title: 'Confirm Delete',
                    message: msg,
                    confirmText: 'Delete',
                    cancelText: 'Cancel',
                    isDanger: true,
                    onConfirm: () => {
                        form.dataset.confirming = 'true';
                        if (window.showProgressModal) {
                            const title = form.dataset.progressTitle || 'Processing Action...';
                            const subtitle = form.dataset.progressSubtitle || 'Please wait while the system processes your request.';
                            window.showProgressModal(title, subtitle, 35);
                        }
                        form.submit();
                    }
                });
                return false;
            }
        }, true);

        // Override native window.alert to present clean modal UI
        window.alert = function(message) {
            window.showAlertModal(message);
        };
    </script>

    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.showAlertModal(@json(session('success')), 'Success', 'success');
        });
    </script>
    @endif

    @if(session('error'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            window.showAlertModal(@json(session('error')), 'Execution Error', 'error');
        });
    </script>
    @endif

    @yield('scripts')
    @stack('scripts')
</body>
</html>
