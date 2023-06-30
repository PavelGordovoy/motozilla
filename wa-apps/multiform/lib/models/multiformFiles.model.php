<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class multiformFilesModel extends waModel
{

    protected $table = 'multiform_files';

    /**
     * Get all files
     * @return array
     */
    public function getFiles()
    {
        $files = array();
        $sql = "SELECT * FROM {$this->table}";
        $result = $this->query($sql);
        if ($result) {
            foreach ($result as $row) {
                $files[$row['record_id']][$row['field_id']][] = $row;
            }
        }
        return $files;
    }

    /**
     * Get file by hash
     *
     * @param string|array $hash
     * @return array
     */
    public function getFileByHash($hash)
    {
        $mrv = new multiformRecordValuesModel();

        $sql = "SELECT f.*, mrv.record_id as record_id, mrv.cloned_index, mrv.field_id  FROM {$this->getTableName()} f 
                LEFT JOIN {$mrv->getTableName()} mrv ON mrv.id=f.record_id WHERE f.hash ";
        if (is_array($hash)) {
            $sql .= "IN (?)";
            $result =$this->query($sql, [$hash])->fetchAll('hash');
        } else {
            $sql .= "= s:hash";
            $result =$this->query($sql, ['hash' => rtrim($hash, '/')])->fetchAssoc();
        }

        return $result;
    }

}