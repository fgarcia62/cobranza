<?php

use SincroCobranza\Entities\GrupoAccion;
use SincroCobranza\Managers\GrupoAccionManager;
use SincroCobranza\Repositories\GrupoRepo;
use SincroCobranza\Repositories\AccionRepo;
use SincroCobranza\Repositories\GrupoAccionRepo;

class GruposAccionesController extends \BaseController {
 
   	protected $grupoRepo;
   	protected $accionRepo;
   	protected $grupoAccionRepo;
	protected $title;

    public function __construct(GrupoRepo $grupoRepo,
    							AccionRepo $accionRepo,
    							GrupoAccionRepo $grupoAccionRepo)
    {
       	$this->grupoRepo = $grupoRepo;
       	$this->accionRepo = $accionRepo;
       	$this->grupoAccionRepo = $grupoAccionRepo;
		$this->title = \Lang::get('utils.Grupos_Acciones');
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
		if ( ! \Util::getAccess('Grupos')) return $this->accessFail();
		
        $grupo = $this->grupoRepo->find($id);

        $this->notFoundUnless($grupo);
		
	   	$gruposAcciones_list = $this->grupoAccionRepo->getAll($id);
		$title = $this->title;		
		
		return View::make('gruposAcciones/list', compact('gruposAcciones_list', 'grupo', 'title'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($id)
	{
		if ( ! \Util::getAccess('Grupos', 'Insert')) return $this->accessFail();
		
		$grupoAccion = new GrupoAccion();
		$grupoAccion->Grp_Id = $id;
        $grupo = $this->grupoRepo->find($id);
       	$acciones = $this->accionRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => 'gruposAcciones.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('gruposAcciones/form', compact('grupoAccion', 'grupo', 'acciones', 'title', 'form_data', 'action', 'action_label'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $grupoAccion = $this->grupoAccionRepo->getModel();
		
        $manager = new GrupoAccionManager($grupoAccion, Input::all());

        $manager->save();

       	return Redirect::route('gruposAcciones.list', $grupoAccion->Grp_Id);
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('Grupos')) return $this->accessFail();
		
        $grupoAccion = $this->grupoAccionRepo->find($id);

        $this->notFoundUnless($grupoAccion);
		
        $grupo = $this->grupoRepo->find($id);
		$acciones = $this->accionRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => array('gruposAcciones.list', $grupoAccion->Grp_Id), 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('gruposAcciones/form', compact('grupoAccion', 'grupo', 'acciones', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		if ( ! \Util::getAccess('Grupos')) return $this->accessFail();
		
        $grupoAccion = $this->grupoAccionRepo->find($id);

        $this->notFoundUnless($grupoAccion);

	    $grupo = $this->grupoRepo->find($id);
 		$acciones = $this->accionRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => array('gruposAcciones.update', $grupoAccion->Grp_Id . '-' . $grupoAccion->Act_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Grupos', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Grupos', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('gruposAcciones/form', compact('grupoAccion', 'grupo', 'acciones', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $grupoAccion = $this->grupoAccionRepo->find($id);

        $this->notFoundUnless($grupoAccion);
		
        $manager = new GrupoAccionManager($grupoAccion, Input::all());

        $manager->update();

       	return Redirect::route('gruposAcciones.list', $grupoAccion->Grp_Id);
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $grupoAccion = $this->grupoAccionRepo->find($id);

        $this->notFoundUnless($grupoAccion);

	    \DB::table('grupos_acciones')
	        ->where('Emp_Id', $grupoAccion->Emp_Id)
	        ->where('Grp_Id', $grupoAccion->Grp_Id)
        	->where('Act_Id', $grupoAccion->Act_Id)
	        ->delete();

       	return Redirect::route('gruposAcciones.list', $grupoAccion->Grp_Id);
 	}


}
