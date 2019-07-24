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

/*
include_once("ipz_misc.php");
include_once("ipz_mysqlconn.php");
*/
/*
function getImageSize($directory, $image) {
		echo "$directory$image";
	if(substr($image, 0, 1)=="#") {
		$p1=strpos($image, "h");
		$p2=strpos($image, ";", $p1);
		$height=substr($image, $p1, $p2-$p1);
		$p1=strpos($image, "w");
		$p2=strpos($image, ";", $p1);
		$width=substr($image, $p1, $p2-$p1);
		if($height==0) $height=$width;
	} elseif {
		list($width, $height)=getimagesize($directory.$image);
	}
	
	return array("width"=>$width, "height"=>$height);
}
*/
function getParameters($this_profile="") {

	global $profile, $skin_model, $apply_skin, $page_colors, $panel_colors, $diary_colors, $grid_colors;
	
	if(session_is_registered("ses_profile")) {
		$profile=$_SESSION["ses_profile"];
	} else
		$profile="";

	if($profile!=$this_profile) {
		if(!session_is_registered("ses_profile")) {
			session_register("ses_profile");
			session_register("ses_page_colors");
			session_register("ses_panel_colors");
			session_register("ses_diary_colors");
			session_register("ses_grid_colors");
		}
		
		global $database, $img;
		$hostname=getHttpRoot()."/";
		$cs=connection(CONNECT, $database);

		//echo "DATABASE='$database'<br>";
	
		//echo jsAlert("Je prends les paramètres dans la base $database avec le profil $this_profile.");
		
		$sql="select p.*, s.* from parameters p, skins s where p.sk_id=s.sk_id and p.par_profile='$this_profile'";
		$stmt = $cs->query($sql);
		$rows = $stmt->fetch(PDO::FETCH_ASSOC);
		$par_id=$rows["par_id"];
		$par_profile=$rows["par_profile"];
		$par_page_back_color=$rows["par_page_back_color"];
		$par_page_text_color=$rows["par_page_text_color"];
		$par_page_link_color=$rows["par_page_link_color"];
		$par_page_alink_color=$rows["par_page_alink_color"];
		$par_page_vlink_color=$rows["par_page_vlink_color"];
		$par_titlebar_back_color=$rows["par_titlebar_back_color"];
		$par_titlebar_text_color=$rows["par_titlebar_text_color"];
		$par_object_border_color=$rows["par_object_border_color"];
		$par_object_back_color=$rows["par_object_back_color"];
		$par_object_text_color=$rows["par_object_text_color"];
		$par_highlight_back_color=$rows["par_highlight_back_color"];
		$par_highlight_text_color=$rows["par_highlight_text_color"];
		$par_grid_even_back_color=$rows["par_grid_even_back_color"];
		$par_grid_even_text_color=$rows["par_grid_even_text_color"];
		$par_grid_odd_back_color=$rows["par_grid_odd_back_color"];
		$par_grid_odd_text_color=$rows["par_grid_odd_text_color"];
		$sk_theme=$rows["sk_theme"];
		$sk_top_left_pic=$rows["sk_top_left_pic"];
		$sk_top_pic=$rows["sk_top_pic"];
		$sk_top_right_pic=$rows["sk_top_right_pic"];
		$sk_right_pic=$rows["sk_right_pic"];
		$sk_bottom_right_pic=$rows["sk_bottom_right_pic"];
		$sk_bottom_pic=$rows["sk_bottom_pic"];
		$sk_bottom_left_pic=$rows["sk_bottom_left_pic"];
		$sk_left_pic=$rows["sk_left_pic"];	
		$par_apply_skin=$rows["par_apply_skin"];
		
		$source=$hostname."/".$img."/skin/".$sk_theme."/";
		
		/*
		$size_top_left_pic=getImageSize($source, $sk_top_left_pic);
		$size_top_pic=getImageSize($source, $sk_top_pic);
		$size_top_right_pic=getImageSize($source, $sk_top_right_pic);
		$size_right_pic=getImageSize($source, $sk_right_pic);
		$size_bottom_right_pic=getImageSize($source, $sk_bottom_right_pic);
		$size_bottom_pic=getImageSize($source, $sk_bottom_pic);
		$size_bottom_left_pic=getImageSize($source, $sk_bottom_left_pic);
		$size_left_pic=getImageSize($source, $sk_left_pic);
		*/
		
		//$min_size=min($size1["height"], $size2["height"], $size1["width"], $size2["whidth"]);
		//$max_size=max($size1["height"], $size2["height"], $size1["width"], $size2["whidth"]);
		
		//Paramètres côté client
		$highlight_colors="\tvar hlBackColor=\"$par_highlight_back_color\";\n\tvar hlTextColor=\"$par_highlight_text_color\";\n";
		$_SESSION["ses_highlight_colors"]=$highlight_colors;
		
		//Paramètres côté serveur

		//Coleurs de la page assignées au tag BODY (text, fond, liens)
		$page_colors=array(
			"back_color"=>$par_page_back_color,
			"text_color"=>$par_page_text_color,
			"link_color"=>$par_page_link_color,
			"vlink_color"=>$par_page_vlink_color,
			"alink_color"=>$par_page_alink_color
		);
		$_SESSION["ses_page_colors"]=$page_colors;
		
		//Couleurs des blocs et des panels (news, etc.) 
		$panel_colors=array(
			"border_color"=>$par_object_border_color,
			"caption_color"=>$par_titlebar_text_color,
			"back_color"=>$par_object_back_color,
			"fore_color"=>$par_object_text_color
		);
		$_SESSION["ses_panel_colors"]=$panel_colors;

		//Couleurs de l'agenda
		$diary_colors=array(
			"border_color"=>$par_object_border_color,
			"caption_color"=>$par_titlebar_text_color,
			"back_color"=>$par_object_back_color,
			"fore_color"=>$par_object_text_color,
			"hl_back_color"=>$par_highlight_back_color,
			"hl_text_color"=>$par_highlight_text_color
		);
		$_SESSION["ses_diary_colors"]=$diary_colors;
	
		//couleurs du control dbGrid
		$grid_colors=array(
			"border_color"=>$par_page_back_color,
			"header_back_color"=>$par_titlebar_back_color,
			"header_fore_color"=>$par_titlebar_text_color,
			"even_back_color"=>$par_grid_even_back_color,
			"odd_back_color"=>$par_grid_odd_back_color, 
			"even_fore_color"=>$par_grid_even_text_color,
			"odd_fore_color"=>$par_grid_odd_text_color,
			"pager_color"=>$par_object_back_color
		);
		$_SESSION["ses_grid_colors"]=$grid_colors;

		$_SESSION["ses_profile"]=$this_profile;
		$_SESSION["ses_skin_theme"]=$sk_theme;
		$_SESSION["ses_apply_skin"]=$par_apply_skin;
		$_SESSION["ses_top_left_pic"]=$sk_top_left_pic;
		$_SESSION["ses_top_pic"]=$sk_top_pic;
		$_SESSION["ses_top_right_pic"]=$sk_top_right_pic;
		$_SESSION["ses_right_pic"]=$sk_right_pic;
		$_SESSION["ses_bottom_right_pic"]=$sk_bottom_right_pic;
		$_SESSION["ses_bottom_pic"]=$sk_bottom_pic;
		$_SESSION["ses_bottom_left_pic"]=$sk_bottom_left_pic;
		$_SESSION["ses_left_pic"]=$sk_left_pic;
	} else {
		//echo jsAlert("Je prends les paramètres dans la session pour le profile $this_profile.");
		
		$profile= $_SESSION["ses_profile"];
		$apply_skin= $_SESSION["ses_apply_skin"];
		$page_colors= $_SESSION["ses_page_colors"];
		$panel_colors= $_SESSION["ses_panel_colors"];
		$diary_colors= $_SESSION["ses_diary_colors"];
		$grid_colors= $_SESSION["ses_grid_colors"];
		$highlight_colors=$_SESSION["ses_highlight_colors"];
		$par_apply_skin=$_SESSION["ses_apply_skin"];
		$sk_theme= $_SESSION["ses_skin_theme"];
		$sk_top_left_pic=$_SESSION["ses_top_left_pic"];
		$sk_top_pic=$_SESSION["ses_top_pic"];
		$sk_top_right_pic=$_SESSION["ses_top_right_pic"];
		$sk_right_pic=$_SESSION["ses_right_pic"];
		$sk_bottom_right_pic=$_SESSION["ses_bottom_right_pic"];
		$sk_bottom_pic=$_SESSION["ses_bottom_pic"];
		$sk_bottom_left_pic=$_SESSION["ses_bottom_left_pic"];
		$sk_left_pic=$_SESSION["ses_left_pic"];	
		
	}
	
	//Paramètres côté client
	$_SESSION["javascript"].=$highlight_colors;
		
}
