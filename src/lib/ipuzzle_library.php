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
 * but WITHOUT ANY WARRANTY, without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */


class iPuzzleLibrary {

    public static function mount() {

        $filenames = [ 
            "constants.php",
            "base.php",
            "misc.php",
            "crud_queries.php",
            "data_driver.php",
            "data_statement.php",
            // "connection.php",
            "mysqlconn.php",
            "design.php",
            "controls.php",
            "db_controls.php",
            "db_graphs.php",            
            "blocks.php",
            "framed_blocks.php",
            "menus.php",
            "analyser.php",
            "mkscripts.php",
            "source.php",
            "splash.php",
            "mail.php",
            "diary.php"

            // "db.php",
            // "dico.php",
            // "pdf.php",
            //"init.php",
            //"empty_file.php",
            // "calendar.php",
    
        ];

        foreach ($filenames as $filename) {
            include __DIR__ . "/ipz_" . $filename;
        }
   
    }
}

iPuzzleLibrary::mount();
