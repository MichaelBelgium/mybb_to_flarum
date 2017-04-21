<?php
class Config
{
    public static $FLARUM_SERVER = "127.0.0.1";
    public static $FLARUM_USER = "";
    public static $FLARUM_PASSWORD = "";
    public static $FLARUM_DB = "";
    public static $FLARUM_PREFIX = "flar_";

    public static $MYBB_SERVER = "127.0.0.1";
    public static $MYBB_USER = "";
    public static $MYBB_PASSWORD  = "";
    public static $MYBB_DB = "";
    public static $MYBB_PREFIX = "mybb_";
}

function rand_color()
{
    return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
}

function to_slug($text, $check_exist = false)
{
    global $flarum_db;

    $text = preg_replace("/[^\w]/", "-", $text);
    $text = preg_replace("/\-+/","-", $text);
    $text = trim($text, "-");

    if($check_exist)
    {
        $result = $flarum_db->query("SELECT slug FROM ".Config::$FLARUM_PREFIX."tags WHERE slug = '$text'");
        if($result->num_rows > 0)
        {
           $result = $flarum_db->query("SELECT slug FROM ".Config::$FLARUM_PREFIX."tags WHERE SLUG LIKE '$text%'");
           $text .= $result->num_rows;
        }
    }

    return strtolower($text);
}
?>