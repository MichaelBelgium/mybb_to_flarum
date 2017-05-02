    <?php
    include "vendor/autoload.php";
    require "Config.php";
    
    set_time_limit(0);

    $flarum_db = new mysqli(Config::$FLARUM_SERVER, Config::$FLARUM_USER, Config::$FLARUM_PASSWORD, Config::$FLARUM_DB);
    if($flarum_db->connect_errno)
        die("Flarum db connection failed: ". $flarum_db->connect_error);

    $mybb_db = new mysqli(Config::$MYBB_SERVER, Config::$MYBB_USER, Config::$MYBB_PASSWORD, Config::$MYBB_DB);
    if($mybb_db->connect_errno)
        die("MyBB db connection failed: ". $mybb_db->connect_error);
    
    $mybb_db->query("SET CHARSET 'utf8';");
    $flarum_db->query("SET CHARSET 'utf8';");
    $parent_tags = array();

    echo "<p>Migrating users ...<br />";

    $users = $mybb_db->query("SELECT uid, username, email, postnum, threadnum, FROM_UNIXTIME( regdate ) AS regdate, FROM_UNIXTIME( lastvisit ) AS lastvisit, usergroup, additionalgroups, avatar FROM ".Config::$MYBB_PREFIX."users ");
    if($users->num_rows > 0)
    {
        $flarum_db->query("TRUNCATE TABLE ".Config::$FLARUM_PREFIX."users");

        while($row = $users->fetch_assoc())
        {
            $password = password_hash(time(),PASSWORD_BCRYPT );
            $result = $flarum_db->query("INSERT INTO ".Config::$FLARUM_PREFIX."users (id, username, email, is_activated, password, join_time, last_seen_time, discussions_count, comments_count) VALUES ({$row["uid"]},'{$row["username"]}', '{$row["email"]}', 1, '$password', '{$row["regdate"]}', '{$row["lastvisit"]}', {$row["threadnum"]}, {$row["postnum"]})");
            if($result === false) die("Error executing query: ". $flarum_db->error);

            $usergroup = (int)$row["usergroup"];
            $othergroups = explode(",", $row["additionalgroups"]);

            if($usergroup > 7)
                $flarum_db->query( "INSERT INTO ".Config::$FLARUM_PREFIX."users_groups (user_id, group_id) VALUES ({$row["uid"]}, {$usergroup})");

            foreach($othergroups as $group)
            {
                if((int)$group <= 7) continue;
                $flarum_db->query("INSERT INTO ".Config::$FLARUM_PREFIX."users_groups (user_id, group_id) VALUES ({$row["uid"]}, $group)");
            }

            if(Config::$MIGRATE_AVATARS)
            {
                if(!empty(Config::$MYBB_PATH) && !empty($row["avatar"]))
                {
                    $avatar = explode("?", basename($row["avatar"]))[0];
                    if(file_exists(Config::$MYBB_PATH.$row["avatar"]))
                    {
                        if(copy(Config::$MYBB_PATH.$row["avatar"],Config::$FLARUM_AVATAR_PATH.$avatar))
                            $flarum_db->query("UPDATE ".Config::$FLARUM_PREFIX."users SET avatar_path = '$avatar' WHERE id = {$row["uid"]}");
                    }
                    else
                        echo "Warning: avatar of user id {$row["uid"]} doesn't exist in the mybb avatar path<br />";
                }
            }
        }
    }
    echo "Done: migrated ".$users->num_rows." users.</p>";
    echo "<p>Migrating categories to tags and forums to sub-tags ...<br />";

    //categories
    $categories = $mybb_db->query("SELECT fid, name, description FROM ".Config::$MYBB_PREFIX."forums WHERE type = 'c'");
    if($categories->num_rows > 0)
    {
        $flarum_db->query("TRUNCATE TABLE ".Config::$FLARUM_PREFIX."tags");
        $c_pos = 0;

        while($crow = $categories->fetch_assoc())
        {
            $color = rand_color();
            $result = $flarum_db->query("INSERT INTO ".Config::$FLARUM_PREFIX."tags (id, name, slug, description, color, position) VALUES ({$crow["fid"]},'{$crow["name"]}', '".to_slug($crow["name"])."', '{$crow["description"]}',  '$color', $c_pos)");
            if($result === false) die("Error executing query: ".$flarum_db->error);
            $parent_tags[$crow["fid"]] = 0;

            //forums
            $forums = $mybb_db->query("SELECT fid, name, description, linkto FROM ".Config::$MYBB_PREFIX."forums WHERE type = 'f' AND pid = {$crow["fid"]}");
            if($forums->num_rows === 0) continue;

            $f_pos = 0;
            while($srow = $forums->fetch_assoc())
            {
                if(!empty($srow["linkto"])) continue;

                $result = $flarum_db->query("INSERT INTO " . Config::$FLARUM_PREFIX . "tags (id, name, slug, description, parent_id, color, position) VALUES ({$srow["fid"]},'{$srow["name"]}', '" . to_slug($srow["name"], true) . "', '{$flarum_db->real_escape_string($srow["description"])}', {$crow["fid"]}, '$color', $f_pos)");
                if($result === false) die("Error executing query: ".$flarum_db->error."(".$flarum_db->errno.")");
                $parent_tags[$srow["fid"]] = $crow["fid"];

                $f_pos++;

                //subforums as secundary tags
                $subforums = $mybb_db->query("SELECT * FROM " . Config::$MYBB_PREFIX . "forums WHERE type = 'f' AND pid = {$srow["fid"]}");
                if ($subforums->num_rows === 0) continue;

                while ($subrow = $subforums->fetch_assoc())
                    $flarum_db->query("INSERT INTO ".Config::$FLARUM_PREFIX."tags (id, name, slug, description, color, is_hidden) VALUES ({$subrow["fid"]}, '{$subrow["name"]}', '".to_slug($subrow["name"], true)."', '{$subrow["description"]}', '$color', 1)");
            }
            $c_pos++;
        }
    }
    echo "Done: migrated ".$categories->num_rows." categories and their forums";

    echo "<p>Migrating threads and thread posts...<br />";

    $threads = $mybb_db->query("SELECT tid, fid, subject, replies, FROM_UNIXTIME(dateline) as dateline, uid, firstpost, FROM_UNIXTIME(lastpost) as lastpost, lastposteruid, closed, sticky, visible FROM ".Config::$MYBB_PREFIX."threads");
    if($threads->num_rows > 0)
    {
        $flarum_db->query("TRUNCATE TABLE ".Config::$FLARUM_PREFIX."discussions");
        $flarum_db->query("TRUNCATE TABLE ".Config::$FLARUM_PREFIX."discussions_tags");
        $flarum_db->query("TRUNCATE TABLE ".Config::$FLARUM_PREFIX."posts");

        while($trow = $threads->fetch_assoc())
        {
            if(Config::$MYBB_SKIP_SOFTDELETED)
                if($trow["visible"] == -1) continue;

            $participants = array();
            $result = $flarum_db->query("INSERT INTO ".Config::$FLARUM_PREFIX."discussions (id, title, start_time, start_user_id, start_post_id, last_time, last_user_id, slug, is_approved, is_locked, is_sticky)
            VALUES ({$trow["tid"]}, '{$flarum_db->real_escape_string($trow["subject"])}', '{$trow["dateline"]}', {$trow["uid"]}, {$trow["firstpost"]}, '{$trow["lastpost"]}', {$trow["lastposteruid"]}, '".to_slug($trow["subject"])."', 1, ".(empty($trow["closed"]) ? "0" : $trow["closed"]).", {$trow["sticky"]})");

            if($result === false) die("Error executing query: ".$flarum_db->error);

            $flarum_db->query("INSERT INTO ".Config::$FLARUM_PREFIX."discussions_tags (discussion_id, tag_id) VALUES ({$trow["tid"]}, {$trow["fid"]})");
            if (array_key_exists($trow["fid"], $parent_tags))
        	    $flarum_db->query("INSERT INTO ".Config::$FLARUM_PREFIX."discussions_tags (discussion_id, tag_id) VALUES ({$trow["tid"]}, {$parent_tags[$trow["fid"]]})");

            $posts = $mybb_db->query("SELECT pid, tid, FROM_UNIXTIME(dateline) as dateline, uid, message FROM ".Config::$MYBB_PREFIX."posts WHERE tid = {$trow["tid"]} ORDER BY pid");
            $lastpost = null;
            if($posts->num_rows === 0) continue;

            $lastpostnumber = 0;
            while($row = $posts->fetch_assoc())
            {
        	$lastpostnumber++;
        	
                if(!in_array($row["uid"], $participants)) $participants[] = (int)$row["uid"];
                $content = $flarum_db->real_escape_string($parser->parse($row["message"]));

                $result = $flarum_db->query("INSERT INTO ".Config::$FLARUM_PREFIX."posts (id, discussion_id, time, user_id, type, content, is_approved, number) VALUES ({$row["pid"]}, {$trow["tid"]}, '{$row["dateline"]}', {$row["uid"]}, 'comment', '$content', 1, $lastpostnumber)");
                if($result === false)  die("Error executing query: ".$flarum_db->error);

                $lastpost = (int)$row["pid"];
            }
            $flarum_db->query("UPDATE ".Config::$FLARUM_PREFIX."discussions SET comments_count = ". $posts->num_rows .", participants_count = ". count($participants) . ", last_post_id = $lastpost, last_post_number = $lastpostnumber WHERE id = {$trow["tid"]}");
        }
    }
    echo "Done: migrated ".$threads->num_rows." threads with their posts";

    echo "<p>Migrating custom user groups...<br />";

    $groups = $mybb_db->query("SELECT * FROM ".Config::$MYBB_PREFIX."usergroups WHERE type = 2");
    if($groups->num_rows > 0)
    {
        $flarum_db->query("DELETE FROM ".Config::$FLARUM_PREFIX."groups WHERE id > 4");

        while ($row = $groups->fetch_assoc())
        {
            $result = $flarum_db->query("INSERT INTO ".Config::$FLARUM_PREFIX."groups (id, name_singular, name_plural, color) VALUES ({$row["gid"]}, '{$row["title"]}', '{$row["title"]}', '".rand_color()."')");
            if ($result === false) die("Error executing query: ".$flarum_db->error);
        }
    }
    echo "Done: migrated ".$groups->num_rows." custom groups.</p>";
?>