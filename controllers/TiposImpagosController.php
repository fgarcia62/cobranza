<?php

use SincroCobranza\Entities\TipoImpago;
use SincroCobranza\Managers\TipoImpagoManager;
use SincroCobranza\Repositories\TipoImpagoRepo;

class TiposImpagosController extends \BaseController {
 
    protected $tipoImpagoRepo;
    protected $unidadRepo;
	protected $title;

    public function __construct(TipoImpagoRepo $tipoImpagoRepo)
    {
       	$this->tipoImpagoRepo = $tipoImpagoRepo;
		$this->title = \Lang::get('utils.Tipos_Impagos');
    }

 	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		if ( ! \Util::getAccess('Tipos_Impagos')) return $this->accessFail();
		
	   	$tiposImpagos_list = $this->tipoImpagoRepo->getAll();
		$title = $this->title;		
		
		return View::make('tiposImpagos/list', compact('tiposImpagos_list', 'title'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if ( ! \Util::getAccess('Tipos_Impagos', 'Insert')) return $this->accessFail();
		
		$tipoImpago = new TipoImpago;
		$title = $this->title;		
        $form_data = array('route' => 'tiposImpagos.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('tiposImpagos/form', compact('tipoImpago', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $tipoImpago = $this->tipoImpagoRepo->getModel();
		
        $manager = new TipoImpagoManager($tipoImpago, Input::all());

        $manager->save();

       	return Redirect::route('tiposImpagos.index');
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('Tipos_Impagos')) return $this->accessFail();
		
        $tipoImpago = $this->tipoImpagoRepo->find($id);

        $this->notFoundUnless($tipoImpago);

		$title = $this->title;		
        $form_data = array('route' => 'tiposImpagos.index', 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('tiposImpagos/form', compact('tipoImpago', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
 		if ( ! \Util::getAccess('Tipos_Impagos')) return $this->accessFail();
		
        $tipoImpago = $this->tipoImpagoRepo->find($id);

        $this->notFoundUnless($tipoImpago);
		
		$title = $this->title;		
        $form_data = array('route' => array('tiposImpagos.update', $tipoImpago->Tp_Impag_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Tipos_Impagos', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Tipos_Impagos', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('tiposImpagos/form', compact('tipoImpago', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $tipoImpago = $this->tipoImpagoRepo->find($id);

        $this->notFoundUnless($tipoImpago);
		
        $manager = new TipoImpagoManager($tipoImpago, Input::all());

        $manager->save();

       	return Redirect::route('tiposImpagos.index');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $tipoImpago = $this->tipoImpagoRepo->find($id);

        $this->notFoundUnless($tipoImpago);

        $tipoImpago->delete();

       	return Redirect::route('tiposImpagos.index');
	}


}
