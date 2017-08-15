<?php
use s9e\TextFormatter\Configurator;

class Config
{
    const MIGRATE_AVATARS = false;                  //enable or disable migration of avatars

    const FLARUM_SERVER = "127.0.0.1";
    const FLARUM_USER = "";
    const FLARUM_PASSWORD = "";
    const FLARUM_DB = "";
    const FLARUM_PREFIX = "flar_";

    const FLARUM_AVATAR_PATH = "assets/avatars/";   //relative path from the script, normally not needed to edit this. (Only used if $MIGRATE_AVATARS = true
    const FLARUM_PM_TAG = "Private Discussions";    //name of the tag for private messages/discussions

    const MYBB_SERVER = "127.0.0.1";
    const MYBB_USER = "";
    const MYBB_PASSWORD  = "";
    const MYBB_DB = "";
    const MYBB_PREFIX = "mybb_";

    const MYBB_SKIP_TSOFTDELETED = true;            //if true, the script won't migrate threads in mybb that are soft deleted
    const MYBB_SKIP_PSOFTDELETED = true;            //if true, the script  won't migrate posts in mybb that are soft deleted
    const MYBB_PATH = "/var/www/html/mybb/";        //absolute path of mybb installation (Only used if $MIGRATE_AVATARS = true)
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
        $result = $flarum_db->query("SELECT slug FROM ".Config::FLARUM_PREFIX."tags WHERE slug = '$text'");
        if($result->num_rows > 0)
        {
           $result = $flarum_db->query("SELECT slug FROM ".Config::FLARUM_PREFIX."tags WHERE SLUG LIKE '$text%'");
           $text .= $result->num_rows;
        }
    }

    return strtolower($text);
}

$configurator = new Configurator;
$configurator->rootRules->createParagraphs(true);
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
$configurator->BBCodes->addCustom('[size={CHOICE=large,small,xx-small,x-small,medium,x-large,xx-large}]{TEXT}[/size]','<span style="font-size:{CHOICE}">{TEXT}</span>');

extract($configurator->finalize());

?>