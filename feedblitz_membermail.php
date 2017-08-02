<?php /* 
    Plugin Name: FeedBlitz Member Mail
    Plugin URI: http://www.feedblitz.com
    Description: Adds a checkbox to the user registration dialog enabling users to register for both a WordPress site/account and create a <a href="http://www.feedblitz.com">FeedBlitz</a>. An additional checkbox is added to the comment form allowing commentators to subscribe to updates via feedblitz
    Version: 1.0.1
    Author: FeedBlitz, LLC & Andy Bailey
    Author URI: http://www.feedblitz.com
    Copyright (C) <2011>  <FeedBlitz & Andy Bailey>

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
    */

    if (! class_exists ( 'fb_membermail' )) {
        // let class begin
        class fb_membermail {
            //localization domain
            var $plugin_domain = 'fb_membermail_domain';
            var $plugin_url;
            var $plugin_dir;
            var $db_option = 'feedblitz_settings';
            var $version = '1.0.1';
            var $slug = 'fb_membermail-settings';

            /** fb_membermail
            * This is the constructor, it runs as soon as the class is created
            * Use this to set up hooks, filters, menus and language config
            */
            function __construct() {
                global $wp_version, $pagenow;
                // pages where this plugin needs translation
                $local_pages = array ('plugins.php', 'options-general.php' );
                // check if translation needed on current page
                if (in_array ( $pagenow, $local_pages ) || in_array ( $_GET ['page'], $local_pages )) {
                    $this->handle_load_domain ();
                }
                $exit_msg = __ ( 'Feedblitz member mail plugin requires Wordpress 2.9 or newer.', $this->plugin_domain ) . '<a href="http://codex.wordpress.org/Upgrading_Wordpress">' . __ ( 'Please Update!', $this->plugin_domain ) . '</a>';
                // can you dig it?
                if (version_compare ( $wp_version, "2.9", "<" )) {
                    echo ( $exit_msg ); // no diggedy
                }
                // plugin dir and url
                $this->plugin_url = trailingslashit ( WP_PLUGIN_URL . '/' . dirname ( plugin_basename ( __FILE__ ) ) );
                $this->plugin_dir = dirname(__FILE__);
                // hooks 
                if(is_admin()){
                    // filters
                    add_filter ( 'plugin_action_links', array (&$this, 'plugin_action_link' ), - 10, 2 ); // add a settings page link to the plugin description. use 2 for allowed vars
                    add_action ( 'admin_init', array (&$this, 'admin_init' ) ); // to register settings group
                    add_action ( 'admin_menu', array (&$this, 'admin_menu' ) ); // to setup menu link for settings page
                } else {
                    add_action ( 'register_form',array (&$this, 'fb_plugin_form') ); // add checkbox to register form
                    add_action ( 'register_post',array (&$this, 'fb_plugin_post'),10,3 ); // process registration from post  
                    add_action ( 'comment_form',array (&$this,'abfb_comment_form') ); // checkbox for comment form
                    add_action ( 'wp_insert_comment',array (&$this,'abfb_comment_post'),10,2 ); // comment was posted.
                }
            }
            /**
            * PHP4 constructor
            */
            function fb_membermail() {
                $this->__construct();
            }

            /**************************************************************
            * plugin functions
            *************************************************************/

            /**
            * Adds the checkbox to the registration form
            * 
            */
            function fb_plugin_form(){
                $options = $this->get_options();
                if( $options['feedblitz_where'] == 'comment'){
                    return;
                }
                if(trim($options['feedblitz_feedid'])!='')
                {
                    $html = '
                    <div width="100%">    
                    <p>
                    <input type="hidden" name="feedid" id="feedid" value="'.$options['feedblitz_feedid'].'" />
                    <input style="width:25px" type="checkbox" checked name="fbz_checkbox" id="fbz_checkbox" class="input" /><label for="fbz_checkbox">'.$options['feedblitz_text'].'</label>
                    </p>
                    </div>
                    ';
                } else {
                    $html='<div width="100%"><p>'.__('FeedBlitz plugin error. No feed id specified. Integrated newsletter registration is disabled.',$this->plugin_domain).'</p></div>';
                }
                echo $html;
            }
            /**
            * Handles the post of registration form
            * 
            * @param mixed $login
            * @param mixed $email
            * @param mixed $errors
            */
            function fb_plugin_post($login,$email,$errors){
                //debugbreak();
                $options = $this->get_options();
                /* ask feedblitz to send the registrant an activation email to confirm their subscirption (dual opt in).
                When the user clicks the link they will have to complete a captcha to prove they're human */
                $feedid=$options['feedblitz_feedid'];
                if(trim($feedid)=='' || $feedid == '0')
                {
                    $errors->add('newsletter_error',__('<strong>Error : </strong>Newsletter ID not specified. Please log in to the admin user interface and update the FeedBlitz settings'));
                }
                if($_POST['fbz_checkbox']==__('on') && empty($errors))
                {

                    $page='http://www.feedblitz.com/f?newsubscriber='.$email.'&feedid='.$options['feedblitz_feedid'];   
                    $headers = array('user-agent'=>'feedblitz','referer'=>home_url());  // feedblitz page needs the referer set and useragent
                    $result = wp_remote_get($page,array('headers'=>$headers));
                }
            }
            /**
            * adds a client side genereated checkbox to the comment form
            * 
            */
            function abfb_comment_form(){
                $options = $this->get_options();
                if($options['feedblitz_where'] == 'registration'){
                    return;
                }
                if($options['feedblitz_feedid']){
                    //only add if feedid exists
                    echo '<p id="abfb_p" style="clear:both;"></p>';
                    echo '<script type="text/javascript">
                    var abfb_p = document.getElementById("abfb_p");
                    var abfb_cb = document.createElement("input");
                    var abfb_text = document.createTextNode("'.$options['feedblitz_text'].'");
                    abfb_cb.type = "checkbox";
                    abfb_cb.id = "fbz_checkbox";
                    abfb_cb.name = "fbz_checkbox";
                    abfb_cb.style.width = "25px";
                    abfb_p.appendChild(abfb_cb);
                    abfb_p.appendChild(abfb_text);
                    </script>';
                }
            }
            /**
            * runs when comment is posted
            * 
            * @param mixed $id
            * @param mixed $commentdata
            */
            function abfb_comment_post($id,$commentdata){
                $email = $commentdata->comment_author_email;
                $this->fb_plugin_post('',$email,'');
            }


            /**************************************************************
            * admin functions
            *************************************************************/

            /** install
            * This function is called when the plugin activation hook is fired when
            * the plugin is first activated or when it is auto updated via admin.
            * use it to make any changes needed for updated version or to add/check
            * new database tables on first install.
            */
            function install(){
                $options = $this->get_options();
                if(!$options['version'] || version_compare($options['version'],$this->version,'<')){
                    // make any changes to this new versions options if needed and update
                    $options['feedblitz_where'] == 'both';
                    $options['version'] = '1.0.1';
                    update_option($this->db_option,$options);
                }
            }	
            /** handle_load_domain
            * This function loads the localization files required for translations
            * It expects there to be a folder called /lang/ in the plugin directory
            * that has all the .mo files
            */
            function handle_load_domain() {
                // get current language
                $locale = get_locale ();
                // locate translation file
                $mofile = WP_PLUGIN_DIR . '/' . plugin_basename ( dirname ( __FILE__ ) ) . '/lang/' . $this->plugin_domain . '-' . $locale . '.mo';
                // load translation
                load_textdomain ( $this->plugin_domain, $mofile );
            }

            /** get_options
            * This function sets default options and handles a reset to default options
            * return array
            */
            function get_options() {
                // default values
                $options = array ('feedblitz_feedid'=>'','feedblitz_text'=>'Keep me up to date via email','feedblitz_where'=>'both');
                // get saved options unless reset button was pressed
                $saved = '';
                if (! isset ( $_POST ['reset'] )) {
                    $saved = get_option ( $this->db_option );
                }
                // assign values
                if (! empty ( $saved )) {
                    foreach ( $saved as $key => $option ) {
                        $options [$key] = $option;
                    }
                }
                // update the options if necessary
                if ($saved != $options) {
                    update_option ( $this->db_option, $options );
                }
                // return the options
                return $options;
            }

            /** admin_init
            * This function registers the settings group
            * it is called by add_action admin_init
            * options in the options page will need to be named using $this->db_option[option]
            */
            function admin_init(){
                // whitelist options
                register_setting( 'fb_membermail_options_group', $this->db_option ,array(&$this,'options_sanitize' ) );
            }

            /** admin_menu
            * This function adds a link to the settings page to the admin menu
            * see http://codex.wordpress.org/Adding_Administration_Menus
            * it is called by add_action admin_menu
            */
            function admin_menu(){
                //$level = 'manage-options'; // for wpmu sub blog admins
                $level = 'administrator'; // for single blog intalls
                add_options_page ( 'Feedblitz Membermail Settings', 'Feedblitz Membermail', $level, $this->slug, array (&$this, 'options_page' ) );
            }

            /** fb_membermail_action
            * This function adds a link to the settings page for the plugin on the plugins listing page
            * it is called by add filter plugin_action_links
            * @param $links - the links being filtered
            * @param $file - the name of the file
            * return array - the new array of links
            */
            function plugin_action_link($links, $file) {
                $this_plugin = plugin_basename ( __FILE__ );
                $slug = 'fb_membermail-settings';
                if ($file == $this_plugin) {
                    $links [] = "<a href='options-general.php?page={$this->slug}'>" . __ ( 'Settings', $this->plugin_domain ) . "</a>";
                }
                return $links;
            }
            /** options_sanitize
            * This is the callback function for when the settings get saved, use it to sanitize options
            * it is called by the callback setting of register_setting in admin_init
            * @param mixed $options - the options that were POST'ed
            * return mixed $options
            */
            function options_sanitize($options){
                $options['feedblitz_feedid'] = intval(trim($options['feedblitz_feedid']));
                $options['feedblitz_text'] = strip_tags($options['feedblitz_text']);
                // do checks here
                return $options;
            }

            /**************************************************************
            * admin output
            *************************************************************/

            /** options_page
            * This function shows the page for saving options
            * it is called by add_options_page
            * You can echo out or use further functions to display admin style widgets here
            */
            function options_page(){
                $options = $this->get_options();
            ?>
            <div class="wrap">
            <h2>Feedblitz Membermail Settings</h2>
            <form method="post" action="options.php">
                <?php settings_fields( 'fb_membermail_options_group' ); // the name given in admin init
                    // after here, put all the inputs and text fields needed ?>
                <p><?php _e('This plugin makes it easy keep visitors up to date with your FeedBlitz newsletter by integrating newsletter sign up with the end user registration process and by adding a checkbox to the comment form on posts and pages to allow visitors to subscribe when they comment',$this->plugin_domain);?></p>
                <table cellpadding="3" cellspacing="3" class="widefat" style="width: 750px;">
                    <thead>
                        <tr><th colspan="2"><?php _e('Easy Setup Steps',$this->plugin_domain);?></th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td width="20">1)<td><?php printf(__('To get started, %s create a FeedBlitz Newsletter for %s %s if you have not done so already.',$this->plugin_domain),'<a target="_blank" href="http://www.feedblitz.com/f/f.fbz?NewsSource&url='.home_url().'">',get_bloginfo(),'</a>');?></td>
                        </tr>
                        <tr class="alt">
                            <td>2)<td><?php printf(__('Once you have created your Newsletter, enter its ID into the field below (the Newsletter ID can be found at %s):',$this->plugin_domain),'<a target="_blank" href="http://www.feedblitz.com/f/f.fbz?Lists">http://www.feedblitz.com/f/f.fbz?Lists</a>');?></td>
                        </tr>
                        <tr>
                            <td width="20">3)<td><?php _e('Optional: Set the text that will appear next to the opt in check box on the registration form: ',$this->plugin_domain);?></td>
                        </tr>
                    </tbody>
                </table>
                <p></p>
                <table cellpadding="3" cellspacing="3" class="widefat" >
                    <thead>
                        <tr><th width="150"><?php _e('Newsletter ID',$this->plugin_domain);?></th><th><?php _e('Opt in text',$this->plugin_domain);?></th><th><?php _e('Where to add the checkbox (Registration/Comment Form)',$this->plugin_domain);?></th></tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><input type="text" name="feedblitz_settings[feedblitz_feedid]" value="<?php echo $options['feedblitz_feedid'];?>"/></td>
                            <td><input style="width:90%" type="text" name="feedblitz_settings[feedblitz_text]" value="<?php echo $options['feedblitz_text'];?>"/></td>
                            <td><select name="feedblitz_settings[feedblitz_where]">
                                    <option value="both" <?php selected('both',$options['feedblitz_where']);?>><?php _e('Both',$this->plugin_domain);?></option>
                                    <option value="registration" <?php selected('registration',$options['feedblitz_where']);?>><?php _e('Registration Form',$this->plugin_domain);?></option>
                                    <option value="comment" <?php selected('comment',$options['feedblitz_where']);?>><?php _e('Comment Form',$this->plugin_domain);?></option>
                                </select></td>
                        </tr>
                    </tbody>
                </table>
                <input type="hidden" name="action" value="update" />
                <p class="submit">
                    <input type="submit" class="button-primary" value="<?php _e('Save Changes') ?>" />
                </p>
            </form>
            <?php
            }

            /**************************************************************
            * public output
            *************************************************************/


            /**************************************************************
            * helper functions
            *************************************************************/


        } // end class
    } // end if class not exists

    // start fb_membermail class engines
    if (class_exists ( 'fb_membermail' )) :
        $fb_membermail = new fb_membermail ( );
        // confirm warp capability
        if (isset ( $fb_membermail )) {
            // engage
            register_activation_hook ( __FILE__, array (&$fb_membermail, 'install' ) );
        }
        endif;
?>
