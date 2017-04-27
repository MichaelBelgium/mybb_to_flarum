<?php
class Config
{
    public static $MIGRATE_AVATARS = false;                 //enable or disable migration of avatars

    public static $FLARUM_SERVER = "127.0.0.1";
    public static $FLARUM_USER = "";
    public static $FLARUM_PASSWORD = "";
    public static $FLARUM_DB = "";
    public static $FLARUM_PREFIX = "flar_";
    public static $FLARUM_AVATAR_PATH = "assets/avatars/";  //relative path from the script, normally not needed to edit this. (Only used if $MIGRATE_AVATARS = true)

    public static $MYBB_SERVER = "127.0.0.1";
    public static $MYBB_USER = "";
    public static $MYBB_PASSWORD  = "";
    public static $MYBB_DB = "";
    public static $MYBB_PREFIX = "mybb_";

    public static $MYBB_SKIP_SOFTDELETED = false;           //if true, the script won't migrate threads in mybb that are soft deleted
    public static $MYBB_PATH = "/var/www/html/mybb/";       //absolute path of mybb installation (Only used if $MIGRATE_AVATARS = true)
}

function rand_color()
{
    return '#' . str_pad(dechex(mt_rand(0, 0xFFFFFF)), 6, '0', STR_PAD_LEFT);
}

function to_slug($text, $check_exist = false)
{
    global $flarum_db;

    $text = preg_replace("/[^\w]/u", "-", $text);
    $text = preg_replace("/\-+/u","-", $text);
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

$configurator = new s9e\TextFormatter\Configurator;

$configurator->BBCodes->addFromRepository('B');
$configurator->BBCodes->addFromRepository('I');
$configurator->BBCodes->addFromRepository('U');
$configurator->BBCodes->addFromRepository('S');
$configurator->BBCodes->addFromRepository('URL');
$configurator->BBCodes->addFromRepository('IMG');
$configurator->BBCodes->addFromRepository('EMAIL');
$configurator->BBCodes->addFromRepository('CODE');
$configurator->BBCodes->addFromRepository('QUOTE');
$configurator->BBCodes->addFromRepository('LIST');
$configurator->BBCodes->addFromRepository('DEL');
$configurator->BBCodes->addFromRepository('COLOR');
$configurator->BBCodes->addFromRepository('*');
$configurator->BBCodes->addFromRepository('FONT');
$configurator->BBCodes->addFromRepository('ALIGN');
$configurator->BBCodes->addFromRepository('HR');

extract($configurator->finalize());

?>