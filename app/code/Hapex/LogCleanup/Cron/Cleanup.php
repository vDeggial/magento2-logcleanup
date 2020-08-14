<?php

declare(strict_types=1);

namespace Hapex\LogCleanup\Cron;

use Hapex\LogCleanup\Helper\Data as DataHelper;
use Hapex\Core\Helper\FileHelper;

class Cleanup
{
    private $helperData;
    private $helperFile;

    public function __construct(DataHelper $helperData, FileHelper $helperFile)
    {
        $this->helperData = $helperData;
        $this->helperFile = $helperFile;
    }

    public function cleanLogs()
    {
        switch ($this->helperData->isEnabled()) {
            case true:
                $this->helperData->log("");
                $this->helperData->log("Starting Log File Cleanup");
                $counter = 0;
                try {
                    $this->helperData->log("- Getting log files list");
                    $files = $this->helperFile->getFiles("/var/log", "log");
                    $counter = $this->processFiles($files);
                    $message = "- " . ($counter == 0 ? "No" : $counter) . " overgrown log files found and deleted";
                    $this->helperData->log($message);
                } catch (\Exception $e) {
                    $this->helperData->errorLog(__METHOD__, $e->getMessage());
                } finally {
                    $this->helperData->log("Ending Log File Cleanup");
                    return $this;
                }
                break;
        }
    }

    private function processFiles($files = [])
    {
        try {
            $counter = 0;
            $maxSize = $this->helperData->getMaxSize();
            $maxSize = !empty($maxSize) ? (int) $maxSize : 10;
            $this->helperData->log("- Looking for any log file larger than $maxSize MB in size");
            foreach ($files as $file) {
                $this->processFile($file, $maxSize, $counter);
            }
        } catch (\Exception $e) {
            $this->helperData->errorLog(__METHOD__, $e->getMessage());
        } finally {
            return $counter;
        }
    }

    private function processFile($file = null, $maxSize = 0, &$counter = 0)
    {
        $size = $this->helperFile->getFileSize($file) / 1024 / 1024;
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
