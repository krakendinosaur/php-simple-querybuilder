<?php
/**
 * Log - A logger class which creates logs when an exception is thrown.
 *
 * Original author of this class: Vivek Wicky Aswal.
 * Original git link: https://github.com/wickyaswal/indieteq-php-my-sql-pdo-database-class
 *
 * Modified to fit the querybuilder project.
 */

namespace Core;

class Logger
{
    # @string, Log directory name
    private $path;
    
    public function __construct($path)
    {
        date_default_timezone_set('Asia/Manila');
        $this->setPath($path);
    }

    public function setPath($value)
    {
        $this->path = $value . DIRECTORY_SEPARATOR;
    }

    public function getPath()
    {
        return $this->path;
    }
    
   /**
    *   @void
    *   Creates the log
    *
    *   @param string $message the message which is written into the log.
    *   @description:
    *    1. Checks if directory exists, if not, create one and call this method again.
    *    2. Checks if log already exists.
    *    3. If not, new log gets created. Log is written into the logs folder.
    *    4. Logname is current date(Year - Month - Day).
    *    5. If log exists, edit method called.
    *    6. Edit method modifies the current log.
    */
    public function write($message)
    {
        $date = new \DateTime();
        $log = $this->path . $date->format('Y-m-d').".log";

        if (is_dir($this->path)) {
            if (!file_exists($log)) {
                $fh  = fopen($log, 'a+');
                $logcontent = "Time : " . $date->format('H:i:s')."\r\n" . $message ."\r\n";
                fwrite($fh, $logcontent);
                fclose($fh);
            } else {
                $this->edit($log, $date, $message);
            }
        } else {
            if (mkdir($this->path, 0664, true) === true) {
                $this->write($message);
            }
        }
    }
    
    /**
     *  @void
     *  Gets called if log exists.
     *  Modifies current log and adds the message to the log.
     *
     * @param string $log
     * @param DateTimeObject $date
     * @param string $message
     */
    private function edit($log, $date, $message)
    {
        $logcontent = "Time : " . $date->format('H:i:s')."\r\n" . $message ."\r\n\r\n";
        $logcontent = $logcontent . file_get_contents($log);
        file_put_contents($log, $logcontent);
    }
}
