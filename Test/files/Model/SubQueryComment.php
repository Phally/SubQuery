<?php
class SubQueryComment extends CakeTestModel {

	public $actsAs = array('SubQuery.SubQuery');

	public $belongsTo = array('SubQueryArticle');

}