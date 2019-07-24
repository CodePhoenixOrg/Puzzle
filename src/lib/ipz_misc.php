<?php   
/*
iPuzzle.WebPieces
Copyright (C) 2004 David Blanchard

This program is free software; you can redistribute it and/or
modify it under the terms of the GNU General Public License
as published by the Free Software Foundation; either version 2
of the License, or (at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program; if not, write to the Free Software
Foundation, Inc., 59 Temple Place - Suite 330, Boston, MA  02111-1307, USA.
*/

define("LINUX", "linux");
define("WIN32", "windows");
define("CONNECT", "connect");
define("DISCONNECT", "disconnect");
// define ('DEBUG_LOG_FILE', serverPath() . '/logs/debug.log');
// define("DOCUMENT_ROOT", getWwwRoot()."/");

// global $DEBUG_LOG_FILE;

// $DEBUG_LOG_FILE = serverPath() . '/logs/debug.log';

// function debugLog($message, $object = '') {
//     // global $DEBUG_LOG_FILE;

//     $handle = fopen($DEBUG_LOG, 'a');
    
//     $message = date('Y-m-d h:i:s') . " : $message"  . (isset($object) ? " : " . print_r($object, true) : "");
//     fwrite($handle, $message . PHP_EOL);
//     fclose($handle);
// }

function serverPath() {
    $result = '';
    
    $sa = explode('/', $_SERVER['SCRIPT_NAME']);
    $result = str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']) .'/'. $sa[1] ;
    
    return $result;
    
}


function escapeChars($phrase) {
        $result = '';
        
        $result = str_replace("'", "''", $phrase);
        
        return $result;
}

function jsQuote($code) {
	$js="<script language='JavaScript'>\n";
	$js.=$code;
	$js.="</script>\n";

	return $js;
}

function jsArray($name="", $array = []) {

	$js="var $name = [\"".implode("\", \"", $array)."\"];";
	
	return $js;
}
	
function jsAlert($msg) {
	$js="<script language=\"JavaScript\">alert(\"$msg\")</script>";

	return $js;
}

function getSqlDate() {
	$date=getdate();
	$mon="0".$date["mon"];
	$mon=substr($mon, strlen($mon)-2, 2);
	$mday="0".$date["mday"];
	$mday=substr($mday, strlen($mday)-2, 2);
	$sql_date= $date["year"]."-".$mon."-".$mday;
	return $sql_date;
}

function getFrenchDate() {
	$date=getdate();
	$mon="0".$date["mon"];
	$mon=substr($mon, strlen($mon)-2, 2);
	$mday="0".$date["mday"];
	$mday=substr($mday, strlen($mday)-2, 2);
	$french_date=$mday."/".$mon."/".$date["year"];
	return $french_date;
}

function getSqlTime() {
	$time=getdate();
	$hours="0".$time["hours"];
	$hours=substr($hours, strlen($hours)-2, 2);
	$minutes="0".$time["minutes"];
	$minutes=substr($minutes, strlen($minutes)-2, 2);
	$seconds="0".$time["seconds"];
	$seconds=substr($seconds, strlen($seconds)-2, 2);
	$time=$hours.":".$minutes.":".$seconds;
	return $time;
}

function getShortTime() {
	$time=getdate();
	$hours="0".$time["hours"];
	$hours=substr($hours, strlen($hours)-2, 2);
	$minutes="0".$time["minutes"];
	$minutes=substr($minutes, strlen($minutes)-2, 2);
	$time=$hours.":".$minutes;
	return $time;
}

function getDaysDelta($recent_date, $old_date) {
	$a_old_date=dateSqlToArray($old_date);
	$a_recent_date=dateSqlToArray($recent_date);
	
	$mm=$a_old_date["month"];
	$dd=$a_old_date["day"];
	$yy=$a_old_date["year"];
	$m_old_date=mktime(0,0,0,$mm,$dd,$yy);
	
	$mm=$a_recent_date["month"];
	$dd=$a_recent_date["day"];
	$yy=$a_recent_date["year"];
	$m_recent_date=mktime(0,0,0,$mm,$dd,$yy);
	
	$delta=round(($m_recent_date-$m_old_date)/86400);
	
	return $delta;
}
				
function dateMysqlToFrench($date) {
	if(strlen($date) > 10) $date = substr($date, 0, 10);
	$date = preg_replace('@^([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})$@', '\3/\2/\1', $date);

	return $date;
}

function dateFrenchToMysql($date) {
	$date = preg_replace('@^([0-9]{1,2})/([0-9]{1,2})/([0-9]{2,4})$@', '\3-\2-\1', $date);

	return $date;
}

function dateSqlToArray($date) {
	$yy = substr($date, 0, 4);
	$mm = substr($date, 5, 2);
	$dd = substr($date, 8, 2);

	return array("day"=>$dd, "month"=>$mm, "year"=>$yy);
}

function timeMysqlToShort($time) {
	if(strlen($time)==19) $time = substr($time, 11, 5);

	return $time;
}

function datetimeMysqlToFrench($datetime) {
	$datetime = preg_replace('^([0-9]{1,2})-([0-9]{1,2})-([0-9]{2,4}) ([0-9]{1,2}):([0-9]{1,2}):([0-9]{1,2})$', '\1/\2/\3 \4:\5', $datetime);

	return $datetime;
}

function datetimeUserToFrench($date, $time) {
	$date = dateMysqlToFrench($date);
	$time = timeMysqlToShort($time);
	$datetime = $date . " " . $time;

	return $datetime;
}

function isNum($var) {

	$isNum=true;
	$l=trim(strlen($var));
	$i=0;
	while(($i<$l) && $isNum===true) {
		$char=substr($var, $i, 1);
        	$asc=ord($char);
		//echo "var[$i]='".$char."'; ascii_code='".$asc."'<br>";
                $char_isNum=($asc>47 && $asc<58);
		$isNum=$isNum && $char_isNum;
		$i++;
	}
    return $isNum;
}

function escapeQuote($value)
{
    return str_replace('"', '\"', $value);
}

function filterPOST($arg)
{
	$result = filter_input(INPUT_POST, $arg, FILTER_SANITIZE_STRING);
	$result = html_entity_decode($result, ENT_QUOTES);
	
	return $result;
}

function getArgument($arg, $default = '')
{
    $result = '';

	// mysql_escape_string
    if (isset($_GET[$arg])) {
        $result = filter_input(INPUT_GET, $arg, FILTER_SANITIZE_STRING);
    } elseif (isset($_POST[$arg])) {
        $result = filter_input(INPUT_POST, $arg, FILTER_SANITIZE_STRING);
    }
    
    if ($result === '') {
        $result = $default;
    }
    return $result;
}

function getHttpRoot() {
	if($_SERVER["SERVER_PORT"]!='80')
		$http_root="http://".$_SERVER["SERVER_ADDR"].":".$_SERVER["SERVER_PORT"]."/";
	else
		$http_root="http://".$_SERVER["SERVER_NAME"];

	return $http_root;
}

function getWwwRoot() {
	/*
	$p=strpos($_SERVER["DOCUMENT_ROOT"], "/");
	if($p==2) 
		$system=WIN32;
	else
		$system=LINUX;

	if($system==LINUX)
		$wwwroot="/var/www";
	else
		$wwwroot="e:/www/html";
	*/
	//$wwwroot=$_SERVER["DOCUMENT_ROOT"]."/..";
	$wwwroot=$_SERVER["DOCUMENT_ROOT"];
	return $wwwroot;
}

function getCurrentDir() {
	$current_dir="";
	$phpself=$_SERVER["PHP_SELF"];
	
	$p3=strrpos($phpself, "/");
	$current_dir=substr($phpself, 0, $p3);
	
	return $current_dir;
}

function getCurrentHttpRoot() {
	$current_dir="";
	$phpself=$_SERVER["PHP_SELF"];
	
	$p3=strrpos($phpself, "/");
	$current_dir=substr($phpself, 0, $p3);
	
	$http_root=getHttpRoot();
	
	$current_http_root=$http_root.$current_dir;

	return $current_http_root;
}
	
function getCurrentWwwRoot() {

	$www_root=getWwwRoot();
	$current_dir=getCurrentDir();
	$files_dir="";

	$files_dir=$www_root.$current_dir;
		
	return $files_dir;
}

function getHttpImagesDir($directory="") {
	$images_dir=getHttpRoot()."img/";
	return $images_dir;
}

function getHttpFontsDir($directory="") {
	$fonts_dir=getHttpRoot()."fonts/";
	return $fonts_dir;
}

function getLocalImagesDir() {
	$images_dir=$_SERVER["DOCUMENT_ROOT"]."/$img/";
	return $images_dir;
}

function getLocalFontsDir() {
	$fonts_dir=$_SERVER["DOCUMENT_ROOT"]."/fonts/";
	return $fonts_dir;
}

function raiseError($message, $action, $on_click) {
	global $panel_colors;
	
	$on_click=trim($on_click);
	if($on_click!='') $on_click=" onClick=\"$on_click\"";
?>
<center>
<form method='POST' name='errorForm' action='<?php echo $action?>'>
<table border='1' bordercolor='<?php echo $panel_colors["border_color"]?>' cellpadding='0' cellspacing='0' witdh='100%' height='1'><tr><td align='center' valign='top' bgcolor='<?php echo $panel_colors["back_color"]?>'>
<table border="0" cellpadding="20">
    <tr>
      <td align="center">
        <?php   
	echo 'ERREUR<br>'.$message;
	?><br><br>
        <input type="submit" name="action" value="OK"<?php echo $on_click?>><br>
      </td>
    </tr>
</table>
</td></tr></table>
</form>
<center>
<?php   
}

function msgBox($message, $action, $on_click) {
	global $panel_colors;
	
	$on_click=trim($on_click);
	if($on_click!='') $on_click=" onClick=\"$on_click\"";
?>
<center>
<form method='POST' name='msgForm' action='<?php echo $action?>'>
<table border='1' bordercolor='<?php echo $panel_colors["border_color"]?>' cellpadding='0' cellspacing='0' witdh='100%' height='1'><tr><td align='center' valign='top' bgcolor='<?php echo $panel_colors["back_color"]?>'>
<table border="0" cellpadding="20">
    <tr>
      <td align="center">
        <?php   
	echo $message;
	?><br><br>
        <input type="submit" name="action" value="OK"<?php echo $on_click?>><br>
      </td>
    </tr>
</table>
</td></tr></table>
</form>
<center>
<?php   
}

define("PZ_DEFAULTS", getCurrentWwwRoot()."/pz_defaults.php");
