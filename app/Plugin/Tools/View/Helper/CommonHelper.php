<?php
App::uses('AppHelper', 'View/Helper');
App::uses('CommonComponent', 'Tools.Controller/Component');
App::uses('Hash', 'Utility');

/**
 * All site-wide necessary stuff for the view layer
 */
class CommonHelper extends AppHelper {

	public $helpers = ['Session', 'Html'];

	/**
	 * Display all flash messages.
	 *
	 * TODO: export div wrapping method (for static messaging on a page)
	 *
	 * @param array $types Types to output. Defaults to all if none are specified.
	 * @return string HTML
	 * @deprecated Use FlashHelper::flash() instead.
	 */
	public function flash(array $types = []) {
		// Get the messages from the session
		$messages = (array)$this->Session->read('messages');
		$cMessages = (array)Configure::read('messages');
		if (!empty($cMessages)) {
			$messages = (array)Hash::merge($messages, $cMessages);
		}
		$html = '';
		if (!empty($messages)) {
			$html = '<div class="flash-messages flashMessages">';

			if ($types) {
				foreach ($types as $type) {
					// Add a div for each message using the type as the class.
					foreach ($messages as $messageType => $msgs) {
						if ($messageType !== $type) {
							continue;
						}
						foreach ((array)$msgs as $msg) {
							$html .= $this->_message($msg, $messageType);
						}
					}
				}
			} else {
				foreach ($messages as $messageType => $msgs) {
					foreach ((array)$msgs as $msg) {
						$html .= $this->_message($msg, $messageType);
					}
				}
			}
			$html .= '</div>';
			if ($types) {
				foreach ($types as $type) {
					CakeSession::delete('messages.' . $type);
					Configure::delete('messages.' . $type);
				}
			} else {
				CakeSession::delete('messages');
				Configure::delete('messages');
			}
		}

		return $html;
	}

	/**
	 * Outputs a single flashMessage directly.
	 * Note that this does not use the Session.
	 *
	 * @param string $message String to output.
	 * @param string $type Type (success, warning, error, info)
	 * @param bool $escape Set to false to disable escaping.
	 * @return string HTML
	 * @deprecated Use FlashHelper::flashMessage() instead.
	 */
	public function flashMessage($msg, $type = 'info', $escape = true) {
		$html = '<div class="flash-messages flashMessages">';
		if ($escape) {
			$msg = h($msg);
		}
		$html .= $this->_message($msg, $type);
		$html .= '</div>';
		return $html;
	}

	/**
	 * Formats a message
	 *
	 * @param string $msg Message to output.
	 * @param string $type Type that will be formatted to a class tag.
	 * @return string
	 */
	protected function _message($msg, $type) {
		if (!empty($msg)) {
			return '<div class="message' . (!empty($type) ? ' ' . $type : '') . '">' . $msg . '</div>';
		}
		return '';
	}

	/**
	 * Add a message on the fly
	 *
	 * @param string $msg
	 * @param string $class
	 * @return void
	 * @deprecated Use FlashHelper::addTransientMessage() instead.
	 */
	public function addFlashMessage($msg, $class = null) {
		CommonComponent::transientFlashMessage($msg, $class);
	}

	/**
	 * CommonHelper::transientFlashMessage()
	 *
	 * @param mixed $msg
	 * @param mixed $class
	 * @return void
	 * @deprecated Use FlashHelper::addTransientMessage() instead.
	 */
	public function transientFlashMessage($msg, $class = null) {
		$this->addFlashMessage($msg, $class);
	}

	/**
	 * Escape text with some more automagic
	 * TODO: move into TextExt?
	 *
	 * @param string $text
	 * @param array $options
	 * @return string processedText
	 * - nl2br: true/false (defaults to true)
	 * - escape: false prevents h() and space transformation (defaults to true)
	 * - tabsToSpaces: int (defaults to 4)
	 */
	public function esc($text, $options = []) {
		if (!isset($options['escape']) || $options['escape'] !== false) {
			//$text = str_replace(' ', '&nbsp;', h($text));
			$text = h($text);
			// try to fix indends made out of spaces
			$text = explode("\n", $text);
			foreach ($text as $key => $t) {
				$i = 0;
				while (!empty($t[$i]) && $t[$i] === ' ') {
					$i++;
				}
				if ($i > 0) {
					$t = str_repeat('&nbsp;', $i) . substr($t, $i);
					$text[$key] = $t;
				}
			}
			$text = implode("\n", $text);
			$esc = true;
		}
		if (!isset($options['nl2br']) || $options['nl2br'] !== false) {
			$text = nl2br($text);
		}
		if (!isset($options['tabsToSpaces'])) {
			$options['tabsToSpaces'] = 4;
		}
		if (!empty($options['tabsToSpaces'])) {
			$text = str_replace("\t", str_repeat(!empty($esc) ? '&nbsp;' : ' ', $options['tabsToSpaces']), $text);
		}

		return $text;
	}

	/**
	 * Alternates between two or more strings.
	 *
	 * echo CommonHelper::alternate('one', 'two'); // "one"
	 * echo CommonHelper::alternate('one', 'two'); // "two"
	 * echo CommonHelper::alternate('one', 'two'); // "one"
	 *
	 * Note that using multiple iterations of different strings may produce
	 * unexpected results.
	 * TODO: move to booststrap/lib!!!
	 *
	 * @param string $string Strings to alternate between
	 * @return string
	 */
	public static function alternate() {
		static $i;

		if (func_num_args() === 0) {
			$i = 0;
			return '';
		}

		$args = func_get_args();
		return $args[($i++ % count($args))];
	}

	/**
	 * Auto-pluralizing a word using the Inflection class
	 * //TODO: move to lib or bootstrap
	 *
	 * @param string $singular The string to be pl.
	 * @param int $count
	 * @return string "member" or "members" OR "Mitglied"/"Mitglieder" if autoTranslate TRUE
	 */
	public function asp($singular, $count, $autoTranslate = false) {
		if ((int)$count !== 1) {
			$pural = Inflector::pluralize($singular);
		} else {
			$pural = null; // No pluralization necessary
		}
		return $this->sp($singular, $pural, $count, $autoTranslate);
	}

	/**
	 * Manual pluralizing a word using the Inflection class
	 *
	 * I18n will be done using default domain.
	 *
	 * @param string $singular
	 * @param string $plural
	 * @param int $count
	 * @return string result
	 */
	public function sp($singular, $plural, $count, $autoTranslate = false) {
		if ((int)$count !== 1) {
			$result = $plural;
		} else {
			$result = $singular;
		}

		if ($autoTranslate) {
			$result = __($result);
		}
		return $result;
	}

	/**
	 * Convenience method for clean ROBOTS allowance
	 *
	 * @param string|array $type - private/public or array of (noindex,nofollow,noarchive,...)
	 * @return string HTML
	 */
	public function metaRobots($type = null) {
		if ($type === null && ($meta = Configure::read('Config.robots')) !== null) {
			$type = $meta;
		}
		$content = [];
		if ($type === 'public') {
			$content['robots'] = ['index', 'follow', 'noarchive'];
		} elseif (is_array($type)) {
			$content['robots'] = $type;
		} else {
			$content['robots'] = ['noindex', 'nofollow', 'noarchive'];
		}

		$return = '<meta name="robots" content="' . implode(',', $content['robots']) . '"/>';
		return $return;
	}

	/**
	 * Convenience method for clean meta name tags
	 *
	 * @param string $name: author, date, generator, revisit-after, language
	 * @param mixed $content: if array, it will be seperated by commas
	 * @return string HTML Markup
	 */
	public function metaName($name, $content = null) {
		if (empty($name) || empty($content)) {
			return '';
		}

		$content = (array)$content;
		$return = '<meta name="' . $name . '" content="' . implode(', ', $content) . '"/>';
		return $return;
	}

	/**
	 * Convenience method for meta description
	 *
	 * @param string $content
	 * @param string $language (iso2: de, en-us, ...)
	 * @param array $additionalOptions
	 * @return string HTML Markup
	 */
	public function metaDescription($content, $language = null, $options = []) {
		if (!empty($language)) {
			$options['lang'] = mb_strtolower($language);
		} elseif ($language !== false) {
			$options['lang'] = Configure::read('Config.locale');
		}
		return $this->Html->meta('description', $content, $options);
	}

	/**
	 * Convenience method to output meta keywords
	 *
	 * @param string|array $keywords
	 * @param string $language (iso2: de, en-us, ...)
	 * @param bool $escape
	 * @return string HTML Markup
	 */
	public function metaKeywords($keywords = null, $language = null, $escape = true) {
		if ($keywords === null) {
			$keywords = Configure::read('Config.keywords');
		}
		if (is_array($keywords)) {
			$keywords = implode(', ', $keywords);
		}
		if ($escape) {
			$keywords = h($keywords);
		}
		if (!empty($language)) {
			$options['lang'] = mb_strtolower($language);
		} elseif ($language !== false) {
			$options['lang'] = Configure::read('Config.locale');
		}
		return $this->Html->meta('keywords', $keywords, $options);
	}

	/**
	 * Convenience function for "canonical" SEO links
	 *
	 * @param mixed $url
	 * @param bool $full
	 * @return string HTML Markup
	 */
	public function metaCanonical($url = null, $full = false) {
		$canonical = $this->Html->url($url, $full);
		$options = ['rel' => 'canonical', 'type' => null, 'title' => null];
		return $this->Html->meta('canonical', $canonical, $options);
	}

	/**
	 * Convenience method for "alternate" SEO links
	 *
	 * @param mixed $url
	 * @param mixed $lang (lang(iso2) or array of langs)
	 * lang: language (in ISO 6391-1 format) + optionally the region (in ISO 3166-1 Alpha 2 format)
	 * - de
	 * - de-ch
	 * etc
	 * @return string HTML Markup
	 */
	public function metaAlternate($url, $lang, $full = false) {
		//$canonical = $this->Html->url($url, $full);
		$url = $this->Html->url($url, $full);
		//return $this->Html->meta('canonical', $canonical, array('rel'=>'canonical', 'type'=>null, 'title'=>null));
		$lang = (array)$lang;
		$res = [];
		foreach ($lang as $language => $countries) {
			if (is_numeric($language)) {
				$language = '';
			} else {
				$language .= '-';
			}
			$countries = (array)$countries;
			foreach ($countries as $country) {
				$l = $language . $country;
				$options = ['rel' => 'alternate', 'hreflang' => $l, 'type' => null, 'title' => null];
				$res[] = $this->Html->meta('alternate', $url, $options) . PHP_EOL;
			}
		}
		return implode('', $res);
	}

	/**
	 * Convenience method for META Tags
	 *
	 * @param mixed $url
	 * @param string $title
	 * @return string HTML Markup
	 */
	public function metaRss($url, $title = null) {
		$tags = [
			'meta' => '<link rel="alternate" type="application/rss+xml" title="%s" href="%s"/>',
		];
		if (empty($title)) {
			$title = __d('tools', 'Subscribe to this feed');
		} else {
			$title = h($title);
		}

		return sprintf($tags['meta'], $title, $this->url($url));
	}

	/**
	 * Convenience method for META Tags
	 *
	 * @param string $type
	 * @param string $content
	 * @return string HTML Markup
	 */
	public function metaEquiv($type, $value, $escape = true) {
		$tags = [
			'meta' => '<meta http-equiv="%s"%s/>',
		];
		if ($value === null) {
			return '';
		}
		if ($escape) {
			$value = h($value);
		}
		return sprintf($tags['meta'], $type, ' content="' . $value . '"');
	}

	/**
	 * (example): array(x, Tools|y, Tools.Jquery|jquery/sub/z)
	 * => x is in webroot/
	 * => y is in plugins/tools/webroot/
	 * => z is in plugins/tools/packages/jquery/files/jquery/sub/
	 *
	 * @return string HTML Markup
	 * @deprecated Use AssetCompress plugin instead
	 */
	public function css($files, array $options = []) {
		$files = (array)$files;
		$pieces = [];
		foreach ($files as $file) {
			$pieces[] = 'file=' . $file;
		}
		if ($v = Configure::read('Config.layout_v')) {
			$pieces[] = 'v=' . $v;
		}
		$string = implode('&', $pieces);
		return $this->Html->css('/css.php?' . $string, $options);
	}

	/**
	 * (example): array(x, Tools|y, Tools.Jquery|jquery/sub/z)
	 * => x is in webroot/
	 * => y is in plugins/tools/webroot/
	 * => z is in plugins/tools/packages/jquery/files/jquery/sub/
	 *
	 * @return string HTML Markup
	 * @deprecated Use AssetCompress plugin instead
	 */
	public function script($files, array $options = []) {
		$files = (array)$files;
		foreach ($files as $file) {
			$pieces[] = 'file=' . $file;
		}
		if ($v = Configure::read('Config.layout_v')) {
			$pieces[] = 'v=' . $v;
		}
		$string = implode('&', $pieces);
		return $this->Html->script('/js.php?' . $string, $options);
	}

	/**
	 * Still necessary?
	 *
	 * @param array $fields
	 * @return string HTML
	 */
	public function displayErrors($fields = []) {
		$res = '';
		if (!empty($this->validationErrors)) {
			if ($fields === null) { # catch ALL
				foreach ($this->validationErrors as $alias => $error) {
					list($alias, $fieldname) = explode('.', $error);
					$this->validationErrors[$alias][$fieldname];
				}
			} elseif (!empty($fields)) {
				foreach ($fields as $field) {
					list($alias, $fieldname) = explode('.', $field);

					if (!empty($this->validationErrors[$alias][$fieldname])) {
						$res .= $this->_renderError($this->validationErrors[$alias][$fieldname]);
					}
				}
			}
		}
		return $res;
	}

	protected function _renderError($error, $escape = true) {
		if ($escape !== false) {
			$error = h($error);
		}
		return '<div class="error-message">' . $error . '</div>';
	}

	/**
	 * Check if session works due to allowed cookies
	 *
	 * @param bool Success
	 */
	public function sessionCheck() {
		return !CommonComponent::cookiesDisabled();
		/*
		if (!empty($_COOKIE) && !empty($_COOKIE[Configure::read('Session.cookie')])) {
			return true;
		}
		return false;
		*/
	}

	/**
	 * Display warning if cookies are disallowed (and session won't work)
	 *
	 * @return string HTML
	 */
	public function sessionCheckAlert() {
		if ($this->sessionCheck()) {
			return '';
		}
		return '<div class="cookieWarning">' . __d('tools', 'Please enable cookies') . '</div>';
	}

	/**
	 * Prevents site being opened/included by others/websites inside frames
	 *
	 * @return string
	 */
	public function framebuster() {
		return $this->Html->scriptBlock('
if (top!=self) top.location.ref=self.location.href;
');
	}

	/**
	 * Currenctly only alerts on IE6/IE7
	 * options
	 * - engine (js, jquery)
	 * - escape
	 * needs the id element to be a present (div) container in the layout
	 *
	 * @return string
	 */
	public function browserAlert($id, $message, $options = []) {
		$engine = 'js';

		if (!isset($options['escape']) || $options['escape'] !== false) {
			$message = h($message);
		}
		return $this->Html->scriptBlock('
// Returns the version of Internet Explorer or a -1
function getInternetExplorerVersion() {
	var rv = -1; // Return value assumes failure.
	if (navigator.appName === "Microsoft Internet Explorer") {
	var ua = navigator.userAgent;
	var re = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
	if (re.exec(ua) != null)
		rv = parseFloat( RegExp.$1 );
	}
	return rv;
}

if ((document.all) && (navigator.appVersion.indexOf("MSIE 7.") != -1) || typeof document.body.style.maxHeight == \'undefined\') {
	document.getElementById(\'' . $id . '\').innerHTML = \'' . $message . '\';
}
/*
jQuery(document).ready(function() {
	if ($.browser.msie && $.browser.version.substring(0,1) < 8) {
		document.getElementById(\'' . $id . '\').innerHTML = \'' . $message . '\';
	}
});
*/
');
	}

	/**
	 * In noscript tags:
	 * - link which should not be followed by bots!
	 * - "pseudo"image which triggers log
	 *
	 * @return string
	 */
	public function honeypot($noFollowUrl, $noscriptUrl = []) {
		$res = '<div class="invisible" style="display:none"><noscript>';
		$res .= $this->Html->defaultLink('Email', $noFollowUrl, ['rel' => 'nofollow']);

		if (!empty($noscriptUrl)) {
			$res .= BR . $this->Html->image($this->Html->defaultUrl($noscriptUrl, true)); //$this->Html->link($noscriptUrl);
		}

		$res .= '</noscript></div>';
		return $res;
	}

	/**
	 * Print js-visit-stats-link to layout
	 * uses Piwik open source statistics framework
	 *
	 * @return string
	 * @deprecated Use element instead
	 */

	public function visitStats($viewPath = null) {
		$res = '';
		if (!defined('HTTP_HOST_LIVESERVER')) {
			return '';
		}
		if (HTTP_HOST == HTTP_HOST_LIVESERVER && (int)Configure::read('Config.tracking') === 1) {
			$trackingUrl = Configure::read('Config.tracking_url');
			if (empty($trackingUrl)) {
				$trackingUrl = 'visit_stats';
			}
			$error = false;
			if (!empty($viewPath) && $viewPath === 'errors') {
				$error = true;
			}
			$res .= '
<script type="text/javascript">
var pkBaseURL = (("https:" == document.location.protocol) ? "https://' . HTTP_HOST . '/' . $trackingUrl . '/" : "http://' . HTTP_HOST . '/' . $trackingUrl . '/");
document.write(unescape("%3Cscript src=\'" + pkBaseURL + "piwik.js\' type=\'text/javascript\'%3E%3C/script%3E"));
</script>
<script type="text/javascript">
try {
var piwikTracker = Piwik.getTracker(pkBaseURL + "piwik.php", 1);
piwikTracker.trackPageView();
piwikTracker.enableLinkTracking();
' . ($error ? 'piwikTracker.setDocumentTitle(\'404/URL = \'+encodeURIComponent(document.location.pathname+document.location.search) + \'/From = \' + encodeURIComponent(document.referrer));' : '') . '
} catch( err ) {}
</script>
<noscript><p>' . $this->visitStatsImg() . '</p></noscript>
';
		}
		return $res;
	}

	/**
	 * Non js browsers
	 *
	 * @return string
	 * @deprecated Use element instead
	 */
	public function visitStatsImg($trackingUrl = null) {
		if (empty($trackingUrl)) {
			$trackingUrl = Configure::read('Config.tracking_url');
		}
		if (empty($trackingUrl)) {
			$trackingUrl = 'visit_stats';
		}
		return '<img src="' . Router::url('/', true) . $trackingUrl . '/piwik.php?idsite=1" style="border:0" alt=""/>';
	}

	/**
	 * Checks if a role is in the current users session
	 *
	 * @param array|null $roles Necessary right(s) as array - or a single one as string possible
	 * @return array
	 * @deprecated - use Auth class instead
	 */
	public function roleNames($sessionRoles = null) {
		$tmp = [];

		if ($sessionRoles === null) {
			$sessionRoles = $this->Session->read('Auth.User.Role');
		}

		$roles = Cache::read('User.Role');

		if (empty($roles) || !is_array($roles)) {
			$Role = ClassRegistry::init('Role');
			$roles = $Role->getActive('list');
			Cache::write('User.Role', $roles);
		}
		if (!empty($sessionRoles)) {
			if (is_array($sessionRoles)) {
				foreach ($sessionRoles as $sessionRole) {
					if (!$sessionRole) {
						continue;
					}
					if (array_key_exists((int)$sessionRole, $roles)) {
						$tmp[$sessionRole] = $roles[(int)$sessionRole];
					}
				}
			} else {
				if (array_key_exists($sessionRoles, $roles)) {
					$tmp[$sessionRoles] = $roles[$sessionRoles];
				}
			}
		}

		return $tmp;
	}

	/**
	 * Display Roles separated by Commas
	 *
	 * @deprecated - use Auth class instead
	 */
	public function displayRoles($sessionRoles = null, $placeHolder = '---') {
		$roles = $this->roleNames($sessionRoles);
		if (!empty($roles)) {
			return implode(', ', $roles);
		}
		return $placeHolder;
	}

	/**
	 * Takes int / array(int) and finds the role name to it
	 *
	 * @return array roles
	 * @deprecated - use Auth class instead
	 */
	public function roleNamesTranslated($value) {
		if (empty($value)) {
			return [];
		}
		$ret = [];
		$translate = (array)Configure::read('Role');
		if (is_array($value)) {
			foreach ($value as $k => $v) {
				$ret[$v] = __d('tools', $translate[$v]);
			}
		} else {
			$ret[$value] = __d('tools', $translate[$value]);
		}
		return $ret;
	}

}
