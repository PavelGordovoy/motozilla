<?php

class logsDialogDeleteAction extends waViewAction
{
    public function execute()
    {
        try {
            $path = waRequest::get('path');

            if (!$this->getRights('delete_files')) {
                throw new Exception(_w('You have no permissions to delete files and directories.'));
            }

            $full_path = logsHelper::getFullPath($path);

            if (!logsItemFile::check($full_path)) {
                throw new logsInvalidDataException();
            }

            $is_dir = is_dir($full_path);
            $dialog_title = $is_dir ? _w('Delete directory') : _w('Delete file');
            $item = logsHelper::getPathParts($path);
            $warnings = [];

            if ($is_dir) {
                $dir_files = $this->getDirFiles($path);

                if ($dir_files) {
                    $this->addDirWarningFiles($warnings, $dir_files);
                    $this->addDirWarningFilesPublished($warnings, $dir_files);
                    $this->addDirWarningFilesTracked($warnings, $dir_files);
                }
            } else {
                $this->addFileWarningPublished($warnings, $path);
                $this->addFileWarningTracked($warnings, $path);
            }

            $item['warnings'] = $warnings;

            $this->view->assign('path', $path);
            $this->view->assign('item', $item);
            $this->view->assign('title', $dialog_title);
        } catch (Exception $exception) {
            $this->view->assign('error', $exception->getMessage());
        }
    }

    private function getDirFiles($dir_path)
    {
        $file_paths = logsHelper::listDir(logsHelper::getFullPath($dir_path), true);

        array_walk($file_paths, function(&$file_path) use ($dir_path) {
            $file_path = $dir_path . '/' . logsHelper::normalizePath($file_path);
        });

        return $file_paths;
    }

    private function addDirWarningFiles(&$warnings, $files)
    {
        $warnings[] = _w(
            '%u file contained in this directory will be deleted.',
            '%u files contained in this directory will be deleted.',
            count($files)
        );
    }

    private function addDirWarningFilesPublished(&$warnings, $files)
    {
        $published_files_count = (new logsPublishedModel())->countByField(array(
            'path' => $files,
        ));

        if (!$published_files_count) {
            return;
        }

        $warnings[] = _w(
            '%u file will no longer be available via a published link.',
            '%u files will no longer be available via published links.',
            $published_files_count
        );
    }

    private function addDirWarningFilesTracked(&$warnings, $files)
    {
        $tracked_files_entries = (new logsTrackedModel())
            ->select('path, contact_id')
            ->where('path IN (s:paths)', [
                'paths' => $files,
            ])
            ->fetchAll();


        if (!$tracked_files_entries) {
            return;
        }

        $tracked = array_reduce($tracked_files_entries, function($result, $entry) {
            $result['paths'] = array_merge(
                ifset($result['paths'], []),
                [$entry['path']]
            );

            $result['user_ids'] = array_merge(
                ifset($result['user_ids'], []),
                [$entry['contact_id']]
            );

            return $result;
        }, []);

        if (in_array($this->getUserId(), $tracked['user_ids'])) {
            if (count($tracked['user_ids']) > 1) {
                $warnings[] = _w(
                    'You and other users will no longer be notified on %u file’s updates.',
                    'You and other users will no longer be notified on %u files’ updates.',
                    count($tracked['paths'])
                );
            } else {
                $warnings[] = _w(
                    'You will no longer be notified on %u file’s updates.',
                    'You will no longer be notified on %u files’ updates.',
                    count($tracked['paths'])
                );
            }
        } else {
            $warnings[] = _w(
                'Other users will no longer be notified on %u file’s updates.',
                'Other users will no longer be notified on %u files’ updates.',
                count($tracked['paths'])
            );
        }
    }

    private function addFileWarningPublished(&$warnings, $path)
    {
        $is_published_file = (new logsPublishedModel())->countByField(array(
            'path' => $path,
        )) > 0;

        if ($is_published_file) {
            $warnings[] = _w('This file will no longer be available via a published link.');
        }
    }

    private function addFileWarningTracked(&$warnings, $path)
    {
        $tracked_user_ids = (new logsTrackedModel())
            ->select('contact_id')
            ->where('path = ?', $path)
            ->fetchAll(null, true);

        if ($tracked_user_ids) {
            if (in_array($this->getUserId(), $tracked_user_ids)) {
                if (count($tracked_user_ids) > 1) {
                    $warnings[] = sprintf_wp('You and other users will be further notified on this file’s updates until the “%” option is disabled.', _w('Track changes'));
                } else {
                    $warnings[] = sprintf_wp('You will be further notified on this file’s updates until the “%” option is disabled.', _w('Track changes'));
                }
            } else {
                $warnings[] = sprintf_wp('Other users will be further notified on this file’s updates until the “%” option is disabled.', _w('Track changes'));
            }
        }
    }
}
