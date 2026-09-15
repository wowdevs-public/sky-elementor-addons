/**
 * Start table widget script
 */

(function ($, elementor) {

    'use strict';

    // Table — sorting, search, pagination and export without a vendor library.
    // All markup (toolbar, buttons, cells, mobile labels) comes from PHP; this
    // only reorders, hides and reads what is already in the DOM.
    var widgetTable = function ($scope, $) {

        var $root = $scope.find('.sa-table__wrap');
        if (!$root.length) {
            return;
        }

        var root = $root[0],
            settings = $root.data('settings') || {};

        settings.i18n = settings.i18n || {};

        var table = {
            rows: [],
            filtered: [],
            visible: [],
            orderDirty: false,
            page: 1,
            perPage: settings.perPage || 10,
            sortIndex: -1,
            sortDir: 'asc',
            query: '',

            // PHP-rendered nodes. Anything missing simply disables that feature.
            cacheDom: function () {
                this.table = root.querySelector('.sa-table');
                this.body = root.querySelector('.sa-table__body');
                this.search = root.querySelector('.sa-table__search-input');
                this.length = root.querySelector('.sa-table__length-select');
                this.info = root.querySelector('.sa-table__info');
                this.pagination = root.querySelector('.sa-table__pagination');
                this.headCells = Array.prototype.slice.call(root.querySelectorAll('.sa-table__head .sa-table__head-column-cell'));
                this.exportButtons = Array.prototype.slice.call(root.querySelectorAll('.sa-table__export-btn'));

                return !!this.body;
            },

            // PHP decides this: a rowspan makes a row non-detachable, so
            // reordering or hiding it would shear the grid. Reading its verdict
            // rather than re-deriving one keeps the two in step.
            isManaged: function () {
                return this.body.classList.contains('sa-table__body--managed');
            },

            // Cache each row's searchable text and per-column sort keys once, so
            // filtering and sorting never touch the DOM to read a value.
            collectRows: function () {
                this.rows = Array.prototype.map.call(this.body.rows, function (tr, index) {
                    var values = {};

                    Array.prototype.forEach.call(tr.cells, function (cell) {
                        var column = cell.getAttribute('data-column');
                        if (column !== null) {
                            values[column] = cell.getAttribute('data-sort') || '';
                        }
                    });

                    return {
                        tr: tr,
                        values: values,
                        // Original position, so the stable-sort tiebreak is a
                        // subtraction rather than an indexOf scan per comparison.
                        index: index,
                        text: (tr.textContent || '').toLowerCase()
                    };
                });

                this.filtered = this.rows.slice();

                // Rows PHP already un-hid for the first page.
                this.visible = this.rows.filter(function (row) {
                    return !row.tr.classList.contains('sa-table__body-row--hidden');
                });
            },

            bindExport: function () {
                var self = this;

                this.exportButtons.forEach(function (button) {
                    button.addEventListener('click', function () {
                        self.runExport(button.getAttribute('data-export'), button);
                    });
                });
            },

            bind: function () {
                var self = this;

                if (settings.sorting) {
                    this.headCells.forEach(function (cell) {
                        var button = cell.querySelector('.sa-table__sort');
                        if (!button) {
                            return;
                        }

                        button.addEventListener('click', function () {
                            self.toggleSort(parseInt(cell.getAttribute('data-column'), 10));
                        });
                    });
                }

                if (this.search) {
                    this.search.addEventListener('input', function () {
                        clearTimeout(self.searchTimer);
                        self.searchTimer = setTimeout(function () {
                            self.query = self.search.value.trim().toLowerCase();
                            self.page = 1;
                            self.applyFilter();
                            self.draw();
                        }, 180);
                    });
                }

                if (this.length) {
                    this.length.addEventListener('change', function () {
                        self.perPage = parseInt(self.length.value, 10) || self.perPage;
                        self.page = 1;
                        self.draw();
                    });
                }

                this.bindExport();
            },

            applyFilter: function () {
                var query = this.query;

                this.filtered = !query ? this.rows.slice() : this.rows.filter(function (row) {
                    return row.text.indexOf(query) !== -1;
                });
            },

            toggleSort: function (index) {
                if (isNaN(index)) {
                    return;
                }

                this.sortDir = this.sortIndex === index && this.sortDir === 'asc' ? 'desc' : 'asc';
                this.sortIndex = index;
                this.page = 1;
                this.applySort();
                this.markSortedColumn();
                this.draw();
            },

            // Stable sort: ties keep their original document order, so repeated
            // sorts on equal values never shuffle rows.
            applySort: function () {
                var index = this.sortIndex,
                    direction = this.sortDir === 'desc' ? -1 : 1;

                if (index < 0) {
                    return;
                }

                this.filtered.sort(function (a, b) {
                    var result = table.compare(a.values[index] || '', b.values[index] || '');
                    return result !== 0 ? result * direction : a.index - b.index;
                });

                this.orderDirty = true;
            },

            compare: function (a, b) {
                var numberA = parseFloat(a),
                    numberB = parseFloat(b),
                    bothNumeric = !isNaN(numberA) && !isNaN(numberB) && a !== '' && b !== '';

                if (bothNumeric) {
                    return numberA - numberB;
                }

                return a.localeCompare(b);
            },

            markSortedColumn: function () {
                var self = this;

                this.headCells.forEach(function (cell) {
                    var isActive = parseInt(cell.getAttribute('data-column'), 10) === self.sortIndex;

                    cell.classList.toggle('sa-table__head-column-cell--sorted', isActive);
                    cell.classList.toggle('sa-table__head-column-cell--desc', isActive && self.sortDir === 'desc');

                    if (cell.hasAttribute('aria-sort')) {
                        cell.setAttribute('aria-sort', isActive ? (self.sortDir === 'asc' ? 'ascending' : 'descending') : 'none');
                    }
                });
            },

            // Cost is proportional to what actually changed, not to table size.
            // Paging a 5,000-row table touches ~2 pages of rows, not 5,000.
            draw: function () {
                var start = settings.pagination ? (this.page - 1) * this.perPage : 0,
                    end = settings.pagination ? start + this.perPage : this.filtered.length,
                    next = this.filtered.slice(start, end);

                this.syncOrder();
                this.syncVisibility(next);

                this.toggleEmptyRow();
                this.renderPagination();
                this.updateInfo(start, Math.min(end, this.filtered.length));
            },

            // Only a sort changes document order. Filtering and paging do not, so
            // they never pay for moving nodes.
            syncOrder: function () {
                if (!this.orderDirty) {
                    return;
                }

                var fragment = document.createDocumentFragment();

                this.filtered.forEach(function (row) {
                    fragment.appendChild(row.tr);
                });

                this.body.appendChild(fragment);
                this.orderDirty = false;
            },

            // Diff the previous page against the next one and touch only the rows
            // that cross the boundary.
            syncVisibility: function (next) {
                next.forEach(function (row) {
                    row.staged = true;
                });

                this.visible.forEach(function (row) {
                    if (!row.staged) {
                        row.tr.classList.add('sa-table__body-row--hidden');
                    }
                });

                // Zebra parity has to count visible rows only — CSS :nth-child
                // would still count the rows pagination just hid.
                next.forEach(function (row, index) {
                    row.staged = false;
                    row.tr.classList.remove('sa-table__body-row--hidden');
                    row.tr.classList.toggle('sa-table__body-row--odd', index % 2 === 0);
                    row.tr.classList.toggle('sa-table__body-row--even', index % 2 === 1);
                });

                this.visible = next;
            },

            // The only node built in JS: a message row whose colspan depends on
            // the rendered column count.
            toggleEmptyRow: function () {
                if (this.filtered.length) {
                    if (this.emptyRow) {
                        this.emptyRow.remove();
                        this.emptyRow = null;
                    }
                    return;
                }

                if (this.emptyRow) {
                    return;
                }

                var columns = this.headCells.length || 1;

                this.emptyRow = document.createElement('tr');
                this.emptyRow.className = 'sa-table__empty';
                this.emptyRow.innerHTML = '<td colspan="' + columns + '"></td>';
                this.emptyRow.firstChild.textContent = settings.i18n.empty;
                this.body.appendChild(this.emptyRow);
            },

            renderPagination: function () {
                if (!this.pagination || !settings.pagination) {
                    return;
                }

                var pages = Math.ceil(this.filtered.length / this.perPage) || 1,
                    self = this;

                this.pagination.textContent = '';

                if (pages < 2) {
                    return;
                }

                this.pagination.appendChild(this.pageButton(settings.i18n.prev, this.page - 1, this.page === 1));

                this.pageNumbers(pages).forEach(function (page) {
                    if (page === '…') {
                        var gap = document.createElement('span');
                        gap.className = 'sa-table__page-gap';
                        gap.textContent = '…';
                        self.pagination.appendChild(gap);
                        return;
                    }

                    self.pagination.appendChild(self.pageButton(String(page), page, false, page === self.page));
                });

                this.pagination.appendChild(this.pageButton(settings.i18n.next, this.page + 1, this.page === pages));
            },

            pageButton: function (label, page, disabled, current) {
                var self = this,
                    button = document.createElement('button');

                button.type = 'button';
                button.className = 'sa-table__page' + (current ? ' sa-table__page--active' : '');
                button.textContent = label;

                if (disabled) {
                    button.disabled = true;
                }

                if (current) {
                    button.setAttribute('aria-current', 'page');
                }

                button.addEventListener('click', function () {
                    self.page = page;
                    self.draw();
                });

                return button;
            },

            // First, last, and a window around the current page — keeps the control
            // a fixed width however many pages there are.
            pageNumbers: function (pages) {
                var list = [],
                    from = Math.max(1, this.page - 1),
                    to = Math.min(pages, this.page + 1),
                    page;

                for (page = 1; page <= pages; page++) {
                    if (page === 1 || page === pages || (page >= from && page <= to)) {
                        list.push(page);
                    } else if (list[list.length - 1] !== '…') {
                        list.push('…');
                    }
                }

                return list;
            },

            updateInfo: function (start, end) {
                if (!this.info || !settings.info) {
                    return;
                }

                var total = this.filtered.length;

                this.info.textContent = settings.i18n.info
                    .replace('{start}', total ? start + 1 : 0)
                    .replace('{end}', end)
                    .replace('{total}', total);
            },

            runExport: function (action, button) {
                if (action === 'csv') {
                    this.download(this.toDelimited(','), settings.fileName + '.csv', 'text/csv');
                    return;
                }

                if (action === 'copy') {
                    this.copy(this.toDelimited('\t'), button);
                    return;
                }

                if (action === 'print') {
                    this.print();
                }
            },

            // Exports what the visitor is looking at: current filter, every page.
            toDelimited: function (separator) {
                var lines = [],
                    columns = settings.columns || [];

                if (columns.length) {
                    lines.push(columns.map(table.quote).join(separator));
                }

                this.filtered.forEach(function (row) {
                    var cells = Array.prototype.map.call(row.tr.cells, function (cell) {
                        return table.quote((cell.textContent || '').trim());
                    });

                    lines.push(cells.join(separator));
                });

                return lines.join('\n');
            },

            quote: function (value) {
                return /[",\t\n]/.test(value) ? '"' + value.replace(/"/g, '""') + '"' : value;
            },

            download: function (content, fileName, type) {
                var blob = new Blob(['﻿' + content], { type: type + ';charset=utf-8;' }),
                    url = URL.createObjectURL(blob),
                    link = document.createElement('a');

                link.href = url;
                link.download = fileName;
                document.body.appendChild(link);
                link.click();
                link.remove();
                URL.revokeObjectURL(url);
            },

            copy: function (content, button) {
                var original = button.textContent,
                    done = function () {
                        button.textContent = settings.i18n.copied;
                        setTimeout(function () {
                            button.textContent = original;
                        }, 1500);
                    };

                if (navigator.clipboard && navigator.clipboard.writeText) {
                    navigator.clipboard.writeText(content).then(done);
                    return;
                }

                var field = document.createElement('textarea');
                field.value = content;
                field.setAttribute('readonly', 'readonly');
                field.className = 'sa-table__clipboard';
                document.body.appendChild(field);
                field.select();
                document.execCommand('copy');
                field.remove();
                done();
            },

            // Print the filtered table in isolation, not the whole page.
            print: function () {
                var frame = document.createElement('iframe');

                frame.className = 'sa-table__print-frame';
                document.body.appendChild(frame);

                var doc = frame.contentWindow.document,
                    clone = this.table.cloneNode(true);

                Array.prototype.slice.call(clone.querySelectorAll('.sa-table__body-row--hidden, .sa-table__sort-icon')).forEach(function (node) {
                    node.remove();
                });

                doc.open();
                doc.write('<!doctype html><html><head><title>' + settings.fileName + '</title>');
                doc.write('<style>body{font-family:sans-serif}table{width:100%;border-collapse:collapse}th,td{border:1px solid #ddd;padding:8px;text-align:left}</style>');
                doc.write('</head><body></body></html>');
                doc.close();
                doc.body.appendChild(doc.importNode(clone, true));

                frame.contentWindow.focus();
                frame.contentWindow.print();

                setTimeout(function () {
                    frame.remove();
                }, 1000);
            },

            init: function () {
                if (!this.cacheDom()) {
                    return;
                }

                this.collectRows();

                // Export works on any table; the rest needs detachable rows.
                if (!this.isManaged()) {
                    this.bindExport();
                    return;
                }

                this.bind();

                // PHP already emitted the rows in this order, so there is nothing
                // to re-sort — only the header indicator to set.
                if (settings.sorting && settings.sortColumn >= 0) {
                    this.sortIndex = settings.sortColumn;
                    this.sortDir = settings.sortOrder === 'desc' ? 'desc' : 'asc';
                    this.markSortedColumn();
                }

                this.draw();
            }
        };

        table.init();
    };

    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/sky-table.default', widgetTable);
    });

}(jQuery, window.elementorFrontend));

/**
 * End table widget script
 */
