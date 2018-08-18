<?php
App::uses('FormShimHelper', 'Shim.View/Helper');

/**
 * Enhance Forms with JS widget stuff
 *
 * Some fixes:
 * - 24 instead of 12 for dateTime()
 * - postLink() has class postLink, deleteLink() class deleteLink
 * - normalize for textareas
 * - novalidate can be applied globally via Configure
 *
 * Improvements:
 * - deleteLink() available
 * - datalist
 * - datetime picker added automatically
 *
 * NEW:
 * - Buffer your scripts with js=>inline, but remember to use
 *   $this->Js->writeBuffer() with onDomReady=>false then, though.
 *
 */
class FormExtHelper extends FormShimHelper {

	public $helpers = ['Html', 'Js', 'Tools.Common'];

	public $settings = [
		'webroot' => true, // true => APP webroot, false => tools plugin
		'js' => 'inline', // inline, buffer
	];

	public $scriptsAdded = [
		'date' => false,
		'time' => false,
		'maxLength' => false,
		'autoComplete' => false
	];

	public function __construct($View = null, $config = []) {
		if (($webroot = Configure::read('Asset.webroot')) !== null) {
			$this->settings['webroot'] = $webroot;
		}
		if (($js = Configure::read('Asset.js')) !== null) {
			$this->settings['js'] = $js;
		}

		parent::__construct($View, $config);
	}

	/**
	 * Creates an HTML link, but accesses the url using DELETE method.
	 * Requires javascript to be enabled in browser.
	 *
	 * This method creates a `<form>` element. So do not use this method inside an existing form.
	 * Instead you should add a submit button using FormHelper::submit()
	 *
	 * ### Options:
	 *
	 * - `data` - Array with key/value to pass in input hidden
	 * - `confirm` - Can be used instead of $confirmMessage.
	 * - Other options is the same of HtmlHelper::link() method.
	 * - The option `onclick` will be replaced.
	 *
	 * @param string $title The content to be wrapped by <a> tags.
	 * @param string|array $url Cake-relative URL or array of URL parameters, or external URL (starts with http://)
	 * @param array $options Array of HTML attributes.
	 * @param string $confirmMessage JavaScript confirmation message.
	 * @return string An `<a />` element.
	 */
	public function deleteLink($title, $url = null, $options = [], $confirmMessage = false) {
		$options['method'] = 'delete';
		if (!isset($options['class'])) {
			$options['class'] = 'delete-link deleteLink';
		}
		return $this->postLink($title, $url, $options, $confirmMessage);
	}

	/**
	 * Create postLinks with a default class "postLink"
	 *
	 * @see FormHelper::postLink for details

	 * @return string
	 */
	public function postLink($title, $url = null, $options = [], $confirmMessage = false) {
		if (!isset($options['class'])) {
			$options['class'] = 'post-link postLink';
		}
		return parent::postLink($title, $url, $options, $confirmMessage);
	}

	/**
	 * Overwrite FormHelper::create() to allow disabling browser html5 validation via configs.
	 * It also grabs inputDefaults from your Configure if set.
	 * Also adds the class "form-control" to all inputs for better control over them.
	 *
	 * @param string $model
	 * @param array $options
	 * @return string
	 */
	public function create($model = null, $options = []) {
		if (Configure::read('Validation.browserAutoRequire') === false && !isset($options['novalidate'])) {
			$options['novalidate'] = true;
		}
		if (!isset($options['inputDefaults'])) {
			$options['inputDefaults'] = [];
		}
		$options['inputDefaults'] += (array)Configure::read('Form.inputDefaults');
		$options['inputDefaults'] += [
			'class' => ['form-control'],
		];

		return parent::create($model, $options);
	}

	/**
	 * Adds the given class to the element options.
	 *
	 * Do not add a "form-error" class, though.
	 *
	 * @overwrite
	 * @param array $options Array options/attributes to add a class to
	 * @param string $class The classname being added.
	 * @param string $key the key to use for class.
	 * @return array Array of options with $key set.
	 */
	public function addClass($options = [], $class = null, $key = 'class') {
		if ($key === 'class' && $class === 'form-error') {
			return $options;
		}
		return parent::addClass($options, $class, $key);
	}

	/**
	 * Overwrite FormHelper::_selectOptions()
	 * Remove form-control if added here as it would only be added to the div.
	 *
	 * @param array $elements
	 * @param array $parents
	 * @param bool $showParents
	 * @param array $attributes
	 * @return array
	 */
	protected function _selectOptions($elements = [], $parents = [], $showParents = null, $attributes = []) {
		if ($attributes['style'] === 'checkbox') {
			if (!empty($attributes['class']) && $attributes['class'] === ['form-control']) {
				unset($attributes['class']);
			}
		}
		$selectOptions = parent::_selectOptions($elements, $parents, $showParents, $attributes);
		return $selectOptions;
	}

	/**
	 * Creates a textarea widget.
	 *
	 * ### Options:
	 *
	 * - `escape` - Whether or not the contents of the textarea should be escaped. Defaults to true.
	 *
	 * @param string $fieldName Name of a field, in the form "Modelname.fieldname"
	 * @param array $options Array of HTML attributes, and special options above.
	 * @return string A generated HTML text input element
	 * @link http://book.cakephp.org/2.0/en/core-libraries/helpers/form.html#FormHelper::textarea
	 */
	public function textarea($fieldName, $options = []) {
		$options['normalize'] = false;
		return parent::textarea($fieldName, $options);
	}

	/**
	 * Generates a form input element complete with label and wrapper div
	 * HTML 5 ready!
	 *
	 * ### Options
	 *
	 * See each field type method for more information. Any options that are part of
	 * $attributes or $options for the different **type** methods can be included in `$options` for input().
	 *
	 * - `type` - Force the type of widget you want. e.g. `type => 'select'`
	 * - `label` - Either a string label, or an array of options for the label. See FormHelper::label()
	 * - `div` - Either `false` to disable the div, or an array of options for the div.
	 *    See HtmlHelper::div() for more options.
	 * - `options` - for widgets that take options e.g. radio, select
	 * - `error` - control the error message that is produced
	 * - `empty` - String or boolean to enable empty select box options.
	 * - `before` - Content to place before the label + input.
	 * - `after` - Content to place after the label + input.
	 * - `between` - Content to place between the label + input.
	 * - `format` - format template for element order. Any element that is not in the array, will not be in the output.
	 *    - Default input format order: array('before', 'label', 'between', 'input', 'after', 'error')
	 *    - Default checkbox format order: array('before', 'input', 'between', 'label', 'after', 'error')
	 *    - Hidden input will not be formatted
	 *    - Radio buttons cannot have the order of input and label elements controlled with these settings.
	 *
	 * @param string $fieldName This should be "Modelname.fieldname"
	 * @param array $options Each type of input takes different options.
	 * @return string Completed form widget.
	 * @link http://book.cakephp.org/view/1390/Automagic-Form-Elements
	 */
	public function inputExt($fieldName, $options = []) {
		$defaults = $this->_inputDefaults + ['before' => null, 'between' => null, 'after' => null, 'format' => null];
		$options += $defaults;

		$modelKey = $this->model();
		$fieldKey = $this->field();
		if (!isset($this->fieldset[$modelKey])) {
			$this->_introspectModel($modelKey);
		}

		if (!isset($options['type'])) {
			$magicType = true;
			$options['type'] = 'text';
			if (isset($options['options'])) {
				$options['type'] = 'select';
			} elseif (in_array($fieldKey, ['color', 'email', 'number', 'range', 'url'])) {
				$options['type'] = $fieldKey;
			} elseif (in_array($fieldKey, ['psword', 'passwd', 'password'])) {
				$options['type'] = 'password';
			} elseif (isset($this->fieldset[$modelKey]['fields'][$fieldKey])) {
				$fieldDef = $this->fieldset[$modelKey]['fields'][$fieldKey];
				$type = $fieldDef['type'];
				$primaryKey = $this->fieldset[$modelKey]['key'];
			}

			if (isset($type)) {
				$map = [
					'string' => 'text', 'datetime' => 'datetime', 'boolean' => 'checkbox',
					'timestamp' => 'datetime', 'text' => 'textarea', 'time' => 'time',
					'date' => 'date', 'float' => 'text', 'integer' => 'number',
				];

				if (isset($this->map[$type])) {
					$options['type'] = $this->map[$type];
				} elseif (isset($map[$type])) {
					$options['type'] = $map[$type];
				}
				if ($fieldKey == $primaryKey) {
					$options['type'] = 'hidden';
				}
			}
			if (preg_match('/_id$/', $fieldKey) && $options['type'] !== 'hidden') {
				$options['type'] = 'select';
			}

			if ($modelKey === $fieldKey) {
				$options['type'] = 'select';
				if (!isset($options['multiple'])) {
					$options['multiple'] = 'multiple';
				}
			}
		}
		$types = ['checkbox', 'radio', 'select'];

		if (
			(!isset($options['options']) && in_array($options['type'], $types)) ||
			(isset($magicType) && $options['type'] === 'text')
		) {
			$varName = Inflector::variable(
				Inflector::pluralize(preg_replace('/_id$/', '', $fieldKey))
			);
			$varOptions = $this->_View->getVar($varName);
			if (is_array($varOptions)) {
				if ($options['type'] !== 'radio') {
					$options['type'] = 'select';
				}
				$options['options'] = $varOptions;
			}
		}

		$autoLength = (!array_key_exists('maxlength', $options) && isset($fieldDef['length']));
		if ($autoLength && $options['type'] === 'text') {
			$options['maxlength'] = $fieldDef['length'];
		}
		if ($autoLength && $fieldDef['type'] === 'float') {
			$options['maxlength'] = array_sum(explode(',', $fieldDef['length'])) + 1;
		}

		$divOptions = [];
		$div = $this->_extractOption('div', $options, true);
		unset($options['div']);

		if (!empty($div)) {
			$divOptions['class'] = 'input';
			$divOptions = $this->addClass($divOptions, $options['type']);
			if (is_string($div)) {
				$divOptions['class'] = $div;
			} elseif (is_array($div)) {
				$divOptions = array_merge($divOptions, $div);
			}
			if (
				isset($this->fieldset[$modelKey]) &&
				in_array($fieldKey, $this->fieldset[$modelKey]['validates'])
			) {
				$divOptions = $this->addClass($divOptions, 'required');
			}
			if (!isset($divOptions['tag'])) {
				$divOptions['tag'] = 'div';
			}
		}

		$label = null;
		if (isset($options['label']) && $options['type'] !== 'radio') {
			$label = $options['label'];
			unset($options['label']);
		}

		if ($options['type'] === 'radio') {
			$label = false;
			if (isset($options['options'])) {
				$radioOptions = (array)$options['options'];
				unset($options['options']);
			}
		}

		if ($label !== false) {
			$label = $this->_inputLabel($fieldName, $label, $options);
		}

		$error = $this->_extractOption('error', $options, null);
		unset($options['error']);

		$selected = $this->_extractOption('selected', $options, null);
		unset($options['selected']);

		if (isset($options['rows']) || isset($options['cols'])) {
			$options['type'] = 'textarea';
		}

		if ($options['type'] === 'datetime' || $options['type'] === 'date' || $options['type'] === 'time' || $options['type'] === 'select') {
			$options += ['empty' => false];
		}
		if ($options['type'] === 'datetime' || $options['type'] === 'date' || $options['type'] === 'time') {
			$dateFormat = $this->_extractOption('dateFormat', $options, 'MDY');
			$timeFormat = $this->_extractOption('timeFormat', $options, 24);
			unset($options['dateFormat'], $options['timeFormat']);
		}
		if ($options['type'] === 'email') {
		}

		$type = $options['type'];
		$out = array_merge(
			['before' => null, 'label' => null, 'between' => null, 'input' => null, 'after' => null, 'error' => null],
			['before' => $options['before'], 'label' => $label, 'between' => $options['between'], 'after' => $options['after']]
		);
		$format = null;
		if (is_array($options['format']) && in_array('input', $options['format'])) {
			$format = $options['format'];
		}
		unset($options['type'], $options['before'], $options['between'], $options['after'], $options['format']);

		switch ($type) {
			case 'hidden':
				$input = $this->hidden($fieldName, $options);
				$format = ['input'];
				unset($divOptions);
			break;
			case 'checkbox':
				$input = $this->checkbox($fieldName, $options);
				$format = $format ? $format : ['before', 'input', 'between', 'label', 'after', 'error'];
			break;
			case 'radio':
				$input = $this->radio($fieldName, $radioOptions, $options);
			break;
			case 'select':
				$options += ['options' => []];
				$list = $options['options'];
				unset($options['options']);
				$input = $this->select($fieldName, $list, $selected, $options);
			break;
			case 'time':
				$input = $this->dateTime($fieldName, null, $timeFormat, $selected, $options);
			break;
			case 'date':
				$input = $this->dateTime($fieldName, $dateFormat, null, $selected, $options);
			break;
			case 'datetime':
				$input = $this->dateTime($fieldName, $dateFormat, $timeFormat, $selected, $options);
			break;
			case 'textarea':
				$input = $this->textarea($fieldName, $options + ['cols' => '30', 'rows' => '6']);
			break;
			case 'password':
			case 'file':
				$input = $this->{$type}($fieldName, $options);
			break;
			default:
				$options['type'] = $type;
				$input = $this->text($fieldName, $options);
		}

		if ($type !== 'hidden' && $error !== false) {
			$errMsg = $this->error($fieldName, $error);
			if ($errMsg) {
				$divOptions = $this->addClass($divOptions, 'error');
				$out['error'] = $errMsg;
			}
		}

		$out['input'] = $input;
		$format = $format ? $format : ['before', 'label', 'between', 'input', 'after', 'error'];
		$output = '';
		foreach ($format as $element) {
			$output .= $out[$element];
			unset($out[$element]);
		}
		if (!empty($divOptions['tag'])) {
			$tag = $divOptions['tag'];
			unset($divOptions['tag']);
			$output = $this->Html->tag($tag, $output, $divOptions);
		}
		return $output;
	}

	/**
	 * FormExtHelper::hour()
	 * Overwrite parent
	 *
	 * @param mixed $fieldName
	 * @param bool $format24Hours
	 * @param mixed $attributes
	 * @return void
	 */
	public function hour($fieldName, $format24Hours = true, $attributes = []) {
		return parent::hour($fieldName, $format24Hours, $attributes);
	}

	/**
	 * Override with some custom functionality
	 *
	 * - `datalist` - html5 list/datalist (fallback = invisible).
	 * - `normalize` - boolean whether the content should be normalized regarding whitespaces.
	 * - `required` - manually set if the field is required.
	 *   If not set, it depends on Configure::read('Validation.browserAutoRequire').
	 *
	 * @return string
	 */
	public function input($fieldName, $options = []) {
		$this->setEntity($fieldName);

		$modelKey = $this->model();
		$fieldKey = $this->field();

		if (isset($options['datalist'])) {
			$options['autocomplete'] = 'off';
			if (!isset($options['list'])) {
				$options['list'] = ucfirst($fieldKey) . 'List';
			}
			$datalist = $options['datalist'];

			$list = '<datalist id="' . $options['list'] . '">';
			//$list .= '<!--[if IE]><div style="display: none"><![endif]-->';
			foreach ($datalist as $key => $val) {
				if (!isset($options['escape']) || $options['escape'] !== false) {
					$key = h($key);
					$val = h($val);
				}
				$list .= '<option label="' . $val . '" value="' . $key . '"></option>';
			}
			//$list .= '<!--[if IE]></div><![endif]-->';
			$list .= '</datalist>';
			unset($options['datalist']);
			$options['after'] = !empty($options['after']) ? $options['after'] . $list : $list;
		}

		$res = parent::input($fieldName, $options);
		return $res;
	}

	/**
	 * FormExtHelper::radio()
	 * Overwrite to avoid "form-control" to be added.
	 *
	 * @param mixed $fieldName
	 * @param mixed $options
	 * @param mixed $attributes
	 * @return void
	 */
	public function radio($fieldName, $options = [], $attributes = []) {
		$attributes = $this->_initInputField($fieldName, $attributes);
		if (!empty($attributes['class']) && $attributes['class'] == ['form-control']) {
			$attributes['class'] = false;
		}
		return parent::radio($fieldName, $options, $attributes);
	}

	/**
	 * Overwrite the default method with custom enhancements
	 *
	 * @return array options
	 */
	protected function _initInputField($field, $options = []) {
		$normalize = true;
		if (isset($options['normalize'])) {
			$normalize = $options['normalize'];
			unset($options['normalize']);
		}

		$options = parent::_initInputField($field, $options);

		if (!empty($options['value']) && is_string($options['value']) && $normalize) {
			$options['value'] = str_replace(["\t", "\r\n", "\n"], ' ', $options['value']);
		}

		return $options;
	}

	//TODO: use http://trentrichardson.com/examples/timepicker/
	// or maybe: http://pttimeselect.sourceforge.net/example/index.html (if 24 hour + select dropdowns are supported)

	/**
	 * quicklinks: clear, today, ...
	 *
	 * @return void
	 */
	public function dateScripts($scripts = [], $quicklinks = false) {
		foreach ($scripts as $script) {
			if (!$this->scriptsAdded[$script]) {
				switch ($script) {
					case 'date':
						$lang = Configure::read('Config.language');
						if (strlen($lang) !== 2) {
							App::uses('L10n', 'I18n');
							$Localization = new L10n();
							$lang = $Localization->map($lang);
						}
						if (strlen($lang) !== 2) {
							$lang = 'en';
						}
						if ($this->settings['webroot']) {
							$this->Html->script('datepicker/lang/' . $lang, ['inline' => false]);
							$this->Html->script('datepicker/datepicker', ['inline' => false]);
							$this->Html->css('common/datepicker', ['inline' => false]);
						} else {
							$this->Common->script(['ToolsExtra.Asset|datepicker/lang/' . $lang, 'ToolsExtra.Asset|datepicker/datepicker'], ['inline' => false]);
							$this->Common->css(['ToolsExtra.Asset|datepicker/datepicker'], ['inline' => false]);
						}
						$this->scriptsAdded['date'] = true;
						break;
					case 'time':
						continue;
						if ($this->settings['webroot']) {
						} else {
							//'ToolsExtra.Jquery|ui/core/jquery.ui.core', 'ToolsExtra.Jquery|ui/core/jquery.ui.widget', 'ToolsExtra.Jquery|ui/widgets/jquery.ui.slider',
							$this->Common->script(['ToolsExtra.Jquery|plugins/jquery.timepicker.core', 'ToolsExtra.Jquery|plugins/jquery.timepicker'], ['inline' => false]);
							$this->Common->css(['ToolsExtra.Jquery|ui/core/jquery.ui', 'ToolsExtra.Jquery|plugins/jquery.timepicker'], ['inline' => false]);
						}
						break;
					default:
						break;

				}

				if ($quicklinks) {
				}
			}
		}
	}

	/**
	 * FormExtHelper::dateTimeExt()
	 *
	 * @param mixed $field
	 * @param mixed $options
	 * @return string
	 */
	public function dateTimeExt($field, $options = []) {
		$res = [];
		if (!isset($options['separator'])) {
			$options['separator'] = null;
		}
		if (!isset($options['label'])) {
			$options['label'] = null;
		}

		if (strpos($field, '.') !== false) {
			list($modelName, $field) = explode('.', $field, 2);
		} else {
			$entity = $this->entity();
			$modelName = $this->model();
		}

		$defaultOptions = [
			'empty' => false,
			'return' => true,
		];
		$customOptions = $options + $defaultOptions;

		$res[] = $this->date($field, $customOptions);
		$res[] = $this->time($field, $customOptions);

		$select = implode(' &nbsp; ', $res);

		//return $this->date($field, $options).$select;
		if ($this->isFieldError($field)) {
			$error = $this->error($field);
		} else {
			$error = '';
		}

		$fieldName = Inflector::camelize($field);
		$script = '
		var opts = {
			formElements: {"' . $modelName . $fieldName . '":"%Y", "' . $modelName . $fieldName . '-mm":"%m", "' . $modelName . $fieldName . '-dd":"%d"},
			showWeeks: true,
			statusFormat: "%l, %d. %F %Y",
			' . (!empty($callbacks) ? $callbacks : '') . '
			positioned: "button-' . $modelName . $fieldName . '"
		};
		datePickerController.createDatePicker(opts);
';
		if ($this->settings['js'] === 'inline') {
			$script = $this->_inlineScript($script);
		} else {
			$this->Js->buffer($script);
			$script = '';
		}
		return '<div class="input date' . (!empty($error) ? ' error' : '') . '">' . $this->label($modelName . '.' . $field, $options['label']) . '' . $select . '' . $error . '</div>' . $script;
	}

	protected function _inlineScript($script) {
		return '<script type="text/javascript">
	// <![CDATA[
' . $script . '
	// ]]>
</script>';
	}

	/**
	 * @deprecated
	 * use Form::dateExt
	 */
	public function date($field, $options = []) {
		return $this->dateExt($field, $options);
	}

	/**
	 * Date input (day, month, year) + js
	 * @see http://www.frequency-decoder.com/2006/10/02/unobtrusive-date-picker-widgit-update/
	 * @param field (field or Model.field)
	 * @param options
	 * - separator (between day, month, year)
	 * - label
	 * - empty
	 * - disableDays (TODO!)
	 * - minYear/maxYear (TODO!) / rangeLow/rangeHigh (xxxx-xx-xx or today)
	 */
	public function dateExt($field, $options = []) {
		$return = false;
		if (isset($options['return'])) {
			$return = $options['return'];
			unset($options['return']);
		}
		$quicklinks = false;
		if (isset($options['quicklinks'])) {
			$quicklinks = $options['quicklinks'];
			unset($options['quicklinks']);
		}
		if (isset($options['callbacks'])) {
			$callbacks = $options['callbacks'];
			unset($options['callbacks']);
		}

		$this->dateScripts(['date'], $quicklinks);
		$res = [];
		if (!isset($options['separator'])) {
			$options['separator'] = '-';
		}
		if (!isset($options['label'])) {
			$options['label'] = null;
		}

		if (isset($options['disableDays'])) {
			$disableDays = $options['disableDays'];
		}
		if (isset($options['highligtDays'])) {
			$highligtDays = $options['highligtDays'];
		} else {
			$highligtDays = '67';
		}

		if (strpos($field, '.') !== false) {
			list($modelName, $fieldName) = explode('.', $field, 2);
		} else {
			$entity = $this->entity();
			$modelName = $this->model();
			$fieldName = $field;
		}

		if (isset($options['class'])) {
			$class = $options['class'];
			unset($options['class']);
		}

		$blacklist = ['timeFormat' => null, 'dateFormat' => null, 'minYear' => null, 'maxYear' => null, 'separator' => null];

		$defaultOptions = [
			'empty' => false,
			'minYear' => date('Y') - 10,
			'maxYear' => date('Y') + 10
		];
		$defaultOptions = (array)Configure::read('Form.date') + $defaultOptions;

		$fieldName = Inflector::camelize($fieldName);

		$customOptions = [
			'id' => $modelName . $fieldName . '-dd',
			'class' => 'form-control day'
		];
		$customOptions = array_merge($defaultOptions, $customOptions, $options);
		$customOptions = array_diff_key($customOptions, $blacklist);
		$res['d'] = $this->day($field, $customOptions);
		$customOptions = [
			'id' => $modelName . $fieldName . '-mm',
			'class' => 'form-control month',
		];
		$customOptions = array_merge($defaultOptions, $customOptions, $options);
		$customOptions = array_diff_key($customOptions, $blacklist);
		$res['m'] = $this->month($field, $customOptions);

		$customOptions = [
			'id' => $modelName . $fieldName,
			'class' => 'form-control year'
		];
		$customOptions = array_merge($defaultOptions, $customOptions, $options);
		$minYear = $customOptions['minYear'];
		$maxYear = $customOptions['maxYear'];
		$customOptions = array_diff_key($customOptions, $blacklist);
		$res['y'] = $this->year($field, $minYear, $maxYear, $customOptions);

		$select = implode($options['separator'], $res);

		if ($this->isFieldError($field)) {
			$error = $this->error($field);
		} else {
			$error = '';
		}

		if (!empty($callbacks)) {
			//callbackFunctions:{"create":...,"dateset":[updateBox]},
			$c = $callbacks['update'];
			$callbacks = 'callbackFunctions:{"dateset":[' . $c . ']},';
		}

		if (!empty($customOptions['type']) && $customOptions['type'] === 'text') {
			$script = '
	var opts = {
		formElements: {"' . $modelName . $fieldName . '":"%Y", "' . $modelName . $fieldName . '-mm":"%m", "' . $modelName . $fieldName . '-dd":"%d"},
		showWeeks: true,
		fillGrid: true,
		constrainSelection: true,
		statusFormat: "%l, %d. %F %Y",
		' . (!empty($callbacks) ? $callbacks : '') . '
		positioned: "button-' . $modelName . $fieldName . '"
	};
	datePickerController.createDatePicker(opts);
';
			if ($this->settings['js'] === 'inline') {
				$script = $this->_inlineScript($script);
			} else {
				$this->Js->buffer($script);
				$script = '';
			}

			$options = array_merge(['id' => $modelName . $fieldName], $options);
			$select = $this->text($field, $options);
			return '<div class="input date' . (!empty($error) ? ' error' : '') . '">' . $this->label($modelName . '.' . $field, $options['label']) . '' . $select . '' . $error . '</div>' . $script;
		}

		if ($return) {
			return $select;
		}
		$script = '
	var opts = {
		formElements:{"' . $modelName . $fieldName . '":"%Y", "' . $modelName . $fieldName . '-mm":"%m", "' . $modelName . $fieldName . '-dd":"%d"},
		showWeeks:true,
		fillGrid:true,
		constrainSelection:true,
		statusFormat:"%l, %d. %F %Y",
		' . (!empty($callbacks) ? $callbacks : '') . '
		// Position the button within a wrapper span with an id of "button-wrapper"
		positioned:"button-' . $modelName . $fieldName . '"
	};
	datePickerController.createDatePicker(opts);
';
		if ($this->settings['js'] === 'inline') {
			$script = $this->_inlineScript($script);
		} else {
			$this->Js->buffer($script);
			$script = '';
		}
		return '<div class="input date' . (!empty($error) ? ' error' : '') . '">' . $this->label($modelName . '.' . $field, $options['label']) . '' . $select . '' . $error . '</div>' . $script;
	}

	/**
	 * Custom fix to overwrite the default of non iso 12 hours to 24 hours.
	 * Try to use Form::dateTimeExt, though.
	 *
	 * @see https://cakephp.lighthouseapp.com/projects/42648/tickets/3945-form-helper-should-use-24-hour-format-as-default-iso-8601
	 *
	 * @param string $field
	 * @param mixed $options
	 * @return string Generated set of select boxes for the date and time formats chosen.
	 */
	public function dateTime($field, $options = [], $timeFormat = 24, $attributes = []) {
		// temp fix
		if (!is_array($options)) {
			return parent::dateTime($field, $options, $timeFormat, $attributes);
		}
		return $this->dateTimeExt($field, $options);
	}

	/**
	 * @deprecated
	 * use Form::timeExt
	 */
	public function time($field, $options = []) {
		return $this->timeExt($field, $options);
	}

	/**
	 * FormExtHelper::timeExt()
	 *
	 * @param string $field
	 * @param array $options
	 * @return string
	 */
	public function timeExt($field, $options = []) {
		$return = false;
		if (isset($options['return'])) {
			$return = $options['return'];
			unset($options['return']);
		}

		$this->dateScripts(['time']);
		$res = [];
		if (!isset($options['separator'])) {
			$options['separator'] = ':';
		}
		if (!isset($options['label'])) {
			$options['label'] = null;
		}

		$defaultOptions = [
			'empty' => false,
			'timeFormat' => 24,
		];

		if (strpos($field, '.') !== false) {
			list($model, $field) = explode('.', $field, 2);
		} else {
			$entity = $this->entity();
			$model = $this->model();
		}
		$fieldname = Inflector::camelize($field);

		$customOptions = $options + $defaultOptions;
		$format24Hours = (int)$customOptions['timeFormat'] !== 24 ? false : true;

		$blacklist = ['timeFormat' => null, 'dateFormat' => null, 'separator' => null];

		$hourOptions = array_merge($customOptions, ['class' => 'form-control hour']);
		$hourOptions = array_diff_key($hourOptions, $blacklist);
		$res['h'] = $this->hour($field, $format24Hours, $hourOptions);

		$minuteOptions = array_merge($customOptions, ['class' => 'form-control minute']);
		$minuteOptions = array_diff_key($minuteOptions, $blacklist);
		$res['m'] = $this->minute($field, $minuteOptions);

		$select = implode($options['separator'], $res);

		if ($this->isFieldError($field)) {
			$error = $this->error($field);
		} else {
			$error = '';
		}

		if ($return) {
			return $select;
		}
		/*
		$script = '
<script type="text/javascript">
	// <![CDATA[
		$(document).ready(function() {
		$(\'#'.$model.$fieldname.'-timepicker\').jtimepicker({
			// Configuration goes here
			\'secView\': false
		});
	});
	// ]]>
</script>
		';
		*/
		$script = '';
		//<div id="'.$model.$fieldname.'-timepicker"></div>
		return '<div class="input date' . (!empty($error) ? ' error' : '') . '">' . $this->label($model . '.' . $field, $options['label']) . '' . $select . '' . $error . '</div>' . $script;
	}

	public $maxLengthOptions = [
		'maxCharacters' => 255,
		//'events' => array(),
		'status' => true,
		'statusClass' => 'status',
		'statusText' => 'characters left',
		'slider' => true
	];

	/**
	 * FormExtHelper::maxLengthScripts()
	 *
	 * @return void
	 */
	public function maxLengthScripts() {
		if (!$this->scriptsAdded['maxLength']) {
			$this->Html->script('jquery/maxlength/jquery.maxlength', ['inline' => false]);
			$this->scriptsAdded['maxLength'] = true;
		}
	}

	/**
	 * MaxLength js for textarea input
	 * final output
	 *
	 * @param array $selectors with specific settings
	 * @param array $globalOptions
	 * @return string with JS code
	 */
	public function maxLength($selectors = [], $options = []) {
		$this->maxLengthScripts();
		$js = '';
		$this->maxLengthOptions['statusText'] = __d('tools', $this->maxLengthOptions['statusText']);

		$selectors = (array)$selectors;
		foreach ($selectors as $selector => $settings) {
			if (is_int($selector)) {
				$selector = $settings;
				$settings = [];
			}
			$js .= $this->_maxLengthJs($selector, array_merge($this->maxLengthOptions, $settings));
		}

		if (!empty($options['plain'])) {
			return $js;
		}
		$js = $this->documentReady($js);
		return $this->Html->scriptBlock($js);
	}

	protected function _maxLengthJs($selector, $settings = []) {
		return '
jQuery(\'' . $selector . '\').maxlength(' . $this->Js->object($settings, ['quoteKeys' => false]) . ');
';
	}

	/**
	 * FormExtHelper::scripts()
	 *
	 * @param string $type
	 * @return bool Success
	 */
	public function scripts($type) {
		switch ($type) {
			case 'charCount':
				$this->Html->script('jquery/plugins/charCount', ['inline' => false]);
				$this->Html->css('/js/jquery/plugins/charCount', ['inline' => false]);
				break;
			default:
				return false;
		}
		$this->scriptsAdded[$type] = true;
		return true;
	}

	public $charCountOptions = [
		'allowed' => 255,
	];

	/**
	 * FormExtHelper::charCount()
	 *
	 * @param array $selectors
	 * @param array $options
	 * @return string
	 */
	public function charCount($selectors = [], $options = []) {
		$this->scripts('charCount');
		$js = '';

		$selectors = (array)$selectors;
		foreach ($selectors as $selector => $settings) {
			if (is_int($selector)) {
				$selector = $settings;
				$settings = [];
			}
			$settings = array_merge($this->charCountOptions, $options, $settings);
			$js .= 'jQuery(\'' . $selector . '\').charCount(' . $this->Js->object($settings, ['quoteKeys' => false]) . ');';
		}
		$js = $this->documentReady($js);
		return $this->Html->scriptBlock($js, ['inline' => isset($options['inline']) ? $options['inline'] : true]);
	}

	/**
	 * @param string $string
	 * @return string Js snippet
	 */
	public function documentReady($string) {
		return 'jQuery(document).ready(function() {
' . $string . '
});';
	}

	/**
	 * FormExtHelper::autoCompleteScripts()
	 *
	 * @return void
	 */
	public function autoCompleteScripts() {
		if (!$this->scriptsAdded['autoComplete']) {
			$this->Html->script('jquery/autocomplete/jquery.autocomplete', false);
			$this->Html->css('/js/jquery/autocomplete/jquery.autocomplete', ['inline' => false]);
			$this->scriptsAdded['autoComplete'] = true;
		}
	}

	/**
	 * //TODO
	 * @param jquery: defaults to null = no jquery markup
	 * - url, data, object (one is necessary), options
	 * @return string
	 */
	public function autoComplete($field = null, $options = [], $jquery = null) {
		$this->autoCompleteScripts();

		$defaults = [
			'autocomplete' => 'off'
		];
		$options += $defaults;
		if (empty($options['id']) && is_array($jquery)) {
			$options['id'] = Inflector::camelize(str_replace(".", "_", $field));
		}

		$res = $this->input($field, $options);
		if (is_array($jquery)) {
			// custom one
			$res .= $this->_autoCompleteJs($options['id'], $jquery);
		}
		return $res;
	}

	/**
	 * FormExtHelper::_autoCompleteJs()
	 *
	 * @param mixed $id
	 * @param array $jquery
	 * @return string
	 */
	protected function _autoCompleteJs($id, $jquery = []) {
		if (!empty($jquery['url'])) {
			$var = '"' . $this->Html->url($jquery['url']) . '"';
		} elseif (!empty($jquery['var'])) {
			$var = $jquery['object'];
		} else {
			$var = '[' . $jquery['data'] . ']';
		}

		$options = '';
		if (!empty($jquery['options'])) {
		}

		$js = 'jQuery("#' . $id . '").autocomplete(' . $var . ', {
		' . $options . '
	});
';
		$js = $this->documentReady($js);
		return $this->Html->scriptBlock($js);
	}

	/**
	 * FormExtHelper::checkboxScripts()
	 *
	 * @return void
	 */
	public function checkboxScripts() {
		if (!$this->scriptsAdded['checkbox']) {
			$this->Html->script('jquery/checkboxes/jquery.checkboxes', false);
			$this->scriptsAdded['checkbox'] = true;
		}
	}

	/**
	 * Returns script + elements "all", "none" etc
	 *
	 * @return string
	 */
	public function checkboxScript($id) {
		$this->checkboxScripts();
		$js = 'jQuery("#' . $id . '").autocomplete(' . $var . ', {
		' . $options . '
	});
';
		$js = $this->documentReady($js);
		return $this->Html->scriptBlock($js);
	}

	/**
	 * FormExtHelper::checkboxButtons()
	 *
	 * @param bool $buttonsOnly
	 * @return string
	 */
	public function checkboxButtons($buttonsOnly = false) {
		$res = '<div>';
		$res .= __d('tools', 'Selection') . ': ';

		$res .= $this->Html->link(__d('tools', 'All'), 'javascript:void(0)');
		$res .= $this->Html->link(__d('tools', 'None'), 'javascript:void(0)');
		$res .= $this->Html->link(__d('tools', 'Revert'), 'javascript:void(0)');

		$res .= '</div>';
		if ($buttonsOnly !== true) {
			$res .= $this->checkboxScript();
		}
		return $res;
	}

	/**
	 * Displays a single checkbox - called for each
	 * //FIXME
	 *
	 * @return string
	 */
	protected function _checkbox($id, $group = null, $options = []) {
		$defaults = [
			'class' => 'checkbox-toggle checkboxToggle'
		];
		$options += $defaults;
		return $script . parent::checkbox($fieldName, $options);
	}

}
