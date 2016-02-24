<?php

use SincroCobranza\Entities\SRegion;
use SincroCobranza\Managers\SRegionManager;
use SincroCobranza\Repositories\SRegionRepo;
use SincroCobranza\Repositories\RegionRepo;
use SincroCobranza\Repositories\PaisRepo;

class SRegionesController extends \BaseController {
 
    protected $sregionRepo;
    protected $regionRepo;
    protected $paisRepo;
	protected $title;

    public function __construct(SRegionRepo $sregionRepo, 
    							RegionRepo $regionRepo,
    							PaisRepo $paisRepo)
    {
       	$this->sregionRepo = $sregionRepo;
       	$this->regionRepo = $regionRepo;
        $this->paisRepo = $paisRepo;
		$this->title = \Lang::get('utils.SRegiones');
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
		if ( ! \Util::getAccess('SRegiones')) return $this->accessFail();
		
		$sregion = new SRegion;
        $sregion->Reg_Id = $id;
		$sregion_filter = array();
		$filter = Input::all();

		if ($filter) {
			$filter = array_only($filter, $sregion->getFillable());
			foreach($filter as $field => $value) {
                if ($field == 'Reg_Id') {
                    $sregion->Reg_Id = $value ? $value : 0;
                }
				if ($value and $field <> '_token' and $field <> 'page') {
					if (isset($fields)) {
						$fields .= ' AND ';
					} else {
						$fields = '';
						$values = array();
					}
										
					$sregion_filter[$field] = $value;
					switch ($field) {
						case 'SReg_Nbr':
						case 'SReg_Codigo':
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
			$sregion->fill($filter);
		   	$sregiones_list = $this->sregionRepo->getFiltered($fields, $values); 
		} else {
		   	$sregiones_list = $this->sregionRepo->getAll($id);
		}

        $region = $this->regionRepo->find($id);
		$regiones = $this->regionRepo->getList();
        $paises = $this->paisRepo->getList();
		$title = $this->title;
        $form_data = array('route' => array('sregiones.list', $id), 'method' => 'POST');
		
		return View::make('sregiones/list', compact('sregion', 'sregiones_list', 'sregion_filter', 'region', 'regiones', 'paises', 'title', 'form_data'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($id)
	{
		if ( ! \Util::getAccess('SRegiones', 'Insert')) return $this->accessFail();
		
		$sregion = new SRegion;
        $sregion->Reg_Id = $id;
        $region = $this->regionRepo->find($id);
        $regiones = $this->regionRepo->getList();
		$paises = $this->paisRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => 'sregiones.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('sregiones/form', compact('sregion', 'region', 'regiones', 'paises', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $sregion = $this->sregionRepo->getModel();
		
        $manager = new SRegionManager($sregion, Input::all());

        $manager->save();

       	return Redirect::route('sregiones.list', $sregion->Reg_Id);
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('SRegiones')) return $this->accessFail();
		
        $sregion = $this->sregionRepo->find($id);

        $this->notFoundUnless($sregion);

        $region = $this->regionRepo->find($sregion->Reg_Id);
        $regiones = $this->regionRepo->getList();
		$paises = $this->paisRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => array('sregiones.list', $sregion->Reg_Id), 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('sregiones/form', compact('sregion', 'region', 'regiones', 'paises', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		if ( ! \Util::getAccess('SRegiones')) return $this->accessFail();
		
        $sregion = $this->sregionRepo->find($id);

        $this->notFoundUnless($sregion);

        $region = $this->regionRepo->find($sregion->Reg_Id);
        $regiones = $this->regionRepo->getList();
		$paises = $this->paisRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => array('sregiones.update', $sregion->SReg_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('SRegiones', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('SRegiones', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('sregiones/form', compact('sregion', 'region', 'regiones', 'paises', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $sregion = $this->sregionRepo->find($id);

        $this->notFoundUnless($sregion);
		
        $manager = new SRegionManager($sregion, Input::all());

        $manager->save();

       	return Redirect::route('sregiones.list', $sregion->Reg_Id);
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $sregion = $this->sregionRepo->find($id);

        $this->notFoundUnless($sregion);

	    $sregion->delete();

       	return Redirect::route('sregiones.list', $sregion->Reg_Id);
	}

    public function get()
    {
        $sregiones = $this->sregionRepo->getRegion(Input::get('Reg_Id'));
        return Response::json($sregiones);
    }


}
