<?php

use SincroCobranza\Entities\CxCCancel;
use SincroCobranza\Managers\CxCCancelManager;
use SincroCobranza\Repositories\CxCCancelRepo;
use SincroCobranza\Repositories\CiudadRepo;
use SincroCobranza\Repositories\ZonaRepo;
use SincroCobranza\Repositories\SRegionRepo;
use SincroCobranza\Repositories\RegionRepo;
use SincroCobranza\Repositories\PaisRepo;
use SincroCobranza\Repositories\ReferenciaRepo;
use SincroCobranza\Repositories\EmpresaRepo;
use SincroCobranza\Repositories\CxCCancelDetalleRepo;
use SincroCobranza\Repositories\CxCCancelFacturaRepo;
use SincroCobranza\Repositories\CxCCancelRetencionRepo;

class CxCCancelController extends \BaseController {
 
    protected $cxcCancelRepo;
   	protected $ciudadRepo;
    protected $zonaRepo;
    protected $sregionRepo;
    protected $regionRepo;
    protected $paisRepo;
    protected $referenciaRepo;
    protected $empresaRepo;
    protected $cxcCancelDetalleRepo;
    protected $cxcCancelFacturaRepo;
    protected $cxcCancelRetencionRepo;
	protected $title;

    public function __construct(CiudadRepo $ciudadRepo, 
    							ZonaRepo $zonaRepo,
                                SRegionRepo $sregionRepo,
                                RegionRepo $regionRepo,
                                PaisRepo $paisRepo,
                                ReferenciaRepo $referenciaRepo,
                                EmpresaRepo $empresaRepo,
                                CxCCancelRepo $cxcCancelRepo,
                                CxCCancelDetalleRepo $cxcCancelDetalleRepo,
                                CxCCancelFacturaRepo $cxcCancelFacturaRepo,
                                CxCCancelRetencionRepo $cxcCancelRetencionRepo)
    {
       	$this->ciudadRepo = $ciudadRepo;
        $this->zonaRepo = $zonaRepo;
        $this->sregionRepo = $sregionRepo;
        $this->regionRepo = $regionRepo;
        $this->paisRepo = $paisRepo;
        $this->referenciaRepo = $referenciaRepo;
        $this->empresaRepo = $empresaRepo;
        $this->cxcCancelRepo = $cxcCancelRepo;
        $this->cxcCancelDetalleRepo = $cxcCancelDetalleRepo;
        $this->cxcCancelFacturaRepo = $cxcCancelFacturaRepo;
        $this->cxcCancelRetencionRepo = $cxcCancelRetencionRepo;
		$this->title = \Lang::get('utils.CxC');
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index()
    {
        if ( ! \Util::getAccess('CxC_Cancel')) return $this->accessFail();

        $cxcCancel = new CxCCancel;
        $cxcCancel_filter = array();
        $cxcCancel_orden = 1;
        $cxcCancel->CxC_Can_Orden = 1;

        $cxcCancel_list = $this->cxcCancelRepo->getAll($cxcCancel_orden);
        $cxcCancel_total = $this->cxcCancelRepo->getTotalAll();

        $ciudades = $this->ciudadRepo->getList();
        $zonas = $this->zonaRepo->getList();
        $sregiones = $this->sregionRepo->getList();
        $regiones = $this->regionRepo->getList();
        $paises = $this->paisRepo->getList();
        $empresas = $this->empresaRepo->getList();
        $obj_id = \Util::getObjId('CxC');
        $title = $this->title;
        $form_data = array('route' => array('cxcCancel.list', 0), 'method' => 'POST');

        return View::make('cxcCancel/list', compact('cxcCancel', 'cxcCancel_list', 'cxcCancel_filter', 'cxcCancel_total', 'cxcCancel_orden', 'ciudades', 'zonas', 'sregiones', 'regiones', 'paises', 'empresas', 'obj_id', 'title', 'form_data'));
    }

 	public function seek($id)
	{
		if ( ! \Util::getAccess('CxC_Cancel')) return $this->accessFail();
		
		$cxcCancel = new CxCCancel;
		$cxcCancel_filter = array();
		$filter = Input::all();
        $cxcCancel_orden = 1;
        $cxcCancel->CxC_Can_Orden = 1;

 		if ($filter) {
			$filter = array_only($filter, $cxcCancel->getFillable());
			foreach($filter as $field => $value) {
                if ($field == 'CxC_Can_Orden') {
                    $cxcCancel_orden = $value;
                    $cxcCancel_filter[$field] = $value;
                    $cxcCancel->CxC_Can_Orden = $value;
                }

                $valid_dates = true;
                if ($field == 'CxC_Can_Fch_Emision_LB' or $field == 'CxC_Can_Fch_Emision_UB') {
                    $valid_dates = \Util::setDate($value);
                }

				if ($valid_dates and ($value or $value == '0') and $field <> 'CxC_Can_Orden') {
					if (isset($fields)) {
						$fields .= ' AND ';
					} else {
						$fields = '';
						$values = array();
					}
										
					$cxcCancel_filter[$field] = $value;
					switch ($field) {
						case 'Ref_Nbr':
							$fields .= '(Ref_Nbr LIKE ? OR Ref_Codigo LIKE ?)';
							$value = '%' . $value . '%';
                            $values[] = $value;
							break;
                        case 'CxC_Can_Fch_Emision_LB':
                            $fields .= 'CxC_Can_Fch_Emision >= ?';
                            $value = \Util::setDate($value);
                            break;
                        case 'CxC_Can_Fch_Emision_UB':
                            $fields .= 'CxC_Can_Fch_Emision <= ?';
                            $value = \Util::setDate($value);
                            break;
                        case 'CxC_Can_Det_Doc':
                            $fields .= 'CxC_Can_Id IN (SELECT DISTINCT CxC_Can_Id FROM cxc_cancel_detalle WHERE CxC_Can_Det_Doc = ?)';
                            break;
                        case 'CxC_Doc_Nro':
                            $fields .= 'CxC_Can_Id IN (SELECT DISTINCT CxC_Can_Id FROM vw_cxc_cancel_facturas WHERE CxC_Doc_Nro = ?)';
                            break;
                        default:
							$fields .= $field . ' = ?';
					}		
                    
					$values[] = $value;
			    }
            }
        } else {
            if (preg_match('/^[0-9]-[0-9]+-[0-9]+$/', $id)) {
                $ids = explode('-', $id);
                switch ($ids[0]) {
                    case 1:
                        $fields = 'CxC_Can_Ref_Id = ?';
                        $referencia = $this->referenciaRepo->find('C-' . $ids[1]);
                        if ($referencia) {
                            $cxcCancel->Ref_Nbr = $referencia->Ref_Nbr;
                        }
                        break;
                    case 2:
                        $fields = 'Zon_Id = ?';
                        $cxcCancel->Zon_Id = $ids[1];
                        break;
                    case 3:
                        $fields = 'SReg_Id = ?';
                        $cxcCancel->SReg_Id = $ids[1];
                        break;
                    case 4:
                        $fields = 'Reg_Id = ?';
                        $cxcCancel->Reg_Id = $ids[1];
                        break;
                    case 5:
                        $fields = 'Pais_Id = ?';
                        $cxcCancel->Pais_Id = $ids[1];
                        break;
                }
                $values[] = $ids[1];

                $dates = $ids[2];
                if ($dates) {
                    $date = substr($dates, 1, 8);
                    if (substr($dates, 0, 1) == 1) {
                        $fields .= ' AND CxC_Can_Fch_Emision >= ?';
                        $cxcCancel->CxC_Can_Fch_Emision_LB = \Util::getDate($date);
                    } else {
                        $fields .= ' AND CxC_Can_Fch_Emision <= ?';
                        $cxcCancel->CxC_Can_Fch_Emision_UB = \Util::getDate($date);
                    }
                    $values[] = $date;

                    if (strlen($dates) > 9) {
                        $date = substr($dates, 10, 8);
                        $fields .= ' AND CxC_Can_Fch_Emision <= ?';
                        $cxcCancel->CxC_Can_Fch_Emision_UB = \Util::getDate($date);
                        $values[] = $date;
                    }
                }
            }
        }

            
		if (isset($fields)) {
			$cxcCancel->fill($filter);
		   	$cxcCancel_list = $this->cxcCancelRepo->getFiltered($fields, $values, $cxcCancel_orden);
            $cxcCancel_total = $this->cxcCancelRepo->getTotalFiltered($fields, $values);
		} else {
		   	$cxcCancel_list = $this->cxcCancelRepo->getAll($cxcCancel_orden);
            $cxcCancel_total = $this->cxcCancelRepo->getTotalAll();
		}

        $ciudades = $this->ciudadRepo->getList();
        $zonas = $this->zonaRepo->getList();
        $sregiones = $this->sregionRepo->getList();
        $regiones = $this->regionRepo->getList();
        $paises = $this->paisRepo->getList();
        $empresas = $this->empresaRepo->getList();
		$obj_id = \Util::getObjId('CxC_Cancel');
		$title = $this->title;		
        $form_data = array('route' => array('cxcCancel.list', $id), 'method' => 'POST');
		
		return View::make('cxcCancel/list', compact('cxcCancel', 'cxcCancel_list', 'cxcCancel_filter', 'cxcCancel_total', 'cxcCancel_orden', 'ciudades', 'zonas', 'sregiones', 'regiones', 'paises', 'empresas', 'obj_id', 'title', 'form_data'));
	}

	public function export()
	{
		if ( ! \Util::getAccess('CxC_Cancel_Export')) return $this->accessFail();
		
		return Excel::create('CxC_Cancel', function($excel) {
		
		    $excel->sheet('CxC_Cancel', function($sheet) {

                $cxcCancel = new CxCCancel;
                $cxcCancel_filter = array();
                $filter = Input::all();
                $cxcCancel_orden = 1;
                $cxcCancel->CxC_Can_Orden = 1;

                if ($filter) {
                    $filter = array_only($filter, $cxcCancel->getFillable());
                    foreach($filter as $field => $value) {
                        if ($field == 'CxC_Can_Orden') {
                            $cxcCancel_orden = $value;
                            $cxcCancel_filter[$field] = $value;
                            $cxcCancel->CxC_Can_Orden = $value;
                        }

                        $valid_dates = true;
                        if ($field == 'CxC_Can_Fch_Emision_LB' or $field == 'CxC_Can_Fch_Emision_UB') {
                            $valid_dates = \Util::setDate($value);
                        }

                        if ($valid_dates and ($value or $value == '0') and $field <> 'CxC_Can_Orden') {
                            if (isset($fields)) {
                                $fields .= ' AND ';
                            } else {
                                $fields = '';
                                $values = array();
                            }

                            $cxcCancel_filter[$field] = $value;
                            switch ($field) {
                                case 'Ref_Nbr':
                                    $fields .= '(Ref_Nbr LIKE ? OR Ref_Codigo LIKE ?)';
                                    $value = '%' . $value . '%';
                                    $values[] = $value;
                                    break;
                                case 'CxC_Can_Fch_Emision_LB':
                                    $fields .= 'CxC_Can_Fch_Emision >= ?';
                                    $value = \Util::setDate($value);
                                    break;
                                case 'CxC_Can_Fch_Emision_UB':
                                    $fields .= 'CxC_Can_Fch_Emision <= ?';
                                    $value = \Util::setDate($value);
                                    break;
                                case 'CxC_Can_Det_Doc':
                                    $fields .= 'CxC_Can_Id IN (SELECT DISTINCT CxC_Can_Id FROM cxc_cancel_detalle WHERE CxC_Can_Det_Doc = ?)';
                                    break;
                                case 'CxC_Doc_Nro':
                                    $fields .= 'CxC_Can_Id IN (SELECT DISTINCT CxC_Can_Id FROM vw_cxc_cancel_facturas WHERE CxC_Doc_Nro = ?)';
                                    break;
                                default:
                                    $fields .= $field . ' = ?';
                            }

                            $values[] = $value;
                        }
                    }
                }

                if (isset($fields)) {
                    $cxcCancel->fill($filter);
                    $cxcCancel_list = $this->cxcCancelRepo->getDataFiltered($fields, $values, $cxcCancel_orden);
                    $cxcCancel_total = $this->cxcCancelRepo->getTotalFiltered($fields, $values);
                } else {
                    $cxcCancel_list = $this->cxcCancelRepo->getData($cxcCancel_orden);
                    $cxcCancel_total = $this->cxcCancelRepo->getTotalAll();
                }

		        $sheet->loadView('cxcCancel/export', ['cxcCancel_list' => $cxcCancel_list, 'cxcCancel_total' => $cxcCancel_total]);
		
		    });
		
		})->export('xls');
	}


    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function show($id)
    {
        if ( ! \Util::getAccess('CxC_Cancel')) return $this->accessFail();

        $cxcCancel = $this->cxcCancelRepo->find($id, 'view');

        $this->notFoundUnless($cxcCancel);

        $cxcCancelDetalle_list = $this->cxcCancelDetalleRepo->getAll($id);
        $cxcCancelDetalle_total = $this->cxcCancelDetalleRepo->getTotalAll($id);
        $cxcCancelFacturas_list = $this->cxcCancelFacturaRepo->getAll($id);
        $cxcCancelFacturas_total = $this->cxcCancelFacturaRepo->getTotalAll($id);
        $cxcCancelRetenciones_list = $this->cxcCancelRetencionRepo->getAll($id);
        $cxcCancelRetenciones_total = $this->cxcCancelRetencionRepo->getTotalAll($id);
        $cxcCancel_filter = array('CxC_Can_Id' => $id);

        $obj_id = \Util::getObjId('CxC_Cancel');
        $title = $this->title;
        $form_data = array('route' => array('cxcCancel.list', '1-' . $cxcCancel->CxC_Can_Ref_Id . '-0'), 'method' => 'GET');
        $action    = '';
        $action_label = \Lang::get('utils.Actualizar');

        return View::make('cxcCancel/form', compact('cxcCancel', 'cxcCancelDetalle_list', 'cxcCancelDetalle_total', 'cxcCancelFacturas_list', 'cxcCancelFacturas_total', 'cxcCancelRetenciones_list', 'cxcCancelRetenciones_total', 'cxcCancel_filter', 'obj_id', 'title', 'form_data', 'action', 'action_label'));
    }


    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return Response
     */
    public function edit($id)
    {
        if ( ! \Util::getAccess('CxC_Cancel')) return $this->accessFail();

        $cxcCancel = $this->cxcCancelRepo->find($id, 'view');

        $this->notFoundUnless($cxcCancel);

        $cxcCancelDetalle_list = $this->cxcCancelDetalleRepo->getAll($id);
        $cxcCancelDetalle_total = $this->cxcCancelDetalleRepo->getTotalAll($id);
        $cxcCancelFacturas_list = $this->cxcCancelFacturaRepo->getAll($id);
        $cxcCancelFacturas_total = $this->cxcCancelFacturaRepo->getTotalAll($id);
        $cxcCancelRetenciones_list = $this->cxcCancelRetencionRepo->getAll($id);
        $cxcCancelRetenciones_total = $this->cxcCancelRetencionRepo->getTotalAll($id);
        $cxcCancel_filter = array('CxC_Can_Id' => $id);

        $obj_id = \Util::getObjId('CxC_Cancel');
        $title = $this->title;
        $form_data = array('route' => array('cxcCancel.update', $cxcCancel->CxC_Can_Id), 'method' => 'PATCH');
        $action    = '';
        if ( \Util::getAccess('CxC_Cancel', 'Update')) $action = 'Actualizar';
        $action_label = \Lang::get('utils.Actualizar');

        return View::make('cxcCancel/form', compact('cxcCancel', 'cxcCancelDetalle_list', 'cxcCancelDetalle_total', 'cxcCancelFacturas_list', 'cxcCancelFacturas_total', 'cxcCancelRetenciones_list', 'cxcCancelRetenciones_total', 'cxcCancel_filter', 'obj_id', 'title', 'form_data', 'action', 'action_label'));
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  int  $id
     * @return Response
     */
    public function update($id)
    {
        $cxcCancel = $this->cxcCancelRepo->find($id);

        $this->notFoundUnless($cxcCancel);

        $manager = new CxCCancelManager($cxcCancel, Input::all());

        $manager->save();

        return Redirect::route('cxcCancel.list', '1-' . $cxcCancel->CxC_Can_Ref_Id . '-0');
    }

}
