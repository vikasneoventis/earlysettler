define([
    'jquery',
    'flickity',
    'jquery/ui'
], function($, Flickity) {
'use strict';

    var debounce = function (func, wait, immediate) {
        var timeout;
        return function() {
            var context = this, args = arguments;
            var later = function() {
                timeout = null;
                if (!immediate) func.apply(context, args);
            };
            var callNow = immediate && !timeout;
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
            if (callNow) func.apply(context, args);
        };
    };


    $.widget('balance.box', {


        options: {
            mobileBreakpoint: 770
        },


        _create: function () {
            this._super();
            this._desktopImage = this.element.attr('data-desktop-image');
            this._mobileImage  = this.element.attr('data-mobile-image');
            this._setSrc();
            this._listen();
        },


        _setSrc: function () {
            if (this._mobileImage && this._desktopImage) {
                if (window.innerWidth <= this.options.mobileBreakpoint) {
                    this.element.attr('src', this._mobileImage);
                } else {
                    this.element.attr('src', this._desktopImage);
                }
            } else if (this._mobileImage) {
                this.element.attr('src', this._mobileImage);
            } else {
                this.element.attr('src', this._desktopImage);
            }
        },


        _listen: function () {
            if (this._mobileImage && this._desktopImage) {
                var ths = this;
                window.addEventListener('resize', debounce(function () {
                    if (window.innerWidth <= ths.options.mobileBreakpoint) {
                        ths.element.attr('src', ths._mobileImage);
                    } else {
                        ths.element.attr('src', ths._desktopImage);
                    }
                }, 200));
            }
        }


    });


    $.widget('balance.boxSlider', {


        options: {
            imgSelector:     'img',
            boxOptions:      {},
            flickityOptions: {
                imagesLoaded: true,
                autoPlay:     6000
            },
        },


        _create: function () {
            this._super();
            this._initImages();
            this._initSlider();
        },


        _initImages: function () {
            var ths = this;
            ths.element.find(ths.options.imgSelector).each(function (i, e) {
                $.balance.box(ths.options.boxOptions, $(e));
            });
        },


        _initSlider: function () {
            this.flickity = new Flickity(this.element.get(0), this.options.flickityOptions);
        }


    });


    return {
        'box':    $.balance.box,
        'slider': $.balance.boxSlider
    };

});
