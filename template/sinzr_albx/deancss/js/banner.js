var jq=jQuery.noConflict();

var jqpfoc=jq("#jqpfoc");
var jqnsp=jqpfoc.find("ul:first");
var jqnrbt=jqpfoc.find("ul:last");
var l_btn=jq("#prevSlide i");
var r_btn=jq("#nextSlide i");
var len=jqnsp.find("li").length;
var nmyTime=0;var nsig=0;
jqnrbt.find("li:first").addClass("current");
jqnrbt.find("li").each(function(x02){
  jq(this).mouseover(function(){
  jqnsp.find("li").eq(x02).stop(true,true).find("img").fadeIn("2000").end().show().siblings().hide();
  jq(this).addClass("current").siblings().removeClass("current");
  nsig=x02;
  //alert(nsig)
})});
//jqnrbt.find("li:first").mouseover();
if(len == 6 && "undefined" != typeof luckyDay6First && luckyDay6First == true) {//用于焦点图从第一帧开始正常显示
         jqnrbt.find("li:last").mouseover();
}else {
         jqnrbt.find("li:first").mouseover();
}
nmyTime=setInterval(function(){
  nsig++;
  if(nsig==len){
          nsig=0;
          jqnsp.find("li").eq(nsig).stop(true,true).find("img").fadeIn("2000").end().show().siblings().hide();
          jqnrbt.find("li").eq(nsig).addClass("current").siblings().removeClass("current");
          }
        else{
          jqnsp.find("li").eq(nsig).stop(true,true).find("img").fadeIn("2000").end().show().siblings().hide();
          jqnrbt.find("li").eq(nsig).addClass("current").siblings().removeClass("current");
          }
  },3000);
jqpfoc.mouseenter(function(){clearInterval(nmyTime)})
jqpfoc.mouseleave(function(){
      nmyTime=setInterval(
        function(){
          nsig++;
          if(nsig==len){
            nsig=0;
            jqnsp.find("li").eq(nsig).stop(true,true).find("img").fadeIn("2000").end().show().siblings().hide();
            jqnrbt.find("li").eq(nsig).addClass("current").siblings().removeClass("current");
            }
          else{
            jqnsp.find("li").eq(nsig).stop(true,true).find("img").fadeIn("2000").end().show().siblings().hide();
            jqnrbt.find("li").eq(nsig).addClass("current").siblings().removeClass("current");
            }
          },3000)
          }
        );
l_btn.click(function(){
  nsig--;
          if(nsig<0){
            nsig=len-1;
            jqnsp.find("li").eq(nsig).stop(true,true).find("img").fadeIn("2000").end().show().siblings().hide();
            jqnrbt.find("li").eq(nsig).addClass("current").siblings().removeClass("current");
            }
          else{
            jqnsp.find("li").eq(nsig).stop(true,true).find("img").fadeIn("2000").end().show().siblings().hide();
            jqnrbt.find("li").eq(nsig).addClass("current").siblings().removeClass("current");
            }
  })
r_btn.click(function(){
  nsig++;
          if(nsig==len){
            nsig=0;
            jqnsp.find("li").eq(nsig).stop(true,true).find("img").fadeIn("2000").end().show().siblings().hide();
            jqnrbt.find("li").eq(nsig).addClass("current").siblings().removeClass("current");
            }
          else{
            jqnsp.find("li").eq(nsig).stop(true,true).find("img").fadeIn("2000").end().show().siblings().hide();
            jqnrbt.find("li").eq(nsig).addClass("current").siblings().removeClass("current");
            }
  })