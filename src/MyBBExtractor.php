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
    private $step = 500;

    public function __construct(string $host, string $user, string $password, string $db, string $prefix, string $mybbPath = '')
    {
        $this->connection = new \mysqli($host, $user, $password, $db);
        $this->connection->set_charset('utf8');
        $this->db_prefix = $prefix;
        $this->mybb_path = $mybbPath;
    }

    public function __destruct()
    {
        if (!is_null($this->connection))
            $this->connection->close();
    }

    public function getGroups(int $offset = 0)
    {
        $query = "SELECT gid, title FROM {$this->db_prefix}usergroups WHERE type = 2";
        return $this->getGenerator($query, $offset);
    }

    protected function getGenerator($query, $offset = 0)
    {
        $helper = new QueryHelper($this->connection, $query, $this->step, $offset);
        return $helper->fetch();
    }

    public function getUsers(int $offset = 0)
    {
        $query = "SELECT uid, username, email, postnum, threadnum, FROM_UNIXTIME( regdate ) AS regdate, FROM_UNIXTIME( lastvisit ) AS lastvisit, usergroup, additionalgroups, avatar, lastip FROM {$this->getPrefix()}users";
        return $this->getGenerator($query, $offset);
    }

    protected function getPrefix()
    {
        return $this->db_prefix;
    }

    public function getCategories(int $offset = 0)
    {
        $query = "SELECT fid, name, description, linkto, disporder, pid FROM {$this->getPrefix()}forums order by fid";
        return $this->getGenerator($query, $offset);
    }

    public function getThreads(bool $includeSoftDeletedThreads, int $offset = 0)
    {
        $query = "SELECT tid, fid, subject, FROM_UNIXTIME(dateline) as dateline, uid, firstpost, FROM_UNIXTIME(lastpost) as lastpost, lastposteruid, closed, sticky, visible FROM {$this->getPrefix()}threads";
        if (!$includeSoftDeletedThreads) {
            $query .= " WHERE visible != -1";
        }
        return $this->getGenerator($query, $offset);
    }

    public function getPosts(int $tid, bool $includeSoftDeletePosts, int $offset = 0)
    {
        $query = "SELECT pid, tid, FROM_UNIXTIME(dateline) as dateline, uid, message, visible FROM {$this->getPrefix()}posts WHERE tid = {$tid}";
        if (!$includeSoftDeletePosts) {
            $query .= " AND visible != -1";
        }
        $query .= " order by pid";

        return $this->getGenerator($query, $offset);
    }

    /**
     * @return int
     */
    public function getStep(): int
    {
        return $this->step;
    }

    /**
     * @param int $step
     */
    public function setStep(int $step): void
    {
        $this->step = $step;
    }
}