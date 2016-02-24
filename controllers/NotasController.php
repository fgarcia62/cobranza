<?php

use SincroCobranza\Entities\Nota;
use SincroCobranza\Managers\NotaManager;
use SincroCobranza\Repositories\NotaRepo;
use SincroCobranza\Entities\NotaDetalle;
use SincroCobranza\Managers\NotaDetalleManager;
use SincroCobranza\Repositories\NotaDetalleRepo;
use SincroCobranza\Repositories\UserRepo;
use SincroCobranza\Repositories\UsuarioEmpresaRepo;
use SincroCobranza\Repositories\ObjetoRepo;
use SincroCobranza\Repositories\GrupoRepo;

class NotasController extends \BaseController {
 
    protected $notaRepo;
    protected $userRepo;
   	protected $objetoRepo;
	protected $title;

    public function __construct(UserRepo $userRepo, 
    							UsuarioEmpresaRepo $usuarioEmpresaRepo,
    							GrupoRepo $grupoRepo,
    							ObjetoRepo $objetoRepo, 
    							NotaRepo $notaRepo,
								NotaDetalleRepo $notaDetalleRepo)
    {
       	$this->userRepo = $userRepo;
       	$this->usuarioEmpresaRepo = $usuarioEmpresaRepo;
       	$this->grupoRepo = $grupoRepo;
       	$this->objetoRepo = $objetoRepo;
       	$this->notaRepo = $notaRepo;
       	$this->notaDetalleRepo = $notaDetalleRepo;
		$this->title = \Lang::get('utils.Notas');
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
		if ( ! \Util::getAccess('Notas')) return $this->accessFail();
		
		$nota = new Nota;
		$nota_filter = array();
		$filter = Input::all();
	   	$ids = explode('-', $id);

	   	$order_val = array(
			'0'   		=> 'User_Nbr_Org',
	        '1'   		=> 'User_Nbr_Dest',
	        '2'   		=> 'Nota_Id',
	        '3'			=> 'Obj_Nbr',
	        '4'			=> 'Nota_Status',
	    );

		$order = $order_val[2];
						
		if ($filter) {
			$filter = array_only($filter, $nota->getFillable());
			foreach($filter as $field => $value) {
				if ($field == 'Nota_Orden') {
					$order = $order_val [$value];;
					$nota_filter[$field] = $value;
				}

				if (($value or $value == '0') and $field <> 'Nota_Orden') {
					if (isset($fields)) {
						$fields .= ' AND ';
					} else {
						if ($id == 'U') {
							$fields = 'User_Id_Dest = ? AND Nota_Status = 0 AND ';
							$values = array(\Auth::user()->id);
						} elseif ($id == 'S' ) {
							$fields = '';
							$values = array();
						} else {
							$fields = 'Obj_Id = ? AND ';
							$values = array($ids[0]);
							if( isset($ids[1] )){
								$fields .= 'Nota_Oper_Id = ? AND ';
								$values[] = $ids[1];
							}
							if( isset($ids[2] )){
								$fields .= 'Nota_SOper_Id = ? AND ';
								$values[] = $ids[2];
							}
						}	
					}
										
					$nota_filter[$field] = $value;
					switch ($field) {
						case 'Nota_Fch_Emision_LB':
							$fields .= 'Nota_Fch_Emision >= ?';
							$value = \DateTime::createFromFormat (\Auth::user()->User_Date_Format . \Util::getTimeFormat(), $value . \Util::getTimeLB());
							break;
						case 'Nota_Fch_Emision_UB':
							$fields .= 'Nota_Fch_Emision <= ?';
							$value = \DateTime::createFromFormat (\Auth::user()->User_Date_Format . \Util::getTimeFormat(), $value . \Util::getTimeUB());
							break;
						default:
							$fields .= $field . ' = ?';
					}						
					$values[] = $value;
				}
			}
		}

		if ($id == 'U') {
			$nota->User_Id_Dest = \Auth::user()->id;
			$nota->Nota_Status = 0;
		} elseif ($id <> 'S' ) {
			$nota->Obj_Id = $ids[0];
			if( isset($ids[1] )){
				$nota->Nota_Oper_Id = $ids[1];
			}
			if( isset($ids[2] )){
				$nota->Nota_SOper_Id = $ids[2];
			}
		}
		
		if (isset($fields)) {
			$nota->fill($filter);
		   	$notas_list = $this->notaRepo->getFiltered($fields, $values, $order); 
		} else {
			$nota->Nota_Orden = 2;
		   	$notas_list = $this->notaRepo->getAll($id, $order);
		}
		
       	$usuariosEmpresas = $this->usuarioEmpresaRepo->getList();
       	$objetos = $this->objetoRepo->getList();
		$obj_nbr = '';
		if ($nota->Obj_Id) {
			$obj_nbr = \Util::getObjNbr($nota->Obj_Id);
		}
		$nota_status_nbr = \Lang::get('utils.Nota_Status_Nbr');
		$nota_orden_nbr = \Lang::get('utils.Nota_Orden_Nbr');
		$title = $this->title;		
        $form_data = array('route' => array('notas.list', 'S'), 'method' => 'POST');
		
		return View::make('notas/list', compact('nota', 'notas_list', 'nota_filter', 'usuariosEmpresas', 'objetos', 'obj_nbr', 'nota_status_nbr', 'nota_orden_nbr', 'title', 'form_data'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($id)
	{
		if ( ! \Util::getAccess('Notas', 'Insert')) return $this->accessFail();
		
		$nota = new Nota;
		$response = false;
		$nota_id_org = 0;
		
   		$ids = explode('-', $id);
		if( isset($ids[0] )) {
			switch (substr($ids[0], 0 , 1)) {
				case 'A':
				case 'R':
					$response = substr($ids[0], 0 , 1);
					$nota_id_org = substr($ids[0], 1 , strpos($ids[0], 'O') - 1);
					$nota_org = $this->notaRepo->find($nota_id_org);
					$nota->Nota_Header = 'FW: '. $nota_org->Nota_Header;
					$nota->Nota_Memo = $nota_org->Nota_Memo;
					$nota->Obj_Id = substr($ids[0], strpos($ids[0], 'O') + 1); 
					$id = substr($id, strpos($id, 'O') + 1); 
					break;
				default:
					$nota->Obj_Id = $ids[0]; 
			}
		}
		if( isset($ids[1] )) {
			$nota->Nota_Oper_Id = $ids[1]; 
		}
		if( isset($ids[2] )) {
			$nota->Nota_SOper_Id = $ids[2]; 
		}
		
		$nota->User_Id_Org = \Auth::user()->User_Id;
		$user_nbr_org = $this->usuarioEmpresaRepo->find(\Auth::user()->User_Id)->User_Nbr;
		date_default_timezone_set('America/Caracas'); 
		$nota->Nota_Fch_Emision = date(\Auth::user()->User_Date_Format . \Util::getTimeFormat());
       	$usuariosEmpresas = $this->usuarioEmpresaRepo->getList();
      	$grupos = $this->grupoRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => 'notas.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');
		$nota_ids = $id;

        return View::make('notas/form', compact('nota', 'usuariosEmpresas', 'user_nbr_org', 'grupos', 'nota_ids', 'response', 'nota_id_org', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $nota = $this->notaRepo->getModel();
        $manager = new NotaManager($nota, Input::all());

        $manager->save();

		switch (Input::get('response')) {
			case  'R':
				$nota_org = $this->notaRepo->find(Input::get('nota_id_org'));
				$this->store_usuario($nota->Nota_Id, $nota_org->User_Id_Org);
				break;
			case 'A':
				$nota_org = $this->notaRepo->find(Input::get('nota_id_org'));
				$this->store_usuario($nota->Nota_Id, $nota_org->User_Id_Org);
				$users = $this->notaDetalleRepo->getUsers(Input::get('nota_id_org'));
				foreach ($users as $user) {
					if ($user <> $nota->User_Id_Org) {
						$this->store_usuario($nota->Nota_Id, $user);
					}
				}
		}
		
		if (Input::get('User_Id_Dest1')) {
			$this->store_usuario($nota->Nota_Id, Input::get('User_Id_Dest1'));
		}
	
		if (Input::get('User_Id_Dest2')) {
			$this->store_usuario($nota->Nota_Id, Input::get('User_Id_Dest2'));
		}
	
		if (Input::get('User_Id_Dest3')) {
			$this->store_usuario($nota->Nota_Id, Input::get('User_Id_Dest3'));
		}
	
		if (Input::get('User_Id_Dest4')) {
			$this->store_usuario($nota->Nota_Id, Input::get('User_Id_Dest4'));
		}
		
		if (Input::get('Grp_Id_Dest1')) {
			$this->store_grupo($nota->Nota_Id, Input::get('Grp_Id_Dest1'));
		}
		
		if (Input::get('Grp_Id_Dest2')) {
			$this->store_grupo($nota->Nota_Id, Input::get('Grp_Id_Dest2'));
		}
		
		if (Input::get('Grp_Id_Dest3')) {
			$this->store_grupo($nota->Nota_Id, Input::get('Grp_Id_Dest3'));
		}
		
		if (Input::get('Grp_Id_Dest4')) {
			$this->store_grupo($nota->Nota_Id, Input::get('Grp_Id_Dest4'));
		}

       	return Redirect::route('notas.list', Input::get('nota_ids'));
	}

	public function store_usuario($nota_id, $user_id)
	{
	        $notaDetalle = $this->notaDetalleRepo->getModel();
			$notaDetalle->Emp_Id = \Auth::user()->Emp_Id;
			$notaDetalle->Nota_Id = $nota_id;
			$notaDetalle->User_Id_Dest = $user_id;
			$notaDetalle->Nota_Status = 0;
			$notaDetalle->save();
	}

	public function store_grupo($nota_id, $grp_id)
	{
		$users = $this->grupoRepo->getUsuariosEmpresas($grp_id);
		foreach ($users as $user) {
			$this->store_usuario($nota_id, $user->User_Id);
		}
	}
	
	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('Notas')) return $this->accessFail();
		
        $notaDetalle = $this->notaDetalleRepo->find($id);

        $this->notFoundUnless($notaDetalle);

		$users = $this->notaDetalleRepo->getUsersName($notaDetalle->Nota_Id);
		$user_nbr_dest = $this->notaDetalleRepo->getNotaDetalle($id)->User_Nbr_Dest;
		$users = array_diff($users, [$user_nbr_dest]);
		$users = implode(', ', $users);
		$nota_status_nbr = \Lang::get('utils.Nota_Status_Nbr');
		$nota = $this->notaDetalleRepo->getNota($notaDetalle->Nota_Id);
		$title = $this->title;		
        $form_data = array('route' => array('notas.list', 'U'), 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('notasDetalle/form', compact('notaDetalle', 'nota', 'users', 'user_nbr_dest', 'nota_status_nbr', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		if ( ! \Util::getAccess('Notas')) return $this->accessFail();
		
        $notaDetalle = $this->notaDetalleRepo->find($id);

        $this->notFoundUnless($notaDetalle);
		
		$users = $this->notaDetalleRepo->getUsersName($notaDetalle->Nota_Id);
		$user_nbr_dest = $this->notaDetalleRepo->getNotaDetalle($id)->User_Nbr_Dest;
		$users = array_diff($users, [$user_nbr_dest]);
		$users = implode(', ', $users);
		$nota_status_nbr = \Lang::get('utils.Nota_Status_Nbr');
		$nota = $this->notaDetalleRepo->getNota($notaDetalle->Nota_Id);
		$title = $this->title;		
        $form_data = array('route' => array('notas.update', $id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Notas', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Notas', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('notasDetalle/form', compact('notaDetalle', 'nota', 'users', 'user_nbr_dest', 'nota_status_nbr', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $notaDetalle = $this->notaDetalleRepo->find($id);

        $this->notFoundUnless($notaDetalle);
		
        $manager = new NotaDetalleManager($notaDetalle, Input::all());

        $manager->update();

       	return Redirect::route('notas.list', 'U');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $notaDetalle = $this->notaDetalleRepo->find($id);

        $this->notFoundUnless($notaDetalle);

        $notaDetalle->delete();

       	return Redirect::route('notas.list', 'U');
	}


}
