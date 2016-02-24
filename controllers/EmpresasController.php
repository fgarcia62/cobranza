<?php

use SincroCobranza\Entities\Empresa;
use SincroCobranza\Entities\EmpresaPais;
use SincroCobranza\Entities\User;
use SincroCobranza\Entities\UsuarioEmpresa;
use SincroCobranza\Managers\EmpresaManager;
use SincroCobranza\Repositories\CiudadRepo;
use SincroCobranza\Repositories\EmpresaRepo;

class EmpresasController extends \BaseController {
 
    protected $empresaRepo;
   	protected $ciudadRepo;
	protected $title;

    public function __construct(CiudadRepo $ciudadRepo, 
    							EmpresaRepo $empresaRepo)
    {
       	$this->ciudadRepo = $ciudadRepo;
       	$this->empresaRepo = $empresaRepo;
		$this->title = \Lang::get('utils.Empresas');
    }

 	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		if ( ! \Util::getAccess('Empresas')) return $this->accessFail();
		
	   	$empresas_list = $this->empresaRepo->getAll();
		$obj_id = \Util::getObjId('Empresas');
		$title = $this->title;		
		
		return View::make('empresas/list', compact('empresas_list', 'obj_id', 'title'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if ( ! \Util::getAccess('Empresas', 'Insert') or Auth::user()->Emp_Id) return $this->accessFail();
		
		$empresa = new Empresa;
		$ciudades = $this->ciudadRepo->getListAll();
		$emp_doc_vencido = 0;
		$emp_cant_docs = 0;
		$obj_id = \Util::getObjId('Empresas');
		$title = $this->title;		
        $form_data = array('route' => 'empresas.store', 'method' => 'POST', 'files' => true);
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('empresas/form', compact('empresa', 'ciudades', 'emp_doc_vencido', 'emp_cant_docs', 'obj_id', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $empresa = $this->empresaRepo->getModel();
		
        $manager = new EmpresaManager($empresa, Input::all());

        $manager->save();
		
		if (Input::hasFile('file_path')) {
			if ( ! $empresa->Emp_Logo_Nbr) {
				$empresa->Emp_Logo_Nbr = Input::file('file_path')->getClientOriginalName();
			    
			    \DB::table('empresas')
			        ->where('Emp_Id', $empresa->Emp_Id)
			        ->update(['Emp_Logo_Nbr' => Input::file('file_path')->getClientOriginalName()]);
			}
						
			Input::file('file_path')->move( $empresa->Emp_Doc_Dir, $empresa->Emp_Logo_Nbr );
		}
		
		$user = new User;
		$user->full_name = $empresa->Emp_Nbr;
		$user->email = $empresa->Emp_Codigo . '@sincrosistemas.com';
		$user->password = $empresa->Emp_Codigo;
		$user->type = 'admin';
		$user->User_Activo = 1;
		$user->User_Permisos = 'M';
		$user->Idioma_Id = 'es';
		$user->User_Pagination = 15;
		$user->User_Date_Format = 'd/m/Y';
		$user->User_Decimal_Point = 'P';
		$user->Emp_Id = $empresa->Emp_Id;
		$user->save();
		
		$userEmp = new UsuarioEmpresa;
		$userEmp->Emp_Id = $empresa->Emp_Id;
		$userEmp->User_Id = $user->id;
		$userEmp->User_Nbr = $empresa->Emp_Nbr;
		$userEmp->User_Tp = 'M';
		$userEmp->User_Login = $empresa->Emp_Codigo;
		$userEmp->User_Passwd = $empresa->Emp_Codigo;
		$userEmp->User_Permisos = 'M';
		$userEmp->Idioma_Id = 'es';
		$userEmp->User_Activo = 1;
		$userEmp->User_Email = $empresa->Emp_Codigo . '@sincrosistemas.com';
		$userEmp->save();
		
		$empresaPais = new EmpresaPais;
		$empresaPais->Emp_Id = $empresa->Emp_Id;
		$empresaPais->Pais_Id = $empresa->ciudad->Pais_Id;
		$empresaPais->save();
		
		$sql  = 'INSERT INTO objetos_empresas ';
		$sql .= 'SELECT ' . $empresa->Emp_Id . ' AS Emp_Id, E.Obj_Id, E.Obj_Tp, E.Obj_Nbr, E.Obj_Prefix, E.Obj_Key, E.Obj_Desc, E.Obj_SKey, E.Obj_SSKey ';
		$sql .= 'FROM objetos_empresas E JOIN objetos O ON E.Obj_Nbr = O.Obj_Nbr WHERE Emp_Id = 2';
		\DB::statement($sql); 

       	return Redirect::route('empresas.index');
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('Empresas')) return $this->accessFail();
		
        $empresa = $this->empresaRepo->find($id);

        $this->notFoundUnless($empresa);

		$ciudades = $this->ciudadRepo->getListAll();
		$emp_doc_vencido = $this->empresaRepo->getDocVencido($id);
		$emp_cant_docs = $this->empresaRepo->getDocs($id);
		$obj_id = \Util::getObjId('Empresas');
		$title = $this->title;		
        $form_data = array('route' => 'empresas.index', 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('empresas/form', compact('empresa', 'ciudades', 'emp_doc_vencido', 'emp_cant_docs', 'obj_id', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		if ( ! \Util::getAccess('Empresas')) return $this->accessFail();
		
        $empresa = $this->empresaRepo->find($id);

        $this->notFoundUnless($empresa);
		
		$ciudades = $this->ciudadRepo->getListAll();
		$emp_doc_vencido = $this->empresaRepo->getDocVencido($id);
		$emp_cant_docs = $this->empresaRepo->getDocs($id);
		$obj_id = \Util::getObjId('Empresas');
		$title = $this->title;		
        $form_data = array('route' => array('empresas.update', $empresa->Emp_Id), 'method' => 'PATCH', 'files' => true);
        $action    = '';
		$action_label = '';
		
		if ( ! Auth::user()->Emp_Id) {
			if ( \Util::getAccess('Empresas', 'Update')) $action = 'Actualizar';	
			if ( \Util::getAccess('Empresas', 'Delete')) $action = 'Eliminar';	
			$action_label = \Lang::get('utils.Actualizar');
		}

        return View::make('empresas/form', compact('empresa', 'ciudades', 'emp_doc_vencido', 'emp_cant_docs', 'obj_id', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $empresa = $this->empresaRepo->find($id);

        $this->notFoundUnless($empresa);
		
        $manager = new EmpresaManager($empresa, Input::all());

        $manager->save();
		
		if (Input::hasFile('file_path')) {
			if ( ! $empresa->Emp_Logo_Nbr) {
				$empresa->Emp_Logo_Nbr = Input::file('file_path')->getClientOriginalName();
			    
			    \DB::table('empresas')
			        ->where('Emp_Id', $empresa->Emp_Id)
			        ->update(['Emp_Logo_Nbr' => Input::file('file_path')->getClientOriginalName()]);
			}
			
			Input::file('file_path')->move( $empresa->Emp_Doc_Dir, $empresa->Emp_Logo_Nbr );
		}

       	return Redirect::route('empresas.index');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $empresa = $this->empresaRepo->find($id);

        $this->notFoundUnless($empresa);

        $empresa->delete();

       	return Redirect::route('empresas.index');
	}


}
