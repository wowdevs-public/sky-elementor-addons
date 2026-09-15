(function ($) {

    'use strict';

    elementor.hooks.addFilter("panel/elements/regionViews", function (panel) {

        if (SkyAddonsEditorConfig.pro_installed || SkyAddonsEditorConfig.promotional_widgets <= 0) return panel;

        var promotionalWidgetHandler,
            promotionalWidgets = SkyAddonsEditorConfig.promotional_widgets,
            elementsCollection = panel.elements.options.collection,
            categories = panel.categories.options.collection,
            categoriesView = panel.categories.view,
            elementsView = panel.elements.view,
            freeCategoryIndex, proWidgets = [];


        _.each(promotionalWidgets, function (widget, index) {
            elementsCollection.add({
                name: widget.name,
                title: widget.title,
                icon: widget.icon,
                categories: widget.categories,
                editable: false
            })
        });

        elementsCollection.each(function (widget) {
            "sky-elementor-addons-pro" === widget.get("categories")[0] && proWidgets.push(widget)
        });

        freeCategoryIndex = categories.findIndex({
            name: "sky-elementor-addons"
        });

        freeCategoryIndex && categories.add({
            name: "sky-elementor-addons-pro",
            title: "Sky Addons Pro",
            defaultActive: !1,
            items: proWidgets
        }, {
            at: freeCategoryIndex + 1
        });

        promotionalWidgetHandler = {

            getWedgetOption: function (name) {
                return promotionalWidgets.find(function (item) {
                    return item.name == name;
                });
            },

            className: function () {
                var className = 'elementor-element-wrapper';

                if (!this.isEditable()) {
                    className += ' elementor-element--promotion';
                }
                return className;
            },

            onMouseDown: function () {
                void this.constructor.__super__.onMouseDown.call(this);
                var promotion = this.getWedgetOption(this.model.get("name"));
                elementor.promotion.showDialog({
                    title: sprintf(wp.i18n.__('%s', 'elementor'), this.model.get("title")),
                    content: sprintf(wp.i18n.__('Use %s widget and dozens more pro features to extend your toolbox and build sites faster and better.', 'elementor'), this.model.get("title")),
                    targetElement: this.el,
                    position: {
                        blockStart: '-7'
                    },
                    actionButton: {
                        url: promotion.action_button.url,
                        text: promotion.action_button.text,
                        classes: promotion.action_button.classes || ['elementor-button', 'elementor-button-success']
                    }
                })
            }
        }

        panel.elements.view = elementsView.extend({
            childView: elementsView.prototype.childView.extend(promotionalWidgetHandler)
        });

        panel.categories.view = categoriesView.extend({
            childView: categoriesView.prototype.childView.extend({
                childView: categoriesView.prototype.childView.prototype.childView.extend(promotionalWidgetHandler)
            })
        });

        return panel;
    });

    // Widget List control for Equal Height — populates select2 with child widgets of the section/container
    var SaWidgetList = elementor.modules.controls.Select2.extend({
        onBeforeRender: function () {
            if (!this.container) return;

            var type = this.container.type,
                widgetsConfig = elementor.widgetsCache || elementor.config.widgets,
                widgets = {};

            if (type === 'section') {
                this.container.children.forEach(function (column) {
                    var $widgets = column.view.$childViewContainer.children('[data-widget_type]');
                    $widgets.each(function (i, widget) {
                        var name = $(widget).data('widget_type');
                        name = name.slice(0, name.lastIndexOf('.'));
                        var config = widgetsConfig[name];
                        if (config) {
                            widgets[config.widget_type] = config.title + ' (' + config.widget_type + ')';
                        }
                    });
                });
            }

            if (type === 'container') {
                this.container.children.forEach(function (child) {
                    var $widgets = child.view.$el.data('element_type') === 'widget'
                        ? child.view.$el
                        : child.view.$el.find('div[data-element_type="widget"]');
                    $widgets.each(function (i, widget) {
                        if ($(widget).data('element_type') === 'widget') {
                            var name = $(widget).data('widget_type');
                            name = name.slice(0, name.lastIndexOf('.'));
                            var config = widgetsConfig[name];
                            if (config) {
                                widgets[config.widget_type] = config.title + ' (' + config.widget_type + ')';
                            }
                        }
                    });
                });
            }

            this.model.set('options', widgets);
        }
    });

    elementor.addControlView('sa-widget-list', SaWidgetList);

}(jQuery));