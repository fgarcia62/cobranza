<?php

use SincroCobranza\Entities\User;
use SincroCobranza\Entities\UsuarioEmpresa;
use SincroCobranza\Managers\UserManager;
use SincroCobranza\Repositories\UserRepo;
use SincroCobranza\Repositories\ClienteRepo;
use SincroCobranza\Repositories\SucursalRepo;
use SincroCobranza\Repositories\ProveedorRepo;
use SincroCobranza\Repositories\FlotaTipoCategoriaRepo;
use SincroCobranza\Repositories\FlotaLocalidadRepo;
use SincroCobranza\Repositories\EmpresaRepo;
use SincroCobranza\Repositories\UsuarioEmpresaRepo;

class UsuariosController extends \BaseController {
 
    protected $userRepo;
	protected $title;

    public function __construct(UserRepo $userRepo,
								ClienteRepo $clienteRepo,
								SucursalRepo $sucursalRepo,
								EmpresaRepo $empresaRepo)
    {
       	$this->userRepo = $userRepo;
       	$this->clienteRepo = $clienteRepo;
       	$this->sucursalRepo = $sucursalRepo;
       	$this->empresaRepo = $empresaRepo;
		$this->title = \Lang::get('utils.Usuarios');
    }

 	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		if ( ! \Util::getAccess('Usuarios')) return $this->accessFail();
		
	   	$users_list = $this->userRepo->getAll();
		$title = $this->title;		
		
		return View::make('usuarios/list', compact('users_list', 'title'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if ( ! \Util::getAccess('Usuarios', 'Insert')) return $this->accessFail();
		
		$user = new User;
		
		if ( \Auth::user()-> Emp_Id ) {
			$user->Emp_Id = \Auth::user()-> Emp_Id;
			$empresas = $this->empresaRepo->getLocal();
		} else {
			$empresas = $this->empresaRepo->getList();
		}
		
		$clientes = $this->clienteRepo->getList();
		$sucursales = $this->sucursalRepo->getList();
		$title = $this->title;
        $form_data = array('route' => 'usuarios.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('usuarios/form', compact('user', 'clientes', 'sucursales', 'empresas', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $user = $this->userRepo->getModel();
		
        $manager = new UserManager($user, Input::all());

        $manager->save();

		if ( $user->User_Activo ) {
		 	Mail::send('usuarios/mail', array('full_name'=>Input::get('full_name')), function($message) {
				$message->to(Input::get('email'), Input::get('full_name'))->subject(\Lang::get('utils.Bienvenido'));
			});
		}
		
       	return Redirect::route('usuarios.index');
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('Usuarios')) return $this->accessFail();
		
        $user = $this->userRepo->find($id);

        $this->notFoundUnless($user);
		
		if ( \Auth::user()-> Emp_Id ) {
			$empresas = $this->empresaRepo->getLocal();
		} else {
			$empresas = $this->empresaRepo->getList();
		}

		$clientes = $this->clienteRepo->getList();
		$sucursales = $this->sucursalRepo->getList();
		$title = $this->title;
        $form_data = array('route' => 'usuarios.index', 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('usuarios/form', compact('user', 'clientes', 'sucursales', 'empresas', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		if ( ! \Util::getAccess('Usuarios')) return $this->accessFail();
		
        $user = $this->userRepo->find($id);

        $this->notFoundUnless($user);
		
		if ( \Auth::user()-> Emp_Id ) {
			$empresas = $this->empresaRepo->getLocal();
		} else {
			$empresas = $this->empresaRepo->getList();
		}
		
		$clientes = $this->clienteRepo->getList();
		$sucursales = $this->sucursalRepo->getList();
		$title = $this->title;
        $form_data = array('route' => array('usuarios.update', $user->id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Usuarios', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Usuarios', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('usuarios/form', compact('user', 'clientes', 'sucursales', 'empresas', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $user = $this->userRepo->find($id);

        $this->notFoundUnless($user);
		
		$user_activo_old = $user->User_Activo;
		
        $manager = new UserManager($user, Input::all());

        $manager->save();
		
		if ( ! $user_activo_old and $user->User_Activo ) {
		 	Mail::send('usuarios/mail', array('full_name'=>Input::get('full_name')), function($message) {
				$message->to(Input::get('email'), Input::get('full_name'))->subject(\Lang::get('utils.Bienvenido'));
			});
		}
	 
       	return Redirect::route('usuarios.index');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $user = $this->userRepo->find($id);

        $this->notFoundUnless($user);

        $user->delete();

       	return Redirect::route('usuarios.index');
	}

    public function editPassword()
    {
        if ( ! \Util::getAccess('Usuarios_Password')) return $this->accessFail();

        $user = $this->userRepo->find(\Auth::user()->id);

        $this->notFoundUnless($user);

        $title = $this->title;
        $form_data = array('route' => array('usuarios.updatePassword'), 'method' => 'PATCH');
        $action = 'Actualizar';
        $action_label = \Lang::get('utils.Actualizar');

        return View::make('usuarios/password', compact('user', 'title', 'form_data', 'action', 'action_label'));
    }

    public function updatePassword()
    {
        $user = $this->userRepo->find(\Auth::user()->id);

        $this->notFoundUnless($user);

        $manager = new UserManager($user, Input::all());

        $manager->save();

        return Redirect::route('home');
    }

}
