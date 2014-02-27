#CakePHP 2.x SubQueryBehavior#

##Introduction##

Subqueries can sometimes be very useful. Take for example negative select queries which are used to double-check
your database integrity. Often these things can be done using counterCache, but usually you can't add these fields
to every table in your database and usually you don't want to and so subqueries are the answer.

In CakePHP creating a subquery can be very tricky. You were required to use all sorts of datasource methods to 
create an SQL query and insert that into your query.

This behavior does this automatically using the ORM. It even allows you to use Containable/Linkable or whatever
behavior inside the subquery. As long as it returns a single recordset. The syntax is exactly the same as a
normal find() call.

##Usage##

There are two ways to use subqueries.

1. In virtual fields to add additional fields to your queries.
2. As conditions to use for negative select queries.

For both operations using subqueries you need to clone the plugin to your Plugin folder. Then add the behavior
to the model using the $actsAs property:

```php
<?php

public $actsAs = array('SubQuery.SubQuery');

?>
```

###Subqueries as virtual fields###

Let's say we want to simulate counterCache. We want all the articles with the number of comments. What we need is
to add an additional field to the query that counts the records in the associated model. Here is the example how
that could look in the code (from within the model):

```php
<?php

$this->virtualFields = array(
	'count' => $this->Comment->subQuery(array(
		'fields' => array('COUNT(id)')
		'conditions' => array('Comment.article_id = Article.id')
	))
);

$result = $this->find('all', array(
	'fields' => array('id', 'count'),
	'recursive' => -1
));

?>
```

This will generate a query looking something like this:

```sql
SELECT `id`, (SELECT COUNT(`id`) FROM `comments` AS Comment WHERE `Comment`.`article_id` = `Article`.`id`) AS `Article__count`
FROM `articles` AS `Article`;
```

Of course you can do a lot more cool things with subqueries than this. You could get the count with additional
conditions. Such as the number of comments for this month per article or even for that for a specific user.

###Subqueries as conditions###

Using subqueries as conditions is a bit different. You need to wrap the entire condition in a method call. For
example with this negative select query inside the comment model. This time I am checking database integrity. I
want all floating comments, which means all comments without an article. Obviously there should be none. Here is the
example in code:

```php
<?php

$result = $this->find('all', array(
	'fields' => array('id'),
	'conditions' => array(
		$this->subQueryCondition($this->Article, array(
			'article_id NOT' => array(
				'fields' => array('id'),
			)
		))
	),
	'recursive' => -1
));

?>
```

This should generate a query like:

```sql
SELECT `Comment`.`id`
FROM `comments` AS `Comment`
WHERE `Comment`.`article_id` NOT IN (SELECT `Article`.`id` FROM `articles` AS `Article`)
```
