<?php
/**
 * Omnisend Sync View
 *
 * @package OmnisendPlugin
 */

defined( 'ABSPATH' ) || exit;

function omnisend_show_sync() {
	$all_sync_stats = ( new Omnisend_Sync_Stats_Repository() )->get_all_stats();
	$show_error     = omnisend_has_sync_stats_error( $all_sync_stats );
	$show_skipped   = omnisend_has_sync_stats_skipped( $all_sync_stats );

	omnisend_handle_sync_page_actions();
	?>

	<div class="settings-page">
		<?php
		omnisend_display_omnisend_connected();
		omnisend_display_tabs( 'Sync' );
		?>
		<div class="settings-section">
			<h3 class="omnisend-content-lead strong setting-title">Sync</h3>
			<p class="omnisend-content-body">Your store data, like contacts and orders, are automatically synced with Omnisend. The
				chart below displays the current sync status.</p>
			<div class="sync-stats">
				<table class="wp-list-table widefat fixed striped posts">
					<thead>
					<tr>
						<td>Data type</td>
						<td class="fixed_date">Successfully synced</td>
						<td>Total</td>
						<td>Pending</td>
						<?php
						if ( $show_error ) {
							echo '<td>Error</td>';
						}
						?>
						<?php
						if ( $show_skipped ) {
							echo '<td>Skipped</td>';
						}
						?>
					</tr>
					</thead>
					<tr>
						<td>Contacts</td>
						<td id="contact-sync-success-count"><?php echo esc_html( $all_sync_stats->contacts->synced ); ?></td>
						<td id="contact-sync-total-count">
							<?php
							if ( $all_sync_stats->contacts->unique && $all_sync_stats->contacts->unique != $all_sync_stats->contacts->total ) {
								echo esc_html( $all_sync_stats->contacts->total ) . ' (Unique - ' . esc_html( $all_sync_stats->contacts->unique ) . ')';
							} else {
								echo esc_html( $all_sync_stats->contacts->total );
							}
							?>
						</td>
						<?php
						if ( $all_sync_stats->contacts->not_synced > 0 ) {
							echo '<td id="contact-sync-pending-count" class="omnisend-warn">' . esc_html( $all_sync_stats->contacts->not_synced ) . '</td>';
						} else {
							echo '<td id="contact-sync-pending-count">' . esc_html( $all_sync_stats->contacts->not_synced ) . '</td>';
						}
						?>
						<?php
						if ( $show_error ) {
							if ( $all_sync_stats->contacts->error > 0 ) {
								echo '<td id="contact-sync-error-count" class="omnisend-warn">' . esc_html( $all_sync_stats->contacts->error ) . '</td>';
							} else {
								echo '<td id="contact-sync-error-count">' . esc_html( $all_sync_stats->contacts->error ) . '</td>';
							}
						}
						?>
						<?php
						if ( $show_skipped ) {
							echo '<td id="contact-sync-skipped-count">' . esc_html( $all_sync_stats->contacts->skipped ) . '</td>';
						}
						?>
					</tr>
					<tr>
						<td>Orders</td>
						<td id="order-sync-success-count"><?php echo esc_html( $all_sync_stats->orders->synced ); ?></td>
						<td id="order-sync-total-count"><?php echo esc_html( $all_sync_stats->orders->total ); ?></td>
						<?php
						if ( $all_sync_stats->orders->not_synced > 0 ) {
							echo '<td id="order-sync-pending-count" class="omnisend-warn">' . esc_html( $all_sync_stats->orders->not_synced ) . '</td>';
						} else {
							echo '<td id="order-sync-pending-count">' . esc_html( $all_sync_stats->orders->not_synced ) . '</td>';
						}
						?>
						<?php
						if ( $show_error ) {
							if ( $all_sync_stats->orders->error > 0 ) {
								echo '<td id="order-sync-error-count" class="omnisend-warn">' . esc_html( $all_sync_stats->orders->error ) . '</td>';
							} else {
								echo '<td id="order-sync-error-count">' . esc_html( $all_sync_stats->orders->error ) . '</td>';
							}
						}
						?>
						<?php
						if ( $show_skipped ) {
							echo '<td id="order-sync-skipped-count">' . esc_html( $all_sync_stats->orders->skipped ) . '</td>';
						}
						?>
					</tr>
					<tr>
						<td>Products</td>
						<td id="product-sync-success-count"><?php echo esc_html( $all_sync_stats->products->synced ); ?></td>
						<td id="product-sync-total-count"><?php echo esc_html( $all_sync_stats->products->total ); ?></td>
						<?php
						if ( $all_sync_stats->products->not_synced > 0 ) {
							echo '<td id="product-sync-pending-count" class="omnisend-warn">' . esc_html( $all_sync_stats->products->not_synced ) . '</td>';
						} else {
							echo '<td id="product-sync-pending-count">' . esc_html( $all_sync_stats->products->not_synced ) . '</td>';
						}
						?>
						<?php
						if ( $show_error ) {
							if ( $all_sync_stats->products->error > 0 ) {
								echo '<td id="product-sync-error-count" class="omnisend-warn">' . esc_html( $all_sync_stats->products->error ) . '</td>';
							} else {
								echo '<td id="product-sync-error-count">' . esc_html( $all_sync_stats->products->error ) . '</td>';
							}
						}
						?>
						<?php
						if ( $show_skipped ) {
							echo '<td id="product-sync-skipped-count">' . esc_html( $all_sync_stats->products->skipped ) . '</td>';
						}
						?>
					</tr>
					<tr>
						<td>Categories</td>
						<td id="category-sync-success-count"><?php echo esc_html( $all_sync_stats->categories->synced ); ?></td>
						<td id="category-sync-total-count"><?php echo esc_html( $all_sync_stats->categories->total ); ?></td>
						<?php
						if ( $all_sync_stats->categories->not_synced > 0 ) {
							echo '<td id="category-sync-pending-count" class="omnisend-warn">' . esc_html( $all_sync_stats->categories->not_synced ) . '</td>';
						} else {
							echo '<td id="category-sync-pending-count">' . esc_html( $all_sync_stats->categories->not_synced ) . '</td>';
						}
						?>
						<?php
						if ( $show_error ) {
							if ( $all_sync_stats->categories->error > 0 ) {
								echo '<td id="category-sync-error-count" class="omnisend-warn">' . esc_html( $all_sync_stats->categories->error ) . '</td>';
							} else {
								echo '<td id="category-sync-error-count">' . esc_html( $all_sync_stats->categories->error ) . '</td>';
							}
						}
						?>
						<?php
						if ( $show_skipped ) {
							echo '<td id="category-sync-skipped-count">' . esc_html( $all_sync_stats->categories->skipped ) . '</td>';
						}
						?>
					</tr>
				</table>
			</div>
			<?php
			omnisend_display_sync_loader();
			omnisend_display_sync_actions( $all_sync_stats );
			omnisend_display_resync_all_contacts();
			?>
		</div>
	</div>
	<?php
}

function omnisend_handle_sync_page_actions() {
	if ( ! isset( $_POST['action'] ) ) {
		return;
	}

	check_ajax_referer( 'omnisend-sync-action' );

	switch ( $_POST['action'] ) {
		case 'omnisend_init_resync':
			Omnisend_Sync_Manager::start_resync_all_with_error_or_skipped();
			break;
		case 'omnisend_resync_all_contacts':
			Omnisend_Sync_Manager::start_resync_contacts();
			break;
	}
}

function omnisend_has_sync_stats_error( $sync_stats ) {
	if ( $sync_stats->contacts->error ) {
		return true;
	}
	if ( $sync_stats->orders->error ) {
		return true;
	}
	if ( $sync_stats->products->error ) {
		return true;
	}
	if ( $sync_stats->categories->error ) {
		return true;
	}

	return false;
}

function omnisend_has_sync_stats_skipped( $sync_stats ) {
	if ( $sync_stats->contacts->skipped ) {
		return true;
	}
	if ( $sync_stats->orders->skipped ) {
		return true;
	}
	if ( $sync_stats->products->skipped ) {
		return true;
	}
	if ( $sync_stats->categories->skipped ) {
		return true;
	}

	return false;
}

function omnisend_has_sync_stats_not_synced( $sync_stats ) {
	if ( $sync_stats->contacts->not_synced ) {
		return true;
	}
	if ( $sync_stats->orders->not_synced ) {
		return true;
	}
	if ( $sync_stats->products->not_synced ) {
		return true;
	}
	if ( $sync_stats->categories->not_synced ) {
		return true;
	}

	return false;
}

function omnisend_display_sync_loader() {
	if ( ! Omnisend_Sync_Manager::are_data_syncing() ) {
		return;
	}
	?>
	<div class="sync-loader">
		<div class="sync-spinner"></div>
		<span>Syncing...</span>
	</div>
	<?php
}

function omnisend_display_sync_actions( $all_sync_stats ) {
	if ( Omnisend_Sync_Manager::are_data_syncing() ) {
		return;
	}

	if ( ! omnisend_has_sync_stats_error( $all_sync_stats ) && ! omnisend_has_sync_stats_skipped( $all_sync_stats ) && ! omnisend_has_sync_stats_not_synced( $all_sync_stats ) ) {
		return;
	}
	?>
	<div class="sync-actions">
		<p>Resync store data from Pending, Error or Skipped columns.</p>
		<div>
			<form method="post">
				<?php wp_nonce_field( 'omnisend-sync-action' ); ?>
				<input type="hidden" name="action" value="omnisend_init_resync"/>
				<button type="submit" class="omnisend-primary-button">Resync</button>
			</form>
		</div>
	</div>
	<?php
}

function omnisend_display_resync_all_contacts() {
	if ( Omnisend_Sync_Manager::are_contacts_syncing() ) {
		return;
	}
	?>
	<div class="resync-contacts">
		<h3 class="omnisend-content-lead strong setting-title">Resync all contacts</h3>
		<p class="omnisend-content-body">
			Resync all of your contacts with Omnisend.
			The resync time depends on how many contacts you have.
		</p>
		<form method="post">
			<?php wp_nonce_field( 'omnisend-sync-action' ); ?>
			<input type="hidden" name="action" value="omnisend_resync_all_contacts">
			<button type="submit" class="omnisend-primary-button">Resync all contacts</button>
		</form>
	</div>
	<?php
}
