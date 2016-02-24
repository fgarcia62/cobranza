<?php

use SincroCobranza\Entities\TipoCancel;
use SincroCobranza\Managers\TipoCancelManager;
use SincroCobranza\Repositories\BancoCuentaRepo;
use SincroCobranza\Repositories\TipoCancelRepo;

class TiposCancelController extends \BaseController {
 
    protected $tipoCancelRepo;
    protected $bancoCuentaRepo;
	protected $title;

    public function __construct(TipoCancelRepo $tipoCancelRepo,
                                BancoCuentaRepo $bancoCuentaRepo)
    {
       	$this->tipoCancelRepo = $tipoCancelRepo;
        $this->bancoCuentaRepo = $bancoCuentaRepo;
		$this->title = \Lang::get('utils.Tipos_Cancel');
    }

 	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		if ( ! \Util::getAccess('Tipos_Cancel')) return $this->accessFail();
		
	   	$tiposCancel_list = $this->tipoCancelRepo->getAll();
		$title = $this->title;
		
		return View::make('tiposCancel/list', compact('tiposCancel_list', 'title'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if ( ! \Util::getAccess('Tipos_Cancel', 'Insert')) return $this->accessFail();
		
		$tipoCancel = new TipoCancel;
        $bancosCuentas = $this->bancoCuentaRepo->getList();
		$title = $this->title;
        $form_data = array('route' => 'tiposCancel.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('tiposCancel/form', compact('tipoCancel', 'bancosCuentas', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $tipoCancel = $this->tipoCancelRepo->getModel();
		
        $manager = new TipoCancelManager($tipoCancel, Input::all());

        $manager->save();

       	return Redirect::route('tiposCancel.index');
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('Tipos_Cancel')) return $this->accessFail();
		
        $tipoCancel = $this->tipoCancelRepo->find($id);

        $this->notFoundUnless($tipoCancel);

        $bancosCuentas = $this->bancoCuentaRepo->getList();
		$title = $this->title;
        $form_data = array('route' => 'tiposCancel.index', 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('tiposCancel/form', compact('tipoCancel', 'bancosCuentas', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
 		if ( ! \Util::getAccess('Tipos_Cancel')) return $this->accessFail();
		
        $tipoCancel = $this->tipoCancelRepo->find($id);

        $this->notFoundUnless($tipoCancel);

        $bancosCuentas = $this->bancoCuentaRepo->getList();
		$title = $this->title;
        $form_data = array('route' => array('tiposCancel.update', $tipoCancel->Tp_Can_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Tipos_Cancel', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Tipos_Cancel', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('tiposCancel/form', compact('tipoCancel', 'bancosCuentas', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $tipoCancel = $this->tipoCancelRepo->find($id);

        $this->notFoundUnless($tipoCancel);
		
        $manager = new TipoCancelManager($tipoCancel, Input::all());

        $manager->save();

       	return Redirect::route('tiposCancel.index');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $tipoCancel = $this->tipoCancelRepo->find($id);

        $this->notFoundUnless($tipoCancel);

        $tipoCancel->delete();

       	return Redirect::route('tiposCancel.index');
	}


}
