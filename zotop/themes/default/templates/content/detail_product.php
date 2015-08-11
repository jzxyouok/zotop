{template 'header.php'}

<style>
.content-feature img{width: 56px;}
.fa-plus{font-size: 14px;color:#999;margin-top: 5px;margin-right: 5px;}
</style>

<ul class="breadcrumb">
    <li><i class="fa fa-home"></i> <a href="{u()}">{t('首页')}</a></li>
	{content action="position" cid="$category.id"}
	<li><a href="{$r.url}">{$r.name}</a></li>
	{/content}
</ul>

<div class="container-fluid">

	<div class="row">
		<div class="col-sm-10 main">

		<div class="content content-box content-page">


			<div id="galleryslider" class="carousel slide" data-ride="carousel">
			  <ol class="carousel-indicators">
			  	<li data-target="#galleryslider" data-slide-to="0" class="active"></li>
				{loop $content.gallery $i $r}
				<li data-target="#galleryslider" data-slide-to="{$i}"></li>
				{/loop}
			  </ol>
			  <div class="carousel-inner" role="listbox">
			  	<div class="item active">
			  		<img src="{thumb($r.image,400,300)}" alt="{$content.title}" style="width:100%">
			  	</div>
			  	{loop $content.gallery $i $r}
			    <div class="item">
			      <img src="{thumb($r.image,400,300)}" alt="{$r.title}" style="width:100%">    
			    </div>
			    {/loop}
			  </div>
			  <a class="left carousel-control" href="#galleryslider" role="button" data-slide="prev">
			    <span class="icon-prev fa fa-angle-left" aria-hidden="true"></span>
			    <span class="sr-only">Previous</span>
			  </a>
			  <a class="right carousel-control" href="#galleryslider" role="button" data-slide="next">
			    <span class="icon-next fa fa-angle-right" aria-hidden="true"></span>
			    <span class="sr-only">Next</span>
			  </a>
			</div>

			<h1 class="content-title" style="font-size:16px;border:0 none;text-align:left;">{$content.title}</h1>
			<div class="content-subtitle">{$content.subtitle}</div>
			<div class="blank"></div>
			
			<div class="content-body-title">产品特色</div>
			<div class="content-feature clearfix" id="feature">
				<table class="table">
				{loop $content.feature $r}
				<tr>
					<td class="image"><img src="{$r.image}" alt="{$r.title}"/></td>
					<td class="text">
						<h4 class="title">{$r.title}</h4>
						<div class="description">{$r.description}</div>
					</td>
				</tr>
				{/loop}
				</table>
			</div>

			<div class="content-body-title" role="button">
				服务热线：{block id="8"}
			</div>
			<div class="content-body-title" role="button" data-toggle="collapse" data-target="#content" aria-expanded="false" aria-controls="content">
				产品详情
				<i class="fa fa-plus pull-right"></i>
			</div>
			<div class="content-body text-justify collapse" id="content">
				{$content.content}
			</div>

			<div class="content-body-title" role="button" data-toggle="collapse" data-target="#cases" aria-expanded="false" aria-controls="cases">
				施工案例
				<i class="fa fa-plus pull-right"></i>
			</div>
			<div class="content-body text-justify collapse" id="cases">
				{$content.content}
			</div>
		
			<div class="blank"></div>

			<div class="content-tool">
				<div class="share">
					<div class="bdsharebuttonbox"><a href="#" class="bds_more" data-cmd="more"></a><a href="#" class="bds_qzone" data-cmd="qzone"></a><a href="#" class="bds_tsina" data-cmd="tsina"></a><a href="#" class="bds_tqq" data-cmd="tqq"></a><a href="#" class="bds_renren" data-cmd="renren"></a><a href="#" class="bds_weixin" data-cmd="weixin"></a></div>
					<script>window._bd_share_config={"common":{"bdSnsKey":{},"bdText":"","bdMini":"2","bdPic":"","bdStyle":"0","bdSize":"16"},"share":{},"image":{"viewList":["qzone","tsina","tqq","renren","weixin"],"viewText":"分享到：","viewSize":"16"},"selectShare":{"bdContainerClass":null,"bdSelectMiniList":["qzone","tsina","tqq","renren","weixin"]}};with(document)0[(getElementsByTagName('head')[0]||body).appendChild(createElement('script')).src='http://bdimg.share.baidu.com/static/api/js/share.js?v=89860593.js?cdnversion='+~(-new Date()/36e5)];</script>
				</div>				
			</div>

			<div class="content-prev-next">
				<div class="content-prev">
					<b>{t('上一篇')}</b> ：
					{content cid="$category.id" prev="$content.id" size="1"}
					<a href="{$r.url}" title="{$r.title}" {$r.style}>{$r.title}</a>
					{/content}
					{if empty($tag_content)} {t('暂无内容')} {/if}
				</div>

				<div class="content-next">
					<b>{t('下一篇')}</b> ：
					{content cid="$category.id" next="$content.id" size="1"}
					<a href="{$r.url}" title="{$r.title}" {$r.style}>{$r.title}</a>
					{/content}
					{if empty($tag_content)} {t('暂无内容')} {/if}
				</div>
			</div>			
		</div><!-- content -->

		</div><!-- main -->
		
		{if m('content.category.get',$category.rootid, 'childid')}

		<div class="col-sm-2 side">
			<div class="blank hidden-md hidden-lg"></div>
			<dl class="list-group">
				<dt class="list-group-item">{m('content.category.get',$category.rootid, 'name')}</dt>
				{content action="category" cid="$category.rootid"}
				<dd class="list-group-item"><a href="{$r.url}" {$r.style}>{$r.title}</a> {$r.new}</dd>
				{/content}			
			</dl>
		</div><!-- side -->

		{/if}

	</div><!-- row -->	

</div> 

{template 'footer.php'}