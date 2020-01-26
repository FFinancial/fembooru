<?php declare(strict_types=1);
use function MicroHTML\INPUT;

class ImageIOTheme extends Themelet
{
    /**
     * Display a link to delete an image
     * (Added inline Javascript to confirm the deletion)
     */
    public function get_deleter_html(int $image_id): string
    {
        return (string)SHM_SIMPLE_FORM(
            make_link("image/delete"),
            INPUT(["type"=>'hidden', "name"=>'image_id', "value"=>$image_id]),
            INPUT(["type"=>'submit', "value"=>'Delete', "onclick"=>'return confirm("Delete the image?");']),
        );
    }

    /**
     * Display link to replace the image
     */
    public function get_replace_html(int $image_id): string
    {
        return (string)SHM_SIMPLE_FORM(
            make_link("image/replace"),
            INPUT(["type"=>'hidden', "name"=>'image_id', "value"=>$image_id]),
            INPUT(["type"=>'submit', "value"=>'Replace']),
        );
    }
}
