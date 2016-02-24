<?php

use SincroCobranza\Entities\Documento;
use SincroCobranza\Managers\DocumentoManager;
use SincroCobranza\Repositories\TipoDocumentoRepo;
use SincroCobranza\Repositories\ProveedorRepo;
use SincroCobranza\Repositories\DocumentoRepo;

class DocumentosController extends \BaseController {
 
   	protected $tipoDocumentoRepo;
   	protected $proveedorRepo;
   	protected $documentoRepo;
	protected $title;

    public function __construct(TipoDocumentoRepo $tipoDocumentoRepo,
    							ProveedorRepo $proveedorRepo,
    							DocumentoRepo $documentoRepo)
    {
       	$this->tipoDocumentoRepo = $tipoDocumentoRepo;
       	$this->proveedorRepo = $proveedorRepo;
       	$this->documentoRepo = $documentoRepo;
		$this->title = \Lang::get('utils.Documentos');
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
 		if ( ! \Util::getAccess('Documentos')) return $this->accessFail();
		
		$documento = new Documento;
		$documento_filter = array();
		$filter = Input::all();
	   	$ids = explode('-', $id);
		$documento->Obj_Id = $ids[0];
		if( isset($ids[1] )) $documento->Doc_Oper_Id = $ids[1];
						
		if ($filter) {
			$filter = array_only($filter, $documento->getFillable());
			foreach($filter as $field => $value) {

				if ($value or $value == '0') {
					if (isset($fields)) {
						$fields .= ' AND ';
					} else {
						$fields = 'Obj_Id = ? AND ';
						$values = array($ids[0]);
						if( isset($ids[1] )){
							$fields .= 'Doc_Oper_Id = ? AND ';
							$values[] = $ids[1];
						}
					}
										
					$documento_filter[$field] = $value;
					switch ($field) {
						case 'Doc_Fch_Emision_LB':
							$fields .= 'Doc_Fch_Emision >= ?';
							$value = \DateTime::createFromFormat (\Auth::user()->User_Date_Format . ' H:i:s', $value . ' 00:00:00');
							break;
						case 'Doc_Fch_Emision_UB':
							$fields .= 'Doc_Fch_Emision <= ?';
							$value = \DateTime::createFromFormat (\Auth::user()->User_Date_Format . ' H:i:s', $value . ' 23:59:59');
							break;
						default:
							$fields .= $field . ' = ?';
					}						
					$values[] = $value;
				}
			}
		}

		$documento->fill($filter);

		if (isset($fields)) {
		   	$documentos_list = $this->documentoRepo->getFiltered($fields, $values); 
		} else {
		   	$documentos_list = $this->documentoRepo->getAll($id);
		}
		
       	$tiposDocumentos = $this->tipoDocumentoRepo->getList();
		$obj_nbr = \Util::getObjNbr($documento->Obj_Id);
		$title = $this->title;		
        $form_data = array('route' => array('documentos.list', $id), 'method' => 'POST');
		
		return View::make('documentos/list', compact('documento', 'documentos_list', 'documento_filter', 'tiposDocumentos', 'obj_nbr', 'title', 'form_data'));
	}
	
	public function expired()
	{
 		if ( ! \Util::getAccess('Documentos')) return $this->accessFail();
		
		$documento = new Documento;
		$documento_filter = array();
		$filter = Input::all();
						
		if ($filter) {
			$filter = array_only($filter, $documento->getFillable());
			foreach($filter as $field => $value) {

				if ($value or $value == '0') {
					if (isset($fields)) {
						$fields .= ' AND ';
					} else {
						$fields = '';
						$values = array();
					}
										
					$documento_filter[$field] = $value;
					switch ($field) {
						case 'Doc_Fch_Emision_LB':
							$fields .= 'Doc_Fch_Emision >= ?';
							$value = \DateTime::createFromFormat (\Auth::user()->User_Date_Format . ' H:i:s', $value . ' 00:00:00');
							break;
						case 'Doc_Fch_Emision_UB':
							$fields .= 'Doc_Fch_Emision <= ?';
							$value = \DateTime::createFromFormat (\Auth::user()->User_Date_Format . ' H:i:s', $value . ' 23:59:59');
							break;
						default:
							$fields .= $field . ' = ?';
					}						
					$values[] = $value;
				}
			}
		}

		$documento->fill($filter);

		if (isset($fields)) {
		   	$documentos_list = $this->documentoRepo->getExpiredFiltered($fields, $values); 
		} else {
		   	$documentos_list = $this->documentoRepo->getExpired();
		}
		
       	$tiposDocumentos = $this->tipoDocumentoRepo->getList();
       	$proveedores = $this->proveedorRepo->getList();
		$title = $this->title;		
        $form_data = array('route' => 'documentos.expired', 'method' => 'POST');
		
		return View::make('documentos/expired', compact('documento', 'documentos_list', 'documento_filter', 'tiposDocumentos', 'proveedores', 'title', 'form_data'));
	}
	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($id)
	{
 		if ( ! \Util::getAccess('Documentos', 'Insert')) return $this->accessFail();
		
		if (preg_match('/^[0-9]+-[0-9]+$/', $id)) {
	    	$ids = explode('-', $id);
	 		$documento = new Documento();
			$documento->Obj_Id = $ids[0];
			$documento->Doc_Oper_Id = $ids[1];
 	      	$tiposDocumentos = $this->tipoDocumentoRepo->getList();
			$obj_nbr = \Util::getObjNbr($documento->Obj_Id);
			$title = $this->title;		
	        $form_data = array('route' => 'documentos.store', 'method' => 'POST', 'files' => true);
	        $action    = 'Ingresar';        
			$action_label = \Lang::get('utils.Ingresar');
	
	        return View::make('documentos/form', compact('documento', 'tiposDocumentos', 'obj_nbr', 'title', 'form_data', 'action', 'action_label'));
        }
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @return Response
	 */
	public function store()
	{
        $documento = new Documento;
		
        $manager = new DocumentoManager($documento, Input::all());

        $manager->save();
		
		if (Input::hasFile('file_path')) {
			if ( ! $documento->Doc_File_Nbr) {
				$documento->Doc_File_Nbr = Input::file('file_path')->getClientOriginalName();
			    
			    \DB::table('documentos')
			        ->where('Emp_Id', \Auth::user()->Emp_Id)
		        	->where('Doc_Id', $documento->Doc_Id)
			        ->update(['Doc_File_Nbr' => Input::file('file_path')->getClientOriginalName()]);
			}
						
			Input::file('file_path')->move( $documento->Emp_Doc_Dir, $documento->Doc_File_Nbr);
		}

       	return Redirect::route('documentos.list', $documento->Obj_Id . '-' . $documento->Doc_Oper_Id);
	}


	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
 		if ( ! \Util::getAccess('Documentos')) return $this->accessFail();
		
        $documento = $this->documentoRepo->find($id);

        $this->notFoundUnless($documento);

		$content_types = [
		    'txt'	=> 'application/octet-stream',
		    'doc'	=> 'application/msword',
		    'docx'	=> 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
		    'xls'	=> 'application/vnd.ms-excel',
		    'xlsx'	=> 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
		    'pdf'	=> 'application/pdf',
		    'jpeg'	=> 'image/jpeg',
		    'jpg'	=> 'image/jpg',
		    'gif'	=> 'image/gif',
		    'png'	=> 'image/png',
		    'avi'	=> 'video/avi',
		    'mpeg'	=> 'video/mpeg',
		    'mp4'	=> 'video/mp4',
		    'dwg'	=> 'application/acad',
		    'dxf'	=> 'application/dxf',
//		    'dxf'	=> 'image/vnd.dxf,
		    'dwf'	=> 'application/x-dwf',
		];
		
		$file= $documento->Emp_Doc_Dir . $documento->Doc_File_Nbr;

		$extension = strtolower(pathinfo($documento->Doc_File_Nbr, PATHINFO_EXTENSION));

		if (File::isFile($file))
		{
			if (in_array($extension, array('txt', 'doc', 'docx', 'xls', 'xlsx', 'dwg', 'dxf', 'dwf'))) 
			{
				return Response::download($file, $documento->Doc_File_Nbr, ['Content-Type' => $content_types[ $extension ]]);
			} 
			else 
			{					
			    $file = File::get($file);
			    $response = Response::make($file, 200);
			    $response->header('Content-Type', $content_types[ $extension ]);
			
			    return $response;
		    }
		}

	}


	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
 		if ( ! \Util::getAccess('Documentos')) return $this->accessFail();
		
        $documento = $this->documentoRepo->find($id);

        $this->notFoundUnless($documento);

      	$tiposDocumentos = $this->tipoDocumentoRepo->getList();
		$obj_nbr = \Util::getObjNbr($documento->Obj_Id);
		$title = $this->title;		
        $form_data = array('route' => array('documentos.update', $id), 'method' => 'PATCH', 'files' => true);
        $action    = '';
		if ( \Util::getAccess('Documentos', 'Update')) $action = 'Actualizar';	
		if ( \Util::getAccess('Documentos', 'Delete')) $action = 'Eliminar';	
		$action_label = \Lang::get('utils.Actualizar');

        return View::make('documentos/form', compact('documento', 'tiposDocumentos', 'obj_nbr', 'title', 'form_data', 'action', 'action_label'));
	}


	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function update($id)
	{
        $documento = $this->documentoRepo->find($id);

        $this->notFoundUnless($documento);
		
        $manager = new DocumentoManager($documento, Input::all());

        $manager->update();
    
		if (Input::hasFile('file_path')) {
			$documento->Doc_File_Nbr = Input::file('file_path')->getClientOriginalName();
			
		    \DB::table('documentos')
		        ->where('Emp_Id', \Auth::user()->Emp_Id)
	        	->where('Doc_Id', $id)
		        ->update(['Doc_File_Nbr' => Input::file('file_path')->getClientOriginalName()]);
						
			Input::file('file_path')->move( $documento->Emp_Doc_Dir, $documento->Doc_File_Nbr);
		}

       	return Redirect::route('documentos.list', $documento->Obj_Id . '-' . $documento->Doc_Oper_Id);
	}


	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
        $documento = $this->documentoRepo->find($id);

        $this->notFoundUnless($documento);
		
	    \DB::table('documentos')
	        ->where('Emp_Id', $documento->Emp_Id)
	        ->where('Doc_Id', $documento->Doc_Id)
	        ->delete();

       	return Redirect::route('documentos.list', $documento->Obj_Id . '-' . $documento->Doc_Oper_Id);
 	}


}
