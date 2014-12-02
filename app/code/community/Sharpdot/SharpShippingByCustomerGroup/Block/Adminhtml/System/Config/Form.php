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
 * System config form block
 *
 * @category   Sharpdot
 * @package    Sharpdot_SharpShippingByCustomerGroup
 * @author     Mike D
 */
class Sharpdot_SharpShippingByCustomerGroup_Block_Adminhtml_System_Config_Form extends Mage_Adminhtml_Block_System_Config_Form
{

    

    /**
     * Init fieldset fields
     *
     * @param Varien_Data_Form_Element_Fieldset $fieldset
     * @param Varien_Simplexml_Element $group
     * @param Varien_Simplexml_Element $section
     * @param string $fieldPrefix
     * @param string $labelPrefix
     * @return Mage_Adminhtml_Block_System_Config_Form
     */
    public function initFields($fieldset, $group, $section, $fieldPrefix='', $labelPrefix='')
    {
    	if(!$group->is('use_custom_form', 1)){
    		return parent::initFields($fieldset, $group, $section, $fieldPrefix='', $labelPrefix='');
    	}
    	
        if (!$this->_configDataObject) {
            $this->_initObjects();
        }

        // Extends for config data
        $configDataAdditionalGroups = array();

        $carriers = Mage::getStoreConfig('carriers');
        
        
        $xmlString = "<config><fields>";
        $sort_order = 0;        
    	foreach($carriers as  $code => $carrierConfig){
    		//TODO: if payment method is not active then hide it and set value to empty or null -MRD
    		if(!isset($carrierConfig['active']) || $carrierConfig['active'] == 0){
    			continue;
    		}
    		//Mainly keeps google checkout option from showing.
    		if(!isset($carrierConfig['title'])){
    			continue;
    		}
    		
    		++$sort_order;
        	$xmlString .= '
        		<'.$code.' translate="label">
					<label>'.$carrierConfig['title'].'</label>
					<frontend_type>multiselect</frontend_type>
                    <source_model>sharpshippingbycustomergroup/adminhtml_system_config_source_customer_group</source_model>
					<sort_order>'.$sort_order.'</sort_order>
					<show_in_default>1</show_in_default>
					<show_in_website>1</show_in_website>
					<show_in_store>1</show_in_store>
				</'.$code.'>';
        }         
        $xmlString .= "</fields></config>";
        
        $element = new Mage_Core_Model_Config_Base();        
        $element->loadString($xmlString);        	        	        	
        
        
        foreach($element->getNode('fields') as $elements){
        	

            $elements = (array)$elements;
            // sort either by sort_order or by child node values bypassing the sort_order
            if ($group->sort_fields && $group->sort_fields->by) {
                $fieldset->setSortElementsByAttribute((string)$group->sort_fields->by,
                    ($group->sort_fields->direction_desc ? SORT_DESC : SORT_ASC)
                );
            } else {
                usort($elements, array($this, '_sortForm'));
            }

            foreach ($elements as $e) {
                if (!$this->_canShowField($e)) {
                    continue;
                }

                /**
                 * Look for custom defined field path
                 */
                $path = (string)$e->config_path;
                if (empty($path)) {
                    $path = $section->getName() . '/' . $group->getName() . '/' . $fieldPrefix . $e->getName();
                } elseif (strrpos($path, '/') > 0) {
                    // Extend config data with new section group
                    $groupPath = substr($path, 0, strrpos($path, '/'));
                    if (!isset($configDataAdditionalGroups[$groupPath])) {
                        $this->_configData = $this->_configDataObject->extendConfig($groupPath, false, $this->_configData);
                        $configDataAdditionalGroups[$groupPath] = true;
                    }
                }

                $id = $section->getName() . '_' . $group->getName() . '_' . $fieldPrefix . $e->getName();

                if (isset($this->_configData[$path])) {
                    $data = $this->_configData[$path];
                    $inherit = false;
                } else {
                    $data = $this->_configRoot->descend($path);
                    $inherit = true;
                }
                if ($e->frontend_model) {
                    $fieldRenderer = Mage::getBlockSingleton((string)$e->frontend_model);
                } else {
                    $fieldRenderer = $this->_defaultFieldRenderer;
                }

                $fieldRenderer->setForm($this);
                $fieldRenderer->setConfigData($this->_configData);

                $helperName = $this->_configFields->getAttributeModule($section, $group, $e);
                $fieldType  = (string)$e->frontend_type ? (string)$e->frontend_type : 'text';
                $name       = 'groups['.$group->getName().'][fields]['.$fieldPrefix.$e->getName().'][value]';
                $label      =  Mage::helper($helperName)->__($labelPrefix).' '.Mage::helper($helperName)->__((string)$e->label);
                $hint       = (string)$e->hint ? Mage::helper($helperName)->__((string)$e->hint) : '';

                if ($e->backend_model) {
                    $model = Mage::getModel((string)$e->backend_model);
                    if (!$model instanceof Mage_Core_Model_Config_Data) {
                        Mage::throwException('Invalid config field backend model: '.(string)$e->backend_model);
                    }
                    $model->setPath($path)
                        ->setValue($data)
                        ->setWebsite($this->getWebsiteCode())
                        ->setStore($this->getStoreCode())
                        ->afterLoad();
                    $data = $model->getValue();
                }

                $comment    = $this->_prepareFieldComment($e, $helperName, $data);
                $tooltip    = $this->_prepareFieldTooltip($e, $helperName);

                if ($e->depends) {
                    foreach ($e->depends->children() as $dependent) {
                        $dependentId = $section->getName() . '_' . $group->getName() . '_' . $fieldPrefix . $dependent->getName();
                        $dependentValue = (string) $dependent;
                        $this->_getDependence()
                            ->addFieldMap($id, $id)
                            ->addFieldMap($dependentId, $dependentId)
                            ->addFieldDependence($id, $dependentId, $dependentValue);
                    }
                }

                $field = $fieldset->addField($id, $fieldType, array(
                    'name'                  => $name,
                    'label'                 => $label,
                    'comment'               => $comment,
                    'tooltip'               => $tooltip,
                    'hint'                  => $hint,
                    'value'                 => $data,
                    'inherit'               => $inherit,
                    'class'                 => $e->frontend_class,
                    'field_config'          => $e,
                    'scope'                 => $this->getScope(),
                    'scope_id'              => $this->getScopeId(),
                    'scope_label'           => $this->getScopeLabel($e),
                    'can_use_default_value' => $this->canUseDefaultValue((int)$e->show_in_default),
                    'can_use_website_value' => $this->canUseWebsiteValue((int)$e->show_in_website),
                ));
                $this->_prepareFieldOriginalData($field, $e);

                if (isset($e->validate)) {
                    $field->addClass($e->validate);
                }

                if (isset($e->frontend_type) && 'multiselect' === (string)$e->frontend_type && isset($e->can_be_empty)) {
                    $field->setCanBeEmpty(true);
                }

                $field->setRenderer($fieldRenderer);

                if ($e->source_model) {
                    // determine callback for the source model
                    $factoryName = (string)$e->source_model;
                    $method = false;
                    if (preg_match('/^([^:]+?)::([^:]+?)$/', $factoryName, $matches)) {
                        array_shift($matches);
                        list($factoryName, $method) = array_values($matches);
                    }

                    $sourceModel = Mage::getSingleton($factoryName);
                    if ($sourceModel instanceof Varien_Object) {
                        $sourceModel->setPath($path);
                    }
                    if ($method) {
                        if ($fieldType == 'multiselect') {
                            $optionArray = $sourceModel->$method();
                        } else {
                            $optionArray = array();
                            foreach ($sourceModel->$method() as $value => $label) {
                                $optionArray[] = array('label' => $label, 'value' => $value);
                            }
                        }
                    } else {
                        $optionArray = $sourceModel->toOptionArray($fieldType == 'multiselect');
                    }
                    $field->setValues($optionArray);
                }
            }
        }
        return $this;
    }   

}
