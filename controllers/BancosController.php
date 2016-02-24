<?php

use SincroCobranza\Entities\Banco;
use SincroCobranza\Managers\BancoManager;
use SincroCobranza\Repositories\BancoRepo;
use SincroCobranza\Repositories\PaisRepo;

class BancosController extends \BaseController {
 
    protected $bancoRepo;
    protected $paisRepo;
	protected $title;

    public function __construct(BancoRepo $bancoRepo, 
    							PaisRepo $paisRepo)
    {
       	$this->bancoRepo = $bancoRepo;
       	$this->paisRepo = $paisRepo;
      	$this->title = \Lang::get('utils.Bancos');
    }

 	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		if ( ! \Util::getAccess('Bancos')) return $this->accessFail();
		
		$banco = new Banco;
		$banco_filter = array();
		$filter = Input::all();
				
		if ($filter) {
			$filter = array_only($filter, $banco->getFillable());
			foreach($filter as $field => $value) {
				if ($value and $field <> '_token' and $field <> 'page') {
					if (isset($fields)) {
						$fields .= ' AND ';
					} else {
						$fields = '';
						$values = array();
					}
										
					$banco_filter[$field] = $value;
					switch ($field) {
						case 'Bco_Nbr':
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
			$banco->fill($filter);
		   	$bancos_list = $this->bancoRepo->getFiltered($fields, $values); 
		} else {
		   	$bancos_list = $this->bancoRepo->getAll();
		}
		
		$paises = $this->paisRepo->getList();
		$title = $this->title;
        $form_data = array('route' => 'bancos.index', 'method' => 'POST');
		
		return View::make('bancos/list', compact('banco', 'bancos_list', 'banco_filter', 'paises', 'title', 'form_data'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if ( ! \Util::getAccess('Bancos', 'Insert')) return $this->accessFail();
		
		$banco = new Banco;
		$paises = $this->paisRepo->getList();
		$title = $this->title;
        $form_data = array('route' => 'bancos.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('bancos/form', compact('banco', 'paises', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $banco = $this->bancoRepo->getModel();
		
        $manager = new BancoManager($banco, Input::all());

        $manager->save();

       	return Redirect::route('bancos.index');
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('Bancos')) return $this->accessFail();
		
        $banco = $this->bancoRepo->find($id);

        $this->notFoundUnless($banco);

		$paises = $this->paisRepo->getList();
		$title = $this->title;
        $form_data = array('route' => 'bancos.index', 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('bancos/form', compact('banco', 'paises', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		if ( ! \Util::getAccess('Bancos')) return $this->accessFail();
		
        $banco = $this->bancoRepo->find($id);

        $this->notFoundUnless($banco);
		
		$paises = $this->paisRepo->getList();
		$title = $this->title;
        $form_data = array('route' => array('bancos.update', $banco->Bco_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Bancos', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Bancos', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('bancos/form', compact('banco', 'paises', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $banco = $this->bancoRepo->find($id);

        $this->notFoundUnless($banco);
		
        $manager = new BancoManager($banco, Input::all());

        $manager->save();

       	return Redirect::route('bancos.index');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $banco = $this->bancoRepo->find($id);

        $this->notFoundUnless($banco);

	    $banco->delete();

       	return Redirect::route('bancos.index');
	}


}
