<?php

namespace Hapex\LogCleanup\Helper;

use Hapex\Core\Helper\DataHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\ObjectManagerInterface;

class Data extends DataHelper
{
    protected const XML_PATH_CONFIG_ENABLED = "hapex_logcleanup/general/enable";
    protected const XML_PATH_CONFIG_MAXSIZE = "hapex_logcleanup/general/max_size";
    protected const FILE_PATH_LOG = "hapex_log_cleanup";
    public function __construct(Context $context, ObjectManagerInterface $objectManager)
    {

        parent::__construct($context, $objectManager);
    }

    public function isEnabled()
    {
        return $this->getConfigFlag(self::XML_PATH_CONFIG_ENABLED);
    }

    public function getMaxSize()
    {
        return $this->getConfigValue(self::XML_PATH_CONFIG_MAXSIZE);
    }

    public function log($message)
    {
        $this->helperLog->printLog(self::FILE_PATH_LOG, $message);
    }
}
