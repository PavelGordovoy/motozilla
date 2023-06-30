<?php

class mylangBackendController extends waViewController
{
    public function execute()
    {
        $this->setLayout(new mylangBackendLayout());
    }
}
