(function ($) {
  $(document).ready(function () {
    /**
     * Black Friday Promotion Notice Transient 
     * 
     * sky_black_friday_notice
     * sky_black_friday_notice is-dismissible
     * 
     * action - sky_black_friday_notice_dismiss
     */
    const isFlackFridayNotice = $(document).find('.sky_black_friday_notice');

    if (isFlackFridayNotice[0]) {
      $(document).on('click', '.sky_black_friday_notice .notice-dismiss', function (e) {
        e.preventDefault();
        let data = {
          'action': 'sky_black_friday_notice_dismiss'
        };
        $.post(ajaxurl, data, function (response) {
          console.log(response);
        });
      });
    }

  });

})(jQuery);
