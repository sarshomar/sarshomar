<?php
namespace lib\tg;
// use telegram class as bot
use \dash\social\telegram\tg as bot;
use \dash\social\telegram\step;

class questionSender
{
	public static function analyse($_questionData, $_answer)
	{
		// get message body
		$text         = self::body($_questionData, $_answer);
		$reply_markup = false;


		switch ($_questionData['type'])
		{
			case 'short_answer':
				self::short_answer($_questionData, $text, $reply_markup, $_answer);
				break;

			case 'descriptive_answer':
				self::descriptive_answer($_questionData, $text, $reply_markup, $_answer);
				break;

			case 'numeric':
				self::numeric($_questionData, $text, $reply_markup, $_answer);
				break;

			case 'rangeslider':
				self::rangeslider($_questionData, $text, $reply_markup, $_answer);
				break;

			case 'date':
				self::date($_questionData, $text, $reply_markup, $_answer);
				break;

			case 'time':
				self::time($_questionData, $text, $reply_markup, $_answer);
				break;

			case 'mobile':
				self::mobile($_questionData, $text, $reply_markup, $_answer);
				break;

			case 'email':
				self::email($_questionData, $text, $reply_markup, $_answer);
				break;

			case 'website':
				self::website($_questionData, $text, $reply_markup, $_answer);
				break;

			case 'single_choice':
			case 'dropdown':
				self::single_choice($_questionData, $text, $reply_markup, $_answer);
				break;

			case 'multiple_choice':
				self::multiple_choice($_questionData, $text, $reply_markup, $_answer);

				if(step::get('qMessageId') && step::get('qChatId'))
				{
					$updateData =
					[
						'message_id'   => step::get('qMessageId'),
						'chat_id'      => step::get('qChatId'),
						// 'text'         => $text,
						'reply_markup' => $reply_markup,
					];
					bot::editMessageReplyMarkup($updateData);
					return true;
				}

				break;

			case 'rating':
				self::rating($_questionData, $text, $reply_markup, $_answer);
				break;

			default:
				// not support this type
				bot::sendMessage(T_('This type of message is not supported!'). $_questionData['type']);
				return false;
				break;
		}

		// generate result
		$sendQData =
		[
			'text'         => $text,
			'reply_markup' => $reply_markup
		];

		// send message
		$questionSended = bot::sendMessage($sendQData);

		if($_questionData['type'] === 'multiple_choice')
		{
			// get result of sended message
			$qMessageId     = null;
			$qChatId        = null;
			if(isset($questionSended['result']['message_id']))
			{
				$qMessageId = $questionSended['result']['message_id'];
			}
			if(isset($questionSended['result']['chat']['id']))
			{
				$qChatId = $questionSended['result']['chat']['id'];
			}
			step::set('qMessageId', $qMessageId);
			step::set('qChatId', $qChatId);
		}
	}


	private static function body($_questionData, $_answer)
	{
		$bodyTxt = '';
		if(isset($_questionData['title']))
		{
			$bodyTxt .= "❔";
			$bodyTxt .= " <b>". $_questionData['title']. "</b>";
			// add require badge
			if(isset($_questionData['require']))
			{
				$bodyTxt .= " <code>*". T_('Require'). "</code>";
			}
			$bodyTxt .= "\n\n";
		}

		if(isset($_questionData['desc']))
		{
			$temp = $_questionData['desc'];
			$temp = str_replace('&nbsp;', ' ', $temp);
			$temp = str_replace('</p>', "</p>\n", $temp);
			$temp = strip_tags($temp, '<br><b>');
			$bodyTxt .= $temp;
		}

		if(isset($_questionData['media']['file']))
		{
			$bodyTxt .= "\n". "<a href='". $_questionData['media']['file']. "'>". T_("File"). "</a>";
		}

		// get user answer list
		if($_answer)
		{
			$bodyTxt .= "\n☑️ ". T_('Your last answer');
			if(count($_answer) > 1)
			{
				$bodyTxt .= "\n<pre>". implode("\n", $_answer). "</pre>";
			}
			else
			{
				$bodyTxt .= " <code>". implode(T_(', '), $_answer). "</code>";
			}
		}
		return $bodyTxt;
	}


	private static function short_answer($_question, &$_txt, &$_kbd, $_answer)
	{
		$_txt .= "\n\n";
		$_txt .= '❇️ '. T_('Please wrote short answer for this question');
	}


	private static function descriptive_answer($_question, &$_txt, &$_kbd, $_answer)
	{
		$_txt .= "\n\n";
		$_txt .= '❇️ '. T_('Please describe your answer');
	}


	private static function numeric($_question, &$_txt, &$_kbd, $_answer)
	{
		$min = null;
		$max = null;
		if(isset($_question['setting']['numeric']['min']))
		{
			$min = $_question['setting']['numeric']['min'];
		}
		if(isset($_question['setting']['numeric']['max']))
		{
			$max = $_question['setting']['numeric']['max'];
		}

		$_txt .= "\n\n";
		$_txt .= '❇️ '. T_('Please enter number between :min and :max', ['min' => $min, 'max' => $max]);
	}


	private static function rangeslider($_question, &$_txt, &$_kbd, $_answer)
	{
		$min = null;
		$max = null;
		if(isset($_question['setting']['rangeslider']['min']))
		{
			$min = $_question['setting']['rangeslider']['min'];
		}
		if(isset($_question['setting']['rangeslider']['max']))
		{
			$max = $_question['setting']['rangeslider']['max'];
		}

		$_txt .= "\n\n";
		$_txt .= '❇️ '. T_('Please enter number between :min and :max', ['min' => $min, 'max' => $max]);
	}


	private static function date($_question, &$_txt, &$_kbd, $_answer)
	{
		$_txt .= "\n\n";
		$_txt .= '❇️ '. T_('Please enter date in format <code>yyyy-mm-dd</code> like <code>2018-10-28</code>');
	}


	private static function time($_question, &$_txt, &$_kbd, $_answer)
	{
		$_txt .= "\n\n";
		$_txt .= '❇️ '. T_('Please enter time like <code>19:41</code>');
	}


	private static function mobile($_question, &$_txt, &$_kbd, $_answer)
	{
		$_txt .= "\n\n";
		$_txt .= '❇️ '. T_('Please enter mobile number like <code>09350001234</code>');
	}


	private static function email($_question, &$_txt, &$_kbd, $_answer)
	{
		$_txt .= "\n\n";
		$_txt .= '❇️ '. T_('Please enter email like <code>abc@example.com</code>');
	}


	private static function website($_question, &$_txt, &$_kbd, $_answer)
	{
		$_txt .= "\n\n";
		$_txt .= '❇️ '. T_('Please enter website like <code>jibres.com</code>');
	}


	private static function single_choice($_question, &$_txt, &$_kbd, $_answer)
	{
		// $_txt .= "\n\n";
		// $_txt .= '❇️ '. T_('Please choose your answer');

		$surveyId   = null;
		$questionId = null;
		if(isset($_question['survey_id']))
		{
			$surveyId = $_question['survey_id'];
		}
		if(isset($_question['id']))
		{
			$questionId = $_question['id'];
		}

		if(isset($_question['choice']))
		{
			$choices = $_question['choice'];
			if(is_array($choices) && $choices)
			{
				$_kbd =
				[
					'inline_keyboard' => []
				];

				foreach ($choices as $key => $value)
				{
					if(isset($value['title']))
					{
						$itemTitle    = $value['title'];
						$itemId       = $value['title'];
						$selectedMark = '';

						if(isset($value['id']) && $value['id'])
						{
							$itemId = $value['id'];
						}
						if(in_array($itemTitle, $_answer))
						{
							$selectedMark = '☑️ ';
						}

						$_kbd['inline_keyboard'][][] =
						[
							'text' => $value['title'],
							'callback_data' => 'survey_'. $surveyId. ' '. $questionId. ' '. $itemId,
						];
					}
				}

			}
		}
	}


	private static function multiple_choice($_question, &$_txt, &$_kbd, $_answer)
	{
		$_txt .= "\n\n";
		$_txt .= '❇️ '. T_('Please choose your answer');

		$surveyId   = null;
		$questionId = null;
		if(isset($_question['survey_id']))
		{
			$surveyId = $_question['survey_id'];
		}
		if(isset($_question['id']))
		{
			$questionId = $_question['id'];
		}
		// if choice is not exist return
		if(!isset($_question['choice']))
		{
			return false;
		}
		$choices = $_question['choice'];
		// if value is not correct return
		if(!is_array($choices) || count($choices) < 1)
		{
			return false;
		}

		// get user answers to this question
		$userFlyAnswer = step::get('multipleAnswers');
		if(!is_array($userFlyAnswer))
		{
			$userFlyAnswer = [];
		}
		// get new answer value
		$newAnswerKey = intval(step::get('multipleLastAnswer'));
		$newAnswer    = null;
		// get name of this new key
		if($newAnswerKey)
		{
			foreach ($choices as $key => $value)
			{
				if(isset($value['title']) && $value['title'])
				{
					if(isset($value['id']) && $value['id'])
					{
						if($newAnswerKey === $value['id'])
						{
							$newAnswer = $value['title'];
						}
					}
				}
			}

		}

		if(isset($userFlyAnswer[$newAnswerKey]))
		{
			unset($userFlyAnswer[$newAnswerKey]);
		}
		else
		{
			$userFlyAnswer[$newAnswerKey] = $newAnswer;
		}
		// clean array
		if(is_array($userFlyAnswer))
		{
			$userFlyAnswer = array_filter($userFlyAnswer);
		}
		// set for next use
		step::set('multipleAnswers', $userFlyAnswer);

		// define kbd
		$_kbd =
		[
			'inline_keyboard' => []
		];

		foreach ($choices as $key => $value)
		{
			if(isset($value['title']) && $value['title'])
			{
				$itemTitle    = $value['title'];
				$itemId       = $value['title'];
				$selectedMark = '';

				if(isset($value['id']) && $value['id'])
				{
					$itemId = $value['id'];
				}

				if(in_array($itemTitle, $userFlyAnswer))
				{
					$selectedMark = '📌 ';
				}

				$_kbd['inline_keyboard'][][] =
				[
					'text' => $selectedMark. $itemTitle,
					'callback_data' => 'survey_'. $surveyId. ' '. $questionId. ' '. $itemId,
				];
			}
		}

		// if($userFlyAnswer)
		{
			$_kbd['inline_keyboard'][][] =
			[
				'text'          => '💾 '.T_('Save and next'),
				'callback_data' => 'survey_'. $surveyId. ' '. $questionId. ' /save',
			];

		}

	}


	private static function rating($_question, &$_txt, &$_kbd, $_answer)
	{
		$_txt      .= "\n\n";
		$_txt      .= '❇️ '. T_('Please choose your rate');
		$max       = 5;
		$rateEmoji = '⭐️';

		$surveyId   = null;
		$questionId = null;
		if(isset($_question['survey_id']))
		{
			$surveyId = $_question['survey_id'];
		}
		if(isset($_question['id']))
		{
			$questionId = $_question['id'];
		}

		if(isset($_question['setting']['rating']['max']))
		{
			$max = $_question['setting']['rating']['max'];
		}

		if(isset($_question['setting']['rating']['ratetype']))
		{
			switch ($_question['setting']['rating']['ratetype'])
			{
				case 'star':
					$rateEmoji = '⭐️';
					break;

				case 'heart':
					$rateEmoji = '❤️';
					break;

				case 'bell':
					$rateEmoji = '🔔';
					break;

				case 'flag':
					$rateEmoji = '🏁';
					break;

				case 'bookmark':
					$rateEmoji = '📎';
					break;

				case 'like':
					$rateEmoji = '👍';
					break;

				case 'dislike':
					$rateEmoji = '👎';
					break;

				case 'user1':
					$rateEmoji = '👤';
					break;
			}
		}


		$_kbd =
		[
			'inline_keyboard' => []
		];

		for ($i=0; $i <= $max; $i++)
		{
			$itemTitle    = $value['title'];
			$itemId       = $value['title'];
			$selectedMark = '';

			if(isset($value['id']) && $value['id'])
			{
				$itemId = $value['id'];
			}
			if(in_array($i, $_answer))
			{
				$selectedMark = '☑️ ';
			}


			$_kbd['inline_keyboard'][][] =
			[
				'text' => str_repeat($rateEmoji, $i),
				'callback_data' => 'survey_'. $surveyId. ' '. $questionId. ' '. $i,
			];
		}
	}


}
?>