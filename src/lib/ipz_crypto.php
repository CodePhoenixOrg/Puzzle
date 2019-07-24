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

class Crypto
{
    public function hashPassword($password)
    {
        mt_srand((double)microtime()*1000000);
        $salt = mhash_keygen_s2k(MHASH_SHA1, $password, substr(pack('h*', md5(mt_rand())), 0, 8), 4);
        $hash = "{SSHA}".base64_encode(mhash(MHASH_SHA1, $password.$salt).$salt);
        return $hash;
    }
 
    public function validatePassword($password, $hash)
    {
        $hash = base64_decode(substr($hash, 6));
        $original_hash = substr($hash, 0, 20);
        $salt = substr($hash, 20);
        $new_hash = mhash(MHASH_SHA1, $password . $salt);
   
        return (strcmp($original_hash, $new_hash) ==0);
    }
}

