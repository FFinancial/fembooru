<?php declare(strict_types=1);

class ReportImageTheme extends Themelet
{
    public function display_reported_images(Page $page, array $reports)
    {
        global $config, $user;

        $h_reportedimages = "";
        foreach ($reports as $report) {
            $image = $report['image'];
            $h_reason = format_text($report['reason']);
            $image_link = $this->build_thumb_html($image);

            $reporter_name = html_escape($report['reporter_name']);
            $userlink = "<a href='".make_link("user/$reporter_name")."'>$reporter_name</a>";

            $iabbe = new ImageAdminBlockBuildingEvent($image, $user);
            send_event($iabbe);
            ksort($iabbe->parts);
            $actions = join("<br>", $iabbe->parts);

            $h_reportedimages .= "
				<tr>
					<td>{$image_link}</td>
					<td>Report by $userlink: $h_reason</td>
					<td class='formstretch'>
						".make_form(make_link("image_report/remove"))."
							<input type='hidden' name='id' value='{$report['id']}'>
							<input type='submit' value='Remove Report'>
						</form>

						<br>$actions
					</td>
				</tr>
			";
        }

        $thumb_width = $config->get_int(ImageConfig::THUMB_WIDTH);
        $html = "
			<table id='reportedimage' class='zebra'>
				<thead><td width='$thumb_width'>Image</td><td>Reason</td><td width='128'>Action</td></thead>
				$h_reportedimages
			</table>
		";

        $page->set_title("Reported Images");
        $page->set_heading("Reported Images");
        $page->add_block(new NavBlock());
        $page->add_block(new Block("Reported Images", $html));
    }

    /**
     * #param ImageReport[] $reports
     */
    public function display_image_banner(Image $image, array $reports)
    {
        global $config, $page;

        $i_image = $image->id;
        $html = "";
        $public = $config->get_string("report_image_publicity");
        if ($public != "none" && count($reports) > 0) {
            $html .= "<b>Current reports:</b>";
            foreach ($reports as $report) {
                $html .= "<br>";
                if ($public == "both") {
                    $html .= html_escape(User::by_id($report->user_id)->name);
                    $html .= " - ";
                    $html .= format_text($report->reason);
                } elseif ($public == "user") {
                    $html .= html_escape(User::by_id($report->user_id)->name);
                } elseif ($public == "reason") {
                    $html .= format_text($report->reason);
                }
            }
            $html .= "<p>";
        }
        $html .= "
			".make_form(make_link("image_report/add"))."
				<input type='hidden' name='image_id' value='$i_image'>
				<input type='text' name='reason' placeholder='Please enter a reason'>
				<input type='submit' value='Report'>
			</form>
		";
        $page->add_block(new Block("Report Image", $html, "left"));
    }

    public function get_nuller(User $duser)
    {
        global $user, $page;
        $html = "
			<form action='".make_link("image_report/remove_reports_by")."' method='POST'>
			".$user->get_auth_html()."
			<input type='hidden' name='user_id' value='{$duser->id}'>
			<input type='submit' value='Delete all reports by this user'>
			</form>
		";
        $page->add_block(new Block("Reports", $html, "main", 80));
    }
}
