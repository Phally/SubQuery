<?php
class SubQueryBehaviorTest extends CakeTestCase {

	public $fixtures = array('plugin.sub_query.sub_query_article', 'plugin.sub_query.sub_query_comment');

	protected $_SubQueryArticle = null;

	public static function setUpBeforeClass() {
		parent::setUpBeforeClass();
		App::build(array('Model' => dirname(__FILE__) . DS . '..' . DS . '..' . DS . '..' . DS . 'files' . DS . 'Model' . DS));
	}

	public function setUp() {
		parent::setUp();
		$this->_SubQueryArticle = ClassRegistry::init('SubQueryArticle');
	}

	public function testSubQueryCondition() {
		$expected = array(
			'SubQueryArticle' => array(
				'id' => 2
			)
		);

		$result = $this->_SubQueryArticle->find('first', array(
			'fields' => array('id'),
			'conditions' => array(
				$this->_SubQueryArticle->subQueryCondition($this->_SubQueryArticle->SubQueryComment, array(
					'id NOT' => array(
						'fields' => array('sub_query_article_id'),
					)
				))
			),
			'recursive' => -1
		));

		$this->assertEquals($expected, $result);

		$expected = array(
			array(
				'SubQueryArticle' => array(
					'id' => 1
				)
			)
		);

		$result = $this->_SubQueryArticle->find('all', array(
			'fields' => array('id'),
			'conditions' => array(
				$this->_SubQueryArticle->subQueryCondition($this->_SubQueryArticle->SubQueryComment, array(
					'id' => array(
						'fields' => array('sub_query_article_id'),
					)
				))
			),
			'recursive' => -1
		));

		$this->assertEquals($expected, $result);
	}

	public function testSubQuery() {
		$expected = array(
			'SubQueryArticle' => array(
				'count' => 2
			)
		);

		$this->_SubQueryArticle->virtualFields = array('count' => $this->_SubQueryArticle->SubQueryComment->subQuery(array('fields' => array('COUNT(id)'))));
		$result = $this->_SubQueryArticle->find('first', array(
			'fields' => array('count'),
			'group' => array('count'),
			'recursive' => -1
		));

		$this->assertEquals($expected, $result);
	}

	public function testInsideModelMethods() {
		$expected = array(2 => 2);
		$result = $this->_SubQueryArticle->getAllIdsWithoutComments();
		$this->assertEquals($expected, $result);

		$expected = array(1 => 1);
		$result = $this->_SubQueryArticle->getAllIdsWithComments();
		$this->assertEquals($expected, $result);
	}

}
