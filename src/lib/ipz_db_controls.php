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

namespace Puzzle\Data;

use Phink\Registry\TRegistry;

//$hostname=getHttpRoot()."/";
class Controls extends \Puzzle\Base
{
    public function getJsArrayFromQuery($name, $sql, $cs)
    {
        $jsArray="<script language='JavaScript'>\n";

        $stmt = $cs->query($sql);
        $n=$stmt->getRowCount();
        $i=0;
        $jsArray.="\tvar $name=new Array($n);\n";
        $jsArray.="\t$name"."[$i]=new Array(\"0\", \"\");"."\n";
        while ($rows=$stmt->fetch()) {
            $i++;
            $jsArray.="\t$name"."[$i]=new Array(\"".implode("\",\"", array_unique($rows))."\");"."\n";
        }
    
        $jsArray.="</script>\n";

        return $jsArray;
    }

    public function getFieldsFromSelectClause($sql)
    {
        $select="select";
        $from="from";
    
        $result=array();
    
        $sql=strtolower($sql);
        $l=strlen($select)+1;
        $p=strpos($sql, $from);
        $fields=substr($sql, $l, $p-$l);

        $fields=explode(",", $fields);
        $i=0;
        foreach ($fields as $value) {
            $afields=explode(" ", trim($value));
            // $s=sizeof($afields)-1;
            //$result[$i]="'".trim($afields[$s])."'";
            $result[$i]=trim($afields[0]);
            $i++;
        }

        return $result;
    }

    public function insertTest($sql, $left, $right, $operator, $right_is_quoted, $criterion = "")
    {
        $where="where";
        $having="having";
        $groupby="group by";
        $orderby="order by";
        $limit="limit";
    
        if ($criterion!="*") {
            $criterion.="%";
    
            $clause=$where;
            $p=strpos($sql, $where);
    
            //echo "sql='$sql'<p>";
    
            if ($right_is_quoted) {
                $right="'$right'";
            }
        
            $operator=trim($operator);
            if ($operator=="like") {
                $operator=" $operator ";
            }
        
            if ($p>0) {
                //echo "clause where trouvée à la position $p<p>";
                $test="where $left$operator$right and";
            } else {
                //echo "clause $clause non trouvée<p>";
                $clause=$having;
                $p=strpos($sql, $clause);
            
                if ($p==0) {
                    //echo "clause $clause non trouvée<p>";
                    $clause=$groupby;
                    $p=strpos($sql, $clause);
                }
            
                if ($p==0) {
                    //echo "clause $clause non trouvée<p>";
                    $clause=$orderby;
                    $p=strpos($sql, $clause);
                }
            
                if ($p==0) {
                    //echo "clause $clause non trouvée<p>";
                    $clause=$limit;
                    $p=strpos($sql, $clause);
                }
            
                if ($p>0) {
                    //echo "clause $clause trouvée à la position $p<p>";
                } else {
                    //echo "aucune clause trouvée<p>";
                    $p=strlen($sql);
                    $clause="";
                }
                
                $test="where $left$operator$right $clause";
            }
    
            if ($clause=="") {
                $sql1=$sql." ";
                $sql2="";
            } else {
                $lw=strlen($clause);
                $sql1=substr($sql, 0, $p);
                $sql2=substr($sql, $p+$lw, strlen($sql)-$p-$lw);
            }
        
            /*
            echo "sql1='$sql1'<p>";
            echo "sql2='$sql2'<p>";

            echo "clause like ajoutée : \"$like\"<p>";
            */
    
            $sql=trim($sql1.$test.$sql2);

            //echo "sql='$sql'<p>";
        }

        return $sql;
    }

    public function insertLikeClause($sql, $filter, $criterion)
    {
        $where="where";
        $having="having";
        $groupby="group by";
        $orderby="order by";
        $limit="limit";
    
        if ($criterion!="*") {
            $criterion.="%";
    
            $clause=$where;
            $p=strpos($sql, $where);
    
            //echo "sql='$sql'<p>";
    
            if ($p>0) {
                //echo "clause where trouvée à la position $p<p>";
                $like="where $filter like '$criterion' and";
            } else {
                //echo "clause $clause non trouvée<p>";
                $clause=$having;
                $p=strpos($sql, $clause);
            
                if ($p==0) {
                    //echo "clause $clause non trouvée<p>";
                    $clause=$groupby;
                    $p=strpos($sql, $clause);
                }
            
                if ($p==0) {
                    //echo "clause $clause non trouvée<p>";
                    $clause=$orderby;
                    $p=strpos($sql, $clause);
                }
            
                if ($p==0) {
                    //echo "clause $clause non trouvée<p>";
                    $clause=$limit;
                    $p=strpos($sql, $clause);
                }
            
                if ($p>0) {
                    //echo "clause $clause trouvée à la position $p<p>";
                } else {
                    //echo "aucune clause trouvée<p>";
                    $p=strlen($sql);
                    $clause="";
                }
                
    
                $like="where $filter like '$criterion' $clause";
            }
    
            if ($clause=="") {
                $sql1=$sql." ";
                $sql2="";
            } else {
                $lw=strlen($clause);
                $sql1=substr($sql, 0, $p);
                $sql2=substr($sql, $p+$lw, strlen($sql)-$p-$lw);
            }
        
            /*
            echo "sql1='$sql1'<p>";
            echo "sql2='$sql2'<p>";

            echo "clause like ajoutée : \"$like\"<p>";
            */
    
            $sql=trim($sql1.$like.$sql2);

            //echo "sql='$sql'<p>";
        }

        return $sql;
    }

    public function getRecordset($sql, $cs)
    {
        $recordset=(array) null;
        $names=(array) null;
        $types=(array) null;
        $values=(array) null;

        $stmt = $cs->query($sql);
        $nfields = $stmt->columnConut();
        //$nrows=$stmt->getRowCount();
    
        for ($i=0;$i<$nfields;$i++) {
            $names[$i] = $stmt->getFieldName($i);
            $types[$i] = $stmt->getFieldType($i);
        }
    
        $i=0;
        while (($rows=$stmt->fetch()) && ($i<256)) {
            $values[$i]=array_unique($rows);
            $i++;
        }
    
        return $recordset=array("names"=>$names, "values"=>$values, "types"=>$types);
    }

    public function createPagerControl($userdb="", $page_link="", $sql_query="", $id="", $lg="", $caption, $sr=0, $step=5, $pc=0, $comp_url)
    {
        /*
            Desciption des parametres :
        
            $userdb : Base de données sur laquelle on se connecte.
            $table : table de la base sur laquelle on récupère le nombre d'enregistrements
            $sql : requête SQL complète ou uniquement la clause ORDER BY de la requête SQL créée automatiquement à partir de $table
            $id : index de menu de la page qui utilise la fonction
            $lg : langue choisie pour afficher la page qui utilise la fonction
            $caption : étiquette du pager qui caractérise le type d'élément paginé (news, article, etc.)
            $sr (starting at record) : numéro d'enregistrement où commence la pagination
            $step : nombre d'éléments affichés par page
            $pc (pager count) : nombre d'éléments à paginer
            $comp_url : utile pour les valeur de formulaire à reporter sur la pagination
        
            La fonction renvoie un tableau à deux éléments nominatifs : pager_ctrl et sql_query
        */
        global $img, $pc, $lg;

        $table="";
        $sql_query=strtolower(trim($sql_query));
        $p=strpos($sql_query, " ");
        $sql_clause=substr($sql_query, 0, $p);
        //echo "SQL Clause='$sql_clause'<br>";
    
        $cs=connection(CONNECT, $userdb);

        if (!isset($step)) {
            $step=5;
        }
        if (!isset($sr)) {
            $sr=0;
        }
        if ($pc==0) {
            if ($sql_clause=="select" || $sql_clause=="show") {
                //$p=strpos($sql_query, "from");
                //$sql="select count(*) ".substr($sql_query, $p, strlen($sql_query)-$p);
                $sql=$sql_query;
            } else {
                $sql="select * from $table";
            }
        
            //echo "SQL='$sql'<br>";
            //echo "Database='$userdb'<br>";
            $stmt = $cs->query($sql);
            //$rows=$stmt->fetch();
            //$pc=$rows[0];
            $pc=$stmt->getRowCount();
            //echo "Count='$pc'<br>";
        }
        $min_sr=0;
        $max_sr=round($pc/$step)*$step;
        if ($max_sr>$pc) {
            $max_sr-=$step;
        }
    
        $pages_num=$max_sr/$step+1;
        $current_page=$sr/$step+1;
        $previous=$sr-$step;
        if ($previous<=0) {
            $previous=0;
        }
        $next=$sr+$step;
        if ($next>=$pc) {
            $next=$max_sr;
        }
    
        $on_click="";
    
        if ($page_link=="") {
            $page_link="page.html";
        }
        /*elseif($page_link!="") {
            if(substr($page_link, 0, 1)=="/") {
                $page_uri=getHttpRoot().$page_link;
            } else {
                $page_uri=$page_link;
            }
        }*/
        if (isNum($id)) {
            $page_uri="$page_link?id=$id&lg=$lg";
        } else {
            $page_uri="$page_link?di=$id&lg=$lg";
        }
        
        if (strpos($page_uri, "?")>0) {
            $page_uri.="&";
        }
    
        //"<tr><td height='1' width='100%' bgcolor='black'></td></tr>\n".
        $pager_ctrl=	"<table border='0' cellspacing='0' cellpadding='0'>\n".
            "<tr>\n".
                "<td width='100%' align='center' valign='bottom'>\n".
                    "<a href='".$page_uri."sr=$min_sr&pc=$pc$comp_url'>\n".
                    "<img src='$img/scroll/leftLimit_0.gif' valign='top' border='0'></a>\n".
                    "<a href='".$page_uri."sr=$previous&pc=$pc$comp_url'>\n".
                    "<img src='$img/scroll/fastLeft_0.gif' valign='top' border='0'></a>\n".
                    "$pc $caption - Page $current_page/$pages_num\n".
                    "<a href='".$page_uri."sr=$next&pc=$pc$comp_url'>\n".
                    "<img src='$img/scroll/fastRight_0.gif' valign='top' border='0'></a>\n".
                    "<a href='".$page_uri."sr=$max_sr&pc=$pc$comp_url'>\n".
                    "<img src='$img/scroll/rightLimit_0.gif' valign='top' border='0'></a>\n".
                "</td>\n".
            "</tr>\n".
            "</table>\n";

        if ($sql_clause=="select") {
            $sql_query = $sql_query." limit $sr,$step";
        } elseif ($sql_clause=="show") {
            $sql_query = $sql_query;
        } elseif ($sql_clause=="order") {
            $sql_query = "select * from $table $sql_query limit $sr,$step";
        } else {
            $sql_query = "select * from $table limit $sr,$step";
        }
    
        $pager=array("pager_ctrl"=>$pager_ctrl, "sql_query"=>$sql_query);
    
        return $pager;
    }

    public function createEnhancedPagerControl($page_link="", $sql_query="", $id="", $lg="", $caption, $sr=0, $step=5, $pc=0, $comp_url, $cs)
    {
        /*
            Desciption des parametres :
        
            $userdb : Base de données sur laquelle on se connecte.
            $table : table de la base sur laquelle on récupère le nombre d'enregistrements
            $sql : requête SQL complète ou uniquement la clause ORDER BY de la requête SQL créée automatiquement à partir de $table
            $id : index de menu de la page qui utilise la fonction
            $lg : langue choisie pour afficher la page qui utilise la fonction
            $caption : étiquette du pager qui caractérise le type d'élément paginé (news, article, etc.)
            $sr (starting at record) : numéro d'enregistrement où commence la pagination
            $step : nombre d'éléments affichés par page
            $pc (pager count) : nombre d'éléments à paginer
            $comp_url : utile pour les valeur de formulaire à reporter sur la pagination
        
        */
        global $img, $pc, $lg;

        $sql_query=strtolower(trim($sql_query));
        $p=strpos($sql_query, " ");
        $sql_clause=substr($sql_query, 0, $p);
    
        //$sql_query=str_replace("\'", "'", $sql_query);
        //$sql_query=str_replace(chr(92), "", $sql_query);
        //echo "SQL query='$sql_query'<br>";

        if (!isset($step)) {
            $step=5;
        }
        if (!isset($sr)) {
            $sr=0;
        }
        if ($pc==0) {
            if ($sql_clause=="select" || $sql_clause=="show") {
                //$p=strpos($sql_query, "from");
                //$sql="select count(*) ".substr($sql_query, $p, strlen($sql_query)-$p);
                $sql=$sql_query;
            } else {
                $sql="select * from $table";
            }
        
            //echo "SQL='$sql'<br>";
            //echo "UserDB='$userdb'<br>";
            //echo "Database='$database'<br>";
            $stmt = $cs->query($sql);
            //$rows=$stmt->fetch();
            //$pc=$rows[0];
            $pc=$stmt->getRowCount();
            //echo "Count='$pc'<br>";
        }
        $min_sr=0;
        $max_sr=round($pc/$step)*$step;
        if ($max_sr>=$pc) {
            $max_sr-=$step;
        }
    
        $pages_num=$max_sr/$step+1;
        $current_page=$sr/$step+1;
        $previous=$sr-$step;
        if ($previous<=0) {
            $previous=0;
        }
        $next=$sr+$step;
        if ($next>=$pc) {
            $next=$max_sr;
        }
    
        $on_click="";
    
        if ($page_link=="") {
            $page_link="page.html";
        }
        /*elseif($page_link!="") {
            if(substr($page_link, 0, 1)=="/") {
                $page_uri=getHttpRoot().$page_link;
            } else {
                $page_uri=$page_link;
            }
        }*/
        if (isNum($id)) {
            $page_uri="$page_link?id=$id&lg=$lg";
        } else {
            $page_uri="$page_link?di=$id&lg=$lg";
        }
        
        if (strpos($page_uri, "?")>0) {
            $page_uri.="&";
        }
    
        //"<tr><td height='1' width='100%' bgcolor='black'></td></tr>\n".
        $pager_ctrl=	"<table border='0' cellspacing='0' cellpadding='0'>\n".
            "<tr>\n".
                "<td width='100%' align='center' valign='bottom'>\n".
                    "<a href='".$page_uri."sr=$min_sr&pc=$pc$comp_url'>\n".
                    "<img src='$img/scroll/leftLimit_0.gif' valign='top' border='0'></a>\n".
                    "<a href='".$page_uri."sr=$previous&pc=$pc$comp_url'>\n".
                    "<img src='$img/scroll/fastLeft_0.gif' valign='top' border='0'></a>\n".
                    "$pc $caption - Page $current_page/$pages_num\n".
                    "<a href='".$page_uri."sr=$next&pc=$pc$comp_url'>\n".
                    "<img src='$img/scroll/fastRight_0.gif' valign='top' border='0'></a>\n".
                    "<a href='".$page_uri."sr=$max_sr&pc=$pc$comp_url'>\n".
                    "<img src='$img/scroll/rightLimit_0.gif' valign='top' border='0'></a>\n".
                "</td>\n".
            "</tr>\n".
            "</table>\n";

        if ($sql_clause=="select") {
            $sql_query = $sql_query." limit $sr,$step";
        } elseif ($sql_clause=="show") {
            $sql_query = $sql_query;
        } elseif ($sql_clause=="order") {
            $sql_query = "select * from $table $sql_query limit $sr,$step";
        } else {
            $sql_query = "select * from $table limit $sr,$step";
        }
        
        $pager=array("pager_ctrl"=>$pager_ctrl, "sql_query"=>$sql_query);
    
        return $pager;
    }

    public function createPagerDbGrid($name="", $sql="", $rows_id=0, $page_link="", $curl_rows="", $curl_pager, $can_filter, $can_add, $dialog, $col_widths, $step, $colors, $cs)
    {
        /*
            Desciption des paramètres :
        
            $name="",
            $sql="",
            $rows_id=0,
            $page_link="",
            $curl_rows="",
            $curl_pager="",
            $filterfield,
            $can_add,
            $dialog,
            $col_widths,
            $step,
            $colors,
            $cs
        
            Dessine un tableau dont les informations sont le result d'une requête SQL passée à $sql. Les parametres $page_link et $image_link sont utilisés pour la premiere colonne. Si $image_link est vide, la valeur affichée est celle du champ d'index.
        */
        global $sr, $pc, $img, $lg;
        global $PHP_SELF;
    
        $criterion=getArgument("criterion");
    
        //Détermine les couleurs du dbGrid
        if (!empty($colors)) {
            global $grid_colors;
            $color=$grid_colors;
        }
        $hidden_fields = '';

        $colors = TRegistry::read('ini', 'grid_colors');

        if (!empty($colors)) {
            $border_color=$colors["border_color"];
            $header_back_color=$colors["header_back_color"];
            $even_back_color=$colors["even_back_color"];
            $odd_back_color=$colors["odd_back_color"];
            $header_fore_color=$colors["header_fore_color"];
            $even_fore_color=$colors["even_fore_color"];
            $odd_fore_color=$colors["odd_fore_color"];
            $pager_color=$colors["pager_color"];
        } else {
            $border_color="white";
            $header_back_color="black";
            $even_back_color="lightgrey";
            $odd_back_color="grey";
            $header_fore_color="white";
            $even_fore_color="black";
            $odd_fore_color="white";
            $pager_color="white";
        }
    
        if (!isset($image_link)) {
            $image_link="img/edit.png";
        }

        $add="Ajouter";
    
        //validité du numéro du premier enregistrement affiché
        if ($sr>0) {
            $curl_rows.="&sr=$sr";
        } else {
            unset($sr);
        }
        
        //validité du compteur de pages
        if ($pc>0) {
            $curl_rows.="&pc=$pc";
        } else {
            unset($pc);
        }

        $i=1;
        $criterions=getArgument("criterions");
        if (is_array($criterions)) {
            //echo "criterions rempli<br>";
            foreach ($criterions as $criterion) {
                $curl_pager.="&c$i=$criterion";
                $i++;
            }
        } else {
            //echo "criterions vide<br>";
            $criterion=getArgument("c$i");
            while ($criterion!="") {
                $criterions[$i]=$criterion;
                $curl_pager.="&c$i=$criterion";
                $i++;
                $criterion=getArgument("c$i");
            }
        }
    
        //$curl_pager.="&criterion=$criterion";
    
        $caption=strtoupper($name[0]).substr($name, 1, strlen($name)-1);
    
        /*
        Y a-t-il un complément d'URL en paramètre ?
        Si oui on sépare les noms de variables de leurs valeurs
        et on place les valeur indicant des champs de la requête dans un tableau.
        On concatène les autres variables avec leurs valeurs.
        */
        if ($curl_rows!="") {
            $acompl_url=array();
            $vars=explode("&", $curl_rows);
            $curl_rows2="";
            for ($i=1; $i<count($vars); $i++) {
                $var=explode("=", $vars[$i]);
                if (substr($var[1], 0, 1)=="#") {
                    $acompl_url[$var[1]]=$var[0];
                } else {
                    $curl_rows2.="&".$var[0]."=".$var[1];
                }
            }
        }
    
        /*
        Y a-t-il un complément d'URL en paramètre pour la pagination et le filtre ?
        Si oui on sépare les noms de variables de leurs valeurs
        et on place les valeur indicant des champs de la requête dans un tableau.
        On concatène les autres variables avec leurs valeurs.
        */
        if ($curl_pager!="") {
            $vars=explode("&", $curl_pager);
            $hidden_fields="";
            for ($i=1; $i<count($vars); $i++) {
                $var=explode("=", $vars[$i]);
                $hidden_fields.="<input type='hidden' name='".$var[0]."' value='".$var[1]."'>\n";
            }
        }
    
        /*
        Le paramètre passé à $page_link est un nom de champ de la reqête précdé du préfixe & ou @.
        Si le préfixe est & on agit différemment en fonction de la valeur du champ.
        Si le préfixe est @ on considère que c'est toujours une adresse web.
        */
        $is_image=false;
        $is_url=false;
        $image_field="";
        $web_field="";
        if (substr($page_link, 0, 1)=="|") {
            $image_field=substr($page_link, 1, strlen($page_link)-1);
            $is_image=true;
            $page_link="page.html";
        }
        if (substr($page_link, 0, 1)=="&") {
            $web_field=substr($page_link, 1, strlen($page_link)-1);
            $page_link="page.html";
        } elseif (substr($page_link, 0, 1)=="@") {
            $web_field=substr($page_link, 1, strlen($page_link)-1);
            $is_url=true;
            $page_link="page.html";
        }

    
        if (is_array($criterions) && $can_filter) {
            $fields=getFieldsFromSelectClause($sql);
            array_unshift($criterions, "dummy");
            $s=sizeof($fields);
            for ($i=1; $i<$s; $i++) {
                $sql=insertLikeClause($sql, $fields[$i], $criterions[$i]);
            }
        }
    
        // DEBUG
        /* begin
            $lb="FROM PZ_DB_CONTROLS.PHP:\n";
            $fh=fopen("./query.txt", "a");
            fwrite($fh, $lb.$sql."\n\n");
            fclose($fh);
        // end*/
    
        $pager_id=getArgument("id");
        //echo "page_link='$page_link'<br>";

        $pager_link=$page_link;
        if ($dialog) {
            $pager_link=$PHP_SELF;
        }
        if (isset($_GET["sr"])) {
            $sr=$_GET["sr"];
        } else {
            $sr=0;
        }
        if (isset($_GET["pc"])) {
            $pc=$_GET["pc"];
        } else {
            $pc=0;
        }
    
        $pager_ctrl = $this->createEnhancedPagerControl($pager_link, $sql, $pager_id, $lg, $caption, $sr, $step, $pc, $curl_pager, $cs);
        $sql=$pager_ctrl["sql_query"];
        $pager=$pager_ctrl["pager_ctrl"];

        //echo "sql='$sql'<br>";
        $stmt = $cs->query($sql);
        $num=$stmt->getRowCount();
    
        //if($num) {

        //Les colonnes auront la largeur définie par ordre d'indexation dans le tableau $col_width.
        //Si le nombre de largeurs définies est inférieur on aggrandi le tableau avec des valeurs à 0.
        $width_count=count($col_widths);

        $fields_count = $stmt->getFieldCount();
        $cols=$fields_count;
        if ($width_count<$fields_count) {
            $j=$fields_count-$width_count;
            $a=array_fill($width_count, $j, 0);
            $col_widths=array_merge($col_widths, $a);
        }
    
        $table="";
        $table.="<table id='$name' border='0' cellpadding='2' cellspacing='1' bordercolor='$border_color' bgcolor='white'>\n".
        "<tr bgcolor='$header_back_color'>\n";
    
        //"<input type=\"hidden\" name=\"curl_pager\" value=\"$curl_pager\">\n".
        if ($can_filter) {
            $filters="<form method=\"post\" action=\"page.html?id=$pager_id&lg=$lg\" name=\"filter\">\n".
            $hidden_fields.
            "<tr>\n";
            if ($fields_count>1) {
                $filters.="<td bgcolor=\"$pager_color\"><img src=\"img/filter.png\" border=\"0\"></td>";
            }
        
            $filter_button="<input type=\"submit\" name=\"filter\" value=\"Filtrer\">\n";
        } else {
            $filters="";
            $filter_button="";
        }
        
        $index_fieldname = $stmt->getFieldName(0);
        $k=0;
        $javascript="";
        for ($j=0; $j<$fields_count; $j++) {
            $fieldname = $stmt->getFieldName($j);
            if ($fieldname==$web_field && $is_url===false) {
                $cols--;
            } else {
                if ($can_filter && $fields_count>1 && $j==0) {
                    //nop;
                } elseif ($can_filter) {
                    if (!isset($criterions[$k])) {
                        $criterions[$k]="*";
                    }
                    $filters.="<td id='crit_td$k' bgcolor='$pager_color'><input id='crit_inp$k' type='text' name='criterions[$k]' value='".$criterions[$k]."' size='10'></td>\n";
                    $javascript.="\tvar critinp$k=eval(document.getElementById(\"crit_inp$k\"));\n\tvar crittd$k=eval(document.getElementById(\"crit_td$k\"));\n\tcritinp$k.style.width=crittd$k.offsetWidth+\"px\";\n";
                }
                $tag_width="";
                if ($col_widths[$j]!=0) {
                    $tag_width=" width='".$col_widths[$j]."'";
                }
                if ($fields_count>1 && $j==0) {
                    $fieldname="<img src='$img/edit.png'>";
                }
                $table.="<td align=center$tag_width><span style='color:$header_fore_color'><b>".strtoupper($fieldname[0]).substr($fieldname, 1, strlen($fieldname)-1)."<b></span></td>\n";
                $k++;
            }
        }

        if ($can_filter) {
            $filters."</tr>\n";
        }
    
        $status_bar="<tr><td bgcolor='$pager_color' colspan='$cols' align='center' valign='middle'>\n";
        $status_bar.="<table border='0' cellpadding='0' cellspacing='0' width='100%'>\n";
        if ($can_filter) {
            $status_bar.="<tr><td align='center' width='20%'>$filter_button</td><td align='center' width='*'>$pager</td></tr>\n";
        } else {
            $status_bar.="<tr><td align='center' width='100%'>$pager</td></tr>\n";
        }
        $status_bar.="</table></td></tr>\n";

        $table.="</tr>\n";
        $r=0;
        $i=$fields_count;
        while ($rows=$stmt->fetch(\PDO::FETCH_BOTH)) {
            $on_mouse_over="";
            $on_mouse_out="";
    
            $r1=$r/2;
            $r2=round($r1);
            if ($r1==$r2) {
                $back_color=$even_back_color;
                $fore_color=$even_fore_color;
            } else {
                $back_color=$odd_back_color;
                $fore_color=$odd_fore_color;
            }
        
            $index_value=$rows[0];
        
            $curl_rows=$curl_rows2;
            $target="";
            $ahref="";
            $a="";
        
            if (!empty($acompl_url)) {
                for ($j=0; $j<$i; $j++) {
                    $fieldname = $stmt->getFieldName($j);
                    $sharpname="#".$fieldname;
                    if (isset($acompl_url[$sharpname])) {
                        $curl_rows.="&".$acompl_url[$sharpname]."=".$rows[$fieldname];
                    }
                }
            }

            $js_events="";
            if (isNum($rows_id)) {
                $page_id="id=$rows_id&lg=$lg";
            } else {
                $page_id="di=$rows_id&lg=$lg";
            }

            if ($web_field!="") {
                $url_field=$rows[$web_field];
                if ($url_field!="none" && $url_field!="") {
                    if ($is_url) {
                        if (substr($url_field, 0, 3)=="www") {
                            $url_field="http://".$url_field;
                        }
                        $url=$url_field;
                        $target=" target=\"_new\"";
                    } else {
                        if (substr($url_field, 0, 7)=="http://") {
                            $url=$url_field;
                            $target=" target=\"_new\"";
                        } else {
                            $url="page.html?$page_id$curl_rows";
                        }
                    }
                    $ahref="<a href='$url'$target>";
                    $a="</a>";
                }
            } elseif ($image_field!="") {
                $ahref="";
                $a="";
            } else {
                if ($page_link!="") {
                    $url="$page_link?$page_id&$index_fieldname=".$index_value."&action=Modifier";
                }
                $ahref="<a href='$url$curl_rows'$target>";
                $a="</a>";
            }
            
            $on_mouse_over.="setRowColor(this, hlBackColor, hlTextColor);";
            $on_mouse_out.="setBackRowColor(this);";

            $js_events=" onMouseOver=\"$on_mouse_over\" onMouseOut=\"$on_mouse_out\">";
            $table.="<tr id='$name$r' bgcolor='$back_color'$js_events";
            $url="";
            for ($j=0; $j<$i; $j++) {
                $fieldname = $stmt->getFieldName($j);
                if ($fieldname==$web_field && $is_url===false) {
                    //nop
                } else {
                    $fieldtype = $stmt->getFieldType($j);
                    $fieldlen = $stmt->getFieldLen($j);

                    $value = $rows[$j];
                    
                    if (!empty($curl_rows)) {
                        $url.=$curl_rows;
                    }
                    $tag_width="";
                    //echo "col_width[$j]=$col_widths[$j]<br>";
                    if ($col_widths[$j]!=0) {
                        $tag_width=" width='".$col_widths[$j]."'";
                    }
                
                    $on_click="";
                    if (!empty($dialog)) {
                        $on_click=" onClick=\"".create_dialog_window($url, $dialog[0], $dialog[1])."\"";
                        $ahref="";
                        $a="";
                    }
                    if ($i>1 && $j==0) {
                        $tag_align=" align='center'";
                        $value="<img border='0' src='$image_link' height='16' width='16'$on_click>";
                        $table.="<td>$ahref$value$a</td>\n";
                    } else {
                        if ($fieldtype=="datetime") {
                            $value = dateMysqlToFrench($value);
                        }
                        //if($fieldtype=="time") $value = timeMysqlToShort($value);
                        $tag_align=" align='left'";
                        if ($fieldtype=="int") {
                            $tag_align=" align='right'";
                        }
                        if ($fieldlen < 5) {
                            $tag_align=" align='center'";
                        }
                        $c=$j-1;
                        $table.="<td$tag_align$tag_width>$ahref<span id='caption_$name$r$c'style='color:$fore_color'><span$on_click>$value</span></span>$a</td>\n";
                    }
                }
            }
            $table.="</tr>\n";
            $r++;
        }
        if ($can_add) {
            $rows=array();
            $rows[0]="0";
            $rows[1]="($add)";

            $fields_count = $stmt->getFieldCount();
    
            for ($i=2; $i<$fields_count; $i++) {
                $rows[$i]="...";
            }
        
            $r1=$r/2;
            $r2=round($r1);
            if ($r1==$r2) {
                $back_color=$even_back_color;
                $fore_color=$even_fore_color;
            } else {
                $back_color=$odd_back_color;
                $fore_color=$odd_fore_color;
            }
        
            $index_value=$rows[0];
        
            $curl_rows=$curl_rows2;
            $target="";
            $ahref="";
            $a="";
        
            if (isNum($rows_id)) {
                $page_id="id=$rows_id&lg=$lg&action=$add";
            } else {
                $page_id="di=$rows_id&lg=$lg&action=$add";
            }
            
            $url="page.html?$page_id$curl_rows";
            $ahref="<a href='$url$curl_rows'$target>";
            $a="</a>";
            
            $table.="<tr id='$name$r' bgcolor='$back_color' onMouseOver=\"setRowColor(this, hlBackColor, hlTextColor);\" onMouseOut=\"setBackRowColor(this);\">";
            for ($j=0; $j<$i; $j++) {
                $fieldname = $stmt->getFieldName($j);
                if ($fieldname==$web_field && $is_url===false) {
                    if ($rows[$j]=="(Ajouter)" && $rows[$j+1]=="...") {
                        $rows[$j+1]=$rows[$j];
                    }
                } else {
                    $value=$rows[$j];
                    
                    if (!empty($curl_rows)) {
                        $url.=$curl_rows;
                    }
                    $tag_width="";
                    if ($col_widths[$j]!=0) {
                        $tag_width=" width='".$col_widths[$j]."'";
                    }
                
                    if ($i>1 && $j==0) {
                        $tag_align=" align='center'";
                        $value="<img border='0' src='$image_link' height='16' width='16'>";
                        $table.="<td>$ahref$value$a</td>\n";
                    } else {
                        $tag_align=" align='left'";
                        $c=$j-1;
                        $table.="<td$tag_align$tag_width>$ahref<span id='caption_$name$r$c'style='color:$fore_color'>$value</span>$a</td>\n";
                    }
                }
            }
            $table.="</tr>\n";
        }
        if ($step>$r) {
            $l=$step-$r;
            for ($k=0; $k<$l; $k++) {
                $table.="<tr bgcolor='$pager_color'>\n";
                $table.="<td><img border='0' src='$img/edit_bw.png'></td>";
                for ($j=1; $j<$i; $j++) {
                    $fieldname = $stmt->getFieldName($j);
                    if ($fieldname==$web_field && $is_url===false) {
                        //nop
                    } else {
                        $table.="<td>&nbsp;</td>";
                    }
                }
                $table.="\n</tr>\n";
            }
        }
        $table.=$filters;
        $table.=$status_bar;
        if ($can_filter) {
            $table.="</form>\n";
        }
        $table.="</table>\n";
        if ($javascript) {
            $_SESSION["javascript"].=$javascript;
        }

        //$stmt->free();

        return $table;
    }

    public function createImageDbGrid($name="", $sql="", $rows_id=0, $page_link="", $curl_rows="", $curl_pager, $can_filter, $can_add, $dialog, $col_widths, $step, $colors, $cs)
    {
        /*
            Desciption des paramètres :
        
            $name="",
            $sql="",
            $rows_id=0,
            $page_link="",
            $curl_rows="",
            $curl_pager="",
            $filterfield,
            $can_add,
            $dialog,
            $col_widths,
            $step,
            $colors,
            $cs
        
            Dessine un tableau dont les informations sont le result d'une requête SQL passée à $sql. Les parametres $page_link et $image_link sont utilisés pour la premiere colonne. Si $image_link est vide, la valeur affichée est celle du champ d'index.
        */
        global $sr, $pc, $img, $lg, $database;
    
        $criterion = getArgument("criterion");
        $image_link = $page_link;

        $userdb = $this->database;
    
        //Détermine les couleurs du dbGrid
        if (!empty($colors)) {
            global $grid_colors;
            $color=$grid_colors;
        }
    
        if (!empty($colors)) {
            $border_color=$colors["border_color"];
            $header_back_color=$colors["header_back_color"];
            $even_back_color=$colors["even_back_color"];
            $odd_back_color=$colors["odd_back_color"];
            $header_fore_color=$colors["header_fore_color"];
            $even_fore_color=$colors["even_fore_color"];
            $odd_fore_color=$colors["odd_fore_color"];
            $pager_color=$colors["pager_color"];
        } else {
            $border_color="white";
            $header_back_color="black";
            $even_back_color="lightgrey";
            $odd_back_color="grey";
            $header_fore_color="white";
            $even_fore_color="black";
            $odd_fore_color="white";
            $pager_color="white";
        }
    
        if ($image_link=="") {
            $image_link="img/edit.png";
        }

        $add="Ajouter";
    
        //validité du numéro du premier enregistrement affiché
        if ($sr>0) {
            $curl_rows.="&sr=$sr";
        } else {
            unset($sr);
        }
        
        //validité du compteur de pages
        if ($pc>0) {
            $curl_rows.="&pc=$pc";
        } else {
            unset($pc);
        }

        $i=1;
        $criterions=getArgument("criterions");
        if (is_array($criterions)) {
            //echo "criterions rempli<br>";
            foreach ($criterions as $criterion) {
                $curl_pager.="&c$i=$criterion";
                $i++;
            }
        } else {
            //echo "criterions vide<br>";
            $criterion=getArgument("c$i");
            while ($criterion!="") {
                $criterions[$i]=$criterion;
                $curl_pager.="&c$i=$criterion";
                $i++;
                $criterion=getArgument("c$i");
            }
        }
    
        //$curl_pager.="&criterion=$criterion";
    
        $caption=strtoupper($name[0]).substr($name, 1, strlen($name)-1);
    
        /*
        Y a-t-il un complément d'URL en paramètre ?
        Si oui on sépare les noms de variables de leurs valeurs
        et on place les valeur indicant des champs de la requête dans un tableau.
        On concatène les autres variables avec leurs valeurs.
        */
        if ($curl_rows!="") {
            $acompl_url=array();
            $vars=explode("&", $curl_rows);
            $curl_rows2="";
            for ($i=1; $i<count($vars); $i++) {
                $var=explode("=", $vars[$i]);
                if (substr($var[1], 0, 1)=="#") {
                    $acompl_url[$var[1]]=$var[0];
                } else {
                    $curl_rows2.="&".$var[0]."=".$var[1];
                }
            }
        }
    
        /*
        Le paramètre passé à $page_link est un nom de champ de la reqête précdé du préfixe & ou @.
        Si le préfixe est & on agit différemment en fonction de la valeur du champ.
        Si le préfixe est @ on considère que c'est toujours une adresse web.
        */
        $is_image=false;
        $is_url=false;
        $image_field="";
        $web_field="";
        if (substr($page_link, 0, 1)=="|") {
            $image_field=substr($page_link, 1, strlen($page_link)-1);
            $is_image=true;
            $page_link="page.html";
        }
        if (substr($page_link, 0, 1)=="&") {
            $web_field=substr($page_link, 1, strlen($page_link)-1);
            $page_link="page.html";
        } elseif (substr($page_link, 0, 1)=="@") {
            $web_field=substr($page_link, 1, strlen($page_link)-1);
            $is_url=true;
            $page_link="page.html";
        }

        if (is_array($criterions) && $can_filter) {
            $fields=getFieldsFromSelectClause($sql);
            array_unshift($criterions, "dummy");
            $s=sizeof($fields);
            for ($i=1; $i<$s; $i++) {
                $sql=insertLikeClause($sql, $fields[$i], $criterions[$i]);
            }
        }
    
        $pager_id=getArgument("id");
        $pager_ctrl=createPagerControl($userdb, $page_link, $sql, $pager_id, $lg, $caption, $sr, $step, $pc, $curl_pager);
        $sql=$pager_ctrl["sql_query"];
        $pager=$pager_ctrl["pager_ctrl"];

        //echo "sql='$sql'<br>";
        $stmt = $cs->query($sql);
        $num=$stmt->getRowCount();
    
        //if($num) {

        //Les colonnes auront la largeur définie par ordre d'indexation dans le tableau $col_width.
        //Si le nombre de largeurs définies est inférieur on aggrandi le tableau avec des valeurs à 0.
        $width_count=count($col_widths);
        $fields_count=$stmt->getFieldCount();
        $cols=$fields_count;
        if ($width_count<$fields_count) {
            $j=$fields_count-$width_count;
            $a=array_fill($width_count, $j, 0);
            $col_widths=array_merge($col_widths, $a);
        }
    
        $table="";
        $table.="<table id='$name' border='0' cellpadding='2' cellspacing='1' bordercolor='$border_color'>\n".
        "<tr bgcolor='$header_back_color'>\n";
    
        if ($can_filter) {
            $filters="<form method=\"post\" action=\"page.html?id=$pager_id&lg=$lg\" name=\"filter\"><tr>\n";
            if ($fields_count>1) {
                $filters.="<td bgcolor=\"$pager_color\"><img src=\"img/filter.png\" border=\"0\"></td>";
            }
            $filter_button="<input type=\"submit\" name=\"filter\" value=\"Filtrer\">\n";
        } else {
            $filters="";
            $filter_button="";
        }
        
        $index_fieldname = $stmt->getFieldName(0);
        $k=0;
        $javascript="";
        $tag_width=" width='100'";
        $table.="<td align='center'$tag_width><span style='color:$header_fore_color'><b>image<b></span></td>\n";
        for ($j=0; $j<$fields_count; $j++) {
            $fieldname = $stmt->getFieldName($j);
            if ($fieldname==$web_field && $is_url===false) {
                $cols--;
            } else {
                if ($fieldname!="al_id") {
                    if ($can_filter && $fields_count>1 && $j==0) {
                        //nop;
                    } elseif ($can_filter) {
                        if ($criterions[$k]=="") {
                            $criterions[$k]="*";
                        }
                        $filters.="<td id='crit_td$k' bgcolor='$pager_color'><input id='crit_inp$k' type='text' name='criterions[$k]' value='".$criterions[$k]."' size='10'></td>\n";
                        $javascript.="\tvar critinp$k=eval(document.getElementById(\"crit_inp$k\"));\n\tvar crittd$k=eval(document.getElementById(\"crit_td$k\"));\n\tcritinp$k.style.width=crittd$k.offsetWidth+\"px\";\n";
                    }
                    $tag_width="";
                    if ($col_widths[$j]!=0) {
                        $tag_width=" width='".$col_widths[$j]."'";
                    }
                    if ($fields_count>1 && $j==0) {
                        $fieldname="<img src='$img/edit.png'>";
                    }
                    $table.="<td align=center$tag_width><span style='color:$header_fore_color'><b>$fieldname<b></span></td>\n";
                    $k++;
                }
            }
        }

        if ($can_filter) {
            $filters."</tr>\n";
        }
    
        $cols++;
        $status_bar="<tr><td bgcolor='$pager_color' colspan='$cols' align='center' valign='middle'>\n";
        $status_bar.="<table border='0' cellpadding='0' cellspacing='0' width='100%'>\n";
        if ($can_filter) {
            $status_bar.="<tr><td align='center' width='20%'>$filter_button</td><td align='center' width='*'>$pager</td></tr>\n";
        } else {
            $status_bar.="<tr><td align='center' width='100%'>$pager</td></tr>\n";
        }
        $status_bar.="</table></td></tr>\n";

        $table.="</tr>\n";
        $r=0;
        $i=$fields_count;
        while ($rows=$stmt->fetch(\PDO::FETCH_BOTH)) {
            $on_mouse_over="";
            $on_mouse_out="";
            $al_id=$rows["al_id"];
    
            $r1=$r/2;
            $r2=round($r1);
            if ($r1==$r2) {
                $back_color=$even_back_color;
                $fore_color=$even_fore_color;
            } else {
                $back_color=$odd_back_color;
                $fore_color=$odd_fore_color;
            }
        
            $index_value=$rows[0];
        
            $curl_rows=$curl_rows2;
            $target="";
            $ahref="";
            $a="";
        
            if (!empty($acompl_url)) {
                for ($j=0; $j<$i; $j++) {
                    $fieldname = $stmt->getFieldName($j);
                    $sharpname="#".$fieldname;
                    if ($acompl_url[$sharpname]!="") {
                        $curl_rows.="&".$acompl_url[$sharpname]."=".$rows[$fieldname];
                    }
                }
            }

            $js_events="";
            if (isNum($rows_id)) {
                $page_id="id=$rows_id&lg=$lg";
            } else {
                $page_id="di=$rows_id&lg=$lg";
            }

            if ($web_field!="") {
                $url_field=$rows[$web_field];
                if ($url_field!="none" && $url_field!="") {
                    if ($is_url) {
                        if (substr($url_field, 0, 3)=="www") {
                            $url_field="http://".$url_field;
                        }
                        $url=$url_field;
                        $target=" target=\"_new\"";
                    } else {
                        if (substr($url_field, 0, 7)=="http://") {
                            $url=$url_field;
                            $target=" target=\"_new\"";
                        } else {
                            $url="page.html?$page_id$curl_rows";
                        }
                    }
                    $ahref="<a href='$url'$target>";
                    $a="</a>";
                }
            } elseif ($image_field!="") {
                $ahref="";
                $a="";
            } else {
                if ($page_link!="") {
                    $url="$page_link?$page_id&$index_fieldname=".$index_value."&action=Modifier";
                }
                $ahref="<a href='$url$curl_rows'$target>";
                $a="</a>";
            }
            
            $on_mouse_over.="setRowColor(this, hlBackColor, hlTextColor);";
            $on_mouse_out.="setBackRowColor(this);";

            $js_events=" onMouseOver=\"$on_mouse_over\" onMouseOut=\"$on_mouse_out\"";
            $js_events="";
            $table.="<tr id='$name$r' bgcolor='$back_color'$js_events>";
            $rowspan=$step;
            if ($can_add) {
                $rowspan++;
            }
            if ($can_filter) {
                $rowspan++;
            }

            if ($r==0) {
                $_SESSION["javascript"].="\tvar thmb=eval(document.getElementById(\"thumbnail\"));\n";
                $_SESSION["javascript"].="\tthmb.src=\"".getHttpRoot()."/$database/$lg/galerie/albums/$al_id/small/".$rows[$web_field]."\";\n";
            }
            if ($r==0) {
                $table.="<td rowspan='$rowspan' width='100' valign='top'><img id='thumbnail' width='100'></td>";
            }
            for ($j=0; $j<$i; $j++) {
                $fieldname = $stmt->getFieldName($j);
                if ($fieldname==$web_field && $is_url===false) {
                    //nop
                } else {
                    if ($fieldname!="al_id") {
                        $fieldtype = $stmt->getFieldType($j);
                        $fieldlen = $stmt->getFieldLen($j);

                        $value=$rows[$j];
                    
                        if (!empty($curl_rows)) {
                            $url.=$curl_rows;
                        }
                        $tag_width="";
                        if ($col_widths[$j]!=0) {
                            $tag_width=" width='".$col_widths[$j]."'";
                        }
                
                        if ($i>1 && $j==0) {
                            $tag_align=" align='center'";
                            $on_click="";
                            if (!empty($dialog)) {
                                $on_click=" onClick=\"".OpenDialog($url, $dialog[0], $dialog[1])."\"";
                            }
                            $value="<img border='0' src='$image_link' height='16' width='16'$on_click>";
                            $table.="<td>$ahref$value$a</td>\n";
                        } else {
                            if ($fieldtype=="date") {
                                $value = preg_replace('^([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})$', '\3/\2/\1', $value);
                            }
                            $tag_align=" align='left'";
                            if ($fieldtype=="int") {
                                $tag_align=" align='right'";
                            }
                            if ($fieldlen < 5) {
                                $tag_align=" align='center'";
                            }
                            $c=$j-1;
                            $onmouseover=" onmouseover='thmb.src=\"".getHttpRoot()."/$database/$lg/galerie/albums/$al_id/small/".$rows[$web_field]."\";'";
                            $table.="<td$tag_align$tag_width$onmouseover>$ahref<span id='caption_$name$r$c'style='color:$fore_color'>$value</span>$a</td>\n";
                        }
                    }
                }
            }
            $table.="</tr>\n";
            $r++;
        }
        if ($can_add) {
            $rows=array();
            $rows[0]="0";
            $rows[1]="($add)";
            for ($i=2; $i<$stmt->getFieldCount(); $i++) {
                $rows[$i]="...";
            }
        
            $r1=$r/2;
            $r2=round($r1);
            if ($r1==$r2) {
                $back_color=$even_back_color;
                $fore_color=$even_fore_color;
            } else {
                $back_color=$odd_back_color;
                $fore_color=$odd_fore_color;
            }
        
            $index_value=$rows[0];
        
            $curl_rows=$curl_rows2;
            $target="";
            $ahref="";
            $a="";
        
            if (isNum($rows_id)) {
                $page_id="id=$rows_id&lg=$lg&action=$add";
            } else {
                $page_id="di=$rows_id&lg=$lg&action=$add";
            }
            
            $url="page.html?$page_id$curl_rows";
            $ahref="<a href='$url$curl_rows'$target>";
            $a="</a>";
            
            $table.="<tr id='$name$r' bgcolor='$back_color' onMouseOver=\"setRowColor(this, hlBackColor, hlTextColor);\" onMouseOut=\"setBackRowColor(this);\">";
            for ($j=0; $j<$i; $j++) {
                $fieldname = $stmt->getFieldName($j);
                if ($fieldname==$web_field && $is_url===false) {
                    if ($rows[$j]=="(Ajouter)" && $rows[$j+1]=="...") {
                        $rows[$j+1]=$rows[$j];
                    }
                } else {
                    if ($fieldname!="al_id") {
                        $value=$rows[$j];
                    
                        if (!empty($curl_rows)) {
                            $url.=$curl_rows;
                        }
                        $tag_width="";
                        if ($col_widths[$j]!=0) {
                            $tag_width=" width='".$col_widths[$j]."'";
                        }
                
                        if ($i>1 && $j==0) {
                            $tag_align=" align='center'";
                            $value="<img border='0' src='$image_link' height='16' width='16'>";
                            $table.="<td>$ahref$value$a</td>\n";
                        } else {
                            $tag_align=" align='left'";
                            $c=$j-1;
                            $table.="<td$tag_align$tag_width>$ahref<span id='caption_$name$r$c'style='color:$fore_color'>$value</span>$a</td>\n";
                        }
                    }
                }
            }
            $table.="</tr>\n";
        }
        if ($step>$r) {
            $l=$step-$r;
            for ($k=0; $k<$l; $k++) {
                $table.="<tr bgcolor='$pager_color'>\n";
                $table.="<td><img border='0' src='$img/edit_bw.png'></td>";
                for ($j=1; $j<$i; $j++) {
                    $fieldname = $stmt->getFieldName($j);
                    if ($fieldname==$web_field && $is_url===false) {
                        //nop
                    } else {
                        if ($fieldname!="al_id") {
                            $table.="<td >&nbsp;</td>";
                        }
                    }
                }
                $table.="\n</tr>\n";
            }
        }
        $table.=$filters;
        $table.=$status_bar;
        if ($can_filter) {
            $table.="</form>\n";
        }
        $table.="</table>\n";
        if ($javascript) {
            $_SESSION["javascript"].=$javascript;
        }

        //$stmt->free();

        return $table;
    }

    public function createDbGrid($name="", $sql="", $rows_id=0, $page_link="", $curl_rows="", $can_add, $dialog, $col_widths, $colors, $cs)
    {
        /*
            Desciption des paramètres :
        
        */
        global $img, $lg;

        $image_link="img/edit.png";
        $step = getArgument('pc');

        //Détermine les couleurs du dbGrid
        if (!empty($colors)) {
            global $grid_colors;
            $color=$grid_colors;
        }
    
        if (!empty($colors)) {
            $border_color=$colors["border_color"];
            $header_back_color=$colors["header_back_color"];
            $even_back_color=$colors["even_back_color"];
            $odd_back_color=$colors["odd_back_color"];
            $header_fore_color=$colors["header_fore_color"];
            $even_fore_color=$colors["even_fore_color"];
            $odd_fore_color=$colors["odd_fore_color"];
            $pager_color=$colors["pager_color"];
        } else {
            $border_color="white";
            $header_back_color="black";
            $even_back_color="lightgrey";
            $odd_back_color="grey";
            $header_fore_color="white";
            $even_fore_color="black";
            $odd_fore_color="white";
            $pager_color="lightgrey";
        }

        //Détermine la langue de la page qui sera affichée
        //$lg=getArgument("lg");
        $add="Ajouter";
    
        /*
        Y a-t-il un complément d'URL en paramètre ?
        Si oui on sépare les noms de variables de leurs valeurs
        et on place les valeur indicant des champs de la requête dans un tableau.
        On concatène les autres variables avec leurs valeurs.
        */
        if ($curl_rows!="") {
            $acompl_url=array();
            $vars=explode("&", $curl_rows);
            $curl_rows2="";
            for ($i=1; $i<count($vars); $i++) {
                $var=explode("=", $vars[$i]);
                if (substr($var[1], 0, 1)=="#") {
                    $acompl_url[$var[1]]=$var[0];
                } else {
                    $curl_rows2.="&".$var[0]."=".$var[1];
                }
            }
        }
    
        /*
        Le paramètre passé à $page_link est un nom de champ de la reqête précdé du préfixe & ou @.
        Si le préfixe est & on agit différemment en fonction de la valeur du champ.
        Si le préfixe est @ on considère que c'est toujours une adresse web.
        */
        $is_image=false;
        $is_url=false;
        $image_field="";
        $web_field="";
        if (substr($page_link, 0, 1)=="|") {
            $image_field=substr($page_link, 1, strlen($page_link)-1);
            $is_image=true;
        }
        if (substr($page_link, 0, 1)=="&") {
            $web_field=substr($page_link, 1, strlen($page_link)-1);
        } elseif (substr($page_link, 0, 1)=="@") {
            $web_field=substr($page_link, 1, strlen($page_link)-1);
            $is_url=true;
        }

        //echo "SQL='$sql'<br>";
        $stmt = $cs->query($sql);
        $num=$stmt->getRowCount();
        //if($num) {

        //Les colonnes auront la largeur définie par ordre d'indexation dans le tableau $col_width.
        //Si le nombre de largeurs définies est inférieur on aggrandi le tableau avec des valeurs à 0.
        $width_count=count($col_widths);
        $i=$stmt->getFieldCount();
        if ($width_count<$i) {
            $j=$i-$width_count;
            $a=array_fill($width_count, $j, 0);
            $col_widths=array_merge($col_widths, $a);
        }
    
        $table="";
        $table.="<table id='$name' border='0' cellpadding='2' cellspacing='1' bordercolor='$border_color'>\n".
        "<tr bgcolor='$header_back_color'>\n";
        $index_fieldname = $stmt->getFieldName(0);
        for ($j=0; $j<$i; $j++) {
            $fieldname = $stmt->getFieldName($j);
            if ($fieldname==$web_field && $is_url===false) {
                //nop
            } else {
                $tag_width="";
                if ($col_widths[$j]!=0) {
                    $tag_width=" width='".$col_widths[$j]."'";
                }
                if ($j==0) {
                    $fieldname="<img src='$img/edit.png'>";
                }
                $table.="<td align=center$tag_width><span style='color:$header_fore_color'><b>$fieldname<b></span></td>\n";
            }
        }
        $table.="</tr>\n";
        $r=0;
        while ($rows=$stmt->fetch(PDO::FETCH_BOTH)) {
            $on_mouse_over="";
            $on_mouse_out="";
    
            $r1=$r/2;
            $r2=round($r1);
            if ($r1==$r2) {
                $back_color=$even_back_color;
                $fore_color=$even_fore_color;
            } else {
                $back_color=$odd_back_color;
                $fore_color=$odd_fore_color;
            }
        
            $index_value=$rows[0];
        
            $curl_rows=$curl_rows2;
            $target="";
            $ahref="";
            $a="";
        
            if (!empty($acompl_url)) {
                for ($j=0; $j<$i; $j++) {
                    $fieldname = $stmt->getFieldName($j);
                    $sharpname="#".$fieldname;
                    if ($acompl_url[$sharpname]!="") {
                        $curl_rows.="&".$acompl_url[$sharpname]."=".$rows[$fieldname];
                    }
                }
            }

            $js_events="";
            if (isNum($rows_id)) {
                $page_id="id=$rows_id&lg=$lg";
            } else {
                $page_id="di=$rows_id&lg=$lg";
            }

            if ($web_field!="") {
                $url_field=$rows[$web_field];
                if ($url_field!="none" && $url_field!="") {
                    if ($is_url) {
                        if (substr($url_field, 0, 3)=="www") {
                            $url_field="http://".$url_field;
                        }
                        $url=$url_field;
                        $target=" target=\"_new\"";
                    } else {
                        if (substr($url_field, 0, 7)=="http://") {
                            $url=$url_field;
                            $target=" target=\"_new\"";
                        } else {
                            $url="page.html?$page_id$curl_rows";
                        }
                    }
                    $ahref="<a href='$url'$target>";
                    $a="</a>";
                }
            } elseif ($image_field!="") {
                $ahref="";
                $a="";
            } else {
                if ($page_link!="") {
                    $url="$page_link?$page_id&$index_fieldname=".$index_value."&action=Modifier";
                }
                $ahref="<a href='$url$curl_rows'$target>";
                $a="</a>";
            }
            
            $on_mouse_over.="setRowColor(this, hlBackColor, hlTextColor);";
            $on_mouse_out.="setBackRowColor(this);";

            $js_events=" onMouseOver=\"$on_mouse_over\" onMouseOut=\"$on_mouse_out\">";
            $table.="<tr id='$name$r' bgcolor='$back_color'$js_events";
            for ($j=0; $j<$i; $j++) {
                $fieldname = $stmt->getFieldName($j);
                if ($fieldname==$web_field && $is_url===false) {
                    //nop
                } else {
                    $fieldtype = $stmt->getFieldType($j);
                    $fieldlen = $stmt->getFieldLen($j);

                    $value=$rows[$j];
                    
                    if (!empty($curl_rows)) {
                        $url.=$curl_rows;
                    }
                    $tag_width="";
                    if ($col_widths[$j]!=0) {
                        $tag_width=" width='".$col_widths[$j]."'";
                    }
                
                    if ($j==0) {
                        $tag_align=" align='center'";
                        $on_click="";
                        if (!empty($dialog)) {
                            $on_click=" onClick=\"".OpenDialog($url, $dialog[0], $dialog[1])."\"";
                        }
                        $value="<img border='0' src='$image_link' height='16' width='16'$on_click>";
                        $table.="<td>$ahref$value$a</td>\n";
                    } else {
                        if ($fieldtype=="date") {
                            $value = preg_replace('^([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})$', '\3/\2/\1', $value);
                        }
                        $tag_align=" align='left'";
                        if ($fieldtype=="int") {
                            $tag_align=" align='right'";
                        }
                        if ($fieldlen < 5) {
                            $tag_align=" align='center'";
                        }
                        $c=$j-1;
                        $table.="<td$tag_align$tag_width>$ahref<span id='caption_$name$r$c'style='color:$fore_color'>$value</span>$a</td>\n";
                    }
                }
            }
            $table.="</tr>\n";
            $r++;
        }
        if ($can_add) {
            $rows=array();
            $rows[0]="0";
            $rows[1]="($add)";
            for ($i=2; $i<$stmt->getFieldCount(); $i++) {
                $rows[$i]="...";
            }
        
            $r1=$r/2;
            $r2=round($r1);
            if ($r1==$r2) {
                $back_color=$even_back_color;
                $fore_color=$even_fore_color;
            } else {
                $back_color=$odd_back_color;
                $fore_color=$odd_fore_color;
            }
        
            $index_value=$rows[0];
        
            $curl_rows=$curl_rows2;
            $target="";
            $ahref="";
            $a="";
        
            if (isNum($rows_id)) {
                $page_id="id=$rows_id&lg=$lg&action=$add";
            } else {
                $page_id="di=$rows_id&lg=$lg&action=$add";
            }
            
            $url="page.html?$page_id$curl_rows";
            $ahref="<a href='$url$curl_rows'$target>";
            $a="</a>";
            
            $table.="<tr id='$name$r' bgcolor='$back_color' onMouseOver=\"setRowColor(this, hlBackColor, hlTextColor);\" onMouseOut=\"setBackRowColor(this);\">";
            for ($j=0; $j<$i; $j++) {
                $fieldname = $stmt->getFieldName($j);
                if ($fieldname==$web_field && $is_url===false) {
                    if ($rows[$j]=="(Ajouter)" && $rows[$j+1]=="...") {
                        $rows[$j+1]=$rows[$j];
                    }
                } else {
                    $value=$rows[$j];
                    
                    if (!empty($curl_rows)) {
                        $url.=$curl_rows;
                    }
                    $tag_width="";
                    if ($col_widths[$j]!=0) {
                        $tag_width=" width='".$col_widths[$j]."'";
                    }
                
                    if ($j==0) {
                        $tag_align=" align='center'";
                        $value="<img border='0' src='$image_link' height='16' width='16'>";
                        $table.="<td>$ahref$value$a</td>\n";
                    } else {
                        $tag_align=" align='left'";
                        $c=$j-1;
                        $table.="<td$tag_align$tag_width>$ahref<span id='caption_$name$r$c'style='color:$fore_color'>$value</span>$a</td>\n";
                    }
                }
            }
            $table.="</tr>\n";
        }
        if ($step>$r) {
            $l=$step-$r;
            for ($k=0; $k<$l; $k++) {
                $table.="<tr bgcolor='$pager_color'>\n";
                $table.="<td><img border='0' src='$img/edit_bw.png'></td>";
                for ($j=1; $j<$i; $j++) {
                    $fieldname = $stmt->getFieldName($j);
                    if ($fieldname==$web_field && $is_url===false) {
                        //nop
                    } else {
                        $table.="<td >&nbsp;</td>";
                    }
                }
                $table.="\n</tr>\n";
            }
        }
    
        $table.="</table>\n";

        //$stmt->free();

        return $table;
    }

    public function createOptionsFromTable($index_field="", $option_field="", $table="", $like="", $orderby="", $default="", $only_default=false, $cs)
    {
        $list="";
        $default=trim($default);
        //$default=strtoupper($default);
        $list="";

        if (!empty($like)) {
            $where_field_like=" where $option_field like '$like%'";
        }
    
        if (!empty($orderby)) {
            $order_by_field=" order by $orderby";
        } else {
            $order_by_field=" order by $option_field";
        }

        if (!$only_default) {
            $sql="select $index_field, $option_field from $table$where_field_like$order_by_field";
            //echo "sql='$sql'<br>";
            $stmt = $cs->query($sql);
            while ($rows=$stmt->fetch(PDO::FETCH_ASSOC)) {
                $value=$rows[$index_field];
                $option=$rows[$option_field];
                //$option=strtoupper($option);
                if ($value==$default) {
                    $list.="<OPTION SELECTED VALUE=\"$default\" LABEL=\"$option\">$option</OPTION>\n";
                } else {
                    $list.="<OPTION VALUE=\"$value\" LABEL=\"$option\">$option</OPTION>\n";
                }
            }
        } elseif ($only_default && $default!="") {
            if (!empty($where_field_like)) {
                $where_field_like.=" and $index_field='$default'";
            } else {
                $where_field_like.=" where $index_field='$default'";
            }
            
            $sql="select $index_field, $option_field from $table$where_field_like$order_by_field";
            //echo "$sql<br>";
        
            $stmt = $cs->query($sql);
            while ($rows=$stmt->fetch(PDO::FETCH_ASSOC)) {
                $value=$rows[$index_field];
                $option=$rows[$option_field];
                $list.="<OPTION SELECTED VALUE=\"$value\" LABEL=\"$option\">$option</OPTION>\n";
            }
        }
    
        return $list;
    }

    public function createOptionsFromQuery($sql="", $value_col=0, $option_col=0, $selected=array(), $default="", $only_default=false, $cs)
    {
        global $PZ_ZERO_SELECT;
        if (empty($PZ_ZERO_SELECT)) {
            $PZ_ZERO_SELECT="(Aucun)";
        }
        $list="";
        $default=trim($default);
        //$default=strtoupper($default);
        $list="";
        $options="";
        if (empty($selected)) {
            $selected=(array) null;
        }
        $inter=(array) null;

        self::getLogger()->debug($sql, __FILE__, __LINE__);
        /*
        echo "<pre>";
        print_r($selected);
        echo "</pre>";
        */
        if (!$only_default) {
            $list.="<OPTION SELECTED VALUE=\"0\">".$PZ_ZERO_SELECT."</OPTION>\n";
            $stmt = $cs->query($sql);
            while ($rows=$stmt->fetch()) {
                $value=$rows[$value_col];
                $option=$rows[$option_col];
                if (!empty($selected)) {
                    $inter=implode("", array_intersect($selected, (array) $value));
                    if ($inter!="") {
                        $options.="<OPTION VALUE=\"$value\">$option</OPTION>\n";
                    }
                }
                if ($value==$default) {
                    $list.="<OPTION SELECTED VALUE=\"$default\">$option</OPTION>\n";
                } else {
                    $list.="<OPTION VALUE=\"$value\">$option</OPTION>\n";
                }
            }
        } elseif ($only_default && $default!="") {
            $fields=getFieldsFromSelectClause($sql);
            $value_field=$fields[$value_col];
            $sql=insertTest($sql, $value_field, $default, "=", true);
        
            $stmt = $cs->query($sql);
            while ($rows=$stmt->fetch()) {
                $value=$rows[$value_col];
                $option=$rows[$option_col];
                $list.="<OPTION SELECTED VALUE=\"$value\">$option</OPTION>\n";
            }
            if ($list=="") {
                $list.="<OPTION SELECTED VALUE=\"0\">".$PZ_ZERO_SELECT."</OPTION>\n";
            }
        }

        return array("list"=>$list, "selected"=>$options, "sql"=>$sql);
    }

    public function createOptionsFromArray($records, $separator="", $value_col=0, $option_col=0, $selected=array(), $default="", $only_default=false)
    {
        global $PZ_ZERO_SELECT;
    
        if (!is_array($records)) {
            $rows=$records;
            $separator=($separator=="") ? ",": $separator;
            $records=explode($separator, $rows);
        }
    
        if (empty($PZ_ZERO_SELECT)) {
            $PZ_ZERO_SELECT="(Aucun)";
        }
        $list="";
        $default=trim($default);
        //$default=strtoupper($default);
        $list="";
        $options="";
        if (empty($selected)) {
            $selected=(array) null;
        }
        $inter=(array) null;

        
        if (trim($separator)!="") {
            $tmp_rows=(array) null;
            $i=0;
            foreach ($records as $rows) {
                //$values=explode($separator, $rows);
                $tmp_rows[$i]=(array) $rows;
                $i++;
            }
            $records=$tmp_rows;
            unset($tmp_rows);
        }
    
    
        /*
        echo "<pre>";
        print_r($records);
        echo "</pre>";
        */
    
        if (!$only_default) {
            $list.="<OPTION SELECTED VALUE=\"0\">".$PZ_ZERO_SELECT."</OPTION>\n";
            foreach ($records as $rows) {
                $value=$rows[$value_col];
                $option=$rows[$option_col];
                if (!empty($selected)) {
                    $inter=implode("", array_intersect($selected, (array) $value));
                    if ($inter!="") {
                        $options.="<OPTION VALUE=\"$value\">$option</OPTION>\n";
                    }
                }
                if ($value==$default) {
                    $list.="<OPTION SELECTED VALUE=\"$default\">$option</OPTION>\n";
                } else {
                    $list.="<OPTION VALUE=\"$value\">$option</OPTION>\n";
                }
            }
        }
        /*elseif ($only_default && $default!="") {
            $fields=getFieldsFromSelectClause($sql);
            $value_field=$fields[$value_col];
            $sql=insertTest($sql, $value_field, $default, "=", true);

            foreach($records as $rows) {
                $value=$rows[$value_col];
                $option=$rows[$option_col];
                $list.="<OPTION SELECTED VALUE=\"$value\">$option</OPTION>\n";
            }
            if($list=="") $list.="<OPTION SELECTED VALUE=\"0\">".$PZ_ZERO_SELECT."</OPTION>\n";

        }*/

        return array("list"=>$list, "selected"=>$options);
    }
}
