<?php

use SincroCobranza\Entities\Ciudad;
use SincroCobranza\Managers\CiudadManager;
use SincroCobranza\Repositories\CiudadRepo;
use SincroCobranza\Repositories\PaisRepo;
use SincroCobranza\Repositories\EstadoRepo;
use SincroCobranza\Repositories\MunicipioRepo;

class CiudadesController extends \BaseController {
 
    protected $ciudadRepo;
    protected $paisRepo;
    protected $estadoRepo;
    protected $municipioRepo;
	protected $title;

    public function __construct(CiudadRepo $ciudadRepo, 
    							PaisRepo $paisRepo,
    							EstadoRepo $estadoRepo,
								MunicipioRepo $municipioRepo)
    {
       	$this->ciudadRepo = $ciudadRepo;
       	$this->paisRepo = $paisRepo;
       	$this->estadoRepo = $estadoRepo;
       	$this->municipioRepo = $municipioRepo;
		$this->title = \Lang::get('utils.Ciudades');
    }

 	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		if ( ! \Util::getAccess('Ciudades')) return $this->accessFail();
		
		$ciudad = new Ciudad;
		$ciudad_filter = array();
		$filter = Input::all();
				
		if ($filter) {
			$filter = array_only($filter, $ciudad->getFillable());
			foreach($filter as $field => $value) {
				if ($value and $field <> '_token' and $field <> 'page') {
					if (isset($fields)) {
						$fields .= ' AND ';
					} else {
						$fields = '';
						$values = array();
					}
										
					$ciudad_filter[$field] = $value;
					switch ($field) {
						case 'Ciudad_Nbr':
						case 'Ciudad_Codigo':
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
			$ciudad->fill($filter);
		   	$ciudades_list = $this->ciudadRepo->getFiltered($fields, $values); 
		} else {
		   	$ciudades_list = $this->ciudadRepo->getAll();
		}
		
		$paises = $this->paisRepo->getList();
		$estados = $this->estadoRepo->getList();
		$municipios = $this->municipioRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => 'ciudades.index', 'method' => 'POST');
		
		return View::make('ciudades/list', compact('ciudad', 'ciudades_list', 'ciudad_filter', 'paises', 'estados', 'municipios', 'title', 'form_data'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if ( ! \Util::getAccess('Ciudades', 'Insert')) return $this->accessFail();
		
		$ciudad = new Ciudad;
		$paises = $this->paisRepo->getList();
		$estados = $this->estadoRepo->getList();
		$municipios = $this->municipioRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => 'ciudades.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('ciudades/form', compact('ciudad', 'paises', 'estados', 'municipios', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $ciudad = $this->ciudadRepo->getModel();
		
        $manager = new CiudadManager($ciudad, Input::all());

        $manager->save();

       	return Redirect::route('ciudades.index');
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('Ciudades')) return $this->accessFail();
		
        $ciudad = $this->ciudadRepo->find($id);

        $this->notFoundUnless($ciudad);

		$paises = $this->paisRepo->getList();
		$estados = $this->estadoRepo->getList();
		$municipios = $this->municipioRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => 'ciudades.index', 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('ciudades/form', compact('ciudad', 'paises', 'estados', 'municipios', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		if ( ! \Util::getAccess('Ciudades')) return $this->accessFail();
		
        $ciudad = $this->ciudadRepo->find($id);

        $this->notFoundUnless($ciudad);
		
		$paises = $this->paisRepo->getList();
		$estados = $this->estadoRepo->getList();
		$municipios = $this->municipioRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => array('ciudades.update', $ciudad->Ciudad_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Ciudades', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Ciudades', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('ciudades/form', compact('ciudad', 'paises', 'estados', 'municipios', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $ciudad = $this->ciudadRepo->find($id);

        $this->notFoundUnless($ciudad);
		
        $manager = new CiudadManager($ciudad, Input::all());

        $manager->save();

       	return Redirect::route('ciudades.index');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $ciudad = $this->ciudadRepo->find($id);

        $this->notFoundUnless($ciudad);

	    $ciudad->delete();

       	return Redirect::route('ciudades.index');
	}


}
