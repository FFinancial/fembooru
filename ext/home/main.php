<?php declare(strict_types=1);

class Home extends Extension
{
    /** @var HomeTheme */
    protected $theme;

	private $femDimensions = array(
		array(23, 64),
		array(14, 86),
		array(31, 63),
		array(37, 100)
	);

	private $femTags = array(
		"Hatsune_Miku",
		"Monika",
		"Violet_Parr",
		"Keith_Kogane"
	);

    public function onPageRequest(PageRequestEvent $event)
    {
        global $config, $page;
        if ($event->page_matches("home")) {
            $base_href = get_base_href();
            $sitename = $config->get_string(SetupConfig::TITLE);
            $theme_name = $config->get_string(SetupConfig::THEME);

            $body = $this->get_body();

            $this->theme->display_page($page, $sitename, $base_href, $theme_name, $body);
        }
    }

    public function onSetupBuilding(SetupBuildingEvent $event)
    {
        $counters = [];
        foreach (glob("ext/home/counters/*") as $counter_dirname) {
            $name = str_replace("ext/home/counters/", "", $counter_dirname);
            $counters[ucfirst($name)] = $name;
        }

        $sb = new SetupBlock("Home Page");
        $sb->add_longtext_option("home_links", 'Page Links (Use BBCode, leave blank for defaults)');
        $sb->add_longtext_option("home_text", "<br>Page Text:<br>");
        $sb->add_choice_option("home_counter", $counters, "<br>Counter: ");
        $event->panel->add_block($sb);
    }

	private function addCountToBlankImage($charId, $digit)
	{
		$font = realpath('ext/home/vga.ttf');
		$file = "ext/home/counters/femcounter/$charId.png";
		$img = imagecreatefrompng($file);
		$black = imagecolorallocate($img, 0, 0, 0);
		$x = $this->femDimensions[$charId][0];
		$y = $this->femDimensions[$charId][1];
		imagettftext($img, 20, 0, $x, $y + 20, $black, $font, $digit);
		imagetruecolortopalette($img, true, 16);
		imagesavealpha($img, true);
		imagecolortransparent($img, imagecolorat($img, 0, 0));
		ob_start();
		imagegif($img);
		$image_data = ob_get_contents();
		ob_end_clean();
		$data = base64_encode($image_data);
		imagedestroy($img);
		return $data;
	}


    private function get_body()
    {
        // returns just the contents of the body
        global $config;
        $base_href = get_base_href();
        $sitename = $config->get_string(SetupConfig::TITLE);
        $contact_link = contact_link();
        if (is_null($contact_link)) {
            $contact_link = "";
        }
        $counter_dir = $config->get_string('home_counter', 'default');

        $total = Image::count_images();
        $streak = Image::count_upload_streak();
        $strtotal = "$total";
        $num_comma = number_format($total);
        $streak_comma = number_format($streak);

        $counter_text = "";
        $length = strlen($strtotal);
        for ($n=0; $n<$length; $n++) {
            $cur = $strtotal[$n];
			if ($counter_dir === 'femcounter') {
				$charId = $n % 4;
				$base64url = $this->addCountToBlankImage($charId, $cur);
				$tag = $this->femTags[$charId];
				$counter_text .= " <a href='$base_href/post/list/$tag/1'><img alt='$cur' title='$tag' src='data:image/gif;base64,$base64url' /></a>  ";
            } else {
				$counter_text .= " <img alt='$cur' src='$base_href/ext/home/counters/$counter_dir/$cur.gif' />  ";
			}
        }

        // get the homelinks and process them
        if (strlen($config->get_string('home_links', '')) > 0) {
            $main_links = $config->get_string('home_links');
        } else {
            $main_links = '[url=site://post/list]Posts[/url] [url=site://comment/list]Comments[/url] [url=site://tags]Tags[/url]';
            if (class_exists("Pools")) {
                $main_links .= ' [url=site://pool]Pools[/url]';
            }
            if (class_exists("Wiki")) {
                $main_links .= ' [url=site://wiki]Wiki[/url]';
            }
            $main_links .= ' [url=site://ext_doc]Documentation[/url]';
        }
        $main_links = format_text($main_links);
        $main_text = $config->get_string('home_text', '');

        return $this->theme->build_body($sitename, $main_links, $main_text, $contact_link, $num_comma, $counter_text, $streak_comma);
    }
}
