export function attachImportEvents() {
    // Certificates Import XLSX: process files and add to S.batches + BATCH_STUDENTS
    const certXlsxBtn = document.getElementById('certXlsxBtn');
    const certXlsxInput = document.getElementById('certXlsxInput');
    if (certXlsxBtn) certXlsxBtn.addEventListener('click', () => certXlsxInput && certXlsxInput.click());
    if (certXlsxInput) {
        certXlsxInput.addEventListener('change', e => {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = evt => {
                try {
                    const wb = window.XLSX.read(evt.target.result, { type: 'array' });
                    const ws = wb.Sheets[wb.SheetNames[0]];
                    const rows = window.XLSX.utils.sheet_to_json(ws, { defval: '' });
                    if (!rows.length) {
                        if (window.showToast) window.showToast('No data rows found in the XLSX file.', 'error', 'Import Failed');
                        return;
                    }

                    const students = [];
                    let sectionCode = '';
                    let programName = '';

                    rows.forEach(row => {
                        const getVal = (...keys) => {
                            for (const k of keys) {
                                const found = Object.keys(row).find(rk => rk.trim().toLowerCase() === k.toLowerCase());
                                if (found !== undefined && row[found] !== '') return String(row[found]).trim();
                            }
                            return '';
                        };

                        const name = getVal('Name', 'Student Name', 'Full Name', 'Student_Name', 'Student', 'STUDENT NAME');
                        if (!name) return; // skip blank rows

                        const serialNo = getVal('Serial No', 'Serial Number', 'Cert No', 'Certificate No', 'Control No', 'ID', 'Student No', 'Serial', 'SERIAL NO', 'SERIAL NUMBER') || `NSTP-${Date.now().toString().slice(-6)}-${students.length + 1}`;
                        students.push({ name, serialNo });

                        if (!sectionCode) sectionCode = getVal('Section Code', 'Section', 'Class', 'section');
                        if (!programName) programName = getVal('Program', 'NSTP Program', 'Component', 'programme');
                    });

                    if (!students.length) {
                        if (window.showToast) window.showToast('No student names found. Ensure a "Name" or "Student Name" column exists.', 'error', 'Import Failed');
                        return;
                    }

                    const fileNameNoExt = file.name.replace(/\.[^/.]+$/, '');
                    const detectedSection = sectionCode || 'Imported';
                    const detectedProgram = programName || (file.name.toUpperCase().includes('LTS') ? 'LTS' : file.name.toUpperCase().includes('ROTC') ? 'ROTC' : 'CWTS');
                    const batchName = fileNameNoExt;
                    const newBatchIdx = S.batches.length;

                    BATCH_STUDENTS.push(students);

                    S.batches.push({
                        name: batchName,
                        fileName: file.name,
                        section: detectedSection,
                        count: students.length,
                        status: 'Ready',
                        date: 'Eligible Today',
                        program: detectedProgram
                    });

                    S.newlyImportedBatchIndex = newBatchIdx;
                    setTimeout(() => {
                        if (S.newlyImportedBatchIndex === newBatchIdx) {
                            S.newlyImportedBatchIndex = null;
                            if (typeof render === 'function') render();
                        }
                    }, 5000);

                    certXlsxInput.value = '';
                    if (window.showToast) window.showToast(`<strong>${batchName}</strong> imported - ${students.length} student(s) ready.`, 'success', 'Batch Imported');
                    if (typeof render === 'function') render();
                } catch (err) {
                    if (window.showToast) window.showToast('Failed to read the XLSX file. Please check the file format.', 'error', 'Import Failed');
                    console.error(err);
                }
            };
            reader.readAsArrayBuffer(file);
        });
    }

    // OCR Export XLSX per processed upload
    document.querySelectorAll('[data-export-row]').forEach(btn => {
        btn.addEventListener('click', () => {
            const ri = parseInt(btn.dataset.exportRow);
            const upload = S.ocrUploads[ri];
            if (!upload || typeof SECTION_STUDENTS === 'undefined') return;
            const sectionCode = upload.section;
            const wb = window.XLSX.utils.book_new();
            const sectionsToExport = Object.keys(SECTION_STUDENTS).length ? Object.keys(SECTION_STUDENTS) : [sectionCode];
            
            sectionsToExport.forEach(sec => {
                const students = SECTION_STUDENTS[sec] || [];
                const rows = [['#', 'Student Name', 'Student No.', 'Section', 'Program', 'Status', 'Exported']];
                students.forEach((st, i) => {
                    const sName = typeof st === 'string' ? st : st.name;
                    const sNo = typeof st === 'string' ? '-??' : st.studentNo;
                    const sProg = typeof st === 'string' ? sec.replace(/-\d+[A-Z]$/, '') : st.program;
                    rows.push([i + 1, sName, sNo, sec, sProg, 'Passed', new Date().toLocaleDateString('en-PH')]);
                });
                const ws = window.XLSX.utils.aoa_to_sheet(rows);
                ws['!cols'] = [{ wch: 4 }, { wch: 24 }, { wch: 12 }, { wch: 8 }, { wch: 10 }, { wch: 16 }];
                window.XLSX.utils.book_append_sheet(wb, ws, sec);
            });
            const fileName = `NSTP_Students_${sectionCode}_${Date.now().toString().slice(-6)}.xlsx`;
            window.XLSX.writeFile(wb, fileName);
        });
    });

    // OCR upload file input & drag-drop
    const ocrDropZone = document.getElementById('ocrDropZone');
    const ocrFileInput = document.getElementById('ocrFileInput');
    function handleOCRFiles(files) {
        const maxMB = 25;
        Array.from(files).forEach(file => {
            const ext = file.name.split('.').pop().toLowerCase();
            if (!['xlsx', 'xls', 'xlsb', 'xlsm', 'csv', 'ods', 'tsv', 'txt', 'xml'].includes(ext)) {
                alert(`"${file.name}" is not supported. Please upload a valid sheet file (XLSX, XLS, CSV, ODS, TSV, XLSM, XLSB).`);
                return;
            }
            if (file.size > maxMB * 1024 * 1024) {
                alert(`"${file.name}" exceeds the 25 MB limit.`);
                return;
            }

            const now = new Date();
            const h = now.getHours();
            const timeStr = `Today ${h}:${String(now.getMinutes()).padStart(2, '0')} ${h >= 12 ? 'PM' : 'AM'}`;
            const entry = { file: file.name, section: '-??', students: 0, status: 'Processing', time: timeStr, results: null };
            S.ocrUploads.unshift(entry);
            if (typeof render === 'function') render();

            const reader = new FileReader();
            reader.onload = (evt) => {
                try {
                    const wb = window.XLSX.read(evt.target.result, { type: 'array' });
                    const ws = wb.Sheets[wb.SheetNames[0]];
                    const rows = window.XLSX.utils.sheet_to_json(ws, { defval: '' });

                    if (!rows.length) {
                        const idx = S.ocrUploads.indexOf(entry);
                        if (idx !== -1) S.ocrUploads[idx] = { ...entry, status: 'Failed', results: [] };
                        if (window.showToast) window.showToast(`No data found in <strong>${file.name}</strong>.`, 'error', 'Import Failed');
                        if (typeof render === 'function') render();
                        return;
                    }

                    const getCol = (row, ...keys) => {
                        for (const k of keys) {
                            const found = Object.keys(row).find(rk => rk.trim().toLowerCase() === k.toLowerCase());
                            if (found !== undefined && String(row[found]).trim() !== '') return String(row[found]).trim();
                        }
                        return '';
                    };

                    const classify = (rawGrade) => {
                        const g = parseFloat(rawGrade);
                        if (isNaN(g)) return 'N/A';
                        if (g >= 1.0 && g <= 2.5) return 'Passed';
                        if (g >= 2.75 && g <= 5.0) return 'Failed';
                        return 'N/A';
                    };

                    let sectionCode = '';
                    const results = [];

                    rows.forEach(row => {
                        const name = getCol(row, 'Student Name', 'Name', 'Full Name', 'Student', 'Lastname, Firstname', 'Student_Name', 'STUDENT NAME', 'FULLNAME');
                        const grade = getCol(row, 'Grade', 'Final Grade', 'Final_Grade', 'GWA', 'Score', 'Rating', 'Grades', 'GRADE', 'FINAL GRADE');
                        const sec = getCol(row, 'Section', 'Section Code', 'Class', 'SECTION');

                        if (!sectionCode && sec) sectionCode = sec;
                        if (!name) return; // skip blank / header-only rows

                        results.push({ name, grade: grade || '-??', remarks: classify(grade), gradeNum: parseFloat(grade) || null });
                    });

                    if (!sectionCode) {
                        sectionCode = file.name.replace(/\.(xlsx|xls)$/i, '').replace(/[_\s]+/g, '-').split('-').slice(0, 2).join('-') || 'Imported';
                    }

                    const passCount = results.filter(r => r.remarks === 'Passed').length;
                    const failCount = results.filter(r => r.remarks === 'Failed').length;
                    const overallStatus = results.length === 0 ? 'Failed' : 'Passed'; 

                    const idx = S.ocrUploads.indexOf(entry);
                    if (idx !== -1) {
                        S.ocrUploads[idx] = { ...entry, section: sectionCode, students: results.length, status: overallStatus, passCount, failCount, results };

                        // Sync grades instantly to Student Archive
                        results.forEach(res => {
                            const sName = res.name.trim();
                            const existIdx = S.studentArchive.findIndex(s => {
                                const clean = str => str.toLowerCase().replace(/[^a-z0-9\s]/g, '').replace(/\s+/g, ' ').trim();
                                const a = clean(s.name);
                                const b = clean(sName);
                                if (a === b) return true;
                                const pA = a.split(' ');
                                const pB = b.split(' ');
                                if (pA.length > 1 && pB.length > 1) {
                                    return pA[0] === pB[0] && pA[1] === pB[1];
                                }
                                return false;
                            });
                            
                            const parsedGrade = res.gradeNum;
                            const parsedRemarks = res.remarks;
                            const ucSec = sectionCode.toUpperCase();
                            const prog = ucSec.includes('ROTC') ? 'ROTC' : (ucSec.includes('LTS') ? 'LTS' : 'CWTS');

                            const gradeRecord = {
                                finalGrade: parsedGrade !== null ? parsedGrade : 1.5,
                                remarks: parsedRemarks !== 'N/A' ? parsedRemarks : 'Passed',
                                section: sectionCode,
                                program: prog,
                                schoolYear: '2025-2026',
                                semester: '1st Semester',
                                dateArchived: new Date().toLocaleDateString('en-US', { year: 'numeric', month: 'long', day: 'numeric' })
                            };

                            if (existIdx !== -1) {
                                if (!S.studentArchive[existIdx].grades) {
                                    S.studentArchive[existIdx].grades = [];
                                    if (S.studentArchive[existIdx].finalGrade !== null && S.studentArchive[existIdx].finalGrade !== undefined) {
                                        S.studentArchive[existIdx].grades.push({
                                            finalGrade: S.studentArchive[existIdx].finalGrade,
                                            remarks: S.studentArchive[existIdx].remarks || 'Passed',
                                            section: S.studentArchive[existIdx].section,
                                            program: S.studentArchive[existIdx].program,
                                            schoolYear: S.studentArchive[existIdx].schoolYear || '2025-2026',
                                            semester: S.studentArchive[existIdx].semester || '1st Semester',
                                            dateArchived: S.studentArchive[existIdx].dateArchived
                                        });
                                    }
                                }
                                S.studentArchive[existIdx].grades.push(gradeRecord);
                                S.studentArchive[existIdx].finalGrade = gradeRecord.finalGrade;
                                S.studentArchive[existIdx].remarks = gradeRecord.remarks;
                            } else {
                                S.studentArchive.push({
                                    studentNo: `2024-${String(Math.floor(10000 + Math.random() * 90000))}`,
                                    name: sName,
                                    gender: 'Female',
                                    section: '', 
                                    program: prog,
                                    instructor: 'Prof. Julian Santos',
                                    finalGrade: gradeRecord.finalGrade,
                                    remarks: gradeRecord.remarks,
                                    schoolYear: '2025-2026',
                                    semester: '1st Semester',
                                    dateArchived: gradeRecord.dateArchived,
                                    grades: [gradeRecord]
                                });
                            }
                        });
                    }

                    if (window.showToast) window.showToast(`<strong>${file.name}</strong> - ${passCount} passed, ${failCount} failed.`, 'success', 'Grades Imported');
                    if (typeof render === 'function') render();

                } catch (err) {
                    const idx = S.ocrUploads.indexOf(entry);
                    if (idx !== -1) S.ocrUploads[idx] = { ...entry, status: 'Failed', results: [] };
                    if (window.showToast) window.showToast(`Failed to read <strong>${file.name}</strong>. Check file format.`, 'error', 'Import Failed');
                    if (typeof render === 'function') render();
                    console.error('XLSX parse error:', err);
                }
            };
            reader.readAsArrayBuffer(file);
        });
        if (ocrFileInput) ocrFileInput.value = '';
    }
    
    if (ocrDropZone) {
        ocrDropZone.addEventListener('click', () => ocrFileInput && ocrFileInput.click());
        ocrDropZone.addEventListener('dragover', e => { e.preventDefault(); ocrDropZone.classList.add('bg-indigo-100'); });
        ocrDropZone.addEventListener('dragleave', () => ocrDropZone.classList.remove('bg-indigo-100'));
        ocrDropZone.addEventListener('drop', e => { e.preventDefault(); ocrDropZone.classList.remove('bg-indigo-100'); handleOCRFiles(e.dataTransfer.files); });
    }
    if (ocrFileInput) {
        ocrFileInput.addEventListener('click', e => e.stopPropagation());
        ocrFileInput.addEventListener('change', e => handleOCRFiles(e.target.files));
    }



    // Modal Import XLSX: auto-fill form fields from first data row
    const modalXlsxBtn = document.getElementById('modalXlsxBtn');
    const modalXlsxInput = document.getElementById('modalXlsxInput');
    if (modalXlsxBtn) modalXlsxBtn.addEventListener('click', () => modalXlsxInput && modalXlsxInput.click());
    if (modalXlsxInput) modalXlsxInput.addEventListener('change', e => {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = evt => {
            try {
                const wb = window.XLSX.read(evt.target.result, { type: 'array' });
                const ws = wb.Sheets[wb.SheetNames[0]];

                const range = window.XLSX.utils.decode_range(ws['!ref'] || 'A1:Z100');
                let headerRowIndex = -1;
                for (let r = range.s.r; r <= range.e.r; r++) {
                    let foundHeader = false;
                    for (let c = range.s.c; c <= range.e.c; c++) {
                        const cellRef = window.XLSX.utils.encode_cell({ r, c });
                        const cellVal = ws[cellRef]?.v;
                        if (cellVal && typeof cellVal === 'string') {
                            const cleanVal = cellVal.trim().toLowerCase();
                            const knownHeaders = ['last name', 'first name', 'lastname', 'firstname', 'last', 'first', 'email address', 'email', 'student name', 'student_name', 'name', 'full name', 'fullname', 'student no', 'student number', 'student_no', 'student_number', 'id'];
                            if (knownHeaders.includes(cleanVal)) {
                                foundHeader = true;
                                headerRowIndex = r;
                                break;
                            }
                        }
                    }
                    if (foundHeader) break;
                }

                const rows = window.XLSX.utils.sheet_to_json(ws, { range: headerRowIndex !== -1 ? headerRowIndex : 0, defval: '' });
                if (!rows.length) { alert('No data rows found in the XLSX file.'); return; }

                const firstRow = rows[0];
                const getVal = (...keys) => {
                    for (const k of keys) {
                        const found = Object.keys(firstRow).find(rk => rk.trim().toLowerCase() === k.toLowerCase());
                        if (found !== undefined && firstRow[found] !== '') return String(firstRow[found]).trim();
                    }
                    return '';
                };

                const titleCase = (str) => {
                    if (!str) return '';
                    return str.trim().toLowerCase().split(/\s+/).map(w => w.charAt(0).toUpperCase() + w.slice(1)).join(' ');
                };

                const importedSts = [];
                rows.forEach(row => {
                    const getRowVal = (...keys) => {
                        for (const k of keys) {
                            const found = Object.keys(row).find(rk => rk.trim().toLowerCase() === k.toLowerCase());
                            if (found !== undefined && row[found] !== '') return String(row[found]).trim();
                        }
                        return '';
                    };

                    let fullName = '';
                    const singleName = getRowVal('Student Name', 'Student_Name', 'Name', 'Full Name', 'FullName');
                    if (singleName) {
                        fullName = titleCase(singleName);
                    } else {
                        const lastName = titleCase(getRowVal('Last Name', 'LastName', 'Last'));
                        const firstName = titleCase(getRowVal('First Name', 'FirstName', 'First'));
                        const middleName = titleCase(getRowVal('Middle Name', 'MiddleName', 'Middle'));
                        if (lastName || firstName) {
                            const middleInitial = middleName ? middleName.charAt(0).toUpperCase() + '.' : '';
                            fullName = `${lastName}, ${firstName} ${middleInitial}`.trim();
                        }
                    }

                    if (!fullName) return;

                    const collegeProgram = getRowVal('Program', 'Course', 'College Program', 'College_Program', 'college_program');

                    importedSts.push({
                        studentNo: getRowVal('Student No', 'Student Number', 'ID', 'Student_No', 'Student_Number') || `2024-${String(Math.floor(10000 + Math.random() * 90000))}`,
                        name: fullName,
                        program: collegeProgram || 'BSIT',
                        dob: getRowVal('Date of Birth', 'DOB', 'Birthday', 'Birth Date', 'Birth_Date'),
                        birthPlace: getRowVal('Place of Birth', 'POB', 'Birthplace', 'Place_of_Birth'),
                        gender: titleCase(getRowVal('Gender', 'Sex')) || 'Female',
                        address: getRowVal('Residential Address', 'Address', 'Residential_Address'),
                        cellNo: getRowVal('Cell #', 'Cell Number', 'Phone', 'Cell_No', 'Contact'),
                        email: getRowVal('Email Address', 'Email', 'Gmail', 'Email_Address')
                    });
                });

                window.modalImportedStudents = importedSts;
                if (typeof S !== 'undefined') {
                    S.modalImportedStudents = importedSts;
                }

                const fill = (id, val) => { const el = document.getElementById(id); if (el && val) el.value = val; };
                const inferredSecCode = file.name.replace(/\.[^/.]+$/, "").replace(/_/, " ").trim();
                fill('secCode', getVal('Section Code', 'Section', 'Class') || inferredSecCode);
                fill('secProgram', getVal('Program', 'Programme', 'Course') || (file.name.toUpperCase().includes('ROTC') ? 'ROTC' : (file.name.toUpperCase().includes('LTS') ? 'LTS' : 'CWTS')));
                fill('secSchoolYear', getVal('School Year', 'Year', 'school_year') || '2025-2026');
                fill('secInstructor', getVal('Instructor', 'Teacher', 'Faculty') || 'Prof. Julian Santos');
                fill('secRoom', getVal('Room', 'Venue', 'Location') || 'TBA');

                if (document.getElementById('secStudents')) {
                    document.getElementById('secStudents').value = importedSts.length;
                }

                modalXlsxInput.value = '';
                if (modalXlsxBtn) {
                    modalXlsxBtn.textContent = '- ' + importedSts.length + ' Students loaded from ' + file.name;
                    modalXlsxBtn.classList.add('border-emerald-500', 'bg-emerald-100', 'text-emerald-800');
                }
            } catch (err) {
                alert('Could not read the XLSX file. Please make sure it is a valid Excel workbook.');
                console.error(err);
            }
        };
        reader.readAsArrayBuffer(file);
    });

    // Platoon XLSX Simulation
    const importPlatXlsxBtn = document.getElementById('importPlatXlsxBtn');
    const platXlsxImportInput = document.getElementById('platXlsxImportInput');
    if (importPlatXlsxBtn && platXlsxImportInput) {
        importPlatXlsxBtn.addEventListener('click', () => platXlsxImportInput.click());
        platXlsxImportInput.addEventListener('change', e => {
            if (e.target.files.length) { alert('Master list uploaded. (Simulation)'); platXlsxImportInput.value = ''; }
        });
    }

    const modalPlatXlsxBtn = document.getElementById('modalPlatXlsxBtn');
    const modalPlatXlsxInput = document.getElementById('modalPlatXlsxInput');
    if (modalPlatXlsxBtn && modalPlatXlsxInput) {
        modalPlatXlsxBtn.addEventListener('click', () => modalPlatXlsxInput.click());
        modalPlatXlsxInput.addEventListener('change', e => {
            if (e.target.files.length) {
                modalPlatXlsxBtn.textContent = '- ' + e.target.files[0].name;
                modalPlatXlsxBtn.classList.add('border-emerald-500', 'bg-emerald-100', 'text-emerald-800');
            }
        });
    }
}
