<?php

namespace Balance\Box\Controller\Adminhtml\Box;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Balance_Box::box';

    /**
     * @var PageFactory
     */
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * Index action
     *
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Balance_Box::box');
        $resultPage->addBreadcrumb(__('Boxes'), __('Boxes'));
        $resultPage->addBreadcrumb(__('Manage Boxes'), __('Manage Boxes'));
        $resultPage->getConfig()->getTitle()->prepend(__('Single Boxes'));

        return $resultPage;
    }
}