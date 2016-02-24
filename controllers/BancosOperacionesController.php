<?php

use SincroCobranza\Entities\BancoOperacion;
use SincroCobranza\Managers\BancoOperacionManager;
use SincroCobranza\Repositories\BancoOperacionRepo;

class BancosOperacionesController extends \BaseController {
 
    protected $bancoOperacionRepo;
    protected $unidadRepo;
	protected $title;

    public function __construct(BancoOperacionRepo $bancoOperacionRepo)
    {
       	$this->bancoOperacionRepo = $bancoOperacionRepo;
		$this->title = \Lang::get('utils.Bancos_Operaciones');
    }

 	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		if ( ! \Util::getAccess('Bancos_Operaciones')) return $this->accessFail();
		
	   	$bancosOperaciones_list = $this->bancoOperacionRepo->getAll();
		$title = $this->title;		
		
		return View::make('bancosOperaciones/list', compact('bancosOperaciones_list', 'title'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if ( ! \Util::getAccess('Bancos_Operaciones', 'Insert')) return $this->accessFail();
		
		$bancoOperacion = new BancoOperacion;
		$title = $this->title;		
        $form_data = array('route' => 'bancosOperaciones.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('bancosOperaciones/form', compact('bancoOperacion', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $bancoOperacion = $this->bancoOperacionRepo->getModel();
		
        $manager = new BancoOperacionManager($bancoOperacion, Input::all());

        $manager->save();

       	return Redirect::route('bancosOperaciones.index');
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('Bancos_Operaciones')) return $this->accessFail();
		
        $bancoOperacion = $this->bancoOperacionRepo->find($id);

        $this->notFoundUnless($bancoOperacion);

		$title = $this->title;		
        $form_data = array('route' => 'bancosOperaciones.index', 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('bancosOperaciones/form', compact('bancoOperacion', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
 		if ( ! \Util::getAccess('Bancos_Operaciones')) return $this->accessFail();
		
        $bancoOperacion = $this->bancoOperacionRepo->find($id);

        $this->notFoundUnless($bancoOperacion);
		
		$title = $this->title;		
        $form_data = array('route' => array('bancosOperaciones.update', $bancoOperacion->Tp_Doc_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Bancos_Operaciones', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Bancos_Operaciones', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('bancosOperaciones/form', compact('bancoOperacion', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $bancoOperacion = $this->bancoOperacionRepo->find($id);

        $this->notFoundUnless($bancoOperacion);
		
        $manager = new BancoOperacionManager($bancoOperacion, Input::all());

        $manager->save();

       	return Redirect::route('bancosOperaciones.index');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $bancoOperacion = $this->bancoOperacionRepo->find($id);

        $this->notFoundUnless($bancoOperacion);

        $bancoOperacion->delete();

       	return Redirect::route('bancosOperaciones.index');
	}


}
