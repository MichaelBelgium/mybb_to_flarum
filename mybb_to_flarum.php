<?php
    require "Config.php";

	set_time_limit(0);
	ini_set('memory_limit', -1);

    $flarum_db = new mysqli(Config::$FLARUM_SERVER, Config::$FLARUM_USER, Config::$FLARUM_PASSWORD, Config::$FLARUM_DB);
    $mybb_db = new mysqli(Config::$MYBB_SERVER, Config::$MYBB_USER, Config::$MYBB_PASSWORD, Config::$MYBB_DB);

    if($flarum_db->connect_errno)
        die("Flarum db connection failed: ". $flarum_db->connect_error);
    else if($mybb_db->connect_errno)
        die("MyBB db connection failed: ". $mybb_db->connect_error);

    echo "<p>Connection successful.</p>";

    echo "<p>Migrating users ...</p>";

    $users = $mybb_db->query("SELECT uid, username, email, postnum, threadnum, FROM_UNIXTIME( regdate ) AS regdate, FROM_UNIXTIME( lastvisit ) AS lastvisit FROM  ".Config::$MYBB_PREFIX."users ");
    if($users->num_rows > 0)
    {
        $flarum_db->query("TRUNCATE TABLE ".Config::$FLARUM_PREFIX."users");
        while($row = $users->fetch_assoc())
        {
            $password = password_hash(time(),PASSWORD_BCRYPT );
            $result = $flarum_db->query("INSERT INTO ".Config::$FLARUM_PREFIX."users (id, username, email, is_activated, password, join_time, last_seen_time, discussions_count, comments_count) VALUES ({$row["uid"]},'{$row["username"]}', 'bla{$row["email"]}', 1, '$password', '{$row["regdate"]}', '{$row["lastvisit"]}', {$row["threadnum"]}, {$row["postnum"]})");
            if($result === false)
                echo "Error executing query: ". $flarum_db->error. "<br/>";
        }
    }

    echo "<p>Migrating categories to tags and forums to sub-tags ...</p>";

    //categories
    $categories = $mybb_db->query("SELECT fid, name, description FROM ".Config::$MYBB_PREFIX."forums WHERE type = 'c'");
    if($categories->num_rows > 0)
    {
        $flarum_db->query("TRUNCATE TABLE ".Config::$FLARUM_PREFIX."tags");
        $c_pos = 0;
        while($crow = $categories->fetch_assoc())
        {
            $slug = str_replace(" ", "-", strtolower($crow["name"]));
            $result = $flarum_db->query("INSERT INTO ".Config::$FLARUM_PREFIX."tags (id, name, slug, description, position) VALUES ({$crow["fid"]},'{$crow["name"]}', '$slug', '{$crow["description"]}', $c_pos)");
            if($result === false)
                echo "Error executing query: ".$flarum_db->error."<br />";
            else
            {
                //subforums
                $result = $mybb_db->query("SELECT * FROM ".Config::$MYBB_PREFIX."forums WHERE type = 'f' AND pid = {$crow["fid"]}");
                if($result->num_rows > 0)
                {
                    $f_pos = 0;
                    while($srow = $result->fetch_assoc())
                    {
                        $slug = str_replace(" ", "-", strtolower($srow["name"]));
                        $flarum_db->query("INSERT INTO ".Config::$FLARUM_PREFIX."tags (id, name, slug, description, parent_id, position) VALUES ({$srow["fid"]},'{$srow["name"]}', '$slug', '{$srow["description"]}', {$crow["fid"]}, $f_pos)");

                        $f_pos++;
                    }
                }
            }
            $c_pos++;
        }
    }

    echo "<p>Migrating topics...</p>"
    //flarum discussions = mybb threads
    //flarum topics = mybb posts




?>