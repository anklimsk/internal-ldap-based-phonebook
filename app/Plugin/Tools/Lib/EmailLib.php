<?php
App::uses('CakeEmail', 'Network/Email');
App::uses('CakeLog', 'Log');
App::uses('Utility', 'Tools.Utility');
App::uses('MimeLib', 'Tools.Lib');
App::uses('CakeText', 'Utility');

// Support BC (snake case config)
if (!Configure::read('Config.systemEmail')) {
	Configure::write('Config.systemEmail', Configure::read('Config.system_email'));
}
if (!Configure::read('Config.systemName')) {
	Configure::write('Config.systemName', Configure::read('Config.system_name'));
}
if (!Configure::read('Config.adminEmail')) {
	Configure::write('Config.adminEmail', Configure::read('Config.admin_email'));
}
if (!Configure::read('Config.adminName')) {
	Configure::write('Config.adminName', Configure::read('Config.admin_name'));
}

/**
 * Convenience class for internal mailer.
 *
 * Adds some useful features and fixes some bugs:
 * - enable easier attachment adding (and also from blob)
 * - enable embedded images in html mails
 * - extensive logging and error tracing
 * - create mails with blob attachments (embedded or attached)
 * - allow wrapLength to be adjusted
 * - Configure::read('Config.xMailer') can modify the x-mailer
 * - basic validation supported
 * - allow priority to be set (1 to 5)
 *
 * Configs for auto-from can be set via Configure::read('Config.adminEmail').
 * For systemEmail() one also needs Configure value Config.systemEmail to be set.
 *
 * @author Mark Scherer
 * @license http://opensource.org/licenses/mit-license.php MIT
 */
class EmailLib extends CakeEmail {

	protected $_log = null;

	protected $_debug = null;

	protected $_error = null;

	protected $_wrapLength = null;

	protected $_priority = null;

	public function __construct($config = null) {
		if ($config === null) {
			$config = Configure::read('Email.config') ?: 'default';
		}
		parent::__construct($config);

		$this->reset($config);
	}

	/**
	 * Quick way to send emails to admin.
	 * App::uses() + EmailLib::systemEmail()
	 *
	 * Note: always go out with default settings (e.g.: SMTP even if debug > 0)
	 *
	 * @param string $subject
	 * @param string $message
	 * @param string $transportConfig
	 * @return bool Success
	 */
	public static function systemEmail($subject, $message = 'System Email', $transportConfig = null) {
		$class = __CLASS__;
		$instance = new $class($transportConfig);
		$instance->from(Configure::read('Config.systemEmail'), Configure::read('Config.systemName'));
		$instance->to(Configure::read('Config.adminEmail'), Configure::read('Config.adminName'));
		if ($subject !== null) {
			$instance->subject($subject);
		}
		if (is_array($message)) {
			$instance->viewVars($message);
			$message = null;
		} elseif ($message === null && array_key_exists('message', $config = $instance->config())) {
			$message = $config['message'];
		}
		return $instance->send($message);
	}

	/**
	 * Change the layout
	 *
	 * @param string $layout Layout to use (or false to use none)
	 * @return resource EmailLib
	 */
	public function layout($layout = false) {
		if ($layout !== false) {
			$this->_layout = $layout;
		}
		return $this;
	}

	/**
	 * Add an attachment from file
	 *
	 * @param string $file: absolute path
	 * @param string $filename
	 * @param array $fileInfo
	 * @return resource EmailLib
	 */
	public function addAttachment($file, $name = null, $fileInfo = []) {
		$fileInfo['file'] = $file;
		if (!empty($name)) {
			$fileInfo = [$name => $fileInfo];
		} else {
			$fileInfo = [$fileInfo];
		}
		return $this->addAttachments($fileInfo);
	}

	/**
	 * Add an attachment as blob
	 *
	 * @param binary $content: blob data
	 * @param string $filename to attach it
	 * @param string $mimeType (leave it empty to get mimetype from $filename)
	 * @param array $fileInfo
	 * @return resource EmailLib
	 */
	public function addBlobAttachment($content, $filename, $mimeType = null, $fileInfo = []) {
		if ($mimeType === null) {
			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			$mimeType = $this->_getMimeByExtension($ext);
		}
		$fileInfo['content'] = $content;
		$fileInfo['mimetype'] = $mimeType;
		$file = [$filename => $fileInfo];
		return $this->addAttachments($file);
	}

	/**
	 * Add an inline attachment from file
	 *
	 * Options:
	 * - mimetype
	 * - contentDisposition
	 *
	 * @param string $file: absolute path
	 * @param string $filename (optional)
	 * @param string $contentId (optional)
	 * @param array $options Options
	 * @return mixed resource $EmailLib or string $contentId
	 */
	public function addEmbeddedAttachment($file, $name = null, $contentId = null, $options = []) {
		if (empty($name)) {
			$name = basename($file);
		}

		$name = pathinfo($name, PATHINFO_FILENAME) . '_' . md5($file) . '.' . pathinfo($name, PATHINFO_EXTENSION);
		if ($contentId === null && ($cid = $this->_isEmbeddedAttachment($file, $name))) {
			return $cid;
		}

		$options['file'] = $file;
		if (empty($options['mimetype'])) {
			$options['mimetype'] = $this->_getMime($file);
		}
		$options['contentId'] = $contentId ? $contentId : str_replace('-', '', CakeText::uuid()) . '@' . $this->_domain;
		$file = [$name => $options];
		$res = $this->addAttachments($file);
		if ($contentId === null) {
			return $options['contentId'];
		}
		return $res;
	}

	/**
	 * Add an inline attachment as blob
	 *
	 * Options:
	 * - contentDisposition
	 *
	 * @param binary $content: blob data
	 * @param string $filename to attach it
	 * @param string $mimeType (leave it empty to get mimetype from $filename)
	 * @param string $contentId (optional)
	 * @param array $options Options
	 * @return mixed resource $EmailLib or string $contentId
	 */
	public function addEmbeddedBlobAttachment($content, $filename, $mimeType = null, $contentId = null, $options = []) {
		if ($mimeType === null) {
			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			$mimeType = $this->_getMimeByExtension($ext);
		}

		$filename = pathinfo($filename, PATHINFO_FILENAME) . '_' . md5($content) . '.' . pathinfo($filename, PATHINFO_EXTENSION);
		if ($contentId === null && ($cid = $this->_isEmbeddedBlobAttachment($content, $filename))) {
			return $cid;
		}

		$options['content'] = $content;
		$options['mimetype'] = $mimeType;
		$options['contentId'] = $contentId ? $contentId : str_replace('-', '', CakeText::uuid()) . '@' . $this->_domain;
		$file = [$filename => $options];
		$res = $this->addAttachments($file);
		if ($contentId === null) {
			return $options['contentId'];
		}
		return $res;
	}

	/**
	 * Returns if this particular file has already been attached as embedded file with this exact name
	 * to prevent the same image to overwrite each other and also to only send this image once.
	 * Allows multiple usage of the same embedded image (using the same cid)
	 *
	 * @param string $file
	 * @param string $name
	 * @return string cid of the found file or false if no such attachment can be found
	 */
	protected function _isEmbeddedAttachment($file, $name) {
		foreach ($this->_attachments as $filename => $fileInfo) {
			if ($filename !== $name) {
				continue;
			}
			return $fileInfo['contentId'];
		}
		return false;
	}

	/**
	 * Returns if this particular file has already been attached as embedded file with this exact name
	 * to prevent the same image to overwrite each other and also to only send this image once.
	 * Allows multiple usage of the same embedded image (using the same cid)
	 *
	 * @return string cid of the found file or false if no such attachment can be found
	 */
	protected function _isEmbeddedBlobAttachment($content, $name) {
		foreach ($this->_attachments as $filename => $fileInfo) {
			if ($filename !== $name) {
				continue;
			}
			return $fileInfo['contentId'];
		}
		return false;
	}

	/**
	 * Try to determine the mimetype by filename.
	 * Uses finfo_open() if availble, otherwise guesses it via file extension.
	 *
	 * @param string $filename
	 * @return string Mimetype
	 */
	protected function _getMime($filename) {
		$mimeType = Utility::getMimeType($filename);
		if (!$mimeType) {
			$ext = pathinfo($filename, PATHINFO_EXTENSION);
			$mimeType = $this->_getMimeByExtension($ext);
		}
		return $mimeType;
	}

	/**
	 * Try to find mimetype by file extension
	 *
	 * @param string $ext lowercase (jpg, png, pdf, ...)
	 * @param string $defaultMimeType
	 * @return string Mimetype (falls back to `application/octet-stream`)
	 */
	protected function _getMimeByExtension($ext, $default = 'application/octet-stream') {
		if (!$ext) {
			return $default;
		}
		if (!isset($this->_Mime)) {
			$this->_Mime = new MimeLib();
		}
		$mime = $this->_Mime->getMimeType($ext);
		if (!$mime) {
			$mime = $default;
		}
		return $mime;
	}

	/**
	 * Read the file contents and return a base64 version of the file contents.
	 * Overwrite parent to avoid File class and file_exists to false negative existent
	 * remove images.
	 * Also fixes file_get_contents (used via File class) to close the connection again
	 * after getting remote files. So far it would have kept the connection open in HTTP/1.1.
	 *
	 * @param string $path The absolute path to the file to read.
	 * @return string File contents in base64 encoding
	 */
	protected function _readFile($path) {
		$context = stream_context_create(
			['http' => ['header' => 'Connection: close']]);
		$content = file_get_contents($path, 0, $context);
		if (!$content) {
			trigger_error('No content found for ' . $path);
		}
		return chunk_split(base64_encode($content));
	}

	/**
	 * Validate if the email has the required fields necessary to make send() work.
	 * Assumes layouting (does not check on content to be present or if view/layout files are missing).
	 *
	 * @return bool Success
	 */
	public function validates() {
		if (!empty($this->_subject) && !empty($this->_to)) {
			return true;
		}
		return false;
	}

	/**
	 * Attach inline/embedded files to the message.
	 *
	 * CUSTOM FIX: blob data support
	 *
	 * @override
	 * @param string $boundary Boundary to use. If null, will default to $this->_boundary
	 * @return array An array of lines to add to the message
	 */
	protected function _attachInlineFiles($boundary = null) {
		if ($boundary === null) {
			$boundary = $this->_boundary;
		}

		$msg = [];
		foreach ($this->_attachments as $filename => $fileInfo) {
			if (empty($fileInfo['contentId'])) {
				continue;
			}
			if (!empty($fileInfo['content'])) {
				$data = $fileInfo['content'];
				$data = chunk_split(base64_encode($data));
			} elseif (!empty($fileInfo['file'])) {
				$data = $this->_readFile($fileInfo['file']);
			} else {
				continue;
			}

			$msg[] = '--' . $boundary;
			$msg[] = 'Content-Type: ' . $fileInfo['mimetype'];
			$msg[] = 'Content-Transfer-Encoding: base64';
			$msg[] = 'Content-ID: <' . $fileInfo['contentId'] . '>';
			$msg[] = 'Content-Disposition: inline; filename="' . $filename . '"';
			$msg[] = '';
			$msg[] = $data;
			$msg[] = '';
		}
		return $msg;
	}

	/**
	 * Attach non-embedded files by adding file contents inside boundaries.
	 *
	 * CUSTOM FIX: blob data support
	 *
	 * @override
	 * @param string $boundary Boundary to use. If null, will default to $this->_boundary
	 * @return array An array of lines to add to the message
	 */
	protected function _attachFiles($boundary = null) {
		if ($boundary === null) {
			$boundary = $this->_boundary;
		}

		$msg = [];
		foreach ($this->_attachments as $filename => $fileInfo) {
			if (!empty($fileInfo['contentId'])) {
				continue;
			}
			if (!empty($fileInfo['content'])) {
				$data = $fileInfo['content'];
				$data = chunk_split(base64_encode($data));
			} elseif (!empty($fileInfo['file'])) {
				$data = $this->_readFile($fileInfo['file']);
			} else {
				continue;
			}

			$msg[] = '--' . $boundary;
			$msg[] = 'Content-Type: ' . $fileInfo['mimetype'];
			$msg[] = 'Content-Transfer-Encoding: base64';
			if (
				!isset($fileInfo['contentDisposition']) ||
				$fileInfo['contentDisposition']
			) {
				$msg[] = 'Content-Disposition: attachment; filename="' . $filename . '"';
			}
			$msg[] = '';
			$msg[] = $data;
			$msg[] = '';
		}
		return $msg;
	}

	/**
	 * Add attachments to the email message
	 *
	 * CUSTOM FIX: Allow URLs
	 * CUSTOM FIX: Blob data support
	 *
	 * Attachments can be defined in a few forms depending on how much control you need:
	 *
	 * Attach a single file:
	 *
	 * {{{
	 * $email->attachments('path/to/file');
	 * }}}
	 *
	 * Attach a file with a different filename:
	 *
	 * {{{
	 * $email->attachments(array('custom_name.txt' => 'path/to/file.txt'));
	 * }}}
	 *
	 * Attach a file and specify additional properties:
	 *
	 * {{{
	 * $email->attachments(array('custom_name.png' => array(
	 *		'file' => 'path/to/file',
	 *		'mimetype' => 'image/png',
	 *		'contentId' => 'abc123'
	 * ));
	 * }}}
	 *
	 * The `contentId` key allows you to specify an inline attachment. In your email text, you
	 * can use `<img src="cid:abc123" />` to display the image inline.
	 *
	 * @override
	 * @param mixed $attachments String with the filename or array with filenames
	 * @return mixed Either the array of attachments when getting or $this when setting.
	 * @throws SocketException
	 */
	public function attachments($attachments = null) {
		if ($attachments === null) {
			return $this->_attachments;
		}
		$attach = [];
		foreach ((array)$attachments as $name => $fileInfo) {
			if (!is_array($fileInfo)) {
				$fileInfo = ['file' => $fileInfo];
			}
			if (empty($fileInfo['content'])) {
				if (!isset($fileInfo['file'])) {
					throw new SocketException('File not specified.');
				}
				$fileName = $fileInfo['file'];
				if (!preg_match('~^https?://~i', $fileInfo['file'])) {
					$fileInfo['file'] = realpath($fileInfo['file']);
				}
				if ($fileInfo['file'] === false || !Utility::fileExists($fileInfo['file'])) {
					throw new SocketException(sprintf('File not found: "%s"', $fileName));
				}
				if (is_int($name)) {
					$name = basename($fileInfo['file']);
				}
			}
			if (empty($fileInfo['mimetype'])) {
				$ext = pathinfo($name, PATHINFO_EXTENSION);
				$fileInfo['mimetype'] = $this->_getMimeByExtension($ext);
			}
			$attach[$name] = $fileInfo;
		}
		$this->_attachments = $attach;
		return $this;
	}

	/**
	 * Set the body of the mail as we send it.
	 * Note: the text can be an array, each element will appear as a seperate line in the message body.
	 *
	 * Do NOT pass a message if you use $this->set() in combination with templates
	 *
	 * @overwrite
	 * @param string/array: message
	 * @return bool Success
	 */
	public function send($message = null) {
		$this->_log = [
			'to' => $this->_to,
			'from' => $this->_from,
			'sender' => $this->_sender,
			'replyTo' => $this->_replyTo,
			'cc' => $this->_cc,
			'subject' => $this->_subject,
			'bcc' => $this->_bcc,
			'transport' => $this->_transportName
		];
		if ($this->_priority) {
			$this->_headers['X-Priority'] = $this->_priority;
			//$this->_headers['X-MSMail-Priority'] = 'High';
			//$this->_headers['Importance'] = 'High';
		}

		// Security measure to not sent to the actual addressee in debug mode while email sending is live
		if (Configure::read('debug') && Configure::read('Email.live')) {
			$adminEmail = Configure::read('Config.adminEmail');
			if (!$adminEmail) {
				$adminEmail = Configure::read('Config.systemEmail');
			}
			foreach ($this->_to as $k => $v) {
				if ($k === $adminEmail) {
					continue;
				}
				unset($this->_to[$k]);
				$this->_to[$adminEmail] = $v;
			}
			foreach ($this->_cc as $k => $v) {
				if ($k === $adminEmail) {
					continue;
				}
				unset($this->_cc[$k]);
				$this->_cc[$adminEmail] = $v;
			}
			foreach ($this->_bcc as $k => $v) {
				if ($k === $adminEmail) {
					continue;
				}
				unset($this->_bcc[$k]);
				$this->_bcc[] = $v;
			}
		}

		try {
			$this->_debug = parent::send($message);
		} catch (Exception $e) {
			$this->_error = $e->getMessage();
			$this->_error .= ' (line ' . $e->getLine() . ' in ' . $e->getFile() . ')' . PHP_EOL .
				$e->getTraceAsString();

			if (!empty($this->_config['logReport'])) {
				$this->_logEmail();
			} else {
				CakeLog::write('error', $this->_error);
			}
			return false;
		}

		if (!empty($this->_config['logReport'])) {
			$this->_logEmail();
		}
		return true;
	}

	/**
	 * Allow modifications of the message
	 *
	 * @param string $text
	 * @return string Text
	 */
	protected function _prepMessage($text) {
		return $text;
	}

	/**
	 * Returns the error if existent
	 *
	 * @return string
	 */
	public function getError() {
		return $this->_error;
	}

	/**
	 * Returns the log if existent
	 *
	 * @return string
	 */
	public function getLog() {
		return $this->_log;
	}

	/**
	 * Returns the debug content returned by send()
	 *
	 * @return string
	 */
	public function getDebug() {
		return $this->_debug;
	}

	/**
	 * Set/Get wrapLength
	 *
	 * @param int $length Must not be more than CakeEmail::LINE_LENGTH_MUST
	 * @return int|CakeEmail
	 */
	public function wrapLength($length = null) {
		if ($length === null) {
			return $this->_wrapLength;
		}
		$this->_wrapLength = $length;
		return $this;
	}

	/**
	 * Set/Get priority
	 *
	 * @param int $priority 1 (highest) to 5 (lowest)
	 * @return int|CakeEmail
	 */
	public function priority($priority = null) {
		if ($priority === null) {
			return $this->_priority;
		}
		$this->_priority = $priority;
		return $this;
	}

	/**
	 * Fix line length
	 *
	 * @overwrite
	 * @param string $message Message to wrap
	 * @return array Wrapped message
	 */
	protected function _wrap($message, $wrapLength = CakeEmail::LINE_LENGTH_MUST) {
		if ($this->_wrapLength !== null) {
			$wrapLength = $this->_wrapLength;
		}
		return parent::_wrap($message, $wrapLength);
	}

	/**
	 * Logs Email to type email
	 *
	 * @return void
	 */
	protected function _logEmail($append = null) {
		$res = $this->_log['transport'] .
			' - ' . 'TO:' . implode(',', array_keys($this->_log['to'])) .
			'||FROM:' . implode(',', array_keys($this->_log['from'])) .
			'||REPLY:' . implode(',', array_keys($this->_log['replyTo'])) .
			'||S:' . $this->_log['subject'];
		$type = 'email';
		if (!empty($this->_error)) {
			$type = 'email_error';
			$res .= '||ERROR:' . $this->_error;
		}
		if ($append) {
			$res .= '||' . $append;
		}
		CakeLog::write($type, $res);
	}

	/**
	 * EmailLib::resetAndSet()
	 *
	 * @return void
	 */
	public function reset($config = null) {
		if ($config === null) {
			$config = Configure::read('Email.config') ?: 'default';
		}
		parent::reset();

		$this->_priority = null;
		$this->_wrapLength = null;

		$this->_log = null;
		$this->_error = null;
		$this->_debug = null;

		$this->_config = (array)Configure::read('Email');
		$this->_applyConfig($config);

		if ($fromEmail = Configure::read('Config.systemEmail')) {
			$fromName = Configure::read('Config.systemName');
		} else {
			$fromEmail = Configure::read('Config.adminEmail');
			$fromName = Configure::read('Config.adminName');
		}
		if (!$fromEmail) {
			throw new RuntimeException('You need to either define Config.systemEmail or Config.adminEmail in Configure.');
		}
		$this->from($fromEmail, $fromName);

		if ($xMailer = Configure::read('Config.xMailer')) {
			$this->addHeaders(['X-Mailer' => $xMailer]);
		}
	}

}
