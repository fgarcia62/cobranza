<?php

use SincroCobranza\Entities\Sucursal;
use SincroCobranza\Managers\SucursalManager;
use SincroCobranza\Repositories\ClienteRepo;
use SincroCobranza\Repositories\CiudadRepo;
use SincroCobranza\Repositories\SucursalRepo;

class SucursalesController extends \BaseController {
 
   	protected $clienteRepo;
   	protected $ciudadRepo;
   	protected $sucursalRepo;
	protected $title;

    public function __construct(ClienteRepo $clienteRepo,
    							CiudadRepo $ciudadRepo,
    							SucursalRepo $sucursalRepo)
    {
       	$this->clienteRepo = $clienteRepo;
       	$this->ciudadRepo = $ciudadRepo;
       	$this->sucursalRepo = $sucursalRepo;
		$this->title = \Lang::get('utils.Sucursales');
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
		if ( ! \Util::getAccess('Sucursales')) return $this->accessFail();
		
        $cliente = $this->clienteRepo->find($id);

        $this->notFoundUnless($cliente);
		
	   	$sucursales_list = $this->sucursalRepo->getAll($id);
		$title = $this->title;		
		
		return View::make('sucursales/list', compact('sucursales_list', 'cliente', 'title'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($id)
	{
		if ( ! \Util::getAccess('Sucursales', 'Insert')) return $this->accessFail();
		
		$sucursal = new Sucursal();
		$sucursal->Cli_Id = $id;
        $cliente = $this->clienteRepo->find($id);
       	$ciudades = $this->ciudadRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => 'sucursales.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('sucursales/form', compact('sucursal', 'cliente', 'ciudades', 'title', 'form_data', 'action', 'action_label'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $sucursal = $this->sucursalRepo->getModel();
		
        $manager = new SucursalManager($sucursal, Input::all());

        $manager->save();

       	return Redirect::route('sucursales.list', $sucursal->Cli_Id);
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('Sucursales')) return $this->accessFail();
		
        $sucursal = $this->sucursalRepo->find($id);

        $this->notFoundUnless($sucursal);
		
        $cliente = $this->clienteRepo->find($id);
		$ciudades = $this->ciudadRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => array('sucursales.list', $sucursal->Cli_Id), 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('sucursales/form', compact('sucursal', 'cliente', 'ciudades', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		if ( ! \Util::getAccess('Sucursales')) return $this->accessFail();
		
        $sucursal = $this->sucursalRepo->find($id);

        $this->notFoundUnless($sucursal);

	    $cliente = $this->clienteRepo->find($id);
 		$ciudades = $this->ciudadRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => array('sucursales.update', $sucursal->Suc_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Sucursales', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Sucursales', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('sucursales/form', compact('sucursal', 'cliente', 'ciudades', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $sucursal = $this->sucursalRepo->find($id);

        $this->notFoundUnless($sucursal);
		
        $manager = new SucursalManager($sucursal, Input::all());

        $manager->update();

       	return Redirect::route('sucursales.list', $sucursal->Cli_Id);
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $sucursal = $this->sucursalRepo->find($id);

        $this->notFoundUnless($sucursal);

	    \DB::table('sucursales')
	        ->where('Emp_Id', $sucursal->Emp_Id)
	        ->where('Suc_Id', $sucursal->Suc_Id)
	        ->delete();

       	return Redirect::route('sucursales.list', $sucursal->Cli_Id);
 	}


}
