function img_bind(){
    $('.itm ul:last').bind('click', function(event) {
        var i,jsonstr;
        var array=[];
        var imglist=$(".img_detail");
        for(var i=0;i<imglist.length;i++){ 
           array.push($(imglist[i]).attr('data-original'));
        }
        var str = '{"imglist":["' + array.join('","') + '"]}'; 

        if (MocuzMobile.Android()) {
            window.myjs.openImage(this.src,str);//android
            return;
        }
       
       if (MocuzMobile.iOS()) {
            var cmd='openImage';
            window.location.href="objc://"+cmd+":/"+str+""
       }

       return false;
    })
}
$(function(){
    $('.img_detail').bind('click', function(event) {
        var i,jsonstr;
        var array=[];
        var imglist=$(".img_detail");
        for(var i=0;i<imglist.length;i++){ 
           array.push($(imglist[i]).attr('data-original'));
        }
        var str = '{"imglist":["' + array.join('","') + '"]}'; 

        if (MocuzMobile.Android()) {
            window.myjs.openImage(this.src,str);//android
            return;
        }
       
       if (MocuzMobile.iOS()) {
            var cmd='openImage';
            window.location.href="objc://"+cmd+":/"+str+""
       }

       return false;
    })
})