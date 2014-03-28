$(document).ready(function() {
    $('#file-list tr.ls').click(function(e) {
    	e.preventDefault();

        $('*').addClass('cursor-wait');

    	window.location = '?ls='+$(this).attr('data-href');
    });
    $('#file-list tr.dl').click(function(e) {
    	e.preventDefault();

        $('*').addClass('cursor-wait');

    	window.location = '?dl='+$(this).attr('data-href')+'&size='+$(this).attr('data-size');

        setTimeout(function() {
            $('*').removeClass('cursor-wait');
        }, 2000);
    });
    $('.breadcrumb a').click(function(e) {
        $('*').addClass('cursor-wait');
    });
    $('.active>a, .disabled>a').click(function(e) {
        e.preventDefault();
    });
});