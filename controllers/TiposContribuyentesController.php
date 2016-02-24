<?php

use SincroCobranza\Entities\TipoContribuyente;
use SincroCobranza\Managers\TipoContribuyenteManager;
use SincroCobranza\Repositories\TipoContribuyenteRepo;

class TiposContribuyentesController extends \BaseController {
 
    protected $tipoContribuyenteRepo;
    protected $unidadRepo;
	protected $title;

    public function __construct(TipoContribuyenteRepo $tipoContribuyenteRepo)
    {
       	$this->tipoContribuyenteRepo = $tipoContribuyenteRepo;
		$this->title = \Lang::get('utils.Tipos_Contribuyentes');
    }

 	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		if ( ! \Util::getAccess('Tipos_Contribuyentes')) return $this->accessFail();
		
	   	$tiposContribuyentes_list = $this->tipoContribuyenteRepo->getAll();
		$title = $this->title;		
		
		return View::make('tiposContribuyentes/list', compact('tiposContribuyentes_list', 'title'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if ( ! \Util::getAccess('Tipos_Contribuyentes', 'Insert')) return $this->accessFail();
		
		$tipoContribuyente = new TipoContribuyente;
		$title = $this->title;		
        $form_data = array('route' => 'tiposContribuyentes.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('tiposContribuyentes/form', compact('tipoContribuyente', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $tipoContribuyente = $this->tipoContribuyenteRepo->getModel();
		
        $manager = new TipoContribuyenteManager($tipoContribuyente, Input::all());

        $manager->save();

       	return Redirect::route('tiposContribuyentes.index');
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('Tipos_Contribuyentes')) return $this->accessFail();
		
        $tipoContribuyente = $this->tipoContribuyenteRepo->find($id);

        $this->notFoundUnless($tipoContribuyente);

		$title = $this->title;		
        $form_data = array('route' => 'tiposContribuyentes.index', 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('tiposContribuyentes/form', compact('tipoContribuyente', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
 		if ( ! \Util::getAccess('Tipos_Contribuyentes')) return $this->accessFail();
		
        $tipoContribuyente = $this->tipoContribuyenteRepo->find($id);

        $this->notFoundUnless($tipoContribuyente);
		
		$title = $this->title;		
        $form_data = array('route' => array('tiposContribuyentes.update', $tipoContribuyente->Tp_Contrib_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Tipos_Contribuyentes', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Tipos_Contribuyentes', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('tiposContribuyentes/form', compact('tipoContribuyente', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $tipoContribuyente = $this->tipoContribuyenteRepo->find($id);

        $this->notFoundUnless($tipoContribuyente);
		
        $manager = new TipoContribuyenteManager($tipoContribuyente, Input::all());

        $manager->save();

       	return Redirect::route('tiposContribuyentes.index');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $tipoContribuyente = $this->tipoContribuyenteRepo->find($id);

        $this->notFoundUnless($tipoContribuyente);

        $tipoContribuyente->delete();

       	return Redirect::route('tiposContribuyentes.index');
	}


}
