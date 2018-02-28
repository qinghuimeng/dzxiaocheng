<?php echo '无道设计--www.wdao.cc，QQ：123855527';exit;?>
				<!--{if $_G['uid']}-->
					<ul class="login">


<li class="wdao_message">

<a class="message-list"><i></i></a>

<div class="menu-list message-box hide">

<ul class="message-box-list">

<li class="no-news-circle"><a href="home.php?mod=space&do=pm" target="_blank">新消息</a></li>							
<li class="no-news-circle"><a href="home.php?mod=follow&do=follower"><!--{lang notice_interactive_follower}--></a>

</ul>



</div>

</li>

<li class="user">

<a href="home.php?mod=space&uid=$_G[uid]" class="user-list" id="header-logined-user-face">

<img src="uc_server/avatar.php?uid=$_G[uid]&size=small" title="{lang visit_my_space}" alt="{$_G[member][username]}">

</a>

<div class="menu-list user-box hide">


<div class="user-box-list">
<div class="user-box-list-area">
<p class="works-manange">
<a href="home.php?mod=space&uid=$_G[uid]">{$_G[member][username]}</a>
</p></div>

<div class="user-box-list-area">

<p class="works-manange"><a href="home.php?mod=space&uid=$_G[uid]">我的作品</a></p>
<p class="works-manange"><a href="home.php?mod=space&do=favorite&view=me">我的收藏</a></p>
</div>

<div class="user-box-list-area">

<p class="works-manange"><a href="home.php?mod=spacecp">个人设置</a></p>

<p class="works-manange"><a href="home.php?mod=space&uid=$_G[uid]" >个人主页</a></p>
<!--{if $_G['setting']['taskon'] && !empty($_G['cookie']['taskdoing_'.$_G['uid']])}-->
<p class="works-manange"><a href="home.php?mod=task&item=doing">{lang task_doing}</a></p>
<!--{/if}-->
<div class="hook">
<!--{hook/global_usernav_extra1}-->
<!--{hook/global_usernav_extra2}-->
<!--{hook/global_usernav_extra3}-->
<!--{hook/global_usernav_extra4}-->
</div>

</div>
<!--{if ($_G['group']['allowmanagearticle'] || $_G['group']['allowpostarticle'] || $_G['group']['allowdiy'] || getstatus($_G['member']['allowadmincp'], 4) || getstatus($_G['member']['allowadmincp'], 6) || getstatus($_G['member']['allowadmincp'], 2) || getstatus($_G['member']['allowadmincp'], 3))}-->
<div class="user-box-list-area">
<p class="works-manange"><a href="portal.php?mod=portalcp"><!--{if $_G['setting']['portalstatus'] }-->{lang portal_manage}<!--{else}-->{lang portal_block_manage}<!--{/if}--></a></p>
							<!--{if $_G['uid'] && $_G['group']['radminid'] > 1}-->
<p class="works-manange"><a href="forum.php?mod=modcp&fid=$_G[fid]" target="_blank">{lang forum_manager}</a></p>
							<!--{/if}-->
							<!--{if $_G['uid'] && getstatus($_G['member']['allowadmincp'], 1)}-->
<p class="works-manange"><a href="admin.php" target="_blank">{lang admincp}</a></p>
							<!--{/if}-->
						<!--{if check_diy_perm($topic)}-->
<p class="works-manange"><a href="javascript:saveUserdata('diy_advance_mode', '1');openDiy();" >打开DIY</a></p>
							<!--{/if}-->	
</div>							
<!--{/if}-->

<div class="user-box-list-area">

<p class="works-manange"><a href="member.php?mod=logging&action=logout&formhash={FORMHASH}">退出登录</a></p>

</div>

</div>

</div>

</li>

</ul>

					<!--{elseif !empty($_G['cookie']['loginuser'])}-->
					<ul class="login">
<div class="user-box-list-area">
<p class="works-manange">
<a href="home.php?mod=space&uid=$_G[uid]"><img class="avatar" src="uc_server/avatar.php?uid=$_G[uid]&size=small" alt=""></a></p>
<p class="works-manange">
<a href="home.php?mod=space&uid=$_G[uid]"><!--{echo dhtmlspecialchars($_G['cookie']['loginuser'])}--></a></p>
<p class="works-manange"><a href="member.php?mod=logging&action=login" onClick="showWindow('login', this.href)"><i class="i-con-info"></i>{lang activation}</a></p>
<p class="works-manange"><a href="member.php?mod=logging&action=logout&formhash={FORMHASH}"><i class="i-con-exit"></i>{lang logout}</a></p>
</div></ul>



					<!--{elseif !$_G[connectguest]}-->
					<ul class="unlogin">
<li>
<a href="member.php?mod={$_G[setting][regname]}">注册<i></i></a>
<a href="member.php?mod=logging&amp;action=login" onclick="showWindow('login', this.href)">{lang login}</a>
</li>
</ul>
                      <!--{else}-->
<div class="user-box-list-area">
<p class="works-manange">
<a  href="home.php?mod=space&uid=$_G[uid]"><img class="avatar" src="uc_server/avatar.php?uid=$_G[uid]&size=small" alt=""></a></p></div>
<div class="user-box-list-area">
<p class="works-manange">
<a href="home.php?mod=space&uid=$_G[uid]"><i class="i-con-res"></i>{$_G[member][username]}</a></p>
<p class="works-manange"><!--{hook/global_usernav_extra1}--></p>
<p class="works-manange"><a href="home.php?mod=spacecp&ac=credit&showcredit=1"><i class="i-con-info"></i>{lang credits}</a></p>
<p class="works-manange"><a href="member.php?mod=logging&action=logout&formhash={FORMHASH}"><i class="i-con-exit"></i>{lang logout}</a></p>
</div>
					<!--{/if}-->