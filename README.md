# mybb_to_flarum

This is a [Flarum](https://flarum.org/) extension to migrate data from a mybb forum to a fresh flarum instance.

![image admin section](http://puu.sh/CrA3x.png)

## What can it migrate?

In your admin panel you can choose what to migrate.

* Users (their passwords are ALL reset to a bcrypt hash from current time)
* Categories
* Forums
* Posts
* Groups
* Avatars

## Installation

**This extension has been tested with MyBB v1.8.21 and Flarum v0.1.0-beta.10**

Execute this command in the root of your flarum installation: `composer require michaelbelgium/mybb-to-flarum`. Navigate to your admin panel, enable the extension and you get a new link in the admin navigation bar.

## Important notes
* If u specify u want to migrate avatars then a path to your <u>MyBB forum is required also.</u>
* Forums with a redirect hyperlink are skipped. Flarum doesn't support them (yet).
* A guest who created a MyBB post or thread will appear as a deleted user in Flarum and not specifically a "guest".
* The core BBcode extension should be enabled too.