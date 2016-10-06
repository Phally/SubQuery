<?php
/**
 * SubQueryBehavior
 *
 * Adds two methods to the model which can be used to generate subqueries for
 * conditions and virtual fields. Check the documentation on GitHub for usage
 * examples.
 *
 * @author Frank de Graaf (Phally)
 * @link https://www.github.com/Phally/SubQuery
 * @license MIT
 */
class SubQueryBehavior extends ModelBehavior {

	/**
	 * Map of datasource aliases.
	 *
	 * @var array
	 */
	protected $_nameMap = array();

	/**
	 * Creates a formatted subquery condition to use in the conditions array. It
	 * will return a datasource expression which the core will accept as a
	 * complete condition.
	 *
	 * @param Model $model Instance of the primary model.
	 * @param Model $on Instance of the model to use for the subquery.
	 * @param array $condition A single condition definition (like: array('id NOT' => $query)).
	 * @return stdObject A datasource expression containing the subquery condition.
	 */
	public function subQueryCondition(Model $model, Model $on, array $condition) {
		list($field, $query) = each($condition);

		$not = false;
		if (stripos($field, 'NOT')) {
			$not = true;
			$field = str_replace('NOT', '', $field);
		}
		$field = $model->getDataSource()->name("{$model->alias}.$field");

		$sql = 'IN (' . $this->_getQuery($on, $query) . ')';
		if ($not) {
			$sql = 'NOT ' . $sql;
		}
		$sql = "$field $sql";

		return $model->getDataSource()->expression($sql);
	}

	/**
	 * Gets a subquery for use as a virtual field for example.
	 *
	 * @param Model $model Instance of the primary model.
	 * @param array $query The find() query.
	 * @param boolean $virtualField Using false here will wrap the query in a datasource expression.
	 * @return type
	 */
	public function subQuery(Model $model, array $query, $virtualField = true) {
		$sql = $this->_getQuery($model, $query);
		if ($virtualField) {
			return $sql;
		}
		return $model->getDataSource()->expression('(' . $sql . ')');
	}

	/**
	 * Gets the raw SQL for a query.
	 *
	 * @param Model $model Instance of the model to run the query on.
	 * @param array $query The find() query.
	 * @return string Raw SQL.
	 */
	protected function _getQuery(Model $model, array $query) {
		$db = $model->getDataSource();
		$query['table'] = $db->fullTableName($model);
		$result = $db->buildStatement($query, $model);
		return $result;
	}
}
