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

class Source
{
    public function __construct()
    {
        $this->_init_javascript();
    }

    protected function _init_javascript()
    {
        ?>
<style type="text/css">
	@import url("/styles/mirror.css");
/*@import url("/styles/site.css");*/
</style>
<?php

		// Set the static content root differently on php.net
		if (!isset($MYSITE)) {
			$MYSITE="";
		}
        $_SERVER['STATIC_ROOT'] = ($MYSITE == 'http://www.php.net/') ?  'http://static.php.net/www.php.net' : '';
    }

    // Highlight PHP code
    public function highlightPhp($code, $return = false)
    {
        // Use colors instead of class names
        ini_set('comment', 'highlight.comment');
        ini_set('default', 'highlight.default');
        ini_set('keyword', 'highlight.keyword');
        ini_set('string', 'highlight.string');
        ini_set('html', 'highlight.html');

        // Using OB, as highlight_string() only supports
        // returning the result from 4.2.0
        ob_start();
        highlight_string($code);
        $highlighted = ob_get_contents();
        ob_end_clean();
    
        /*
        if ($return) { return $highlighted; }
        else { echo $highlighted; }
        */
    
        return $highlighted;
    }

    // Highlight PHP code
    public function highlightPhpWithCss($code, $return = false)
    {
        // Use class names instead of colors
        ini_set('highlight.comment', 'comment');
        ini_set('highlight.default', 'default');
        ini_set('highlight.keyword', 'keyword');
        ini_set('highlight.string', 'string');
        ini_set('highlight.html', 'html');

        // Using OB, as highlight_string() only supports
        // returning the result from 4.2.0
        ob_start();
        highlight_string($code);
        $highlighted = ob_get_contents();
        ob_end_clean();
    
        // Fix output to use CSS classes and wrap well
        $highlighted = '<div class="phpcode">' . str_replace(
        array(
            '&nbsp;',
            '<br />',
            '<span style="color:',
            '</span>',
            "\n ",
            '  '
        ),
        array(
            ' ',
            "<br />\n",
            '<span class="',
            '</span>',
            "\n&nbsp;",
            '&nbsp; '
        ),
        $highlighted
    ) . '</div>';
    
        /*
        if ($return) { return $highlighted; }
        else { echo $highlighted; }
        */
    
        return $highlighted;
    }

    public function getPagename($url="")
    {
        // No file param specified
        if (!isset($url)) {
            echo "<h1>No page URL specified</h1>";
            exit;
        }
 
        // Get dirname of the specified URL part
        $dir = dirname($url);
 
        // Some dir was present in the filename
        if (!empty($dir) && !preg_match("!^(\\.|/)$!", $dir)) {
            $page_name = $_SERVER['DOCUMENT_ROOT'] . $url;
        } else {
            $page_name = $_SERVER['DOCUMENT_ROOT'] . '/' . basename($url);
        }

        return $page_name;
    }
}
