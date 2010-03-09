<?php
/*
Plugin Name: Footnotes for WordPress
Plugin URI: http://projects.radgeek.com/wp-footnotes.php
Description: easy-to-use fancy footnotes for WordPress posts
Version: 2010.0309
Author: Charles Johnson
Author URI: http://radgeek.com/
License: GPL
*/

/**
 * @package FootnotesForWordPress
 * @version 2010.0309
 */

class FootnotesForWordPress {
	var $accumulated;

	function add_scripts () {
		$url = str_replace(WP_PLUGIN_DIR, WP_PLUGIN_URL, dirname(__FILE__));

		wp_enqueue_script(
			'footnote-voodoo',
			$url.'/footnote-voodoo.js',
			/*depends on=*/ array('jquery'),
			/*ver=*/ '2010.0306'
		);
		wp_enqueue_style(
			'footnote-vodoo',
			$url.'/footnote-voodoo.css',
			/*depends on=*/ array(),
			/*ver=*/ '2010.0306'
		);
	}
	function add_inline_styles () {
	?>
<style type="text/css">

	.footnote-indicator:before {
		content: url(<?php print str_replace(WP_PLUGIN_DIR, WP_PLUGIN_URL, dirname(__FILE__)); ?>/footnoted.png);
		width: 10px;
		height: 10px;
	}
	ol.footnotes li {
		background: #eeeeee url(<?php print trailingslashit(str_replace(WP_PLUGIN_DIR, WP_PLUGIN_URL, dirname(__FILE__))); ?>note.png) 0px 0px repeat-x;
	}
</style>
<script type="text/javascript">
	// Globals
	var tipUpUrl = 'url(<?php print str_replace(WP_PLUGIN_DIR, WP_PLUGIN_URL, dirname(__FILE__)); ?>/tip.png)';
	var tipDownUrl = 'url(<?php print str_replace(WP_PLUGIN_DIR, WP_PLUGIN_URL, dirname(__FILE__)); ?>/tip-down.png)';
</script>
	<?php
	}
	function FootnotesForWordPress () {
		$this->accumulated = array();
	} /* FootnotesForWordPress constructor */

	function shortcode ($atts, $content = NULL, $code = '') {
		global $post;

		// Get parameters
		$atts = shortcode_atts( array(
			"name" => NULL,
			'backlink-prefix' => 'to-',
		), $atts );

		$bullet = (count($this->accumulated) + 1);
		$noteId = $atts['name'];
		if (is_null($noteId) and !is_null($post)) :
			$noteId = $post->post_name.'-n-'.$bullet;
		endif;

		// Allow any inside shortcodes to do their work.
		$content = do_shortcode($content);
		$note_marker = "<strong><sup>[$bullet]</sup></strong>";

		$note = <<<EON
<li class="footnote" id="$noteId">$note_marker $content <a class="note-return" href="#{$atts['backlink-prefix']}{$noteId}">&#x21A9;</a></li>
EON;
		$this->accumulated[] = $note;

		return '<sup>[<a href="#'.$noteId.'" class="footnoted" id="'.$atts['backlink-prefix'].$noteId.'">'.$bullet.'</a>]</sup>';
	} /* FootnotesForWordPress::shortcode */

	function discharge ($atts = array(), $content = NULL, $code = '') {
		$notes = '';
		if (count($this->accumulated) > 0) :
			$notes = "<ol class=\"footnotes\">\n\t"
				.implode("\n\t", $this->accumulated)
				."</ol>\n";
			$this->accumulated = array();
		endif;

		return $notes;
	} /* FootnotesForWordPress::discharge */

	function the_content ($content) {
		/* Discharge any remaining footnotes */
		$content .= "\n".$this->discharge();

		return $content;
	} /* FootnotesForWordPress::the_content() */
} /* class FootnotesForWordPress */

$footnotesForWordPress = new FootnotesForWordPress;

add_shortcode('ref', array($footnotesForWordPress, 'shortcode'));
add_shortcode('references', array($footnotesForWordPress, 'discharge'));

// Way downstream; needs to be after do_shortcode (priority 11), for one thing
add_filter('the_content', array($footnotesForWordPress, 'the_content'), 1000, 2);

add_action('init', array($footnotesForWordPress, 'add_scripts'));
add_action('wp_head', array($footnotesForWordPress, 'add_inline_styles'));

