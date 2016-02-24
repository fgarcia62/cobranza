<?php

use SincroCobranza\Entities\Actividad;
use SincroCobranza\Managers\ActividadManager;
use SincroCobranza\Repositories\ActividadRepo;

class ActividadesController extends \BaseController {
 
    protected $actividadRepo;
    protected $unidadRepo;
	protected $title;

    public function __construct(ActividadRepo $actividadRepo)
    {
       	$this->actividadRepo = $actividadRepo;
		$this->title = \Lang::get('utils.Actividades');
    }

 	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		if ( ! \Util::getAccess('Actividades')) return $this->accessFail();
		
	   	$actividades_list = $this->actividadRepo->getAll();
		$title = $this->title;		
		
		return View::make('actividades/list', compact('actividades_list', 'title'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if ( ! \Util::getAccess('Actividades', 'Insert')) return $this->accessFail();
		
		$actividad = new Actividad;
		$title = $this->title;		
        $form_data = array('route' => 'actividades.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('actividades/form', compact('actividad', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $actividad = $this->actividadRepo->getModel();
		
        $manager = new ActividadManager($actividad, Input::all());

        $manager->save();

       	return Redirect::route('actividades.index');
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('Actividades')) return $this->accessFail();
		
        $actividad = $this->actividadRepo->find($id);

        $this->notFoundUnless($actividad);

		$title = $this->title;		
        $form_data = array('route' => 'actividades.index', 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('actividades/form', compact('actividad', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
 		if ( ! \Util::getAccess('Actividades')) return $this->accessFail();
		
        $actividad = $this->actividadRepo->find($id);

        $this->notFoundUnless($actividad);
		
		$title = $this->title;		
        $form_data = array('route' => array('actividades.update', $actividad->Act_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Actividades', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Actividades', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('actividades/form', compact('actividad', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $actividad = $this->actividadRepo->find($id);

        $this->notFoundUnless($actividad);
		
        $manager = new ActividadManager($actividad, Input::all());

        $manager->save();

       	return Redirect::route('actividades.index');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $actividad = $this->actividadRepo->find($id);

        $this->notFoundUnless($actividad);

        $actividad->delete();

       	return Redirect::route('actividades.index');
	}


}
