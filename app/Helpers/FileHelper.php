<?php

namespace App\Helpers;

class FileHelper {
    
    static function checkMimeType($data, $mime_type)
    {
        $f = finfo_open();
        $m_type = finfo_buffer($f, $data, FILEINFO_MIME_TYPE);

        return  $m_type === $mime_type;
    }
}

