var BROWSER = {};
var USERAGENT = navigator.userAgent.toLowerCase();
browserVersion({'ie':'msie','firefox':'','chrome':'','opera':'','safari':'','mozilla':'','webkit':'','maxthon':'','qq':'qqbrowser'});
if(BROWSER.safari) {
    BROWSER.firefox = true;
}
BROWSER.opera = BROWSER.opera ? opera.version() : 0;

HTMLNODE = document.getElementsByTagName('head')[0].parentNode;
if(BROWSER.ie) {
    BROWSER.iemode = parseInt(typeof document.documentMode != 'undefined' ? document.documentMode : BROWSER.ie);
    HTMLNODE.className = 'ie_all ie' + BROWSER.iemode;
}

function $dz(id) {
    return !id ? null : document.getElementById(id);
}
function browserVersion(types) {
    var other = 1;
    for(i in types) {
        var v = types[i] ? types[i] : i;
        if(USERAGENT.indexOf(v) != -1) {
            var re = new RegExp(v + '(\\/|\\s)([\\d\\.]+)', 'ig');
            var matches = re.exec(USERAGENT);
            var ver = matches != null ? matches[2] : 0;
            other = ver !== 0 && v != 'mozilla' ? 0 : other;
        }else {
            var ver = 0;
        }
        eval('BROWSER.' + i + '= ver');
    }
    BROWSER.other = other;
}
function mobileplayer()
{
    var platform = navigator.platform;
    var ua = navigator.userAgent;
    var ios = /iPhone|iPad|iPod/.test(platform) && ua.indexOf( "AppleWebKit" ) > -1;
    var andriod = ua.indexOf( "Android" ) > -1;
    if(ios || andriod) {
        return true;
    } else {
        return false;
    }
}

function AC_FL_RunContent() {
    var str = '';
    var ret = AC_GetArgs(arguments, "clsid:d27cdb6e-ae6d-11cf-96b8-444553540000", "application/x-shockwave-flash");
    if(BROWSER.ie && !BROWSER.opera) {
        str += '<object ';
        for (var i in ret.objAttrs) {
            str += i + '="' + ret.objAttrs[i] + '" ';
        }
        str += '>';
        for (var i in ret.params) {
            str += '<param name="' + i + '" value="' + ret.params[i] + '" /> ';
        }
        str += '</object>';
    } else {
        str += '<embed ';
        for (var i in ret.embedAttrs) {
            str += i + '="' + ret.embedAttrs[i] + '" ';
        }
        str += '></embed>';
    }
    return str;
}
function AC_GetArgs(args, classid, mimeType) {
    var ret = new Object();
    ret.embedAttrs = new Object();
    ret.params = new Object();
    ret.objAttrs = new Object();
    for (var i = 0; i < args.length; i = i + 2){
        var currArg = args[i].toLowerCase();
        switch (currArg){
            case "classid":break;
            case "pluginspage":ret.embedAttrs[args[i]] = 'http://www.macromedia.com/go/getflashplayer';break;
            case "src":ret.embedAttrs[args[i]] = args[i+1];ret.params["movie"] = args[i+1];break;
            case "codebase":ret.objAttrs[args[i]] = 'http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=9,0,0,0';break;
            case "onafterupdate":case "onbeforeupdate":case "onblur":case "oncellchange":case "onclick":case "ondblclick":case "ondrag":case "ondragend":
            case "ondragenter":case "ondragleave":case "ondragover":case "ondrop":case "onfinish":case "onfocus":case "onhelp":case "onmousedown":
            case "onmouseup":case "onmouseover":case "onmousemove":case "onmouseout":case "onkeypress":case "onkeydown":case "onkeyup":case "onload":
            case "onlosecapture":case "onpropertychange":case "onreadystatechange":case "onrowsdelete":case "onrowenter":case "onrowexit":case "onrowsinserted":case "onstart":
            case "onscroll":case "onbeforeeditfocus":case "onactivate":case "onbeforedeactivate":case "ondeactivate":case "type":
            case "id":ret.objAttrs[args[i]] = args[i+1];break;
            case "width":case "height":case "align":case "vspace": case "hspace":case "class":case "title":case "accesskey":case "name":
            case "tabindex":ret.embedAttrs[args[i]] = ret.objAttrs[args[i]] = args[i+1];break;
            default:ret.embedAttrs[args[i]] = ret.params[args[i]] = args[i+1];
        }
    }
    ret.objAttrs["classid"] = classid;
    if(mimeType) {
        ret.embedAttrs["type"] = mimeType;
    }
    return ret;
}

// �������������еı���
function odz_smiley_parse(message) {   
    for(var serach_key in smiley_search){
        var replace = smiley_search[serach_key];
        var serach_key = quote(serach_key);
        message = message.replace(new RegExp(serach_key, 'g'), replace);
    }

    return message;
}
//�������ת��
function quote(text){
    var str = ['.','+','*','?','[','^',']','$','(',')','{','}','=','','<','>','|'];
    for(var id in str){                 
        if(str[id] == ''){
            continue;
        }
        text = text.replace(new RegExp('\\'+str[id], 'g'), '\\'+str[id]);
    }
    return text;
}

//ͼƬ�������
function img_click(src){
    var i,jsonstr;
    var array=[];
    var thisbig_url;
    var imglist=$(".img_detail");
    for(var i=0;i<imglist.length;i++){
        if($(imglist[i]).attr("big_url")==""||typeof($(imglist[i]).attr("big_url"))=="undefined"){
            array.push($(imglist[i]).attr('data-original'));
        }else{
            array.push($(imglist[i]).attr('big_url'));
        }
    }
    
    var str = '{"thisimg":"'+src+'","imglist":["' + array.join('","') + '"]}'; 

    if (MocuzMobile.Android()) {
        window.myjs.openImage(src,str);//android
        return;
    }
   
   if (MocuzMobile.iOS()) {
        var cmd='openImage';
        window.location.href="objc://"+cmd+":/"+str+""
   }

   return false;
}
//���ۼ��غ����°�
function rebind(){
    //ͼƬ�����
    $('.itm .reply_box:last').find('.img_detail').bind('click', function(event) {
		var src = this.src
        /*img_click(src);*/
    })
    //ͼƬ�����ذ�
    $(".itm .reply_box:last").find('.img_detail').lazyload();
}


//var Comment = {
//    auth : null,
//    tid: 0,
//    totalpage : 1, //��ҳ��
//    currentpage : 1,
//    url : '',
//    loadinInter : null, //����ͼƬjquery����
//    isloading : false, //�Ƿ�����ajax��
//    containter : null, //���۲�������,jquery����
//    load:function(){
//        console.log(this.currentpage)
//        if(this.isloading){
//            return false;
//        }
//        //��ǰҳ������ҳ�����������
//        if(this.currentpage > this.totalpage){
//            return false;
//        }
//        //����ajax��������ͼƬ��ʾ
//        this.loadinInter.show();
//        this.isloading = true;
//        var ele = this;
//        $.ajax({
////            url:this.url,
//            url:'http://192.168.20.8/discuz/moc20/moc22/index.php?mod=viewthread',
//            type:'GET',
//            data:{tid:this.tid,item_view:1,page:this.currentpage,auth:this.auth},
//            //���ͻ��˵�����
//            //http://192.168.20.8/discuz/moc20/moc22/index.php?mod=viewthread&tid=305&item_view=1&page=2&auth=dc68PzEfNSBuBa8xU51HFPpz1MYdgIVt8ZMfigVIBp1uKofageRr38Wb5F4nDo8c7oZdECxUY5SQ8e41I2ER
//            dataType: "html",success:function(html) {
//                //������ɣ��رռ���ͼ��
//                ele.isloading = false;
//                ele.loadinInter.hide();
//                //����ɹ�����������
////                ele.containter.append($(html));
//                $('.quote-arrow').remove();
//                var arrowstr='<div class="quote-arrow">';
//                $('.hd-arrow .quote ').after(arrowstr);
//                //���°�
//                rebind();
//                //��ǰҳ����
//                ele.currentpage++;
//             }
//        }) 
//    }
//}

$(function(){
    //ͼƬ�����
    if (MocuzMobile.Android()) {
        $('.img_detail').live('click', function(event) {
            var src;
            if($(this).attr("big_url")==""||typeof($(this).attr("big_url"))=="undefined"){
                 src=$(this).attr('data-original');
            }else{
                 src=$(this).attr('big_url');
            }
            console.log(src);
            img_click(src);
        })
    }

   
   if (MocuzMobile.iOS()) {
        $('.img_detail').bind('click', function(event) {
            var src;
            if($(this).attr("big_url")==""||typeof($(this).attr("big_url"))=="undefined"){
                 src=$(this).attr('data-original');
            }else{
                 src=$(this).attr('big_url');
            }
            img_click(src);
        })

        $('.img_detail').live('click', function(event) {
            var src;
            if($(this).attr("big_url")==""||typeof($(this).attr("big_url"))=="undefined"){
                 src=$(this).attr('data-original');
            }else{
                 src=$(this).attr('big_url');
            }
            console.log(src);
            img_click(src);
        })
    }

});