<?php

namespace Puzzle;

use Phink\Core\TObject;

class Base extends TObject
{
    protected $lg = '';
    protected $db_prefix = '';
    protected $database = '';
    // protected $database = '';

    public function __construct($lg, $db_prefix)
    {
        parent::__construct();
        
        $this->db_prefix = $db_prefix;
        $this->lg = $lg;
    }
}
