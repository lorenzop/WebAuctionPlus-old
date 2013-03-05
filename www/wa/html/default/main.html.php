<?php namespace wa\html;
if(!defined('psm\INDEX_FILE') || \psm\INDEX_FILE!==TRUE) {if(headers_sent()) {echo '<header><meta http-equiv="refresh" content="0;url=../"></header>';}
	else {header('HTTP/1.0 301 Moved Permanently'); header('Location: ../');} die("<font size=+2>Access Denied!!</font>");}
class html_main extends \psm\html\tplFile_Main {

	private $mainMenu;
	private $subMenu;


	public function __construct() {
		parent::__construct();
		// main menu
		$this->mainMenu = \psm\Widgets\NavBar\NavBar::factory()
			->setSelected(\psm\Portal::getModName())
			->setBrand('WebAuction<sup>Plus</sup>')
			->addBreak()
			->addButton('',			'Home',				'/',					'icon-home')
			->addButton('wa',		'WebAuction',		'/wa/',					'icon-shopping-cart')
			->addButton('wb',		'WeBook',			'/wb/',					'icon-book')
			->addDropdown('profile','lorenzop',			NULL,					'icon-user',			TRUE)
		;
		// sub menu
		$this->subMenu  = \psm\Widgets\NavBar\NavBar::factory()
			->setSelected(\psm\Portal::getPage())
			->addButton('current',	'Current Sales',	'./?page=current',		'icon-home')
			->addButton('myshop',	'My Shop',			'./?page=myshop',		'icon-shopping-cart')
			->addButton('mymailbox','My Mailbox',		'./?page=mymailbox',	'icon-envelope')
		;
	}


	/**
	 * html header
	 *
	 * @internal {site title}
	 * @internal {css}
	 * @internal {add to header}
	 * @return string
	 */
	protected function _head() {
//\psm\Portal::getPortal()->getEngine()->addCss('
//');
		// css
		self::addFileCSS(
			'{path=static}bootstrap/Cerulean/bootstrap.min.css',
			//'{path=static}bootstrap/css/bootstrap.min.css',
			'{path=static}bootstrap/css/bootstrap-responsive.min.css',
			'{path=theme}main.css'
		);
		// javascript
		self::addFileJS_top(
//			'{path=static}inputfunc.js'
			'{path=static}jquery/jquery-1.8.3.min.js',
			'{path=static}bootstrap/js/bootstrap.min.js'
		);
		// custom css/js
//		$this->addFileCSS_ifExists('{path=theme}custom.css');
//		$this->addFileJS_ifExists ('{path=theme}custom.js');
		return
'<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html lang="en" xmlns="http://www.w3.org/1999/xhtml">
<head>

<title>{site title}</title>
<meta http-equiv="content-type" content="text/html; charset=UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1.0" />
<link rel="icon" type="image/x-icon" href="{path=static}favicon.ico" />

{header content}

</head>
<body>
';
	}


	protected function _body() {
		return '

'.$this->mainMenu->Render().'
<div id="page-wrap">
'.$this->subMenu->Render(TRUE).'
<div class="container">


<!--
<div class="alert alert-block alert-info" style="margin-bottom: 0px;">
	<a href="#" class="close" data-dismiss="alert">&times;</a>
	<strong>Warning!</strong> Some error!
</div>
<div class="alert alert-block alert-success" style="margin-bottom: 0px;">
	<a href="#" class="close" data-dismiss="alert">&times;</a>
	<strong>Warning!</strong> Some error!
</div>
<div class="alert alert-block alert-warning" style="margin-bottom: 0px;">
	<a href="#" class="close" data-dismiss="alert">&times;</a>
	<strong>Warning!</strong> Some error!
</div>
<div class="alert alert-block alert-error" style="margin-bottom: 0px;">
	<a href="#" class="close" data-dismiss="alert">&times;</a>
	<strong>Warning!</strong> Some error!
</div>
-->

<!--
<div class="alert alert-block alert-error" style="margin-bottom: 0px;">
	<a href="#" class="close" data-dismiss="alert">&times;</a>
	<strong>Warning!</strong> Some error!
</div>
-->








{page content}

</div>
';
	}


	protected function _foot() {
$num_queries=3;
		return '
	<div id="footer-push"></div>
</div>
<footer id="footer">
	<div class="container">
		<table id="footer-table">
		<tr>
			<td class="footer-td-1">'.
				'Rendered page in '.\psm\Portal::GetRenderTime().' Seconds<br />'.
				'with '.((int)@$num_queries).' Queries&nbsp;</b>'.
			'</td>
			<td class="footer-td-2">


<!-- Paste advert code here -->
<!--                        -->
<!--                        -->
<!-- ====================== -->


{footer content}

				<p><a href="http://dev.bukkit.org/server-mods/webauctionplus/" target="_blank" style="color: #333333;">
				<u>WebAuctionPlus</u> '.\wa\module_wa::getVersion().'</a><br /><span style="font-size: smaller;">by lorenzop</span><span style="font-size: xx-small;"> &copy; 2012-2013</span></p>
			</td>
			<td class="footer-td-3">'.
				'<a href="http://twitter.github.com/bootstrap/" target="_blank">'.
				'<img src="{path=static}bootstrap-logo-128.png" alt="Powered by Twitter Bootstrap" style="width: 32px; height: 32px;" /></a>'.
//				'&nbsp;&nbsp;'.
//				'<a href="http://validator.w3.org/#validate_by_input" target="_blank">'.
//				'<img src="{path=static}valid-xhtml10.png" alt="Valid XHTML 1.0 Transitional" style="width: 88px; height: 31px;" /></a>'.
			'</td>
		</tr>
		</table>
	</div>
</footer>
';
	}


}
?>