<?php echo '无道设计--www.wdao.cc，QQ：123855527';exit;?>
<!--{if $_G['setting']['search']}-->
	<!--{eval $slist = array();}-->
	<!--{if $_G['fid'] && $_G['forum']['status'] != 3 && $mod != 'group'}--><!--{block slist[forumfid]}--><li><a href="javascript:;" rel="curforum" fid="$_G[fid]" >{lang search_this_forum}</a></li><!--{/block}--><!--{/if}-->
	<!--{if $_G['setting']['portalstatus'] && $_G['setting']['search']['portal']['status'] && ($_G['group']['allowsearch'] & 1 || $_G['adminid'] == 1)}--><!--{block slist[portal]}--><li><a href="javascript:;" rel="article">{lang article}</a></li><!--{/block}--><!--{/if}-->
	<!--{if $_G['setting']['search']['forum']['status'] && ($_G['group']['allowsearch'] & 2 || $_G['adminid'] == 1)}--><!--{block slist[forum]}--><li><a href="javascript:;" rel="forum" class="curtype">{lang thread}</a></li><!--{/block}--><!--{/if}-->
	<!--{if helper_access::check_module('blog') && $_G['setting']['search']['blog']['status'] && ($_G['group']['allowsearch'] & 4 || $_G['adminid'] == 1)}--><!--{block slist[blog]}--><li><a href="javascript:;" rel="blog">{lang blog}</a></li><!--{/block}--><!--{/if}-->
	<!--{if helper_access::check_module('album') && $_G['setting']['search']['album']['status'] && ($_G['group']['allowsearch'] & 8 || $_G['adminid'] == 1)}--><!--{block slist[album]}--><li><a href="javascript:;" rel="album">{lang album}</a></li><!--{/block}--><!--{/if}-->
	<!--{if $_G['setting']['groupstatus'] && $_G['setting']['search']['group']['status'] && ($_G['group']['allowsearch'] & 16 || $_G['adminid'] == 1)}--><!--{block slist[group]}--><li><a href="javascript:;" rel="group">$_G['setting']['navs'][3]['navname']</a></li><!--{/block}--><!--{/if}-->
	<!--{block slist[user]}--><li><a href="javascript:;" rel="user">{lang users}</a></li><!--{/block}-->
<!--{/if}-->

<!--{if $_G['setting']['search'] && $slist}-->
<form id="scbar_form" method="{if $_G[fid] && !empty($searchparams[url])}get{else}post{/if}" autocomplete="off" onsubmit="searchFocus($('scbar_txt'))" action="{if $_G[fid] && !empty($searchparams[url])}$searchparams[url]{else}search.php?searchsubmit=yes{/if}" target="_blank">
	<input type="hidden" name="mod" id="scbar_mod" value="search" />
		<input type="hidden" name="formhash" value="{FORMHASH}" />
		<input type="hidden" name="srchtype" value="title" />
		<input type="hidden" name="srhfid" value="$_G[fid]" />
		<input type="hidden" name="srhlocality" value="$_G['basescript']::{CURMODULE}" />
			<!--{if !empty($searchparams[params])}-->
			<!--{loop $searchparams[params] $key $value}-->
			<!--{eval $srchotquery .= '&' . $key . '=' . rawurlencode($value);}-->
			<input type="hidden" name="$key" value="$value" />
			<!--{/loop}-->
			<input type="hidden" name="source" value="discuz" />
			<input type="hidden" name="fId" id="srchFId" value="$_G[fid]" />
			<input type="hidden" name="q" id="cloudsearchquery" value="" />
<!--{/if}-->
<!--{/if}-->
<div class="search-input-hull hide">

<span class="search-ipt"></span>

<input type="text" name="srchtxt" autocomplete="off" value="" id="nav-search-ipt" >

<span class="search-cancel"></span>

<div id="search-content" class="search-content">

<div class="search-content-list">
<!--{if $_G['setting']['srchhotkeywords']}-->
<div class="search-title">
{lang hot_search}: 
</div>
						
							<!--{loop $_G['setting']['srchhotkeywords'] $val}-->
								<!--{if $val=trim($val)}-->
									<!--{eval $valenc=rawurlencode($val);}-->
									<!--{block srchhotkeywords[]}-->
										<!--{if !empty($searchparams[url])}-->
										<div class="hot-list search-l">
<a href="$searchparams[url]?q=$valenc&source=hotsearch{$srchotquery}" target="_blank" sc="1">$val</a>
</div>
											
										<!--{else}-->
										<div class="hot-list search-l">
											<a href="search.php?mod=forum&srchtxt=$valenc&formhash={FORMHASH}&searchsubmit=true&source=hotsearch" target="_blank"  sc="1">$val</a>
											</div>
										<!--{/if}-->
									<!--{/block}-->
								<!--{/if}-->
							<!--{/loop}-->
							<!--{echo implode('', $srchhotkeywords);}-->
						<!--{/if}-->

</div>

</div>

</div>

</form>