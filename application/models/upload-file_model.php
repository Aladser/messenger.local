<?php

class UploadFileModel extends \core\Model
{

    public function __construct($CONFIG=null){
    }

    public function getData(){
        var_dump($_POST);
        echo '<br>';
        var_dump($_FILES);
    }
}

