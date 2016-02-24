<?php

use SincroCobranza\Entities\UsuarioZona;
use SincroCobranza\Managers\UsuarioZonaManager;
use SincroCobranza\Repositories\UserRepo;
use SincroCobranza\Repositories\ZonaRepo;
use SincroCobranza\Repositories\SRegionRepo;
use SincroCobranza\Repositories\RegionRepo;
use SincroCobranza\Repositories\PaisRepo;
use SincroCobranza\Repositories\UsuarioZonaRepo;

class UsuariosZonasController extends \BaseController {
 
   	protected $userRepo;
   	protected $zonaRepo;
    protected $sregionRepo;
    protected $regionRepo;
    protected $paisRepo;
   	protected $usuarioZonaRepo;
	protected $title;

    public function __construct(UserRepo $userRepo,
    							ZonaRepo $zonaRepo,
                                SRegionRepo $sregionRepo,
                                RegionRepo $regionRepo,
                                PaisRepo $paisRepo,
                                UsuarioZonaRepo $usuarioZonaRepo)
    {
       	$this->userRepo = $userRepo;
       	$this->zonaRepo = $zonaRepo;
        $this->sregionRepo = $sregionRepo;
        $this->regionRepo = $regionRepo;
        $this->paisRepo = $paisRepo;
        $this->usuarioZonaRepo = $usuarioZonaRepo;
		$this->title = \Lang::get('utils.Usuarios_Zonas');
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
		if ( ! \Util::getAccess('Usuarios')) return $this->accessFail();
		
        $usuario = $this->userRepo->find($id);

        $this->notFoundUnless($usuario);
		
	   	$usuariosZonas_list = $this->usuarioZonaRepo->getAll($id);
		$title = $this->title;		
		
		return View::make('usuariosZonas/list', compact('usuariosZonas_list', 'usuario', 'title'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($id)
	{
		if ( ! \Util::getAccess('Usuarios', 'Insert')) return $this->accessFail();
		
		$usuarioZona = new UsuarioZona();
		$usuarioZona->User_Id = $id;
       	$zonas = $this->zonaRepo->getList();
        $sregiones = $this->sregionRepo->getList();
        $regiones = $this->regionRepo->getList();
        $paises = $this->paisRepo->getList();
		$title = $this->title;
        $form_data = array('route' => 'usuariosZonas.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('usuariosZonas/form', compact('usuarioZona', 'zonas', 'sregiones', 'regiones', 'paises', 'title', 'form_data', 'action', 'action_label'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $usuarioZona = $this->usuarioZonaRepo->getModel();
		
        $manager = new UsuarioZonaManager($usuarioZona, Input::all());

        $manager->save();

       	return Redirect::route('usuariosZonas.list', $usuarioZona->User_Id);
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('Usuarios')) return $this->accessFail();
		
        $usuarioZona = $this->usuarioZonaRepo->find($id);

        $this->notFoundUnless($usuarioZona);
	
		$zonas = $this->zonaRepo->getList();
        $sregiones = $this->sregionRepo->getList();
        $regiones = $this->regionRepo->getList();
        $paises = $this->paisRepo->getList();
		$title = $this->title;
        $form_data = array('route' => array('usuariosZonas.list', $usuarioZona->User_Id), 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('usuariosZonas/form', compact('usuarioZona', 'zonas', 'sregiones', 'regiones', 'paises', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		if ( ! \Util::getAccess('Usuarios')) return $this->accessFail();
		
        $usuarioZona = $this->usuarioZonaRepo->find($id);

        $this->notFoundUnless($usuarioZona);

		$zonas = $this->zonaRepo->getList();
        $sregiones = $this->sregionRepo->getList();
        $regiones = $this->regionRepo->getList();
        $paises = $this->paisRepo->getList();
		$title = $this->title;
        $form_data = array('route' => array('usuariosZonas.update', $usuarioZona->User_Id . '-' . $usuarioZona->Act_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Usuarios', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Usuarios', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('usuariosZonas/form', compact('usuarioZona', 'zonas', 'sregiones', 'regiones', 'paises', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $usuarioZona = $this->usuarioZonaRepo->find($id);

        $this->notFoundUnless($usuarioZona);
		
        $manager = new UsuarioZonaManager($usuarioZona, Input::all());

        $manager->save();

       	return Redirect::route('usuariosZonas.list', $usuarioZona->User_Id);
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $usuarioZona = $this->usuarioZonaRepo->find($id);

        $this->notFoundUnless($usuarioZona);

	    \DB::table('usuarios_zonas')
	        ->where('User_Id', $usuarioZona->User_Id)
        	->where('Pais_Id', $usuarioZona->Pais_Id)
            ->where('Reg_Id', $usuarioZona->Reg_Id)
            ->where('SReg_Id', $usuarioZona->SReg_Id)
            ->where('Zon_Id', $usuarioZona->Zon_Id)
	        ->delete();

       	return Redirect::route('usuariosZonas.list', $usuarioZona->User_Id);
 	}


}
