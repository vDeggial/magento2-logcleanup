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

    public function __construct(
        DataHelper $helperData,
        ResourceConnection $resource,
        LoggerInterface $logger
    ) {
        $this->helperData = $helperData;
        $this->resource = $resource;
        $this->logger = $logger;
    }

    public function cleanLogs()
    {
        if ($this->helperData->isEnabled())
        {
            $this->helperData->log("");
            $this->helperData->log("--- Starting Log File Cleanup ---");
            $counter = 0;
            try {
                $files = $this->helperData->getLogFiles();
                foreach ($files as $file)
                {
                    $size = $this->helperData->getFileSize($file) / 1024 / 1024;
                    $maxSize = $this->helperData->getMaxSize();
                    $maxSize = !empty($maxSize) ? (int)$maxSize : 10;
                    if ($size >= $maxSize)
                    {
                        $this->helperData->deleteFile($file);
                        $counter++;
                        $this->helperData->log("---- Deleted $file of size $size" . "MB ----");
                    }                
                }
                
                $message = "---- " . ($counter == 0 ? "No" : $counter) . " log files deleted ----";
                $this->helperData->log($message);
                
            } catch (\Exception $e) {
                $this->helperData->log(sprintf('Error: %s', $e->getMessage()));
            }
            finally
            {
                $this->helperData->log("--- Ending Log File Cleanup ---");
            }
    
            return $this;
        }
    }
}