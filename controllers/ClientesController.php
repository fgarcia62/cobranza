<?php

use SincroCobranza\Entities\Cliente;
use SincroCobranza\Managers\ClienteManager;
use SincroCobranza\Repositories\ClienteRepo;
use SincroCobranza\Repositories\CiudadRepo;
use SincroCobranza\Repositories\TipoContribuyenteRepo;
use SincroCobranza\Repositories\DivisaRepo;
use SincroCobranza\Repositories\ZonaRepo;
use SincroCobranza\Repositories\AtributoRepo;

class ClientesController extends \BaseController {
 
    protected $clienteRepo;
   	protected $ciudadRepo;
   	protected $tipoContribuyenteRepo;
	protected $divisaRepo;
    protected $zonaRepo;
    protected $atributoRepo;
	protected $title;

    public function __construct(CiudadRepo $ciudadRepo, 
    							TipoContribuyenteRepo $tipoContribuyenteRepo,
                                DivisaRepo $divisaRepo,
                                ZonaRepo $zonaRepo,
                                AtributoRepo $atributoRepo,
    							ClienteRepo $clienteRepo)
    {
       	$this->ciudadRepo = $ciudadRepo;
       	$this->tipoContribuyenteRepo = $tipoContribuyenteRepo;
    	$this->divisaRepo = $divisaRepo;
        $this->zonaRepo = $zonaRepo;
        $this->atributoRepo = $atributoRepo;
       	$this->clienteRepo = $clienteRepo;
		$this->title = \Lang::get('utils.Clientes');
    }

 	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
 	public function index()
	{
		if ( ! \Util::getAccess('Clientes')) return $this->accessFail();
		
		$cliente = new Cliente;
		$cliente_filter = array();
		$filter = Input::all();
				
		if ($filter) {
			$filter = array_only($filter, $cliente->getFillable());
			foreach($filter as $field => $value) {
				if (($value or $value == '0') and $field <> '_token' and $field <> 'page') {
					if (isset($fields)) {
						$fields .= ' AND ';
					} else {
						$fields = '';
						$values = array();
					}
										
					$cliente_filter[$field] = $value;
					switch ($field) {
						case 'Cli_Institucion':
						case 'Cli_Codigo':
						case 'Cli_Rif':
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
			$cliente->fill($filter);
		   	$clientes_list = $this->clienteRepo->getFiltered($fields, $values); 
		} else {
		   	$clientes_list = $this->clienteRepo->getAll();
		}
		
       	$ciudades = $this->ciudadRepo->getList();
        $zonas = $this->zonaRepo->getList();
        $atributos = $this->atributoRepo->getList();
		$cli_activo_nbr = \Lang::get('utils.Cli_Activo_Nbr');
		$obj_id = \Util::getObjId('Clientes');
		$title = $this->title;		
        $form_data = array('route' => 'clientes.index', 'method' => 'POST');
		
		return View::make('clientes/list', compact('cliente', 'clientes_list', 'cliente_filter', 'ciudades', 'zonas', 'atributos', 'cli_activo_nbr', 'obj_id', 'title', 'form_data'));
	}

	public function export()
	{
		if ( ! \Util::getAccess('Clientes_Export')) return $this->accessFail();
		
		return Excel::create('Clientes', function($excel) {
		
		    $excel->sheet('Clientes', function($sheet) {
		
			   	$clientes_list = $this->clienteRepo->getData();
		        $sheet->loadView('clientes/export', ['clientes_list' => $clientes_list]);
		
		    });
		
		})->export('xls');
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if ( ! \Util::getAccess('Clientes', 'Insert')) return $this->accessFail();
		
		$cliente = new Cliente;
       	$divisas = $this->divisaRepo->getList();
		$ciudades = $this->ciudadRepo->getList();
		$tiposContribuyentes = $this->tipoContribuyenteRepo->getList();
        $zonas = $this->zonaRepo->getList();
        $atributos = $this->atributoRepo->getList();
		$cli_activo_nbr = \Lang::get('utils.Cli_Activo_Nbr');
		$obj_id = \Util::getObjId('Clientes');
		$title = $this->title;		
        $form_data = array('route' => 'clientes.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('clientes/form', compact('cliente', 'ciudades', 'divisas', 'tiposContribuyentes', 'zonas',  'atributos', 'cli_activo_nbr', 'obj_id', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $cliente = $this->clienteRepo->getModel();
 
        $manager = new ClienteManager($cliente, Input::all());

        $manager->save();

       	return Redirect::route('clientes.index');
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('Clientes')) return $this->accessFail();

        $cliente = $this->clienteRepo->find($id);

        $this->notFoundUnless($cliente);

       	$divisas = $this->divisaRepo->getList();
		$ciudades = $this->ciudadRepo->getList();
		$tiposContribuyentes = $this->tipoContribuyenteRepo->getList();
        $zonas = $this->zonaRepo->getList();
        $atributos = $this->atributoRepo->getList();
		$cli_activo_nbr = \Lang::get('utils.Cli_Activo_Nbr');
		$obj_id = \Util::getObjId('Clientes');
		$title = $this->title;		
        $form_data = array('route' => 'clientes.index', 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('clientes/form', compact('cliente', 'ciudades', 'divisas', 'tiposContribuyentes', 'zonas',  'atributos', 'cli_activo_nbr', 'obj_id', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		if ( ! \Util::getAccess('Clientes')) return $this->accessFail();

        $cliente = $this->clienteRepo->find($id);

        $this->notFoundUnless($cliente);
		
      	$divisas = $this->divisaRepo->getList();
		$ciudades = $this->ciudadRepo->getList();
		$tiposContribuyentes = $this->tipoContribuyenteRepo->getList();
        $zonas = $this->zonaRepo->getList();
        $atributos = $this->atributoRepo->getList();
		$cli_activo_nbr = \Lang::get('utils.Cli_Activo_Nbr');
		$obj_id = \Util::getObjId('Clientes');
		$title = $this->title;		
        $form_data = array('route' => array('clientes.update', $cliente->Cli_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Clientes', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Clientes', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('clientes/form', compact('cliente', 'divisas', 'ciudades', 'tiposContribuyentes', 'zonas', 'atributos', 'cli_activo_nbr', 'obj_id', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $cliente = $this->clienteRepo->find($id);

        $this->notFoundUnless($cliente);
		
        $manager = new ClienteManager($cliente, Input::all());

        $manager->update();

       	return Redirect::route('clientes.index');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $cliente = $this->clienteRepo->find($id);

        $this->notFoundUnless($cliente);

	    \DB::table('clientes')
	        ->where('Emp_Id', $cliente->Emp_Id)
        	->where('Cli_Id', $cliente->Cli_Id)
	        ->delete();

       	return Redirect::route('clientes.index');
	}


}
