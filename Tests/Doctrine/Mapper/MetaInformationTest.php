<?php
namespace FS\SolrBundle\Tests\Doctrine\Mapper;

use FS\SolrBundle\Doctrine\Mapper\MetaInformation;

/**
 *
 * @group mapper
 */
class MetaInformationTest extends \PHPUnit_Framework_TestCase {
	private function createFieldObject($name, $value) {
		$value = new \stdClass();
		$value->name = $name;
		$value->value = $value;

		return $value;
	}
	
	public function testHasField_FieldExists() {
		$value1 = $this->createFieldObject('field1', 'oldfieldvalue');
		$value2 = $this->createFieldObject('field2', true);
		
		$fields = array(
				'field1' => $value1,
				'field2' => $value2
		);
		
		$information = new MetaInformation();
		$information->setFields($fields);

		$this->assertTrue($information->hasField('field2'), 'metainformation should have field2');
	}

	public function testHasField_FieldNotExists() {
		$value1 = $this->createFieldObject('field1', 'oldfieldvalue');
	
		$fields = array(
				'field1' => $value1,
		);
	
		$information = new MetaInformation();
		$information->setFields($fields);
	
		$this->assertFalse($information->hasField('field2'), 'unknown field field2');
	}	
	
	public function testSetFieldValue() {
		$value1 = $this->createFieldObject('field1', 'oldfieldvalue');
		$value2 = $this->createFieldObject('field2', true);		
		
		$fields = array(
			'field1' => $value1,
			'field2' => $value2
		);
		
		$information = new MetaInformation();
		$information->setFields($fields);
		
		$expectedValue = 'newFieldValue';
		$information->setFieldValue('field2', $expectedValue);
		
		$this->assertEquals($expectedValue, $information->getField('field2')->value, 'field2 should have new value');
	}
	
	public function testHasCallback() {
		$information = new MetaInformation();
		$information->setSynchronizationCallback('function');
		
		$this->assertTrue($information->hasSynchronizationFilter(), 'has callback');
	}
}

?>