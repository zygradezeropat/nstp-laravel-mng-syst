export function attachSectionEvents() {
    // =========================================================
    // IMPORT RESULT MODAL
    // =========================================================
    const importResultModal = document.getElementById('importResultModal');
    const closeImportResultModal = document.getElementById('closeImportResultModal');

    if (closeImportResultModal) {
        closeImportResultModal.addEventListener('click', () => {

            if (importResultModal) {
                importResultModal.classList.add('hidden');
                importResultModal.classList.remove('flex');
            }

            // Reload only after the user closes the result modal
            window.location.reload();
        });
    }


    // =========================================================
    // EDIT SECTION BUTTON
    // =========================================================
    document.querySelectorAll('[data-edit-section]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();

            const sc = btn.dataset.editSection;

            if (
                typeof S !== 'undefined' &&
                typeof SECTIONS !== 'undefined'
            ) {
                const sec = SECTIONS.find(s => s.code === sc);

                if (sec) {
                    S.editingSectionCode = sc;
                    S.editingSectionData = { ...sec };
                }

                if (typeof render === 'function') {
                    render();
                }
            }
        });
    });


    // =========================================================
    // DELETE SECTION BUTTON
    // =========================================================
    document.querySelectorAll('[data-delete-section]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();

            const sc = btn.dataset.deleteSection;

            if (confirm(`Are you sure you want to delete section ${sc}?`)) {

                if (typeof SECTIONS !== 'undefined') {
                    const idx = SECTIONS.findIndex(s => s.code === sc);

                    if (idx !== -1) {
                        SECTIONS.splice(idx, 1);
                    }
                }

                if (typeof SECTION_STUDENTS !== 'undefined') {
                    delete SECTION_STUDENTS[sc];
                }

                if (window.showToast) {
                    window.showToast(
                        `Section ${sc} deleted.`,
                        'success',
                        'Section Deleted'
                    );
                }

                if (typeof render === 'function') {
                    render();
                }
            }
        });
    });


    // =========================================================
    // EDIT SECTION INPUTS
    // =========================================================

    const editSectionProgram =
        document.getElementById('editSectionProgram');

    if (editSectionProgram) {
        editSectionProgram.addEventListener('change', e => {
            if (S.editingSectionData) {
                S.editingSectionData.program = e.target.value;
            }
        });
    }


    const editSectionInstructor =
        document.getElementById('editSectionInstructor');

    if (editSectionInstructor) {
        editSectionInstructor.addEventListener('change', e => {
            if (S.editingSectionData) {
                S.editingSectionData.instructor = e.target.value;
            }
        });
    }


    const editSectionTime =
        document.getElementById('editSectionTime');

    if (editSectionTime) {
        editSectionTime.addEventListener('input', e => {
            if (S.editingSectionData) {
                S.editingSectionData.time = e.target.value;
            }
        });
    }


    const editSectionRoom =
        document.getElementById('editSectionRoom');

    if (editSectionRoom) {
        editSectionRoom.addEventListener('input', e => {
            if (S.editingSectionData) {
                S.editingSectionData.room = e.target.value;
            }
        });
    }


    // =========================================================
    // SAVE SECTION EDIT
    // =========================================================
    const saveSectionEdit =
        document.getElementById('saveSectionEditBtn');

    if (saveSectionEdit) {
        saveSectionEdit.addEventListener('click', () => {

            if (
                typeof S !== 'undefined' &&
                typeof SECTIONS !== 'undefined' &&
                S.editingSectionData
            ) {
                const idx = SECTIONS.findIndex(
                    s => s.code === S.editingSectionCode
                );

                if (idx !== -1) {
                    SECTIONS[idx] = {
                        ...S.editingSectionData
                    };

                    if (window.showToast) {
                        window.showToast(
                            `Section ${S.editingSectionCode} updated.`,
                            'success',
                            'Section Updated'
                        );
                    }
                }

                S.editingSectionCode = null;
                S.editingSectionData = null;

                if (typeof render === 'function') {
                    render();
                }
            }
        });
    }


    // =========================================================
    // CANCEL SECTION EDIT
    // =========================================================
    const cancelSectionEditBtn =
        document.getElementById('cancelSectionEditBtn');

    if (cancelSectionEditBtn) {
        cancelSectionEditBtn.addEventListener('click', () => {

            if (typeof S !== 'undefined') {
                S.editingSectionCode = null;
                S.editingSectionData = null;

                if (typeof render === 'function') {
                    render();
                }
            }
        });
    }


    // =========================================================
    // ADD SECTION
    // =========================================================
    const addSectionBtn =
        document.getElementById('addSectionBtn');

    if (addSectionBtn) {
        addSectionBtn.addEventListener('click', () => {

            if (typeof S !== 'undefined') {
                S.showAddSectionModal = true;

                if (typeof render === 'function') {
                    render();
                }
            }
        });
    }


    // =========================================================
    // CLOSE ADD SECTION
    // =========================================================
    const closeAddSectionBtn =
        document.getElementById('closeAddSectionBtn');

    if (closeAddSectionBtn) {
        closeAddSectionBtn.addEventListener('click', () => {

            if (typeof S !== 'undefined') {
                S.showAddSectionModal = false;

                if (typeof render === 'function') {
                    render();
                }
            }
        });
    }


    // =========================================================
    // CREATE NEW SECTION
    // =========================================================
    const saveNewSectionBtn =
        document.getElementById('saveNewSectionBtn') ||
        document.getElementById('sectionFormCreate');

    if (saveNewSectionBtn) {

        saveNewSectionBtn.addEventListener('click', () => {

            const code =
                (
                    document.getElementById('secCode') ||
                    document.getElementById('newSecCode')
                )?.value.trim();

            const program =
                (
                    document.getElementById('secProgram') ||
                    document.getElementById('newSecProgram')
                )?.value;

            const room =
                (
                    document.getElementById('secRoom') ||
                    document.getElementById('newSecRoom')
                )?.value.trim();

            const semester =
                (
                    document.getElementById('secSemester') ||
                    document.getElementById('newSecSemester')
                )?.value.trim() || '1st Semester';

            const instructor =
                (
                    document.getElementById('secInstructor') ||
                    document.getElementById('newSecInstructor')
                )?.value;

            const schoolYear =
                document.getElementById('secSchoolYear')
                    ?.value.trim() || '2025-2026';


            if (!code) {
                alert('Section code is required');
                return;
            }


            const importedStudents =
                window.modalImportedStudents ||
                (
                    typeof S !== 'undefined'
                        ? S.modalImportedStudents
                        : null
                ) ||
                [];


            const originalText =
                saveNewSectionBtn.innerHTML;

            saveNewSectionBtn.innerHTML =
                'Creating...';

            saveNewSectionBtn.disabled = true;


            fetch('/api/sections', {
                method: 'POST',

                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },

                body: JSON.stringify({
                    code: code,
                    program: program,
                    room: room || 'TBA',
                    school_year: schoolYear,
                    semester: semester,
                    instructor_name: instructor || null,
                    students: importedStudents,
                    upload_token:
                        window.modalUploadToken || null
                })
            })

            .then(response =>
                response.json().then(data => ({
                    status: response.status,
                    body: data
                }))
            )

            .then(({ status, body }) => {

                if (status !== 201) {
                    throw new Error(
                        body.message ||
                        'Failed to create section.'
                    );
                }


                if (window.showToast) {

                    window.showToast(
                        body.message ||
                        `Section ${code} created successfully.`,
                        'success',
                        'Section Added'
                    );

                } else {

                    alert(
                        `Section ${code} created successfully!`
                    );

                }


                const overlay =
                    document.getElementById(
                        'newSectionOverlay'
                    );

                if (overlay) {
                    overlay.classList.add('hidden');
                }


                window.modalImportedStudents = null;
                window.modalUploadToken = null;


                if (typeof S !== 'undefined') {
                    S.modalImportedStudents = null;
                    S.showAddSectionModal = false;
                }


                setTimeout(
                    () => window.location.reload(),
                    1000
                );

            })

            .catch(err => {

                alert(
                    'Error creating section: ' +
                    err.message
                );

                console.error(err);

            })

            .finally(() => {

                saveNewSectionBtn.innerHTML =
                    originalText;

                saveNewSectionBtn.disabled =
                    false;

            });

        });
    }


    // =========================================================
    // DELETE INDIVIDUAL STUDENT FROM SECTION
    // =========================================================
    document.querySelectorAll('[data-delete-student]')
        .forEach(btn => {

            btn.addEventListener('click', (e) => {

                e.stopPropagation();

                if (
                    !confirm(
                        'Are you sure you want to remove this student? They will be moved to the Student Archive.'
                    )
                ) {
                    return;
                }


                const sno =
                    btn.dataset.deleteStudent;

                const sc =
                    btn.dataset.sectionCode;


                if (
                    typeof SECTION_STUDENTS !== 'undefined' &&
                    SECTION_STUDENTS[sc] &&
                    typeof S !== 'undefined'
                ) {

                    const arr =
                        SECTION_STUDENTS[sc];

                    const idx =
                        arr.findIndex(
                            st =>
                                typeof st === 'object'
                                    ? st.studentNo === sno
                                    : false
                        );


                    if (idx !== -1) {

                        const studentRecord =
                            arr[idx];


                        // Archive sync
                        const ucSec =
                            sc.toUpperCase();

                        const prog =
                            studentRecord.program ||
                            (
                                ucSec.includes('ROTC')
                                    ? 'ROTC'
                                    : (
                                        ucSec.includes('LTS')
                                            ? 'LTS'
                                            : 'CWTS'
                                    )
                            );


                        const secInfo =
                            typeof SECTIONS !== 'undefined'
                                ? SECTIONS.find(
                                    s => s.code === sc
                                )
                                : null;


                        const instructor =
                            studentRecord.instructor ||
                            (
                                secInfo
                                    ? secInfo.instructor
                                    : 'Unknown Instructor'
                            );


                        const archivedRecord = {

                            studentNo:
                                studentRecord.studentNo,

                            name:
                                studentRecord.name,

                            gender:
                                studentRecord.gender ||
                                'Unknown',

                            section:
                                sc,

                            program:
                                prog,

                            instructor:
                                instructor,

                            finalGrade:
                                studentRecord.finalGrade !== undefined
                                    ? studentRecord.finalGrade
                                    : null,

                            remarks:
                                studentRecord.remarks ||
                                'Dropped',

                            schoolYear:
                                studentRecord.schoolYear ||
                                '2025-2026',

                            semester:
                                studentRecord.semester ||
                                '1st Semester',

                            dateArchived:
                                new Date().toLocaleDateString(
                                    'en-US',
                                    {
                                        year: 'numeric',
                                        month: 'long',
                                        day: 'numeric'
                                    }
                                )
                        };


                        const existIdx =
                            S.studentArchive.findIndex(
                                s =>
                                    s.studentNo ===
                                    studentRecord.studentNo
                            );


                        if (existIdx !== -1) {

                            S.studentArchive[existIdx] =
                                archivedRecord;

                        } else {

                            S.studentArchive.push(
                                archivedRecord
                            );

                        }


                        arr.splice(idx, 1);


                        if (secInfo) {
                            secInfo.students =
                                arr.length;
                        }


                        if (window.showToast) {

                            window.showToast(
                                `Student removed from ${sc} and archived.`,
                                'success',
                                'Student Removed'
                            );

                        }


                        if (typeof render === 'function') {
                            render();
                        }

                    }
                }

            });

        });


    // =========================================================
    // QUICK ADD STUDENT
    // =========================================================
    const quickAddStudentBtn =
        document.getElementById(
            'quickAddStudentBtn'
        );

    if (quickAddStudentBtn) {

        quickAddStudentBtn.addEventListener(
            'click',
            () => {

                const sc =
                    S.selectedSectionCode;

                if (!sc) {
                    return;
                }


                const sno =
                    document.getElementById(
                        'qaStudentNo'
                    )?.value.trim();


                const sname =
                    document.getElementById(
                        'qaStudentName'
                    )?.value.trim();


                if (!sno || !sname) {

                    alert(
                        'Please enter both Student No. and Name.'
                    );

                    return;
                }


                if (
                    typeof SECTION_STUDENTS !== 'undefined' &&
                    SECTION_STUDENTS[sc]
                ) {

                    const arr =
                        SECTION_STUDENTS[sc];


                    if (
                        arr.find(
                            st =>
                                typeof st === 'object' &&
                                st.studentNo === sno
                        )
                    ) {

                        alert(
                            'Student number already exists in this section.'
                        );

                        return;
                    }


                    arr.unshift({
                        studentNo: sno,
                        name: sname
                    });


                    if (
                        typeof SECTIONS !== 'undefined'
                    ) {

                        const sec =
                            SECTIONS.find(
                                s => s.code === sc
                            );

                        if (sec) {
                            sec.students =
                                arr.length;
                        }

                    }


                    if (window.showToast) {

                        window.showToast(
                            `Added ${sname} to ${sc}.`,
                            'success',
                            'Student Added'
                        );

                    }


                    const studentNoInput =
                        document.getElementById(
                            'qaStudentNo'
                        );

                    const studentNameInput =
                        document.getElementById(
                            'qaStudentName'
                        );


                    if (studentNoInput) {
                        studentNoInput.value = '';
                    }


                    if (studentNameInput) {
                        studentNameInput.value = '';
                    }


                    if (typeof render === 'function') {
                        render();
                    }

                }

            }
        );

    }


    // =========================================================
    // SECTION VIEW DETAILS
    // =========================================================
    document.querySelectorAll('[data-view-section]')
        .forEach(btn => {

            btn.addEventListener('click', (e) => {

                e.stopPropagation();

                if (typeof S !== 'undefined') {

                    S.selectedSectionCode =
                        btn.dataset.viewSection;

                    if (typeof render === 'function') {
                        render();
                    }

                }

            });

        });


    // =========================================================
    // CLOSE SECTION VIEW
    // =========================================================
    const closeSectionViewBtn =
        document.getElementById(
            'closeSectionViewBtn'
        );

    if (closeSectionViewBtn) {

        closeSectionViewBtn.addEventListener(
            'click',
            () => {

                if (typeof S !== 'undefined') {

                    S.selectedSectionCode = null;

                    if (typeof render === 'function') {
                        render();
                    }

                }

            }
        );

    }


    // =========================================================
    // BACK TO SECTIONS
    // =========================================================
    const backToSectionsBtn =
        document.getElementById(
            'backToSectionsBtn'
        );

    if (backToSectionsBtn) {

        backToSectionsBtn.addEventListener(
            'click',
            () => {

                if (typeof S !== 'undefined') {

                    S.selectedSectionCode = null;

                    if (typeof render === 'function') {
                        render();
                    }

                }

            }
        );

    }


    // =========================================================
    // IMPORT MASTER LIST XLSX
    // =========================================================
    const importXlsxBtn =
        document.getElementById(
            'importXlsxBtn'
        );

    const xlsxImportInput =
        document.getElementById(
            'xlsxImportInput'
        );


    if (
        importXlsxBtn &&
        xlsxImportInput
    ) {

        importXlsxBtn.addEventListener(
            'click',
            () => {
                xlsxImportInput.click();
            }
        );

    }


    window.closeConfirmMasterImportModal = function() {
        const modal = document.getElementById('confirmMasterImportModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
        }
        const input = document.getElementById('xlsxImportInput');
        if (input) input.value = '';
    };

    let pendingImportFile = null;

    if (xlsxImportInput) {
        xlsxImportInput.addEventListener('change', e => {
            const file = e.target.files[0];
            if (!file) return;

            pendingImportFile = file;

            const reader = new FileReader();
            reader.onload = function(evt) {
                let rows = [];
                try {
                    const data = new Uint8Array(evt.target.result);
                    const XLSXLib = window.XLSX || (typeof XLSX !== 'undefined' ? XLSX : null);
                    if (XLSXLib) {
                        const workbook = XLSXLib.read(data, { type: 'array' });
                        const sheetName = workbook.SheetNames[0];
                        const sheet = workbook.Sheets[sheetName];
                        rows = XLSXLib.utils.sheet_to_json(sheet, { header: 1 });
                    }
                } catch (err) {
                    console.error("Preview parsing failed:", err);
                }
                showConfirmMasterImportModal(file, rows);
            };
            reader.readAsArrayBuffer(file);
        });
    }

    function showConfirmMasterImportModal(file, rows) {
        const modal = document.getElementById('confirmMasterImportModal');
        const fileNameEl = document.getElementById('confirmImportFileName');
        const fileSizeEl = document.getElementById('confirmImportFileSize');
        const recordCountEl = document.getElementById('confirmImportRecordCount');
        const tbody = document.getElementById('confirmImportPreviewTable');
        const submitBtn = document.getElementById('confirmMasterImportSubmitBtn');

        if (!modal || !fileNameEl || !fileSizeEl || !recordCountEl || !tbody) {
            executeMasterListImport(file);
            return;
        }

        fileNameEl.textContent = file.name;
        fileNameEl.title = file.name;
        fileSizeEl.textContent = (file.size / 1024).toFixed(1) + ' KB';

        tbody.innerHTML = '';

        let dataRows = [];
        let headerIdx = -1;

        if (rows && rows.length > 0) {
            for (let r = 0; r < Math.min(5, rows.length); r++) {
                const row = rows[r];
                if (!row) continue;
                const str = row.join(' ').toLowerCase();
                if (str.includes('name') || str.includes('serial') || str.includes('surname') || str.includes('student')) {
                    headerIdx = r;
                    break;
                }
            }
            if (headerIdx === -1) headerIdx = 0;

            dataRows = rows.slice(headerIdx + 1).filter(r => r && r.length > 0 && r.some(c => c !== null && c !== ''));

            const headerRow = rows[headerIdx] || [];
            let nameIdx = -1, surnameIdx = -1, firstIdx = -1, serialIdx = -1, idIdx = -1, nstpIdx = -1, progIdx = -1;

            headerRow.forEach((col, idx) => {
                const val = String(col || '').toLowerCase();
                if (val.includes('serial')) serialIdx = idx;
                else if (val.includes('student') || val.includes('id') || val.includes('no')) idIdx = idx;

                if (val.includes('surname') || val.includes('last')) surnameIdx = idx;
                else if (val.includes('first')) firstIdx = idx;
                else if (val.includes('name')) nameIdx = idx;

                if (val.includes('nstp') || val.includes('component')) nstpIdx = idx;
                else if (val.includes('program') || val.includes('course') || val.includes('main program')) progIdx = idx;
            });

            const previewRows = dataRows.slice(0, 5);

            previewRows.forEach((r, index) => {
                let serialOrId = (serialIdx !== -1 ? r[serialIdx] : '') || (idIdx !== -1 ? r[idIdx] : '') || 'N/A';
                let fullName = '';
                if (surnameIdx !== -1 || firstIdx !== -1) {
                    const surname = surnameIdx !== -1 ? (r[surnameIdx] || '') : '';
                    const firstname = firstIdx !== -1 ? (r[firstIdx] || '') : '';
                    fullName = surname ? `${surname}, ${firstname}` : firstname;
                } else if (nameIdx !== -1) {
                    fullName = r[nameIdx] || '';
                }
                if (!fullName) fullName = 'N/A';

                let prog = progIdx !== -1 ? (r[progIdx] || 'N/A') : 'N/A';
                let nstp = nstpIdx !== -1 ? (r[nstpIdx] || 'CWTS') : 'CWTS';

                const tr = document.createElement('tr');
                tr.className = 'hover:bg-slate-50 transition';
                tr.innerHTML = `
                    <td class="py-2.5 px-3 font-semibold text-slate-400">${index + 1}</td>
                    <td class="py-2.5 px-3 font-mono font-medium text-slate-700">${serialOrId}</td>
                    <td class="py-2.5 px-3 font-bold text-slate-800">${fullName}</td>
                    <td class="py-2.5 px-3 text-slate-600 truncate max-w-[150px]">${prog}</td>
                    <td class="py-2.5 px-3"><span class="px-2 py-0.5 rounded-full text-[10px] font-bold bg-indigo-50 text-indigo-700">${nstp}</span></td>
                `;
                tbody.appendChild(tr);
            });
        }

        if (dataRows.length === 0) {
            tbody.innerHTML = '<tr><td colspan="5" class="py-4 text-center text-slate-400 text-xs">No student records parsed from file</td></tr>';
        }

        recordCountEl.textContent = `${dataRows.length} Student Records`;

        modal.classList.remove('hidden');
        modal.classList.add('flex');

        submitBtn.onclick = function() {
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            executeMasterListImport(file);
        };
    }

    function executeMasterListImport(file) {
        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);


                // =================================================
                // LOADING STATE
                // =================================================
                const originalText =
                    importXlsxBtn.innerHTML;


                importXlsxBtn.innerHTML = `
                    <svg
                        class="animate-spin -ml-1 mr-2 h-4 w-4 text-emerald-700"
                        xmlns="http://www.w3.org/2000/svg"
                        fill="none"
                        viewBox="0 0 24 24"
                    >
                        <circle
                            class="opacity-25"
                            cx="12"
                            cy="12"
                            r="10"
                            stroke="currentColor"
                            stroke-width="4"
                        ></circle>

                        <path
                            class="opacity-75"
                            fill="currentColor"
                            d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"
                        ></path>
                    </svg>

                    Importing...
                `;


                importXlsxBtn.disabled =
                    true;


                // =================================================
                // SEND XLSX TO BACKEND
                // =================================================
                fetch(
                    '/api/students/import',
                    {
                        method: 'POST',

                        body: formData,

                        headers: {
                            'Accept':
                                'application/json'
                        }
                    }
                )


                // =================================================
                // PARSE RESPONSE
                // =================================================
                .then(response =>
                    response.json()
                        .then(data => ({
                            status:
                                response.status,

                            body:
                                data
                        }))
                )


                // =================================================
                // PROCESS IMPORT RESULT
                // =================================================
                .then(
                    ({
                        status,
                        body
                    }) => {

                        if (status !== 200) {

                            throw new Error(
                                body.message ||
                                'Failed to import master list.'
                            );

                        }


                        // =================================================
                        // REFRESH DATABASE DATA
                        // =================================================
                        const loads = [];


                        if (
                            window.loadStudentsFromDatabase
                        ) {

                            loads.push(
                                window.loadStudentsFromDatabase()
                            );

                        }


                        if (
                            window.loadSectionsFromDatabase
                        ) {

                            loads.push(
                                window.loadSectionsFromDatabase()
                            );

                        }


                        if (
                            window.fetchDashboardMetrics
                        ) {

                            loads.push(
                                window
                                    .fetchDashboardMetrics()
                                    .catch(
                                        () => {}
                                    )
                            );

                        }


                        Promise.all(loads)
                            .finally(() => {

                                if (
                                    typeof render ===
                                    'function'
                                ) {

                                    render();

                                }


                                // =================================================
                                // GET MODAL ELEMENTS
                                // =================================================
                                const createdCount =
                                    document.getElementById(
                                        'createdStudentsCount'
                                    );


                                const updatedCount =
                                    document.getElementById(
                                        'updatedStudentsCount'
                                    );


                                const skippedCount =
                                    document.getElementById(
                                        'skippedStudentsCount'
                                    );


                                const resultMessage =
                                    document.getElementById(
                                        'importResultMessage'
                                    );


                                // =================================================
                                // SET CREATED COUNT
                                // =================================================
                                if (createdCount) {

                                    createdCount.textContent =
                                        body.created_students ??
                                        0;

                                }


                                // =================================================
                                // SET UPDATED COUNT
                                // =================================================
                                if (updatedCount) {

                                    updatedCount.textContent =
                                        body.updated_students ??
                                        0;

                                }


                                // =================================================
                                // SET SKIPPED COUNT
                                // =================================================
                                if (skippedCount) {

                                    skippedCount.textContent =
                                        body.skipped_students ??
                                        0;

                                }


                                // =================================================
                                // SET RESULT MESSAGE
                                // =================================================
                                if (resultMessage) {

                                    resultMessage.textContent =
                                        body.message ||
                                        'Master list imported successfully.';

                                }


                                // =================================================
                                // SHOW IMPORT RESULT MODAL
                                // =================================================
                                const importResultModal =
                                    document.getElementById(
                                        'importResultModal'
                                    );


                                if (importResultModal) {

                                    importResultModal
                                        .classList
                                        .remove('hidden');

                                    importResultModal
                                        .classList
                                        .add('flex');

                                }


                                // IMPORTANT:
                                // DO NOT RELOAD HERE.
                                //
                                // The page will reload when
                                // the user clicks Close.
                            });

                    }
                )


                // =================================================
                // HANDLE IMPORT ERROR
                // =================================================
                .catch(err => {

                    if (window.showToast) {

                        window.showToast(
                            'Import failed: ' +
                            err.message,
                            'error',
                            'Error'
                        );

                    } else {

                        alert(
                            'Import failed: ' +
                            err.message
                        );

                    }


                    console.error(err);

                })


                // =================================================
                // RESTORE IMPORT BUTTON
                // =================================================
                .finally(() => {

                    xlsxImportInput.value =
                        '';

                    importXlsxBtn.innerHTML =
                        originalText;

                    importXlsxBtn.disabled =
                        false;

                });

    }
}