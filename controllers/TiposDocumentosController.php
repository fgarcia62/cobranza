<?php

use SincroCobranza\Entities\TipoDocumento;
use SincroCobranza\Managers\TipoDocumentoManager;
use SincroCobranza\Repositories\TipoDocumentoRepo;

class TiposDocumentosController extends \BaseController {
 
    protected $tipoDocumentoRepo;
    protected $unidadRepo;
	protected $title;

    public function __construct(TipoDocumentoRepo $tipoDocumentoRepo)
    {
       	$this->tipoDocumentoRepo = $tipoDocumentoRepo;
		$this->title = \Lang::get('utils.Tipos_Documentos');
    }

 	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */
	public function index()
	{
		if ( ! \Util::getAccess('Tipos_Documentos')) return $this->accessFail();
		
	   	$tiposDocumentos_list = $this->tipoDocumentoRepo->getAll();
		$title = $this->title;		
		
		return View::make('tiposDocumentos/list', compact('tiposDocumentos_list', 'title'));
	}


	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create()
	{
		if ( ! \Util::getAccess('Tipos_Documentos', 'Insert')) return $this->accessFail();
		
		$tipoDocumento = new TipoDocumento;
		$title = $this->title;		
        $form_data = array('route' => 'tiposDocumentos.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('tiposDocumentos/form', compact('tipoDocumento', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $tipoDocumento = $this->tipoDocumentoRepo->getModel();
		
        $manager = new TipoDocumentoManager($tipoDocumento, Input::all());

        $manager->save();

       	return Redirect::route('tiposDocumentos.index');
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('Tipos_Documentos')) return $this->accessFail();
		
        $tipoDocumento = $this->tipoDocumentoRepo->find($id);

        $this->notFoundUnless($tipoDocumento);

		$title = $this->title;		
        $form_data = array('route' => 'tiposDocumentos.index', 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('tiposDocumentos/form', compact('tipoDocumento', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
 		if ( ! \Util::getAccess('Tipos_Documentos')) return $this->accessFail();
		
        $tipoDocumento = $this->tipoDocumentoRepo->find($id);

        $this->notFoundUnless($tipoDocumento);
		
		$title = $this->title;		
        $form_data = array('route' => array('tiposDocumentos.update', $tipoDocumento->Tp_Doc_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('Tipos_Documentos', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Tipos_Documentos', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('tiposDocumentos/form', compact('tipoDocumento', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $tipoDocumento = $this->tipoDocumentoRepo->find($id);

        $this->notFoundUnless($tipoDocumento);
		
        $manager = new TipoDocumentoManager($tipoDocumento, Input::all());

        $manager->update();

       	return Redirect::route('tiposDocumentos.index');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $tipoDocumento = $this->tipoDocumentoRepo->find($id);

        $this->notFoundUnless($tipoDocumento);

	    \DB::table('tipos_documentos')
	        ->where('Emp_Id', $tipoDocumento->Emp_Id)
        	->where('Tp_Doc_Id', $tipoDocumento->Tp_Doc_Id)
	        ->delete();

       	return Redirect::route('tiposDocumentos.index');
	}


}
