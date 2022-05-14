<?php

class CustomUploadTheme extends UploadTheme
{
    public function display_block(Page $page)
    {
        $page->add_block(new Block("Upload", $this->build_upload_block(), "head", 20));
        $page->add_block(new Block("Upload", $this->build_upload_block(), "left", 20));
    }

    public function display_full(Page $page)
    {
        $page->add_block(new Block("Upload", "Disk nearly full, uploads disabled", "head", 20));
    }

    public function display_page(Page $page)
    {
        parent::display_page($page);
        $html = "
			<a href='//fembooru.jp/wiki/tagging'>Tagging Guide</a>
		";
        $page->add_block(new Block(null, $html, "main", 19));
    }

    protected function build_upload_block(): string
    {
        $url = make_link("upload");
        $streak = Image::count_upload_streak();
        $streak_comma = number_format($streak);
        $html = "<a href='$url' style='font-size: 2em; display: block;'>Upload</a>";
        if ($streak > 0) {
            $html .= "Current streak: $streak_comma days 🔥";
        }
        return $html;
    }
}
