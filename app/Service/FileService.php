<?php
namespace App\Service;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use App\Models\Products;

class FileService extends AppService
{
    public static function upload($imageBase64Data, $module, $item_id = false, $disk = null){
        $disk = $disk ?? config('services.filesystem.disk');
        try {
            if (preg_match('/^(data:\s*image\/(\w+);base64,)/' , $imageBase64Data , $res)) {
                $file_ext = $res[2];
                $base64Img = base64_decode(str_replace($res[1],'', $imageBase64Data));

                $timestamp = microtime(true);
                $timestamp = str_replace('.', '-', $timestamp);
                $file_path = 'files/'.$module.'/';
                $file_name = $item_id?$item_id.'_' . $timestamp . '.' . $file_ext:$timestamp . '.' . $file_ext;
                if(Storage::disk($disk)->put($file_path.$file_name, $base64Img)){
                    return [
                        'file_path' => $file_path,
                        'file_name' => $file_name,
                        'file_ext'  => $file_ext,
                    ];
                }
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function upload_file($file, $module, $item_id = false, $disk = null){
        $disk = $disk ?? config('services.filesystem.disk');
        try {
            $file_ext = $file->getClientOriginalExtension();

            $timestamp = microtime(true);
            $timestamp = str_replace('.', '-', $timestamp);
            $file_path = 'files/'.$module.'/';
            $file_name = $item_id?$item_id.'_' . $timestamp . '.' . $file_ext:$timestamp . '.' . $file_ext;
            // if(Storage::disk($disk)->put($file_path.$file_name, $file)){
            if (Storage::disk($disk)->putFileAs($file_path, $file, $file_name)) {
                return [
                    'file_path' => $file_path,
                    'file_name' => $file_name,
                    'file_ext'  => $file_ext,
                ];
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

    public static function upload_string($content, $module, $file_full_name, $disk = null){
        $disk = $disk ?? config('services.filesystem.disk');
        try {
            $file_path = 'files/'.$module.'/';
            $file_name = $file_full_name;
            if (Storage::disk($disk)->put($file_path.$file_name, $content)){
                // $file = Storage::disk($disk)->path($file_path . $file_name);
                // chmod($file, 0755);

                return [
                    'file_path' => $file_path,
                    'file_name' => $file_name,
                ];
            }
            return false;
        } catch (\Exception $e) {
            \Log::error("文件儲存失敗: " . $e->getMessage());
            return false;
        }
    }

    public function ckUpload($file, $module, $item_id = false){
        $full_path = public_path('upload');
        $this->createDir($full_path);
        $full_path .= '/'.$module;
        $this->createDir($full_path);
        $file_path = '/upload/'.$module;

        if($item_id){
            $full_path .= '/'.$item_id;
            $file_path .= '/'.$item_id;
            $this->createDir($full_path);
        }
        $url_path = Url($file_path);
        $permitted = array('image/gif', 'image/jpeg', 'image/pjpeg', 'image/png');

        // replace spaces with underscores
        $file_name = preg_replace('/\s(?=)/', '_', urldecode($file['name'])); //str_replace(' ', '_', $file['name']);
        // 修改上傳檔案名稱
        $tmp = explode('.', $file_name);
        $file_name = date('YmdHis').rand(10,99).'_'.md5_file($file['tmp_name']);
        $file_name .= (count($tmp) > 1 ? ".{$tmp[count($tmp) - 1]}" : '');
        // assume filetype is false
        $typeOK = false;
        // check filetype is ok
        foreach ($permitted as $type) {
            if ($type == $file['type']) {
                $typeOK = true;
                break;
            }
        }
        $file_path .= '/'.$file_name;

        $result = false;
        if ($typeOK) {
            if($file['error']==0){
                if(move_uploaded_file($file['tmp_name'], $full_path.'/'.$file_name)){
                    $result = [];
                    $result['file_path'] = $file_path;
                    $result['file_name'] = $file_name;
                    $result['url_path'] = $url_path .'/'. $file_name;
                }
                else{
                    \Log::debug('move_uploaded_file fail '.$module);
                }
            }
        }

        return $result;
    }

    public function delete($file){
        unlink($fileInfo->path);
        return true;
    }

    public static function createDir($dir) {
        if (!is_dir($dir)) {
            $oldumask = umask(0);
            if (!mkdir($dir, 0777, true)) {
                die('Failed to create folders...' . $dir);
            }
            umask($oldumask);
        }
    }

    // 刪除r2上的檔案
    public static function deleteR2File($file_path, $disk = null) {
        $disk = $disk ?? config('services.filesystem.disk');
        try {
            if (Storage::disk($disk)->exists($file_path)) {
                Storage::disk($disk)->delete($file_path);
                return true;
            }
            return false;
        } catch (\Exception $e) {
            Log::error("刪除檔案失敗: " . $e->getMessage());
            return false;
        }
    }
}
