<?php

use SincroCobranza\Entities\Region;
use SincroCobranza\Managers\RegionManager;
use SincroCobranza\Repositories\RegionRepo;
use SincroCobranza\Repositories\PaisRepo;

class RegionesController extends \BaseController {
 
    protected $regionRepo;
    protected $paisRepo;
	protected $title;

    public function __construct(RegionRepo $regionRepo, 
    							PaisRepo $paisRepo)
    {
       	$this->regionRepo = $regionRepo;
       	$this->paisRepo = $paisRepo;
		$this->title = \Lang::get('utils.Regiones');
    }

 	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		if ( ! \Util::getAccess('Regiones')) return $this->accessFail();
		
		$region = new Region;
		$region_filter = array();
		$filter = Input::all();
				
		if ($filter) {
			$filter = array_only($filter, $region->getFillable());
			foreach($filter as $field => $value) {
				if ($value and $field <> '_token' and $field <> 'page') {
					if (isset($fields)) {
						$fields .= ' AND ';
					} else {
						$fields = '';
						$values = array();
					}
										
					$region_filter[$field] = $value;
					switch ($field) {
						case 'Reg_Nbr':
						case 'Reg_Codigo':
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
			$region->fill($filter);
		   	$regiones_list = $this->regionRepo->getFiltered($fields, $values); 
		} else {
		   	$regiones_list = $this->regionRepo->getAll();
		}
		
		$paises = $this->paisRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => 'regiones.index', 'method' => 'POST');
		
		return View::make('regiones/list', compact('region', 'regiones_list', 'region_filter', 'paises', 'title', 'form_data'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if ( ! \Util::getAccess('Regiones', 'Insert')) return $this->accessFail();
		
		$region = new Region;
		$paises = $this->paisRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => 'regiones.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('regiones/form', compact('region', 'paises', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $region = $this->regionRepo->getModel();
		
        $manager = new RegionManager($region, Input::all());

        $manager->save();

       	return Redirect::route('regiones.index');
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('Regiones')) return $this->accessFail();
		
        $region = $this->regionRepo->find($id);

        $this->notFoundUnless($region);

		$paises = $this->paisRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => 'regiones.index', 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('regiones/form', compact('region', 'paises', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		if ( ! \Util::getAccess('Regiones')) return $this->accessFail();
		
        $region = $this->regionRepo->find($id);

        $this->notFoundUnless($region);
		
		$paises = $this->paisRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => array('regiones.update', $region->Reg_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Regiones', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Regiones', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('regiones/form', compact('region', 'paises', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $region = $this->regionRepo->find($id);

        $this->notFoundUnless($region);
		
        $manager = new RegionManager($region, Input::all());

        $manager->save();

       	return Redirect::route('regiones.index');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $region = $this->regionRepo->find($id);

        $this->notFoundUnless($region);

	    $region->delete();

       	return Redirect::route('regiones.index');
	}

    public function get()
    {
        $regiones = $this->regionRepo->getPais(Input::get('Pais_Id'));
        return Response::json($regiones);
    }

}
