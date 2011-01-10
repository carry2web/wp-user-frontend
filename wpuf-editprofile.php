<?php

function wpuf_user_edit_profile() {

    wpuf_auth_redirect_login(); // if not logged in, redirect to login page
    nocache_headers();
    wpuf_post_form_style();
    wpuf_user_edit_profile_form();
}
add_shortcode('wpuf_editprofile', 'wpuf_user_edit_profile');

function wpuf_user_edit_profile_form() {
    global $userdata;
    get_currentuserinfo();

    if(!(function_exists('get_user_to_edit'))) {
        require_once(ABSPATH.'/wp-admin/includes/user.php');
    }

    if(!(function_exists('_wp_get_user_contactmethods'))) {
        require_once(ABSPATH.'/wp-includes/registration.php');
    }

    $current_user = wp_get_current_user();
    $user_id = $user_ID = $current_user->ID;

    if (isset($_POST['submit'])) {
        check_admin_referer('update-profile_' . $user_id);
        $errors = edit_user($user_id);
        if(is_wp_error( $errors ) ) {
            $message = $errors->get_error_message();
            $style = "error";
        } else {
            $message = __("<strong>Success</strong>: Profile updated");
            $style = "success";
            do_action('personal_options_update', $user_id);
        }
    }

    $profileuser = get_user_to_edit($user_id);

    if( isset ($message) ){
        echo '<div class="'.$style.'">'.$message.'</div>';
    }
    ?>
<div class="wpuf-profile">
    <form name="profile" id="your-profile" action="" method="post">
            <?php wp_nonce_field('update-profile_' . $user_id) ?>
            <?php if ( $wp_http_referer ) : ?>
        <input type="hidden" name="wp_http_referer" value="<?php echo esc_url($wp_http_referer); ?>" />
            <?php endif; ?>
        <input type="hidden" name="from" value="profile" />
        <input type="hidden" name="checkuser_id" value="<?php echo $user_ID ?>" />
        <table class="form-table">
                <?php do_action('personal_options', $profileuser); ?>
        </table>
            <?php do_action('profile_personal_options', $profileuser); ?>

        <h3><?php _e('Name') ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="user_login"><?php _e('Username'); ?></label></th>
                <td><input type="text" name="user_login" id="user_login" value="<?php echo esc_attr($profileuser->user_login); ?>" disabled="disabled" class="regular-text" /><br /><em><span class="description"><?php _e('Usernames cannot be changed.'); ?></span></em></td>
            </tr>
            <tr>
                <th><label for="first_name"><?php _e('First Name') ?></label></th>
                <td><input type="text" name="first_name" id="first_name" value="<?php echo esc_attr($profileuser->first_name) ?>" class="regular-text" /></td>
            </tr>

            <tr>
                <th><label for="last_name"><?php _e('Last Name') ?></label></th>
                <td><input type="text" name="last_name" id="last_name" value="<?php echo esc_attr($profileuser->last_name) ?>" class="regular-text" /></td>
            </tr>

            <tr>
                <th><label for="nickname"><?php _e('Nickname'); ?> <span class="description"><?php _e('(required)'); ?></span></label></th>
                <td><input type="text" name="nickname" id="nickname" value="<?php echo esc_attr($profileuser->nickname) ?>" class="regular-text" /></td>
            </tr>

            <tr>
                <th><label for="display_name"><?php _e('Display to Public as') ?></label></th>
                <td>
                    <select name="display_name" id="display_name">
                            <?php
                            $public_display = array();
                            $public_display['display_username']  = $profileuser->user_login;
                            $public_display['display_nickname']  = $profileuser->nickname;
                            if ( !empty($profileuser->first_name) )
                                $public_display['display_firstname'] = $profileuser->first_name;
                            if ( !empty($profileuser->last_name) )
                                $public_display['display_lastname'] = $profileuser->last_name;
                            if ( !empty($profileuser->first_name) && !empty($profileuser->last_name) ) {
                                $public_display['display_firstlast'] = $profileuser->first_name . ' ' . $profileuser->last_name;
                                $public_display['display_lastfirst'] = $profileuser->last_name . ' ' . $profileuser->first_name;
                            }
                            if ( !in_array( $profileuser->display_name, $public_display ) ) // Only add this if it isn't duplicated elsewhere
                                $public_display = array( 'display_displayname' => $profileuser->display_name ) + $public_display;
                            $public_display = array_map( 'trim', $public_display );
                            $public_display = array_unique( $public_display );
                            foreach ( $public_display as $id => $item ) {
                                ?>
                        <option id="<?php echo $id; ?>" value="<?php echo esc_attr($item); ?>"<?php selected( $profileuser->display_name, $item ); ?>><?php echo $item; ?></option>
                                <?php
                            }
                            ?>
                    </select>
                </td>
            </tr>
        </table>

        <h3><?php _e('Contact Info') ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="email"><?php _e('E-mail'); ?> <span class="description"><?php _e('(required)'); ?></span></label></th>
                <td><input type="text" name="email" id="email" value="<?php echo esc_attr($profileuser->user_email) ?>" class="regular-text" /> </td>
            </tr>

            <tr>
                <th><label for="url"><?php _e('Website') ?></label></th>
                <td><input type="text" name="url" id="url" value="<?php echo esc_attr($profileuser->user_url) ?>" class="regular-text code" /></td>
            </tr>

                <?php
                foreach (_wp_get_user_contactmethods() as $name => $desc) {
                    ?>
            <tr>
                <th><label for="<?php echo $name; ?>"><?php echo apply_filters('user_'.$name.'_label', $desc); ?></label></th>
                <td><input type="text" name="<?php echo $name; ?>" id="<?php echo $name; ?>" value="<?php echo esc_attr($profileuser->$name) ?>" class="regular-text" /></td>
            </tr>
                    <?php
                }
                ?>
        </table>

        <h3><?php _e('About Yourself'); ?></h3>
        <table class="form-table">
            <tr>
                <th><label for="description"><?php _e('Biographical Info'); ?></label></th>
                <td><textarea name="description" id="description" rows="5" cols="30"><?php echo esc_html($profileuser->description); ?></textarea><br />
                    <span class="description"><?php _e('Share a little biographical information to fill out your profile. This may be shown publicly.'); ?></span></td>
            </tr>
            <tr id="password">
                <th><label for="pass1"><?php _e('New Password'); ?></label><br /><br /><em><span class="description"><?php _e("If you would like to change the password type a new one. Otherwise leave this blank."); ?></span></em></th>
                <td>
                    <input type="password" name="pass1" id="pass1" size="16" value="" autocomplete="off" /><br /><br />
                    <input type="password" name="pass2" id="pass2" size="16" value="" autocomplete="off" />&nbsp;<em><span class="description"><?php _e("Type your new password again."); ?></span></em>
                </td>
            </tr>
        </table>
            <?php do_action( 'show_user_profile', $profileuser ); ?>
        <p class="submit">
            <input type="hidden" name="action" value="update" />
            <input type="hidden" name="user_id" id="user_id" value="<?php echo esc_attr($user_id); ?>" />
            <input type="submit" class="wpuf-submit" value="<?php _e('Update Profile'); ?>" name="submit" />
        </p>
    </form>
</div>
    <?php
}