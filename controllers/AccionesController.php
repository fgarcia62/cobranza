<?php

use SincroCobranza\Entities\Accion;
use SincroCobranza\Managers\AccionManager;
use SincroCobranza\Repositories\AccionRepo;

class AccionesController extends \BaseController {
 
    protected $accionRepo;
	protected $title;

    public function __construct(AccionRepo $accionRepo)
    {
       	$this->accionRepo = $accionRepo;
		$this->title = \Lang::get('utils.Acciones');
    }

 	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		if ( ! \Util::getAccess('Acciones')) return $this->accessFail();
		
	   	$acciones_list = $this->accionRepo->getAll();
		$title = $this->title;		
		
		return View::make('acciones/list', compact('acciones_list', 'title'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if ( ! \Util::getAccess('Acciones', 'Insert')) return $this->accessFail();
		
		$accion = new Accion;
		$title = $this->title;		
        $form_data = array('route' => 'acciones.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('acciones/form', compact('accion', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $accion = $this->accionRepo->getModel();
		
        $manager = new AccionManager($accion, Input::all());

        $manager->save();

       	return Redirect::route('acciones.index');
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('Acciones')) return $this->accessFail();
		
        $accion = $this->accionRepo->find($id);

        $this->notFoundUnless($accion);

		$title = $this->title;		
        $form_data = array('route' => 'acciones.index', 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('acciones/form', compact('accion', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		if ( ! \Util::getAccess('Acciones')) return $this->accessFail();
		
        $accion = $this->accionRepo->find($id);

        $this->notFoundUnless($accion);
		
		$title = $this->title;		
        $form_data = array('route' => array('acciones.update', $accion->Act_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Acciones', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Acciones', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('acciones/form', compact('accion', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $accion = $this->accionRepo->find($id);

        $this->notFoundUnless($accion);
		
        $manager = new AccionManager($accion, Input::all());

        $manager->update();

       	return Redirect::route('acciones.index');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $accion = $this->accionRepo->find($id);

        $this->notFoundUnless($accion);

	    \DB::table('acciones')
	        ->where('Emp_Id', $accion->Emp_Id)
        	->where('Act_Id', $accion->Act_Id)
	        ->delete();

       	return Redirect::route('acciones.index');
	}


}
