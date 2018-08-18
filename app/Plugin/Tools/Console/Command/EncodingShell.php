<?php

App::uses('AppShell', 'Console/Command');

/**
 * Detect encoding or find invalid files (starting with BOM)
 *
 * @author Mark Scherer
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class EncodingShell extends AppShell {

	/**
	 * Files that need to be processed.
	 *
	 * @var array
	 */
	protected $_found = [];

	/**
	 * ConvertShell::folder()
	 *
	 * @return void
	 */
	public function folder() {
		$folder = APP;
		if (!empty($this->args)) {
			$folder = array_shift($this->args);
			$folder = realpath($folder);
		}
		if (empty($folder)) {
			return $this->error('Invalid dir', 'No valid dir given (either absolute or relative to APP)');
		}
		$this->out('Searching folder:');
		$this->out($folder, 2);

		$extensions = $this->params['ext'];
		if (!$extensions) {
			$extensions = 'php';
		}

		$this->_detect($folder, $extensions);
		$this->out('Found: ' . count($this->_found));
		if ($this->params['verbose']) {
			foreach ($this->_found as $file) {
				$this->out(' - ' . str_replace(APP, '/', $file));
			}
		}

		$in = '';
		if ($this->_found) {
			$in = $this->in('Correct those files?', ['y', 'n'], 'n');
		}
		if ($in === 'y') {
			if (empty($this->params['dry-run'])) {
				foreach ($this->_found as $file) {
					$content = file_get_contents($file);
					$content = trim($content, b"\xEF\xBB\xBF");
					file_put_contents($file, $content);
				}
			}
			$this->out('Corrections applied');
		}

		$this->out('Done!');
	}

	/**
	 * EncodingShell::_detect()
	 *
	 * @param string $path
	 * @param array $extensions
	 * @param array $excludes
	 * @return void
	 */
	protected function _detect($path, $extensions, $excludes = []) {
		$Iterator = new RegexIterator(
			new RecursiveIteratorIterator(new RecursiveDirectoryIterator($path)),
			'/^.+\.(' . $extensions . ')$/i',
			RegexIterator::MATCH
		);
		foreach ($Iterator as $path) {
			$fullPath = $path->__toString();
			$continue = false;

			foreach ($excludes as $exclude) {
				if (strpos($fullPath, $exclude) === 0) {
					$continue = true;
					break;
				}
			}
			if ($continue) {
				continue;
			}

			if ($path->isDir()) {
				continue;
			}
			if (strpos($fullPath, DS . '.') !== false) {
				continue;
			}
			if (!empty($this->params['verbose'])) {
				$this->out('Probing file: ' . str_replace(APP, '/', $fullPath));
			}

			$content = file_get_contents($fullPath);
			if (strpos($content, b"\xEF\xBB\xBF") === 0) {
				$this->_found[] = $fullPath;
			}
		}
	}

	/**
	 * Get the option parser.
	 *
	 * @return ConsoleOptionParser
	 */
	public function getOptionParser() {
		$subcommandParser = [
			'options' => [
				'ext' => [
					'short' => 'e',
					'help' => 'Specify extensions [php|txt|...] - defaults to [php].',
					'default' => '',
				],
				'dry-run' => [
					'short' => 'd',
					'help' => 'Dry run the command, no files will actually be modified. Should be combined with verbose.',
					'boolean' => true
				],
				'exclude' => [
					'short' => 'x',
					'help' => 'Exclude the following files',
					'boolean' => true,
					'default' => ''
				]
			]
		];

		return parent::getOptionParser()
			->description(sprintf('The %sShell finds BOM files and can correct them.', $this->name))
			->addSubcommand('folder', [
				'help' => 'Search and correct folder recursivly.',
				'parser' => $subcommandParser
			]);
	}

}
