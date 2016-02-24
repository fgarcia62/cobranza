<?php

class BaseController extends Controller {

	/**
	 * Setup the layout used by the controller.
	 *
	 * @return void
	 */
	protected function setupLayout()
	{
		if ( ! is_null($this->layout))
		{
			$this->layout = View::make($this->layout);
		}
	}

    public function notFoundUnless($value)
    {
//        if ( ! $value) App::abort(404);
        if ( ! $value) return View::make('pageNotFound');
    }

    public function accessFail()
    {
        return Redirect::route('accessDenied');
    }

    public function accessDenied()
    {
        return View::make('accessDenied');
    }

}
