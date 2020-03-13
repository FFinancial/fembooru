<?php
/**
 * Shimmie Installer
 *
 * @package    Shimmie
 * @copyright  Copyright (c) 2007-2015, Shish et al.
 * @author     Shish [webmaster at shishnet.org], jgen [jeffgenovy at gmail.com]
 * @link       http://code.shishnet.org/shimmie2/
 * @license    http://opensource.org/licenses/gpl-2.0.php GNU General Public License v2
 *
 * Initialise the database, check that folder
 * permissions are set properly.
 *
 * This file should be independent of the database
 * and other such things that aren't ready yet
 */

function install()
{
    date_default_timezone_set('UTC');
    define("DATABASE_TIMEOUT", 10000);

    if (is_readable("data/config/shimmie.conf.php")) {
        exit_with_page(
            "Shimmie is already installed.",
            "data/config/shimmie.conf.php exists, how did you get here?"
        );
    }

    if (!file_exists("vendor/")) {
        exit_with_page("Install Error", "
            <p>Shimmie is unable to find the composer vendor directory.</p>
            <p>Have you followed the composer setup instructions found in the
            <a href=\"https://github.com/shish/shimmie2#installation-development\">README</a>?</p>
            <p>If you are not intending to do any development with Shimmie,
            it is highly recommend you use one of the pre-packaged releases
            found on <a href=\"https://github.com/shish/shimmie2/releases\">Github</a> instead.</p>
        ");
    }

    // Pull in necessary files
    require_once "vendor/autoload.php";
    global $_tracer;
    $_tracer = new EventTracer();

    require_once "core/exceptions.php";
    require_once "core/cacheengine.php";
    require_once "core/dbengine.php";
    require_once "core/database.php";
    require_once "core/util.php";

    $dsn = get_dsn();
    if ($dsn) {
        do_install($dsn);
    } else {
        ask_questions();
    }
}

function get_dsn()
{
    if (file_exists("data/config/auto_install.conf.php")) {
        $dsn = null;
        /** @noinspection PhpIncludeInspection */
        require_once "data/config/auto_install.conf.php";
    } elseif (@$_POST["database_type"] == DatabaseDriver::SQLITE) {
        /** @noinspection PhpUnhandledExceptionInspection */
        $id = bin2hex(random_bytes(5));
        $dsn = "sqlite:data/shimmie.{$id}.sqlite";
    } elseif (isset($_POST['database_type']) && isset($_POST['database_host']) && isset($_POST['database_user']) && isset($_POST['database_name'])) {
        $dsn = "{$_POST['database_type']}:user={$_POST['database_user']};password={$_POST['database_password']};host={$_POST['database_host']};dbname={$_POST['database_name']}";
    } else {
        $dsn = null;
    }
    return $dsn;
}

function do_install($dsn)
{
    try {
        create_dirs();
        create_tables(new Database($dsn));
        write_config($dsn);
    } catch (InstallerException $e) {
        exit_with_page($e->title, $e->body, $e->code);
    }
}

function ask_questions()
{
    $warnings = [];
    $errors = [];

    if (check_gd_version() == 0 && check_im_version() == 0) {
        $errors[] = "
			No thumbnailers could be found - install the imagemagick
			tools (or the PHP-GD library, if imagemagick is unavailable).
		";
    } elseif (check_im_version() == 0) {
        $warnings[] = "
			The 'convert' command (from the imagemagick package)
			could not be found - PHP-GD can be used instead, but
			the size of thumbnails will be limited.
		";
    }

    if (!function_exists('mb_strlen')) {
        $errors[] = "
			The mbstring PHP extension is missing - multibyte languages
			(eg non-english languages) may not work right.
		";
    }

    $drivers = PDO::getAvailableDrivers();
    if (
        !in_array(DatabaseDriver::MYSQL, $drivers) &&
        !in_array(DatabaseDriver::PGSQL, $drivers) &&
        !in_array(DatabaseDriver::SQLITE, $drivers)
    ) {
        $errors[] = "
			No database connection library could be found; shimmie needs
			PDO with either Postgres, MySQL, or SQLite drivers
		";
    }

    $db_m = in_array(DatabaseDriver::MYSQL, $drivers)  ? '<option value="'. DatabaseDriver::MYSQL .'">MySQL</option>' : "";
    $db_p = in_array(DatabaseDriver::PGSQL, $drivers)  ? '<option value="'. DatabaseDriver::PGSQL .'">PostgreSQL</option>' : "";
    $db_s = in_array(DatabaseDriver::SQLITE, $drivers) ? '<option value="'. DatabaseDriver::SQLITE .'">SQLite</option>' : "";

    $warn_msg = $warnings ? "<h3>Warnings</h3>".implode("\n<p>", $warnings) : "";
    $err_msg = $errors ? "<h3>Errors</h3>".implode("\n<p>", $errors) : "";

    exit_with_page(
        "Install Options",
        <<<EOD
    $warn_msg
    $err_msg

    <h3>Database Install</h3>
    <form action="index.php" method="POST">
        <div style="text-align: center;">
            <table class='form'>
                <tr>
                    <th>Type:</th>
                    <td><select name="database_type" id="database_type" onchange="update_qs();">
                        $db_m
                        $db_p
                        $db_s
                    </select></td>
                </tr>
                <tr class="dbconf mysql pgsql">
                    <th>Host:</th>
                    <td><input type="text" name="database_host" size="40" value="localhost"></td>
                </tr>
                <tr class="dbconf mysql pgsql">
                    <th>Username:</th>
                    <td><input type="text" name="database_user" size="40"></td>
                </tr>
                <tr class="dbconf mysql pgsql">
                    <th>Password:</th>
                    <td><input type="password" name="database_password" size="40"></td>
                </tr>
                <tr class="dbconf mysql pgsql">
                    <th>DB&nbsp;Name:</th>
                    <td><input type="text" name="database_name" size="40" value="shimmie"></td>
                </tr>
                <tr><td colspan="2"><input type="submit" value="Go!"></td></tr>
            </table>
        </div>
        <script>
        document.addEventListener('DOMContentLoaded', update_qs);
        function q(n) {
            return document.querySelectorAll(n);
        }
        function update_qs() {
            Array.prototype.forEach.call(q('.dbconf'), function(el, i){
                el.style.display = 'none';
            });
            let seldb = q("#database_type")[0].value || "none";
            Array.prototype.forEach.call(q('.'+seldb), function(el, i){
                el.style.display = null;
            });
        }
        </script>
    </form>

    <h3>Help</h3>
    <p class="dbconf mysql pgsql">
        Please make sure the database you have chosen exists and is empty.<br>
        The username provided must have access to create tables within the database.
    </p>
    <p class="dbconf sqlite">
        For SQLite the database name will be a filename on disk, relative to
        where shimmie was installed.
    </p>
    <p class="dbconf none">
        Drivers can generally be downloaded with your OS package manager;
        for Debian / Ubuntu you want php-pgsql, php-mysql, or php-sqlite.
    </p>
EOD
    );
}


function create_dirs()
{
    $data_exists = file_exists("data") || mkdir("data");
    $data_writable = is_writable("data") || chmod("data", 0755);

    if (!$data_exists || !$data_writable) {
        throw new InstallerException(
            "Directory Permissions Error:",
            "<p>Shimmie needs to have a 'data' folder in its directory, writable by the PHP user.</p>
			<p>If you see this error, if probably means the folder is owned by you, and it needs to be writable by the web server.</p>
			<p>PHP reports that it is currently running as user: ".$_ENV["USER"]." (". $_SERVER["USER"] .")</p>
			<p>Once you have created this folder and / or changed the ownership of the shimmie folder, hit 'refresh' to continue.</p>",
            7
        );
    }
}

function create_tables(Database $db)
{
    try {
        if ($db->count_tables() > 0) {
            throw new InstallerException(
                "Warning: The Database schema is not empty!",
                "<p>Please ensure that the database you are installing Shimmie with is empty before continuing.</p>
				<p>Once you have emptied the database of any tables, please hit 'refresh' to continue.</p>",
                2
            );
        }

        $db->create_table("aliases", "
			oldtag VARCHAR(128) NOT NULL,
			newtag VARCHAR(128) NOT NULL,
			PRIMARY KEY (oldtag)
		");
        $db->execute("CREATE INDEX aliases_newtag_idx ON aliases(newtag)", []);

        $db->create_table("config", "
			name VARCHAR(128) NOT NULL,
			value TEXT,
			PRIMARY KEY (name)
		");
        $db->create_table("users", "
			id SCORE_AIPK,
			name VARCHAR(32) UNIQUE NOT NULL,
			pass VARCHAR(250),
			joindate TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			class VARCHAR(32) NOT NULL DEFAULT 'user',
			email VARCHAR(128)
		");
        $db->execute("CREATE INDEX users_name_idx ON users(name)", []);

        $db->execute("INSERT INTO users(name, pass, joindate, class) VALUES(:name, :pass, now(), :class)", ["name" => 'Anonymous', "pass" => null, "class" => 'anonymous']);
        $db->execute("INSERT INTO config(name, value) VALUES(:name, :value)", ["name" => 'anon_id', "value" => $db->get_last_insert_id('users_id_seq')]);

        if (check_im_version() > 0) {
            $db->execute("INSERT INTO config(name, value) VALUES(:name, :value)", ["name" => 'thumb_engine', "value" => 'convert']);
        }

        $db->create_table("images", "
			id SCORE_AIPK,
			owner_id INTEGER NOT NULL,
			owner_ip SCORE_INET NOT NULL,
			filename VARCHAR(64) NOT NULL,
			filesize INTEGER NOT NULL,
			hash CHAR(32) UNIQUE NOT NULL,
			ext CHAR(4) NOT NULL,
			source VARCHAR(255),
			width INTEGER NOT NULL,
			height INTEGER NOT NULL,
			posted TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
			locked SCORE_BOOL NOT NULL DEFAULT SCORE_BOOL_N,
			FOREIGN KEY (owner_id) REFERENCES users(id) ON DELETE RESTRICT
		");
        $db->execute("CREATE INDEX images_owner_id_idx ON images(owner_id)", []);
        $db->execute("CREATE INDEX images_width_idx ON images(width)", []);
        $db->execute("CREATE INDEX images_height_idx ON images(height)", []);
        $db->execute("CREATE INDEX images_hash_idx ON images(hash)", []);

        $db->create_table("tags", "
			id SCORE_AIPK,
			tag VARCHAR(64) UNIQUE NOT NULL,
			count INTEGER NOT NULL DEFAULT 0
		");
        $db->execute("CREATE INDEX tags_tag_idx ON tags(tag)", []);

        $db->create_table("image_tags", "
			image_id INTEGER NOT NULL,
			tag_id INTEGER NOT NULL,
			UNIQUE(image_id, tag_id),
			FOREIGN KEY (image_id) REFERENCES images(id) ON DELETE CASCADE,
			FOREIGN KEY (tag_id) REFERENCES tags(id) ON DELETE CASCADE
		");
        $db->execute("CREATE INDEX images_tags_image_id_idx ON image_tags(image_id)", []);
        $db->execute("CREATE INDEX images_tags_tag_id_idx ON image_tags(tag_id)", []);

        $db->execute("INSERT INTO config(name, value) VALUES('db_version', 11)");
        $db->commit();
    } catch (PDOException $e) {
        throw new InstallerException(
            "PDO Error:",
            "<p>An error occurred while trying to create the database tables necessary for Shimmie.</p>
		    <p>Please check and ensure that the database configuration options are all correct.</p>
		    <p>{$e->getMessage()}</p>",
            3
        );
    }
}

function write_config($dsn)
{
    $file_content = "<" . "?php\ndefine('DATABASE_DSN', '$dsn');\n";

    if (!file_exists("data/config")) {
        mkdir("data/config", 0755, true);
    }

    if (file_put_contents("data/config/shimmie.conf.php", $file_content, LOCK_EX)) {
        header("Location: index.php");
        exit_with_page(
            "Installation Successful",
            "<p>If you aren't redirected, <a href=\"index.php\">click here to Continue</a>."
        );
    } else {
        $h_file_content = htmlentities($file_content);
        throw new InstallerException(
            "File Permissions Error:",
            "The web server isn't allowed to write to the config file; please copy
			the text below, save it as 'data/config/shimmie.conf.php', and upload it into the shimmie
			folder manually. Make sure that when you save it, there is no whitespace
			before the \"&lt;?php\".

			<p><textarea cols='80' rows='2'>$h_file_content</textarea>

			<p>Once done, <a href='index.php'>click here to Continue</a>.",
            0
        );
    }
}

function exit_with_page($title, $body, $code=0)
{
    print("<!DOCTYPE html>
<html lang='en'>
	<head>
		<title>Shimmie Installer</title>
		<link rel=\"shortcut icon\" href=\"ext/static_files/static/favicon.ico\">
		<link rel=\"stylesheet\" href=\"ext/static_files/style.css\" type=\"text/css\">
	</head>
	<body>
		<div id=\"installer\">
		    <h1>Shimmie Installer</h1>
		    <h3>$title</h3>
			<div class=\"container\">
			    $body
			</div>
		</div>
    </body>
</html>");
    exit($code);
}