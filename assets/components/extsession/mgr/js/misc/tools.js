
extsession.tools.cloneArray = function (arr) {
    var i, copy;

    if (Array.isArray(arr)) {
        copy = arr.slice(0);
        for (i = 0; i < copy.length; i++) {
            copy[i] = extsession.tools.cloneArray(copy[i]);
        }
        return copy;
    } else if (typeof arr === 'object') {
        throw 'Cannot clone array containing an object!';
    } else {
        return arr;
    }
};


extsession.tools.renderActions = function (value, props, row) {
    var res = [];
    var cls, icon, title, action, item = '';
    for (var i in row.data.actions) {
        if (!row.data.actions.hasOwnProperty(i)) {
            continue;
        }
        var a = row.data.actions[i];
        if (!a['button']) {
            continue;
        }

        cls = a['cls'] ? a['cls'] : '';
        icon = a['icon'] ? a['icon'] : '';
        action = a['action'] ? a['action'] : '';
        title = a['title'] ? a['title'] : '';

        item = String.format(
            '<li class="{0}"><button class="btn btn-default_ {1}" action="{2}" title="{3}"></button></li>',
            cls, icon, action, title
        );

        res.push(item);
    }

    return String.format(
        '<ul class="extsession-row-actions">{0}</ul>',
        res.join('')
    );
};

extsession.tools.getMenu = function (actions, grid, selected) {
    var menu = [];
    var cls, icon, title, action = '';

    var has_delete = false;
    for (var i in actions) {
        if (!actions.hasOwnProperty(i)) {
            continue;
        }

        var a = actions[i];
        if (!a['menu']) {
            if (a == '-') {
                menu.push('-');
            }
            continue;
        } else if (menu.length > 0 && (/^sep/i.test(a['action']))) {
            menu.push('-');
            continue;
        }

        if (selected.length > 1) {
            if (!a['multiple']) {
                continue;
            } else if (typeof (a['multiple']) === 'string') {
                a['title'] = a['multiple'];
            }
        }

        cls = a['cls'] ? a['cls'] : '';
        icon = a['icon'] ? a['icon'] : '';
        title = a['title'] ? a['title'] : a['title'];
        action = a['action'] ? grid[a['action']] : '';

        menu.push({
            handler: action,
            text: String.format(
                '<span class="{0}"><i class="x-menu-item-icon {1}"></i>{2}</span>',
                cls, icon, title
            ),
            scope: grid
        });
    }

    return menu;
};

extsession.tools.renderDate = function (string) {

    return String.format(
        '<div class="extsession-date"><div>{0}</div></div>',
        Ext.util.Format.date(string, MODx.config['manager_date_format']),
    );
};

extsession.tools.renderDateTime = function (string) {

    return String.format(
        '<div class="extsession-date_"><div>{0} {1}</div></div>',
        Ext.util.Format.date(string, MODx.config['manager_date_format']),
        Ext.util.Format.date(string, 'H:i')
    );
};

extsession.tools.userLink = function (value, id) {
    if (!value) {
        return '';
    }
    else if (!id) {
        return value;
    }
    var action = MODx.action ? MODx.action['security/user/update'] : 'security/user/update';
    var url = 'index.php?a=' + action + '&id=' + id;

    return String.format('<a href="{0}" target="_blank" class="user-link green">{1}</a>', url, value);
};