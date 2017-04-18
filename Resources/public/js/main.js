require.config({
    paths: {
        suluactivitylog: '../../suluactivitylog/js'
    }
});

define(function() {

    'use strict';

    return {

        name: "Sulu Activity Log Bundle",

        initialize: function(app) {

            app.components.addSource('suluactivitylog', '/bundles/suluactivitylog/js/components');

            app.sandbox.mvc.routes.push({
                route: 'activity-log',
                callback: function() {
                    return '<div data-aura-component="activitylog/list@suluactivitylog" data-aura-name="sulu" />';
                }
            });
        }
    };
});
