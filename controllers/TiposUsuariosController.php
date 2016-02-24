<?php

use SincroCobranza\Entities\TipoUsuario;
use SincroCobranza\Managers\TipoUsuarioManager;
use SincroCobranza\Repositories\TipoUsuarioRepo;

class TiposUsuariosController extends \BaseController {
 
    protected $tipoUsuarioRepo;
    protected $unidadRepo;
	protected $title;

    public function __construct(TipoUsuarioRepo $tipoUsuarioRepo)
    {
       	$this->tipoUsuarioRepo = $tipoUsuarioRepo;
		$this->title = \Lang::get('utils.Tipos_Usuarios');
    }

 	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		if ( ! \Util::getAccess('Tipos_Usuarios')) return $this->accessFail();
		
	   	$tiposUsuarios_list = $this->tipoUsuarioRepo->getAll();
		$title = $this->title;		
		
		return View::make('tiposUsuarios/list', compact('tiposUsuarios_list', 'title'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if ( ! \Util::getAccess('Tipos_Usuarios', 'Insert')) return $this->accessFail();
		
		$tipoUsuario = new TipoUsuario;
		$title = $this->title;		
        $form_data = array('route' => 'tiposUsuarios.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('tiposUsuarios/form', compact('tipoUsuario', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $tipoUsuario = $this->tipoUsuarioRepo->getModel();
		
        $manager = new TipoUsuarioManager($tipoUsuario, Input::all());

        $manager->save();

       	return Redirect::route('tiposUsuarios.index');
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('Tipos_Usuarios')) return $this->accessFail();
		
        $tipoUsuario = $this->tipoUsuarioRepo->find($id);

        $this->notFoundUnless($tipoUsuario);

		$title = $this->title;		
        $form_data = array('route' => 'tiposUsuarios.index', 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('tiposUsuarios/form', compact('tipoUsuario', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
 		if ( ! \Util::getAccess('Tipos_Usuarios')) return $this->accessFail();
		
        $tipoUsuario = $this->tipoUsuarioRepo->find($id);

        $this->notFoundUnless($tipoUsuario);
		
		$title = $this->title;		
        $form_data = array('route' => array('tiposUsuarios.update', $tipoUsuario->Tp_User_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Tipos_Usuarios', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Tipos_Usuarios', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('tiposUsuarios/form', compact('tipoUsuario', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $tipoUsuario = $this->tipoUsuarioRepo->find($id);

        $this->notFoundUnless($tipoUsuario);
		
        $manager = new TipoUsuarioManager($tipoUsuario, Input::all());

        $manager->save();

       	return Redirect::route('tiposUsuarios.index');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $tipoUsuario = $this->tipoUsuarioRepo->find($id);

        $this->notFoundUnless($tipoUsuario);

        $tipoUsuario->delete();

       	return Redirect::route('tiposUsuarios.index');
	}


}
