<?php
namespace lib\app;

/**
 * Class for answer.
 */
class answer
{
	use \lib\app\answer\datalist;
	use \lib\app\answer\get;

	public static function dateNow()
	{
		return date("Y-m-d H:i:s");
	}


	public static function my_answer($_survey_id, $_question_id)
	{
		if(!\dash\user::id())
		{
			\dash\notif::error(T_("Please login to conitinue"));
			return false;
		}

		$survey_id = \dash\coding::decode($_survey_id);
		if(!$survey_id)
		{
			\dash\notif::error(T_("Survay id not set"));
			return false;
		}

		$question_id = \dash\coding::decode($_question_id);
		if(!$question_id)
		{
			\dash\notif::error(T_("Question id not set"));
			return false;
		}

		$get_answer =
		[
			'answerdetails.question_id' => $question_id,
			'answerdetails.user_id'     => \dash\user::id(),
			'answerdetails.survey_id'   => $survey_id,
			// 'limit'                     => 1,
		];

		$old_answer_detail = \lib\db\answerdetails::get_join($get_answer);

		if(!$old_answer_detail)
		{
			return null;
		}

		return $old_answer_detail;


	}

	public static function add($_survey_id, $_question_id, $_args)
	{
		if(!\dash\user::id())
		{
			\dash\notif::error(T_("Please login to conitinue"));
			return false;
		}

		$survey_id = \dash\coding::decode($_survey_id);
		if(!$survey_id)
		{
			\dash\notif::error(T_("Survay id not set"));
			return false;
		}

		$question_id = \dash\coding::decode($_question_id);
		if(!$question_id)
		{
			\dash\notif::error(T_("Invalid question id"));
			return false;
		}

		$survey_detail = \lib\db\surveys::get(['id' => $survey_id, 'limit' => 1]);

		if(!$survey_detail)
		{
			\dash\notif::error(T_("Invalid survey id"));
			return false;
		}

		$question_detail = \lib\db\questions::get(['survey_id' => $survey_id, 'id' => $question_id, 'limit' => 1]);

		if(!$question_detail || !isset($question_detail['id']))
		{
			\dash\notif::error(T_("Invalid question id"));
			return false;
		}

		$step = 1;
		if(array_key_exists('sort', $question_detail))
		{
			$step = intval($question_detail['sort']);
		}

		$question_id = $question_detail['id'];

		$question_detail = \lib\app\question::ready($question_detail);

		\dash\app::variable($_args);

		$answer = \dash\app::request('answer');
		$skip   = \dash\app::request('skip') ? true : false;
		if($skip)
		{
			$answer = null;
		}

		if(!$skip)
		{
			$validation = self::answer_validate($question_detail, $answer);
			if(!$validation)
			{
				return false;
			}
		}

		$require = self::check_require($question_detail, $answer, $skip);
		if(!$require)
		{
			\dash\notif::error(T_("Please fill this field to continue"), 'answer');
			return false;
		}

		if(\dash\temp::get('realAnswerTitle'))
		{
			$answer = \dash\temp::get('realAnswerTitle');
		}


		$answer_term_id = null;

		$multiple_choice = false;

		if(!$skip)
		{
			if(is_array($answer))
			{
				$multiple_choice = true;
			}

			if(($answer || $answer === '0') && !$multiple_choice)
			{
				$answer_term_id = \lib\db\answerterms::get_id($answer, $question_detail['type']);
				if(!$answer_term_id)
				{
					\dash\notif::error(T_("No way to inset your answer"));
					return false;
				}
			}
		}

		$load_old_answer =
		[
			'user_id'   => \dash\user::id(),
			'survey_id' => $survey_id,
			'limit'     => 1,
		];

		$load_old_answer = \lib\db\answers::get($load_old_answer);

		if(!$load_old_answer)
		{
			$insert_answer =
			[
				'user_id'      => \dash\user::id(),
				'survey_id'    => $survey_id,
				'startdate'    => self::dateNow(),
				'step'         => 1,
				'lastquestion' => $question_id,
				'status'       => 'start',
				'ref'          => null,
				'skip'         => $skip   ? 1 : null,
				'skiptry'      => $skip   ? 1 : null,
				'answer'       => $answer && !$skip ? 1 : null,
				'answertry'    => $answer && !$skip ? 1 : null,
			];
			$answer_id = \lib\db\answers::insert($insert_answer);
		}
		else
		{
			$answer_id       = $load_old_answer['id'];

			$answer_count    = (isset($load_old_answer['answer']) && $load_old_answer['answer'])            ? intval($load_old_answer['answer'])    	: 0;
			$skip_count      = (isset($load_old_answer['skip']) && $load_old_answer['skip'])           		? intval($load_old_answer['skip'])      	: 0;
			$answertry_count = (isset($load_old_answer['answertry']) && $load_old_answer['answertry']) 		? intval($load_old_answer['answertry']) 	: 0;
			$skiptry_count   = (isset($load_old_answer['skiptry']) && $load_old_answer['skiptry'])     		? intval($load_old_answer['skiptry'])   	: 0;
			$countblock      = (isset($survey_detail['countblock']) && $survey_detail['countblock'])        ? intval($survey_detail['countblock'])      : 0;

			$update_answer = [];

			if($skip)
			{
				$update_answer['skip']    = $skip_count + 1;
				$update_answer['skiptry'] = $skiptry_count + 1;
			}

			if($answer)
			{
				$update_answer['answer']    = $answer_count + 1;
				$update_answer['answertry'] = $answertry_count + 1;
			}

			$update_answer['step']         = $step;
			$update_answer['lastquestion'] = $question_id;
			$update_answer['lastmodified'] = self::dateNow();

			if(intval($step) === intval($countblock))
			{
				// if(isset($load_old_answer['complete']) && $load_old_answer['complete'])
				// {
				// 	// if before this request the question is completed not complete again
				// }
				// else
				// {
					// need to check all required question is answered
					$check_require_is_answer = \lib\db\answers::required_question_is_answered($survey_id, \dash\user::id());
					if($check_require_is_answer === true)
					{
						$update_answer['complete'] = 1;
						$update_answer['enddate']  = self::dateNow();
					}
					else
					{
						$update_answer['complete'] = 0;
						$update_answer['enddate']  = null;

						\dash\temp::set('notAnsweredQuestion', $check_require_is_answer);

						$msg = T_("You not answer to some required question"). ' '. T_("Your survey is not complete");

						if(isset($check_require_is_answer[0]['sort']))
						{
							$msg = "<a href='". \dash\url::kingdom(). '/s/'. \dash\coding::encode($survey_id). '?step='. $check_require_is_answer[0]['sort']. "'>$msg</a>";
						}

						\dash\notif::warn($msg);
					}
				// }
			}

			\lib\db\answers::update($update_answer, $answer_id);
		}

		$old_answer_detail_args =
		[
			'user_id'     => \dash\user::id(),
			'survey_id'   => $survey_id,
			'answer_id'   => $answer_id,
			'question_id' => $question_id,
		];

		$time_key = 'dateview_'. (string) $survey_id. '_'. (string) $step;
		$dateview = \dash\session::get($time_key) && is_string(\dash\session::get($time_key)) ? \dash\session::get($time_key) : self::dateNow();

		if(!$multiple_choice || $skip)
		{
			$old_answer_detail_args['limit'] = 1;
			$old_answer_detail = \lib\db\answerdetails::get($old_answer_detail_args);
			if(isset($old_answer_detail['id']))
			{
				$update_answer_detail =
				[
					'answerterm_id' => $answer_term_id,
					'skip'          => $skip ? 1 : null,
					'dateanswer'    => self::dateNow(),
				];

				\lib\db\answerdetails::update($update_answer_detail, $old_answer_detail['id']);
			}
			else
			{
				// @chekc telegram have not url module!!

				$insert_answer_detail =
				[
					'user_id'       => \dash\user::id(),
					'survey_id'     => $survey_id,
					'answer_id'     => $answer_id,
					'question_id'   => $question_id,
					'answerterm_id' => $answer_term_id,
					'skip'          => $skip ? 1 : null,
					'dateview'      => $dateview,
					'dateanswer'    => self::dateNow(),
				];

				\lib\db\answerdetails::insert($insert_answer_detail);
			}
		}
		else
		{
			// mutli choise mode
			$old_answer_detail = \lib\db\answerdetails::get($old_answer_detail_args);
			if($old_answer_detail)
			{
				\lib\db\answerdetails::delete_where($old_answer_detail_args);
			}

			// insert new answer detail
			$multi_insert = [];
			foreach ($answer as $key => $value)
			{
				$answer_term_id = \lib\db\answerterms::get_id($value, $question_detail['type']);
				$multi_insert[] =
				[
					'user_id'       => \dash\user::id(),
					'survey_id'     => $survey_id,
					'answer_id'     => $answer_id,
					'question_id'   => $question_id,
					'answerterm_id' => $answer_term_id,
					'dateview'      => $dateview,
					'dateanswer'    => self::dateNow(),
				];
			}

			if(!empty($multi_insert))
			{
				\lib\db\answerdetails::multi_insert($multi_insert);
			}
		}

		// \dash\notif::ok(T_("Your answer was saved"));
		return true;

	}


	public static function check_require($_question_detail, $_answer, $_skip = false)
	{
		// check is require
		if(isset($_question_detail['require']) && $_question_detail['require'])
		{
			if((!$_answer && $_answer !== '0') || (is_array($_answer) && empty($_answer)))
			{
				if(isset($_question_detail['type']) && $_question_detail['type'] === 'confirm')
				{
					return true;
				}

				if(!$_skip)
				{
					return false;
				}
			}
		}
		return true;
	}



	public static function answer_validate($_question_detail, $_answer)
	{
		$myType = null;

		if(isset($_question_detail['type']))
		{
			$myType = $_question_detail['type'];
		}

		$default = \lib\app\question::get_type($myType, 'default_load');
		$min    = 0;
		if(isset($default['min']))
		{
			$min = $default['min'];
		}

		$max = 1E+9;

		if(isset($default['max']))
		{
			$max = $default['max'];
		}

		if(isset($_question_detail['setting'][$myType]['min']))
		{
			$min = intval($_question_detail['setting'][$myType]['min']);
		}

		if(isset($_question_detail['setting'][$myType]['max']))
		{
			$max = intval($_question_detail['setting'][$myType]['max']);
		}


		$valid = true;
		switch ($_question_detail['type'])
		{
			case 'short_answer':
			case 'descriptive_answer':
				if(!is_string($_answer))
				{
					\dash\notif::error(T_("Invalid answer"), 'answer');
					$valid = false;
				}
				if($_answer)
				{
					if(mb_strlen($_answer) < $min || mb_strlen($_answer) > $max)
					{
						\dash\notif::error(T_("Your answer is out of range"), 'answer');
						$valid = false;
					}
				}
				break;

			case 'numeric':
			case 'rating':
			case 'rangeslider':
				if($_answer)
				{
					if(!is_numeric($_answer))
					{
						$valid = false;
					}

					if(intval($_answer) < $min || intval($_answer) > $max)
					{
						\dash\notif::error(T_("Your answer is out of range"), 'answer');
						$valid = false;
					}
				}
				break;

			case 'single_choice':
			case 'dropdown':
				if($_answer)
				{
					if(isset($_question_detail['choice']) && is_array($_question_detail['choice']))
					{
						$choice_title = array_column($_question_detail['choice'], 'id');

						if(!in_array($_answer, $choice_title))
						{
							if(\dash\permission::supervisor())
							{
								\dash\notif::error(T_("This choice not found in choice list!"). ' _ '.$_answer . ' _ '. @$_question_detail['title'], 'answer');
							}
							else
							{
								\dash\notif::error(T_("This choice not found in choice list!"), 'answer');
							}
							$valid = false;
						}
						else
						{
							$myKey = array_search($_answer, $choice_title);

							if(isset($_question_detail['choice'][$myKey]) && array_key_exists('title', $_question_detail['choice'][$myKey]))
							{
								\dash\temp::set('realAnswerTitle', $_question_detail['choice'][$myKey]['title']);
							}
						}
					}
				}
				break;

			case 'multiple_choice':
				if(is_array($_answer) && isset($_question_detail['choice']) && is_array($_question_detail['choice']))
				{
					$choice_title = array_column($_question_detail['choice'], 'id');
					foreach ($_answer as $key => $value)
					{
						if(!in_array($value, $choice_title))
						{
							\dash\notif::error(T_("This choice not found in choice list!"), 'answer');
							$valid = false;
						}
					}

					$realAnswerTitle = [];

					foreach ($_answer as $key => $value)
					{
						$myKey = array_search($value, $choice_title);

						if(isset($_question_detail['choice'][$myKey]) && array_key_exists('title', $_question_detail['choice'][$myKey]))
						{
							array_push($realAnswerTitle, $_question_detail['choice'][$myKey]['title']);
						}
					}

					\dash\temp::set('realAnswerTitle', $realAnswerTitle);
				}
				break;

			case 'date':
				if(\dash\date::db($_answer) === false)
				{
					\dash\notif::error(T_("Invalid date"), 'answer');
					$valid = false;
				}
				break;

			case 'time':
				if(\dash\date::make_time(\dash\utility\convert::to_en_number($_answer)) === false)
				{
					\dash\notif::error(T_("Invalid time"), 'answer');
					$valid = false;
				}
				break;

			case 'email':
				if($_answer && !filter_var($_answer, FILTER_VALIDATE_EMAIL))
				{
					\dash\notif::error(T_("Invalid email"), 'answer');
					$valid = false;
				}
				break;

			case 'mobile':
				if($_answer && !\dash\utility\filter::mobile(\dash\utility\convert::to_en_number($_answer)))
				{
					\dash\notif::error(T_("Invalid mobile"), 'answer');
					$valid = false;
				}
				break;

			case 'website':
				if($_answer && !filter_var($_answer, FILTER_VALIDATE_URL))
				{
					\dash\notif::error(T_("Invalid url"), 'answer');
					$valid = false;
				}
				break;
		}

		return $valid;
	}


	/**
	 * ready data of question to load in api
	 *
	 * @param      <type>  $_data  The data
	 */
	public static function ready($_data)
	{
		$result    = [];
		$startdate = null;
		$enddate   = null;

		foreach ($_data as $key => $value)
		{
			switch ($key)
			{
				case 'id':
				case 'user_id':
				case 'survey_id':
					$result[$key] = \dash\coding::encode($value);
					break;

				case 'startdate':
					$result[$key] = $value;
					$startdate = strtotime($value);
					break;

				case 'enddate':
					$result[$key] = $value;
					$enddate = strtotime($value);
					break;

				default:
					$result[$key] = $value;
					break;
			}
		}

		if($startdate && $enddate)
		{
			$result['answer_in'] = \dash\utility\human::time($enddate - $startdate, null, null, 'sec');
		}

		return $result;
	}
}
?>