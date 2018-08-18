<?php
App::uses('Folder', 'Utility');
App::uses('File', 'Utility');
App::uses('AppShell', 'Console/Command');

/**
 * Folder Sync Shell to update all files from location a with new files from location b
 * You can also remove files in a if they are not longer existent in b
 *
 * @version 1.0
 * @author Mark Scherer
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class FolderSyncShell extends AppShell {

	public $sourceFolder = null;

	public $targetFolder = null;

	public $files = 0;

	public $missing = [];

	public $updatedFiles = 0;

	public $removedFiles = 0;

	public $excludes = ['.git', '.svn'];

	/**
	 * Main
	 *
	 * @return void
	 */
	public function main() {
		$this->help();
	}

	/**
	 * Main
	 *
	 * @return void
	 */
	public function update() {
		$this->sourceFolder = $this->params['source'];
		if (empty($this->sourceFolder)) {
			$this->sourceFolder = APP;
			if (!empty($this->params['plugin'])) {
				$this->sourceFolder = CakePlugin::path($this->params['plugin']);
			}
		}
		if ($this->sourceFolder) {
			$this->sourceFolder = realpath($this->sourceFolder);
		}
		if (!empty($this->params['target'])) {
			$this->targetFolder = realpath($this->params['target']);
		}
		if (!$this->sourceFolder || !is_dir($this->sourceFolder)) {
			return $this->error('Folder not exists', 'Please specify a valid source folder');
		}
		if (!$this->targetFolder || !is_dir($this->targetFolder)) {
			return $this->error('Folder not exists', 'Please specify a valid target folder');
		}

		if (!empty($this->params['invert'])) {
			$tmp = $this->targetFolder;
			$this->targetFolder = $this->sourceFolder;
			$this->sourceFolder = $tmp;
			$this->out('Inverted direction!');
		}

		$this->out('From: ' . $this->sourceFolder);
		$this->out('To: ' . $this->targetFolder);

		$excludes = $this->excludes;
		$this->_sync($this->sourceFolder, $this->targetFolder, $excludes);

		$this->out(sprintf('Files: %s', $this->files));
		$this->out();

		if (!empty($this->missing)) {
			$this->out(sprintf('%s missing files', count($this->missing)));
			foreach ($this->missing as $missing) {
				$this->out('- ' . $missing, 1, Shell::VERBOSE);
			}
			$this->out();
		}

		if ($this->updatedFiles) {
			$this->out(sprintf('%s target files updated', $this->updatedFiles));
		}
		if ($this->removedFiles) {
			$this->out(sprintf('%s source files removed', $this->removedFiles));
		}
		if (!$this->updatedFiles && !$this->removedFiles) {
			$this->out('(nothing to do)');
		}
	}

	protected function _sync($from, $to, $excludes = []) {
		$Folder = new Folder($to);
		$content = $Folder->read(true, true, true);
		foreach ($content[0] as $folder) {
			$targetFolder = $folder;
			$folder = str_replace($this->targetFolder, '', $targetFolder);
			$sourceFolder = $this->sourceFolder . $folder;
			$this->_sync($sourceFolder, $targetFolder, $excludes);
		}
		foreach ($content[1] as $file) {
			$targetFile = $file;
			$file = str_replace($this->targetFolder, '', $targetFile);
			$sourceFile = $this->sourceFolder . $file;
			$this->_updateFile($targetFile, $sourceFile, $file);
		}
	}

	/**
	 * @param target
	 * @param source - does not have to exists
	 * @return void;
	 */
	protected function _updateFile($target, $source, $name = null) {
		if (!$name) {
			$name = $target;
		}
		$this->out('- ' . $name, 1, Shell::VERBOSE);
		$this->files++;

		$sourceExists = file_exists($source);
		if (!$sourceExists && !empty($this->params['remove'])) {
			if (empty($this->params['dry-run']) && !unlink($target)) {
				throw new InternalErrorException('no rights');
			}
			$this->missing[] = $name;
			$this->removedFiles++;
			$this->out('   (source missing, deleting)', 1, Shell::VERBOSE);
			return;
		}
		if (!$sourceExists) {
			$this->missing[] = $name;
			$this->out('   (target missing, skipping)', 1, Shell::VERBOSE);
			return;
		}
		if (sha1(file_get_contents($source)) === sha1(file_get_contents($target))) {
			$this->out('   (equal, skipping)', 1, Shell::VERBOSE);
			return;
		}
		if (empty($this->params['dry-run']) && !copy($source, $target)) {
			throw new InternalErrorException('no rights');
		}
		$this->updatedFiles++;
	}

	public function help() {
		$head = 'Usage: cake FolderSync <command>' . "\n";
		$head .= "-----------------------------------------------\n";
		$head .= 'Commands:' . "\n\n";

		$head .= "\t" . 'update' . "\n\n";
		//$head .= "\t" . 'update' . "\n\n";

		$this->out($head);
	}

	public function getOptionParser() {
		$subcommandParser = [
			'options' => [
				'source' => [
					'short' => 's',
					'help' => 'source - defaults to app',
					'default' => '',
				],
				'target' => [
					'short' => 't',
					'help' => 'target - required',
					'default' => '',
				],
				'plugin' => [
					'short' => 'p',
					'help' => 'The plugin folder - can only be used with app as source',
					'default' => '',
				],
				'remove' => [
					'short' => 'r',
					'help' => 'Remove files if source is non-existent',
					'boolean' => true
				],
				'invert' => [
					'short' => 'i',
					'help' => 'Invert direction (target to source)',
					'boolean' => true
				],
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
			]
		];

		return parent::getOptionParser()
			->description('Sync folders via CakePHP shell')
			->addSubcommand('update', [
				'help' => 'Update',
				'parser' => $subcommandParser
			]);
	}

}
