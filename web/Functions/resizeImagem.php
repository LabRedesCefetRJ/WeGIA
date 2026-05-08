<?php
  /*
      Hello everyone!, this script process jpg and png files.
      for gif images and more information you can visit the
      following links if you want to stick with gd2 library of php.
      well it's better you use imagemagick to convert images.

      **Links**
      https://stackoverflow.com/questions/718491/resize-animated-gif-file-without-destroying-animation
      http://www.akemapa.com/2008/07/10/php-gd-resize-transparent-image-png-gif/

      Thanks for watching the video and Subscribe my channel for more videos.
      Aman Kharbanda
  */
  $filename1 = './3.jpg';
  $filename2 = './2.png';
  function processjpg($filename){
    list($width,$height) = getimagesize($filename);
    $newheight = 266;
    $imagetruecolor = imagecreatetruecolor(200,$newheight);
    $newimage = imagecreatefromjpeg($filename);
    imagecopyresampled($imagetruecolor,$newimage,0,0,0,0,200,$newheight,$width,$height);
    imagejpeg($imagetruecolor,'newjpg.jpg',80);
    echo $filename." Processed";
  };

  function processpng($filename){
    list($width,$height) = getimagesize($filename);
    $newheight = (200*$height)/$width;
    $imagetruecolor = imagecreatetruecolor(200,$newheight);
    $newimage = imagecreatefrompng($filename);
    imagecopyresampled($imagetruecolor,$newimage,0,0,0,0,200,$newheight,$width,$height);
    imagepng($imagetruecolor,'newpng.png',8);
    echo $filename." Processed";
  };

  processjpg($filename1);
  processpng($filename2);
 ?>
