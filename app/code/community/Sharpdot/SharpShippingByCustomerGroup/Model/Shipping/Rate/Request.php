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
 * @copyright   Copyright (c) 2010 Sharpdot Inc. (http://www.sharpdotinc..com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Fields:
 * - orig:
 *   - country_id: UK
 *   - region_id: 1
 *   - postcode: 90034
 * - dest:
 *   - country_id: UK
 *   - region_id: 2
 *   - postcode: 01005
 * - package:
 *   - value: $100
 *   - weight: 1.5 lb
 *   - height: 10"
 *   - width: 10"
 *   - depth: 10"
 * - order:
 *   - total_qty: 10
 *   - subtotal: $100
 * - option
 *   - insurance: true
 *   - handling: $1
 * - table (shiptable)
 *   - condition_name: package_weight
 * - limit
 *   - carrier: ups
 *   - method: 3dp
 * - ups
 *   - pickup: CC
 *   - container: CP
 *   - address: RES
 */
class Sharpdot_SharpShippingByCustomerGroup_Model_Shipping_Rate_Request extends Mage_Shipping_Model_Rate_Request
{

	protected function _construct()
	{
		//allways force the check.
		$this->setLimitCarrier();
	}
	
	protected function _getCustomerGroupCarriers()
	{
		//Is enabled
		if(!Mage::getStoreConfig('sharpshippingbycustomergroup/settings/enabled')){
			return array();
		}
		
		//Is admin
		if (Mage::getSingleton('admin/session')->isLoggedIn()) {  //Dont restrict admin            
            return array();
        }
        
        
		$customerGroupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
		$allowedShippingMethodsByGroup = Mage::helper('sharpshippingbycustomergroup')->getAllowedShippingCarriersByCustomerGroup($customerGroupId);
		
		return $allowedShippingMethodsByGroup;
	}
	
	public function setLimitCarrier($carrier='')
	{
		if(empty($carrier)){
			$carrier = array();
		}elseif(!is_array($carrier)){
			$carrier = array($carrier);
		}
		
		
		$allowedCarriers = array_unique(array_merge($this->_getCustomerGroupCarriers(), $carrier));
		
		$this->setData('limit_carrier', $allowedCarriers);
	}
}
