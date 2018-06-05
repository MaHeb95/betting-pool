$('#hoverMe').hover(function () {
    $('#tooltip').stop().fadeIn();
}, function () {
    $('#tooltip').stop().fadeOut();
});