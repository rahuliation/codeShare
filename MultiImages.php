<?php
/**
 * Created by PhpStorm.
 * User: rahul
 * Date: 10/11/17
 * Time: 10:57 AM
 */

namespace App\CustomTrait;


use App\Http\Helpers\Helper;
use Illuminate\Support\Facades\Route;


trait MultiImage
{
    private $storage_location='storage';

    private function uploadImages($image,$path){

            $file=$image['file'];
            $caption=!empty($image['caption'])? $image['caption']:'' ;
            $extentionless_name= preg_replace('/\\.[^.\\s]{3,4}$/', '', $file->getClientOriginalName());

             do {
                 $image_name=$extentionless_name.'-'.str_random(10).'.'.$file->extension();
                 $full_path=public_path().'/'.$this->storage_location.$path;
             } while(file_exists($full_path.$image_name));
             if( $file->move($this->storage_location.$path,$image_name)) {
                   $uploaded_image= [
                     'name'=> $image_name,
                     'path'=>'/'.$this->storage_location.$path
                     ];
                   foreach (Helper::get_languages() as $key=> $language)
                   {
                       $uploaded_image['caption'][$key]=$caption;
                   }
                   return $uploaded_image;
             };
    }
    private function oldImage($image,$image_list)
    {
        $old_image=$image_list[$image['old_file']];
        if(!empty($image['caption']))
        {
            $old_image['caption'][session('lang_id',1)]=$image['caption'];

        }
        return $old_image;
    }



    public function removeImagesJson($field) {
        if(!empty($this->attributes[$field]))
        {
            $remove_image_list=json_decode($this->attributes[$field],1);

            if(!empty($remove_image_list) && count($remove_image_list)) {
                foreach ($remove_image_list as $remove_image)
                {
                    $delete_path=public_path().$remove_image['path'].$remove_image['name'];
                    unlink($delete_path);
                }
            }
        }
    }

    private function setImagesJson($field,$images,$path){

        $uploaded_images=[];
        if(!$images)
        {
            $images=[];
        }


        if(!empty($this->attributes[$field]))
        {
            $old_image_list=json_decode($this->attributes[$field],1);
            $remove_image_list=$old_image_list;
        }

         foreach ($images as $image)
          {
              if(!empty($image['file'])) {
              $uploaded_images[]=$this->uploadImages($image,$path);
              }
              else if(isset($image['old_file']))
              {
                  unset($remove_image_list[$image['old_file']]);
                  $uploaded_images[]=$this->oldImage($image,$old_image_list);
              }

          }
         if(!empty($remove_image_list) && count($remove_image_list)) {
              foreach ($remove_image_list as $remove_image)
              {
                  $delete_path=public_path().$remove_image['path'].$remove_image['name'];
                  unlink($delete_path);
              }
          }
        $this->attributes[$field] = json_encode($uploaded_images);


    }

}
