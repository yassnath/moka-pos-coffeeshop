import './bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

function hydrateMobileTableLabels() {
    document.querySelectorAll('table.moka-table-mobile').forEach((table) => {
        const labels = Array.from(table.querySelectorAll('thead th')).map((th) =>
            th.textContent.replace(/\s+/g, ' ').trim()
        );

        table.querySelectorAll('tbody tr').forEach((row) => {
            const cells = Array.from(row.children).filter((cell) => cell.tagName === 'TD');

            cells.forEach((cell, index) => {
                if (cell.hasAttribute('colspan')) {
                    cell.removeAttribute('data-label');
                    return;
                }

                const label = labels[index] ?? '';
                if (label !== '') {
                    cell.setAttribute('data-label', label);
                }
            });
        });
    });
}

document.addEventListener('DOMContentLoaded', hydrateMobileTableLabels);
window.addEventListener('load', hydrateMobileTableLabels);
