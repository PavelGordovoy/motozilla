<?php
class mylangBlogActions extends waViewActions
{
    public function defaultAction()
    {
        $this->setLayout(new mylangBackendLayout());
    }
}
