extsession.panel.Main = function (config) {
    config = config || {};
    Ext.apply(config, {
        baseCls: 'modx-formpanel',
        cls: 'extsession-formpanel',
        layout: 'anchor',
        hideMode: 'offsets',
        items: [{
            xtype: 'modx-header',
            html: _('extsession_main')
        },{
            xtype: 'extsession-grid-session',
            pageSize: 20
        }]
    });
    extsession.panel.Main.superclass.constructor.call(this, config);
};
Ext.extend(extsession.panel.Main, MODx.Panel);
Ext.reg('extsession-panel-main', extsession.panel.Main);