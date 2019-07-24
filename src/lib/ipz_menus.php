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

define("BUTTON_OUT", "out");
define("BUTTON_OVER", "over");
define("BUTTON_DOWN", "down");
define("BUTTON_UP", "up");
define("BUTTON_IMAGE", "img");
define("BUTTON_INPUT", "input");
define("BUTTON_IMAGE_RESET", "img_reset");
define("BUTTON_INPUT_RESET", "input_reset");
define("SUB_MENU_HORIZONTAL", 0);
define("SUB_MENU_VERTICAL", 1);

class Menus extends Base
{

    public function getAdminUrl($userdb)
    {
        $adm_url="";
        $cs=connection(CONNECT, $userdb);
        $sql="select app_link from {$this->db_prefix}applications where di_name='modadmin'";
        $stmt = $cs->query($sql);
        if ($rows=$stmt->fetch(\PDO::FETCH_ASSOC)) {
            $adm_url=$rows["app_link"];
        }
    
        return $adm_url;
    }
        
    public function showMenu($userdb)
    {
        $cs=connection(CONNECT, $userdb);
    
        $sql = 'delete from {$this->db_prefix}v_menus;';
        $cs->query($sql);

        $sql=   "insert into {$this->db_prefix}v_menus (me_id, pa_id, me_level, di_name, me_target, pa_filename, di_fr_short, di_fr_long, di_en_short, di_en_long)".
        "select m.me_id, m.pa_id, m.me_level, m.di_name, m.me_target, p.pa_filename, d.di_fr_short, d.di_fr_long, d.di_en_short, d.di_en_long " .
                "from {$this->db_prefix}menus m, {$this->db_prefix}pages p, {$this->db_prefix}dictionary d " .
                "where m.di_name = d.di_name " .
                "and p.di_name = d.di_name " .
                "order by m.me_id";
        //echo $sql;
        $cs->query($sql);

        $sql=   "select me_id as Menu, pa_id as Page, me_level as Niveau, di_name as Dictionnaire, me_target as Cible, pa_filename as Fichier, di_fr_short as 'Francais court', di_fr_long as 'Francais long', di_en_short as 'Anglais court', di_en_long as 'Anglais long' from {$this->db_prefix}v_menus";
    
        //tableau_sql("menu", $sql, 0, "edit.php", "", "&database=$database", "", "", "", $cs);
        //container("menu", 50, 250, 200, 355, 16);
        $dbgrid=createDbGrid("menu", $sql, "editor", "page.php", "&me_id=#Menu&userdb={$this->database}", false, $dialog, array(), $grid_colors, $cs);
        echo $dbgrid;
    }

    /*** OBSOLETE ***/
    /*
    function menu_exists($database, $pa_filename="")
    {
        $cs=connection(CONNECT, $database);
    
        $sql=	"select m.me_id, m.pa_id, m.me_level, m.di_name, m.me_target, p.pa_filename, d.di_fr_short, d.di_fr_long, d.di_en_short, d.di_en_long " .
                    "from menus m, pages p, dictionary d " .
                    "where m.di_name = d.di_name " .
                    "and p.di_name = d.di_name " .
            "and p.pa_filename = '$pa_filename' " .
                    "order by m.me_id";
    
        $stmt = $cs->query($sql);
        $exists=$result->num_rows>0;
    
        return $exists;
    }
    */

    public function getPageId($userdb, $pa_filename)
    {
        $cs = connection(CONNECT, $userdb);
        $sql = "select pa_id from {$this->db_prefix}pages where pa_filename = '$pa_filename'";
        self::getLogger()->debug($sql, __FILE__, __LINE__);
        self::getLogger()->dump('LG', $this->lg);

        $stmt = $cs->query($sql);
        $rows = $stmt->fetch();
        $pa_id = isset($rows[0]) ? (int)$rows[0] : 0;

        return $pa_id;
    }

    public function getMenuId($database, $pa_filename)
    {
        $cs = connection(CONNECT, $database);
        $sql = "select m.me_id, p.pa_id from {$this->db_prefix}menus m left outer join {$this->db_prefix}pages p on m.pa_id = p.pa_id where p.pa_filename = '$pa_filename'";
        self::getLogger()->debug($sql, __FILE__, __LINE__);
        $stmt = $cs->query($sql);
        $rows = $stmt->fetch();
        $me_id = isset($rows[0]) ? (int)$rows[0] : 0;

        return $me_id;
    }

    public function getMenuAndPage($userdb, $pa_filename)
    {
        $cs = connection(CONNECT, $userdb);
        $sql = "select m.me_id, p.pa_id from {$this->db_prefix}menus m left outer join {$this->db_prefix}pages p on m.pa_id = p.pa_id where p.pa_filename = '$pa_filename'";
        self::getLogger()->debug($sql, __FILE__, __LINE__);
        $stmt = $cs->query($sql);
        $rows = $stmt->fetch();
        $me_id = isset($rows[0]) ? (int)$rows[0] : 0;
        $pa_id = isset($rows[1]) ? (int)$rows[1] : 0;

        return [$me_id, $pa_id];
    }

    public function getPageFilename($database, $id=0)
    {
        $sql=   "select p.pa_filename " .
                "from {$this->db_prefix}pages p, {$this->db_prefix}menus m, {$this->db_prefix}dictionary d " .
                "where m.di_name=d.di_name " .
                "and p.pa_id=m.pa_id " .
                "and m.me_id=" . $id;
        $cs=connection(CONNECT, $database);
        $stmt = $cs->query($sql);
        $rows=$stmt->fetch();
        $page = $rows[0];
        
        return $page;
    }
        
    public function addMenuAndPage(
        $userdb,
        $di_name,
        $me_level,
        $me_target,
        $pa_filename,
        $di_fr_short,
        $di_fr_long,
        $di_en_short="",
        $di_en_long=""
    ) {
        list($me_id, $pa_id) = $this->getMenuAndPage($userdb, $pa_filename);
        if (!($me_id && $pa_id)) {
            $cs=connection(CONNECT, $userdb);
            $wwwroot=getWwwRoot();
        
            if (empty($me_target)) {
                $me_target="page";
            }
        
            $cs->beginTransaction();
            $sql = <<<INSERT
    insert into {$this->db_prefix}dictionary (di_name, di_fr_short, di_fr_long, di_en_short, di_en_long)
    values('$di_name', '$di_fr_short', '$di_fr_long', '$di_en_short', '$di_en_long')
INSERT;
            $affected_rows = $cs->exec($sql);
            $di_id = $cs->lastInsertId();

            self::getLogger()->debug($sql, __FILE__, __LINE__);

            $sql = <<<INSERT
    insert into {$this->db_prefix}pages (di_name, pa_filename)
    values('$di_name', '$pa_filename')
INSERT;
            $affected_rows += $cs->exec($sql);
            $pa_id = $cs->lastInsertId();

            self::getLogger()->debug($sql, __FILE__, __LINE__);
    
            $sql = <<<INSERT
    insert into {$this->db_prefix}menus (di_name, me_level, me_target, pa_id)
    values('$di_name', '$me_level', '$me_target', $pa_id)
INSERT;
            $affected_rows += $cs->exec($sql);
            $me_id = $cs->lastInsertId();

            self::getLogger()->debug($sql, __FILE__, __LINE__);

            $cs->commit();
        }
        return [$me_id, $pa_id, $affected_rows];
    }

    public function updateMenu(
        $userdb,
        $di_name,
        $me_level,
        $me_target,
        $pa_filename,
        $di_fr_short,
        $di_fr_long,
        $di_en_short,
        $di_en_long
    ) {
        list($me_id, $pa_id) = $this->getMenuAndPage($userdb, $pa_filename);

        $cs=connection(CONNECT, $userdb);

        $cs->beginTransaction();

        $sql=   "update {$this->db_prefix}menus set di_name='$di_name', me_level='$me_level', me_target='$me_target', pa_id=$pa_id ".
        "where me_id=$me_id";
        $affected_rows = $cs->exec($sql);

        $sql=   "update {$this->db_prefix}pages set di_name='$di_name', pa_filename='$pa_filename'".
        "where pa_id=$pa_id";
        $affected_rows += $cs->exec($sql);

        $sql=   "update {$this->db_prefix}menus set di_fr_short='$di_fr_short', di_fr_long='$di_fr_long', di_en_short='$di_en_short', di_en_long='$di_en_long' where di_name=$di_name";
        $affected_rows += $cs->exec($sql);

        $cs->commit();

        return $affected_rows;
    }

    public function deleteMenu($userdb, $di_name)
    {
        $cs=connection(CONNECT, $userdb);

        $cs->beginTransaction();

        $sql="delete from {$this->db_prefix}menus where di_name='$di_name'";
        $affected_rows = $cs->exec($sql);

        $sql="delete from {$this->db_prefix}pages where di_name='$di_name'";
        $affected_rows += $cs->exec($sql);

        $sql="delete from {$this->db_prefix}dictionary where di_name='$di_name'";
        $affected_rows += $cs->exec($sql);

        $cs->commit();

        return $affected_rows;
    }

    public function makeButtonImage($text="", $style="", $hl_color="")
    {
        $images_dir=getLocalImagesDir();
        $filename=$images_dir.$text."_".$style.".png";

        if (!file_exists($filename)) {
            $size=10;
            $offset=-16;
            $fonts_dir=getLocalFontsDir();
            $font=$fonts_dir."tahoma.ttf";

            if (!empty($hl_color)) {
                $red=hexdec(substr($hl_color, 0, 2));
                $green=hexdec(substr($hl_color, 2, 2));
                $blue=hexdec(substr($hl_color, 4, 2));
            } else {
                $red=255;
                $green=255;
                $blue=255;
            }

            if ($style==BUTTON_UP
            || $style==BUTTON_OUT
            || $style==BUTTON_OVER) {
                $offsetX=-2;
                $offsetY=-2;
                $position=BUTTON_UP;
            } elseif ($style==BUTTON_DOWN) {
                $offsetX=0;
                $offsetY=0;
                $position=BUTTON_DOWN;
            }
        
            list($llx, $lly, $lrx, $lry, $urx, $ury, $ulx, $uly)=imageTTFbbox($size, 0, $font, $text);

            $fwidth=abs($llx)+$lrx;
            $fheight=abs($uly-$lly);
    
            $im=imagecreate($fwidth+$offset+24, 24);
            $blue_bg=ImageColorAllocate($im, 0, 0, 255);
            imagecolortransparent($im, $blue_bg);
    
            $src_im = imagecreatefrompng($images_dir."builds/button_".$position."_left.png");
            imagecopy($im, $src_im, 0, 0, 0, 0, 12, 24);
            imagedestroy($src_im);

            $src_im = imagecreatefrompng($images_dir."builds/button_".$position."_middle.png");
            imagecopy($im, $src_im, 12, 0, 0, 0, $fwidth+$offset, 24);
            imagedestroy($src_im);
    
            $src_im = imagecreatefrompng($images_dir."builds/button_".$position."_right.png");
            imagecopy($im, $src_im, $fwidth+$offset+12, 0, 0, 0, 12, 24);
            imagedestroy($src_im);

    
            $width=imagesx($im);
            $height=imagesy($im);
            $shadow_color= ImageColorAllocate($im, 0, 0, 0);
            $fore_color=ImageColorAllocate($im, $red, $green, $blue);
            $values="($red, $green, $blue)";

            $left=abs(($width-$fwidth)/2)+abs($llx)+$offsetX;
            $top=abs(($height-$fheight)/2)+$fheight-$lly+$offsetY;
            //$top=abs(($height-$fheight)/2)+abs($uly);
        
            imagettftext($im, $size, 0, $left, $top, $shadow_color, $font, $text);
            imagettftext($im, $size, 0, $left+1, $top+1, $fore_color, $font, $text);
            imagepng($im, $filename, 255);
            //passthru("convert $filename.png $filename.gif");
            imagedestroy($im);
        }

        return $values;
    }

    public function makeButtonCode($text="", $type="", $out_color="", $over_color="", $down_color="")
    {
        $values=makeButtonImage($text, BUTTON_OUT, $out_color);
        $values=makeButtonImage($text, BUTTON_OVER, $over_color);
        $values=makeButtonImage($text, BUTTON_DOWN, $down_color);
        $images_dir=getHttpImagesDir();

        if ($type==BUTTON_IMAGE || $type==BUTTON_IMAGE_RESET) {
            $button="<img\n";
        } elseif ($type==BUTTON_INPUT || $type==BUTTON_INPUT_RESET) {
            $button="<input type=\"image\" name=\"$text\" value=\"$text\"\n";
        }
    
        $button.="\tid=\"$text\"\n";
        $button.="\tsrc=\"".$images_dir.$text."_out.png\"\n";
        $button.="\tonMouseOut=\"PZ_IMG.src='".$images_dir.$text."_out.png';\"\n";
        $button.="\tonMouseOver=\"PZ_IMG=document.getElementById('$text'); PZ_IMG.src='".$images_dir.$text."_over.png';\"\n";
        $button.="\tonMouseDown=\"PZ_IMG.src='".$images_dir.$text."_down.png';\"\n";
        if ($type==BUTTON_IMAGE || $type==BUTTON_INPUT) {
            $button.="\tonMouseUp=\"PZ_IMG.src='".$images_dir.$text."_over.png';\"\n";
        } elseif ($type==BUTTON_IMAGE_RESET || $type==BUTTON_INPUT_RESET) {
            $button.="\tonMouseUp=\"PZ_IMG.src='".$images_dir.$text."_over.png'; document.myForm.reset();\"\n";
        }
        $button.=">\n";

        return $button;
    }

    public function createMainMenu($database, $level=0)
    {
        //${this->lg}=getArgument("lg");
    
        $main_menu="<table border='0' cellpading='0' cellspacing='0'><tr>";
        $sql="";
        $sql=   "select m.pa_id, m.me_level, d.di_{$this->lg}_short " .
                "from {$this->db_prefix}menus m, {$this->db_prefix}dictionary d " .
                "where m.di_name=d.di_name " .
                "and m.me_level='$level' " .
                "order by m.me_id";
        
        //		echo $sql . "<br>";
        self::getLogger()->debug($sql, __FILE__, __LINE__);
        self::getLogger()->dump('LG', $this->lg);
        
        $cs=connection(CONNECT, $database);
        $stmt = $cs->query($sql);
        $count=0;
        while ($rows=$stmt->fetch()) {
            $index=$rows[0];
            $level=$rows[1];
            $caption=$rows[2];
            //$target=$rows[3];
            //$link=$rows[4];
        
            #$main_menu=$main_menu . "<td bgcolor='black'><a href='page.php?id=$index&lg=" . ${this->lg} . "'><span style='color:#ffffff'><b>$caption</b></span></a><span style='color:#ffffff'><b>&nbsp;|&nbsp;</b></span></td>";
            $main_menu=$main_menu . "<td><a href='page.php?id=$index&lg={$this->lg}'><span style='color:#ffffff'><b>$caption</b></span></a><span style='color:#ffffff'><b>&nbsp;|&nbsp;</b></span></td>";
        
            if ($count==0) {
                $default_id=$index;
            }
            $count++;
        }
        $main_menu=substr($main_menu, 0, strlen($main_menu)-23);
        $main_menu.="</tr></table>";
    
        //$stmt->free();

        return array("index"=>$default_id, "menu"=>$main_menu);
    }

    public function createFramedMainMenu($userdb, $color, $text_color, $over_color, $width, $height)
    {
        $main_menu="";
        $sql="";
        $sql=   "select m.pa_id, m.me_level, d.di_{$this->lg}_short, m.me_target, p.pa_filename " .
                "from {$this->db_prefix}menus m, {$this->db_prefix}pages p, {$this->db_prefix}dictionary d " .
                "where m.me_level=1 " .
                "and m.pa_id=p.pa_id " .
                "and m.di_name=d.di_name " .
                "order by m.me_id";
        $cs=connection(CONNECT, $userdb);
        $stmt = $cs->query($sql);
        while ($rows=$stmt->fetch()) {
            $index=$rows[0];
            $level=$rows[1];
            $caption=$rows[2];
            $target=$rows[3];
            $link=$rows[4];
            $main_menu.="<applet code=\"fphover.class\" codebase=\"/{$this->database}/java/\" width=\"$width\" height=\"$height\">\n";
            $main_menu.="\t<param name=\"textcolor\" value=\"$text_color\">\n";
            $main_menu.="\t<param name=\"text\" value=\"$caption\">\n";
            $main_menu.="\t<param name=\"color\" value=\"$color\">\n";
            $main_menu.="\t<param name=\"hovercolor\" value=\"$over_color\">\n";
            $main_menu.="\t<param name=\"effect\" value=\"glow\">\n";
            $main_menu.="\t<param name=\"target\" value=\"page\">\n";
            $main_menu.="\t<param name=\"url\" valuetype=\"ref\" value=\"{$this->lg}/$link?lg={$this->lg}\">\n";
            $main_menu.="</applet>\n\n";
        }
        //$stmt->free();
        return $main_menu;
    }

    public function createSubMenu($database, $id=0, $orientation)
    {
        if ($orientation==SUB_MENU_HORIZONTAL) {
            $sub_menu="";
        } elseif ($orientation==SUB_MENU_VERTICAL) {
            $sub_menu="<table width='100%'>";
        }
    
        $sql=	"select m.me_id, m.me_level, d.di_{$this->lg}_short, m.me_target, p.pa_filename, p.pa_id " .
                "from {$this->db_prefix}menus m, {$this->db_prefix}pages p, {$this->db_prefix}dictionary d " .
                "where m.di_name=d.di_name " .
                "and p.pa_id=m.pa_id " .
                "and m.me_id<>m.pa_id " .
                "and m.me_level>1 " .
                "and m.pa_id=" . $id;
        //and m.me_id<>m.pa_id

        $cs=connection(CONNECT, $database);
        $stmt = $cs->query($sql);
        while ($rows=$stmt->fetch()) {
            $index=$rows[0];
            $level=$rows[1];
            $caption=$rows[2];
            $target=$rows[3];
            $link=$rows[4];
            $page=$rows[5];
            if ($orientation==SUB_MENU_HORIZONTAL) {
                switch ($level) {
            case "2":
                            $sub_menu.="<a href='page.php?id=$index&lg={$this->lg}'><span style='color:#FFFFFF'>$caption</span></a><span style='color:#FFFFFF'>&nbsp;|&nbsp;</span>";
                break;
            case "3":
                            $sub_menu.="<a href='$target?id=$index&lg={$this->lg}'><span style='color:#FFFFFF'>$caption</span></a><span style='color:#FFFFFF'>&nbsp;|&nbsp;</span>";
                break;
            case "4":
                            $sub_menu.="<a href='page.php?id=$page&lg={$this->lg}#$target'><span style='color:#FFFFFF'>$caption</span></a><span style='color:#FFFFFF'>&nbsp;|&nbsp;</span>";
                            //$sub_menu.="<a href='$PHP_SELF#$target'><span style='color:#FFFFFF'>$caption</span></a><span style='color:#FFFFFF'>&nbsp;|&nbsp;</span>";
                break;
                    }
            } elseif ($orientation==SUB_MENU_VERTICAL) {
                switch ($level) {
            case "2":
                            $sub_menu.="<tr><td><a href='page.php?id=$index&lg={$this->lg}'>$caption</a></td></tr>";
                break;
            case "3":
                            $sub_menu.="<tr><td><a href='$target?id=$index&lg={$this->lg}'>$caption</a></td></tr>";
                break;
            case "4":
                            $sub_menu.="<tr><td><a href='page.php?id=$page&lg={$this->lg}#$target'>$caption</a></td></tr>";
                break;
            case "5":
                            $sub_menu.="<tr><td>&nbsp;&nbsp;&nbsp;<a href='page.php?id=$page&lg={$this->lg}#$target'>$caption</a></td></tr>";
                            // no break
            case "6":
                            $sub_menu.="<tr><td><a href='$link' target='_new'>$caption</a></td></tr>";
                break;
                    }
            }
        }
        if ($orientation==SUB_MENU_HORIZONTAL) {
            $sub_menu=substr($sub_menu, 0, strlen($sub_menu)-14);
        } elseif ($orientation==SUB_MENU_VERTICAL) {
            $sub_menu.="</table>";
        }
        //$stmt->free();
        return $sub_menu;
    }

    public function createMenuTree($database, $id=0, $lg="", $orientation)
    {
        if ($orientation==SUB_MENU_HORIZONTAL) {
            $sub_menu="";
        } elseif ($orientation==SUB_MENU_VERTICAL) {
            $sub_menu="<table width='100%'>";
        }

        $sql=   "select m.me_id, m.me_level, d.di_{$this->lg}_short, m.me_target, p.pa_filename, p.pa_id " .
                "from {$this->db_prefix}menus m, {$this->db_prefix}pages p, {$this->db_prefix}dictionary d " .
                "where m.me_level>1 " .
                "and m.pa_id=p.pa_id " .
                "and m.di_name=d.di_name " .
                "and m.me_id=" . $id . " " .
        "order by p.pa_id, m.me_level";

        //echo "$sql<br>";

        $cs=connection(CONNECT, $database);
        $stmt = $cs->query($sql);
        while ($rows=$stmt->fetch()) {
            $page=$rows[5];
        }
        if (!empty($page)) {
            if ($page!=$id) {
                $id=$page;
            }
        }
        
        $sql=   "select m.me_id, m.me_level, d.di_{$this->lg}_short, m.me_target, p.pa_filename, p.pa_id " .
                "from {$this->db_prefix}menus m, pages p, {$this->db_prefix}dictionary d " .
                "where m.me_level=1 " .
                "and m.pa_id=p.pa_id " .
                "and m.di_name=d.di_name " .
        "union " .
            "select m.me_id, m.me_level, d.di_{$this->lg}_short, m.me_target, p.pa_filename, p.pa_id " .
                "from {$this->db_prefix}menus m, {$this->db_prefix}pages p, {$this->db_prefix}dictionary d " .
                "where m.di_name=d.di_name " .
                "and m.me_id<>m.pa_id " .
                "and p.pa_id=m.pa_id " .
                "and m.pa_id=" . $id . " " .
        "order by p.pa_id, m.me_level";
        
        //echo "$sql<br>";

        $stmt = $cs->query($sql);
        while ($rows=$stmt->fetch()) {
            $index=$rows[0];
            $level=$rows[1];
            $caption=$rows[2];
            $target=$rows[3];
            $link=$rows[4];
            $page=$rows[5];
            if ($orientation==SUB_MENU_HORIZONTAL) {
                switch ($level) {
            case "1":
                            $sub_menu.="<a href='page.php?id=$index&lg={$this->lg}'><span style='color:#FFFFFF'>$caption</span></a><span style='color:#FFFFFF'>&nbsp;|&nbsp;</span>";
                break;
            case "2":
                            $sub_menu.="<a href='page.php?id=$index&lg={$this->lg}'><span style='color:#FFFFFF'>$caption</span></a><span style='color:#FFFFFF'>&nbsp;|&nbsp;</span>";
                break;
            case "3":
                            $sub_menu.="<a href='$target?id=$index&lg={$this->lg}'><span style='color:#FFFFFF'>$caption</span></a><span style='color:#FFFFFF'>&nbsp;|&nbsp;</span>";
                break;
            case "4":
                            $sub_menu.="<a href='page.php?id=$page&lg={$this->lg}#$target'><span style='color:#FFFFFF'>$caption</span></a><span style='color:#FFFFFF'>&nbsp;|&nbsp;</span>";
                            //$sub_menu.="<a href='$PHP_SELF#$target'><span style='color:#FFFFFF'>$caption</span></a><span style='color:#FFFFFF'>&nbsp;|&nbsp;</span>";
                break;
                    }
            } elseif ($orientation==SUB_MENU_VERTICAL) {
                switch ($level) {
            case "1":
                            $sub_menu.="<tr><td><a href='page.php?id=$index&lg={$this->lg}'>$caption</a></td></tr>";
                break;
            case "2":
                            $sub_menu.="<tr><td>&nbsp;&nbsp;<a href='page.php?id=$index&lg={$this->lg}'>$caption</a></td></tr>";
                break;
            case "3":
                            $sub_menu.="<tr><td>&nbsp;&nbsp;<a href='$target?id=$index&lg={$this->lg}'>$caption</a></td></tr>";
                break;
            case "4":
                            $sub_menu.="<tr><td>&nbsp;&nbsp;<a href='page.php?id=$page&lg={$this->lg}#$target'>$caption</a></td></tr>";
                break;
            case "5":
                            $sub_menu.="<tr><td>&nbsp;&nbsp;&nbsp;&nbsp;<a href='page.php?id=$page&lg={$this->lg}#$target'>$caption</a></td></tr>";
                            // no break
            case "6":
                            $sub_menu.="<tr><td>&nbsp;&nbsp;<a href='$link' target='_new'>$caption</a></td></tr>";
                break;
                    }
            }
        }
        if ($orientation==SUB_MENU_HORIZONTAL) {
            $sub_menu=substr($sub_menu, 0, strlen($sub_menu)-14);
        } elseif ($orientation==SUB_MENU_VERTICAL) {
            $sub_menu.="</table>";
        }
        //$stmt->free();
        return $sub_menu;
    }

    public function retrievePageById($database, $id=0, $lg="")
    {
        $title="";
        $page="";
        $sql = "";
        $sql=   "select d.di_name, p.pa_filename, m.me_charset, d.di_{$this->lg}_short, d.di_{$this->lg}_long " .
                "from {$this->db_prefix}pages p, {$this->db_prefix}menus m, {$this->db_prefix}dictionary d " .
                "where m.di_name=d.di_name " .
                "and p.di_name=m.di_name " .
                "and p.pa_id=$id";
        self::getLogger()->debug($sql, __FILE__, __LINE__);

        //echo $sql . "<br>";
        //"and p.pa_id=m.me_id " .
        $cs=connection(CONNECT, $database);
        $stmt = $cs->query($sql);
        $rows=$stmt->fetch(\PDO::FETCH_ASSOC);
        $index = $rows["di_name"];
        $page = $rows["pa_filename"];
        $title = $rows["di_".$this->lg."_long"];
        $charset = $rows["me_charset"];
        if ($title=="") {
            $title = $rows["di_".$this->lg."_short"];
        }
        self::getLogger()->debug($rows, __FILE__, __LINE__);
    
        $request="";
        $p=strpos($page, "?", 0);
        if ($p>-1) {
            $request="&".substr($page, $p+1, strlen($page)-$p);
            $page=substr($page, 0, $p);
        }
    
        $title_page=array("index"=>$index, "title"=>$title, "page"=>$page, "request"=>$request, "charset"=>$charset);

        /*
        $filename=${this->lg}."/".$page;

        if (!file_exists($filename)) {
            copy("includes/fichier_vide.php", $filename);
        }
        */
        return $title_page;
    }

    public function retrievePageByMenuId($database, $id=0, $lg="")
    {
        $title="";
        $page="";
        $sql = "";
        $sql=   "select d.di_name, p.pa_filename, m.me_charset, d.di_{$this->lg}_short, d.di_{$this->lg}_long " .
                "from {$this->db_prefix}pages p, {$this->db_prefix}menus m, {$this->db_prefix}dictionary d " .
                "where m.di_name=d.di_name " .
                "and p.di_name=m.di_name " .
                "and m.me_id=$id";
        // self::getLogger()->debug($sql, __FILE__, __LINE__);

//        echo $sql . "<br>";
        //"and p.pa_id=m.me_id " .
        $cs=connection(CONNECT, $database);
        $stmt = $cs->query($sql);
        $rows=$stmt->fetch(\PDO::FETCH_ASSOC);
        $index = $rows["di_name"];
        $page = $rows["pa_filename"];
        $title = $rows["di_".$this->lg."_long"];
        $charset = $rows["me_charset"];
        if ($title=="") {
            $title = $rows["di_".$this->lg."_short"];
        }
    
        self::getLogger()->debug($rows, __FILE__, __LINE__);
    
        $request="";
        $p=strpos($page, "?", 0);
        if ($p>-1) {
            $request="&".substr($page, $p+1, strlen($page)-$p);
            $page=substr($page, 0, $p);
        }
    
        $title_page=array("index"=>$index, "title"=>$title, "page"=>$page, "request"=>$request, "charset"=>$charset);

        /*
        $filename=${this->lg}."/".$page;

        if (!file_exists($filename)) {
            copy("includes/fichier_vide.php", $filename);
        }
        */
        return $title_page;
    }

    public function retrievePageByDictionaryId($database, $di="", $lg="")
    {
        $title="";
        $page="";
        $sql = "";
        $sql=   "select m.me_id, p.pa_filename, m.me_charset, d.di_{$this->lg}_short, d.di_{$this->lg}_long " .
                "from {$this->db_prefix}pages p, {$this->db_prefix}menus m, {$this->db_prefix}dictionary d " .
                "where m.di_name=d.di_name " .
                "and p.di_name=m.di_name ".
                "and d.di_name='$di'";
                
        self::getLogger()->debug($sql, __FILE__, __LINE__);

        $cs=connection(CONNECT, $database);
        $stmt = $cs->query($sql);
        $rows=$stmt->fetch(\PDO::FETCH_ASSOC);
        $index = $rows["me_id"];
        $page = $rows["pa_filename"];
        $charset = $rows["me_charset"];
        $title = $rows["di_".$this->lg."_long"];
        if ($title=="") {
            $title = $rows["di_".$this->lg."_short"];
        }

        self::getLogger()->debug($rows, __FILE__, __LINE__);

        $request="";
        $p=strpos($page, "?", 0);
        if ($p>-1) {
            $request="&".substr($page, $p+1, strlen($page)-$p);
            $page=substr($page, 0, $p);
        }
    
        $title_page=array("index"=>$index, "title"=>$title, "page"=>$page, "request"=>$request, "charset"=>$charset);
        
        return $title_page;
    }

    public function getTabIdes($database)
    {
        self::getLogger()->dump(__FILE__ . ':' . __METHOD__ . ':' . __LINE__ . ':DATABASE', $database);
    
        $sql="select m.me_id, m.di_name ";
        $sql.="from {$this->db_prefix}menus m ";
        $sql.="where m.di_name like 'mk%' ";
        $sql.="order by m.me_id";
        self::getLogger()->dump(__FILE__ . ':' . __METHOD__ . ':' . __LINE__ . ':SQL', $sql);
    
        $cs=connection(CONNECT, $database);
    
        $stmt = $cs->query($sql);
        $tab_ides=(array) null;
        $i=0;
        while ($rows=$stmt->fetch()) {
            $tab_ides[$rows[0]]=$rows[1];
            $i++;
        }
    
        self::getLogger()->dump(__FILE__ . ':' . __METHOD__ . ':' . __LINE__ . ':RES', $tab_ides);

        return $tab_ides;
    }

}
