<?php
// Renders the social media admin for smallbusiness.com
// This page displays a table of recent posts and their corresponding social media hits
?>

<div class="wrap sociate-menu-wrap">

	<h2>Small Business.com Social Media Management</h2>

	<div class="post-listing sociate-post-table-wrapper">

		<?php
		// custom loop
		$args = array();
		$args['orderby'] = 'date';

		// pagination
		if ( $_REQUEST['paged'] ) { $args['paged'] = $_REQUEST['paged']; }
		else { $args['paged'] = '1'; }

		// order, ascending or descending
		if ( $_REQUEST['order'] ) { $args['order'] = $_REQUEST['order']; }

		// posts per page
		if ( $_REQUEST['posts_per_page'] ) { $args['posts_per_page'] = $_REQUEST['posts_per_page']; }
		if ( $_REQUEST['orderby'] ) { $args['orderby'] = $_REQUEST['orderby']; }
		if ( $_REQUEST['meta_key'] ) { $args['meta_key'] = $_REQUEST['meta_key']; }

		$query = new WP_Query( $args );


		$current = $args['paged'];
		$current_url = set_url_scheme( 'http://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'] );
		$total_pages = $query->max_num_pages;



		function soc_table_url( $args ) {
			global $current_url;

			if ( ! isset($_REQUEST['order'] ) || $_REQUEST['order'] == 'ASC' ) {
				$args['order'] = 'DESC';
			} else {
				$args['order'] = 'ASC';
			}

			echo esc_url( add_query_arg( $args ), $current_url );
		}

		?>

		<div class="tablenav top">
			<!-- actions -->
			<div class="alignleft actions">
				<button id="refresh-all-social-scores" class="button">Refresh all social scores</button>
				<span class="replacing-scores hidden">Replacing scores, please do not refresh or close the browser window
					<img src="<?php bloginfo( 'url' )?>/wordpress/wp-admin/images/wpspin_light.gif">
				</span>
			</div>

			<!-- navigation -->
			<div class="tablenav-pages">
				<span class="displaying-num"><?php echo $query->found_posts; ?> total items</span>
				<span class="pagination-links">
					<a href="<?php echo esc_url( add_query_arg( 'paged', 1), $current_url ); ?>" class="first-page <?php if ($current === '1') { echo 'disabled'; } ?>">&laquo;</a>
					<a href="<?php echo esc_url( add_query_arg( 'paged', max( 1, $current - 1 ), $current_url ) ); ?>" class="prev-page <?php if ($current === '1') { echo 'disabled'; } ?>">&lsaquo;</a>
					<span class="paging"><?php echo $current . ' of ' . $total_pages; ?></span>
					<a href="<?php echo esc_url( add_query_arg( 'paged', min( $total_pages, $current + 1 ), $current_url ) ); ?>" class="next-page <?php if ($current == $total_pages) { echo 'disabled'; } ?>">&rsaquo;</a>
					<a href="<?php echo esc_url( add_query_arg( 'paged', $total_pages ), $current_url ); ?>" class="last-page <?php if ($current == $total_pages) { echo 'disabled'; } ?>">&raquo;</a>
				</span>
			</div>
		</div>

		<table class="table-hover table table-bordered">
			<thead>
				<tr>
					<th><a href="<?php soc_table_url( array( 'orderby' => 'title' ) ); ?>">Title</a></th>
					<th><a href="<?php soc_table_url( array( 'orderby' => 'date' ) ); ?>">Published</a></th>
					<th><a href="<?php soc_table_url( array( 'orderby' => 'meta_value_num', 'meta_key' => 'sociate-total' ) ); ?>">Total Shares</a></th>
					<th><a href="<?php soc_table_url( array( 'orderby' => 'meta_value_num', 'meta_key' => 'sociate-trending' ) ); ?>">Trending Score</a></th>
					<th><a href="<?php soc_table_url( array( 'orderby' => 'meta_value_num', 'meta_key' => 'sociate-facebook' ) ); ?>">Facebook</a></th>
					<th><a href="<?php soc_table_url( array( 'orderby' => 'meta_value_num', 'meta_key' => 'sociate-twitter' ) ); ?>">Twitter</a></th>
					<th><a href="<?php soc_table_url( array( 'orderby' => 'meta_value_num', 'meta_key' => 'sociate-google-plus' ) ); ?>">Google+</a></th>
					<th><a href="<?php soc_table_url( array( 'orderby' => 'meta_value_num', 'meta_key' => 'sociate-linkedin' ) ); ?>">Linkedin</a></th>
					<th><a href="<?php soc_table_url( array( 'orderby' => 'meta_value_num', 'meta_key' => 'sociate-pinterest' ) ); ?>">Pinterest</a></th>
				</tr>
			</thead>

			<tbody>
				<?php if ( $query->have_posts() ) : while ( $query->have_posts() ) : $query->the_post(); ?>
					<tr>
						<!-- get the post's social information-->
						<?php $social = SOC_get_social_data( get_the_ID() ); ?>
						<th class="post-title">
							<div class="sociate-refresh-wrapper">
								<button class="refresh-post-social button" data-url="<?php the_permalink(); ?>" data-postid="<?php the_ID(); ?>">
									<span class="text">Refresh</span>
									<img src="<?php bloginfo( 'url' ); ?>/wordpress/wp-admin/images/wpspin_light.gif" alt="" class="hidden">
								</button>&nbsp;
							</div>
							<a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
						</th>
						<th class="published"><?php the_time( 'n-j-y' ); ?></th>
						<th class="total"><?php echo $social['total']; ?></th>
						<th class="trending"><?php echo $social['trending']; ?></th>
						<th class="facebook"><?php echo $social['facebook']; ?></th>
						<th class="twitter"><?php echo $social['twitter']; ?></th>
						<th class="google-plus"><?php echo $social['google-plus']; ?></th>
						<th class="linkedin"><?php echo $social['linkedin']; ?></th>
						<th class="pinterest"><?php echo $social['pinterest']; ?></th>
					</tr>
				<?php endwhile; endif; ?><!-- end loop-->
			</tbody>

			<tfoot>
				<tr>
					<th>Title</th>
					<th>Published</th>
					<th>Total Shares</th>
					<th>Trending Score</th>
					<th>Facebook</th>
					<th>Twitter</th>
					<th>Google+</th>
					<th>Linkedin</th>
					<th>Pinterest</th>
				</tr>
			</tfoot>
		</table>

		<div class="tablenav bottom">
			<div class="tablenav-pages">
				<span class="displaying-num"><?php echo $query->found_posts; ?> total items</span>
				<span class="pagination-links">
					<a href="<?php echo esc_url( add_query_arg( 'paged', 1), $current_url ); ?>" class="first-page <?php if ($current === '1') { echo 'disabled'; } ?>">&laquo;</a>
					<a href="<?php echo esc_url( add_query_arg( 'paged', max( 1, $current - 1 ), $current_url ) ); ?>" class="prev-page <?php if ($current === '1') { echo 'disabled'; } ?>">&lsaquo;</a>
					<span class="paging"><?php echo $current . ' of ' . $total_pages; ?></span>
					<a href="<?php echo esc_url( add_query_arg( 'paged', min( $total_pages, $current + 1 ), $current_url ) ); ?>" class="next-page <?php if ($current == $total_pages) { echo 'disabled'; } ?>">&rsaquo;</a>
					<a href="<?php echo esc_url( add_query_arg( 'paged', $total_pages ), $current_url ); ?>" class="last-page <?php if ($current == $total_pages) { echo 'disabled'; } ?>">&raquo;</a>
				</span>
			</div>
		</div>

	</div>

</div>