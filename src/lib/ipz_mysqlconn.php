<?php

use \Phink\Data\Client\PDO\TPDOConfiguration;
use \Phink\Data\Client\PDO\TPDOConnection;
use \Puzzle\Data\Driver;

function connection($c, $d, $cs = null)
{
    $host="mysql";
    $user="ladmin";
    $passwd="demo";
    $dbname=$d;

    try {
        if ($c == CONNECT) {

            $cnf = new TPDOConfiguration(Driver::MYSQL, $dbname, $host, $user, $passwd);
            $cs = new TPDOConnection($cnf);
            // debugLog("CONNECTION::Driver::MYSQL, $dbname, $host, $user, $passwd", $cs);            
            $cs->open();
        } elseif ($c == DISCONNECT && $cs !== null) {
            //$cs->close();
            unset($cs);
        }   
            
    }
    catch(\PDOException $ex) {
        die("Connection failed: " . $ex->getMessage());
    }
    return $cs;
}
