<?php

class QueryBuilder
{

    protected $select = "*";
    protected $from = '';
    protected $limit = '';
    protected $offset = '';
    protected $order = '';

    protected $dataArr = [];
    protected $where = [];
    protected $orwhere = [];

    protected $connection;

    public function __construct()
    {
        $string = 'mysql:host=' . DBHOST . ";dbname=" . DBNAME;
        $this->connection = new PDO($string, DBUSER, DBPASS);
    }

    public function table(string $table): QueryBuilder
    {
        $this->from = $table;
        return $this;
    }

    public function select(string ...$columns): QueryBuilder
    {
        $this->select = " " . implode(',', $columns) . " ";
        return $this;
    }

    public function where(string $column, string $operator, string $value, string $mode = "and"): QueryBuilder
    {

        if ($mode == "and") {
            $this->where[] = " $column $operator :$column";
        } else {
            $this->orwhere[] = " $column $operator :$column";
        }
        $this->dataArr[$column] = $value;
        return $this;
    }

    public function andwhere(string $column, string $operator, string $value): QueryBuilder
    {
        $this->where($column, $operator, $value);
        return $this;
    }

    public function orwhere(string $column, string $operator, string $value): QueryBuilder
    {
        $this->where($column, $operator, $value, "or");
        return $this;
    }

    public function limit(int $count): QueryBuilder
    {
        $this->limit = " LIMIT $count ";
        return $this;
    }

    public function offset(int $count): QueryBuilder
    {
        $this->offset = " OFFSET $count ";
        return $this;
    }

    public function orderBy(string $column, string $type = 'DESC'): QueryBuilder
    {
        $this->order = " ORDER BY $column $type ";
        return $this;
    }

    public function get()
    {
        $sql =  $this->buildQuery();
        return $this->query($sql)->fetch(PDO::FETCH_OBJ);
    }

    public function getAll()
    {
        $sql =  $this->buildQuery();
        return $this->query($sql)->fetchAll(PDO::FETCH_OBJ);
    }

    public function insert(array $data)
    {
        $this->dataArr = array_merge($this->dataArr, $data);

        $sql =  $this->buildQuery('insert', $data);
        $this->query($sql);
        return $this->connection->lastInsertId();
    }

    public function update(array $data)
    {
        $this->dataArr = array_merge($this->dataArr, $data);

        $sql =  $this->buildQuery('update', $data);
        return $this->query($sql)->rowCount();
    }

    public function delete()
    {
        $sql =  $this->buildQuery('delete');
        return $this->query($sql)->rowCount();
    }

    public function raw(string $sql)
    {
        if (str_contains($sql, 'select')) {
            return $this->query($sql)->fetchAll(PDO::FETCH_OBJ);
        } else {
            $this->query($sql);
        }
    }

    private function buildQuery(string $mode = "select", array $data = [])
    {
        switch ($mode) {
            case 'select':
                # code...
                $sql = "SELECT " . $this->select . ' FROM ' . $this->from . " ";
                if (!empty($this->where))
                    $sql .= ' WHERE ' . implode(' AND ', $this->where);

                if (!empty($this->orwhere)) {

                    if (!empty($this->where))
                        $sql .= ' OR ' . implode(' OR ', $this->orwhere);

                    else
                        $sql .= ' WHERE ' . implode(' OR ', $this->orwhere);
                }
                break;

            case 'update':
                # code...
                $sql = "UPDATE " .  $this->from . " SET ";

                foreach ($data as $key => $value) {
                    $sql .= " $key = :$key,";
                }

                $sql = rtrim($sql, ",");

                if (!empty($this->where))
                    $sql .= ' WHERE ' . implode(' AND ', $this->where);

                if (!empty($this->orwhere)) {

                    if (!empty($this->where))
                        $sql .= ' OR ' . implode(' OR ', $this->orwhere);

                    else
                        $sql .= ' WHERE ' . implode(' OR ', $this->orwhere);
                }
                break;

            case 'insert':
                # code...
                $keys = array_keys($data);
                $sql = "INSERT INTO " .  $this->from . ' (' . implode(',', $keys) . ') VALUES (:' . implode(',:', $keys) . ') ';

                break;

            case 'delete':
                # code...
                $sql = "DELETE FROM  " . $this->from . " ";
                if (!empty($this->where))
                    $sql .= ' WHERE ' . implode(' AND ', $this->where);

                if (!empty($this->orwhere)) {

                    if (!empty($this->where))
                        $sql .= ' OR ' . implode(' OR ', $this->orwhere);

                    else
                        $sql .= ' WHERE ' . implode(' OR ', $this->orwhere);
                }
                break;

            default:
                # code...
                break;
        }

        if ($mode != "insert") {

            if (!empty($this->order))
                $sql .= $this->order;

            if (!empty($this->limit))
                $sql .= $this->limit;

            if (!empty($this->offset))
                $sql .= $this->offset;
        }

        return preg_replace("/\s+/", " ", $sql);
    }

    private function query(string $sql)
    {
        $statement = $this->connection->prepare($sql);
        $statement->execute($this->dataArr);
        $this->resetToDefaults();
        return $statement;
    }

    private function resetToDefaults()
    {
        $this->select = "*";
        $this->from = '';
        $this->limit = '';
        $this->offset = '';
        $this->order = '';

        $this->dataArr = [];
        $this->where = [];
        $this->orwhere = [];
    }
}
