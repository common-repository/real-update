<?php
// Mit den folgenden Zeilen lassen sich
// alle Dateien in einem Verzeichnis auslesen
global $merk;
 
function name2name($a,$b,$pnf){
$a=strtolower(str_replace(" ","-",$a));
$b= substr(str_replace($pnf,"",$b),0,-1);

if($b!="")return $b;
return $a;

}

function run($pre,$pnf,$sub){
 if($sub==2)return;
global $merk;
	$handle=opendir ("$pre");
	while ($datei = readdir ($handle)) {
		if($datei!="." and  $datei!=".." ){
			if(is_dir($pre."/".$datei))run($pre."/".$datei,$pnf,$sub+1);
			elseif( $sub==1 and substr($datei,-4)==".php") {
				$f=fopen("$pre/$datei",'r'); 
				$data=fread($f,8000);
				fclose($f); 

				if(preg_match_all( '/(Plugin Name|Version):(\s|)(.*)$/mi', $data, $a)){
					if(count($a[1])==2){
				 if(substr($_SERVER['PHP_SELF'],-7)=="tmp.php")$p=substr($pre,2);
				 else $p=$pre;
					$u=name2name($a[3][0],"$p/",$pnf);
					$po=strrpos($u,"/");
					if($po!==false)$u=substr($u,$po+1);
						if($a[1][0]=="Plugin Name")$merk[$u]=array("n"=>str_replace(array(chr(13),chr(10)),array("",""),$a[3][0]),"v"=> preg_replace('/\s+/', '',$a[3][1]),"p"=>"$p/$datei","u"=>$u);
						else $merk[$u]=array("v"=> preg_replace('/\s+/', '',$a[3][0]),"n"=>str_replace(array(chr(13),chr(10)),array("",""),$a[3][1]),"p"=>"$p/$datei","u"=>$u);
					}
				}
				

			}
		}
	}
	 
	closedir($handle);
}
if($path=="")$path=".";

 if(substr($_SERVER['PHP_SELF'],-7)!="tmp.php"){
 	if(strpos(REALUPDATEHOME,"/real-update"))$path=substr(REALUPDATEHOME,0,strpos(REALUPDATEHOME,"/real-update"));
	else $path=REALUPDATEHOME;
 }
run($path,"/server/www/www.sorben.org/wp-content/plugins/",0);


 
 if(substr($_SERVER['PHP_SELF'],-7)=="tmp.php")echo serialize($merk);
?>