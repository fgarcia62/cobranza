<?php

use SincroCobranza\Entities\Grupo;
use SincroCobranza\Managers\GrupoManager;
use SincroCobranza\Repositories\GrupoRepo;

class GruposController extends \BaseController {
 
    protected $grupoRepo;
	protected $title;

    public function __construct(GrupoRepo $grupoRepo)
    {
       	$this->grupoRepo = $grupoRepo;
		$this->title = \Lang::get('utils.Grupos');
    }

 	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		if ( ! \Util::getAccess('Grupos')) return $this->accessFail();
		
	   	$grupos_list = $this->grupoRepo->getAll();
		$title = $this->title;		
		
		return View::make('grupos/list', compact('grupos_list', 'title'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if ( ! \Util::getAccess('Grupos', 'Insert')) return $this->accessFail();
		
		$grupo = new Grupo;
		$title = $this->title;		
        $form_data = array('route' => 'grupos.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('grupos/form', compact('grupo', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $grupo = $this->grupoRepo->getModel();
		
        $manager = new GrupoManager($grupo, Input::all());

        $manager->save();

       	return Redirect::route('grupos.index');
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
		
        $grupo = $this->grupoRepo->find($id);

        $this->notFoundUnless($grupo);

		$title = $this->title;		
        $form_data = array('route' => 'grupos.index', 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('grupos/form', compact('grupo', 'title', 'form_data', 'action', 'action_label'));
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
		
        $grupo = $this->grupoRepo->find($id);

        $this->notFoundUnless($grupo);
		
		$title = $this->title;		
        $form_data = array('route' => array('grupos.update', $grupo->Grp_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Grupos', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Grupos', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('grupos/form', compact('grupo', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $grupo = $this->grupoRepo->find($id);

        $this->notFoundUnless($grupo);
		
        $manager = new GrupoManager($grupo, Input::all());

        $manager->update();

       	return Redirect::route('grupos.index');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $grupo = $this->grupoRepo->find($id);

        $this->notFoundUnless($grupo);

	    \DB::table('grupos')
	        ->where('Emp_Id', $grupo->Emp_Id)
        	->where('Grp_Id', $grupo->Grp_Id)
	        ->delete();

       	return Redirect::route('grupos.index');
	}


}
