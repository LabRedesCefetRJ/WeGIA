<?php
require_once dirname(__FILE__, 5) . DIRECTORY_SEPARATOR . 'html' . DIRECTORY_SEPARATOR . 'seguranca' . DIRECTORY_SEPARATOR . 'sessaoCookie.php';
     if (session_status() === PHP_SESSION_NONE)
         session_start();

     if (!isset($_SESSION['usuario'])) {
         http_response_code(401);
         exit('Não autenticado.');
     }

     function delTree($dir) {
      
        $dir = realpath($dir);

        if (!is_dir($dir) || strpos($dir, realpath(__DIR__)) !== 0) {
          return false;
        }

        $files = array_diff(scandir($dir), array('.','..')); 
        foreach ($files as $file) { 
          $file_path = "$dir/$file";

          if (!is_readable($file_path) || strpos(realpath($file_path), $dir) !== 0) {
            continue;
          }

          (is_dir($file_path)) ? delTree($file_path) : unlink($file_path); 
        } 
        return rmdir($dir); 
      }
      
      $dirToDelete = realpath('../tabelas/');

      if ($dirToDelete && strpos($dirToDelete, realpath(__DIR__)) === 0 && delTree($dirToDelete)) {
        echo true;
      }else echo false;
?>