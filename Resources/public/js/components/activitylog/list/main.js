define(['text!./list.html'], function(list) {

    var defaults = {
        templates: {
            list: list
        }
    };

    var ready = false;

    var total = 0;

    var exportActivityLog = function() {
        if (ready) {
            window.location = '/admin/api/activity-log/export?limit=' + total;
        }
    };

    return {

        defaults: defaults,

        header: {
            title: 'navigation.activity_log',
            underline: false,

            toolbar: {
                buttons: {
                    deleteSelected: {}
                }
            }
        },

        layout: {
            content: {
                width: 'max'
            }
        },

        initialize: function() {
            this.render();

            this.bindDomEvents();
            this.bindCustomEvents();
        },

        render: function() {
            this.$el.html(this.templates.list());

            this.sandbox.sulu.initListToolbarAndList.call(this,
                'activitylog',
                '/admin/api/activity-log/fields',
                {
                    el: this.$find('#list-toolbar-container'),
                    instanceName: 'activitylog',
                    template: this.sandbox.sulu.buttons.get({
                        exportButton: {
                            options: {
                                id: 'export-button',
                                icon: 'download',
                                callback: exportActivityLog,
                                class: 'disabled'
                            }
                        },
                        settings: {
                            options: {
                                dropdownItems: [
                                    {
                                        type: 'columnOptions'
                                    }
                                ]
                            }
                        }
                    })
                },
                {
                    el: this.sandbox.dom.find('#activity-log-list'),
                    url: '/admin/api/activity-log',
                    searchInstanceName: 'activitylog',
                    searchFields: ['title'],
                    resultKey: 'activity-log-items',
                    instanceName: 'activitylog',
                    viewOptions: {
                        table: {
                            actionIconColumn: 'title'
                        }
                    }
                }
            );

            // use total count of rows for loading export
            this.sandbox.on('husky.datagrid.activitylog.loaded', function(data) {
                ready = true;
                total = data.total;
                $('[data-id="exportButton"]').removeClass('disabled');
            });
        },

        deleteItems: function(ids) {
            for (var i = 0, length = ids.length; i < length; i++) {
                this.deleteItem(ids[i]);
            }
        },

        deleteItem: function(id) {
            this.sandbox.util.save('/admin/api/activity-log/' + id, 'DELETE').then(function() {
                this.sandbox.emit('husky.datagrid.activity-log.record.remove', id);
            }.bind(this));
        },

        bindDomEvents: function() {
        },

        bindCustomEvents: function() {
            this.sandbox.on('husky.datagrid.activity-log.number.selections', function(number) {
                var postfix = number > 0 ? 'enable' : 'disable';
                this.sandbox.emit('sulu.header.toolbar.item.' + postfix, 'deleteSelected', false);
            }.bind(this));

            this.sandbox.on('sulu.toolbar.delete', function() {
                this.sandbox.emit('husky.datagrid.activity-log.items.get-selected', this.deleteItems.bind(this));
            }.bind(this));
        }
    };
});
