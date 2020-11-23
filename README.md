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

**This extension has been tested with MyBB v1.8.24 and Flarum v0.1.0-beta.14**

Execute this command in the root of your flarum installation: `composer require michaelbelgium/mybb-to-flarum`. Navigate to your admin panel, enable the extension and you get a new link in the admin navigation bar.

## Usage
you can trigger the migration from the admin panel or the console:

```
$>flarum migrate-data:from-mybb

Description:
  Migrates data from an existing mybb forum

Usage:
  migrate-data:from-mybb [options]

Options:
      --host=HOST                            host of the mybb database
  -u, --user=USER                            user of the mybb database
  -p, --password[=PASSWORD]                  password for the mybb database [default: false]
  -d, --db=DB                                name of the mybb database
      --prefix[=PREFIX]                      prefix of the mybb database tables [default: "mybb_"]
      --path[=PATH]                          path to the mybb forum (used for avatar migration) [default: false]
      --avatars[=AVATARS]                    import avatars [default: true]
      --soft-posts[=SOFT-POSTS]              import soft deleted posts [default: true]
      --soft-threads[=SOFT-THREADS]          import soft deleted threads [default: true]
      --do-users[=DO-USERS]                  import users [default: true]
      --do-threads-posts[=DO-THREADS-POSTS]  import posts [default: true]
      --do-groups[=DO-GROUPS]                import groups [default: true]
      --do-categories[=DO-CATEGORIES]        import categories [default: true]
  -i, --interactive[=INTERACTIVE]            if false, do not prompt the user for missing data. useful for scripts [default: true]
```

## Important notes
* If u specify u want to migrate avatars then a path to your <u>MyBB forum is required also.</u>
* Forums with a redirect hyperlink are skipped. Flarum doesn't support them (yet).
* A guest who created a MyBB post or thread will appear as a deleted user in Flarum and not specifically a "guest".
* The core BBcode extension should be enabled too.