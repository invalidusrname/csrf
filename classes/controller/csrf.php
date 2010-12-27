<?php
class Controller_Csrf extends Controller {

	/**
	 * Used to generate a return response with a new token attached.
	 *
	 * @access	public
	 */
	public function action_generate()
	{
        header('Content-Type: application/json');
	    $this->request->response = json_encode(array('token' => CSRF::token(TRUE)));
	}

}
