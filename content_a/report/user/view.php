<?php
namespace content_a\report\user;


class view
{
	public static function config()
	{
		\dash\data::page_pictogram('list');
		\dash\data::page_title(T_("Answers"));
		\dash\data::page_desc(T_("List of your survey answers"));

		if(\dash\request::get('id') && \dash\request::get('answer'))
		{
			\dash\data::badge_link(\dash\url::this(). '/answers?id='. \dash\request::get('id'));
			\dash\data::badge_text(T_('Back to answer list'));

			\content_a\survey\view::load_survey();

			\dash\data::page_title(\dash\data::page_title(). ' | '. \dash\data::surveyRow_title());

			$answer = \dash\request::get('answer');

			$dataTable = \lib\app\answer::get_user_answer(\dash\request::get('id'), $answer);

			\dash\data::dataTable($dataTable);

			if(isset($dataTable[0]['user_id']))
			{
				$user_score = \lib\app\answer::user_score(\dash\coding::decode(\dash\request::get('id')), \dash\coding::decode($dataTable[0]['user_id']));
				\dash\data::userScore($user_score);
			}
		}
		else
		{
			\dash\redirect::to(\dash\url::here());
		}
	}
}
?>
