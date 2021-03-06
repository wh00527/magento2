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
 * @category    Mage
 * @package     Mage_Backend
 * @copyright   Copyright (c) 2013 X.commerce, Inc. (http://www.magentocommerce.com)
 * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Backend System Configuration reader.
 * Retrieves system configuration form layout from system.xml files. Merges configuration and caches it.
 *
 * @category    Mage
 * @package     Mage_Backend
 * @author      Magento Core Team <core@magentocommerce.com>
 */
class Mage_Backend_Model_Config_Structure_Reader extends Magento_Config_XmlAbstract
{
    const CACHE_SYSTEM_CONFIGURATION_STRUCTURE = 'backend_system_configuration_structure';

    /**
     * Turns runtime validation on/off
     *
     * @var bool
     */
    protected $_runtimeValidation;

    /**
     * @var Mage_Backend_Model_Config_Structure_Converter
     */
    protected $_converter;

    /**
     * @var Mage_Core_Model_Config
     */
    protected $_config;

    /**
     * @param Mage_Core_Model_Config $config
     * @param Mage_Core_Model_Cache $cache
     * @param Mage_Backend_Model_Config_Structure_Converter $structureConverter
     * @param bool $runtimeValidation
     */
    public function __construct(
        Mage_Core_Model_Config $config,
        Mage_Core_Model_Cache $cache,
        Mage_Backend_Model_Config_Structure_Converter $structureConverter,
        $runtimeValidation = true
    ) {
        $this->_runtimeValidation = $runtimeValidation;
        $this->_converter = $structureConverter;
        $this->_config = $config;

        if ($cache->canUse('config')
            && ($cachedData = $cache->load(self::CACHE_SYSTEM_CONFIGURATION_STRUCTURE))) {
            $this->_data = unserialize($cachedData);
        } else {
            $fileNames = $this->_config
                ->getModuleConfigurationFiles('adminhtml' . DIRECTORY_SEPARATOR . 'system.xml');
            parent::__construct($fileNames);

            if ($cache->canUse('config')) {
                $cache->save(
                    serialize($this->_data),
                    self::CACHE_SYSTEM_CONFIGURATION_STRUCTURE,
                    array(Mage_Core_Model_Config::CACHE_TAG)
                );
            }
        }
    }

    /**
     * Get absolute path to the XML-schema file
     *
     * @return string
     */
    public function getSchemaFile()
    {
        return $this->_config->getModuleDir('etc', 'Mage_Backend') . DIRECTORY_SEPARATOR . 'system.xsd';
    }

    /**
     * Get absolute path to the XML-schema file
     *
     * @return string
     */
    public function getPerFileSchemaFile()
    {
        return $this->_config->getModuleDir('etc', 'Mage_Backend') . DIRECTORY_SEPARATOR . 'system_file.xsd';
    }

    /**
     * Extract configuration data from the DOM structure
     *
     * @param DOMDocument $dom
     * @return array|DOMNodeList
     */
    protected function _extractData(DOMDocument $dom)
    {
        $data = $this->_converter->convert($dom);
        return $data['config']['system'];
    }

    /**
     * Get XML-contents, initial for merging
     *
     * @return string
     */
    protected function _getInitialXml()
    {
        return '<?xml version="1.0" encoding="utf-8"?><config><system></system></config>';
    }

    /**
     * Get list of paths to identifiable nodes
     *
     * @return array
     */
    protected function _getIdAttributes()
    {
        return array(
            '/config/system/tab' => 'id',
            '/config/system/section' => 'id',
            '/config/system/section/group' => 'id',
            '/config/system/section/group/field' => 'id',
            '/config/system/section/group/group/field' => 'id',
            '/config/system/section/group/group/group/field' => 'id',
            '/config/system/section/group/group/group/group/field' => 'id',
        );
    }

    /**
     * Check whether runtime validation should be performed
     *
     * @return bool
     */
    protected function _isRuntimeValidated()
    {
        return $this->_runtimeValidation;
    }

    /**
     * Retrieve all sections system configuration layout
     *
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }
}
