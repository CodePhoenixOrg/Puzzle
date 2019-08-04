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
namespace Puzzle;

class Controls extends Base {

	public $pz_current_tab;

	function create_tab_control($name="", $tab_captions=array(), $tab_ctrl="", $hidden="") {
		//Contrôle d'onglets
		
		// global $pz_current_tab;

		$id=getArgument("id");
		$lg=getArgument("lg");

		$tab_count=sizeof($tab_captions);
		
		if(empty($pz_current_tab)) $pz_current_tab=$tab_captions[0];
		
		$js="\t".jsArray($name."Captions", $tab_captions)."\n";
		$js.="\tvar currentTab=document.getElementById(\"$pz_current_tab\");\n";
		$js.="\tdisplay_tab(currentTab,".$name."Captions);\n";
		
		if(!isset($_SESSION["javascript"])) $_SESSION["javascript"]="";
		$_SESSION["javascript"].=$js;
		
		$labels="";
		$width=round(100/$tab_count);
		for($i=0; $i<$tab_count; $i++) {
			if($tab_captions[$i]!="&nbsp;") {
				$labels.="<td id='".$tab_captions[$i]."' align='center' width='$width%' onClick='return display_tab(this, ".$name."Captions);'><a href=\"#\">&nbsp;".$tab_captions[$i]."&nbsp;</a></td>\n";
			}
		}
		
		$result="";
		$result="<table valign='top' bgcolor='black' border='0' cellpadding='0' cellspacing='1' width='400' style='display:none'>\n";
		$result.="<tr bgcolor='lightgrey' height='10'>\n";
		$result.="$labels\n";
		$result.="</tr>\n";
		$result.="</table>\n";

		$result.="<table valign='top' bgcolor='black' border='0' cellpadding='0' cellspacing='1' width='400' height='400'>\n";
		$result.="<tr bgcolor='lightgrey'><td colspan='$tab_count'>\n";
		$result.="<form method='post' name='".$name."Form' action='page.html?id=$id&lg=$lg'>\n";

		$result.=$hidden;
		$result.=$tab_ctrl;
			
		$result.="</form>\n";
		$result.="</td></tr>\n";
		$result.="</table>\n";

		return array("tab_ctrl"=>$result);
	}

	function on_change_tab($tab_ctrl_name="", $tab_id=0) {
		$js="";
		$js.="var changeTab=document.getElementById(".$tab_ctrl_name."Captions[$tab_id]);";
		$js.="display_tab(changeTab,".$tab_ctrl_name."Captions);";
		
		return $js;
	}

	function create_server_file_selector($name="", $formname="", $basedir="", $filter="", $size=0, $dir_selector="", $onChange="") {
		// global $_SESSION;
		if(substr($basedir, -2) == "/.") $basedir = substr($basedir, 0, -1) ;
		if(substr($basedir, -1) != "/") $basedir.="/";
		if(substr($basedir, -3) == "../") {
			$basedir = substr($basedir, 0, -4);
			$p = strrpos($basedir, "/");
			$basedir = substr($basedir, 0, $p)."/";
		}

		$basedir = str_replace("/./", "/", $basedir);

		if($dir_selector!="") {
			$js="\t$name=eval(document.getElementById('$name'));\n";
			$js.="\t$dir_selector=eval(document.getElementById('$dir_selector'));\n";
			$js.="\tif($dir_selector && $name) {\n";
			$js.="\t\t$name.style.width=$dir_selector.offsetWidth+'px';\n";
			$js.="\t}\n";
			$js.="";
			
			if(!isset($_SESSION["javascript"])) $_SESSION["javascript"]="";
			$_SESSION["javascript"].=$js;
		}

		$filter=trim($filter);	
		if($filter!="") {
			$extensions=explode(",", $filter);

			for($i=0; $i<sizeof($extensions);$i++) {
				$extensions[$i]=".".trim($extensions[$i]);
			}
		}
		
		/*
		echo "<pre>";
		print_r($extensions);
		echo "</pre>";
		*/
		
		chdir(getWwwRoot().$basedir);
		
		$handle=opendir(getWwwRoot().$basedir);

		$result="";
		$result.="<select id='$name' name='$name' multiple size='$size' maxlength='15' onChange='document.$formname.filename.value=this.value;".$onChange."'>\n";
		$files=array();
		$i=0;
		while ($file = readdir($handle)) {
			if($filter!="" && !is_dir($file) && $file[0]!=".") {
				$file_extension=array();
				$file_extension[]=substr($file, 0, -4);
				$ext=array_intersect($file_extension, $extensions);
				if(isset($ext[0])) {
					$files[]=$file;
					$i++;
				}
			} elseif($filter=="" && !is_dir($file) && $file[0]!=".") {
				$files[]=$file;
				$i++;
			}
		}

		asort($files);
		foreach($files as $key=>$value) { 
			$option=$value;
			
			if($value==".") $option=$basedir;

			$result.="<option value='$basedir$option'>$option</option>\n";
		}
		closedir($handle);
		$result.="</select>\n";
		
		return $result;
	}

	function create_server_directory_selector($name="", $formname="", $basedir="", $onChange="") {
		if(substr($basedir, -2) == "/.") $basedir = str_replace("/.", "/", $basedir);
		if(substr($basedir, -1) != "/") $basedir.="/";
		if(substr($basedir, -3) == "../") {
			$basedir = substr($basedir, 0, -4);
			$p = strrpos($basedir, "/");
			$basedir = substr($basedir, 0, $p)."/";
		}

		$basedir = str_replace("/./", "/", $basedir);

		echo "basedir='".getWwwRoot().$basedir."'<br>";
		
		chdir(getWwwRoot().$basedir);
		
		$handle=opendir(getWwwRoot().$basedir);
		//document.$formname.basedir.value=this.value

		$result="";
		$result.="<select id='$name' name='$name' maxlength='15' onChange='".$onChange."document.$formname.submit()'>\n";
		$dirs=array();
		$i=0;
		while ($dir = readdir($handle)) {
			if(is_dir($dir)) {
				$dirs[]=$dir;
				$i++;
			}
		}

		asort($dirs);
		foreach($dirs as $key=>$value) { 
			$option=$value;
			
			if($value==".") $option=$basedir;
			if($value=="..") $option="(Répertoire parent)";
			
			if(!(($basedir=="/") && ($value==".."))) {
				//if((strlen($value)>1 && $value[0]!=".") || ($value=="..") || ($value==".")) 
					$result.="<option value='$basedir$value'>$option</option>\n";
			}
		}
		closedir($handle);
		$result.="</select>\n";
		
		return $result;
	}

	function create_file_upload_control($name="", $accept="", $size=0, $dir_selector="") {
		if($accept!="") $accept=" accept='$accept'";
		if($size!="") $size=" size='$size'";
		
		if($dir_selector!="") {
			$js="\t$name=eval(document.getElementById('$name'));\n";
			$js.="\t$dir_selector=eval(document.getElementById('$dir_selector'));\n";
			$js.="\tif($dir_selector && $name) {\n";
			$js.="\t\t$dir_selector.style.width=$name.offsetWidth+'px';\n";
			$js.="\t}\n";
			$js.="";
			
			$_SESSION["javascript"].=$js;
		}
		
		$result="<input id='$name' type='file' name='$name'$size$accept>\n";

		return $result;
	}

	function perform_file_upload($file_field_name="", $file_dir="") {

		// global $HTTP_POST_FILES;
		
		if(substr($file_dir, strlen($file_dir)-1, 1)!="/") $file_dir.="/";
		
		if(!empty($HTTP_POST_FILES[$file_field_name]) && $file_dir!="") { 
			$source = $HTTP_POST_FILES[$file_field_name]["tmp_name"];
			$destination = DOCUMENT_ROOT.$file_dir.$HTTP_POST_FILES[$file_field_name]["name"]; 
		
			//echo "copy ($source, $destination);<br>";
		
			$result=copy("$source", "$destination");
			
			if($result) {
				//echo("file uploaded");
				return true;
			} else {
				//echo ("error!");
				return false;
			}
		}
			
	}

	function create_dialog_window($url, $height, $width)
	{
		$dialog =
			"window.open(".
				"'$url', ".
				"'', ".
				"'width=". $width .", ".
				"height=". $height .", ".
				"menubar=no, ".
				"location=no, ".
				"resizable=no, ".
				"scrollbars=yes, ".
				"status=no, ".
				"toolbar=no'".
			")";
		return $dialog;
	}
}
