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
                    //1��ʾ���޳ɹ�2ȡ�����޳ɹ�
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
    // ȡ��
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
    //ȷ��
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
    /*�ö�*/
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

    // ȡ��
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
   
    //ȷ��
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
    /*checkboxͶƱ*/
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
    /*checkboxѡ��δѡ��*/
    $('.vote-check').click(function(event) {
         if($(".vote-check:checked").length ==checknum){  
             $('.vote-check').not("input:checked").each(function(){
                $(this).attr('disabled',true);
            });
         }else{
            $('.vote-check').attr('disabled',false);
         }
    });
    // ��ת��ͶƱ�б�
    $('#showpoll').click(function(){
        auth = encodeURIComponent(auth);
        var url = siteurl+'index.php?mod=forum_misc&action=viewvote&tid='+tid;
        url = url+'&auth='+auth;
        if (MocuzMobile.Android()) {
            window.commonjs.tonextpage(url); //��׿ˢ��
            return;
        }
        location.href = url;
    })
    // �������
    function erollshow(){
        $('.shade').css({ "width": $(document).width(), "height": $(document).height()});
        $('.shade').show();
        $('.eroll-show').show();
        var div=$('.eroll-show');
        $('.eroll-show').css('left',parseInt(($(window).width()-div.width())/2));
        $('.eroll-show').css('top',parseInt(($(window).height()-div.height())/2+$(document).scrollTop()));
    }
    // �������
    $('.baoming').click(function() {
        erollshow();
    })
    //�ύ�����֮�󵯳���
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

    //�ύ�����֮�󵯳��� �ر�
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
    // �ύ�������Ϣ
    $('.sub').click(function() {
        var action = 'activityapplies';
        var message = $('.remarks').val() || '';
        var payvalue = "";
        var payment = 0;
        var activitysubmit = 1;
        auth = encodeURIComponent(auth);
        href = location.href+'&auth='+auth;
        var action, activitysubmit = 0, activitycancel = 0;
        var errmsg = '�ύ�ɹ�';
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
                    alert('�� "*" ��Ϊ���������д����');
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
    // ͶƱ�ύ
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
    // ͶƱ�ɹ������
    //��ʾ����
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
    //�رյ���
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
            window.commonjs.tonextpage(href); //��׿ˢ��
            return;
        }
        location.href = href;
    }
    //ȡ��
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
    //��������
    function op_center(){
        var liindex=$('.operation li').length;
        /*var lihidden=$(".operation li:hidden").length;*/
        var licount=liindex
        var op_w=$('.operation').width();
        $('.operation li').width(op_w/licount-1);
        
    }
    op_center();
        
    //������
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

    //�л�ѡ��
    $('.them').click(function(event) {
        $('.them').find('img').attr('data',"false");
        $('.them').find('img').attr('src',siteurl+'assets/images/nselect.png');
        $(this).find('img').attr('data',"true");
        if($(this).find('img').attr('data',"true")){
            $(this).find('img').attr('src',siteurl+'assets/images/select.png');
        }
    });
    
    //����
    $('.frame-main select:first').change(function(event) {
        var options=$(".test option:selected");
        if(options.text()!='Ĭ�ϰ��'){
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
    //ȷ��
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

        // ���ÿͻ����ƶ����ӷ���
        if (MocuzMobile.Android()) {
            window.myjs.executeMove(""+fid, ""+moderate, ""+moveto, ""+threadtypeid, ""+type);
            return;
        }
        if (MocuzMobile.iOS()) {
            var cmd = "executeMove";
            window.location.href="objc://"+cmd+":/"+action+"/"+fid+"/"+moderate+"/"+operations+"/"+moveto+"/"+threadtypeid+"/"+type;
        }
    });
    // �༭����
    function editpost(pid1) {
        var pid = pid1;
            // alert($('.item-view').length);
        // ���ÿͻ��˱༭���ӷ���
        if (MocuzMobile.Android()) {
            window.myjs.executeEdit(""+fid, ""+tid, ""+pid);
            return;
        }
        if (MocuzMobile.iOS()) {
            var cmd = "executeEdit";
            window.location.href="objc://"+cmd+":/"+fid+"/"+tid+"/"+pid;
        }
    }
    // ����Ա�����Ӳ�����������
    function topic_manage(pid,groudstatus) {
        // ���ÿͻ��˱༭���ӷ���
        if (MocuzMobile.Android()) {
            window.myjs.topic_manage(""+fid, ""+tid, ""+pid, "delpost",""+groudstatus);
            return;
        }
        if (MocuzMobile.iOS()) {
            var cmd = "topic_manage";
            window.location.href="objc://"+cmd+":/"+fid+"/"+tid+"/"+pid+"/delpost/"+groudstatus;
        }
    }
    // ����ɾ��    topic ɾ������ postɾ������
    function deletepost(type, pid) {
        if(confirm("ȷ��Ҫɾ����")) {
            if (type == 'post') {
                data = {auth:auth, action:'delpost', fid:fid, tid:tid, topiclist:pid};
                // alert(data.tid);
                //���ÿͻ���ɾ�����ӷ���
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
                // ���ÿͻ���ɾ�����ⷽ��
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
    
    // ����ɾ���ɹ����÷���
    function afterdel(pid) {
        var item = document.getElementsByClassName('item-view');
        for(var i=0; i<item.length; i++) {
            tagid = item[i].getAttribute('pid');
            if (tagid == pid) {
                item[i].style.display = 'none';
            }
        }
    }
    // ��ȡ�������
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
    // ͶƱ 
   //li����
    function op_center1(){
        var op_w=$('.poll-list').width();
        $('.poll-list li').width(op_w/2-6);
    }
    op_center1();
    // ͶƱ
    var param=0;                      //�ж��Ƿ�ѡ����һ��   
    $('.toupiao').click(function() {
            $(this).children().attr('src',siteurl+'assets/images/select.png');
            //��¼��ѡ��
            param=$(this).attr('data');
            obj=$(this);
            if ($(this).children().attr('src',siteurl+'assets/images/select.png')) {
                $('.toupiao').not(this).children().attr('src',siteurl+'assets/images/noselect.png')
            };               
    });
    /*
        �ı�ͶƱ���
    */
    $('.signtext').click(function(){
        votepoll(param);
    })
    // ͶƱ��
    function hideVote(){
        $('.v-hide').css('visibility','hidden');
    }
    //������л�ѡ��
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

    //�л�ѡ��
    $('.poll-list p').click(function(event) {
        $('.poll-list p').find('img').attr('data',"false");
        $('.poll-list p').find('img').attr('src',siteurl+'assets/images/noselect.png');
        $(this).find('img').attr('data',"true");
        //��¼��ѡ��
        s_param=$(this).attr('data');
        //��ȡ��ѡ�Ķ������ϼ�(�Ǹ�li)
        s_obj=$(this).prev().parent();
        if($(this).find('img').attr('data',"true")){
            $(this).find('img').attr('src',siteurl+'assets/images/select.png');
        }        
    });
    /*
        �ı�ͶƱ���
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
        //�������ط���
        sharew();
        replies_page();
		cmtFixed(view_pids);
        //������ȥҳ��򿪵���ظ���ť��ȥ��������ҳ
        var wei_href=baseurl+"app.html#mp.weixin.qq.com"
        $('.report-advert').attr('href',wei_href); 
        /*��ȡҳ����img*/
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

        //������������
//        var scrollTrriget = function(){
//            var bodyTop = document.documentElement.scrollTop ;//document.body.scrollTop;
//            if(!bodyTop) bodyTop = document.body.scrollTop;
//            //�������ײ�ʱ��������
//            //�����ĵ�ǰλ��+���ڵĸ߶� >= ����body�ĸ߶�
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
       * ҹ��ģʽ�л�
       * @param isNight �Ƿ�����ҹ��ģʽ
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
        //��̳�ٱ��ͻ��˼���
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
        //��̳h5�ͻ��˼���
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
        //�ظ���ת
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
//        //���ض���
//        $('.backtop').click(function(event) {
//                $('.backtop img').attr('src','assets/images/backtop2.png');
//                $("html,body").animate({scrollTop:"0px"},300,function(){
//                        $('.backtop img').attr('src','assets/images/backtop.png');
//                        $('.backtop').hide();
//                });
//        });
//	//���ض�����ʼ���� ������ʾ
//	$(document).scroll(function(){
//		if($(window).scrollTop()>100){
//			$('.backtop').show();
//		}else if($(window).scrollTop()==0){
//			$('.backtop').hide();
//		}
//	});
    // ��ݻظ�ί�з���
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
	//�ٶ�����ê��
    function cmtFixed(pid){
        if(pid){
            var changeLi=".discuss_ulA li[pid="+pid+"]";
            $("html,body").animate({scrollTop:$(changeLi).offset().top},1000);   
        }    
    }
    // ����ҳ���۷�ҳ
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
    // ���ݽ���
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
    
    /*4�������Ŀ���*/
    function sharew(){
        var su=$('.toshare ul').width();
        $('.toshare ul li').width(su/4-20);
    }
    //������΢��
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
    //������΢�ź���
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
    //������΢������Ȧ
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
    //����
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
    //���
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
    //�ı������С
    function change_fontsize(fontsize){
        var font_size = fontsize + 'px';
        $('.bbs-cv-hd h1').css('font-size', font_size);
        $('.changesize').css('font-size', font_size);
    }
    //��ת��������
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
    //��ע����3.0
   function follow_friend1(friend_id,fusername,type){
       var delete_friend="delete_friend";
        $.ajax({
             type: "POST",
             url: siteurl+"index.php?mod=follow_friend&friend_id="+friend_id+"&fusername="+fusername+"&type="+type+"&uid="+uid,
             dataType: "html",
             success: function(data){
                 if(data==1){
                    var delete_friend="delete_friend";
                    var follow_view = "<p class='attention attention_y' onclick=follow_friend1(" +fuid+ ",'" +fusername+ "','"+delete_friend+"')>�ѹ�ע</p>" 
                    $(".attention").remove();
                    $(".attention_view").append(follow_view);return;
                 }else if(data==2){
                    var add_friend="add_friend";
                    var follow_view = "<p class='attention attention_n' onclick=follow_friend1(" +fuid+ ",'" +fusername+ "','"+add_friend+"')>+ ��ע</p>" 
                    $(".attention").remove();
                    $(".attention_view").append(follow_view);return;
                 }
                 alert(data);
             }
        });
    }
    //����ҳ���������Ӵ���
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
	//�ٱ���ʽ�޸�
	$('.jubao').live('touchstart',function(event) {
            alert("�ٱ��ɹ���лл��");
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
    //���ûظ���ͷ
    var arrowstr='<div class="quote-arrow">';
    $('.hd-arrow .quote ').after(arrowstr);
 
//    function loaded () {
//        var myScroll;	
//        totalpage = totalpage;
//        currentpage = 2;
//        //��ǰҳ������ҳ�����������
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