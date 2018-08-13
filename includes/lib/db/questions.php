<?php
namespace lib\db;


class questions
{
	public static function save_sort($_sort)
	{
		$query = [];
		foreach ($_sort as $key => $value)
		{
			$sort = $key + 1;
			$query[] = " UPDATE questions SET questions.sort = $sort WHERE questions.id = $value LIMIT 1 ";
		}

		$query = implode(';', $query);

		return \dash\db::query($query, true, ['multi_query' => true]);
	}


	public static function get_sort($_where)
	{
		$limit = null;
		$only_one_value = false;
		if(isset($_where['limit']))
		{
			if($_where['limit'] === 1)
			{
				$only_one_value = true;
			}

			$limit = " LIMIT $_where[limit] ";
		}

		unset($_where['limit']);

		$where = \dash\db\config::make_where($_where);
		if($where)
		{
			$query = "SELECT * FROM questions WHERE $where ORDER BY questions.sort ASC, questions.id ASC $limit";
			$result = \dash\db::get($query, null, $only_one_value);
			return $result;
		}
		return false;

	}


	public static function insert()
	{
		\dash\db\config::public_insert('questions', ...func_get_args());
		return \dash\db::insert_id();
	}


	public static function update()
	{
		return \dash\db\config::public_update('questions', ...func_get_args());
	}


	public static function get()
	{
		return \dash\db\config::public_get('questions', ...func_get_args());
	}


	public static function search($_string = null, $_option = [])
	{
		$default_option =
		[
			'search_field' => " (title LIKE '%__string__%' ) ",
		];

		$_option = array_merge($default_option, $_option);
		return \dash\db\config::public_search('questions', $_string, $_option);
	}


	public static function get_count()
	{
		return \dash\db\config::public_get_count('questions', ...func_get_args());
	}

}
?>