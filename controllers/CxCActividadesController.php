<?php

use SincroCobranza\Entities\CxCActividad;
use SincroCobranza\Managers\CxCActividadManager;
use SincroCobranza\Repositories\CxCRepo;
use SincroCobranza\Repositories\CiudadRepo;
use SincroCobranza\Repositories\ReferenciaRepo;
use SincroCobranza\Repositories\ActividadRepo;
use SincroCobranza\Repositories\CxCActividadRepo;

class CxCActividadesController extends \BaseController {
 
   	protected $cxcRepo;
    protected $ciudadRepo;
    protected $referenciaRepo;
   	protected $actividadRepo;
   	protected $cxcActividadRepo;
	protected $title;

    public function __construct(CxCRepo $cxcRepo,
                                CiudadRepo $ciudadRepo,
                                ReferenciaRepo $referenciaRepo,
                                ActividadRepo $actividadRepo,
    							CxCActividadRepo $cxcActividadRepo)
    {
       	$this->cxcRepo = $cxcRepo;
        $this->ciudadRepo = $ciudadRepo;
        $this->referenciaRepo = $referenciaRepo;
        $this->actividadRepo = $actividadRepo;
       	$this->cxcActividadRepo = $cxcActividadRepo;
		$this->title = \Lang::get('utils.CxC_Actividades');
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
		if ( ! \Util::getAccess('CxC')) return $this->accessFail();
		
        $cxc = $this->cxcRepo->find($id, 'view');

        $this->notFoundUnless($cxc);
		
	   	$cxcActividades_list = $this->cxcActividadRepo->getAll($id);
        $referencia = $this->referenciaRepo->find($cxc->CxC_Tp_Ref . '-' . $cxc->CxC_Ref_Id);
        $ciudad = $this->ciudadRepo->find($cxc->Ciudad_Id, 'view');
        $title = $this->title;
		
		return View::make('cxcActividades/list', compact('cxcActividades_list', 'cxc', 'referencia', 'ciudad', 'title'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($id)
	{
		if ( ! \Util::getAccess('CxC', 'Insert')) return $this->accessFail();

        $cxc = $this->cxcRepo->find($id, 'view');

        $this->notFoundUnless($cxc);

		$cxcActividad = new CxCActividad();
		$cxcActividad->CxC_Id = $id;
        $cxcActividad->CxC_Act_Fch_Prog = date(\Util::getDateFormat());
        $cxcActividades_list = $this->cxcActividadRepo->getAll($id);
       	$actividades = $this->actividadRepo->getList();
        $referencia = $this->referenciaRepo->find($cxc->CxC_Tp_Ref . '-' . $cxc->CxC_Ref_Id);
        $ciudad = $this->ciudadRepo->find($cxc->Ciudad_Id, 'view');
		$title = $this->title;
        $form_data = array('route' => 'cxcActividades.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('cxcActividades/form', compact('cxcActividad', 'cxcActividades_list', 'cxc', 'actividades', 'referencia', 'ciudad', 'title', 'form_data', 'action', 'action_label'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $cxcActividad = $this->cxcActividadRepo->getModel();
		
        $manager = new CxCActividadManager($cxcActividad, Input::all());

        $manager->save();

        $cxc = $this->cxcRepo->find($cxcActividad->CxC_Id);

       	return Redirect::route('cxc.list','1-' . $cxc->CxC_Ref_Id . '-0');
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		if ( ! \Util::getAccess('CxC')) return $this->accessFail();

        $cxcActividad = $this->cxcActividadRepo->find($id);

        $this->notFoundUnless($cxcActividad);

        $cxc = $this->cxcRepo->find($cxcActividad->CxC_Id);

        $this->notFoundUnless($cxc, 'view');

        $cxcActividades_list = $this->cxcActividadRepo->getAll($id);
		$actividades = $this->actividadRepo->getList();
        $referencia = $this->referenciaRepo->find($cxc->CxC_Tp_Ref . '-' . $cxc->CxC_Ref_Id);
        $ciudad = $this->ciudadRepo->find($cxc->Ciudad_Id, 'view');
		$title = $this->title;
        $form_data = array('route' => array('cxcActividades.list', $cxcActividad->CxC_Id), 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('cxcActividades/form', compact('cxcActividad', 'cxc', 'cxcActividades_list', 'actividades', 'referencia', 'ciudad', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		if ( ! \Util::getAccess('CxC')) return $this->accessFail();

        $cxcActividad = $this->cxcActividadRepo->find($id);

        $this->notFoundUnless($cxcActividad);

        $cxc = $this->cxcRepo->find($cxcActividad->CxC_Id, 'view');

        $this->notFoundUnless($cxc);

        $cxcActividades_list = $this->cxcActividadRepo->getAll($id);
		$actividades = $this->actividadRepo->getList();
        $referencia = $this->referenciaRepo->find($cxc->CxC_Tp_Ref . '-' . $cxc->CxC_Ref_Id);
        $ciudad = $this->ciudadRepo->find($cxc->Ciudad_Id, 'view');
		$title = $this->title;
        $form_data = array('route' => array('cxcActividades.update', $cxcActividad->CxC_Id . '-' . $cxcActividad->CxC_Act_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('CxC', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('CxC', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('cxcActividades/form', compact('cxcActividad', 'cxc', 'cxcActividades_list', 'actividades', 'referencia', 'ciudad', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $cxcActividad = $this->cxcActividadRepo->find($id);

        $this->notFoundUnless($cxcActividad);

        $manager = new CxCActividadManager($cxcActividad, Input::all());

        $manager->update();

        $cxc = $this->cxcRepo->find($cxcActividad->CxC_Id);

        return Redirect::route('cxc.list','1-' . $cxc->CxC_Ref_Id . '-0');
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $cxcActividad = $this->cxcActividadRepo->find($id);

        $this->notFoundUnless($cxcActividad);

        \DB::table('cxc_actividades')
            ->where('CxC_Id', $cxcActividad->CxC_Id)
            ->where('CxC_Act_Id', $cxcActividad->CxC_Act_Id)
            ->delete();

        $cxc = $this->cxcRepo->find($cxcActividad->CxC_Id);

        return Redirect::route('cxc.list','1-' . $cxc->CxC_Ref_Id . '-0');
 	}


}
