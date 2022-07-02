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
* Attachments (requires fof/upload)

## Installation

*This extension has been tested with MyBB v1.8.\* and Flarum v1.\**

Execute this command in the root of your flarum installation: `composer require michaelbelgium/mybb-to-flarum`. Navigate to your admin panel, enable the extension and you get a new link in the admin navigation bar.

## Usage
you can trigger the migration from the admin panel or the console:

```
> php flarum migrate-data:from-mybb

Description:
  Migrates data from an existing mybb forum

Usage:
  migrate-data:from-mybb [options]

Options:
      --host=HOST            host of the mybb database
  -u, --user=USER            user of the mybb database
  -p, --password[=PASSWORD]  password for the mybb database [default: ""]
  -d, --db=DB                name of the mybb database
      --prefix[=PREFIX]      prefix of the mybb database tables [default: "mybb_"]
      --users                Import users (excluding avatars)
      --threads-posts        Import posts (excluding soft deleted posts/threads)
      --groups               Import groups
      --categories           Import categories
      --avatars              Import avatars
      --path[=PATH]          Path to the mybb forum (required for avatar and attachment migration) [default: ""]
      --soft-posts           Import soft deleted posts
      --soft-threads         Import soft deleted threads
      --attachments          Import attachments
  -h, --help                 Display help for the given command. When no command is given display help for the list command   
  -n, --no-interaction       Do not ask any interactive question
```

### Example commands

Migrate only users and avatars
```
> php flarum migrate-data:from-mybb --host=127.0.0.1 --user=homestead --password=secret --db=mybb --users --avatars --path=../mybb
```

Migrate everything (excluding avatars and soft deleted posts/threads)
```
php flarum migrate-data:from-mybb --host=127.0.0.1 --user=homestead --password=secret --db=mybb --users --groups --threads-posts
```

Migrate users with threads and posts including soft deleted threads but excluding soft deleted posts
```
php flarum migrate-data:from-mybb --host=127.0.0.1 --user=homestead --password=secret --db=mybb --users --threads-posts --soft-threads
```
## Important notes
* If u specify u want to migrate avatars then a path to your <u>MyBB forum is required also.</u>
* Forums with a redirect hyperlink are skipped. Flarum doesn't support them (yet).
* A guest who created a MyBB post or thread will appear as a deleted user in Flarum and not specifically a "guest".
* The core BBcode extension should be enabled too.