<?php

namespace Hapex\LogCleanup\Helper;

use Hapex\Core\Helper\DataHelper;
use Hapex\Core\Helper\FileHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;

class Data extends DataHelper
{
    protected $helperFile;
    public function __construct(Context $context, ObjectManagerInterface $objectManager, FileHelper $helperFile)
    {

        parent::__construct($context, $objectManager);
        $this->helperFile = $helperFile;
    }

    public function isEnabled()
    {
        return $this->getConfigFlag('hapex_logcleanup/general/enable');
    }

    public function getMaxSize()
    {
        return $this->getConfigValue('hapex_logcleanup/general/max_size');
    }

    public function log($message)
    {
        $this->helperLog->printLog("hapex_log_cleanup", $message);
    }

    public function getLogFiles()
    {
        $files = array();
        $rootPath = $this->helperFile->getRootPath();
        foreach (glob($rootPath . "/var/log/*.log") as $file) {
            $files[] = $file;
        }
        return $files;
    }

    public function getFileSize($filename)
    {
        if (file_exists($filename)) {
            return filesize($filename);
        }

        return 0;
    }

    public function deleteFile($filename)
    {
        unlink($filename);
    }
}
