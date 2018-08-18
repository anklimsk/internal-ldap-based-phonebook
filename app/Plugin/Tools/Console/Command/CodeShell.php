<?php
App::uses('AppShell', 'Console/Command');
if (!defined('LF')) {
	define('LF', PHP_EOL); # use PHP to detect default linebreak
}

/**
 * Misc Code Fix Tools
 *
 * cake Tools.Code dependencies [-p PluginName] [-c /custom/path]
 * - Fix missing App::uses() statements
 *
 * @author Mark Scherer
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class CodeShell extends AppShell {

	protected $_files;

	protected $_paths;

	/**
	 * Detect and fix class dependencies
	 *
	 * @return void
	 */
	public function dependencies() {
		if ($customPath = $this->params['custom']) {
			$this->_paths = [$customPath];
		} elseif (!empty($this->params['plugin'])) {
			$this->_paths = [CakePlugin::path($this->params['plugin'])];
		} else {
			$this->_paths = [APP];
		}

		$this->_findFiles('php');
		foreach ($this->_files as $file) {
			$this->out(sprintf('Updating %s...', $file), 1, Shell::VERBOSE);

			$this->_correctFile($file);

			$this->out(sprintf('Done updating %s', $file), 1, Shell::VERBOSE);
		}
	}

	/**
	 * @return void
	 */
	protected function _correctFile($file) {
		$fileContent = $content = file_get_contents($file);

		preg_match_all('/class \w+ extends (.+)\s*{/', $fileContent, $matches);
		if (empty($matches)) {
			return;
		}

		$excludes = ['Fixture', 'Exception', 'TestSuite', 'CakeTestModel'];
		$missingClasses = [];

		foreach ($matches[1] as $match) {
			$match = trim($match);
			preg_match('/\bApp\:\:uses\(\'' . $match . '\'/', $fileContent, $usesMatches);
			if (!empty($usesMatches)) {
				continue;
			}

			preg_match('/class ' . $match . '\s*(w+)?{/', $fileContent, $existingMatches);
			if (!empty($existingMatches)) {
				continue;
			}

			if (in_array($match, $missingClasses)) {
				continue;
			}
			$break = false;
			foreach ($excludes as $exclude) {
				if (strposReverse($match, $exclude) === 0) {
					$break = true;
					break;
				}
			}
			if ($break) {
				continue;
			}

			$missingClasses[] = $match;
		}

		if (empty($missingClasses)) {
			return;
		}

		$fileContent = explode(LF, $fileContent);
		$inserted = [];
		$pos = 1;

		if (!empty($fileContent[1]) && $fileContent[1] === '/**') {
			$count = count($fileContent);
			for ($i = $pos; $i < $count - 1; $i++) {
				if (strpos($fileContent[$i], '*/') !== false) {
					if (strpos($fileContent[$i + 1], 'class ') !== 0) {
						$pos = $i + 1;
					}
					break;
				}
			}
		}

		// try to find the best position to insert app uses statements
		foreach ($fileContent as $row => $rowValue) {
			preg_match('/^App\:\:uses\(/', $rowValue, $matches);
			if ($matches) {
				$pos = $row;
				break;
			}
		}

		foreach ($missingClasses as $missingClass) {
			$classes = [
				'Controller' => 'Controller',
				'Component' => 'Controller/Component',
				'Shell' => 'Console/Command',
				'Model' => 'Model',
				'Behavior' => 'Model/Behavior',
				'Datasource' => 'Model/Datasource',
				'Task' => 'Console/Command/Task',
				'View' => 'View',
				'Helper' => 'View/Helper',
			];
			$type = null;
			foreach ($classes as $class => $namespace) {
				if (($t = strposReverse($missingClass, $class)) === 0) {
					$type = $namespace;
					break;
				}
			}
			if (empty($type)) {
				$this->err($missingClass . ' (' . $file . ') could not be matched');
				continue;
			}

			if ($class === 'Model') {
				$missingClassName = $missingClass;
			} else {
				$missingClassName = substr($missingClass, 0, strlen($missingClass) - strlen($class));
			}
			$objects = App::objects(($this->params['plugin'] ? $this->params['plugin'] . '.' : '') . $class);

			//FIXME: correct result for plugin classes
			if ($missingClass === 'Component') {
				$type = 'Controller';
			} elseif ($missingClass === 'Helper') {
				$type = 'View';
			} elseif ($missingClass === 'ModelBehavior') {
				$type = 'Model';
			} elseif (!empty($this->params['plugin']) && ($location = App::location($missingClass))) {
				$type = $location;
			} elseif (in_array($missingClass, $objects)) {
				$type = ($this->params['plugin'] ? ($this->params['plugin'] . '.') : '') . $type;
			}

			$inserted[] = 'App::uses(\'' . $missingClass . '\', \'' . $type . '\');';
		}

		if (!$inserted) {
			return;
		}

		array_splice($fileContent, $pos, 0, $inserted);
		$fileContent = implode(LF, $fileContent);

		if (empty($this->params['dry-run'])) {
			file_put_contents($file, $fileContent);
			$this->out(sprintf('Correcting %s', $file), 1, Shell::VERBOSE);
		}
	}

	/**
	 * Make sure all files are properly encoded (ü instead of &uuml; etc)
	 * FIXME: non-utf8 files to utf8 files error on windows!
	 *
	 * @return void
	 */
	public function utf8() {
		$this->_paths = [APP . 'View' . DS];
		$this->params['ext'] = 'php|ctp';
		//$this->out('found: '.count($this->_files));

		$patterns = [
		];
		$umlauts = ['ä', 'ö', 'ü', 'Ä', 'Ö', 'Ü', 'ß'];
		foreach ($umlauts as $umlaut) {
			$patterns[] = [
				ent($umlaut) . ' => ' . $umlaut,
				'/' . ent($umlaut) . '/',
				$umlaut,
			];
		}

		$this->_filesRegexpUpdate($patterns);
	}

	/**
	 * CodeShell::_filesRegexpUpdate()
	 *
	 * @param mixed $patterns
	 * @return void
	 */
	protected function _filesRegexpUpdate($patterns) {
		$this->_findFiles($this->params['ext']);
		foreach ($this->_files as $file) {
			$this->out(sprintf('Updating %s...', $file), 1, Shell::VERBOSE);
			$this->_utf8File($file, $patterns);
		}
	}

	/**
	 * Searches the paths and finds files based on extension.
	 *
	 * @param string $extensions
	 * @return void
	 */
	protected function _findFiles($extensions = '') {
		$this->_files = [];
		foreach ($this->_paths as $path) {
			if (!is_dir($path)) {
				continue;
			}
			$Iterator = new RegexIterator(
				new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)),
				'/^.+\.(' . $extensions . ')$/i',
				RegexIterator::MATCH
			);
			foreach ($Iterator as $file) {
				$excludes = ['Config'];
				//Iterator processes plugins even if not asked to
				if (empty($this->params['plugin'])) {
					$excludes = array_merge($excludes, ['Plugin', 'plugins']);
				}
				if (empty($this->params['vendor'])) {
					$excludes = array_merge($excludes, ['Vendor', 'vendors']);
				}
				if (!empty($excludes)) {
					$isIllegalPluginPath = false;
					foreach ($excludes as $exclude) {
						if (strpos($file, $path . $exclude . DS) === 0) {
							$isIllegalPluginPath = true;
							break;
						}
					}
					if ($isIllegalPluginPath) {
						continue;
					}
				}
				if ($file->isFile()) {
					$this->_files[] = $file->getPathname();
				}
			}
		}
	}

	/**
	 * CodeShell::_utf8File()
	 *
	 * @param mixed $file
	 * @param mixed $patterns
	 * @return void
	 */
	protected function _utf8File($file, $patterns) {
		$contents = $fileContent = file_get_contents($file);

		foreach ($patterns as $pattern) {
			$this->out(sprintf(' * Updating %s', $pattern[0]), 1, Shell::VERBOSE);
			$contents = preg_replace($pattern[1], $pattern[2], $contents);
		}

		$this->out(sprintf('Done updating %s', $file), 1, Shell::VERBOSE);
		if (!$this->params['dry-run'] && $contents !== $fileContent) {
			if (file_exists($file)) {
				unlink($file);
			}
			if (WINDOWS) {
				//$fileContent = utf8_decode($fileContent);
			}
			file_put_contents($file, $contents);
		}
	}

	public function getOptionParser() {
		$subcommandParser = [
			'options' => [
				'plugin' => [
					'short' => 'p',
					'help' => 'The plugin to update. Only the specified plugin will be updated.',
					'default' => ''
				],
				'custom' => [
					'short' => 'c',
					'help' => 'Custom path to update recursivly.',
					'default' => ''
				],
				'ext' => [
					'short' => 'e',
					'help' => 'The extension(s) to search. A pipe delimited list, or a preg_match compatible subpattern',
					'default' => 'php|ctp|thtml|inc|tpl'
				],
				'vendor' => [
					'short' => 'e',
					'help' => 'Include vendor files, as well',
					'boolean' => true
				],
				'dry-run' => [
					'short' => 'd',
					'help' => 'Dry run the update, no files will actually be modified.',
					'boolean' => true
				]
			]
		];

		return parent::getOptionParser()
			->description("A shell to help automate code cleanup. \n" .
				"Be sure to have a backup of your application before running these commands.")
			->addSubcommand('group', [
				'help' => 'Run multiple commands.',
				'parser' => $subcommandParser
			])
			->addSubcommand('dependencies', [
				'help' => 'Correct dependencies',
				'parser' => $subcommandParser
			])
			->addSubcommand('utf8', [
				'help' => 'Make files utf8 compliant',
				'parser' => $subcommandParser
			]);
	}

	/**
	 * Shell tasks
	 *
	 * @var array
	 */
	public $tasks = [
		'CodeConvention',
		'CodeWhitespace'
	];

	/**
	 * Main execution function
	 *
	 * @return void
	 */
	public function group() {
		if (!empty($this->args)) {
			if (!empty($this->args[1])) {
				$this->args[1] = constant($this->args[1]);
			} else {
				$this->args[1] = APP;
			}
			$this->{'Code' . ucfirst($this->args[0])}->execute($this->args[1]);
		} else {
			$this->out('Usage: cake code type');
			$this->out('');
			$this->out('type should be space-separated');
			$this->out('list of any combination of:');
			$this->out('');
			$this->out('convention');
			$this->out('whitespace');
		}
	}

}

function strposReverse($str, $search) {
	$str = strrev($str);
	$search = strrev($search);

	$posRev = strpos($str, $search);
	return $posRev;
}
