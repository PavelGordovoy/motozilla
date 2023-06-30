<?php

/*
 * @author Gaponov Igor <gapon2401@gmail.com>
 */

/**
 * Cron job to
 * php cli.php helpdesk proPluginIndexFiles
 * php cli.php helpdesk proPluginIndexFiles --no-report - Without output
 */

class helpdeskProPluginIndexFilesCli extends waCliController
{
    /** @var  array */
    private $result;

    public function execute()
    {
        $runner = new helpdeskProPluginAttachmentsIndexController();
        $this->result = $runner->fastExecute();
    }

    protected function afterExecute()
    {
        if (waRequest::param('no-report') === null) {
            if (!empty($this->result['notice'])) {
                print($this->result['notice'] . "\n");
            }
            if (!empty($this->result['success'])) {
                print($this->result['success']);
            } elseif (!empty($this->result['error'])) {
                print($this->result['error']);
            } elseif (!empty($this->result['warning'])) {
                print($this->result['warning']);
            }
            print "\n";
        }
        parent::afterExecute();
    }
}
