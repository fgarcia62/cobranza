<?php

use SincroCobranza\Entities\CxCDetalle;
use SincroCobranza\Managers\CxCDetalleManager;
use SincroCobranza\Repositories\CxCRepo;
use SincroCobranza\Repositories\CiudadRepo;
use SincroCobranza\Repositories\ReferenciaRepo;
use SincroCobranza\Repositories\CxCDetalleRepo;

class CxCDetalleController extends \BaseController {
 
   	protected $cxcRepo;
    protected $ciudadRepo;
    protected $referenciaRepo;
    protected $cxcDetalleRepo;
	protected $title;

    public function __construct(CxCRepo $cxcRepo,
                                CiudadRepo $ciudadRepo,
                                ReferenciaRepo $referenciaRepo,
                                CxCDetalleRepo $cxcDetalleRepo)
    {
       	$this->cxcRepo = $cxcRepo;
        $this->ciudadRepo = $ciudadRepo;
        $this->referenciaRepo = $referenciaRepo;
        $this->cxcDetalleRepo = $cxcDetalleRepo;
		$this->title = \Lang::get('utils.CxC_Detalle');
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
		
	   	$cxcDetalle_list = $this->cxcDetalleRepo->getAll($id);
        $cxcDetalle_total = $this->cxcDetalleRepo->getTotales($id);
        $referencia = $this->referenciaRepo->find($cxc->CxC_Tp_Ref . '-' . $cxc->CxC_Ref_Id);
        $ciudad = $this->ciudadRepo->find($cxc->Ciudad_Id, 'view');
		$title = $this->title;
		
		return View::make('cxcDetalle/list', compact('cxcDetalle_list', 'cxcDetalle_total', 'cxc', 'referencia', 'ciudad', 'title'));
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

		$cxcDetalle = new CxCDetalle();
		$cxcDetalle->CxC_Id = $id;
        $referencia = $this->referenciaRepo->find($cxc->CxC_Tp_Ref . '-' . $cxc->CxC_Ref_Id);
        $ciudad = $this->ciudadRepo->find($cxc->Ciudad_Id, 'view');
		$title = $this->title;
        $form_data = array('route' => 'cxcDetalle.store', 'method' => 'POST');
        $action    = 'Ingresar';        
		$action_label = \Lang::get('utils.Ingresar');

        return View::make('cxcDetalle/form', compact('cxcDetalle', 'cxc', 'referencia', 'ciudad', 'title', 'form_data', 'action', 'action_label'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $cxcDetalle = $this->cxcDetalleRepo->getModel();
		
        $manager = new CxCDetalleManager($cxcDetalle, Input::all());

        $manager->save();

       	return Redirect::route('cxcDetalle.list', $cxcDetalle->CxC_Id);
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

        $cxcDetalle = $this->cxcDetalleRepo->find($id);

        $this->notFoundUnless($cxcDetalle);

        $cxc = $this->cxcRepo->find($cxcDetalle->CxC_Id);

        $this->notFoundUnless($cxc, 'view');

        $referencia = $this->referenciaRepo->find($cxc->CxC_Tp_Ref . '-' . $cxc->CxC_Ref_Id);
        $ciudad = $this->ciudadRepo->find($cxc->Ciudad_Id, 'view');
		$title = $this->title;
        $form_data = array('route' => array('cxcDetalle.list', $cxcDetalle->CxC_Id), 'method' => 'GET');
        $action    = '';
		$action_label = '';

        return View::make('cxcDetalle/form', compact('cxcDetalle', 'cxc', 'referencia', 'ciudad', 'title', 'form_data', 'action', 'action_label'));
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

        $cxcDetalle = $this->cxcDetalleRepo->find($id);

        $this->notFoundUnless($cxcDetalle);

        $cxc = $this->cxcRepo->find($cxcDetalle->CxC_Id, 'view');

        $this->notFoundUnless($cxc);

        $referencia = $this->referenciaRepo->find($cxc->CxC_Tp_Ref . '-' . $cxc->CxC_Ref_Id);
        $ciudad = $this->ciudadRepo->find($cxc->Ciudad_Id, 'view');
		$title = $this->title;
        $form_data = array('route' => array('cxcDetalle.update', $cxcDetalle->CxC_Id . '-' . $cxcDetalle->CxC_Det_Id), 'method' => 'PATCH');
        $action    = '';
		if ( \Util::getAccess('CxC', 'Update')) $action = 'Actualizar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('cxcDetalle/form', compact('cxcDetalle', 'cxc', 'referencia', 'ciudad', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $cxcDetalle = $this->cxcDetalleRepo->find($id);

        $this->notFoundUnless($cxcDetalle);

        $manager = new CxCDetalleManager($cxcDetalle, Input::all());

        $manager->update();

       	return Redirect::route('cxcDetalle.list', $cxcDetalle->CxC_Id);
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $cxcDetalle = $this->cxcDetalleRepo->find($id);

        $this->notFoundUnless($cxcDetalle);

        \DB::table('cxc_detalle')
            ->where('CxC_Id', $cxcDetalle->CxC_Id)
            ->where('CxC_Det_Id', $cxcDetalle->CxC_Det_Id)
            ->delete();

       	return Redirect::route('cxcDetalle.list', $cxcDetalle->CxC_Id);
 	}


}
