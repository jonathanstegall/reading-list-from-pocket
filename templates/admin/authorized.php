
user: <?php echo $authorized['username']; ?>
<p><a class="button button-primary" href="<?php echo esc_url( get_admin_url( null, 'options-general.php?page=' . $this->slug . '&tab=logout' ) ); ?>"><?php echo esc_html__( 'Disconnect from Pocket', 'reading-list-for-pocket' ); ?></a></p>

<?php if ( true === $demo_result['cached'] ) : ?>
    it is cached
<?php else : ?>
    it is not cached
<?php endif; ?>

<?php if ( true === $demo_result['from_cache'] ) : ?>
    it is loaded from the cache
<?php else : ?>
    it is not loaded from the cache
<?php endif; ?>

<?php if ( ! empty( $demo_retrieved_items ) ) : ?>
    <ol>
    <?php foreach ( $demo_retrieved_items as $save ) : ?>
        <li>
            <a href="<?php echo esc_url( $save['resolved_url'] ); ?>">
                <?php if ( '' !== $save['resolved_title'] ) : ?>
                    <?php echo $save['resolved_title']; ?>
                <?php elseif ( '' !== $save['given_title'] ) : ?>
                    <?php echo $save['given_title']; ?>
                <?php elseif ( '' !== $save['resolved_url'] ) : ?>
                    <?php echo $save['resolved_url']; ?>
                <?php else : ?>
                    <?php echo $save['given_url']; ?>
                <?php endif; ?>
            </a>
        </li>
    <?php endforeach; ?>
    </ol>
<?php endif; ?>
