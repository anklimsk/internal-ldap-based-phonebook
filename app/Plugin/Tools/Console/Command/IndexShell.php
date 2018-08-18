<?php

if (!defined('CONFIGS')) {
	define('CONFIGS', APP . 'Config' . DS);
}
App::uses('ConnectionManager', 'Model');
App::uses('AppShell', 'Console/Command');

/**
 * Add missing indexes to your schema in a snip
 * based on ad7six' UuidifyShell
 *
 * Currently supports automatically:
 * - BTREE Indexes for UUIDs
 *
 * TODO:
 * - BTREE Indexes for AIIDs if desired
 * - PRIMARY_KEY Indexes for primary keys ("id")
 *
 * @author Mark Scherer
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class IndexShell extends AppShell {

	/**
	 * Runtime settings
	 *
	 * @var array
	 */
	public $settings = [
		'ds' => 'default',
	];

	/**
	 * The Stack of sql queries to run as an array
	 *
	 * @var array
	 */
	protected $_script = [];

	/**
	 * Startup method
	 *
	 * @return void
	 */
	public function startup() {
		parent::startup();

		$this->_welcome();
	}

	/**
	 * Initialize method
	 *
	 * If the flags -h, -help or --help are present bail here and show help
	 *
	 * @return void
	 */
	public function initialize() {
		parent::initialize();

		$this->_loadModels();
	}

	/**
	 * Main method
	 *
	 * Generate the required sql, and then run it
	 * To run for more than one datasource - comma seperate them:
	 * 	cake Tools.Index default,permissions,other
	 *
	 * @return void
	 */
	public function run() {
		$this->_buildScript(explode(',', $this->settings['ds']));
		$this->_runScript();
	}

	/**
	 * Process each named datasource in turn
	 *
	 * E.g. ->_buildScript(array('default', 'users'));
	 *
	 * @param array $sources array()
	 * @return void
	 */
	protected function _buildScript($sources = []) {
		foreach ($sources as $ds) {
			$this->_buildScriptForDataSource($ds);
		}
	}

	/**
	 * Generate the conversion sql for the requested datasource.
	 *
	 * For each table in the db - find all primary or foreign keys (that follow conventions)
	 * currently skips primary keys (should already be PRIMARY)
	 *
	 * @param mixed $ds
	 * @return void
	 */
	protected function _buildScriptForDataSource($ds = 'default') {
		$tables = $this->_tables($ds);
		$db = ConnectionManager::getDataSource($ds);
		$usePrefix = empty($db->config['prefix']) ? '' : $db->config['prefix'];

		$doneSomething = false;
		foreach ($tables as $table) {
			if (in_array($table, ['i18n'])) {
				continue;
			}

			$model = Inflector::classify($table);
			$Inst = ClassRegistry::init([
				'class' => $model,
				'table' => $table,
				'ds' => $ds
			]);
			if (!is_callable([$Inst, 'schema'])) {
				continue;
			}
			$fields = $Inst->schema();

			$indexInfo = $Inst->query('SHOW INDEX FROM `' . $usePrefix . $table . '`');

			foreach ($fields as $field => $details) {
				if (!preg_match('@(^|_)(id|key)$@', $field)) {
					continue;
				}
				if ($details['type'] !== 'integer' && ($details['type'] !== 'string' || $details['length'] !== 36)) {
					continue;
				}
				// right now ONLY for uuids
				if ($details['type'] !== 'string') {
					continue;
				}

				foreach ($indexInfo as $info) {
					$column = $info['STATISTICS']['Column_name'];
					$key = $info['STATISTICS']['Key_name'];
					// dont override primary keys
					if ($column == $field && $key === 'PRIMARY') {
						continue 2;
					}
					// already exists
					if ($column == $field && $key == $field) {
						continue 2;
					}
				}

				$this->out('Create index for ' . $table . '.' . $field);
				$this->_script[$ds]['index'][] = "ALTER TABLE `$usePrefix$table` ADD INDEX (`$field`)";
			}
		}
	}

	protected function _tables($useDbConfig = 'default') {
		if (!$useDbConfig) {
			return [];
		}
		require_once CONFIGS . 'database.php';
		$connections = get_class_vars('DATABASE_CONFIG');
		if (!isset($connections[$useDbConfig])) {
			return [];
		}
		$db = ConnectionManager::getDataSource($useDbConfig);
		if (!$db) {
			return [];
		}
		$usePrefix = empty($db->config['prefix']) ? '' : $db->config['prefix'];
		$tables = [];
		if ($usePrefix) {
			foreach ($db->listSources() as $table) {
				if (!strncmp($table, $usePrefix, strlen($usePrefix))) {
					$tables[$useDbConfig . '::' . $table] = substr($table, strlen($usePrefix));
				}
			}
		} else {
			$_tables = $db->listSources();
			foreach ($_tables as $table) {
				$tables[$useDbConfig . '::' . $table] = $table;
			}
		}
		return $tables;
	}

	/**
	 * Query method
	 *
	 * If the force (or the shortcut f) parameter is set, don't ask for confirmation
	 * If the user chooses to quit - stop processing at that moment
	 * If the parameter `dry-run` is specified - don't do anything except dump the script to stdout
	 *
	 * @param mixed $statement
	 * @return void
	 */
	protected function _query($statement) {
		if (!$statement) {
			$this->out();
			return;
		}
		$statement .= ';';

		$this->out($statement);
		if (!empty($this->params['dry-run'])) {
			return;
		}
		if (empty($this->params['interactive'])) {
			$continue = 'Y';
		} else {
			$continue = strtoupper($this->in('Run this statement?', ['Y', 'N', 'A', 'Q']));
			switch ($continue) {
				case 'Q':
					return $this->_stop();
					return;
				case 'N':
					return;
				case 'A':
					$continue = 'Y';
					$this->params['interactive'] = false;
				case 'Y':
					break;
			}
		}
		if ($continue === 'Y') {
			$this->Db->query($statement);
		}
	}

	/**
	 * Loop over the script running each statement in turn
	 *
	 * @return void
	 */
	protected function _runScript() {
		foreach ($this->_script as $ds => $steps) {
			ksort($steps);
			$this->Db = ConnectionManager::getDataSource($ds);
			foreach ($steps as $step => $statements) {
				foreach ($statements as $statement) {
					$this->_query($statement);
				}
			}
		}
	}

	public function getOptionParser() {
		$subcommandParser = [
			'options' => [
				'dry-run' => [
					'short' => 'd',
					'help' => 'Dry run the update, no files will actually be modified.',
					'boolean' => true
				],
				'log' => [
					'short' => 'l',
					'help' => 'Log all ouput to file log.txt in TMP dir',
					'boolean' => true
				],
				'interactive' => [
					'short' => 'i',
					'help' => 'Interactive',
					'boolean' => true
				],
				'ds' => [
					'short' => 'c',
					'help' => 'Custom ds',
					'boolean' => true
				]
			]
		];

		return parent::getOptionParser()
			->description("...")
			->addSubcommand('run', [
				'help' => 'Run',
				'parser' => $subcommandParser
			]);
	}

}
