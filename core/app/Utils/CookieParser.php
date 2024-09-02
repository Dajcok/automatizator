<?php

namespace App\Utils;

class CookieParser
{
    static function parseFromResponse(array $setCookieHeaders): array
    {
        if(empty($setCookieHeaders)) return [];

        $cookies = [];

        foreach ($setCookieHeaders as $setCookie) {
            $cookieParts = explode(';', $setCookie);
            $cookieNameValue = explode('=', $cookieParts[0]);

            $cookies[] = (object)[
                'name' => $cookieNameValue[0],
                'value' => $cookieNameValue[1],
            ];
        }

        return $cookies;
    }
}
