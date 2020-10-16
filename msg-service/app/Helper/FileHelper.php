<?php

declare(strict_types=1);

namespace App\Helper;

class FileHelper
{

    /**
     * 获取文件扩展名
     *
     * @param string $file 文件路径
     *
     * @return string
     */
    public static function getFileExt(string $file): string
    {
        if (strpos($file, '?')) {
            $file = substr($file, 0, strpos($file, '?'));
        }
        return strtolower(pathinfo($file, PATHINFO_EXTENSION));
    }

    /**
     * 写入文件
     *
     * @param string $file   文件路径
     * @param string $data   内容
     * @param bool   $append 是否追加
     * @param int    $mode   文件模式
     *
     * @return bool
     */
    public static function writeFile(string $file, string $data, bool $append = false, int $mode = 0766): bool
    {
        $res = false;
        if (!empty($file) && !empty($data)) {
            $dir = dirname($file);
            if (!is_dir($dir)) {
                @mkdir($dir, 0766, true);
            }

            if ($fp = @fopen($file, $append ? 'ab' : 'wb')) {
                $res = @fwrite($fp, $data);
                @fclose($fp);
                @chmod($file, $mode);
            }
        }

        return $res;
    }

    /**
     * 移除UTF8的BOM头
     *
     * @param string $val
     *
     * @return string
     */
    public static function removeBom(string $val): string
    {
        //        if (substr($val, 0, 3) == pack('CCC', 239, 187, 191)) {
        //            return substr($val, 3);
        //        }

        $val = str_replace("\xEF\xBB\xBF", '', $val);
        return $val;
    }

    /**
     * 创建ZIP压缩包
     *
     * @param array  $files       要压缩的文件路径数组
     * @param string $destination zip目标文件
     * @param bool   $overwrite   是否覆盖目标文件
     *
     * @return bool
     */
    public static function createZip(array $files = [], string $destination = '', bool $overwrite = false): bool
    {
        //if the zip file already exists and overwrite is false, return false
        $exist = file_exists($destination);
        if ($exist && !$overwrite) {
            return false;
        }

        $validFiles = [];
        //cycle through each file
        foreach ($files as $file) {
            if (is_dir($file)) {
                $arr        = DirectoryHelper::getFileTree($file, 'file');
                $validFiles = array_merge($validFiles, $arr);
            } elseif (file_exists($file)) {
                array_push($validFiles, $file);
            }
        }

        //if we have good files...
        if (count($validFiles)) {
            //create the archive
            $zip = new \ZipArchive();
            if (@$zip->open($destination, $exist ? \ZIPARCHIVE::OVERWRITE : \ZIPARCHIVE::CREATE) !== true) {
                return false;
            }

            //add the files
            $desPath = dirname($destination);
            foreach ($validFiles as $file) {
                $localname = str_replace($desPath, '', $file);
                $zip->addFile($file, $localname);
            }

            //close the zip -- done!
            @$zip->close();

            //check to make sure the file exists
            return file_exists($destination);
        }
        return false;
    }

    /**
     * 将图片文件转换为base64编码
     *
     * @param string $file 图片文件路径
     *
     * @return string
     */
    public static function img2Base64(string $file): string
    {
        $res = '';
        if (empty($file) || !file_exists($file)) {
            return $res;
        }

        $imgInfo = getimagesize($file); //取得图片的大小，类型等
        $fp      = fopen($file, 'r');
        if ($fp) {
            $fileContent = chunk_split(base64_encode(fread($fp, filesize($file)))); //base64编码
            $imgType     = 'jpg';
            $typeNum     = $imgInfo[2] ?? '';
            switch ($typeNum) {
                case 1:
                    $imgType = 'gif';
                    break;
                case 2:
                    $imgType = 'jpg';
                    break;
                case 3:
                    $imgType = 'png';
                    break;
                case 18:
                    $imgType = 'webp';
                    break;
            }
            $res = 'data:image/' . $imgType . ';base64,' . $fileContent; //合成图片的base64编码
            @fclose($fp);
        }

        return $res;
    }

    /**
     * 获取所有的文件MIME键值数组
     * @return array
     */
    public static function getAllMimes()
    {
        return [
            '323'     => 'text/h323',
            '3gp'     => 'video/3gpp',
            '7z'      => 'application/x-7z-compressed',
            'acx'     => 'application/internet-property-stream',
            'ai'      => 'application/postscript',
            'aif'     => 'audio/x-aiff',
            'aifc'    => 'audio/x-aiff',
            'aiff'    => 'audio/x-aiff',
            'apk'     => 'application/vnd.android.package-archive',
            'asf'     => 'video/x-ms-asf',
            'asr'     => 'video/x-ms-asf',
            'asx'     => 'video/x-ms-asf',
            'au'      => 'audio/basic',
            'avi'     => 'video/x-msvideo',
            'axs'     => 'application/olescript',
            'bas'     => 'text/plain',
            'bcpio'   => 'application/x-bcpio',
            'bin'     => 'application/octet-stream',
            'bmp'     => 'image/bmp',
            'bz'      => 'application/x-bzip',
            'bz2'     => 'application/x-bzip2',
            'c'       => 'text/plain',
            'cat'     => 'application/vnd.ms-pkiseccat',
            'cdf'     => 'application/x-cdf',
            'cer'     => 'application/x-x509-ca-cert',
            'class'   => 'application/octet-stream',
            'clp'     => 'application/x-msclip',
            'cmx'     => 'image/x-cmx',
            'cod'     => 'image/cis-cod',
            'conf'    => 'text/plain',
            'cpio'    => 'application/x-cpio',
            'crd'     => 'application/x-mscardfile',
            'crl'     => 'application/pkix-crl',
            'crt'     => 'application/x-x509-ca-cert',
            'csh'     => 'application/x-csh',
            'css'     => 'text/css',
            'csv'     => 'text/csv',
            'dcr'     => 'application/x-director',
            'der'     => 'application/x-x509-ca-cert',
            'dir'     => 'application/x-director',
            'dll'     => 'application/x-msdownload',
            'dms'     => 'application/octet-stream',
            'doc'     => 'application/msword',
            'docx'    => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
            'dot'     => 'application/msword',
            'dvi'     => 'application/x-dvi',
            'dxr'     => 'application/x-director',
            'eps'     => 'application/postscript',
            'epub'    => 'application/epub+zip',
            'etx'     => 'text/x-setext',
            'evy'     => 'application/envoy',
            'exe'     => 'application/octet-stream',
            'fif'     => 'application/fractals',
            'flr'     => 'x-world/x-vrml',
            'flv'     => 'video/x-flv',
            'gif'     => 'image/gif',
            'gtar'    => 'application/x-gtar',
            'gz'      => 'application/x-gzip',
            'h'       => 'text/plain',
            'hdf'     => 'application/x-hdf',
            'hlp'     => 'application/winhlp',
            'hqx'     => 'application/mac-binhex40',
            'hta'     => 'application/hta',
            'htc'     => 'text/x-component',
            'htm'     => 'text/html',
            'html'    => 'text/html',
            'htt'     => 'text/webviewhtml',
            'ico'     => 'image/x-icon',
            'ief'     => 'image/ief',
            'iii'     => 'application/x-iphone',
            'ins'     => 'application/x-internet-signup',
            'isp'     => 'application/x-internet-signup',
            'jar'     => 'application/java-archive',
            'java'    => 'text/plain',
            'jfif'    => 'image/pipeg',
            'jpe'     => 'image/jpeg',
            'jpeg'    => 'image/jpeg',
            'jpg'     => 'image/jpeg',
            'js'      => 'application/x-javascript',
            'json'    => 'application/json',
            'latex'   => 'application/x-latex',
            'lha'     => 'application/octet-stream',
            'log'     => 'text/plain',
            'lsf'     => 'video/x-la-asf',
            'lsx'     => 'video/x-la-asf',
            'lzh'     => 'application/octet-stream',
            'm13'     => 'application/x-msmediaview',
            'm14'     => 'application/x-msmediaview',
            'm3u'     => 'audio/x-mpegurl',
            'man'     => 'application/x-troff-man',
            'mdb'     => 'application/x-msaccess',
            'me'      => 'application/x-troff-me',
            'mht'     => 'message/rfc822',
            'mhtml'   => 'message/rfc822',
            'mid'     => 'audio/mid',
            'mny'     => 'application/x-msmoney',
            'mov'     => 'video/quicktime',
            'movie'   => 'video/x-sgi-movie',
            'mp2'     => 'video/mpeg',
            'mp3'     => 'audio/mpeg',
            'mp4'     => 'video/mp4',
            'mpa'     => 'video/mpeg',
            'mpe'     => 'video/mpeg',
            'mpeg'    => 'video/mpeg',
            'mpg'     => 'video/mpeg',
            'mpp'     => 'application/vnd.ms-project',
            'mpv2'    => 'video/mpeg',
            'ms'      => 'application/x-troff-ms',
            'mvb'     => 'application/x-msmediaview',
            'nws'     => 'message/rfc822',
            'oda'     => 'application/oda',
            'p10'     => 'application/pkcs10',
            'p12'     => 'application/x-pkcs12',
            'p7b'     => 'application/x-pkcs7-certificates',
            'p7c'     => 'application/x-pkcs7-mime',
            'p7m'     => 'application/x-pkcs7-mime',
            'p7r'     => 'application/x-pkcs7-certreqresp',
            'p7s'     => 'application/x-pkcs7-signature',
            'pbm'     => 'image/x-portable-bitmap',
            'pdf'     => 'application/pdf',
            'pfx'     => 'application/x-pkcs12',
            'pgm'     => 'image/x-portable-graymap',
            'pko'     => 'application/ynd.ms-pkipko',
            'pma'     => 'application/x-perfmon',
            'pmc'     => 'application/x-perfmon',
            'pml'     => 'application/x-perfmon',
            'pmr'     => 'application/x-perfmon',
            'pmw'     => 'application/x-perfmon',
            'png'     => 'image/png',
            'pnm'     => 'image/x-portable-anymap',
            'pot'     => 'application/vnd.ms-powerpoint',
            'ppm'     => 'image/x-portable-pixmap',
            'pps'     => 'application/vnd.ms-powerpoint',
            'ppt'     => 'application/vnd.ms-powerpoint',
            'pptx'    => 'application/vnd.openxmlformats-officedocument.presentationml.presentation',
            'prf'     => 'application/pics-rules',
            'ps'      => 'application/postscript',
            'pub'     => 'application/x-mspublisher',
            'qt'      => 'video/quicktime',
            'ra'      => 'audio/x-pn-realaudio',
            'ram'     => 'audio/x-pn-realaudio',
            'rar'     => 'application/x-rar-compressed',
            'ras'     => 'image/x-cmu-raster',
            'rgb'     => 'image/x-rgb',
            'rmi'     => 'audio/mid',
            'rmvb'    => 'audio/x-pn-realaudio',
            'roff'    => 'application/x-troff',
            'rtf'     => 'application/rtf',
            'rtx'     => 'text/richtext',
            'scd'     => 'application/x-msschedule',
            'sct'     => 'text/scriptlet',
            'setpay'  => 'application/set-payment-initiation',
            'setreg'  => 'application/set-registration-initiation',
            'sh'      => 'application/x-sh',
            'shar'    => 'application/x-shar',
            'sit'     => 'application/x-stuffit',
            'snd'     => 'audio/basic',
            'spc'     => 'application/x-pkcs7-certificates',
            'spl'     => 'application/futuresplash',
            'src'     => 'application/x-wais-source',
            'sst'     => 'application/vnd.ms-pkicertstore',
            'stl'     => 'application/vnd.ms-pkistl',
            'stm'     => 'text/html',
            'sv4cpio' => 'application/x-sv4cpio',
            'sv4crc'  => 'application/x-sv4crc',
            'svg'     => 'image/svg+xml',
            'swf'     => 'application/x-shockwave-flash',
            't'       => 'application/x-troff',
            'tar'     => 'application/x-tar',
            'tcl'     => 'application/x-tcl',
            'tex'     => 'application/x-tex',
            'texi'    => 'application/x-texinfo',
            'texinfo' => 'application/x-texinfo',
            'tgz'     => 'application/x-compressed',
            'tif'     => 'image/tiff',
            'tiff'    => 'image/tiff',
            'tr'      => 'application/x-troff',
            'trm'     => 'application/x-msterminal',
            'tsv'     => 'text/tab-separated-values',
            'txt'     => 'text/plain',
            'uls'     => 'text/iuls',
            'ustar'   => 'application/x-ustar',
            'vcf'     => 'text/x-vcard',
            'vrml'    => 'x-world/x-vrml',
            'wav'     => 'audio/x-wav',
            'wcm'     => 'application/vnd.ms-works',
            'wdb'     => 'application/vnd.ms-works',
            'weba'    => 'audio/webm',
            'webm'    => 'video/webm',
            'webp'    => 'image/webp',
            'wks'     => 'application/vnd.ms-works',
            'wma'     => 'audio/x-ms-wma',
            'wmf'     => 'application/x-msmetafile',
            'wmv'     => 'audio/x-ms-wmv',
            'wps'     => 'application/vnd.ms-works',
            'wri'     => 'application/x-mswrite',
            'wrl'     => 'x-world/x-vrml',
            'wrz'     => 'x-world/x-vrml',
            'xaf'     => 'x-world/x-vrml',
            'xbm'     => 'image/x-xbitmap',
            'xhtml'   => 'application/xhtml+xml',
            'xla'     => 'application/vnd.ms-excel',
            'xlc'     => 'application/vnd.ms-excel',
            'xlm'     => 'application/vnd.ms-excel',
            'xls'     => 'application/vnd.ms-excel',
            'xlsx'    => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'xlt'     => 'application/vnd.ms-excel',
            'xlw'     => 'application/vnd.ms-excel',
            'xml'     => 'text/plain',
            'xof'     => 'x-world/x-vrml',
            'xpm'     => 'image/x-xpixmap',
            'xwd'     => 'image/x-xwindowdump',
            'z'       => 'application/x-compress',
            'zip'     => 'application/zip',
        ];
    }

    /**
     * 获取文件的mime类型
     *
     * @param string $file 文件路径
     *
     * @return string
     */
    public static function getFileMime(string $file): string
    {
        $allMimes = self::getAllMimes();
        $ext      = self::getFileExt($file);
        $res      = $allMimes[$ext] ?? '';

        return $res;
    }

    /**
     * 把整个文件读入一个数组中,每行作为一个元素.
     *
     * @param string $path
     *
     * @return array
     */
    public static function readInArray(string $path): array
    {
        if (!is_file($path)) {
            return [];
        }

        return file($path, FILE_IGNORE_NEW_LINES);
    }
}
