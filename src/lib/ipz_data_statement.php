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

use PDOStatement;

use Phink\Core\TObject;
use Puzzle\Data\Driver;

/**
 * Description of adatareader
 *
 * @author david
 */
class Statement extends TObject
{
    private $_statement;
    private $_values;
    private $_fieldCount;
    private $_rowCount;
    private $_meta = [];
    private $_colNames = [];
    private $_config = null;
    private $_sql = '';
    private $_result = null;
    private $_driver = '';
    private $_native_types = [];
    private $_native2php_assoc = [];
    private $_native2php_num = [];

    public function __construct($statement, $config, $sql)
    {
        $this->_statement = $statement;
        $this->_config = (object) $config;
        $this->_sql = $sql;
        $this->_driver = $this->_config->driver;
        $this->_typesMapper();

    }

    public function execute($params = []) {
        $result = null;

        if (isset($params)) {
            $result = $this->_statement->execute($params);
        } else {
            $result = $this->_statement->execute();
        }

        return $result; 
    }

    public function fetch($mode = \PDO::FETCH_NUM)
    {
        $this->_values = $this->_statement->fetch($mode);
        return $this->_values;
    }
    
    public function fetchAll($mode = \PDO::FETCH_NUM)
    {
        $this->_values = $this->_statement->fetchAll($mode);
        return $this->_values;
    }

    public function fetchObject()
    {
        return $this->_statement->fetchObject();
    }
    
    public function columnCount()
    {
        if (!isset($this->_fieldCount)) {
            try {
                $this->_fieldCount = $this->_statement->columnCount();
            } catch (\PDOException $ex) {
                if (isset($this->_values[0])) {
                    $this->_fieldCount = count($this->_values[0]);
                } else {
                    throw new \Exception("Cannot count fields of a row before the resource is fetched", -1, $ex);
                }
            }
        }
        return $this->_fieldCount;
    }

    public function rowCount()
    {
        if (!isset($this->_rowCount)) {
            try {
                $this->_rowCount = $this->_statement->rowCount();
            } catch (\PDOException $ex) {
                if (is_array($this->_values)) {
                    $this->_rowCount = count($this->_values);
                } else {
                    throw new \Exception("Cannot count rows of a result set before the resource is fetched", -1, $ex);
                }
            }
        }
        return $this->_rowCount;
    }

    public function getColumnMeta($i)
    {
        return $this->_statement->getColumnMeta($i);
    }

    public function getFieldName($i)
    {
        $name = '';

        if ($this->_driver === Driver::MYSQL && $this->_getMySQLiResult()) {
            $field_info = $this->_result->fetch_field_direct($i);
            $name = $field_info->name;
        } else {
            if (!isset($this->_meta[$i])) {
                $this->_meta[$i] = $this->_statement->getColumnMeta($i);
            }
            $name = $this->_meta[$i]['name'];
        }

        return $name;
    }

    public function getFieldType($i)
    {
        $type = '';

        if ($this->_driver === Driver::MYSQL && $this->_getMySQLiResult()) {
            $field_info = $this->_result->fetch_field_direct($i);
            $type = $field_info->type;
        } else {
            if (!isset($this->_meta[$i])) {
                $this->_meta[$i] = $this->_statement->getColumnMeta($i);
            }
            $type = $this->_meta[$i]['native_type'];
        }

        return $type;
    }

    public function getFieldLen($i)
    {
        $len = 0;

        if ($this->_driver === Driver::MYSQL && $this->_getMySQLiResult()) {
            $field_info = $this->_result->fetch_field_direct($i);
            $len = $field_info->length;
        } else {
            if (!isset($this->_meta[$i])) {
                $this->_meta[$i] = $this->_statement->getColumnMeta($i);
            }
            $len = $this->_meta[$i]['len'];
        }

        return $len;
    }

    private function _getMySQLiResult()
    {
        if ($this->_result === null) {
            try {
                $cs = new \mysqli(
                    $this->_config->host,
                    $this->_config->user,
                    $this->_config->password,
                    $this->_config->database,
                    ($this->_config->port !== '') ? $this->_config->port : null
                );

                $this->_result = $cs->query($this->_sql);
            } catch (\Exception $ex) {
                self::getLogger()->dump(__FILE__ . ':' . __LINE__ . ':' . __METHOD__ . ':', $ex);
                return false;
            }
        }

        return true;
    }

    public function typeNumToName($type)
    {
        return $this->_native_types[$type];
    }

    public function typeNameToPhp($type)
    {
        return $this->_native2php_assoc[$type];
    }

    public function typeNumToPhp($type)
    {
        return $this->_native2php_num[$type];
    }

    private function _typesMapper()
    {
        if ($this->_driver === Driver::MYSQL) {
            $this->_mysqlTypes();
        }
        if ($this->_driver === Driver::SQLITE) {
            $this->_sqliteTypes();
        }
    }
    
    private function _sqliteTypes()
    {
        $this->_native_types = (array) null;
        $this->_native2php_assoc = (array) null;
        $this->_native2php_num = (array) null;

        $this->_native_types[1] = "INTEGER";
        $this->_native_types[2] = "TEXT";
        $this->_native_types[3] = "BLOB";
        $this->_native_types[4] = "REAL";
        $this->_native_types[5] = "NUMERIC";

        
        $this->_native2php_assoc["INTEGER"] = "int";
        $this->_native2php_assoc["TEXT"] = "string";
        $this->_native2php_assoc["BLOB"] = "blob";
        $this->_native2php_assoc["REAL"] = "float";
        $this->_native2php_assoc["NUMERIC"] = "float";

        
        $this->_native2php_num[1] = "int";
        $this->_native2php_num[2] = "string";
        $this->_native2php_num[3] = "blob";
        $this->_native2php_num[4] = "float";
        $this->_native2php_num[5] = "float";

    }

    private function _mysqlTypes()
    {
        $this->_native_types = (array) null;
        $this->_native2php_assoc = (array) null;
        $this->_native2php_num = (array) null;

        $this->_native_types[1] = "TINYINT";
        $this->_native_types[2] = "SMALLINT";
        $this->_native_types[3] = "INT";
        $this->_native_types[4] = "FLOAT";
        $this->_native_types[5] = "DOUBLE";
        $this->_native_types[7] = "TIMESTAMP";
        $this->_native_types[8] = "BIGINT";
        $this->_native_types[9] = "MEDIUMINT";
        $this->_native_types[10] = "DATE";
        $this->_native_types[11] = "TIME";
        $this->_native_types[12] = "DATETIME";
        $this->_native_types[13] = "YEAR";
        $this->_native_types[16] = "BIT";
        $this->_native_types[246] = "DECIMAL";
        $this->_native_types[252] = "BLOB";
        $this->_native_types[253] = "VARCHAR";
        $this->_native_types[254] = "CHAR";
        
        $this->_native2php_assoc["TINYINT"] = "int";
        $this->_native2php_assoc["SMALLINT"] = "int";
        $this->_native2php_assoc["INT"] = "int";
        $this->_native2php_assoc["FLOAT"] = "float";
        $this->_native2php_assoc["DOUBLE"] = "float";
        $this->_native2php_assoc["TIMESTAMP"] = "int";
        $this->_native2php_assoc["BIGINT"] = "int";
        $this->_native2php_assoc["MEDIUMINT"] = "int";
        $this->_native2php_assoc["DATE"] = "date";
        $this->_native2php_assoc["TIME"] = "time";
        $this->_native2php_assoc["DATETIME"] = "datetime";
        $this->_native2php_assoc["YEAR"] = "year";
        $this->_native2php_assoc["BIT"] = "int";
        $this->_native2php_assoc["DECIMAL"] = "float";
        $this->_native2php_assoc["BLOB"] = "blob";
        $this->_native2php_assoc["VARCHAR"] = "string";
        $this->_native2php_assoc["CHAR"] = "char";
        
        $this->_native2php_num[1] = "int";
        $this->_native2php_num[2] = "int";
        $this->_native2php_num[3] = "int";
        $this->_native2php_num[4] = "float";
        $this->_native2php_num[5] = "float";
        $this->_native2php_num[7] = "int";
        $this->_native2php_num[8] = "int";
        $this->_native2php_num[9] = "int";
        $this->_native2php_num[10] = "date";
        $this->_native2php_num[11] = "time";
        $this->_native2php_num[12] = "datetime";
        $this->_native2php_num[13] = "year";
        $this->_native2php_num[16] = "int";
        $this->_native2php_num[246] = "float";
        $this->_native2php_num[252] = "blob";
        $this->_native2php_num[253] = "string";
        $this->_native2php_num[254] = "string";
    }
}
