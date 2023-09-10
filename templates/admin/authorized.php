
user: <?php echo $authorized['username']; ?>
<p><a class="button button-primary" href="<?php echo esc_url( get_admin_url( null, 'options-general.php?page=' . $this->slug . '&tab=logout' ) ); ?>"><?php echo esc_html__( 'Disconnect from Pocket', 'reading-list-for-pocket' ); ?></a></p>

<?php if ( ! empty( $demo_retrieved_items ) ) : ?>
    <ol>
    <?php foreach ( $demo_retrieved_items as $save ) : ?>
        <li><a href="<?php echo esc_url( $save['resolved_url'] ); ?>"><?php echo $save['resolved_title']; ?></a></li>
    <?php endforeach; ?>
    </ol>
<?php endif; ?>
