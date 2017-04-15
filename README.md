# mybb_to_flarum

This is a PHP migration script to migrate most data from a mybb forum to a fresh flarum forum.

## What does it migrate?
* Forum users (their passwords are ALL reset to a bcrypt hash from current time)
* Categories
* Forums (not complete: see to do)
* Posts (not complete: see to do)

## Intructions
To get this to work u only need your (old) mybb database and a (recommended: fresh - altho it truncates everything) installed flarum forum.
Afterwards you need to edit the `Config.php` file so it can connect.

When this is done, you only need to get these files to anywhere on your webhost. At last but not least browse to `www.mywebsite.com/mybb_to_flarum.php` and let it do its job.

## To do
* posts that are in bbcode should be converted
* only categories and their forums will be converted, subforums under forums should be too
* participants_count should be filled in when migrating threads to discussions
