<?php

namespace Hapex\LogCleanup\Helper;

use Hapex\Core\Helper\DataHelper;
use Magento\Framework\App\Helper\Context;

class Data extends DataHelper
{
    
    public function __construct(
        \Magento\Framework\App\Helper\Context $context
    ) {
        parent::__construct($context);
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
        $this->printLog("hapex_log_cleanup", $message);
    }
    
    public function getLogFiles()
    {
        $files = array();
        foreach (glob(BP . "/var/log/*.log") as $file) {
            $files[] = $file;
        }
        return $files;
    }
    
    public function getFileSize($filename)
    {
        if (file_exists($filename)) return filesize($filename);
        return 0;
    }
    
    public function deleteFile($filename)
    {
        unlink($filename);
    }
}
