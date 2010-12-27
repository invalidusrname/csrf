<?php defined('SYSPATH') or die('No direct script access.');

class CSRF_Form extends Kohana_Form {

	/**
	 * Generates an opening HTML form tag.
	 *
	 *     // Form will submit back to the current page using POST
	 *     echo Form::open();
	 *
	 *     // Form will submit to 'search' using GET
	 *     echo Form::open('search', array('method' => 'get'));
	 *
	 *     // When "file" inputs are present, you must include the "enctype"
	 *     echo Form::open(NULL, array('enctype' => 'multipart/form-data'));
	 *
	 * @param   string  form action, defaults to the current request URI
	 * @param   array   html attributes
	 * @return  string
	 * @uses    Request::instance
	 * @uses    URL::site
	 * @uses    HTML::attributes
	 */
	public static function open($action = NULL, array $attributes = NULL)
	{
		if ($action === NULL)
		{
			// Use the current URI
			$action = Request::current()->uri;
		}

		if ($action === '')
		{
			// Use only the base URI
			$action = Kohana::$base_url;
		}
		elseif (strpos($action, '://') === FALSE)
		{
			// Make the URI absolute
			$action = URL::site($action);
		}

		// Add the form action to the attributes
		$attributes['action'] = $action;

		// Only accept the default character set
		$attributes['accept-charset'] = Kohana::$charset;

		if (!isset($attributes['method'])) {
			// Use POST method
			$attributes['method'] = 'post';
		} else {
			$attributes['method'] = strtolower($attributes['method']);
		}

		// begin generating form output
		$output = '<form'.HTML::attributes($attributes).'>';

		// handle CSRF
		if ($attributes['method'] = 'post') {
			$output .= '<input type="hidden" name="csrf_token" id="csrf_token" value="' . CSRF::token(TRUE) . '" />';
			$output .= CSRF::javascript();
		}

		return $output;
	}

}
