<?php
require_once "./vendor/autoload.php";
use \Eventviva\ImageResize;






echo "Starting image resize benchmark ...\n\r";

$column1len = 30;
$column2len = 10;

foreach (new DirectoryIterator(__DIR__."/images") as $file) {
    if ($file->isFile()){
        if($file->getExtension() != 'jpg') continue;
        echo '|'.str_repeat('-', $column1len).'|'.str_repeat('-', $column2len)."|\n\r";
        echo '| '.$file->getFilename().str_repeat(' ', $column1len - strlen($file->getFilename()) - 1).'|';

        $loops = 6;
        $allTime = 0;
        for($i = 0; $i < $loops; $i++){
            $time = microtime(true);
            $image = new ImageResize('./images/'.$file->getFilename());
            $image->resizeToBestFit(300, 300);
            $image->save('./tmp/thumb_'.$file->getFilename());
            $allTime += (microtime(true) - $time);
        }
        $endTime = round($allTime/$loops, 3)."s";
        echo " $endTime ".str_repeat(' ', $column2len - strlen($endTime) - 3)." |\n\r";
    }
}
echo '|'.str_repeat('-', $column1len).'|'.str_repeat('-', $column2len)."|\n\r";
