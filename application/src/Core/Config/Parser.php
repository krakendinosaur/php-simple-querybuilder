<?php
/**
 * A simple config file parser.
 *
 * PHP Version 5.6.35
 *
 * @author Prince Ryan Sy
 */

namespace Core\Config;

use Core\Logger;
use Core\Exception\FileNotFoundException;
use Core\Exception\IndexNotFoundException;

class Parser
{
    /**
     * @var string Config filename
     */
    private $filename;

    /**
     * @var string The full file path with filename
     */
    private $filePath;

    /**
     * @var array An array containing the requested configurations.
     */
    private $settings;

    /**
     * @var Logger The logger object
     */
    private $logger;

    /**
     * @var FileFactory The file factory object
     */
    private $fileFactory;

    /**
     * @var string The full config file path
     */
    private $fullFile;

    public function __construct($filePath = null, $filename = null)
    {
        $this->logger = new Logger(LOGPATH . 'config');
        if ($filePath) {
            $this->filePath = $filePath;
        }
        if ($filename) {
            $this->filename = $filename;
        }

        if (!is_null($filePath) && !is_null($filename)) {
            $this->load();
        }
    }

    /**
     * Stores the config filename
     *
     * @param string $filename
     * @return $this
     */
    public function setFileName($filename)
    {
        $this->filename = $filename;
        return $this;
    }

    /**
     * Stores the config file path
     *
     * @param string $filepath
     * @return $this
     */
    public function setFilePath($filePath)
    {
        $this->filePath = $filePath;
        return $this;
    }

    /**
     * @void
     *
     * Loads the config file
     */
    public function load()
    {
        try {
            $this->fullFile = $this->filePath . $this->filename;

            if (is_readable($this->fullFile)) {
                $extension = pathinfo($this->fullFile, PATHINFO_EXTENSION);
                $this->fileFactory = new FileFactory($this->fullFile);
                $this->settings = $this->fileFactory->$extension()->parse();
            } else {
                throw new FileNotFoundException("Error! File does not exist at given path: " . $this->fullFile);
            }
        } catch (FileNotFoundException $e) {
            $this->logger->write($e->getMessage());
        }
    }

    /**
     * Traverses deeper into the array per parameter given.
     * If no parameter is given, returns the whole config's array of settings.
     *
     * @return mixed
     */
    public function get()
    {
        $argCount = func_num_args();
        try {
            if ($argCount === 0) {
                return $this->settings;
            } elseif ($argCount > 1) {
                $args = func_get_args();
                return $this->getNested($args);
            } else {
                if (is_array($this->settings) && array_key_exists(func_get_arg(0), $this->settings)) {
                    return $this->settings[func_get_arg(0)];
                } else {
                    throw new IndexNotFoundException("Index [" . func_get_arg(0) . "] does not exist.");
                }
            }
        } catch (IndexNotFoundException $e) {
            $this->logger->write($e->getFullMessage());
        }
    }

    /**
     * Method to traverse an infinite depth multidimensional array.
     *
     * @param array $args Each value must be equivalent to a key inside $settings, otherwise returns null.
     * @return mixed
     */
    private function getNested(array $args)
    {
        $context = $this->settings;
        try {
            foreach ($args as $arg) {
                if (!is_array($context) || !array_key_exists($arg, $context)) {
                    $context = null;
                    throw new IndexNotFoundException("Index [" . func_get_arg(0) . "] does not exist.");
                } else {
                    $context = $context[$arg];
                }
            }
        } catch (IndexNotFoundException $e) {
            $this->logger->write($e->getFullMessage());
        }
        return $context;
    }

    public function __toString()
    {
        return $this->settings;
    }
}
