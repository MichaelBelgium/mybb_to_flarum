    <?php
    include "../vendor/autoload.php";
    require "migration_config.php";
    
    set_time_limit(0);

    $flarum_db = new mysqli(Config::FLARUM_SERVER, Config::FLARUM_USER, Config::FLARUM_PASSWORD, Config::FLARUM_DB);
    if($flarum_db->connect_errno)   die("Flarum db connection failed: ". $flarum_db->connect_error);

    $mybb_db = new mysqli(Config::MYBB_SERVER, Config::MYBB_USER, Config::MYBB_PASSWORD, Config::MYBB_DB);
    if($mybb_db->connect_errno)     die("MyBB db connection failed: ". $mybb_db->connect_error);
    
    $mybb_db->query("SET CHARSET 'utf8'");
    $flarum_db->query("SET CHARSET 'utf8'");

    $parent_tags = array();
    $user_ips = array();
    $extension_installed = false;

    $result = $flarum_db->query("SELECT 1 FROM ".Config::FLARUM_PREFIX."recipients LIMIT 1");
    if($result !== false) $extension_installed = true;

    echo "<p>Migrating users ...<br />";

    $flarum_db->query("SET FOREIGN_KEY_CHECKS = 0");
    
    $users = $mybb_db->query("SELECT uid, username, email, postnum, threadnum, FROM_UNIXTIME( regdate ) AS regdate, FROM_UNIXTIME( lastvisit ) AS lastvisit, usergroup, additionalgroups, avatar, lastip FROM ".Config::MYBB_PREFIX."users");

    if($users->num_rows > 0)
    {
        $flarum_db->query("TRUNCATE TABLE ".Config::FLARUM_PREFIX."users");
        $flarum_db->query("TRUNCATE TABLE ".Config::FLARUM_PREFIX."group_user");

        while($row = $users->fetch_assoc())
        {
            $password = password_hash(time(),PASSWORD_BCRYPT );
            $result = $flarum_db->query("INSERT INTO ".Config::FLARUM_PREFIX."users (id, username, email, is_email_confirmed, password, joined_at, last_seen_at, discussion_count, comment_count) VALUES ({$row["uid"]},'{$flarum_db->escape_string($row["username"])}', '{$row["email"]}', 1, '$password', '{$row["regdate"]}', '{$row["lastvisit"]}', {$row["threadnum"]}, {$row["postnum"]})");
            if($result === false) die("Error executing query (at uid {$row["uid"]}, saving as flarum user): ". $flarum_db->error);

            $usergroup = (int)$row["usergroup"];
            $othergroups = explode(",", $row["additionalgroups"]);
            $user_ips[(int)$row["uid"]] = inet_ntop($row["lastip"]);

            if($usergroup > 7)
                $flarum_db->query( "INSERT INTO ".Config::FLARUM_PREFIX."group_user (user_id, group_id) VALUES ({$row["uid"]}, {$usergroup})");

            foreach($othergroups as $group)
            {
                if((int)$group <= 7) continue;
                $flarum_db->query("INSERT INTO ".Config::FLARUM_PREFIX."group_user (user_id, group_id) VALUES ({$row["uid"]}, $group)");
            }

            if(Config::MIGRATE_AVATARS)
            {
                if(!empty(Config::MYBB_PATH) && !empty($row["avatar"]))
                {
                    $fullpath = Config::MYBB_PATH.explode("?", $row["avatar"])[0];
                    $avatar = basename($fullpath);
                    if(file_exists($fullpath))
                    {
                        if(copy($fullpath,Config::FLARUM_AVATAR_PATH.$avatar))
                            $flarum_db->query("UPDATE ".Config::FLARUM_PREFIX."users SET avatar_url = '$avatar' WHERE id = {$row["uid"]}");
                        else
                            echo "Warning: could not copy avatar of user id {$row["uid"]}";
                    }
                    else
                        echo "Warning: avatar of user id {$row["uid"]} doesn't exist in the mybb avatar path<br />";
                }
            }
        }
    }
    $flarum_db->query("INSERT INTO ".Config::FLARUM_PREFIX."group_user (user_id, group_id) VALUES (1, 1)"); //set first account as admin automaticly
    echo "Done: migrated ".$users->num_rows." users.</p>";

    echo "<p>Migrating categories to tags and forums to sub-tags ...<br />";
    //categories
    $categories = $mybb_db->query("SELECT fid, name, description FROM ".Config::MYBB_PREFIX."forums WHERE type = 'c'");
    if($categories->num_rows > 0)
    {
        $flarum_db->query("TRUNCATE TABLE ".Config::FLARUM_PREFIX."tags");
        $c_pos = 0;

        while($crow = $categories->fetch_assoc())
        {
            $color = rand_color();
            $result = $flarum_db->query("INSERT INTO ".Config::FLARUM_PREFIX."tags (id, name, slug, description, color, position) VALUES ({$crow["fid"]},'{$flarum_db->escape_string($crow["name"])}', '".to_slug($crow["name"])."', '{$flarum_db->escape_string($crow["description"])}',  '$color', $c_pos)");
            if($result === false) die("Error executing query (at fid {$crow["fid"]}, saving category as tag): ".$flarum_db->error);
            $parent_tags[$crow["fid"]] = 0;

            //forums
            $forums = $mybb_db->query("SELECT fid, name, description, linkto FROM ".Config::MYBB_PREFIX."forums WHERE type = 'f' AND pid = {$crow["fid"]}");
            if($forums->num_rows === 0) continue;

            $f_pos = 0;
            while($srow = $forums->fetch_assoc())
            {
                if(!empty($srow["linkto"])) continue;

                $result = $flarum_db->query("INSERT INTO " . Config::FLARUM_PREFIX . "tags (id, name, slug, description, parent_id, color, position) VALUES ({$srow["fid"]},'{$flarum_db->escape_string($srow["name"])}', '" . to_slug($srow["name"], true) . "', '{$flarum_db->escape_string($srow["description"])}', {$crow["fid"]}, '$color', $f_pos)");
                if($result === false) die("Error executing query (at fid {$srow["fid"]}, saving forum as tag): ".$flarum_db->error."(".$flarum_db->errno.")");
                $parent_tags[$srow["fid"]] = $crow["fid"];

                $f_pos++;

                //subforums as secundary tags
                $subforums = $mybb_db->query("SELECT fid, name, description, linkto FROM " . Config::MYBB_PREFIX . "forums WHERE type = 'f' AND pid = {$srow["fid"]}");
                if ($subforums->num_rows === 0) continue;

                while ($subrow = $subforums->fetch_assoc())
                {
                    if(!empty($subrow["linkto"])) continue;

                    $result = $flarum_db->query("INSERT INTO ".Config::FLARUM_PREFIX."tags (id, name, slug, description, parent_id, color, is_hidden) VALUES ({$subrow["fid"]}, '{$flarum_db->escape_string($subrow["name"])}', '".to_slug($subrow["name"], true)."', '{$flarum_db->escape_string($subrow["description"])}', {$srow["fid"]} ,'$color', 1)");
                    if($result === false) die("Error executing query (at fid {$subrow["fid"]}, saving subforum as tag): ".$flarum_db->error."(".$flarum_db->errno.")");
                }
            }
            $c_pos++;
        }
    }
    echo "Done: migrated ".$categories->num_rows." categories and their forums";

    echo "<p>Migrating threads and thread posts...<br />";

    $threads = $mybb_db->query("SELECT tid, fid, subject, FROM_UNIXTIME(dateline) as dateline, uid, firstpost, FROM_UNIXTIME(lastpost) as lastpost, lastposteruid, closed, sticky, visible FROM ".Config::MYBB_PREFIX."threads");
    if($threads->num_rows > 0)
    {    
        $flarum_db->query("TRUNCATE TABLE ".Config::FLARUM_PREFIX."discussions");
        $flarum_db->query("TRUNCATE TABLE ".Config::FLARUM_PREFIX."discussion_tag");
        $flarum_db->query("TRUNCATE TABLE ".Config::FLARUM_PREFIX."posts");

        while($trow = $threads->fetch_assoc())
        {
            if(Config::MYBB_SKIP_TSOFTDELETED && $trow["visible"] == -1) continue;

            $participants = array();
            $result = $flarum_db->query("INSERT INTO ".Config::FLARUM_PREFIX."discussions (id, title, created_at, user_id, first_post_id, last_posted_at, last_posted_user_id, slug, is_approved, is_locked, is_sticky)
            VALUES ({$trow["tid"]}, '{$flarum_db->escape_string($trow["subject"])}', '{$trow["dateline"]}', {$trow["uid"]}, {$trow["firstpost"]}, '{$trow["lastpost"]}', {$trow["lastposteruid"]}, '".to_slug($trow["subject"])."', 1, ".($trow["closed"] == "1" ? "1" : "0").", {$trow["sticky"]})");

            if($result === false) die("Error executing query (at tid {$trow["tid"]}, saving thread as discussion): ".$flarum_db->error);

            $flarum_db->query("INSERT INTO ".Config::FLARUM_PREFIX."discussion_tag (discussion_id, tag_id) VALUES ({$trow["tid"]}, {$trow["fid"]})");
            if (array_key_exists($trow["fid"], $parent_tags))
                $flarum_db->query("INSERT INTO ".Config::FLARUM_PREFIX."discussion_tag (discussion_id, tag_id) VALUES ({$trow["tid"]}, {$parent_tags[$trow["fid"]]})");

            $posts = $mybb_db->query("SELECT pid, tid, FROM_UNIXTIME(dateline) as dateline, uid, message, visible FROM ".Config::MYBB_PREFIX."posts WHERE tid = {$trow["tid"]}");
            $lastpost = null;
            if($posts->num_rows === 0) continue;

            $lastpostnumber = 0;
            while($row = $posts->fetch_assoc())
            {
                if(Config::MYBB_SKIP_PSOFTDELETED)
                    if($row["visible"] == -1 && $row["pid"] != $trow["firstpost"]) continue;
                    
                if(!in_array($row["uid"], $participants)) $participants[] = (int)$row["uid"];
                $lastpostnumber++;

                $content = substr($flarum_db->escape_string($parser->parse($row["message"])), 0, 65535);
                $result = $flarum_db->query("INSERT INTO ".Config::FLARUM_PREFIX."posts (id, discussion_id, created_at, user_id, type, content, is_approved, number, ip_address) VALUES ({$row["pid"]}, {$trow["tid"]}, '{$row["dateline"]}', {$row["uid"]}, 'comment', '$content', 1, $lastpostnumber, '".$user_ips[(int)$row["uid"]]."')");
                if($result === false)  die("Error executing query (at pid {$row["pid"]}, saving as post): ".$flarum_db->error);

                $lastpost = (int)$row["pid"];
            }
            $flarum_db->query("UPDATE ".Config::FLARUM_PREFIX."discussions SET participant_count = ". count($participants) . ", comment_count =  $lastpostnumber, last_post_id = $lastpost, last_post_number = $lastpostnumber WHERE id = {$trow["tid"]}");
        }
    }
    echo "Done: migrated ".$threads->num_rows." threads with their posts";

    echo "<p>Migrating custom usergroups...<br />";

    $groups = $mybb_db->query("SELECT * FROM ".Config::MYBB_PREFIX."usergroups WHERE type = 2");
    if($groups->num_rows > 0)
    {
        $flarum_db->query("DELETE FROM ".Config::FLARUM_PREFIX."groups WHERE id > 4");

        while ($row = $groups->fetch_assoc())
        {
            $result = $flarum_db->query("INSERT INTO ".Config::FLARUM_PREFIX."groups (id, name_singular, name_plural, color) VALUES ({$row["gid"]}, '{$row["title"]}', '{$row["title"]}', '".rand_color()."')");
            if ($result === false) die("Error executing query (at gid {$row["gid"]}, saving usergroup as group): ".$flarum_db->error);
        }
    }
    echo "Done: migrated ".$groups->num_rows." custom groups.</p>";

    if(!$extension_installed)
    {
        $flarum_db->query("SET FOREIGN_KEY_CHECKS = 1");
        exit;
    }

    echo "<p>Migrating private messages...<br />";
    
    $flarum_db->query("TRUNCATE TABLE ". Config::FLARUM_PREFIX ."recipients");

    $pms = $mybb_db->query("SELECT * FROM ".Config::MYBB_PREFIX."privatemessages WHERE folder = 2 AND subject NOT LIKE 'RE: %' AND subject NOT LIKE '%buddy request%' ORDER BY dateline ASC");
    if($pms->num_rows > 0)
    {
        $tag_id = null;
        $checktag = $flarum_db->query("SELECT id FROM ".Config::FLARUM_PREFIX."tags WHERE name = '". Config::FLARUM_PM_TAG."'");

        if($checktag->num_rows === 1)
            $tag_id = (int)$checktag->fetch_row()[0];
        else
        {
            $flarum_db->query("INSERT INTO ".Config::FLARUM_PREFIX."tags (name, slug, description, color) VALUES ('".Config::FLARUM_PM_TAG."', '".to_slug(Config::FLARUM_PM_TAG)."', 'Private discussions are listed here', '".rand_color()."')");
            $tag_id = $flarum_db->insert_id;
        }

        while($row = $pms->fetch_assoc())
        {
            $sender = (int)$row["fromid"];
            $receiver = (int)$row["toid"];
            $time = "FROM_UNIXTIME('{$row["dateline"]}')";
            $lastpostnumber = 1;
            $title = $flarum_db->escape_string($row["subject"]);

            $result = $flarum_db->query("INSERT INTO ".Config::FLARUM_PREFIX."discussions (title, participant_count, created_at, user_id, slug) VALUES ('$title', 2, $time, $sender, '".to_slug($row["subject"], true)."')");
            if($result === false) die("Error executing query (at pmid {$row["pmid"]}, saving as private message discussion): ".$flarum_db->error);
            $dID = $flarum_db->insert_id;

            $content = $flarum_db->escape_string($parser->parse($row["message"]));
            $flarum_db->query("INSERT INTO ".Config::FLARUM_PREFIX."posts (discussion_id, created_at, user_id, type, content, is_approved, number) VALUES ($dID, $time, $sender, 'comment', '$content', 1, $lastpostnumber)");
            $startpID = $flarum_db->insert_id;

            $flarum_db->query("INSERT INTO ".Config::FLARUM_PREFIX."discussions_tags (discussion_id, tag_id) VALUES ($dID, $tag_id)");

            $flarum_db->query("INSERT INTO ".Config::FLARUM_PREFIX."recipients (discussion_id, user_id, created_at, updated_at) VALUES ($dID, $sender, $time, $time)");
            $flarum_db->query("INSERT INTO ".Config::FLARUM_PREFIX."recipients (discussion_id, user_id, created_at, updated_at) VALUES ($dID, $receiver, $time, $time)");

            $pmposts = $mybb_db->query("SELECT * FROM ".Config::MYBB_PREFIX."privatemessages WHERE folder = 2 AND subject = 'RE: $title' AND (fromid = $sender OR fromid = $receiver) ORDER BY dateline ASC ");

            if($pmposts->num_rows > 0)
            {
                while($prow = $pmposts->fetch_assoc())
                {
                    $lastpostnumber++;
                    $content = $flarum_db->escape_string($parser->parse($prow["message"]));
                    $ptime = "FROM_UNIXTIME('{$prow["dateline"]}')";
                    $lastID = (int)$prow["fromid"];

                    $result = $flarum_db->query("INSERT INTO ".Config::FLARUM_PREFIX."posts (discussion_id, created_at, user_id, type, content, is_approved, number) VALUES ($dID, $ptime, $lastID, 'comment', '$content', 1, $lastpostnumber)");
                    if($result === false)  die("Error executing query (at pmid {$prow["pmid"]}, saving private message repies as posts): ".$flarum_db->error);
                    $lastpID = $flarum_db->insert_id;
                }
            }

            $flarum_db->query("UPDATE ".Config::FLARUM_PREFIX."discussions SET first_post_id = $startpID, last_posted_at = $ptime, last_posted_user_id = $lastID, last_post_number = $lastpostnumber, last_post_id = $lastpID, comment_count = $lastpostnumber WHERE id = $dID");
        }
    }

    echo "Done: migrated ".$pms->num_rows." private messages</p>";

    $flarum_db->query("SET FOREIGN_KEY_CHECKS = 1");
?>