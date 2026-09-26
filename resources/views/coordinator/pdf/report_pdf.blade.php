@php
    $footerImgPath = public_path('images/footer.sample.png');
    $footerBase64 = '';
    if (file_exists($footerImgPath)) {
        $footerBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($footerImgPath));
    }
    
    $logoImgPath = public_path('images/DSNC.png');
    $logoBase64 = '';
    if (file_exists($logoImgPath)) {
        $logoBase64 = 'data:image/png;base64,' . base64_encode(file_get_contents($logoImgPath));
    }
@endphp
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $reportTitle }}</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        @page {
            size: letter portrait;
            margin: 35mm 12mm 34mm 12mm;
        }

        body {
            font-family: "Helvetica", "Arial", sans-serif;
            font-size: 8pt;
            color: #0f172a;
            background: #ffffff;
            line-height: 1.25;
            margin-top: 30mm;
            margin-bottom: 26mm;
        }

        /* ── Fixed Institution Header at Top of Every Page ── */
        header {
            position: fixed;
            top: 5mm;
            left: 0;
            right: 0;
            height: 24mm;
            z-index: 9999;
        }

        /* ── Fixed Wall-to-Wall Page Footer Banner at Bottom of Every Page ── */
        footer {
            position: fixed;
            bottom: 0mm;
            left: -12mm;
            right: -12mm;
            height: 22mm;
            width: 216mm;
            text-align: center;
            z-index: 9999;
        }

        footer img {
            width: 100%;
            height: 22mm;
            display: block;
        }

        /* ── Centered Document Wrapper (creates margins on each side to emphasize table) ── */
        .document-wrapper {
            width: 91%;
            margin: 0 auto;
            padding-top: 1px;
        }

        /* ── Header Table inside Fixed Header ── */
        .header-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2px;
        }

        .header-table td {
            vertical-align: middle;
        }

        .logo-img {
            width: 48px;
            height: 48px;
        }

        .college-name {
            font-size: 12.5pt;
            font-weight: bold;
            color: #047857; /* DNSC Primary Green */
            text-transform: uppercase;
            letter-spacing: 0.3px;
            line-height: 1.1;
        }

        .college-sub {
            font-size: 7.5pt;
            color: #047857;
            font-weight: bold;
            margin-top: 2px;
        }

        .contact-info {
            text-align: right;
            font-size: 7pt;
            color: #047857;
            font-weight: bold;
            line-height: 1.35;
            white-space: nowrap;
        }

        .header-line {
            border-bottom: 2.5px solid #047857;
            margin-top: 3px;
        }

        /* ── Title & Meta ── */
        .title-block {
            text-align: center;
            margin-bottom: 8px;
        }

        .report-main-title {
            font-size: 11.5pt;
            font-weight: bold;
            color: #000000;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .report-sub-title {
            font-size: 8pt;
            font-weight: bold;
            color: #1e293b;
            margin-top: 2px;
        }

        .meta-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 8px;
            font-size: 7.5pt;
        }

        .meta-table td {
            padding: 2px 0;
            vertical-align: top;
        }

        .meta-lbl {
            font-weight: bold;
            color: #1e293b;
            width: 80px;
        }

        .meta-val {
            font-weight: bold;
            color: #000000;
        }

        .meta-line {
            border-bottom: 1px dashed #cbd5e1;
            display: inline-block;
            min-width: 160px;
        }

        /* ── Emphasized Data Table ── */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 4px;
            margin-bottom: 10px;
            table-layout: fixed;
            page-break-inside: auto;
        }

        .data-table thead {
            display: table-header-group;
        }

        .data-table tr {
            page-break-inside: avoid;
            page-break-after: auto;
        }

        .data-table th {
            background: #f1f5f9;
            color: #0f172a;
            font-size: 7pt;
            font-weight: bold;
            text-transform: uppercase;
            padding: 4px 3px;
            text-align: center;
            border: 1.2px solid #0f172a;
            overflow: hidden;
            word-wrap: break-word;
        }

        .data-table td {
            padding: 3.5px 3px;
            font-size: 7.5pt;
            border: 1px solid #0f172a;
            color: #000000;
            height: 18px;
            overflow: hidden;
            word-wrap: break-word;
        }

        .data-table tr:nth-child(even) td {
            background: #fafafa;
        }

        .text-center { text-align: center; }
        .text-left { text-align: left; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }

        /* ── Signatures ── */
        .signature-container {
            page-break-inside: avoid;
        }

        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 12px;
            margin-bottom: 6px;
        }

        .sig-box {
            width: 44%;
            vertical-align: top;
        }

        .sig-lbl {
            font-size: 7.5pt;
            color: #1e293b;
            margin-bottom: 22px;
        }

        .sig-name {
            font-size: 8pt;
            font-weight: bold;
            text-transform: uppercase;
            color: #000000;
            border-top: 1.5px solid #000000;
            padding-top: 3px;
            text-align: center;
            width: 85%;
            margin: 0 auto;
        }

        .sig-title {
            font-size: 7pt;
            color: #475569;
            text-align: center;
            margin-top: 1px;
        }

        .disclaimer {
            font-size: 6.5pt;
            color: #64748b;
            margin-top: 8px;
            margin-bottom: 4px;
        }
    </style>
</head>
<body>

<!-- Fixed Institution Header at Top of Every Page -->
<header>
    <div class="document-wrapper">
        <table class="header-table">
            <tr>
                <td style="width: 52px; vertical-align: middle;">
                    @if(!empty($logoBase64))
                        <img src="{{ $logoBase64 }}" class="logo-img" />
                    @endif
                </td>
                <td style="padding-left: 8px; vertical-align: middle;">
                    <div class="college-name">DAVAO DEL NORTE STATE COLLEGE</div>
                    <div class="college-sub">New Visayas, Panabo City, Davao Del Norte, Philippines 8105</div>
                </td>
                <td class="contact-info" style="width: 160px; vertical-align: middle;">
                    <div>✉ president@dnsc.edu.ph</div>
                    <div>🌐 dnsc.edu.ph</div>
                    <div>f @davnorstatecollege</div>
                </td>
            </tr>
        </table>
        <div class="header-line"></div>
    </div>
</header>

<!-- Fixed Wall-to-Wall Footer Banner Image at Absolute Bottom of Every Page -->
@if(!empty($footerBase64))
<footer>
    <img src="{{ $footerBase64 }}" />
</footer>
@endif

<div class="document-wrapper">
    <!-- Title Section -->
    <div class="title-block">
        <div class="report-main-title">{{ strtoupper($reportTitle) }}</div>
        <div class="report-sub-title">Second Semester, School Year: {{ $schoolYear === 'all' ? '2025-2026' : $schoolYear }}</div>
    </div>

    <!-- Subject / Course Info Block -->
    <table class="meta-table">
        <tr>
            <td style="width: 56%;">
                <table>
                    <tr>
                        <td class="meta-lbl">Subject No:</td>
                        <td class="meta-val"><span class="meta-line">{{ $program === 'all' ? 'CWTS2 / NSTP1' : $program . '2' }}</span></td>
                    </tr>
                    <tr>
                        <td class="meta-lbl">Description:</td>
                        <td class="meta-val"><span class="meta-line">{{ $program === 'ROTC' ? 'Reserve Officers\' Training Corps 2' : ($program === 'LTS' ? 'Literacy Training Service 2' : 'Civic Welfare Training Service 2') }}</span></td>
                    </tr>
                    <tr>
                        <td class="meta-lbl">Course/Year:</td>
                        <td class="meta-val"><span class="meta-line">{{ $program === 'all' ? 'CWTS - 1' : $program . ' - 1' }}</span></td>
                    </tr>
                </table>
            </td>
            <td style="width: 44%;">
                <table>
                    <tr>
                        <td class="meta-lbl">Schedule:</td>
                        <td class="meta-val"><span class="meta-line">MTH 02:30PM-04:00PM ROOM 14</span></td>
                    </tr>
                    <tr>
                        <td class="meta-lbl">Section:</td>
                        <td class="meta-val"><span class="meta-line">{{ $sectionId === 'all' ? 'All Enrolled Sections' : $sectionId }}</span></td>
                    </tr>
                    <tr>
                        <td class="meta-lbl">Total Records:</td>
                        <td class="meta-val"><span class="meta-line">{{ count($students) }} Enrolled Student(s)</span></td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>

    <!-- Emphasized Data Table with Fixed Proportional Widths -->
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">NO.</th>
                <th style="width: 15%;">STUDENT ID</th>
                <th style="width: 40%;">NAME</th>
                <th style="width: 15%;">NUMERICAL PERCENTAGE GRADE</th>
                <th style="width: 8%;">FINAL GRADE</th>
                <th style="width: 7%;">UNITS EARNED</th>
                <th style="width: 10%;">REMARKS</th>
            </tr>
        </thead>
        <tbody>
            @php $rowCount = count($students); @endphp

            @forelse($students as $index => $std)
                @php
                    $finalGrade = $std->grade !== 'N/A' ? $std->grade : '1.0';
                    $numericVal = is_numeric($finalGrade) ? floatval($finalGrade) : 1.0;
                    
                    $percentage = match(true) {
                        $numericVal == 1.0  => 99,
                        $numericVal == 1.25 => 97,
                        $numericVal == 1.5  => 93,
                        $numericVal == 1.75 => 90,
                        $numericVal == 2.0  => 88,
                        $numericVal == 2.25 => 85,
                        $numericVal == 2.5  => 83,
                        $numericVal == 2.75 => 80,
                        $numericVal == 3.0  => 75,
                        $numericVal > 3.0   => 50,
                        default => 95,
                    };

                    $remarksText = strtoupper($std->remarks === 'Passed' ? 'PASSED' : ($std->remarks === 'Failed' ? 'FAILED' : 'PENDING'));
                @endphp
                <tr>
                    <td class="text-center">{{ $index + 1 }}.</td>
                    <td class="text-center font-bold">{{ $std->student_no }}</td>
                    <td class="font-bold" style="padding-left: 5px;">{{ strtoupper($std->name) }}</td>
                    <td class="text-center font-bold">{{ $percentage }}</td>
                    <td class="text-center font-bold">{{ $finalGrade }}</td>
                    <td class="text-center">3</td>
                    <td class="text-center font-bold" style="color: {{ $remarksText === 'PASSED' ? '#000000' : ($remarksText === 'FAILED' ? '#dc2626' : '#d97706') }};">
                        {{ $remarksText }}
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="text-center" style="padding: 15px; color: #64748b;">
                        No enrolled student performance records found matching the selected filter.
                    </td>
                </tr>
            @endforelse

            {{-- Fill out empty table grid rows to match official grading sheet format --}}
            @if($rowCount < 18)
                @for($i = $rowCount; $i < 18; $i++)
                <tr>
                    <td class="text-center">&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
                @endfor
            @endif
        </tbody>
    </table>

    <!-- Signature & Disclaimer Section (Protected from Breaking Across Pages) -->
    <div class="signature-container">
        <table class="signature-table">
            <tr>
                <td class="sig-box">
                    <div class="sig-lbl">Received by:</div>
                    <div style="height: 38px; text-align: center; margin-bottom: -12px;">
                        @if(!empty($receivedSig))
                            <img src="{{ $receivedSig }}" style="max-height: 38px; max-width: 140px; display: inline-block;" />
                        @endif
                    </div>
                    <div class="sig-name">{{ strtoupper($receivedBy ?? 'FELICIDAD L. FORRO') }}</div>
                    <div class="sig-title">{{ $receivedByTitle ?? 'Registrar III' }}</div>
                </td>
                <td style="width: 12%;"></td>
                <td class="sig-box">
                    <div class="sig-lbl">Submitted by:</div>
                    <div style="height: 38px; text-align: center; margin-bottom: -12px;">
                        @if(!empty($submittedSig))
                            <img src="{{ $submittedSig }}" style="max-height: 38px; max-width: 140px; display: inline-block;" />
                        @endif
                    </div>
                    <div class="sig-name">{{ strtoupper($submittedBy ?? 'DODONGAN, EUGINE B. / DR. EMIL F. BRIONES') }}</div>
                    <div class="sig-title">{{ $submittedByTitle ?? 'Professor / NSTP Coordinator' }}</div>
                </td>
            </tr>
        </table>

        <!-- System Generation Line -->
        <div class="disclaimer">
            This is a system generated Grading Sheet | Processed By: {{ auth()->user()->name ?? 'Coordinator' }} | {{ date('F d, Y | h:i A') }}
        </div>
    </div>
</div>

</body>
</html>
