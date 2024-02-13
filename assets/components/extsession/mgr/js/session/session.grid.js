extsession.grid.Session = function (config) {
    config = config || {};

    this.exp = new Ext.grid.RowExpander({
        expandOnDblClick: false,
        enableCaching: true,
        tpl: '',
        renderer: function (v, p, record) {
            return '';
        }
    });

    this.sm = new Ext.grid.CheckboxSelectionModel();

    var columns = this.getColumns(config);
    this.filter = this.getFilter(columns);

    Ext.applyIf(config, {
        url: extsession.config['connectorUrl'],
        baseParams: {
            action: 'Session\\GetList',
        },
        autosave: false,
        fields: this.getFields(config),
        columns: columns,
        tbar: this.getTopBar(config),
        listeners: this.getListeners(config),

        sm: this.sm,
        plugins: [this.filter, this.exp],

        autoHeight: true,

        paging: true,
        pageSize: 20,
        remoteSort: true,
        remoteGroup: true,

        stateful: false,
        grouping: true,
        groupField: '',
        groupBy: 'undefined',
        sortBy: 'access',
        sortDir: 'DESC',
        showActionsColumn: false,
        actionsColumnWidth: 10,

        view: new Ext.grid.GroupingView({
            emptyText: config.emptyText || _('ext_emptymsg'),
            forceFit: true,
            autoFill: true,
            showPreview: true,
            enableRowBody: true,
            //enableNoGroups: false,
            scrollOffset: 0,
            enableGrouping: false,
            enableGroupingMenu: true,
            showGroupsText: 'Группировать',
            groupTextTpl: '{text} ({[values.rs.length]})',
            afterRenderUI: function () {
                Ext.grid.GroupingView.superclass.afterRenderUI.call(this);
                if (this.enableGroupingMenu && this.hmenu) {
                    if (this.enableNoGroups) {
                        this.hmenu.add({
                            itemId: 'showGroups',
                            text: this.showGroupsText,
                            checked: true,
                            checkHandler: this.onShowGroupsClick,
                            scope: this
                        });
                    }
                    this.hmenu.on('beforeshow', this.beforeMenuShow, this);
                }
            },
            onGroupByClick: function () {
                let groupField = this.cm.getDataIndex(this.hdCtxIndex);
                var grid = this.grid;
                let groupDir = 'ASC';
                let sortToggle = grid.store.sortToggle;
                if (sortToggle[groupField]) {
                    groupDir = sortToggle[groupField];
                }
                grid.store.multiSortInfo.sorters = [{
                    'field': groupField,
                    'direction': groupDir
                }]
                this.enableGrouping = true;
                grid.store.groupBy(groupField, true, groupDir);
                grid.fireEvent('groupchange', grid, grid.store.getGroupState());
                this.beforeMenuShow();
                this.refresh();
            },
            onShowGroupsClick: function (mi, checked) {
                this.enableGrouping = checked;
                if (checked) {
                    this.onGroupByClick();
                } else {
                    var grid = this.grid;
                    grid.store.groupField = false;
                    if (grid.remoteGroup) {
                        if (grid.store.baseParams) {
                            delete grid.store.baseParams.sort;
                            delete grid.store.baseParams.dir;
                        }
                        grid.store.setDefaultSort(grid.sortBy, grid.sortDir);
                        grid.store.reload();
                    } else {
                        grid.sort();
                    }
                    grid.fireEvent('datachanged', this);
                    grid.fireEvent('groupchange', this, null);
                }
            },
        }),
        viewConfig: {
            forceFit: true,
            enableRowBody: true,
            autoFill: true,
            showPreview: true,
            scrollOffset: 0
        },
        cls: 'extsession-grid main-wrapper modx-grid-small',
        bodyCssClass: 'grid-with-buttons',
    });
    extsession.grid.Session.superclass.constructor.call(this, config);

    this.getView().getRowClass = function (rec) {
        var cls = [];
        if (rec.json['user_bot'] !== undefined && rec.json['user_bot'] === true) {
            cls.push('extsession-row-danger');
        }
        return cls.join(' ');
    };

};
Ext.extend(extsession.grid.Session, MODx.grid.Grid, {
    windows: {},
    groupField: '',

    getFilter: function (columns) {
        var filters = [];
        columns.map(function (column) {
            if (column['filterable'] !== undefined && !column['filterable']) {
                return true;
            }
            if (['actions', ''].includes(column['dataIndex'])) {
                return true;
            }

            filters.push({
                type: 'string',
                dataIndex: column['dataIndex'],
                filterKey: column['filterKey'] ? column['filterKey'] : column['dataIndex'],
                disabled: false,
            });
        });

        return new Ext.ux.grid.GridFilters({
            menuFilterText: _('extsession_filter'),
            filters: filters,
            encode: true,
        });
    },

    getFields: function (config) {
        config = config || {};

        var fields = extsession.tools.cloneArray(extsession.config.grid_session_fields || []);
        Ext.iterate(config.excludeColumnFields || [], function (field, i) {
            fields.remove(field)
        });

        return fields;
    },

    getTopBarComponent: function (config) {
        config = config || {};

        var fields = ['menu', 'create', 'update', 'group', 'left', 'active', 'search', 'spacer'];
        Ext.iterate(config.excludeTopBarFields || [], function (field, i) {
            fields.remove(field)
        });

        return fields;
    },

    getTopBar: function (config) {
        var tbar = [];

        var add = {
            menu: {
                text: '<i class="icon icon-cogs"></i> ',
                menu: [{
                    text: '<i class="icon icon-trash-o"></i> ' + _('extsession_action_session_gc'),
                    cls: 'extsession-cogs',
                    handler: this.SessionGc,
                    scope: this
                },'-',{
                    text: '<i class="icon icon-trash-o"></i> ' + _('extsession_action_truncate'),
                    cls: 'extsession-cogs',
                    handler: this.Truncate,
                    scope: this
                }]
            },
            update: {
                text: '<i class="icon icon-refresh"></i>',
                handler: this._updateRow,
                scope: this
            },
            group: {
                text: '<i class="icon icon-object-group"></i>',
                handler: function (btn) {
                    let groupField = 'id';
                    if (this.view.hdCtxIndex) {
                        groupField = this.view.cm.getDataIndex(this.view.hdCtxIndex);
                    } else {
                        var hdCtxIndex = null;
                        var lookup = this.getColumnModel().lookup;
                        Object.keys(lookup).forEach(function (key, index) {
                            if (lookup[key]['dataIndex'] === groupField) {
                                hdCtxIndex = index + 1;
                            }
                        });
                        this.view.hdCtxIndex = hdCtxIndex;
                    }
                    let isGroup = false
                    if (groupField && groupField === this.store.groupField) {
                        isGroup = true;
                    } else {

                    }

                    if (isGroup) {
                        this.view.onShowGroupsClick(null, false);
                    } else {
                        this.view.onGroupByClick();
                    }
                }
            },
            left: '->',
            search: {
                xtype: 'extsession-field-search',
                width: 300,
                listeners: {
                    search: {
                        fn: function (field) {
                            this._doSearch(field);
                        },
                        scope: this
                    },
                    clear: {
                        fn: function (field) {
                            field.setValue('');
                            this._clearSearch();
                        },
                        scope: this
                    }
                }
            },
            spacer: {
                xtype: 'spacer',
                style: 'width:1px;'
            }
        };

        var fields = this.getTopBarComponent(config);
        Ext.iterate(fields, function (field, i) {
            if (add[field]) {
                tbar.push(add[field]);
            }
        });

        return tbar;
    },

    getColumns: function (config) {
        var columns = [this.exp, this.sm, this.exp];

        var add = {
            id: {
                width: 25,
                header: _('extsession_id'),
                hidden: false,
                sortable: true,
                groupable: false
            },
            access: {
                width: 15,
                header: _('extsession_access'),
                sortable: true,
            },
            user_agent: {
                width: 70,
                header: _('extsession_user_agent'),
                sortable: true,
            },
            user_bot: {
                width: 10,
                header: _('extsession_user_bot'),
                hidden: false,
                sortable: true,
                renderer: function (value) {
                    if (value) {
                        return _('yes');
                    }
                    return _('no');
                }
            },
            user_ip: {
                width: 20,
                header: _('extsession_user_ip'),
                sortable: true,
            },
            user_id: {
                width: 20,
                header: _('extsession_user_id'),
                sortable: true,
            },
            'Profile.fullname': {
                width: 30,
                header: _('extsession_user_fullname'),
                sortable: true,
                filterKey: 'Profile.fullname',
                renderer: function (value, metaData, record) {
                    var user_id = record['json']['user_id'];
                    if (user_id) {
                        return extsession.tools.userLink(value, user_id, true);
                    }
                    return value;
                }
            },
        };

        var fields = this.getFields(config);
        Ext.iterate(fields, function (field, i) {
            if (add[field]) {
                Ext.applyIf(add[field], {
                    header: _('extsession_header_' + field) || '<i class="icon icon-info"></i>',
                    tooltip: _('extsession_tooltip_' + field) || _('extsession_' + field),
                    dataIndex: field,
                    groupable: true,
                });
                columns.push(add[field]);
            }
        });

        return columns;
    },

    getListeners: function (config) {
        return Ext.applyIf(config.listeners || {}, {});
    },

    getMenu: function (grid, rowIndex) {
        var ids = this._getSelectedIds();
        var row = grid.getStore().getAt(rowIndex);
        var menu = extsession.tools.getMenu(row.json['actions'], this, ids);
        this.addContextMenuItem(menu);
    },

    onClick: function (e) {
        var elem = e.getTarget();
        if (elem.nodeName === 'BUTTON') {
            var row = this.getSelectionModel().getSelected();
            if (typeof (row) != 'undefined') {
                var action = elem.getAttribute('action');
                if (action == 'showMenu') {
                    var ri = this.getStore().find('id', row.id);
                    return this._showMenu(this, ri, e);
                } else if (typeof this[action] === 'function') {
                    this.menu.record = row.data;
                    return this[action](this, e);
                }
            }
        } else if (elem.nodeName === 'SPAN') {
            var row = this.getSelectionModel().getSelected();
            if (typeof (row) != 'undefined') {
                var action = elem.getAttribute('action');
                if (typeof this[action] === 'function') {
                    this.menu.record = row.json;
                    return this[action](this, elem, row);
                }
            }
        }

        return this.processEvent('click', e);
    },

    SessionGc: function () {
        extsession.Msg.confirm(
            _('extsession_action_session_gc'),
            _('extsession_confirm_session_gc'),
            function (val) {
                if (val == 'yes') {
                    MODx.Ajax.request({
                        url: extsession.config.connectorUrl,
                        params: {
                            action: 'Session\\GarbageCollect',
                        },
                        listeners: {
                            success: {
                                fn: function () {
                                    this.refresh();
                                },
                                scope: this
                            },
                            failure: {
                                fn: function (r) {
                                    if (r.message) {
                                        extsession.Msg.alert(_('error'), r.message);
                                    }
                                },
                                scope: this
                            }
                        }
                    })
                }
            },
            this
        );
    },
    Truncate: function () {
        extsession.Msg.confirm(
            _('extsession_action_truncate'),
            _('extsession_confirm_truncate'),
            function (val) {
                if (val == 'yes') {
                    MODx.Ajax.request({
                        url: extsession.config.connectorUrl,
                        params: {
                            action: 'Session\\Truncate',
                        },
                        listeners: {
                            success: {
                                fn: function () {
                                    this.refresh();
                                },
                                scope: this
                            },
                            failure: {
                                fn: function (r) {
                                    if (r.message) {
                                        extsession.Msg.alert(_('error'), r.message);
                                    }
                                },
                                scope: this
                            }
                        }
                    })
                }
            },
            this
        );
    },

    setAction: function (method, field, value) {
        var ids = this._getSelectedIds();
        if (!ids.length && (field !== 'false')) {
            return false;
        }
        MODx.Ajax.request({
            url: extsession.config.connectorUrl,
            params: {
                action: 'Session\\Multiple',
                method: method,
                field_name: field,
                field_value: value,
                ids: Ext.util.JSON.encode(ids)
            },
            listeners: {
                success: {
                    fn: function () {
                        this.refresh();
                    },
                    scope: this
                },
                failure: {
                    fn: function (r) {
                        if (r.message) {
                            extsession.Msg.alert(_('error'), r.message);
                        }
                    },
                    scope: this
                }
            }
        })
    },

    Remove: function () {
        extsession.Msg.confirm(
            _('extsession_action_remove'),
            _('extsession_confirm_remove'),
            function (val) {
                if (val == 'yes') {
                    this.setAction('Session\\Remove');
                }
            },
            this
        );
    },

    _doSearch: function (tf) {
        this.getStore().baseParams.query = tf.getValue();
        this.getBottomToolbar().changePage(1);
    },

    _clearSearch: function () {
        this.getStore().baseParams.query = '';
        this.getBottomToolbar().changePage(1);
    },

    _updateRow: function () {
        this.refresh();
    },

    _getSelectedIds: function () {
        var ids = [];
        var selected = this.getSelectionModel().getSelections();
        for (var i in selected) {
            if (!selected.hasOwnProperty(i)) {
                continue;
            }
            ids.push(selected[i]['id']);
        }
        return ids;
    }

});
Ext.reg('extsession-grid-session', extsession.grid.Session);


