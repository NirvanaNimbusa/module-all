<?php
/**
 * Landofcoder
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the landofcoder.com license that is
 * available through the world-wide-web at this URL:
 * https://landofcoder.com/license
 * 
 * DISCLAIMER
 * 
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 * 
 * @category   Landofcoder
 * @package    Ves_All
 * @copyright  Copyright (c) 2017 Landofcoder (https://www.landofcoder.com/)
 * @license    https://www.landofcoder.com/LICENSE-1.0.html
 */

namespace Lof\All\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Module\Dir;
use Magento\Framework\App\Config\ScopeConfigInterface;

class AdminSystemConfigSave implements ObserverInterface
{
	protected $configWriter;
    protected $_cacheTypeList;
    protected $_cacheFrontendPool;

	public function __construct(
		\Magento\Framework\App\Config\Storage\WriterInterface $configWriter
		) {
        $this->configWriter = $configWriter;
        $this->_cacheTypeList = $cacheTypeList;
        $this->_cacheFrontendPool = $cacheFrontendPool;
    }
    protected function flushCache(){
        $types = array('config','layout','block_html','full_page');
        foreach ($types as $type) {
            $this->_cacheTypeList->cleanType($type);
        }
        foreach ($this->_cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		$configData        = $observer->getConfigData();
        $request = $observer->getRequest();
        $section = $request->getParam("section");
        if(($section && $section=="loflicense") && (!$configData || ($configData && isset($configData['groups']) && !$configData['groups'])) ){
            $groups = $request->getParam('groups');
            if($groups && isset($groups['general']) && $groups['general']){
                $modules = $groups['general']['fields'];
                if($modules){
                    foreach($modules as $key=>$item){
                        $module_license_key = isset($item['value'])?$item['value']:'';
                        if($module_license_key){
                            $module_license_key = is_array($module_license_key)?implode(",",$module_license_key):$module_license_key;
                            $this->configWriter->save('loflicense/general/'.$key,  $module_license_key, ScopeConfigInterface::SCOPE_TYPE_DEFAULT, 0);
                        }
                    }
                    $this->flushCache();
                }
            }
        }
		
	}
}