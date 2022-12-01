<?php  


$admin = (isset($argv[1]) && $argv[1] == 'admin');

$path = './templates_min';
$files = scandir($path);
$files = array_slice($files, 2);
$init = 'var templates';
$templ = 'templates';
$data = $init.'={};'."\n".$templ.'.default={};'."\n";

if(!empty($files)) {
   $template_group_name = " ";

   for($i=0; $i<count($files); $i++) {

      if(substr($files[$i], -9) == '.mustache') {
         $template_name = substr($files[$i],0,-9);
         $name_parts = explode('.', $template_name);

         if(count($name_parts) > 1) {

            if( $name_parts[0] != $template_group_name) {
               $data .= "\n" .$templ.'.'.$name_parts[0].'={};';
               $template_group_name = $name_parts[0];
            }
         }

         $FileName = $path . '/' . $files[$i];

         if (filesize($FileName)) {
             $fileHandle = fopen($FileName, 'r');
             $data .= "\n" . $templ . '.' . $template_name . '=\'' . fread($fileHandle, filesize($FileName)) . '\';' . "\n";
             fclose($fileHandle);
         }
      }
   }

   $js_path = 'js/templates.js';

   if(file_put_contents($js_path, $data)) {
      echo "success";
   } else {
      echo "error";
   }
} else {
   echo "no input files";
}
 
  