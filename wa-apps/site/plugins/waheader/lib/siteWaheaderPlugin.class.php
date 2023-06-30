<?php

class siteWaheaderPlugin extends sitePlugin
{

    public function backendHeader()
    {
        $enabled = $this->getSettings('ENABLED');
        if($enabled != true) {
            return true;
        }

        $url = $this->getPluginStaticUrl();
        $type_event = $this->getSettings('TYPE_EVENT');

        $assets = array(
            "<script>var waheader_type_event = '{$type_event}';</script>",
            "<script src=\"{$url}js/waheader.js?v={$this->getVersion()}\" type=\"text/javascript\"></script>",
            "<link href=\"{$url}css/waheader.css?v={$this->getVersion()}\" rel=\"stylesheet\" />",
        );

        return array(
            'header_top' => implode("\n", $assets),
            'header_bottom' => $this->getHtml()
        );
    }

    protected function getHtml()
    {
        $position_class = $this->getSettings('POSITION_CLASS');

        return <<<HTML
<div class="waheader-stiky-ad waheader-stiky-ad_closed" touch-action="none" style="height: 100px; top: -100px; animation: waheader-move-out 0.5s ease 0s 1 normal forwards running;">
    <div id="waheader-1" class="waheader-stiky-ad__slot" style="height:83px;">
        
    </div>
    <div class="waheader-sticky-ad__swipe-area {$position_class}">
        <div class="waheader-stiky-ad__control">
            <div class="waheader-stiky-ad__arrow waheader-stiky-ad__arrow_left"></div>
            <div class="waheader-stiky-ad__arrow waheader-stiky-ad__arrow_right"></div>
        </div>
    </div>
</div>
HTML;

    }
}
