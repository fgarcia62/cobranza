<?php

use SincroCobranza\Entities\AccionObjeto;
use SincroCobranza\Managers\AccionObjetoManager;
use SincroCobranza\Repositories\AccionRepo;
use SincroCobranza\Repositories\ObjetoRepo;
use SincroCobranza\Repositories\AccionObjetoRepo;

class AccionesObjetosController extends \BaseController {
 
   	protected $accionRepo;
   	protected $objetoRepo;
   	protected $accionObjetoRepo;
	protected $title;

    public function __construct(AccionRepo $accionRepo,
    							ObjetoRepo $objetoRepo,
    							AccionObjetoRepo $accionObjetoRepo)
    {
       	$this->accionRepo = $accionRepo;
       	$this->objetoRepo = $objetoRepo;
       	$this->accionObjetoRepo = $accionObjetoRepo;
		$this->title = \Lang::get('utils.Acciones_Objetos');
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
		if ( ! \Util::getAccess('Acciones')) return $this->accessFail();
		
        $accion = $this->accionRepo->find($id);

        $this->notFoundUnless($accion);
		
	   	$accionesObjetos_list = $this->accionObjetoRepo->getAll($id);
		$title = $this->title;		
		
		return View::make('accionesObjetos/list', compact('accionesObjetos_list', 'accion', 'title'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($id)
	{
		if ( ! \Util::getAccess('Acciones', 'Insert')) return $this->accessFail();
		
		$accionObjeto = new AccionObjeto();
		$accionObjeto->Act_Id = $id;
       	$objetos = $this->objetoRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => 'accionesObjetos.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('accionesObjetos/form', compact('accionObjeto', 'objetos', 'title', 'form_data', 'action', 'action_label'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $accionObjeto = $this->accionObjetoRepo->getModel();
		
        $manager = new AccionObjetoManager($accionObjeto, Input::all());

        $manager->save();

       	return Redirect::route('accionesObjetos.list', $accionObjeto->Act_Id);
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
		
        $accionObjeto = $this->accionObjetoRepo->find($id);

        $this->notFoundUnless($accionObjeto);
	
		$objetos = $this->objetoRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => array('accionesObjetos.list', $accionObjeto->Act_Id), 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('accionesObjetos/form', compact('accionObjeto', 'objetos', 'title', 'form_data', 'action', 'action_label'));
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
		
        $accionObjeto = $this->accionObjetoRepo->find($id);

        $this->notFoundUnless($accionObjeto);

		$objetos = $this->objetoRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => array('accionesObjetos.update', $accionObjeto->Act_Id . '-' . $accionObjeto->Obj_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Acciones', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Acciones', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('accionesObjetos/form', compact('accionObjeto', 'objetos', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $accionObjeto = $this->accionObjetoRepo->find($id);

        $this->notFoundUnless($accionObjeto);
		
        $manager = new AccionObjetoManager($accionObjeto, Input::all());

        $manager->update();

       	return Redirect::route('accionesObjetos.list', $accionObjeto->Act_Id);
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $accionObjeto = $this->accionObjetoRepo->find($id);

        $this->notFoundUnless($accionObjeto);

	    \DB::table('acciones_objetos')
	        ->where('Emp_Id', $accionObjeto->Emp_Id)
	        ->where('Act_Id', $accionObjeto->Act_Id)
        	->where('Obj_Id', $accionObjeto->Obj_Id)
	        ->delete();

       	return Redirect::route('accionesObjetos.list', $accionObjeto->Act_Id);
 	}


}
