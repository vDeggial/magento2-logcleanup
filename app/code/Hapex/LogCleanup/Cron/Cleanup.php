<?php

namespace Hapex\LogCleanup\Cron;

use Hapex\Core\Cron\BaseCron;
use Hapex\Core\Helper\LogHelper;
use Hapex\Core\Helper\FileHelper;
use Hapex\LogCleanup\Helper\Data as DataHelper;

class Cleanup extends BaseCron
{
    protected $helperFile;

    public function __construct(DataHelper $helperData, LogHelper $helperLog, FileHelper $helperFile)
    {
        parent::__construct($helperData, $helperLog);
        $this->helperFile = $helperFile;
    }

    public function cleanLogs()
    {
        switch (!$this->isMaintenance && $this->helperData->isEnabled()) {
            case true:
                $this->helperData->log("");
                $this->helperData->log("Starting Log File Cleanup");
                $counter = 0;
                try {
                    $this->helperData->log("- Getting log files list");
                    $files = $this->helperFile->getFiles("/var/log", "log");
                    $fileCount = count($files);
                    $this->helperData->log("- Found $fileCount total log files");
                    $counter = $this->processFiles($files);
                    $message = "- " . ($counter == 0 ? "No" : $counter) . " overgrown log files found and deleted";
                    $this->helperData->log($message);
                } catch (\Throwable $e) {
                    $this->helperLog->errorLog(__METHOD__, $this->helperLog->getExceptionTrace($e));
                } finally {
                    $this->helperData->log("Ending Log File Cleanup");
                    return $this;
                }
                break;
        }
    }

    protected function processFiles($files = [])
    {
        try {
            $counter = 0;
            $maxSize = $this->helperData->getMaxSize();
            $maxSize = !empty($maxSize) ? (int) $maxSize : 10;
            $this->helperData->log("- Looking for any log file larger than $maxSize MB in size");
            array_walk($files, function ($file) use (&$maxSize, &$counter) {
                $this->processFile($file, $maxSize, $counter);
            });
        } catch (\Throwable $e) {
            $this->helperLog->errorLog(__METHOD__, $this->helperLog->getExceptionTrace($e));
        } finally {
            return $counter;
        }
    }

    protected function processFile($file = null, $maxSize = 0, &$counter = 0)
    {
        $size = $this->helperFile->getFileSize($file) / 1024 / 1024;
        $this->helperData->log("- Filesize for file $file is $size MB");
        switch ($size >= $maxSize) {
            case true:
                if ($this->helperFile->deleteFile($file)) {
                    $counter++;
                    $this->helperData->log("-- Deleted $file of size $size MB");
                }
                break;
        }
    }
}
