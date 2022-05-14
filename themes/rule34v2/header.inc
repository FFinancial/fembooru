<table class="headbox">
	<tr>
		<td colspan="4" id="big-logo">
			<a class="vis-desktop" href="//fembooru.jp/post/list"><img alt="logo" src="//fembooru.jp/themes/rule34v2/Fembooru.gif" style="width: 240px; margin:15px 0; image-rendering:crisp-edges;"/></a>
		</td>
	</tr>

	<tr>
		<form action='/post/list' method='GET' id="barbot">
			<td id="nav-toggle">
				<a style="font-size: 2em;" onclick="toggleNav();">&nbsp;Sidebar&nbsp;</a>
			</td>
			<td id="mini-logo">
				<a class="vis-mobile" href="//fembooru.jp/post/list"><img alt="logo" src="//fembooru.jp/themes/rule34v2/Fembooru.gif" style="height: 34px; margin:10px 0; image-rendering:crisp-edges;"/></a>
			</td>
			<td>
				<input name='search' size="45" type='text' placeholder='Search' autocomplete='off' class='autocomplete_tags' value="<?=$query;?>"/>
			</td>
			<td width="100">
				<input type='submit' value='Find' id="submit" style="border: 1px solid #888; height: 34px; border-radius: 2px; background: #EEE;"/>
			</td>
		</form>
	</tr>

	<tr>
		<td colspan="4">
<div id="menuh-container">
<div id="menuh">

	<ul>
		<li><a href="//fembooru.jp/post/list" class="top_parent">Main &#9660;</a>
		<ul>
			<li><a href="//fembooru.jp/post/list" class="sub_option">Home page</a></li>
			<li><a href="//fembooru.jp/comment/list" class="sub_option">Comments</a></li>
			<li><a href="//fembooru.jp/tags" class="sub_option">Tags</a></li>
			<li><a href="//fembooru.jp/upload" class="sub_option">Upload</a></li>
		</ul>
		</li>
	</ul>

	<ul>
		<li><a href="//fembooru.jp/post/list" class="top_parent">Help &#9660;</a>
		<ul>
			<li><a href="//fembooru.jp/wiki/rules" class="sub_option">Site rules</a></li>
            <li><a href="//fembooru.jp/wiki/faq" class="sub_option">F.A.Q.</a></li>
			<li><a href="//fembooru.jp/wiki/tagging" class="sub_option">Tagging Guide</a></li>
			<li><a href="//fembooru.jp/wiki/staff" class="sub_option">Staff</a></li>
			<li><a href="#" class="parent">Contact</a>
			<ul>
				<li><a href="mailto:staff@fembooru.jp" class="sub_option">Staff</a></li>
				<li><a href="mailto:webmaster@fembooru.jp" class="sub_option">Programmer</a></li>
			</ul>
			</li>
		</ul>
		</li>
	</ul>

	<ul>
		<li><a class="menu top_parent" href="//fembooru.jp/wiki/Notes">ANNOUNCEMENTS</a></li>
	</ul>

</div>
</div>

		</td>
	</tr>
</table>
