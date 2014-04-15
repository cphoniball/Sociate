<?php $sociate_options = get_option( 'sociate_options' ); ?>

 <div class="sociate-buttons clearfix cf"
 	data-postid="<?php the_ID(); ?>"
 	data-url="<?php the_permalink(); ?>"
	data-title="<?php the_title(); ?>"
	data-postid="<?php the_ID(); ?>"
	data-summary="<?php the_excerpt(); ?>"
	data-twitteraccount="<?php echo $sociate_options['twitter_account']; ?>"
	data-imageurl="<?php echo wp_get_attachment_url( get_post_thumbnail_id( the_ID() ) ); ?>"
	data-getcount="true"
	data-initialized="false"
 	>

 	<?php $social = SOC_get_social_data( get_the_ID() ); ?>

    <ul class="sociate button-list showcount">
        <?php if ($sociate_options['use_facebook'] == 'checked') : ?>
            <li>
            	<a data-site="facebook" class="sociate-button <?php the_ID(); ?> sociate-facebook">
            		<i class="icon-lg icon-facebook"></i>
            		<?php if ( $social['facebook'] ) : ?>
            			<span class="sociate-count"><?php echo $social['facebook']; ?></span>
            		<?php endif; ?>
            	</a>
            </li>
        <?php endif; ?> 

        <?php if ($sociate_options['use_twitter'] == 'checked') : ?>
            <li>
            	<a data-site="twitter" class="sociate-button <?php the_ID(); ?> sociate-twitter">
            		<i class="icon-lg icon-twitter"></i>
            		<?php if ( $social['twitter'] ) : ?>
            			<span class="sociate-count"><?php echo $social['twitter']; ?></span>
            		<?php endif; ?>
            	</a>
            </li>
        <?php endif; ?> 

        <?php if ($sociate_options['use_google_plus'] == 'checked') : ?>
            <li>
            	<a data-site="google-plus" class="sociate-button <?php the_ID(); ?> sociate-google-plus">
            		<i class="icon-lg icon-google-plus"></i>
    				<?php if ( $social['google-plus'] ) : ?>
            			<span class="sociate-count"><?php echo $social['google-plus']; ?></span>
            		<?php endif; ?>
            	</a>
            </li>
        <?php endif; ?> 

        <?php if ($sociate_options['use_linkedin'] == 'checked') : ?>
            <li>
            	<a data-site="linkedin" class="sociate-button <?php the_ID(); ?> sociate-linkedin">
            		<i class="icon-lg icon-linkedin"></i>
            		<?php if ( $social['linkedin'] ) : ?>
            			<span class="sociate-count"><?php echo $social['linkedin']; ?></span>
            		<?php endif; ?>
            	</a>
            </li>
        <?php endif; ?> <?php if ($sociate_options['use_pinterest'] == 'checked') : ?>
            <li>
            	<a data-site="pinterest" class="sociate-button <?php the_ID(); ?> sociate-pinterest">
            		<i class="icon-lg icon-pinterest"></i>
            		<?php if ( $social['pinterest'] ) : ?>
            			<span class="sociate-count"><?php echo $social['pinterest']; ?></span>
            		<?php endif; ?>
            	</a>
            </li>
        <?php endif; ?>
    </ul>
</div>

<script>
	document.dispatchEvent(new Event('sociateButtons'));
</script>