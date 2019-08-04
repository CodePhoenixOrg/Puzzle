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

define("PZ_JS_TEMP", "tmp/ipz_javascript_temp.php");
define("PANEL_IMAGE_ON_RIGHT", "r");
define("PANEL_IMAGE_ON_LEFT", "l");
define("PANEL_IMAGE_NONE", "n");

    
class Design extends Base
{
    public function __construct()
    {
        $this->_init_javascript();
    }

    protected function _init_javascript()
    {
        $js = <<<JAVASCRIPT
<script language="JavaScript" src="js/pz_design.js"></script>
JAVASCRIPT;

        // echo $js;
    }

    public function createPanel($name, $caption, $content, $colors, $panel_width)
    {
        if (!empty($colors)) {
            $border_color=$colors["border_color"];
            $caption_color=$colors["caption_color"];
            $back_color=$colors["back_color"];
            $fore_color=$colors["fore_color"];
        } else {
            $border_color="black";
            $caption_color="white";
            $back_color="white";
            $fore_color="black";
        }

        if (empty($panel_width)) {
            $panel_width="530";
        }

        $panel=	"<table id='$name' border='1' cellspacing='0' cellpadding='0' width='$panel_width' bordercolor='$border_color'><tr><td>\n".
        "<table border='0' cellspacing='0' cellpadding='1' width='$panel_width' bordercolor='$border_color'>\n".
        "<tr bgcolor='$border_color'>\n".
            "\t<td align='left' valign='top' width='$panel_width'>\n".
                "\t\t<span style='color:$caption_color'>\n".
                "\t\t$caption\n".
                "\t\t</span>\n".
            "\t</td>\n".
        "</tr>\n".
        "<tr height='4' valign='top' bgcolor='$back_color'> \n".
            "\t<td align='left' valign='top' width='$panel_width'>\n".
                "\t\t<span style='color:$fore_color'>\n".
                "\t\t$content<br>\n".
                "\t\t</span>\n".
            "\t</td>\n".
        "</tr>\n".
        "</table>\n".
        "</td></tr></table>\n";
    
        return $panel;
    }

    public function createEnhancedPanel($name, $caption, $content, $caption_style, $content_style, $border_width, $panel_height, $panel_width, $color)
    {
        if (empty($colors)) {
            global $panel_colors;
            $colors=$panel_colors;
        }
    
        if (!empty($colors)) {
            $border_color=$colors["border_color"];
            $caption_color=$colors["caption_color"];
            $back_color=$colors["back_color"];
            $fore_color=$colors["fore_color"];
        } else {
            $border_color="black";
            $caption_color="white";
            $back_color="white";
            $fore_color="black";
        }

        if (empty($panel_width)) {
            $panel_width="400";
        }
        if (empty($panel_height)) {
            $panel_height="100";
        }

        $caption_style=" style=\"background-color: $border_color;color: $caption_color;$caption_style\"";
        $content_style=" style=\"background-color: $back_color;color: $fore_color;$content_style\"";
    
        $panel=	"<table id='$name' border='$border_width' cellspacing='0' cellpadding='0' height='$panel_height' width='$panel_width' bordercolor='$border_color'><tr><td>\n".
        "<table border='0' cellspacing='0' cellpadding='0' height='100%' width='100%' bordercolor='$border_color'>\n".
        "<tr height='10' bgcolor='$border_color'>\n".
            "\t<td align='left' valign='top' width='$panel_width'$caption_style>\n".
                "\t\t&nbsp;$caption\n".
            "\t</td>\n".
        "</tr>\n".
        "<tr height='*' valign='top' bgcolor='$back_color'> \n".
            "\t<td align='left' valign='top' width='$panel_width'$content_style>\n".
                "\t\t&nbsp;$content<br>\n".
            "\t</td>\n".
        "</tr>\n".
        "</table>\n".
        "</td></tr></table>\n";
    
        return $panel;
    }

    public function createPanelWithImage($name, $caption, $content, $colors, $panel_width, $image_file, $image_pos, $image_width)
    {
        if (!isset($colors)) {
            global $panel_colors;
            $colors=$panel_colors;
        }
    
        if (!empty($colors)) {
            $border_color=$colors["border_color"];
            $caption_color=$colors["caption_color"];
            $back_color=$colors["back_color"];
            $fore_color=$colors["fore_color"];
        } else {
            $border_color="black";
            $caption_color="white";
            $back_color="white";
            $fore_color="black";
        }

        if (empty($panel_width)) {
            $panel_width="530";
        }
        if (empty($image_width)) {
            $image_width="100";
        }
        if (empty($image_pos)) {
            $image_pos=PANEL_IMAGE_ON_RIGHT;
        }

        $display_image=(file_exists($image_file) && $image_pos!=PANEL_IMAGE_NONE);
        if ($display_image) {
            $content_width=$panel_width-$image_width;
            $caption_span="colspan='2'";
            if ($image_pos==PANEL_IMAGE_ON_RIGHT) {
                $imright.="\t<td align='right' valign='middle' width='$image_width'>\n".
                "\t\t<img align='right' border=1 src='$image_file' NOSAVE>\n";
                "\t</td>\n";
                $imleft="";
            } elseif ($image_pos==PANEL_IMAGE_ON_LEFT) {
                $imright="";
                $imleft.="\t<td align='left' valign='middle' width='$image_width'>\n".
                "\t\t<img align='left' border=1 src='$image_file' NOSAVE>\n";
                "\t</td>\n";
            }
        } else {
            $content_width=$panel_width;
            $caption_span="";
        }
    
        $panel=	"<table id='$name' border='1' cellspacing='0' cellpadding='0' width='$panel_width' bordercolor='$border_color'><tr><td>\n".
        "<table border='0' cellspacing='0' cellpadding='1' width='$panel_width' bordercolor='$border_color'>\n".
        "<tr bgcolor='$border_color'>\n".
            "\t<td $caption_span align='left' valign='top' width='$panel_width'>\n".
                "\t\t<span style='color:$caption_color'>\n".
                "\t\t$caption\n".
                "\t\t</span>\n".
            "\t</td>\n".
        "</tr>\n".
        "<tr height='4' valign='top' bgcolor='$back_color'> \n".
            $imleft.
            "\t<td align='left' valign='top' width='$content_width'>\n".
                "\t\t<span style='color:$fore_color'>\n".
                "\t\t$content<br>\n".
                "\t\t</span>\n".
            "\t</td>\n".
            $imright.
        "</tr>\n".
        "</table>\n".
        "</td></tr></table>\n";
    
        return $panel;
    }


    public function tableShadow($name="", $html_table="", $suffix="eeeeee")
    {
        /*
            $name : nom du tableau
            $html_table : tableau html
        
            La fonction renvoie un tableau html à 2 linges et 2 colonnes contenant le tableau passé en paramètre dans la première cellule. Les autres cellules contiennent des img d\"ombres.
        */
        global $img, $panel_colors;

        $hostname=getHttpRoot()."/";
    
        if (!isset($_SESSION["ses_apply_skin"])) {
            $_SESSION["ses_apply_skin"]="N";
        }
        
        if ($_SESSION["ses_apply_skin"]=="N") {
            //return $html_table;
        
            $shadow="  <table id=\"".$name."_shadow\" border=\"0\" bordercolor=\"".$panel_colors['border_color']."\" cellspacing=\"0\" cellpadding=\"3\">\n".
        "    <tr>\n".
        "      <td>\n\n".
        $html_table."\n\n".
        "      </td>\n".
        "    </tr>\n".
        "  </table>\n\n";
        } else {
            $shadow="  <table id=\"".$name."_shadow\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n".
        "    <tr><td bgcolor=\"${suffix}\" height=\"3\" colspan=\"2\"></td></tr>\n".
        "    <tr>\n".
        "      <td rowspan=\"2\" colspan=\"2\">\n\n".
        $html_table."\n\n".
        "      </td>\n".
        "      <td background=\"img/shadows/${suffix}_top_right.png\" style=\"font-size: 1; width:11; height:8;\"></td>\n".
        "    </tr>\n".
        "    <tr>\n".
        "      <td id=\"".$name."_sh\" background=\"img/shadows/${suffix}_right.png\" style=\"font-size: 1; width:11;\"></td>\n".
        "    </tr>\n".
        "    <tr>\n".
        "      <td background=\"img/shadows/${suffix}_bottom_left.png\" style=\"font-size: 1; width:8; height:11;\"></td>\n".
        "      <td id=\"".$name."_sw\" background=\"img/shadows/${suffix}_bottom.png\" style=\"font-size: 1; height: 11\"></td>\n".
        "      <td background=\"img/shadows/${suffix}_bottom_right.png\" style=\"font-size: 1; width:11; height:11;\"></td>\n".
        "    </tr>\n".
        "  </table>\n\n";
    
            $js= "\tpz_shadow(\"$name\");\n";
    
            /*
            $www_root=get_files_root();
            $filename=$www_root.PZ_JS_TEMP;
            if (!file_exists($filename)) touch($filename);
            $handle = fopen($filename, 'a');
            if($handle) fwrite($handle, $js);
            fclose($handle);
            */
            $_SESSION["javascript"].=$js;
        }
    
        return $shadow;
    }

    public function createSkin($name="", $table="", $skin_theme="")
    {
        /*
            $name : nom du tableau
            $html_table : tableau html
        
            La fonction renvoie un tableau html à 2 linges et 2 colonnes contenant le tableau passé en paramètre dans la première cellule. Les autres cellules contiennent des img d\"ombres.
        */
        global $img;

        if ($skin_theme="") {
            $skin=$_SESSION["ses_skin_theme"];
        }
    
        if ($_SESSION["ses_apply_skin"]=="N") {
            return $table;
        }
    
        $hostname=getHttpRoot()."/";
    
        $shadow="";
    
    
        $shadow="  <table id=\"".$name."_shadow\" border=\"0\" cellspacing=\"0\" cellpadding=\"0\">\n".
        "    <tr><td bgcolor=\"white\" height=\"3\" colspan=\"2\"></td></tr>\n".
        "    <tr>\n".
        "      <td rowspan=\"2\" colspan=\"2\">\n\n".
        $table."\n\n".
        "      </td>\n".
        "      <td background=\"img/skin/$skin/$sk_top_right_pic\" style=\"font-size: 1; width:11; height:8;\"></td>\n".
        "    </tr>\n".
        "    <tr>\n".
        "      <td id=\"".$name."_sh\" background=\"img/skin/$skin/$sk_right_pic\" style=\"font-size: 1; width:11;\"></td>\n".
        "    </tr>\n".
        "    <tr>\n".
        "      <td background=\"img/skin/$skin/$sk_bottom_left_pic\" style=\"font-size: 1; width:$min_size; height:$max_size;\"></td>\n".
        "      <td id=\"".$name."_sw\" background=\"img/skin/$skin/$sk_bottom_pic\" style=\"font-size: 1; height: 11\"></td>\n".
        "      <td background=\"img/skin/$skin/$sk_bottom_right_pic\" style=\"font-size: 1; width:11; height:11;\"></td>\n".
        "    </tr>\n".
        "  </table>\n\n";
    
        $js= "\tpz_shadow(\"$name\");\n";
 
        $_SESSION["javascript"].=$js;
    
        return $shadow;
    }


    public function createThumbnail($source, $destination, $new_width)
    {
        $result=copy($source, $destination);

        if (!file_exists($source)) {
            return false;
        }

        list($width, $height)=getimagesize($source);

        $ratio=$height/$width;
        $tn_width=$new_width;
        $tn_height=round($tn_width*$ratio);
    
        //echo "Avant : width='$width'; height='$height';<br>";
        $command="/usr/bin/convert -size ".$width."x".$height." $destination -resize ".$tn_width."x".$tn_height." $destination";
        //echo $command;
    
        system($command);
    
        //echo "Après : width='$width'; height='$height';<br>";
    
        return $result;
    }
}
