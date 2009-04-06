<?php
/**
 * Plugin Name: Manual Related Links
 * Plugin URI: http://xavisys.com/2009/04/manual-related-links/
 * Description: A related links plugin that allows you to manually specify links, even if they're on another site
 * Version: 1.0.0
 * Author: Aaron D. Campbell
 * Author URI: http://xavisys.com/
 */
/**
 *	Changelog:
 * 		04/05/2008 - 1.0.0:
 *			- First public release
 */
/**
 * manualRelatedLinks is the class that handles ALL of the plugin functionality.
 * It helps us avoid name collisions
 * http://codex.wordpress.org/Writing_a_Plugin#Avoiding_Function_Name_Collisions
 */
class manualRelatedLinks {
	/**
	 * Static property to hold our singleton instance
	 */
	static $instance = false;

	/**
	 * @var array Plugin settings
	 */
	private $_settings;

	/**
	 * This is our constructor, which is private to force the use of getInstance()
	 * @return void
	 */
	private function __construct() {
		$this->_getSettings();
		if ( $this->_settings['auto_insert'] != 'no' ) {
			add_filter('the_content', array( $this, 'filterPostContent'), 99);
		}
		if ( $this->_settings['rss'] == 'yes' ) {
			add_filter('the_content', array( $this, 'filterPostContentRSS'), 1);
		}
	}

	/**
	 * Function to instantiate our class and make it a singleton
	 */
	public static function getInstance() {
		if ( !self::$instance ) {
			self::$instance = new self;
		}
		return self::$instance;
	}

	/**
	 * This adds the options page for this plugin to the Options page
	 *
	 * @access public
	 */
	public function admin_menu() {
		add_options_page(__('Xavisys Manual Related Links', 'manual_related_links'), __('Related Links', 'manual_related_links'), 'manage_options', str_replace("\\", "/", __FILE__), array($this, 'options'));
		add_meta_box('relatedLinks', 'Related Links', array($this, 'insertForm'), 'post', 'normal');
		add_meta_box('relatedLinks', 'Related Links', array($this, 'insertForm'), 'page', 'normal');
	}

	public function insertForm($post) {
?>
		<table class="form-table">
<?php
		$relatedLinks = get_post_meta($post->ID, '_manual_related_links', true);
		for ( $i=0; $i < $this->_settings['max_relations_stored']; $i++) {
			if ( empty( $relatedLinks[$i] ) ) {
				$relatedLinks[$i] = '';
			}
			$rl = $i+1;
?>
			<tr valign="top">
				<th scope="row"><label for="_manual_related_links_<?php echo $rl; ?>"><?php _e("Related Link {$rl}:")?></label></th>
				<td>
					<input type="text" size="40" style="width:95%;" name="_manual_related_links[<?php echo $i; ?>]" id="_manual_related_links_<?php echo $rl; ?>" value="<?php echo attribute_escape($relatedLinks[$i]); ?>" />
				</td>
			</tr>
<?php
		}
?>
		</table>
<?php
	}

	public function addMetaData($post_ID) {
		$_POST['_manual_related_links'] = array_filter( $_POST['_manual_related_links'] );
		if (!add_post_meta($post_ID, '_manual_related_links', $_POST['_manual_related_links'], true)) {
			update_post_meta($post_ID, '_manual_related_links', $_POST['_manual_related_links']);
		}
	}

	/**
	 * This is used to display the options page for this plugin
	 */
	public function options() {
		$this->_getSettings();
?>
		<div class="wrap">
			<h2><?php _e('Manual Related Links', 'manual_related_links') ?></h2>
			<h3><?php _e('General Settings', 'manual_related_links') ?></h3>
			<form action="options.php" method="post">
				<?php wp_nonce_field('update-options'); ?>
				<table class="form-table">
					<tr valign="top">
						<th scope="row">
							<label for="mrl_title"><?php _e("Title:",'manual_related_links'); ?></label>
						</th>
						<td>
							<input id="mrl_title" name="mrl[title]" type="text" class="regular-text code" value="<?php echo attribute_escape($this->_settings['title']); ?>" size="40" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="mrl_no_rl_text"><?php _e("Display Text When No Related Links Found:",'manual_related_links'); ?></label>
						</th>
						<td>
							<input id="mrl_no_rl_text" name="mrl[no_rl_text]" type="text" class="regular-text code" value="<?php echo attribute_escape($this->_settings['no_rl_text']); ?>" size="40" />
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="mrl_max_relations_stored"><?php _e('Max Related Links to Store:', 'manual_related_links'); ?></label>
						</th>
						<td>
							<input id="mrl_max_relations_stored" name="mrl[max_relations_stored]" type="text" class="regular-text code" value="<?php echo attribute_escape($this->_settings['max_relations_stored']); ?>" size="40" />
							<span class="setting-description"><?php _e("Max number to store.  You can't display more than this.", 'manual_related_links'); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<label for="mrl_num_to_display"><?php _e('Number of Related Links to Display:', 'manual_related_links'); ?></label>
						</th>
						<td>
							<input id="mrl_num_to_display" name="mrl[num_to_display]" type="text" class="regular-text code" value="<?php echo attribute_escape($this->_settings['num_to_display']); ?>" size="40" />
							<span class="setting-description"><?php _e('The number of related links to display if none is specified.', 'manual_related_links'); ?></span>
						</td>
					</tr>
					<tr valign="top">
						<th scope="row">
							<?php _e("Other Setting:",'manual_related_links');?>
						</th>
						<td>
							<input name="mrl[auto_insert]" id="mrl_auto_insert_no" type="radio" value="no"<?php checked('no', $this->_settings['auto_insert']) ?>>
							<label for="mrl_auto_insert_no">
								<?php _e("Do Not Auto Insert Into Posts",'manual_related_links');?>
							</label>
							<br />
							<input name="mrl[auto_insert]" id="mrl_auto_insert_all" type="radio" value="all"<?php checked('all', $this->_settings['auto_insert']) ?>>
							<label for="mrl_auto_insert_all">
								<?php _e("Auto Insert Into Posts",'manual_related_links');?>
							</label>
							<br />
							<input name="mrl[auto_insert]" id="mrl_auto_insert_single" type="radio" value="single"<?php checked('single', $this->_settings['auto_insert']) ?>>
							<label for="mrl_auto_insert_single">
								<?php _e("Auto Insert Into Only Single Posts",'manual_related_links');?>
							</label>
							<br />
							<br />
							<input name="mrl[rss]" id="mrl_rss" type="checkbox" value="yes"<?php checked('yes', $this->_settings['rss']) ?>>
							<label for="mrl_rss">
								<?php _e("Related Links for RSS",'manual_related_links');?>
							</label>
						</td>
					</tr>
				</table>
				<p class="submit">
					<input type="hidden" name="action" value="update" />
					<input type="hidden" name="page_options" value="mrl" />
					<input type="submit" name="Submit" value="<?php _e('Update Options &raquo;', 'manual_related_links'); ?>" />
				</p>
			</form>
		</div>
<?php
	}

	public function getRelatedLinks( $args = array() ) {
		global $post;
		$output = '';

		$settings = wp_parse_args($args, $this->_settings);

		$relatedLinks = get_post_meta($post->ID, '_manual_related_links', true);

		if ( empty($relatedLinks) || $settings['num_to_display'] == 0 ){
			$output .= "<li>{$settings['no_rl_text']}</li>";
		} else {
			$relatedLinks = array_slice($relatedLinks, 0, $settings['num_to_display']);
			foreach ( $relatedLinks as $num => $rl ) {
				$num++;
				$output .= "<li class='related-link-{$num}'>" . make_clickable($rl) . '</li>';
			}
		}

		$output = "<ul class='related_links'>{$output}</ul>";

		if ( !empty($settings['title']) ) {
			$output = "<h3 class='related_links_title'>{$settings['title']}</h3>{$output}";
		}
		return $output;
	}

	public function relatedLinks( $args = array() ) {
		echo $this->getRelatedLinks($args);
	}

	public function filterPostContent($content) {
		// We don't want to filter if this is a feed or if settings tell us not to
		if ( ($this->_settings['auto_insert'] == 'all' || ( $this->_settings['auto_insert'] == 'single' && is_single() ) ) && !is_feed() ) {
			$content .= $this->getRelatedLinks();
		}

		return $content;
	}

	public function filterPostContentRSS($content) {
		if ( $this->_settings['rss'] == 'yes' && is_feed() ) {
			$content .= $this->getRelatedLinks();
		}

		return $content;
	}

	private function _getSettings() {
		$defaults = array(
			'title'					=> __("Related Links:",'manual_related_links'),
			'no_rl_text'			=> __("No Related Links",'manual_related_links'),
			'max_relations_stored'	=> 10,
			'num_to_display'		=> 5,
			'auto_insert'			=> 'no',
			'rss'					=> 'no'
		);
		$this->_settings = get_option('mrl');
		$this->_settings = wp_parse_args($this->_settings, $defaults);

		$this->_settings['max_relations_stored'] = intval($this->_settings['max_relations_stored']);
		$this->_settings['num_to_display'] = intval($this->_settings['num_to_display']);
	}
}

/**
 * Helper functions
 */

function wp_related_links( $args = array() ) {
	// Instantiate our class
	$manualRelatedLinks = manualRelatedLinks::getInstance();
	$manualRelatedLinks->relatedLinks($args);
}

function wp_get_related_links( $args = array() ) {
	// Instantiate our class
	$manualRelatedLinks = manualRelatedLinks::getInstance();
	return $manualRelatedLinks->getrelatedLinks($args);
}

if ( !function_exists('get_memory_limit') ) {
	function get_memory_limit() {
		$limit = ini_get('memory_limit');
		$symbol = array('B', 'K', 'M', 'G');
		$numLimit = (int) $limit;
		$units = str_replace($numLimit, '', $limit);

		if ( empty($units) ) {
			return $numLimit;
		} else {
			return $numLimit * pow(1024, array_search(strtoupper($units[0]), $symbol));
		}
	}
}

// Instantiate our class
$manualRelatedLinks = manualRelatedLinks::getInstance();

/**
 * Add filters and actions
 */
add_action( 'admin_menu', array( $manualRelatedLinks, 'admin_menu' ) );
add_action( 'save_post', array( $manualRelatedLinks, 'addMetaData' ) );

/**
 * For use with debugging
 */
if ( !function_exists('dump') ) {
	function dump($v, $title = '') {
		if (!empty($title)) {
			echo '<h4>' . htmlentities($title) . '</h4>';
		}
		echo '<pre>' . htmlentities(print_r($v, true)) . '</pre>';
	}
}
