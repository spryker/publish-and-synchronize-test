/**
 * Copyright (c) 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

'use strict';

var dataTable = require('ZedGuiModules/libs/data-table');

var BlocksTable = function (options) {
    var _self = this;
    this.tableBaseUrl = '';
    this.blocksTableSelector = '';
    this.cmsSlotBlocksSelector = '';
    this.cmsSlotBlocksOverlaySelector = '';
    this.cmsSlotBlocksOverlayTogglerClass = '';
    this.viewBlockUrl = '';
    this.$cmsSlotBlocks = {};
    this.$blocksTable = {};
    this.slotBlocksForm = {};
    this.blocksChoiceFormSelector = '';
    this.$blocksChoiceDropDown = '';
    this.initOptionsState = [];
    this.initTableState = [];
    this.isFirstInit = true;
    this.isFirstTableRender = true;
    this.changeOrderButtonSelector = '.btn[data-direction]';
    this.removeButtonSelector = '.js-slot-block-remove-button';
    this.resetButtonSelector = '.js-slot-block-reset-button';
    this.selectedRowIndex = 0;

    $.extend(this, options);

    this.init = function () {
        _self.$blocksTable = $(_self.blocksTableSelector);
        _self.initTableState = [];
        _self.$cmsSlotBlocks = $(_self.cmsSlotBlocksSelector);
        _self.$blocksChoiceDropDown = $(_self.blocksChoiceFormSelector).find('select');
        _self.isFirstTableRender = true;
        if (!_self.isFirstInit) {
            return;
        }
        $(document).on('savedBlocksForm', function () {
            _self.getInitTableState(_self.$blocksTable.data('ajax'));
            _self.toggleResetButton(false);
            _self.tableRowSelect();
        });
        _self.isFirstInit = false;
        _self.setInitOptionsState();
    };

    this.loadBlocksTable = function (params, idCmsSlotTemplate, idCmsSlot) {
        _self.idCmsSlotTemplate = idCmsSlotTemplate;
        _self.idCmsSlot = idCmsSlot;

        var ajaxUrl = _self.tableBaseUrl + '?' + params;
        _self.$blocksTable.data('ajax', ajaxUrl);
        _self.getInitTableState(ajaxUrl);
        _self.$blocksTable.DataTable({
            destroy: true,
            ajax: {
                cache: false
            },
            autoWidth: false,
            language: dataTable.defaultConfiguration.language,
            searching: false,
            info: false,
            drawCallback: function() {
                _self.initDataTableListeners(idCmsSlotTemplate, idCmsSlot);
                _self.tableRowSelect();
            },
        });
    };

    this.initDataTableListeners = function (idCmsSlotTemplate, idCmsSlot) {
        _self.$blocksTable.on('processing.dt', function () {
            _self.overlayToggler(true);
        });
        _self.slotBlocksForm.rebuildForm(idCmsSlotTemplate, idCmsSlot, _self.$blocksTable.DataTable().rows().data(), _self.isUnsaved());
        if (!_self.isFirstTableRender) {
            _self.toggleResetButton();
            return;
        }
        _self.isFirstTableRender = false;
        _self.initActionButtonsListeners();
        $(_self.$blocksTable).find('tbody').on('click', 'tr', _self.tableRowSelect);
    };

    this.initActionButtonsListeners = function () {
        _self.$blocksTable.on('click', _self.changeOrderButtonSelector, _self.changeOrderButtonsHandler.bind(this));
        _self.$blocksTable.on('click', _self.removeButtonSelector, _self.removeButtonsHandler.bind(this));
        _self.$cmsSlotBlocks.on('click', _self.resetButtonSelector, _self.resetButtonsHandler.bind(this));
    };

    this.updateTable = function (tableApi, tableData) {
        tableApi.rows().remove();
        tableApi.rows.add(tableData).draw();
        tableApi.rows( { selected: true } ).deselect();
        tableApi.row(_self.selectedRowIndex).select();
    };

    this.getInitTableState = function (url) {
        $.get(url).done(function (response) {
            _self.initTableState = response.data;
        });
    };

    this.addRow = function (rowData = {}) {
        rowData = [
            Number(rowData.blockId),
            rowData.blockName,
            rowData.validFrom,
            rowData.validTo,
            _self.getStatusLabel(rowData.isActive),
            _self.getStoresLabels(rowData.stores),
            _self.getActionButtons(rowData.blockId),
        ];

        var table = _self.getTable();
        table.data.unshift(rowData);
        _self.updateTable(table.api, table.data);
    };

    this.getActionButtons = function(blockId) {
        var $buttons = $(_self.$cmsSlotBlocks.data('actions-buttons-template'));
        var buttonsTemplate = '';

        $buttons.each(function() {
            var button = $(this);

            if (!button.is('a')) {
                return;
            }

            if (button.hasClass('btn-view')) {
                button.attr('href', _self.viewBlockUrl + '?id-cms-block=' + blockId);
            }

            buttonsTemplate += button[0].outerHTML + ' ';
        });

        return buttonsTemplate;
    };

    this.getStatusLabel = function (isActive) {
        var statusLabel = isActive ? 'active-label-template' : 'inactive-label-template';

        return _self.$cmsSlotBlocks.data(statusLabel);
    };

    this.getStoresLabels = function (stores) {
        var $storeTemplate = $(_self.$cmsSlotBlocks.data('active-label-template'));
        var storesArray = stores.split(',');

        return storesArray.reduce(function (storesTemplate, store) {
            return storesTemplate + $storeTemplate.html(store)[0].outerHTML + ' ';
        }, '');
    };

    this.getTable = function () {
        return {
            api: _self.$blocksTable.dataTable().api(),
            data: _self.$blocksTable.dataTable().api().data().toArray(),
        }
    };

    this.changeOrderButtonsHandler = function (event) {
        var clickInfo = _self.getClickInfo(event);
        var direction = clickInfo.$button.data('direction');
        var isRowFirst = clickInfo.$clickedTableRow === 0;
        var isRowLast = clickInfo.$clickedTableRow === clickInfo.$tableLength - 1;

        if (isRowFirst && direction === 'up' || isRowLast && direction === 'down') {
            return;
        }

        _self.changeOrderRow(clickInfo.$clickedTableRow, direction);
    };

    this.changeOrderRow = function (rowIndex, direction) {
        var table = _self.getTable();
        var newRowIndex = rowIndex;

        switch (direction) {
            case 'up':
                newRowIndex = rowIndex - 1;
                var tempRow = table.data[newRowIndex];
                table.data[newRowIndex] = table.data[rowIndex];
                table.data[rowIndex] = tempRow;
                break;

            case 'down':
                newRowIndex = rowIndex + 1;
                var tempRow = table.data[newRowIndex];
                table.data[newRowIndex] = table.data[rowIndex];
                table.data[rowIndex] = tempRow;
                break;
        }

        _self.updateTable(table.api, table.data);
    };

    this.removeButtonsHandler = function (event) {
        var clickInfo = _self.getClickInfo(event);
        var table = _self.getTable();
        var rowName = table.data[clickInfo.$clickedTableRow][1];
        _self.updateChoiceDropdown(rowName);
        table.data.splice(clickInfo.$clickedTableRow, 1);
        _self.updateTable(table.api, table.data);
    };

    this.setInitOptionsState = function () {
        _self.$blocksChoiceDropDown.find('option').each(function () {
            _self.initOptionsState.push($(this).prop('disabled'));
        });
    };

    this.updateChoiceDropdown = function (optionLabel) {
        _self.$blocksChoiceDropDown.children('option[disabled]')
            .filter(function() { return $(this).text() === optionLabel })
            .prop('disabled', false);
        _self.$blocksChoiceDropDown.select2();
    };

    this.resetChoiceDropdown = function () {
        _self.$blocksChoiceDropDown.children('option').each(function (index) {
            $(this).prop('disabled', _self.initOptionsState[index]);
        });
        _self.$blocksChoiceDropDown.select2();
    };

    this.resetHandlerCallback = function () {};

    this.resetButtonsHandler = function () {
        if (!_self.isUnsaved()) {
            return;
        }

        _self.resetHandlerCallback();
    };

    this.toggleResetButton = function (state = _self.isUnsaved()) {
        $(_self.resetButtonSelector).toggleClass('hidden', !state);
    };

    this.getClickInfo = function(event) {
        return {
            $button: $(event.currentTarget),
            $clickedTableRow: $(event.currentTarget).parents('tr').index(),
            $tableLength: $(event.currentTarget).parents('tbody').children('tr').length,
        }
    };

    this.isUnsaved = function () {
        if (_self.slotBlocksForm.isStateChanged) {
            return _self.slotBlocksForm.isStateChanged;
        }

        var initTableState = _self.initTableState;
        var currentTableState = _self.getTable().data;

        if (initTableState.length !== currentTableState.length) {
            return true;
        }

        return initTableState.some(function (item, index) {
            return item[0] !== currentTableState[index][0]
        });
    };

    this.overlayToggler = function (state) {
        $(_self.cmsSlotBlocksOverlaySelector).toggleClass(_self.cmsSlotBlocksOverlayTogglerClass, state);
    };

    this.tableRowSelect = function (element) {
        var cellIndex = _self.selectedRowIndex;
        if ($(_self.$blocksTable).DataTable().rows().count() < 1) {
            return;
        }

        if (element !== undefined && $(element.target).is('td')) {
            cellIndex = $(this).index();
            _self.selectedRowIndex = cellIndex;
        }

        var row = _self.$blocksTable.DataTable().row(cellIndex);
        _self.$blocksTable.DataTable().rows().deselect();
        row.select();
        var idCmsBlock = row.data()[0];

        $('.js-cms-slot-block-form-item').hide();
        $('#js-cms-slot-block-form-item-' + idCmsBlock).show();
    };
};

module.exports = BlocksTable;
