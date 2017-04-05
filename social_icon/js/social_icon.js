(function ($, Drupal) {
    Drupal.behaviors.social_icon = {
        attach: function (context, settings) {
            $(context).find('img.social-img').hover(function () {
                var hover_icon_src = $(this).data('hover-icon');
                var icon_src = $(this).attr('src');
                if(hover_icon_src) {
                    $(this).attr('src', hover_icon_src);
                    $(this).data('hover-icon', icon_src);
                }
            },function() {
                var hover_icon_src = $(this).data('hover-icon');
                var icon_src = $(this).attr('src');
                if(hover_icon_src) {
                    $(this).attr('src', hover_icon_src);
                    $(this).data('hover-icon', icon_src);
                }
            });
        }
    };
})(jQuery, Drupal);