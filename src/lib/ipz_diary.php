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

/***************************************************************************
 *   copyleft	: (C) 2004 www.ipuzzle.net - David BLANCHARD
 *   copyleft   : (C) 2002-2003 PHPtools4U.com - Mathieu LESNIAK
 *
 *   This program was used and modified by David Blanchard to fit the iPuzzle.net project needs.
 *   So it is now released in version 1.0 with some changes :
 *      - new function diaryLang() to determine diary language,
 *	- diary() function was renamed as createDiaryControl() to respect the iPuzzle.net project API model,
 *      - CSS style has been temporarly removed to ease the use of the functions in the API.
 *
 *   This program was previously made and released by Mathieu Lesniak under these terms :
 *   	begin             : June 2002
 *      version           : 2.0 (may 03)
 *   	support (for v.2) : support@phptools4u.com
 *
 *   This program is free software; you can redistribute it and/or modify
 *   it under the terms of the GNU General Public License as published by
 *   the Free Software Foundation; either version 2 of the License, or
 *   (at your option) any later version.
 *
 ***************************************************************************/

function diaryLang($lg="fr") {

	$diary_txt=array();
	if(!isset($lg)) $lg="fr";
	switch($lg) {
	case "fr":
		 //French Version
		$diary_txt['fr']['months'] = array('', 'Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre');
		$diary_txt['fr']['days'] = array('Lundi', 'Mardi', 'Mercredi','Jeudi', 'Vendredi', 'Samedi',	'Dimanche');
		$diary_txt['fr']['first_day'] = 0;
		$diary_txt['fr']['misc'] = array('Mois précédent', 'Mois suivant','Jour précédent', 'Jour suivant');
	break;
	case "en":
		// English version
		$diary_txt['en']['months'] = array('', 'January', 'February', 'March',	'April', 'May', 'June', 'July', 'August', 'September', 'October','November', 'December');
		$diary_txt['en']['days'] = array('Monday', 'Tuesday', 'Wednesday','Thursday', 'Friday', 'Saturday','Sunday');
		$diary_txt['en']['first_day'] = -1;
		$diary_txt['en']['misc'] = array('Previous month', 'Next month', 'Previous day', 'Next day');
	break;
	case "es":								
		// Spanish version
		$diary_txt['es']['months'] = array('', 'Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre');
		$diary_txt['es']['days'] = array('Lunes', 'Martes', 'Mi&eacute;rcoles', 'Jueves', 'Viernes', 'S&aacute;bado', 'Domingo');
		$diary_txt['es']['first_day'] = 0;
		$diary_txt['es']['misc'] = array('Mes anterior', 'Mes pr&oacute;ximo', 'd&iacute;a anterior', 'd&iacute;a siguiente');
	break;
	case "de":	
		// German version
		$diary_txt['de']['months'] = array('', 'Januar', 'Februar', 'M&auml;rz', 'April', 'Mai', 'Juni', 'Juli', 'August', 'September', 'Oktober','November', 'Dezember');
		$diary_txt['de']['days'] = array('Montag', 'Dienstag', 'Mittwoch', 'Donnerstag','Freitag','Samstag', 'Sonntag');
		$diary_txt['de']['first_day'] = 0;
		$diary_txt['de']['misc'] = array('Vorhergehender Monat', 'Folgender Monat', 'Vorabend', 'Am n&auml;chsten Tag');
	break;
	}

	return $diary_txt;
}

function createDiaryControl($date, $colors=array()) {
	global $link_on_day, $PHP_SELF, $params;
	global $_POST, $_GET;
	global $diary_txt;
	global $img, $id;
	global $diarydb, $database, $db_prefix;
	global $diary_colors;
	
	if(isset($diary_colors)) {
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

	//$hl_back_color="grey";
    	//$id = getArgument('id');
	$lg = getArgument('lg');
	
	//echo $database;
	if(!isset($diarydb)) $diarydb=$database;
	
	$cs=connection(CONNECT, $diarydb);
	
	$date = getArgument('date');
	if(empty($date))
       		$sql_date="now()";
	else
		$sql_date=$date;
		
	$sql="select dy_date, dy_object from ${db_prefix}diary where month(dy_date)=month($sql_date) and year(dy_date)=year($sql_date) group by dy_date order by dy_date";

	//echo $sql;
	$stmt = $cs->query($sql);
	$rst=array();
	while($rows=$stmt->fetch(PDO::FETCH_ASSOC)) {
		$rst[]=$rows["dy_date"];
	}

	// Default parameters
	
	$params['diary_id']		= 1; // Calendar ID
	$params['diary_columns'] 	= 5; // Nb of columns
	$params['show_day'] 		= 1; // Show the day bar
	$params['show_month']		= 1; // Show the month bar
	$params['nav_link']		= 1; // Add a nav bar below
	$params['link_after_date']	= 1; // Enable link on days after the current day

	// Modified for Puzzle Project
	$params['link_on_day']		= $PHP_SELF.'?di=diary&lg='.$lg.'&date=%%dd%%'; // Link to put on each day
	$params['caption_face']		= 'Verdana, Arial, Helvetica'; // Default font to use
	$params['caption_size']		= 8; // Font size in px
	
	$params['use_img']		= 1; // Use gif for nav bar on the bottom
	
	// New parameters in old version 2.0
	$params['caption_highlight_color']= '#FF0000';
	$params['bg_highlight_color']  = '#00FF00';
	$params['day_mode']		= 0;
	$params['time_step']		= 30;
	$params['time_start']		= '0:00';
	$params['time_stop']		= '24:00';
	$params['highlight']		= array();
	
	// Can be 'hightlight' or 'text'
	$params['highlight_type']      = 'highlight';
	$params['cell_width']          = 16;
	$params['cell_height']         = 16;
	$params['short_day_name']      = 1;
	// Modified for Puzzle Project
	//$params['link_on_hour']        = $PHP_SELF.'?hour=%%hh%%';
	$params['link_on_hour']	= $PHP_SELF.'?id=42&lg='.$lg.'&hour=%%hh%%';

	$diary_txt = diaryLang($lg);
	
	$month_names = $diary_txt[$lg]['months'];
	$params['diary_columns'] = ($params['show_day']) ? 7 : $params['diary_columns'];
    
	if ($date == '') {
		$timestamp = time();
	}
	else {
		$month 		= substr($date, 4 ,2);
		$day 		= substr($date, 6, 2);
		$year		= substr($date, 0 ,4);
		$timestamp 	= mktime(0, 0, 0, $month, $day, $year);
	}
    
    
	$current_day 		= date("d", $timestamp);
	$current_month 		= date('n', $timestamp);
	$current_month_2	= date('m', $timestamp);
	$current_year 		= date('Y', $timestamp);
	
    	$first_shift 	= date("w", mktime(0, 0, 0, $current_month, 1, $current_year));
	
	// Sunday is the _LAST_ day
	$first_shift		= ( $first_shift == 0 ) ? 7 : $first_shift;
	
	$current_day_id 	= date('w', $timestamp);
	$current_day_name	= $diary_txt[$lg]['days'][$current_day_id];
	$current_month_name 	= $month_names[$current_month];
	$nb_days_month 		= date("t", $timestamp);
	
	$current_timestamp 	= mktime(23,59,59,date("m"), date("d"), date("Y"));
	
	$output='';
	$output .= '<table border="0" bgcolor="'.$back_color.'" cellpadding="1" cellspacing="0" width="100%">'."\n";
	
	// Displaying the current month/year
	if ($params['show_month'] == 1) {
		$output .= '<tr>'."\n";
		$output .= '	<td bgcolor="'.$border_color.'" colspan="'.$params['diary_columns'].'" align="center" style="color: white">'."\n";
		if ( $params['day_mode'] == 1 ) {
			$output .= '		'.$current_day_name.' '.$current_day.' '.$current_month_name.' '.$current_year."\n";
		}
		else {
			$output .= '		'.$current_month_name.' '.$current_year."\n";
		}
		$output .= '	</td>'."\n";
		$output .= '</tr>'."\n";
	}
	
	// Building the table row with the days
	if ($params['show_day'] == 1 && $params['day_mode'] == 0) {
		$output .= '<tr align="center">'."\n";
		$first_day = $diary_txt[$lg]['first_day'];
		for ($i = $first_day; $i < 7 + $first_day; $i++) {
			
			$index = ( $i >= 7) ? (7 + $i): $i;
			$index = ($i < 0) ? (7 + $i) : $i;
		    
			$day_name = ( $params['short_day_name'] == 1 ) ? substr($diary_txt[$lg]['days'][$index], 0, 1) : $diary_txt[$lg]['days'][$index];
			$output .= '	<td><b>'.$day_name.'</b></td>'."\n";
		}
		
		$output .= '</tr>'."\n";	
		$first_shift = $first_shift - $diary_txt[$lg]['first_day'];
		$first_shift = ( $first_shift > 7 ) ? $first_shift - 7 : $first_shift;
	}
	else {
		$first_shift = 0;	
	}
	
	$output .= '<tr align="center">';
	$int_counter = 0;
	
	
	// Filling with empty cells at the begining
	for ($i = 1; $i < $first_shift; $i++) {
		$output .= '<td>&nbsp;</td>'."\n";
		$int_counter++;
	}
	// Building the table
	$k=0;
	$jour=0;
	if(isset($rst[$k])) $jour=$rst[$k];
	for ($i = 1; $i <= $nb_days_month; $i++) {
		// Do we highlight the current day ?
		$j = ($i < 10) ? '0'.$i : $i;		
	    	$highlight_current = ( isset($params['highlight'][date('Ym', $timestamp).$j]) );	
		// Row start
		if ( ($i + $first_shift) % $params['diary_columns'] == 2 && $i != 1) {
			$output .= '<tr align="center">'."\n";
			$int_counter = 0;
		}
		if($jour==$current_year.'-'.$current_month_2.'-'.$j) {
			$text_color=$hl_text_color;
			$bgstyle=' style="background: '.$hl_back_color.'; color: '.$hl_text_color.'; font-size: 10;"';
			$k++;
			$jour=$rst[$k];
		} else {
			$text_color=$fore_color;
			$bgstyle=' style="font-size: 10;"';
		}
			
		$txt_2_use = ( $highlight_current && $params['highlight_type'] == 'text') ? '<br>'.$params['highlight'][date('Ym', $timestamp).$j] : '';
           
           	if ($i == $current_day) {
			$output .= '<td align="center" bgcolor="'.$fore_color.'" style="color: white">'.$i.$txt_2_use.'</td>'."\n";
		} else if ($params['link_on_day'] != '') {
			$loop_timestamp = mktime(0,0,0, $current_month, $i, $current_year);
			
			if(($params['link_after_date'] == 0) && ($current_timestamp < $loop_timestamp)) {
				$output .= '<td'.$bgstyle.'>'.$i.$txt_2_use.'</td>'."\n";
			} else {
				$output .= '<td'.$bgstyle.'><a href="'.str_replace('%%dd%%', $current_year.$current_month_2.$j ,$params['link_on_day']).'"><span style="color:'.$text_color.'">'.$i.'</span></a>'.$txt_2_use.'</td>'."\n";
			}
		} else {
			$output .= '<td>'.$i.'</td>'."\n";
		}	
		$int_counter++;
		
		// Row end
		if (  ($i + $first_shift) % ($params['diary_columns'] ) == 1 ) {
			$output .= '</tr>'."\n";	
		}
	}
	$missing_cell = $params['diary_columns'] - $int_counter;
	
	for ($i = 0; $i < $missing_cell; $i++) {
		$output .= '<td>&nbsp;</td>'."\n";
	}
	$output .= '</tr>'."\n";

	// Display the nav links on the bottom of the table
	$previous_month = date("Ymd", mktime(12, 0, 0, ($current_month - 1), $current_day, $current_year));
	$previous_day 	= date("Ymd", mktime(12, 0, 0, $current_month, $current_day - 1, $current_year));
       	$next_day	= date("Ymd", mktime(1, 12, 0, $current_month, $current_day + 1, $current_year));
	$next_month	= date("Ymd", mktime(1, 12, 0, $current_month + 1, $current_day, $current_year));
	
	$gg = '<img src="'.$img.'/scroll/fastLeft_0.gif" border="0">';
	$g = '<img src="'.$img.'/scroll/left_0.gif" border="0">';
	$d = '<img src="'.$img.'/scroll/right_0.gif" border="0">';
	$dd = '<img src="'.$img.'/scroll/fastRight_0.gif" border="0">';

	$this_timestamp=mktime(0,0,0, $current_month, $current_day+1, $current_year);
	if(($params['link_after_date'] == 0) && ($current_timestamp < $this_timstamp)) {
       		$next_day_link = '&nbsp;';
	} else {
		$next_day_link = '<a href="'.$PHP_SELF.'?id='.$id.'&lg='.$lg.'&date='.$next_day.'" title="'.$diary_txt[$lg]['misc'][3].'">'.$d.'</a>'."\n";
	}
	
	$this_timestamp=mktime(0,0,0, $current_month+1, $current_day, $current_year);
	if(($params['link_after_date'] == 0) && ($current_timestamp < $this_timestamp)) {
		$next_month_link = '&nbsp;';		
	} else {
		$next_month_link = '<a href="'.$PHP_SELF.'?id='.$id.'&lg='.$lg.'&date='.$next_month.'" title="'.$diary_txt[$lg]['misc'][1].'">'.$dd.'</a>'."\n";
	}

	$output .= '<tr>'."\n";
	//$output .= '	<td colspan="'.$params['diary_columns'].'" class="diaryDays'.$params['diary_id'].'">'."\n";
	$output .= '	<td colspan="7">'."\n";
	$output .= '		<table width="100%" border="0">';
	$output .= '		<tr>'."\n";
	$output .= '			<td width="25%" align="left">'."\n";
	$output .= '				<b><A href="'.$PHP_SELF.'?id='.$id.'&lg='.$lg.'&date='.$previous_month.'" title="'.$diary_txt[$lg]['misc'][0].'">'.$gg.'</a></b>'."\n";
	$output .= '			</td>'."\n";
	$output .= '			<td width="25%" align="center">'."\n";
	$output .= '				<a href="'.$PHP_SELF.'?id='.$id.'&lg='.$lg.'&date='.$previous_day.'" title="'.$diary_txt[$lg]['misc'][2].'">'.$g.'</a>'."\n";
	$output .= '			</td>'."\n";
	$output .= '			<td width="25%" align="center">'."\n";
	$output .= 					$next_day_link;
	$output .= '			</td>'."\n";
	$output .= '			<td width="25%" align="right">'."\n";
	$output .= 					$next_month_link;
	$output .= '			</td>'."\n";
	$output .= '		</tr>';
	$output .= '		</table>';
	$output .= '	</td>'."\n";
	$output .= '</tr>'."\n";
		
	$output .= '</table>'."\n";

	return $output;
}

function createFramedDiaryControl($date, $target, $colors=array()) {
	global $link_on_day, $PHP_SELF, $params;
	global $_POST, $_GET;
	global $diary_txt;
	global $img, $id;
	global $diarydb, $database, $db_prefix;
	global $diary_colors;
	
	if(isset($diary_colors)) {
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

	//$hl_back_color="grey";
    	//$id = getArgument('id');
	$lg = getArgument('lg');
	if(empty($diarydb)) $diarydb=$database;
	
	$cs=connection(CONNECT, $diarydb);
	
	$date = getArgument('date');
	if(empty($date))
       		$sql_date="now()";
	else
		$sql_date=$date;
		
	$sql="select dy_date, dy_object from ${db_prefix}diary where month(dy_date)=month($sql_date) and year(dy_date)=year($sql_date) group by dy_date order by dy_date";

	$stmt = $cs->query($sql);
	$rst=array();
	while($rows=$stmt->fetch(PDO::FETCH_ASSOC)) {
		$rst[]=$rows["dy_date"];
	}

	// Default parameters
	
	$params['diary_id']		= 1; // Calendar ID
	$params['diary_columns'] 	= 5; // Nb of columns
	$params['show_day'] 		= 1; // Show the day bar
	$params['show_month']		= 1; // Show the month bar
	$params['nav_link']		= 1; // Add a nav bar below
	$params['link_after_date']	= 1; // Enable link on days after the current day

	// Modified for Puzzle Project
	$params['link_on_day']		= 'page.html?di=diary&lg='.$lg.'&date=%%dd%%'; // Link to put on each day
	$params['caption_face']		= 'Verdana, Arial, Helvetica'; // Default font to use
	$params['caption_size']		= 8; // Font size in px
	
	$params['use_img']		= 1; // Use gif for nav bar on the bottom
	
	// New parameters in old version 2.0
	$params['caption_highlight_color']= '#FF0000';
	$params['bg_highlight_color']  = '#00FF00';
	$params['day_mode']		= 0;
	$params['time_step']		= 30;
	$params['time_start']		= '0:00';
	$params['time_stop']		= '24:00';
	$params['highlight']		= array();
	
	// Can be 'hightlight' or 'text'
	$params['highlight_type']      = 'highlight';
	$params['cell_width']          = 16;
	$params['cell_height']         = 16;
	$params['short_day_name']      = 1;
	// Modified for Puzzle Project
	//$params['link_on_hour']        = $PHP_SELF.'?hour=%%hh%%';
	$params['link_on_hour']	= 'page.html?di=diary&lg='.$lg.'&hour=%%hh%%';

	$diary_txt = diaryLang($lg);
	
	$month_names = $diary_txt[$lg]['months'];
	$params['diary_columns'] = ($params['show_day']) ? 7 : $params['diary_columns'];
    
	if ($date == '') {
		$timestamp = time();
	}
	else {
		$month 		= substr($date, 4 ,2);
		$day 		= substr($date, 6, 2);
		$year		= substr($date, 0 ,4);
		$timestamp 	= mktime(0, 0, 0, $month, $day, $year);
	}
    
    
	$current_day 		= date("d", $timestamp);
	$current_month 		= date('n', $timestamp);
	$current_month_2	= date('m', $timestamp);
	$current_year 		= date('Y', $timestamp);
	
    	$first_shift 	= date("w", mktime(0, 0, 0, $current_month, 1, $current_year));
	
	// Sunday is the _LAST_ day
	$first_shift		= ( $first_shift == 0 ) ? 7 : $first_shift;
	
	$current_day_id 	= date('w', $timestamp);
	$current_day_name	= $diary_txt[$lg]['days'][$current_day_id];
	$current_month_name 	= $month_names[$current_month];
	$nb_days_month 		= date("t", $timestamp);
	
	$current_timestamp 	= mktime(23,59,59,date("m"), date("d"), date("Y"));
	
	$output='';
	$output .= '<table border="0" bgcolor="'.$back_color.'" cellpadding="1" cellspacing="0" width="100%">'."\n";
	
	// Displaying the current month/year
	if ($params['show_month'] == 1) {
		$output .= '<tr>'."\n";
		$output .= '	<td bgcolor="'.$border_color.'" colspan="'.$params['diary_columns'].'" align="center" style="color: white">'."\n";
		if ( $params['day_mode'] == 1 ) {
			$output .= '		'.$current_day_name.' '.$current_day.' '.$current_month_name.' '.$current_year."\n";
		}
		else {
			$output .= '		'.$current_month_name.' '.$current_year."\n";
		}
		$output .= '	</td>'."\n";
		$output .= '</tr>'."\n";
	}
	
	// Building the table row with the days
	if ($params['show_day'] == 1 && $params['day_mode'] == 0) {
		$output .= '<tr align="center">'."\n";
		$first_day = $diary_txt[$lg]['first_day'];
		for ($i = $first_day; $i < 7 + $first_day; $i++) {
			
			$index = ( $i >= 7) ? (7 + $i): $i;
			$index = ($i < 0) ? (7 + $i) : $i;
		    
			$day_name = ( $params['short_day_name'] == 1 ) ? substr($diary_txt[$lg]['days'][$index], 0, 1) : $diary_txt[$lg]['days'][$index];
			$output .= '	<td><b>'.$day_name.'</b></td>'."\n";
		}
		
		$output .= '</tr>'."\n";	
		$first_shift = $first_shift - $diary_txt[$lg]['first_day'];
		$first_shift = ( $first_shift > 7 ) ? $first_shift - 7 : $first_shift;
	}
	else {
		$first_shift = 0;	
	}
	
	$output .= '<tr align="center">';
	$int_counter = 0;
	
	
	// Filling with empty cells at the begining
	for ($i = 1; $i < $first_shift; $i++) {
		$output .= '<td>&nbsp;</td>'."\n";
		$int_counter++;
	}
	// Building the table
	$k=0;
	$jour=0;
	if(isset($rst[$k])) $jour=$rst[$k];
	for ($i = 1; $i <= $nb_days_month; $i++) {
		// Do we highlight the current day ?
		$j = ($i < 10) ? '0'.$i : $i;		
	    	$highlight_current = ( isset($params['highlight'][date('Ym', $timestamp).$j]) );	
		// Row start
		if ( ($i + $first_shift) % $params['diary_columns'] == 2 && $i != 1) {
			$output .= '<tr align="center">'."\n";
			$int_counter = 0;
		}
		if($jour==$current_year.'-'.$current_month_2.'-'.$j) {
			$text_color=$hl_text_color;
			$bgstyle=' style="background: '.$hl_back_color.'; color: '.$hl_text_color.'; font-size: 10;"';
			$k++;
			$jour=$rst[$k];
		} else {
			$text_color=$fore_color;
			$bgstyle=' style="font-size: 10;"';
		}
			
		$txt_2_use = ( $highlight_current && $params['highlight_type'] == 'text') ? '<br>'.$params['highlight'][date('Ym', $timestamp).$j] : '';
           
           	if ($i == $current_day) {
			$output .= '<td align="center" bgcolor="'.$fore_color.'" style="color: white">'.$i.$txt_2_use.'</td>'."\n";
		} else if ($params['link_on_day'] != '') {
			$loop_timestamp = mktime(0,0,0, $current_month, $i, $current_year);
			
			if(($params['link_after_date'] == 0) && ($current_timestamp < $loop_timestamp)) {
				$output .= '<td'.$bgstyle.'>'.$i.$txt_2_use.'</td>'."\n";
			} else {
				$date_dd="date=$current_year$current_month_2$j";
				$page_link_of_day="page.html?di=diary&lg=$lg&$date_dd";
				$menu_link_of_day="$PHP_SELF?id=$id&lg=$lg&$date_dd";
				$output .= "<td$bgstyle><a href='$page_link_of_day' target='$target' onClick='parent.frames[\"menu\"].location.href=\"$menu_link_of_day\";'><span style='color:$text_color'>$i</span></a>$txt_2_use</td>\n";
			}
		} else {
			$output .= '<td>'.$i.'</td>'."\n";
		}	
		$int_counter++;
		
		// Row end
		if (  ($i + $first_shift) % ($params['diary_columns'] ) == 1 ) {
			$output .= '</tr>'."\n";	
		}
	}
	$missing_cell = $params['diary_columns'] - $int_counter;
	
	for ($i = 0; $i < $missing_cell; $i++) {
		$output .= '<td>&nbsp;</td>'."\n";
	}
	$output .= '</tr>'."\n";

	// Display the nav links on the bottom of the table
	$previous_month = date("Ymd", mktime(12, 0, 0, ($current_month - 1), $current_day, $current_year));
	$previous_day 	= date("Ymd", mktime(12, 0, 0, $current_month, $current_day - 1, $current_year));
       	$next_day	= date("Ymd", mktime(1, 12, 0, $current_month, $current_day + 1, $current_year));
	$next_month	= date("Ymd", mktime(1, 12, 0, $current_month + 1, $current_day, $current_year));
	
	$gg = '<img src="'.$img.'/scroll/fastLeft_0.gif" border="0">';
	$g = '<img src="'.$img.'/scroll/left_0.gif" border="0">';
	$d = '<img src="'.$img.'/scroll/right_0.gif" border="0">';
	$dd = '<img src="'.$img.'/scroll/fastRight_0.gif" border="0">';

	$page_link_of="page.html?di=diary&lg=$lg&date=";
	$menu_link_of="$PHP_SELF?id=$id&lg=$lg&date=";
	
	$previous_day_link = "<a href='$page_link_of$previous_day' title='".$diary_txt[$lg]['misc'][0]."' target='$target' onClick='parent.frames[\"menu\"].location.href=\"$menu_link_of$previous_day\";'>$g</a>\n";
	
	$this_timestamp=mktime(0,0,0, $current_month, $current_day+1, $current_year);
	if(($params['link_after_date'] == 0) && ($current_timestamp < $this_timstamp)) {
       		$next_day_link = '&nbsp;';
	} else {
		
		$next_day_link = "<a href='$page_link_of$next_day' title='".$diary_txt[$lg]['misc'][1]."' target='$target' onClick='parent.frames[\"menu\"].location.href=\"$menu_link_of$next_day\";'>$d</a>\n";
	}
	
	$previous_month_link = "<a href='$page_link_of$previous_month' title='".$diary_txt[$lg]['misc'][2]."' target='$target' onClick='parent.frames[\"menu\"].location.href=\"$menu_link_of$previous_month\";'>$gg</a>\n";
	
	$this_timestamp=mktime(0,0,0, $current_month+1, $current_day, $current_year);
	if(($params['link_after_date'] == 0) && ($current_timestamp < $this_timestamp)) {
		$next_month_link = '&nbsp;';
	} else {
		$next_month_link = "<a href='$page_link_of$next_month' title='".$diary_txt[$lg]['misc'][3]."' target='$target' onClick='parent.frames[\"menu\"].location.href=\"$menu_link_of$next_month\";'>$dd</a>\n";
	}

	$output .= '<tr>'."\n";
	$output .= '	<td colspan="7">'."\n";
	$output .= '		<table width="100%" border="0">';
	$output .= '		<tr>'."\n";
	$output .= '			<td width="25%" align="left">'."\n";
	$output .= 				$previous_month_link;
	$output .= '			</td>'."\n";
	$output .= '			<td width="25%" align="center">'."\n";
	$output .= 				$previous_day_link;
	$output .= '			</td>'."\n";
	$output .= '			<td width="25%" align="center">'."\n";
	$output .= 				$next_day_link;
	$output .= '			</td>'."\n";
	$output .= '			<td width="25%" align="right">'."\n";
	$output .= 				$next_month_link;
	$output .= '			</td>'."\n";
	$output .= '		</tr>';
	$output .= '		</table>';
	$output .= '	</td>'."\n";
	$output .= '</tr>'."\n";
		
	$output .= '</table>'."\n";

	return $output;
}

function createDiaryGrid($name="", $date="", $id=0, $page_link="",  $curl_rows="", $can_add, $dialog, $col_widths, $colors, $conn) { 
/*
	Desciption des paramètres :

*/
	global $img, $lg, $id;


	$dday = substr($date,6,2);
	$mmonth = substr($date,4,2);
	$yyear = substr($date,0,4);

	$sql=	"select dy_id, dy_date, dy_time, dy_length, dy_object, dy_description, dy_place ".
		"from diary ".
		"where year(dy_date) = '$yyear' ".
		"and month(dy_date) = '$mmonth' ".
		"and dayofmonth(dy_date) = '$dday' ".
		"order by hour(dy_time), minute(dy_time)";

	
	//Détermine les couleurs du dbGrid
	if(!empty($colors)) { 
		global $grid_colors, $panel_colors;
		$color=$grid_colors;
	}
	
	if(!empty($colors)) {
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
	if($image_link=="") $image_link="img/Editer.png";

	//Détermine la langue de la page qui sera affichée
	//$lg=getArgument("lg");
	$add="Ajouter";
	
	/*
	Y a-t-il un complément d'URL en paramètre ?
	Si oui on sépare les noms de variables de leurs valeurs
	et on place les valeur indicant des champs de la requête dans un tableau.
	On concatène les autres variables avec leurs valeurs.
	*/
	if($curl_rows!="") {
		$acompl_url=array();
		$vars=explode("&", $curl_rows);
		$curl_rows2="";
		for($i=1; $i<count($vars); $i++) {
			$var=explode("=", $vars[$i]);
			if(substr($var[1],0,1)=="#") {
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
	if(substr($page_link,0,1)=="|") {
       		$image_field=substr($page_link, 1, strlen($page_link)-1);
		$is_image=true;
	}
	if(substr($page_link,0,1)=="&") {
       		$web_field=substr($page_link, 1, strlen($page_link)-1);
	} elseif(substr($page_link,0,1)=="@") {
       		$web_field=substr($page_link, 1, strlen($page_link)-1);
		$is_url=true;
	}

	//echo "$sql<p>";
	$result = $cs->query($sql, $conn);
	$num=$result->num_rows;
	$r=0;
	$div="";
	$i=0;
	$hours=array(8,9,10,11,12,13,14,15,16,17,18,19,20,21);
	$tdname0="td$name$i";
	$_SESSION["javascript"].="\tvar vdiv$name=eval(document.getElementById(\"div$name\"));\n";
	$_SESSION["javascript"].="\tvar v$name=eval(document.getElementById(\"$name\"));\n";
	$_SESSION["javascript"].="\tvar v$tdname0=eval(document.getElementById(\"$tdname0\"));\n";
	while($rows=$stmt->fetch(PDO::FETCH_ASSOC)) {
		$obj=$rows["dy_object"];
		$len=$rows["dy_length"];
		$time=$rows["dy_time"];
		$comment=$rows["dy_description"];

		$len=abs($len)+($len-abs($len))/0.6;
		
		$hhour = substr($time,0,2);
		$mminutes1 = substr($time,3,2);
		$mminutes2 = $mminutes1/60;
		$hhour=$hhour+$mminutes2-$hours[0];
		$panel_width=$col_widths[1]-10;
		
		//echo "time='$time'; hhour='$hhour'; minutes1='$mminutes1'; minutes2='$mminutes2'<br>";
		
		$_SESSION["javascript"].="\tvar vrdv$name$i=eval(document.getElementById(\"rdv$name$i\"));\n";
		$_SESSION["javascript"].="\tvar vdiv$name$i=eval(document.getElementById(\"div$name$i\"));\n";
		$_SESSION["javascript"].="\tvdiv$name$i.style.width=".$panel_width."+\"px\";\n";
		$_SESSION["javascript"].="\tvdiv$name$i.style.height=(v$tdname0.offsetHeight+1)*$len-1+\"px\";\n";
		$_SESSION["javascript"].="\tvdiv$name$i.style.left=v$tdname0.offsetWidth+vdiv$name.offsetLeft+v$name.offsetLeft+9+\"px\";\n";
		$_SESSION["javascript"].="\tvdiv$name$i.style.top=(v$tdname0.offsetHeight+1)*$hhour+v$name.offsetTop+vdiv$name.offsetTop+v$tdname0.offsetTop+\"px\";\n";
		$panels.="<div id=\"div$name$i\" style=\"background:$pager_color;z-index:1;position:absolute;\">\n";
		$panels.=createEnhancedPanel("div$name$i", "$time $obj", $comment, "font-size: 10;", "", "0", "100%", $panel_width, $panel_colors);
		$panels.="</div>\n";	
		$i++;
	}
	//if($num) {

	//Les colonnes auront la largeur définie par ordre d'indexation dans le tableau $col_width.
	//Si le nombre de largeurs définies est inférieur on aggrandi le tableau avec des valeurs à 0.
	$width_count=count($col_widths);
	$i=$result->num_fields;
	if($width_count<$i) {
		
		$j=$i-$width_count;
		$a=array_fill($width_count, $j, 0);
		$col_widths=array_merge($col_widths, $a);
	}
	
	$table="";

	$table1="";
	$table1.="<table id='$name' border='0' cellpadding='1' cellspacing='1' bordercolor='$border_color' width='100%'>\n".
		"<tr bgcolor='$header_back_color'>\n";
	//$index_fieldname=$field->name;
	for($j=0; $j<2; $j++) {
		$tag_width="";
		$tag_width=" width='".$col_widths[$j]."'";
		if($j==0) {
			$text="&nbsp;";
			$table1.="<td align=center><span style='color:$header_fore_color'><b>$text<b></span></td>\n";
		} else {
			$text="Journée du $dday/$mmonth/$yyear";
			$table1.="<td align=center><span style='color:$header_fore_color'><b>$text<b></span></td>\n";
		}
	}
	$table1.="</tr>\n";
	$r=0;
	foreach($hours as $hour) {
		$on_mouse_over="";
		$on_mouse_out="";
	
		$r1=$r/2;
		$r2=round($r1);
		if($r1==$r2) {
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
		
		$js_events="";
		if(isNum($id))
			$page_id="id=$id&lg=$lg";
		else
			$page_id="di=$id&lg=$lg";

		if($page_link!="") $url="$page_link?$page_id&$index_fieldname=".$index_value."&action=Modifier";
		$ahref="<a href='$url$curl_rows'$target>";
		$a="</a>";
			
		$on_mouse_over.="setRowColor(this, hlBackColor, hlTextColor);";
		$on_mouse_out.="setBackRowColor(this);";

		$js_events=" onMouseOver=\"$on_mouse_over\" onMouseOut=\"$on_mouse_out\"";
		$table1.="<tr id='t1$name$r' bgcolor='$back_color' style='height:15;'$js_events>";
							
		for($j=0; $j<2; $j++) {
		
			if(!empty($curl_rows)) $url.=$curl_rows;
			$tag_width="";
			$tag_width=" width='100%'";
			$tag_width=" width='".$col_widths[$j]."'";
			
			if($j==0) {
				$tag_align=" align='center'";
				$on_click="";
				if(!empty($dialog)) $on_click=" onClick=\"".OpenDialog($url, $dialog[0], $dialog[1])."\"";
				$field=$hour."h00";
				$table1.="<td id=\"td$name$r\" style=\"font-size:10\" valign=\"top\"$tag_width>$field</td>\n";
			} else if($j==1) {
				if($fieldtype=="date") $field = preg_replace('^([0-9]{2,4})-([0-9]{1,2})-([0-9]{1,2})$', '\3/\2/\1', $field);
				$tag_align=" align='left'";
				if($fieldtype=="int") $tag_align=" align='right'";
				if($fieldlen < 5) $tag_align=" align='center'";
				$c=$j-1;
				$table1.="<td $tag_align style=\"font-size:10;height:15\"$tag_width><span id='caption_$name$r$c'style='color:$fore_color'>&nbsp;<br>&nbsp;</span></td>\n";
			}
		}
		$table1.="</tr>\n";
		$r++;
	}
	
	$table1.="</table>\n";

	//$stmt->free();

	
	$table="<div id=\"div$name\" style=\"z-index:1;position:relative;\">\n";
	$table.=$table1;
	$table.="</div>";
	$table.=$panels;
	

	return $table;
}
