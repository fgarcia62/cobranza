<?php

use SincroCobranza\Entities\AccionObjetoElemento;
use SincroCobranza\Managers\AccionObjetoElementoManager;
use SincroCobranza\Repositories\AccionObjetoRepo;
use SincroCobranza\Repositories\AccionObjetoElementoRepo;

class AccionesObjetosElementosController extends \BaseController {
 
   	protected $accionObjetoRepo;
   	protected $accionObjetoElementoRepo;
	protected $title;

    public function __construct(AccionObjetoRepo $accionObjetoRepo,
    							AccionObjetoElementoRepo $accionObjetoElementoRepo)
    {
       	$this->accionObjetoRepo = $accionObjetoRepo;
       	$this->accionObjetoElementoRepo = $accionObjetoElementoRepo;
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
		
        $accionObjeto = $this->accionObjetoRepo->find($id);

        $this->notFoundUnless($accionObjeto);
		
	   	$accionesObjetosElementos_list = $this->accionObjetoElementoRepo->getAll($id);
		$title = $this->title;		
		
		return View::make('accionesObjetosElementos/list', compact('accionesObjetosElementos_list', 'accionObjeto', 'title'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($id)
	{
		if ( ! \Util::getAccess('Acciones', 'Insert')) return $this->accessFail();
		
		if (preg_match('/^[0-9]+-[0-9]+$/', $id)) {
	        $accionObjeto = $this->accionObjetoRepo->find($id);

	        $this->notFoundUnless($accionObjeto);
				
			$accionObjetoElemento = new AccionObjetoElemento();
			$accionObjetoElemento->Act_Id = $accionObjeto->Act_Id;
			$accionObjetoElemento->Obj_Id = $accionObjeto->Obj_Id;
			$title = $this->title;		
	        $form_data = array('route' => 'accionesObjetosElementos.store', 'method' => 'POST');
	        $action    = 'Ingresar';        
			$action_label = \Lang::get('utils.Ingresar');
	
	        return View::make('accionesObjetosElementos/form', compact('accionObjetoElemento', 'title', 'form_data', 'action', 'action_label'));
		}
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $accionObjetoElemento = $this->accionObjetoElementoRepo->getModel();
		
        $manager = new AccionObjetoElementoManager($accionObjetoElemento, Input::all());

        $manager->save();

       	return Redirect::route('accionesObjetosElementos.list', $accionObjetoElemento->Act_Id . '-' . $accionObjetoElemento->Obj_Id);
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
		
        $accionObjetoElemento = $this->accionObjetoElementoRepo->find($id);

        $this->notFoundUnless($accionObjetoElemento);
	
		$title = $this->title;		
        $form_data = array('route' => array('accionesObjetosElementos.list', $accionObjetoElemento->Act_Id . '-' . $accionObjetoElemento->Obj_Id), 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('accionesObjetosElementos/form', compact('accionObjetoElemento', 'objetos', 'title', 'form_data', 'action', 'action_label'));
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
		
        $accionObjetoElemento = $this->accionObjetoElementoRepo->find($id);

        $this->notFoundUnless($accionObjetoElemento);

		$title = $this->title;		
        $form_data = array('route' => array('accionesObjetosElementos.update', $accionObjetoElemento->Act_Id . '-' . $accionObjetoElemento->Obj_Id . '-' . $accionObjetoElemento->Elem_Nbr), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Acciones', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Acciones', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('accionesObjetosElementos/form', compact('accionObjetoElemento', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $accionObjetoElemento = $this->accionObjetoElementoRepo->find($id);

        $this->notFoundUnless($accionObjetoElemento);
		
        $manager = new AccionObjetoElementoManager($accionObjetoElemento, Input::all());

        $manager->update();

       	return Redirect::route('accionesObjetosElementos.list', $accionObjetoElemento->Act_Id . '-' . $accionObjetoElemento->Obj_Id);
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $accionObjetoElemento = $this->accionObjetoElementoRepo->find($id);

        $this->notFoundUnless($accionObjetoElemento);

	    \DB::table('acciones_objetos_elementos')
	        ->where('Emp_Id', $accionObjetoElemento->Emp_Id)
	        ->where('Act_Id', $accionObjetoElemento->Act_Id)
        	->where('Obj_Id', $accionObjetoElemento->Obj_Id)
        	->where('Elem_Nbr', $accionObjetoElemento->Elem_Nbr)
	        ->delete();

       	return Redirect::route('accionesObjetosElementos.list', $accionObjetoElemento->Act_Id . '-' . $accionObjetoElemento->Obj_Id);
 	}


}
