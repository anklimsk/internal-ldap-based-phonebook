<?php
App::uses('TextHelper', 'View/Helper');
App::uses('StringTemplate', 'Tools.View');

/**
 * Format helper with basic html snippets
 *
 * TODO: make snippets more "css and background image" (instead of inline img links)
 *
 * @author Mark Scherer
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class FormatHelper extends TextHelper {

	/**
	 * Other helpers used by FormHelper
	 *
	 * @var array
	 */
	public $helpers = ['Html', 'Tools.Numeric'];

	public $template;

	protected $_defaultConfig = [
		'fontIcons' => false, // Defaults to false for BC
		'iconNamespace' => 'fa',  // Used to be icon
	];

	public function __construct(View $View, $config = []) {
		$config += $this->_defaultConfig;

		if ($config['fontIcons'] === true) {
			$config['fontIcons'] = (array)Configure::read('Format.fontIcons');
			if ($namespace = Configure::read('Format.iconNamespace')) {
				$config['iconNamespace'] = $namespace;
			}
		}

		$templates = (array)Configure::read('Format.templates') + [
			'icon' => '<i class="{{class}}" title="{{title}}" data-placement="bottom" data-toggle="tooltip"></i>',
		];
		if (!isset($this->template)) {
			$this->template = new StringTemplate($templates);
		}

		parent::__construct($View, $config);
	}

	/**
	 * jqueryAccess: {id}Pro, {id}Contra
	 *
	 * @return string
	 */
	public function thumbs($id, $inactive = false, $inactiveTitle = null) {
		$status = 'Active';
		$upTitle = __d('tools', 'consentThis');
		$downTitle = __d('tools', 'dissentThis');
		if ($inactive === true) {
			$status = 'Inactive';
			$upTitle = $downTitle = !empty($inactiveTitle) ? $inactiveTitle : __d('tools', 'alreadyVoted');
		}

		if ($this->settings['fontIcons']) {
			// TODO: Return proper font icons
			// fa-thumbs-down
			// fa-thumbs-up
		}

		$ret = '<div class="thumbsUpDown">';
		$ret .= '<div id="' . $id . 'Pro' . $status . '" rel="' . $id . '" class="thumbUp up' . $status . '" title="' . $upTitle . '"></div>';
		$ret .= '<div id="' . $id . 'Contra' . $status . '" rel="' . $id . '" class="thumbDown down' . $status . '" title="' . $downTitle . '"></div>';
		$ret .= '<br class="clear"/>';
		$ret .=	'</div>';
		return $ret;
	}

	/**
	 * Display neighbor quicklinks
	 *
	 * @param array $neighbors (containing prev and next)
	 * @param string $field: just field or Model.field syntax
	 * @param array $options:
	 * - name: title name: next{Record} (if none is provided, "record" is used - not translated!)
	 * - slug: true/false (defaults to false)
	 * - titleField: field or Model.field
	 * @return string
	 */
	public function neighbors($neighbors, $field, $options = []) {
		if (mb_strpos($field, '.') !== false) {
			$fieldArray = explode('.', $field, 2);
			$alias = $fieldArray[0];
			$field = $fieldArray[1];
		}

		if (empty($alias)) {
			if (!empty($neighbors['prev'])) {
				$modelNames = array_keys($neighbors['prev']);
				$alias = $modelNames[0];
			} elseif (!empty($neighbors['next'])) {
				$modelNames = array_keys($neighbors['next']);
				$alias = $modelNames[0];
			}
		}
		if (empty($alias)) {
			throw new InternalErrorException('Invalid neighbors setup');
		}

		$name = 'Record'; // Translation further down!
		if (!empty($options['name'])) {
			$name = ucfirst($options['name']);
		}

		$prevSlug = $nextSlug = null;
		if (!empty($options['slug'])) {
			if (!empty($neighbors['prev'])) {
				$prevSlug = Inflector::slug($neighbors['prev'][$alias][$field], '-');
			}
			if (!empty($neighbors['next'])) {
				$nextSlug = Inflector::slug($neighbors['next'][$alias][$field], '-');
			}
		}
		$titleAlias = $alias;
		$titleField = $field;
		if (!empty($options['titleField'])) {
			if (mb_strpos($options['titleField'], '.') !== false) {
				$fieldArray = explode('.', $options['titleField'], 2);
				$titleAlias = $fieldArray[0];
				$titleField = $fieldArray[1];
			} else {
				$titleField = $options['titleField'];
			}
		}
		if (!isset($options['escape']) || $options['escape'] === false) {
			$titleField = h($titleField);
		}

		$ret = '<div class="next-prev-navi nextPrevNavi">';
		if (!empty($neighbors['prev'])) {
			$url = [$neighbors['prev'][$alias]['id'], $prevSlug];
			if (!empty($options['url'])) {
				$url += $options['url'];
			}

			$ret .= $this->Html->link($this->cIcon(ICON_PREV, ['title' => false]) . '&nbsp;' . __d('tools', 'prev' . $name), $url, ['escape' => false, 'title' => $neighbors['prev'][$titleAlias][$titleField]]);
		} else {
			$ret .= $this->cIcon(ICON_PREV_DISABLED, ['title' => __d('tools', 'noPrev' . $name)]) . '&nbsp;' . __d('tools', 'prev' . $name);
		}
		$ret .= '&nbsp;&nbsp;';
		if (!empty($neighbors['next'])) {
			$url = [$neighbors['next'][$alias]['id'], $prevSlug];
			if (!empty($options['url'])) {
				$url += $options['url'];
			}

			$ret .= $this->Html->link($this->cIcon(ICON_NEXT, ['title' => false]) . '&nbsp;' . __d('tools', 'next' . $name), $url, ['escape' => false, 'title' => $neighbors['next'][$titleAlias][$titleField]]);
		} else {
			$ret .= $this->cIcon(ICON_NEXT_DISABLED, ['title' => __d('tools', 'noNext' . $name)]) . '&nbsp;' . __d('tools', 'next' . $name);
		}
		$ret .= '</div>';
		return $ret;
	}

	/**
	 * Allows icons to be added on the fly
	 * NOTE: overriding not allowed by default
	 *
	 * @return void
	 * @deprecated Try to use font icons and templates with icon()
	 */
	public function addIcon($name = null, $pic = null, $title = null, $allowOverride = false) {
		if ($allowOverride === true || ($allowOverride !== true && !array_key_exists($name, $this->icons))) {
			if (!empty($name) && !empty($pic)) {
				$this->icons[$name] = ['pic' => strtolower($pic), 'title' => (!empty($title) ? $title : '')];
			}
		}
	}

	const GENDER_FEMALE = 2;
	const GENDER_MALE = 1;

	/**
	 * Displays gender icon
	 *
	 * @return string
	 */
	public function genderIcon($value = null) {
		$value = (int)$value;
		if ($value == static::GENDER_FEMALE) {
			$icon =	$this->icon('genderFemale', [], ['class' => 'gender']);
		} elseif ($value == static::GENDER_MALE) {
			$icon =	$this->icon('genderMale', [], ['class' => 'gender']);
		} else {
			$icon =	$this->icon('genderUnknown', [], ['class' => 'gender']);
		}
		return $icon;
	}

	/**
	 * Returns img from customImgFolder
	 *
	 * @param string $folder
	 * @param string $icon
	 * @param bool $checkExists
	 * @param array $options (ending [default: gif])
	 * @return string
	 * @deprecated Try to use font icons or move functionality into own helper.
	 */
	public function customIcon($folder, $icon = null, $checkExist = false, $options = [], $attr = []) {
		$attachment = 'default';
		$ending = 'gif';
		$image = null;

		if (!empty($options)) {
			if (!empty($options['ending'])) {
				$ending = $options['ending'];
			}

			if (!empty($options['backend'])) {
				$attachment = 'backend';
			}
		}

		if (empty($icon)) {
		} elseif ($checkExist === true && !file_exists(PATH_CONTENT . $folder . DS . $icon . '.' . $ending)) {
		} else {
			$image = $icon;
		}
		if ($image === null) {
			return $this->Html->image(IMG_ICONS . 'custom' . '/' . $folder . '_' . $attachment . '.' . $ending, $attr);
		}
		return $this->Html->image(IMG_CONTENT . $folder . '/' . $image . '.' . $ending, $attr);
	}

	/**
	 * @param value
	 * @param array $options
	 * - max (3/5, defaults to 5)
	 * - normal: display an icon for normal as well (defaults to false)
	 * - map: array (manually map values, if you use 1 based values no need for that)
	 * - title, alt, ...
	 * @return string html
	 * @deprecated Try to use font icons or move functionality into own helper.
	 */
	public function priorityIcon($value, $options = []) {
		$defaults = [
			'max' => 5,
			'normal' => false,
			'map' => [],
			'css' => true,
		];
		$options += $defaults;
		extract($options);

		$matching = [
			1 => 'low',
			2 => 'lower',
			3 => 'normal',
			4 => 'higher',
			5 => 'high'
		];

		if (!empty($map)) {
			$value = $map[$value];
		}
		if (!$normal && $value == ($max + 1) / 2) {
			return '';
		}

		if ($max != 5) {
			if ($value == 2) {
				$value = 3;
			} elseif ($value == 3) {
				$value = 5;
			}
		}

		$attr = [
			'class' => 'prio-' . $matching[$value],
			'title' => __d('tools', 'prio' . ucfirst($matching[$value])),
		];
		if (!$css) {
			$attr['alt'] = $matching[$value];
		}
		$attr = array_merge($attr, array_diff_key($options, $defaults));

		if ($css) {
			$html = $this->Html->tag('div', '&nbsp;', $attr);
		} else {
			$icon = 'priority_' . $matching[$value] . '.gif';
			$html = $this->Html->image('icons/' . $icon, $attr);
		}

		return $html;
	}

	/**
	 * Display a font icon (fast and resource-efficient).
	 * Uses http://fontawesome.io/icons/
	 *
	 * Options:
	 * - size (int|string: 1...5 or large)
	 * - rotate (integer: 90, 270, ...)
	 * - spin (booelan: true/false)
	 * - extra (array: muted, light, dark, border)
	 * - pull (string: left, right)
	 *
	 * @param string|array $icon
	 * @param array $options
	 * @param array $attributes
	 * @return string
	 */
	public function fontIcon($icon, array $options = [], array $attributes = []) {
		$defaults = [
			'namespace' => $this->settings['iconNamespace']
		];
		$options += $defaults;
		$icon = (array)$icon;
		$class = [
			$options['namespace']
		];
		foreach ($icon as $i) {
			$class[] = $options['namespace'] . '-' . $i;
		}
		if (!empty($options['extra'])) {
			foreach ($options['extra'] as $i) {
				$class[] = $options['namespace'] . '-' . $i;
			}
		}
		if (!empty($options['size'])) {
			$class[] = $options['namespace'] . '-' . ($options['size'] === 'large' ? 'large' : $options['size'] . 'x');
		}
		if (!empty($options['pull'])) {
			$class[] = 'pull-' . $options['pull'];
		}
		if (!empty($options['rotate'])) {
			$class[] = $options['namespace'] . '-rotate-' . (int)$options['rotate'];
		}
		if (!empty($options['spin'])) {
			$class[] = $options['namespace'] . '-spin';
		}
		return '<i class="' . implode(' ', $class) . '"></i>';
	}

	/**
	 * Quick way of printing default icons.
	 *
	 * I18n will be done using default domain.
	 *
	 * @todo refactor to $type, $options, $attributes
	 *
	 * @param string $type
	 * @param array $t Used to be title, now options array
	 * @param array $a Used to be alt, now attributes array
	 * @param bool $translate Automagic i18n translate [default true = __d('tools', 'xyz')]
	 * @param array $options array('class'=>'','width/height'=>'','onclick=>'') etc
	 * @return string
	 */
	public function icon($type, $t = [], $a = [], $translate = null, $options = []) {
		if (is_array($t)) {
			$translate = isset($t['translate']) ? $t['translate'] : true;
			$options = (array)$a;
			$a = isset($t['alt']) ? $t['alt'] : null; // deprecated
			$t = isset($t['title']) ? $t['title'] : null; // deprecated
		} else {
			trigger_error('Deprecated, use array syntax', E_USER_DEPRECATED);
		}

		if (isset($t) && $t === false) {
			$title = '';
		} else {
			$title = $t;
		}

		if (isset($a) && $a === false) {
			$alt = '';
		} else {
			$alt = $a;
		}

		if (!$this->settings['fontIcons'] || !isset($this->settings['fontIcons'][$type])) {
			if (array_key_exists($type, $this->icons)) {
				$pic = $this->icons[$type]['pic'];
				$title = (isset($title) ? $title : $this->icons[$type]['title']);
				$alt = (isset($alt) ? $alt : preg_replace('/[^a-zA-Z0-9]/', '', $this->icons[$type]['title']));
				if ($translate !== false) {
					$title = __($title);
					$alt = __($alt);
				}
				$alt = '[' . $alt . ']';
			} else {
				$pic = 'pixelspace.gif';
			}
			$defaults = ['title' => $title, 'alt' => $alt, 'class' => 'icon'];
			$newOptions = $options + $defaults;

			return $this->Html->image('icons/' . $pic, $newOptions);
		}

		$options['title'] = $title;
		$options['translate'] = $translate;
		return $this->_fontIcon($type, $options);
	}

	/**
	 * Custom Icons
	 *
	 * I18n will be done using default domain.
	 *
	 * @param string $icon (constant or filename)
	 * @param array $t Used to be title, now options array
	 * - translate, ...
	 * @param array $a Used to be alt, now attributes array
	 * - title, alt, ...
	 * THE REST IS DEPRECATED
	 * @return string
	 */
	public function cIcon($icon, $t = [], $a = [], $translate = true, $options = []) {
		if (is_array($t)) {
			$translate = isset($t['translate']) ? $t['translate'] : true;
			$options = (array)$a;
			$a = isset($t['alt']) ? $t['alt'] : null; // deprecated
			$t = isset($t['title']) ? $t['title'] : null; // deprecated
		} else {
			trigger_error('Deprecated, use array syntax', E_USER_DEPRECATED);
		}

		$type = extractPathInfo('filename', $icon);

		if (!$this->settings['fontIcons'] || !isset($this->settings['fontIcons'][$type])) {
			$title = isset($t) ? $t : ucfirst($type);
			$alt = (isset($a) ? $a : Inflector::slug($title, '-'));
			if ($translate !== false) {
				$title = __($title);
				$alt = __($alt);
			}
			$alt = '[' . $alt . ']';

			$defaults = ['title' => $title, 'alt' => $alt, 'class' => 'icon'];
			$options += $defaults;
			if (substr($icon, 0, 1) !== '/') {
				$icon = 'icons/' . $icon;
			}
			return $this->Html->image($icon, $options);
		}

		$options['title'] = $t;
		$options['translate'] = $translate;
		return $this->_fontIcon($type, $options);
	}

	/**
	 * FormatHelper::_fontIcon()
	 *
	 * I18n will be done using default domain.
	 *
	 * @param string $type
	 * @param array $options
	 * @return string
	 */
	protected function _fontIcon($type, $options) {
		$iconType = $this->settings['fontIcons'][$type];

		$defaults = [
			'class' => $iconType . ' ' . $type
		];
		$options += $defaults;

		if (!isset($options['title'])) {
			$options['title'] = ucfirst($type);
			if ($options['translate'] !== false) {
				$options['title'] = __($options['title']);
			}
		}

		return $this->template->format('icon', $options);
	}

	/**
	 * Print Star Bar
	 * //TODO: 0.5 steps!
	 *
	 * array $options: steps=1/0.5 [default:1]), show_zero=true/false [default:false], title=false/true [default:false]
	 * array $attr: string 'title' (both single and span title empty => 'x of x' for span)
	 * @return string
	 * @deprecated use RatingHelper::stars() instead
	 */
	public function showStars($current, $max, $options = [], $attr = []) {
		$res = '---';

		if (!empty($options['steps']) && $options['steps'] == 0.5) {
			$steps = 0.5;
			$current = ((int)(2 * $current) / 2);
		} else {
			$steps = 1;
			$current = (int)$current;
		}
		$min = (int)$current;
		$max = (int)$max;

		if ((!empty($current) || (!empty($options['show_zero']) && $current == 0)) && (!empty($max)) && $current <= $max) {
			$text = '';
			for ($i = 0; $i < $min; $i++) {
				$attributes = ['alt' => '#', 'class' => 'full'];
				if (!empty($options['title'])) {
					$attributes['title'] = ($i + 1) . '/' . $max;
				} // ?
				$text .= $this->Html->image('icons/star_icon2.gif', $attributes);
			}
			for ($i = $min; $i < $max; $i++) {
				$attributes = ['alt' => '-', 'class' => 'empty'];
				if (!empty($options['title'])) {
					$attributes['title'] = ($i + 1) . '/' . $max;
				} // ?
				if ($steps == 0.5 && $current == $i + 0.5) {
					$text .= $this->Html->image('icons/star_icon2_half.gif', $attributes);
				} else {
					$text .= $this->Html->image('icons/star_icon2_empty.gif', $attributes);
				}
			}

			$attributes = ['class' => 'star-bar starBar'];
			$attributes = array_merge($attributes, $attr);
			if (empty($attributes['title']) && empty($options['title'])) {
				$attributes['title'] = ($current) . ' ' . __d('tools', 'of') . ' ' . $max;
			}

			$res = $this->Html->tag('span', $text, $attributes);
			//$res='<span title="ss" class="starBar">'.$text.'</span>';
		} else {
			if ($max > 3) {
				for ($i = 0; $i < $max - 3; $i++) {
					$res .= '-';
				}
			}
		}

		return $res;
	}

	/**
	 * Display language flags
	 *
	 * @return string HTML
	 * @deprecated Try to use font icons or move functionality into own helper.
	 */
	public function languageFlags() {
		$langs = (array)Configure::read('LanguagesAvailable');
		$supportedLangs = [
			'de' => ['title' => 'Deutsch'],
			'en' => ['title' => 'English'],
			'it' => ['title' => 'Italiano'],
		];

		$languageChange = __d('tools', 'Language') . ': ';

		$languages = [];
		foreach ($langs as $lang) {
			$languages[$lang] = $supportedLangs[$lang];
		}

		if ($sLang = (string)CakeSession::read('Config.language')) {
			$lang = $sLang;
		} else {
			$lang = '';
		}
		$languageChange .= '<span class="country">';
		foreach ($languages as $code => $la) {
			if ($lang === $code) {
				$languageChange .= $this->Html->image('language_flags/' . $code . '.gif', ['alt' => $code, 'title' => $la['title'] . ' (' . __d('tools', 'active') . ')', 'class' => 'country_flag active']) . '';
			} else {
				$languageChange .= $this->Html->link($this->Html->image('language_flags/' . $code . '.gif', ['alt' => $code, 'title' => $la['title'], 'class' => 'country_flag']), '/lang/' . $code, ['escape' => false]) . '';
			}
		}

		$languageChange .= '</span>'; //.__d('tools', '(Translation not complete yet)');
		return $languageChange;
	}

	/**
	 * It is still believed that encoding will stop spam-bots being able to find your email address.
	 * Nevertheless, encoded email address harvester are on the way (http://www.dreamweaverfever.com/experiments/spam/).
	 *
	 * //TODO: move to TextExt?
	 * Helper Function to Obfuscate Email by inserting a span tag (not more! not very secure on its own...)
	 * each part of this mail now does not make sense anymore on its own
	 * (striptags will not work either)
	 *
	 * @param string $mail Email (must be valid - containing one @)
	 * @return string
	 */
	public function encodeEmail($mail) {
		list($mail1, $mail2) = explode('@', $mail);
		$encMail = $this->encodeText($mail1) . '<span>@</span>' . $this->encodeText($mail2);
		return $encMail;
	}

	/**
	 * //TODO: move to TextExt?
	 * Obfuscates Email (works without JS!) to avoid spam bots to get it
	 *
	 * @param string $mail : email to encode
	 * @param string|null $text : optional (if none is given, email will be text as well)
	 * @param array $params : ?subject=y&body=y to be attached to "mailto:xyz"
	 * @param array $attr HTML tag attributes
	 * @return string Safe string with JS generated link around email (and non JS fallback)
	 */
	public function encodeEmailUrl($mail, $text = null, $params = [], $attr = []) {
		if (empty($class)) {
			$class = 'email';
		}

		$defaults = [
			'title' => __d('tools', 'for use in an external mail client'),
			'class' => 'email',
			'escape' => false
		];

		if (empty($text)) {
			$text = $this->encodeEmail($mail);
		}

		$encMail = 'mailto:' . $mail;

		// additionally there could be a span tag in between: email<span syle="display:none"></span>@web.de
		$querystring = '';
		foreach ($params as $key => $val) {
			if ($querystring) {
				$querystring .= "&$key=" . rawurlencode($val);
			} else {
				$querystring = "?$key=" . rawurlencode($val);
			}
		}

		$attr = array_merge($defaults, $attr);

		$xmail = $this->Html->link('', $encMail . $querystring, $attr);
		$xmail1 = mb_substr($xmail, 0, count($xmail) - 5);
		$xmail2 = mb_substr($xmail, -4, 4);

		$len = mb_strlen($xmail1);
		$i = 0;
		while ($i < $len) {
			$c = mt_rand(2, 6);
			$par[] = (mb_substr($xmail1, $i, $c));
			$i += $c;
		}
		$join = implode('\'+ \'', $par);

		return '<script language=javascript><!--
	document.write(\'' . $join . '\');
	//--></script>
		' . $text . '
	<script language=javascript><!--
	document.write(\'' . $xmail2 . '\');
	//--></script>';
	}

	/**
	 * //TODO: move to TextExt?
	 * Encodes Piece of Text (without usage of JS!) to avoid spam bots to get it
	 *
	 * @param string $text Text to encode
	 * @return string (randomly encoded)
	 */
	public function encodeText($text) {
		$encmail = '';
		for ($i = 0; $i < mb_strlen($text); $i++) {
			$encMod = mt_rand(0, 2);
			switch ($encMod) {
			case 0: // None
				$encmail .= mb_substr($text, $i, 1);
				break;
			case 1: // Decimal
				$encmail .= "&#" . ord(mb_substr($text, $i, 1)) . ';';
				break;
			case 2: // Hexadecimal
				$encmail .= "&#x" . dechex(ord(mb_substr($text, $i, 1))) . ';';
				break;
			}
		}
		return $encmail;
	}

	/**
	 * Display yes/no symbol.
	 *
	 * Params $on, $text are deprecated
	 *
	 * @param int|bool $value Value
	 * @param array $options
	 * - on (defaults to 1/true)
	 * - onTitle
	 * - offTitle
	 * @param array $attributes
	 * - title, ...
	 * @return string HTML icon Yes/No
	 */
	public function yesNo($value, $options = [], $attributes = [], $on = 1, $text = false) {
		$defaults = [
			'on' => 1,
			'onTitle' => __d('tools', 'Yes'),
			'offTitle' => __d('tools', 'No'),
			'text' => false
		];

		if (!is_array($options)) {
			$onTitle = $options ?: null;
			$options = [
				'on' => $on,
				'text' => $text,
			];
			if ($onTitle) {
				$options['onTitle'] = $onTitle;
			}
			trigger_error('Deprecated, use array syntax', E_USER_DEPRECATED);
		}
		if (!is_array($attributes)) {
			$options['offTitle'] = $attributes;
		}

		$options += $defaults;

		$sbez = ['0' => @substr($options['offTitle'], 0, 1), '1' => @substr($options['onTitle'], 0, 1)];
		$bez = ['0' => $options['offTitle'], '1' => $options['onTitle']];

		if ($value == $options['on']) {
			$icon = ICON_YES;
			$value = 1;
		} else {
			$icon = ICON_NO;
			$value = 0;
		}

		if ($options['text'] !== false) {
			return $bez[$value];
		}

		$options = ['title' => ($options['onTitle'] === false ? '' : $bez[$value]), 'alt' => $sbez[$value], 'class' => 'icon'];

		if ($this->settings['fontIcons']) {
			return $this->cIcon($icon, ['title' => $options['title']]);
		}
		return $this->Html->image('icons/' . $icon, $options);
	}

	/**
	 * Get URL of a png img of a website (16x16 pixel).
	 *
	 * @param string domain
	 * @return string
	 */
	public function siteIconUrl($domain) {
		if (strpos($domain, 'http') === 0) {
			// Strip protocol
			$pieces = parse_url($domain);
			$domain = $pieces['host'];
		}
		return 'http://www.google.com/s2/favicons?domain=' . $domain;
	}

	/**
	 * Display a png img of a website (16x16 pixel)
	 * if not available, will return a fallback image (a globe)
	 *
	 * @param string $domain (preferably without protocol, e.g. "www.site.com")
	 * @param array $options
	 * @return string
	 */
	public function siteIcon($domain, $options = []) {
		$url = $this->siteIconUrl($domain);
		$options['width'] = 16;
		$options['height'] = 16;
		if (!isset($options['alt'])) {
			$options['alt'] = $domain;
		}
		if (!isset($options['title'])) {
			$options['title'] = $domain;
		}
		return $this->Html->image($url, $options);
	}

	/**
	 * Display text as image
	 *
	 * @param string $text
	 * @param array $options (for generation):
	 * - inline, font, size, background (optional)
	 * @param array $tagAttributes (for image)
	 * @return string result - as image
	 * @deprecated Must be a different helper in the future
	 */
	public function textAsImage($text, $options = [], $tagAttributes = []) {
		/*
		$image = new Imagick();
		//$image->newImage(218, 46, new ImagickPixel('white'));
		$image->setImageCompression(10); // Keine Auswirkung auf Dicke
		$draw = new ImagickDraw();
		$draw->setFont($font);
		$draw->setFontSize(22.0); // Keine Auswirkung auf Dicke
		$draw->setFontWeight(100); // 0-999 Keine Auswirkung auf Dicke
		$draw->annotation(5, 20, $text);
		$image->drawImage($draw);
		$image->setImageResolution(1200, 1200); // Keine Auswirkung auf Dicke
		$image->setImageFormat('gif');
		$image->writeImage(TMP.'x.gif');
		$image->trim($mw,0);
		*/
		$defaults = ['alt' => $text];
		$tagAttributes += $defaults;
		return $this->_textAsImage($text, $options, $tagAttributes);
	}

	/**
	 * @param string $text
	 * @param array $options
	 * @param array $attr
	 * @return string htmlImage tag (or empty string on failure)
	 * @deprecated Must be a different helper in the future
	 */
	public function _textAsImage($text, $options = [], $attr = []) {
		$defaults = ['inline' => true, 'font' => FILES . 'linotype.ttf', 'size' => 18, 'color' => '#7A7166'];
		$options += $defaults;

		if ($options['inline']) { // Inline base 64 encoded
			$folder = CACHE . 'imagick';
		} else {
			$folder = WWW_ROOT . 'img' . DS . 'content' . DS . 'imagick';
		}

		$file = sha1($text . serialize($options)) . '.' . ($options['inline'] || !empty($options['background']) ? 'png' : 'gif');
		if (!file_exists($folder)) {
			mkdir($folder, 0755);
		}
		if (!file_exists($folder . DS . $file)) {
			$command = 'convert -background ' . (!empty($options['background']) ? '"' . $options['background'] . '"' : 'transparent') . ' -font ' . $options['font'] . ' -fill ' . (!empty($options['color']) ? '"' . $options['color'] . '"' : 'transparent') . ' -pointsize ' . $options['size'] . ' label:"' . $text . '" ' . $folder . DS . $file;
			exec($command, $a, $r);
			if ($r !== 0) {
				return '';
			}
		}

		if ($options['inline']) {
			$res = file_get_contents($folder . DS . $file);
			$out = $this->Html->imageFromBlob($res, $attr);
		} else {
			$out = $this->Html->image($this->Html->url('/img/content/imagick/', true) . $file, $attr);
		}
		return $out;
	}

	/**
	 * Display a disabled link tag
	 *
	 * @param string $text
	 * @param array $options
	 * @return string
	 */
	public function disabledLink($text, $options = []) {
		$defaults = ['class' => 'disabledLink', 'title' => __d('tools', 'notAvailable')];
		$options += $defaults;

		return $this->Html->tag('span', $text, $options);
	}

	/**
	 * Generates a pagination count: #1 etc for each pagination record
	 * respects order (ASC/DESC)
	 *
	 * @param array $paginator
	 * @param int $count (current post count on this page)
	 * @param string $dir (ASC/DESC)
	 * @return int
	 */
	public function absolutePaginateCount(array $paginator, $count, $dir = null) {
		if ($dir === null) {
			$dir = 'ASC';
		}

		$currentPage = $paginator['page'];
		$pageCount = $paginator['pageCount'];
		$totalCount = $paginator['count'];

		$limit = $paginator['limit'];
		$step = 1; //$paginator['step'];
		//pr($paginator);

		if ($dir === 'DESC') {
			$currentCount = $count + ($pageCount - $currentPage) * $limit * $step;
			if ($currentPage != $pageCount && $pageCount > 1) {
				$currentCount -= $pageCount * $limit * $step - $totalCount;
			}
		} else {
			$currentCount = $count + ($currentPage - 1) * $limit * $step;
		}

		return $currentCount;
	}

	/**
	 * @param float $progress
	 * @param array $options:
	 * - min, max
	 * - steps
	 * - decimals (how precise should the result be displayed)
	 * @return string HTML
	 * @deprecated Try to use font icons or move to own helper
	 */
	public function progressBar($progress, $options = [], $htmlOptions = []) {
		$defaults = [
			'min' => 0,
			'max' => 100,
			'steps' => 15,
			'decimals' => 1 // TODO: rename to places!!!
		];
		$options += $defaults;

		$current = (((float)$progress / $options['max']) - $options['min']);
		$percent = $current * 100;

		$current *= $options['steps'];

		$options['progress'] = number_format($current, $options['decimals'], null, '');

		$params = Router::queryString($options, [], true);

		$htmlDefaults = [
			'title' => $this->Numeric->format($percent, $options['decimals']) . ' ' . __d('tools', 'Percent'),
			'class' => 'help'];
		$htmlDefaults['alt'] = $htmlDefaults['title'];

		$htmlOptions += $htmlDefaults;
		//return $this->Html->image('/files/progress_bar/index.php'.$params, $htmlOptions);

		return '<img src="' . $this->Html->url('/files') . '/progress_bar/index.php' . $params . '" title="' . $htmlOptions['title'] . '" class="' .
			$htmlOptions['class'] . '" alt="' . $htmlOptions['title'] . '" />';
	}

	/**
	 * FormatHelper::tip()
	 *
	 * @param mixed $type
	 * @param mixed $file
	 * @param mixed $title
	 * @param mixed $icon
	 * @return string
	 * @deprecated Try to use font icons or move to own helper
	 */
	public function tip($type, $file, $title, $icon) {
		return $this->cIcon($icon, ['title' => $title], ['class' => 'tip-' . $type . ' tip' . ucfirst($type) . ' hand', 'rel' => $file]);
	}

	/**
	 * FormatHelper::tipHelp()
	 *
	 * @param mixed $file
	 * @return string
	 * @deprecated Try to use font icons or move to own helper
	 */
	public function tipHelp($file) {
		return $this->tip('help', $file, __d('tools', 'Help'), ICON_HELP);
	}

	/**
	 * Fixes utf8 problems of native php str_pad function
	 * //TODO: move to textext helper?
	 *
	 * @param string $input
	 * @param int $padLength
	 * @param string $padString
	 * @param mixed $padType
	 * @return string input
	 */
	public function pad($input, $padLength, $padString, $padType = STR_PAD_RIGHT) {
		$length = mb_strlen($input);
		if ($padLength - $length > 0) {
			switch ($padType) {
				case STR_PAD_LEFT:
					$input = str_repeat($padString, $padLength - $length) . $input;
					break;
				case STR_PAD_RIGHT:
					$input .= str_repeat($padString, $padLength - $length);
					break;
			}
		}
		return $input;
	}

	/**
	 * Display traffic light for status etc
	 *
	 * @return void
	 * @deprecated Try to use font icons or move to own helper
	 */
	public function statusLight($color = null, $title = null, $alt = null, $options = []) {
		$icons = [
			'green', 'yellow', 'red', 'blue'
			/*
			'red' => array(
				'title'=>'',
				'alt'=>''
			),
			*/
		];

		$icon = (in_array($color, $icons) ? $color : 'blank');

		$defaults = ['title' => (!empty($title) ? $title : ucfirst(__d('tools', 'color' . ucfirst($color)))), 'alt' => (!empty($alt) ? $alt :
			__d('tools', 'color' . ucfirst($color))), 'class' => 'icon help'];
		$options += $defaults;

		return $this->Html->image('icons/status_light_' . $icon . '.gif', $options);
	}

	/**
	 * FormatHelper::onlineIcon()
	 *
	 * @param mixed $modified
	 * @param mixed $options
	 * @return string
	 * @deprecated Try to use font icons or move to own helper
	 */
	public function onlineIcon($modified = null, $options = []) {
		// from low (off) to high (on)
		$icons = ['healthbar0.gif', 'healthbar1.gif', 'healthbar1b.gif', 'healthbar2.gif', 'healthbar3.gif', 'healthbar4.gif', 'healthbar5.gif'];

		// default = offline
		$res = $icons[0]; // inactive

		$time = strtotime($modified);
		$timeAgo = time() - $time; // in seconds

		if ($timeAgo < 180) { // 3min // active
			$res = $icons[6];
		} elseif ($timeAgo < 360) { // 6min
			$res = $icons[5];
		} elseif ($timeAgo < 540) { // 9min
			$res = $icons[4];
		} elseif ($timeAgo < 720) { // 12min
			$res = $icons[3];
		} elseif ($timeAgo < 900) { // 15min
			$res = $icons[2];
		} elseif ($timeAgo < 1080) { // 18min
			$res = $icons[1];
		}

		return $this->Html->image('misc/' . $res, ['style' => 'width: 60px; height: 16px']);
	}

	/**
	 * Returns red colored if not ok
	 *
	 * @param string $value
	 * @param $okValue
	 * @return string Value in HTML tags
	 */
	public function warning($value, $ok = false) {
		if (!$ok) {
			return $this->ok($value, false);
		}
		return $value;
	}

	/**
	 * Returns green on ok, red otherwise
	 *
	 * @todo Remove inline css and make classes better: green=>ok red=>not-ok
	 *
	 * @param mixed $currentValue
	 * @param bool $ok: true/false (defaults to false)
	 * //@param string $comparizonType
	 * //@param mixed $okValue
	 * @return string newValue nicely formatted/colored
	 */
	public function ok($value, $ok = false) {
		if ($ok) {
			$value = '<span class="green" style="color:green">' . $value . '</span>';
		} else {
			$value = '<span class="red" style="color:red">' . $value . '</span>';
		}
		return $value;
	}

	/**
	 * test@test.de becomes t..t@t..t.de
	 *
	 * @param string $email: valid(!) email address
	 * @return string
	 */
	public static function hideEmail($mail) {
		$mailParts = explode('@', $mail, 2);
		$domainParts = explode('.', $mailParts[1], 2);

		$user = mb_substr($mailParts[0], 0, 1) . '..' . mb_substr($mailParts[0], -1, 1);
		$domain = mb_substr($domainParts[0], 0, 1) . '..' . mb_substr($domainParts[0], -1, 1) . '.' . $domainParts[1];
		return $user . '@' . $domain;
	}

	/**
	 * (Intelligent) Shortening of a text string
	 *
	 * @param STRING textstring
	 * @param int chars = max-length
	 * For options array:
	 * @param bool strict (default: FALSE = intelligent shortening, cutting only between whole words)
	 * @param STRING ending (default: '...' no leading whitespace)
	 * @param bool remain_lf (default: false = \n to ' ')
	 * Note: ONLY If intelligent:
	 * - the word supposed to be cut is removed completely (instead of remaining as last one)
	 * - Looses line breaks (for textarea content to work with this)!
	 * @deprecated use truncate instead
	 */
	public function shortenText($textstring, $chars, $options = []) {
		$chars++; // add +1 for correct cut
		$needsEnding = false;

		#Options
		$strict = false;
		$ending = CHAR_HELLIP; //'...';
		$remainLf = false; // not implemented: choose if LF transformed to ' '
		$class = 'help';
		$escape = true;
		$title = '';

		if (!empty($options) && is_array($options)) {
			if (!empty($options['strict']) && ($options['strict'] === true || $options['strict'] === false)) {
				$strict = $options['strict'];
			}
			if (!empty($options['remain_lf']) && ($options['remain_lf'] === true || $options['remain_lf'] === false)) {
				$remainLf = $options['remain_lf'];
			}

			if (isset($options['title'])) {
				$title = $options['title'];
				if ($options['title'] === true) {
					$title = $textstring;
				}
			}
			if (isset($options['class']) && $options['class'] === false) {
				$class = '';
			}

			if (isset($options['ending'])) {
				$ending = (string)$options['ending'];
			}

			if (isset($options['escape'])) {
				$escape = (bool)$options['escape'];
			}
		}

		$textstring = trim($textstring);

		// cut only between whole words
		if ($strict !== true) {
			$completeWordText = $textstring . ' ';
			// transform line breaks to whitespaces (for textarea content etc.)
			$completeWordTextLf = str_replace(LF, ' ', $completeWordText);
			$completeWordText = $completeWordTextLf;
			$completeWordText = substr($completeWordTextLf, 0, $chars);
			// round the text to the previous entire word instead of cutting off part way through a word
			$completeWordText = substr($completeWordText, 0, strrpos($completeWordText, ' '));
		}

		$textEnding = '';
		if ($strict !== true && strlen($completeWordText) > 1) {
			$text = trim($completeWordText);
			// add ending only if result is shorter then original
			if (strlen($text) < strlen(trim($completeWordTextLf))) {
				$textEnding = ' ' . $ending; // additional whitespace as there is a new word added
			}
		} else {
			$text = trim(substr($textstring, 0, $chars));
			// add ending only if result is shorter then original
			if (strlen($text) < strlen($textstring)) {
				$textEnding = $ending;
			}
		}

		if ($escape) {
			$text = h($text);
			$title = h($title);
		}
		$text .= $textEnding;

		#TitleIfTooLong
		if (!empty($title)) {
			$text = '<span ' . (!empty($class) ? 'class="' . $class . '" ' : '') . 'title="' . $title . '">' . $text . '</span>';
		}

		return $text;
	}

	/**
	 * Useful for displaying tabbed (code) content when the default of 8 spaces
	 * inside <pre> is too much. This converts it to spaces for better output.
	 *
	 * Inspired by the tab2space function found at:
	 * @see http://aidan.dotgeek.org/lib/?file=function.tab2space.php
	 * @param string $text
	 * @param int $spaces
	 * @return string
	 */
	public function tab2space($text, $spaces = 4) {
		$spaces = str_repeat(" ", $spaces);
		$text = preg_split("/\r\n|\r|\n/", trim($text));
		$wordLengths = [];
		$wArray = [];

		// Store word lengths
		foreach ($text as $line) {
			$words = preg_split("/(\t+)/", $line, -1, PREG_SPLIT_DELIM_CAPTURE);
			foreach (array_keys($words) as $i) {
				$strlen = strlen($words[$i]);
				$add = isset($wordLengths[$i]) && ($wordLengths[$i] < $strlen);
				if ($add || !isset($wordLengths[$i])) {
					$wordLengths[$i] = $strlen;
				}
			}
			$wArray[] = $words;
		}

		$text = '';

		// Apply padding when appropriate and rebuild the string
		foreach (array_keys($wArray) as $i) {
			foreach (array_keys($wArray[$i]) as $ii) {
				if (preg_match("/^\t+$/", $wArray[$i][$ii])) {
					$wArray[$i][$ii] = str_pad($wArray[$i][$ii], $wordLengths[$ii], "\t");
				} else {
					$wArray[$i][$ii] = str_pad($wArray[$i][$ii], $wordLengths[$ii]);
				}
			}
			$text .= str_replace("\t", $spaces, implode("", $wArray[$i])) . "\n";
		}

		return $text;
	}

	/**
	 * Word Censoring Function
	 *
	 * Supply a string and an array of disallowed words and any
	 * matched words will be converted to #### or to the replacement
	 * word you've submitted.
	 *
	 * @todo Move to Text Helper etc.
	 *
	 * @param string $str The text string
	 * @param string $censored The array of censoered words
	 * @param string|null $replacement The optional replacement value
	 * @return string
	 */
	public function wordCensor($str, $censored, $replacement = null) {
		if (empty($censored)) {
			return $str;
		}
		$str = ' ' . $str . ' ';

		// \w, \b and a few others do not match on a unicode character
		// set for performance reasons. As a result words like ..ber
		// will not match on a word boundary. Instead, we'll assume that
		// a bad word will be bookended by any of these characters.
		$delim = '[-_\'\"`() {}<>\[\]|!?@#%&,.:;^~*+=\/ 0-9\n\r\t]';

		foreach ($censored as $badword) {
			if ($replacement !== null) {
				$str = preg_replace("/({$delim})(" . str_replace('\*', '\w*?', preg_quote($badword, '/')) . ")({$delim})/i", "\\1{$replacement}\\3", $str);
			} else {
				$str = preg_replace_callback(
					"/({$delim})(" . str_replace('\*', '\w*?', preg_quote($badword, '/')) . ")({$delim})/i",
					function ($x) {
						return $x[1] . str_repeat('#', strlen($x[2])) . $x[3];
					},
					$str
				);
			}
		}

		return trim($str);
	}

	/**
	 * Translate a result array into a HTML table
	 *
	 * @todo Move to Text Helper etc.
	 *
	 * @author Aidan Lister <aidan@php.net>
	 * @version 1.3.2
	 * @link http://aidanlister.com/2004/04/converting-arrays-to-human-readable-tables/
	 * @param array $array The result (numericaly keyed, associative inner) array.
	 * @param bool $recursive Recursively generate tables for multi-dimensional arrays
	 * @param string $null String to output for blank cells
	 */
	public function array2table($array, $options = []) {
		$defaults = [
			'null' => '&nbsp;',
			'recursive' => false,
			'heading' => true,
			'escape' => true
		];
		$options += $defaults;

		// Sanity check
		if (empty($array) || !is_array($array)) {
			return false;
		}

		if (!isset($array[0]) || !is_array($array[0])) {
			$array = [$array];
		}

		// Start the table
		$table = "<table>\n";

		if ($options['heading']) {
			// The header
			$table .= "\t<tr>";
			// Take the keys from the first row as the headings
			foreach (array_keys($array[0]) as $heading) {
				$table .= '<th>' . ($options['escape'] ? h($heading) : $heading) . '</th>';
			}
			$table .= "</tr>\n";
		}

		// The body
		foreach ($array as $row) {
			$table .= "\t<tr>";
			foreach ($row as $cell) {
				$table .= '<td>';

				// Cast objects
				if (is_object($cell)) {
					$cell = (array)$cell;
				}

				if ($options['recursive'] && is_array($cell) && !empty($cell)) {
					// Recursive mode
					$table .= "\n" . static::array2table($cell, $options) . "\n";
				} else {
					$table .= (!is_array($cell) && strlen($cell) > 0) ? ($options['escape'] ? h($cell) : $cell) : $options['null'];
				}

				$table .= '</td>';
			}

			$table .= "</tr>\n";
		}

		$table .= '</table>';
		return $table;
	}

	public $icons = [
		'up' => [
			'pic' => ICON_UP,
			'title' => 'Up',
		],
		'down' => [
			'pic' => ICON_DOWN,
			'title' => 'Down',
		],
		'edit' => [
			'pic' => ICON_EDIT,
			'title' => 'Edit',
		],
		'view' => [
			'pic' => ICON_VIEW,
			'title' => 'View',
		],
		'delete' => [
			'pic' => ICON_DELETE,
			'title' => 'Delete',
		],
		'reset' => [
			'pic' => ICON_RESET,
			'title' => 'Reset',
		],
		'help' => [
			'pic' => ICON_HELP,
			'title' => 'Help',
		],
		'loader' => [
			'pic' => 'loader.white.gif',
			'title' => 'Loading...',
		],
		'loader-alt' => [
			'pic' => 'loader.black.gif',
			'title' => 'Loading...',
		],
		'details' => [
			'pic' => ICON_DETAILS,
			'title' => 'Details',
		],
		'use' => [
			'pic' => ICON_USE,
			'title' => 'Use',
		],
		'yes' => [
			'pic' => ICON_YES,
			'title' => 'Yes',
		],
		'no' => [
			'pic' => ICON_NO,
			'title' => 'No',
		],
		// deprecated from here down
		'close' => [
			'pic' => ICON_CLOCK,
			'title' => 'Close',
		],
		'reply' => [
			'pic' => ICON_REPLY,
			'title' => 'Reply',
		],
		'time' => [
			'pic' => ICON_CLOCK,
			'title' => 'Time',
		],
		'check' => [
			'pic' => ICON_CHECK,
			'title' => 'Check',
		],
		'role' => [
			'pic' => ICON_ROLE,
			'title' => 'Role',
		],
		'add' => [
			'pic' => ICON_ADD,
			'title' => 'Add',
		],
		'remove' => [
			'pic' => ICON_REMOVE,
			'title' => 'Remove',
		],
		'email' => [
			'pic' => ICON_EMAIL,
			'title' => 'Email',
		],
		'options' => [
			'pic' => ICON_SETTINGS,
			'title' => 'Options',
		],
		'lock' => [
			'pic' => ICON_LOCK,
			'title' => 'Locked',
		],
		'warning' => [
			'pic' => ICON_WARNING,
			'title' => 'Warning',
		],
		'genderUnknown' => [
			'pic' => 'gender_icon.gif',
			'title' => 'genderUnknown',
		],
		'genderMale' => [
			'pic' => 'gender_icon_m.gif',
			'title' => 'genderMale',
		],
		'genderFemale' => [
			'pic' => 'gender_icon_f.gif',
			'title' => 'genderFemale',
		],
	];

}

// Default icons

if (!defined('ICON_UP')) {
	define('ICON_UP', 'up.gif');
}
if (!defined('ICON_DOWN')) {
	define('ICON_DOWN', 'down.gif');
}
if (!defined('ICON_EDIT')) {
	define('ICON_EDIT', 'edit.gif');
}
if (!defined('ICON_VIEW')) {
	define('ICON_VIEW', 'see.gif');
}
if (!defined('ICON_DELETE')) {
	define('ICON_DELETE', 'delete.gif');
}
if (!defined('ICON_DETAILS')) {
	define('ICON_DETAILS', 'loupe.gif');
}
if (!defined('ICON_OPTIONS')) {
	define('ICON_OPTIONS', 'options.gif');
}
if (!defined('ICON_SETTINGS')) {
	define('ICON_SETTINGS', 'options.gif');
}
if (!defined('ICON_USE')) {
	define('ICON_USE', 'use.gif');
}
if (!defined('ICON_CLOSE')) {
	define('ICON_CLOSE', 'close.gif');
}
if (!defined('ICON_REPLY')) {
	define('ICON_REPLY', 'reply.gif');
}

if (!defined('ICON_RESET')) {
	define('ICON_RESET', 'reset.gif');
}
if (!defined('ICON_HELP')) {
	define('ICON_HELP', 'help.gif');
}
if (!defined('ICON_YES')) {
	define('ICON_YES', 'yes.gif');
}
if (!defined('ICON_NO')) {
	define('ICON_NO', 'no.gif');
}
if (!defined('ICON_CLOCK')) {
	define('ICON_CLOCK', 'clock.gif');
}
if (!defined('ICON_CHECK')) {
	define('ICON_CHECK', 'check.gif');
}
if (!defined('ICON_ROLE')) {
	define('ICON_ROLE', 'role.gif');
}
if (!defined('ICON_ADD')) {
	define('ICON_ADD', 'add.gif');
}
if (!defined('ICON_REMOVE')) {
	define('ICON_REMOVE', 'remove.gif');
}
if (!defined('ICON_EMAIL')) {
	define('ICON_EMAIL', 'email.gif');
}
if (!defined('ICON_LOCK')) {
	define('ICON_LOCK', 'lock.gif');
}
if (!defined('ICON_WARNING')) {
	define('ICON_WARNING', 'warning.png');
}
if (!defined('ICON_MAP')) {
	define('ICON_MAP', 'map.gif');
}
if (!defined('ICON_PREV')) {
	define('ICON_PREV', 'nav_back.png');
}
if (!defined('ICON_NEXT')) {
	define('ICON_NEXT', 'nav_forward.png');
}
if (!defined('ICON_PREV_DISABLED')) {
	define('ICON_PREV_DISABLED', 'nav_back_grey.png');
}
if (!defined('ICON_NEXT_DISABLED')) {
	define('ICON_NEXT_DISABLED', 'nav_forward_grey.png');
}
