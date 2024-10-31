<?php

/*
Plugin Name: Real Update
Description: Updates your Wordpress and Plugins
Plugin URI:  http://www.phpwelt.net/real-update/
Version:     0.3
Author:      Erik Sefkow
Author URI:  http://blog.phpwelt.net/

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

*/
 if (!defined("WP_CONTENT_URL")) 	define("WP_CONTENT_URL", get_option("siteurl") . "/wp-content");
 if (!defined("WP_CONTENT_DIR")) 	define("WP_CONTENT_DIR", ABSPATH . "wp-content");
 

 if (!defined("WP_INCLUDES_DIR")) 	define("WP_INCLUDES_DIR", ABSPATH . "wp-includes");
 
 if( !defined( 'REALUPDATEHOME' ) )
	define('REALUPDATEHOME', dirname(__FILE__).'/');
	

	
if (preg_match ( '#' . basename ( __FILE__ ) . '#', $_SERVER ['PHP_SELF'] )) {
	die ( 'Get it here: <a title="real update wordpress plugin" href="http://www.phpwelt.net/real-update">real update wordpress plugin</a>' );
}
$currentLocale = get_locale ();

if (! empty ( $currentLocale )) {
	$moFile = dirname ( __FILE__ ) . "/lang/" . $currentLocale . ".mo";
	if (@file_exists ( $moFile ) && is_readable ( $moFile ))
		load_textdomain ( 'real-update', $moFile );
			define('RE_LANG',$currentLocale);
			 
}

#$plugins = get_plugins();


add_action ( 'admin_menu', 'touchit_admin_menu3', 22 );
function touchit_admin_menu3() {
	// Add admin page to the Options Tab of the admin section
	

	if (function_exists ( 'add_submenu_page' ))
		add_submenu_page ( 'options-general.php', 'Real Updater', 'Real Updater', 10, __FILE__, 'touchit_plugin_options3' );
	else
		add_options_page ( 'Real Updater', 'Real Updater', 8, __FILE__, 'touchit_plugin_options3' );
	// Check if the options exists on the database and add them if not


}
/**
 * @return void
 */
 function getcurrentversion($name,$typ=0){
 $a= rudb ( "pluginversion" );
 if($typ==0 and isset($a[$name]))return $a[$name];
 
 $url="http://wordpress.org/extend/plugins/$name/";
if (function_exists ( 'file_get_contents' )  and ini_get('allow_url_fopen')==1) {
	$content = @file_get_contents ( $url );
} else {
	$curl = curl_init ( $url );
	curl_setopt ( $curl, CURLOPT_HEADER, 0 );
	curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
	$content = curl_exec ( $curl );
	curl_close ( $curl );
}
preg_match_all( "/<li><strong>Version\:<\/strong> ([0-9.]*)<\/li>/mi", $content, $lo);
  $a[$name]=$lo[1][0];
  if($a[$name]=="")$a[$name]="error";
update_option ( "ru-pluginversion", $a );
return $a[$name];
 
 }
 function getdatacacherefresh($id){
 $a=rudb("allplugins");
 $a[$id]=getdata($id);
 	update_option ( "ru-allplugins", $a );
 }
 function getdata($id){

 if($id==-1) { require(REALUPDATEHOME."tmp.php"); 
global $wp_version;
$merk["ver"]=$wp_version;
 return $merk;}
 else{
 $a= rudb ( "ftp" );
 $v=$a[$id];
 
 if($v[t]==1)require_once ("php/ftp.php");
 elseif($v[t]==2)require_once ("php/sftp.php");

 if($v[t]==1)rs_connect ( $v [s], $v [u], $v [pw], $v [po] );
 elseif($v[t]==2)rs_connect2 ( $v [s], $v [u], $v [pw], $v [po] );
 
$out=  file_get_contents(REALUPDATEHOME."tmp.php");

 if($v[t]==1)rs_writecontent ( $v [pa] . "wp-content/plugins/tmp.php", $out );
 elseif($v[t]==2)rs_writecontent2 ( $v [pa] . "wp-content/plugins/tmp.php", $out );
$url = $v [url] . "wp-content/plugins/tmp.php";

if (function_exists ( 'file_get_contents' )  and ini_get('allow_url_fopen')==1) {
	$content = @file_get_contents ( $url );
} else {
	$curl = curl_init ( $url );
	curl_setopt ( $curl, CURLOPT_HEADER, 0 );
	curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
	$content = curl_exec ( $curl );
	curl_close ( $curl );
}
#echo $content."<hr>"; 
$a = unserialize ( $content );

 if($v[t]==1)$data=rs_readfile ($v [pa] . "wp-includes/version.php");
 elseif($v[t]==2)$data=rs_readfile2 ($v [pa] . "wp-includes/version.php");
preg_match_all( "/wp_version = \'(.*)\';$/mi", $data, $lo);
$a["ver"]=$lo[1][0];


 #if($v[t]==1)rs_deletefile ( $v [pa] . "wp-content/plugins/tmp.php" );
 #elseif($v[t]==2)rs_deletefile2 ( $v [pa] . "wp-content/plugins/tmp.php" );
 if($v[t]==1)rs_disconnect ();
 elseif($v[t]==2)rs_disconnect2 ();
return $a;
 
 }
 }
 /**
 * $a aktuelle verion online bei wordpress.org
 * $b verson auf system
 * return true wenn update notwendig
 */
 function testvers($a,$b){
 if($b=="")return -1;
 if($a==$b)return false;
 else $t=compareVersion( $a, $b );
 if($t==1)
  return true;
 }
 /*
 * 1=ist aktuell ?
 * 2=nachste version
 */
 function compareVersion( $version1, $version2 ){ 
    
    $match1 = explode( '.', $version1 ); 
     
    $match2 = explode( '.', $version2 ); 
   #$a=  count($match1);
   #$b=  count($match2);
	#if($a>2 or $b>2) {
	
	#}
	 
    $int1 = sprintf( '%d%02d%02d%02d%02d', $match1[0], $match1[1], intval( $match1[2] ), intval( $match1[3] ), intval( $match1[4] ) );
     
    $int2 = sprintf( '%d%02d%02d%02d%02d', $match2[0], $match2[1], intval( $match2[2] ), intval( $match2[3] ), intval( $match2[4] ) );
     
    $result = 0; 
     
    if ( $int1 < $int2 ) { 

        $result = -1; 
         
    } 
     
    if ( $int1 > $int2 ) { 

        $result = 1; 
         
    } 
  #    echo "$version1, $version2 $int1 $int2 $result<hr>";
    return $result; 
     
} 
/**
*typ==1 ist akktuelle version?
*	2 nächste version
*
*/
 function wordpressversion($typ,$ver){

 
 $v[]="2.5.1";
 $v[]="2.6.0";
 $v[]="2.6.1";
 $v[]="2.6.2";
 $v[]="2.6.3";
 $v[]="2.6.5";
 $v[]="2.7.0";
 $v[]="2.7.1";
 $v[]="2.8.0";
 $v[]="2.8.1";
 $v[]="2.8.2";
 $v[]="2.8.3";
 $v[]="2.8.4";
 $v[]="2.8.5";
 $v[]="2.8.6";
 $v[]="2.9.0";
 $v[]="2.9.1";
 $v[]="2.9.2";
 

 if($typ==1){
 #echo $ver.$v[count($v)-1]."<hr>";


	if(compareVersion( $v[count($v)-1], $ver ) ==1)return false;
	else return true;
 }elseif($typ==2){

 if(strlen($ver)<4)$ver=$ver.".0";
 $v = array_flip($v);
 $ver=$v[$ver]+1;
 $v = array_flip($v);
 
 
 return $v[$ver];
 }
 
 }
function touchit_plugin_options3() {



	echo "<h1>Real Update - Alphaversion</h1>";

#getcurrentversion("google-sitemap-generator",1);
#update_option ( "ru-allplugins",array());
#getdatacacherefresh(-1);
#getdatacacherefresh(0);
#getdatacacherefresh(2);



  
 
 
	if (count ( $_POST ) > 0) {
	$s=rudb ( "secure" );
	#if($s[$_POST["secureid"]]!=$_POST["secure"] or $_POST["secure"]=="" or $_POST["secureid"]<0 or $_POST["secureid"]>9)die(__("secureerror"));
	if($_POST["updateplugincache"]){
	 $a=rudb("allplugins");
	  foreach($a as $v){
  
  foreach($v as $vv){
  if(is_array($vv)){
  if(!isset($anz[$vv[n]])){
  $anz[$vv[n]]++;
 # print_R( $vv[u]);
 # exit;
  getcurrentversion($vv[u],1);
  }
  }
  }
  }
   echo "Done";
	}elseif($_POST["stepbystep"]){
	echo "UPDATING STEP";
	$ok = update ( 1, "", "work","",$_POST[id]."," );
	}elseif($_POST["latest"]){
	echo "UPDATING COMP";
	$ok = update ( 2, "", "work","",$_POST[id]."," );
	}elseif($_POST["refreshusedplugins"]){
		getdatacacherefresh($_POST[id]);
	}elseif ($_POST ["typ"] == "plugin") {
		$indi=array_flip($_POST["indi"]);
		$iii="";
		if(isset($indi[-1]))$iii="";
		else foreach(array_flip($indi) as $v)$iii.=$v.",";

		
				$ok = update ( "http://downloads.wordpress.org/plugin/" . $_POST ["pluginname"] . ".zip", "wp-content/plugins/" . $_POST ["pluginname"] . "/", "work" ,$_POST ["pluginname"],$iii);
			if ($ok === false)
				echo "FEHLER234";
		} elseif ($_POST ["typ"] == "plugin2") {
			
			if (strpos ( $_POST ["pluginname"], "://" ) !== false) {
				$a = pathinfo ( $_POST ["pluginname"] );
				$a = $a [filename];
				$ok = update ( $_POST ["pluginname"], "wp-content/plugins/" . $a . "/", "work" );
			} else
			$hh=str_replace(" ","-",strtolower($_POST ["pluginname"]));
				$ok = update ( "http://downloads.wordpress.org/plugin/" . $hh . ".zip", "wp-content/plugins/" . $hh . "/", "work" );
			if ($ok === false)
				echo "FEHLER324";
				else{
				if(strpos ( $_POST ["pluginname"], "://" ) === false){
				$t=rudb("plugins");
				$t[]=$hh;
				update_option ( "ru-plugins",$t);
				}
				}
		} elseif ($_POST ["typ"] == "wordpress") {
			$ok = update ( $_POST ["url"], "", "work" );
		} elseif ($_POST ["typ"] == "login") {
			 
			$a = rudb ( "ftp" );
	
			#$a=array();
			if ($_POST ["id"] == - 1){
 
				$a [] = array ("n" => $_POST ["servername"],"s" => $_POST ["server"], "u" => $_POST ["user"], "pw" => $_POST ["pass"], "po" => $_POST ["port"], "pa" => $_POST ["path"], "url" => $_POST ["url"], "inc" => $_POST ["inc"], "t" => $_POST ["typp"] );
				end($a);
#print_R($a);
				update_option ( "ru-ftp", $a );
				
				getdatacacherefresh(key($a));
			}else {
				if ($_POST [Loesch] != "")
					unset ( $a [$_POST ["id"]] );
				else
					$a [$_POST ["id"]] = array ("n" => $_POST ["servername"],"s" => $_POST ["server"], "u" => $_POST ["user"], "pw" => $_POST ["pass"], "po" => $_POST ["port"], "pa" => $_POST ["path"], "url" => $_POST ["url"], "inc" => $_POST ["inc"] , "t" => $_POST ["typp"]);
					update_option ( "ru-ftp", $a );
			}
			
			
		
		}
	}
	##########
update_option ( "ru-secureid",(rudb ( "secureid" )+1)%10 );
$o=rudb ( "secure" );
srand(time());
 
$o[rudb ( "secureid" )]=(rand()%10000);
update_option ( "ru-secure",$o);

$o='<input type="hidden" name="secureid" value="' . rudb ( "secureid" ) . '"/><input type="hidden" name="secure" value="' . $o[rudb ( "secureid" )] . '"/>';

###################
  $a=rudb("allplugins");

 ###################################################################
 #########################################################
 #####################################################
#$ok = update ( 2, "", "work","","5," );


 if(count($a)>0){
 
  foreach($a as $v){
  
  foreach($v as $vv){
  if(is_array($vv)){
  if(!isset($anz[$vv[n]]))$m.= $vv[n]."|";
  $anz[$vv[n]]++;
  $u[$vv[n]]=$vv[u];
  }else $vers[$vv]++;
  }
  }
  ksort($anz);
 
#
   
  
		echo __ ( "Inside this wordpress are the following Plugins aktivatet:" );
		echo "<table border='1' width='100%'>";
		echo "<th>name</th>";
		echo "<th>&nbsp;</th>";
		echo "<th>all</th>";
		foreach ( rudb ( "ftp" ) as $k=>$v ) {
		if($v[n]=="")echo "<th>".($k+1)."</th>";
		else echo "<th>".$v[n]."</th>";
		}
		echo "<th></th>";
		echo '<th align="left">version</th>';
		foreach ( $anz as $k=>$v ) {
		   $uu='<td align="center"><input type="checkbox" name="indi[]" value="-1"></td>';
			if(getcurrentversion($u[$k])=="error")$uu="<td>&nbsp;</td>";
			foreach(rudb ( "ftp" ) as $lk=>$lv){
					$ttt=testvers(getcurrentversion($u[$k]),$a[$lk][$u[$k]][v]);
					if($ttt===true)$chc="checked ";
					else $chc="";
					if($ttt<0)$uu.="<td>&nbsp</td>";
					elseif(getcurrentversion($u[$k])=="error")$uu.='<td align="center">o</td>';
					else $uu.='<td align="center"><input type="checkbox" name="indi[]" '.$chc.'value="'.$lk.'"></td>';
			}
			echo '<tr><td><form method="post" id="rstatic_option-form">'.$o.'<input type="hidden" name="typ" value="plugin"/><input type="hidden" name="pluginname" value="'.$u[$k].'"/><input type="hidden" name="folder" value="' . $k . '"/>'.$v."x ".$k.'</td><td><input type="submit" value="' . __ ( "update" ) . '"></td>'.$uu.'<td></form></td><td>'.getcurrentversion($u[$k]).'</td></tr>';
		}
		
		
		echo "<td>&nbsp;</td>";
		echo "<td>&nbsp;</td>";
		echo "<td>&nbsp;</td>";
		foreach ( rudb ( "ftp" ) as $k=>$v ) echo '<td align="center"><form method="post" id="rstatic_option-form"><input type="hidden" name="id" value="'.$k.'"/><input type="submit" name="refreshusedplugins" value="' . __ ( "refresh" ) . '"></form></td>';
		echo "<td></td>";
		echo '<td align="left">&nbsp;</td>';	
		
echo "</table>";

 



/*
	if (is_array ( rudb ( "ftp" ) )) {
		$active = rudb("plugins");
		$active = get_option ( 'active_plugins' );
		echo __ ( "Inside this wordpress are the following Plugins aktivatet:" );
		foreach ( $active as $v ) {
			$a = substr ( $v, 0, strpos ( $v, "/" ) );
			echo '<form method="post" id="rstatic_option-form">'.$o.'<input type="hidden" name="typ" value="plugin"/><input type="hidden" name="folder" value="' . $a . '"/><input disabled="disabled" name="pluginname" type="text" size="60" value="' . $a . '"><input type="submit" value="' . __ ( "update" ) . '"></form>';
		}
		echo __ ("Type the name of the plugin or insert the downloadlink of the zipfile");
		echo '<br><form method="post" id="rstatic_option-form">'.$o.'<input type="hidden" name="typ" value="plugin2"/><input name="pluginname" type="text" size="60" value="' . '"><input type="submit" value="' . __ ( "update" ) . '"></form>';
		echo __ ("Upgrade wordpresscore");
		$url["de_DE"] = "http://static.wordpress-deutschland.org/upgradepaket/fix-291-to-292.zip";
		$url=$url[get_locale ()];
		if($url=="")$url="http://wordpress.org/latest.zip";
		echo '<form method="post" id="rstatic_option-form">'.$o.'<input type="hidden" name="typ" value="wordpress"/><input name="url" type="text" size="60" value="' . $url . '"><input type="submit" value="' . __ ( "update wordpress" ) . '"></form>';
	} else
		echo __ ( "Please at least one FTP-accout" );
		*/
		
		
	echo "<hr>";
	  $a=rudb("allplugins");
echo '<form method="post" id="rstatic_option-form"><input type="submit" name="updateplugincache" value="' . __ ( "refresh Pluginversioncache" ) . '"></form>';
 

	  echo "<hr>";
	  }
	if (is_array ( rudb ( "ftp" ) )) {

	echo __ ( "Updates/Upgrades are made on following locations" );
		foreach ( rudb ( "ftp" ) as $k => $v ) {
			
			echo '<form method="post" id="rstatic_option-form">'.$o.'<input type="hidden" name="typ" value="login"/><input type="hidden" name="id" value="' . $k . '"/>
' . __ ( "Wordpressversion" ) . ': '.$a[$k][ver];
if(wordpressversion(1,$a[$k][ver])===false ){
 if( wordpressurl($a[$k][ver],1)!= wordpressurl($a[$k][ver],2))
echo '<input type="submit" name="stepbystep" value="' . __ ( "update step by step" ) . '"> ';
echo '<input name="latest" type="submit" value="' . __ ( "Upgrade to latest Wordpress version" ) . '">';
}
echo '<br>
' . __ ( "servername" ) . ': <input name="servername" type="text" size="60" value="' . $v [n] . '"><br>
' . __ ( "serverip" ) . ': <input name="server" type="text" size="60" value="' . $v [s] . '"><br>
' . __ ( "username" ) . ': <input name="user" type="text" size="60" value="' . $v [u] . '"><br>
' . __ ( "password" ) . ': <input name="pass" type="password" size="60" value="' . $v [pw] . '"><br>
' . __ ( "port" ) . ': <input name="port" type="text" size="60" value="' . $v [po] . '"><br>
' . __ ( "path" ) . ': <input name="path" type="text" size="60" value="' . $v [pa] . '"><br>
' . __ ( "wordpressinclude" ) . ': <input name="inc" type="text" size="60" value="' . $v [inc] . '"><br>
' . __ ( "wordpressurl" ) . ': <input name="url" type="text" size="60" value="' . $v [url] . '"><br>
' . __ ( "typ" ) . ': <select name="typp" size="1">
      <option ';
if($v [t]== 2)echo " selected ";	  
echo '  value="2">SFTP</option>
      <option ';
if($v [t]== 1)echo " selected ";	  
echo ' value="1">FTP</option>
 </select><br>

<input type="submit" value="' . __ ( "save" ) . '">  <input type="submit" value="' . __ ( "delete" ) . '" name="Loesch"></form><hr>';
		
		}
	
	}
	echo __ ( "Type in a new updatelocation" );
	echo '<b>NEW</b>:<br><form method="post" id="rstatic_option-form">'.$o.'<input type="hidden" name="typ" value="login"/><input type="hidden" name="id" value="-1"/>
' . __ ( "servername" ) . ': <input name="servername" type="text" size="60" value=""><br>
' . __ ( "serverip" ) . ': <input name="server" type="text" size="60" value=""><br>
' . __ ( "username" ) . ': <input name="user" type="text" size="60" value=""><br>
' . __ ( "password" ) . ': <input name="pass" type="password" size="60" value=""><br>
' . __ ( "port" ) . ': <input name="port" type="text" size="60" value="21"><br>
' . __ ( "path" ) . ': <input name="path" type="text" size="60" value="/">'.__("location of wordpress root-dir (where wp-admin, wp-conent e.g. folder is)").'<br>
' . __ ( "wordpressinclude" ) . ': <input name="inc" type="text" size="60" value="wp-includes/"><br>
' . __ ( "wordpressurl" ) . ': <input name="url" type="text" size="60" value=""><br>
' . __ ( "typ" ) . ': <select name="typp" size="1">
      <option value="2">SFTP</option>
      <option value="1">FTP</option>
 </select><br>
<input type="submit" value="' . __ ( "save" ) . '"></form>';
#print_R($anz);
if(count($anz)>0){
rsort($anz);
foreach($anz as $v){$c.="$v,";}
 echo '<img src="http://chart.apis.google.com/chart?cht=p3&chd=t:'.substr($c,0,-1).'&chds=0,169&chs=500x600&chdl='.substr($m,0,-1).'&chma=10,0,0,0|70&chco=3366CC|DC3912|FF9900|109618|336611|DC39CC|FF99DD|10CC18|FFC6A5|FFFF42|DEF3BD|00A5C6|DEBDDE|c0c0c0|cec00e|123456|abcdef|f1f1f1|FF0000|00FF00|0000FF&chp=4.7">';
 }
 echo wordpressurl($allplugins[0][ver],1);
 echo wordpressurl($allplugins[0][ver],2);
}
function wordpressurl($vers,$typ){

if(RE_LANG=="de_DE" and $typ==2) return "http://counter.wordpress-deutschland.org/dlcount.php?id=static&url=/de-edition/latest.zip";
if(RE_LANG=="de_DE" and $typ==1) return "http://static.wordpress-deutschland.org/upgradepaket/fix-".str_replace(".","",$vers)."-to-".str_replace(".","",wordpressversion(2,$vers)).".zip";

if($typ==2 or $typ==1 )return "http://wordpress.org/latest.zip";
#http://static.wordpress-deutschland.org/upgradepaket/fix-291-to-292.zip
#

}
function update($url, $path, $work,$pname,$o="") {
 
$urler=$url;$allplugins=get_option("ru-allplugins");
if(is_int($url)){
	
 
	$url=wordpressurl($allplugins[substr($o,0,-1)][ver],$url);
#$allplugins[substr($o,0,-1)][ver]=wordpressversion(2,$allplugins[substr($o,0,-1)][ver]);
 
}
 
	if($o!="")$o=",$o";
	if (function_exists ( 'file_get_contents' )) {
		$content = @file_get_contents ( $url );
	} else {
		$curl = curl_init ( $url );
		curl_setopt ( $curl, CURLOPT_HEADER, 0 );
		curl_setopt ( $curl, CURLOPT_RETURNTRANSFER, true );
		$content = curl_exec ( $curl );
		curl_close ( $curl );
	}
 
	if ($content === false)
		return false;
	$f = fopen ( "temp.zip", 'w' );
	fwrite ( $f, $content, strlen ( $content ) );
	fclose ( $f );
	unset($content);
	require_once ('php/pclzip.lib.php');
	$archive = new PclZip ( 'temp.zip' );
	$list = $archive->extract ( PCLZIP_OPT_PATH, REALUPDATEHOME.$work, PCLZIP_OPT_REPLACE_NEWER );
 	unset ( $list [0] );
	
	$a = strlen ( REALUPDATEHOME.$work ) + 1;
	$a = strpos ( $list [1] [filename], "/", $a ) + 1;
	#echo "<pre>";
	foreach ( $list as $k => $v ) {
	$ex=pathinfo ($v [filename]);
		if (substr ( $v [filename], - 1 ) != "/")
			$do [] = array ("r" => substr ( $v [filename], $a ), "a" => $v [filename] ,"e"=>$ex[extension]);

	}
 
	#updaten

	
	$a = rudb ( "ftp" );

	# $allplugins= rudb ( "allplugins" );
 
	foreach ( $a as $K=> $v ) {
	#echo "|||$K,$o|||";
	if($o=="" ||strpos($o,",".$K.",")!==false){

if($v[t]==1)require_once ("php/ftp.php");
 elseif($v[t]==2)require_once ("php/sftp.php");
 
 if($v[t]==1)rs_connect ( $v [s], $v [u], $v [pw], $v [po] );
 elseif($v[t]==2)rs_connect2 ( $v [s], $v [u], $v [pw], $v [po] );
		#echo "updating: $v[s] : $v[u] :  $v[pa]<br>";
		

		foreach ( $do as $v2 ) {
		#echo "!";
ob_flush();
		flush();
			#	echo $v [s] . ", $v[u], $v[pw],$v[po]   $v[pa].$path.$v2[r],$v2[a]<br>";
			if($v[t]==1)rs_writefile ( $v [pa] . $path . $v2 [r], $v2 [a] );
			 elseif($v[t]==2)rs_writefile2 ( $v [pa] . $path . $v2 [r], $v2 [a] );
			  if(($v2[r]=="wp-includes/version.php") and (is_int($urler))){
			  $f=fopen( $v2 [a],'r'); 
				$data=fread($f,8000);
				fclose($f); 
				
					preg_match_all( "/wp_version = \'(.*)\';$/mi", $data, $lo);
						$allplugins[substr($o,1,-1)][ver]=$lo[1][0];
			  }elseif(($v2[e]=="php" and $version=="") and (!is_int($urler))){
				$f=fopen( $v2 [a],'r'); 
				$data=fread($f,8000);
				fclose($f); 
				if(preg_match_all( '/(Plugin Name|Version):(\s|)(.*)$/mi', $data, $aa) and count($aa[1])>1){
				if($aa[1][0]=="Plugin Name")$version=$aa[3][1];
				else $version=$aa[3][0];
				}
			 }
		}
		if($v[t]==1)rs_disconnect ();
		elseif($v[t]==2)rs_disconnect2 ();
		if(!is_int($urler))$allplugins[$K][$pname][v]=$version;
		#echo "[ $K ][ $pname ]";
		
	#	print_r($allplugins);
		
		}
		
		#exit;
		#exit;
		#$a[$K][ver]
		
	}
	//Tempordner sauber
	foreach ( $do as $v ) {
		unlink ( $v [a] );
	}
	
	unlink ( "temp.zip" );
	if(!is_int($urler)){
		 $a= rudb ( "pluginversion" );
		 $a[$pname]=$version;
		update_option("ru-pluginversion",$a);
		update_option("ru-allplugins",$allplugins);
		}else update_option("ru-allplugins",$allplugins);
}

function rudb($name) {
	$pre = "ru-";
	if (get_option ( $pre . $name ) === false) {
		if ($name == "ftp")
			add_option ( $pre . $name, array (), '', 'yes' );		
			if ($name == "pluginversion")
			add_option ( $pre . $name, array (), '', 'yes' );
			if ($name == "allplugins")
			add_option ( $pre . $name, array (), '', 'yes' );
		if ($name == "work")
			add_option ( $pre . $name, array (), '', 'yes' );
	if ($name == "secureid")
			add_option ( $pre . $name, 0, '', 'yes' );
if ($name == "secure")
			add_option ( $pre . $name, array (), '', 'yes' );
if ($name == "version")
			add_option ( $pre . $name, "0.200", '', 'yes' );
			if ($name == "plugins")
			add_option ( $pre . $name, get_option ( 'active_plugins' ), '', 'yes' );
	}
	
	return get_option ( $pre . $name );
}

?>
