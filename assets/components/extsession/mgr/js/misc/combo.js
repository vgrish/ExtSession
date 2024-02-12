extsession.Msg =  {
    alert : function (title, text) {
        let d= Ext.Msg.show({
            title: title,
            msg: text,
            buttons: Ext.Msg.OK,
            minWidth: 400
        });
        d.getDialog().getEl().addClass('x-window-extsession');
    },
    confirm: function (title, text, fn, scope) {
        let d= Ext.Msg.show({
            title: title,
            msg: text,
            buttons: Ext.Msg.YESNO,
            icon: Ext.Msg.QUESTION,
            minWidth: 400,
            fn: fn,
            scope : scope,
        });
        d.getDialog().getEl().addClass('x-window-extsession');
    },
};

extsession.combo.ComboBoxDefault = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        assertValue: function () {
            var val = this.getRawValue(),
                rec;
            if (this.valueField && Ext.isDefined(this.value)) {
                rec = this.findRecord(this.valueField, this.value);
            }
            /* fix for https://github.com/bezumkin/extsession/pull/350
            if(!rec || rec.get(this.displayField) != val){
                rec = this.findRecord(this.displayField, val);
            }*/
            if (!rec && this.forceSelection) {
                if (val.length > 0 && val != this.emptyText) {
                    this.el.dom.value = Ext.value(this.lastSelectionText, '');
                    this.applyEmptyText();
                } else {
                    this.clearValue();
                }
            } else {
                if (rec && this.valueField) {
                    if (this.value == val) {
                        return;
                    }
                    val = rec.get(this.valueField || this.displayField);
                }
                this.setValue(val);
            }
        },
    });

    Ext.apply(config.listeners || {}, {
        beforequery: {
            fn: this.beforequery,
            scope: this
        },
    });

    extsession.combo.ComboBoxDefault.superclass.constructor.call(this, config);
};
Ext.extend(extsession.combo.ComboBoxDefault, MODx.combo.ComboBox, {
    beforequery: function (o) {
        if (o.combo.fields) {
            o.combo.store.baseParams.fields = Ext.util.JSON.encode(o.combo.fields);
        }
    },
});
Ext.reg('extsession-combo-combobox-default', extsession.combo.ComboBoxDefault);

extsession.combo.Search = function (config) {
    config = config || {};
    Ext.applyIf(config, {
        xtype: 'twintrigger',
        ctCls: 'x-field-search',
        allowBlank: true,
        msgTarget: 'under',
        emptyText: _('search'),
        name: 'query',
        triggerAction: 'all',
        clearBtnCls: 'x-field-search-clear',
        searchBtnCls: 'x-field-search-go',
        onTrigger1Click: this._triggerSearch,
        onTrigger2Click: this._triggerClear
    });
    extsession.combo.Search.superclass.constructor.call(this, config);
    this.on('render', function () {
        this.getEl().addKeyListener(Ext.EventObject.ENTER, function () {
            this._triggerSearch();
        }, this);
    });
    this.addEvents('clear', 'search');
};
Ext.extend(extsession.combo.Search, Ext.form.TwinTriggerField, {

    initComponent: function () {
        Ext.form.TwinTriggerField.superclass.initComponent.call(this);
        this.triggerConfig = {
            tag: 'span',
            cls: 'x-field-search-btns',
            cn: [{
                tag: 'div',
                cls: 'x-form-trigger ' + this.searchBtnCls
            }, {
                tag: 'div',
                cls: 'x-form-trigger ' + this.clearBtnCls
            }]
        };
    },

    _triggerSearch: function () {
        this.fireEvent('search', this);
    },

    _triggerClear: function () {
        this.fireEvent('clear', this);
    }

});
Ext.reg('extsession-field-search', extsession.combo.Search);

extsession.combo.DateTime = function (config) {
    config = config || {};

    Ext.applyIf(config, {
        timePosition: 'right',
        allowBlank: true,
        hiddenFormat: 'Y-m-d H:i:s',
        dateFormat: MODx.config['manager_date_format'] || 'Y-m-d',
        timeFormat: 'H:i',
        cls: 'date-combo',
        ctCls: 'date-combo',
        timeWidth: 80,
        dateWidth: 120,
        timeIncrement: 60,
        selectOnFocus: true,
    });

    extsession.combo.DateTime.superclass.constructor.call(this, config);
};
Ext.extend(extsession.combo.DateTime, Ext.ux.form.DateTime, {});
Ext.reg('extsession-combo-datetime', extsession.combo.DateTime);
