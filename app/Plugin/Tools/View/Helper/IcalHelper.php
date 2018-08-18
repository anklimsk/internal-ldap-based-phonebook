<?php
App::uses('AppHelper', 'View/Helper');
App::uses('IcalLib', 'Tools.Lib');

/**
 * Uses ical lib
 * tipps see http://labs.iamkoa.net/2007/09/07/create-downloadable-ical-events-via-cake/
 *
 * needs ical layout
 * needs Router::parseExtensions('ics') in router.php
 *
 */
class IcalHelper extends AppHelper {

	public $Ical;

	protected $_data = [];

	public function __construct($View = null, $config = []) {
		parent::__construct($View, $config);

		$this->Ical = new IcalLib();
	}

	/**
	 * IcalHelper::reset()
	 *
	 * @return void
	 */
	public function reset() {
		$this->_data = [];
	}

	/**
	 * Add a new ical record.
	 *
	 * @param array $data
	 * @return bool Success
	 */
	public function add($data = []) {
		//TODO: validate!
		$this->_data[] = $data;

		return true;
	}

	/**
	 * Returns complete ical calender file content to output.
	 *
	 * @param array $globalData
	 * @param bool $addStartAndEnd
	 * @return string
	 */
	public function generate($globalData = [], $addStartAndEnd = true) {
		$res = [];
		foreach ($this->_data as $data) {
			$res[] = $this->Ical->build($data);
		}
		$res = implode(PHP_EOL . PHP_EOL, $res);
		if ($addStartAndEnd) {
			$res = $this->Ical->createStart($globalData) . PHP_EOL . PHP_EOL . $res . PHP_EOL . PHP_EOL . $this->Ical->createEnd();
		}
		return $res;
	}

}
