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

define("STRING", 0);
define("PERCENT", 1);
define("NUMBER", 2);
define("CURRENCY_USD", 4);
define("CURRENCY_GBP", 6);
define("CURRENCY_EUR", 8);

class Graphs extends \Puzzle\Base
{
    function colorRange($number)
    {
        $r_angle=0;
        $g_angle=120;
        $b_angle=240;
    
        $colors=array();
        for ($i=0;$i<$number;$i++) {
            $r_angle+=120/$number;
            $g_angle+=120/$number;
            $b_angle+=120/$number;

            $r_rad=abs(sin(deg2rad($r_angle)));
            $g_rad=abs(sin(deg2rad($g_angle)));
            $b_rad=abs(sin(deg2rad($b_angle)));

            $r_value=floor($r_rad*255);
            $g_value=floor($g_rad*255);
            $b_value=floor($b_rad*255);
            $colors[$i]=array("red"=>$r_value,"green"=>$g_value,"blue"=>$b_value);
        }
        return $colors;
    }

    function darkColorRange($number)
    {
        $r_angle=0;
        $g_angle=120;
        $b_angle=240;
    
        $colors=array();
        for ($i=0;$i<$number;$i++) {
            $r_angle+=120/$number;
            $g_angle+=120/$number;
            $b_angle+=120/$number;

            $r_rad=abs(sin(deg2rad($r_angle)));
            $g_rad=abs(sin(deg2rad($g_angle)));
            $b_rad=abs(sin(deg2rad($b_angle)));

            $r_value=floor($r_rad*255)*.8;
            $g_value=floor($g_rad*255)*.8;
            $b_value=floor($b_rad*255)*.8;
            $colors[$i]=array("red"=>$r_value,"green"=>$g_value,"blue"=>$b_value);
        }
        return $colors;
    }

    function legend($name, $data, $data_type, $caption_name, $caption_size, $margin_size, $border)
    {
        $legend_data="";
        if ($data_type==PERCENT) {
            $legend_data=" %";
        } elseif ($data_type==CURRENCY_EUR) {
            $legend_data=" E";
        } elseif ($data_type==CURRENCY_GBP) {
            $legend_data=" £";
        } elseif ($data_type==CURRENCY_USD) {
            $legend_data=" $";
        } elseif ($data_type==NUMBER) {
            $legend_data="";
        } elseif ($data_type==PERCENT+CURRENCY_EUR) {
            $legend_data=" E";
        } elseif ($data_type==PERCENT+CURRENCY_GBP) {
            $legend_data=" £";
        } elseif ($data_type==PERCENT+CURRENCY_USD) {
            $legend_data=" $";
        } elseif ($data_type==PERCENT+NUMBER) {
            $legend_data="";
        }

        $j=sizeof($data);

        $total=array_sum($data);

        $colors=color_range($j);
    
        $max_width=0;
        $max_height=0;

        $caption_widths=array();
        for ($i=0;$i<$j;$i++) {
            if ($data_type==PERCENT) {
                $text=round($data[$i]/$total*100, 2);
                $text.=$legend_data;
            } else {
                $text=$data[$i].$legend_data;
            }

            $data[$i]=$text;
            list($llx, $lly, $lrx, $lry, $urx, $ury, $ulx, $uly)=imageTTFbbox($caption_size, 0, $caption_name, $text);
            $caption_width=abs($llx)+$lrx;
            $caption_height=abs($uly-$lly);
            if ($caption_width>$max_width) {
                $max_width=$caption_width;
            }
            if ($caption_height>$max_height) {
                $max_height=$caption_height;
            }

            $caption_widths[$i]=$caption_width;
        }
    
        $ih=$j*$max_height*1.5+$margin_size;
        //if($data_type>STRING)
        $iw=$max_width+2*$margin_size;
        //else
        //	$iw=$max_width+2*$margin_size;
        $im=imagecreate($iw, $ih);
        $white=imagecolorallocate($im, 255, 255, 255);
        $black=imagecolorallocate($im, 0, 0, 0);
    
        if ($border) {
            imagerectangle($im, 0, 0, --$iw, --$ih, $black);
        }
    
        for ($i=0;$i<$j;$i++) {
            $left=$margin_size;
            $top+=$caption_height*1.5;
            $right=$left+$caption_size;
            $bottom=$top+$caption_size;
        
            //if($data_type>STRING) {
            $r_value=$colors[$i]["red"];
            $g_value=$colors[$i]["green"];
            $b_value=$colors[$i]["blue"];

            unset($color);
            $color=imagecolorallocate($im, $r_value, $g_value, $b_value);
        
            //imagefilledrectangle($im, $left, $top, $right, $bottom, $color);
            //imagerectangle($im, $left, $top, $right, $bottom, $black);
        
            if ($data_type>STRING) {
                //	$left+=$margin_size*1.5+($max_width-$caption_widths[$i]);
                //else if($data_type==STRING)
                $left+=$margin_size*1.5;
            }
            //}

            imagettftext($im, $caption_size, 0, $left, $top, $black, $caption_name, $data[$i]);
        }

        $filename="tmp/".uniqid($name).".png";
        imagepng($im, $filename);
        imagedestroy($im);

        return $filename;
    }

    function legendTable($name, $recordset, $caption_size, $refs)
    {
        $legend_data="";
        if ($data_type==PERCENT) {
            $legend_data=" %";
        } elseif ($data_type==CURRENCY_EUR) {
            $legend_data=" E";
        } elseif ($data_type==CURRENCY_GBP) {
            $legend_data=" £";
        } elseif ($data_type==CURRENCY_USD) {
            $legend_data=" $";
        } elseif ($data_type==NUMBER) {
            $legend_data="";
        } elseif ($data_type==PERCENT+CURRENCY_EUR) {
            $legend_data=" E";
        } elseif ($data_type==PERCENT+CURRENCY_GBP) {
            $legend_data=" £";
        } elseif ($data_type==PERCENT+CURRENCY_USD) {
            $legend_data=" $";
        } elseif ($data_type==PERCENT+NUMBER) {
            $legend_data="";
        }

        $data=$recordset["values"];
        $j=sizeof($data);

        $colors=color_range($j);
    
        $max_width=0;
        $max_height=0;

        $caption_widths=array();
        $i=0;
    
        if ($data_type==PERCENT) {
            $text=round($data[$i][0]/$total*100, 2);
            $text.=$legend_data;
        } else {
            $text=$data[$i][0].$legend_data;
        }

        $data[$i][0]=$text;
    
        $filenames=(array) null;
    
        if ($refs) {
            for ($i=0;$i<$j;$i++) {
                $left=0; //$caption_size;
            $top+=0; //$caption_size*1.5;
            $right=$left+$caption_size-1;
                $bottom=$top+$caption_size-1;
            
                $im=imagecreate($caption_size, $caption_size);
                $white=imagecolorallocate($im, 255, 255, 255);
                $black=imagecolorallocate($im, 0, 0, 0);
                $im_size=$caption_size-1;
                if ($border) {
                    imagerectangle($im, 0, 0, $im_size, $im_size, $black);
                }
        
                $r_value=$colors[$i]["red"];
                $g_value=$colors[$i]["green"];
                $b_value=$colors[$i]["blue"];
    
                unset($color);
                $color=imagecolorallocate($im, $r_value, $g_value, $b_value);
        
                imagefilledrectangle($im, 1, 1, $caption_size-2, $caption_size-2, $color);
                imagerectangle($im, $left, $top, $right, $bottom, $black);
            
                $filenames[$i]="tmp/".uniqid($name).".png";
            
                imagepng($im, $filenames[$i]);
                imagedestroy($im);
            }
        }
    
        $table="";
        $table.="<table cellspacing='0' cellpadding='0' border='1' bgcolor='black'>\n\t<tr>\n\t\t<td>\n";
        $table.="<table cellspacing='0' cellpadding='2' border='0'>\n";
        /*	$table.="\t<tr bgcolor='white'>\n";
            $table.="\t\t<td>&nbsp;</td><td>".implode("</td><td>", $recordset["names"])."</td>\n";
            $table.="\t</tr>\n";*/
        $nfields=sizeof($recordset["names"]);
        $table.="\t<tr bgcolor='white'>\n";
        $table.="\t\t<td>&nbsp;</td>";
        for ($k=0;$k<$nfields;$k++) {
            $table.="<td align='center'><b>".$recordset["names"][$k]."</b></td>";
        }
        $table.="</td>\n";
        $table.="\t</tr>\n";
        for ($i=0;$i<$j;$i++) {
            $table.="\t<tr bgcolor='white'>\n";
            $td="&nbsp;";
            if ($refs) {
                $td="<img src='$filenames[$i]'>";
            }
            $table.="\t\t<td>$td</td>";
            for ($k=0;$k<$nfields;$k++) {
                if ($recordset["types"][$k]=="int" || $recordset["types"][$k]=="real") {
                    $align=" align='right'";
                } else {
                    $align="";
                }
                $table.="<td$align>".$data[$i][$k]."</td>";
            }
            $table.="</td>\n";
            $table.="\t</tr>\n";
        }
        $table.="</table>\n";
        $table.="\t\t</td>\n\t</tr>\n</table>\n";
    
        return $table;
    }

    function camembert($name, $series, $highlight, $data_type, $sql, $width, $height, $caption_size, $caption_name, $cs)
    {
        $gh=$height;
        $gw=$width;
        $iw=$gw;
        $ih=$gh;

        $im=imagecreate($iw, $ih);
        $white=imagecolorallocate($im, 255, 255, 255);
        $black=imagecolorallocate($im, 0, 0, 0);
        $grey=imagecolorallocate($im, 216, 216, 216);
        $cx=$gw/2;
        $cy=$gh/2;
        $cw=$gw*0.7;
        $ch=$gh*0.7;
        $rx=$cw/2;
        $ry=$ch/2;
        $text_offsetX=$cw*0.37;
        $text_offsetY=$ch*0.37;

        $graph_data="";
        if ($data_type && PERCENT) {
            $graph_data=" %";
        }

        $ref_field=$series[0];
        $data_field=$series[0];
        if (sizeof($series)>1) {
            $data_field=$series[1];
        }

        $rst=getRecordset($sql, $cs);
        $names=$rst["names"];
        $values=$rst["values"];
        $nrows=sizeof($values);
    
        unset($ref_list);
        unset($data_list);
    
        for ($i=0;$i<$nrows;$i++) {
            $data_list.=$values[$i][$data_field].",";
            if ($data_field!=$ref_field) {
                $ref_list.=$values[$i][$ref_field].",";
            }
        }
        
        $data=array();
        $data=explode(",", $data_list);
        $empty_cell=array_pop($data);

        if (!empty($ref_list)) {
            $references=array();
            $references=explode(",", $ref_list);
            $empty_cell=array_pop($references);
        }
    
        $total=array_sum($data);
        $j=sizeof($data);
        $colors=color_range($j);
        $dark_colors=dark_color_range($j);

        unset($cumul);

        $start=180;
        $cumul=$start;
    
        if ($height!=$width) {
            $so=20;
        } else {
            $so=1;
        }
        //$cy+=abs($so);
    
        for ($k=$so; $k>0; $k--) {
            for ($i=0; $i<$j; $i++) {
                $p=$data[$i]/$total*360;
                $cumul+=$p;
    
                $angle=$cumul;
    
                $a_rad=deg2rad($angle);
    
                $r_value=$colors[$i]["red"];
                $g_value=$colors[$i]["green"];
                $b_value=$colors[$i]["blue"];
                
                $dr_value=$dark_colors[$i]["red"];
                $dg_value=$dark_colors[$i]["green"];
                $db_value=$dark_colors[$i]["blue"];
    
                unset($color);
                unset($dark_color);
                if ($k==1) {
                    $color=imagecolorallocate($im, $r_value, $g_value, $b_value);
                } else {
                    $color=imagecolorallocate($im, $dr_value, $dg_value, $db_value);
                }
    
                $end=$angle;
    
                if ($data[$i]==max($data)) {
                    $average=abs($start-$end)/2+$start;
                    $xx=$cx+cos(deg2rad($average))*$cw*0.05;
                    $yy=$cy+($k)+sin(deg2rad($average))*$ch*0.05;
                } else {
                    $xx=$cx;
                    $yy=$cy+($k);
                }
            
                imagefilledarc($im, $xx, $yy, $cw, $ch, $start, $end, $color, 4);
                
                if ($k==1 || $k==$so) {
                    $style=6;
                    imagefilledarc($im, $xx, $yy, $cw, $ch, $start, $end, $black, $style);
                } else {
                    $xx1=$xx+cos(deg2rad($start-1))*$rx;
                    $yy1=$yy+sin(deg2rad($start-1))*$ry;
                    $xx2=$xx+cos(deg2rad($end-1))*$rx;
                    $yy2=$yy+sin(deg2rad($end-1))*$ry;
                    imageline($im, $xx1, $yy1, $xx1, $yy1, $black);
                    imageline($im, $xx2, $yy2, $xx2, $yy2, $black);
                    imageline($im, $xx, $yy, $xx, $yy, $black);
                }
    
                $start=$end;
            }
        }
    
        if ($data_type==PERCENT
    || $data_type==PERCENT+CURRENCY_EUR
    || $data_type==PERCENT+CURRENCY_GBP
    || $data_type==PERCENT+CURRENCY_USD
    || $data_type==PERCENT+NUMBER) {
            unset($cumul);

            $start=180;
            $cumul=$start;
            $percent_size=floor($caption_size*0.8);
            for ($i=0; $i<$j; $i++) {
                $p=$data[$i]/$total*360;
                $cumul+=$p;

                $angle=$cumul;

                $a_rad=deg2rad($angle);

                $end=$angle;

                $average=abs($start-$end)/2+$start;
                $xx=$cx+cos(deg2rad($average))*$text_offsetX;
                $yy=$cy+sin(deg2rad($average))*$text_offsetY;
            
                $text=round($data[$i]/$total*100, 2);
            
                list($llx, $lly, $lrx, $lry, $urx, $ury, $ulx, $uly)=imageTTFbbox($percent_size, 0, $caption_name, $text);
            
                $caption_width=abs($llx)+$lrx;
                $caption_height=abs($uly-$lly);
            
                $xx-=$caption_width/2;
                $yy+=$caption_height/2;
            
                imagettftext($im, $percent_size, 0, $xx, $yy, $black, $caption_name, $text);
                //imagettftext($im, $percent_size, $average-180, $xx, $yy, $black, $caption_name, $text);

                $start=$end;
            }
        }

        $legend=legend_table($name."_legend", $rst, $caption_size, true);
    
        /*	if($data_field!=$ref_field)
                $legend=legend($name."_legend", $references, STRING, $caption_name, $caption_size, 12, true);
            else
                $legend=legend($name."_legend", $data, $data_type, $caption_name, $caption_size, 12, true);*/
    
        $filename="tmp/".uniqid($name).".png";
        imagepng($im, $filename);
        imagedestroy($im);

        return array($filename, $legend);
    }

    function histogram($name, $series, $highlight, $data_type, $unit, $sql, $width, $height, $percent_width, $caption_size, $caption_name, $color, $highlight_color, $cs)
    {
        $gh=$height*0.80;
        //$gw=$width*0.80;

        $gh_offset=$height-$gh;
        $gw_offset=$width-$gw;

        if (empty($color)) {
            $color="000080";
        }
        if (empty($highlight_color)) {
            $highlight_color="FF0000";
        }
    
        $red=hexdec(substr($color, 0, 2));
        $green=hexdec(substr($color, 2, 2));
        $blue=hexdec(substr($color, 4, 2));

        $hl_red=hexdec(substr($highlight_color, 0, 2));
        $hl_green=hexdec(substr($highlight_color, 2, 2));
        $hl_blue=hexdec(substr($highlight_color, 4, 2));

        $im=imagecreate($width, $height);
        $white=imagecolorallocate($im, 255, 255, 255);
        $grey=imagecolorallocate($im, 192, 192, 192);
        $black=imagecolorallocate($im, 0, 0, 0);
        $std_color=imagecolorallocate($im, $red, $green, $blue);
        $hl_color=imagecolorallocate($im, $hl_red, $hl_green, $hl_blue);
    
        $graph_data="";
        if ($data_type && PERCENT) {
            $graph_data=" %";
        }

        $ref_field=$series[0];
        $data_field=$series[0];
        if (sizeof($series)>1) {
            $data_field=$series[1];
        }

        $rst=getRecordset($sql, $cs);
        $names=$rst["names"];
        $values=$rst["values"];
        $nrows=sizeof($values);
    
        unset($ref_list);
        unset($data_list);
    
        for ($i=0;$i<$nrows;$i++) {
            $data_list.=$values[$i][$data_field].",";
            if ($data_field!=$ref_field) {
                $ref_list.=$values[$i][$ref_field].",";
            }
        }
        
        /*	unset($ref_list);
            unset($data_list);
            $stmt = $cs->query($sql);
        
            $j=$result->num_fields;*/
    
        $column_names=array();
        $column_names[0]="Colonne : ".$names[0];
        $column_names[1]="Ligne : ".$names[1];
        
        /*	while($rows=$stmt->fetch()) {
                $data_list.=$rows[$data_field].",";
                if($data_field!=$ref_field)
                    $ref_list.=$rows[$ref_field].",";
            }*/
    
        $data=array();
        $data=explode(",", $data_list);
        $empty_cell=array_pop($data);

        if (!empty($ref_list)) {
            $references=array();
            $references=explode(",", $ref_list);
            $empty_cell=array_pop($references);
        }
    
        $total=array_sum($data);
        $j=sizeof($data);

        asort($data);
        array_reverse($data);
        $max=floor(max($data)/$unit)+1;

        $caption_widths=array();
        $max_width=0;
        $max_height=0;
        $percent_size=floor($caption_size*0.80);
        for ($i=0; $i<$max; $i++) {
            $text=$unit*$i;

            list($llx, $lly, $lrx, $lry, $urx, $ury, $ulx, $uly)=imageTTFbbox($percent_size, 0, $caption_name, $text);
        
            $caption_width=abs($llx)+$lrx;
            $caption_height=abs($uly-$lly);
            
            if ($caption_width>$max_width) {
                $max_width=$caption_width;
            }
            if ($caption_height>$max_height) {
                $max_height=$caption_height;
            }

            $caption_widths[$i]=$caption_width;
        }

        $gw_offset=$max_width*1.2;
        $gw=$width-$gw_offset;

        $step=$gh/($max);
        imageline($im, $gw_offset, 0, $gw_offset, --$gh, $black);
        for ($i=0; $i<$max; $i++) {
            $text=$unit*$i;
            $h=$gh-$step*$i;
            imageline($im, $gw_offset-3, $h, $gw+$gw_offset+3, $h, $grey);
    
            $left=($max_width-$caption_widths[$i]);
            imagettftext($im, $percent_size, 0, $left, $h+$max_height/2, $black, $caption_name, $text);
        }
    
        if (empty($percent_width)) {
            $percent_width=0.75;
        }
        $bar_width=$gw/$j*$percent_width;
        $bar_space=$gw/$j-$bar_width;
        $base=$max*$unit;
        $height_limit=$step*$max;
    
        for ($i=0; $i<$j; $i++) {
            $percent=$data[$i]/$base;
            $top=$gh-$height_limit*$percent;
            $left=($bar_width+$bar_space)*$i+1+$gw_offset;
            $bottom=$gh;
            $right=$left+$bar_width;
            //if($i==$highlight)
            if ($data[$i]==max($data)) {
                imagefilledrectangle($im, $left, $top, $right, $bottom, $hl_color);
            } else {
                imagefilledrectangle($im, $left, $top, $right, $bottom, $std_color);
            }

            if ($data_type==PERCENT
        || $data_type==PERCENT+CURRENCY_EUR
        || $data_type==PERCENT+CURRENCY_GBP
        || $data_type==PERCENT+CURRENCY_USD
        || $data_type==PERCENT+NUMBER) {
                $text=round($data[$i]/$total*100, 2);
                imagettftext($im, $percent_size, 0, $left, $top-1, $black, $caption_name, $text);
            }
    
            $text=$data[$i];
            if ($data_field!=$ref_field) {
                $text=$references[$i];
            }
            imagettftext($im, $percent_size, 315, $left, $gh+$max_height+2, $black, $caption_name, $text);
        }
    
        /*
        if($data_field!=$ref_field)
            $legend=legend($name."_legend", $references, STRING, $caption_name, $caption_size, 12, true);
        else
            $legend=legend($name."_legend", $data, $data_type, $caption_name, $caption_size, 12, true);
        */
        $legend=legend_table($name."_legend", $rst, $caption_size, false);
    
        $filename="tmp/".uniqid($name).".png";
        imagepng($im, $filename);
        imagedestroy($im);

        return array($filename, $legend);
    }
}
