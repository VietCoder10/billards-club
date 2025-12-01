<?php

namespace App\Components;

use Exception;
use Illuminate\Support\Facades\Storage;

class CommonComponent
{
    public static function uploadFile($folder, $file, $fileName)
    {
        try {
            // $storage = Storage::disk('public');

            // return $storage->put($folder, $file, $fileName);
            $storage = Storage::disk('public');

            return $storage->putFileAs($folder, $file, $fileName);
        } catch (Exception $exception) {
            return false;
        }

        return true;
    }

    public static function uploadFileName($extension = '')
    {
        return sha1(time().rand(0, 9999999)).'.'.$extension;
    }

    public static function deleteFile($folder, $nameFile)
    {
        try {
            return Storage::disk('public')->delete($folder.'/'.$nameFile);
        } catch (Exception $exception) {
            return false;
        }

        return true;
    }

    public static function getFullUrl($blob)
    {
        return Storage::disk('public')->url($blob);
        $_signature = self::getSASForBlob(env('AZURE_STORAGE_NAME'), env('AZURE_STORAGE_CONTAINER'), $blob, 'b', 'r', env('AZURE_STORAGE_KEY'));
        $_blobUrl = self::getBlobUrl(env('AZURE_STORAGE_NAME'), env('AZURE_STORAGE_CONTAINER'), $blob, 'b', 'r', $_signature);

        return $_blobUrl;
    }

    public static function getSASForBlob($accountName, $container, $blob, $resourceType, $permissions, $key)
    {
        $_arraysign = [];
        $_arraysign[] = $permissions;
        $_arraysign[] = '';
        $_arraysign[] = gmdate("Y-m-d\TH:i:s\Z", strtotime('+ 24 hour'));
        $_arraysign[] = '/'.$accountName.'/'.$container.'/'.$blob;
        $_arraysign[] = '';
        $_arraysign[] = '2014-02-14'; //the API version is now required
        $_arraysign[] = '';
        $_arraysign[] = '';
        $_arraysign[] = '';
        $_arraysign[] = '';
        $_arraysign[] = '';

        $_str2sign = implode("\n", $_arraysign);

        return base64_encode(
            hash_hmac('sha256', urldecode(utf8_encode($_str2sign)), base64_decode($key), true)
        );
    }

    public static function getBlobUrl($accountName, $container, $blob, $resourceType, $permissions, $_signature)
    {
        $_parts = [];
        $expiry = gmdate("Y-m-d\TH:i:s\Z", strtotime('+ 24 hour'));
        $_parts[] = (! empty($expiry)) ? 'se='.urlencode($expiry) : '';
        $_parts[] = 'sr='.$resourceType;
        $_parts[] = (! empty($permissions)) ? 'sp='.$permissions : '';
        $_parts[] = 'sig='.urlencode($_signature);
        $_parts[] = 'sv=2014-02-14';

        /* Create the signed blob URL */
        $accountName = 'https://'.$accountName.'.blob.core.windows.net/';
        $finalUrlPath = (env('ASSET_CDN_URL') !== null) ? env('ASSET_CDN_URL') : $accountName;

        $_url = $finalUrlPath
            .$container.'/'
            .$blob.'?'
            .implode('&', $_parts);

        return $_url;
    }

    public static function urlencode($string)
    {
        $encoded = rawurlencode($string);
        $encoded = str_replace('%7E', '~', $encoded);

        return $encoded;
    }

    public static function newListLimit($query)
    {
        $newSizeLimit = 20;
        $arrPageSize = [20, 50, 100];
        if (isset($query['limit_page'])) {
            $newSizeLimit = (($query['limit_page'] === '') || ! in_array($query['limit_page'], $arrPageSize)) ? $newSizeLimit : $query['limit_page'];
        }
        if (((isset($query['limit_page']))) && (! empty($query->query('limit_page')))) {
            $newSizeLimit = (! in_array($query->query('limit_page'), $arrPageSize)) ? $newSizeLimit : $query->query('limit_page');
        }

        return $newSizeLimit;
    }

    public static function checkPaginatorList($query)
    {
        if ($query->currentPage() > $query->lastPage()) {
            return true;
        }

        return false;
    }

    /**
     * [escapeLikeSentence description]
     * @param  [type]  $str    :search conditions
     * @param  bool $before : add % before
     * @param  bool $after  : add % after
     * @return [type]          [description]
     */
    public static function escapeLikeSentence($column, $str, $before = true, $after = true)
    {
        $result = str_replace('\\', '[\]', self::mbTrim($str)); // \ -> \\
        $result = str_replace('%', '\%', $result); // % -> \%
        $result = str_replace('_', '\_', $result); // _ -> \_

        return [[$column, 'LIKE', (($before) ? '%' : '').$result.(($after) ? '%' : '')]];
    }

    public static function handleSearchQuery($str, $before = true, $after = true)
    {
        $result = str_replace('\\', '[\]', self::mbTrim($str)); // \ -> \\
        $result = str_replace('%', '\%', $result); // % -> \%
        $result = str_replace('_', '\_', $result); // _ -> \_

        return (($before) ? '%' : '').$result.(($after) ? '%' : '');
    }

    public static function mbTrim($string)
    {
        $whitespace = '[\s\0\x0b\p{Zs}\p{Zl}\p{Zp}]';
        $ret = preg_replace(sprintf('/(^%s+|%s+$)/u', $whitespace, $whitespace), '', $string);

        return $ret;
    }

    public static function generateRandomString($length = 10)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[rand(0, $charactersLength - 1)];
        }

        return $randomString;
    }

    public static function getVimeoThumbnail($url)
    {
        $curl = curl_init();
        $requestUrl = "https://vimeo.com/api/oembed.json?url=".urlencode($url)."&width=480&height=360";
        curl_setopt_array($curl, array(
            CURLOPT_URL => $requestUrl,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET'
        ));
        $response = curl_exec($curl);
        curl_close($curl);
        return json_decode($response, true);
    }
}
