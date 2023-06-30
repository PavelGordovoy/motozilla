<?php

/**
 * Class pocketlistsModel
 */
class pocketlistsModel extends waModel
{
    /**
     * pocketlistsModel constructor.
     *
     * @param null $type
     * @param bool $writable
     */
    public function __construct($type = null, $writable = false)
    {
        parent::__construct($type, $writable);

        try {
            $this->exec('set names utf8mb4');
        } catch (Exception $ex) {
            waLog::log('PLEASE UPDATE YOUR MYSQL DATABASE. ' . $ex->getMessage());
        }
    }

    /**
     * @param array $query
     * @param int   $limit
     * @param int   $offset
     * @param bool  $calcFoundRows
     *
     * @return string
     * @todo: вынести в отдельный сервис
     */
    public function buildSqlComponents(array $query, $limit = 0, $offset = 0, $calcFoundRows = false)
    {
        $where = [];

        if (!empty($query['where']['and'])) {
            $where[] = '('.implode(') and (', $query['where']['and']).')';
        }
        if (!empty($query['where']['or'])) {
            $where[] = '('.implode(') or (', $query['where']['or']).')';
        }

        if ($where) {
            $where = ' where ' . implode(' or ', $where);
        }

        $q = sprintf('
            select %s %s
            from %s
            %s
            %s
            %s
            %s
            %s',
            $calcFoundRows ? ' SQL_CALC_FOUND_ROWS ' : '',
            implode(",\n", $query['select']),
            implode(",\n", $query['from']),
            implode("\n", $query['join']),
            $where,
            !empty($query['group by'])
                ? 'group by '.implode(",\n", $query['group by'])
                : '',
            !empty($query['order by'])
                ? 'order by '.implode(",\n", $query['order by'])
                : '',
            $limit || $offset ? sprintf('limit %d, %d', $offset, $limit) : ''
        );

        return $q;
    }

//    public function exec($sql, $params = null)
//    {
//        $args = func_get_args();
//
//        return kmStatistics::getInstance()->addQueryWithTime($sql, function () use ($args) {
//            return parent::exec(...$args);
//        });
//    }
//
//    public function query($sql)
//    {
//        $args = func_get_args();
//
//        return kmStatistics::getInstance()->addQueryWithTime($sql, function () use ($args) {
//            return parent::query(...$args); // TODO: Change the autogenerated stub
//        });
//    }
}