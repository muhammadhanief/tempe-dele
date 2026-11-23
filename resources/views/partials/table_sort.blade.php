<style>
    /* ==========================================================================
       PointSE-Style Universal Table Sorter Styles (BPS Antislop Compliant)
       ========================================================================== */
    th.table-header-sortable {
        cursor: pointer !important;
        user-select: none !important;
        -webkit-user-select: none !important;
        transition: background-color 0.15s ease-in-out, color 0.15s ease-in-out;
    }

    th.table-header-sortable:hover {
        background-color: rgba(243, 244, 246, 0.85) !important;
    }

    th.table-header-sortable:focus-visible {
        outline: 2px solid #faa938 !important;
        outline-offset: -2px !important;
    }

    .table-sort-indicator {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        margin-left: 0.35rem;
        vertical-align: middle;
        line-height: 1;
        pointer-events: none;
        flex-shrink: 0;
    }

    .table-sort-svg {
        width: 0.875rem;
        height: 0.875rem;
        display: inline-block;
    }

    .table-sort-arrow-up,
    .table-sort-arrow-down {
        transition: opacity 0.15s ease, stroke 0.15s ease, stroke-width 0.15s ease;
        opacity: 0.35;
        stroke: #94a3b8;
    }

    th.table-header-sortable:hover .table-sort-arrow-up,
    th.table-header-sortable:hover .table-sort-arrow-down {
        opacity: 0.65;
        stroke: #64748b;
    }

    th.table-header-sortable.is-sorted-asc .table-sort-arrow-up {
        opacity: 1 !important;
        stroke: #faa938 !important;
        stroke-width: 3.2px !important;
    }

    th.table-header-sortable.is-sorted-asc .table-sort-arrow-down {
        opacity: 0.12 !important;
        stroke: #cbd5e1 !important;
    }

    th.table-header-sortable.is-sorted-desc .table-sort-arrow-down {
        opacity: 1 !important;
        stroke: #faa938 !important;
        stroke-width: 3.2px !important;
    }

    th.table-header-sortable.is-sorted-desc .table-sort-arrow-up {
        opacity: 0.12 !important;
        stroke: #cbd5e1 !important;
    }

    th.table-header-sortable.is-sorted-asc,
    th.table-header-sortable.is-sorted-desc {
        color: #0f172a !important;
    }
</style>

<script>
(function() {
    'use strict';

    // Indonesian Month Dictionary
    const ID_MONTHS = {
        'jan': 0, 'januari': 0,
        'feb': 1, 'februari': 1,
        'mar': 2, 'maret': 2,
        'apr': 3, 'april': 3,
        'mei': 4, 'may': 4,
        'jun': 5, 'juni': 5,
        'jul': 6, 'juli': 6,
        'agu': 7, 'agustus': 7, 'agt': 7,
        'sep': 8, 'september': 8,
        'okt': 9, 'oktober': 9,
        'nov': 10, 'november': 10,
        'des': 11, 'desember': 11
    };

    // Civil Service Rank (Golongan) Order
    const GOLONGAN_ORDER = {
        'i/a': 11, 'i/b': 12, 'i/c': 13, 'i/d': 14,
        'ii/a': 21, 'ii/b': 22, 'ii/c': 23, 'ii/d': 24,
        'iii/a': 31, 'iii/b': 32, 'iii/c': 33, 'iii/d': 34,
        'iv/a': 41, 'iv/b': 42, 'iv/c': 43, 'iv/d': 44, 'iv/e': 45,
        'ix': 51, 'x': 52
    };

    const NON_SORTABLE_KEYWORDS = [
        'aksi', 'action', 'tanda tangan', 'ttd',
        'dokumentasi', 'detail', 'password',
        'opsi', 'pilih', 'checkbox'
    ];

    /**
     * Parse date string into timestamp
     */
    function parseDate(str) {
        if (!str || typeof str !== 'string') return null;
        const s = str.trim();

        // 1. "dd MMM yyyy" or "d MMMM yyyy" (e.g. "23 Nov 2026", "11 Sep 2026", "9 September 2026")
        const dmyMatch = s.match(/^(\d{1,2})\s+([a-zA-Z]+)\s+(\d{4})(?:[,\s]+(\d{1,2}):(\d{2}))?/);
        if (dmyMatch) {
            const day = parseInt(dmyMatch[1], 10);
            const mKey = dmyMatch[2].toLowerCase();
            const year = parseInt(dmyMatch[3], 10);
            if (ID_MONTHS.hasOwnProperty(mKey)) {
                const hour = dmyMatch[4] ? parseInt(dmyMatch[4], 10) : 0;
                const min = dmyMatch[5] ? parseInt(dmyMatch[5], 10) : 0;
                return new Date(year, ID_MONTHS[mKey], day, hour, min).getTime();
            }
        }

        // 2. ISO format "yyyy-mm-dd" (e.g. "2026-11-23" or "2026-10-15 08:30:00")
        const isoMatch = s.match(/^(\d{4})-(\d{2})-(\d{2})(?:[T\s]+(\d{1,2}):(\d{2}))?/);
        if (isoMatch) {
            const y = parseInt(isoMatch[1], 10);
            const m = parseInt(isoMatch[2], 10) - 1;
            const d = parseInt(isoMatch[3], 10);
            const h = isoMatch[4] ? parseInt(isoMatch[4], 10) : 0;
            const min = isoMatch[5] ? parseInt(isoMatch[5], 10) : 0;
            return new Date(y, m, d, h, min).getTime();
        }

        // 3. Slash / dash format "dd/mm/yyyy" or "dd-mm-yyyy"
        const slashMatch = s.match(/^(\d{1,2})[\/\-](\d{1,2})[\/\-](\d{4})/);
        if (slashMatch) {
            const d = parseInt(slashMatch[1], 10);
            const m = parseInt(slashMatch[2], 10) - 1;
            const y = parseInt(slashMatch[3], 10);
            return new Date(y, m, d).getTime();
        }

        return null;
    }

    /**
     * Parse cell raw string into typed value
     */
    function parseValue(rawStr) {
        if (rawStr === null || rawStr === undefined) return { type: 'empty', val: '', raw: '' };
        const s = String(rawStr).trim();
        if (!s || s === '-' || s === '—' || s === 'N/A' || s === 'null') {
            return { type: 'empty', val: '', raw: s };
        }

        // 1. Golongan PNS/PPPK
        const golClean = s.toLowerCase();
        if (GOLONGAN_ORDER.hasOwnProperty(golClean)) {
            return { type: 'number', val: GOLONGAN_ORDER[golClean], raw: s };
        }
        const golMatch = golClean.match(/^(?:gol(?:ongan)?\.?\s*)?(i|ii|iii|iv|ix|x)(?:\/([a-e]))?$/);
        if (golMatch) {
            const key = golMatch[1] + '/' + (golMatch[2] || 'a');
            if (GOLONGAN_ORDER.hasOwnProperty(key)) {
                return { type: 'number', val: GOLONGAN_ORDER[key], raw: s };
            }
        }

        // 2. Durasi jam (e.g. "11 jam", "3.5 jam", "0 jam")
        const jamMatch = s.match(/^(\d+(?:[\.,]\d+)?)\s*jam/i);
        if (jamMatch) {
            const n = parseFloat(jamMatch[1].replace(',', '.'));
            if (!isNaN(n)) return { type: 'number', val: n, raw: s };
        }

        // 3. Indonesian numbers / currency (e.g. "Rp 1.050.000", "1.050.000", "50.000", "15%")
        const cleanNum = s.replace(/^Rp\s*/i, '').replace(/%/g, '').trim();
        if (/^-?\d{1,3}(\.\d{3})+(,\d+)?$/.test(cleanNum)) {
            const normalized = cleanNum.replace(/\./g, '').replace(',', '.');
            const n = parseFloat(normalized);
            if (!isNaN(n)) return { type: 'number', val: n, raw: s };
        } else if (/^-?\d+(\.\d+)?$/.test(cleanNum) || /^-?\d+(,\d+)?$/.test(cleanNum)) {
            const n = parseFloat(cleanNum.replace(',', '.'));
            if (!isNaN(n)) return { type: 'number', val: n, raw: s };
        }

        // 4. Date check
        const dt = parseDate(s);
        if (dt !== null) {
            return { type: 'date', val: dt, raw: s };
        }

        // 5. Time check (e.g. "16:30 - 20:00" or "16:30")
        const timeMatch = s.match(/^(\d{1,2}):(\d{2})/);
        if (timeMatch) {
            const min = parseInt(timeMatch[1], 10) * 60 + parseInt(timeMatch[2], 10);
            return { type: 'number', val: min, raw: s };
        }

        // 6. Default to text string
        return { type: 'string', val: s.toLowerCase(), raw: s };
    }

    /**
     * Extract cell text from a table row
     */
    function getCellContent(row, colIndex) {
        const cell = row.children[colIndex];
        if (!cell) return '';

        // Explicit data attribute
        if (cell.hasAttribute('data-sort-value')) {
            return cell.getAttribute('data-sort-value');
        }

        // Inline editable cell display
        const display = cell.querySelector('.cell-display');
        if (display) {
            return display.textContent.trim();
        }

        // Input or select value
        const input = cell.querySelector('input:not([type="hidden"]):not([type="checkbox"]), select');
        if (input && input.value !== undefined) {
            if (input.tagName === 'SELECT' && input.selectedOptions && input.selectedOptions[0]) {
                return input.selectedOptions[0].textContent.trim();
            }
            return input.value.trim();
        }

        return (cell.innerText || cell.textContent || '').trim();
    }

    /**
     * Comparator for sorting
     */
    function compareValues(a, b, isAsc) {
        // Empty values always go to the bottom
        if (a.type === 'empty' && b.type !== 'empty') return 1;
        if (b.type === 'empty' && a.type !== 'empty') return -1;
        if (a.type === 'empty' && b.type === 'empty') return 0;

        let diff = 0;
        if (a.type === 'number' && b.type === 'number') {
            diff = a.val - b.val;
        } else if (a.type === 'date' && b.type === 'date') {
            diff = a.val - b.val;
        } else {
            diff = a.raw.localeCompare(b.raw, 'id', { numeric: true, sensitivity: 'base' });
        }

        return isAsc ? diff : -diff;
    }

    /**
     * Build mapping between leaf TH cells and body column indexes
     */
    function getHeaderLeafMapping(table) {
        const thead = table.querySelector('thead');
        if (!thead) return [];
        const rows = Array.from(thead.querySelectorAll('tr'));
        if (rows.length === 0) return [];

        if (rows.length === 1) {
            return Array.from(rows[0].children).map((th, idx) => ({ th, colIndex: idx }));
        }

        // Multi-row header matrix (e.g. daftar hadir)
        const matrix = [];
        rows.forEach((tr, rIdx) => {
            let cIdx = 0;
            Array.from(tr.children).forEach(cell => {
                while (matrix[rIdx] && matrix[rIdx][cIdx]) {
                    cIdx++;
                }
                const rowspan = parseInt(cell.getAttribute('rowspan') || '1', 10);
                const colspan = parseInt(cell.getAttribute('colspan') || '1', 10);
                for (let r = 0; r < rowspan; r++) {
                    for (let c = 0; c < colspan; c++) {
                        const row = rIdx + r;
                        const col = cIdx + c;
                        if (!matrix[row]) matrix[row] = [];
                        matrix[row][col] = {
                            cell,
                            isLeaf: (r === rowspan - 1 && colspan === 1) || (rowspan === 1 && colspan === 1)
                        };
                    }
                }
                cIdx += colspan;
            });
        });

        const lastRowIdx = rows.length - 1;
        const mapping = [];
        if (matrix[lastRowIdx]) {
            matrix[lastRowIdx].forEach((entry, colIdx) => {
                if (entry && entry.cell) {
                    mapping.push({ th: entry.cell, colIndex: colIdx });
                }
            });
        }
        return mapping;
    }

    /**
     * Check if TH should be excluded from sorting
     */
    function isNonSortable(th) {
        if (th.hasAttribute('data-no-sort') || th.getAttribute('data-sortable') === 'false') {
            return true;
        }

        // If TH only contains a checkbox or button without text
        const onlyInput = th.querySelector('input[type="checkbox"]');
        if (onlyInput && th.textContent.trim() === '') {
            return true;
        }

        const text = th.textContent.trim().toLowerCase();
        for (let i = 0; i < NON_SORTABLE_KEYWORDS.length; i++) {
            if (text === NON_SORTABLE_KEYWORDS[i] || text.startsWith(NON_SORTABLE_KEYWORDS[i] + ' ')) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if a row is a pinned summary / total row
     */
    function isPinnedRow(row) {
        if (row.classList.contains('total-row') || row.classList.contains('pinned-row')) {
            return true;
        }
        // First cell has colspan >= 2 or contains "Total"
        const firstCell = row.firstElementChild;
        if (firstCell) {
            const colspan = parseInt(firstCell.getAttribute('colspan') || '1', 10);
            if (colspan >= 2) return true;
            const txt = (firstCell.textContent || '').trim().toLowerCase();
            if (txt === 'total' || txt === 'jumlah total') return true;
        }
        return false;
    }

    /**
     * Perform sorting on a table
     */
    function sortTable(table, activeTh, colIndex, isNoCol) {
        const tbody = table.querySelector('tbody');
        if (!tbody) return;

        const allRows = Array.from(tbody.querySelectorAll('tr'));
        if (allRows.length <= 1) return;

        // Skip sorting if table only contains an empty state message row
        if (allRows.length === 1 && allRows[0].firstElementChild &&
            parseInt(allRows[0].firstElementChild.getAttribute('colspan') || '1', 10) >= 2) {
            return;
        }

        // Determine next sort state
        const isCurrentlyAsc = activeTh.classList.contains('is-sorted-asc');
        const nextOrder = isCurrentlyAsc ? 'desc' : 'asc';

        // Reset all sortable headers in this table
        table.querySelectorAll('th.table-header-sortable').forEach(th => {
            th.classList.remove('is-sorted-asc', 'is-sorted-desc');
            th.setAttribute('aria-sort', 'none');
        });

        // Set active header classes
        activeTh.classList.add(nextOrder === 'asc' ? 'is-sorted-asc' : 'is-sorted-desc');
        activeTh.setAttribute('aria-sort', nextOrder === 'asc' ? 'ascending' : 'descending');

        // Separate data rows and pinned rows
        const dataRows = [];
        const pinnedRows = [];

        allRows.forEach(row => {
            if (isPinnedRow(row)) {
                pinnedRows.push(row);
            } else {
                dataRows.push(row);
            }
        });

        if (dataRows.length <= 1) return;

        // Sort data rows
        dataRows.sort((rowA, rowB) => {
            if (isNoCol) {
                const initA = parseInt(rowA.dataset.initialOrder || '0', 10);
                const initB = parseInt(rowB.dataset.initialOrder || '0', 10);
                return nextOrder === 'asc' ? (initA - initB) : (initB - initA);
            }

            const valA = parseValue(getCellContent(rowA, colIndex));
            const valB = parseValue(getCellContent(rowB, colIndex));
            const cmp = compareValues(valA, valB, nextOrder === 'asc');

            // Stable sort tie-breaker using initialOrder
            if (cmp === 0) {
                const initA = parseInt(rowA.dataset.initialOrder || '0', 10);
                const initB = parseInt(rowB.dataset.initialOrder || '0', 10);
                return nextOrder === 'asc' ? (initA - initB) : (initB - initA);
            }
            return cmp;
        });

        // Check if table has a visual "No" sequence column (column 0 titled "No")
        const mapping = getHeaderLeafMapping(table);
        const firstColHeader = mapping[0] ? (mapping[0].th.textContent || '').trim().toLowerCase() : '';
        const hasNoColumn = (firstColHeader === 'no' || firstColHeader === 'no.');

        let startNumber = 1;
        if (hasNoColumn && dataRows[0] && dataRows[0].children[0]) {
            const parsedStart = parseInt(dataRows[0].children[0].textContent.trim(), 10);
            if (!isNaN(parsedStart)) {
                // Find minimum initial number among all rows
                let minNum = parsedStart;
                dataRows.forEach(r => {
                    const n = parseInt(r.dataset.initialNo || (r.children[0] ? r.children[0].textContent.trim() : ''), 10);
                    if (!isNaN(n) && n < minNum) minNum = n;
                });
                startNumber = minNum;
            }
        }

        // Fragment re-append for maximum performance and zero flicker
        const fragment = document.createDocumentFragment();
        dataRows.forEach((row, idx) => {
            // If table has a "No" column and we sorted by another column, update sequence number
            if (hasNoColumn && !isNoCol && row.children[0]) {
                row.children[0].textContent = String(startNumber + idx);
            } else if (hasNoColumn && isNoCol && row.children[0] && row.dataset.initialNo) {
                row.children[0].textContent = row.dataset.initialNo;
            }
            fragment.appendChild(row);
        });

        // Re-append pinned summary rows at the bottom
        pinnedRows.forEach(row => {
            fragment.appendChild(row);
        });

        tbody.appendChild(fragment);

        // Dispatch custom event for interoperability
        table.dispatchEvent(new CustomEvent('table:sorted', {
            bubbles: true,
            detail: { colIndex, direction: nextOrder }
        }));
    }

    /**
     * Attach sorting capability to a single table
     */
    function attachTableSorter(table) {
        if (table.hasAttribute('data-no-sort') || table.classList.contains('no-sort')) {
            return;
        }

        // Avoid signature pads, calendars, print documents
        if (table.closest('.ttd-table, .tui-calendar, .signature-pad, #signature-pad, .no-sort-container')) {
            return;
        }

        const thead = table.querySelector('thead');
        const tbody = table.querySelector('tbody');
        if (!thead || !tbody) return;

        // Tag initial order and initial "No" on data rows
        Array.from(tbody.querySelectorAll('tr')).forEach((row, idx) => {
            if (!row.dataset.initialOrder) {
                row.dataset.initialOrder = String(idx);
                if (row.children[0]) {
                    row.dataset.initialNo = row.children[0].textContent.trim();
                }
            }
        });

        const leafMapping = getHeaderLeafMapping(table);
        if (leafMapping.length === 0) return;

        leafMapping.forEach(({ th, colIndex }) => {
            if (th.dataset.sortInitialized) return;
            if (isNonSortable(th)) return;

            th.dataset.sortInitialized = 'true';
            th.classList.add('table-header-sortable');
            th.setAttribute('role', 'columnheader');
            th.setAttribute('tabindex', '0');
            th.setAttribute('aria-sort', 'none');

            const headerText = th.textContent.trim();
            th.setAttribute('title', `Klik untuk mengurutkan kolom "${headerText}" (A-Z / 0-9 / Terbaru)`);

            // Check if column is "No"
            const isNoCol = (headerText.toLowerCase() === 'no' || headerText.toLowerCase() === 'no.');

            // Inject PointSE-style sort indicator SVG if not already present
            if (!th.querySelector('.table-sort-indicator')) {
                const indicator = document.createElement('span');
                indicator.className = 'table-sort-indicator';
                indicator.setAttribute('aria-hidden', 'true');
                indicator.innerHTML = `
                    <svg class="table-sort-svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
                        <path class="table-sort-arrow-up" stroke-linecap="round" stroke-linejoin="round" d="M7 10l5-5 5 5"/>
                        <path class="table-sort-arrow-down" stroke-linecap="round" stroke-linejoin="round" d="M7 14l5 5 5-5"/>
                    </svg>
                `;

                // If TH has an inner flex or inline-flex container, append inside it
                const innerFlex = th.querySelector('.inline-flex, .flex');
                if (innerFlex && !innerFlex.querySelector('.table-sort-indicator')) {
                    innerFlex.appendChild(indicator);
                } else {
                    th.appendChild(indicator);
                }
            }

            // Click listener
            th.addEventListener('click', function(e) {
                // Ignore click if user clicked an interactive input, select, link, or button inside header
                if (e.target.closest('select, input, button, a, label')) {
                    return;
                }
                sortTable(table, th, colIndex, isNoCol);
            });

            // Keyboard navigation listener (Enter or Space)
            th.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' || e.key === ' ') {
                    if (e.target.closest('select, input, button, a')) return;
                    e.preventDefault();
                    sortTable(table, th, colIndex, isNoCol);
                }
            });
        });
    }

    /**
     * Scan and initialize all tables in document
     */
    function initAllTables() {
        const tables = document.querySelectorAll('table');
        tables.forEach(table => {
            attachTableSorter(table);
        });
    }

    // Expose global initializer
    window.initTableSorter = initAllTables;

    // Run on DOM ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initAllTables);
    } else {
        initAllTables();
    }

    // Intercept dynamic content (modals, AJAX tabs)
    const observer = new MutationObserver((mutations) => {
        let shouldInit = false;
        for (let i = 0; i < mutations.length; i++) {
            const addedNodes = mutations[i].addedNodes;
            for (let j = 0; j < addedNodes.length; j++) {
                const node = addedNodes[j];
                if (node.nodeType === 1) { // ELEMENT_NODE
                    if (node.tagName === 'TABLE' || node.querySelector('table')) {
                        shouldInit = true;
                        break;
                    }
                }
            }
            if (shouldInit) break;
        }
        if (shouldInit) {
            initAllTables();
        }
    });

    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
})();
</script>
