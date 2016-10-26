
require(['jquery','domReady!'], function ($) {
    'use strict';
    $(document).ready(function(){
        try {
            updatePriceHtml();
        } catch (err) {
        }

        // Mobile menu
        $('.icon-nav-toggle').click(function(){
            $('body').toggleClass('nav-open');
            $(this).toggleClass('active');
            $('.menu-mobile-title .title').removeClass('active');
            $('.menu-mobile-title .nav-title-mobile').addClass('active');
            $('.navigation-mobile-wrap .menu-mobile-tab-content').css({"display":"none"});
            $('.navigation-mobile-wrap .nav-tab-mobile').css({"display":"block"});
        });

        $('.navigation-mobile-wrap nav.navigation > ul li.parent').has('ul').append( '<span class="touch-button"><span>open</span></span>' );

        $('.touch-button').click(function(){
            $(this).prev().slideToggle(200);
            $(this).toggleClass('active');
            $(this).parent().toggleClass('parent-active');
        });

        $('.menu-mobile-title .title').click(function(){
            $('.menu-mobile-title .title').removeClass('active');
            $(this).addClass('active');
            var content_tab = $(this).attr('data-content');
            $('.navigation-mobile-wrap .menu-mobile-tab-content').css({"display":"none"});
            $('.navigation-mobile-wrap .' +content_tab).css({"display":"block"});
        });

        /* for style info detail is out stock*/
        if($('.product-info-main .stock').hasClass('unavailable')) {
            $('.product-info-main .product-social-links').addClass('case-unavailable');
        }else {
            $('.product-info-main .product-social-links').addClass('case-available');
        }

        /* for Button checkout visual mobile shopping cart */
        $('.checkout-cart-index .control-checkout-custom').click(function() {
            $('.checkout-cart-index .checkout-methods-items .action.primary.checkout').trigger('click');
        });

        addAccordionFooter();
        moveUpsellProducts();
        moveWishlistButton();
        moveBreadcrumbOnShoppingCart();
        moveBreadcrumbOnLogin();
        moveBreadcrumbOnCreate();
        moveCartDiscountOnShopingCart();
        $('select').niceSelect();
        $(window).resize(function() {
            moveUpsellProducts();
            moveCartDiscountOnShopingCart();
        });
    });

    function updatePriceHtml()
    {
        $('.info-box-detail .price-box > .price-final_price span.price').each(function (item) {
            var html = $(this).text();
            if (html.search('<sup>') == -1 && html.search('</sup>') == -1 && html.charAt(0) == '$') {
                var html_currency = html.replace('$', '<sup>$</sup>');
                var html_price = '<span class="price">' + html_currency.replace('.', '<sup>.') + '</sup> </span>';
                jQuery(this).parent().html(html_price);
            }
        });

        $('.info-box-detail .price-box .special-price .price-wrapper  span.price').each(function (item) {
            var html = $(this).text();
            if (html.search('<sup>') == -1 && html.search('</sup>') == -1 && html.charAt(0) == '$') {
                var html_currency = html.replace('$', '<sup>$</sup>');
                var html_price = '<span class="price">' + html_currency.replace('.', '<sup>.') + '</sup> </span>';
                jQuery(this).parent().html(html_price);
            }
        });
    }

    function moveUpsellProducts(){
        var windowWidth = $(window).outerWidth();
        if (windowWidth < 992) {
            $('.block.upsell').insertBefore('.related-wrapper');
        } else {
            $('.block.upsell').insertAfter('.product.media');
        }
    }

    function moveWishlistButton(){
        $('.product-info-main .product-social-links.case-available').insertAfter('.product-options-bottom .box-tocart');
    }

    function moveBreadcrumbOnShoppingCart(){
        $('.checkout-cart-index .breadcrumbs').insertAfter('.checkout-cart-index .page-title-wrapper');
    }

    function moveBreadcrumbOnLogin(){
        $('.customer-account-login .breadcrumbs').insertAfter('.customer-account-login .page-title-wrapper');
    }

    function moveBreadcrumbOnCreate(){
        $('.customer-account-create .breadcrumbs').insertAfter('.customer-account-create .page-title-wrapper');
    }

    function moveCartDiscountOnShopingCart(){
        var windowWidth = $(window).outerWidth();
        if (windowWidth < 992) {
            $('.checkout-cart-index .cart-container .cart-discount').insertBefore('.checkout-cart-index .cart-container .form-cart');
        } else {
            $('.checkout-cart-index .cart-container .cart-discount').insertAfter('.checkout-cart-index .cart-container .form-cart');
        }
    }


    function addAccordionFooter(){
        /*$('.page-footer .footer.content .footer-content .footer-column > h5').each(function (item) {
            if($(this).next().length() > 0) {
                $(this).addClass('has-menu');
            }
        });*/

        var windowWidth = $(window).outerWidth();
        if (windowWidth < 992) {
            /* for Accodion Footer */
            $('.page-footer .footer.content .footer-content .footer-column:not(.first-child) > h5').click(function() {
                if (windowWidth < 992) {
                    if($(this).parent().hasClass('show')){
                        $(this).parent().removeClass('show');
                    }else{
                        $(this).parent().addClass('show');
                    }
                    $(this).parent().siblings('.show').removeClass('show');
                    /*$(this).next().slideToggle();*/
                }
            });
        }else{
            /*$('.page-footer .footer.content .footer-content .footer-column > h5 + *').css({"display":"block"});*/
        }
    }

});
