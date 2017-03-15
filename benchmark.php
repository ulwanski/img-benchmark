<?php
require_once "./vendor/autoload.php";
use \Eventviva\ImageResize;

function fastimagecopyresampled (&$dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h, $quality = 3) {
  // Plug-and-Play fastimagecopyresampled function replaces much slower imagecopyresampled.
  // Just include this function and change all "imagecopyresampled" references to "fastimagecopyresampled".
  // Typically from 30 to 60 times faster when reducing high resolution images down to thumbnail size using the default quality setting.
  // Author: Tim Eckel - Date: 09/07/07 - Version: 1.1 - Project: FreeRingers.net - Freely distributable - These comments must remain.
  //
  // Optional "quality" parameter (defaults is 3). Fractional values are allowed, for example 1.5. Must be greater than zero.
  // Between 0 and 1 = Fast, but mosaic results, closer to 0 increases the mosaic effect.
  // 1 = Up to 350 times faster. Poor results, looks very similar to imagecopyresized.
  // 2 = Up to 95 times faster.  Images appear a little sharp, some prefer this over a quality of 3.
  // 3 = Up to 60 times faster.  Will give high quality smooth results very close to imagecopyresampled, just faster.
  // 4 = Up to 25 times faster.  Almost identical to imagecopyresampled for most images.
  // 5 = No speedup. Just uses imagecopyresampled, no advantage over imagecopyresampled.

  if (empty($src_image) || empty($dst_image) || $quality <= 0) { return false; }
  if ($quality < 5 && (($dst_w * $quality) < $src_w || ($dst_h * $quality) < $src_h)) {
    $temp = imagecreatetruecolor ($dst_w * $quality + 1, $dst_h * $quality + 1);
    imagecopyresized ($temp, $src_image, 0, 0, $src_x, $src_y, $dst_w * $quality + 1, $dst_h * $quality + 1, $src_w, $src_h);
    imagecopyresampled ($dst_image, $temp, $dst_x, $dst_y, 0, 0, $dst_w, $dst_h, $dst_w * $quality, $dst_h * $quality);
    imagedestroy ($temp);
  } else imagecopyresampled ($dst_image, $src_image, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
  return true;
}

function makeThumbnail($sourcefile, $endfile, $thumbwidth, $thumbheight, $quality){
    // Takes the $sourcefile and makes a thumbnail from it and places it at $endfile.

    // Load image and get image size.
    $img = imagecreatefromjpeg($sourcefile);
    $width = imagesx( $img );
    $height = imagesy( $img );

    if ($width > $height) {
        $newwidth = $thumbwidth;
        $divisor = $width / $thumbwidth;
        $newheight = floor( $height / $divisor);
    } else {
        $newheight = $thumbheight;
        $divisor = $height / $thumbheight;
        $newwidth = floor( $width / $divisor );
    }
  
  // Create a new temporary image.
  $tmpimg = imagecreatetruecolor( $newwidth, $newheight );

  // Copy and resize old image into new image.
  fastimagecopyresampled( $tmpimg, $img, 0, 0, 0, 0, $newwidth, $newheight, $width, $height );

  // Save thumbnail into a file.
  imagejpeg( $tmpimg, $endfile, $quality);

  // release the memory
  imagedestroy($tmpimg);
  imagedestroy($img);
}



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
            makeThumbnail('./images/'.$file->getFilename(), './tmp/thumb_'.$file->getFilename(), 300, 300, 65);
            //$image = new ImageResize('./images/'.$file->getFilename());
            //$image->resizeToBestFit(300, 300);
            //$image->save('./tmp/thumb_'.$file->getFilename());
            $allTime += (microtime(true) - $time);
        }
        $endTime = round($allTime/$loops, 3)."s";
        echo " $endTime ".str_repeat(' ', $column2len - strlen($endTime) - 3)." |\n\r";
    }
}
echo '|'.str_repeat('-', $column1len).'|'.str_repeat('-', $column2len)."|\n\r";
