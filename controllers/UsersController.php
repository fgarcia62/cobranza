<?php

use SincroCobranza\Entities\User;
use SincroCobranza\Managers\RegisterManager;
use SincroCobranza\Repositories\CandidateRepo;
use SincroCobranza\Repositories\CategoryRepo;
use SincroCobranza\Managers\AccountManager;
use SincroCobranza\Managers\ProfileManager;

class UsersController extends BaseController {

    protected $candidateRepo;
    protected $categoryRepo;

    public function __construct(CandidateRepo $candidateRepo,
                                CategoryRepo  $categoryRepo)
    {
        $this->candidateRepo = $candidateRepo;
        $this->categoryRepo  = $categoryRepo;
    }

    public function signUp()
    {
        return View::make('users/sign-up');
    }

    public function register()
    {
        $user = $this->candidateRepo->newCandidate();
        $manager = new RegisterManager($user, Input::all());
        $manager->save();
        Session::flash('success', \Lang::get('utils.Register_Success'));

        Mail::send('users/mail', array('full_name'=>Input::get('full_name'), 'email'=>Input::get('email'), 'user_tel'=>Input::get('User_Tel')), function($message)
		{
			$message->to('info@arpcorp.us', 'Arpcorp')->subject(\Lang::get('utils.New_Usuario'));
		});

        return Redirect::route('home');
    }

    public function account()
    {
        $user = Auth::user();
        return View::make('users/account', compact('user'));
    }

    public function updateAccount()
    {
        $user = Auth::user();
        $manager = new AccountManager($user, Input::all());

        $manager->save();

        return Redirect::route('home');
    }

    public function profile()
    {
        $user = Auth::user();
        $candidate = $user->getCandidate();

        $categories = $this->categoryRepo->getList();
        $job_types  = \Lang::get('utils.job_types');

        return View::make('users/profile', compact('user', 'candidate', 'categories', 'job_types'));
    }

    public function updateProfile()
    {
        $user = Auth::user();
        $candidate = $user->getCandidate();
        $manager = new ProfileManager($candidate, Input::all());

        $manager->save();

        return Redirect::route('home');
    }

} 