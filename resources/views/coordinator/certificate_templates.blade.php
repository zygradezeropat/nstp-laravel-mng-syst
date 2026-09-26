@extends('layouts.coordinator')

@section('title', 'Certificate Design Templates - DNSC NSTP Portal')

@section('content')
<div class="space-y-6" id="certificate-templates-container">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-2">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900">Certificate Design Templates</h1>
            <p class="text-sm text-slate-500 mt-1">Create, edit, and manage high-fidelity certificate templates for different NSTP components</p>
        </div>
        <button onclick="openCreateTemplateModal()" class="inline-flex items-center gap-1.5 px-4 py-2.5 text-sm font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-md hover:shadow-indigo-100 hover:-translate-y-0.5 transition-all duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-4 h-4">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg> New Design Template
        </button>
    </div>


    <!-- Templates Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
        @forelse($templates as $template)
        <div class="bg-white rounded-2xl border border-slate-100 shadow-sm overflow-hidden flex flex-col justify-between hover:shadow-md hover:border-slate-200/80 transition-all duration-300">
            <div>
                <!-- Mockup Scaled Visualization of Theme -->
                <div class="p-4 bg-slate-50 border-b border-slate-100 relative h-36 flex items-center justify-center overflow-hidden">
                    <!-- Miniature Watermark -->
                    <div class="absolute w-20 h-20 rounded-full border border-dashed opacity-5 select-none pointer-events-none flex items-center justify-center font-bold text-[8px]">DNSC NSTP</div>
                    
                    <!-- Miniature Certificate Border & Inner Border based on custom background or default -->
                    @php
                        $borderClass = '';
                        $bgStyle = '';
                        $titleColor = '';
                        
                        if ($template->bg_image) {
                            $bgStyle = 'background-image: url(' . asset($template->bg_image) . '); background-size: 100% 100%; border: none;';
                        } else {
                            $borderClass = 'border-4 border-indigo-900 double bg-white';
                            $titleColor = 'text-indigo-950 font-serif';
                        }
                    @endphp

                    <div class="w-full h-full rounded-lg border-2 {{ $borderClass }} p-3 flex flex-col items-center justify-between text-center relative z-10" style="{{ $bgStyle }}">
                        <span class="text-[6px] tracking-widest font-extrabold uppercase text-slate-400">Republic of the Philippines</span>
                        <h4 class="text-[8px] font-bold tracking-wide {{ $titleColor ?: 'text-indigo-950 font-serif' }} uppercase my-0.5 leading-none">{{ $template->title_text }}</h4>
                        <p class="text-[4px] text-slate-400 leading-tight max-w-[80%] truncate">This is to certify that [STUDENT_NAME] has completed...</p>
                        <div class="w-full flex justify-between items-end mt-1 px-2">
                            <div class="text-[3px] text-slate-400 border-t border-slate-200 pt-0.5 w-12 text-center">{{ $template->signatory_name }}</div>
                            <div class="text-[3px] text-slate-400 border-t border-slate-200 pt-0.5 w-12 text-center">Date Issued</div>
                        </div>
                    </div>
                </div>

                <div class="p-5 space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs px-2.5 py-0.5 rounded-full font-bold uppercase {{ $template->component === 'ROTC' ? 'bg-rose-50 text-rose-700' : ($template->component === 'LTS' ? 'bg-emerald-50 text-emerald-700' : ($template->component === 'CWTS' ? 'bg-indigo-50 text-indigo-700' : 'bg-slate-100 text-slate-700')) }}">
                            {{ $template->component }}
                        </span>
                        @if($template->is_active)
                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-emerald-600">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span> Active
                        </span>
                        @else
                        <span class="inline-flex items-center gap-1 text-xs font-semibold text-slate-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-slate-300"></span> Inactive
                        </span>
                        @endif
                    </div>
                    
                    <h3 class="font-bold text-slate-800 text-base leading-snug group-hover:text-indigo-600 transition">{{ $template->name }}</h3>
                    <div class="text-xs text-slate-500 space-y-1 bg-slate-50 p-2.5 rounded-xl border border-slate-100">
                        @if($template->bg_image)
                        <div class="truncate text-indigo-600 font-semibold"><strong class="text-slate-700">Design:</strong> Custom PNG Attached</div>
                        @else
                        <div><strong class="text-slate-700">Design:</strong> Default Border Template</div>
                        @endif
                        <div class="truncate"><strong class="text-slate-700">Signatory:</strong> {{ $template->signatory_name }} ({{ $template->signatory_title }})</div>
                    </div>
                </div>
            </div>

            <!-- Actions Panel -->
            <div class="px-5 py-3 border-t border-slate-50 bg-slate-50/50 flex justify-between items-center gap-2">
                <button onclick="openEditTemplateModal({{ $template->id }})" class="flex-1 inline-flex items-center justify-center gap-1 py-1.5 px-3 rounded-lg border border-slate-200 bg-white text-xs font-bold text-slate-600 hover:bg-slate-50 hover:text-indigo-600 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-3.5 h-3.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.07a4.5 4.5 0 01-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 011.13-1.897l8.932-8.931zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0115.75 21H5.25A2.25 2.25 0 013 18.75V8.25A2.25 2.25 0 015.25 6H10" />
                    </svg> Edit Template
                </button>
                <form action="{{ route('coordinator.certificate_templates.destroy', $template->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to delete this template?')" class="inline-block shrink-0">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="p-1.5 rounded-lg border border-rose-200 text-rose-600 hover:bg-rose-50 transition" title="Delete Template">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
        @empty
        <div class="col-span-full py-16 text-center text-slate-500 bg-white rounded-2xl border border-dashed border-slate-200">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 mx-auto text-slate-300 mb-3">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            <p class="font-semibold text-slate-700">No Design Templates Found</p>
            <p class="text-sm text-slate-400 mt-1 max-w-sm mx-auto">Build customized layout options with custom signatory profiles and elegant themes.</p>
        </div>
        @endforelse
    </div>
</div>

<!-- ========================================== -->
<!-- CREATE / EDIT TEMPLATE INTERACTIVE MODAL   -->
<!-- ========================================== -->
<div id="templateModal" class="hidden fixed inset-0 z-50 flex items-center justify-center bg-slate-900/60 backdrop-blur-sm p-4 overflow-y-auto">
    <div class="bg-white rounded-2xl shadow-xl w-full max-w-5xl overflow-hidden flex flex-col md:flex-row min-h-[500px] border border-slate-100 animate-in fade-in zoom-in duration-200">
        
        <!-- Left Side: Interactive Creation Form -->
        <div class="flex-1 p-6 md:p-8 space-y-5 overflow-y-auto max-h-[85vh]">
            <div class="flex justify-between items-center pb-3 border-b border-slate-100">
                <h2 id="modalTitle" class="text-lg font-bold text-slate-900">New Design Template</h2>
                <button onclick="closeTemplateModal()" class="p-1 rounded-lg hover:bg-slate-100 text-slate-400 hover:text-slate-600 transition">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>

            <!-- Form -->
            <form id="templateForm" method="POST" enctype="multipart/form-data" class="space-y-4">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">

                <div class="grid grid-cols-2 gap-4">
                    <!-- Template Name -->
                    <div class="col-span-2">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Template Name</label>
                        <input id="inputName" type="text" name="name" required placeholder="e.g. CWTS Official Roster Template" class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 focus:border-indigo-300 focus:outline-none focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>

                    <!-- Component -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Program / Component</label>
                        <select id="inputComponent" name="component" required class="w-full px-3 py-2 text-sm rounded-xl border border-slate-200 focus:border-indigo-300 focus:outline-none focus:ring-4 focus:ring-indigo-50 transition">
                            <option value="CWTS">CWTS</option>
                            <option value="LTS">LTS</option>
                            <option value="ROTC">ROTC</option>
                            <option value="ALL">ALL Programs</option>
                        </select>
                    </div>

                    <!-- Custom Background Image PNG Upload -->
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">
                            Background Image Design (.png, .jpg)
                            <span class="text-[9px] text-slate-400 font-normal lowercase">(Optional)</span>
                        </label>
                        <input id="inputBgImage" type="file" name="bg_image" accept=".png,.jpg,.jpeg" class="w-full px-3.5 py-1 text-xs rounded-xl border border-slate-200 focus:border-indigo-300 focus:outline-none transition file:mr-3 file:py-1 file:px-2.5 file:rounded-lg file:border-0 file:text-[10px] file:font-bold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer" />
                        <div id="editBgImagePreview" class="hidden text-[10px] text-slate-500 mt-1 flex items-center gap-1.5">
                            <span>Current:</span>
                            <a id="currentBgImageLink" href="#" target="_blank" class="text-indigo-600 hover:underline font-semibold truncate max-w-[150px]">image.png</a>
                        </div>
                    </div>
                </div>

                <!-- Certificate Title -->
                <div>
                    <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Certificate Header Title</label>
                    <input id="inputTitle" type="text" name="title_text" required placeholder="e.g. Certificate of Completion" class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 focus:border-indigo-300 focus:outline-none focus:ring-4 focus:ring-indigo-50 transition" />
                </div>

                <!-- Certificate Body -->
                <div>
                    <div class="flex justify-between items-center mb-1.5">
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider">Certificate Body Statement</label>
                        <div class="text-[10px] text-slate-400 font-medium">Use tags: [STUDENT_NAME], [SECTION], [SCHOOL_YEAR], [SERIAL_NO]</div>
                    </div>
                    <textarea id="inputBody" name="body_text" required rows="4" placeholder="This is to certify that [STUDENT_NAME] has..." class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 focus:border-indigo-300 focus:outline-none focus:ring-4 focus:ring-indigo-50 transition font-sans leading-relaxed"></textarea>
                </div>

                <!-- Signatories -->
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Signatory Full Name</label>
                        <input id="inputSignatoryName" type="text" name="signatory_name" required placeholder="e.g. Dr. Emil F. Briones" class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 focus:border-indigo-300 focus:outline-none focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 uppercase tracking-wider mb-1.5">Signatory Subtitle / Title</label>
                        <input id="inputSignatoryTitle" type="text" name="signatory_title" required placeholder="e.g. NSTP Coordinator" class="w-full px-3.5 py-2 text-sm rounded-xl border border-slate-200 focus:border-indigo-300 focus:outline-none focus:ring-4 focus:ring-indigo-50 transition" />
                    </div>
                </div>

                <!-- Active Status (Only on Edit) -->
                <div id="activeStatusWrapper" class="hidden items-center gap-2.5 py-1">
                    <input id="inputActive" type="checkbox" name="is_active" value="1" checked class="w-4 h-4 text-indigo-600 border-slate-200 rounded focus:ring-indigo-500 transition" />
                    <label for="inputActive" class="text-sm font-semibold text-slate-700">Keep this certificate template active</label>
                </div>

                <div class="pt-3 border-t border-slate-100 flex items-center justify-end gap-3">
                    <button type="button" onclick="closeTemplateModal()" class="px-4 py-2 text-sm font-semibold rounded-xl border border-slate-200 bg-white text-slate-700 hover:bg-slate-50 transition">Cancel</button>
                    <button type="submit" class="px-5 py-2 text-sm font-semibold rounded-xl bg-indigo-600 text-white hover:bg-indigo-700 shadow-sm transition">Save Template</button>
                </div>
            </form>
        </div>

        <!-- Right Side: Interactive Live Canvas Preview (WOW effect) -->
        <div class="w-full md:w-[420px] bg-slate-950 p-6 md:p-8 flex flex-col justify-center border-l border-slate-800 relative overflow-hidden">
            <!-- Dynamic ambient background -->
            <div class="absolute -right-20 -top-20 w-44 h-44 rounded-full bg-indigo-600/10 blur-3xl"></div>
            
            <div class="relative z-10 w-full">
                <div class="text-xs uppercase font-extrabold tracking-wider text-slate-500 mb-3 text-center">Live Preview Canvas</div>
                
                <!-- Live Certificate container (renders live inputs) -->
                <div id="liveCertBox" class="w-full aspect-[1.414/1] rounded-xl border-4 border-slate-800 bg-white flex flex-col items-center justify-between text-center p-5 shadow-2xl relative transition-all duration-300">


                    <!-- Subtle Watermark -->
                    <div id="liveCertWatermark" class="absolute inset-0 flex items-center justify-center opacity-[0.03] select-none pointer-events-none">
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" class="w-24 h-24 text-slate-900">
                            <path d="M11.644 1.59a.75.75 0 01.712 0l9.75 5.25a.75.75 0 010 1.32l-9.75 5.25a.75.75 0 01-.712 0l-9.75-5.25a.75.75 0 010-1.32l9.75-5.25z" />
                            <path d="M3.265 10.602l7.668 4.129a1.25 1.25 0 001.134 0l7.668-4.13 1.565.843a.75.75 0 010 1.32l-9.75 5.25a.75.75 0 01-.712 0l-9.75-5.25a.75.75 0 010-1.32l1.565-.843z" />
                            <path d="M3.265 14.352l7.668 4.129a1.25 1.25 0 001.134 0l7.668-4.13 1.565.843a.75.75 0 010 1.32l-9.75 5.25a.75.75 0 01-.712 0l-9.75-5.25a.75.75 0 010-1.32l1.565-.843z" />
                        </svg>
                    </div>

                    <!-- Mini Header -->
                    <div class="flex flex-col items-center">
                        <span class="text-[6px] tracking-widest font-black uppercase text-slate-400">Davao del Norte State College</span>
                        <span class="text-[4px] tracking-wider text-slate-400 uppercase leading-none mt-0.5">National Service Training Program</span>
                    </div>

                    <!-- Dynamic Title -->
                    <h3 id="liveTitle" class="text-xs font-serif font-bold text-slate-800 uppercase tracking-wide leading-none mt-1">Certificate of Completion</h3>
                    
                    <!-- Dynamic Body -->
                    <div class="w-full px-2 mt-1.5">
                        <p id="liveBody" class="text-[6px] text-slate-500 leading-relaxed font-serif break-words">
                            This is to certify that <strong class="text-slate-800">[STUDENT_NAME]</strong> has successfully completed the Civic Welfare Training Service component.
                        </p>
                    </div>

                    <!-- Signatory -->
                    <div class="w-full flex justify-between items-end mt-2 px-4 relative z-10">
                        <div class="flex flex-col items-center">
                            <!-- Dynamic Signature Mock -->
                            <div class="font-serif italic text-[7px] text-indigo-700/80 -rotate-3 select-none leading-none -mb-0.5" id="liveSigMock">E. Briones</div>
                            <span id="liveSignatoryName" class="text-[5px] font-bold text-slate-800 uppercase border-t border-slate-200 pt-0.5 w-16 text-center">DR. EMIL F. BRIONES</span>
                            <span id="liveSignatoryTitle" class="text-[3px] text-slate-400 leading-none mt-0.5 w-20 text-center truncate">NSTP Coordinator</span>
                        </div>
                        <div class="flex flex-col items-center">
                            <span class="text-[5px] font-medium text-slate-600 border-t border-slate-200 pt-0.5 w-16 text-center">May 31, 2026</span>
                            <span class="text-[3px] text-slate-400 leading-none mt-0.5 text-center">Date Issued</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', () => {
        const modal = document.getElementById('templateModal');
        const form = document.getElementById('templateForm');
        const modalTitle = document.getElementById('modalTitle');
        const formMethod = document.getElementById('formMethod');
        
        // Input fields
        const inputName = document.getElementById('inputName');
        const inputComponent = document.getElementById('inputComponent');
        const inputBgImage = document.getElementById('inputBgImage');
        const inputTitle = document.getElementById('inputTitle');
        const inputBody = document.getElementById('inputBody');
        const inputSignatoryName = document.getElementById('inputSignatoryName');
        const inputSignatoryTitle = document.getElementById('inputSignatoryTitle');
        const inputActive = document.getElementById('inputActive');
        const activeStatusWrapper = document.getElementById('activeStatusWrapper');
        
        // Live Preview elements
        const liveCertBox = document.getElementById('liveCertBox');
        const liveCertWatermark = document.getElementById('liveCertWatermark');
        const liveTitle = document.getElementById('liveTitle');
        const liveBody = document.getElementById('liveBody');
        const liveSignatoryName = document.getElementById('liveSignatoryName');
        const liveSignatoryTitle = document.getElementById('liveSignatoryTitle');
        const liveSigMock = document.getElementById('liveSigMock');

        // Theme properties helper (Always defaults to classic styles behind the scenes)
        const applyThemeStyles = () => {
            liveCertBox.className = "w-full aspect-[1.414/1] rounded-xl flex flex-col items-center justify-between text-center p-5 shadow-2xl relative transition-all duration-300";
            
            // Check if there is an active custom background image loaded or selected
            const hasBgImage = liveCertBox.style.backgroundImage && liveCertBox.style.backgroundImage !== 'none';
            
            if (hasBgImage) {
                liveCertBox.style.borderStyle = 'none';
                liveCertBox.style.backgroundSize = '100% 100%';
                if (liveCertWatermark) liveCertWatermark.style.display = 'none';
            } else {
                liveCertBox.style.borderStyle = 'solid';
                liveCertBox.style.borderWidth = '6px';
                if (liveCertWatermark) liveCertWatermark.style.display = 'flex';
                liveCertBox.classList.add('border-indigo-950', 'bg-white');
            }
            
            liveTitle.className = "text-xs font-serif font-bold text-indigo-950 uppercase tracking-wide leading-none mt-1";
            liveSigMock.className = "font-serif italic text-[7px] text-indigo-700/80 -rotate-3 select-none leading-none -mb-0.5";
        };

        // Render functions for Live Preview
        const updateLivePreview = () => {
            // Apply title
            liveTitle.textContent = inputTitle.value || 'Certificate of Completion';
            
            // Apply body
            let bodyText = inputBody.value || 'This is to certify that [STUDENT_NAME] has successfully completed the program during the school year [SCHOOL_YEAR] in section [SECTION].';
            // Prettify placeholders
            bodyText = bodyText
                .replace(/\[STUDENT_NAME\]/g, '<span class="text-[7px] font-black text-slate-800">Juan Dela Cruz</span>')
                .replace(/\[SECTION\]/g, '<span class="font-bold text-slate-800">CWTS-1A</span>')
                .replace(/\[SCHOOL_YEAR\]/g, '<span class="font-bold text-slate-800">2025-2026</span>')
                .replace(/\[DATE\]/g, '<span class="font-bold text-slate-800">May 31, 2026</span>')
                .replace(/\[SERIAL_NO\]/g, '<span class="font-bold text-slate-800">2026-0001</span>')
                .replace(/\[STUDENT_NO\]/g, '<span class="font-bold text-slate-800">2026-0001</span>');
            liveBody.innerHTML = bodyText;

            // Signatory
            liveSignatoryName.textContent = (inputSignatoryName.value || 'Dr. Emil F. Briones').toUpperCase();
            liveSignatoryTitle.textContent = inputSignatoryTitle.value || 'NSTP Coordinator';

            // Signatory Initials for mock signature
            const nameParts = (inputSignatoryName.value || 'E Briones').split(' ');
            let initials = 'Sig';
            if (nameParts.length > 0) {
                const last = nameParts[nameParts.length - 1];
                const first = nameParts[0].charAt(0);
                initials = `${first}. ${last}`;
            }
            liveSigMock.textContent = initials;

            // Apply Theme Styles (always uses classic border layout when no custom PNG is loaded)
            applyThemeStyles();
        };

        // Attach live input hooks
        const inputHooks = [inputTitle, inputBody, inputSignatoryName, inputSignatoryTitle];
        inputHooks.forEach(input => {
            if (input) {
                input.addEventListener('input', updateLivePreview);
                input.addEventListener('change', updateLivePreview);
            }
        });

        // Background Image Selection Handler for instant live preview
        inputBgImage.addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const url = URL.createObjectURL(file);
                liveCertBox.style.backgroundImage = `url(${url})`;
                liveCertBox.style.backgroundSize = '100% 100%';
                liveCertBox.style.borderStyle = 'none';
                if (liveCertWatermark) liveCertWatermark.style.display = 'none';
            } else {
                liveCertBox.style.backgroundImage = 'none';
                liveCertBox.style.borderStyle = 'solid';
                liveCertBox.style.borderWidth = '6px';
                if (liveCertWatermark) liveCertWatermark.style.display = 'flex';
            }
            updateLivePreview();
        });

        // Global functions
        window.openCreateTemplateModal = () => {
            form.reset();
            form.action = '/coordinator/certificate-templates';
            formMethod.value = 'POST';
            modalTitle.textContent = 'New Design Template';
            activeStatusWrapper.classList.add('hidden');
            
            // Clear file inputs & previews
            inputBgImage.value = '';
            document.getElementById('editBgImagePreview').classList.add('hidden');
            liveCertBox.style.backgroundImage = 'none';
            liveCertBox.style.borderStyle = 'solid';
            liveCertBox.style.borderWidth = '6px';
            if (liveCertWatermark) liveCertWatermark.style.display = 'flex';

            // Default template details to start preview cleanly
            inputTitle.value = 'Certificate of Completion';
            inputBody.value = 'This is to certify that [STUDENT_NAME] has successfully completed the Civic Welfare Training Service (CWTS) component of the National Service Training Program (NSTP) during the school year [SCHOOL_YEAR] in section [SECTION], in compliance with Republic Act No. 9163.';
            inputSignatoryName.value = 'Dr. Emil F. Briones';
            inputSignatoryTitle.value = 'NSTP Coordinator';
            
            updateLivePreview();
            modal.classList.remove('hidden');
        };

        window.openEditTemplateModal = (id) => {
            form.reset();
            form.action = `/coordinator/certificate-templates/${id}`;
            formMethod.value = 'PUT';
            modalTitle.textContent = 'Edit Design Template';
            activeStatusWrapper.classList.remove('hidden');
            
            // Clear current file inputs
            inputBgImage.value = '';

            fetch(`/coordinator/certificate-templates/${id}`)
                .then(r => r.json())
                .then(template => {
                    inputName.value = template.name;
                    inputComponent.value = template.component;
                    inputTitle.value = template.title_text;
                    inputBody.value = template.body_text;
                    inputSignatoryName.value = template.signatory_name;
                    inputSignatoryTitle.value = template.signatory_title;
                    inputActive.checked = template.is_active;

                    const previewDiv = document.getElementById('editBgImagePreview');
                    const link = document.getElementById('currentBgImageLink');
                    if (template.bg_image) {
                        previewDiv.classList.remove('hidden');
                        link.href = template.bg_image;
                        link.textContent = template.bg_image.split('/').pop();
                        
                        // Set in live preview
                        liveCertBox.style.backgroundImage = `url(${template.bg_image})`;
                        liveCertBox.style.backgroundSize = '100% 100%';
                        liveCertBox.style.borderStyle = 'none';
                        if (liveCertWatermark) liveCertWatermark.style.display = 'none';
                    } else {
                        previewDiv.classList.add('hidden');
                        liveCertBox.style.backgroundImage = 'none';
                        liveCertBox.style.borderStyle = 'solid';
                        liveCertBox.style.borderWidth = '6px';
                        if (liveCertWatermark) liveCertWatermark.style.display = 'flex';
                    }

                    updateLivePreview();
                    modal.classList.remove('hidden');
                })
                .catch(err => {
                    console.error('Fetch error:', err);
                    alert('Could not retrieve template details.');
                });
        };

        window.closeTemplateModal = () => {
            modal.classList.add('hidden');
        };

        // Close on backdrop click
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeTemplateModal();
            }
        });
    });
</script>
@endpush
