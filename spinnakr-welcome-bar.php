<?php
/*
Plugin Name: Spinnakr
Plugin URI: http://wordpress.org/plugins/spinnakr-welcome-bar/
Description: This is a simple plug-in for adding Spinnakr to your Wordpress site.
Author: frick, adamsfallen
Version: 2.7
Author URI: http://spinnakr.com
*/

/* Runs when plugin is activated */
register_activation_hook(__FILE__, 'spinnakr_install');

/* Runs on plugin deactivation*/
register_deactivation_hook( __FILE__, 'spinnakr_remove');

function spinnakr_install() {
    /* Creates new database field */
    add_option('spinnakr_script', '', '', 'yes');
    add_option('spinnakr_pages', '', '', 'yes');
    add_option('spinnakr_not_home', '', '', 'yes');
}

function spinnakr_remove() {
    /* Deletes the database field */
    delete_option('spinnakr_script');
    delete_option('spinnakr_pages');
    delete_option('spinnakr_not_home');
}


// This just echoes the code
function spinnakr_script_output() {
	$script   = get_option('spinnakr_script');
	$not_home = get_option('spinnakr_not_home');
	$pages    = explode(",", get_option('spinnakr_pages'));
    $posts    = ($pages != false && $pages[0] == "posts") ? true : false;

    if ($posts == true) {
        array_shift($pages);
    }

	if ($not_home != "true" && (is_front_page() || is_home())) {
        echo $script;
    } else if ($pages == false) {
        echo $script;
    } else if ($posts == true && is_single()) {
        echo $script;
	} else if (is_page($pages)) {
		echo $script;
	}
}
add_action('wp_footer', 'spinnakr_script_output');

if (is_admin()) {
    // create custom plugin settings menu
    add_action('admin_menu', 'spinnakr_create_menu');
}

function spinnakr_create_menu() {
	//create new top-level menu
	add_menu_page('Spinnakr Settings', 'Spinnakr', 'administrator', __FILE__, 'spinnakr_settings_page', plugins_url('/images/icon.png', __FILE__));

	//call register settings function
	add_action('admin_init', 'register_spinnakr_settings');
}

function register_spinnakr_settings() {
	register_setting('spinnakr-settings-group', 'spinnakr_script');
	register_setting('spinnakr-settings-group', 'spinnakr_pages', 'spinnakr_sanitize');
	register_setting('spinnakr-settings-group', 'spinnakr_not_home');
}

function spinnakr_sanitize($input) {
    if (is_array($input)) {
        $input = implode(',', $input);
    }
    if (is_string($input) != true) {
        $input = '';
    }
    return $input;
}

function spinnakr_settings_page() {
?>
<div class="wrap">
    <img src="<?php echo plugins_url('/images/logo.png', __FILE__); ?>" width="32" height="32" alt="Spinnakr" style="float: left; margin-top: 10px;" />
    <h2>Spinnakr Settings</h2>
    <br>
    <form method="post" action="options.php">
        <?php settings_fields('spinnakr-settings-group'); ?>

        <div>
            <h2>Instructions:</h2>
            <ol>
                <li>If the box below does not already have your Spinnakr code...<br>
                    <br>
                    <ul>
                        <li><b>Go to your <a href="https://spinnakr.com/manage" target="_blank">Spinnakr Dashboard</a></b>. There should be a feed item asking you to Add your Spinnakr Code (<a href="http://cl.ly/image/153X2k3g3t2d" target="_blank">screenshot</a>). </li>
                        <li><b>Copy your code</b> from the feed (you can also add SSL support or remove our "Spinnakr badge" if you don't want our badge on your site using the options below the code if you'd like). 
                        <li><b>Paste the code into the box below</b>.</li>
                    </ul>
                </li>
                <br>
                <li><b>Click "Save Changes" and you're done!</b> You can now go back to your Spinnakr Dashboard and click "I've added it, check again!" to verify the code is installed. And feel free to shoot us an email or chat if you need help.</li>
            </ol>
            <br>
        </div>
        <div>
            <h2>Enter your Spinnakr code:</h2>
            <textarea name="spinnakr_script" id="spinnakr_script" cols=80 rows=5><?php echo htmlspecialchars(get_option('spinnakr_script')); ?></textarea>
            <br><br><br>
        </div>
        <fieldset id="spinnakr_advanced" style="border-top: 1px solid #ccc; padding: 10px 20px 0 0; width: 530px; text-align: right; display: none;">
            <legend id="spinnakr_advanced_title" style="cursor: pointer;">&nbsp;&nbsp;&#9657;&nbsp;&nbsp;Advanced Options&nbsp;&nbsp;</legend>
            <div id="spinnakr_advanced_options" style="text-align: left; display: none;">
                <!--<p style="padding-right:25px;">If you'd like to display the Welcome Bar on posts or pages other than the homepage, enter their IDs separated by commas here (e.g. 12, 5). Otherwise leave blank:</h4><br><br></p>
                <p><input type="text" name="spinnakr_pages" value="<?php echo htmlspecialchars(get_option('spinnakr_pages')); ?>" /></p>-->
                <p style="padding-right: 25px;">Select the pages on which you would like to have Spinnakr appear:<br />Only use this if you wish to <span style="font-weight: bold">not</span> have Spinnakr appear on every page (default)</p>
                <p style="width: 400px; text-align: right; padding: 0; margin: 0;"><a href="#" id="spinnakr_select_all">Select All</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="#" id="spinnakr_deselect_all">Select None</a></p>
                <p style="padding-right: 25px; margin: 0.3em 0 1em 0;">
                    <select id="spinnakr_pages" name="spinnakr_pages[]" multiple="multiple" style="width: 400px;"><?php
                            $selected_pages = explode(',', get_option('spinnakr_pages'));
                            $selected = (in_array('posts', $selected_pages)) ? ' selected="selected"' : '';
                        ?><option value="posts"<?php echo $selected; ?>>All Blog Posts</option>
                        <?php
                            $pages = get_pages();
                            foreach ($pages as $page) {
                                $selected = (in_array($page->ID, $selected_pages)) ? ' selected="selected"' : '';
                                echo '<option value="' . $page->ID . '"' . $selected . '>' . $page->post_title . '</option>';
                            }
                        ?>

                    </select>
                </p>
                <p style="padding-right:25px;">
                    Only display Spinnakr on the specified page(s) above and <span style="font-weight: bold;">not</span> the homepage:
                    <input type="checkbox" name="spinnakr_not_home" value="true" <?php
                        if (get_option('spinnakr_not_home') == "true") {
                            echo 'checked="yes"';
                        }
                    ?>/>
                </p>
            </div>
        </fieldset>
        <p class="submit">
            <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
        </p>
    </form>
</div>
<div id="spinnakr_overlay" style="display: none;"><img src="<?php echo plugins_url('/images/loading.gif', __FILE__); ?>" width="32" height="32" /></div>
<script src="//s5.spn.ee/js/wp_detect.js" type="text/javascript"></script>
<?php
}
