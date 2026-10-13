import './utils/pdfGenerator';
import './utils/csvHandler';
import './utils/charts';
import { initUI } from './components/ui';

import { attachLayoutEvents } from './features/layout';
import { attachNotificationEvents } from './features/notifications';
import { attachFilterEvents } from './features/filters';
import { attachCertificateEvents } from './features/certificates';
import { attachImportEvents } from './features/imports';
import { attachSectionEvents } from './features/sections';
import { attachPlatoonEvents } from './features/platoons';
import { attachInstructorEvents } from './features/instructors';
import { attachActivityEvents } from './features/activities';
import { attachSystemEvents } from './features/system';
import { setupDragDrop } from './features/dragDrop';

// Initialize global UI components
initUI();

// Setup global window functions
setupDragDrop();
attachSystemEvents();

// Global stub for syncSectionStudentsToArchive
window.syncSectionStudentsToArchive = function () { /* no-op */ };

// Re-expose a unified attachEvents to the window object so that standalone scripts
// (e.g., coordinator.js, rotc.js, admin.js) can invoke it post-render.
window.attachEvents = function() {
    attachLayoutEvents();
    attachNotificationEvents();
    attachFilterEvents();
    attachCertificateEvents();
    attachImportEvents();
    attachSectionEvents();
    attachPlatoonEvents();
    attachInstructorEvents();
    attachActivityEvents();
    attachSystemEvents();
};
