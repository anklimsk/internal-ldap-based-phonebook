<?php

App::uses('Sanitize', 'Utility');
App::uses('Router', 'Routing');

/**
 * Main class for all app-wide utility methods
 *
 * @author Mark Scherer
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class Utility {

	/**
	 * Clean implementation of inArray to avoid false positives.
	 *
	 * in_array itself has some PHP flaws regarding cross-type comparison:
	 * - in_array('50x', array(40, 50, 60)) would be true!
	 * - in_array(50, array('40x', '50x', '60x')) would be true!
	 *
	 * @param mixed $needle
	 * @param array $haystack
	 * @return bool Success
	 */
	public static function inArray($needle, $haystack) {
		$strict = !is_numeric($needle);
		return in_array((string)$needle, $haystack, $strict);
	}

	/**
	 * Tokenizes a string using $separator.
	 *
	 * Options
	 * - clean: true/false (defaults to true and removes empty tokens and whitespace)
	 *
	 * @param string $data The data to tokenize
	 * @param string $separator The token to split the data on.
	 * @param array $options
	 * @return array
	 */
	public static function tokenize($data, $separator = ',', array $options = []) {
		$defaults = [
			'clean' => true,
			'callback' => null
		];
		$options += $defaults;
		if (empty($data)) {
			return [];
		}

		$tokens = explode($separator, $data);
		$tokens = array_map('trim', $tokens);

		if ($options['callback']) {
			foreach ($tokens as $key => $token) {
				$tokens[$key] = $options['callback']($token);
			}
		}

		if (empty($tokens) || !$options['clean']) {
			return $tokens;
		}

		foreach ($tokens as $key => $token) {
			if ($token === '') {
				unset($tokens[$key]);
			}
		}

		return $tokens;
	}

	/**
	 * Multibyte analogue of preg_match_all() function. Only that this returns the result.
	 * By default this works properly with UTF8 strings.
	 *
	 * Do not forget to use preg_quote() first on strings that could potentially contain
	 * unescaped characters.
	 *
	 * Note that you still need to add the u modifier (for UTF8) to your pattern yourself.
	 *
	 * Example: /some(.*)pattern/u
	 *
	 * @param string $pattern The pattern to use.
	 * @param string $subject The string to match.
	 * @param int $flags
	 * @param int $offset
	 * @return array Result
	 */
	public static function pregMatchAll($pattern, $subject, $flags = PREG_SET_ORDER, $offset = null) {
		$pattern = substr($pattern, 0, 1) . '(*UTF8)' . substr($pattern, 1);
		preg_match_all($pattern, $subject, $matches, $flags, $offset);
		return $matches;
	}

	/**
	 * Multibyte analogue of preg_match() function. Only that this returns the result.
	 * By default this works properly with UTF8 strings.
	 *
	 * Do not forget to use preg_quote() first on strings that could potentially contain
	 * unescaped characters.
	 *
	 * Note that you still need to add the u modifier (for UTF8) to your pattern yourself.
	 *
	 * Example: /some(.*)pattern/u
	 *
	 * @param string $pattern The pattern to use.
	 * @param string $subject The string to match.
	 * @param int $flags
	 * @param int $offset
	 * @return array Result
	 */
	public static function pregMatch($pattern, $subject, $flags = null, $offset = null) {
		$pattern = substr($pattern, 0, 1) . '(*UTF8)' . substr($pattern, 1);
		preg_match($pattern, $subject, $matches, $flags, $offset);
		return $matches;
	}

	/**
	 * Multibyte analogue of str_split() function.
	 * By default this works properly with UTF8 strings.
	 *
	 * @param string $text
	 * @param int $length
	 * @return array Result
	 */
	public static function strSplit($str, $length = 1) {
		if ($length < 1) {
			return false;
		}
		$result = [];
		$c = mb_strlen($str);
		for ($i = 0; $i < $c; $i += $length) {
			$result[] = mb_substr($str, $i, $length);
		}
		return $result;
	}

	/**
	 * Get the current IP address.
	 *
	 * @param bool $safe
	 * @return string IP address
	 */
	public static function getClientIp($safe = true) {
		if (!$safe && env('HTTP_X_FORWARDED_FOR')) {
			$ipaddr = preg_replace('/(?:,.*)/', '', env('HTTP_X_FORWARDED_FOR'));
		} elseif (!$safe && env('HTTP_CLIENT_IP')) {
			$ipaddr = env('HTTP_CLIENT_IP');
		} else {
			$ipaddr = env('REMOTE_ADDR');
		}
		return trim($ipaddr);
	}

	/**
	 * Get the current referrer if available.
	 *
	 * @param bool $full (defaults to false and leaves the url untouched)
	 * @return string referer (local or foreign)
	 */
	public static function getReferer($full = false) {
		$ref = env('HTTP_REFERER');
		$forwarded = env('HTTP_X_FORWARDED_HOST');
		if ($forwarded) {
			$ref = $forwarded;
		}
		if (empty($ref)) {
			return $ref;
		}
		if ($full) {
			$ref = Router::url($ref, $full);
		}
		return $ref;
	}

	/**
	 * Remove unnessary stuff + add http:// for external urls
	 * TODO: protocol to lower!
	 *
	 * @param string $url
	 * @return string Cleaned Url
	 */
	public static function cleanUrl($url, $headerRedirect = false) {
		if ($url === '' || $url === 'http://' || $url === 'http://www' || $url === 'http://www.') {
			$url = '';
		} else {
			$url = static::autoPrefixUrl($url, 'http://');
		}

		if ($headerRedirect && !empty($url)) {
			$headers = static::getHeaderFromUrl($url);
			if ($headers !== false) {
				$headerString = implode("\n", $headers);

				if ((bool)preg_match('#^HTTP/.*\s+[(301)]+\s#i', $headerString)) {
					foreach ($headers as $header) {
						if (mb_strpos($header, 'Location:') === 0) {
							$url = trim(hDec(mb_substr($header, 9))); // rawurldecode/urldecode ?
						}
					}
				}
			}
		}

		$length = mb_strlen($url);
		while (!empty($url) && mb_strrpos($url, '/') === $length - 1) {
			$url = mb_substr($url, 0, $length - 1);
			$length--;
		}
		return $url;
	}

	/**
	 * A more robust wrapper around for file_exists() which easily
	 * fails to return true for existent remote files.
	 * Per default it allows http/https images to be looked up via urlExists()
	 * for a better result.
	 *
	 * @param string $file File
	 * @return bool Success
	 */
	public static function fileExists($file, $pattern = '~^https?://~i') {
		if (!preg_match($pattern, $file)) {
			return file_exists($file);
		}
		return static::urlExists($file);
	}

	/**
	 * file_exists() does not always work with URLs.
	 * So if you check on strpos(http) === 0 you can use this
	 * to check for URLs instead.
	 *
	 * @param string $url Absolute URL
	 * @return bool Success
	 */
	public static function urlExists($url) {
		$headers = @get_headers($url);
		if ($headers && preg_match('|\b200\b|', $headers[0])) {
			return true;
		}
		return false;
	}

	/**
	 * Utility::getMimeType()
	 *
	 * @param string $file File
	 * @return string Mime type
	 */
	public static function getMimeType($file) {
		if (!function_exists('finfo_open')) {
			throw new InternalErrorException('finfo_open() required - please enable');
		}

		// Treat non local files differently
		$pattern = '~^https?://~i';
		if (preg_match($pattern, $file)) {
			$headers = @get_headers($file);
			if (!preg_match("|\b200\b|", $headers[0])) {
				return '';
			}
			foreach ($headers as $header) {
				if (strpos($header, 'Content-Type:') === 0) {
					return trim(substr($header, 13));
				}
			}
			return '';
		}

		$finfo = finfo_open(FILEINFO_MIME);
		$mimetype = finfo_file($finfo, $file);
		if (($pos = strpos($mimetype, ';')) !== false) {
			$mimetype = substr($mimetype, 0, $pos);
		}
		return $mimetype;
	}

	/**
	 * Parse headers from a specific URL content.
	 *
	 * @param string $url
	 * @return mixed array of headers or FALSE on failure
	 */
	public static function getHeaderFromUrl($url) {
		$url = @parse_url($url);

		if (empty($url)) {
			return false;
		}

		$url = array_map('trim', $url);
		$url['port'] = (!isset($url['port'])) ? '' : (':' . (int)$url['port']);
		$path = (isset($url['path'])) ? $url['path'] : '';

		if (empty($path)) {
			$path = '/';
		}

		$path .= (isset($url['query'])) ? "?$url[query]" : '';

		$defaults = [
			'http' => [
				'header' => "Accept: text/html\r\n" .
					"Connection: Close\r\n" .
					"User-Agent: Mozilla/5.0 (Windows NT 6.2; WOW64)\r\n",
			]
		];
		stream_context_get_default($defaults);

		if (isset($url['host']) && $url['host'] !== gethostbyname($url['host'])) {
			$url = "$url[scheme]://$url[host]$url[port]$path";
			$headers = get_headers($url);
			if (is_array($headers)) {
				return $headers;
			}
		}
		return false;
	}

	/**
	 * Add protocol prefix if necessary (and possible)
	 *
	 * @param string $url
	 */
	public static function autoPrefixUrl($url, $prefix = null) {
		if ($prefix === null) {
			$prefix = 'http://';
		}

		if (($pos = strpos($url, '.')) !== false) {
			if (strpos(substr($url, 0, $pos), '//') === false) {
				$url = $prefix . $url;
			}
		}
		return $url;
	}

	/**
	 * Encode strings with base64_encode and also
	 * replace chars base64 uses that would mess up the url.
	 *
	 * Do not use this for querystrings. Those will escape automatically.
	 * This is only useful for named or passed params.
	 *
	 * @deprecated Use query strings instead
	 * @param string $string Unsafe string
	 * @return string Encoded string
	 */
	public static function urlEncode($string) {
		return str_replace(['/', '='], ['-', '_'], base64_encode($string));
	}

	/**
	 * Decode strings with base64_encode and also
	 * replace back chars base64 uses that would mess up the url.
	 *
	 * Do not use this for querystrings. Those will escape automatically.
	 * This is only useful for named or passed params.
	 *
	 * @deprecated Use query strings instead
	 * @param string $string Safe string
	 * @return string Decoded string
	 */
	public static function urlDecode($string) {
		return base64_decode(str_replace(['-', '_'], ['/', '='], $string));
	}

	/**
	 * Returns true only if all values are true.
	 * //TODO: maybe move to bootstrap?
	 *
	 * @param array $array
	 * @return bool Result
	 */
	public static function logicalAnd($array) {
		if (empty($array)) {
			return false;
		}
		foreach ($array as $result) {
			if (!$result) {
				return false;
			}
		}
		return true;
	}

	/**
	 * Returns true if at least one value is true.
	 * //TODO: maybe move to bootstrap?
	 *
	 * @param array $array
	 * @return bool Result
	 *
	 */
	public static function logicalOr($array) {
		foreach ($array as $result) {
			if ($result) {
				return true;
			}
		}
		return false;
	}

	/**
	 * On non-transaction db connections it will return a deep array of bools instead of bool.
	 * So we need to call this method inside the modified saveAll() method to return the expected single bool there, too.
	 *
	 * @param array
	 * @return bool
	 */
	public static function isValidSaveAll($array) {
		if (empty($array)) {
			return false;
		}
		$ret = true;
		foreach ($array as $key => $val) {
			if (is_array($val)) {
				$ret = $ret & Utility::logicalAnd($val);
			} else {
				$ret = $ret & $val;
			}
		}
		return (bool)$ret;
	}

	/**
	 * Convenience function for automatic casting in form methods etc.
	 * //TODO: maybe move to bootstrap?
	 *
	 * @param mixed $value
	 * @param string $type
	 * @return safe value for DB query, or NULL if type was not a valid one
	 */
	public static function typeCast($value, $type) {
		switch ($type) {
			case 'int':
				$value = (int)$value;
				break;
			case 'float':
				$value = (float)$value;
				break;
			case 'double':
				$value = (double)$value;
				break;
			case 'array':
				$value = (array)$value;
				break;
			case 'bool':
				$value = (bool)$value;
				break;
			case 'string':
				$value = (string)$value;
				break;
			default:
				return null;
		}
		return $value;
	}

	/**
	 * Trim recursivly
	 *
	 */
	public static function trimDeep($value) {
		$value = is_array($value) ? array_map('self::trimDeep', $value) : trim($value);
		return $value;
	}

	/**
	 * H() recursivly
	 *
	 */
	public static function specialcharsDeep($value) {
		$value = is_array($value) ? array_map('self::specialcharsDeep', $value) : htmlspecialchars($value, ENT_QUOTES, 'UTF-8');
		return $value;
	}

	/**
	 * Main deep method
	 *
	 */
	public static function deep($function, $value) {
		$value = is_array($value) ? array_map('self::' . $function, $value) : $function($value);
		return $value;
	}

	/**
	 * Counts the dimensions of an array. If $all is set to false (which is the default) it will
	 * only consider the dimension of the first element in the array.
	 *
	 * @param array $array Array to count dimensions on
	 * @param bool $all Set to true to count the dimension considering all elements in array
	 * @param int $count Start the dimension count at this number
	 * @return int The number of dimensions in $array
	 */
	public static function countDim($array, $all = false, $count = 0) {
		if ($all) {
			$depth = [$count];
			if (is_array($array) && reset($array) !== false) {
				foreach ($array as $value) {
					$depth[] = static::countDim($value, true, $count + 1);
				}
			}
			$return = max($depth);
		} else {
			if (is_array(reset($array))) {
				$return = static::countDim(reset($array)) + 1;
			} else {
				$return = 1;
			}
		}
		return $return;
	}

	/**
	 * Expands the values of an array of strings into a deep array.
	 * Opposite of flattenList().
	 *
	 * It needs at least a single separator to be present in all values
	 * as the key would otherwise be undefined. If data can contain such key-less
	 * rows, use $undefinedKey to avoid an exception being thrown. But it will
	 * effectivly collide with other values in that same key then.
	 *
	 * So `Some.Deep.Value` becomes `array('Some' => array('Deep' => array('Value')))`.
	 *
	 * @param array $data
	 * @param string $separator
	 * @param string $undefinedKey
	 * @return array
	 */
	public static function expandList(array $data, $separator = '.', $undefinedKey = null) {
		$result = [];
		foreach ($data as $value) {
			$keys = explode($separator, $value);
			$value = array_pop($keys);

			$keys = array_reverse($keys);
			if (!isset($keys[0])) {
				if ($undefinedKey === null) {
					throw new RuntimeException('Key-less values are not supported without $undefinedKey.');
				}
				$keys[0] = $undefinedKey;
			}
			$child = [$keys[0] => [$value]];
			array_shift($keys);
			foreach ($keys as $k) {
				$child = [
					$k => $child
				];
			}
			$result = Set::merge($result, $child);
		}
		return $result;
	}

	/**
	 * Flattens a deep array into an array of strings.
	 * Opposite of expandList().
	 *
	 * So `array('Some' => array('Deep' => array('Value')))` becomes `Some.Deep.Value`.
	 *
	 * Note that primarily only string should be used.
	 * However, boolean values are casted to int and thus
	 * both boolean and integer values also supported.
	 *
	 * @param array $data
	 * @return array
	 */
	public static function flattenList(array $data, $separator = '.') {
		$result = [];
		$stack = [];
		$path = null;

		reset($data);
		while (!empty($data)) {
			$key = key($data);
			$element = $data[$key];
			unset($data[$key]);

			if (is_array($element) && !empty($element)) {
				if (!empty($data)) {
					$stack[] = [$data, $path];
				}
				$data = $element;
				reset($data);
				$path .= $key . $separator;
			} else {
				if (is_bool($element)) {
					$element = (int)$element;
				}
				$result[] = $path . $element;
			}

			if (empty($data) && !empty($stack)) {
				list($data, $path) = array_pop($stack);
				reset($data);
			}
		}
		return $result;
	}

	/**
	 * Force-flattens an array.
	 *
	 * Careful with this method. It can lose information.
	 * The keys will not be changed, thus possibly overwrite each other.
	 *
	 * //TODO: check if it can be replace by Hash::flatten() or Utility::flatten().
	 *
	 * @param array $array to flatten
	 * @param bool $perserveKeys
	 * @return array
	 */
	public static function arrayFlatten($array, $preserveKeys = false) {
		if ($preserveKeys) {
			return static::_arrayFlatten($array);
		}
		if (!$array) {
			return [];
		}
		$result = [];
		foreach ($array as $key => $value) {
			if (is_array($value)) {
				$result = array_merge($result, static::arrayFlatten($value));
			} else {
				$result[$key] = $value;
			}
		}
		return $result;
	}

	/**
	 * Force-flattens an array and preserves the keys.
	 *
	 * Careful with this method. It can lose information.
	 * The keys will not be changed, thus possibly overwrite each other.
	 *
	 * //TODO: check if it can be replace by Hash::flatten() or Utility::flatten().
	 *
	 * @param array $a
	 * @param array $f
	 * @return array
	 */
	protected static function _arrayFlatten($a, $f = []) {
		if (!$a) {
			return [];
		}
		foreach ($a as $k => $v) {
			if (is_array($v)) {
				$f = static::_arrayFlatten($v, $f);
			} else {
				$f[$k] = $v;
			}
		}
		return $f;
	}

	/**
	 * Similar to array_shift but on the keys of the array
	 * like array_shift() only for keys and not values
	 *
	 * @param array $keyValuePairs
	 * @return string key
	 */
	public static function arrayShiftKeys(&$array) {
		foreach ($array as $key => $value) {
			unset($array[$key]);
			return $key;
		}
	}

	protected static $_counterStartTime;

	/**
	 * Returns microtime as float value
	 * (to be subtracted right away)
	 *
	 * @param int $precision
	 * @return float
	 */
	public static function microtime($precision = 8) {
		return round(microtime(true), $precision);
	}

	/**
	 * @return void
	 */
	public static function startClock() {
		static::$_counterStartTime = static::microtime();
	}

	/**
	 * @param int $precision
	 * @param bool $restartClock
	 * @return float
	 */
	public static function returnElapsedTime($precision = 8, $restartClock = false) {
		$startTime = static::$_counterStartTime;
		if ($restartClock) {
			static::startClock();
		}
		return static::calcElapsedTime($startTime, static::microtime(), $precision);
	}

	/**
	 * Returns microtime as float value
	 * (to be subtracted right away)
	 *
	 * @param int $start
	 * @param int $end
	 * @param int $precision
	 * @return float
	 */
	public static function calcElapsedTime($start, $end, $precision = 8) {
		$elapsed = $end - $start;
		return round($elapsed, $precision);
	}

	/**
	 * Returns pretty JSON
	 *
	 * @link https://github.com/ndejong/pretty_json/blob/master/pretty_json.php
	 * @param string $json The original JSON string
	 * @param string $ind The string to indent with
	 * @return string
	 */
	public static function prettyJson($json, $indString = "\t") {
		// Replace any escaped \" marks so we don't get tripped up on quotemarks_counter
		$tokens = preg_split('|([\{\}\]\[,])|', str_replace('\"', '~~PRETTY_JSON_QUOTEMARK~~', $json), -1, PREG_SPLIT_DELIM_CAPTURE);

		$indent = 0;
		$result = '';
		$quotemarksCounter = 0;
		$nextTokenUsePrefix = true;

		foreach ($tokens as $token) {
			$quotemarksCounter = $quotemarksCounter + (count(explode('"', $token)) - 1);

			if ($token === '') {
				continue;
			}

			if ($nextTokenUsePrefix) {
				$prefix = str_repeat($indString, $indent);
			} else {
				$prefix = null;
			}

			// Determine if the quote marks are open or closed
			if ($quotemarksCounter & 1) {
				// odd - thus quotemarks open
				$nextTokenUsePrefix = false;
				$newLine = null;
			} else {
				// even - thus quotemarks closed
				$nextTokenUsePrefix = true;
				$newLine = "\n";
			}

			if ($token === "{" || $token === "[") {
				$indent++;
				$result .= $token . $newLine;
			} elseif ($token === "}" || $token === "]") {
				$indent--;

				if ($indent >= 0) {
					$prefix = str_repeat($indString, $indent);
				}

				if ($nextTokenUsePrefix) {
					$result .= $newLine . $prefix . $token;
				} else {
					$result .= $newLine . $token;
				}
			} elseif ($token === ",") {
				$result .= $token . $newLine;
			} else {
				$result .= $prefix . $token;
			}
		}
		return str_replace('~~PRETTY_JSON_QUOTEMARK~~', '\"', $result);
	}

}
