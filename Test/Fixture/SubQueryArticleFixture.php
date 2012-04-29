<?php
class SubQueryArticleFixture extends CakeTestFixture {

	public $fields = array(
		'id' => array('type' => 'integer', 'key' => 'primary'),
		'title' => array('type' => 'string', 'length' => 255),
	);

	public $records = array(
		array('id' => 1, 'title' => 'Some useless shizzle about subqueries'),
		array('id' => 2, 'title' => 'Even more wasted words on subqueries'),
	);

}
