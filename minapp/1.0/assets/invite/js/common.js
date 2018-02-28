    // 浏览器类型检测代码
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
    //跳转个人中心
    function lbsPerson(user_id){
        console.log(user_id);
        var user_id = '{"user_id":' + user_id + '}';
        if(MocuzMobile.Android()) {
            window.myjs.lbsPerson(user_id);//android
            return;
        }
        if(MocuzMobile.iOS()) {
            var cmd = "lbsPerson";
            window.location.href="objc://"+cmd+":/"+user_id;
            return;
        }
    }
