<?php

use SincroCobranza\Entities\Objeto;
use SincroCobranza\Managers\ObjetoManager;
use SincroCobranza\Repositories\ObjetoRepo;

class ObjetosController extends \BaseController {
 
    protected $objetoRepo;
	protected $title;

    public function __construct(ObjetoRepo $objetoRepo)
    {
       	$this->objetoRepo = $objetoRepo;
		$this->title = \Lang::get('utils.Objetos');
    }

 	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		if ( ! \Util::getAccess('Objetos')) return $this->accessFail();
		
	   	$objetos_list = $this->objetoRepo->getAll();
		$title = $this->title;		
		
		return View::make('objetos/list', compact('objetos_list', 'title'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if ( ! \Util::getAccess('Objetos', 'Insert')) return $this->accessFail();
		
		$objeto = new Objeto;
		$title = $this->title;		
        $form_data = array('route' => 'objetos.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('objetos/form', compact('objeto', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $objeto = $this->objetoRepo->getModel();
		
        $manager = new ObjetoManager($objeto, Input::all());

        $manager->save();

       	return Redirect::route('objetos.index');
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('Objetos')) return $this->accessFail();
		
        $objeto = $this->objetoRepo->find($id);

        $this->notFoundUnless($objeto);

		$title = $this->title;		
        $form_data = array('route' => 'objetos.index', 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('objetos/form', compact('objeto', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		if ( ! \Util::getAccess('Objetos')) return $this->accessFail();
		
        $objeto = $this->objetoRepo->find($id);

        $this->notFoundUnless($objeto);
		
		$title = $this->title;		
        $form_data = array('route' => array('objetos.update', $objeto->Obj_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Objetos', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Objetos', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('objetos/form', compact('objeto', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $objeto = $this->objetoRepo->find($id);

        $this->notFoundUnless($objeto);
		
        $manager = new ObjetoManager($objeto, Input::all());

        $manager->save();

       	return Redirect::route('objetos.index');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $objeto = $this->objetoRepo->find($id);

        $this->notFoundUnless($objeto);

        $objeto->delete();

       	return Redirect::route('objetos.index');
	}


}
