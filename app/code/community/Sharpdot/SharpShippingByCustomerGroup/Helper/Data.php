<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magentocommerce.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magentocommerce.com for more information.
 *
 * @category    Sharpdot
 * @package     Sharpdot_SharpShippingByCustomerGroup
 * @copyright   Copyright (c) 2010 Sharpdot Inc. (http://www.sharpdotinc.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Helper
 *
 * @category   Sharpdot
 * @package    Sharpdot_SharpShippingByCustomerGroup
 * @author     Mike D
 */
class Sharpdot_SharpShippingByCustomerGroup_Helper_Data extends Mage_Core_Helper_Abstract
{
	public function getAllowedShippingCarriersByCustomerGroup($customerGroup = null)
	{
		//if no customergroupId then return nothing. this allows admin to get all avaliable methods.
		if(is_null($customerGroup)){
			return array();
		}
		
		$carriers = Mage::getStoreConfig('sharpshippingbycustomergroup/shipping_methods');

		$allowedCarriers = array();
		foreach($carriers as $code => $customerGroupIdCsv){
			$allowedGroupIds = explode(',', $customerGroupIdCsv);
			
			if(in_array($customerGroup, $allowedGroupIds)){
				$allowedCarriers[] = $code;
			}
		}
		
		return $allowedCarriers;
	}
}