function get_uids(uid){
         $.ajax({
             type: "POST",
             url: siteurl+"index.php?mod=viewthread&get_uids=1&uid="+  uid,
             dataType: "html",
             success: function(data){
                 var datas=eval("("+data+")");
                 if(datas){
                     reward_socre.socre_name = datas.socre_name;
                     reward_socre.credits = datas.credits;
                     reward_socre.score_list = datas.score_list;	 
                 }
             }
        });
        get_uid = uid;
    }
    function test_tid_Praise(id,idtype,type,uid){
        if(!uid){
            if(!get_uid){
                if(MocuzMobile.Android()) {
                    window.myjs.get_uid();//android
					return;
                }
                if(MocuzMobile.iOS()) {
                     window.location.href="objc://get_uid";
					 return;
                }
            }
            uid = get_uid;
        }
        $.ajax({
             type: "POST",
             url: siteurl+"index.php?mod=praisethread&id="+id+"&idtype="+idtype+"&type="+type+"&uid="+  uid,
             dataType: "html",
             success: function(data){
                 var datas=eval("("+data+")");
                 if(datas.status==1){
					 alert(datas.praisenum)
                    var follow_view = "<div class='likea' ><img src='assets/images/good_b.png' onclick=test_tid_Praise(" +id+ ",'" +idtype+ "','delete','" +uid+ "')></div>\n\
<p>"+datas.praisenum+" </p>"; 
                    $(".likea").remove();
                    $(".content_zan p").remove();
                    $(".content_zan").html(follow_view);
                    //1表示点赞成功2取消点赞成功
                    if(MocuzMobile.Android()) {
                        window.myjs.praise(1);//android
                        return;
                    }
                    if(MocuzMobile.iOS()) {
                        window.location.href="objc://praise:/1";
                        return;
                    }
                    return;
                 }else if(datas.status==2){
                     var follow_view = "<div class='likea' ><img src='assets/images/good.png' onclick=test_tid_Praise(" +id+ ",'" +idtype+ "','click','" +uid+ "')></div>\n\
<p>"+datas.praisenum+" </p>";
                    $(".likea").remove();
                    $(".content_zan p").remove();
                    $(".content_zan").html(follow_view);
                    if(MocuzMobile.Android()) {
                        window.myjs.praise(2);//android
                        return;
                    }
                    if(MocuzMobile.iOS()) {
                        window.location.href="objc://praise:/2";
                        return;
                    }
                    return;
                 }
                 alert(datas.message);
             }
        });
        
    }
    function test_pid_Praise(id,idtype,type,uid){
        if(!uid){
            if(!get_uid){
                if(MocuzMobile.Android()) {
                    window.myjs.get_uid();//android
					return;
                }
                if(MocuzMobile.iOS()) {
                     window.location.href="objc://get_uid";
					 return;
                }
            }
            uid = get_uid;
        }
        $.ajax({
             type: "POST",
             url: siteurl+"index.php?mod=praisethread&id="+id+"&idtype="+idtype+"&type="+type+"&uid="+uid,
             dataType: "html",
             success: function(data){
                 var datas=eval("("+data+")");
                 if(datas.status==1){
                    var follow_view = "<img src='assets/images/praise.png' onclick=test_pid_Praise(" +id+ ",'" +idtype+ "','delete','" +uid+ "')>\n\
<span class='discuss_zan'>"+datas.praisenum+" </span>";
                    $(".pid_praise").html('');
                    $(".pid_praise"+id).html(follow_view);return;
                 }else if(datas.status==2){
                     var follow_view = "<img src='assets/images/zan.png' onclick=test_pid_Praise(" +id+ ",'" +idtype+ "','click','" +uid+ "')>\n\
<span class='discuss_zan'>"+datas.praisenum+" </span>";
                    $(".pid_praise").html('');
                    $(".pid_praise"+id).html(follow_view);return;
                 }
                alert(datas.message);
             }
        });
    }
	 $('#dashang').click(function(){
        click_reward(reward_socre.socre_name,reward_socre.credits,reward_socre.score_list);
    })
    function click_reward(socre_name,credits,score_list){
		if(!uid){
            if(!get_uid){
                if(MocuzMobile.Android()) {
                    window.myjs.get_uid();//android
					return;
                }
                if(MocuzMobile.iOS()) {
                     window.location.href="objc://get_uid";
					 return;
                }
            }
            uid = get_uid;
        }
        if(MocuzMobile.Android()) {
            window.myjs.click_reward(socre_name,credits,score_list);//android
            return;
        }
        if(MocuzMobile.iOS()) {
            var cmd='click_reward'
            window.location.href="objc://"+cmd+":/"+socre_name+"/"+credits+"/"+score_list;
            return;
        }
    }
    function essence_show(){
        window.ontouchmove=function(e){
            e.preventDefault && e.preventDefault();
            e.returnValue=false;
            e.stopPropagation && e.stopPropagation();
            return false;
        }     
        $('.shade').css({ "width": $(document).width(), "height": $(document).height()});
        $('.shade').show();
        $('.essence-box').show();
        var div=$('.essence-box');
        $('.essence-box').css('left',parseInt(($(window).width()-div.width())/2));
        $('.essence-box').css('top',parseInt(($(window).height()-div.height())/2+$(document).scrollTop()));
    }
    $('.essence').click(function(){
        essence_show();
    });
    // 取消
    $('.eclance').click(function(event) {
        $('.essence-box').hide();
        $('.shade').hide();
         window.ontouchmove=function(e){
            e.preventDefault && e.preventDefault();
            e.returnValue=true;
            e.stopPropagation && e.stopPropagation();
            return true;
        }
    });
    //确定
    $('.econfirm').click(function(event) {
        window.ontouchmove=function(e){
            e.preventDefault && e.preventDefault();
            e.returnValue=true;
            e.stopPropagation && e.stopPropagation();
            return true;
        }
        auth = encodeURIComponent(auth);
        href = location.href+'&auth='+auth;
        var digestlevel = $('#digestlevel').val();
        $.ajax({
             type: "POST",
             url: siteurl+"index.php?mod=topicadmin&ishtml=1&action=moderate&digestlevel="+digestlevel,
             data: jinghua_params,
             dataType: "html",
             success: function(data){
                        $('.essence-box').hide();
                        v_dialog(data);
                    }
        });
    });
    /*置顶*/
    function puttop_show(){
        window.ontouchmove=function(e){
            e.preventDefault && e.preventDefault();
            e.returnValue=false;
            e.stopPropagation && e.stopPropagation();
            return false;
        } 
        $('.shade').css({ "width": $(document).width(), "height": $(document).height()});
        $('.shade').show();
        $('.puttop-box').show();
        var div=$('.puttop-box');
        $('.puttop-box').css('left',parseInt(($(window).width()-div.width())/2));
        $('.puttop-box').css('top',parseInt(($(window).height()-div.height())/2+$(document).scrollTop()));
    }

    $('.puttop').click(function(){
        puttop_show();
    });

    // 取消
    $('.tclance').click(function(event) {
        $('.puttop-box').hide();
        $('.shade').hide();
         window.ontouchmove=function(e){
            e.preventDefault && e.preventDefault();
            e.returnValue=true;
            e.stopPropagation && e.stopPropagation();
            return true;
        }
    });
   
    //确定
    $('.tconfirm').click(function(event) {
        window.ontouchmove=function(e){
            e.preventDefault && e.preventDefault();
            e.returnValue=true;
            e.stopPropagation && e.stopPropagation();
            return true;
        }
        auth = encodeURIComponent(auth);
        href = location.href+'&auth='+auth;
        var sticklevel = $('#sticklevel').val();
        $.ajax({
             type: "POST",
             url: siteurl+"index.php?mod=topicadmin&ishtml=1&action=moderate&sticklevel="+sticklevel,
             data: zhiding_params,
             dataType: "html",
             success: function(data){
                        $('.puttop-box').hide();
                        v_dialog(data);
                    }
        });
    });

    function setdownappsize(){
        if (window.innerWidth)
            winWidth = window.innerWidth;
        else if ((document.body) && (document.body.clientWidth))
            winWidth = document.body.clientWidth;
        $('.downappimg').css('width',winWidth);
        $('.downappimg').css('margin-bottom', '-8px');
    }
    setdownappsize();
    /*checkbox投票*/
    checknum = parseInt(checknum);
    function votecheck(){
        var str = '';
        if($(".vote-check:checked").length <= checknum){
            // var chkVal = [];
            $(".vote-check:checked").each(function(){
                // chkVal.push($(this).val());
                str += "&pollanswers[]="+$(this).val();
            });
        }

        return str;
    }
    /*checkbox选中未选中*/
    $('.vote-check').click(function(event) {
         if($(".vote-check:checked").length ==checknum){  
             $('.vote-check').not("input:checked").each(function(){
                $(this).attr('disabled',true);
            });
         }else{
            $('.vote-check').attr('disabled',false);
         }
    });
    // 跳转到投票列表
    $('#showpoll').click(function(){
        auth = encodeURIComponent(auth);
        var url = siteurl+'index.php?mod=forum_misc&action=viewvote&tid='+tid;
        url = url+'&auth='+auth;
        if (MocuzMobile.Android()) {
            window.commonjs.tonextpage(url); //安卓刷新
            return;
        }
        location.href = url;
    })
    // 活动报名框
    function erollshow(){
        $('.shade').css({ "width": $(document).width(), "height": $(document).height()});
        $('.shade').show();
        $('.eroll-show').show();
        var div=$('.eroll-show');
        $('.eroll-show').css('left',parseInt(($(window).width()-div.width())/2));
        $('.eroll-show').css('top',parseInt(($(window).height()-div.height())/2+$(document).scrollTop()));
    }
    // 点击报名
    $('.baoming').click(function() {
        erollshow();
    })
    //提交活动报名之后弹出层
    function subshow(html){
        window.ontouchmove=function(e){
            e.preventDefault && e.preventDefault();
            e.returnValue=false;
            e.stopPropagation && e.stopPropagation();
            return false;
        } 
        $('.subshade').css({ "width": $(document).width(), "height": $(document).height()});
        $('.subshade').show();
        $('.subdialog').show();
        $('.subdTop').text(html);
        var div=$('.subdialog');
        $('.subdialog').css('left',parseInt(($(window).width()-div.width())/2));
        $('.subdialog').css('top',parseInt(($(window).height()-div.height())/2+$(document).scrollTop()));
    }

    //提交活动报名之后弹出层 关闭
    function sub_dBottom(){   
        window.ontouchmove=function(e){
            e.preventDefault && e.preventDefault();
            e.returnValue=true;
            e.stopPropagation && e.stopPropagation();
            return true;
        }
        $('.subdialog').hide();
        $('.subshade').hide();
        
    }
    var href;
    // 提交活动报名信息
    $('.sub').click(function() {
        var action = 'activityapplies';
        var message = $('.remarks').val() || '';
        var payvalue = "";
        var payment = 0;
        var activitysubmit = 1;
        auth = encodeURIComponent(auth);
        href = location.href+'&auth='+auth;
        var action, activitysubmit = 0, activitycancel = 0;
        var errmsg = '提交成功';
        if($('.payImg').attr('alt')=='1'){
            payment = 1
            payvalue = $('.pay').val();
        }
        action = 'activityapplies';
        if($(this).attr('data') == 'go') {  
            activitysubmit = 1;
        } else if($(this).attr('data') == 'back') {
            activitycancel = 1;
        }
        var oparams = $('#myactive').serialize();  
        if (oparams) {
            var d = oparams.split('&');
            for (var i = d.length - 1; i >= 0; i--) {
                var dd = d[i].split('=');
                if(!trim(dd[1])) {
                    alert('带 "*" 号为必填项，请填写完整');
                    return;
                }
            };
        }
        $.ajax({
             type: "POST",
             url: siteurl+"index.php?mod=forum_misc&payment="+payment+"&message="+message+"&payvalue="+payvalue+"&ishtml=1&action="+action+"&activitysubmit="+activitysubmit+"&activitycancel="+activitycancel+'&'+decodeURI(oparams),
             data: baoming_params,
             dataType: "html",
             success: function(data){
                        $('.eroll-show').hide();
                        v_dialog(data);
                    }
         });
    })
    function trim(str){ 
        return str.replace(/(^\++)|(\++$)|(^\s+)|(\s+$)/g,"");
    }
    // 投票提交
    function votepoll(pollid) {
        var pollid = parseInt(pollid);
        var str = votecheck();
        if(!checknum) {
            str += '&pollanswers[]='+pollid;
        }
        auth = encodeURIComponent(auth);
        href = location.href+'&auth='+auth;
        $.ajax({
             type: "POST",
             url: siteurl+"index.php?mod=forum_misc&ishtml=1"+str,
             data: toupiao_params,
             dataType: "html",
             success: function(data){
                        v_dialog(data);
                    }
         });
    }
    // 投票成功后调用
    //显示弹窗
    function v_dialog(html){
        window.ontouchmove=function(e){
            e.preventDefault && e.preventDefault();
            e.returnValue=false;
            e.stopPropagation && e.stopPropagation();
            return false;
        }  
        $('.shade').css({ "width": $(document).width(), "height": $(document).height()});
        $('.shade').show();
        $('.vdialog').show();
        $('.dTop').text(html);
        var div=$('.vdialog');
        $('.vdialog').css('left',parseInt(($(window).width()-div.width())/2));
        $('.vdialog').css('top',parseInt(($(window).height()-div.height())/2+$(document).scrollTop()));
    }
    //关闭弹窗
    function v_dBottom(){
        window.ontouchmove=function(e){
            e.preventDefault && e.preventDefault();
            e.returnValue=true;
            e.stopPropagation && e.stopPropagation();
            return true;
        }
        $('.vdialog').hide();
        $('.shade').hide();
        if (MocuzMobile.Android()) {
            window.commonjs.tonextpage(href); //安卓刷新
            return;
        }
        location.href = href;
    }
    //取消
    $('.opera a').click(function(event) {
        $('.shade').hide();
        $('.eroll-show').hide();
         window.ontouchmove=function(e){
            e.preventDefault && e.preventDefault();
            e.returnValue=true;
            e.stopPropagation && e.stopPropagation();
            return true;
        }
    });
    //操作居中
    function op_center(){
        var liindex=$('.operation li').length;
        /*var lihidden=$(".operation li:hidden").length;*/
        var licount=liindex
        var op_w=$('.operation').width();
        $('.operation li').width(op_w/licount-1);
        
    }
    op_center();
        
    //弹出框
    $('.move_op').click(function(event) {
        window.ontouchmove=function(e){
            e.preventDefault && e.preventDefault();
            e.returnValue=false;
            e.stopPropagation && e.stopPropagation();
            return false;
        }
        $('.shade').css({ "width": $(document).width(), "height": $(document).height()});
        $('.frame-box').show();
        $('.shade').show();
        var div=$('.frame-box');
        $('.frame-box').css('left',parseInt(($(window).width()-div.width())/2));
        $('.frame-box').css('top',parseInt(($(window).height()-div.height())/2+$(document).scrollTop()));
        
    });

    //切换选择
    $('.them').click(function(event) {
        $('.them').find('img').attr('data',"false");
        $('.them').find('img').attr('src',siteurl+'assets/images/nselect.png');
        $(this).find('img').attr('data',"true");
        if($(this).find('img').attr('data',"true")){
            $(this).find('img').attr('src',siteurl+'assets/images/select.png');
        }
    });
    
    //下拉
    $('.frame-main select:first').change(function(event) {
        var options=$(".test option:selected");
        if(options.text()!='默认板块'){
            $('.select-them').show();
            $('.frame-main select').css('color','#454545');
        }else{
            $('.select-them').hide();
        } 
    });
    $('.clance').click(function(event) {
        $('.frame-box').hide();
        $('.shade').hide();
        window.ontouchmove=function(e){
            e.preventDefault && e.preventDefault();
            e.returnValue=true;
            e.stopPropagation && e.stopPropagation();
            return true;
        }
    });
    //确定
    $('.confirm').click(function(event) {
         window.ontouchmove=function(e){
            e.preventDefault && e.preventDefault();
            e.returnValue=true;
            e.stopPropagation && e.stopPropagation();
            return true;
        }
        var action = 'moderate';
        var moveto = $('select[name=moveto]').val();
        var threadtypeid = $('select[name=threadtypeid]').val();
        var moderate = tid;
        var operations = 'move';
        var type = $('.them1 img').attr('data') ? 'normal' : 'redirect';

        // 调用客户端移动帖子方法
        if (MocuzMobile.Android()) {
            window.myjs.executeMove(""+fid, ""+moderate, ""+moveto, ""+threadtypeid, ""+type);
            return;
        }
        if (MocuzMobile.iOS()) {
            var cmd = "executeMove";
            window.location.href="objc://"+cmd+":/"+action+"/"+fid+"/"+moderate+"/"+operations+"/"+moveto+"/"+threadtypeid+"/"+type;
        }
    });
    // 编辑帖子
    function editpost(pid1) {
        var pid = pid1;
            // alert($('.item-view').length);
        // 调用客户端编辑帖子方法
        if (MocuzMobile.Android()) {
            window.myjs.executeEdit(""+fid, ""+tid, ""+pid);
            return;
        }
        if (MocuzMobile.iOS()) {
            var cmd = "executeEdit";
            window.location.href="objc://"+cmd+":/"+fid+"/"+tid+"/"+pid;
        }
    }
    // 管理员对贴子操作参数传递
    function topic_manage(pid,groudstatus) {
        // 调用客户端编辑帖子方法
        if (MocuzMobile.Android()) {
            window.myjs.topic_manage(""+fid, ""+tid, ""+pid, "delpost",""+groudstatus);
            return;
        }
        if (MocuzMobile.iOS()) {
            var cmd = "topic_manage";
            window.location.href="objc://"+cmd+":/"+fid+"/"+tid+"/"+pid+"/delpost/"+groudstatus;
        }
    }
    // 帖子删除    topic 删除帖子 post删除主题
    function deletepost(type, pid) {
        if(confirm("确认要删除吗？")) {
            if (type == 'post') {
                data = {auth:auth, action:'delpost', fid:fid, tid:tid, topiclist:pid};
                // alert(data.tid);
                //调用客户端删除帖子方法
                if (MocuzMobile.Android()) {
                    window.myjs.executeDelete(""+data.action, ""+data.fid, ""+data.tid, ""+data.topiclist);//android
                    return;
                }
                if (MocuzMobile.iOS()) {
                    var cmd = "executeDelete";
                    window.location.href="objc://"+cmd+":/"+data.action+"/"+data.fid+"/"+data.tid+"/"+data.topiclist;
                }
            } else if (type == 'topic') {
                data = {auth:'', action:'moderate', fid:fid, moderate:tid, operations:'delete'};
                // 调用客户端删除主题方法
                if (MocuzMobile.Android()) {
                    window.myjs.executeDelete(""+data.action, ""+data.fid, ""+data.moderate, ""+data.operations);//android
                    return;
                }
                if (MocuzMobile.iOS()) {
                    var cmd = "executeDelete";
                    window.location.href="objc://"+cmd+":/"+data.action+"/"+data.fid+"/"+data.moderate+"/"+data.operations;
                }
            } else {
                return;
            }
        }
    }
    
    // 帖子删除成功调用方法
    function afterdel(pid) {
        var item = document.getElementsByClassName('item-view');
        for(var i=0; i<item.length; i++) {
            tagid = item[i].getAttribute('pid');
            if (tagid == pid) {
                item[i].style.display = 'none';
            }
        }
    }
    // 获取板块类型
    function getthreadtypes(fid) {
        if (!fid) {
            return;
        }
        $.ajax({
            type: "GET",
            url: baseurl+mocuz_type+"/index.php?mod=forum_ajax&action=getthreadtypes&fid="+fid,
            dataType: "html",
            success: function(data){
                $('#threadtypeid').html('');
                $('#threadtypeid').append(data);
                return;
            }
        });
    }
    // 投票 
   //li居中
    function op_center1(){
        var op_w=$('.poll-list').width();
        $('.poll-list li').width(op_w/2-6);
    }
    op_center1();
    // 投票
    var param=0;                      //判断是否选择了一项   
    $('.toupiao').click(function() {
            $(this).children().attr('src',siteurl+'assets/images/select.png');
            //记录已选择
            param=$(this).attr('data');
            obj=$(this);
            if ($(this).children().attr('src',siteurl+'assets/images/select.png')) {
                $('.toupiao').not(this).children().attr('src',siteurl+'assets/images/noselect.png')
            };               
    });
    /*
        改变投票结果
    */
    $('.signtext').click(function(){
        votepoll(param);
    })
    // 投票后
    function hideVote(){
        $('.v-hide').css('visibility','hidden');
    }
    //活动报名切换选择
    $('.payable').click(function(event) {
        $('.payable').find('img').attr('alt',"0");
        $('.payable').find('img').attr('data',"false");
        $('.payable').find('img').attr('src',siteurl+'assets/images/noselect.png');
        $(this).find('img').attr('data',"true");
        if($(this).find('img').attr('data',"true")){
            $(this).find('img').attr('alt',"1");
            $(this).find('img').attr('src',siteurl+'assets/images/select.png');
        }
    });

    //切换选择
    $('.poll-list p').click(function(event) {
        $('.poll-list p').find('img').attr('data',"false");
        $('.poll-list p').find('img').attr('src',siteurl+'assets/images/noselect.png');
        $(this).find('img').attr('data',"true");
        //记录已选择
        s_param=$(this).attr('data');
        //获取勾选的对象上上级(那个li)
        s_obj=$(this).prev().parent();
        if($(this).find('img').attr('data',"true")){
            $(this).find('img').attr('src',siteurl+'assets/images/select.png');
        }        
    });
    /*
        改变投票结果
    */
    $('.signimg').click(function(){
        votepoll(s_param);
    })
    var tagindex = 1;
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
    $(function() {
        //滚动加载方法
        sharew();
        replies_page();
		cmtFixed(view_pids);
        //分享出去页面打开点击回复按钮进去下载引导页
        var wei_href=baseurl+"app.html#mp.weixin.qq.com"
        $('.report-advert').attr('href',wei_href); 
        /*获取页面中img*/
        var  contentid = tid;
        var data = {
            'tid'        : tid
        };
        var contentid = tid;
//        Comment.auth = auth;
//        Comment.tid = tid;;
//        Comment.totalpage = totalpage;
//        Comment.currentpage = 2;
//        Comment.url = baseurl+mocuz_type+"/index.php?mod=viewthread";
//        Comment.loadinInter = $("div.bbs-feed-list").find('div.loading-con');
//        Comment.containter = $("div.bbs-feed-list").find('div.itm');

        //滚动触发加载
//        var scrollTrriget = function(){
//            var bodyTop = document.documentElement.scrollTop ;//document.body.scrollTop;
//            if(!bodyTop) bodyTop = document.body.scrollTop;
//            //滚动到底部时出发函数
//            //滚动的当前位置+窗口的高度 >= 整个body的高度
//            var oBody = $('body')[0];
//            if(bodyTop+oBody.clientHeight >= $(document.body).height()){
//                Comment.load();
//            };
//        }
//
//        $(window).bind('scroll',function(event){
//            if($(document).height() - $(window).height()-$(window).scrollTop()<=100){
//                scrollTrriget()
//            }
//        });
//
//        var AdapterObj = {
//            scrollEvent: scrollTrriget
//        };
//        window.AdapterObj = AdapterObj;
        //==============================
     })
        /**
       * 夜间模式切换
       * @param isNight 是否启用夜间模式
       */
       function switchNightMode(isNight) {
           var colorVal = '';
           if (isNight == 1) {
               $('.bbs-cv-hd .btm').css('border-bottom','1px solid #454545');
               $('.bbs-feed-list li').css('border-top','1px solid #454545');
               $('.quote').css('background-color','#A5A7AA');
               colorVal = '#2C2D32';
           } else {
               colorVal = '';
           }
           document.body.style.backgroundColor = colorVal;
        }
        //论坛举报客户端加载
        function report(){
           if(MocuzMobile.Android()) {
               window.myjs.report(pid,fid);//android
               return;
           }
           if(MocuzMobile.iOS()) {
               window.location.href="objc://report:/"+fid+"/"+pid;
               return;
           }
        }
        //论坛h5客户端加载
        function testClick(cmd, pid, author,authorid){
            var author = author || forum_author;
            if(MocuzMobile.Android()) {
                window.myjs.changePage(""+tid, ""+pid, ""+author, ""+authorid);//android
                return;
            }
            if(MocuzMobile.iOS()) {
                window.location.href="objc://"+cmd+":/"+tid+"/"+author+"/"+pid+"/"+authorid;
            }
        }
    
        function scrollParameter(){
            $(window).bind('scroll', AdapterObj.scrollEvent);
        };
        //回复跳转
        tagindex = 1;
		function  postParameter(uid,username,message) {
			var divFeedList = $("div.bbs-feed-list");
			var divFeedItem = divFeedList.find("div.itm");
			var author=ucenterurl+"/avatar.php?uid="+uid+"&size=small";
			var html ='';
			html+= "<li class='item-view'>";
			html+=  "<div class='discuss_box '>";
			html+=  "<a href='javascript:void(0);' ><img class='discuss_portrait' src="+author+"></a>";
			html+=  "<div class='discuss_user'>";
			html+=   "<p onclick='lbsPerson("+uid+")' class='user_name'>"+username+"</p>";
			html+=  " </div>";
			html+=  "<p class='discuss_content'>"+odz_smiley_parse(message)+"</p>";
			html+=  "</div>";
			html+=  " </li>";


			$(window).unbind('scroll');
			if (tagindex == 1) {
				divFeedItem.append(html);
				tagindex = 1;
			}
			var oBody = $('body')[0];
			var scrollTop = oBody.scrollHeight - $(window).height();
			scrollTop = scrollTop - 45;
			$('body').animate({scrollTop:scrollTop}, 300, '', function() {
				$(window).bind('scroll');
			});
		}; 
//        //返回顶部
//        $('.backtop').click(function(event) {
//                $('.backtop img').attr('src','assets/images/backtop2.png');
//                $("html,body").animate({scrollTop:"0px"},300,function(){
//                        $('.backtop img').attr('src','assets/images/backtop.png');
//                        $('.backtop').hide();
//                });
//        });
//	//返回顶部开始隐藏 滚动显示
//	$(document).scroll(function(){
//		if($(window).scrollTop()>100){
//			$('.backtop').show();
//		}else if($(window).scrollTop()==0){
//			$('.backtop').hide();
//		}
//	});
    // 快捷回复委托方法
    function odz_quick_reply() {
        var author = forum_author;
        if (MocuzMobile.Android()) {
            window.myjs.changePage(""+tid, ""+pid, "","");//android
            return;
        }
        if (MocuzMobile.iOS()) {
            var cmd = "changePage";
            window.location.href="objc://"+cmd+":/"+tid+"/"+author+"/"+pid+"/"+authorid;
        }
    }
	//百度推送锚点
    function cmtFixed(pid){
        if(pid){
            var changeLi=".discuss_ulA li[pid="+pid+"]";
            $("html,body").animate({scrollTop:$(changeLi).offset().top},1000);   
        }    
    }
    // 详情页评论翻页
    function replies_page() {

        if (MocuzMobile.Android()) {
            window.myjs.replies_page(""+replies_pagecount);//android
            return;
        }
        if (MocuzMobile.iOS()) {
            var cmd = "replies_page";
            window.location.href="objc://"+cmd+":/"+replies_pagecount;
        }
    }
    // 数据交互
    function jiaohu(cmd,str) {
        if(favid){
                var status='1';
        }else{
                var status='0';
        }
        var cmd ='jiaohu';
        var str = '{"shoucang":'+status+', "shareSNS":{"sharelink":"'+sharelink+'","shareIcon":"'+shareIcon+'", "sharetext":"'+share_text+'", "sharesubject":"'+subject+'"}}';
//        var str = '{"shoucang":'+subject+'}';
        if(MocuzMobile.Android()&&client==1) {
            window.shareInfo.jiaohu(str);//android
            return;
        }
        if (MocuzMobile.iOS()&&client==1) {
            window.location.href="objc://"+cmd+":/"+str;
        }
    }
    window.onload = jiaohu();
    
    /*4个分享的宽度*/
    function sharew(){
        var su=$('.toshare ul').width();
        $('.toshare ul li').width(su/4-20);
    }
    //分享到微博
    function shareSina(){
        if(MocuzMobile.Android()) {
            window.myjs.shareSina();//android
            return;
        }
        if(MocuzMobile.iOS()) {
            var cmd = "shareSina";
            window.location.href="objc://"+cmd;
            return;
        }
    }
    //分享到微信好友
    function shareWeixin(){
        if(MocuzMobile.Android()) {
            window.myjs.shareWeixin();//android
            return;
        }
        if(MocuzMobile.iOS()) {
            var cmd = "shareWeixin";
            window.location.href="objc://"+cmd;
            return;
        }
    }
    //分享到微信朋友圈
    function shareWeiCof(){
        if(MocuzMobile.Android()) {
            window.myjs.shareWeiCof();//android
            return;
        }
        if(MocuzMobile.iOS()) {
            var cmd = "shareWeiCof";
            window.location.href="objc://"+cmd;
            return;
        }
    }
    //更多
    function shareMore(){
        if(MocuzMobile.Android()) {
            window.myjs.shareMore();//android
            return;
        }
        if(MocuzMobile.iOS()) {
            var cmd = "shareMore";
            window.location.href="objc://"+cmd;
            return;
        }
    }
    //广告
    function showadvert(){
        if(MocuzMobile.Android()) {
            window.myjs.webAd(android_typedata);//android
            return;
        }
        if(MocuzMobile.iOS()) {
            var cmd = "webAd";
            window.location.href="objc://"+cmd+":/"+ios_typedata;
            return;
        }
    }
    //改变字体大小
    function change_fontsize(fontsize){
        var font_size = fontsize + 'px';
        $('.bbs-cv-hd h1').css('font-size', font_size);
        $('.changesize').css('font-size', font_size);
    }
    //跳转个人中心
    function lbsPerson(user_id){
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
    //关注好友3.0
   function follow_friend1(friend_id,fusername,type){
       var delete_friend="delete_friend";
        $.ajax({
             type: "POST",
             url: siteurl+"index.php?mod=follow_friend&friend_id="+friend_id+"&fusername="+fusername+"&type="+type+"&uid="+uid,
             dataType: "html",
             success: function(data){
                 if(data==1){
                    var delete_friend="delete_friend";
                    var follow_view = "<p class='attention attention_y' onclick=follow_friend1(" +fuid+ ",'" +fusername+ "','"+delete_friend+"')>已关注</p>" 
                    $(".attention").remove();
                    $(".attention_view").append(follow_view);return;
                 }else if(data==2){
                    var add_friend="add_friend";
                    var follow_view = "<p class='attention attention_n' onclick=follow_friend1(" +fuid+ ",'" +fusername+ "','"+add_friend+"')>+ 关注</p>" 
                    $(".attention").remove();
                    $(".attention_view").append(follow_view);return;
                 }
                 alert(data);
             }
        });
    }
    //详情页面文字链接处理
    function tothread(thread){
//       var th = eval('('+ thread +')');
//       var str = '{"tid":'+thread.tid+',"typenum":'+thread.typenum+',"name":'+thread.name+',}';
       var  str = '{';
            if(thread.tid){
                str += '"tid":'+thread.tid+','
            }
            if(thread.fid){
                str += '"fid":'+thread.fid+','
            }
            if(thread.name){
                var name='"'+thread.name+'"'; 
                str += '"name":'+name+',';
            }
            str += '"typenum":'+thread.typenum
            str +='}';
        if(MocuzMobile.Android()) {
            window.commonjs.tothread(str);//android
            return;
        }
        if(MocuzMobile.iOS()) {
            var cmd = "tothread";
            window.location.href="objc://"+cmd+":/"+str;
            return;
        }
    }
	//举报样式修改
	$('.jubao').live('touchstart',function(event) {
            alert("举报成功，谢谢！");
        });

    $('.pingpi').live('touchstart',function(event) {
        $.ajax({
             type: "POST",
             url: homeurl+'mocuz/2.2/ajax.php',
             dataType: "html",
             success: function(data){
                alert(data);
            }
        });       
    });

    $('.jubao1').live('touchstart',function(event) {
        var $this=$(this).parents('li').find('div').eq(2);
        if($this.is(':hidden')){
            $this.show().slideDown();
        }else{
            $this.hide().slideUp();
        }
        
    });
    //引用回复箭头
    var arrowstr='<div class="quote-arrow">';
    $('.hd-arrow .quote ').after(arrowstr);
 
//    function loaded () {
//        var myScroll;	
//        totalpage = totalpage;
//        currentpage = 2;
//        //当前页大于总页数，无需加载
//        if(currentpage > totalpage){
//            return false;
//        }
//        myScroll = new IScroll('#wrapper', { probeType: 3, mouseWheel: true,click: true });	
//        myScroll.on("slideDown",function(){
//                if(this.y > 40){
//                    currentpage--;
//                    var url = baseurl+mocuz_type+"index.php?mod=viewthread&tid="+tid+"&item_view=1&page="+currentpage;
//                    if(MocuzMobile.Android()) {
//                        window.myjs.loaded_page(""+url);//android
//                    }
//                    if(MocuzMobile.iOS()) {
//                        var cmd='loaded_page';
//                        window.location.href="objc://"+cmd+":/"+url;
//                    }
//                }
//        });
//        myScroll.on("slideUp",function(){
//                if(this.maxScrollY - this.y > 40){
//                    currentpage++;
//                    var url = baseurl+mocuz_type+"index.php?mod=viewthread&tid="+tid+"&item_view=1&page="+currentpage+"&auth="+auth;
//                    if(MocuzMobile.Android()) {
//                        window.myjs.loaded_page(""+url);//android
//                    }
//                    if(MocuzMobile.iOS()) {
//                        var cmd='loaded_page';
//                        window.location.href="objc://"+cmd+":/"+url;
//                    }
//                }
//        });
//    }