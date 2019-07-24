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

class Pieces
{
    public function __construct()
    {
        $this->_init_javascript();
    }

    protected function _init_javascript()
    {
        $js = <<<JAVASCRIPT
<script language="JavaScript" src="js/pz_shared.js"></script>
<script language="JavaScript" src="js/pz_scroller.js"></script>
<script language="JavaScript" src="js/pz_cursor.js"></script>
JAVASCRIPT;

		echo $js;
	}
	
    public function debugScroller($switch=false)
    {
        if ($switch) {
            echo '

	<div id="myDebug1" style="z-index:1;position:absolute;left:0px;top:0px;">
	<form method="post" name="debug">
	<table>
	<tr>
		<td>Top</td><td><input type="text" name="top" size="5"></td>
		<td>Left</td><td><input type="text" name="left" size="5"></td>
		<td>Width</td><td><input type="text" name="width" size="5"></td>
		<td>Height</td><td><input type="text" name="height" size="5"></td>
		<td>Step</td><td><input type="text" name="step" size="5"></td>
		<td>CursorTop</td><td><input type="text" name="cursortop" size="5"></td>
	</tr>
	<tr>
		<td>clipTop</td><td><input type="text" name="cliptop" size="5"></td>
		<td>clipLeft</td><td><input type="text" name="clipleft" size="5"></td>
		<td>clipWidth</td><td><input type="text" name="clipwidth" size="5"></td>
		<td>clipHeight</td><td><input type="text" name="clipheight" size="5"></td>
		<td>Direction</td><td><input type="text" name="direction" size="5"></td>
		<td>CursorLeft</td><td><input type="text" name="cursorleft" size="5"></td>
	</tr>
	<tr>
		<td>offsetTop</td><td><input type="text" name="offsettop" size="5"></td>
		<td>offsetLeft</td><td><input type="text" name="offsetleft" size="5"></td>
		<td>tableWidth</td><td><input type="text" name="tablewidth" size="5"></td>
		<td>tableHeight</td><td><input type="text" name="tableheight" size="5"></td>
		<td>cursorXPos (%)</td><td><input type="text" name="cursorxpos" size="5"></td>
		<td>cursorYPos (%)</td><td><input type="text" name="cursorypos" size="5"></td>
	</tr>
	</table>
	</form>
	</div>
	';
        }
    }

    public function container($name, $top, $left, $height, $width, $cursor_width)
    {
        echo '
<div id="scrX_'. $name.' style="position:absolute;z-index:1;height:16px;visibility:visible" onMouseOver="PZ_SCROLLBOX=my_'.$name.';PZ_CURSOR=my_'.$name.'.cursorX;">
	<table height="'. $cursor_width.'" width="'. $width.'" cellpadding="0" cellspacing="0" border="0">
	<tr>
	<td id="butL_'. $name.'"> <img src="/img/scroll/scrollerXleft.png" height="16" width="13"
			onMouseDown="PZ_SCROLLBOX.goLeft();"
			onMouseUp="PZ_SCROLLBOX.stop();"
		> </td>
	<td id="accX_'. $name.'"> <img width="'. ($width-26) .'" height="16" src="/img/scroll/scrollerXmiddle.png"
			onMouseDown="PZ_SCROLLBOX.goFastLeft();"
			onMouseUp="PZ_SCROLLBOX.stop();"
		> </td>
	<td id="butR_'. $name.'"> <img src="/img/scroll/scrollerXright.png" height="16" width="13"
			onMouseDown="PZ_SCROLLBOX.goRight();"
			onMouseUp="PZ_SCROLLBOX.stop();"
		> </td>
	</tr>
	</table>
</div>

<div id="scrY_'. $name.'" style="position:absolute;z-index:1;visibility:visible" onMouseOver="PZ_SCROLLBOX=my_'. $name.';PZ_CURSOR=my_'. $name.'.cursorY;"> 
	<table width="'. $cursor_width.'" height="'. $height.'" cellpadding="0" cellspacing="0" border="0">
	<tr>
	<td id="butU_'. $name.'" width="16" height="13">
               <img src="/img/scroll/scrollerYtop.png" width="16" height="13"
	       		onMouseDown="PZ_SCROLLBOX.goUp();"
			onMouseUp="PZ_SCROLLBOX.stop();"
		></td>	
	</tr>
	<tr>
	<td id="accY_'. $name.'" width="16" height="'. ($height-26) .'">
               <img src="/img/scroll/scrollerYmiddle.png" width="16" height="'. ($height-26).'"
	       		onMouseDown="PZ_SCROLLBOX.goFastUp();"
			onMouseUp="PZ_SCROLLBOX.stop();"
		></td>
	</tr>
	<tr>
	<td id="butD_'. $name.'" width="16" height="13">
	        <img src="/img/scroll/scrollerYbottom.png" width="16" height="13"
		        onMouseDown="PZ_SCROLLBOX.goDown();"
			onMouseUp="PZ_SCROLLBOX.stop();"
		></td>
	</tr>
	</table>
</div>

<div id="curX_'. $name.'" style="position:absolute;z-index:2;visibility:visible" onMouseOver="PZ_SCROLLBOX=my_'. $name.';PZ_CURSOR=my_'. $name.'.cursorX;">
	<img src="/img/scroll/cursorX.png" height="16" width="16" onMouseDown="PZ_CURSOR.changeEvents();">
</div>
<div id="curY_'. $name.'" style="position:absolute;z-index:2;visibility:visible" onMouseOver="PZ_SCROLLBOX=my_'. $name.';PZ_CURSOR=my_'. $name.'.cursorY;" >
	<img src="/img/scroll/cursorY.png" height="16" width="16" onMouseDown="PZ_CURSOR.changeEvents();">
</div>

<script language="JavaScript">
	var my_'. $name.'=new pz_object("cnt_'. $name.'", '. $top.', '. $left.', '. $height.', '. $width.', '. $cursor_width.');
	
	my_'. $name.'.table=eval(document.getElementById("'. $name.'"));

	my_'. $name.'.scrollerX=eval(document.getElementById("scrX_'. $name.'"));
	my_'. $name.'.buttonLeft=eval(document.getElementById("butL_'. $name.'"));
	my_'. $name.'.acceleratorX=eval(document.getElementById("accX_'. $name.'"));
	my_'. $name.'.buttonRight=eval(document.getElementById("butR_'. $name.'"));
	
	my_'. $name.'.scrollerY=eval(document.getElementById("scrY_'. $name.'"));
	my_'. $name.'.buttonUp=eval(document.getElementById("butU_'. $name.'"));
	my_'. $name.'.acceleratorY=eval(document.getElementById("accY_'. $name.'"));
	my_'. $name.'.buttonDown=eval(document.getElementById("butD_'. $name.'"));

	my_'. $name.'.init();

	my_'. $name.'.cursorX=new pz_cursor("curX_'. $name.'", 16, 16);
	my_'. $name.'.cursorX.scroller=my_'. $name.'.scrollerX;
	my_'. $name.'.cursorX.init_cursorX();
	
	my_'. $name.'.cursorY=new pz_cursor("curY_'. $name.'", 16, 16);
	my_'. $name.'.cursorY.scroller=my_'. $name.'.scrollerY;
	my_'. $name.'.cursorY.init_cursorY();
	
	my_'. $name.'.show();

</script>';
	}
}
