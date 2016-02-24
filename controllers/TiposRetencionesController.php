<?php

use SincroCobranza\Entities\TipoRetencion;
use SincroCobranza\Managers\TipoRetencionManager;
use SincroCobranza\Repositories\TipoRetencionRepo;

class TiposRetencionesController extends \BaseController {
 
    protected $tipoRetencionRepo;
    protected $unidadRepo;
	protected $title;

    public function __construct(TipoRetencionRepo $tipoRetencionRepo)
    {
       	$this->tipoRetencionRepo = $tipoRetencionRepo;
		$this->title = \Lang::get('utils.Tipos_Retenciones');
    }

 	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		if ( ! \Util::getAccess('Tipos_Retenciones')) return $this->accessFail();
		
	   	$tiposRetenciones_list = $this->tipoRetencionRepo->getAll();
		$title = $this->title;		
		
		return View::make('tiposRetenciones/list', compact('tiposRetenciones_list', 'title'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if ( ! \Util::getAccess('Tipos_Retenciones', 'Insert')) return $this->accessFail();
		
		$tipoRetencion = new TipoRetencion;
		$title = $this->title;		
        $form_data = array('route' => 'tiposRetenciones.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('tiposRetenciones/form', compact('tipoRetencion', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $tipoRetencion = $this->tipoRetencionRepo->getModel();
		
        $manager = new TipoRetencionManager($tipoRetencion, Input::all());

        $manager->save();

       	return Redirect::route('tiposRetenciones.index');
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('Tipos_Retenciones')) return $this->accessFail();
		
        $tipoRetencion = $this->tipoRetencionRepo->find($id);

        $this->notFoundUnless($tipoRetencion);

		$title = $this->title;		
        $form_data = array('route' => 'tiposRetenciones.index', 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('tiposRetenciones/form', compact('tipoRetencion', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
 		if ( ! \Util::getAccess('Tipos_Retenciones')) return $this->accessFail();
		
        $tipoRetencion = $this->tipoRetencionRepo->find($id);

        $this->notFoundUnless($tipoRetencion);
		
		$title = $this->title;		
        $form_data = array('route' => array('tiposRetenciones.update', $tipoRetencion->Tp_Ret_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Tipos_Retenciones', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Tipos_Retenciones', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('tiposRetenciones/form', compact('tipoRetencion', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $tipoRetencion = $this->tipoRetencionRepo->find($id);

        $this->notFoundUnless($tipoRetencion);
		
        $manager = new TipoRetencionManager($tipoRetencion, Input::all());

        $manager->save();

       	return Redirect::route('tiposRetenciones.index');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $tipoRetencion = $this->tipoRetencionRepo->find($id);

        $this->notFoundUnless($tipoRetencion);

        $tipoRetencion->delete();

       	return Redirect::route('tiposRetenciones.index');
	}


}
