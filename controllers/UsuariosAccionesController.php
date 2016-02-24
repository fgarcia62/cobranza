<?php

use SincroCobranza\Entities\UsuarioAccion;
use SincroCobranza\Managers\UsuarioAccionManager;
use SincroCobranza\Repositories\UserRepo;
use SincroCobranza\Repositories\AccionRepo;
use SincroCobranza\Repositories\UsuarioAccionRepo;

class UsuariosAccionesController extends \BaseController {
 
   	protected $userRepo;
   	protected $accionRepo;
   	protected $usuarioAccionRepo;
	protected $title;

    public function __construct(UserRepo $userRepo,
    							AccionRepo $accionRepo,
    							UsuarioAccionRepo $usuarioAccionRepo)
    {
       	$this->userRepo = $userRepo;
       	$this->accionRepo = $accionRepo;
       	$this->usuarioAccionRepo = $usuarioAccionRepo;
		$this->title = \Lang::get('utils.Usuarios_Acciones');
    }

 	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		
	}
	
	public function seek($id)
	{
		if ( ! \Util::getAccess('Usuarios')) return $this->accessFail();
		
        $usuario = $this->userRepo->find($id);

        $this->notFoundUnless($usuario);
		
	   	$usuariosAcciones_list = $this->usuarioAccionRepo->getAll($id);
		$title = $this->title;		
		
		return View::make('usuariosAcciones/list', compact('usuariosAcciones_list', 'usuario', 'title'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($id)
	{
		if ( ! \Util::getAccess('Usuarios', 'Insert')) return $this->accessFail();
		
		$usuarioAccion = new UsuarioAccion();
		$usuarioAccion->User_Id = $id;
       	$acciones = $this->accionRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => 'usuariosAcciones.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('usuariosAcciones/form', compact('usuarioAccion', 'acciones', 'title', 'form_data', 'action', 'action_label'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $usuarioAccion = $this->usuarioAccionRepo->getModel();
		
        $manager = new UsuarioAccionManager($usuarioAccion, Input::all());

        $manager->save();

       	return Redirect::route('usuariosAcciones.list', $usuarioAccion->User_Id);
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
		
        $usuarioAccion = $this->usuarioAccionRepo->find($id);

        $this->notFoundUnless($usuarioAccion);
	
		$acciones = $this->accionRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => array('usuariosAcciones.list', $usuarioAccion->User_Id), 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('usuariosAcciones/form', compact('usuarioAccion', 'acciones', 'title', 'form_data', 'action', 'action_label'));
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
		
        $usuarioAccion = $this->usuarioAccionRepo->find($id);

        $this->notFoundUnless($usuarioAccion);

		$acciones = $this->accionRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => array('usuariosAcciones.update', $usuarioAccion->User_Id . '-' . $usuarioAccion->Act_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Usuarios', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Usuarios', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('usuariosAcciones/form', compact('usuarioAccion', 'acciones', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $usuarioAccion = $this->usuarioAccionRepo->find($id);

        $this->notFoundUnless($usuarioAccion);
		
        $manager = new UsuarioAccionManager($usuarioAccion, Input::all());

        $manager->update();

       	return Redirect::route('usuariosAcciones.list', $usuarioAccion->User_Id);
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $usuarioAccion = $this->usuarioAccionRepo->find($id);

        $this->notFoundUnless($usuarioAccion);

	    \DB::table('usuarios_acciones')
	        ->where('Emp_Id', $usuarioAccion->Emp_Id)
	        ->where('User_Id', $usuarioAccion->User_Id)
        	->where('Act_Id', $usuarioAccion->Act_Id)
	        ->delete();

       	return Redirect::route('usuariosAcciones.list', $usuarioAccion->User_Id);
 	}


}
