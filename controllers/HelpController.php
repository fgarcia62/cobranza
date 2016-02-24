<?php


class HelpController extends BaseController {


    public function __construct()
    {
    }

    public function about()
    {
        return View::make('about');
    }

    public function services()
    {
        return View::make('services');
    }

    public function contact()
    {
        return View::make('contact');
    }
}