<?php

namespace Aladser\Core;

use Aladser\Config;

class Model
{
    protected DBQuery $dbQuery;

    public function __construct()
    {
        $this->dbQuery = new DBQuery(Config::HOST_DB, Config::NAME_DB, Config::USER_DB, Config::PASS_DB);
    }
}
