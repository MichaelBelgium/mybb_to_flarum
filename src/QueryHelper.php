<?php

namespace Michaelbelgium\Mybbtoflarum;

use mysqli;

class QueryHelper
{
    protected int $step = 500;
    protected int $offset;

    private mysqli $connection;
    private \mysqli_stmt|false $statement;

    public function __construct(mysqli $connection, string $query, $offset = 0)
    {
        $this->offset = $offset;
        $this->connection = $connection;
        $this->statement = $this->connection->prepare("$query LIMIT ?,?");
        /*$this->statement->attr_set(MYSQLI_STMT_ATTR_CURSOR_TYPE, MYSQLI_CURSOR_TYPE_READ_ONLY);
        $this->statement->attr_set(MYSQLI_STMT_ATTR_PREFETCH_ROWS, $this->step);*/
        $this->statement->bind_param('ii', $this->offset, $this->step);
    }

    public function fetch(): \Generator
    {
        $stmtResult = $this->statement->execute();
        $result = $this->statement->get_result();
        // if there is no rows, it means we are at the end of the table
        while ($stmtResult && $result->num_rows > 0) {
            // we fetch a row batch and yield rows until this batch is exhausted
            while ($row = $result->fetch_object()) {
                yield $row;
            }
            $result->free();
            // update offset and fetch again
            $this->offset += $result->num_rows;
            $stmtResult = $this->statement->execute();
            $result = $this->statement->get_result();
        }

        $this->statement->close();
    }
}
