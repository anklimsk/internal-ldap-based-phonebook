<?php
/**
 * This file is the ConsoleOutput file of the plugin.
 *
 * CakeTheme: Set theme for application.
 * @copyright Copyright 2016, Andrey Klimov.
 * @package plugin.Console
 */

App::uses('ConsoleOutput', 'Console');

/**
 * Object wrapper for outputting information from a shell application.
 * Can be connected to any stream resource that can be used with fopen()
 *
 * Can generate colorized output on consoles that support it. There are a few
 * built in styles
 *
 * - `error` Error messages.
 * - `warning` Warning messages.
 * - `info` Informational messages.
 * - `comment` Additional text.
 * - `question` Magenta text used for user prompts
 *
 * By defining styles with addStyle() you can create custom console styles.
 *
 * ### Using styles in output
 *
 * You can format console output using tags with the name of the style to apply. From inside a shell object
 *
 * `$this->out('<warning>Overwrite:</warning> foo.php was overwritten.');`
 *
 * This would create orange 'Overwrite:' text, while the rest of the text would remain the normal color.
 * See ConsoleOutput::styles() to learn more about defining your own styles. Nested styles are not supported
 * at this time.
 *
 * @package       plugin.Console
 */
class CakeThemeConsoleOutput extends ConsoleOutput
{

    /**
     * The current encoding of application
     *
     * @var int
     */
    protected $_appEncoding = 'UTF-8';

    /**
     * Construct the output object.
     *
     * Checks for a pretty console environment. Ansicon and ConEmu allows
     * pretty consoles on Windows, and is supported.
     *
     * @param string $stream The identifier of the stream to write output to.
     */
    public function __construct($stream = 'php://stdout')
    {
        parent::__construct($stream);

        $appEncoding = Configure::read('App.encoding');
        if (!empty($appEncoding)) {
            $this->_appEncoding = $appEncoding;
        }
    }

    /**
     * Outputs a single or multiple messages to stdout. If no parameters
     * are passed, outputs just a newline.
     *
     * Actions:
     * - Conversion to another encoding system depending on OS.
     *
     * @param string|array $message A string or an array of strings to output
     * @param int $newlines Number of newlines to append
     * @return int Returns the number of bytes returned from writing to stdout.
     */
    public function write($message, $newlines = 1)
    {
        if (DIRECTORY_SEPARATOR !== '\\') {
            return parent::write($message, $newlines);
        }

        $inputEncoding = $this->_appEncoding;
        $outputEncoding = 'CP866//IGNORE';
        if (is_array($message)) {
            array_walk(
                $message,
                function (&$value) use ($inputEncoding, $outputEncoding) {
                    $value = iconv($inputEncoding, $outputEncoding, $value);
                }
            );
        } else {
            $message = iconv($inputEncoding, $outputEncoding, $message);
        }

        return parent::write($message, $newlines);
    }
}
