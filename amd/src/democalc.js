/**
 * Created by dtomefer on 12/11/16.
 */
define(['jquery', 'core/ajax', 'jqueryui'], function ($, ajax) {
    return {
        initialise: function () {
            $('#local-gradebook-demo-calculate').click(function () {
                var sesskey = $('#local-demo-sesskey').val();
                var courseid = $('#local-demo-courseid').val();
                var pageload = $('#local-demo-timepageload').val();
                var report = $('#local-demo-report').val();
                var page = $('#local-demo-page').val();
                window.console.log(sesskey + '-' + courseid + '-' + pageload + '-' + report + '-' + page);
                var promises = ajax.call([
                    {
                        methodname: 'local_gradebook_get_demo_calc',
                        args: {sesskey: sesskey, id: courseid, timepageload: pageload, report: report, page: page}
                    }
                ]);
                promises[0].done(function (response) {
                    window.console.log(response);
                    $.each(response, function(i, grade) {
                        window.console.log(grade.id);
                        $('#grade-'+grade.id).val(grade.value);
                    });
                }).fail(function (ex) {
                    window.console.log(ex);
                });
            });
        }
    };
});