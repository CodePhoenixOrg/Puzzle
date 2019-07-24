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
 
namespace Puzzle\Data;

define("CONNECT", "connect");
define("DISCONNECT", "disconnect");

use PDO;

use Phink\Core\TObject;
use Puzzle\Data\Driver;
use Puzzle\Data\Statement;

/**
 * Description of Connection
 *
 * @author david
 */
class Connection extends TObject
{

    private $_state = null;
    private $_dsn;
    private $_params;
    private $_driver = '';
    private $_host = '';
    private $_databaseName = '';
    private $_user = '';
    private $_password = '';
    private $_port = 0;
    private $_config = '';

    use CrudQueries;

    public function __construct($driver, $databaseName, $host = '', $user = '', $password = '', $port = 0)
    {
        parent::__construct();
        
        $this->_driver = $driver;
        $this->_databaseName = $databaseName;
        $this->_host = $host;
        $this->_user = $user;
        $this->_password = $password;
        $this->_port = $port;
        $this->_dsn = '';
        $this->_params = (array) null;

        $this->_config = [
            'driver' => $driver,
            'host' => $host, 
            'database' => $databaseName, 
            'user' => $user, 
            'password' => $password,
            'port' => $port
        ];

        if ($this->_driver == Driver::MYSQL) {
            $this->_params = [PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_NUM];
            $this->_dsn = $this->_driver . ':host=' . $this->_host . ';dbname=' . $this->_databaseName;
        } elseif($this->_driver == Driver::SQLSERVER) {
            $this->_params = [PDO::SQLSRV_ATTR_ENCODING => PDO::SQLSRV_ENCODING_SYSTEM, PDO::SQLSRV_ATTR_DIRECT_QUERY => true];
            $this->_dsn = $this->_driver . ':Driver=' . $this->_host . ';Database=' . $this->_databaseName; 
        } elseif($this->_driver == Driver::SQLITE) {
            $this->_dsn = $this->_driver . ':' . $this->_databaseName; 
        }
    }

    public function getDriver()
    {
        return $this->_driver;
    }
    
    public function getState()
    {
        return $this->_state;
    }

    public function open()
    {
        try {
            if($this->_params != null) {
                $this->_state = new \PDO($this->_dsn, $this->_user, $this->_password, $this->_params);
            } else {
                $this->_state = new \PDO($this->_dsn, $this->_user, $this->_password);
            }
        } catch (\PDOException $ex) {
            self::getLogger()->error($ex, __FILE__, __LINE__);
        }

        return $this->_state !== null;
    }
    
    public function query($sql = '', array $params = null)
    {
        $result = false;
        try {
            if($params != null) {
                $this->_statement = $this->_state->prepare($sql);
                $this->_statement->execute($params);
            } else {
                $this->_statement = $this->_state->query($sql);
            }
            
            $result = new Statement($this->_statement, $this->_config, $sql);
        } catch (\PDOException $ex) {
            self::getLogger()->debug(__FILE__ . ':' . __LINE__ . ':', ['SQL' => $sql, 'PARAMS' => $params]);
            self::getLogger()->debug(__FILE__ . ':' . __LINE__ . ':', ['exception' => $ex]);
        } catch (\Exception $ex) {
            self::getLogger()->debug(__FILE__ . ':' . __LINE__ . ':', ['exception' => $ex]);
        }
        
        return $result;
    }

    public function exec($sql = '')
    {
        return $this->_state->exec($sql);
    }

    public function prepare($sql)
    {
        return $this->_state->prepare($sql);
    }

    public function beginTransaction()
    {
        $this->_state->beginTransaction();
    }
    
    public function commit()
    {
        $this->_state->commit();
    }
    
    public function rollback()
    {
        $this->_state->rollBack();
    }
    
    public function inTransaction()
    {
        $this->_state->inTransaction();
    }
    
    public function lastInsertId()
    {
        return $this->_state->lastInsertId();
    }
    
    public function setAttribute($key, $value)
    {
        $this->_state->setAttribute($key, $value);
    }
    
    public function getAttribute($key)
    {
        return $this->_state->getAttribute($key);
    }
    
    public function getLastInsertId()
    {
        return $this->_state->lastInsertId();
    }
    
    public function quote($value)
    {
        return $this->_state->quote($value);
    }

    public function close()
    {
        // $this->_state->free();
        unset($this->_state);

        return true;
    }

    public function __destruct()
    {
        $this->close();
    }
    
}
