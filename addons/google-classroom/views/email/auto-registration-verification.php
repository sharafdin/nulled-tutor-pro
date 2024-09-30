<!DOCTYPE html>
<html>
    <body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; color: rgb(83, 83, 83);">
        <div style="width:500px; max-width:calc(90% - 40px); margin: 25px auto; background: rgb(231, 231, 231); border-radius: 20px; padding: 40px;">
            <div style="background:white; padding: 30px; border-radius: 20px; text-align: center;">
                <img src="<?php echo esc_url( TUTOR_GC()->url . 'assets/images/mail-icon.png' ); ?>" style="display:inline-block"/>
                <br/>
                <br/>
                <h3 style="margin: 0;">
                    <?php esc_html_e( 'Please, Verify Your Email Address', 'tutor-pro' ); ?>
                </h3>
                <br/>
                <p style="margin: 0; margin-bottom: 10px;">
                    <?php echo __( 'Hello', 'tutor-pro' ), ', ', $user_data['display_name']; ?>
                </p>
                <p style="margin: 0;">
                    <?php 
                        $translated    = __( 'You are now enrolled in a course on %site_url% from Google Classroom %class_name%. Your validated credentials for %site_url% are attached below.', 'tutor-pro' ); 

                        $site_url = get_home_url();
                        $domain_anchor = '<a href="' . esc_url( $site_url ) . '" target="_blank" style="text-decoration: none;">' . parse_url( $site_url )['host'] . '</a>';
                        
                        $replaced = str_replace( '%site_url%', $domain_anchor, $translated );
                        $replaced = str_replace( '%class_name%', '<b>' . $class_name . '</b>', $replaced );
                        
                        echo $replaced;
                    ?> 
                </p>
                <br/>
                <br/>
                <span style="border: 1px solid #DCDBDC; border-radius: 10px; padding: 15px 54px; display: inline-block;">
                    <span>
                        <?php esc_html_e( 'Email', 'tutor-pro' ); ?>: 
                    </span> &nbsp;
                    
                    <b style="color: black;">
                        <?php echo esc_html( $user_data['user_email'] ); ?>
                    </b>
                </span>
                <br/>
                <br/>
                <br/>
                <a href="<?php echo esc_url( $password_reset_link ); ?>" target="_blank" style="background: #3E64DE; color: white; border-radius: 3px; padding: 10px 50px; text-decoration: none;">Verify Email</a>
                <br/>
                <br/>
            </div>
        </div>
    </body>
</html>