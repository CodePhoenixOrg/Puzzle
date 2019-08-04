<?php
namespace Puzzle;

include_once 'ipz_constants.php';

use Phink\Core\TObject;

class Base extends TObject
{
    protected $lg = '';
    protected $db_prefix = '';
    protected $database = '';
    protected $configName = '';

    public function __construct($lg, $db_prefix)
    {
        parent::__construct();
        
        $this->db_prefix = $db_prefix;
        $this->lg = $lg;
    }

    public function setDatabaseName($value) {
        $this->database = $value;
    }

    public function getDatabaseName() {
        return $this->database;
    }

    public function setConfigName($value) {
        $this->configName = $value;
    }

    public function getConfigName() {
        return $this->configName;
    }
}
