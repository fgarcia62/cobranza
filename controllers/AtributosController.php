<?php

use SincroCobranza\Entities\Atributo;
use SincroCobranza\Managers\AtributoManager;
use SincroCobranza\Repositories\AtributoRepo;

class AtributosController extends \BaseController {
 
    protected $atributoRepo;
    protected $unidadRepo;
	protected $title;

    public function __construct(AtributoRepo $atributoRepo)
    {
       	$this->atributoRepo = $atributoRepo;
		$this->title = \Lang::get('utils.Atributos');
    }

 	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		if ( ! \Util::getAccess('Atributos')) return $this->accessFail();
		
	   	$atributos_list = $this->atributoRepo->getAll();
		$title = $this->title;		
		
		return View::make('atributos/list', compact('atributos_list', 'title'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if ( ! \Util::getAccess('Atributos', 'Insert')) return $this->accessFail();
		
		$atributo = new Atributo;
		$title = $this->title;		
        $form_data = array('route' => 'atributos.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('atributos/form', compact('atributo', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $atributo = $this->atributoRepo->getModel();
		
        $manager = new AtributoManager($atributo, Input::all());

        $manager->save();

       	return Redirect::route('atributos.index');
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('Atributos')) return $this->accessFail();
		
        $atributo = $this->atributoRepo->find($id);

        $this->notFoundUnless($atributo);

		$title = $this->title;		
        $form_data = array('route' => 'atributos.index', 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('atributos/form', compact('atributo', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
 		if ( ! \Util::getAccess('Atributos')) return $this->accessFail();
		
        $atributo = $this->atributoRepo->find($id);

        $this->notFoundUnless($atributo);
		
		$title = $this->title;		
        $form_data = array('route' => array('atributos.update', $atributo->Atrib_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Atributos', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Atributos', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('atributos/form', compact('atributo', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $atributo = $this->atributoRepo->find($id);

        $this->notFoundUnless($atributo);
		
        $manager = new AtributoManager($atributo, Input::all());

        $manager->save();

       	return Redirect::route('atributos.index');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $atributo = $this->atributoRepo->find($id);

        $this->notFoundUnless($atributo);

        $atributo->delete();

       	return Redirect::route('atributos.index');
	}


}
