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

define("FRAME", true);
define("NO_FRAME", false);

class ScriptsMaker extends Base
{
    public function __construct()
    {
        
    }    

    public function makeCode(
        $database,
        $table="",
        $stmt,
        $page_id=0,
        $indexfield=0,
        $secondfield="",
        $A_sqlFields,
        $cs,
        $with_frames
    ) {
        $script="\n";
        //$script.="<script language=\"JavaScript\" src=\"js/pz_form_events.js\"></script>\n";
        $script="<?php   \n";
        $script.="\t\$cs = connection(CONNECT,\$database);\n";
        $script.="\t\$query = getArgument(\"query\", \"SELECT\");\n";
        $script.="\t\$event = getArgument(\"event\", \"onLoad\");\n";
        $script.="\t\$action = getArgument(\"action\", \"Ajouter\");\n";
        $script.="\t\$id = getArgument(\"id\");\n";
        $script.="\t\$di = getArgument(\"di\");\n";
        $script.="\t\$tablename = \"$table\";\n";
        $defs=explode(',', $A_sqlFields[0]);
        $fieldname=$defs[0];
        $script.="\t$$fieldname = getArgument(\"$fieldname\");\n";
        $script.="\tif(\$event === \"onLoad\" && \$query === \"ACTION\") {\n";
        $script.="\t\tswitch (\$action) {\n";
        $script.="\t\tcase \"Ajouter\":\n\n";
        //echo "$L_formFields<br>";
        for ($i = 0; $i < sizeof($A_sqlFields); $i++) {
            $defs=explode(',', $A_sqlFields[$i]);
            $fieldname=$defs[0];
            if ($i === 0) {
                $indexfield=$fieldname;
            } else {
                $script.="\t\t\t\$$fieldname=\"\";\n";
            }
        }
        $script.="\t\tbreak;\n";
        $script.="\t\tcase \"Modifier\":\n";
        for ($i = 1; $i < sizeof($A_sqlFields); $i++) {
            $defs=explode(',', $A_sqlFields[$i]);
            $fieldname=$defs[0];
            if ($i === 1) {
                $script.="\t\t\t\$sql=\"select * from \$tablename where $indexfield='$$indexfield';\";\n";
                $script.="\t\t\t\$stmt = \$cs->query(\$sql);\n";
                $script.="\t\t\t\$rows = \$stmt->fetch(PDO::FETCH_ASSOC);\n";
                $script.="\t\t\t\$$fieldname = \$rows[\"$fieldname\"];\n";
            } else {
                $script.="\t\t\t\$$fieldname = \$rows[\"$fieldname\"];\n";
            }
        }
        $script .= "\t\tbreak;\n";
        $script .= "\t\t}\n";
        $script .= "\t} else if(\$event === \"onRun\" && \$query === \"ACTION\") {\n";
        $script .= "\t\tswitch (\$action) {\n";
        $script .= "\t\tcase \"Ajouter\":\n";
        for ($i = 1; $i < sizeof($A_sqlFields); $i++) {
            $defs = explode(',', $A_sqlFields[$i]);
            $fieldname = $defs[0];
            $script .= "\t\t\t\$$fieldname = filterPOST(\"$fieldname\");\n";
        }
        $replaces=[];
        $insertFields=[];
        $prepargs = [];
        for ($i = 1; $i < sizeof($A_sqlFields); $i++) {
            $defs = explode(',', $A_sqlFields[$i]);
            $fieldname = $defs[0];
            $fieldtype = $stmt->typeNameToPhp($defs[2]);
            $insertFields[$i] = "\t\t\t\t$fieldname";
            // if ($fieldtype === 'string') {
            //     $replaces[] = "\t\t\t\$$fieldname = \$$fieldname";
            // }
            $prepargs[] = "':$fieldname' => \$$fieldname";
        }
        $prepare = '[' . implode(', ', $prepargs) . ']'; 

        // $script .= implode($replaces, ";\n") . ";\n";
        $script .= "\t\t\t\$sql = <<<SQL\n\t\t\tinsert into \$tablename (\n";
        $script .= implode(", \n", $insertFields) . "";
        $script .= "\n\t\t\t) values (\n";
        $insertValues = [];
        for ($i = 1; $i < sizeof($A_sqlFields); $i++) {
            $defs = explode(',', $A_sqlFields[$i]);
            $fieldname = $defs[0];
            $fieldtype = $stmt->typeNameToPhp($defs[2]);
            // if ($fieldtype === 'string') {
            //     $replaces[] = "\t\t\t\$$fieldname = \$$fieldname";
            // }
            $insertValues[$i] = "\t\t\t\t:$fieldname";
        }
        $script .= implode(", \n", $insertValues) . "\n";
        $script .= "\t\t\t)\n";
        $script .= "SQL;\n";
        $script .= "\t\t\t\$stmt = \$cs->prepare(\$sql);\n";
        $script .= "\t\t\t\$stmt->execute($prepare);\n";
        $script .= "\t\tbreak;\n";
        $script .= "\t\tcase \"Modifier\":\n";
        for ($i = 1; $i < sizeof($A_sqlFields); $i++) {
            $defs = explode(',', $A_sqlFields[$i]);
            $fieldname = $defs[0];
            $script.="\t\t\t\$$fieldname = filterPOST(\"$fieldname\");\n";
        }
        $replaces = [];
        $update = [];
        $prepare = [];
        for ($i = 1; $i < sizeof($A_sqlFields); $i++) {
            $defs=explode(',', $A_sqlFields[$i]);
            $fieldname = $defs[0];
            $fieldtype = $stmt->typeNameToPhp($defs[2]);
        
            // if ($fieldtype=='string') {
            //     $replaces[]="\t\t\t\$$fieldname = \$$fieldname";
            // }
            $update[$i]="\t\t\t\t$fieldname = :$fieldname";
            $prepargs[] = "':$fieldname' => \$$fieldname";
        }
        $prepare = '[' . implode(', ', $prepargs) . ']'; 

        // $script .= implode($replaces, ";\n") . ";\n";
        $script .= "\t\t\t\$sql=<<<SQL\n\t\t\tupdate \$tablename set \n";
        $script .= implode(", \n", $update) . "\n";
        $script .= "\t\t\twhere $indexfield = '\$$indexfield';\n";
        $script .= "SQL;\n";
        $script .= "\t\t\t\$stmt = \$cs->prepare(\$sql);\n";
        $script .= "\t\t\t\$stmt->execute($prepare);\n";
        $script .= "\t\tbreak;\n";
        $script .= "\t\tcase \"Supprimer\":\n";
        $script .= "\t\t\t\$sql = \"delete from \$tablename where $indexfield='\$$indexfield'\";\n";
        $script .= "\t\t\t\$stmt = \$cs->query(\$sql);\n";
        $script .= "\t\tbreak;\n";
        $script .= "\t\t}\n";
        if ($with_frames) {
            $script.="\t\techo \"<script language='JavaScript'>window.location.href='<?php echo \$lg?>/$page_id_page?id=$page_id&lg=fr'</script>\";\n";
        } elseif (!$with_frames) {
            $script.="\t\t\$query=\"SELECT\";\n";
        }
        
        //$script.="\t\techo \"<script language='JavaScript'>window.location.href='page.html?id=$page_id&lg=fr'</script>\";\n";
        // $script.="\t} else if(\$event==\"onUnload\" && \$query==\"ACTION\") {\n";
        // $script.="\t\t\$cs=connection(DISCONNECT,\$database);\n";
        // $script.="\t\techo \"<script language='JavaScript'>window.location.href='page.html?id=$page_id&lg=fr'</script>\";\n";
        $script .= "\t}\n";
        // $script.="\? >\n";

        return $script;
    }

    public function makePage(
        $database,
        $table="",
        $pa_filename="",
        $page_id=0,
        $indexfield=0,
        $secondfield="",
        $A_sqlFields,
        $cs,
        $with_frames
    ) {
        $formname=$table."Form";
    
        $analyzer = new \Puzzle\Data\Analyzer;
        $references = $analyzer->searchReferences($database, $table, $cs);
        $A_formFields = $references["form_fields"];

        $script="\n";
        $script="<center>\n";
        $script.="<?php   \n";
        $script.="\tinclude(\"".$pa_filename."_code.php\");\n";
        $script.="\tuse \\Puzzle\\Data\\Controls as DataControls;\n";
        $script.="\t\$datacontrols = new DataControls(\$lg, \$db_prefix);\n";
        $script.="\t\$pc = getArgument(\"pc\");\n";
        $script.="\t\$sr = getArgument(\"sr\");\n";
        $script.="\t\$curl_pager = \"\";\n";
        $script.="\t\$dialog = \"\";\n";
        // $script.="\t\$tablename = \"$table\";\n";
        $script.="\tif(isset(\$pc)) \$curl_pager=\"&pc=\$pc\";\n";
        $script.="\tif(isset(\$sr)) \$curl_pager.=\"&sr=\$sr\";\n";
        $script.="\tif(\$query === \"SELECT\") {\n";
        $script.="\t\t\t\$sql = \"select $indexfield, $secondfield from \$tablename order by $indexfield\";\n";
        $script.="\t\t\t\$dbgrid = \$datacontrols->createPagerDbGrid(\$tablename, \$sql, \$id, \"page.html\", \"&query=ACTION\$curl_pager\", \"\", true, true, \$dialog, array(0, 400), 15, \$grid_colors, \$cs);\n";
        $script.="\t\t\t//\$dbgrid = tableShadow(\$tablename, \$dbgrid);\n";
        $script.="\t\t\techo \"<br>\".\$dbgrid;\n";
        $script.="\t} elseif(\$query === \"ACTION\") {\n";
        $script.="?>\n";
        //$page_filename=getPageFilename($database, $page_id);
        $page_filename="page.html";
        if ($with_frames) {
            $script.="<form method=\"POST\" name=\"$formname\" action=\"<?php echo \$lg?>/$page_filename?id=$page_id&lg=fr\">\n";
        } elseif (!$with_frames) {
            $script.="<form method=\"POST\" name=\"$formname\" action=\"page.html?id=$page_id&lg=fr\">\n";
        }
        $script.="\t<input type=\"hidden\" name=\"query\" value=\"ACTION\">\n";
        $script.="\t<input type=\"hidden\" name=\"event\" value=\"onRun\">\n";
        $script.="\t<input type=\"hidden\" name=\"pc\" value=\"<?php echo \$pc?>\">\n";
        $script.="\t<input type=\"hidden\" name=\"sr\" value=\"<?php echo \$sr?>\">\n";
        $script.="\t<input type=\"hidden\" name=\"$indexfield\" value=\"<?php echo $$indexfield?>\">\n";
        $script.="\t<table border=\"1\" bordercolor=\"<?php echo \$panel_colors[\"border_color\"]?>\" cellpadding=\"0\" cellspacing=\"0\" witdh=\"100%\" height=\"1\">\n";
        $script.="\t\t<tr>\n";
        $script.="\t\t\t<td align=\"center\" valign=\"top\" bgcolor=\"<?php echo \$panel_colors[\"back_color\"]?>\">\n";
        $script.="\t\t\t\t<table>\n";
        $inputs="";
        for ($i = 0; $i < sizeof($A_formFields); $i++) {
            $inputs.= $A_formFields[$i] . "\n";
        }
        $script.= $inputs;
        //$script.="<tr><td align=\"center\" colspan=\"2\"><input type=\"submit\" name=\"action\" value=\"<?php echo \$action>\" onClick=\"return runForm(\"$formname\");\">\n";
    
        $script.="\t\t\t\t\t<tr>\n";
        $script.="\t\t\t\t\t\t<td align=\"center\" colspan=\"2\">\n";
        $script.="\t\t\t\t\t\t\t<input type=\"submit\" name=\"action\" value=\"<?php echo \$action?>\">\n";
        $script.="\t\t\t\t\t\t\t<?php   if(\$action!=\"Ajouter\") { ?>\n";
        //$script.="<input type=\"submit\" name=\"action\" value=\"Supprimer\" onClick=\"return runForm(\"$formname\");\">\n";
        $script.="\t\t\t\t\t\t\t\t<input type=\"submit\" name=\"action\" value=\"Supprimer\">\n";
        $script.="\t\t\t\t\t\t\t<?php   } ?>\n";
        $script.="\t\t\t\t\t\t\t<input type=\"reset\" name=\"action\" value=\"Annuler\">\n";
        //$script.="<input type=\"submit\" name=\"action\" value=\"Retour\" onClick=\"return runForm(\"$formname\");\">\n";
        $script.="\t\t\t\t\t\t\t<input type=\"submit\" name=\"action\" value=\"Retour\">\n";
        $script.="\t\t\t\t\t\t</td>\n";
        $script.="\t\t\t\t\t</tr>\n";
        $script.="\t\t\t\t</table>\n";
        $script.="\t\t\t</td>\n";
        $script.="\t\t</tr>\n";
        $script.="\t</table>\n";
        $script.="</form>\n";
        //$script.="<table><tr><td valign=\"middle\"><a href=\"javascript: history.go(-1);\"><img src=\"../img/scroll/left_0.gif\" border=\"0\">Retour</a></td></tr></table>\n";
        $script.="<?php   \t} ?>\n";
        $script.="</center>\n";

        return $script;
    }

    public function makeSingleScript(
        $database,
        $table="",
        $pa_filename="",
        $page_id=0,
        $indexfield=0,
        $secondfield="",
        $A_sqlFields,
        $cs,
        $with_frames
    ) {
        $formname=$table."Form";
    
        $analyzer=relations($database, $table, $cs);
        $A_formFields=$analyzer["form_fields"];
    
        $script="\n";
        $script="<center>\n";
        //$script.="<script language=\"JavaScript\" src=\"js/pz_form_events.js\"></script>\n";
        $script.="<?php   \n";
        $script.="\tinclude_once(\"puzzle/ipz_mysqlconn.php\");\n";
        $script.="\tinclude_once(\"puzzle/ipz_db_controls.php\");\n";
        $script.="\t\$cs=connection(CONNECT,\$database);\n";
        $script.="\t\$query = getArgument(\"query\");\n";
        $script.="\t\$event = getArgument(\"event\");\n";
        $script.="\t\$action = getArgument(\"action\");\n";
        $fieldname=$A_sqlFields[0];
        $script.="\t$$fieldname = getArgument(\"$fieldname\");\n";
        $script.="\tif(empty(\$query)) \$query=\"SELECT\";\n";
        $script.="\tif(empty(\$event)) \$event=\"onLoad\";\n";
        $script.="\tif(empty(\$action)) \$action=\"Ajouter\";\n";
        $script.="\tif(isset(\$pc)) \$curl_pager=\"&pc=\$pc\";\n";
        $script.="\tif(isset(\$sr)) \$curl_pager.=\"&sr=\$sr\";\n";
        $script.="\tif(\$event==\"onLoad\" && \$query==\"ACTION\") {\n";
        $script.="\t\tswitch (\$action) {\n";
        $script.="\t\tcase \"Ajouter\":\n\n";
        //echo "$L_formFields<br>";
        for ($i=0;$i<sizeof($A_sqlFields);$i++) {
            $fieldname=$A_sqlFields[$i];
            if ($i==0) {
                $indexfield=$fieldname;
                $script.="\t\t\t\$sql=\"select max($fieldname) from $table;\";\n";
                $script.="\t\t\t\$stmt = \$cs->query(\$sql);\n";
                $script.="\t\t\t\$rows = \$stmt->fetch(PDO::FETCH_ASSOC);\n";
                $script.="\t\t\t\$$fieldname=\$rows[0]+1;\n";
            } else {
                $script.="\t\t\t\$$fieldname=\"\";\n";
            }
        }
        $script.="\t\tbreak;\n";
        $script.="\t\tcase \"Modifier\":\n";
        for ($i=0;$i<sizeof($A_sqlFields);$i++) {
            $fieldname=$A_sqlFields[$i];
            if ($i==0) {
                $script.="\t\t\t\$sql=\"select * from $table where $fieldname=\"$$fieldname\";\";\n";
                $script.="\t\t\t\$stmt = \$cs->query(\$sql);\n";
                $script.="\t\t\t\$rows = \$stmt->fetch(PDO::FETCH_ASSOC);\n";
                $script.="\t\t\t\$$fieldname=\$rows[\"$fieldname\"];\n";
            } else {
                $script.="\t\t\t\$$fieldname=\$rows[\"$fieldname\"];\n";
            }
        }
        $script.="\t\tbreak;\n";
        $script.="\t\t}\n";
        $script.="\t} else if(\$event==\"onRun\" && \$query==\"ACTION\") {\n";
        $script.="\t\tswitch (\$action) {\n";
        $script.="\t\tcase \"Ajouter\":\n";
        $script.="\t\t\t\$sql=\"insert into $table (\".\n";
        $insert=[];
        for ($i=0;$i<sizeof($A_sqlFields);$i++) {
            $fieldname=$A_sqlFields[$i];
            $insert[$i]="\t\t\t\t\"$fieldname";
        }
        $script.=implode(", \".\n", $insert) . "\".";
        $script.="\n\t\t\t\") values (\".\n";
        $insert=[];
        for ($i=0;$i<sizeof($A_sqlFields);$i++) {
            $fieldname=$A_sqlFields[$i];
            $insert[$i]="\t\t\t\t\"'\$$fieldname'";
        }
        $script.=implode(", \".\n", $insert) . "\".\n";
        $script.="\t\t\t\")\";\n";
        $script.="\t\t\t\$stmt = \$cs->query(\$sql);\n";
        $script.="\t\tbreak;\n";
        $script.="\t\tcase \"Modifier\":\n";
        for ($i=0;$i<sizeof($A_sqlFields);$i++) {
            $fieldname=$A_sqlFields[$i];
            $script.="\t\t\t\$$fieldname = filterPOST(\"$fieldname\");\n";
        }
        $script.="\t\t\t\$sql=\"update $table set \".\n";
        $update=[];
        for ($i=0;$i<sizeof($A_sqlFields);$i++) {
            $fieldname=$A_sqlFields[$i];
            $update[$i]="\t\t\t\t\"$fieldname='\$$fieldname'";
        }
        $script.=implode(", \".\n", $update) . " \".\n";
        $script.="\t\t\t\"where $indexfield='\$$indexfield'\";\n";
        $script.="\t\t\t\$stmt = \$cs->query(\$sql);\n";
        $script.="\t\tbreak;\n";
        $script.="\t\tcase \"Supprimer\":\n";
        $script.="\t\t\t\$sql=\"delete from $table where $indexfield='\$$indexfield'\";\n";
        $script.="\t\t\t\$stmt = \$cs->query(\$sql);\n";
        $script.="\t\tbreak;\n";
        $script.="\t\t}\n";
        if ($with_frames) {
            $script.="\t\techo \"<script language='JavaScript'>window.location.href='<?php echo \$lg?>/$page_id_page?id=$page_id&lg=fr'</script>\";\n";
        } elseif (!$with_frames) {
            $script.="\t\t\$query=\"SELECT\";\n";
        }
        //$script.="\t\techo \"<script language='JavaScript'>window.location.href='page.html?id=$page_id&lg=fr'</script>\";\n";
        $script.="\t} else if(\$event==\"onUnload\" && \$query==\"ACTION\") {\n";
        $script.="\t\t\$cs=connection(\"disconnect\",\"$database\");\n";
        $script.="\t\techo \"<script language='JavaScript'>window.location.href='page.html?id=$page_id&lg=fr'</script>\";\n";
        $script.="\t}\n";

        $script.="if(\$query==\"SELECT\") {\n";
        $script.="\t\t\$sql=\"select $indexfield, $secondfield from $table order by $indexfield\";\n";

        $script.="\t\t\$dbgrid=\$datacontrols->createPagerDbGrid(\"$table\", \$sql, \$id, \"page.html\", \"&query=ACTION\$curl_pager\", \"\", true, true, \$dialog, array(0, 400), 15, \$grid_colors, \$cs);\n";
  
        $script.="\t\t//\$dbgrid=tableShadow(\"$table\", \$dbgrid);\n";
        $script.="\t\techo \"<br>\".\$dbgrid;\n";
        $script.="} elseif(\$query==\"ACTION\") {\n";
        $script.="?>\n";
        //$page_filename=getPageFilename($database, $page_id);
        $page_filename="page.html";
        if ($with_frames) {
            $script.="<form method='POST' name='$formname' action='<?php echo \$lg?>/$page_filename?id=$page_id&lg=fr'>\n";
        } elseif (!$with_frames) {
            $script.="<form method='POST' name='$formname' action='page.html?id=$page_id&lg=fr'>\n";
        }
        $script.="\t<input type='hidden' name='query' value='ACTION'>\n";
        $script.="\t<input type='hidden' name='event' value='onRun'>\n";
        $script.="\t<input type='hidden' name='pc' value='<?php echo \$pc?>'>\n";
        $script.="\t<input type='hidden' name='sr' value='<?php echo \$sr?>'>\n";
        $script.="\t<input type='hidden' name='$indexfield' value='<?php echo $$indexfield?>'>\n";
        $script.="\t<table border='1' bordercolor='<?php echo \$panel_colors[\"border_color\"]?>' cellpadding='0' cellspacing='0' witdh='100%' height='1'>\n";
        $script.="\t\t<tr>\n";
        $script.="\t\t\t<td align='center' valign='top' bgcolor='<?php echo \$panel_colors[\"back_color\"]?>'>\n";
        $script.="\t\t\t\t<table>\n";
        $inputs="";
        for ($i=0;$i<sizeof($A_formFields);$i++) {
            $inputs.= $A_formFields[$i] . "\n";
        }
        $script.= $inputs;
        //$script.="<tr><td align='center' colspan='2'><input type='submit' name='action' value='<?php echo \$action>' onClick='return runForm(\"$formname\");'>\n";
    
        $script.="\t\t\t\t\t<tr>\n";
        $script.="\t\t\t\t\t\t<td align='center' colspan='2'>\n";
        $script.="\t\t\t\t\t\t\t<input type='submit' name='action' value='<?php echo \$action?>'>\n";
        $script.="<?php   \t\t\t\t\t\t\tif(\$action!=\"Ajouter\") { ?>\n";
        //$script.="<input type='submit' name='action' value='Supprimer' onClick='return runForm(\"$formname\");'>\n";
        $script.="\t\t\t\t\t\t\t\t<input type='submit' name='action' value='Supprimer'>\n";
        $script.="<?php   \t\t\t\t\t\t\t} ?>\n";
        $script.="\t\t\t\t\t\t\t<input type='reset' name='action' value='Annuler'>\n";
        //$script.="<input type='submit' name='action' value='Retour' onClick='return runForm(\"$formname\");'>\n";
        $script.="\t\t\t\t\t\t\t<input type='submit' name='action' value='Retour'>\n";
        $script.="\t\t\t\t\t\t</td>\n";
        $script.="\t\t\t\t\t</tr>\n";
        $script.="\t\t\t\t</table>\n";
        $script.="\t\t\t</td>\n";
        $script.="\t\t</tr>\n";
        $script.="\t</table>\n";
        $script.="</form>\n";
        //$script.="<table><tr><td valign=\"middle\"><a href=\"javascript: history.go(-1);\"><img src=\"../img/scroll/left_0.gif\" border=\"0\">Retour</a></td></tr></table>\n";
        $script.="<?php   } ?>\n";
        $script.="</center>\n";

        return $script;
    }

    public function scriptHeader($database)
    {
        $script="";
        $script.="<?php   \n";
        $script.="\tglobal \$img;\n";
        $script.="\t\$img=\"/img\";\n";
        $script.="\tinclude_once(\"puzzle/ipz_style.php\");\n";
        $script.="\tinclude_once(\"puzzle/ipz_misc.php\");\n";
        $script.="\tinclude_once(PZ_DEFAULTS);\n";
        $script.="\tinclude_once(\"puzzle/ipz_mysqlconn.php\");\n";
        $script.="\tinclude_once(\"puzzle/ipz_db_controls.php\");\n\n";
        $script.="\tif(!session_is_registered(\"javascript\")) {\n";
        $script.="\t\tsession_register(\"javascript\");\n";
        $script.="\t}\n";
        $script.="\t\$_SESSION[\"javascript\"]=\"\";\n\n";
        $script.="\t\$cs=connection(CONNECT,\$database, \$cs);\n\n";
        $script.="?>\n";
    
        return $script;
    }

    public function scriptFooter()
    {
        $script="";
        $script.="<script language=\"JavaScript\">\n";
        //$script.="\tpz_shadow(\"my_table\");\n";
        $script.="<?php   \n";
        $script.="\techo \$_SESSION[\"javascript\"];\n";
        $script.="?>\n";
        $script.="</script>\n";
    
        return $script;
    }

    public function scriptBrowse(
        $database,
        $table="",
        $pa_filename="",
        $page_id=0,
        $indexfield=0,
        $secondfield="",
        $A_sqlFields,
        $cs,
        $with_frames
    ) {
        $script="";
        $script.="<?php   \n";
        $script.="\tif(empty(\$query)) \$query=\"SELECT\";\n";
        $script.="\tif(empty(\$event)) \$event=\"onLoad\";\n";
        $script.="\tif(empty(\$action)) \$action=\"Ajouter\";\n";
        $script.="\tif(isset(\$pc)) \$curl_pager=\"&pc=\$pc\";\n";
        $script.="\tif(isset(\$sr)) \$curl_pager.=\"&sr=\$sr\";\n";
        $script.="\t\$sql=\"select $indexfield, $secondfield from $table order by $indexfield\";\n";

        $script.="\t\$dbgrid=\$datacontrols->createPagerDbGrid(\"$table\", \$sql, \$id, \"".$pa_filename."_browse.php\", \"&query=ACTION\$curl_pager\", \"\", true, true, \$dialog, array(0, 400), 15, \$grid_colors, \$cs);\n";
  
        $script.="\t//\$dbgrid=tableShadow(\"$table\", \$dbgrid);\n";
        $script.="\techo \"<br>\".\$dbgrid;\n";
        $script.="?>\n";
    
        return $script;
    }

    public function scriptForm(
        $database,
        $table="",
        $pa_filename="",
        $page_id=0,
        $indexfield=0,
        $secondfield="",
        $A_sqlFields,
        $cs,
        $with_frames
    ) {
        $analyzer=relations($database, $table, $cs);
        $A_formFields=$analyzer["form_fields"];
        $script="";
        $script.="<?php   \n";
        $script.="\tif(empty(\$event)) \$event=\"onLoad\";\n";
        $script.="\tif(empty(\$action)) \$action=\"Ajouter\";\n";
        $script.="\tif(isset(\$pc)) \$curl_pager=\"&pc=\$pc\";\n";
        $script.="\tif(isset(\$sr)) \$curl_pager.=\"&sr=\$sr\";\n";
        $script.="?>\n";
        //$page_filename=getPageFilename($database, $page_id);
        $page_filename=$pa_filename;
        //if($with_frames)
        $script.="\t<form method='POST' name='$formname' action='<?php echo \$lg?>/$page_filename?id=$page_id&lg=fr'>\n";
        //else if(!$with_frames)
        //$script.="<form method='POST' name='$formname' action='page.html?id=$page_id&lg=fr'>\n";
        $script.="\t\t<input type='hidden' name='query' value='ACTION'>\n";
        $script.="\t\t<input type='hidden' name='event' value='onRun'>\n";
        $script.="\t\t<input type='hidden' name='pc' value='<?php echo \$pc?>'>\n";
        $script.="\t\t<input type='hidden' name='sr' value='<?php echo \$sr?>'>\n";
        $script.="\t\t<input type='hidden' name='$indexfield' value='<?php echo $$indexfield?>'>\n";
        $script.="\t\t<table border='1' bordercolor='<?php echo \$panel_colors[\"border_color\"]?>' cellpadding='0' cellspacing='0' witdh='100%' height='1'>";
        $script.="\t\t\t<tr>\n";
        $script.="\t\t\t\t<td align='center' valign='top' bgcolor='<?php echo \$panel_colors[\"back_color\"]?>'>\n";
        $script.="\t\t\t\t\t<table>\n";
        $inputs="";
        for ($i=0;$i<sizeof($A_formFields);$i++) {
            $inputs.=$A_formFields[$i];
        }
        $script.="$inputs\n";
    
        $script.="<tr><td align='center' colspan='2'>\n<input type='submit' name='action' value='<?php echo \$action?>'>\n";
        $script.="<?php   \tif(\$action!=\"Ajouter\") { ?>\n";
        $script.="<input type='submit' name='action' value='Supprimer'>\n";
        $script.="<?php   \t} ?>\n";
        $script.="<input type='reset' name='action' value='Annuler'>\n";
        $script.="<input type='submit' name='action' value='Retour'>\n";
        $script.="</td></tr></table>\n";
        $script.="</td></tr></table>\n";
        $script.="</form>\n";

        return $script;
    }

    public function makeBrowseScript(
        $database,
        $table="",
        $pa_filename="",
        $page_id=0,
        $indexfield=0,
        $secondfield="",
        $A_sqlFields,
        $cs,
        $with_frames
    ) {
        $formname=$table."Form";
    
        //$script.="<script language=\"JavaScript\" src=\"js/pz_form_events.js\"></script>\n";
        $script="";
        $script.=scriptHeader($database);
        $script.="<center>\n";
        $script.=scriptBrowse($database, $table, $pa_filename, $page_id, $indexfield, $secondfield, $A_sqlFields, $cs, $with_frames);
        $script.="</center>\n";
        $script.=scriptFooter();

        return $script;
    }

    public function makeFormScript(
        $database,
        $table="",
        $pa_filename="",
        $page_id=0,
        $indexfield=0,
        $secondfield="",
        $A_sqlFields,
        $cs,
        $with_frames
    ) {
        $formname=$table."Form";
    
        $analyzer=relations($database, $table, $cs);
        $A_formFields=$analyzer["form_fields"];
    
        //$script.="<script language=\"JavaScript\" src=\"js/pz_form_events.js\"></script>\n";
        $script="";
        $script.=scriptHeader($database);
        $script.="<center>\n";
        $script.=scriptForm($database, $table, $pa_filename, $page_id, $indexfield, $secondfield, $A_sqlFields, $cs, $with_frames);
        $script.="</center>\n";
        $script.=scriptFooter();

        return $script;
    }

    public function makeInsertScript(
        $database,
        $table="",
        $pa_filename="",
        $page_id=0,
        $indexfield=0,
        $secondfield="",
        $A_sqlFields,
        $cs,
        $with_frames
    ) {
        $extension=substr($pa_filename, strlen($pa_filename) - 4, 4);
        $formname=$table."Form";
    
        $script="";
        $script.=scriptHeader($database);
        $script.="<?php   \n";
        $script.="\t\$cs=connection(CONNECT,\$database);\n";
        $script.="\tif(empty(\$event)) \$event=\"onLoad\";\n";
        $script.="\tif(\$event==\"onLoad\") {\n";
        //$analyzer=relations($database,$table,$cs);
        //$A_formFields=$analyzer["form_fields"];
        echo "$L_formFields<br>";
        for ($i=0;$i<sizeof($A_sqlFields);$i++) {
            $fieldname=$A_sqlFields[$i];
            if ($i==0) {
                $indexfield=$fieldname;
                $script.="\t\t\$sql=\"select max($fieldname) from $table;\";\n";
                $script.="\t\t\$stmt = \$cs->query(\$sql);\n";
                $script.="\t\t\$rows = \$stmt->fetch();\n";
                $script.="\t\t\$$fieldname=\$rows[0]+1;\n";
            } else {
                $script.="\t\t\$$fieldname=\"\";\n";
            }
        }
        $script.="\t\tinclude('".$pa_filename."_form".$extension."')\n";
        $script.="\t} else if(\$event==\"onRun\") {\n";
        $script.="\t\t\$sql=\"insert into $table (\".\n";
        $insert=[];
        for ($i=0;$i<sizeof($A_sqlFields);$i++) {
            $fieldname=$A_sqlFields[$i];
            $insert[$i]="\t\t\t\"$fieldname";
        }
        $script.=implode(", \".\n", $insert) . "\".";
        $script.="\n\t\t\") values (\".\n";
        $insert=[];
        for ($i=0;$i<sizeof($A_sqlFields);$i++) {
            $fieldname=$A_sqlFields[$i];
            $insert[$i]="\t\t\t\"'\$$fieldname'";
        }
        $script.=implode(", \".\n", $insert) . "\".\n";
        $script.="\t\t\")\";\n";
        $script.="\t\t\$stmt = \$cs->query(\$sql);\n";
        $script.="\t\tinclude('".$pa_filename."_browse".$extension."')\n";
        $script.="\t}\n";
        $script.="?>\n";
        $script.=scriptBrowse($database, $table, $pa_filename, $page_id, $indexfield, $secondfield, $A_sqlFields, $cs, $with_frames);
        $script.=scriptFooter();

        return $script;
    }

    public function makeUpdateScript(
        $database,
        $table="",
        $pa_filename="",
        $page_id=0,
        $indexfield=0,
        $secondfield="",
        $A_sqlFields,
        $cs,
        $with_frames
    ) {
        $extension=substr($pa_filename, strlen($pa_filename) - 4, 4);
        $formname=$table."Form";
    
        $script="";
        $script.=scriptHeader($database);
        $script.="<?php   \n";
        $script.="\t\$cs=connection(CONNECT,\$database);\n";
        $script.="\tif(empty(\$event)) \$event=\"onLoad\";\n";
        $script.="\tif(\$event==\"onLoad\") {\n";
        for ($i=0;$i<sizeof($A_sqlFields);$i++) {
            $fieldname=$A_sqlFields[$i];
            if ($i==0) {
                $script.="\t\t\$sql=\"select * from $table where $fieldname='$$fieldname';\";\n";
                $script.="\t\t\$stmt = \$cs->query(\$sql);\n";
                $script.="\t\t\$rows = \$stmt->fetch(PDO::FETCH_ASSOC);\n";
                $script.="\t\t\$$fieldname=\$rows[\"$fieldname\"];\n";
            } else {
                $script.="\t\t\$$fieldname=\$rows[\"$fieldname\"];\n";
            }
        }
        $script.="\t\tinclude('".$pa_filename."_form".$extension."')\n";
        $script.="\t} else if(\$event==\"onRun\") {\n";
        $script.="\t\t\$sql=\"update $table set \".\n";
        $update=[];
        for ($i=0;$i<sizeof($A_sqlFields);$i++) {
            $fieldname=$A_sqlFields[$i];
            $update[$i]="\t\t\t\"$fieldname='\$$fieldname'";
        }
        $script.=implode(", \".\n", $update) . " \".\n";
        $script.="\t\t\t\"where $indexfield='\$$indexfield'\";\n";
        $script.="\t\t\$stmt = \$cs->query(\$sql);\n";
        $script.="\t\tinclude('".$pa_filename."_browse".$extension."')\n";
        $script.="\t}\n";
        $script.="?>\n";
        $script.=scriptBrowse($database, $table, $pa_filename, $page_id, $indexfield, $secondfield, $A_sqlFields, $cs, $with_frames);
        $script.=scriptFooter();

        return $script;
    }

    public function makeDeleteScript(
        $database,
        $table="",
        $pa_filename="",
        $page_id=0,
        $indexfield=0,
        $secondfield="",
        $A_sqlFields,
        $cs,
        $with_frames
    ) {
        $extension=substr($pa_filename, strlen($pa_filename) - 4, 4);
        $formname=$table."Form";
    
        $script="";
        $script.=scriptHeader($database);
        $script.="<?php   \n";
        $script.="\t\$cs=connection(CONNECT,\$database);\n";
        $script.="\tif(empty(\$event)) \$event=\"onLoad\";\n";
        $script.="\tif(\$event==\"onRun\") {\n";
        $script.="\t\t\$sql=\"delete from $table where $indexfield='\$$indexfield'\";\n";
        $script.="\t\t\$stmt = \$cs->query(\$sql);\n";
        $script.="\t\tinclude('".$pa_filename."_browse".$extension."')\n";
        $script.="\t}\n";
        $script.="?>\n";
        $script.=scriptBrowse($database, $table, $pa_filename, $page_id, $indexfield, $secondfield, $A_sqlFields, $cs, $with_frames);
        $script.=scriptFooter();

        return $script;
    }
}
