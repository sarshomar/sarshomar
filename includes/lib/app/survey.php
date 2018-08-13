<?php
namespace lib\app;

/**
 * Class for survey.
 */
class survey
{

	use survey\add;
	use survey\edit;
	use survey\datalist;
	use survey\dashboard;


	public static $raw_field =
	[
		'redirect',
		'brandingmeta',
		'welcomemedia',
		'thankyoumedia',
	];

	public static function get($_id)
	{
		$id = \dash\coding::decode($_id);
		if(!$id)
		{
			\dash\notif::error(T_("Survay id not set"));
			return false;
		}


		$get = \lib\db\surveys::get(['id' => $id, 'limit' => 1]);

		if(!$get)
		{
			\dash\notif::error(T_("Invalid survey id"));
			return false;
		}

		$result = self::ready($get);

		return $result;
	}


	/**
	 * check args
	 *
	 * @return     array|boolean  ( description_of_the_return_value )
	 */
	private static function check($_id = null)
	{

		$title = \dash\app::request('title');
		if(\dash\app::isset_request('title') && !$title)
		{
			\dash\notif::error(T_("Please fill the survey title"), 'title');
			return false;
		}

		if(mb_strlen($title) >= 500)
		{
			\dash\notif::error(T_("Please fill the survey title less than 500 character"), 'title');
			return false;
		}

		$language = \dash\app::request('language');
		if($language && mb_strlen($language) !== 2)
		{
			\dash\notif::error(T_("Invalid parameter language"), 'language');
			return false;
		}

		if($language && !\dash\language::check($language))
		{
			\dash\notif::error(T_("Invalid parameter language"), 'language');
			return false;
		}

		$password = \dash\app::request('password');
		if($password && mb_strlen($password) >= 200)
		{
			\dash\notif::error(T_("Please fill the survey password less than 200 character"), 'password');
			return false;
		}

		$privacy = \dash\app::request('privacy');
		if($privacy && !in_array($privacy, ['public', 'private']))
		{
			\dash\notif::error(T_("Invalid privacy of survey"), 'privacy');
			return false;
		}

		$status = \dash\app::request('status');
		if($status && !in_array($status, ['draft','publish','expire','deleted','lock','awaiting','question','filter','close', 'full']))
		{
			\dash\notif::error(T_("Invalid status of survey"), 'status');
			return false;
		}

		$branding      = \dash\app::request('branding') ? 1 : null;
		$brandingtitle = \dash\app::request('brandingtitle');
		$brandingdesc  = \dash\app::request('brandingdesc');
		$brandingmeta  = \dash\app::request('brandingmeta');
		if(is_array($brandingmeta))
		{
			$brandingmeta = json_encode($brandingmeta, JSON_UNESCAPED_UNICODE);
		}

		$redirect = \dash\app::request('redirect');
		if($redirect && mb_strlen($redirect) >= 2000)
		{
			\dash\notif::error(T_("Please fill the survey redirect less than 2000 character"), 'redirect');
			return false;
		}

		$progresbar = \dash\app::request('progresbar') ? 1 : null;

		$trans  = \dash\app::request('trans');

		$email  = \dash\app::request('email') ? 1 : null;

		$emailtitle = \dash\app::request('emailtitle');
		if($emailtitle && mb_strlen($emailtitle) >= 500)
		{
			\dash\notif::error(T_("Please fill the survey emailtitle less than 500 character"), 'emailtitle');
			return false;
		}

		$emailto = \dash\app::request('emailto');
		if($emailto && mb_strlen($emailto) >= 500)
		{
			\dash\notif::error(T_("Please fill the survey emailto less than 500 character"), 'emailto');
			return false;
		}

		$emailmsg      = \dash\app::request('emailmsg');

		$welcometitle = \dash\app::request('welcometitle');
		$welcomedesc  = \dash\app::request('welcomedesc');
		$welcomemedia = \dash\app::request('welcomemedia');
		if(is_array($welcomemedia))
		{
			$welcomemedia = json_encode($welcomemedia, JSON_UNESCAPED_UNICODE);
		}

		$thankyoutitle = \dash\app::request('thankyoutitle');
		$thankyoudesc  = \dash\app::request('thankyoudesc');
		$thankyoumedia = \dash\app::request('thankyoumedia');
		if(is_array($thankyoumedia))
		{
			$thankyoumedia = json_encode($thankyoumedia, JSON_UNESCAPED_UNICODE);
		}

		$desc  = \dash\app::request('desc');

		$args                  = [];
		$args['desc']          = $desc;
		$args['title']         = $title;
		$args['lang']          = $language;
		$args['password']      = $password;
		$args['privacy']       = $privacy;
		$args['status']        = $status;
		$args['branding']      = $branding;
		$args['brandingtitle'] = $brandingtitle;
		$args['brandingdesc']  = $brandingdesc;
		$args['brandingmeta']  = $brandingmeta;
		$args['redirect']      = $redirect;
		$args['progresbar']    = $progresbar;
		$args['trans']         = $trans;
		$args['email']         = $email;
		$args['emailtitle']    = $emailtitle;
		$args['emailto']       = $emailto;
		$args['emailmsg']      = $emailmsg;
		$args['welcometitle'] = $welcometitle;
		$args['welcomedesc']  = $welcomedesc;
		$args['welcomemedia'] = $welcomemedia;
		$args['thankyoutitle'] = $thankyoutitle;
		$args['thankyoudesc']  = $thankyoudesc;
		$args['thankyoumedia'] = $thankyoumedia;

		return $args;
	}


	/**
	 * ready data of survey to load in api
	 *
	 * @param      <type>  $_data  The data
	 */
	public static function ready($_data)
	{
		$result = [];
		foreach ($_data as $key => $value)
		{

			switch ($key)
			{
				case 'id':
				case 'user_id':
					$result[$key] = \dash\coding::encode($value);
					break;

				case 'brandingmeta':
				case 'welcomemedia':
				case 'thankyoumedia':
					$result[$key] = json_decode($value, true);
					break;

				default:
					$result[$key] = $value;
					break;
			}
		}

		return $result;
	}

}
?>