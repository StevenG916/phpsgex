<?php
/**
 * Created by PhpStorm.
 * User: gabrielesantoro
 * Date: 19/03/18
 * Time: 17:24
 */

namespace phpsgex\controllers;
class ImageUpload
{
   public static function upload($dir,$image){
       if (!file_exists($dir)) {
           mkdir($dir, 0777, true);
       }
       $target_dir = $dir;
       $target_file = $target_dir . basename($image["name"]);
       $imageFileType = strtolower(pathinfo($target_file,PATHINFO_EXTENSION));
       $check = getimagesize($image["tmp_name"]);
       if($check !== false) {
           $uploadOk = 1;
       } else {
           $uploadOk = 0;
       }
       if (file_exists($target_file)) {
           $uploadOk = 0;
       }
       if($imageFileType != "jpg" && $imageFileType != "png" && $imageFileType != "jpeg" && $imageFileType != "gif" ) {
           $uploadOk = 0;
       }
       if ($uploadOk != 0){
           if (move_uploaded_file($image["tmp_name"], $target_file)) {
               ImageUpload::smart_resize_image($target_file,null,25,25,false,$target_file,true,false,100,false);
               $uploadOk = 1;
           }
           else{
               $uploadOk = 0;
           }
       }
       return $uploadOk;
   }
   public static function smart_resize_image($file, $string = null, $width = 0, $height = 0, $proportional = false, $output = 'file', $delete_original    = true, $use_linux_commands = false, $quality = 100, $grayscale = false) {
            if ( $height <= 0 && $width <= 0 ) return false;
            if ( $file === null && $string === null ) return false;
            # Setting defaults and meta
            $info                         = $file !== null ? getimagesize($file) : getimagesizefromstring($string);
            $image                        = '';
            $final_width                  = 0;
            $final_height                 = 0;
            list($width_old, $height_old) = $info;
            $cropHeight = $cropWidth = 0;
            # Calculating proportionality
            if ($proportional) {
                if      ($width  == 0)  $factor = $height/$height_old;
                elseif  ($height == 0)  $factor = $width/$width_old;
                else                    $factor = min( $width / $width_old, $height / $height_old );
                $final_width  = round( $width_old * $factor );
                $final_height = round( $height_old * $factor );
            }
            else {
                $final_width = ( $width <= 0 ) ? $width_old : $width;
                $final_height = ( $height <= 0 ) ? $height_old : $height;
                $widthX = $width_old / $width;
                $heightX = $height_old / $height;

                $x = min($widthX, $heightX);
                $cropWidth = ($width_old - $width * $x) / 2;
                $cropHeight = ($height_old - $height * $x) / 2;
            }
            # Loading image to memory according to type
            switch ( $info[2] ) {
                case IMAGETYPE_JPEG:  $file !== null ? $image = imagecreatefromjpeg($file) : $image = imagecreatefromstring($string);  break;
                case IMAGETYPE_GIF:   $file !== null ? $image = imagecreatefromgif($file)  : $image = imagecreatefromstring($string);  break;
                case IMAGETYPE_PNG:   $file !== null ? $image = imagecreatefrompng($file)  : $image = imagecreatefromstring($string);  break;
                default: return false;
            }
            # Making the image grayscale, if needed
            if ($grayscale) {
                imagefilter($image, IMG_FILTER_GRAYSCALE);
            }
            # This is the resizing/resampling/transparency-preserving magic
            $image_resized = imagecreatetruecolor( $final_width, $final_height );
            if ( ($info[2] == IMAGETYPE_GIF) || ($info[2] == IMAGETYPE_PNG) ) {
                $transparency = imagecolortransparent($image);
                $palletsize = imagecolorstotal($image);
                if ($transparency >= 0 && $transparency < $palletsize) {
                    $transparent_color  = imagecolorsforindex($image, $transparency);
                    $transparency       = imagecolorallocate($image_resized, $transparent_color['red'], $transparent_color['green'], $transparent_color['blue']);
                    imagefill($image_resized, 0, 0, $transparency);
                    imagecolortransparent($image_resized, $transparency);
                }
                elseif ($info[2] == IMAGETYPE_PNG) {
                    imagealphablending($image_resized, false);
                    $color = imagecolorallocatealpha($image_resized, 0, 0, 0, 127);
                    imagefill($image_resized, 0, 0, $color);
                    imagesavealpha($image_resized, true);
                }
            }
            imagecopyresampled($image_resized, $image, 0, 0, $cropWidth, $cropHeight, $final_width, $final_height, $width_old - 2 * $cropWidth, $height_old - 2 * $cropHeight);
            # Taking care of original, if needed
            if ( $delete_original ) {
                if ( $use_linux_commands ) exec('rm '.$file);
                else @unlink($file);
            }
            # Preparing a method of providing result
            switch ( strtolower($output) ) {
                case 'browser':
                    $mime = image_type_to_mime_type($info[2]);
                    header("Content-type: $mime");
                    $output = NULL;
                    break;
                case 'file':
                    $output = $file;
                    break;
                case 'return':
                    return $image_resized;
                    break;
                default:
                    break;
            }
            # Writing image according to type to the output destination and image quality
            switch ( $info[2] ) {
                case IMAGETYPE_GIF:   imagegif($image_resized, $output);    break;
                case IMAGETYPE_JPEG:  imagejpeg($image_resized, $output, $quality);   break;
                case IMAGETYPE_PNG:
                    $quality = 9 - (int)((0.9*$quality)/10.0);
                    imagepng($image_resized, $output, $quality);
                    break;
                default: return false;
            }
            return true;
        }
}