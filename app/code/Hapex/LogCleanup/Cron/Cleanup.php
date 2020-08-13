<?php

declare(strict_types=1);

namespace Hapex\LogCleanup\Cron;

use Hapex\LogCleanup\Helper\Data as DataHelper;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class Cleanup
{
    protected $resource;
    protected $logger;
    private $helperData;

    public function __construct(DataHelper $helperData, ResourceConnection $resource, LoggerInterface $logger)
    {
        $this->helperData = $helperData;
        $this->resource = $resource;
        $this->logger = $logger;
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
                    $files = $this->helperData->getLogFiles();
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
        $size = $this->helperData->getFileSize($file) / 1024 / 1024;
        switch ($size >= $maxSize) {
            case true:
                $this->helperData->deleteFile($file);
                $counter++;
                $this->helperData->log("-- Deleted $file of size $size MB");
                break;
        }
    }
}
