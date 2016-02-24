<?php

use SincroCobranza\Entities\Municipio;
use SincroCobranza\Managers\MunicipioManager;
use SincroCobranza\Repositories\MunicipioRepo;
use SincroCobranza\Repositories\EstadoRepo;
use SincroCobranza\Repositories\PaisRepo;

class MunicipiosController extends \BaseController {
 
    protected $municipioRepo;
    protected $estadoRepo;
    protected $paisRepo;
	protected $title;

    public function __construct(MunicipioRepo $municipioRepo, 
    							EstadoRepo $estadoRepo,
   								PaisRepo $paisRepo)
     {
       	$this->municipioRepo = $municipioRepo;
       	$this->estadoRepo = $estadoRepo;
       	$this->paisRepo = $paisRepo;
		$this->title = \Lang::get('utils.Municipios');
    }

 	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		if ( ! \Util::getAccess('Municipios')) return $this->accessFail();
		
		$municipio = new Municipio;
		$municipio_filter = array();
		$filter = Input::all();
				
		if ($filter) {
			$filter = array_only($filter, $municipio->getFillable());
			foreach($filter as $field => $value) {
				if ($value and $field <> '_token' and $field <> 'page') {
					if (isset($fields)) {
						$fields .= ' AND ';
					} else {
						$fields = '';
						$values = array();
					}
										
					$municipio_filter[$field] = $value;
					switch ($field) {
						case 'Mun_Nbr':
						case 'Mun_Codigo':
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
			$municipio->fill($filter);
		   	$municipios_list = $this->municipioRepo->getFiltered($fields, $values); 
		} else {
		   	$municipios_list = $this->municipioRepo->getAll();
		}
		
		$estados = $this->estadoRepo->getList();
		$paises = $this->paisRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => 'municipios.index', 'method' => 'POST');
		
		return View::make('municipios/list', compact('municipio', 'municipios_list', 'municipio_filter', 'estados', 'paises', 'title', 'form_data'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if ( ! \Util::getAccess('Municipios', 'Insert')) return $this->accessFail();
		
		$municipio = new Municipio;
		$estados = $this->estadoRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => 'municipios.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('municipios/form', compact('municipio', 'estados', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $municipio = $this->municipioRepo->getModel();
		
        $manager = new MunicipioManager($municipio, Input::all());

        $manager->save();

       	return Redirect::route('municipios.index');
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('Municipios')) return $this->accessFail();
		
        $municipio = $this->municipioRepo->find($id);

        $this->notFoundUnless($municipio);

		$estados = $this->estadoRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => 'municipios.index', 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('municipios/form', compact('municipio', 'estados', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		if ( ! \Util::getAccess('Municipios')) return $this->accessFail();
		
        $municipio = $this->municipioRepo->find($id);

        $this->notFoundUnless($municipio);
		
		$estados = $this->estadoRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => array('municipios.update', $municipio->Mun_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Municipios', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Municipios', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('municipios/form', compact('municipio', 'estados', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $municipio = $this->municipioRepo->find($id);

        $this->notFoundUnless($municipio);
		
        $manager = new MunicipioManager($municipio, Input::all());

        $manager->save();

       	return Redirect::route('municipios.index');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $municipio = $this->municipioRepo->find($id);

        $this->notFoundUnless($municipio);

	    $municipio->delete();

       	return Redirect::route('municipios.index');
	}


}
