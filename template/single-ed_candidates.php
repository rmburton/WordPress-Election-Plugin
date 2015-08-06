<?php
/**
 * The template for displaying all single posts and attachments
 *
 * @package WordPress
 * @subpackage Twenty_Fifteen
 * @since Twenty Fifteen 1.0
 */

//get_header(); 
require_once plugin_dir_path( __FILE__ ) . 'functions.php';
$candidate_id = get_the_ID();
$candidate = get_candidate( $candidate_id );
$party = get_party_from_candidate( $candidate_id );
$constituency = get_constituency_from_candidate( $candidate_id );
$candidate_news = get_news( $candidate['reference_id'] );
 ?>

<div id="container">
	<?php display_header(); ?>
    <div id="main" role="main">
		<h2 class="title"><?php echo $candidate['name']; ?></h2>
		<div class="flow_it">
			<div class="politicians">
				<?php display_candidate( $candidate, $constituency, $party, $candidate_news, array( 'constituency', 'party' ), 'constituency' ); ?>
			</div>
			<div class="three_columns">
				<h2>News that mentions <?php echo $candidate['name']; ?></h2>
				<p class="news-article-notice">Articles are gathered from <a href="http://news.google.ca">Google News</a> by searching for the candidate's full name.</p>
				<?php display_news_summaries( $candidate_news, $candidate['reference_id'] ); ?>
			</div>
		</div>
	</div>
</div>
<?php get_footer(); ?>
