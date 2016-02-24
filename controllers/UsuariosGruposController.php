<?php

use SincroCobranza\Entities\UsuarioGrupo;
use SincroCobranza\Managers\UsuarioGrupoManager;
use SincroCobranza\Repositories\UserRepo;
use SincroCobranza\Repositories\GrupoRepo;
use SincroCobranza\Repositories\UsuarioGrupoRepo;

class UsuariosGruposController extends \BaseController {
 
   	protected $userRepo;
   	protected $grupoRepo;
   	protected $usuarioGrupoRepo;
	protected $title;

    public function __construct(UserRepo $userRepo,
    							GrupoRepo $grupoRepo,
    							UsuarioGrupoRepo $usuarioGrupoRepo)
    {
       	$this->userRepo = $userRepo;
       	$this->grupoRepo = $grupoRepo;
       	$this->usuarioGrupoRepo = $usuarioGrupoRepo;
		$this->title = \Lang::get('utils.Usuarios_Grupos');
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
		
		if (substr($id, 0, 1) == 'U') {
        	$entity = $this->userRepo->find(substr($id, 1));
	        $this->notFoundUnless($entity);
			
		   	$usuariosGrupos_list = $this->usuarioGrupoRepo->getGrupos(substr($id, 1));
			$title = $this->title;		
		} else {
        	$entity = $this->grupoRepo->find(substr($id, 1));
	        $this->notFoundUnless($entity);
			
		   	$usuariosGrupos_list = $this->usuarioGrupoRepo->getUsers(substr($id, 1));
			$title = \Lang::get('utils.Grupos_Usuarios');		
		}
			
		return View::make('usuariosGrupos/list', compact('usuariosGrupos_list', 'entity', 'id', 'title'));

	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($id)
	{
		if ( ! \Util::getAccess('Usuarios', 'Insert')) return $this->accessFail();
		
		$usuarioGrupo = new UsuarioGrupo();
		if (substr($id, 0, 1) == 'U') {
			$usuarioGrupo->User_Id = substr($id, 1);
		} else {
			$usuarioGrupo->Grp_Id = substr($id, 1);
		}
       	$usuarios = $this->userRepo->getList();
       	$grupos = $this->grupoRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => 'usuariosGrupos.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('usuariosGrupos/form', compact('usuarioGrupo', 'usuarios', 'grupos', 'id', 'title', 'form_data', 'action', 'action_label'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $usuarioGrupo = $this->usuarioGrupoRepo->getModel();
		
        $manager = new UsuarioGrupoManager($usuarioGrupo, Input::all());

        $manager->save();

       	return Redirect::route('usuariosGrupos.list', Input::get('id'));
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
		
        $usuarioGrupo = $this->usuarioGrupoRepo->find(substr($id, 1));

        $this->notFoundUnless($usuarioGrupo);
	
       	$usuarios = $this->userRepo->getList();
		$grupos = $this->grupoRepo->getList();
		$title = $this->title;
		$list_type = substr($id, 0, 1) == 'U' ? substr($id, 0, strpos($id, '-')) : 'G' . substr($id, strpos($id, '-') + 1);		
        $form_data = array('route' => array('usuariosGrupos.list', $list_type), 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('usuariosGrupos/form', compact('usuarioGrupo', 'usuarios', 'grupos', 'id', 'title', 'form_data', 'action', 'action_label'));
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
		
        $usuarioGrupo = $this->usuarioGrupoRepo->find(substr($id, 1));

        $this->notFoundUnless($usuarioGrupo);

       	$usuarios = $this->userRepo->getList();
		$grupos = $this->grupoRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => array('usuariosGrupos.update', $id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Usuarios', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Usuarios', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('usuariosGrupos/form', compact('usuarioGrupo', 'usuarios', 'grupos', 'id', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $usuarioGrupo = $this->usuarioGrupoRepo->find(substr($id, 1));

        $this->notFoundUnless($usuarioGrupo);
		
        $manager = new UsuarioGrupoManager($usuarioGrupo, Input::all());

        $manager->update();

		$list_type = substr($id, 0, 1) == 'U' ? substr($id, 0, strpos($id, '-')) : 'G' . substr($id, strpos($id, '-') + 1);		
       	return Redirect::route('usuariosGrupos.list', $list_type);
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $usuarioGrupo = $this->usuarioGrupoRepo->find(substr($id, 1));

        $this->notFoundUnless($usuarioGrupo);

	    \DB::table('usuarios_grupos')
	        ->where('Emp_Id', $usuarioGrupo->Emp_Id)
	        ->where('User_Id', $usuarioGrupo->User_Id)
        	->where('Grp_Id', $usuarioGrupo->Grp_Id)
	        ->delete();

 		$list_type = substr($id, 0, 1) == 'U' ? substr($id, 0, strpos($id, '-')) : 'G' . substr($id, strpos($id, '-') + 1);		
       	return Redirect::route('usuariosGrupos.list', $list_type);
 	}


}
