<?php

namespace App\Helpers;

use Intervention\Image\ImageManagerStatic as Image;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ImageHelper {
    
    static function create_image_from_base64($folder, $base64_image, $user_config = []) {
            
        $path = 'public/' . $folder . '/';

        if (strpos($base64_image, 'svg+xml') !== false) {
            
            $encoded_base64_image = substr($base64_image, strpos($base64_image, ',') + 1);
            
            $name = !empty($user_config['name']) ? $user_config['name'] : Str::lower(Str::random(16));
            $name = $name . '.svg';

            Storage::disk('local')->put($path . $name, base64_decode($encoded_base64_image));
        
            return $name;

        } else {

            $config = [
                'width'  => !empty($user_config['width']) ? $user_config['width'] : 1024,
                'height' => !empty($user_config['height']) ? $user_config['height'] : 1024,
                'crop'   => !empty($user_config['crop']) ? $user_config['crop'] : false,
            ];
    
            $base64_image = str_replace('*;charset=utf-8', 'png', $base64_image);
                
            if (preg_match('/^data:image\/(\w+);base64,/', $base64_image, $type)) 
            {
                $encoded_base64_image = substr($base64_image, strpos($base64_image, ',') + 1);
                $type = strtolower($type[1]);
        
                $name = !empty($user_config['name']) ? $user_config['name'] : Str::lower(Str::random(16));
                $name = $name . '.' . $type;
        
                $img = Image::make($encoded_base64_image);
    
                if ($config['crop']) 
                {
                    $img->fit($config['width'], $config['height'], function ($constraint) use ($config) {
                        $constraint->aspectRatio(); // сохранить пропорции
                        $constraint->upsize();      // не увеличивать
                    });
                } 
                else 
                {
                    $img->resize($config['width'], $config['height'], function ($constraint) use ($config) {
                        $constraint->aspectRatio(); // сохранить пропорции
                        $constraint->upsize();      // не увеличивать
                    });
                }
        
                Storage::disk('local')->put($path . $name, $img->encode());
        
                return $name;
            }
        
        }
    
        return null;
    }

    static function create_image_from_url($folder, $url, $user_config = []) {
            
        $path = 'public/' . $folder . '/';

        $config = [
            'width'  => !empty($user_config['width']) ? $user_config['width'] : 1024,
            'height' => !empty($user_config['height']) ? $user_config['height'] : 1024,
            'crop'   => !empty($user_config['crop']) ? $user_config['crop'] : false,
        ];

        $img = Image::make($url)->encode('jpg', 80);

        if ($config['crop']) 
        {
            $img->fit($config['width'], $config['height'], function ($constraint) use ($config) {
                $constraint->aspectRatio(); // сохранить пропорции
                $constraint->upsize();      // не увеличивать
            });
        } 
        else 
        {
            $img->resize($config['width'], $config['height'], function ($constraint) use ($config) {
                $constraint->aspectRatio(); // сохранить пропорции
                $constraint->upsize();      // не увеличивать
            });
        }

        $name = Str::lower(Str::random(16)) . '.jpg';

        Storage::disk('local')->put($path . $name, $img->encode());

        return $name;
    }

    static function remove_image($path) {

        $path = 'public/' . $path;

        return Storage::disk('local')->delete($path);
    }

    static function copy_image($path, $filename) {

        $path  = 'public/' . $path . '/';
        $image = Image::make(Storage::disk('local')->path($path . $filename));

        $parts = explode('.', $filename);
        $filename = Str::lower(Str::random(16)) . '.' . $parts[1];

        Storage::disk('local')->put($path . $filename, $image->encode());

        return $filename; 
    }

    static function create_image_from_post($folder, $request, $user_config = []) {
        $path = 'public/' . $folder . '/';

        $config = [
            'width'  => !empty($user_config['width']) ? $user_config['width'] : 1024,
            'height' => !empty($user_config['height']) ? $user_config['height'] : 1024,
            'crop'   => !empty($user_config['crop']) ? $user_config['crop'] : false,
        ];

        if ($request->hasFile('imageupload'))
        {
            $file = $request->file('imageupload');

            $img = Image::make($file);

            $extentions = [
                'image/jpeg' =>'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/bmp' => 'bmp',
                'jpeg' =>'jpg'
            ];

            $mime = $img->mime();

            $ext  = 'jpg';
            if (array_key_exists($mime, $extentions)) {
                $ext  = $extentions[$mime];
            }

            $img = $img->encode($ext, 80);
            
            $name = Str::lower(Str::random(16)) . '.' . $ext;

            if ($config['crop']) 
            {
                $img->fit($config['width'], $config['height'], function ($constraint) use ($config) {
                    $constraint->aspectRatio(); // сохранить пропорции
                    $constraint->upsize();      // не увеличивать
                });
            } 
            else 
            {
                $img->resize($config['width'], $config['height'], function ($constraint) use ($config) {
                    $constraint->aspectRatio(); // сохранить пропорции
                    $constraint->upsize();      // не увеличивать
                });
            }
    
            Storage::disk('local')->put($path . $name, $img);
    
            return $name;
        }
    }
}

