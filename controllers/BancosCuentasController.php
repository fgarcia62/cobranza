<?php

use SincroCobranza\Entities\BancoCuenta;
use SincroCobranza\Managers\BancoCuentaManager;
use SincroCobranza\Repositories\BancoCuentaRepo;
use SincroCobranza\Repositories\PaisRepo;
use SincroCobranza\Repositories\BancoRepo;
use SincroCobranza\Repositories\EmpresaRepo;

class BancosCuentasController extends \BaseController {
 
    protected $bancoCuentaRepo;
    protected $paisRepo;
    protected $bancoRepo;
    protected $empresaRepo;
	protected $title;

    public function __construct(BancoCuentaRepo $bancoCuentaRepo,
    							PaisRepo $paisRepo,
                                BancoRepo $bancoRepo,
                                EmpresaRepo $empresaRepo)
    {
       	$this->bancoCuentaRepo = $bancoCuentaRepo;
       	$this->paisRepo = $paisRepo;
        $this->bancoRepo = $bancoRepo;
        $this->empresaRepo = $empresaRepo;
      	$this->title = \Lang::get('utils.Bancos_Cuentas');
    }

 	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		if ( ! \Util::getAccess('Bancos_Cuentas')) return $this->accessFail();
		
		$bancoCuenta = new BancoCuenta;
		$bancoCuenta_filter = array();
		$filter = Input::all();
				
		if ($filter) {
			$filter = array_only($filter, $bancoCuenta->getFillable());
			foreach($filter as $field => $value) {
				if ($value and $field <> '_token' and $field <> 'page') {
					if (isset($fields)) {
						$fields .= ' AND ';
					} else {
						$fields = '';
						$values = array();
					}
										
					$bancoCuenta_filter[$field] = $value;
					switch ($field) {
						case 'BCta_Nbr':
                        case 'BCta_Codigo':
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
			$bancoCuenta->fill($filter);
		   	$bancosCuentas_list = $this->bancoCuentaRepo->getFiltered($fields, $values);
		} else {
		   	$bancosCuentas_list = $this->bancoCuentaRepo->getAll();
		}
		
		$paises = $this->paisRepo->getList();
        $bancos = $this->bancoRepo->getList();
        $empresas = $this->empresaRepo->getList();
		$title = $this->title;
        $form_data = array('route' => 'bancosCuentas.index', 'method' => 'POST');
		
		return View::make('bancosCuentas/list', compact('bancoCuenta', 'bancosCuentas_list', 'bancoCuenta_filter', 'paises', 'bancos', 'empresas', 'title', 'form_data'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if ( ! \Util::getAccess('Bancos_Cuentas', 'Insert')) return $this->accessFail();
		
		$bancoCuenta = new BancoCuenta;
        $bancos = $this->bancoRepo->getList();
        $empresas = $this->empresaRepo->getList();
		$title = $this->title;
        $form_data = array('route' => 'bancosCuentas.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('bancosCuentas/form', compact('bancoCuenta', 'bancos', 'empresas', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $bancoCuenta = $this->bancoCuentaRepo->getModel();
		
        $manager = new BancoCuentaManager($bancoCuenta, Input::all());

        $manager->save();

       	return Redirect::route('bancosCuentas.index');
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('Bancos_Cuentas')) return $this->accessFail();
		
        $bancoCuenta = $this->bancoCuentaRepo->find($id);

        $this->notFoundUnless($bancoCuenta);

        $bancos = $this->bancoRepo->getList();
        $empresas = $this->empresaRepo->getList();
		$title = $this->title;
        $form_data = array('route' => 'bancosCuentas.index', 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('bancosCuentas/form', compact('bancoCuenta', 'bancos', 'empresas', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		if ( ! \Util::getAccess('Bancos_Cuentas')) return $this->accessFail();
		
        $bancoCuenta = $this->bancoCuentaRepo->find($id);

        $this->notFoundUnless($bancoCuenta);
		
        $bancos = $this->bancoRepo->getList();
        $empresas = $this->empresaRepo->getList();
		$title = $this->title;
        $form_data = array('route' => array('bancosCuentas.update', $bancoCuenta->BCta_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Bancos_Cuentas', 'Update')) $action = 'Actualizar';
		if ( \Util::getAccess('Bancos_Cuentas', 'Delete')) $action = 'Eliminar';
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('bancosCuentas/form', compact('bancoCuenta', 'bancos', 'empresas', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $bancoCuenta = $this->bancoCuentaRepo->find($id);

        $this->notFoundUnless($bancoCuenta);
		
        $manager = new BancoCuentaManager($bancoCuenta, Input::all());

        $manager->save();

       	return Redirect::route('bancosCuentas.index');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $bancoCuenta = $this->bancoCuentaRepo->find($id);

        $this->notFoundUnless($bancoCuenta);

	    $bancoCuenta->delete();

       	return Redirect::route('bancosCuentas.index');
	}


}
