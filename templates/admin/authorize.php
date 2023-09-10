<?php if ( ! isset( $get_data['request_token'] ) ) : ?>
    <p><a class="button button-primary" href="<?php echo $this->sign_in_link_to_pocket(); ?>"><?php echo esc_html__( 'Connect to Pocket', 'reading-list-for-pocket' ); ?></a></p>
<?php else :
    // request an access token and username.
    $request_access_token = $this->pocket->request_access_token( $get_data['request_token'] );
    if ( isset( $request_access_token['access_token'] ) && isset( $request_access_token['username'] ) ) {
        $save_token = $this->pocket->save_token( $request_access_token );
    }
    // save access token and reload the page.
    ?>
    <script>window.location = '<?php echo esc_url_raw( $this->login_credentials['redirect_url'] ); ?>'</script>
<?php endif; 