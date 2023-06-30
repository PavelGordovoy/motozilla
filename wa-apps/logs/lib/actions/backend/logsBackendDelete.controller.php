<?php

class logsBackendDeleteController extends waJsonController
{
    public function execute()
    {
        $path = waRequest::post('path', '', waRequest::TYPE_STRING_TRIM);

        try {
            if (!strlen($path)) {
                throw new logsInvalidDataException();
            }

            if (!$this->getRights('delete_files')) {
                throw new Exception(_w('Insufficient access rights to delete log files or directories.'));
            }

            $full_path = logsHelper::getFullPath($path);
            $available = logsItemFile::check($full_path);

            if (!$available) {
                throw new Exception(sprintf(_w('%s is not available for deletion.'), $path));
            }

            $is_dir = is_dir($full_path);

            if ($is_dir) {
                $dir_files = $this->getDirectoryFiles($path, $full_path);
            }

            $deleted = waFiles::delete($full_path);

            if (!$deleted) {
                throw new Exception(sprintf(_w('Could not delete %s.'), $path));
            }

            if (!$is_dir || $dir_files) {
                (new logsPublishedModel())->deleteByField([
                    'path' => $is_dir ? $dir_files : $path,
                ]);
            }

            if ($is_dir) {
                if (!empty($dir_files)) {
                    foreach ($dir_files as $dir_file) {
                        $this->logAction('file_delete', $dir_file);
                    }
                }
            } else {
                $this->logAction('file_delete', $path);
            }

            $update_total_size = (bool) waRequest::get('update_size', 0, waRequest::TYPE_INT);

            if ($update_total_size && (!$is_dir || !empty($dir_files))) {
                $total_size = logsHelper::getTotalLogsSize();
                $is_large = logsHelper::isLargeSize($total_size);

                if (!$is_large) {
                    logsHelper::hideCountBadge();
                }

                $this->response['total_size'] = logsHelper::formatSize($total_size);
                $this->response['total_size_class'] = $is_large ? 'total-size total-size-large' : 'total-size';
                $this->response['is_large'] = $is_large;
            }
        } catch (Exception $e) {
            $this->errors[] = $e->getMessage();
        }
    }

    private function getDirectoryFiles($dir_path, $full_dir_path)
    {
        $dir_files = logsHelper::listDir($full_dir_path, true);

        if ($dir_files) {
            array_walk($dir_files, function(&$dir_file) use ($dir_path) {
                $dir_file = $dir_path . '/' . logsHelper::normalizePath($dir_file);
            });
        }

        return $dir_files;
    }
}


