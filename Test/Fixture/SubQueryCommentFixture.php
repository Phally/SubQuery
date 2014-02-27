<?php
class SubQueryCommentFixture extends CakeTestFixture {

	public $fields = array(
		'id' => array('type' => 'integer', 'key' => 'primary'),
		'sub_query_article_id' => array('type' => 'integer'),
		'comment' => array('type' => 'text'),
	);

	public $records = array(
		array('id' => 1, 'sub_query_article_id' => 1, 'comment' => 'Really? Dude, you suck.'),
		array('id' => 2, 'sub_query_article_id' => 1, 'comment' => 'One word: Genius.'),
		array('id' => 3, 'sub_query_article_id' => 3, 'comment' => 'I am spam.'),
	);

}
