export function attachInstructorEvents() {
    // Edit instructor
    document.querySelectorAll('[data-edit-inst]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const iid = btn.dataset.editInst;
            if (typeof S !== 'undefined' && typeof INSTRUCTORS !== 'undefined') {
                const inst = INSTRUCTORS.find(i => i.id === iid);
                if (inst) {
                    S.editingInstId = iid;
                    S.editingInstData = { ...inst };
                }
                if (typeof render === 'function') render();
            }
        });
    });

    const cancelInstEditBtn = document.getElementById('cancelInstEditBtn');
    if (cancelInstEditBtn) cancelInstEditBtn.addEventListener('click', () => {
        if (typeof S !== 'undefined') {
            S.editingInstId = null;
            S.editingInstData = null;
            if (typeof render === 'function') render();
        }
    });

    const saveInstEditBtn = document.getElementById('saveInstEditBtn');
    if (saveInstEditBtn) saveInstEditBtn.addEventListener('click', () => {
        if (typeof S !== 'undefined' && typeof INSTRUCTORS !== 'undefined' && S.editingInstData) {
            const name = document.getElementById('editInstName')?.value.trim();
            const role = document.getElementById('editInstRole')?.value;
            const status = document.getElementById('editInstStatus')?.value;
            
            if (!name) { alert('Name cannot be empty.'); return; }
            
            const idx = INSTRUCTORS.findIndex(i => i.id === S.editingInstId);
            if (idx !== -1) {
                INSTRUCTORS[idx].name = name;
                INSTRUCTORS[idx].role = role;
                INSTRUCTORS[idx].status = status;
                if (window.showToast) window.showToast(`${name}'s profile updated.`, 'success', 'Instructor Updated');
            }
            S.editingInstId = null;
            S.editingInstData = null;
            if (typeof render === 'function') render();
        }
    });

    // Delete instructor
    document.querySelectorAll('[data-delete-inst]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            const iid = btn.dataset.deleteInst;
            window.showConfirmModal({
                title: 'Remove Instructor',
                message: 'Are you sure you want to remove this personnel?',
                confirmText: 'Remove Personnel',
                cancelText: 'Cancel',
                isDanger: true,
                onConfirm: () => {
                    if (typeof INSTRUCTORS !== 'undefined') {
                        const idx = INSTRUCTORS.findIndex(i => i.id === iid);
                        if (idx !== -1) {
                            const nm = INSTRUCTORS[idx].name;
                            INSTRUCTORS.splice(idx, 1);
                            if (window.showToast) window.showToast(`${nm} removed.`, 'success', 'Instructor Removed');
                        }
                    }
                    if (typeof render === 'function') render();
                }
            });
        });
    });

    // Invite instructor
    const inviteInstBtn = document.getElementById('inviteInstBtn');
    if (inviteInstBtn) inviteInstBtn.addEventListener('click', () => {
        if (typeof S !== 'undefined') {
            S.showInviteInstModal = true;
            if (typeof render === 'function') render();
        }
    });

    const closeInviteInstBtn = document.getElementById('closeInviteInstBtn');
    if (closeInviteInstBtn) closeInviteInstBtn.addEventListener('click', () => {
        if (typeof S !== 'undefined') {
            S.showInviteInstModal = false;
            if (typeof render === 'function') render();
        }
    });

    const sendInviteInstBtn = document.getElementById('sendInviteInstBtn');
    if (sendInviteInstBtn) sendInviteInstBtn.addEventListener('click', () => {
        const email = document.getElementById('inviteEmail')?.value.trim();
        const role = document.getElementById('inviteRole')?.value;

        if (!email) { alert('Please enter an email address.'); return; }

        if (typeof INSTRUCTORS !== 'undefined') {
            INSTRUCTORS.unshift({
                id: 'I-' + Date.now().toString().slice(-4),
                name: 'Pending Invite',
                email: email,
                role: role,
                status: 'Offline',
                load: 0,
                rating: null
            });
            if (window.showToast) window.showToast(`Invitation sent to ${email}`, 'success', 'Invitation Sent');
        }
        
        if (typeof S !== 'undefined') {
            S.showInviteInstModal = false;
            if (typeof render === 'function') render();
        }
    });
}
