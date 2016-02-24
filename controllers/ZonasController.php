<?php

use SincroCobranza\Entities\Zona;
use SincroCobranza\Managers\ZonaManager;
use SincroCobranza\Repositories\ZonaRepo;
use SincroCobranza\Repositories\SRegionRepo;
use SincroCobranza\Repositories\RegionRepo;
use SincroCobranza\Repositories\PaisRepo;

class ZonasController extends \BaseController {
 
    protected $zonaRepo;
    protected $sregionRepo;
    protected $regionRepo;
    protected $paisRepo;
	protected $title;

    public function __construct(ZonaRepo $zonaRepo, 
    							SRegionRepo $sregionRepo,
                                RegionRepo $regionRepo,
    							PaisRepo $paisRepo)
    {
       	$this->zonaRepo = $zonaRepo;
       	$this->sregionRepo = $sregionRepo;
        $this->regionRepo = $regionRepo;
        $this->paisRepo = $paisRepo;
		$this->title = \Lang::get('utils.Zonas');
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
		if ( ! \Util::getAccess('Zonas')) return $this->accessFail();

        if (preg_match('/^[0-9]+-[0-9]+$/', $id)) {
            $ids = explode('-', $id);
            $zona = new Zona;
            $zona->Reg_Id = $ids[0];
            $zona->SReg_Id = $ids[1];

            $zona_filter = array();
            $filter = Input::all();

            if ($filter) {
                $filter = array_only($filter, $zona->getFillable());
                foreach ($filter as $field => $value) {
                    if ($field == 'Reg_Id') {
                        $zona->Reg_Id = $value ? $value : 0;
                    }
                    if ($field == 'SReg_Id') {
                        $zona->SReg_Id = $value ? $value : 0;
                    }
                    if ($value and $field <> '_token' and $field <> 'page') {
                        if (isset($fields)) {
                            $fields .= ' AND ';
                        } else {
                            $fields = '';
                            $values = array();
                        }

                        $zona_filter[$field] = $value;
                        switch ($field) {
                            case 'Zon_Nbr':
                            case 'Zon_Codigo':
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
                $zona->fill($filter);
                $zonas_list = $this->zonaRepo->getFiltered($fields, $values);
            } else {
                $zonas_list = $this->zonaRepo->getAll($id);
            }

            $sregion = $this->sregionRepo->find($ids[1]);
            $region = $this->regionRepo->find($ids[0]);
            $sregiones = $this->sregionRepo->getList();
            $regiones = $this->regionRepo->getList();
            $paises = $this->paisRepo->getList();
            $title = $this->title;
            $form_data = array('route' => array('zonas.list', $id), 'method' => 'POST');

            return View::make('zonas/list', compact('zona', 'zonas_list', 'zona_filter', 'id', 'sregion', 'region', 'sregiones', 'regiones', 'paises', 'title', 'form_data'));
        }
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($id)
	{
		if ( ! \Util::getAccess('Zonas', 'Insert')) return $this->accessFail();

        if (preg_match('/^[0-9]+-[0-9]+$/', $id)) {
            $id = explode('-', $id);
            $zona = new Zona;
            $zona->Reg_Id = $id[0];
            $zona->SReg_Id = $id[1];

            $sregion = $this->sregionRepo->find($id[1]);
            $region = $this->regionRepo->find($id[0]);
            $sregiones = $this->sregionRepo->getList();
            $regiones = $this->regionRepo->getList();
            $paises = $this->paisRepo->getList();
            $title = $this->title;
            $form_data = array('route' => 'zonas.store', 'method' => 'POST');
            $action = 'Ingresar';
            $action_label = \Lang::get('utils.Ingresar');

            return View::make('zonas/form', compact('zona', 'sregion', 'region', 'sregiones', 'regiones', 'paises', 'title', 'form_data', 'action', 'action_label'));
        }
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $zona = $this->zonaRepo->getModel();
		
        $manager = new ZonaManager($zona, Input::all());

        $manager->save();

       	return Redirect::route('zonas.list', $zona->Reg_Id . '-'. $zona->SReg_Id);
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('Zonas')) return $this->accessFail();
		
        $zona = $this->zonaRepo->find($id);

        $this->notFoundUnless($zona);

        $sregion = $this->regionRepo->find($zona->SReg_Id);
        $region = $this->regionRepo->find($zona->Reg_Id);
        $sregiones = $this->sregionRepo->getList();
        $regiones = $this->regionRepo->getList();
		$paises = $this->paisRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => array('zonas.list', $zona->Reg_Id . '-'. $zona->SReg_Id), 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('zonas/form', compact('zona', 'sregion', 'region', 'sregiones', 'regiones', 'paises', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		if ( ! \Util::getAccess('Zonas')) return $this->accessFail();
		
        $zona = $this->zonaRepo->find($id);

        $this->notFoundUnless($zona);

        $sregion = $this->sregionRepo->find($zona->SReg_Id);
        $region = $this->regionRepo->find($zona->Reg_Id);
        $sregiones = $this->sregionRepo->getList();
        $regiones = $this->regionRepo->getList();
		$paises = $this->paisRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => array('zonas.update', $zona->Zon_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Zonas', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Zonas', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('zonas/form', compact('zona', 'sregion', 'region', 'sregiones', 'regiones', 'paises', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $zona = $this->zonaRepo->find($id);

        $this->notFoundUnless($zona);
		
        $manager = new ZonaManager($zona, Input::all());

        $manager->save();

       	return Redirect::route('zonas.list', $zona->Reg_Id . '-'. $zona->SReg_Id);
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $zona = $this->zonaRepo->find($id);

        $this->notFoundUnless($zona);

	    $zona->delete();

       	return Redirect::route('zonas.list', $zona->Reg_Id . '-'. $zona->SReg_Id);
	}

    public function get()
    {
        $fields = '';
        $values = array();

        $pais_id = Input::get('Pais_Id');
        $reg_id = Input::get('Reg_Id');
        $sreg_id = Input::get('SReg_Id');

        if ($pais_id){
            $fields = 'Pais_Id = ?';
            $values = array($pais_id);
        }

        if ($reg_id){
            if ($fields) {
                $fields .= ' AND ';
            }
            $fields .= 'Reg_Id = ?';
            $values = array_merge($values, array($reg_id));
        }

        if ($sreg_id){
            if ($fields) {
                $fields .= ' AND ';
            }
            $fields .= 'SReg_Id = ?';
            $values = array_merge($values, array($sreg_id));
        }

        $zonas = $this->zonaRepo->getZonas($fields, $values);
        return Response::json($zonas);
    }


}
