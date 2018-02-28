$(document).ready(function(){
        init();
        var SHAKE_THRESHOLD = 3000;
        var last_update = 0;
        var signing = 0; // request state
        var x = y = z = last_x = last_y = last_z = 0;
        var MocuzMobile = {
            Android: function() {
                return /Android/i.test(navigator.userAgent);
            },
            BlackBerry: function() {
                return /BlackBerry/i.test(navigator.userAgent);
            },
            iOS: function() {
                return /iPhone|iPad|iPod/i.test(navigator.userAgent);
            },
            Windows: function() {
                return /IEMobile/i.test(navigator.userAgent);
            },
            any: function() {
                return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Windows());
            }
        };
        function init() {
            if (window.DeviceMotionEvent) {
                window.addEventListener('devicemotion', deviceMotionHandler, false);
            } else {
                alert('not support mobile event');
            }
        }
        function deviceMotionHandler(eventData) {
            var acceleration = eventData.accelerationIncludingGravity;
            var curTime = new Date().getTime();
            if ((curTime - last_update) > 100) {
                var diffTime = curTime - last_update;
                last_update = curTime;
                x = acceleration.x;
                y = acceleration.y;
                z = acceleration.z;
                var speed = Math.abs(x + y + z - last_x - last_y - last_z) / diffTime * 10000;
                if (speed > SHAKE_THRESHOLD) {
                        shaking();
                  }
                last_x = x;
                last_y = y;
                last_z = z;
            }
        }
//
    $('.sign_close').click(function(){
        $('.sign_box').css('display','none');
        $('.shake_cover').css('display','none');
    }); 

    $('.shake_top img').click(function(){
        $('.shake_top').trigger("click");
    })

    
        $('.shake_top').click(function(){
            shaking();
        })
   

    function shaking(){
                    if(MocuzMobile.Android()) {
                         window.commonjs.startVibrato();//android
//                            window.commonjs.music1();//android
                     }
                     if(MocuzMobile.iOS()) {
                         var cmd = "startVibrato";
                         window.location.href="objc://"+cmd;
//                            window.location.href="objc://"+cmd1;
                     }   
                    var content=  $(".sign_detail").html();
                    if(!content && signing == 0){
                        signing = 1;
                        $.ajax({
                            type: "POST",
                            url: siteurl+"index.php?mod=qiandao_sign&auth="+auth,
                            dataType: "html",
                            success: function(data){
                                $(".sign_detail").empty();
                                if(data!=1){
                                    $('.shake_cover').css('display','block');
                                    $('.sign_box').css('display','block');
                                    $(".sign_detail").append(data);
                                    
                                }else{
                                    $(".sign_detail").css('display','none');
                                    $('.shake_cover').css('display','block');
                                    $('.sign_error').css('display','block');
                                    $(".sign_error").append(data);
                                }
                                signing = 0;
                           },
                           error: function() {
                               signing = 0;
                           }
                        });
                    }else{
                        $('.shake_cover').css('display','block');
                        $('.sign_box').css('display','block');
                    }
                  }
     
});