<?php
require_once dirname(dirname(dirname(__FILE__))).'/classes/EventCache.php';

class EventCacheTest extends PHPUnit_Framework_TestCase {
    public $DBCalled = false;
    public $MagicKey = '';
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     *
     * @access protected
     */
    protected function setUp()
    {
        EventCache::setOption(array(
            'app' => 'testapp',
            'trackEvents' => true,
        ));
    }
    #magic($scope, $method, $args = array(), $val = null, $events = array(), $options = array()) {
    public function testRead() {
        EventCache::write('name', 'Kevin');
        $this->assertEquals('Kevin', EventCache::read('name'));
    }
    public function testSquashArrayTo1Dim() {
        $y = array(
            'a' => array(1, 2, 3, 4),
            'b' => array(5, 6, 7, 8),
            'c' => array(9, 10, 11, 12),
        );

        $x = EventCache::squashArrayTo1Dim($y);

        $this->assertEquals('7bd4c63ba2cdadb060f5730e7bf66a30', $x['a']);
        $this->assertTrue(count($x) === 3);
    }
    
    public function testMagic() {

        $EventCacheInst = EventCache::getInstance();
        $EventCacheInst->flush();
        $this->DBCalled = false;

        $events = $EventCacheInst->getEvents();
        $this->assertTrue(empty($events));
        
        $this->assertEquals('Kevin', $this->heavyDBFunction('Kevin'));
        $this->assertTrue($this->DBCalled);
        $this->assertEquals('van Zonneveld', $this->heavyDBFunction('van Zonneveld', 5));
        $this->assertTrue($this->DBCalled);


        $this->assertEquals('Kevin', $this->heavyDBFunction('Kevin'));
        $this->assertTrue(!$this->DBCalled);

        $events = $EventCacheInst->getEvents();
        $this->assertArrayHasKey('testapp-event-deploy', $events);
        $this->assertTrue(count($events) === 2);
        
        $this->assertEquals('Kevin', EventCache::read($this->MagicKey));
    }
    public function heavyDBFunction($name, $retry = 3) {
        $this->DBCalled = false;
        $args = func_get_args();
        $this->MagicKey = EventCache::magicKey($this, __FUNCTION__, $args, array(
            'deploy',
            'Server::afterSave',
        ), array(
            'unique' => 'otherSpecificStuff'
        ));
        
        return EventCache::magic($this, __FUNCTION__, $args, array(
            'deploy',
            'Server::afterSave',
        ), array(
            'unique' => 'otherSpecificStuff'
        ));
    }
    public function _heavyDBFunction($name, $retry = 3) {
        $this->DBCalled = true;
        return $name;
    }

}
?>