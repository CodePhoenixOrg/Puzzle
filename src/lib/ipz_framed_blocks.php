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

define("FRAMED_BLOCK_LEFT_COLUMN", 1);
define("FRAMED_BLOCK_RIGHT_COLUMN", 3);

class FramedBlocks
{
    public $dyna_bset_counter;
    public $_design = null;

    public function __construct($lg, $db_prefix)
    {
        parent::__construct($lg, $db_prefix);
        $this->_design = new \Puzzle\Design();
    }
    
    public function createEnhancedFramedBlockSet($database, $column, $target, $id, $lg, $colors)
    {
        $cs=connection(CONNECT, $database);
    
        global $dyna_bset_counter, $panel_colors, $diarydb;
        $border_color=$panel_colors["border_color"];
    
        $block_set="";
        $sql="select bl_id, bl_type from {$this->db_prefix}blocks where bl_column=$column order by bl_id";
        //echo "$sql<br>";
        $stmt = $cs->query($sql);
        $bl_type="";
        $old_bl_type="";
        $first_block=true;
        $dbs_id=0;
        $js="<script language='text/javascript'>alert(\" ferme le tableau des block dynamiques !\");</script>";
        while ($rows=$stmt->fetch()) {
            $index=$rows[0];
            $bl_type=$rows[1];

            //$js="<script language='text/javascript'>alert(\"Pendant : old_bl_type='$old_bl_type'; bl_type='$bl_type';\");</script>";
            //echo $js;
            if (($old_bl_type=="dynamic") && ($bl_type!=$old_bl_type)) {
                $dyna_block_set.="</td></tr></table>\n";
                $dyna_block_set=tableShadow($dbs_name, $dyna_block_set);
                $first_block=true;
                $dbs_name="";
                $block_set.=$dyna_block_set;
            }
        
            if ($bl_type=="static") {
                $block_set.=createFramedBlock($database, $index, $target, $id, $lg, $colors);
            } elseif ($bl_type=="dynamic") {
                if ($first_block) {
                    $dyna_bset_counter++;
                    $dbs_name="dyna_bset$dyna_bset_counter";
                    $dyna_block_set="<table id=\"$dbs_name\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\" width=\"100\" bordercolor=\"$border_color\"><tr><td>\n";
                    $first_block=false;
                }
                $dyna_block_set.=createFramedCollapseableBlock($dbs_name, $index, $target, $colors);
            } elseif ($bl_type=="members") {
                $logout=getArgument("logout");
                //$panel_colors=getArgument("panel_colors");
                $block_set.=createFramedMembersBlock($database, $logout, "members", $id, $lg, $target, $panel_colors);
            } elseif ($bl_type=="newsletter") {
                //$panel_colors=getArgument("panel_colors");
                global $panel_colors;
                $block_set.=createFramedNewsletterBlock($database, "newsltr", $id, $lg, $target, $panel_colors);
            } elseif ($bl_type=="diary") {
                global $diary_colors;
                $date=getArgument("date");
                $block_set.=createFramedDiaryBlock($date, $id, $lg, $target, $diary_colors);
            }
        
            $old_bl_type=$bl_type;
        }
    
        //$js="<script language='text/javascript'>alert(\"Après : old_bl_type='$old_bl_type'; bl_type='$bl_type';\");</script>";
        //echo $js;
        if ($old_bl_type=="dynamic") {
            $dyna_block_set.="</td></tr></table>\n";
            $dyna_block_set=tableShadow($dbs_name, $dyna_block_set);
            $first_block=true;
            $dbs_name="";
            $block_set.=$dyna_block_set;
        }
        
        //print_r($dyna_block_set);
        return $block_set;
    }

    public function createFramedBlock($database, $block_num, $target, $id, $lg, $colors)
    {
        global $db_prefix;
    
        if (empty($colors)) {
            global $panel_colors;
            $color=$panel_colors;
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

        $cs=connection(CONNECT, $database);
        $sql=	"select d.di_".$lg."_short ".
        "from {$this->db_prefix}blocks b, {$this->db_prefix}dictionary d ".
        "where  b.di_id=d.di_id ".
        "and b.bl_id=$block_num";
        $stmt = $cs->query($sql);
        $rows=$stmt->fetch();
        $block_name=$rows[0];

        $sql=	"select m.me_id, m.me_level, m.bl_id, d.di_".$lg."_short, m.me_target, p.pa_filename, p.pa_id ".
                "from {$this->db_prefix}menus m, {$this->db_prefix}blocks b, {$this->db_prefix}pages p, {$this->db_prefix}dictionary d ".
                "where m.di_name=d.di_name ".
                "and p.pa_id=m.pa_id ".
        "and m.bl_id=b.bl_id ".
        "and m.bl_id=$block_num ".
        "order by m.me_id";
        // 		//echo "$sql<br>";
        
        $sub_menu="";
        $count=0;
        $zero=0;
    
        $stmt = $cs->query($sql);
        while ($rows=$stmt->fetch()) {
            $index=$rows[0];
            $level=$rows[1];
            $block=$rows[2];
            $caption=$rows[3];
            $target=$rows[4];
            $uri=$rows[5];
            $page=$rows[6];
        
            $request="";
            $p=strpos($uri, "?", 0);
            if ($p>-1) {
                $request="&".substr($uri, $p+1, strlen($uri)-$p);
                $uri=substr($uri, 0, $p);
                //echo "$request<br>";
            }
        
            $sub_menu.="<tr id=\"$index$count\" onMouseOver=\"window.status='';setRowColor(this, hlBackColor, hlTextColor);\" onMouseOut=\"setBackRowColor(this);\"><td><a href=\"page.php?id=$index&lg=$lg$request\" target=\"$target\"><span id=\"caption_$index$count$zero\" style=\"color:$fore_color\">$caption</span></a></td></tr>\n";
            $count++;
        }
    
        $table_name="block$id$index";

        $block=	"<table id=\"$table_name\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\" width=\"100\" bordercolor=\"$border_color\"><tr><td>\n".
        "<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=\"100\" bordercolor=\"$border_color\">\n".
        "<tr bgcolor=\"$border_color\">\n".
            "\t<td width=\"100%\" height=\"4\">\n".
            "\t<span style=\"color:$caption_color\"><center>$block_name</center></span>\n".
            "\t</td>\n".
        "</tr>\n".
        "<tr height=\"4\" valign=\"top\"> \n".
            "\t<td width=\"100%\" bgcolor=\"$back_color\">\n".
            "\t<table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">\n".
            "\t$sub_menu\n".
            "\t</table>\n".
            "\t</td>\n".
        "</tr>\n".
        "</table>\n".
        "</td></tr></table>\n";

        $block=tableShadow($table_name, $block);

        return $block;
    }

    public function createFramedCollapseableBlock($block_skin_name, $block_num, $target, $colors)
    {
        if (empty($colors)) {
            global $panel_colors;
            $color=$panel_colors;
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

        $id=getArgument("id");
        $lg=getArgument("lg");
        $database=getArgument("database");

        $cs=connection(CONNECT, $database);
        $sql=	"select d.di_".$lg."_short, b.bl_column ".
        "from {$this->db_prefix}blocks b, {$this->db_prefix}dictionary d ".
        "where  b.di_id=d.di_id ".
        "and b.bl_id=$block_num";
        $stmt = $cs->query($sql);
        $rows=$stmt->fetch();
        $block_name=$rows[0];
        $block_column=$rows[1];

        $sql=	"select m.me_id, m.me_level, m.bl_id, d.di_" . $lg . "_short, m.me_target, p.pa_filename, p.pa_id " .
                "from {$this->db_prefix}menus m, {$this->db_prefix}blocks b, {$this->db_prefix}pages p, {$this->db_prefix}dictionary d ".
                "where m.di_name=d.di_name ".
                "and p.pa_id=m.pa_id ".
        "and m.bl_id=b.bl_id ".
        "and m.bl_id=$block_num ".
        "order by m.me_id";
        
        $sub_menu="";
        $count=0;
        $zero=0;
    
        $stmt = $cs->query($sql);
        while ($rows=$stmt->fetch()) {
            $index=$rows[0];
            $level=$rows[1];
            $block=$rows[2];
            $caption=$rows[3];
            $target=$rows[4];
            $link=$rows[5];
            $page=$rows[6];
            $sub_menu.="<tr id=\"$index$count\" onMouseOver=\"setRowColor(this, hlBackColor, hlTextColor);\" onMouseOut=\"setBackRowColor(this);\"><td><a href=\"page.php?id=$index&lg=" . $lg . "\" target=\"$target\"><span id=\"caption_$index$count$zero\" style=\"color:$fore_color\">$caption</span></a></td></tr>\n";
            $count++;
        }
    
        $table_name="block$block_column$id$index";

        /*
        $js="\tif(PZ_CURRENT_EXPANDED_BLOCK==null) {\n\t\tvar block=eval(document.getElementById(\"block_caption$index\"));\n\t\texpand_block(block, \"$block_skin_name\");\n\t}\n";
        $_SESSION["javascript"].=$js;
        */
    
        //"\t<td id=\"block_caption_$index\" name=\"block_caption$block_column\" width=\"100%\" height=\"4\" onClick=\"PZ_CURRENT_BLOCK_SKIN='$block_skin_name'; expand_block(this);\">\n".
        $block=	"<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=\"100\" bordercolor=\"$border_color\">\n".
        "<tr bgcolor=\"$border_color\">\n".
            "\t<td id=\"block_caption_$index\" name=\"block_caption$block_column\" width=\"100%\" height=\"4\" onClick=\"expand_block(this, '$block_skin_name');\">\n".
            "\t<a href=\"#\"><span style=\"color:$caption_color\"><center>$block_name</center></span></a>\n".
            "\t</td>\n".
        "</tr>\n".
        "<tr height=\"4\" valign=\"top\">\n".
            "\t<td width=\"100%\" bgcolor=\"$back_color\">\n".
            "\t<table id=\"block_table_$index\" name=\"block_table$block_column\" width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"display: none; visibilty: visible;\">\n".
            "\t$sub_menu\n".
            "\t</table>\n".
            "\t</td>\n".
        "</tr>\n".
        "</table>\n";

        return $block;
    }

    public function createFramedDiaryBlock($date, $id, $lg, $target, $colors)
    {
        global $diary_colors;
    
        if (!isset($colors)) {
            $colors=$diary_colors;
        }
    
        if (isset($colors)) {
            $border_color=$colors["border_color"];
            $caption_color=$colors["caption_color"];
            $back_color=$colors["back_color"];
            $fore_color=$colors["fore_color"];
            $hl_back_color=$colors["hl_back_color"];
            $hl_text_color=$colors["hl_text_color"];
        } else {
            $border_color="black";
            $caption_color="white";
            $back_color="white";
            $fore_color="black";
            $hl_back_color="grey";
            $hl_text_color="white";
        }

        $diary=createFramedDiaryControl("", $target, $colors);
    
        $table_name="diary";
        $block=	"<table id=\"$table_name\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\" width=\"100\" bordercolor=\"$border_color\">\n".
        "<tr height=\"4\" valign=\"top\"> \n".
            "\t<td width=\"98\" bgcolor=\"$back_color\" align=\"center\">\n".
            $diary.
            "\t</td>\n".
        "</tr>\n".
        "</table>\n";

        $block=tableShadow($table_name, $block);
    
        return $block;
    }

    public function createFramedMembersBlock($database, $logout, $di_name, $id, $lg, $target, $colors)
    {
        global $PHP_SELF;

        if (empty($colors)) {
            global $panel_colors;
            $color=$panel_colors;
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

        $cs=connection(CONNECT, $database);
        $sql=	"select d.di_".$lg."_short ".
        "from {$this->db_prefix}blocks b, {$this->db_prefix}dictionary d ".
        "where  b.di_id=d.di_id ".
        "and b.di_id=\"$di_id\"";
        $stmt = $cs->query($sql);
        $rows=$stmt->fetch();
        $block_name=$rows[0];
    
        $table_name="members";

        if (!isset($_SESSION["ses_status"])) {
            $status=MEMBER_LOGGED_OUT;
        } else {
            $status=$_SESSION["ses_status"];
        }
        if (!isset($_SESSION["ses_apps_login"])) {
            $apps_login="";
        } else {
            $apps_login=$_SESSION["ses_apps_login"];
        }

        if ($status==MEMBER_LOGGED_OUT) {
            $connection_link="\t<a href=\"page.php?di=ed_membe&lg=$lg&action=Ajouter\" target=\"$target\">Devenir membre >></a>\n";
        } elseif ($status==MEMBER_LOGGED_IN) {
            $connection_link="\t<a href=\"menu.php?id=$id&lg=$lg&logout=1\">Déconnexion</a>\n";
        }

        if ($apps_login) {
            $connection_link="";
        }
        
        if ($logout) {
            session_destroy();
            $js=	"<script language=JavaScript>".
            "window.location.href='menu.php?id=1&lg=$lg';".
            "</script>\n";
            echo $js;
        }

        //session_destroy();
        //"\t<input type=\"hidden\" name=\"event\" value=\"onRun\">".
        $block=	"<table id=\"$table_name\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\" width=\"100\" bordercolor=\"$border_color\"><tr><td>\n".
        "<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=\"100\" bordercolor=\"$border_color\">\n".
        "<tr bgcolor=\"$border_color\">\n".
            "\t<td width=\"92\" height=\"4\">\n".
            "\t<span style=\"color:$caption_color\"><center>$block_name</center></span>\n".
            "\t</td>\n".
        "</tr>\n".
        "<tr height=\"4\" valign=\"top\"> \n".
            "\t<form method=\"POST\" name=\"membersForm\" action=\"$PHP_SELF\">\n".
            "\t<td width=\"96\" bgcolor=\"$back_color\" align=\"center\">\n".
            "\t<input type=\"hidden\" name=\"mbr_valider\" value=\"\">".
            "\tLogin<br>\n".
            "\t<input type=\"text\" name=\"mbr_login\" size=\"10\"><br>\n".
            "\tMot de passe<br>\n".
            "\t<input type=\"password\" name=\"mbr_pass\" size=\"10\"><br>\n".
            "\t<input type=\"button\" name=\"mbr_ok\" value=\"OK\" ".
            "onClick=\"document.membersForm.mbr_valider.value='OK';document.membersForm.submit();\">\n".
            "\t</td>\n".
            "\t</form>\n".
        "</tr>\n".
        "<tr height=\"4\" valign=\"top\"> \n".
            "\t<td width=\"96\" bgcolor=\"$back_color\" align=\"center\" style=\"font-size: 10;\">\n".
            $connection_link.
            "\t</td>\n".
        "</tr>\n".
        "</table>\n".
        "</td></tr></table>\n";

        $block=tableShadow($table_name, $block);
    
        return $block;
    }

    public function createFramedNewsletterBlock($database, $di_name, $id, $lg, $target, $colors)
    {
        global $PHP_SELF;

        if (empty($colors)) {
            global $panel_colors;
            $color=$panel_colors;
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

        $cs=connection(CONNECT, $database);
        $sql=	"select d.di_".$lg."_short ".
        "from {$this->db_prefix}blocks b, {$this->db_prefix}dictionary d ".
        "where  b.di_id=d.di_id ".
        "and b.di_id=\"$di_id\"";
        $stmt = $cs->query($sql);
        $rows=$stmt->fetch();
        $block_name=$rows[0];

        $table_name="newsltr";
        $block=	"<table id=\"$table_name\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\" width=\"100\" bordercolor=\"$border_color\"><tr><td>\n".
        "<table border=\"0\" cellspacing=\"0\" cellpadding=\"1\" width=\"100\" bordercolor=\"$border_color\">\n".
        "<tr bgcolor=\"$border_color\">\n".
            "\t<td width=\"92\" height=\"4\">\n".
            "\t<span style=\"color:$caption_color\"><center>$block_name</center></span>\n".
            "\t</td>\n".
        "</tr>\n".
        "<tr height=\"4\" valign=\"top\"> \n".
            "\t<form method=\"POST\" name=\"newsletterForm\" action=\"$PHP_SELF\">\n".
            "\t<td width=\"96\" bgcolor=\"$back_color\" align=\"center\">\n".
            "\t<input type=\"hidden\" name=\"sub_valider\" value=\"\">".
            "\tE-mail<br>\n".
            "\t<input type=\"text\" name=\"sub_email\" size=\"13\"><br>\n".
            "\t</td>\n".
        "</tr>\n".
        "<tr height=\"4\" valign=\"top\"> \n".
            "\t<td width=\"96\" bgcolor=\"$back_color\" align=\"center\" style=\"font-size: 10;\">\n".
            "\t<input type=\"radio\" checked name=\"sub_subscribe[]\" value=\"subscribe\">S'abonner<br>\n".
            "\t<input type=\"radio\" name=\"sub_subscribe[]\" value=\"unsubscribe\">Se désabonner<br>\n".
            "\t<input type=\"button\" name=\"sub_ok\" value=\"OK\" \n".
            "onClick=\"document.newsletterForm.sub_valider.value='OK';document.newsletterForm.submit();\">\n".
            "\t</td>\n".
            "\t</form>\n".
        "</tr>\n".
        "</table>\n".
        "</td></tr></table>\n";
        
        $block=tableShadow($table_name, $block);
    
        return $block;
    }
}
