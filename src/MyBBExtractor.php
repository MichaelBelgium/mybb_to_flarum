<?php

namespace Michaelbelgium\Mybbtoflarum;

/**
 * Migrator class
 *
 * Connects to a mybb forum and extract different elements
 * Tested with MyBB v1.8.24
 */
class MyBBExtractor
{
    /**
     * @var \mysqli
     */
    private $connection;
    private $db_prefix;
    private $mybb_path;

    public function __construct(string $host, string $user, string $password, string $db, string $prefix, string $mybbPath = '')
    {
        $this->connection = new \mysqli($host, $user, $password, $db);
        $this->connection->set_charset('utf8');
        $this->db_prefix = $prefix;
        $this->mybb_path = $mybbPath;
    }

    public function __destruct()
    {
        if (!is_null($this->getMybbConnection()))
            $this->getMybbConnection()->close();
    }

    protected function getMybbConnection()
    {
        return $this->connection;
    }


    public function escapeString(string $source): string
    {
        return $this->connection->escape_string($source);
    }

    public function getGroups()
    {
        return $this->getMybbConnection()->query("SELECT * FROM {$this->db_prefix}usergroups WHERE type = 2");
    }

    public function getUsers()
    {
        return $this->getMybbConnection()->query(
            "SELECT uid, username, email, postnum, threadnum, FROM_UNIXTIME( regdate ) AS regdate, FROM_UNIXTIME( lastvisit ) AS lastvisit, usergroup, additionalgroups, avatar, lastip FROM {$this->getPrefix()}users"
        );
    }

    public function getCategories()
    {
        return $this->getMybbConnection()->query("SELECT fid, name, description, linkto, disporder, pid FROM {$this->getPrefix()}forums order by fid");
    }

    public function getThreads(bool $includeSoftDeletedThreads)
    {
        $query = "SELECT tid, fid, subject, FROM_UNIXTIME(dateline) as dateline, uid, firstpost, FROM_UNIXTIME(lastpost) as lastpost, lastposteruid, closed, sticky, visible FROM {$this->getPrefix()}threads";
        if (!$includeSoftDeletedThreads) {
            $query .= " WHERE visible != -1";
        }

        return $this->getMybbConnection()->query($query);
    }

    public function getPosts(int $id, bool $includeSoftDeletePosts)
    {
        $query = "SELECT pid, tid, FROM_UNIXTIME(dateline) as dateline, uid, message, visible FROM {$this->getPrefix()}posts WHERE tid = {$id}";
        if (!$includeSoftDeletePosts) {
            $query .= " AND visible != -1";
        }
        $query .= " order by pid";

        return $this->getMybbConnection()->query($query);
    }

    private function getPrefix()
    {
        return $this->db_prefix;
    }
}