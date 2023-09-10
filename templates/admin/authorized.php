
user: <?php echo $authorized['username']; ?>
<p><a class="button button-primary" href="<?php echo esc_url( get_admin_url( null, 'options-general.php?page=' . $this->slug . '&tab=logout' ) ); ?>"><?php echo esc_html__( 'Disconnect from Pocket', 'reading-list-for-pocket' ); ?></a></p>