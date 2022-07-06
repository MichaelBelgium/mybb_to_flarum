<?php

namespace Michaelbelgium\Mybbtoflarum;

use mysqli;

/**
 * Migrator class
 *
 * Connects to a mybb forum and extract different elements
 * Tested with MyBB v1.8.24
 */
class MyBBExtractor
{
    /**
     * @var mysqli
     */
    private $connection;
    private $db_prefix;

    public function __construct(string $host, string $user, string $password, string $db, string $prefix)
    {
        $this->connection = new mysqli($host, $user, $password, $db);
        $this->connection->set_charset('utf8');
        $this->db_prefix = $prefix;

    }

    public function __destruct()
    {
        if (!is_null($this->connection))
            $this->connection->close();
    }

    public function getGroups(int $offset = 0): \Generator
    {
        $query = "SELECT gid, title FROM {$this->db_prefix}usergroups WHERE type = 2";
        return $this->getGenerator($query, $offset);
    }

    protected function getGenerator($query, $offset = 0): \Generator
    {
        $helper = new QueryHelper($this->connection, $query, $offset);
        return $helper->fetch();
    }

    public function getUsers(int $offset = 0): \Generator
    {
        $query = "SELECT uid, username, email, postnum, threadnum, FROM_UNIXTIME( regdate ) AS regdate, FROM_UNIXTIME( lastvisit ) AS lastvisit, usergroup, additionalgroups, avatar, lastip FROM {$this->getPrefix()}users";
        return $this->getGenerator($query, $offset);
    }

    protected function getPrefix(): string
    {
        return $this->db_prefix;
    }

    public function getCategories(int $offset = 0): \Generator
    {
        $query = "SELECT fid, name, description, linkto, disporder, pid FROM {$this->getPrefix()}forums order by fid";
        return $this->getGenerator($query, $offset);
    }

    public function getThreads(bool $includeSoftDeletedThreads, int $offset = 0): \Generator
    {
        $query = "SELECT tid, fid, subject, FROM_UNIXTIME(dateline) as dateline, uid, firstpost, FROM_UNIXTIME(lastpost) as lastpost, lastposteruid, closed, sticky, visible FROM {$this->getPrefix()}threads";
        if (!$includeSoftDeletedThreads) {
            $query .= " WHERE visible != -1";
        }
        return $this->getGenerator($query, $offset);
    }

    public function getPosts(int $tid, bool $includeSoftDeletePosts, int $offset = 0): \Generator
    {
        $query = "SELECT pid, tid, FROM_UNIXTIME(dateline) as dateline, uid, message, visible FROM {$this->getPrefix()}posts WHERE tid = {$tid}";
        if (!$includeSoftDeletePosts) {
            $query .= " AND visible != -1";
        }
        $query .= " order by pid";

        return $this->getGenerator($query, $offset);
    }

    public function getAttachments($pid): \Generator {
        $query = "SELECT * FROM {$this->getPrefix()}attachments WHERE pid = {$pid}";
        return $this->getGenerator($query, 0);
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
