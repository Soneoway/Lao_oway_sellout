<?php
if ($this->getRequest()->isXmlHttpRequest()) {
    $this->_helper->layout->disableLayout();
    $this->_helper->viewRenderer->setRender('timing/timing-control-add');
}