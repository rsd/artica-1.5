<?php
	session_start();
	if($_SESSION["uid"]==null){echo "window.location.href ='logoff.php';";die();}
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.system.network.inc');
	include_once('ressources/class.freeweb.inc');
	include_once('ressources/class.awstats.inc');
	
	

	$user=new usersMenus();
	if($user->AsWebMaster==false){
		$tpl=new templates();
		echo "alert('". $tpl->javascript_parse_text("{ERROR_NO_PRIVS}")."');";
		die();exit();
	}
	
	if(isset($_GET["apacheMasterconfig"])){SaveMacterConfig();exit;}
	if(isset($_GET["popup"])){popup();exit;}
	if(isset($_GET["index"])){index();exit;}
	if(isset($_GET["webs"])){popup_webs();exit;}
	if(isset($_GET["EnableFreeWeb"])){saveEnableFreeWeb();exit;}
	if(isset($_GET["listwebs"])){listwebs();exit;}
	if(isset($_GET["listwebs-search"])){listwebs_search();exit;}
	if(isset($_GET["delete-servername"])){delete();exit;}
	if(isset($_GET["FreeWebLeftMenu"])){FreeWebLeftMenuSave();exit;}
	if(isset($_GET["apache-src-status"])){apache_src_status();exit;}
	if(isset($_GET["mode-evasive-section"])){mode_evasive();exit;}
	if(isset($_GET["mod-evasive-default"])){mode_evasive_default_js();exit;}
	if(isset($_GET["mod-evasive-form"])){mode_evasive_form();exit;}
	if(isset($_POST["DOSHashTableSize"])){mode_evasive_save();exit;}
	if(isset($_GET["modules"])){modules_list();exit;}
	if(isset($_POST["AddDefaultOne"])){add_default_site();exit;}
	if(isset($_POST["CheckAVailable"])){CheckAVailable();exit;}
	
	
	
	if(isset($_GET["params"])){parameters();exit;}
	
	if(isset($_GET["rebuild-items"])){rebuild_items();exit;}
	
js();


function js(){
	
	$page=CurrentPageName();
	
	if(isset($_GET["in-front-ajax"])){
		echo "$('#BodyContent').load('$page?popup=yes');";
		return;
	}	
	
	$html="YahooWin4('730','$page?popup=yes','Freewebs');";
	echo $html;
	}
function mode_evasive_default_js(){
	$page=CurrentPageName();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body("{DDOS_prevention}:{default}");
	$html="YahooWin3('650','$page?mod-evasive-form=yes','$title');";
	echo $html;
	}	

function parameters(){
	$sock=new sockets();
	$tpl=new templates();
	$users=new usersMenus();
	$page=CurrentPageName();
	$ApacheServerTokens=$sock->GET_INFO("ApacheServerTokens");
	$ApacheServerSignature=$sock->GET_INFO("ApacheServerSignature");
	$FreeWebsEnableModSecurity=$sock->GET_INFO("FreeWebsEnableModSecurity");
	$FreeWebsEnableModEvasive=$sock->GET_INFO("FreeWebsEnableModEvasive");
	$FreeWebsEnableModQOS=$sock->GET_INFO("FreeWebsEnableModQOS");
	$FreeWebsEnableOpenVPNProxy=$sock->GET_INFO("FreeWebsEnableOpenVPNProxy");
	$FreeWebsOpenVPNRemotPort=$sock->GET_INFO("FreeWebsOpenVPNRemotPort");
	$FreeWebsDisableSSLv2=$sock->GET_INFO("FreeWebsDisableSSLv2");
	$FreeWebPerformances=unserialize(base64_decode($sock->GET_INFO("FreeWebPerformances")));
	
	$JSFreeWebsEnableModSecurity=1;
	$JSFreeWebsEnableModEvasive=1;
	$JSFreeWebsEnableModQOS=1;
	$JSFreeWebsEnableOpenVPNProxy=1;
	if(!is_numeric($ApacheServerSignature)){$ApacheServerSignature=1;}
	if(!is_numeric($FreeWebsEnableModSecurity)){$FreeWebsEnableModSecurity=0;}
	if(!is_numeric($FreeWebsEnableModQOS)){$FreeWebsEnableModQOS=0;}
	if(!is_numeric($FreeWebsEnableOpenVPNProxy)){$FreeWebsEnableOpenVPNProxy=0;}
	if(!is_numeric($FreeWebsDisableSSLv2)){$FreeWebsDisableSSLv2=0;}
	if($ApacheServerTokens==null){$ApacheServerTokens="Full";}
	$varWwwPerms=$sock->GET_INFO("varWwwPerms");
	if($varWwwPerms==null){$varWwwPerms=755;}
	$ApacheServerTokens_array["Full"]="{all_informations}";
	$ApacheServerTokens_array["OS"]="{operating_system}";
	$ApacheServerTokens_array["Min"]="{minimal_infos}";
	$ApacheServerTokens_array["Minor"]="{minor_version}";
	$ApacheServerTokens_array["Major"]="{major_version}";
	$ApacheServerTokens_array["Prod"]="{product_apache_name}";
	
	if(!$users->APACHE_MOD_SECURITY){$JSFreeWebsEnableModSecurity=0;}
	if(!$users->APACHE_MOD_EVASIVE){$JSFreeWebsEnableModEvasive=0;}
	if(!$users->APACHE_MOD_QOS){$JSFreeWebsEnableModQOS=0;}
	if(!$users->APACHE_PROXY_MODE){$JSFreeWebsEnableOpenVPNProxy=0;}
	if(!is_numeric($FreeWebsOpenVPNRemotPort)){
		if($users->OPENVPN_INSTALLED){
			include_once(dirname(__FILE__).'/ressources/class.openvpn.inc');
			$vpn=new openvpn();
			$FreeWebsOpenVPNRemotPort=$vpn->main_array["GLOBAL"]["LISTEN_PORT"];
		}
	}
	
	if(!is_numeric($FreeWebPerformances["Timeout"])){$FreeWebPerformances["Timeout"]=300;}
	if(!is_numeric($FreeWebPerformances["KeepAlive"])){$FreeWebPerformances["KeepAlive"]=0;}
	if(!is_numeric($FreeWebPerformances["MaxKeepAliveRequests"])){$FreeWebPerformances["MaxKeepAliveRequests"]=100;}
	if(!is_numeric($FreeWebPerformances["KeepAliveTimeout"])){$FreeWebPerformances["KeepAliveTimeout"]=15;}
	if(!is_numeric($FreeWebPerformances["MinSpareServers"])){$FreeWebPerformances["MinSpareServers"]=5;}
	if(!is_numeric($FreeWebPerformances["MaxSpareServers"])){$FreeWebPerformances["MaxSpareServers"]=10;}
	if(!is_numeric($FreeWebPerformances["StartServers"])){$FreeWebPerformances["StartServers"]=5;}
	if(!is_numeric($FreeWebPerformances["MaxClients"])){$FreeWebPerformances["MaxClients"]=50;}
	if(!is_numeric($FreeWebPerformances["MaxRequestsPerChild"])){$FreeWebPerformances["MaxRequestsPerChild"]=10000;}
	
	
	
	
	
	$html="
	<div id='apacheMasterconfig'>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{ApacheServerTokens}:</td>
		<td>". Field_array_Hash($ApacheServerTokens_array,"ApacheServerTokens",$ApacheServerTokens,"style:font-size:14px;padding:3px")."</td>
	</tr>
	<tr>
		<td class=legend>{ApacheServerSignature}:</td>
		<td>". Field_checkbox("ApacheServerSignature",1,$ApacheServerSignature)."</td>
	</tr>
	<tr>
		<td class=legend>{VarWWWChmod}:</td>
		<td>". Field_text("varWwwPerms",$varWwwPerms,"font-size:14px;width:60px")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{disableSSLv2}:</td>
		<td>". Field_checkbox("FreeWebsDisableSSLv2",1,$FreeWebsDisableSSLv2)."</td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{FreeWebsEnableModSecurity}:</td>
		<td>". Field_checkbox("FreeWebsEnableModSecurity",1,$FreeWebsEnableModSecurity)."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{FreeWebsEnableModEvasive}:</td>
		<td>". Field_checkbox("FreeWebsEnableModEvasive",1,$FreeWebsEnableModEvasive)."&nbsp;<a href=\"javascript:blur();\" OnClick=\"javascript:Loadjs('$page?mod-evasive-default=yes')\" style='font-size:13px;text-decoration:underline'>{default}</a></td>
		<td>&nbsp;</td>
	</tr>	
	<tr>
		<td class=legend>{FreeWebsEnableModQOS}:</td>
		<td>". Field_checkbox("FreeWebsEnableModQOS",1,$FreeWebsEnableModQOS)."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{FreeWebsEnableOpenVPNProxy}:</td>
		<td>". Field_checkbox("FreeWebsEnableOpenVPNProxy",1,$FreeWebsEnableOpenVPNProxy,"FreeWebsEnableOpenVPNProxyCheck()")."</td>
		<td>&nbsp;</td>
	</tr>
	<tr>
		<td class=legend>{OpenVPNRemotePort}:</td>
		<td>". Field_text("FreeWebsOpenVPNRemotPort",$FreeWebsOpenVPNRemotPort,"font-size:13px;width:90px;padding:3px")."</td>
		<td>&nbsp;</td>
	</tr>
</table>
<br>
<table style='width:100%' class=form>
	<tr><td colspan=3 style='font-size:16px'>{performances}</td></tr>
	<tr>
		<td class=legend>{Timeout}:</td>
		<td>". Field_text("Timeout",$FreeWebPerformances["Timeout"],"font-size:13px;width:90px;padding:3px")."&nbsp;{seconds}</td>
		<td>". help_icon("{ApacheTimeout}")."</td>
	</tr>
	<tr>
		<td class=legend>{KeepAlive}:</td>
		<td>". Field_checkbox("KeepAlive",1,$FreeWebPerformances["KeepAlive"])."</td>
		<td>". help_icon("{ApacheKeepAlive}")."</td>
	</tr>	
	<tr>
		<td class=legend>{MaxKeepAliveRequests}:</td>
		<td>". Field_text("MaxKeepAliveRequests",$FreeWebPerformances["MaxKeepAliveRequests"],"font-size:13px;width:90px;padding:3px")."&nbsp;{requests}</td>
		<td>". help_icon("{ApacheMaxKeepAliveRequests}")."</td>
	</tr>		
	<tr>
		<td class=legend>{KeepAliveTimeout}:</td>
		<td>". Field_text("KeepAliveTimeout",$FreeWebPerformances["KeepAliveTimeout"],"font-size:13px;width:90px;padding:3px")."&nbsp;{seconds}</td>
		<td>". help_icon("{ApacheKeepAliveTimeout}")."</td>
	</tr>
	<tr>
		<td class=legend>{StartServers}:</td>
		<td>". Field_text("StartServers",$FreeWebPerformances["StartServers"],"font-size:13px;width:90px;padding:3px")."&nbsp;</td>
		<td>". help_icon("{ApacheStartServers}")."</td>
	</tr>	
	<tr>
		<td class=legend>{MinSpareServers}:</td>
		<td>". Field_text("MinSpareServers",$FreeWebPerformances["MinSpareServers"],"font-size:13px;width:90px;padding:3px")."&nbsp;</td>
		<td>". help_icon("{ApacheMinSpareServers}")."</td>
	</tr>	
	<tr>
		<td class=legend>{MaxSpareServers}:</td>
		<td>". Field_text("MaxSpareServers",$FreeWebPerformances["MaxSpareServers"],"font-size:13px;width:90px;padding:3px")."&nbsp;</td>
		<td>". help_icon("{ApacheMinSpareServers}")."</td>
	</tr>	
	<tr>
		<td class=legend>{MaxClients}:</td>
		<td>". Field_text("MaxClients",$FreeWebPerformances["MaxClients"],"font-size:13px;width:90px;padding:3px")."&nbsp;</td>
		<td>". help_icon("{ApacheMaxClients}")."</td>
	</tr>		
	<tr>
		<td class=legend>{MaxRequestsPerChild}:</td>
		<td>". Field_text("MaxRequestsPerChild",$FreeWebPerformances["MaxRequestsPerChild"],"font-size:13px;width:90px;padding:3px")."&nbsp;</td>
		<td>". help_icon("{ApacheMaxRequestsPerChild}")."</td>
	</tr>		
	
	
	<tr>
		<td colspan=3 align='right'>
		<hr>". button("{apply}","SaveApacheCentralSettings()")."</td>
	</tr>
	</table>
	</div>
	
	
	
<script>
		var x_SaveApacheCentralSettings=function (obj) {
			var results=obj.responseText;
			if(results.length>2){alert(results);}			
			RefreshTab('main_config_freeweb');
		}	
		
		function SaveApacheCentralSettings(){
			var XHR = new XHRConnection();
			XHR.appendData('apacheMasterconfig','yes');
    		XHR.appendData('ApacheServerTokens',document.getElementById('ApacheServerTokens').value);
    		XHR.appendData('varWwwPerms',document.getElementById('varWwwPerms').value);
    		XHR.appendData('FreeWebsOpenVPNRemotPort',document.getElementById('FreeWebsOpenVPNRemotPort').value);
    		if(document.getElementById('ApacheServerSignature').checked){XHR.appendData('ApacheServerSignature',1);}else{XHR.appendData('ApacheServerSignature',0);}
			if(document.getElementById('FreeWebsEnableModSecurity').checked){XHR.appendData('FreeWebsEnableModSecurity',1);}else{XHR.appendData('FreeWebsEnableModSecurity',0);}
			if(document.getElementById('FreeWebsEnableModEvasive').checked){XHR.appendData('FreeWebsEnableModEvasive',1);}else{XHR.appendData('FreeWebsEnableModEvasive',0);}
			if(document.getElementById('FreeWebsEnableModQOS').checked){XHR.appendData('FreeWebsEnableModQOS',1);}else{XHR.appendData('FreeWebsEnableModQOS',0);}
			if(document.getElementById('FreeWebsEnableOpenVPNProxy').checked){XHR.appendData('FreeWebsEnableOpenVPNProxy',1);}else{XHR.appendData('FreeWebsEnableOpenVPNProxy',0);}
			if(document.getElementById('FreeWebsDisableSSLv2').checked){XHR.appendData('FreeWebsDisableSSLv2',1);}else{XHR.appendData('FreeWebsDisableSSLv2',0);}
			
			if(document.getElementById('KeepAlive').checked){XHR.appendData('KeepAlive',1);}else{XHR.appendData('KeepAlive',0);}
			XHR.appendData('Timeout',document.getElementById('Timeout').value);
			XHR.appendData('MaxKeepAliveRequests',document.getElementById('MaxKeepAliveRequests').value);
			XHR.appendData('KeepAliveTimeout',document.getElementById('KeepAliveTimeout').value);
			XHR.appendData('MinSpareServers',document.getElementById('MinSpareServers').value);
			XHR.appendData('MaxSpareServers',document.getElementById('MaxSpareServers').value);
			XHR.appendData('StartServers',document.getElementById('StartServers').value);
			XHR.appendData('MaxClients',document.getElementById('MaxClients').value);
			XHR.appendData('MaxRequestsPerChild',document.getElementById('MaxRequestsPerChild').value);

 			AnimateDiv('apacheMasterconfig');
    		XHR.sendAndLoad('$page', 'GET',x_SaveApacheCentralSettings);
			
		}
		
		function ModSecurityDisable(){
			var JSFreeWebsEnableModSecurity=$JSFreeWebsEnableModSecurity;
			var JSFreeWebsEnableModEvasive=$JSFreeWebsEnableModEvasive;
			var JSFreeWebsEnableModQOS=$JSFreeWebsEnableModQOS;
			var JSFreeWebsEnableOpenVPNProxy=$JSFreeWebsEnableOpenVPNProxy;
			if(JSFreeWebsEnableModSecurity==0){
				document.getElementById('FreeWebsEnableModSecurity').checked=false;
				document.getElementById('FreeWebsEnableModSecurity').disabled=true;
			}
			if(JSFreeWebsEnableModEvasive==0){
				document.getElementById('FreeWebsEnableModEvasive').checked=false;
				document.getElementById('FreeWebsEnableModEvasive').disabled=true;
			}
			if(JSFreeWebsEnableModQOS==0){
				document.getElementById('FreeWebsEnableModQOS').checked=false;
				document.getElementById('FreeWebsEnableModQOS').disabled=true;
			}
			if(JSFreeWebsEnableOpenVPNProxy==0){
				document.getElementById('FreeWebsEnableOpenVPNProxy').checked=false;
				document.getElementById('FreeWebsEnableOpenVPNProxy').disabled=true;
				document.getElementById('FreeWebsOpenVPNRemotPort').disabled=true;
			}
			
		}
		
		function FreeWebsEnableOpenVPNProxyCheck(){
			if(!document.getElementById('FreeWebsEnableOpenVPNProxy').checked){
				document.getElementById('FreeWebsOpenVPNRemotPort').disabled=true;
			}else{
				document.getElementById('FreeWebsOpenVPNRemotPort').disabled=false;
			}
		}
		
		ModSecurityDisable();
		FreeWebsEnableOpenVPNProxyCheck();
	</script>
	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function SaveMacterConfig(){
	$sock=new sockets();
	$sock->SET_INFO("ApacheServerSignature",$_GET["ApacheServerSignature"]);
	$sock->SET_INFO("ApacheServerTokens",$_GET["ApacheServerTokens"]);
	$sock->SET_INFO("FreeWebsEnableModSecurity",$_GET["FreeWebsEnableModSecurity"]);
	$sock->SET_INFO("FreeWebsEnableModEvasive",$_GET["FreeWebsEnableModEvasive"]);
	$sock->SET_INFO("FreeWebsEnableModQOS",$_GET["FreeWebsEnableModQOS"]);
	$sock->SET_INFO("FreeWebsOpenVPNRemotPort",$_GET["FreeWebsOpenVPNRemotPort"]);
	$sock->SET_INFO("FreeWebsEnableOpenVPNProxy",$_GET["FreeWebsEnableOpenVPNProxy"]);
	$sock->SET_INFO("FreeWebsDisableSSLv2",$_GET["FreeWebsDisableSSLv2"]);
	$sock->SET_INFO("varWwwPerms",$_GET["varWwwPerms"]);
	$sock->SaveConfigFile(base64_encode(serialize($_GET)), "FreeWebPerformances");
	
	
	
	$sock->getFrameWork("cmd.php?restart-apache-src=yes");
}




function popup(){
	$tpl=new templates();	
	$page=CurrentPageName();
	$users=new usersMenus();
	$array["index"]='{status}';
	$array["webs"]='{squid_accel_websites}';
	$array["params"]='{parameters}';
	$array["modules"]='{available_modules}';
	if($users->PUREFTP_INSTALLED){
		$array["pure-ftpd"]='{APP_PUREFTPD}';
	}
	
	
	
	
	
	while (list ($num, $ligne) = each ($array) ){
		
		if($num=="pure-ftpd"){
			$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"pureftp.index.php?pure-ftpd-page=yes\"><span>$ligne</span></a></li>\n");
			continue;
		}
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?$num=yes\"><span>$ligne</span></a></li>\n");
	}
	
	
	echo "
	<div id=main_config_freeweb style='width:100%;height:700px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
		  $(document).ready(function() {
			$(\"#main_config_freeweb\").tabs();});
		</script>";		
	
}

function index(){
	$page=CurrentPageName();
	$tpl=new templates();	
	$sock=new sockets();
	$EnableFreeWeb=$sock->GET_INFO("EnableFreeWeb");
	$FreeWebListen=$sock->GET_INFO("FreeWebListen");
	$FreeWebListenPort=$sock->GET_INFO("FreeWebListenPort");
	$FreeWebListenSSLPort=$sock->GET_INFO("FreeWebListenSSLPort");
	$FreeWebListen=$sock->GET_INFO("FreeWebListen");
	$FreeWebLeftMenu=$sock->GET_INFO("FreeWebLeftMenu");
	
	if($FreeWebListen==null){$FreeWebListen="*";}
	if($EnableFreeWeb==null){$EnableFreeWeb=0;}
	if($FreeWebListenPort==null){$FreeWebListenPort=80;}
	if($FreeWebListenSSLPort==null){$FreeWebListenSSLPort=443;}
	if($FreeWebLeftMenu==null){$FreeWebLeftMenu=1;}
	$tcp=new networking();
	
	$ips=$tcp->ALL_IPS_GET_ARRAY();
	$ips["*"]="{all}";
	
	
	
	$p=Paragraphe_switch_img("{enable_freeweb}","{enable_freeweb_text}","EnableFreeWeb",$EnableFreeWeb,null,400);

	$html="
	<table style='width:100%'>
	<tr>
		<td valign='top'>
			<center><img src='img/free-web-128.png'></center>
			<hr>
			<div id='apache-src-status'></div>
			
			
		</td>
		<td valign='top'>$p
		<table style='width:100%;margin:5px;padding:5px;border:1px solid #CCCCCC'>
			<td class=legend>{add_to_left_menu}:</td>
			<td>". Field_checkbox("FreeWebLeftMenu",1,$FreeWebLeftMenu,"FreeWebLeftMenuCheck()")."</td>
		</tr>		
		<tr>
			<td class=legend>{listen_ip}:</td>
			<td>". Field_array_Hash($ips,"FreeWebListen",$FreeWebListen,"style:font-size:13px;padding:3px")."</td>
		</tr>
		<tr>
			<td class=legend>{listen_port}:</td>
			<td>". Field_text("FreeWebListenPort",$FreeWebListenPort,"font-size:13px;padding:3px;width:60px")."</td>
		</tr>
		<tr>
			<td class=legend>{listen_port} SSL:</td>
			<td>". Field_text("FreeWebListenSSLPort",$FreeWebListenSSLPort,"font-size:13px;padding:3px;width:60px")."</td>
		</tr>					
		</table>
		
		<hr>
		<div style='width:100%;text-align:right'>". button("{apply}","EnableFreeWebSave()")."</div>
		</td>
	</tr>
	</table>
	<script>
	
	function statusRefresh(){
		LoadAjax('apache-src-status','$page?apache-src-status=yes');
	}
	
	var x_EnableFreeWebSave=function (obj) {
			var results=obj.responseText;
			RefreshTab('main_config_freeweb');
		}	
		
		function EnableFreeWebSave(){
			var XHR = new XHRConnection();
    		XHR.appendData('EnableFreeWeb',document.getElementById('EnableFreeWeb').value);
    		XHR.appendData('FreeWebListen',document.getElementById('FreeWebListen').value);
    		XHR.appendData('FreeWebListenPort',document.getElementById('FreeWebListenPort').value);
    		XHR.appendData('FreeWebListenSSLPort',document.getElementById('FreeWebListenSSLPort').value);
 			document.getElementById('img_EnableFreeWeb').src='img/wait_verybig.gif';
    		XHR.sendAndLoad('$page', 'GET',x_EnableFreeWebSave);
			
		}	
		
		var x_FreeWebLeftMenuCheck=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}			
			CacheOff();
		}		
		
		function FreeWebLeftMenuCheck(){
			var XHR = new XHRConnection();
			if(document.getElementById('FreeWebLeftMenu').checked){
	    			XHR.appendData('FreeWebLeftMenu',1);
				}else{
					XHR.appendData('FreeWebLeftMenu',1);
				}
				XHR.sendAndLoad('$page', 'GET',x_FreeWebLeftMenuCheck);
		}
	statusRefresh();
	</script>";
	
	echo $tpl->_ENGINE_parse_body($html);
}

function saveEnableFreeWeb(){
	$sock=new sockets();
	$sock->SET_INFO("EnableFreeWeb",$_GET["EnableFreeWeb"]);
	$sock->SET_INFO("EnableApacheSystem",$_GET["EnableFreeWeb"]);

	if($_GET["EnableFreeWeb"]==null){$_GET["EnableFreeWeb"]="*";}
	if($_GET["EnableFreeWeb"]==1){$sock->SET_INFO("PureFtpdEnabled",1);}
	
	$sock->SET_INFO("FreeWebListen",$_GET["FreeWebListen"]);
	$sock->SET_INFO("FreeWebListenPort",$_GET["FreeWebListenPort"]);
	$sock->SET_INFO("FreeWebListenSSLPort",$_GET["FreeWebListenSSLPort"]);
	
	
	$sock->getFrameWork("cmd.php?freeweb-restart=yes");
	$sock->getFrameWork("cmd.php?pure-ftpd-restart=yes");
	}

function popup_webs(){
	$page=CurrentPageName();
	$tpl=new templates();		
	$html="
	<table style='width:100%'>
	<tr>
		<td width=99%><div class=explain>{freewebs_explain}</div></td>
		<td width=1%>". imgtootltip("website-add-64.png","{add}","Loadjs('freeweb.edit.php?hostname=')")."</td>
	</tr>
</table>
	<div style='width:100%;text-align:right'>
	<a href=\"javascript:blur();\" OnClick=\"javascript:RebuildFreeweb();\" style='font-size:14px'>{rebuild_items}</a></div>
		
	<div id='freewebs_list' style='margin:5px'></div>
	
	<script>
		LoadAjax('freewebs_list','$page?listwebs=yes');
		
		var x_RebuildFreeweb=function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}			
			LoadAjax('freewebs_list','$page?listwebs=yes');
		}			
		
		function RebuildFreeweb(){
			var XHR = new XHRConnection();
			XHR.appendData('rebuild-items','yes');
 			document.getElementById('freewebs_list').innerHTML='<center><img src=img/wait_verybig.gif></center>';
    		XHR.sendAndLoad('$page', 'GET',x_RebuildFreeweb);
		
		}
		
	</script>
	
	";
	echo $tpl->_ENGINE_parse_body($html);
	
	
}	

function listwebs(){
		$page=CurrentPageName();
			$tpl=new templates();	
	$t=time();
	$html="
	<center>
	<table style='width:100%' class=form style='width:50%'>
	<tr>
	<td class=legend>{search}:</td>
	<td>". Field_text("freewebs-search",null,"font-size:14px;padding:3px",null,null,null,false,"FreeWebsSearchCheck(event)")."</td>
	</tr>
	</table>
	</center>
	<div id='$t'></div>
	<script>
		function FreeWebsSearchCheck(e){
			if(checkEnter(e)){FreeWebsSearch();}
		}
		function FreeWebsSearch(){
			var se=escape(document.getElementById('freewebs-search').value);
			LoadAjax('$t','$page?listwebs-search=yes&search='+se);
		}
			
		FreeWebsSearch();	
	</script>
	";
	echo $tpl->_ENGINE_parse_body($html);
}


function listwebs_search(){
	include_once(dirname(__FILE__).'/ressources/class.apache.inc');
	$vhosts=new vhosts();
	$search=$_GET["search"];
	$page=CurrentPageName();
	$users=new usersMenus();
	$where=null;
	if(!$users->AsSystemAdministrator){
		$whereOU="  AND ou='{$_SESSION["ou"]}'";$ou="&nbsp;&raquo;&nbsp;{$_SESSION["ou"]}";
	}
	
	if(strlen($search)>1){
		$search="*$search*";
		$search=str_replace("*","%",$search);
		$search=str_replace("%%","%",$search);
		$whereOU="AND (servername LIKE '$search' $whereOU) OR (domainname LIKE '$search' $whereOU)";
	}
	
	
	
	
	$tpl=new templates();	
	$sock=new sockets();	
	
	$delete_freeweb_text=$tpl->javascript_parse_text("{delete_freeweb_text}");
	$sql="SELECT * FROM freeweb WHERE 1 $whereOU ORDER BY servername";
	$q=new mysql();
	if(!isset($_SESSION["CheckTableWebsites"])){$q->BuildTables();$_SESSION["CheckTableWebsites"]=true;}
	
	$results=$q->QUERY_SQL($sql,'artica_backup');
	if(!$q->ok){echo "<H2>$q->mysql_error</H2><code>$sql</code>";}
	$vgservices=unserialize(base64_decode($sock->GET_INFO("vgservices")));
	$html="
<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
<thead class='thead'>
	<tr>
	<th colspan=2>{joomlaservername}:$ou</th>
	<th>{ssl}</th>
	<th>&nbsp;</th>
	<th>&nbsp;</th>
	<th>{member}</th>
	<th>&nbsp;</th>
	</tr>
</thead>
<tbody class='tbody'>";			
	
	while($ligne=mysql_fetch_array($results,MYSQL_ASSOC)){
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
		if($ligne["useSSL"]==1){$ssl="check2.gif";}else{$ssl="check1.gif";}
		$statistics="&nbsp;";
		$exec_statistics="&nbsp;";
		$groupware=null;
		$forward_text=null;
		
		$added_port=null;
		$icon="free-web-32.png";
		$aw=new awstats($ligne["servername"]);
		if($aw->getCountDePages()>0){
			$statistics= imgtootltip("status_statistics-22.png","{statistics}","Loadjs('awstats.view.php?servername={$ligne["servername"]}')");
		}
		
		if($aw->GET("AwstatsEnabled")){
			$exec_statistics=imgtootltip("22-recycle.png","{build_awstats_statistics}","Loadjs('awstats.php?servername={$ligne["servername"]}&execute=yes')");
		}
		
		if($vgservices["freewebs"]<>null){
			if($ligne["lvm_size"]>0){
				$ligne["lvm_size"]=$ligne["lvm_size"]*1024;
				$sizevg="&nbsp;<i style='font-size:11px'>(".FormatBytes($ligne["lvm_size"]).")</i>";
				
			}
		}
		$ServerPort=$ligne["ServerPort"];
		if($ServerPort>0){$added_port=":$ServerPort";}
		if($ligne["UseReverseProxy"]){$icon="Firewall-Move-Right-32.png";}
		
		if($ligne["groupware"]<>null){
			$groupware="<span style='text-align:right;font-size:11px;font-weight:bold;font-style:italic;color:#B64B13;float:right'>&nbsp;({{$vhosts->TEXT_ARRAY[$ligne["groupware"]]["TITLE"]}})</span>";
		}
		
		if($ligne["Forwarder"]==1){$forward_text="<span style='text-align:right;font-size:11px;font-weight:bold;font-style:italic;color:#B64B13;float:right'>{www_forward} <b>{$ligne["ForwardTo"]}</b></span>";}
		$edit=imgtootltip($icon,"{$ligne["resolved_ipaddr"]}<br>{edit}","Loadjs('freeweb.edit.php?hostname={$ligne["servername"]}')");
		
		
		$servername_text=$ligne["servername"];
		if($servername_text=="_default_"){
			$servername_text="{all}";
			$groupware="<span style='text-align:right;font-size:11px;font-weight:bold;font-style:italic;color:#B64B13;float:right'>&nbsp;({default_website})</span><br>";
		}else{
			if(trim($ligne["resolved_ipaddr"])==null){
				$edit=imgtootltip("warning-panneau-32.png","{could_not_find_iphost}<br>{click_to_edit}","Loadjs('freeweb.edit.php?hostname={$ligne["servername"]}')");
			}
				
		}
		
		$href="<a href=\"javascript:blur();\" OnClick=\"javascript:Loadjs('freeweb.edit.php?hostname={$ligne["servername"]}')\"
		style='font-size:13px;text-decoration:underline;font-weight:bold'>";
		
		$html=$html."
			<tr class=$classtr>
			<td width=1%>$edit</td>
			<td nowrap>$groupware$forward_text<span style='float:left'><strong style='font-size:13px'>$href$servername_text</a>$added_port$sizevg</strong></span></td>
			<td width=1%><img src='img/$ssl'></td>
			<td width=1%>$statistics</td>
			<td width=1%>$exec_statistics</td>
			<td nowrap><strong style='font-size:13px'>{$ligne["uid"]}$ipDetect</strong></td>
			<td width=1%>". imgtootltip("delete-24.png","{delete}","FreeWebDelete('{$ligne["servername"]}')")."</td>
			</tr>
			";
		
	}	
	
	$html=$html."
	</tbody>
	</table>
	<div style='text-align:right;margin-top:8px'>". button("{recheck_net_items}","FreeWeCheckVirtualHost()")."&nbsp;&nbsp;|&nbsp;&nbsp;". button("{add_default_www}","FreeWebAddDefaultVirtualHost()")."</div>
	<script>
	var x_FreeWebDelete=function (obj) {
			var results=obj.responseText;
			if(results.length>10){alert(results);}	
			if(document.getElementById('main_config_freeweb')){	RefreshTab('main_config_freeweb');}
			if(document.getElementById('container-www-tabs')){	RefreshTab('container-www-tabs');}
		}	
		
		function FreeWebDelete(server){
			if(confirm('$delete_freeweb_text')){
				var XHR = new XHRConnection();
				XHR.appendData('delete-servername',server);
				AnimateDiv('freewebs_list');
    			XHR.sendAndLoad('$page', 'GET',x_FreeWebDelete);
			}
		}	
		
		function FreeWebAddDefaultVirtualHost(){
			var XHR = new XHRConnection();
			XHR.appendData('AddDefaultOne','yes');
			AnimateDiv('freewebs_list');
    		XHR.sendAndLoad('$page', 'POST',x_FreeWebDelete);		
		}
		
		function FreeWeCheckVirtualHost(){
			var XHR = new XHRConnection();
			XHR.appendData('CheckAVailable','yes');
			AnimateDiv('freewebs_list');
    		XHR.sendAndLoad('$page', 'POST',x_FreeWebDelete);			
		}
		
	</script>	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}

function FreeWebLeftMenuSave(){
	$sock=new sockets();
	$sock->SET_INFO("FreeWebLeftMenu",$_GET["FreeWebLeftMenu"]);
	
}

function delete(){
	writelogs("Delete server \"{$_GET["delete-servername"]}\"",__FUNCTION__,__FILE__,__LINE__);
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?ApacheDirDelete=". urlencode($_GET["delete-servername"]));
	}

function apache_src_status(){
	$refresh="<div style='text-align:right;margin-top:8px'>".imgtootltip("refresh-24.png","{refresh}","RefreshTab('main_config_freeweb')")."</div>";
	$sock=new sockets();
	$tpl=new templates();
	$ini=new Bs_IniHandler();
	$page=CurrentPageName();	
	$datas=$sock->getFrameWork("cmd.php?apachesrc-ini-status=yes");
	writelogs(strlen($datas)." bytes for apache status",__CLASS__,__FUNCTION__,__FILE__,__LINE__);
	$ini->loadString(base64_decode($datas));
	$status=DAEMON_STATUS_ROUND("APP_APACHE_SRC",$ini,null,0)."<br>".DAEMON_STATUS_ROUND("PUREFTPD",$ini,null,0).$refresh;
	echo $tpl->_ENGINE_parse_body($status);	
	
}

function rebuild_items(){
	$sock=new sockets();
	$sock->getFrameWork("cmd.php?freewebs-rebuild=yes");
	$tpl=new templates();
	echo $tpl->javascript_parse_text("{operation_launched_in_background}");
	
}

function mode_evasive_form(){
	$page=CurrentPageName();
	$tpl=new templates();
	$sock=new sockets();
	$Params=unserialize(base64_decode($sock->GET_INFO("modEvasiveDefault")));
	
	
	if(!is_numeric($Params["DOSHashTableSize"])){$Params["DOSHashTableSize"]=1024;}
	if(!is_numeric($Params["DOSPageCount"])){$Params["DOSPageCount"]=10;}
	if(!is_numeric($Params["DOSSiteCount"])){$Params["DOSSiteCount"]=150;}
	if(!is_numeric($Params["DOSPageInterval"])){$Params["DOSPageInterval"]=1.5;}
	if(!is_numeric($Params["DOSSiteInterval"])){$Params["DOSSiteInterval"]=1.5;}
	if(!is_numeric($Params["DOSBlockingPeriod"])){$Params["DOSBlockingPeriod"]=10.7;}
	
	
	
	
	$html="
	<div class=explain id='modeevasivedef'>{mod_evasive_explain}</div>
	<table style='width:100%' class=form>
	<tr>
		<td class=legend>{DOSHashTableSize}:</td>
		<td>". Field_text("DOSHashTableSize",$Params["DOSHashTableSize"],"font-size:13px;padding:3px;width:60px")."</td>
		<td>". help_icon("{DOSHashTableSize_explain}")."</td>
	</tr>	
	
	
	<tr>
		<td class=legend>{threshold}:</td>
		<td>". Field_text("DOSPageCount",$Params["DOSPageCount"],"font-size:13px;padding:3px;width:60px")."</td>
		<td>". help_icon("{DOSPageCount_explain}")."</td>
	</tr>

	
	<tr>
		<td class=legend>{total_threshold}:</td>
		<td>". Field_text("DOSSiteCount",$Params["DOSSiteCount"],"font-size:13px;padding:3px;width:60px")."</td>
		<td>". help_icon("{DOSSiteCount_explain}")."</td>
	</tr>

	<tr>
		<td class=legend>{page_interval}:</td>
		<td>". Field_text("DOSPageInterval",$Params["DOSPageInterval"],"font-size:13px;padding:3px;width:60px")."</td>
		<td>". help_icon("{DOSPageInterval_explain}")."</td>
	</tr>	
	


	<tr>
		<td class=legend>{site_interval}:</td>
		<td>". Field_text("DOSSiteInterval",$Params["DOSSiteInterval"],"font-size:13px;padding:3px;width:60px")."</td>
		<td>". help_icon("{DOSSiteInterval_explain}")."</td>
	</tr>

	<tr>
		<td class=legend>{Blocking_period}:</td>
		<td>". Field_text("DOSBlockingPeriod",$Params["DOSBlockingPeriod"],"font-size:13px;padding:3px;width:60px")."</td>
		<td>". help_icon("{DOSBlockingPeriod_explain}")."</td>
	</tr>	
	
	<tr>
		<td colspan=3 align='right'><hr>". button("{apply}","SaveModEvasiveDefault()")."</td>
	</tr>
	</table>
	
	<script>
	var x_SaveModEvasiveDef=function (obj) {
		var results=obj.responseText;
		if(results.length>3){alert(results);}			
		YahooWin3Hide();
	}	
	
	function SaveModEvasiveDefault(){
		var XHR = new XHRConnection();
		XHR.appendData('DOSHashTableSize',document.getElementById('DOSHashTableSize').value);
		XHR.appendData('DOSPageCount',document.getElementById('DOSPageCount').value);
		XHR.appendData('DOSSiteCount',document.getElementById('DOSSiteCount').value);
		XHR.appendData('DOSPageInterval',document.getElementById('DOSPageInterval').value);
		XHR.appendData('DOSSiteInterval',document.getElementById('DOSSiteInterval').value);
		XHR.appendData('DOSBlockingPeriod',document.getElementById('DOSBlockingPeriod').value);
		AnimateDiv('modeevasivedef');
    	XHR.sendAndLoad('$page', 'POST',x_SaveModEvasiveDef);		
	}

	</script>	
	
	
	";
	
	echo $tpl->_ENGINE_parse_body($html);
	
	
}
function mode_evasive_save(){
	$sock=new sockets();
	$sock->SaveConfigFile(base64_encode(serialize($_POST)), "modEvasiveDefault");
	$sock->getFrameWork("freeweb.php?reconfigure=yes");
}

function modules_list(){
	$sock=new sockets();
	$page=CurrentPageName();
	$tpl=new templates();	
	$results=unserialize(base64_decode($sock->getFrameWork("freeweb.php?loaded-modules=yes")));
	$html="
	<table cellspacing='0' cellpadding='0' border='0' class='tableView' style='width:100%'>
	<thead class='thead'>
		<tr>
		<th width=1%>". imgtootltip("refresh-24.png","{refresh}","RefreshTab('main_config_freeweb')")."</th>
		<th>{available_modules}</th>
	</thead>
	<tbody class='tbody'>";	
	
	while (list ($num, $ligne) = each ($results) ){
		if(!preg_match("#(.+?)\s+\((.+?)\)#",$ligne,$re)){continue;}
		if($classtr=="oddRow"){$classtr=null;}else{$classtr="oddRow";}
$html=$html."
		<tr class=$classtr>
		<td width=1%><img src='img/32-nodes.png'></td>
		<td style='font-size:14px'><strong style='font-size:16px'>{$re[1]}</strong> ({$re[2]})</td>
		</tr>
		";
			
	}
	
	$html=$html."
	</table>";
	echo $tpl->_ENGINE_parse_body($html);
}

function add_default_site(){
	$free=new freeweb();
	$free->AddDefaultSite();
}
function CheckAVailable(){
	$sock=new sockets();
	$sock->getFrameWork("freeweb.php?force-resolv=yes");
}


?>
	
	