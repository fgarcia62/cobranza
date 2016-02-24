<?php

use SincroCobranza\Entities\GrupoZona;
use SincroCobranza\Managers\GrupoZonaManager;
use SincroCobranza\Repositories\GrupoRepo;
use SincroCobranza\Repositories\ZonaRepo;
use SincroCobranza\Repositories\SRegionRepo;
use SincroCobranza\Repositories\RegionRepo;
use SincroCobranza\Repositories\PaisRepo;
use SincroCobranza\Repositories\GrupoZonaRepo;

class GruposZonasController extends \BaseController {
 
   	protected $grupoRepo;
   	protected $zonaRepo;
    protected $sregionRepo;
    protected $regionRepo;
    protected $paisRepo;
   	protected $grupoZonaRepo;
	protected $title;

    public function __construct(GrupoRepo $grupoRepo,
    							ZonaRepo $zonaRepo,
                                SRegionRepo $sregionRepo,
                                RegionRepo $regionRepo,
                                PaisRepo $paisRepo,
                                GrupoZonaRepo $grupoZonaRepo)
    {
       	$this->grupoRepo = $grupoRepo;
       	$this->zonaRepo = $zonaRepo;
        $this->sregionRepo = $sregionRepo;
        $this->regionRepo = $regionRepo;
        $this->paisRepo = $paisRepo;
        $this->grupoZonaRepo = $grupoZonaRepo;
		$this->title = \Lang::get('utils.Grupos_Zonas');
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
		if ( ! \Util::getAccess('Grupos')) return $this->accessFail();
		
        $grupo = $this->grupoRepo->find($id);

        $this->notFoundUnless($grupo);
		
	   	$gruposZonas_list = $this->grupoZonaRepo->getAll($id);
		$title = $this->title;		
		
		return View::make('gruposZonas/list', compact('gruposZonas_list', 'grupo', 'title'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($id)
	{
		if ( ! \Util::getAccess('Grupos', 'Insert')) return $this->accessFail();

        $grupo = $this->grupoRepo->find($id);

        $this->notFoundUnless($grupo);

        $grupoZona = new GrupoZona();
		$grupoZona->Grp_Id = $id;
       	$zonas = $this->zonaRepo->getList();
        $sregiones = $this->sregionRepo->getList();
        $regiones = $this->regionRepo->getList();
        $paises = $this->paisRepo->getList();
		$title = $this->title;
        $form_data = array('route' => 'gruposZonas.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('gruposZonas/form', compact('grupoZona', 'grupo', 'zonas', 'sregiones', 'regiones', 'paises', 'title', 'form_data', 'action', 'action_label'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $grupoZona = $this->grupoZonaRepo->getModel();
		
        $manager = new GrupoZonaManager($grupoZona, Input::all());

        $manager->save();

       	return Redirect::route('gruposZonas.list', $grupoZona->Grp_Id);
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('Grupos')) return $this->accessFail();
		
        $grupoZona = $this->grupoZonaRepo->find($id);

        $this->notFoundUnless($grupoZona);

        $grupo = $this->grupoRepo->find($grupoZona->Grp_Id);
		$zonas = $this->zonaRepo->getList();
        $sregiones = $this->sregionRepo->getList();
        $regiones = $this->regionRepo->getList();
        $paises = $this->paisRepo->getList();
		$title = $this->title;
        $form_data = array('route' => array('gruposZonas.list', $grupoZona->Grp_Id), 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('gruposZonas/form', compact('grupoZona', 'grupo', 'zonas', 'sregiones', 'regiones', 'paises', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		if ( ! \Util::getAccess('Grupos')) return $this->accessFail();
		
        $grupoZona = $this->grupoZonaRepo->find($id);

        $this->notFoundUnless($grupoZona);

        $grupo = $this->grupoRepo->find($grupoZona->Grp_Id);
		$zonas = $this->zonaRepo->getList();
        $sregiones = $this->sregionRepo->getList();
        $regiones = $this->regionRepo->getList();
        $paises = $this->paisRepo->getList();
		$title = $this->title;
        $form_data = array('route' => array('gruposZonas.update', $grupoZona->Grp_Id . '-' . $grupoZona->Act_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Grupos', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Grupos', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('gruposZonas/form', compact('grupoZona', 'grupo', 'zonas', 'sregiones', 'regiones', 'paises', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $grupoZona = $this->grupoZonaRepo->find($id);

        $this->notFoundUnless($grupoZona);
		
        $manager = new GrupoZonaManager($grupoZona, Input::all());

        $manager->save();

       	return Redirect::route('gruposZonas.list', $grupoZona->Grp_Id);
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $grupoZona = $this->grupoZonaRepo->find($id);

        $this->notFoundUnless($grupoZona);

	    \DB::table('grupos_zonas')
	        ->where('Grp_Id', $grupoZona->Grp_Id)
        	->where('Pais_Id', $grupoZona->Pais_Id)
            ->where('Reg_Id', $grupoZona->Reg_Id)
            ->where('SReg_Id', $grupoZona->SReg_Id)
            ->where('Zon_Id', $grupoZona->Zon_Id)
	        ->delete();

       	return Redirect::route('gruposZonas.list', $grupoZona->Grp_Id);
 	}


}
