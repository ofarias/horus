<?php
session_start();
date_default_timezone_set('America/Mexico_City');
//session_cache_limiter('private_no_expire');
require_once('app/controller/pegaso.controller.php');
require_once('app/controller/pegaso.controller.cobranza.php');
require_once('app/controller/pegaso.controller.ventas.php');
$controller = new pegaso_controller;
$controller_cxc = new pegaso_controller_cobranza;
$controller_v= new pegaso_controller_ventas;

//echo $_POST['nombre'];
//echo $_POST['actualizausr'];

if(isset($_GET['action'])){
$action = $_GET['action'];
}else{
	$action = '';
}
if (isset($_POST['usuario'])){
}elseif (isset($_POST['crearPedido'])) {
	echo 'Entro al index de Ventas';
	//break;
}elseif(isset ($_POST['buscarArticulo'])){
        $articulo = $_POST['articulo'];
        $descripcion = $_POST['descripcion'];
        $cliente = $_POST['clave'];
        $folio = $_POST['folio'];
        $partida = $_POST['partida'];
        $controller_v->consultarArticulo($cliente, $folio, $partida, $articulo, $descripcion);
} elseif(isset($_POST['actualizaCotizacionPartida'])){
        $folio = $_POST['cotizacion'];
        $partida = $_POST['partida'];
        $articulo = $_POST['articulo'];
        $precio = $_POST['precio'];
        $descuento = $_POST['descuento'];
        $cantidad = $_POST['cantidad']; 
        $ida =$_POST['ida'];       
        $controller_v->actualizaCotizacion($folio, $partida, $articulo, $precio, $descuento, $cantidad, $ida);
} elseif(isset($_POST['buscarCliente'])){
        $clave = '';
        $cliente = '';
        if(isset($_POST['clave'])){
            $clave = $_POST['clave'];
        }
        if(isset($_POST['cliente'])){
            $cliente = $_POST['cliente'];
        }  
        $controller_v->consultarClientes($clave, $cliente);
} elseif(isset($_POST['seleccionaCliente'])){
        $cliente = $_POST['clave'];

      //  echo 'cliente: '.$cliente;
      //  echo 'Mover cotizcion: '.$_SESSION['cotizacion_mover_cliente'];
      //  echo 'identificadorDocumento: '.$_SESSION['identificadorDocumento'];
      //  break;
        if(isset($_SESSION['cotizacion_mover_cliente']) && $_SESSION['cotizacion_mover_cliente']==true){
            $folio = $_SESSION["cotizacion_folio"];
            $_SESSION['cotizacion_mover_cliente'] = false;
            $_SESSION["cotizacion_folio"] = '';
            $controller_v->moverClienteCotizacion($folio, $cliente);
        }else {
            if(isset($_POST['identificadorDocumento'])){
                $identificadorDocumento = $_POST['identificadorDocumento'];
            }
            $controller_v->insertaCotizacion($cliente, $identificadorDocumento);
        }
} elseif(isset($_POST['generaNuevaCotizacion'])){
        $controller_v->consultarClientes('', '');
} elseif(isset($_POST['actualizaPedido'])){
        $folio = $_POST['folio'];
        $pedido = $_POST['pedido'];
        $controller_v->actualizaPedidoCotizacion($folio, $pedido);
}elseif (isset($_POST['altaProdVentas'])) {
	$descripcion=$_POST['descripcion'];
	$cotizacion =$_POST['cotizacion'];
	$cliente =$_POST['cliente'];
	$controller_v->altaProdVentas($descripcion, $cotizacion, $cliente);
}elseif(isset($_GET['term']) && isset($_GET['proveedor'])){
		$buscar = $_GET['term'];
		$nombres = $controller->TraeProveedores($buscar);
		echo json_encode($nombres);
		exit;
}elseif(isset($_GET['term']) && isset($_GET['producto'])){
		$buscar = $_GET['term'];
		$nombres = $controller->TraeProductos($buscar);
		echo json_encode($nombres);
		exit;
}elseif(isset($_GET['term']) && isset($_GET['descAuto'])){
		$buscar = $_GET['term'];
		$nombres = $controller_v->TraeProductosFTC($buscar);
		echo json_encode($nombres);
		exit;
}elseif(isset($_GET['term']) && isset($_GET['cliente'])){
		$buscar = $_GET['term'];
		$nombres=$controller_v->TraeClientes($buscar);
		echo json_encode($nombres);
		exit();
}elseif (isset($_POST['solicitarAlta'])) {
		$categoria=$_POST['categoria'];	
		//$linea=$_POST['linea'];
		$descripcion =$_POST['descripcion'];
		$marca=$_POST['marca'];
		/*
		$generico=$_POST['generico'];
		$sinonimos=$_POST['sinonimos'];
		$calificativo=$_POST['calificativo'];
		$medidas=$_POST['medidas'];
		$prov1=$_POST['prov1'];
		$codigo_prov1=$_POST['codigo_prov1'];
		$sku=$_POST['sku'];
		$costo_prov1=$_POST['costo_prov1'];
		$iva=$_POST['iva'];
		$desc1=$_POST['desc1'];
		$desc2=$_POST['desc2'];
		$desc3=$_POST['desc3'];
		$desc4=$_POST['desc4'];
		$desc5=$_POST['desc5'];
		$impuesto=$_POST['impuesto'];
		$costo_total=$_POST['costo_total'];
		*/
		$unidadmedida=$_POST['unidadmedida'];
		$empaque=$_POST['empaque'];
		$cantsol=$_POST['cantSol'];
		$cotizacion =$_POST['cotizacion'];
		$cliente=$_POST['cliente'];
		//$controller_v->solicitarAlta($categoria, $linea, $descripcion, $marca, $generico, $sinonimos, $calificativo, $medidas, $unidadmedida, $empaque, $prov1, $codigo_prov1, $sku, $costo_prov1, $iva, $desc1, $desc2, $desc3, $desc4, $desc5, $impuesto, $costo_total, $cotizacion, $cliente);
		$controller_v->solicitarAlta($categoria, $descripcion, $marca, $cotizacion, $cliente, $unidadmedida,$empaque, $cantsol);
}elseif (isset($_POST['altaCategoria'])) {
	$nombreCategoria=$_POST['nombreCategoria'];
	$abreviatura=$_POST['abreviatura'];
	$responsable=$_POST['responsable'];
	$status=$_POST['status'];
	$controller_v->altaCategoria($nombreCategoria, $abreviatura, $responsable, $status);
}elseif (isset($_POST['editaCategoria'])) {
	$idcat=$_POST['idcat'];
	$controller_v->editaCategoria($idcat);
}elseif (isset($_POST['editarCategoria'])) {
	$idcat=$_POST['idcat'];
	$nombreCategoria=$_POST['nombreCategoria'];
	$abreviatura=$_POST['abreviatura'];
	$responsable=$_POST['responsable'];
	$status=$_POST['status'];
	$controller_v->editarCategoria($nombreCategoria, $abreviatura, $responsable, $status, $idcat);
}elseif (isset($_POST['altaMarca'])) {
	$cm=$_POST['nombreMarca'];
	$nc = $_POST['nombreComercial'];
	$rz =$_POST['razonSocial'];
	$dir =$_POST['direccion'];
	$tel = $_POST['telefono'];
	$cont = $_POST['contacto'];
	$s =$_POST['status'];
	$p = $_POST['periodo'];
	$d = $_POST['dia'];
	$controller_v->altaMarca($cm, $nc, $rz, $dir, $tel, $cont, $s, $p, $d);
}elseif (isset($_POST['editaMarca'])) {
	$idm = $_POST['idm'];
	$controller_v->editaMarca($idm);
}elseif (isset($_POST['editarMarca'])) {
	$idm =$_POST['idm'];
	$cm=$_POST['nombreMarca'];
	$nc = $_POST['nombreComercial'];
	$rz =$_POST['razonSocial'];
	$dir =$_POST['direccion'];
	$tel = $_POST['telefono'];
	$cont = $_POST['contacto'];
	$s =$_POST['status'];
	$p = $_POST['periodo'];
	$d = $_POST['dia'];
	$controller_v->editarMarca($idm, $cm, $nc, $rz, $dir, $tel, $cont, $s, $p, $d);
}elseif (isset($_POST['creaProductoFTC'])) {
		$categoria=$_POST['categoria'];	
		$linea=$_POST['linea'];
		$descripcion =$_POST['descripcion'];
		$marca=$_POST['marca'];		
		$generico=$_POST['generico'];
		$sinonimos=$_POST['sinonimos'];
		$calificativo=$_POST['calificativo'];
		$medidas=$_POST['medidas'];
		$unidadmedida=$_POST['unidadmedida'];
		$empaque=$_POST['empaque'];
		$prov1=$_POST['prov1'];
		$codigo_prov1=$_POST['codigo_prov1'];
		$sku=$_POST['sku'];
		$costo_prov1=$_POST['costo_prov1'];
		if(isset($_POST['iva'])){
			$iva='Si';	
		}else{
			$iva='No';
		}
		//echo 'descuento antes de la validacion: '.$_POST['desc1'].'<p>'; 
		//echo 'descuento antes de la validacion: '.$_POST['desc2'].'<p>';
		if(empty($_POST['desc1'])){
			$desc1= 0;
		}else {
			$desc1=$_POST['desc1'];
		}
		if(empty($_POST['desc2'])){
			$desc2= 0;
		}else {
			$desc2=$_POST['desc2'];
		}
		if(empty($_POST['desc3'])){
			$desc3= 0;
		}else {
			$desc3=$_POST['desc3'];
		}
		if(empty($_POST['desc4'])){
			$desc4= 0;
		}else {
			$desc4=$_POST['desc4'];
		}
		if(empty($_POST['desc5'])){
			$desc5= 0;
		}else {
			$desc5=$_POST['desc5'];
		}
		if(empty($_POST['costo_total'])){
			$costo_total=0;
		}else{
			$costo_total=$_POST['costo_total'];	
		}
		if(empty($_POST['costo_t'])){
			$costo_t =0; 
		}else{
			$costo_t =$_POST['costo_t']; 
		}
		if(empty($_POST['costo_oc'])){
			$costo_oc =0; 
		}else{
			$costo_oc =$_POST['costo_oc']; 
		}
		if(empty($_POST['impuesto'])){
			$impuesto= 0;
		}else {
			$impuesto=number_format($_POST['impuesto'],2,".","");
		}
		$clave = $_POST['clave'];

		$iva_v = empty($_POST['iva_v'])? '0':$_POST['iva_v'];
		$ieps_v = empty($_POST['ieps_v'])? '0':$_POST['ieps_v'];
		$precio_v = empty($_POST['precio_v'])? '0':$_POST['precio_v'];

		//echo 'desc-.'.$desc1.'<p>';
		//echo 'desc2-.'.$desc2.'<p>';
		//echo $desc3.'<p>';
		//echo $desc4.'<p>';
		//echo $desc5.'<p>';
		//echo $costo_total.'<p>';
		//break;
		//$cotizacion =$_POST['cotizacion'];
		//$cliente=$_POST['cliente'];
		$controller->creaProductoFTC($categoria, $linea, $descripcion, $marca, $generico, $sinonimos, $calificativo, $medidas, $unidadmedida, $empaque, $prov1, $codigo_prov1, $sku, $costo_prov1, $iva, $desc1, $desc2, $desc3, $desc4, $desc5, $impuesto, $costo_total, $clave, $costo_t, $costo_oc, $iva_v, $ieps_v, $precio_v);
}elseif (isset($_POST['verMarcasxCategoria'])) {
	$idcat = $_POST['idcat'];
	if(isset($_POST['marca'])){
		$marca = $_POST['marca'];
	}else{
		$marca = false;
	}
	$controller_v->verMarcasxCategoria($idcat, $marca);
}elseif (isset($_POST['asignarMarca'])){
	$idcat = $_POST['idcat'];
	$idmca = $_POST['idmca'];
	$controller_v->asignarMarca($idcat, $idmca);
}elseif (isset($_POST['desasignarMarca'])) {
	$idcat = $_POST['idcat'];
	$idmca = $_POST['idmca'];
	$controller_v->desasignarMarca($idcat, $idmca);
}elseif (isset($_POST['cltprovXprod'])) {
	$ids = $_POST['ids'];
	$controller_v->cltprovXprod($ids);
}elseif (isset($_POST['cltXprod'])){
	$ids = $_POST['ids'];
	$controller_v->cltXprod($ids);
}
elseif(isset($_POST['buscaClienteProveedor'])){
	$ids = $_POST['ids'];
	$aguja = $_POST['aguja'];
	$controller_v->buscaClienteProveedor($ids, $aguja);
}elseif (isset($_POST['buscaCliente'])) {
	$ids=$_POST['ids'];
	$aguja=$_POST['aguja'];
	$controller_v->buscaCliente($ids, $aguja);
}elseif (isset($_POST['pagaNV'])) {
	$res=$controller_v->pagaNV($_POST['tcc'],$_POST['tcd'],$_POST['efe'],$_POST['tef'],$_POST['val'],$_POST['cupon'],$_POST['cr'],$_POST['doc'], $_POST['cambio']);
	echo json_encode($res);
	exit();
}
elseif (isset($_POST['proveedorXproducto'])) {
	$ids = $_POST['ids'];
	$idprov = $_POST['idprov'];
	
	echo 'valor de pxp antes de validacion'.$_POST['pxp'];
	//break;
	if(isset($_POST['pieza'])){
		$pieza=$_POST['pieza'];
	}else{
		$pieza='No';
	}
	if(isset($_POST['empaque'])){
		$empaque =$_POST['empaque'];
	}else{
		$empaque = 'No';
	}
	if(!empty($_POST['pxp'])){
		$pxp =$_POST['pxp'];
	}else{
		$pxp=0;
	}
	if (isset($_POST['empaque2'])) {
		$empaque2=$_POST['empaque2'];
	}else{
		$empaque2='No';
	}
	if (!empty($_POST['pxp2'])) {
		$pxp2 = $_POST['pxp2'];
	}else{
		$pxp2 = 0;
	}
	if(isset($_POST['urgencia'])){
		$urgencia=$_POST['urgencia'];
	}else{
		$urgencia='No';
	}
	if (isset($_POST['entrega'])){
		$entrega = $_POST['entrega'];
	}else{
		$entrega = 'No';
	}
	if(isset($_POST['recoge'])){
		$recoleccion =$_POST['recoge'];
	}else{
		$recoleccion = 'No';
	}
	if (isset($_POST['efectivo'])) {
		$efectivo = $_POST['efectivo']; 
	}else{
		$efectivo ='No';
	}
	if (isset($_POST['cheque'])){
		$cheque = $_POST['cheque'];
	}else{
		$cheque = 'No';
	}
	if(isset($_POST['credito'])){
		$credito = $_POST['credito'];
	}else{
		$credito = 'No';
	}
	$costo =$_POST['costo'];
	$costo2 = $_POST['costo2'];
	//echo 'PXP despues de la validacion: '.$pxp;
	//break;
	$controller_v->proveedorXproducto($ids, $idprov, $pieza, $empaque, $pxp, $empaque2, $pxp2, $urgencia, $entrega, $recoleccion, $efectivo, $cheque, $credito, $costo, $costo2);
}elseif(isset($_POST['clienteXproducto'])){
	$idclie = $_POST['idclie'];
	$ids = $_POST['ids'];
	$sku = $_POST['sku'];

	if(isset($_POST['skuFact'])){
		$skuFact=$_POST['skuFact'];
	}else{
		$skuFact = 'No';
	}
	if(isset($_POST['listaCliente'])){
		$listaCliente =$_POST['listaCliente'];
	}else{
		$listaCliente = 'No';
	}
	if(empty($_POST['correo'])){
		$correo='No';
	}else{
		$correo=$_POST['correo'];
	}
	if(empty($_POST['precio'])){
		$precio = 0;
	}else{
		$precio = $_POST['precio'];
	}
	//echo 'Precio:'.$precio;
	//break;
	$controller_v->clienteXproducto($idclie, $ids, $sku, $skuFact, $listaCliente, $correo, $precio);
}elseif (isset($_POST['parCotSMB'])){
	$folio=$_POST['folio'];
	$partida = $_POST['partida'];
	$por2 = $_POST['por2'];
	$controller_v->parCotSMB($folio, $partida, $por2);
}elseif (isset($_POST['autMB'])) {
	$folio =$_POST['folio'];
	$partida =$_POST['partida'];
	$utilAuto=$_POST['utilAuto'];
	$precio =$_POST['precio'];
	$controller_v->autMB($folio, $partida, $utilAuto, $precio);
}elseif(isset($_POST['guardaSKU'])){
	$producto = $_POST['producto'];
	$sku = $_POST['sku'];
	$cliente = $_POST['cliente'];
	$cdfolio =$_POST['cdfolio'];
	$cotizacion = $_POST['cotizacion'];
	$nombre = $_POST['nombre'];
	$descripcion = $_POST['descripcion'];
	$sku_cliente = $_POST['sku_cliente'];
	$sku_otro = $_POST['sku_otro'];
	$controller_v->guardaSKU($producto, $sku, $cliente, $cdfolio, $nombre, $descripcion, $cotizacion, $sku_cliente, $sku_otro);
}elseif(isset($_POST['copiarCotizacion'])){
	$cotizacion = $_POST['copiarCotizacion'];
	$response=$controller_v->copiarCotizacion($cotizacion);
	echo json_encode($response);
	exit();
}elseif (isset($_POST['copiar'])){
	$cotizacion = $_POST['copiar'];
	$response = $controller_v->copiar($cotizacion);
	echo json_encode($response);
	exit();
}elseif (isset($_POST['guardaPartida'])){
	$producto = $_POST['guardaPartida'];
	$cotizacion = $_POST['folio'];
	$tipo = $_POST['tipo'];
	$precio  = $_POST['precio'];
	$cantidad =$_POST['cantidad'];
	$descuento = $_POST['descuento'];
	$mm = $_POST['mm'];
	$mb = $_POST['mb'];
	$costo = $_POST['costo'];
	$response=$controller_v->guardaPartida($producto, $cotizacion, $tipo, $cantidad, $precio, $descuento, $mb, $mm, $costo );
	echo json_encode($response);
	exit();
}elseif (isset($_POST['enviarSolicitudMB'])) {
	$folio = $_POST['folio'];
	$partida = $_POST['partida'];
	$precioreq = $_POST['precion'];
	$cantidad = $_POST['cantidad'];
	$por2 = $_POST['porcentaje'];
	$controller_v->parCotSMB($folio, $partida, $por2);
}elseif(isset($_POST['recalcular'])){
	$idpreoc = $_POST['recalcular'];
	$tipo = $_POST['tipo'];
	$response= $controller_v->recalcular($idpreoc, $tipo);
	echo json_encode($response);
	exit();
}elseif (isset($_POST['cancelar'])) {
	$res=$controller_v->cancelar($_POST['docf'], $_POST['cancelar'], $_POST['mot'], $_POST['uuidSust']);echo json_encode($res);exit();
}elseif(isset($_POST['traePendientes'])){
	$prod = $_POST['traePendientes'];
	$response=$controller_v->traePendientes($prod);
	echo json_encode($response);
	exit();
}elseif(isset($_POST['buscaCaja'])){
	$docf = $_POST['buscaCaja'];
	$controller_v->buscaCaja($docf);
}elseif(isset($_POST['actPartida'])){
	$docf = $_POST['actPartida'];
	$cantidad=$_POST['ncantidad'];
	$precio=$_POST['nprecio'];
	$descuento=$_POST['ndescuento'];
	$partida=$_POST['par'];
	$uso = $_POST['uso'];
	$mp= $_POST['mp'];
	$fp= $_POST['fp'];
	$clie= $_POST['clie'];
	$response=$controller_v->actPartida($docf, $cantidad, $precio, $descuento, $partida, $uso, $mp, $fp, $clie);
	echo json_encode($response);
	exit();
}elseif(isset($_POST['realizaCEP'])){
	$folios = $_POST['fol'];
	$controller_v->realizaCEP($folios);
	exit();
}elseif (isset($_POST['realizaNCBonificacion'])) {
	$docf=$_POST['docf'];
	$monto=$_POST['monto'];
	$concepto=$_POST['concepto'];
	$obs=$_POST['obs'];
	$caja=$_POST['caja'];
	$controller_v->realizaNCBonificacion($docf, $monto, $concepto, $obs, $caja);
}elseif (isset($_POST['copiaFP'])) {
	$docf=$_POST['copiaFP'];
	$response=$controller_v->copiaFP($docf);
	echo json_encode($response);
	exit();
}elseif(isset($_POST['conteoCopias'])){
	$docf=$_POST['conteoCopias'];
	$res=$controller_v->conteoCopias($docf);
	echo json_encode($res);
	exit();
}elseif(isset($_POST['cajaNC'])){
	$idc = $_POST['cajaNC'];
	$res=$controller_v->cajaNC($idc);
	echo json_encode($res);
	exit();
}elseif(isset($_POST['verNCC'])){
	$serie=$_POST['serie'];
	$controller_v->verNCC($serie);
	exit();
}elseif(isset($_POST['aplicaNC'])){
	$docn=$_POST['aplicaNC'];
	$res=$controller_v->aplicaNC($docn);
	echo json_encode($res);
	exit();
}elseif (isset($_POST['anexoDescr'])) {
	$tipo=$_POST['anexoDescr'];
	$idc=$_POST['idc'];
	$par=$_POST['par'];
	$descr=$_POST['descr'];
	$res=$controller_v->anexoDescr($tipo, $idc, $par, $descr);
	echo json_encode($res);
	exit();
}elseif (isset($_POST['cargaSae'])) {
	$res=$controller_v->cargaSae($_POST['doc'], $_POST['folio'], $_POST['serie'], $_POST['uuid'], $_POST['ruta'], $_POST['rfcr'], $_POST['tipo']);
	echo json_encode($res);
	exit();
}elseif(isset($_POST['repVenta'])){
	$res=$controller_v->repVenta($_POST['op1'], $_POST['op2'],$_POST['op3'],$_POST['op4'],$_POST['op5'],$_POST['op6'],$_POST['op7']);
	echo json_encode($res);
	exit();
}elseif(isset($_GET['term']) && isset($_GET['clieVM'])){
    $b = $_GET['term'];
    $cliente=$controller_v->clieVM($b);
    echo json_encode($cliente);
    exit;
}elseif(isset($_GET['term']) && isset($_GET['prodVM'])){
    $b = $_GET['term'];
    $producto=$controller_v->prodVM($b);
    echo json_encode($producto);
    exit;
}elseif (isset($_POST['docNV'])) {
	$partida=$controller_v->docNV($_POST['clie'], $_POST['prod'], $_POST['cant'], $_POST['prec'], $_POST['desc'], $_POST['iva'], $_POST['ieps'], $_POST['descf'], $_POST['doc'], $_POST['idf'], $_POST['add'], $_POST['nvm'], $_POST['obs']);
	echo json_encode($partida);
	exit();
}elseif (isset($_POST['dropP'])) {
	$par=$controller_v->dropP($_POST['doc'], $_POST['idf'], $_POST['p']);
	echo json_encode($par);
	exit();
}elseif (isset($_POST['impresionTicket'])){
	$res=$controller_v->impresionTicket($_POST['impresionTicket'], $cambio=0); echo json_encode($res); exit();
}elseif (isset($_POST['cancelaNV'])){
	$res=$controller_v->cancelaNV($_POST['cancelaNV']);
	echo json_encode($res);
	exit();
}elseif(isset($_POST['cambioCliente'])){
	$res=$controller_v->cambioCliente($_POST['cambioCliente'], $_POST['doc']);
	echo json_encode($res);
	exit();
}elseif (isset($_POST['chgTipo'])){
	$res=$controller_v->chgTipo($_POST['tipo'], $_POST['id'], $_POST['nt']);
	echo json_encode($res);
	exit();
}elseif (isset($_POST['cambiaObs'])){
	$res=$controller_v->cambiaObs($_POST['lin'], $_POST['doc'], $_POST['obs']);
	echo json_encode($res);
	exit();
}elseif (isset($_POST['factNV'])) {
	$res = $controller_v->factNV($_POST['doc'] , $_POST['mp'], $_POST['fp'], $_POST['uf']);
	echo json_encode($res);
	exit();
}elseif (isset($_POST['copiaNV'])) {
	$res = $controller_v->copiaNV($_POST['doc']);
	echo json_encode($res);
	exit();
}elseif (isset($_POST['chgEmail'])) {
	$res = $controller_v->chgEmail($_POST['cl'], $_POST['correo']);
	echo json_encode($res);
	exit();
}elseif (isset($_POST['nvcl'])) {
	$res = $controller_v->nvcl($_POST['nvcl']);
	echo json_encode($res);
	exit();
}elseif(isset($_POST['cargaProd'])){
	$res = $controller_v->cargaProd($_POST['files2upload']); echo json_encode($res);exit();
}elseif(isset($_POST['cargaImg'])){
	$res = $controller_v->cargaImg($_POST['files2upload']); echo json_encode($res);exit();
}elseif(isset($_POST['sisbn'])){
	$res = $controller_v->sisbn($_POST['sisbn']); echo json_encode($res);exit();
}elseif(isset($_POST['impNV'])){
	$res = $controller_v->impNV($_POST['impNV'], $d='f'); echo json_encode($res);exit();
}elseif(isset($_POST['factPar'])){
	$res = $controller_v->factPar($_POST['factPar'], $_POST['datos'], $_POST['uf'], $_POST['mp'], $_POST['fp']); echo json_encode($res);exit();
}elseif(isset($_POST['sincwoo'])){
	$res = $controller_v->sincwoo($_POST['sincwoo']); echo json_encode($res);exit();
}elseif(isset($_POST['pubWoo'])){
	$res = $controller_v->pubWoo($_POST['pubWoo'], $_POST['t']); echo json_encode($res);exit();
}elseif(isset($_POST['chgPart'])){
	$res = $controller_v->chgPart($_POST['chgPart'], $_POST['part'], $_POST['campo'], $_POST['val']); echo json_encode($res);exit();
}elseif(isset($_POST['infoCte'])){
	$res = $controller_v->infoCte($_POST['infoCte']); echo json_encode($res); exit();
}elseif(isset($_POST['editCte'])){
	$res = $controller_v->editCte($_POST['editCte'], $_POST['campo'], $_POST['val']);echo json_encode($res); exit();
}elseif(isset($_POST['productoVM'])){
	$res = $controller_v->productoVM($_POST['val']);echo json_encode($res); exit();
}elseif(isset($_POST['genCepNV'])){
	$res = $controller_v->genCepNV($_POST['doc'], $_POST['bancoO'], $_POST['cuentaO'], $_POST['bancoD'], $_POST['cuentaD'], $_POST['fecha'], $_POST['monto'], $_POST['tipo']);echo json_encode($res); exit();
}elseif (isset($_POST['actObsNvm'])) {
	$res = $controller_v->actObsNvm($_POST['obs'], $_POST['nvm'], $_POST['doc']);echo json_encode($res); exit();
}elseif (isset($_POST['catalogo'])) {
	$res = $controller_v->catalogo();echo json_encode($res); exit();
}elseif (isset($_POST['factG'])){
	$res= $controller_v->factG($_POST['factG'], $_POST['tipo']); echo json_encode($res); exit();
}elseif (isset($_POST['ccat'])){
	$res= $controller_v->ccat($_POST['ccat'], $_POST['id']); echo json_encode($res); exit();
}elseif(isset($_POST['cancelAdmin'])){
	$res= $controller_v->cancelAdmin($_POST['cancelAdmin']); echo json_encode($res);exit();
}
else{switch ($_GET['action']){
		case 'login':
		$controller->Login();
		break;
		   	case 'crearPedido':
	       		$controller_v->crearPedido();
	       		break;
	       	case 'consultarCotizacion':
            $cerradas =false;
            if(isset($_GET['cerradas'])){
                $cerradas = true;
            }
            $controller_v->consultarCotizaciones($cerradas);
            break;
		    case 'consultaArticulo':
		            $cliente = $_GET['clave'];
		            $articulo = "";
		            $descripcion = "";
		            $folio = 0;
		            $partida = "";
		            if(isset($_GET['articulo'])){
		                $articulo = $_GET['articulo'];
		            } 
		            if(isset($_GET['descripcion'])){
		                $descripcion = $_GET['descripcion'];
		            }
		            if(isset($_GET['folio'])){
		                $folio = $_GET['folio'];
		            }
		            if(isset($_GET['partida'])){
		                $partida = $_GET['partida'];
		            }
		            $controller_v->consultarArticulo($cliente, $folio, $partida, $articulo, $descripcion);
		            break;
		    case 'verDetalleCotizacion':
		    	$folio = $_GET['folio'];
		    	$controller_v->verDetalleCotizacion($folio);
		    	break;
		    case 'quitarPartida':
		        $folio = $_GET['folio'];
		        $partida = $_GET['partida'];
		        $controller_v->quitarPartida($folio, $partida);
		        break;
		    case 'cancelarCotizacion':
		        $folio = $_GET['folio'];
		        $controller_v->cancelaCotizacion($folio);                
		        break;
		    case 'cambiaCliente':
		            $folio = $_GET['folio'];            
		            $_SESSION['cotizacion_mover_cliente'] = true;
		            $_SESSION['cotizacion_folio'] = $folio;
		            $controller_v->consultarClientes('', '');
		            break;
		    case 'avanzaCotizacion':
		            $folio = $_GET['folio'];
		            $controller_v->avanzaCotizacion($folio);
		            break;
		    case 'verDetalleCotizacion':
		    	$folio = $_GET['folio'];
		    	$controller_v->verDetalleCotizacion($folio);
		    	break;
		    case 'recepciones':
		    	$controller_v->verRecepciones();
		    	break;
			case 'cmaestra':
		    	$controller_v->ComprasMaestro();
		    	break;
			case 'capturaproductos':
		    	$controller_v->CapturaProductos();
		    	break;
		    case 'crearCategoria':
		    	$controller_v->crearCategoria();
		    	break;
		    case 'verMarcas':
		    	$controller_v->verMarcas();
		    	break;
		    case 'crearMarca':
		    	$controller_v->crearMarca();
		    	break;
		    case 'verMarcasxCategoria':
		    	$idcat = $_GET['idcat'];
		    	$marca = $_GET['marca'];
		    	//echo 'valor devuelto por idcat: '.$idcat;
		    	$controller_v->verMarcasxCategoria($idcat, $marca);
		    	break;
		    case 'cltprovXprod':
		    	$ids =$_GET['ids'];
		    	$controller_v->cltprovXprod($ids);
		    	break;
		    case 'verFTCArticulosVentas':
		    	$controller_v->verFTCArticulosVentas();
		    	break;
		    case 'cltXprod':
		    	$ids = $_GET['ids'];
		    	$controller_v->cltXprod($ids);
		    	break;
		    case 'consultarCotizaciones':
		    	$controller_v->consultarCotizaciones();
		    	break;
		    case 'verSMB':
		    	$controller_v->verSMB();
		    	break;
		    case 'marcarUrgente':
		    	$folio = $_GET['folio'];
		    	$controller_v->marcarUrgente($folio);
		    	break;
		    case 'solLiberacion':
		    	$folio=$_GET['folio'];
		    	$cliente=$_GET['cliente'];
		    	$controller_v->solLiberacion($folio, $cliente);
		    	break;
		    case 'verSKUS':
				$cliente = $_GET['cliente'];
				$cdfolio = $_GET['cdfolio'];
				$controller_v->verSKUS($cliente, $cdfolio);
				break;
			case 'solicitarMargenBajo':
				$cotizacion = $_GET['folio'];
				$partida = $_GET['partida'];
				$controller_v->solicitarMargenBajo($cotizacion, $partida);
				break;
			case 'cajas':
				if(isset($_GET['tipo'])){
					$tipo = $_GET['tipo'];
					$var = '';
					$mes = $_GET['mes'];
					$anio = $_GET['anio'];
				}else{
					$tipo = 1;
					$var='';
					$mes = '';
					$anio = '';
				}
				$controller_v->cajas($tipo, $var, $mes, $anio);
				break;
			case 'detalleFaltante':
				$docf=$_GET['docf'];
				$controller_v->detalleFaltante($docf);
				break;
			case 'verPagos':
				$controller_v->verPagos();
				break;
			case 'verPartidas':
				$idc=$_GET['caja'];
				$controller_v->verPartidas($idc);
				break;
			case 'repVentas':
				$controller_v->repVentas($GET_['tipo']=false,$_GET['clie']=false, $_GET['inicio']=false, $_GET['fin']=false);
				break;
			case 'ventasMostrador':
				$controller_v->ventasMostrador($doc = 0, $idf=0);
				break;
			case 'nv2':
				$controller_v->ventasMostrador($_GET['doc'], $_GET['idf']);
				break;
			case 'verNV':
				$p = isset($_GET['p'])? $_GET['p']:'s';
				$fi = isset($_GET['p'])? $_GET['fi']:'a';
				$ff = isset($_GET['p'])? $_GET['ff']:'a';
				$controller_v->verNV($p, $fi, $ff);
				break;
			case 'histProd':
				$controller_v->histProd($_GET['id'],$_GET['per'],$_GET['fi'],$_GET['ff'],$_GET['tipo'],$_GET['isbn']);
				break;
			case 'factNVR':
				$res=$controller_v->factNVR($_GET['doc'], $_GET['t']);
				json_encode($res);
				exit;
				break;
	default:
		header('Location: index.v.php?action=login');
		break;
	}


}
?>