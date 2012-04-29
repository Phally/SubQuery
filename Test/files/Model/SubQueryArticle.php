<?php
class SubQueryArticle extends CakeTestModel {

	public $actsAs = array('SubQuery.SubQuery');

	public $hasMany = array('SubQueryComment');

	public function getAllIdsWithoutComments() {
		return $this->find('list', array(
			'fields' => array('id'),
			'conditions' => array(
				$this->subQueryCondition($this->SubQueryComment, array(
					'id NOT' => array(
						'fields' => array('sub_query_article_id'),
					)
				))
			),
			'recursive' => -1
		));
	}

	public function getAllIdsWithComments() {
		return $this->find('list', array(
			'fields' => array('id'),
			'conditions' => array(
				$this->subQueryCondition($this->SubQueryComment, array(
					'id' => array(
						'fields' => array('sub_query_article_id'),
					)
				))
			),
			'recursive' => -1
		));
	}

}