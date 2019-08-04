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

define("BLOCK_LEFT_COLUMN", 1);
define("BLOCK_RIGHT_COLUMN", 3);
define("MEMBER_LOGGED_OUT", 0);
define("MEMBER_LOGGED_IN", 1);
define("UNSUBSCRIBE", "unsubscribe");
define("SUBSCRIBE", "subscribe");

class Blocks extends Base
{
	public $dyna_bset_counter;
	public $_design = null;

	public function __construct($lg, $db_prefix) {
		parent::__construct($lg, $db_prefix);
		$this->_design = new \Puzzle\Design();
	}

    public function createBlockSet($userdb, $column, $id, $lg, $colors)
    {
        $cs=connection(CONNECT, $userdb);
    
        $block_set="";
        $sql="select bl_id from {$this->db_prefix}blocks where bl_column=$column and bl_type=\"menu\" order by bl_id";
        //echo "$sql<br>";
        $stmt = $cs->query($sql);
        while ($rows=$stmt->fetch()) {
            $index=$rows[0];
            $block_set.=createBlock($userdb, $index, $id, $lg, $colors);
        }
    
        return $block_set;
    }

    public function createEnhancedBlockSet($userdb, $column, $id, $lg, $colors)
    {

        $cs=connection(CONNECT, $userdb);
    
        global $dyna_bset_counter, $panel_colors, $diarydb;
        $border_color=$panel_colors["border_color"];
    
        $block_set="";
        $sql="select bl_id, bl_type from {$this->db_prefix}blocks where bl_column=$column order by bl_id";
        // self::getLogger()->debug($sql, __FILE__, __LINE__);

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
                $dyna_block_set = $this->_design->tableShadow($dbs_name, $dyna_block_set);
                $first_block=true;
                $dbs_name="";
                $block_set.=$dyna_block_set;
            }
        
            if ($bl_type=="static") {
                $block_set .= $this->createBlock($userdb, $index, $id, $lg, $colors);
            } elseif ($bl_type=="dynamic") {
                if ($first_block) {
                    $dyna_bset_counter++;
                    $dbs_name="dyna_bset$dyna_bset_counter";
                    $dyna_block_set="<table id=\"$dbs_name\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\" width=\"100\" bordercolor=\"$border_color\"><tr><td>\n";
                    $first_block=false;
                }
                $dyna_block_set .= $this->createCollapseableBlock($dbs_name, $index, $colors);
            } elseif ($bl_type=="members") {
                $logout=getArgument("logout");
                //$panel_colors=getArgument("panel_colors");
                $block_set .= $this->createMembersBlock($userdb, $logout, "members", $id, $lg, $panel_colors);
            } elseif ($bl_type=="newsletter") {
                //$panel_colors=getArgument("panel_colors");
                global $panel_colors;
                $block_set .= $this->createNewsletterBlock($userdb, "newsltr", $id, $lg, $panel_colors);
            } elseif ($bl_type=="diary") {
                global $diary_colors;
                $date=getArgument("date");
                //$block_set.=createDiaryBlock($date, $id, $lg, $diary_colors);
            }
        
            $old_bl_type=$bl_type;
        }
    
        //$js="<script language='text/javascript'>alert(\"Après : old_bl_type='$old_bl_type'; bl_type='$bl_type';\");</script>";
        //echo $js;
        if ($old_bl_type=="dynamic") {
            $dyna_block_set.="</td></tr></table>\n";
            $dyna_block_set = $this->_design->tableShadow($dbs_name, $dyna_block_set);
            $first_block=true;
            $dbs_name="";
            $block_set.=$dyna_block_set;
        }
        
        //print_r($dyna_block_set);
        return $block_set;
    }


    public function createBlock($userdb, $block_num, $id, $lg, $colors)
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

        $cs=connection(CONNECT, $userdb);
        $sql=	"select d.di_".$lg."_short ".
        "from {$this->db_prefix}blocks b, {$this->db_prefix}dictionary d ".
        "where  b.di_id=d.di_id ".
        "and b.bl_id=$block_num";
        self::getLogger()->debug($sql, __FILE__, __LINE__);
        
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
        self::getLogger()->debug($sql, __FILE__, __LINE__);
        
        $sub_menu="";
        $count=0;
        $zero=0;
        $index=0;
    
        $stmt = $cs->query($sql);
        while ($rows=$stmt->fetch()) {
            $index=$rows[0];
            $level=$rows[1];
            $block=$rows[2];
            $caption=$rows[3];
            $target=$rows[4];
            $link=$rows[5];
            $page=$rows[6];
            //echo "$caption;$link<br>";
        
            $url="page.html?id=$index&lg=$lg";
            if (substr($link, 0, 7)=="http://") {
                $target=" target=\"_new\"";
                $url=$link;
            }
        
            $sub_menu.="<tr id=\"$index$count\" onMouseOver=\"setRowColor(this, hlBackColor, hlTextColor);\" onMouseOut=\"setBackRowColor(this);\"><td><a href=\"$url\"$target><span id=\"caption_$index$count$zero\" style=\"color:$fore_color\">$caption</span></a></td></tr>\n";
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

        $block = $this->_design->tableShadow($table_name, $block);

        return $block;
    }

    public function createCollapseableBlock($block_skin_name, $block_num, $colors)
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

        $id=getArgument("id");
        $lg=getArgument("lg");
        $userdb=getArgument("database");

        $cs=connection(CONNECT, $userdb);
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
            $sub_menu.="<tr id=\"$index$count\" onMouseOver=\"setRowColor(this, hlBackColor, hlTextColor);\" onMouseOut=\"setBackRowColor(this);\"><td><a href=\"page.html?id=$index&lg=" . $lg . "\"><span id=\"caption_$index$count$zero\" style=\"color:$fore_color\">$caption</span></a></td></tr>\n";
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

    public function createMembersBlock($userdb, $logout, $di_id, $id, $lg, $colors)
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

        $cs=connection(CONNECT, $userdb);
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
            $connection_link="\t<a href=\"page.html?di=ed_membe&lg=$lg&action=Ajouter\">Devenir membre >></a>\n";
        } elseif ($status==MEMBER_LOGGED_IN) {
            $connection_link="\t<a href=\"page.html?id=$id&lg=$lg&logout=1\">Déconnexion</a>\n";
        }

        if ($apps_login) {
            $connection_link="";
        }
        
        if ($logout) {
            session_destroy();
            $js=	"<script language=JavaScript>".
            "window.location.href='page.html?id=1&lg=$lg';".
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

        $block = $this->_design->tableShadow($table_name, $block);
    
        return $block;
    }

    public function getAuthentication($login)
    {
        global $db_prefix;

        $authenticate=false;
        if ($login!="") {
            if (!isset($_SESSION["ses_status"])) {
                $sstatus=MEMBER_LOGGED_OUT;
            } else {
                $sstatus=$_SESSION["ses_status"];
            }
            
            if (!isset($_SESSION["ses_login"])) {
                $slogin="";
            } else {
                $slogin=$_SESSION["ses_login"];
            }
    
            if (!isset($_SESSION["ses_pass"])) {
                $spass="";
            } else {
                $spass=$_SESSION["ses_pass"];
            }

            $mlogin="";
            $mpass="";

            $cs=connection(CONNECT, $userdb);
            $sql="select mbr_id, mbr_nom, mbr_ident, mbr_mpasse from {$this->db_prefix}members where mbr_ident='$login'";
            $stmt = $cs->query($sql);
            if ($rows=$stmt->fetch(PDO::FETCH_ASSOC)) {
                $index=$rows["mbr_id"];
                $nom=$rows["mbr_nom"];
                $mlogin=$rows["mbr_ident"];
                $mpass=$rows["mbr_mpasse"];
            
                //echo "login='$mlogin'; passwd='$mpass'; group='$mgroup';<br>";
            }
            //$stmt->free();
    
            $login_ok=($slogin==$mlogin);
            $passwd_ok=($spass==$mpass);
        
            $authenticate=($login_ok && $passwd_ok && $status==MEMBER_LOGGED_IN);

            if (!$authenticate) {
                if (!$login_ok && $passwd_ok) {
                    $msg="Identifiant invalide";
                } elseif ($login_ok && !$passwd_ok) {
                    $msg="Mot de passe invalide";
                } elseif (!$login_ok && !$passwd_ok) {
                    $msg="Identifiant et mot de passe invalides";
                }
            
                $_SESSION["javascript"].="\talert(\"$msg\");\n";
            }
        }

        return $authenticate;
    }


    public function performMembersIdent($login, $pass, $submit)
    {
        global $userdb, $db_prefix;
    
        $cs=connection(CONNECT, $userdb);
    
        if (!isset($_SESSION["ses_status"])) {
            $sstatus=MEMBER_LOGGED_OUT;
        } else {
            $sstatus=$_SESSION["ses_status"];
        }
        
        if ($submit=="OK" && $status==MEMBER_LOGGED_OUT) {
            $event='onRun';
        } else {
            $event='onLoad';
        }
        
        $js="";

        if ($event=="onRun") {
            $sql="select mbr_id, mbr_nom, mbr_ident, mbr_mpasse from {$this->db_prefix}members where mbr_ident='$login'";
            $stmt = $cs->query($sql);
            if ($rows=$stmt->fetch(PDO::FETCH_ASSOC)) {
                $index=$rows["mbr_id"];
                $nom=$rows["mbr_nom"];
                $ident=$rows["mbr_ident"];
                $mpasse=$rows["mbr_mpasse"];
            }
        
            $login_ok=($login==$ident);
            $passwd_ok=($pass==$mpasse);
        
            $logged_in=($login_ok) && ($passwd_ok);

            //$js="alert(\"'$ident'='$login'; '$mpasse'='$pass';\");";
    
            if ($logged_in) {
                $_SESSION["ses_status"]=MEMBER_LOGGED_IN;
                $_SESSION["ses_id"]=$index;
                $_SESSION["ses_nom"]=$nom;
                $_SESSION["ses_login"]=$ident;
                $_SESSION["ses_pass"]=$mpasse;
            
                $js="window.location.href='".$_SERVER["REQUEST_URI"]."&mbr_id=$index';";
            } elseif (!$logged_in) {
                $msg="L'accès à l'espace membres nécessite un identifiant et un mot de passe.";
                if (!$login_ok && $passwd_ok) {
                    $msg="Identifiant invalide";
                } elseif ($login_ok && !$passwd_ok) {
                    $msg="Mot de passe invalide";
                } elseif (!$login_ok && !$passwd_ok) {
                    $msg="Identifiant et mot de passe invalides";
                }
            
                //$_SESSION["ses_status"]=MEMBER_LOGGED_OUT;
                $js="alert(\"$msg\");";
            }
        }

        $js="<script language=\"JavaScript\">\t\n$js\n</script>";
    
        //$_SESSION["javascript"].=$js;
        return $js;
    }

    public function createNewsletterBlock($userdb, $di_id, $id, $lg, $colors)
    {
        global $PHP_SELF, $db_prefix;
    
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

        $cs=connection(CONNECT, $userdb);
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
        
        $block = $this->_design->tableShadow($table_name, $block);
    
        return $block;
    }

    public function performNewsletterSubscription($email, $radios, $submit)
    {
        global $db_prefix;
    
        //if(isset($radios)) $chklist=implode("', '", $radios);
        //echo "e-mail='$email'; subscribe='".$chklist."'; submit='$submit'<br>";
        global $userdb;
    
        $cs=connection(CONNECT, $userdb);
        $event="onLoad";
        
        if (is_array($radios)) {
            $action=$radios[0];
        }

        $sql="select sub_email from {$this->db_prefix}subscribers where sub_email='$email'";
        $stmt = $cs->query($sql);
        $email_exists=($result->num_rows>0);

        $p1=strpos($email, "@");
        $p2=strrpos($email, ".");
    
        $invalid_email=($p1>$p2) || ($p1==0) || ($p2==0) || ($p1==strlen($email)-1) || ($p2==strlen($email)-1);
    
        $event="onLoad";
        $js="";

        if ($submit=="OK" && !$invalid_email) {
            if ($email_exists && $action==SUBSCRIBE) {
                $js="alert(\"Cette e-mail est déjà enregistrée.\");";
            } elseif ($email_exists && $action==UNSUBSCRIBE) {
                $event="onRun";
            } elseif (!$email_exists && $action==SUBSCRIBE) {
                $event="onRun";
            } elseif (!$email_exists && $action==UNSUBSCRIBE) {
                $js="alert(\"Cette e-mail n'est pas enregistrée.\");";
            } elseif ($email=="") {
                $js="alert(\"Vous devez entrer une adresse e-mail pour recevoir la lettre d'informations.\");";
            }
        } elseif ($submit=="OK" && $invalid_email) {
            $js="alert(\"Cette e-mail n'est pas valide.\");";
        }
    
        if ($event=="onRun") {
            switch ($action) {
        case SUBSCRIBE:
            $sql="insert into subscribers (".
                "sub_email".
            ") values (".
                "'$email'".
            ")";
            $stmt = $cs->query($sql);
            $js="alert(\"Votre inscription a bien été prise en compte\\n".
                "Vous recevrez régulièrement une lettre d'informations.\");";
        break;
        case UNSUBSCRIBE:
            $sql="delete from subscribers where sub_email='$email'";
            $stmt = $cs->query($sql);
            $js="alert(\"Dorénavant vous ne recevrez plus de lettre d'informations.\");";
        break;
        }
        }
        $js="<script language=\"JavaScript\">\t\n$js\n</script>";

        return $js;
    }

    /* OBSOLETE Calendar block, no more supported
    function create_calendarBlock($date, $id, $lg, $colors) {
        if(empty($colors)) {
            global $panel_colors;
            $color=$panel_colors;
        }
    
        if(!empty($colors)) {
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
    
        $calendar=create_calendar_control("", $colors);
    
        $table_name="calendar";
        $block=	"<table id=\"$table_name\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\" width=\"100\" bordercolor=\"$border_color\"><tr><td>\n".
            "<table border=\"0\" cellspacing=\"0\" cellpadding=\"0\" width=\"100\" bordercolor=\"$border_color\">\n".
            "<tr height=\"4\" valign=\"top\"> \n".
                "\t<td width=\"96\" bgcolor=\"$back_color\" align=\"center\">\n".
                $calendar.
                "\t</td>\n".
            "</tr>\n".
            "</table>\n".
            "</td></tr></table>\n";
    
        $block = $this->_design->tableShadow($table_name, $block);
    
        return $block;
    }
    */

    public function createDiaryBlock($date, $id, $lg, $colors)
    {
        global $diary_colors;
        /*
        if(!is_array($colors)) {
            $colors=$diary_colors;
        }*/
    
        if (isset($diary_colors)) {
            $border_color=$diary_colors["border_color"];
            $caption_color=$diary_colors["caption_color"];
            $back_color=$diary_colors["back_color"];
            $fore_color=$diary_colors["fore_color"];
            $hl_back_color=$diary_colors["hl_back_color"];
            $hl_text_color=$diary_colors["hl_text_color"];
        } else {
            $border_color="black";
            $caption_color="white";
            $back_color="white";
            $fore_color="black";
            $hl_back_color="grey";
            $hl_text_color="white";
        }

        $diary=createDiaryControl("", $colors);
    
        $table_name="diary";
        $block=	"<table id=\"$table_name\" border=\"1\" cellspacing=\"0\" cellpadding=\"0\" width=\"100\" bordercolor=\"$border_color\">\n".
        "<tr height=\"4\" valign=\"top\"> \n".
            "\t<td width=\"100\" bgcolor=\"$back_color\" align=\"center\">\n".
            $diary.
            "\t</td>\n".
        "</tr>\n".
        "</table>\n";

        $block = $this->_design->tableShadow($table_name, $block);
    
        return $block;
    }
}
