<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
   use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

   public function imgUpload($image, $destination, $dir, $imgName)
   {
      try {
         $type = "JPG";
         $permissions = 0777;

         if (stripos("pdf", $image->getClientOriginalExtension()) !== false) {
            $type = "PDF";
            if (file_exists("$dir/$imgName.pdf")) {
               // Establecer permisos
               if (chmod("$dir/$imgName.pdf", $permissions)) {
                  @unlink("$dir/$imgName.pdf");
                  sleep(2);
               }
               $type = "PDF";
            } elseif (file_exists("$dir/$imgName.PDF")) {
               // Establecer permisos
               if (chmod("$dir/$imgName.PDF", $permissions)) {
                  @unlink("$dir/$imgName.PDF");
                  sleep(2);
               }
               $type = "pdf";
            }
         } else {
            if (file_exists("$dir/$imgName.PNG")) {
               // Establecer permisos
               if (chmod("$dir/$imgName.PNG", $permissions)) {
                  @unlink("$dir/$imgName.PNG");
               }
               $type = "JPG";
            } elseif (file_exists("$dir/$imgName.JPG")) {
               // Establecer permisos
               if (chmod("$dir/$imgName.JPG", $permissions)) {
                  @unlink("$dir/$imgName.JPG");
               }
               $type = "PNG";
            }
         }

         $imgName = "$imgName.$type";
         $image->move($destination, $imgName);
         return "$dir/$imgName";
      } catch (\Error $err) {
         error_log("error en imgUpload(): " . $err->getMessage());
      }
   }
}
