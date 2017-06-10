# mybb_to_flarum

This is a PHP migration script to migrate most data from a mybb forum to a fresh flarum forum.

## What does it migrate?

* Users (their passwords are ALL reset to a bcrypt hash from current time)
* Categories
* Forums
* Posts
* Groups
* Avatars (if specified)
* Private messages (if required extension is installed)

## Instructions
**This script has been tested with: MyBB v1.8.11 and Flarum v0.1.0-beta.6**

To get this to work u only need your (old) mybb database and a (recommended: fresh - altho it truncates everything) installed flarum forum.
Afterwards you need to edit the `Config.php` file so it can connect.

When this is done, you only need to upload these files to the <u>root of your Flarum installation</u>.
This is because Flarum uses [the s9e TextFormatter](https://github.com/s9e/TextFormatter) and the script uses it too to parse and save the content of the mybb posts.
At last but not least browse to `www.mywebsite.com/myflarumforum/mybb_to_flarum.php` and let it do its job.

## Important notes
* If u specify u want to migrate avatars then your (old) <u>MyBB forum is required also.</u>
* To be able to migrate private messages you need the extension [flagrow/byobu](https://github.com/flagrow/byobu)
* To get the SIZE-tag to work you'll need to edit the Flarum BBcode extension **FIRST** (before running the script) by replacing a line (see below) in `vendor/flarum/flarum-ext-bbcode/src/Listener/FormatBBCode.php` and then **re-enabling** the BBcode extension in administration panel

```PHP
//Replace:
$event->configurator->BBCodes->addFromRepository('SIZE');
//With:
$event->configurator->BBCodes->addCustom('[size={CHOICE=large,small,xx-small,x-small,medium,x-large,xx-large}]{TEXT}[/size]','<span style="font-size:{CHOICE}">{TEXT}</span>');
```

* forums with a redirect hyperlink are skipped. Flarum doesn't support them (yet).