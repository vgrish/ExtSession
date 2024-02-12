var extsession = function (config) {
    config = config || {};
    extsession.superclass.constructor.call(this, config);
};
Ext.extend(extsession, Ext.Component, {
    page: {}, window: {}, grid: {}, tree: {}, panel: {}, combo: {}, field: {}, config: {}, view: {}, tools: {}
});
Ext.reg('extsession', extsession);

extsession = new extsession();