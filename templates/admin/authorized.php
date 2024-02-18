
user: <?php echo $authorized['username']; ?>
<p><a class="button button-primary" href="<?php echo esc_url( get_admin_url( null, 'options-general.php?page=' . $this->slug . '&tab=logout' ) ); ?>"><?php echo esc_html__( 'Disconnect from Pocket', 'reading-list-for-pocket' ); ?></a></p>

<?php if ( true === $demo_result['cached'] ) : ?>
	<p>it is cached</p>
<?php else : ?>
	<p>it is not cached</p>
<?php endif; ?>

<?php if ( true === $demo_result['from_cache'] ) : ?>
	<p>it is loaded from the cache</p>
<?php else : ?>
	<p>it is not loaded from the cache</p>
<?php endif; ?>

<?php if ( ! empty( $demo_retrieved_items ) ) : ?>
<ol>
	<?php foreach ( $demo_retrieved_items as $save ) : ?>
		<?php if ( isset( $save['given_url'] ) ) : ?>
			<li>
				<a href="<?php echo isset( $save['resolved_url'] ) && '' !== $save['resolved_url'] ? esc_url( $save['resolved_url'] ) : $save['given_url']; ?>">
					<?php if ( isset( $save['resolved_title'] ) && '' !== $save['resolved_title'] ) : ?>
						<?php echo $save['resolved_title']; ?>
					<?php elseif ( isset( $save['given_title'] ) && '' !== $save['given_title'] ) : ?>
						<?php echo $save['given_title']; ?>
					<?php elseif ( isset( $save['resolved_url'] ) && '' !== $save['resolved_url'] ) : ?>
						<?php echo $save['resolved_url']; ?>
					<?php else : ?>
						<?php echo $save['given_url']; ?>
					<?php endif; ?>
				</a>
			</li>
		<?php endif; ?>
	<?php endforeach; ?>
	</ol>
<?php endif; ?>
