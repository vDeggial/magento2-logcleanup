<?php

namespace Hapex\LogCleanup\Helper;

use Hapex\Core\Helper\DataHelper;
use Hapex\Core\Helper\FileHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;

class Data extends DataHelper
{
    public function __construct(Context $context, ObjectManagerInterface $objectManager)
    {

        parent::__construct($context, $objectManager);
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
}
