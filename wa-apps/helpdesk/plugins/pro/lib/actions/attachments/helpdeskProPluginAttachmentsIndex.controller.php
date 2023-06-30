<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

class helpdeskProPluginAttachmentsIndexController extends waLongActionController
{
    public static $LIMIT = 5;

    private $model = null;
    private $done = false;

    public function preExecute()
    {
        if (!wa('helpdesk')->getUser()->isAdmin()) {
            throw new waRightsException();
        }
    }

    public function execute()
    {
        try {
            parent::execute();
        } catch (waException $ex) {
            if ($ex->getCode() == '302') {
                echo json_encode(array('warning' => $ex->getMessage()));
            } else {
                echo json_encode(array('error' => $ex->getMessage()));
            }
        }
    }

    private function error($message)
    {
        if (func_num_args() > 1) {
            $args = func_get_args();
            $format = array_shift($args);
            $message = vsprintf($format, $args);
        } elseif (is_array($message)) {
            $message = var_export($message, true);
        }
        waLog::log($message, 'helpdesk/plugins/pro/index.error.log');
    }

    public function fastExecute()
    {
        $result = null;
        try {
            ob_start();
            $this->init();
            $this->restore();
            $is_done = $this->isDone();
            while (!$is_done) {
                $this->step();
                $is_done = $this->isDone();
            }
            $_POST['cleanup'] = true;
            $this->finish(null);

            $out = ob_get_clean();
            $this->cliReport();

            if ($out) {
                $this->error(_wp("Error occurred: %s"), $out);
                $result['notice'] = 'See error log for details';
            }
            $result = array(
                'success' => $this->cliReport(true),
            );
        } catch (waException $ex) {
            if ($ex->getCode() == '302') {
                $result = array(
                    'warning' => $ex->getMessage(),
                );
            } else {
                $result = array(
                    'error' => $ex->getMessage(),
                );
            }
        }
        return $result;
    }

    public function cliReport($finish = false)
    {
        static $total = 0;
        static $interval = 0;

        if (!$finish) {
            $total += $this->data['total_count'];
            if (!empty($this->data['timestamp'])) {
                $interval += (time() - $this->data['timestamp']);
            }
        } else {
            if ($interval) {
                $interval = sprintf(_wp('%02d h %02d min %02d sec'), floor($interval / 3600), floor($interval / 60) % 60, $interval % 60);
            }
            $report = sprintf(_wp("Indexing files.") . "\n" . _wp("Time elapsed:") . "\t%s", $interval);
            $report .= "\n" . _wp('Number of all files') . ": " . $this->data['offset'] . '.';
            return $report;
        }
    }

    protected function finish($filename)
    {
        if ($filename !== null) {
            $this->info();
        }
        if ($this->getRequest()->post('cleanup')) {
            return true;
        }
        return false;
    }

    protected function init()
    {
        $this->data['total_count'] = $this->data['offset'] = 0;
        $this->data['not_found'] = [];
        $this->data['timestamp'] = time();

        $request_params = (new helpdeskRequestParamsModel())->getByField('name', 'attachments', true);
        $log_params = (new helpdeskRequestLogParamsModel())->getByField('name', 'attachments', true);
        $request_attachments = [];
        $log_attachments = [];

        if ($request_params) {
            foreach ($request_params as $param) {
                if (!empty($param['value'])) {
                    $param_attachments = @unserialize($param['value']);
                    if ($param_attachments) {
                        $request_attachments[$param['request_id']] = $param_attachments;
                        $this->data['total_count'] += count($param_attachments);
                    }
                }
            }
            $this->data['request_attachments'] = $request_attachments;
        }
        if ($log_params) {
            foreach ($log_params as $param) {
                if (!empty($param['value'])) {
                    $param_attachments = @unserialize($param['value']);
                    if ($param_attachments) {
                        $log_attachments[$param['request_log_id']] = $param_attachments;
                        $this->data['total_count'] += count($param_attachments);
                    }
                }
            }
            $this->data['log_attachments'] = $log_attachments;
        }
    }

    protected function isDone()
    {
        return ($this->data['offset'] >= $this->data['total_count'] || $this->done);
    }

    protected function restore()
    {
        $this->model = new helpdeskProPluginAttachmentsModel();
    }

    protected function step()
    {
        try {
            $c = $stop = 0;
            if ($this->data['request_attachments']) {
                foreach ($this->data['request_attachments'] as $request_id => $request_attachments) {
                    foreach ($request_attachments as $k => $attachment) {

                        if ($c > self::$LIMIT) {
                            $stop = 1;
                            break 2;
                        }

                        $file = $this->getFilePath($request_id, 0, $attachment['file']);
                        $data = [
                            'request_id' => $request_id,
                            'log_id' => 0,
                            'attach_id' => $k,
                            'file' => $attachment['file'],
                            'name' => $attachment['name'],
                            'attach_datetime' => ifset($attachment, 'datetime', null),
                            'size' => filesize($file)
                        ];
                        if (!$file) {
                            $data['not_found'] = 1;
                        }
                        $this->model->insert($data, 1);
                        unset($this->data['request_attachments'][$request_id][$k]);
                        $this->data['offset']++;
                        $c++;
                    }
                }
            }

            if ($this->data['log_attachments'] && !$stop) {
                $request_ids = (new helpdeskRequestLogModel())->select('id, request_id')->where('id IN (?)', [array_keys($this->data['log_attachments'])])->fetchAll('id', true);
                foreach ($this->data['log_attachments'] as $log_id => $request_attachments) {
                    foreach ($request_attachments as $k => $attachment) {

                        if ($c > self::$LIMIT) {
                            break 2;
                        }

                        if (isset($request_ids[$log_id])) {
                            $file = $this->getFilePath($request_ids[$log_id], $log_id, $attachment['file']);
                            $data = [
                                'request_id' => $request_ids[$log_id],
                                'log_id' => $log_id,
                                'attach_id' => $k,
                                'file' => $attachment['file'],
                                'name' => $attachment['name'],
                                'attach_datetime' => ifset($attachment, 'datetime', null),
                                'size' => filesize($file)
                            ];
                            if (!$file) {
                                $data['not_found'] = 1;
                            }
                            $this->model->insert($data, 1);
                            unset($this->data['log_attachments'][$log_id][$k]);
                            $c++;
                        }
                        $this->data['offset']++;
                    }
                }
            }
        } catch (Exception $ex) {
            sleep(5);
            $this->error($ex->getMessage() . "\n" . $ex->getTraceAsString());
        }
    }

    private function getFilePath($request_id, $log_id, $attach_id)
    {
        // Check if this attachment exists
        if ($log_id) {
            $file = helpdeskRequest::getAttachmentsDir($request_id, $log_id) . '/' . $attach_id;
        } else {
            $file = helpdeskRequest::getAttachmentsDir($request_id) . '/' . $attach_id;
        }
        if (file_exists($file)) {
            return $file;
        }
    }

    protected function info()
    {
        $interval = 0;
        if (!empty($this->data['timestamp'])) {
            $interval = time() - $this->data['timestamp'];
        }
        $response = array(
            'time' => sprintf(_wp('%02d h %02d min %02d sec'), floor($interval / 3600), floor($interval / 60) % 60, $interval % 60),
            'processId' => $this->processId,
            'progress' => 0.0,
            'ready' => $this->isDone(),
            'offset' => $this->data['offset'],
            'total_count' => $this->data['total_count'],
            'error_message' => isset($this->data['error_message']) ? $this->data['error_message'] : "",
        );
        $response['progress'] = $this->data['total_count'] ? ($this->data['offset'] / $this->data['total_count']) * 100 : 100;
        $response['progress'] = sprintf('%0.3f%%', $response['progress']);

        if ($this->getRequest()->post('cleanup')) {
            $response['report'] = $this->report($response);
            $response['finish'] = true;
        }

        echo json_encode($response);
    }

    protected function report($response)
    {
        $report = '<div style="font-size: 18px; margin-top: 40px" class="align-center">'
            . '<i class="icon16 yes" style="vertical-align: middle;"></i>'
            . _wp('Files have been indexed successfully!')
            . '</div>'
            . '<div class="margin-block top align-center">'
            . '(' . _wp('Number of processed files') . ': ' . $this->data['offset'] . ', ' . _wp('time elapsed') . ': ' . $response['time'] . ')'
            . '</div>';
        return $report;
    }

}
