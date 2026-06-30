// UI Elements & Helpers
import { state } from './state.js';

export const ELEMENTS = {
    uiPanel: document.getElementById('ui-panel'),
    showUiBtn: document.getElementById('show-ui-btn'),
    hideUiBtn: document.getElementById('hide-ui-btn'),
    userSelect: document.getElementById('user-select'),
    addUserBtn: document.getElementById('add-user-btn'),
    paintToggle: document.getElementById('paint-mode-toggle'),
    darkModeToggle: document.getElementById('dark-mode-toggle'),
    eraserToggle: document.getElementById('eraser-mode-toggle'),
    opacitySlider: document.getElementById('opacity-slider'),
    opacityValueEl: document.getElementById('opacity-value'),
    levelBtns: document.querySelectorAll('.level-btn'),
    hexCountEl: document.getElementById('hex-count'),
    zoomLevelEl: document.getElementById('zoom-level'),
    hexAreaEl: document.getElementById('hex-area'),
    toastContainer: document.getElementById('toast-container')
};

export function showToast(message) {
    // Remove oldest if we have 3
    if (state.activeToasts.length >= 3) {
        const oldest = state.activeToasts.shift();
        oldest.classList.remove('show');
        setTimeout(() => oldest.remove(), 300);
    }

    const toast = document.createElement('div');
    toast.className = 'toast success';
    toast.innerHTML = `<div class="dot"></div><span>${message}</span>`;
    ELEMENTS.toastContainer.appendChild(toast);
    state.activeToasts.push(toast);

    // Initial trigger for animation
    setTimeout(() => toast.classList.add('show'), 10);
    
    setTimeout(() => {
        if (toast.parentNode) {
            toast.classList.remove('show');
            setTimeout(() => {
                toast.remove();
                state.activeToasts = state.activeToasts.filter(t => t !== toast);
            }, 300);
        }
    }, 4000);
}
