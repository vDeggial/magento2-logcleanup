<?php
declare(strict_types=1);

namespace Hapex\LogCleanup\Cron;

use Hapex\LogCleanup\Helper\Data as DataHelper;
use Magento\Framework\App\ResourceConnection;
use Psr\Log\LoggerInterface;

class Cleanup
{
    /**
     * @var ResourceConnection
     */
    protected $resource;
    /**
     * @var LoggerInterface
     */
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
                    $maxSize = $this->helperData->getMaxSize();
                    $maxSize = !empty($maxSize) ? (int)$maxSize : 10;
                    $this->helperData->log("- Getting log files list");
                    $files = $this->helperData->getLogFiles();
                    $this->helperData->log("- Looking for any log file larger than $maxSize MB in size");
                    foreach ($files as $file) {
                        $size = $this->helperData->getFileSize($file) / 1024 / 1024;
                        switch ($size >= $maxSize) {
                            case true:
                                $this->helperData->deleteFile($file);
                                $counter++;
                                $this->helperData->log("-- Deleted $file of size $size MB");
                                break;
                        }
                    }

                    $message = "- " . ($counter == 0 ? "No" : $counter) . " overgrown log files found and deleted";
                    $this->helperData->log($message);
                } catch (\Exception $e) {
                    $this->helperData->log(sprintf('Error: %s', $e->getMessage()));
                } finally {
                    $this->helperData->log("Ending Log File Cleanup");
                    return $this;
                }
                break;
        }
    }
}
