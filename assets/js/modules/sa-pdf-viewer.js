;(function ($, elementor) {
    'use strict';

var widgetPdfViewer = function ($scope, $) {
    var $pdfViewer = $scope.find('.sa-pdf-viewer'),
        $settings = $pdfViewer.data('settings'),
        $options = $pdfViewer.data('pdf-settings');

    if (!$pdfViewer.length) {
        return;
    }

    PDFObject.embed($settings.pdfUrl, $settings.id, $options);
};
    jQuery(window).on('elementor/frontend/init', function () {
        elementorFrontend.hooks.addAction('frontend/element_ready/' + 'sky-pdf-viewer.default', widgetPdfViewer);
    });

}(jQuery, window.elementorFrontend));
