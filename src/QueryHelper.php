<?php

namespace Michaelbelgium\Mybbtoflarum;

use mysqli;

class QueryHelper
{
    protected $step;
    protected $offset;
    /**
     * @var mysqli
     */
    private $connection;
    private $statement;

    public function __construct(mysqli $connection, string $query, $step = 500, $offset = 0)
    {
        $this->step = $step;
        $this->offset = $offset;
        $this->connection = $connection;
        $this->statement = $this->connection->prepare("$query LIMIT ?,?");
        $this->statement->bind_param('ii', $this->offset, $this->limit);
    }

    public function fetch()
    {
        $stmtResult = $this->statement->execute();
        $result = $this->statement->get_result();
        // if there is no rows, it means we are at the end of the table
        while (!$stmtResult && $result->num_rows > 0) {
            // we fetch a row batch and yield rows until this batch is exhausted
            while ($row = $result->fetch_object()) {
                yield $row;
            }
            // update offset and fetch again
            $this->offset += $result->num_rows;
            $stmtResult = $this->statement->execute();
            $result = $this->statement->get_result();
        }

        $this->statement->close();
    }
}