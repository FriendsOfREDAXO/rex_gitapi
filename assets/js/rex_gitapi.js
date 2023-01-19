$(document).on('rex:ready', function (event, container) {
    container.find('.rex-docs-content').find('table').addClass('table table-striped table-hover');

    container.find("a[href^=http]").each(function () {
        if (this.href.indexOf(location.hostname) == -1) {
            $(this).attr({
                target: "_blank",
                title: "Opens in a new window"
            });
        }
    })
});
