<?php

/**
 * Class MP_Debug_Test_Config_Base
 *
 * @category MP
 * @package  MP_Debug
 * @license  Copyright: MP, 2016
 * @link     https://merchantprotocol.com
 */
class MP_Debug_Test_Config_Base extends EcomDev_PHPUnit_Test_Case_Config
{

    public function testModelAliases()
    {
        $this->assertModelAlias('mp_debug/resourceInfo', 'MP_Debug_Model_ResourceInfo');
        $this->assertModelAlias('core/email', 'MP_Debug_Model_Core_Email');
        $this->assertModelAlias('core/email_template', 'MP_Debug_Model_Core_Email_Template');
    }

    public function testSetup()
    {
        $this->assertSetupResourceDefined('MP_Debug', 'mp_debug_setup');
    }

    public function testBlocks()
    {
        $this->assertBlockAlias('mp_debug/abstract', 'MP_Debug_Block_Abstract');
    }

    public function testHelperAlias()
    {
        $this->assertHelperAlias('mp_debug', 'MP_Debug_Helper_Data');
    }

    public function testObservers()
    {
        $this->assertEventObserverDefined('global', 'controller_front_init_before', 'mp_debug/observer', 'onControllerFrontInitBefore');
        $this->assertEventObserverDefined('global', 'controller_action_layout_generate_blocks_after', 'mp_debug/observer', 'onLayoutGenerate');
        $this->assertEventObserverDefined('global', 'core_block_abstract_to_html_before', 'mp_debug/observer', 'onBlockToHtml');
        $this->assertEventObserverDefined('global', 'core_block_abstract_to_html_after', 'mp_debug/observer', 'onBlockToHtmlAfter');
        $this->assertEventObserverDefined('global', 'controller_action_postdispatch', 'mp_debug/observer', 'onActionPostDispatch');
        $this->assertEventObserverDefined('global', 'core_collection_abstract_load_before', 'mp_debug/observer', 'onCollectionLoad');
        $this->assertEventObserverDefined('global', 'eav_collection_abstract_load_before', 'mp_debug/observer', 'onCollectionLoad');
        $this->assertEventObserverDefined('global', 'model_load_after', 'mp_debug/observer', 'onModelLoad');
        $this->assertEventObserverDefined('global', 'controller_action_predispatch', 'mp_debug/observer', 'onActionPreDispatch');
        $this->assertEventObserverDefined('global', 'controller_front_send_response_after', 'mp_debug/observer', 'onControllerFrontSendResponseAfter');
    }

    public function testFrontend()
    {
        $this->assertRouteIn('mp_debug');
        $this->assertLayoutFileDefined('frontend', 'mp_debug.xml');
    }

    public function testAdmin()
    {
        $this->assertLayoutFileDefined('adminhtml', 'mp_debug.xml');
    }

    public function testDefaultConfigs()
    {
        $this->assertDefaultConfigValue('mp_debug/options/enable', 1);
        $this->assertDefaultConfigValue('mp_debug/options/persist', 1);
        $this->assertDefaultConfigValue('mp_debug/options/persist_expiration', 1);
        $this->assertDefaultConfigValue('mp_debug/options/force_varien_profile', 1);
    }

}
