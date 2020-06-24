<?php

require_once "events.php";

class Download extends Extension
{
    public function get_priority(): int
    {
        // Set near the end to give everything else a chance to process
        return 99;
    }


    public function onImageDownloading(ImageDownloadingEvent $event)
    {
        global $page;

        $page->set_type($event->mime);

        $page->set_mode(PageMode::FILE);

        $page->set_file($event->path, $event->file_modified);

        $event->stop_processing = true;
    }
}