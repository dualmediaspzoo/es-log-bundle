$(function() {
    function pullContentAndRedraw(href, $clickedElement)
    {
        let $block = $clickedElement.closest('.dm-es-logs-container').parent();
        $.get(href, function (response) {
            $block.html(response);
        });
    }

    // original content pull
    $('.ea .dm-es-logs-container.is-loader-only').each(function (i, e) {
        pullContentAndRedraw(
            $(e).attr('data-controller-url'),
            $(e)
        );
    });

    $('.ea').on('click', '.dm-es-logs-container .page-link', function(e) {
        e.preventDefault();
        let $this = $(this);
        let href = $this.attr('href');
        pullContentAndRedraw(href, $this);
    }).on('click', '.dm-es-logs-container .list-pagination-rows-per-page .dropdown-item', function(e) {
        e.preventDefault();
        let $this = $(this);
        let href = $this.attr('href');
        pullContentAndRedraw(href, $this);
    }).on('click', '.dm-es-logs-container .js-sort a', function(e) {
        e.preventDefault();
        let $this = $(this);
        let href = $this.attr('href');
        pullContentAndRedraw(href, $this);
    });
});