import './bootstrap';

function updateModalScrollLock() {
    const hasModal = document.querySelector('[data-modal-root]') !== null;
    if (hasModal) {
        if (document.body.dataset.modalScrollLocked !== '1') {
            document.body.dataset.modalScrollLocked = '1';
            document.body.dataset.modalPrevOverflow = document.body.style.overflow || '';
            document.body.style.overflow = 'hidden';
        }
        return;
    }

    if (document.body.dataset.modalScrollLocked === '1') {
        document.body.style.overflow = document.body.dataset.modalPrevOverflow || '';
        delete document.body.dataset.modalPrevOverflow;
        delete document.body.dataset.modalScrollLocked;
    }
}

function closeTopMostModal() {
    const modals = Array.from(document.querySelectorAll('[data-modal-root]'));
    if (modals.length === 0) return;

    const top = modals[modals.length - 1];
    const closeEl = top.querySelector('[data-modal-close]');
    if (closeEl) {
        closeEl.click();
        return;
    }

    const overlay = top.querySelector('[data-modal-overlay]');
    if (overlay) {
        overlay.click();
    }
}

document.addEventListener('keydown', (e) => {
    if (e.key !== 'Escape') return;
    if (document.querySelector('[data-modal-root]') === null) return;

    e.preventDefault();
    closeTopMostModal();
});

const observer = new MutationObserver(() => {
    updateModalScrollLock();
});

observer.observe(document.documentElement, {
    childList: true,
    subtree: true,
});

updateModalScrollLock();
