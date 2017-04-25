# mybb_to_flarum

This is a PHP migration script to migrate most data from a mybb forum to a fresh flarum forum.

## What does it migrate?

* Users (their passwords are ALL reset to a bcrypt hash from current time)
* Categories
* Forums
* Posts
* Groups
* Avatars (if specified)

## Instructions
**This script has been tested with: MyBB v1.8.11 and Flarum v0.1.0-beta.6**

To get this to work u only need your (old) mybb database and a (recommended: fresh - altho it truncates everything) installed flarum forum.
Afterwards you need to edit the `Config.php` file so it can connect.

When this is done, you only need to upload these files to the <u>root of your Flarum installation</u>.
This is because Flarum uses <a href='https://github.com/s9e/TextFormatter'>the s9e TextFormatter</a> and the script uses it too to parse and save the content of the mybb posts.
At last but not least browse to `www.mywebsite.com/myflarumforum/mybb_to_flarum.php` and let it do its job.

Note: If u specify u want to migrate avatars then your (old) <u>MyBB forum is required also.</u>

## To do
* forums with a redirect hyperlink are skipped. Flarum doesn't support them (yet).
* add last_post_number in tbl discussions and number in tbl posts to migrate