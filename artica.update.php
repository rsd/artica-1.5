<?php
	include_once('ressources/class.templates.inc');
	include_once('ressources/class.ldap.inc');
	include_once('ressources/class.users.menus.inc');
	include_once('ressources/class.artica.inc');
	include_once('ressources/class.mysql.inc');
	include_once('ressources/class.system.network.inc');
	$usersmenus=new usersMenus();
	if($usersmenus->AsArticaAdministrator==false){header('location:users.index.php');exit;}	
	if(isset($_GET["main_artica_update"])){main_artica_update_switch();exit;}
	if(isset($_GET["CheckEveryMinutes"])){SaveConf();exit;}
	if(isset($_GET["ArticaUpdateInstallPackage"])){ArticaUpdateInstallPackage();exit;}
	if(isset($_GET["auto_update_perform"])){auto_update_perform();exit;}
	if(isset($_GET["ajax-events"])){main_artica_update_events_display();exit;}
	
	
	
	if(isset($_GET["js"])){popup_js();exit;}
	if(isset($_GET["ajax-pop"])){popup();exit;}
	
	
	main_artica_update_page();
	
	
function popup_js(){
	$artica=new artica_general();
	$tpl=new templates();
	$title=$tpl->_ENGINE_parse_body('{artica_autoupdate}');
	$events=$tpl->_ENGINE_parse_body('{events}');
	$page=CurrentPageName();
	$datas=file_get_contents('js/artica_settings.js');
	$html="
	$datas
	YahooWin(700,'artica.update.php?ajax-pop=yes','$title V.$artica->ArticaVersion');
	
	function ShowArticaUpdateEvents(file){
		YahooWin2('700','artica.update.php?ajax-events='+file,'$events V.$artica->ArticaVersion ');
		}
	
var x_SaveArticaUpdateForm= function (obj) {
			var results=obj.responseText;
			if(results.length>0){alert(results);}
			RefreshTab('main_config_artica_update');
			}		
	
	
	function SaveArticaUpdateForm(){
		var XHR = new XHRConnection();
		if(document.getElementById('enabled').checked){XHR.appendData('enabled','yes');}else{XHR.appendData('enabled','no');}
		if(document.getElementById('autoinstall').checked){XHR.appendData('autoinstall','yes');}else{XHR.appendData('autoinstall','no');}
		if(document.getElementById('nightlybuild').checked){XHR.appendData('nightlybuild','yes');}else{XHR.appendData('nightlybuild','no');}		
		if(document.getElementById('front_page_notify').checked){XHR.appendData('front_page_notify','yes');}else{XHR.appendData('front_page_notify','no');}
		if(document.getElementById('EnableNightlyInFrontEnd').checked){XHR.appendData('EnableNightlyInFrontEnd','1');}else{XHR.appendData('EnableNightlyInFrontEnd','0');}
		
		
		
		
		if(document.getElementById('samba_notify')){
			if(document.getElementById('samba_notify').checked){XHR.appendData('samba_notify','yes');}else{XHR.appendData('samba_notify','no');}
		}
		
		
		if(document.getElementById('auto_apt')){
			if(document.getElementById('auto_apt').checked){XHR.appendData('auto_apt','yes');}else{XHR.appendData('auto_apt','no');}
		}		

		
		
		
		XHR.appendData('WgetBindIpAddress',document.getElementById('WgetBindIpAddress').value);
    	XHR.appendData('CheckEveryMinutes',document.getElementById('CheckEveryMinutes').value);
    	XHR.appendData('uri',document.getElementById('uri').value);
    	document.getElementById('ArticaUpdateForm').innerHTML='<center><img src=\"img/wait_verybig.gif\"></center>';
    	XHR.sendAndLoad('$page', 'GET',x_SaveArticaUpdateForm);
		}
	
	";
	
	echo $html;
	
	
	
	
}


function popup(){
	$tpl=new templates();
	$html="
	<div id='main_artica_update'>
	";
	echo $tpl->_ENGINE_parse_body($html);
	echo main_artica_update_switch();
	echo "</div>";
	
	
	
	
	
}

	
function main_artica_update_page(){
$artica=new artica_general();
$page=CurrentPageName();

$html="
<div style='text-align:right'><H2>{current_version}:$artica->ArticaVersion</H2></div>
<div id='main_artica_update'></div>
		<script>LoadAjax('main_artica_update','$page?main_artica_update=config');</script>
	
	";
	$cfg["JS"][]="js/artica_settings.js";
	$tpl=new template_users('{artica_autoupdate}',$html,0,0,0,0,$cfg);
	echo $tpl->web_page;
		
	
	
}


function main_artica_update_config(){

	$page=CurrentPageName();
	$users=new usersMenus();
	
$sock=new sockets();	
$ini=new Bs_IniHandler();
$configDisk=trim($sock->GET_INFO('ArticaAutoUpdateConfig'));	
$ini->loadString($configDisk);	
$AUTOUPDATE=$ini->_params["AUTOUPDATE"];	
$EnableNightlyInFrontEnd=$sock->GET_INFO("EnableNightlyInFrontEnd");
if($EnableNightlyInFrontEnd==null){$EnableNightlyInFrontEnd=1;}


	if(trim($AUTOUPDATE["uri"])==null){$AUTOUPDATE["uri"]="http://www.artica.fr/auto.update.php";}
	if(trim($AUTOUPDATE["enabled"])==null){$AUTOUPDATE["enabled"]="yes";}
	if(trim($AUTOUPDATE["autoinstall"])==null){$AUTOUPDATE["autoinstall"]="yes";}
	if(trim($AUTOUPDATE["CheckEveryMinutes"])==null){$AUTOUPDATE["CheckEveryMinutes"]="60";}
	if(trim($AUTOUPDATE["front_page_notify"])==null){$AUTOUPDATE["front_page_notify"]="yes";}
	if(trim($AUTOUPDATE["samba_notify"])==null){$AUTOUPDATE["samba_notify"]="no";}
	if(trim($AUTOUPDATE["auto_apt"])==null){$AUTOUPDATE["auto_apt"]="no";}

	$html="
	<input type='hidden' id='perform_update_text' value='{perform_update_text}'>
	<table style='width:100%'>
	<tr>
	<td valign='top'>
		<div class=explain>{autoupdate_text}</div>
	</td>
	<td valign='top' width=1%>
	". Paragraphe('64-recycle.png','{update_now}','{perform_update_text}',"javascript:auto_update_perform()")."</td>
	</tr>
	</table>
	";
	
	$form="
	<div id='ArticaUpdateForm'><form name='ffm1' >
	<table style='width:100%' class='table_form'>
	<tr>
	<td width=1% nowrap align='right' class=legend class=legend>{enable_autoupdate}:</strong></td>
	<td align='left'>" . Field_yesno_checkbox('enabled',$AUTOUPDATE["enabled"])."</td>
	</tr>
	<tr>
	<td width=1% nowrap align='right' class=legend>{enable_autoinstall}:</strong></td>
	<td align='left'>" . Field_yesno_checkbox('autoinstall',$AUTOUPDATE["autoinstall"])."</td>
	</tr>
	<tr>
	<td width=1% nowrap align='right' class=legend>{enable_nightlybuild}:</strong></td>
	<td align='left'>" . Field_yesno_checkbox('nightlybuild',$AUTOUPDATE["nightlybuild"])."</td>
	</tr>
	<tr>
	<td width=1% nowrap align='right' class=legend>{EnableNightlyInFrontEnd}:</strong></td>
	<td align='left'>" . Field_checkbox('EnableNightlyInFrontEnd',1,$EnableNightlyInFrontEnd)."</td>
	</tr>	
	<tr>
	<td width=1% nowrap align='right' class=legend>{front_page_notify}:</strong></td>
	<td align='left'>" . Field_yesno_checkbox('front_page_notify',$AUTOUPDATE["front_page_notify"])."</td>
	</tr>
	<tr>";
	if($users->SAMBA_INSTALLED){
	$form=$form."<td width=1% nowrap align='right' class=legend>{auto_apt}:</strong></td>
	<td align='left'>" . Field_yesno_checkbox('auto_apt',$AUTOUPDATE["auto_apt"])."</td>
	</tr>";
	}

	if(is_file("/usr/bin/apt-get")){
	$form=$form."<td width=1% nowrap align='right' class=legend>{samba_notify}:</strong></td>
	<td align='left'>" . Field_yesno_checkbox('samba_notify',$AUTOUPDATE["samba_notify"])."</td>
	</tr>";
	}
	
	$ip=new networking();
	
	while (list ($eth, $cip) = each ($ip->array_TCP) ){
		if($cip==null){continue;}
		$arrcp[$cip]=$cip;
	}
	
	$arrcp[null]="{default}";
	
	$WgetBindIpAddress=$sock->GET_INFO("WgetBindIpAddress");
	$WgetBindIpAddress=Field_array_Hash($arrcp,"WgetBindIpAddress",$WgetBindIpAddress,null,null,0,"font-size:13px;padding:3px;");
	
	$form=$form."
	<tr>
	<td width=1% nowrap align='right' class=legend>{WgetBindIpAddress}:</strong></td>
	<td align='left'>$WgetBindIpAddress</td>
	</tr>			
	<tr>
	<td width=1% nowrap align='right' class=legend>{CheckEveryMinutes}:</strong></td>
	<td align='left'>" . Field_text('CheckEveryMinutes',$AUTOUPDATE["CheckEveryMinutes"],'ont-size:13px;padding:3px;width:90px' )."</td>
	</tr>
	<tr>
	<td width=1% nowrap align='right' class=legend>{uri}:</strong></td>
	<td align='left'>" . Field_text('uri',$AUTOUPDATE["uri"],'ont-size:13px;padding:3px;width:100%' )."</td>
	</tr>	
	<tr>
	<td colspan=2 align='right'>
	<hr>
	". button("{edit}","SaveArticaUpdateForm()")."
	</tr>			
	</table>
	</form>
	</div>
	";
	

	
	$tpl=new templates();
	return $tpl->_ENGINE_parse_body($html.$form);
	
	
}

function main_artica_update_switch(){
	
	switch ($_GET["main_artica_update"]) {
		case "config":echo main_artica_update_config();exit;break;
		case "events":echo main_artica_update_events();exit;break;
		case "list":echo main_artica_update_updatelist();exit;break;
		default:echo main_artica_update_tabs();exit;break;
	}
	
}

function main_artica_update_tabs(){
	
	if(GET_CACHED(__FILE__,__FUNCTION__)){return;}
	
	$page=CurrentPageName();
	$array["config"]='{parameters}';
	$array["events"]='{events}';
	$tpl=new templates();
	
	while (list ($num, $ligne) = each ($array) ){
		$html[]= $tpl->_ENGINE_parse_body("<li><a href=\"$page?main_artica_update=$num\"><span>$ligne</span></a></li>\n");
	}
	
	
	$html= "
	<div id=main_config_artica_update style='width:100%;height:460px;overflow:auto'>
		<ul>". implode("\n",$html)."</ul>
	</div>
		<script>
				$(document).ready(function(){
					$('#main_config_artica_update').tabs({
				    load: function(event, ui) {
				        $('a', ui.panel).click(function() {
				            $(ui.panel).load(this.href);
				            return false;
				        });
				    }
				});
			
			
			});
		</script>";			

	SET_CACHED(__FILE__,__FUNCTION__,null,$html);
	echo $html;
}

function SaveConf(){
	writelogs("AUTOUPDATE -> SAVE",__FUNCTION__,__FILE__);
$sock=new sockets();	
$ini=new Bs_IniHandler();
$configDisk=trim($sock->GET_INFO('ArticaAutoUpdateConfig'));
$ini->loadString($configDisk);
	while (list ($num, $ligne) = each ($_GET) ){
		writelogs("AUTOUPDATE:: $num=$ligne",__FUNCTION__,__FILE__);
		$ini->_params["AUTOUPDATE"][$num]=$ligne;
	}
	
	$data=$ini->toString();
	$sock->SET_INFO("WgetBindIpAddress",$_GET["WgetBindIpAddress"]);
	$sock->SET_INFO("EnableNightlyInFrontEnd",$_GET["EnableNightlyInFrontEnd"]);
	$sock->SaveConfigFile($data,"ArticaAutoUpdateConfig");
	$sock->getFrameWork("cmd.php?ForceRefreshLeft=yes");
	$tpl=new templates();
		
}

function main_artica_update_events_display(){
	$table="<table style='width:100%'>";
	$file=$_GET["ajax-events"];
	
	$sock=new sockets();
	$tbl=unserialize(base64_decode($sock->getFrameWork("cmd.php?ReadArticaLogs=yes&file=$file")));
	$datenow=date("Y-m-d");
		
if(!is_array($tbl)){return null;}
	krsort($tbl);
while (list ($num, $ligne) = each ($tbl) ){
		if(trim($ligne)==null){continue;}
		  $color_blue=false;
		 if(preg_match('#curl --progress#i',$ligne)){continue;}
		  if(preg_match('#Couldn.+?t#i',$ligne)){$color=true;}
		  if(preg_match('#can.+?t#i',$ligne)){$color=true;}
		  if(preg_match('#didn\'t#i',$ligne)){$color=true;}
		  if(preg_match('#FATAL ERROR#i',$ligne)){$color=true;}
		  if(preg_match("#CheckAndInstall#",$ligne)){continue;}
		  if(preg_match('#nightly#i',$ligne)){$color_blue=true;}
		  
		  
		  
		  if($color){$colorw="color:red";}
		  if($color_blue){$colorw="color:blue";}
		  
		  if(preg_match("#Downloading new version#",$ligne)){
		  	$colorw="color:#005447;font-weight:bold;";
		  }
		  $ligne=str_replace($datenow,"",$ligne);
		  
		  $table=$table . "
		  <tr>
		  <td width=1% valign='top'><img src='img/fw_bold.gif'></td>
		  <td><code style='font-size:11px;$colorw'>$ligne</code></td>
		  </tr>";
	  	$color=false;
	  	$colorw=null;
	  }
	  
	 $table=$table . "</table>";
	 $table=RoundedLightWhite($table);

	 $html="<H1>{events}</H1>
	 <div style='width:100%;height:300px;overflow:auto'>
	 $table
	 </div>
	 
	 ";
	 
	 $tpl=new templates();
	 echo $tpl->_ENGINE_parse_body($html);
	 
	
}

function main_artica_update_events(){
	
	
	$sock=new sockets();
	$tbl=unserialize(base64_decode($sock->getFrameWork("cmd.php?QueryArticaLogs=yes")));
	
	
	$table="<table style='width:100%'>";
	
	
	while (list ($num, $ligne) = each ($tbl) ){
	  if(!preg_match('#artica-update-([0-9]+)-([0-9]+)-([0-9]+)-([0-9]+).debug#',$ligne,$re)){continue;}
	  $date="{$re[1]}-{$re[2]}-{$re[3]} {$re[4]}:00:00";
	  $tbl2["{$re[1]}{$re[2]}{$re[3]}{$re[4]}{$re[5]}00"]=array("DATE"=>$date,"FILE"=>$ligne);
	}
	  
	if(is_array($tbl2)){
	  krsort($tbl2);
	  $maintenant=date('Y-m-d H:00:00');
	  $today=date('Y-m-d');
	  while (list ($num, $ligne) = each ($tbl2) ){
	  		if($ligne["DATE"]==$maintenant){$color="color:red";$text="({today})";}else{$color=null;$text=null;}
	  		if(preg_match("#$today#",$ligne["DATE"])){$text="({today})";}else{$text=null;}
	  		
		  $table=$table . "<tr ". CellRollOver("ShowArticaUpdateEvents('{$ligne["FILE"]}')").">
		  <td width=1%><img src='img/fw_bold.gif'></td>
		  <td><code style='font-size:13px;$color'>{$ligne["DATE"]}</code>&nbsp;$text</td>
		  </tr>";
	  	}
	  }
	  
	 $table=$table . "</table>";
	 
	 
	 $html="<div style='width:100%;height:300px;overflow:auto'>$html$table</div>";
	 
	 $tpl=new templates();
	 $page="<H5>{events}</H5><br>$html";
	 return $tpl->_ENGINE_parse_body($page);
	}
	
function main_artica_update_updatelist(){
	$sock=new sockets();
	$datas=$sock->getfile('autoupdate_list');
	$tbl=explode("\n",$datas);
	
	if(!is_array($tbl)){
		if(strlen($datas)>0){$tbl[]=trim($datas);}
	}
	
	if(is_array($tbl)){
		krsort($tbl);
		$table="<table style='width:100%'>";
		while (list ($num, $ligne) = each ($tbl) ){
			if(trim($ligne)<>null){
			$table=$table.
			"<tr " . CellRollOver().">
			<td width=1%><img src='img/fw_bold.gif'></td>
			<td><strong>$ligne</strong></td>
			<td width=1%><input type='button' value='{install_package}' OnClick=\"javascript:ArticaUpdateInstallPackage('$ligne');\"></td>
			</tr>
			";}
			
		}
		$table=$table ."</table>";
		
	}
	
	 $table="<div style='width:100%;height:300px;overflow:auto'>$table</div>";
	
	$tpl=new templates();
	 $page=main_artica_update_tabs() . "
	 <input type='hidden' id='install_package_text' value='{install_package_text}'>
	 <br><H5>{update_list}</H5><br>$table";
	 return $tpl->_ENGINE_parse_body($page);	
	
	}
	
function ArticaUpdateInstallPackage(){
	$package=$_GET["ArticaUpdateInstallPackage"];
	$sock=new sockets();
	$sock->getfile('autoupdate_perform:'.$package);
	}
function auto_update_perform(){
	$sock=new sockets();
	$sock->getFrameWork('cmd.php?perform-autoupdate=yes');	
	}
	
?>	