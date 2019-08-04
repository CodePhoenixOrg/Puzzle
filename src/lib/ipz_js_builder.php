<?php
/*
 * Copyright (C) 2016 David Blanchard
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */

namespace Puzzle;

class JsBuilder
{

    public static function build(): void
    {

        // $srcdir = dirname(__FILE__) . DIRECTORY_SEPARATOR . 'bower_components' . DIRECTORY_SEPARATOR . 'phinkjs' . DIRECTORY_SEPARATOR . 'client' . DIRECTORY_SEPARATOR;
        // $srcdir = SRC_ROOT . 'web' . DIRECTORY_SEPARATOR . 'bower_components' . DIRECTORY_SEPARATOR . 'phinkjs' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'client' . DIRECTORY_SEPARATOR;
        $srcdir = Core::PUZZLEJS_ROOT;

        $js_filename = DOCUMENT_ROOT . 'puzzle.js';

        $filenames = [
            'form_events.js',
            'scroller.js',
            'splash.js',
            'cursor.js',
            'images.js',
            'select.js',
            'validator.js',
            'design.js',
            'menus.js',
            'shared.js'
        ];

        $js_content = '';

        foreach ($filenames as $filename) {
            $js_content .= file_get_contents($srcdir . 'pz_' . $filename);
        }

        file_put_contents($js_filename, $js_content);
    }
}
