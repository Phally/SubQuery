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
		$this->_setDataSource($model);
		$model->find('all', $query);
		$result = $model->getDataSource()->getSql();
		$this->_resetDataSource($model);
		return $result;
	}

	/**
	 * Switches the datasource to one that can return raw SQL.
	 *
	 * @param Model $model Instance of the model to do the switch on.
	 */
	protected function _setDataSource(Model $model) {
		$ds = $model->getDataSource();
		$sourceName = $this->_nameMap[$model->alias] = ConnectionManager::getSourceName($ds);
		$dummyName = "_sub_query_$sourceName";
		if (!in_array($dummyName, ConnectionManager::sourceList())) {
			$config = $ds->config;
			$config['datasource'] = $this->_createDatasource($ds);
			App::uses($config['datasource'], 'Model/Datasource');
			$new = ConnectionManager::create($dummyName, $config);
			$new->setConnection($ds->getConnection());
		}
		$model->setDataSource($dummyName);
	}

	/**
	 * Returns the datasource to its original state.
	 *
	 * @param Model $model Instance of the model to reset.
	 */
	protected function _resetDataSource(Model $model) {
		$model->setDataSource($this->_nameMap[$model->alias]);
	}

	/**
	 * Creates a dummy datasource which can return SQL.
	 *
	 * @param DataSource $ds The datasource to fake.
	 * @return string Name of the created class.
	 */
	protected function _createDataSource(DataSource $ds) {
		$base = get_class($ds);
		$class = "SubQuery$base";
		if (class_exists($class)) {
			return $class;
		}
		$code = <<<CODE

class $class extends $base {

	protected \$_sql = null;

	public function connect() {
		return true;
	}

	public function execute(\$sql, \$options = array(), \$params = array()) {
		\$this->_sql = \$sql;
		return true;
	}

	public function getSql() {
		return \$this->_sql;
	}

	public function setConnection(\$connection) {
		\$this->_connection = \$connection;
	}
}

CODE;
		eval($code);
		return $class;
	}
}
