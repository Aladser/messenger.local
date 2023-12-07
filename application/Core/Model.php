<?php

namespace Aladser\Core;

class Model
{
    protected DBQuery $dbQuery;

    public function __construct()
    {
        $this->dbQuery = new DBQuery(ConfigClass::HOST_DB, ConfigClass::NAME_DB, ConfigClass::USER_DB, ConfigClass::PASS_DB);
    }
}
