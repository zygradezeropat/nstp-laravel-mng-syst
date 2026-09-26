export function attachPlatoonEvents() {
    // Add Platoon Modal
    const addPlatoonBtn = document.getElementById('addPlatoonBtn');
    if (addPlatoonBtn) addPlatoonBtn.addEventListener('click', () => {
        if (typeof S !== 'undefined') {
            S.showAddPlatoonModal = true;
            if (typeof render === 'function') render();
        }
    });

    const closeAddPlatoonBtn = document.getElementById('closeAddPlatoonBtn');
    if (closeAddPlatoonBtn) closeAddPlatoonBtn.addEventListener('click', () => {
        if (typeof S !== 'undefined') {
            S.showAddPlatoonModal = false;
            if (typeof render === 'function') render();
        }
    });

    const saveNewPlatoonBtn = document.getElementById('saveNewPlatoonBtn');
    if (saveNewPlatoonBtn) saveNewPlatoonBtn.addEventListener('click', () => {
        const pName = document.getElementById('newPlatName')?.value.trim();
        const pInstr = document.getElementById('newPlatInstructor')?.value;

        if (!pName) { alert('Platoon Name is required'); return; }

        if (typeof S !== 'undefined' && S.platoons) {
            if (S.platoons[pName]) { alert('A platoon with this name already exists!'); return; }
            S.platoons[pName] = [];
            if (window.showToast) window.showToast(`Platoon "${pName}" created.`, 'success', 'Platoon Added');
            S.showAddPlatoonModal = false;
            if (typeof render === 'function') render();
        }
    });

    // Assign Officer
    document.querySelectorAll('[data-assign-officer]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (typeof S !== 'undefined') {
                S.assignOfficerModal = btn.dataset.assignOfficer;
                if (typeof render === 'function') render();
            }
        });
    });

    const closeOfficerAssignBtn = document.getElementById('closeOfficerAssignBtn');
    if (closeOfficerAssignBtn) closeOfficerAssignBtn.addEventListener('click', () => {
        if (typeof S !== 'undefined') {
            S.assignOfficerModal = null;
            if (typeof render === 'function') render();
        }
    });

    const saveOfficerAssignBtn = document.getElementById('saveOfficerAssignBtn');
    if (saveOfficerAssignBtn) saveOfficerAssignBtn.addEventListener('click', () => {
        const plat = S.assignOfficerModal;
        const studentNo = document.getElementById('assignOfficerStudentNo')?.value.trim();
        const role = document.getElementById('assignOfficerRole')?.value;
        const name = document.getElementById('assignOfficerName')?.value.trim();

        if (!plat || !studentNo || !name) { alert('Please fill in Student No and Name.'); return; }

        if (typeof S !== 'undefined' && S.platoons) {
            const list = S.platoons[plat] || [];
            list.unshift({ id: studentNo, name, role });
            S.platoons[plat] = list;
            
            if (window.showToast) window.showToast(`${role} assigned to ${plat}`, 'success', 'Officer Assigned');
            S.assignOfficerModal = null;
            if (typeof render === 'function') render();
        }
    });

    // Delete Officer
    document.querySelectorAll('[data-delete-officer]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            window.showConfirmModal({
                title: 'Remove Officer',
                message: 'Remove this officer from the roster?',
                confirmText: 'Remove Officer',
                cancelText: 'Cancel',
                isDanger: true,
                onConfirm: () => {
                    const sno = btn.dataset.deleteOfficer;
                    const plat = btn.dataset.platName;

                    if (typeof S !== 'undefined' && S.platoons && S.platoons[plat]) {
                        const list = S.platoons[plat];
                        const idx = list.findIndex(c => c.id === sno);
                        if (idx !== -1) {
                            list.splice(idx, 1);
                            if (window.showToast) window.showToast('Officer removed.', 'success', 'Removed');
                            if (typeof render === 'function') render();
                        }
                    }
                }
            });
        });
    });

    // Delete Platoon
    document.querySelectorAll('[data-delete-platoon]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const plat = btn.dataset.deletePlatoon;
            window.showConfirmModal({
                title: 'Delete Platoon',
                message: `Are you sure you want to delete ${plat}? All assigned officers will be unassigned.`,
                confirmText: 'Delete Platoon',
                cancelText: 'Cancel',
                isDanger: true,
                onConfirm: () => {
                    if (typeof S !== 'undefined' && S.platoons && S.unassigned) {
                        const students = S.platoons[plat] || [];
                        S.unassigned = [...S.unassigned, ...students];
                        delete S.platoons[plat];
                        if (window.showToast) window.showToast(`${plat} deleted.`, 'success', 'Platoon Deleted');
                        if (typeof render === 'function') render();
                    }
                }
            });
        });
    });
}
