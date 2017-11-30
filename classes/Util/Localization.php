<?php
class Localization{
    static $directory = "locale";
    private static $defaultLanguage = "en";
    private static $language;
    private static $data = [];

    public static function SetLanguage($language){
        self::$language = $language;
    }
    public static function SetDefaultLanguage($language){
        self::$defaultLanguage = $language;
    }
    public static function GetLanguage(){
        return self::$language ? self::$language : self::$defaultLanguage;
    }

    private function Load(){
        if (!isset(self::$data[self::GetLanguage()])){

            $file = GetDynamicDirectory().self::$directory."/".self::GetLanguage().".json";

            if (file_exists($file)){
                self::$data[self::GetLanguage()] = json_decode(file_get_contents($file), true);
            }else{
                throw new Exception("Localization file not found for ".self::GetLanguage());
            }
        }
    }

    public static function Get($key){
        self::Load();
        $data = self::$data[self::GetLanguage()];
        if (isset($data[$key])){
            return $data[$key];
        }

        return null;
    }

    public static function Out($key){
        echo self::Get($key);
    }

	public static function GetJson(){
		self::Load();
		return json_encode(self::$data[self::GetLanguage()]);
	}
}