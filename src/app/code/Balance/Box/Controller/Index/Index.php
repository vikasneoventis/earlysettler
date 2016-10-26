<?php

namespace Balance\Box\Controller\Index;

class Index extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Framework\View\Result\Page
     */
    protected $resultPageFactory;

    /**
     * @param \Magento\Framework\App\Action\Context $content
     */

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    public function execute() {
        return $this->resultPageFactory->create();
    }
}