(function($) {
  $(document).ready(function() {
    if ($(document).find(".sky_black_friday_notice")[0]) {
      $(document).on("click", ".sky_black_friday_notice .notice-dismiss", function(e) {
        e.preventDefault();
        $.post(
          ajaxurl,
          { action: "sky_black_friday_notice_dismiss" },
          function(response) {
            console.log(response);
          }
        );
      });
    }
  });
})(jQuery);
