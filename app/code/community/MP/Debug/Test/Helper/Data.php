<?php

/**
 * Class MP_Debug_Test_Helper_Data
 *
 * @category MP
 * @package  MP_Debug
 * @license  Copyright: MP, 2016
 * @link     https://merchantprotocol.com
 *
 * @covers MP_Debug_Helper_Data
 */
class MP_Debug_Test_Helper_Data extends EcomDev_PHPUnit_Test_Case
{
    /** @var  MP_Debug_Helper_Data */
    protected $helper;

    protected function setUp()
    {
        $this->helper = Mage::helper('mp_debug');
    }

    public function testIsEnabled()
    {
        $actual = $this->helper->isEnabled();
        $this->assertTrue($actual);
    }

    public function testGetModuleName()
    {
        $this->assertEquals('MP_Debug', $this->helper->getModuleName());
    }

    public function testGetModuleVersion()
    {
        $this->assertNotEmpty($this->helper->getModuleVersion());
    }

    public function testGetModuleDirectory()
    {
        $config = $this->getModelMock('core/config', array('getModuleDir'));
        $config->expects($this->once())->method('getModuleDir')->with('', 'MP_Module')->willReturn('app/code/community');

        $helper = $this->getHelperMock('mp_debug', array('getModuleName', 'getConfig'));
        $helper->expects($this->any())->method('getModuleName')->willReturn('MP_Module');
        $helper->expects($this->any())->method('getConfig')->willReturn($config);

        $actual = $helper->getModuleDirectory();
        $this->assertEquals('app/code/community', $actual);
    }

    public function testGetIsDevelopMode()
    {
        Mage::setIsDeveloperMode(true);
        $this->assertTrue($this->helper->getIsDeveloperMode());
    }

    public function testGetLogFilename()
    {
        $this->assertEquals('system.log', $this->helper->getLogFilename(null));
    }

    public function testGetExceptionLogFilename()
    {
        $this->assertEquals('exception.log', $this->helper->getExceptionLogFilename(null));
    }

    public function testRunSql()
    {
        $statement = $this->getMock('Varien_Db_Statement_Pdo_Mysql', array(), array(), '', false);
        $statement->expects($this->once())->method('fetchAll')->with(PDO::FETCH_ASSOC)->willReturn(2);

        $connection = $this->getMock('Varien_Db_Adapter_Pdo_Mysql', array(), array(), '', false);
        $connection->expects($this->once())->method('query')
            ->with('TEST', array('var1' => 1, 'var2' => 2))
            ->willReturn($statement);

        $coreResource = $this->getModelMock('core/resource', array('getConnection'));
        $coreResource->expects($this->once())->method('getConnection')->with('core_write')->willReturn($connection);
        $this->replaceByMock('singleton', 'core/resource', $coreResource);

        $actual = $this->helper->runSql('TEST', array('var1' => 1, 'var2' => 2));
        $this->assertEquals(2, $actual);
    }

    public function testGetSqlProfiler()
    {
        $profiler = $this->getMock('Zend_Db_Profiler');

        $connection = $this->getMock('Varien_Db_Adapter_Pdo_Mysql', array(), array(), '', false);
        $connection->expects($this->once())->method('getProfiler')->willReturn($profiler);

        $coreResource = $this->getModelMock('core/resource', array('getConnection'));
        $coreResource->expects($this->once())->method('getConnection')->with('core_write')->willReturn($connection);
        $this->replaceByMock('singleton', 'core/resource', $coreResource);

        $actual = $this->helper->getSqlProfiler();
        $this->assertNotNull($actual);
        $this->assertEquals($profiler, $actual);
    }

    public function testXml2arrayWithoutData()
    {
        $data = array();
        $xmlText = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<config/>
XML;
        $xml = simplexml_load_string($xmlText, 'Mage_Core_Model_Config_Element');

        $this->helper->xml2array($xml, $data, 'root');
        $this->assertEmpty($data);
    }

    public function testXml2array()
    {
        return $this->markTestSkipped('Test is failing on Travis CI. I guess is because of objects passed by reference.');
        $data = array();
        $xmlText = <<<XML
<?xml version="1.0" encoding="utf-8" ?>
<config>
    <modules>
        <MP_Debug>
            <version>1.3.1</version>
        </MP_Debug>
    </modules>
    <global>
        <models>
            <mp_debug>
                <class>MP_Debug_Model</class>
            </mp_debug>
        </models>
    </global>
</config>
XML;
        $xml = simplexml_load_string($xmlText, 'Mage_Core_Model_Config_Element');

        $this->helper->xml2array($xml, $data, 'root');

        $this->assertCount(2, $data);
        $this->assertArrayHasKey('root/modules/MP_Debug/version', $data);
        $this->assertEquals('1.3.1', $data['root/modules/MP_Debug/version']);
        $this->assertArrayHasKey('root/global/models/mp_debug/class', $data);
        $this->assertEquals('MP_Debug_Model', $data['root/global/models/mp_debug/class']);
    }

    public function captureCombinations()
    {
        return array(
            // is debug request, can show, expected can capture
            array(true, true, false),
            array(true, false, false),
            array(false, true, true),
            array(false, false, false)
        );
    }

    /**
     * @dataProvider captureCombinations
     * @param boolean $isDebugRequest
     * @param boolean $canShowToolbar
     * @param boolean $expected
     */
    public function testCanCapture($isDebugRequest, $canShowToolbar, $expected)
    {
        $request = $this->getMock('Mage_Core_Controller_Request_Http', array(), array(), '', false);
        $helper = $this->getHelperMock('mp_debug', array('_getRequest', 'isMPDebugRequest', 'canShowToolbar'));
        $helper->expects($this->any())->method('_getRequest')->willReturn($request);
        $helper->expects($this->once())->method('isMPDebugRequest')->with($request)->willReturn($isDebugRequest);
        $helper->expects($this->any())->method('canShowToolbar')->willReturn($canShowToolbar);

        $actual = $helper->canCapture();
        $this->assertEquals($expected, $actual);
    }

    public function testIsMPDebugRequest()
    {
        $request = $this->getMock('Mage_Core_Controller_Request_Http', array(), array(), '', false);
        $request->expects($this->once())->method('getPathInfo')->willReturn('/mp_debug/');

        $this->assertTrue($this->helper->isMPDebugRequest($request));
    }

    public function testIsMPDebugRequestFalse()
    {
        $request = $this->getMock('Mage_Core_Controller_Request_Http', array(), array(), '', false);
        $request->expects($this->once())->method('getPathInfo')->willReturn('/catalog/product/view/');

        $this->assertFalse($this->helper->isMPDebugRequest($request));
    }

    public function testCanShowToolbarWithoutDevMode()
    {
        Mage::setIsDeveloperMode(false);
        $helper = $this->getHelperMock('mp_debug', array('isEnabled', 'getIsDeveloperMode', 'isDevAllowed'));
        $helper->expects($this->once())->method('isEnabled')->willReturn(true);
        $helper->expects($this->once())->method('isDevAllowed')->willReturn(true);

        $actual = $helper->canShowToolbar();
        $this->assertTrue($actual);
    }

    public function testCanShowToolbarWithoutDevModeAndNotWhitelisted()
    {
        Mage::setIsDeveloperMode(false);
        $helper = $this->getHelperMock('mp_debug', array('isEnabled', 'getIsDeveloperMode', 'isDevAllowed'));
        $helper->expects($this->once())->method('isEnabled')->willReturn(true);
        $helper->expects($this->once())->method('isDevAllowed')->willReturn(false);

        $actual = $helper->canShowToolbar();
        $this->assertFalse($actual);
    }

    public function testCanShowToolbarWithDevMode()
    {
        Mage::setIsDeveloperMode(true);
        $helper = $this->getHelperMock('mp_debug', array('isEnabled', 'getIsDeveloperMode', 'isDevAllowed'));
        $helper->expects($this->once())->method('isEnabled')->willReturn(true);
        $helper->expects($this->never())->method('isDevAllowed')->willReturn(true);

        $actual = $helper->canShowToolbar();
        $this->assertTrue($actual);
    }

    public function testCanShowToolbarWithModuleDisabled()
    {
        $helper = $this->getHelperMock('mp_debug', array('isEnabled', 'getIsDeveloperMode', 'isDevAllowed'));
        $helper->expects($this->once())->method('isEnabled')->willReturn(false);
        $helper->expects($this->never())->method('isDevAllowed')->willReturn(true);

        $actual = $helper->canShowToolbar();
        $this->assertFalse($actual);
    }

    public function testIsAllowed()
    {
        $helper = $this->getHelperMock('mp_debug', array('canShowToolbar'));
        $helper->expects($this->once())->method('canShowToolbar')->willReturn(true);
        $this->assertTrue($helper->isAllowed());
    }

    public function testIsAllowedFalse()
    {
        $helper = $this->getHelperMock('mp_debug', array('canShowToolbar'));
        $helper->expects($this->once())->method('canShowToolbar')->willReturn(false);
        $this->assertFalse($helper->isAllowed());
    }

    public function testFormatNumber()
    {
        $actual = $this->helper->formatNumber(100, 2);
        $this->assertNotEmpty($actual);
    }

    public function memorySizes()
    {
        return array(
            // memory size in bytes, expected formatted value
            array(0, 'n/a'),
            array(1300, '1.3 KB'),
            array(1000000, '1 MB'),
            array(1230000, '1.23 MB'),
            array(14330000, '14.33 MB'),
        );
    }

    /**
     * @dataProvider memorySizes
     * @param $value
     * @param $expectedFormattedValue
     */
    public function testFormatMemorySize($value, $expectedFormattedValue)
    {
        $helper = $this->getHelperMock('mp_debug', array('formatNumber'));
        $helper->expects($this->any())->method('formatNumber')->with()->willReturnArgument(0);

        $actual = $helper->formatMemorySize($value, 2);
        $this->assertEquals($expectedFormattedValue, $actual);
    }

    public function testGetMemoryUsage()
    {
        $this->assertGreaterThan(0, $this->helper->getMemoryUsage());
    }

    public function testGetCurrentScriptDuration()
    {
        $this->assertGreaterThan(0, $this->helper->getCurrentScriptDuration());
    }

    public function testGetAllHeaders()
    {
        $this->assertInternalType('array', $this->helper->getAllHeaders());
    }

    public function testGetGlobalServer()
    {
        $this->assertEquals($_SERVER, $this->helper->getGlobalServer());
    }

    public function testGetGlobalSession()
    {
        // Session was not started so we should get an empty array
        $this->assertEquals(array(), $this->helper->getGlobalSession());
    }

    public function testGetGlobalPost()
    {
        $this->assertEquals($_POST, $this->helper->getGlobalPost());
    }

    public function testGlobalGet()
    {
        $this->assertEquals($_GET, $this->helper->getGlobalGet());
    }

    public function testGetGlobalCookie()
    {
        $this->assertEquals($_COOKIE, $this->helper->getGlobalCookie());
    }

    public function testSortModelCmp()
    {
        $a = new Varien_Object(array('count' => 3));
        $b = new Varien_Object(array('count' => 2));

        $this->assertEquals(0, $this->helper->sortModelCmp($a, $a));
        $this->assertEquals(-1, $this->helper->sortModelCmp($a, $b));
        $this->assertEquals(1, $this->helper->sortModelCmp($b, $a));
    }

    public function testGetBlockFilename()
    {
        $this->assertStringEndsWith('core/Mage/Catalog/Block/Product/List.php', $this->helper->getBlockFilename('Mage_Catalog_Block_Product_List'));
    }

    public function testGetLayoutUpdatesFile()
    {
        $updatesRootXml = <<<XML
<updates>
    <sheep_module>
        <file>some_file.xml</file>
    </sheep_module>
    <sheep_other>
        <file>other_file.xml</file>
    </sheep_other>
</updates>
XML;
        $updatesXml = simplexml_load_string($updatesRootXml, 'Mage_Core_Model_Config_Element');


        $config = $this->getModelMock('core/config', array('getNode'));
        $config->expects($this->once())->method('getNode')
            ->with('frontend/layout/updates')
            ->willReturn($updatesXml);

        $helper = $this->getHelperMock('mp_debug', array('getConfig'));
        $helper->expects($this->any())->method('getConfig')->willReturn($config);

        $updatesFiles = $helper->getLayoutUpdatesFiles(10, 'frontend');
        $this->assertCount(3, $updatesFiles);
        $this->assertContains('some_file.xml', $updatesFiles);
        $this->assertContains('other_file.xml', $updatesFiles);
        $this->assertContains('local.xml', $updatesFiles);
    }

    public function testIsMagentoEE()
    {
        $this->assertFalse($this->helper->isMagentoEE());
    }

    public function testCanPersist()
    {
        return $this->assertTrue($this->helper->canPersist());
    }

    public function testGetPersistLifetime()
    {
        return $this->assertEquals(1, $this->helper->getPersistLifetime());
    }

    public function testCanEnableVarienProfiler()
    {
        return $this->assertTrue($this->helper->canEnableVarienProfiler());
    }

    public function testGetBlockNameWithoutParent()
    {
        $block = $this->getBlockMock('core/template', array('getParentBlock', 'getNameInLayout', 'getBlockAlias'));
        $block->expects($this->any())->method('getParentBlock')->willReturn(null);
        $block->expects($this->any())->method('getNameInLayout')->willReturn('customer_account_dashboard_hello');
        $block->expects($this->any())->method('getBlockAlias')->willReturn('hello');

        $actual = $this->helper->getBlockName($block);
        $this->assertEquals('customer_account_dashboard_hello_hello', $actual);
    }

    public function testGetBlockNameWithParent()
    {
        $parentBlock = $this->getBlockMock('customer/account_dashboard', array('getNameInLayout'));
        $parentBlock->expects($this->any())->method('getNameInLayout')->willReturn('customer_account_dashboard');

        $block = $this->getBlockMock('core/template', array('getParentBlock', 'getNameInLayout', 'getBlockAlias'));
        $block->expects($this->any())->method('getParentBlock')->willReturn($parentBlock);
        $block->expects($this->any())->method('getNameInLayout')->willReturn('customer_account_dashboard_hello');
        $block->expects($this->any())->method('getBlockAlias')->willReturn('hello');

        $actual = $this->helper->getBlockName($block);
        $this->assertEquals('customer_account_dashboard_customer_account_dashboard_hello_hello', $actual);
    }
    
}
