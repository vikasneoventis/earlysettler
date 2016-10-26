<?php

namespace Balance\Box\Controller\Adminhtml\Group;

use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Balance_Box::group';

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
        $resultPage->setActiveMenu('Balance_Box::group');
        $resultPage->addBreadcrumb(__('Groups'), __('Groups'));
        $resultPage->addBreadcrumb(__('Manage Group'), __('Manage Groups'));
        $resultPage->getConfig()->getTitle()->prepend(__('Box Groups'));

        return $resultPage;
    }

    /**
     * Is the user allowed to view the box group grid.
     *
     * @return bool
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Balance_Box::group');
    }
}