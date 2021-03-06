<?php
namespace lib\app\answer;

trait datalist
{

	public static $sort_field =
	[
		'startdate',
		'enddate',
		'step',
		'complete',
		'answer',
		'skip',
	];


	/**
	 * Gets the survey.
	 *
	 * @param      <type>  $_args  The arguments
	 *
	 * @return     <type>  The survey.
	 */
	public static function list($_string = null, $_args = [])
	{
		if(!\dash\user::id())
		{
			return false;
		}

		$default_meta =
		[
			'sort'  => null,
			'order' => null,
		];

		if(!is_array($_args))
		{
			$_args = [];
		}

		$_args = array_merge($default_meta, $_args);

		if($_args['sort'] && !in_array($_args['sort'], self::$sort_field))
		{
			$_args['sort'] = null;
		}


		$result            = \lib\db\answers::search($_string, $_args);

		$score = null;
		if(is_array($result) && isset($result[0]['survey_id']))
		{
			$user_id = array_column($result, 'user_id');
			$user_id = array_filter($user_id);
			$user_id = array_unique($user_id);
			if($user_id)
			{
				$score = \lib\db\answerdetails::get_users_score($result[0]['survey_id'], $user_id);
				if(is_array($score))
				{
					$score = array_combine(array_column($score, 'user_id'), $score);
				}
			}
		}
		$temp              = [];

		foreach ($result as $key => $value)
		{
			$check = self::ready($value, ['score' => $score]);
			if($check)
			{
				$temp[] = $check;
			}
		}

		return $temp;
	}
}
?>