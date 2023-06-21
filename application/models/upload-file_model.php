<?php

class UploadFileModel extends \core\Model
{
    public function __construct($CONFIG){
    }

    public function getData(){
        $fromPath = $_FILES['image']['tmp_name'];
        $toPath =  dirname(__DIR__, 1).'\\data\\'.$_FILES['image']['name'];
        $rslt = move_uploaded_file($fromPath, $toPath) ? 1 : 0;
        echo $rslt == 1 ? 'application/data/'.$_FILES['image']['name'] : 0;
    }
}