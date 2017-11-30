<?php

class LinkParser {
    public static function GetCleanLink($link){
        $link = str_ireplace('http://', '', $link);
        $link = str_ireplace('https://', '', $link);
        if (substr_count($link, '/') === 1){
            $link = str_ireplace('/', '', $link);
        }
        return $link;
    }
    public static function GetFullLink($link){
        $ishttps = substr_count($link, 'https://') === 1;

        $link = str_ireplace('http://', '', $link);
        $link = str_ireplace('https://', '', $link);

        return ($ishttps ? 'https://' : 'http://').$link;
    }
}