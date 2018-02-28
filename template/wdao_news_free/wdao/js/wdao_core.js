/*! Wdao!Design | wdao.cc 丨QQ：123855527*/


jQuery(function() {

    // 取消冒泡

    function cancelbuble(e) {

        if (e && e.stopPropagation) {

            //W3C取消冒泡事件

            e.stopPropagation();

        } else {

            //IE取消冒泡事件

            window.event.cancelBubble = true;

        }

    }



    /*-- 搜索 --*/

    jQuery('.search').on('click', function(event) {

        cancelbuble(event)

        jQuery('.menu .wdao_navigition').fadeOut('slow')

        jQuery('.search-input-hull').fadeIn('slow')

        jQuery(this).fadeOut('slow')

        jQuery('#nav-search-ipt').focus()

    })





    /*-- 关闭搜索框 --*/

    jQuery('.search-input-hull .search-cancel').on('click', function(event) {

        cancelbuble(event)

        jQuery(this).parent().fadeOut('slow')

        jQuery('.menu .wdao_navigition').fadeIn('slow')

        jQuery('.search').fadeIn('slow')

        jQuery('.search-content-list').empty()

        jQuery('.correl-link').addClass('hide')

        jQuery('#nav-search-ipt').val('')

    })

    jQuery('#nav-search-ipt').on('click', function(event) {

        cancelbuble(event)

    })



    jQuery('body').on('click', function() {

            jQuery('.menu .wdao_navigition').fadeIn('slow')

            jQuery('.search').fadeIn('slow')

            jQuery('.search-content-list').empty()

            jQuery('.correl-link').addClass('hide')

            jQuery('#nav-search-ipt').val('')

            jQuery('.search-input-hull').fadeOut('slow')

            jQuery('.wdao_navigition').fadeIn('slow')

    })



})