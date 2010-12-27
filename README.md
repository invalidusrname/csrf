CSRF for Kohana 3
=================

Usage
-------------

**Implementation for Pages with Forms**

There are a few different use cases for the CSRF module. The most common is to automatically add CSRF protection to 
a form on your website. Below is a quick example:

	<?php echo CSRF_Form::open('/login/', array('method' => 'post')); ?>
		<input type="text" name="email" id="email" class="focus" value="Email Address*" />
		<input type="text" name="password" id="password" class="pw-focus" value="Password*" />
		<button type="submit" class="button" value="Register Now">Submit</button>
	<?php echo CSRF_Form::close(); ?>

CSRF_Form overrides Kohana_Form and as such there are no differences in the parameters you may pass to the open() method.
The open() method includes special handling for generating a hidden form element as well as javascript for generating
and returning a new token to be used in coordination with AJAX handlers:

	if ($attributes['method'] = 'post') {
		$output .= '<input type="hidden" name="csrf_token" id="csrf_token" value="' . CSRF::token(TRUE) . '" />';
		$output .= CSRF::javascript();
	}

	// below is the pertinent snippet of CSRF::javascript()
	$javascript  = '<script type="text/javascript">';
	$javascript .= 'var csrf_token = "' . $current_token . '";';
	$javascript .= 'var csrf_invalidated = false;';
	$javascript .= 'function getToken(callback) { $.getJSON("'.url::site('csrf/generate/').'", function(json) { csrf_token = json.token; csrf_invalidated = false; $(form).each(function(){ $(this).find("#csrf_token").val(csrf_token); }); if ($.isFunction(callback)) callback.call(this, csrf_token); }); }';
	$javascript .= '</script>';

**Validating Form Submissions**

In order to validate that a form is not forged, you would include the following in your controller or model method:

	if (Request::$method == 'POST') {
		if (!isset($_POST['csrf_token']) || CSRF::valid($_POST['csrf_token'])) {
			// possible cross-site request forgery
		} else {
			// perform further form validation
		}
	}

**Implementation for AJAX Only Pages**

If your page only has AJAX requests and no forms that need to be validated, you will need to add the javascript to your page
before any of the AJAX handling code is added:

	<?php echo CSRF::javascript(); ?>

**Caveats of AJAX CSRF Protection (and a workaround)**

It's worth noting that any call made to CSRF::valid() will delete the current csrf-token session variable. This means that if you
have multiple AJAX requests on a page, only the first one will be valid without implementing further frontend handling. To combat 
this, you will need to ensure that you generate a new token after each AJAX request. 

	<script type="text/javascript">
	// below is a globally set variable you need to maintain for whether to get a new token
	var csrf_invalidated = false;

	function ajaxyLogin(email, password) {
		// if invalidated, request a new token and pass ajaxyLogin as a callback.
		if (csrf_invalidated) {
			getToken(ajaxyLogin);
			return false;
		}

		$.post('/ajax/login/', { csrf_token: csrf_token, email: email, password: password }, function(data) {
			// you need to manually invalidate the token
			csrf_invalidated = true;
			// suggested usage is to automatically regenerate token in callback
			getToken();
		});

	}
	</script>

To combat the scenario of an AJAX request invalidating a subsequent form submission, it is recommended that you run
getToken() in the callback handling of your AJAX request. This will ensure that any forms on your page will have their
hidden input tokens updated accordingly.

**Validating AJAX Submissions**

In order to validate that an AJAX post is not forged, you must remember to include the csrf_token javascript global variable in your 
request. From there, you're going to want to validate it in your controller or model method:

        if (Request::$method == 'POST' && Request::$is_ajax) {
                if (!isset($_POST['csrf_token']) || CSRF::valid($_POST['csrf_token'])) {
                        // possible cross-site request forgery
                } else {
                        // perform further form validation
                }
        }

Requirements
------------
* The PHP mcrypt module (php-mcrypt or php5-mcrypt depending on your distro)
* jQuery >= 1.3.2
