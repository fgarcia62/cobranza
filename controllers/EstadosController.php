<?php

use SincroCobranza\Entities\Estado;
use SincroCobranza\Managers\EstadoManager;
use SincroCobranza\Repositories\EstadoRepo;
use SincroCobranza\Repositories\PaisRepo;

class EstadosController extends \BaseController {
 
    protected $estadoRepo;
    protected $paisRepo;
	protected $title;

    public function __construct(EstadoRepo $estadoRepo, 
    							PaisRepo $paisRepo)
    {
       	$this->estadoRepo = $estadoRepo;
       	$this->paisRepo = $paisRepo;
		$this->title = \Lang::get('utils.Estados');
    }

 	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		if ( ! \Util::getAccess('Estados')) return $this->accessFail();
		
		$estado = new Estado;
		$estado_filter = array();
		$filter = Input::all();
				
		if ($filter) {
			$filter = array_only($filter, $estado->getFillable());
			foreach($filter as $field => $value) {
				if ($value and $field <> '_token' and $field <> 'page') {
					if (isset($fields)) {
						$fields .= ' AND ';
					} else {
						$fields = '';
						$values = array();
					}
										
					$estado_filter[$field] = $value;
					switch ($field) {
						case 'Edo_Nbr':
						case 'Edo_Codigo':
							$fields .= $field . " LIKE ?";
							$value = '%' . $value . '%';
							break;
						default:
							$fields .= $field . ' = ?';
					}						
					$values[] = $value;
				}
			}
		}

		if (isset($fields)) {
			$estado->fill($filter);
		   	$estados_list = $this->estadoRepo->getFiltered($fields, $values); 
		} else {
		   	$estados_list = $this->estadoRepo->getAll();
		}
		
		$paises = $this->paisRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => 'estados.index', 'method' => 'POST');
		
		return View::make('estados/list', compact('estado', 'estados_list', 'estado_filter', 'paises', 'title', 'form_data'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if ( ! \Util::getAccess('Estados', 'Insert')) return $this->accessFail();
		
		$estado = new Estado;
		$paises = $this->paisRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => 'estados.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('estados/form', compact('estado', 'paises', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $estado = $this->estadoRepo->getModel();
		
        $manager = new EstadoManager($estado, Input::all());

        $manager->save();

       	return Redirect::route('estados.index');
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('Estados')) return $this->accessFail();
		
        $estado = $this->estadoRepo->find($id);

        $this->notFoundUnless($estado);

		$paises = $this->paisRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => 'estados.index', 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('estados/form', compact('estado', 'paises', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		if ( ! \Util::getAccess('Estados')) return $this->accessFail();
		
        $estado = $this->estadoRepo->find($id);

        $this->notFoundUnless($estado);
		
		$paises = $this->paisRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => array('estados.update', $estado->Edo_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Estados', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Estados', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('estados/form', compact('estado', 'paises', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $estado = $this->estadoRepo->find($id);

        $this->notFoundUnless($estado);
		
        $manager = new EstadoManager($estado, Input::all());

        $manager->save();

       	return Redirect::route('estados.index');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $estado = $this->estadoRepo->find($id);

        $this->notFoundUnless($estado);

	    $estado->delete();

       	return Redirect::route('estados.index');
	}


}
