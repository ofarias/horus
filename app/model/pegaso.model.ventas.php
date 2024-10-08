<?php 
require_once 'app/model/database.php';
require_once 'app/model/class.ctrid.php';
require_once 'app/model/pegaso.model.php';
/*Clase para hacer uso de database*/
class pegaso_ventas extends database{

	function clientes(){
		$this->query="SELECT * FROM CLIE01 WHERE STATUS = 'A'";
		$rs=$this->QueryObtieneDatosN();

		while($tsarray = ibase_fetch_object($rs)){
			$data[]=$tsarray;
		}

		return $data;
	}
	/***
     * cfa: 210316
     * consulta todas las cotizaciones registradas en la aplicación. Esta consulta es preparada para mostrar el grid 
     * en la pantalla de p.cotizacion.php
    ***/
    function consultarCotizaciones($cerradas=false){
        $usuario = $_SESSION['user']->USER_LOGIN;
        $data=array();
        $this->query = "SELECT A.CDFOLIO as folio, A.CVE_CLIENTE as cliente, B.NOMBRE, B.RFC, A.IDPEDIDO, A.INSTATUS as estatus, EXTRACT(DAY FROM A.DTFECREG) || '/' || EXTRACT(MONTH FROM A.DTFECREG) || '/' || EXTRACT(YEAR FROM A.DTFECREG) AS FECHA,
                                        A.SERIE AS SERIE, A.FOLIO AS FOLIOL, A.DBIMPTOT AS TOTAL , B.SALDO_CORRIENTE, B.SALDO_VENCIDO, A.URGENTE,
                    (SELECT iif(COUNT(ftcd.cdfolio) is null, 0, count(ftcd.cdfolio)) from FTC_COTIZACION_DETALLE ftcd where A.cdfolio = ftcd.cdfolio) as productos,
                    (SELECT iif(count(ftcd2.cdfolio) is null,0, count(ftcd2.cdfolio)) from FTC_COTIZACION_DETALLE ftcd2
            left join inve_clib01 clib on clib.cve_prod = ('PGS')||ftcd2.cve_art
            left join ftc_cotizacion ftc on ftc.cdfolio = ftcd2.cdfolio
            left join clie01 cl on trim(cl.clave) = trim(ftc.cve_cliente)
            where (cl.addendaf = 'Complemento_Liverpool_CFDI_5v3.xml' or cl.addendaf = 'Complemento_Suburbia_CFDI_v2.xml')
                    and ftcd2.cdfolio = A.cdfolio) as skus,
                    (SELECT iif(count(ftcd2.cdfolio) is null,0, count(ftcd2.cdfolio)) from FTC_COTIZACION_DETALLE ftcd2
            left join inve_clib01 clib on clib.cve_prod = ('PGS')||ftcd2.cve_art
            left join ftc_cotizacion ftc on ftc.cdfolio = ftcd2.cdfolio
            left join clie01 cl on trim(cl.clave) = trim(ftc.cve_cliente)
            where ((cl.addendaf = 'Complemento_Liverpool_CFDI_5v3.xml' or cl.addendaf = 'Complemento_Suburbia_CFDI_v2.xml') 
                    or cl.rfc = 'SUB910603SB'  
                    or cl.rfc = 'DLI83051718'
                   )
                    and ftcd2.cdfolio = A.cdfolio
                    and clib.camplib2 is null) as Sinskus,
            B.addendaf as addenda 
            FROM FTC_COTIZACION A 
            INNER JOIN CLIE01 B ON TRIM(A.CVE_CLIENTE) = TRIM(B.CLAVE) 
            WHERE CDUSUARI = '$usuario'";        
        $cerradas?$this->query.=" AND upper(INSTATUS) <> upper('PENDIENTE') ":$this->query.=" AND (upper(INSTATUS) = upper('PENDIENTE') or  upper(INSTATUS) = upper('LIBERADO') or upper(INSTATUS)= upper('LIBERACION') or upper(instatus) = 'RECHAZADO')";
        $this->query.=" ORDER BY CDFOLIO";
        $result = $this->QueryObtieneDatosN();
        //echo $this->query;
        while ($tsArray = ibase_fetch_object($result)){
                $data[] = $tsArray;
        }
        return $data;
    }    

    function cabeceraCotizacion($folio) {
        $this->query = "SELECT CDFOLIO, CVE_CLIENTE, NOMBRE, RFC, INSTATUS, DSIDEDOC, IDPEDIDO, EXTRACT(DAY FROM DTFECREG) || '/' || EXTRACT(MONTH FROM DTFECREG) || '/' || EXTRACT(YEAR FROM DTFECREG) AS FECHA,
                                DSPLANTA, DSENTREG, DBIMPSUB, DBIMPIMP, DBIMPTOT 
                          FROM FTC_COTIZACION A INNER JOIN CLIE01 B 
                            ON TRIM(A.CVE_CLIENTE) = TRIM(B.CLAVE)
                        WHERE CDFOLIO = '$folio'";
        $result = $this->QueryObtieneDatosN();
        while ($tsArray = ibase_fetch_object($result)){
                $data[] = $tsArray;
        }
        return $data;
    }

    function detalleCotizacion($folio) {
        $this->query = "SELECT CDFOLIO, A.CVE_ART, (B.GENERICO || B.SINONIMO|| ', '||B.CALIFICATIVO||', '||B.MARCA||', Medida:'||B.MEDIDAS) AS  DESCR, FLCANTID, DBIMPCOS, DBIMPPRE, DBIMPDES, B.CLAVE_PROD 
                          FROM FTC_COTIZACION_DETALLE A 
                        INNER JOIN FTC_Articulos B
                          ON A.CVE_ART = B.ID
                        WHERE CDFOLIO = '$folio'";
                        
        $result = $this->QueryObtieneDatosN();
        $data = array();
        while ($tsArray = ibase_fetch_object($result)){
                $data[] = $tsArray;
        }
        return $data;
    }

    function listaArticulos($cliente, $articulo, $descripcion, $partida, $folio){

            $this->query="SELECT RFC, NOMBRE FROM CLIE01 WHERE trim(CLAVE) = trim('$cliente')";
            $rs=$this->EjecutaQuerySimple();
            $row = ibase_fetch_object($rs);
            if($row->RFC == 'DLI931201MI9'){
            }
            if(!empty($descripcion)){          
                    $val = strpos($descripcion,':');

                    if($val === false){
                            $this->query = "SELECT A.*, 0 AS MARGEN_MINIMO, '' AS MARGEN_BAJO, pftc.nombre as nombre
                              FROM FTC_Articulos A 
                              left join producto_ftc pftc on pftc.clave_ftc = A.id
                              left join inve_clib01 clib on clib.cve_prod = pftc.clave
                              where (upper(pftc.NOMBRE) containing trim((upper('$descripcion'))) or upper(clib.camplib2) = upper('$descripcion'))
                              and A.status = 'A'";                                     
                        }else{
                            $a= explode(':',$descripcion);    
                            $descripcion = $a[0];
                            $this->query = "SELECT A.*, 0 AS MARGEN_MINIMO, '' AS MARGEN_BAJO, pftc.nombre as nombre
                              FROM FTC_Articulos A 
                              left join producto_ftc pftc on pftc.clave_ftc = A.id
                              where trim(upper(clave))=trim((upper('$descripcion')))
                              and A.status = 'A'";    
                        }                          
                
                $result = $this->QueryObtieneDatosN();

                if(isset($result)){
                    while ($tsArray = ibase_fetch_object($result)){
                      $data[] = $tsArray;
                    }    
                }
            }elseif(!empty($articulo)){
                        $this->query = "SELECT A.*, 0 AS MARGEN_MINIMO, '' AS MARGEN_BAJO
                              FROM FTC_Articulos A 
                              left join producto_ftc pftc on pftc.clave_ftc = A.id
                              where (upper(pftc.clave) containing upper('$articulo') or 
                                     upper(pftc.cve_prod) containing upper('$articulo'))  
                              and status = 'A'";
                $result = $this->QueryObtieneDatosN();
                if(isset($result)){
                    while ($tsArray = ibase_fetch_object($result)){
                      $data[] = $tsArray;
                    }    
                }
            }elseif(!empty($partida)){
                        $this->query = "SELECT A.*, (A.PRECIO * 1.23) AS PRECIO, ftcc.* , p.nombre as nombre, ftcc.CDFOLIO AS PARTIDA
                                          FROM FTC_Articulos A 
                                            left join FTC_COTIZACION_DETALLE ftcc on ftcc.cdfolio = $folio and ftcc.cve_art = $partida
                                            left join producto_ftc p on p.clave_ftc = $partida
                                            where ID = $partida";        
                        //echo 'Consulta nueva'.$this->query.'<p>';
                        $result = $this->QueryObtieneDatosN();
                if(isset($result)){
                    while ($tsArray = ibase_fetch_object($result)){
                      $data[] = $tsArray;
                    }    
                }
            }
            if(!isset($data)){
                $data='Alta';
                return $data;
            }
        return $data;
    }

    function articuloXcliente($cliente){
        $data=array();
        $this->query="SELECT cve_art as id, max(p.nombre) AS NOMBRE, max(p.categoria) AS CATEGORIA, max(p.precio) AS PRECIO, max(p.MARCA) AS MARCA, max(p.medidas) as medidas, max(p.COSTO_VENTAS) as costo,  0 as MARGEN_MINIMO, '' as MARGEN_BAJO
             FROM FTC_COTIZACION c
             left join FTC_COTIZACION_DETALLE d on d.CDFOLIO = c.CDFOLIO
             left join producto_ftc p on p.clave_ftc = d.cve_art
             where trim(c.cve_cliente) = trim('$cliente') and p.status = 'A' group by d.cve_art";
                $rs=$this->EjecutaQuerySimple();
                //echo $this->query;
        while ($tsarray=ibase_fetch_object($rs)){
            $data[]=$tsarray;
        }
        return $data;

    }

    function articuloXvendedor(){
        $data=array();
        $usuario = $_SESSION['user']->NOMBRE;
        $data=array();
        $this->query="SELECT idart as id , (p.nombre) AS NOMBRE, (p.categoria) AS CATEGORIA, (p.precio) AS PRECIO, (p.MARCA) AS MARCA, (p.medidas) as medidas, (p.COSTO_VENTAS) as costo,  0 as MARGEN_MINIMO, '' as MARGEN_BAJO, (SKU) as SKU
                    FROM FTC_ART_X_CLIE f
                    left join producto_ftc p on p.clave_ftc = f.idart
                    where usuario = '$usuario' and p.status = 'A'
                    ";
        $rs= $this->EjecutaQuerySimple();
        //echo $this->query;
        while ($tsarray= ibase_fetch_object($rs)){
            $data[]=$tsarray;
        }
        return $data;
    }


    function articuloXrfc($cliente){
        $data=array();
        $this->query="SELECT RFC FROM CLIE01 WHERE trim(CLAVE) = trim('$cliente')";
        $rs=$this->EjecutaQuerySimple();
        $row=ibase_fetch_object($rs);

        $this->query="SELECT  d.cve_art as id , f.cot_rfc, max(p.nombre) as nombre, max(p.categoria) AS CATEGORIA, max(p.precio) AS PRECIO, max(p.MARCA) AS MARCA, max(p.medidas) as medidas, max(p.COSTO_VENTAS) as costo,  0 as MARGEN_MINIMO, '' as MARGEN_BAJO,
                (select first 1 sku_cliente from lista_de_precios where trim(cliente) = '$cliente' and producto = MAX(p.clave)) as sku_cliente,
                 (select first 1 sku_otro from lista_de_precios where trim(cliente) = '$cliente' and producto = MAX(p.clave)) as sku_otro,
                 max(INVE.CAMPLIB2) as sku
                    from ftc_cotizacion_detalle d
                    left join ftc_cotizacion f on d.cdfolio = f.cdfolio
                    left join producto_ftc p on p.clave_ftc = d.cve_art
                    left join inve_clib01 inve on inve.cve_prod = p.clave
                    where cot_rfc = '$row->RFC'
                    and p.status = 'A'
                    group by d.cve_art, f.cot_rfc
                    order by f.cot_rfc";

    /*SELECT cve_art as id, max(p.nombre) AS NOMBRE, max(p.categoria) AS CATEGORIA, max(p.precio) AS PRECIO, max(p.MARCA) AS MARCA, max(p.medidas) as medidas, max(p.COSTO_VENTAS) as costo,  0 as MARGEN_MINIMO, '' as MARGEN_BAJO, 
                max(INVE.CAMPLIB2) as sku,
                (select first 1 sku_cliente from lista_de_precios where trim(cliente) = trim('$key->CLAVE') and producto = MAX(p.clave)) as sku_cliente,
                (select first 1 sku_otro from lista_de_precios where trim(cliente) = trim('$key->CLAVE') and producto = MAX(p.clave)) as sku_otro */
        $res=$this->EjecutaQuerySimple();

        while ($tsarray = ibase_fetch_object($res)){
            $data[]=$tsarray;
        }
        return $data;
    }

    function insertaCotizacion($cliente, $identificadorDocumento){

        $usuario = $_SESSION['user']->NOMBRE;

        $this->query = "SELECT iif(MAX(cdfolio) is null, 0, max(cdfolio)) as folio FROM FTC_COTIZACION";
        $result = $this->QueryObtieneDatosN();
        $row=ibase_fetch_object($result);
        $folio=$row->FOLIO + 1;

        $user = $_SESSION['user']->USER_LOGIN;
        $this->query = "SELECT LETRA_NUEVA FROM PG_USERS WHERE USER_LOGIN = '$user'";
        $rs=$this->QueryObtieneDatosN();
        $row=ibase_fetch_object($rs);
        $letra = $row->LETRA_NUEVA;

        $this->query="SELECT COALESCE(MAX(folio),0) as folio FROM  FTC_COTIZACION WHERE SERIE = '$letra'";
        $rs=$this->QueryObtieneDatosN();
        $row=ibase_fetch_object($rs);
        $foliol = $row->FOLIO +1;
        //echo $this->query;
        //echo 'Folio Letra:'.$foliol;
        //echo 'Folio:'.$folio;
        $usuario = $_SESSION['user']->USER_LOGIN;
        $this->query = "INSERT INTO FTC_COTIZACION (CDFOLIO, CVE_CLIENTE, DSIDEDOC, DTFECREG, INSTATUS, DBIMPSUB, DBIMPIMP, DBIMPTOT, DSPLANTA, DSENTREG, CDUSUARI, FOLIO, SERIE, cve_cotizacion, vendedor, cot_rfc ) "
                . "VALUES ($folio, TRIM('$cliente'), '$identificadorDocumento', CAST('Now' as date),'PENDIENTE',0,0,0,(SELECT COALESCE(substring(CAMPLIB7 from 1 for 90), '') FROM CLIE_CLIB01 WHERE TRIM(CVE_CLIE) = TRIM('$cliente')),(SELECT COALESCE(substring(CAMPLIB8 from 1 for 90), '') FROM CLIE_CLIB01 WHERE TRIM(CVE_CLIE) = TRIM('$cliente')),'$usuario', $foliol, '$letra', '$letra'||'$foliol', '$usuario', (SELECT RFC FROM CLIE01 WHERE trim(CLAVE) = '$cliente'))";        
        $rs = $this->EjecutaQuerySimple();
        return $rs;  
    }
    
    function avanzaCotizacion($folio){
        $this->query ="SELECT INSTATUS FROM FTC_COTIZACION WHERE CDFOLIO = $folio";
        $rs=$this->EjecutaQuerySimple();
        $row=ibase_fetch_object($rs);
        $sta = $row->INSTATUS;
        if($sta == 'PENDIENTE'){
            $this->query = "UPDATE FTC_COTIZACION SET INSTATUS = 'CERRADA' WHERE CDFOLIO = $folio";
            $rs = $this->grabaBD();
            $this->generaDocumentoCotizacion($folio);
            //echo "<br />query: ".$this->query;
            return $rs;            
        }else{
            echo 'La cotizacion ya habia sido previamente liberada.';
        }
        return;
    }
    
    function generaDocumentoCotizacion($folio) {
        $this->query = "SELECT CDFOLIO, CVE_CLIENTE, DSIDEDOC, IDPEDIDO, DBIMPSUB, DBIMPIMP, DBIMPTOT, DBIMPDES, SERIE, FOLIO FROM FTC_COTIZACION WHERE CDFOLIO = $folio";
        $result = $this->QueryObtieneDatosN();
        $data = array();
        while ($tsArray = ibase_fetch_object($result)){
            $data[] = $tsArray;
        }
        $existeFolio = false;
        if(count($data)>0){  
            $existeFolio =true;
            foreach ($data as $row){
                $folio = $row->CDFOLIO;
                $cliente = $row->CVE_CLIENTE;
                $letra = $row->DSIDEDOC;
                $pedido = $row->IDPEDIDO;
                $subtotal = $row->DBIMPSUB;
                $impuesto = $row->DBIMPIMP;
                $total = $row->DBIMPTOT;
                $descuento = $row->DBIMPDES;
                $docp = $row->SERIE.$row->FOLIO;
                $serie_pegaso = $row->SERIE;
            }
        }
        $serie = 'P'.substr($letra,1);
        //echo "serie: $serie";
        if(!$existeFolio){
            return NULL;
        } else {
            $usuario = $_SESSION['user']->USER_LOGIN;
            $consecutivo = $this->obtieneConsecutivoClaveDocumento($serie);
            $cve_doc = $letra.$consecutivo;
        }

        $serie=$letra;
        $consecutivo = $consecutivo;
        $bitacora = 123125;
        
        $insert = "INSERT INTO FACTP01 ";
        $insert.="(TIP_DOC, CVE_DOC, CVE_CLPV, STATUS, DAT_MOSTR, CVE_VEND, CVE_PEDI, FECHA_DOC, FECHA_ENT, CAN_TOT, IMP_TOT1, IMP_TOT2, IMP_TOT3, IMP_TOT4, DES_TOT, DES_FIN, COM_TOT, CONDICION, IMPORTE, CVE_OBS, NUM_ALMA, ACT_CXC, ACT_COI, NUM_MONED, TIPCAMB, ENLAZADO, TIP_DOC_E, NUM_PAGOS, FECHAELAB, SERIE, FOLIO, CTLPOL, ESCFD, CONTADO, CVE_BITA, BLOQ, DES_FIN_PORC, DES_TOT_PORC, TIP_DOC_ANT, DOC_ANT, TIP_DOC_SIG, DOC_SIG, FORMAENVIO, REALIZA)";
        $insert.="VALUES";
        $insert.="('P' ,'$docp', (SELECT CLAVE FROM CLIE01 WHERE TRIM(CLAVE) = TRIM('$cliente')), 'O' ,0,'    1', '$pedido' , CAST('Now' as date), CAST('Now' as date), $subtotal, 0, 0, 0 , $impuesto, $descuento, 0,0,'', $total, 0, 99,'S','N', 1, 1, 'O', 'O',1, CAST('Now' as date),'$serie', $consecutivo, 0, 'N', 'N','$bitacora' ,'N', 0 , 0, '', '',  '', '', 'I', '$usuario')";
        $this->query = $insert;
        $rs = $this->grabaBD();
        if(empty($rs)){
            echo '<br/> NO Se ha insertado correctamente en FACTP01';
        }
         
        $this->query = "SELECT CVE_ART, DBIMPCOS, FLCANTID, DBIMPPRE, DBIMPDES FROM FTC_COTIZACION_DETALLE WHERE CDFOLIO = $folio";
        $result = $this->QueryObtieneDatosN();
        $data = array();
        while ($tsArray = ibase_fetch_object($result)){
            $data[] = $tsArray;
        }
        if(count($data)>0){            
            foreach ($data as $row){
                $cve_art = $row->CVE_ART;
                $costo = $row->DBIMPCOS;
                $cantidad = $row->FLCANTID;
                $precio = $row->DBIMPPRE;
                $descuentoPartida = $row->DBIMPDES;
                $subtotalPartida = round($cantidad * $precio, 2) - round($cantidad * $descuentoPartida, 2);
                $subtotal += $subtotalPartida;                
                $descuento+= $descuentoPartida;
                $cveprov = 'Pendiente';
                $nomprov = 'Pendiente';
                $this->query="SELECT iif(CLAVE_DISTRIBUIDOR is null or clave_distribuidor ='' , '0000:pendiente', clave_distribuidor) AS CD FROM FTC_Articulos WHERE ID = $cve_art";
                $rs = $this->QueryObtieneDatosN();
                $row=ibase_fetch_object($rs);
                $prove = $row->CD;
                $prov = explode(':', $prove);
                $cveprov = $prov[0];
                $nomprov = $prov[1];
                /// INSERTAMOS DIRECTO EN PREOC01 
                $this->query = "INSERT INTO PREOC01 (COTIZA, PROD, CANTI, CANT_ORIG, COSTO, IVA, TOTAL, PROVE, CLIEN, FECHASOL, STATUS, NOM_PROV, NOM_CLI, PAR, NOMPROD, REST, docorigen, urgente, fact_ant, pedido_clie, rec_faltante, ordenado, um, importe, letra_v, status_ventas, facturado, pendiente_facturar, remisionado, pendiente_remisionar, empacado, rev_dospasos, envio, utilidad_estimada,costo_maximo )
                 VALUES ( '$docp', 'PGS$cve_art', $cantidad, $cantidad, 
                        (SELECT COALESCE(COSTO,0) FROM FTC_Articulos WHERE ID = $cve_art),
                        $impuesto, $subtotalPartida, '$cveprov',
                        (SELECT CLAVE FROM CLIE01 WHERE TRIM(CLAVE) = TRIM('$cliente')), current_date, 'P','$nomprov', 
                        (SELECT NOMBRE FROM CLIE01 WHERE TRIM(CLAVE) = TRIM('$cliente')),
                        (SELECT COALESCE(MAX(NUM_PAR), 0) FROM PAR_FACTP01 where cve_doc = '$docp') + 1,
                        (SELECT substring((GENERICO||' '||SINONIMO||' '||CALIFICATIVO||' '||MEDIDAS||' '||UM||' '||MARCA) FROM 1 FOR 255) FROM FTC_Articulos WHERE ID = $cve_art),
                        $cantidad,'NA', '', null, '$pedido', $cantidad, 0,
                        (SELECT COALESCE(UNI_MED,'pz') FROM INVE01 WHERE CVE_ART = 'PGS$cve_art'), 
                        $total, '$serie', 'Pe', 0, $cantidad, 0, $cantidad, 0, 
                        (SELECT COALESCE(rev_dospasos, 'N') FROM CARTERA WHERE TRIM(idcliente) = TRIM('$cliente')),
                        (SELECT COALESCE(ENVIO, 'Local') FROM CARTERA WHERE TRIM(idcliente) = TRIM('$cliente')),
                         0, 
                        (SELECT COALESCE(COSTO,1) FROM FTC_Articulos WHERE ID = $cve_art) * 1.2)";
                //echo $this->query;
                $rs=$this->grabaBD();
                if(empty($rs)){
                    echo '<br/> NO Se ha insertado correctamente en PREOC01';
                }
                $this->query="SELECT MAX(ID) as idp FROM PREOC01";
                $rs=$this->QueryObtieneDatosN();
                $row=ibase_fetch_object($rs);
                $idpreoc = $row->IDP;

                $actualiza = "INSERT INTO PAR_FACTP01 
                (CVE_DOC, NUM_PAR, CVE_ART,CANT, PREC, COST, IMPU1,IMPU2, IMPU3, IMPU4, IMP1APLA, IMP2APLA, IMP3APLA, IMP4APLA,TOTIMP1, TOTIMP2,TOTIMP3,TOTIMP4,DESC1,ACT_INV, TIP_CAM, UNI_VENTA,TIPO_ELEM, TIPO_PROD, CVE_OBS, E_LTPD, NUM_ALM, NUM_MOV, TOT_PARTIDA, USUARIO_PHP, IMPRIMIR, id_preoc, desc2, desc3) 
                VALUES
                ('$docp',(SELECT COALESCE(MAX(NUM_PAR), 0) FROM PAR_FACTP01 where cve_doc = '$docp') + 1,'PGS$cve_art',$cantidad,$precio,$costo,0,0,0,16,0,0,0,0,0,0,0,$impuesto,$descuentoPartida,'N',1,
                (SELECT UNI_MED FROM INVE01 WHERE CVE_ART = 'PGS$cve_art'),
                'N','P',0,0,9,NULL,($subtotalPartida),'$usuario', 'S', $idpreoc,0,0)";
                //echo '<br/>'.$actualiza.'<br/>';
                $this->query = $actualiza;
                $rs=$this->grabaBD();
                if(empty($rs)){
                    echo '<br/> NO Se ha insertado correctamente la partida ';
                }
                ///echo 'Inserta en preoc01:'.$this->query.'<p>';
                // Inserta la nueva Liberacion de los productos.
            }

            $this->query="SELECT iif(MAX(CP_FOLIO) is null, 0, max(CP_FOLIO)) AS FOLIO FROM CAJAS_ALMACEN WHERE CP_SERIE = '$serie_pegaso'";
            $rs=$this->EjecutaQuerySimple();
            $row=ibase_fetch_object($rs);
            $folio_cp_folio= $row->FOLIO + 1; 
            $caja_pegaso = 'L'.$serie_pegaso.$folio_cp_folio;

            $this->query= "INSERT INTO CAJAS_ALMACEN (IDCA, PEDIDO, COTIZACION, VENDEDOR, PRESUP_COMPRA, PRESUP_VENTA, NUM_PROD, STATUS, FECHA_VENTAS, CCC, MAESTRO, caja_pegaso, cp_folio, cp_serie) VALUES(NULL, '$docp', $folio, '$usuario', 
                    (select sum(DBIMPCOS) from FTC_COTIZACION_DETALLE WHERE CDFOLIO = $folio),
                    (select sum(DBIMPPRE) FROM FTC_COTIZACION_DETALLE WHERE CDFOLIO =$folio),
                    (select count(cve_art) from FTC_COTIZACION_DETALLE WHERE CDFOLIO = $folio),
                    '0',
                    current_timestamp,
                    (SELECT CCC FROM CARTERA WHERE TRIM(idcliente) = TRIM('$cliente')),
                    (SELECT CVE_MAESTRO FROM CLIE01 WHERE TRIM(clave) = Trim('$cliente')),
                    null,
                    null,
                    null
                    )";
                $rs=$this->grabaBD();
                if(empty($rs)){
                    echo '<br/> NO Se ha insertado correctamente en CAJAS_ALMACEN. <br/>';
                }
        }
        return $rs;
    }
        
        
    function obtieneConsecutivoClaveDocumento($letra){
        $this->query = "SELECT COALESCE(MAX(FOLIO), 1)+1 FOLIO FROM FACTC01 WHERE TIP_DOC = 'C' AND SERIE = '$letra'";        
        $result = $this->QueryObtieneDatosN();
        //echo "query: ".$this->query;
        $data = array();
        while ($tsArray = ibase_fetch_object($result)){
            $data[] = $tsArray;
        }
        $consecutivo = 1;
        if(count($data)>0){            
           foreach ($data as $row){
                $consecutivo = $row->FOLIO;
            } 
        }
        //echo "consecutivo : $consecutivo";
        return $consecutivo;
    }
    
    function actualizaPedidoCotizacion($folio, $pedido) {

        $this->query="SELECT IIF(idPEDIDO IS NULL OR idPEDIDO = '', 'ok', idPEDIDO) as val, f.cve_cliente, cl.nombre 
                        FROM FTC_COTIZACION f
                        left join clie01 cl on trim(cl.clave) = trim(f.cve_cliente)
                         WHERE idpedido = '$pedido'";
        $rs=$this->EjecutaQuerySimple();
        $row = ibase_fetch_object($rs);

        if($row){
            echo '<label> <font color="red"> El pedido '.$row->VAL.' ya existe, para el cliente ('.$row->CVE_CLIENTE.') '.$row->NOMBRE.' , NO se permite pedidos duplicados, aun que sea para diferente cliente; favor de verificarlo. </font></label>';
        }else{    
            $this->query = "UPDATE FTC_COTIZACION SET IDPEDIDO = '$pedido' WHERE CDFOLIO = $folio";
            $rs = $this->EjecutaQuerySimple();
        }
        return $rs;
    }
            
    function cancelaCotizacion($folio){
        $this->query = "UPDATE FTC_COTIZACION SET INSTATUS = 'CANCELADA' WHERE CDFOLIO = $folio";        
        $rs = $this->EjecutaQuerySimple();
        return $rs;
    }
    
    function quitarCotizacionPartida($folio, $partida) {
        $this->query = "DELETE FROM FTC_COTIZACION_DETALLE WHERE CDFOLIO = $folio AND CVE_ART = '$partida'";        
        $rs = $this->EjecutaQuerySimple();
        $this->actualizaTotales($folio);
        return $rs;
    }
    
    function actualizaCotizacion($folio, $partida, $articulo, $precio, $descuento, $cantidad, $ida){
        //echo 'Este es el valor de la partida: '.$partida;
        if($partida != ''){
            $this->query = "UPDATE FTC_COTIZACION_DETALLE SET "
                    . " CVE_ART = '$ida', FLCANTID = $cantidad, DBIMPCOS = (SELECT costo FROM FTC_Articulos A WHERE ID = '$ida'), DBIMPPRE = $precio, DBIMPDES = $descuento "
                    . " WHERE CDFOLIO = '$folio' AND CVE_ART = '$ida'";
        } else {
            $this->query = "INSERT INTO FTC_COTIZACION_DETALLE "
                    . "(CDFOLIO,CVE_ART,FLCANTID,DBIMPPRE,DBIMPCOS,DBIMPDES)"
                    . "VALUES ('$folio','$ida',$cantidad,$precio,(SELECT costo FROM FTC_Articulos A WHERE ID = '$ida'), $descuento)";
        }        

        $rs = $this->EjecutaQuerySimple();
        $this->actualizaTotales($folio);
        return $rs;        
    }

    function actualizaTotales($folio){
        $this->query = "SELECT FLCANTID, DBIMPPRE, DBIMPDES FROM FTC_COTIZACION_DETALLE WHERE CDFOLIO = $folio";
        $result = $this->QueryObtieneDatosN();
        $data = array();
        while ($tsArray = ibase_fetch_object($result)){
            $data[] = $tsArray;
        }
        $subtotal = 0;
        $impuesto = 0;
        $descuento = 0;
        $total = 0;       
        if(count($data)>0){       
            foreach ($data as $row){
                $cantidad = $row->FLCANTID;
                $precio = $row->DBIMPPRE;
                $descuentoPartida = $row->DBIMPDES;
                $desImp = round(($cantidad * $precio),2) * round(($descuentoPartida/100),4);
                $subtotalPartida = round($cantidad * $precio, 2) - round(($cantidad * $precio) * ($descuentoPartida/100), 2);
                $subtotal += $subtotalPartida;                
                $descuento+= $desImp;             
            }
            $descuento = round($descuento, 2);
            $impuesto = round(($subtotal * 0.16), 2);
            $total = round(($subtotal + $impuesto), 2);
        } 
        $this->query = "UPDATE FTC_COTIZACION SET DBIMPSUB = $subtotal, DBIMPIMP = $impuesto, DBIMPTOT = $total, DBIMPDES = $descuento "
                . " WHERE CDFOLIO = $folio";
        
        $rs = $this->EjecutaQuerySimple();
        return $rs;
    }
    
    function moverClienteCotizacion($folio, $cliente){
        $usuario=$_SESSION['user']->USER_LOGIN;
        $this->query = "UPDATE FTC_COTIZACION SET CVE_CLIENTE = TRIM('$cliente'),
            DSPLANTA = (SELECT COALESCE(substring(CAMPLIB7 from 1 for 90), '') FROM CLIE_CLIB01 WHERE TRIM(CVE_CLIE) = TRIM('$cliente')), 
            DSENTREG= (SELECT COALESCE(substring(CAMPLIB8 from 1 for 90), '') FROM CLIE_CLIB01 WHERE TRIM(CVE_CLIE)=TRIM('$cliente')),
            CDUSUARI='$usuario', 
            COT_RFC= (SELECT RFC FROM CLIE01 WHERE trim(CLAVE) = TRIM('$cliente')), 
            VENDEDOR = '$usuario' 
            WHERE CDFOLIO = $folio";
        $rs = $this->EjecutaQuerySimple();      
        return $rs;        
    }
    
    function autocompletaArticulo($descripcion) {
        $this->query="SELECT DESC FROM INVE01 WHERE DESC LIKE '$descripcion%'";
        $result = $this->QueryObtieneDatosN();
        $data = array();
        while ($tsArray = ibase_fetch_object($result)){
                $data[] = $tsArray->descripcion;
        }        
        $json = json_encode($data);
        return $json;
    }
    
    function listadoClientes($clave, $cliente){
        $data = array();
        $usuario = $_SESSION['user']->USER_LOGIN;
        $select_letras = ", (SELECT COALESCE(LETRA, '') || ',' || COALESCE(LETRA2, '') || ',' || COALESCE(LETRA3, '') || ',' || COALESCE(LETRA4, '') || ',' || COALESCE(LETRA5, '') LETRAS ";
        $select_letras.= " FROM PG_USERS ";
        $select_letras.= " WHERE USER_LOGIN = '$usuario') letras";

        if($clave!=''){
            $this->query = "SELECT TRIM(cl.CLAVE) CLAVE, cl.STATUS, cl.NOMBRE, cl.RFC, cl.SALDO_VENCIDO, cl.SALDO_CORRIENTE, cl.CVE_MAESTRO ".$select_letras.", ct.plazo, ct.linea_cred, CAST(substring(clb.CAMPLIB7 from 1 for 60) AS VARCHAR(60)) as dir
                            FROM CLIE01 cl
                            LEFT JOIN cartera ct on trim(ct.idcliente) = cl.clave
                            left join CLIE_CLIB01 clb on clb.cve_clie = cl.clave 
                            WHERE (STATUS <> 'S' and status <> 'B') AND TRIM(CLAVE) = '$clave'";
        } elseif($cliente!=''){
            $this->query = "SELECT TRIM(cl.CLAVE) CLAVE, cl.STATUS, cl.NOMBRE, cl.RFC, cl.SALDO_VENCIDO, cl.SALDO_CORRIENTE, cl.CVE_MAESTRO ".$select_letras.", ct.plazo, ct.linea_cred, CAST(substring(clb.CAMPLIB7 from 1 for 60) AS VARCHAR(60)) as dir
                            FROM CLIE01 cl
                            LEFT JOIN cartera ct on trim(ct.idcliente) = cl.clave
                            left join CLIE_CLIB01 clb on clb.cve_clie = cl.clave 
                            WHERE upper(NOMBRE) LIKE upper('%$cliente%') AND (STATUS <> 'S' and status <> 'B')";
        } else {
            return $data;
        }
        $result = $this->QueryObtieneDatosN();  
        //echo $this->query;
        //break;
        while ($tsArray = ibase_fetch_object($result)){
            $data[] = $tsArray;
        }
        return $data;
    }

    function listadoLetras() {
        $usuario = $_SESSION['user'];
        $this->query = "SELECT COALESCE(LETRA, '') || ',' || COALESCE(LETRA2, '') || ',' || COALESCE(LETRA3, '') || ',' || COALESCE(LETRA4, '') || ',' || COALESCE(LETRA5, '') LETRAS";
        $this->query .= " FROM PG_USERS ";
        $this->query .= " WHERE USER_LOGIN = '$usuario'";
        $data = array();
        $result = $this->QueryObtieneDatosN();        
        while ($tsArray = ibase_fetch_object($result)){
            $data[] = $tsArray;
        }
        $letras = "";
        if(count($data)>0){            
            foreach ($data as $row){
                $letras = $row->LETRAS;
            }
        } 
        $myArray = explode(',', $letras);
        print_r($myArray);
        return $myArray;
    }
    
////// FINALIZA COTIZACION CFA- 
    
///// Modulo de productos almacen 10.
    function VerCat10($alm){
    	$prod="SELECT * from PRODUCTOS WHERE ACTIVO = 'S'";
    	$this->query=$prod;
    	$result=$this->QueryObtieneDatosN();
    	while ($tsArray=ibase_fetch_object($result)){
    		$data[]=$tsArray;
    	}
    	return $data;
    }
/*
    function EditProd($id){
    	$this->query="SELECT * from PRODUCTOS where id =$id";
    	$result=$this->QueryObtieneDatosN();
    	while ($tsArray=ibase_fetch_object($result)){
    		$data[]=$tsArray;
    	}
    	return $data;
    }
*/

    function traeMarcas(){
        $data=array();
        $this->query="SELECT * FROM MARCAS WHERE STATUS = 'A'";
        $rs=$this->QueryObtieneDatosN();
            while($tsarray = ibase_fetch_object($rs)){
                $data[]=$tsarray;
            }
        return @$data;
    }

    function traeMarcasT(){
        $data=array();
        $this->query="SELECT * FROM MARCAS WHERE STATUS <> 'A'";
        $rs=$this->QueryObtieneDatosN();
            while($tsarray = ibase_fetch_object($rs)){
                $data[]=$tsarray;
            }
        return @$data;
    }


    function traeProveedores(){
        $this->query="SELECT * FROM PROV01 WHERE STATUS = 'A'";
        $rs=$this->QueryDevuelveAutocomplete();          
        return @$rs;
    }
    function traeProductos(){
        $this->query="SELECT fart.*, ct.id as idc FROM FTC_Articulos fart
                    left join CATEGORIAS ct ON  ct.nombre_categoria = fart.categoria where fart.status = 'A'";
        $rs=$this->QueryObtieneDatosN();

        while($tsarray = ibase_fetch_object($rs)){
            $data[]=$tsarray;
        }
        return $data;
    }

    function traeProductosFTC($descripcion){
        $COMPLETO = false;
        if(strpos($descripcion, ' ')){
            $desc2 = explode(' ', $descripcion);
            $contado  = count($desc2);
            for($i = 0; $i < $contado; $i++){
                $COMPLETO = $COMPLETO." AND (nombre containing('".$desc2[$i]."') or clave containing (upper('".$descripcion."')) or cve_prod containing ('".$descripcion."'))";
            }
        }else{
            $COMPLETO = " AND nombre containing ('".$descripcion."') or clave containing (upper('".$descripcion."')) or cve_prod containing ('".$descripcion."')";
        }
        //$this->query = "INSERT INTO DICCIONARIO (ID, PALABRA)"
        /*$this->query="SELECT pftc.* FROM producto_ftc pftc 
            left join FTC_Articulos ftca on ftca.id = pftc.clave_ftc 
            where ftca.status = 'A' 
            $COMPLETO";
        */
        $this->query="SELECT pftc.* FROM producto_ftc pftc 
            where STATUS!=''
            $COMPLETO";
        //echo $this->query;
        $rs=$this->QueryDevuelveAutocompletePFTC();

        return @$rs;
    }

    function traeClientes($cliente){
        $COMPLETO = false;
        if(strpos($cliente, ' ')){
            $desc2 = explode(' ', $cliente);
            $contado  = count($desc2);
            for($i = 0; $i < $contado; $i++){
                $COMPLETO = $COMPLETO." AND nombre containing('".$desc2[$i]."') ";
            }
        }else{
            $COMPLETO = " and nombre containing ('".$cliente."') or clave=upper('".$cliente."')";
        }
        $this->query="SELECT c.* FROM clie01 c where c.status!='' $COMPLETO";
        //echo $this->query;
        $rs=$this->QueryDevuelveAutocompleteClie();
        return @$rs;    
    }

    function traeCategorias(){
        $data = array();
        $this->query="SELECT * FROM CATEGORIAS WHERE STATUS = 'A'";
        $rs=$this->QueryObtieneDatosN();
            while($tsarray = ibase_fetch_object($rs)){
                $data[]=$tsarray;
            }
        return @$data;   
    }

    function traeCategoriasT(){
        $data = array();
        $this->query="SELECT * FROM CATEGORIAS where (status = 'P' or status ='B')";
        $rs=$this->QueryObtieneDatosN();
            while($tsarray = ibase_fetch_object($rs)){
                $data[]=$tsarray;
            }
        return @$data;   
    }

    function traeLineas(){
        $this->query="SELECT * FROM LINEAS WHERE STATUS = 'A'";
        $rs=$this->QueryObtieneDatosN();
            while($tsarray = ibase_fetch_object($rs)){
                $data[]=$tsarray;
            }
        return @$data;   
    }

    function traeUM(){
        $this->query="SELECT * FROM UM WHERE STATUS = 'A'";
        $rs=$this->QueryObtieneDatosN();
            while($tsarray = ibase_fetch_object($rs)){
                $data[]=$tsarray;
            }
        return @$data;   
    }

    function traeMarcasxCat(){
        $this->query="SELECT mxc.id, mxc.idmarca, mc.clave_marca as Marca, cat.id, cat.nombre_categoria as categoria from marcas_x_categoria mxc
                        left join marcas mc on mxc.idmarca = mc.id
                        left join categorias cat on mxc.idcat = cat.id
                        order by mc.clave_marca asc";
        $rs=$this->QueryObtieneDatosN();
        while ($tsarray=ibase_fetch_object($rs)){
            $data[]=$tsarray;
        }
        return @$data;
    }

    function traeProducto($ids){
        $this->query="SELECT * FROM FTC_Articulos  WHERE ID = $ids";
        $rs=$this->QueryObtieneDatosN();

        while($tsarray=ibase_fetch_object($rs)){
            $data[]=$tsarray;
        }
        return $data;
    }

   

    function traeCliente($aguja, $ids){
        $this->query="SELECT cl.clave, cl.nombre, cl.calle, cl.colonia, cl.numext, fac.*  
                        FROM CLIE01 cl 
                        left join FTC_ART_X_CLIE fac on fac.idclie = cl.clave and fac.idart = $ids 
                        WHERE (clave containing ('$aguja') or nombre containing ('$aguja'))";

        $res=$this->QueryObtieneDatosN();

            while($tsarray=ibase_fetch_object($res)){
            $data[]=$tsarray;
        }
        return @$data;
    }

    function traeProveedor($aguja, $ids){
        $this->query="SELECT p.CLAVE, p.NOMBRE, p.CALLE, p.NUMEXT, p.COLONIA, fap.*
                        FROM PROV01 p
                        left join FTC_ART_X_PROV fap on fap.idprov = p.clave and idart = $ids 
                        WHERE (clave containing ('$aguja') or nombre containing ('$aguja'))";
        $res=$this->QueryObtieneDatosN();

            while ($tsarray=ibase_fetch_object($res)){
                    $data[]=$tsarray;
                }
            return @$data;
    }


    function insertaSol($categoria, $descripcion, $marca, $cotizacion, $cliente, $unidadmedida,$empaque, $cantsol){
    //( $categoria, $linea, $descripcion, $marca, $generico, $sinonimos, $calificativo, $medidas, $unidadmedida, $empaque, $prov1, $codigo_prov1, $sku, $costo_prov, $iva, $desc1, $desc2, $desc3, $desc4, $desc5, $impuesto, $costo_total, $cotizacion, $cliente){

        $user= $_SESSION['user']->NOMBRE;

        $this->query="SELECT mxc.id, mxc.idmarca, mc.clave_marca as Marca, cat.id, cat.nombre_categoria as categoria 
                      from marcas_x_categoria mxc
                      left join marcas mc on mxc.idmarca = mc.id
                      left join categorias cat on mxc.idcat = cat.id
                      where mxc.id = $categoria
                      order by mc.clave_marca asc";
        $res=$this->QueryObtieneDatosN();
        $row=ibase_fetch_object($res);
        $categoria = $row->CATEGORIA;
        $marca = $row->MARCA;

        $this->query="INSERT INTO FTC_Articulos (id,categoria, generico, marca, cotizacion, vendedor, STATUS, um, empaque, cantsol) VALUES(null,'$categoria', '$descripcion', '$marca', $cotizacion, '$user', 'P', '$unidadmedida', $empaque, $cantsol)";
        //(NULL, '$linea', '$categoria', '$generico', '$sinonimos', '$calificativo', '$medidas', Null, '$marca', '$unidadmedida', $empaque, '$prov1', '$codigo_prov1', '$sku', $costo_prov, 0, 0, 0,'P','$user',$cotizacion)
        $rs=$this->EjecutaQuerySimple();
        
        //echo $this->query;
        return;
    }


    function usuariosCompras(){
        $this->query="SELECT * FROM PG_USERS WHERE USER_ROL = 'costos' and user_status = 'alta'";
        $rs=$this->QueryObtieneDatosN();

        while ($tsarray=ibase_fetch_object($rs)){
            $data[]=$tsarray;
        }

        return @$data;
    }


    function altaCategoria($nombreCategoria, $abreviatura, $responsable, $status){
        $this->query="INSERT INTO CATEGORIAS VALUES (NULL, '$nombreCategoria', '$abreviatura', '$responsable', 0, '$status' )";
        $rs=$this->EjecutaQuerySimple();
        //echo $this->query;
        return;
    }

    function editaCategoria($idcat){
        $this->query="SELECT * FROM CATEGORIAS WHERE ID =$idcat";
        $rs=$this->QueryObtieneDatosN();
        while($tsarray=ibase_fetch_object($rs)){
            $data[]=$tsarray;
        }
        return @$data;
    }

    function editarCategoria($nombreCategoria, $abreviatura, $responsable, $status, $idcat){
        $this->query="UPDATE CATEGORIAS SET abreviatura ='$abreviatura', responsable='$responsable', status ='$status' where id = $idcat";
        $rs=$this->EjecutaQuerySimple();

        return;
    }

    function altaMarca($cm, $nc, $rz, $dir, $tel, $cont, $s, $p, $d){
        echo 'Valor de cm antes de entities: '.$cm.'<p>';
        $cm = htmlentities(strtoupper($cm),ENT_QUOTES);
        $nc = strtoupper($nc);
        $rz = strtoupper($rz);
        echo 'Valor de cm despues de entities:'.$cm.'<p>';
        $this->query="INSERT INTO MARCAS VALUES(NULL, '$cm', '$nc', '$rz', '$dir', '$tel', '$cont', '$s', $p, 'Nuevo', '$d')";
        $rs=$this->EjecutaQuerySimple();
        return;
    }

    function editaMarca($idm){
        $this->query="SELECT * FROM MARCAS WHERE ID = $idm";
        $rs=$this->QueryObtieneDatosN();

        while($tsarray=ibase_fetch_object($rs)){
            $data[]=$tsarray;
        }
        return @$data;
    }

    function editarMarca($idm, $cm, $nc, $rz, $dir, $tel, $cont, $s, $p, $d){
        $this->query="UPDATE MARCAS SET NOMBRE_COMERCIAL = '$nc', RAZON_SOCIAL = '$rz', DIRECCION = '$dir', TELEFONO='$tel', CONTACTO='$cont', status ='$s', revision = '$p', dia_rev = '$d'  WHERE ID = $idm";
        $rs=$this->EjecutaQuerySimple();
        return;
    }

    function categoriaxMarca($idcat){
        $this->query="SELECT m.* FROM MARCAS m
                      INNER JOIN MARCAS_X_CATEGORIA mxc on mxc.idmarca = m.id
                      WHERE mxc.idcat = $idcat";
        $rs=$this->QueryObtieneDatosN();
        //echo $this->query;

        if(isset($rs)){
            while($tsarray=ibase_fetch_object($rs)){
            $data[]=$tsarray;
            }    
        }else{
            $data='N';
        }
        return @$data;
    }

    function verCategoria($idcat){
        $this->query = "SELECT * FROM CATEGORIAS WHERE ID = $idcat";
        $rs=$this->QueryObtieneDatosN();
        while($tsarray=ibase_fetch_object($rs)){
            $data[]=$tsarray;
        }
        return $data;
    }

    function buscaMarca($marca, $idcat){
        $this->query="SELECT m.*, mxc.idcat FROM MARCAS m
                      left join MARCAS_X_CATEGORIA mxc on mxc.idmarca = m.id and (mxc.idcat = $idcat or mxc.idcat is null)
                      WHERE upper(m.CLAVE_MARCA) containing upper('$marca') or upper(m.NOMBRE_COMERCIAL) containing upper('$marca')
                      ";
        $rs =$this->QueryObtieneDatosN();

        while($tsarray = ibase_fetch_object($rs)){
            $data[]=$tsarray;
        }
        //echo $this->query; 
        return @$data;
    }

    function asignarMarca($idcat, $idmca){
        $usuario =$_SESSION['user']->NOMBRE;
        $this->query = "INSERT INTO MARCAS_X_CATEGORIA VALUES(NULL, $idcat, $idmca, current_timestamp, '$usuario', null )";
        $rs=$this->EjecutaQuerySimple();
        echo $this->query;
        //break;
        return;
    }

    function desasignarMarca($idcat, $idmca){
        $usuario =$_SESSION['user']->NOMBRE;
        $this->query = "DELETE FROM MARCAS_X_CATEGORIA WHERE idcat = $idcat and idmarca = $idmca";
        $rs=$this->EjecutaQuerySimple();
        return;   
    }

    function proveedorXproducto($ids, $idprov, $pieza, $empaque, $pxp, $empaque2, $pxp2, $urgencia, $entrega, $recoleccion, $efectivo, $cheque, $credito, $costo, $costo2){
        $usuario=$_SESSION['user']->NOMBRE;

        $this->query = "SELECT ID FROM FTC_ART_X_PROV WHERE idart = $ids and idprov = '$idprov'";
        $res = $this->QueryObtieneDatosN();
        $row = ibase_fetch_object($res);


        if($row == true){
            $this->query="UPDATE FTC_ART_X_PROV SET pieza = '$pieza', empaque ='$empaque', pza_x_empaque= $pxp, empaque_2='$empaque2', pza_x_empaque2 = $pxp2, urgencia = '$urgencia', entrega = '$entrega', recoge = '$recoleccion', efectivo ='$efectivo', cheque='$cheque', credito = '$credito', costo = $costo, costo2=$costo2 where idart = $ids and idprov = '$idprov'";
        $rs=$this->EjecutaQuerySimple(); 
        echo 'Actualiza: '.$this->query;
        }else{
            $this->query="INSERT INTO FTC_ART_X_PROV (ID, IDART, IDPROV, FECHA_ASOC, USUARIO, PIEZA, PIEZAS, EMPAQUE, PZA_X_EMPAQUE, EMPAQUE_2, PZA_X_EMPAQUE2, URGENCIA, ENTREGA, RECOGE, EFECTIVO, CHEQUE, CREDITO, costo , costo2) 
                VALUES (NULL, $ids, '$idprov', current_timestamp, '$usuario', '$pieza', 0, '$empaque', $pxp, '$empaque2', $pxp2, '$urgencia', '$entrega', '$recoleccion', '$efectivo', '$cheque', '$credito', 0, 0)";
        $rs=$this->EjecutaQuerySimple();
        echo 'Inserta: '.$this->query;            
        }
       // break;

        return;    
    } 

    function clienteXproducto($idclie, $ids, $sku, $skuFact, $listaCliente, $correo, $precio){
        $usuario = $_SESSION['user']->NOMBRE;

        $this->query ="SELECT ID FROM FTC_ART_X_CLIE WHERE idart = $ids and idclie = '$idclie'";
        $rs=$this->QueryObtieneDatosN();
        $row=ibase_fetch_object($rs);

        if($row == true){
            $this->query="UPDATE FTC_ART_X_CLIE SET sku = '$sku', sku_Factura = '$skuFact', lista_Cliente='$listaCliente', correo = '$correo', precio = $precio , ultima_modificacion = current_timestamp, usuario_modifica = '$usuario' where idclie = '$idclie' and idart = $ids ";
            $this->EjecutaQuerySimple();

        }else{
            $this->query="INSERT INTO FTC_ART_X_CLIE (ID, IDCLIE, IDART, SKU, SKU_FACTURA, CORREO, LISTA_CLIENTE, USUARIO, ASOCIACION, precio) 
                            VALUES (NULL, '$idclie', $ids, '$sku', '$skuFact',  '$correo', '$listaCliente', '$usuario', current_timestamp, $precio)";
            $rs=$this->EjecutaQuerySimple();
            //echo $this->query;
        }
        return; 
    }


    function parCotSMB($folio, $partida, $por2){
        $this->query="UPDATE FTC_COTIZACION_DETALLE SET MARGEN_BAJO = 'Si', MARGEN_MINIMO = 0, UTILIDAD = $por2 WHERE CDFOLIO = $folio and CVE_ART = $partida";
        $rs=$this->queryActualiza();
        return;
    }

    function verSMB(){
        $this->query="SELECT * 
                        FROM FTC_COTIZACION_DETALLE ftcc
                        LEFT JOIN FTC_COTIZACION ftc on ftc.CDFOLIO = ftcc.CDFOLIO
                        LEFT JOIN CLIE01 cl on trim(cl.clave) = trim(ftc.cve_cliente)
                        LEFT JOIN FTC_Articulos ftca on ftca.id = ftcc.cve_art
                        WHERE MARGEN_BAJO = 'Si' and MARGEN_MINIMO = 0";
        $rs=$this->QueryObtieneDatosN();

        while ($tsarray=ibase_fetch_object($rs)){
            $data[]=$tsarray;
        }
        return @$data;
    }

    function autMB($folio, $partida, $utilAuto, $precio){
        $this->query="UPDATE FTC_COTIZACION_DETALLE SET DBIMPPRE = $precio,  MARGEN_MINIMO = $utilAuto where CDFOLIO = $folio and CVE_ART = $partida";
        $rs=$this->queryActualiza();
        $this->query="SELECT * FROM FTC_COTIZACION_DETALLE WHERE CDFOLIO = $folio";
        $rs=$this->EjecutaQuerySimple();
                while($tsarray=ibase_fetch_object($rs)){
                    $data[]=$tsarray;
                }
                $subtotal = 0;
                $iva = 0;
                $total = 0;
                $descuento = 0;
                $costo = 0;
                $i = 0;
                foreach ($data as $key) {
                    $i = $i +1;
                    $subtotal = $subtotal + ($key->DBIMPPRE * $key->FLCANTID);
                    $descuento = $descuento + ($key->DBIMPPRE * (($key->DBIMPDES/100) * $key->FLCANTID));
                    $costo = $costo + $key->DBIMPCOS;
                }
                $iva = ($subtotal - $descuento ) *.16 ; 
                $total = ($subtotal - $descuento ) * 1.16;
                $this->query="UPDATE FTC_COTIZACION SET DBIMPSUB = $subtotal, DBIMPDES = $descuento, DBIMPIMP = $iva, DBIMPTOT = $total where CDFOLIO = $folio";
                $this->EjecutaQuerySimple();

        //echo $this->query;
        return;
    }

    function marcarUrgente($folio){
        $this->query="UPDATE FTC_COTIZACION SET URGENTE = 'Si' where cdfolio = $folio ";
        $rs=$this->EjecutaQuerySimple();

        return;
    }

    function solLiberacion($folio, $cliente){
        $this->query="UPDATE FTC_COTIZACION SET INSTATUS='LIBERACION' WHERE CDFOLIO = $folio";
        $rs =$this->EjecutaQuerySimple();

        return;
    }

    function verSKUS($cliente, $cdfolio){
        $this->query="SELECT ftcd.cdfolio AS FOLIO, cl.nombre, pftc.nombre as descripcion, cl.clave as cliente, 
                iif(icl.camplib2 is null or icl.camplib2 = '', (select sku from lista_de_precios where trim(cliente) = '$cliente' and producto = ('PGS'||ftcd.cve_art)), icl.camplib2 ) AS SKU, 
                    ftcd.cve_art, (ftcc.serie||ftcc.folio) as cotiza,
                    (select first 1 sku_cliente from lista_de_precios where trim(cliente) = '$cliente' and producto = ('PGS'||ftcd.cve_art)) as SKU_CLIENTE, 
                    (select  first 1 sku_otro from lista_de_precios where trim(cliente) = '$cliente' and producto = ('PGS'||ftcd.cve_art)) as SKU_OTRO
                 FROM FTC_COTIZACION_DETALLE ftcd
                 left join inve_clib01 icl on icl.cve_prod = ('PGS'||ftcd.cve_art)
                 left join producto_ftc pftc on pftc.clave_ftc = ftcd.cve_art
                 left join ftc_cotizacion ftcc on ftcc.cdfolio = $cdfolio
                 left join clie01 cl on trim(cl.clave) = trim(ftcc.cve_cliente)
                  WHERE ftcd.CDFOLIO = $cdfolio";
        $rs=$this->EjecutaQuerySimple();

        //echo $this->query;
        while($tsarray=ibase_fetch_object($rs)){
            $data[]=$tsarray;
        }
        return $data;
    }

    function guardaSKU($producto, $sku, $cliente, $cdfolio, $nombre, $descripcion, $cotizacion, $sku_cliente, $sku_otro){
        
        $usuario=$_SESSION['user']->NOMBRE;

        $this->query="SELECT iif(count(cve_prod) =0 , 'No', 'Si') as VALIDACION from inve_clib01 where cve_prod = '$producto'";
        $rs=$this->EjecutaQuerySimple();
        $row = ibase_fetch_object($rs);
        $existe = $row->VALIDACION;

        if($existe == 'Si'){

            $this->query="SELECT ADDENDAF FROM CLIE01 WHERE CLAVE = '$cliente'";
            $rs=$this->EjecutaQuerySimple();
            $row=ibase_fetch_object($rs);

            
            if($row->ADDENDAF <> '' OR !empty($row->ADDENDAF)){     
                $this->query="UPDATE inve_clib01 SET camplib2='$sku' where cve_prod = '$producto'";
                $rs=$this->EjecutaQuerySimple();
            }

        }elseif($existe == 'No'){
            $this->query="INSERT INTO inve_clib01 (CVE_PROD, CAMPLIB2, CAMPLIB7) 
                                    VALUES('$producto', '$sku',substring((select nombre from producto_ftc where clave = '$producto') from 1 for 35))";
            $this->EjecutaQuerySimple();
            //echo $this->query;
        }

        $this->query="SELECT count(id) as actualiza from lista_de_precios where producto = '$producto' and cotizacion = '$cotizacion'";
        $rs=$this->EjecutaQuerySimple();
        $row=ibase_fetch_object($rs);

            if($row->ACTUALIZA  == 0){    
                    $this->query="INSERT INTO LISTA_DE_PRECIOS (ID, CLIENTE, NOMBRE, PRODUCTO, DESCRIPCION, COTIZACION, USUARIO, FECHA, sku, sku_cliente, sku_otro)
                                        VALUES (NULL, '$cliente', '$nombre', '$producto', '$descripcion', '$cotizacion', '$usuario', current_timestamp, '$sku', '$sku_cliente', '$sku_otro' )";
                    $this->EjecutaQuerySimple();
            }else{
                    $this->query="UPDATE LISTA_DE_PRECIOS SET SKU = '$sku', SKU_CLIENTE='$sku_cliente', SKU_OTRO = '$sku_otro' where producto = '$producto' and cotizacion = '$cotizacion'";
                    $this->EjecutaQuerySimple();
            }

        return;
    }

    function copiarCotizacion($cotizacion){
        $serie = substr($cotizacion, 0,1);
        $folio = substr($cotizacion, 1,10);
        $response = array('status'=>'no','numeroCliente'=>'no','cliente'=>'no', 'monto'=>'no', 'fecha'=>'no');
        $this->query="SELECT ftc.*, cl.nombre as cliente FROM FTC_COTIZACION ftc left join clie01 cl on trim(cl.clave) = trim(ftc.cve_cliente) WHERE folio = $folio and serie = '$serie' ";
        $rs=$this->EjecutaQuerySimple();
        $row = ibase_fetch_object($rs);

        if(!empty($row)){
            $nro_cliente = $row->CVE_CLIENTE;
            $cliente = $row->CLIENTE;
            $fecha = $row->DTFECREG;
            $monto = $row->DBIMPTOT;
          $response = array('status'=>'ok', 'numeroCliente'=>$nro_cliente, 'cliente'=>$cliente, 'monto'=>$monto, 'fecha'=>$fecha);
        }
         return $response;
    }

    function copiar($cotizacion){
        $response= array('status'=>'error', 'nueva'=>'error', 'productos'=>0);
        $serie = substr($cotizacion, 0,1);
        $folio = substr($cotizacion, 1, 10);
        $usuario = $_SESSION['user']->NOMBRE;
        $cdusuari = $_SESSION['user']->USER_LOGIN;
        $this->query="SELECT * FROM FTC_COTIZACION WHERE FOLIO = $folio and serie = '$serie'";
        $rs=$this->EjecutaQuerySimple();
        $row = ibase_fetch_object($rs);
        //echo 'Seleccion de cotizacion'.$this->query.'<p>';
        $this->query="SELECT MAX(CDFOLIO) AS folioa FROM FTC_COTIZACION";
        $res=$this->EjecutaQuerySimple();
        $row2 = ibase_fetch_object($res);
        $folion = $row2->FOLIOA + 1;
        //echo 'Seleccion de nuevo folio'.$this->query.'<p>';
        $this->query="SELECT LETRA_NUEVA FROM PG_USERS WHERE NOMBRE = '$usuario'";
        $resp=$this->EjecutaQuerySimple();
        $row3 = ibase_fetch_object($resp);
        $serieletra = $row3->LETRA_NUEVA;
        //echo 'Seleccion de letra'.$this->query.'<p>';
        $this->query="SELECT MAX(FOLIO) AS FOLIO FROM FTC_COTIZACION WHERE SERIE = '$row3->LETRA_NUEVA'";
        $respuesta = $this->EjecutaQuerySimple();
        $row4 = ibase_fetch_object($respuesta);
        $folioletra = $row4->FOLIO + 1;
        //echo 'Seleccion de nuevo folio por letra'.$this->query.'<p>';
        if(empty($row->DBIMPDES)){
            $dbimpdes = 0;
        }else{
            $dbimpdes = $row->DBIMPDES;
        }
        $this->query="INSERT INTO FTC_COTIZACION VALUES($folion, '$row->CVE_CLIENTE', '$row->DSIDEDOC', '$row->IDPEDIDO', current_date, '$cdusuari', '$row->DSPLANTA', $dbimpdes,
        '$row->DSENTREG', 'PENDIENTE', $row->DBIMPSUB, $row->DBIMPIMP, $row->DBIMPTOT, ('$serieletra'||$folioletra), null, $folioletra, '$serieletra', 'No', null, null, (select trim(rfc) from clie01 where trim(clave) = trim('$row->CVE_CLIENTE')))";
        $respuesta1=$this->grabaBD();
        //echo 'inserta cotizacion '.$this->query.'<p>';
        $i= 0;
        if($respuesta1){
                $this->query="SELECT * FROM FTC_COTIZACION_DETALLE WHERE cdfolio = $row->CDFOLIO";
                $result = $this->EjecutaQuerySimple();

            //echo 'Obtenemos las partidas'.$this->query.'<p>';

                while ($tsarray=ibase_fetch_object($result)){
                    $data[]=$tsarray;
                }

                foreach ($data as $key){
                    $i=$i+1;
                    $this->query="INSERT INTO FTC_COTIZACION_DETALLE VALUES($folion, '$key->CVE_ART', $key->FLCANTID, $key->DBIMPPRE, $key->DBIMPCOS, $key->DBIMPDES, null, null,null)";
                    $this->grabaBD();
                }
            $response=array('status'=>'ok', 'nueva'=>$serieletra.$folioletra, 'productos'=>$i);

        }
        return $response;
    }


    function guardaPartida($producto, $cotizacion, $tipo, $cantidad, $precio, $descuento, $mb, $mm, $costo){
        $response  = array('status'=>'no');

        if($tipo == 'g'){
            $this->query="SELECT * FROM producto_ftc WHERE CLAVE_FTC = '$producto' and status = 'A'";
            $rs=$this->EjecutaQuerySimple();
            $row= ibase_fetch_object($rs);

            if($row){
                $this->query="INSERT INTO FTC_COTIZACION_DETALLE VALUES($cotizacion, $producto, $cantidad, $precio, $costo, $descuento, $mb, $mm, 0  )";
                $this->EjecutaQuerySimple();
                //echo $this->query;
                $response=array('status'=>'ok');
            }    
        }elseif($tipo == 'e'){
            $this->query="DELETE FROM FTC_COTIZACION_DETALLE WHERE CDFOLIO = $cotizacion and cve_art = $producto";
            $res=$this->EjecutaQuerySimple();
            //echo $this->query;
            if($res){
                $response=array('status'=>'ok');
            }
        }elseif ($tipo == 'gd'){

            $prod = explode(':', $producto);
            $producto = trim($prod[0]);
            $this->query="SELECT * FROM FTC_Articulos WHERE ('PGS'||ID) = '$producto'";    
            $rs=$this->EjecutaQuerySimple();
            $row = ibase_fetch_object($rs);
        
            $precio = $row->COSTO * 1.23;
            $costo = $row->COSTO;

            $this->query="INSERT INTO FTC_COTIZACION_DETALLE VALUES($cotizacion, substring('$producto' from 4 for 10 ) , 1, $precio, $costo, 0, 0, 0, 0)";
            $res=$this->EjecutaQuerySimple();

            if($res){
                $response = array('status'=>'ok');
            }
        }
        
        $this->query="SELECT * FROM FTC_COTIZACION_DETALLE WHERE CDFOLIO = $cotizacion";
        $rs=$this->EjecutaQuerySimple();
                while($tsarray=ibase_fetch_object($rs)){
                    $data[]=$tsarray;
                }
                $subtotal = 0;
                $iva = 0;
                $total = 0;
                $descuento = 0;
                $costo = 0;
                $i = 0;
                foreach ($data as $key) {
                    $i = $i +1;
                    $subtotal = $subtotal + ($key->DBIMPPRE * $key->FLCANTID);
                    $descuento = $descuento + ($key->DBIMPPRE * (($key->DBIMPDES/100) * $key->FLCANTID));
                    $costo = $costo + $key->DBIMPCOS;
                }
                $iva = ($subtotal - $descuento ) *.16 ; 
                $total = ($subtotal - $descuento ) * 1.16;
                $this->query="UPDATE FTC_COTIZACION SET DBIMPSUB = $subtotal, DBIMPDES = $descuento, DBIMPIMP = $iva, DBIMPTOT = $total where CDFOLIO = $cotizacion";
                $this->EjecutaQuerySimple();
                //echo 'Partidas contabilizadas: '.$i.'<p> Consulra de Actualizacion: '.$this->query;
        return $response;
    }

    function solicitarMargenBajo($cotizacion, $partida){
        $this->query="SELECT F.*, pftc.nombre, ftcc.cve_cliente, ftcc.idpedido, cl.nombre as cliente, pftc.COSTO_VENTAS
                            FROM FTC_COTIZACION_DETALLE F
                            left join PRODUCTO_FTC pftc on pftc.clave_ftc = $partida 
                            left join FTC_COTIZACION ftcc on ftcc.CDFOLIO = $cotizacion
                            left join clie01 cl on cl.clave  = ftcc.cve_cliente
                            WHERE F.CDFOLIO = '$cotizacion' and F.cve_art = $partida";
        $rs=$this->EjecutaQuerySimple();
        //echo $this->query;
        while($tsArray = ibase_fetch_object($rs)){
            $data[]=$tsArray;
        }
        return $data;
    }

    function cajas($tipo, $var, $mes, $anio){
        //exit($tipo);
        $data=array();
        $datos = array();
        if($tipo == 1){
            $usuario=$_SESSION['user']->USER_LOGIN;
            $this->query="SELECT count(cotiza), extract(month from fechasol) as mes,
                        max(extract(year from fechasol)) as anio
                        from preoc01
                        where fechasol >= '01.08.2017'
                        and status <> 'P' 
                        and status <> 'R'
                        and cotiza starting 
                            with (select letra_nueva from pg_users where  USER_LOGIN='$usuario')
                        group by
                            extract(month from fechasol),
                            extract(year from fechasol)
                        order by
                             extract(year from fechasol) asc,
                            extract(month from fechasol) asc";
            $rs=$this->EjecutaQuerySimple();
            while ($tsarray=ibase_fetch_object($rs)) {
            $data[]=$tsarray;
            }
            foreach ($data as $key) {
                $this->query="SELECT cotiza
                            FROM PREOC01
                            WHERE
                            EXTRACT(MONTH FROM FECHASOL)= $key->MES
                            AND EXTRACT(YEAR FROM FECHASOL)= $key->ANIO
                            and cotiza starting with (select letra_nueva from pg_users where USER_LOGIN='$usuario')
                            and status <> 'P'
                            and status <> 'R'
                            group by cotiza";
                $rs=$this->EjecutaQuerySimple();
                while ($tsarray=ibase_fetch_object($rs)) {
                    $data2[]=$tsarray;
                }

            //// ananlisis de cajas inconclusas.
                     $this->query="SELECT cotiza, extract(month from fechasol) as mes
                        from preoc01
                        where extract(month from fechasol) = $key->MES
                        and extract(year from fechasol)=$key->ANIO
                        and cotiza starting with (select letra_nueva from pg_users where  USER_LOGIN='$usuario')
                        and status <> 'R'
                        and status <> 'P'
                        group by
                            cotiza,
                            extract(month from fechasol)
                        order by
                            extract(month from fechasol)";
                        $rs=$this->EjecutaQuerySimple();

                        while ($tsarray=ibase_fetch_object($rs)) {
                            $data3[]=$tsarray;
                        }
                        $pendiente = 0;
                        $subtotal = 0;
                        foreach ($data3 as $key2){
                                $cotizacion = $key2->COTIZA;
                                $this->query="SELECT sum(cant_orig) as Original, sum(recepcion) as recibido, sum(empacado) as empacado, datediff(day from max(fechasol) to current_date) as dias, max(par) as partidas, sum(total) as subtotal
                                    from preoc01  
                                    where cotiza ='$key2->COTIZA'";
                                    //echo $this->query.'<br/><br/>';
                                    $res=$this->EjecutaQuerySimple();
                                    $row3=ibase_fetch_object($res);

                                    $original = $row3->ORIGINAL;
                                    $recibido = $row3->RECIBIDO;
                                    $dias = $row3->DIAS;
                                    $st = $row3->SUBTOTAL;

                                    $subtotal = $subtotal + $st;
                                    //echo 'Original'.$original.' Recibido: '.$recibido.'<br/><br/>';
                                    if($original > $recibido){
                                        //echo 'Entro a la suma.<br/><br/>';
                                        $pendiente = $pendiente + 1;
                                    }
                                //echo 'Pendiente: '.$pendiente.'<br/><br/>';
                        }
                        
                $datos[]= array("mes"=>$key->MES, "anio"=>$key->ANIO, "cajas"=>count($data2), "pendientes"=>$pendiente, "subtotal"=>$subtotal);   
                unset($data2);
                unset($data3);
            }
            return $datos;   
        }elseif($tipo == 3){
        
        $usuario=$_SESSION['user']->USER_LOGIN;
        $this->query="SELECT cotiza, extract(month from fechasol) as mes
                        from preoc01
                        where extract(month from fechasol) = $mes
                        and extract(year from fechasol)=$anio
                        and cotiza starting with (select letra_nueva from pg_users where  USER_LOGIN='$usuario')
                        and status <> 'R'
                        and status <> 'P'
                        group by
                            cotiza,
                            extract(month from fechasol),
                            extract(day from fechasol)
                        order by
                            extract(day from fechasol)";
        $rs=$this->EjecutaQuerySimple();

            while ($tsarray=ibase_fetch_object($rs)) {
                $data[]=$tsarray;
            }
            foreach ($data as $key) {
                        $cotizacion = $key->COTIZA;
                        $this->query="SELECT sum(cant_orig) as Original, sum(recepcion) as recibido, sum(empacado) as empacado, datediff(day from max(fechasol) to current_date) as dias, max(par) as partidas, ('('||max(clien)||') '||max(nom_cli)) as cliente, 
                            (select fecha_almacen from CAJAS_ALMACEN where  pedido ='$key->COTIZA') AS fechalib,
                            (select NOMBRE_ARCHIVO from CAJAS_ALMACEN where  pedido ='$key->COTIZA') AS archivo
                            from preoc01  
                            where cotiza ='$key->COTIZA'";
                            
                        $res=$this->EjecutaQuerySimple();
                        $row=ibase_fetch_object($res);

                    $datos[] = array("cotizacion"=> $key->COTIZA,"original"=>$row->ORIGINAL,"recepcion"=>$row->RECIBIDO, "empacado"=>$row->EMPACADO, "dias"=>$row->DIAS, "partidas"=>$row->PARTIDAS, "cliente"=>$row->CLIENTE, "fechalib"=>$row->FECHALIB, "archivo"=>$row->ARCHIVO);
                    }
            return $datos;        
        }elseif ($tipo == 2) {
            $usuario=$_SESSION['user']->USER_LOGIN;
            $this->query="SELECT cotiza--, extract(month from fechasol) as mes
                        from preoc01
                        where extract(month from fechasol) = $mes
                        and extract(year from fechasol)=$anio
                        --and cotiza starting with (select letra_nueva from pg_users where  USER_LOGIN='$usuario')
                        and status <> 'R'
                        and status <> 'P'
                        group by
                            cotiza,
                            --extract(month from fechasol),
                            extract(day from fechasol)
                        order by
                            extract(day from fechasol)";
        $rs=$this->EjecutaQuerySimple();

            while ($tsarray=ibase_fetch_object($rs)) {
                $data[]=$tsarray;
            }
            foreach ($data as $key) {
                        $cotizacion = $key->COTIZA;

                        $this->query="SELECT sum(cant_orig) as Original, sum(recepcion) as recibido, sum(empacado) as empacado, datediff(day from max(fechasol) to current_date) as dias, max(par) as partidas, ('('||max(clien)||') '||max(nom_cli)) as cliente, 
                            (select fecha_almacen from CAJAS_ALMACEN where  pedido ='$key->COTIZA') AS fechalib,
                            (select NOMBRE_ARCHIVO from CAJAS_ALMACEN where  pedido ='$key->COTIZA') AS archivo
                            from preoc01  
                            where cotiza ='$key->COTIZA'";
                            
                        $res=$this->EjecutaQuerySimple();
                        $row=ibase_fetch_object($res);
                        if($row->ORIGINAL <> $row->RECIBIDO){
                            $datos[] = array("cotizacion"=> $key->COTIZA,"original"=>$row->ORIGINAL,"recepcion"=>$row->RECIBIDO, "empacado"=>$row->EMPACADO, "dias"=>$row->DIAS, "partidas"=>$row->PARTIDAS, "cliente"=>$row->CLIENTE, "fechalib"=>$row->FECHALIB, "archivo"=>$row->ARCHIVO);

                        }

                    }
            return $datos;      

        }      
    }


    function detalleFaltante($docf){
        $histcompra=array();

        $this->query="SELECT * FROM PREOC01 WHERE cotiza = '$docf'";
        $rs=$this->EjecutaQuerySimple();
        while ($tsarray=ibase_fetch_object($rs)) {
            $data[]=$tsarray;
        }

        foreach ($data as $key) {
            $data2=array();
            $this->query="SELECT * FROM FTC_DETALLE_RECEPCIONES WHERE IDPREOC = $key->ID and cantidad_rec > 0";
            $res=$this->EjecutaQuerySimple();
            $prov=array();
            while ($tsArray2 =ibase_fetch_object($res)) {
                $data2[]=$tsArray2;
            }

                if(count($data2)){
                    foreach($data2 as $key2){
                    $prov[]=array("OC"=>$key2->ORDEN);
                    }    
                }
                
            unset($data2);
        }
        foreach ($data as $key2 ) {
            $data3=array();
            $this->query="SELECT first 3  o.oC, o.cve_prov,(select nombre from prov01 where clave = o.cve_prov), F.*, P.*  FROM FTC_DETALLE_RECEPCIONES F 
                            LEFT JOIN PREOC01 P ON P.ID = F.IDPREOC 
                            LEFT JOIN FTC_POC o on o.oc = F.ORDEN
                            WHERE P.PROD =  '$key2->PROD' and cantidad_rec > 0 
                            order by F.fecha asc";
            $res=$this->EjecutaQuerySimple();
            $prov2 = array();
            while($tsArray = ibase_fetch_object($res)){
                $data3[]=$tsArray;
            }
            foreach ($data3 as $key2 ) {
                echo "OC: ".$key2->ORDEN.' Proveedor ('.$key2->CVE_PROV.') '.$key2->NOMBRE.'  Producto: '.$key->PROD.' descripcion: '.$key2->NOMPROD.'<br/>';
                $histcompra[]=array("oc"=>$key2->ORDEN,"prod"=>$key->PROD,"descr"=>$key2->NOMPROD);
            }
            unset($data3);
            //echo $this->query.'<br/>';
        }
        //exit(var_dump($histcompra));
        return $data;
    }

    function recalcular($idpreoc, $tipo){
        $data= new idpegaso;
        $info = $data->revisaDuplicado($idpreoc);
        $usuario = $_SESSION['user']->NOMBRE;
        $nordenado = '';
        $nrecepcion= '';
        $nempacado = '';
        if( $info['ordenado'] > $info['original']){
            $nordenado = $info['original'];
        }
        if($info['recibido'] > $info['original']){
            $nrecepcion = $info['original'];
        }
        if($info['empacado'] > $info['original']){
            $nempacado = $info['original'];
        }
        if($tipo == 2 ){
                $id= $info['ID'];
                $ordenado =$info['ordenado'];
                $recepcion = $info['recibido'];
                $pendiente = $info['pendiente'];
                $empacado = $info['empacado'];
                $status = $info['status'];
                $empacado =$info['empacado'];

              $this->query="INSERT INTO FTC_ACT_PREOC (ID, IDPREOC, USUARIO, O_ORDENADO, N_ORDENADO, O_RECIBIDO, N_RECIBIDO, O_REST, N_REST,  O_EMPACADO, N_EMPACADO, FECHA_MOV, STATUS )
                                VALUES 
                                    ( null,
                                      $id,
                                      '$usuario',
                                      (SELECT ORDENADO FROM PREOC01 WHERE ID = $id),
                                      $ordenado,
                                      (SELECT RECEPCION FROM PREOC01 WHERE ID = $id),
                                      $recepcion,
                                      (SELECT REST FROM PREOC01 WHERE ID = $id),
                                      $pendiente,
                                      (SELECT EMPACADO FROM PREOC01 WHERE ID = $id),
                                      $empacado,
                                      current_timestamp,
                                      0
                                    )";
                                
              $ins= $this->grabaBD();
              if($ins){
                    $this->query="SELECT FECHASOL FROM PREOC01 WHERE ID = $id";
                    $rs=$this->EjecutaQuerySimple();
                    $row=ibase_fetch_object($rs);

                    if($row->FECHASOL >= '15.02.2018'){
                        $this->query="UPDATE PREOC01 SET ORDENADO = $ordenado, recepcion = $recepcion, rest = $pendiente, status='$status' where id = $id";
                        $rs=$this->queryActualiza();
                        if($rs == 1 ){
                            return $response=array("status"=>'Se Actualizo la informacion.');
                        }else{
                            return $response=array("status"=>'No se pudo actualizar, favor de intentarlo nuevamente.');
                        }    
                    }else{
                        $this->query="UPDATE PREOC01 SET ORDENADO = $ordenado, recepcion = $recepcion, rest = $pendiente, status='B' where id = $id";
                        $rs=$this->queryActualiza();
                        if($rs == 1 ){
                            return $response=array("status"=>'Se Actualizo la informacion, pero no se solicita nuevamente por fecha, si cree que es un error o requiere el material, revisarlo con Alejandro Perla');
                        }else{
                            return $response=array("status"=>'No se pudo actualizar, favor de intentarlo nuevamente.');
                        }
                    }          
              } 
        }


        return $response = array('ordenado'=>$info['ordenado'], 'recibido'=>$info['recibido'], 'pendiente'=>$info['pendiente'], 'status'=>$info['status'], 'empacado'=>$info['empacado']); 
        //$response = array('nordenado'=>$nordenado,'nrecepcion'=>$nrecepcion,'nempacado'=>$nempacado);
    }


    function validacion($folio){
        $this->query="SELECT * FROM FTC_COTIZACION WHERE CDFOLIO = $folio";
        $rs=$this->EjecutaQuerySimple();
        $row=ibase_fetch_object($rs);
        //$status2=$row->INSTATUS;
        return($row->INSTATUS);
    }

    function cancelar($docf, $uuid, $mot, $uuidSust){
        $usuario= $_SESSION['user']->NOMBRE;
        if($mot == '01'){
            $this->query = "SELECT count(*) AS ID, MAX(UUID) as UUID FROM XML_DATA WHERE uuid = '$uuidSust'";
            $res=$this->EjecutaQuerySimple(); 
            $rowS = ibase_fetch_object($res);
            if($rowS->ID==0){
                return array("status"=>'no', "motivo"=>'No se encontro el UUID Sustituto, favor de revisar la información.');
            }else{
                $uuidSust = $rowS->UUID;
            }
        }
        $this->query = "SELECT first 1 X.*, iif(f.status is null, 5, f.status) as factstat FROM XML_DATA X 
                        left join ftc_facturas f on f.serie = X.serie and f.folio = X.folio 
                        WHERE X.UUID = '$uuid' and X.serie||X.folio='$docf'";
        $rs=$this->EjecutaQuerySimple();
        $row=ibase_fetch_object($rs);
        if($row->ID > 0 ){
            if($row->FACTSTAT == 8 || $row->FACTSTAT==5){
                return array("status"=>"No","motivo"=>"La factura ya ha sido cancelada con anterioridad o esta intentando cancelar una Nota de Credito, favor de actualizar o revisar la informacion");
            }
            #### Metodo Antiguo de cancelacion 
                $path = "C:\\xampp\\htdocs\\facturas\\Cancelaciones\\";
                if(!file_exists($path)){
                    mkdir($path);
                }
                $csv=fopen('C:\\xampp\\htdocs\\facturas\\Cancelaciones\\'.$docf.'-C'.'.csv','w');
                $datos = array(
                    array('uuid', 'serie', 'folio', 'esNomina'),
                    array($uuid,$row->SERIE,$row->FOLIO,"")
                    );
                if($csv){
                    foreach ($datos as $ln ) {
                        fputcsv($csv, $ln);
                    }
                }
                fclose($csv);
            #### Finaliza el metodo antiguo de cancelacion
            $this->query="SELECT * FROM ftc_empresas WHERE ID = 1";
            $res=$this->EjecutaQuerySimple();
            $rowDF=ibase_fetch_object($res);
            if($mot == '01'){
                $cancelaciones[] = array(
                                        "Motivo"=>$mot,
                                        "uuid"=>$row->UUID,
                                        "FolioSustitucion"=>"$uuidSust"
                                    ); 
            }else{
                $cancelaciones[] = array(
                                        "UUID"=>$row->UUID,
                                        "Motivo"=>$mot
                                    ); 
            }

            $params= array("user"=>'administrador',
                           "pass"=>$rowDF->CONTRASENIA,
                           "RFC"=>strtolower($rowDF->RFC),
                           "folios"=>$cancelaciones
                            );

            $cancela = array(   "id"=>0,
                                "method"=>'cancelarCFDI',
                                "params"=>$params
                            );

            $nf = $row->DOCUMENTO;
            $file = json_encode($cancela,JSON_UNESCAPED_UNICODE);
            $path = "C:\\xampp\\htdocs\\Facturas\\EntradaJson\\";
            file_exists($path)? '':mkdir($path);
            $fh = fopen($path.'Cancela'.$nf.".json", 'w');
            fwrite($fh, $file);
            fclose($fh);
            $this->query="UPDATE FTC_FACTURAS SET STATUS = 8, SALDO_FINAL = 0, fecha_cancelacion = current_timestamp, usuario_cancelacion = '$usuario' WHERE DOCUMENTO = '$docf'";
            $rs=$this->EjecutaQuerySimple();
            ### Nuevo metodo para la cancelacion
            $this->query="UPDATE FTC_FACTURAS_DETALLE SET STATUS = 8 WHERE DOCUMENTO = '$docf'";
            $this->grabaBD();
            $this->afectaNV($docf);
            ### Finaliza nuevo metodo.
            $this->query="INSERT INTO XML_DATA_CANCELADOS (ID, SERIE, FOLIO, UUID, STATUS, TIPO, FECHA_CANCELACION, USUARIO_CANCELACION ) 
                                    VALUES (NULL,'$row->SERIE', '$row->FOLIO', '$uuid', 'C', 'C', current_timestamp, '$usuario')";
            $this->grabaBD();
            $this->query="UPDATE CAJAS SET status = 'cerrado',par_facturadas = 0, FACTURA='', REMISION=('PF'||id) where id = (SELECT IDCAJA FROM FTC_FACTURAS WHERE DOCUMENTO = '$docf' )";
            $this->EjecutaQuerySimple();
            return array("status"=>'ok', 'motivo'=>'Se ha solicitado la cancelacion de la Factura'.$docf);
        }
        return array("status"=>'No', "motivo"=>"No se encontro la informacion"); 
    }

    function afectaNV($docf){
        $this->query="UPDATE FTC_NV_DETALLE SET status = 0 where documento = (SELECT DOCUMENTO FROM FTC_NV WHERE METODO_PAGO = '$docf')";
        $this->grabaBD();

        $this->query="UPDATE FTC_NV SET METODO_PAGO = '', status='P' WHERE METODO_PAGO= '$docf'";
        $this->grabaBD();
    }

    function informacionFactura($docf){
        $this->query="SELECT * FROM FACTURAS_CANCELADAS_FP WHERE CVE_DOC = '$docf'";
        $rs=$this->EjecutaQuerySimple();
        while ($tsarray=ibase_fetch_object($rs)) {
            $data[]=$tsarray;
        }
        return $data;
    }

    function procesaCancelado($docf, $uuid){
        sleep(3);
        if(file_exists("C:\\xampp\\htdocs\\Facturas\\originales\\".$docf."-C.csv")){
            //echo 'Se encontro el archivo';
            $factura = 'ok';
            $archivo = "C:\\xampp\\htdocs\\Facturas\\facturaPegaso\\".$docf."-C.xml";
            sleep(2);
            copy("C:\\xampp\\htdocs\\Facturas\\FacturasJson\\".'Acuse de Cancelacion ('.$docf."-C.csv).xml", "C:\\xampp\\htdocs\\Facturas\\facturaPegaso\\".$docf."-C.xml");
            $mensaje='Si la timbro';
            $data = new pegaso;
            $exec=$data->insertarArchivoXMLCargado($archivo, $tipo='C');
        
        }else{
            echo 'No se encontro el archivo';
        }
        return; 
    }

    function traePendientes($prod){
        $data=array();
        $prod = explode(":", $prod);
        $producto = $prod[0];
        $this->query="SELECT * FROM PREOC01 WHERE STATUS ='N' AND rec_faltante > 0 AND PROD = trim(UPPER('$producto')) and rest >=1 ";
        $res=$this->EjecutaQuerySimple();
        while ($tsarray=ibase_fetch_object($res)) {
            $data[]=$tsarray;
        }
        if($data){
            foreach ($data as $key){
            $response[]=array("status"=>'ok',"cotizacion"=>$key->COTIZA, "id"=>$key->ID, "faltante"=>$key->REC_FALTANTE, "original"=>$key->CANT_ORIG, "cliente"=>utf8_encode($key->NOM_CLI));
            } 
        }else{
            $response = array("status"=>'no');
        }
        return $response;
    }

    function actPartida($docf, $cantidad, $precio, $descuento, $partida, $uso, $mp, $fp, $clie){
        $fiscales=$this->actualizaFiscalesDirecta($docf, $uso, $mp, $fp, $clie);
        if($precio == "" and $cantidad == "" and $descuento ==""){
            return array("status"=>'no', "mensaje"=>'No se detecto ningun cambio en la partida '.$partida);
        }else{
            if($cantidad != ""){
                $this->query="UPDATE FTC_FACTURAS_DETALLE SET cantidad = $cantidad where documento = '$docf' and partida = $partida";
                $this->queryActualiza();
            }
            if($precio != ""){
                $this->query="UPDATE FTC_FACTURAS_DETALLE SET precio = $precio where documento = '$docf' and partida = $partida";
                $this->queryActualiza(); 
            }
            if($descuento != "" ){
                $this->query="UPDATE FTC_FACTURAS_DETALLE SET desc1 = $descuento where documento = '$docf' and partida = $partida";
                $this->queryActualiza();    
            }
            $total=$this->actualizaTotalesDocf($docf);
            return array("status"=>"OK", "mensaje"=>"Se actualizo la partida".$partida);
        }
    }

    function actualizaTotalesDocf($docf){
        $sdesc = 0;
        $st = 0; 
        $this->query="SELECT * FROM FTC_FACTURAS_DETALLE WHERE DOCUMENTO ='$docf'";
        $res=$this->EjecutaQuerySimple();
        while ($tsArray=ibase_fetch_object($res)) {
            $data[]=$tsArray;
        }
        foreach ($data as $key) {
            $partida = $key->PARTIDA;
            $cant= $key->CANTIDAD;
            $prec = $key->PRECIO;
            $desc = $key->DESC1;
            $subtotal = ($cant *  $prec) - $desc;
            $st += $subtotal;
            $sdesc += $desc;
            $this->query="UPDATE FTC_FACTURAS_DETALLE SET subtotal = $subtotal, imp1 = $subtotal*0.16, total= $subtotal*1.16 where documento ='$docf' and partida = $partida"; 
            $this->queryActualiza();
        }
        $this->query="UPDATE FTC_FACTURAS SET desc1 = $sdesc, SUBTOTAL = $st, iva =$st *0.16, total = $st*1.16, SALDO_FINAL=$st*1.16 where documento = '$docf'";
        $this->queryActualiza();
        return $st*1.16;
    }

    function actualizaFiscalesDirecta($docf, $uso, $mp, $fp, $clie){
        $this->query="UPDATE FTC_FACTURAS SET USO_CFDI = '$uso', METODO_PAGO = '$mp', FORMADEPAGOSAT = '$fp', CLIENTE = '$clie' where documento = '$docf'";
        $this->queryActualiza();
        return;
    }

    function verPagos(){
        $data=array();
        $this->query="SELECT C.*, CD.*, (SELECT IMPORTE FROM FACTF01 WHERE CVE_DOC = CD.NO_FACTURA) AS IMPORTE_DOC,
    (SELECT CPT.DESCR FROM conc01 CPT WHERE CPT.NUM_CPTO = CD.NUM_CPTO ) AS nom_cpto,
    (SELECT CPT.FORMADEPAGOSAT FROM conc01 CPT WHERE CPT.NUM_CPTO = CD.NUM_CPTO ) AS TIPO_SAT
FROM CUEN_DET01 CD LEFT JOIN CLIE01 C ON C.CLAVE = CD.CVE_CLIE
WHERE CVE_DOC_COMPPAGO IS NULL AND (NUM_CPTO = 22 OR NUM_CPTO = 11 OR NUM_CPTO =10)";
        $res=$this->EjecutaQuerySimple();
        while ($tsarray=ibase_fetch_object($res)) {
            $data[]=$tsarray;
        }

        /*
        foreach ($data as $d) {
            $folioBanco=0;
            $usuario=$_SESSION['user']->NOMBRE;
            $rfc = 'XXX000000XXX';
            $this->query="INSERT INTO CARGA_PAGOS (ID,CLIENTE,FECHA,MONTO,SALDO,USUARIO,BANCO,FECHA_APLI,FECHA_RECEP,FOLIO_X_BANCO,RFC,STATUS,CF,FOLIO_ACREEDOR,TIPO_PAGO,REGISTRO,CONTABILIZADO,POLIZA_INGRESO,APLICACIONES,MONTO_ACREEDOR,MONTO_CF,CVE_MAESTRO,CIERRE_CONTA,USUARIO_CONTA,FECHA_CONTA,SELECCIONADO,GUARDADO,ARCHIVO,CEP,ARCHIVO_CEP) VALUES (NULL, '$d->CVE_CLIE', '$d->FECHAELAB', '$d->IMPMON_EXT', '$d->IMPMON_EXT', '$usuario','banco','$d->FECHA_APLI','$d->FECHA_APLI', '$d->FECHA_APLI',1, )";
        }
        */
        return $data;
    }

    function realizaCEP($folios){
        $folios = explode(",", $folios);
        $docs= "";
        for ($i=0; $i < count($folios); $i++) { 
            $doc = $folios[$i];
            $docs = $docs.",'".$doc."'";
            //echo $doc.'<br/>';
        }
        $docs = substr($docs, 1);
        $this->query="SELECT cve_clie, refer, FECHAELAB as fecha_elab, max(FECHA_APLI) as fecha_apli, sum(IMPORTE) AS monto, NUM_CPTO FROM CUEN_DET01 WHERE NO_FACTURA IN ($docs) group by cve_clie, refer, FECHAELAB, NUM_CPTO";
        $res=$this->EjecutaQuerySimple();
        while ($tsarray=ibase_fetch_object($res)) {
            $data[]=$tsarray;
        }
        foreach ($data as $k) {
            $this->realizaJson($k);
        }
    }

    function realizaJson($k){
        echo('PRIMER CLIENTE: '.$k->CVE_CLIE.' REFERENCIA: '.$k->REFER.' MONTO: '.$k->MONTO.' fecha elab: '.$k->FECHA_ELAB.' FECHA APLI:'.$k->FECHA_APLI.'<br/>');
        /// Json
        $conceptos = array(
                "ClaveProdServ"=>"84111506",
                "ClaveUnidad"=>"ACT",
                "Importe"=>"0",
                "Cantidad"=>"1",
                "descripcion"=>"Pago",
                "ValorUnitario"=>"0"
            );

        $this->query="SELECT * FROM CONC01 WHERE NUM_CPTO = $k->NUM_CPTO"; /// revisado
        //echo '<br/>'.$this->query;
        $res=$this->EjecutaQuerySimple();
        $rowCpto=ibase_fetch_object($res);

        $DocsRelacionados=array();

        $this->query="SELECT CU.*, F.*,(SELECT IMPORTE FROM FACTF01 WHERE CVE_DOC = CU.NO_FACTURA) AS IMPFACT, (SELECT UUID FROM CFDI01 WHERE CVE_DOC = CU.NO_FACTURA) AS UUIDF 
                FROM CUEN_DET01 CU LEFT JOIN FACTF01 F ON CU.NO_FACTURA = F.CVE_DOC
                     WHERE TRIM(CU.CVE_CLIE) = TRIM('$k->CVE_CLIE') AND CU.REFER='$k->REFER' AND CU.FECHA_APLI= '$k->FECHA_APLI' AND CU.CVE_DOC_COMPPAGO IS NULL"; /// REVISADO
        $res=$this->EjecutaQuerySimple();
        while ($tsarray=ibase_fetch_object($res)) {
            $dataDocs[]=$tsarray;
        }

            foreach ($dataDocs as $docu) {
                $saldoInsoluto = $docu->IMPFACT-$docu->IMPORTE;
                $documento = array (
                        "IdDocumento"=>$docu->UUIDF,
                        "Serie"=>"$docu->SERIE",
                        "Folio"=>"$docu->FOLIO",
                        "MonedaDR"=>"MXN",
                        "MetodoDePagoDR"=>"PPD",
                        "NumParcialidad"=>"1",
                        "ImpSaldoAnt"=>"$docu->IMPORTE",
                        "ImpPagado"=>"$docu->IMPFACT",
                        "ImpSaldoInsoluto"=>"$saldoInsoluto"
                    );    
                $DocsRelacionados[]=$documento;
            }
                    
                $aplica= array(
                    "FechaPago"=>substr($k->FECHA_APLI,0,10).'T 12:00:00',
                    "FormaDePagoP"=>"$rowCpto->FORMADEPAGOSAT",
                    "MonedaP"=>"MXN",
                    "Monto"=>"$k->MONTO",
                    "NumOperacion"=>"$k->REFER",
                    "DoctoRelacionado"=>$DocsRelacionados
                );

            $datosCEP[] = $aplica;

        $this->query="SELECT max(folio) as folio FROM FTC_CTRL_FACTURAS WHERE IDFF= 7 AND STATUS =1 AND SERIE = 'CEP'";
        $res=$this->EjecutaQuerySimple();
        $rowfolio=ibase_fetch_object($res);
        $folio=$rowfolio->FOLIO +1;
        $this->query="UPDATE FTC_CTRL_FACTURAS SET FOLIO = $folio WHERE IDFF = 7 AND STATUS =1 AND SERIE = 'CEP'";
        $this->queryActualiza();

        $datosFactura = array(
                "Serie"=>"CEP",
                "Folio"=>"$folio",
                "Version"=>"3.3",
                "RegimenFiscal"=>"601",
                "LugarExpedicion"=>"54080",
                "Moneda"=>"XXX",
                "TipoDeComprobante"=>"P",
                "numero_de_pago"=>"1",
                "cantidad_de_pagos"=>"1"
            );

        $this->query="SELECT * FROM CLIE01 WHERE TRIM(CLAVE) =  TRIM($k->CVE_CLIE)";
        $res=$this->EjecutaQuerySimple();
        $rowClie=ibase_fetch_object($res);

        $datosCliente = array(
                    "id"=>"$k->CVE_CLIE",
                    "nombre"=>$rowClie->NOMBRE,
                    "rfc"=>$rowClie->RFC,
                    "UsoCFDI"=>'P01'
                );

                $Complementos[] = array("Pagos"=>array("Pago"=>$datosCEP)); 
                $cep = array (
                    "id_transaccion"=>"0",
                    "cuenta"=>"faao790324e57",
                    "user"=>"administrador",
                    "password"=>"@1KRhz11",
                    "getPdf"=>true,
                    "conceptos"=>[$conceptos],
                    "datos_factura"=>$datosFactura,
                    "method"=>"nueva_factura",
                    "cliente"=>$datosCliente,
                    "Complementos"=>$Complementos
                );

            $location = "C:\\xampp\\htdocs\\Facturas\\generaJson\\";
            $json = json_encode($cep, JSON_UNESCAPED_UNICODE);
            $mysql = new pegaso;
            $mysql->query = "INSERT INTO FTC_CEP VALUES (";
            $mysql->query.= "$folio, '$json', 'P');";
            $mysql->grabaBD();
            $nameFile = "CEP_".$folio;      
            $theFile = fopen($location.$nameFile.".json", 'w');
            fwrite($theFile, $json);
            fclose($theFile);
    }

    function realizaNCBonificacion($docf, $monto, $concepto, $obs, $caja){
        $data=array();
        $folio = $this->creaFolioNCB();
        $docd = 'NCB'.$folio;
        $sub = $monto/1.16;
        $iva = $monto - ($monto /1.16);
        $imp = $monto;
        $usuario=$_SESSION['user']->NOMBRE;
        $caja = explode("-", $caja);
        $caja = $caja[0];
        $caja = strlen($caja)==0? 0:$caja;
        $this->query="EXECUTE PROCEDURE SP_NC_BONIFICACION ('$docd', '$folio', $sub, $iva, $imp, '$docf', '$usuario', $caja)";
        if($res=$this->EjecutaQuerySimple()){
            echo 'Revisa si hay una caja por liberar';
            $this->query="SELECT * FROM FTC_FACTURAS WHERE idcaja = $caja and SALDO_FINAL > 5";
            $result=$this->EjecutaQuerySimple();   
            while($tsArray=ibase_fetch_object($result)){
                   $data[]=$tsArray;
            }
            if(count($data)==0){
                $this->query="UPDATE CAJAS SET FACTURA = '', status='cerrado', remision = ('PF'||id) WHERE FACTURA ='$docf'";
                $this->EjecutaQuerySimple();
                $this->query="EXECUTE PROCEDURE SP_LIB_X_NC ($caja)";
                $res=$this->queryActualiza();    
            }
        }else{

            $this->query="UPDATE ftc_ctrl_facturas SET FOLIO = FOLIO -1 WHERE SERIE = 'NCB' AND STATUS = 1";
            $this->queryActualiza();
            return;
        }
        //echo $this->query;
        $fact = new factura;
        $json = $fact->timbraNC($docd, $caja);
        $moverNC = $fact->moverNCSUB($docd,$json);


        return;
    }

    function creaFolioNCB(){
        $this->query="SELECT FOLIO FROM FTC_CTRL_FACTURAS WHERE SERIE = 'NCB' AND STATUS = 1";
        $rs=$this->EjecutaQuerySimple();
        $row=ibase_fetch_object($rs);
        $this->query="UPDATE FTC_CTRL_FACTURAS SET FOLIO = ($row->FOLIO +1) WHERE SERIE='NCB' AND STATUS = 1 ";
        $this->queryActualiza();
        return $row->FOLIO+1;
    }

    function conteoCopias($docf){
        
        $this->query="SELECT COUNT(IDF) as ID FROM FTC_FACTURAS WHERE DOCUMENTO = '$docf'";
        $res=$this->EjecutaQuerySimple();
        $row1=ibase_fetch_object($res);
        if($row1->ID == 0){
            return array("status"=>"noExiste","copias"=>"No existe la factura");
        }

        $this->query="SELECT count(id) as copias, cast(list(R_F) as varchar(100)) as facturas  FROM REFACTURACION WHERE FACT_ORIGINAL='$docf' and tipo_solicitud='copia'";
        $res= $this->EjecutaQuerySimple();
        $row=ibase_fetch_object($res);
        if($row->COPIAS > 0){
            return array("status"=>'ok', "copias"=>$row->COPIAS, 'facturas'=>$row->FACTURAS);    
        }else{
            return array("status"=>'no', "copias"=>'0','facturas'=>'');
        }
    }

    function copiaFP($docf){
        $fact= new factura;

        $nuevoFolio=$fact->crearFoliosFactura();
        $copiaFactura=$fact->copiaFactura($idsol=999999,$nuevoFolio, $docf);
        return $copiaFactura;    
    }

    function cajaNC($idc){
        $this->query="SELECT C.*,
                            COALESCE((select SALDO_FINAL FROM FTC_FACTURAS WHERE DOCUMENTO = FACTURA), 
                                     (select SALDOFINAL FROM FACTF01 WHERE CVE_DOC = FACTURA), 
                                     9999
                            ) AS SF
                            FROM CAJAS  C WHERE ID = $idc";
        $res=$this->EjecutaQuerySimple();  
        $row=ibase_fetch_object($res);
        $sf=$row->SF;
        if($sf > 5){
                //$this->query="UPDATE CAJAS SET STATUS_LOG = 'NC', status_recepcion = iif(status_recepcion >=5, status_recepcion, 5) WHERE ID = $idc";
                //        $res=$this->queryActualiza();
                $this->query="EXECUTE PROCEDURE SP_LIB_X_NC ($idc)";
                $res=$this->EjecutaQuerySimple();
                if($res == 1){
                    return array("status"=>'ok');
                }else{
                    return array("status"=>'ok');
                }            
        }else{
            return array("status"=>'no', "mensaje"=>'El Saldo de la Factura'.$row->FACTURA.' asociada a la caja '.$idc.', es de $ '.number_format($sf,2).' la cual es menor a $ 5.00 pesos' );
        }
    }

    function verNCC($serie){
        $data = array();
        $this->query="SELECT nc.*, f.DOCUMENTO AS FACTURA, f.SALDO_FINAL AS SALDO_FACT, f.total as totalFactura, f.notas_credito as facturaNC, 
            f.status as facturaStatus, f.monto_nc, f.monto_pagos as pagos FROM FTC_NC nc left join ftc_facturas f on f.documento = nc.notas_credito WHERE nc.SERIE = '$serie'";
        $res=$this->EjecutaQuerySimple();
        while ($tsArray=ibase_fetch_object($res)) {
            $data[]=$tsArray;
        }
        return $data;
    }

    function aplicaNC($docn){
        $this->query="SELECT * FROM FTC_NC WHERE DOCUMENTO ='$docn'";
        $res=$this->EjecutaQuerySimple();
        $row=ibase_fetch_object($res);
        if($row){
            $this->query="SELECT * FROM FTC_FACTURAS WHERE DOCUMENTO = '$row->NOTAS_CREDITO'";
            $res=$this->EjecutaQuerySimple();
            $rowFact=ibase_fetch_object($res);
            if(((int)$rowFact->SALDO_FINAL - (int)$row->TOTAL) >= 0 and strpos($rowFact->NOTAS_CREDITO, $row->DOCUMENTO) === false) {
               // echo 'No la encontro y el Saldo restante es igual o mayor a cero';
                $this->query="UPDATE FTC_FACTURAS SET SALDO_FINAL = SALDO_FINAL - $row->TOTAL, notas_credito = iif(notas_credito = '' or notas_credito is null, '$docn', notas_credito||', '||'$docn'), monto_nc=monto_nc + $row->TOTAL WHERE DOCUMENTO= '$rowFact->DOCUMENTO'";
                $this->queryActualiza();
                return array("status"=>'ok', "mensaje"=>'Se ha aplicado la NC correctamente');
            }elseif(strpos($rowFact->NOTAS_CREDITO, $row->DOCUMENTO)){
                return array("status"=>'ok', "mensaje"=>'Encontro'.$row->DOCUMENTO.' en '.$rowFact->NOTAS_CREDITO.' y la posicion es'.strpos($rowFact->NOTAS_CREDITO, $row->DOCUMENTO));
            }elseif(($rowFact->SALDO_FINAL - $row->TOTAL) < 0){
                return array("status"=>'ok', "mensaje"=>'La aplicacion de la '.$row->DOCUMENTO.', crearia un saldo negativo en la factura, favor de revisar la informacion');
            }else{
                return array("status"=>'no',"mensaje"=>'No se pudo procesar, se realizo un ticket para su revision, se encontro la nota de credito '.$row->DOCUMENTO.', pero no se pudo procesar');
            }   
        }else{
            return array("status"=>'no',"mensaje"=>'No se pudo procesar, se realizo un ticket para su revision, no existe la nota de credito '.$row->DOCUMENTO);
        }
    }

    function verPartidas($idc){
        $data=array();
        $this->query="SELECT D.*, COALESCE((SELECT CAST(ANEXO_DESCRIPCION AS VARCHAR(1000)) FROM FTC_ANEXO_DESCR WHERE CAJA = $idc and Partida = D.PARTIDA AND STATUS IS NULL), '') AS ANEXO FROM DETALLE_CAJA D WHERE D.IDCAJA = $idc";
        $res=$this->EjecutaQuerySimple();
        while ($tsArray=ibase_fetch_object($res)) {
            $data[]=$tsArray;
        }
        return $data;
    }

    function verCabecera($idc){
        $data=array();
        $this->query="SELECT * FROM CABECERA_CAJA WHERE ID = $idc";
        $res=$this->EjecutaQuerySimple();
        while ($tsArray=ibase_fetch_object($res)) {
            $data[]=$tsArray;
        }
        return $data;
    }

    function anexoDescr($tipo, $idc, $par, $descr){
        $data=array();
        $descr=htmlentities($descr,ENT_QUOTES);
        if($tipo == 'a'){
            $this->query="SELECT * FROM FTC_ANEXO_DESCR WHERE caja = $idc and partida = $par and status is null ";
            $res=$this->EjecutaQuerySimple();
            while ($tsArray=ibase_fetch_object($res)) {
                $data[]=$tsArray;
            }
            if(count($data) > 0){
                $this->query="UPDATE FTC_ANEXO_DESCR SET ANEXO_DESCRIPCION = '$descr' where caja = $idc and partida = $par and status is null ";
                $this->queryActualiza();    
            }else{
                $this->query="INSERT INTO FTC_ANEXO_DESCR (ID , ANEXO_DESCRIPCION, PARTIDA, CAJA) VALUES (NULL, '$descr', $par, $idc)";
                $this->grabaBD();
            }    
        }else{
            $this->query="UPDATE FTC_ANEXO_DESCR SET STATUS= 9 WHERE caja = $idc and partida = $par and status is null";
            $this->queryActualiza();
            return array("status"=>'ok', "mensaje"=>'Se ha restaurado la descripción');
        }

        return array("status"=>'ok', "mensaje"=>'Se ha anexado Correctamente');
    }

    function repVentas($tipo, $clie, $inicio, $fin){
        $data = array();
        $fechas = '';
        if(!empty($inicio) and !empty($fin)){
            $fechas= " and fecha between ".$inicio." and ".$fin;
        }

        if($tipo == 'c'){
            $this->query="SELECT * FROM FTC_FACTURAS WHERE status != 9 $fechas ";
            $this->EjecutaQuerySimple();
        }
    }

    function cargaSae($doc, $folio, $serie, $uuid, $ruta, $rfcr, $tipo){
        $ruta2= "C:\\xampp\\htdocs\\uploads\\xml\\IMI161007SY7\\Emitidos\\".$rfcr."\\IMI161007SY7-".$doc.'-'.$uuid.".xml";
        if($tipo != 'P'){
            $myFile = fopen("$ruta2", "r") or die("No se ha logrado abrir el archivo ($ruta2)!");
            $myXMLData = fread($myFile, filesize($ruta2));
            $doc = $serie.$folio;
            $this->query="EXECUTE PROCEDURE SP_CARGA_CFDI_SAE($folio,'$serie','$doc', '123', '$tipo')";
            $this->EjecutaQuerySimple();
            $this->query = "UPDATE CFDI01 SET XML_DOC = '$myXMLData' WHERE CVE_DOC = '$doc'";
            $this->EjecutaQuerySimple();
                $this->query="EXECUTE PROCEDURE  SP_CARGA_FACTURA_SAE($folio,'$serie','$doc', '$tipo')";
                $this->EjecutaQuerySimple();
                $this->query="EXECUTE PROCEDURE  SP_CARGA_PARTIDAS_SAE($folio,'$serie', '$doc', '$uuid', '$tipo')";
                $this->EjecutaQuerySimple();
                $this->query="EXECUTE PROCEDURE  SP_CARGA_CUENM_SAE ($folio, '$serie', '$doc', '$uuid', '$tipo')";
                $this->EjecutaQuerySimple();    
            if($tipo == 'I'){
                $this->query = "UPDATE CFDI01 SET XML_DOC = SUBSTRING(XML_DOC FROM 4), tipo_doc='F'  WHERE CVE_DOC = '$doc'";
            }else{
                $this->query = "UPDATE CFDI01 SET XML_DOC = SUBSTRING(XML_DOC FROM 4), tipo_doc='D' WHERE CVE_DOC = '$doc'";
            }
            $this->queryActualiza();
        }else{
            $res=$this->cargaCEP($doc, $ruta2, $rfcr, $serie, $folio);
        }   
        return $mensaje = array('status' => 'ok');
    }

    function InsertaCEPSAE($nameFile, $cve_clie, $rfc, $serie, $folio, $ven, $file, $fecha){
        $this->query="INSERT into factg01 (TIP_DOC, CVE_DOC, CVE_CLPV, STATUS, DAT_MOSTR, CVE_VEND, CVE_PEDI, FECHA_DOC, FECHA_ENT, FECHA_VEN, FECHA_CANCELA, CAN_TOT, IMP_TOT1, IMP_TOT2, IMP_TOT3, IMP_TOT4, DES_TOT, DES_FIN, COM_TOT, CONDICION, CVE_OBS, NUM_ALMA, ACT_CXC, ACT_COI, ENLAZADO, TIP_DOC_E, NUM_MONED, TIPCAMB, NUM_PAGOS, FECHAELAB, PRIMERPAGO, RFC, CTLPOL, ESCFD, AUTORIZA, SERIE, FOLIO, AUTOANIO, DAT_ENVIO, CONTADO, CVE_BITA, BLOQ, FORMAENVIO, DES_FIN_PORC, DES_TOT_PORC, IMPORTE, COM_TOT_PORC, METODODEPAGO, NUMCTAPAGO, TIP_DOC_ANT, DOC_ANT, TIP_DOC_SIG, DOC_SIG, UUID, VERSION_SINC, FORMADEPAGOSAT, USO_CFDI)
            values ('G', '$nameFile', (SELECT FIRST 1 c.CLAVE FROM CLIE01 c WHERE c.rfc = '$rfc' AND c.tipo_empresa = 'M' and c.Matriz = c.clave ), 'E', 0 , (SELECT FIRST 1 c.CVE_VEND FROM CLIE01 c WHERE c.rfc = '$rfc' AND c.tipo_empresa = 'M' and c.Matriz = c.clave ), '', CURRENT_DATE, CURRENT_DATE, CURRENT_DATE, NULL, 0,0,0,0,0,0,0,0,NULL, 0, 1, 'S','N','O','O',1,1,1,current_timestamp, 0, '$rfc', 0, 'T', 0,
            '$serie', $folio, '', 0, 'N', 0, 'N','A', 0, 0, 0, 0,null, NULL, '', '', null, NULL, '$file', current_timestamp, null,  'P01')";
            $this->grabaBD();

            $this->query="INSERT into FACTG_CLIB01 (CLAVE_DOC ) VALUES ( '$nameFile')";
            $this->grabaBD();

            $this->query="INSERT INTO PAR_FACTG01 (CVE_DOC, NUM_PAR, CVE_ART, CANT, PXS, PREC, COST, IMPU1, IMPU2, IMPU3, IMPU4, IMP1APLA, IMP2APLA, IMP3APLA, IMP4APLA, TOTIMP1, TOTIMP2, TOTIMP3, TOTIMP4, DESC1, DESC2, DESC3, COMI, APAR, ACT_INV, NUM_ALM, POLIT_APLI, TIP_CAM, UNI_VENTA, TIPO_PROD, CVE_OBS, REG_SERIE, E_LTPD, TIPO_ELEM, NUM_MOV, TOT_PARTIDA, IMPRIMIR, MAN_IEPS, APL_MAN_IMP, CUOTA_IEPS, APL_MAN_IEPS, MTO_PORC, MTO_CUOTA, CVE_ESQ, DESCR_ART, UUID, VERSION_SINC)
                         VALUES ('$nameFile', 1, 'SERV-PAGO', 1, 1, 0, 0, 0, 0, 0, 16, 0, 0, 0, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 'S', 1, NULL, 1, 'ACT', 'S', 0, 1, 1, 'N', 0, 0, 'S', 'N', 1, 0, 'C', 0, 0, 1, NULL, NULL, current_timestamp)";
            $this->grabaBD();
        return;
        //if(file_exists($file)){
        //    $xml_doc=fopen($file, "r");
        //    $r=$this->cargaCEP($folio);
        //}
    }

    function cargaCEP($cep, $ruta2, $rfcr, $serie, $folio){
        $path="C:\\xampp\\htdocs\\uploads\\xml\\IMI161007SY7\\Emitidos\\".$rfcr."\\";
        $files = array_diff(scandir($path), array('.', '..'));
        foreach($files as $file){
            $data = explode(".", $file);
            $fileName = $data[0];
            $fileExtension = $data[1];
            if(strtoupper($fileExtension) == 'XML' and strpos($fileName, 'CEP') !== false){
                if(strpos($fileName, $cep) !== false){
                    $file = $path.$fileName.'.'.$fileExtension;
                    $myFile = fopen($file, "r") or die("No se ha logrado abrir el archivo ($file)!");
                    $myXMLData = fread($myFile, filesize($file));
                    $xml = simplexml_load_string($myXMLData) or die("Error: No se ha logrado crear el objeto XML ($file)");
                    $ns = $xml->getNamespaces(true);
                    $xml->registerXPathNamespace('c', $ns['cfdi']);
                    $xml->registerXPathNamespace('t', $ns['tfd']);

                    foreach ($xml->xpath('//t:TimbreFiscalDigital') as $tfd) {
                           $fechaT = $tfd['FechaTimbrado']; 
                           $fechaT = str_replace("T", " ", $fechaT); 
                           $uuid = $tfd['UUID'];
                           $noNoCertificadoSAT = $tfd['NoCertificadoSAT'];
                           $RfcProvCertif=$tfd['RfcProvCertif'];
                           $SelloCFD=$tfd['SelloCFD'];
                           $SelloSAT=$tfd['SelloSAT'];
                           $versionT = $tfd['Version'];
                           $rfcprov = $tfd['RfcProvCertif'];
                    }
                    foreach ($xml->xpath('//cfdi:Comprobante') as $cfdiComprobante){
                        $version = $cfdiComprobante['version'];
                        if($version == ''){
                            $version = $cfdiComprobante['Version'];
                        }
                        if($version == '3.2'){
                        }elseif($version == '3.3'){
                            $serie = $cfdiComprobante['Serie'];                  
                            $folio = $cfdiComprobante['Folio'];
                            $total = $cfdiComprobante['Total'];
                            $tipo = $cfdiComprobante['TipoDeComprobante'];
                            $moneda = $cfdiComprobante['Moneda'];
                            $lugar = $cfdiComprobante['LugarExpedicion'];
                            $Certificado = $cfdiComprobante['Certificado'];
                            $Sello = $cfdiComprobante['Sello'];
                            $noCert = $cfdiComprobante['NoCertificado'];
                            $fecha = $cfdiComprobante['Fecha'];
                            $fecha = str_replace("T", " ", $fecha);
                            $subtotal = $cfdiComprobante['SubTotal'];
                        }
                    }
                    foreach ($xml->xpath('//cfdi:Emisor') as $emi){
                        if($version == '3.2'){
                        }elseif($version == '3.3'){
                            $rfce=$emi['Rfc'];
                            $emisor=$emi['Nombre'];
                            $rf = $emi['RegimenFiscal'];
                        }
                    }
                    foreach ($xml->xpath('//cfdi:Receptor') as $rec){
                        if($version == '3.2'){
                        }elseif($version == '3.3'){
                            $rfcr=$rec['Rfc'];
                            $recep=$rec['Nombre'];
                            $UsoCFDI = $rec['UsoCFDI'];
                        }
                    }
                    if($tipo == 'P'){
                        $doc = $serie.str_pad($folio,6,"0", STR_PAD_LEFT);
                            $this->query="INSERT INTO CFDI01 (TIPO_DOC, CVE_DOC, VERSION, UUID, NO_SERIE, FECHA_CERT, FECHA_CANCELA, XML_DOC, DESGLOCEIMP1, DESGLOCEIMP2, DESGLOCEIMP3, DESGLOCEIMP4, MSJ_CANC, PENDIENTE)
                            VALUES ('G', '$doc', '1.1', '$uuid', '$noNoCertificadoSAT', '$fecha', '', '$myXMLData','S', 'N', 'N', 'S', NULL, 'N')";
                            $this->grabaBD();
                            $this->InsertaCEPSAE($doc, $cve_clie = null, $rfcr, $serie, $folio, $ven=null,$file= $uuid, $fecha= null);
                    }

                }else{
                }
            }
        }
        return array("status"=>'ok', "mensaje"=>'Se inserto el documento');
        //return array("status"=>'no',"mensaje"=>'No se encontro el Archivo', "archivo"=>'no');
    }

    function repVenta($op1,$op2,$op3,$op4,$op5,$op6,$op7){
        $data=array();
        switch ($op6) {
            case 'Facturas':
                $tabla = "FTC_FACTURAS t1 ";
                $tabla2 = "FTC_FACTURAS_DETALLE t2 ";
                break;
            case 'Prefacturas':
                $tabla = " Cajas t1 ";
                $tabla2 = " preoc01 t2 ";
                break;
            case 'Cotizaciones':
                $tabla = "FACTP01 t1 ";
                $tabla2 = " PAR_FACTP01 t2 ";
                break;
            default:
            break;
        }
        $fecha=''; $cls=''; $detalle= ''; $cd=""; $campos='';
        if($op4!='' and $op5!=''){
            // Quiere decir que se parametizan las fechas
            $fecha = " and fecha_doc >= '".$op4."' and fecha_doc <='".$op5."' ";    
        }elseif($op4 == '' and $op5 !=''){
            $fecha = " and fecha_doc <= '".$op5."' ";
        }elseif ($op4 != '' and $op5 == ''){
            $fecha = " and fecha_doc >= '".$op4."' ";
        }

        if($op7!= '' and strpos($op7, ":")){
            $clie=explode(",",$op7);
            for($i=0; $i < (count($clie)-1); $i++){
                $cli=explode(":", $clie[$i]);
                $cli[0]=str_replace("<p>","", trim($cli[0]));
                $cli[0]=str_replace("</p>", "", trim($cli[0]));
                $cls .= "trim('".$cli[0]."'),"; 
            }     
            $cls=" and trim(cliente) in (".substr($cls,0,-1).")";
        }
        if($op2 == 'Detallado'){
            $campos = " , t2.SUBTOTAL AS SUBTOTAL_P, t2.TOTAL AS TOTAL_P, t2.DESC1 AS DESC1_P, t2.DESC2 AS DESC2_P, t2.DESC3 AS DESC3_P, t2.DESCF AS DESCF_P  ";
            $detalle = " left join ".$tabla2." on t2.documento = t1.documento ";
            $cd="t2.*, ";
        }

        if($op3 == 'Agrupado'){
            $this->query="SELECT t1.*, $cd (SELECT C.NOMBRE FROM CLIE01 C WHERE trim(C.CLAVE) = t1.cliente ) $campos FROM $tabla $detalle where t1.status is not null  $fecha $cls order by cliente";
        }else{
            $this->query="SELECT t1.*, $cd (SELECT C.NOMBRE FROM CLIE01 C WHERE trim(C.CLAVE) = t1.cliente ) $campos FROM $tabla $detalle where t1.status is not null  $fecha $cls";
        }
        //echo $this->query;
        //die;
        $res=$this->EjecutaQuerySimple();
        while($tsarray=ibase_fetch_object($res)){
            $data[]=$tsarray;
        }
        return array("status"=>"ok", "datos"=>$data, "archivo"=>'');
    } 
    ## autocompletar producto nota de venta.
    function prodVM($b){
        $this->query="SELECT A.*, 
        (SELECT coalesce(SUM(b.RESTANTE), 0) FROM ingresobodega b where b.producto = 'PGS'||A.ID ) - (SELECT coalesce(SUM(v.cantidad),0) from ftc_NV_detalle v where v.articulo = A.id and fecha >= '1.1.2024')  as Existencia  
        FROM FTC_Articulos A WHERE (A.GENERICO||' '||A.SINONIMO||' '|| A.CALIFICATIVO||' '||A.CLAVE_PROD||' '||A.SKU||' '||A.SKU_CLIENTE||' '||A.CLAVE_PEGASO) CONTAINING('$b') and STATUS = 'A'";
        $r=$this->QueryDevuelveAutocompleteProd();
        return $r;
    }

    function clieVM($b){
        $this->query="SELECT A.*, A.CALLE||'-'||COALESCE(A.NUMEXT,'') AS DIRECCION, A.NUMINT AS INTERIOR, A.MUNICIPIO AS DELEGACION  FROM clie01 A WHERE A.NOMBRE CONTAINING('$b') OR a.rfc containing ('$b')";
        $r=$this->QueryDevuelveAutocompleteClieNV();
        return $r;
    }

    function docNV($clie, $prod, $cant, $prec, $desc, $iva, $ieps, $descf, $doc, $idf, $add, $nvm, $obs){
        //$folio = $this->creaFolioNV();
        $c=array(); 
        if($doc == 0 and $idf== 0){
            $c = $this->cabeceraNV($clie, $nvm, $obs);
        }
        $d = $this->partidaNV($prod, $cant, $prec, $desc, $iva, $ieps, $c, $descf, $doc, $idf, $add);
        return array("status"=>'ok',"doc"=>$d['doc'], "idf"=>$d['idf']);
    }

    function cabeceraNV($clie, $nvm, $obs){
        $usuario=$_SESSION['user']->NOMBRE;
        $letra = $_SESSION['user']->LETRA_NUEVA;
        $clie = explode(":", $clie);
        $cliente = $clie[0];
        $cliente = !empty($cliente)? $cliente:'999999';
        $this->query="INSERT INTO FTC_NV 
            ( 
            IDF, DOCUMENTO, SERIE, FOLIO, FORMADEPAGOSAT, VERSION, TIPO_CAMBIO, METODO_PAGO, REGIMEN_FISCAL, LUGAR_EXPEDICION, MONEDA, TIPO_COMPROBANTE, CONDICIONES_PAGO, SUBTOTAL, IVA, IEPS, DESC1, DESC2, TOTAL, SALDO_FINAL, ID_PAGOS, ID_APLICACIONES, NOTAS_CREDITO, MONTO_NC, MONTO_PAGOS, MONTO_APLICACIONES, CLIENTE, USO_CFDI, STATUS, USUARIO, FECHA_DOC, FECHAELAB, IDIMP, UUID, DESCF, IDCAJA, CONTABILIZADO, POLIZA, FECHA_CANCELACION, USUARIO_CANCELACION, NV_MANUAL, OBSERVACION
            ) 
            VALUES (null, '$letra'||(SELECT COALESCE(MAX(FOLIO), 0) + 1 FROM FTC_NV WHERE SERIE = '$letra'),'$letra', (SELECT COALESCE(MAX(FOLIO), 0) + 1 FROM FTC_NV WHERE SERIE = '$letra'), '', 1.1, 1, '', '', '', 1,'NV', 'Contado', 0,0,0,0,0,0,0,'','','',0,0,0,'$cliente', '', 'P', '$usuario', current_date, current_timestamp, 0, null, 0, null, 0, null, null, '' , '$nvm', '$obs'
            ) RETURNING IDF, SERIE, FOLIO, DOCUMENTO";
            //echo $this->query;
        $res=$this->grabaBD();
        $row=ibase_fetch_object($res);
        return $row;
    }

    function partidaNV($prod, $cant, $prec, $desc, $iva, $ieps, $c, $descf, $doc, $idf, $add){
        $usuario=$_SESSION['user']->NOMBRE;
        $st = $prec*$cant;
        $d = $cant*($prec * ($desc/100));
        $t = ($st-$d)*(1+($iva/100));
        if(!empty($c)){
            $idf = $c->IDF;
            $doc = $c->DOCUMENTO;
        }
        $this->query="INSERT INTO FTC_NV_DETALLE ( IDFP ,IDF ,DOCUMENTO ,PARTIDA ,CANTIDAD ,ARTICULO ,UM ,DESCRIPCION ,IMP1 ,IMP2 ,IMP3 ,IMP4 ,DESC1 ,DESC2 ,DESC3 ,DESCF ,SUBTOTAL ,TOTAL ,CLAVE_SAT ,MEDIDA_SAT ,PEDIMENTOSAT ,LOTE ,USUARIO ,FECHA ,IDPREOC ,IDCAJA ,IDPAQUETE ,PRECIO ,STATUS ,NUEVO_PRECIO ,NUEVA_CANTIDAD ,CAMBIO ) 
            VALUES (null, $idf, '$doc', (SELECT COALESCE(MAX(PARTIDA),0) + 1 FROM FTC_NV_DETALLE WHERE IDF = $idf), $cant, '$prod', (SELECT FIRST 1 UM FROM producto_ftc WHERE CLAVE_FTC='$prod'), (SELECT FIRST 1 NOMBRE FROM producto_ftc WHERE CLAVE_FTC='$prod')|| ' $add', $iva, $ieps, 0, 0, $desc, 0, 0, $descf, $st, $t,  (SELECT coalesce(CLAVE_SAT,'01010101') FROM ftc_articulos I WHERE I.id = $prod ), (SELECT coalesce(UNIDAD_SAT, 'H87') FROM ftc_articulos I WHERE I.id =$prod ), '',(SELECT FIRST 1 CVE_PROD FROM producto_ftc WHERE CLAVE_FTC='$prod'),'$usuario', current_date, 0, 0, 0, $prec, 0, 0, 0, 0  
        )";
        //echo $this->query;
        $this->grabaBD();

        $this->query="UPDATE FTC_NV F SET 
            F.SUBTOTAL = (SELECT SUM(SUBTOTAL) FROM FTC_NV_DETALLE FD WHERE FD.IDF = $idf), 
            F.TOTAL = (SELECT SUM(TOTAL) FROM FTC_NV_DETALLE FD WHERE FD.IDF = $idf),
            F.IVA = (SELECT SUM((PRECIO * (IMP1/100))*CANTIDAD) FROM FTC_NV_DETALLE FD WHERE FD.IDF = $idf),
            F.DESC1 = (SELECT SUM((PRECIO * (DESC1/100))*CANTIDAD) FROM FTC_NV_DETALLE FD WHERE FD.IDF = $idf),
            F.SALDO_FINAL = (SELECT SUM(TOTAL) FROM FTC_NV_DETALLE FD WHERE FD.IDF = $idf),
            F.DESCF = $descf
            WHERE IDF = $idf";
        $this->queryActualiza();
        return array("doc"=>$doc, "idf"=>$idf);
    }

    function cambiaObs($lin, $doc, $obs){
        $this->query="UPDATE FTC_NV_DETALLE NVD SET DESCRIPCION = (SELECT FIRST 1 NOMBRE FROM PRODUCTO_FTC PF WHERE PF.CLAVE_FTC = NVD.ARTICULO AND NVD.PARTIDA = $lin AND NVD.DOCUMENTO = '$doc') || '$obs' where NVD.PARTIDA = $lin AND NVD.DOCUMENTO = '$doc'";
        $this->queryActualiza();
        return array("status"=>'ok', "info"=>$obs);
    }


    function nvCabecera($docf){
        $data=array();
        $this->query="SELECT F.*, C.*, (SELECT FIRST 1 NOMBRE FROM PG_USERS P WHERE F.SERIE = P.LETRA_NUEVA) AS VENDEDOR, (SELECT FIRST 1 CATEGORIA FROM PG_USERS P WHERE F.SERIE = P.LETRA_NUEVA) AS VND  FROM FTC_NV F left join CLIE01 C ON C.CLAVE = F.CLIENTE WHERE F.DOCUMENTO='$docf'";
        $res=$this->EjecutaQuerySimple();
        while ($tsarray=ibase_fetch_object($res)) {
            $data[]=$tsarray;
        }
        return $data;
    }

    function nvPartidas($docf, $t){
        $data=array();$fact=array(); $facts='';
        if ($t == 'P' or $t == 'F' or $t == 'E'){
            $this->query="SELECT F.*,(SELECT SUM(RESTANTE) FROM ingresobodega I WHERE I.PRODUCTO='PGS'||F.ARTICULO) - (SELECT coalesce(SUM(v.cantidad),0) from ftc_NV_detalle v where v.articulo = F.ARTICULO and fecha >= '1.1.2024')  AS EXISTENCIA, (SELECT SKU FROM FTC_Articulos A WHERE A.ID = F.ARTICULO), (SELECT FIRST 1 NOMBRE FROM producto_ftc WHERE CLAVE_FTC= F.articulo) AS PRODUCTO, (SELECT FIRST 1 CVE_PROD FROM producto_ftc WHERE CLAVE_FTC= F.articulo) AS ISBN FROM FTC_NV_DETALLE F WHERE IDF=(select idf from ftc_nv where documento='$docf') and Documento = '$docf' order by F.partida";
        }else{
            $this->query="SELECT FACTURA FROM FTC_NV_fp WHERE NV = '$docf'";
            $res=$this->EjecutaQuerySimple();
            while($tsarray=ibase_fetch_object($res)){
                $fact[]=$tsarray;
            }
            foreach($fact as $factura){
                $facts .= "'".$factura->FACTURA."',";
            }
            if(strlen($facts)>0){
                $facts = substr($facts, 0 , strlen($facts)-1);
            }
            $this->query="SELECT ND.*,
                coalesce ((select sum(cantidad)
                    from ftc_facturas_detalle fd
                    where fd.documento in ($facts) and fd.partida = nd.partida and status = 0)
                    ,0) as facturado,
                    cantidad - coalesce ((select sum(cantidad)
                    from ftc_facturas_detalle fd
                    where fd.documento in ($facts) and fd.partida = nd.partida and status = 0)
                    ,0) as pendiente,

                    (SELECT SUM(RESTANTE) FROM ingresobodega I WHERE I.PRODUCTO='PGS'||ND.ARTICULO) - (SELECT coalesce(SUM(v.cantidad),0) from ftc_NV_detalle v where v.articulo = ND.ARTICULO and fecha >= '1.1.2024')  AS EXISTENCIA, 
                    (SELECT SKU FROM FTC_Articulos A WHERE A.ID = ND.ARTICULO), 
                    (SELECT FIRST 1 NOMBRE FROM producto_ftc WHERE CLAVE_FTC= ND.articulo) AS PRODUCTO
                    from ftc_nv_detalle nd
                    where IDF=(select idf from ftc_nv where documento='$docf') and documento ='$docf'";
        }
        //echo $this->query;
        $res=$this->EjecutaQuerySimple();
        while ($tsArray=ibase_fetch_object($res)) {
            $data[]=$tsArray;
        }
        return $data;
    }



    function traeAplicaciones($doc, $cambio){
        $data = array();
        $this->query="SELECT A.*, $cambio as cambio FROM APLICACIONES A WHERE DOCUMENTO = '$doc' and cancelado = 0";
        $res=$this->EjecutaQuerySimple();
        while ($tsarray=ibase_fetch_object($res)) {
            $data[]=$tsarray;
        }
        return $data;
    }

    function pagaNV($tcc,$tcd,$efe,$tef,$val,$cupon,$cr,$doc,$cambio){
        $pagos = array($tcc,$tcd,$efe,$tef,$val,$cupon);
        $usuario = $_SESSION['user']->NOMBRE;
        for ($i=0; $i < count($pagos); $i++){ 
            switch ($i){
                case 0:
                    $tipo = 'TCC';
                    break;
                case 1:
                    $tipo = 'TCD';
                    break;
                case 2:
                    $tipo = 'EFE';
                    break;
                case 3:
                    $tipo = 'TEF';
                    break;
                case 4:
                    $tipo = 'VAL';
                    break;
                case 5:
                    $tipo = 'CUPON';
                    break;
                case 6:
                    $tipo = 'Cr';
                    break;
                default:
                    break;
            }
            $monto = $pagos[$i];
            if($pagos[$i] > 0){
                if($tipo == 'EFE' and $cambio > 0){
                    $monto=$pagos[$i]-$cambio;
                }
                //echo 'El pago es: '.$tipo.' por un monto de: '.$pagos[$i].'<br/>';
                $this->query="INSERT INTO CARGA_PAGOS (ID, CLIENTE, FECHA, MONTO, SALDO, USUARIO, FECHA_APLI, FECHA_RECEP, FOLIO_X_BANCO, RFC, STATUS, TIPO_PAGO) values (NULL, (SELECT CLIENTE FROM FTC_NV WHERE DOCUMENTO = '$doc'), current_date, $monto, 0, '$usuario', current_timestamp, current_timestamp,'$tipo'||'-'||'$doc', (SELECT RFC FROM CLIE01 WHERE CLAVE = (SELECT CLIENTE FROM FTC_NV WHERE DOCUMENTO = '$doc')), 1, '$tipo') RETURNING ID";
                $res=$this->grabaBD();
                $row=ibase_fetch_object($res);
                if(!empty($row->ID)){
                    $this->query="INSERT INTO APLICACIONES (ID, FECHA, IDPAGO, DOCUMENTO, MONTO_APLICADO, SALDO_DOC, SALDO_PAGO, USUARIO, STATUS, RFC, FORMA_PAGO, CANCELADO, OBSERVACIONES) VALUES (NULL, current_timestamp, $row->ID, '$doc', $monto, (SELECT SALDO_FINAL FROM FTC_NV F WHERE F.DOCUMENTO = '$doc') - $monto, 0, '$usuario', 'E',  (SELECT RFC FROM CLIE01 WHERE CLAVE = (SELECT CLIENTE FROM FTC_NV WHERE DOCUMENTO = '$doc')), '$tipo', 0, 'Nota Venta: '||'$doc')";
                    $this->grabaBD();

                    $this->query="UPDATE FTC_NV SET SALDO_FINAL = SALDO_FINAL - $monto, status = 'E' where DOCUMENTO = '$doc'";
                    $this->queryActualiza();
                }
            }
        }
        return array("status"=>'ok');
    }

    function cancelaNV($doc){
        $this->query="UPDATE FTC_NV SET STATUS= 'C' WHERE DOCUMENTO = '$doc' and status='P'";
        $res=$this->queryActualiza();

        if($res == 1){
            $this->query="UPDATE FTC_NV_DETALLE SET STATUS= 8 WHERE DOCUMENTO = '$doc' ";
            $res=$this->queryActualiza();
            return array("status"=>'ok', "mensaje"=>'Revise la informacion');
        }else{
            return array("status"=>'ok', "mensaje"=>'La Nota ya ha sido cancelada o facturada');
        }
    }

    function cambioCliente($clie, $doc){
        $cliente = explode(":", $clie);
        $cliente = $cliente[0];
        $this->query="UPDATE FTC_NV SET CLIENTE = (SELECT CLAVE FROM CLIE01 WHERE TRIM(CLAVE) = TRIM('$cliente')) where documento = '$doc'";
        $res=$this->queryActualiza();
        if($res == 1){
            return array("status"=>'ok', "mensaje"=>'Se ha efectuado el cambio de cliente');
        }else{
            return array("status"=>'no', "mensaje"=>'Ocurrio un error, favor de revisar la informacion');
        }
    }

    function dropP($doc, $idf, $p){
        $this->query="DELETE FROM FTC_NV_DETALLE WHERE DOCUMENTO = '$doc' and IDF=$idf and PARTIDA = $p ";
        $this->queryActualiza();
        $res=$this->acomodoPartidas($doc, $idf);
        return $res;
    }

    function acomodoPartidas($doc, $idf){
        $this->query="SELECT * FROM FTC_NV_DETALLE WHERE DOCUMENTO = '$doc' and idf=$idf  order by partida";
        $res=$this->EjecutaQuerySimple();
        while ($tsarray=ibase_fetch_object($res)) {
            $data[]=$tsarray;
        }
        $p=0;
        foreach ($data as $x){
            $p++;
            $this->query="UPDATE FTC_NV_DETALLE SET PARTIDA = $p where documento = '$doc' and idf=$idf and partida = $x->PARTIDA";
            $this->queryActualiza();
        }

        $this->query="UPDATE FTC_NV F SET 
            F.SUBTOTAL = (SELECT SUM(SUBTOTAL) FROM FTC_NV_DETALLE FD WHERE FD.IDF = $idf), 
            F.TOTAL = (SELECT SUM(TOTAL) FROM FTC_NV_DETALLE FD WHERE FD.IDF = $idf),
            F.IVA = (SELECT SUM((PRECIO * (IMP1/100))*CANTIDAD) FROM FTC_NV_DETALLE FD WHERE FD.IDF = $idf),
            F.DESC1 = (SELECT SUM((PRECIO * (DESC1/100))*CANTIDAD) FROM FTC_NV_DETALLE FD WHERE FD.IDF = $idf),
            F.SALDO_FINAL = (SELECT SUM(TOTAL) FROM FTC_NV_DETALLE FD WHERE FD.IDF = $idf)
            WHERE IDF = $idf";
        $this->queryActualiza();

        return array("status"=>'ok');
    }

    function chgTipo($tipo, $id, $nt){
        switch ($tipo) {
            case 1: //// El tipo define la tabla de donde viene la informacion, el numero 1 es Carga Pagos 
                if($nt == 'DV' or $nt == 'gasto'){
                    $res=0;
                    $m='No se pude Cambiar un ingreso a Devolucion de Venta';
                    break;
                }elseif($nt == 'venta'){
                    $this->query="UPDATE CARGA_PAGOS SET TIPO_PAGO = null where id= $id";
                }else{
                    $this->query="UPDATE CARGA_PAGOS SET TIPO_PAGO = '$nt' where id= $id";
                }    
                $res=$this->queryActualiza();
                break;
            case 2:
                /// ESTE TIPO NO SE PUEDE CAMBIAR POR QUE VIENE DE UNA COMPRA DE MATERIAL.
                break;
            case 3:
                $id = substr($id, 3);
                $this->query="UPDATE CR_DIRECTO SET TIPO = '$nt' where id=$id";
                $res = $this->queryActualiza();
                break;
            case 4:
                $id=substr($id, 3);
                $this->query="UPDATE GASTOS SET TIPO='$nt' where id=$id";
                $res = $this->queryActualiza();
                break;
            case 5:
                $id = substr($id, 3);
                $this->query="UPDATE CR_DIRECTO SET TIPO = '$nt' where id=$id";
                $res = $this->queryActualiza();
                break;
            case 6:
                /// Deudores... esto no se puede cambiar.
                break;
            case 7:
                /// SOLICITUD DE PAGO. 
                $id = substr($id, 4);
                $this->query="UPDATE SOLICITUD_PAGO SET TIPO = '$nt' where idsol = $id";
                $res = $this->queryActualiza();
                break;
            case 8:
                /// FTC_POC  no se pueden cambiar las compras. 
                break;
            default:
                break;
        }
        if($res > 0){
            $m='Se ha realizado el cambio. :)';
        }else{
            $m='No se pudo realizar el cambio, actualice la pantalla antes de intentarlo nuevamente. :( ';
        }
        return $r=array("status"=>'ok', "mensaje"=>$m);
    }

    function verNV($p, $fi, $ff){
        $data=array();
        $param = '';
        if($p=='s') {
            $param=' where extract(WEEK from f.FECHAELAB)='.date("W"). ' and extract(year from f.FECHAELAB) = '.date("Y");  
        }elseif($p=='m'){
            $param=' where extract(MONTH from f.FECHAELAB)='.date("m"). ' and extract(year from f.FECHAELAB) = '.date("Y");  
        }elseif($p=='a'){
            $param=' where extract(YEAR from f.FECHAELAB)='.date("Y");  
        }elseif($p=='t'){
            $param = '';
        }elseif($p =='p'){
            $param = " where FECHAELAB BETWEEN '".$fi."' and '".$ff."' ";
        }
        $this->query="SELECT f.*, (select fa.uuid from ftc_facturas fa where fa.documento = f.METODO_PAGO) as f_UUID,
        (select fa.FORMADEPAGOSAT from ftc_facturas fa where fa.documento = f.METODO_PAGO) as f_formadepagosat,
         (SELECT NOMBRE FROM CLIE01 C where C.clave = f.cliente) as nombre, (SELECT COUNT(*) FROM FTC_NV_DETALLE fd WHERE fd.documento = f.documento) as prod, (SELECT sum(CANTIDAD) FROM FTC_NV_DETALLE fd WHERE fd.documento = f.documento) as piezas, CAST((SELECT LIST(FORMA_PAGO) FROM APLICACIONES a WHERE a.DOCUMENTO = f.DOCUMENTO) AS VARCHAR(100)) as fp ,
            iif(f.status = 'R', CAST((SELECT LIST(FACTURA) FROM FTC_NV_FP WHERE NV = F.DOCUMENTO) AS VARCHAR(300)), '' ) AS FACTURAS,
            (SELECT COUNT(IDE) FROM FTC_LOG_ENVIO L WHERE L.DOCUMENTO = f.METODO_PAGO ) as envio,
            (SELECT FIRST 1 mensaje FROM FTC_LOG_ENVIO L WHERE L.DOCUMENTO = f.METODO_PAGO order by ide desc) as MENSAJE,
            (SELECT FIRST 1 fecha FROM FTC_LOG_ENVIO L WHERE L.DOCUMENTO = f.METODO_PAGO order by ide desc) as FECHA_ENVIO, 
            CAST ( (SELECT LIST(CEP) FROM CARGA_PAGOS WHERE ID IN (SELECT IDPAGO FROM APLICACIONES WHERE DOCUMENTO = f.METODO_PAGO)) AS VARCHAR(100)) AS CEPS,
            (SELECT FIRST 1 MENSAJE FROM FTC_LOG_TIMBRADO ftcl WHERE ftcl.doc = f.metodo_pago order by id desc) as estatusFact
            FROM FTC_NV f $param ORDER BY f.Serie asc, f.folio asc";
        $res=$this->EjecutaQuerySimple();
        while ($tsArray=ibase_fetch_object($res)) {
            $data[]=$tsArray;
        }
        return $data;
    }   

    function creaFact($doc, $mp, $fp, $uso){
        $usuario = $_SESSION['user']->NOMBRE;
        $this->query="EXECUTE PROCEDURE SP_FACTURA_NV('$uso', '$mp', '$doc', '$fp', '$usuario')";
        $res=$this->EjecutaQuerySimple();
        $row = ibase_fetch_object($res);
        $factura = $row->FACTURA;
        return $factura;
    }

    function copiaNV($doc){
        $this->query="EXECUTE PROCEDURE SP_COPIA_NV('$doc')";
        $res=$this->EjecutaQuerySimple();
        $row=ibase_fetch_object($res);
        return array("status"=>'ok', "NNV"=>$row->NNV, "Mensaje"=>'Se ha creado la Nota de Venta '.$row->NNV);
    }

    function chgEmail($cl, $correo){
        $this->query="UPDATE CLIE01 SET EMAILPRED = '$correo' where clave = '$cl'";
        $this->EjecutaQuerySimple();
        return array("status"=>'ok');
    }

    function nvcl($cl){
        $this->query="SELECT * FROM CLIE01 WHERE trim(CLAVE) = trim('$cl')";
        $res=$this->EjecutaQuerySimple();
        $row = ibase_fetch_row($res);
        return array("n"=>$row[2]);
    }

    function histProd($id, $per, $fi , $ff ){
        $data =array();
        switch ($per){
            case 't':
                $t = '';
                break;
            case 'a':
                $t = ' and f.anio = '.date("Y");
                break;
            case 's':
                $t = '';
                break;
            case 'p':
                $t = " and f.fecha_doc >= '".$fi."' and f.fecha_doc <='".$ff."'";
                break;    
            default:
                break;
        }
        $this->query="SELECT 'Venta' as tipo, fd.*, p.*, f.*, f.nombre||'('||f.rfc||')' as cliente FROM FTC_FACTURAS_DETALLE fd
                            left join producto_ftc p on p.clave = fd.articulo
                            left join facturas_fp f on fd.documento = f.cve_doc
                            WHERE p.clave_ftc = '$id' $t";
        //echo $this->query;
        $res=$this->EjecutaQuerySimple();
        while ($tsArray=ibase_fetch_object($res)){
            $data[]=$tsArray;
        }

        return $data;
    }

    function cargaProd($file, $ext){
        if($ext == 'xls' or $ext == 'XLS' or $ext == 'xlsx' or $ext == 'XLSX'){
            $this->cargaXLS($file);
        }elseif($ext == 'txt' or $ext == 'TXT'){

        }elseif($ext == 'csv' or $ext == 'CSV'){
            $this->cargaWoo($file);
        }
    }

    function cargaXLS($file){
        if($xlsx = SimpleXLSX::parse($file)){
            $hoja = $xlsx->sheetName(0);
            $ln=0;$update=0; $insert=0;
            foreach($xlsx->rows() as $key){
                //print_r($key);
                $ln++;
                if($ln > 1){
                    if($key[3] != '' and $key[7]!=''){/// Validamos los campos indispensables para la insercion del producto
                        ///detectamos los campos que son int o double y vengan vacios, los colocamos como 0.00 
                        // 10, 15, 16, 17, 20, 21, 22, 23, 24, 25, 28 date, 29, 30 , 31, 32, 33 timestamp, 36, 37, 38
                        $numeros = array(10, 15, 16, 17, 20, 21, 22, 23, 24, 25, 29, 30 , 31, 32, 36, 37, 38);
                        $fechas = array(28, 33);
                        $texto = array(1, 2, 3, 4, 5, 6, 7, 8, 9, 11, 12, 13, 14, 18, 19, 26, 27, 34, 35);
                        for ($i=0; $i <=38 ; $i++) { 
                            if(in_array($i, $texto)){
                                $caracteres = array("'",'"');
                                $t = 't'.$i;
                                //$$t = htmlentities($key[$i]);
                                $$t = str_replace($caracteres, '',$key[$i]);
                            }
                            if(in_array($i, $numeros)){
                                $c = 'c'.$i;
                                $$c = (!is_numeric($key[$i]))? 0:$key[$i];                               
                            }
                        }
                        $caracteres = array("/", "-", ",", ".", " ", "+", "'", " ");
                        $cve_prod = str_replace($caracteres, '',$key[7]);
                        $proISBN=array();
                        $this->query="SELECT * FROM FTC_Articulos WHERE CLAVE_PROD = '$cve_prod'";
                        $res=$this->EjecutaQuerySimple();
                        while($tsarray=ibase_fetch_object($res)){
                            $proISBN[]=$tsarray;
                        }
                        if(count($proISBN)>0){
                            $update++;
                            $costo = $c15>0? ' ,COSTO= $c15 ':'';
                            $costot = $c30>0? ' ,COSTO_T = $c30 ':'';
                            $marca = $t8!=''? " , MARCA = '".strtoupper($t8)."'":'';
                            $categoria = $t2!=''? " , CATEGORIA = '$t2'":'';
                            $sku_cliente = $t13!=''? " , sku_cliente = '$t13'":'';
                            $sku = $t14!=''? " , sku = '$t14'":'';
                            //$status = " , status = ".$t19;
                            $this->query="UPDATE FTC_ARTICULOS SET PRECIO_V = $c38, status = '$t18' $costo $costot $marca $categoria $sku_cliente $sku where CLAVE_PROD = '$cve_prod'";
                            //echo '<br/> Linea '.$ln.' = '.$this->query;
                            $this->queryActualiza();
                            
                        }else{
                            $insert++;
                            $t8 = strtoupper($t8);
                            $this->query="INSERT INTO FTC_ARTICULOS (ID, LINEA, CATEGORIA, GENERICO, SINONIMO, CALIFICATIVO, MEDIDAS, CLAVE_PROD, MARCA, UM, EMPAQUE, CLAVE_DISTRIBUIDOR, CLAVE_FABRICANTE, SKU_CLIENTE, SKU, COSTO, PRECIO, UTILIDAD_MININA, STATUS, VENDEDOR, COTIZACION, DESC1, DESC2, DESC3, DESC4, DESCF, DESCRIPCION, IVA, FECHA_ALTA, IMPUESTO, COSTO_T, COSTO_OC, CANTSOL, FECHA_BAJA, USUARIO_BAJA, CLAVE_PEGASO, IVA_V, IEPS_V, PRECIO_V)
                            VALUES (NULL,'$t1', '$t2', '$t3', '$t4', '$t5', '$t6', '$cve_prod', '$t8', '$t9', $c10, '$t11', '$t12', '$t13', '$t14',
                            $c15, $c16, $c17, '$t18', '$t19', $c20, $c21, $c22, $c23, $c24, $c25, '$t26', '$t27', current_timestamp,
                            $c29, $c30, $c31, $c32, current_timestamp, '$t34', '$t35', $c36, $c37, $c38
                            )"; //echo $this->query; die();
                            $this->grabaBD();    
                        }
                    }    
                }
            }
        }
        echo '<br/> Se realizaron '.$update.' actualizaciones';
        echo '<br/> Se realizaron '.$insert.' inserciones';
    }

    function cargaWoo($file){
        $csv = fopen($file, "r"); $ln = 0; $nameFile=explode("/", $file);
        if(substr($nameFile[5],0,2) != 'wc'){
            die('No es un archivo de Woo Commerce');
        }
        $ln=1;
        while (($raw_string = fgets($csv)) !== false){
            $r = str_getcsv($raw_string);       
            if($ln >= 2){
                    // Arreglo de numeros
                    $r4 = empty($r[4])? 0:$r[4];
                    $r5 = empty($r[5])? 0:$r[5];
                    $r14 = empty($r[14])? 0:$r[14];
                    $r15 = empty($r[15])? 0:$r[15];
                    $r18 = empty($r[18])? 0:$r[18];
                    $r19 = empty($r[19])? 0:$r[19];
                    $r20 = empty($r[20])? 0:$r[20];
                    $r21 = empty($r[21])? 0:$r[21];
                    $r24 = empty($r[24])? 0:$r[24];
                    $r25 = empty($r[25])? 0:$r[25];
                    $r38 = empty($r[38])? 0:$r[38];
                    /// Arreglo de fechas:
                    $r9 = empty($r[9])? 'null':$r[9];
                    $r10 = empty($r[10])? 'null':$r[10];

                    $r4 = $r[4]=="'-1"? -1:$r[4];
                    $r30 = $r[30]=="'-1"? -1:$r[30];
                    $r31 = $r[31]=="'-1"? -1:$r[31];
                    $this->query="INSERT INTO FTC_WOO (ID, TIPO, SKU, NOMBRE, PUBLICADO, DESTACADO, VISIBILIDAD, DESC_CORTA, DESCRIPCION, DIA_REBAJA, DIA_REBAJA_FIN, ESTADO_IMPUESTO, CLASE_IMPUESTO, EN_INVENTARIO, INVENTARIO, INVENTARIO_BAJO, RESERVA_PRODUCTOS_AGOTADOS, VENDIDO_INDIVIDUALMENTE, PESO, LONGITUD, ANCHURA, ALTURA, VALORACIONES, NOTA_COMPRA, PRECIO_BAJO, PRECIO_NORMAL, CATEGORIAS, ETIQUETAS, CLASE_ENVIO, IMAGENES, LIMITE_DESCARGAS, DIAS_CADUCIDAD_DESCARGAS, SUPERIOR, PRODUCTOS_AGRUPADOS, VENTAS_DIRIGIDAS, VENTAS_CRUZADAS, URL_EXTERNA, TEXTO_BOTON, POSICION, NOMBRE_ATRIBUTO_1, VALOR_ATRIBUTO_1, ATRIBUTO_VISIBLE_1, ATRIBUTO_GLOBAL_1, NOMBRE_ATRIBUTO_2, VALOR_ATRIBUTO_2, ATRIBUTO_VISIBLE_2, ATRIBUTO_GLOBAL_2, ATRIBUTO_DEFECTO_1, ATRIBUTO_DEFECTO_2) VALUES ( $r[0], '$r[1]', '$r[2]', '$r[3]', $r4, $r5, '$r[6]', '$r[7]', '$r[8]', $r9, $r10, '$r[11]', '$r[12]', '$r[13]', $r14, $r15, '$r[16]', '$r[17]', $r18, $r19, $r20, $r21, '$r[22]', '$r[23]', $r24, $r25, '$r[26]', '$r[27]', '$r[28]', '$r[29]', '$r30', '$r31', '$r[32]', '$r[33]', '$r[34]', '$r[35]', '$r[36]', '$r[37]', $r38, '$r[39]', '$r[40]', '$r[41]', '$r[42]', '$r[43]', '$r[44]', '$r[45]', '$r[46]', '$r[47]', '$r[48]')";
                    if($res= $this->grabaBD()){

                    }else{
                        echo $this->query;
                        echo 'Valor de r4: '.$r4;
                        die();
                    }
            }
            $ln++;
        }
        echo "Total de lineas: " . $ln;
        fclose($csv);
    }

    function producto($id){
        $data=array();
        $this->query="SELECT * FROM FTC_ARTICULOS WHERE ID = $id";
        $res=$this->EjecutaQuerySimple();
        while ($tsArray=ibase_fetch_object($res)) {
            $data[]=$tsArray;
        }
        return $data;
    }

    function productoF($isbn){
        $data=array();
        $this->query="SELECT max(descripcion) as generico, max(identificador) as clave_prod FROM XML_PARTIDAS WHERE IDENTIFICADOR = '$isbn'";
        $res=$this->EjecutaQuerySimple();
        while ($tsArray=ibase_fetch_object($res)) {
            $data[]=$tsArray;
        }
        return $data;
    }

    function sisbn($isbn){
        $data=array(); $status='ok';
        $this->query="SELECT
                            (SELECT NOM_COMERCIAL FROM FTC_EMPRESAS WHERE RFC = 'MABL7705259U7') as cliente,
                            x.documento,
                            x.tipo, 
                            x.fecha as fecha_doc,
                            p.cantidad,
                            p.unitario as precio, 
                            (select iif(max(tasa) ='' , 0,coalesce(max(tasa),0)) from xml_impuestos i where p.uuid = i.uuid and p.partida = i.partida and impuesto = '002') as IMP1, 
                            (select iif(max(tasa) ='' , 0,coalesce(max(tasa),0)) from xml_impuestos i where p.uuid = i.uuid and p.partida = i.partida and impuesto = '003') as IMP2, 
                            (select iif(max(tasa) ='' , 0,coalesce(max(tasa),0)) from xml_impuestos i where p.uuid = i.uuid and p.partida = i.partida and impuesto = '002') as IMP1,
                            P.descuento, 
                            p.importe as total,
                            (SELECT c.NOMBRE FROM XML_CLIENTES c where c.rfc = x.rfce) AS PROVEEDOR,
                            P.descripcion,
                            p.identificador
                        FROM XML_PARTIDAS p 
                        left join xml_data x on x.uuid = p.uuid 
                        WHERE p.IDENTIFICADOR CONTAINING ('$isbn')";
                        
        $res=$this->EjecutaQuerySimple();
        while($tsArray=ibase_fetch_object($res)){
            $data[]=$tsArray;
        }
        if(count($data)==0 ){
            $status='no';
        }
        return array("status"=>$status, "datos"=>$data);
    }

    function cabeceraN_V($nv){
        $data=array();
        $this->query="SELECT * FROM FTC_NOTA_VENTA WHERE DOCUMENTO = '$nv'";
        $res=$this->EjecutaQuerySimple();
        while($tsarray=ibase_fetch_object($res)){
            $data[]=$tsarray;
        }
        return $data;
    }

    function detalleNV($nv){
        $data=array();
        $this->query="SELECT fd.*, 
                (select descripcion from claves_sat cs where cs.cve_prod_serv = fd.clave_sat) as descCve, 
                (select descripcion from unidades_sat us where  us.clave = fd.medida_sat) as descUni 
                FROM FTC_NV_DETALLE fd WHERE DOCUMENTO = '$nv'";
        $res=$this->EjecutaQuerySimple();
        while($tsarray=ibase_fetch_object($res)){
            $data[]=$tsarray;
        }
        return $data;   
    }

    function factPar($doc, $datos, $uf, $mp, $fp){
        $usuario = $_SESSION['user']->NOMBRE;
        $this->query="EXECUTE PROCEDURE SP_FACTURA_NV_PARCIAL('$uf', '$mp', '$doc', '$fp', '$usuario')";
        $res=$this->grabaBD();
        $res = ibase_fetch_object($res);
        $idf = $res->IDF;
        $docf =$res->FACTURA;
        for ($i=1; $i < count($datos) ; $i++) { 
            if($datos[$i] > 0){
                $this->query = "EXECUTE PROCEDURE SP_FACTURA_NV_PARCIAL_DETALLE('$doc','$docf', $idf, $i, $datos[$i], '$usuario')";
                $res=$this->grabaBD();
            }
        }
        $this->query="EXECUTE PROCEDURE SP_NV_PARCIAL('$doc', '$docf', '$usuario')";
        $this->grabaBD();

    }

    function sincwoo($opc){
        $data=array();$ruta='C:\\xampp\\htdocs\\woo\\';
        //if(!is_dir($ruta)){mkdir($ruta);}
        !is_dir($ruta)? mkdir($ruta):'';
        $edit='Mc Graw Hill';
        if($opc == 1 ){
            $this->query="SELECT * FROM FTC_WOO where id > 5000 and etiquetas containing ('$edit')";
            //$this->query="SELECT * FROM FTC_WOO where id > 19494 and id< 19500";
            $res=$this->EjecutaQuerySimple();
            while ($tsArray=ibase_fetch_object($res)) {
                $data[]=$tsArray;
            }
            $nombre='Woo_Horus_'.date("d-m-Y H_i_s").'-'.$edit.'.csv';

            $file_handle=fopen($ruta.$nombre, 'w');
            $linea = array("ID", "TIPO", "SKU", "NOMBRE", "PUBLICADO", "¿Está destacado?", "Visibilidad en el catálogo", "Descripción Corta", "Descripción", "Día en que empieza el precio rebajado", "Día en que termina el precio rebajado", "Estado del impuesto", "Clase de impuesto", "¿En inventario?", "Inventario", "Cantidad de bajo inventario", "¿Permitir reservas de productos agotados?", "¿vendido individualmente?", "Peso (kg)", "Longitud (cm)", "Anchura (cm)", "Altura (cm)", "¿Permitir valoraciones de clientes?", "Nota de compra", "Precio Rebajado", "Precio normal", "Categorías", "Etiquetas (separadas por una coma)", "Clase de envío", "Imágenes", "Límite de descargas", "Días de caducidad de la descarga", "SUPERIOR", "Productos agrupados", "Ventas dirigidas", "Ventas cruzadas", "URL externa", "Texto del botón", "Posición", "Nombre del atributo 1", "Valor(es) del Atributo 1", "Visibilidad del atributo 1", "¿Es un atributo global 1?", "Nombre del Atributo 2", "valor(es) del atributo 2", "Visibilidad del atributo 2", "¿es atributo global 2?", "Atributo por defecto 1", "Atributo por defecto 2");
            fputcsv($file_handle, $linea, ',', '"');   
            foreach ($data as $d) {
                //$linea = array($d->ID_WOO.','.$d->ISBN.',"'.$d->NOMBRE.'",'.$d->PRECIO.','.$d->IMAGEN.','.$d->PUBLICADO);
                $linea = array($d->ID.','.$d->TIPO.",'".$d->SKU.',"'.$d->NOMBRE.'",'.$d->PUBLICADO.','.$d->DESTACADO.','.$d->VISIBILIDAD.',"'.$d->DESC_CORTA.'","'.$d->DESCRIPCION.'",'.$d->DIA_REBAJA.','.$d->DIA_REBAJA_FIN.','.$d->ESTADO_IMPUESTO.','.$d->CLASE_IMPUESTO.','.$d->EN_INVENTARIO.','.$d->INVENTARIO.','.$d->INVENTARIO_BAJO.','.$d->RESERVA_PRODUCTOS_AGOTADOS.','.$d->VENDIDO_INDIVIDUALMENTE.','.$d->PESO.','.$d->LONGITUD.','.$d->ANCHURA.','.$d->ALTURA.','.$d->VALORACIONES.','.$d->NOTA_COMPRA.','.$d->PRECIO_BAJO.','.$d->PRECIO_NORMAL.','.'"'.$d->CATEGORIAS.'"'.','.$d->ETIQUETAS.','.$d->CLASE_ENVIO.','.$d->IMAGENES.','.$d->LIMITE_DESCARGAS.','.$d->DIAS_CADUCIDAD_DESCARGAS.','.$d->SUPERIOR.','.$d->PRODUCTOS_AGRUPADOS.','.$d->VENTAS_DIRIGIDAS.','.$d->VENTAS_CRUZADAS.','.$d->URL_EXTERNA.','.$d->TEXTO_BOTON.','.$d->POSICION.','.$d->NOMBRE_ATRIBUTO_1.','.$d->VALOR_ATRIBUTO_1.','.$d->ATRIBUTO_VISIBLE_1.','.$d->ATRIBUTO_GLOBAL_1.','.$d->NOMBRE_ATRIBUTO_2.','.$d->VALOR_ATRIBUTO_2.','.$d->ATRIBUTO_VISIBLE_2.','.$d->ATRIBUTO_GLOBAL_2.','.$d->ATRIBUTO_DEFECTO_1.','.$d->ATRIBUTO_DEFECTO_2);
                fputcsv($file_handle, $linea, ';', " ");   
            }
            rewind($file_handle);
            fclose($file_handle);
        }
    }
    
    

    function pubWoo($id, $t){
        $var = $t==1? " CLAVE_PROD ":"''";
        $this->query="UPDATE FTC_ARTICULOS SET SKU = $var WHERE ID = $id";
        $res=$this->queryActualiza();
        $res = $res==1? array("status"=>'Ok', "mensaje"=>'Se actualizo correctamente'):array("status"=>'No', "mensaje"=>'No se pudo actializar el status');
        return $res;
    }

    function chgPart($doc, $part, $campo, $val){
        switch ($campo){
            case 'cant':
                $campo = 'CANTIDAD';
                break;
            case 'prec':
                $campo = 'PRECIO';
                break;
            case 'desc':
                $campo = 'DESC1';
                break;
        }

        $this->query = "UPDATE FTC_NV_DETALLE SET $campo = $val where documento = '$doc' and partida = $part AND STATUS = 0";
        //echo $this->query;
        $res = $this->queryActualiza() == 1? array("status"=>'ok', "mensjae"=>'Se actualizo'):array("status"=>'no', "mensjae"=>'No se actualizo');
        $act = $res['status']=='ok'? $this->actualizaTotalesNV($doc):'';
        return $res;
    }

    function actualizaTotalesNV($doc){
        /// EN EL DETALLE DEL DOCUMENTOS LOS VALORES DE IMPUESTOS IMP1 Y DESCUENTO DESC1 SON PORCENTAJES
        $this->query="UPDATE FTC_NV_DETALLE SET 
                                        SUBTOTAL = (CANTIDAD * PRECIO),
                                        TOTAL = ((CANTIDAD * PRECIO) - ((CANTIDAD * PRECIO)*(DESC1/100))) + ((CANTIDAD * PRECIO) * (IMP1/100))
                                        WHERE DOCUMENTO = '$doc' AND STATUS = 0";
        $this->queryActualiza();
        /// EN LA CABECERA DEL DOCUMENTOS LOS VALORES DE IMPUESTOS IMP1 Y DESCUENTO DESC1 SON IMPORTES
        $this->query="SELECT * FROM FTC_NV_DETALLE WHERE  DOCUMENTO = '$doc' AND STATUS = 0";
        $res=$this->EjecutaQuerySimple();
        while($tsarray=ibase_fetch_object($res)){
            $data[]=$tsarray;
        }
        $subTotal=0; $total = 0;
        foreach ($data as $k) {
            $subTotal =+ $k->SUBTOTAL;
            $desc =+ $k->SUBTOTAL * ($k->DESC1 / 100) ;
            $imp  =+ $k->SUBTOTAL * ($k->IMP1 / 100) ;
            $total =+ $k->TOTAL;
        }
        $this->query="UPDATE FTC_NV SET 
                                        DESC1= $desc,
                                        IVA = $imp,
                                        SUBTOTAL = $subTotal, 
                                        TOTAL = $total, 
                                        SALDO_FINAL = $total
                                        WHERE DOCUMENTO = '$doc' and STATUS = 'P'";
        //echo $this->query;
        $this->queryActualiza();
        //echo 'Hay que actualizar los totales';
    }


    function registraProdImg($file){
        $isbn = substr($file, 0, 13); $usuario=$_SESSION['user']->ID;
        $this->query="INSERT INTO FTC_ARTICULOS_IMG (ID_IMG, ID_ART, ISBN, CDB, RUTA, TIPO, NOMBRE, FECHA, STATUS, USUARIO, OBS) VALUES (NULL, NULL,'$isbn' ,'', 'C:\\xampp\\htdocs\\imagenes\\books\\', '', '$file', current_timestamp, 1, $usuario, 'Carga desde Productos' )";
        //echo $this->query;
        $this->grabaBD();
        return;
    }

    function infoCte($cte){
        $data=array(); $info=array();
        $this->query="SELECT c.*, (SELECT R.DESCRIPCION FROM REGIMEN_SAT R WHERE R.CLAVE = c.SAT_REGIMEN) AS regimen, (SELECT U.DESCRIPCION FROM USO_SAT U WHERE U.CLAVE = c.USO_CFDI) AS uso FROM CLIE01 c WHERE c.CLAVE_TRIM = trim('$cte')";
        $res=$this->EjecutaQuerySimple();
        while ($tsArray=ibase_fetch_object($res)) {
            $data[]=$tsArray;
        }
        foreach ($data as $k) {
            $info= array("nombre"=>$k->NOMBRE,
                         "rfc"=>$k->RFC,
                         "calle"=>$k->CALLE,
                         "int"=>$k->NUMINT,
                         "ext"=>$k->NUMEXT,
                         "colonia"=>$k->COLONIA, 
                         "cp"=>$k->CODIGO, 
                         "localidad"=>$k->LOCALIDAD, 
                         "municipio"=>$k->MUNICIPIO, 
                         "estado"=>$k->ESTADO, 
                         "tel"=>$k->TELEFONO,
                         "cfdi4"=>empty($k->CFDI4)? '':'checked',
                         "uso_cfdi"=>empty($k->USO_CFDI)? '':$k->USO_CFDI.' ('.utf8_encode($k->USO).') ' ,
                         "regimen"=>empty($k->SAT_REGIMEN)? '':$k->SAT_REGIMEN.' ('.utf8_encode($k->REGIMEN).') ' 
                        );
        }
        return $info;
    }

    function editCte($cte, $campo, $val){
            $this->query="SELECT $campo as val from CLIE01 WHERE CLAVE_TRIM = trim('$cte')";
            $res=$this->EjecutaQuerySimple();
            $row=ibase_fetch_object($res);
            $this->ftclog($cte, $campo, $val, 'CLIE01', $row->VAL);
            $this->query="UPDATE CLIE01 SET $campo = '$val' where CLAVE_TRIM = trim('$cte')";
            $this->queryActualiza();
        return;
    }

    function ftclog($idt, $campo, $val, $tabla, $actual){
        $usuario = $_SESSION['user']->NOMBRE;
        $this->query="INSERT INTO FTC_CHG_LOG (ID, TABLA, USUARIO, CAMPO, ACTUAL, NUEVO, FECHA, STATUS, ID_TABLA) 
            VALUES (NULL, '$tabla', '$usuario', '$campo', '$actual', '$val', current_timestamp, 0, '$idt')";
        $this->grabaBD();
        return;
    }

    function productoVM($val){
        $pos = strpos($val, ":" );
        if ($pos > 0 ){
            return array("status"=>'ok',"prod"=>$val);
        }else{
            $this->query="SELECT A.*, (SELECT coalesce(SUM(b.RESTANTE), 0) FROM ingresobodega b where b.producto = 'PGS'||A.ID ) - (SELECT coalesce(SUM(v.cantidad),0) from ftc_NV_detalle v where v.articulo = A.id and fecha >= '01.01.2024' and status != 8) as Existencia  FROM FTC_Articulos A WHERE CLAVE_PROD = '$val' and STATUS = 'A'";
            $r=$this->QueryProdVM();
            //print_r($r);
            //echo '<br/>tamaño de la cadena: '.strlen($r);
            if (strlen(@$r)>0){
                return array("status"=>'ok', "prod"=>$r);
            }else{
                return array("status"=>'no', "prod"=>@$r);
            }
        }
    }

    function actObsNvm($obs , $nvm, $doc){
        $this->query="UPDATE FTC_NV SET OBSERVACIOn = '$obs', NV_MANUAL = '$nvm' where DOCUMENTO = '$doc'";
        $this->EjecutaQuerySimple();
        return array("status"=>'ok');
    }

    function catalogo(){
        $data=array();
        //$this->query="SELECT p.*,
        //    (SELECT coalesce(SUM(b.RESTANTE), 0) FROM ingresobodega b where b.producto = p.CLAVE) - (SELECT coalesce(SUM(v.cantidad),0) from ftc_NV_detalle v where v.articulo = p.CLAVE_FTC and fecha >= '8.2.2023')  as Existencia 
        // from PRODUCTO_FTC p";
        $this->query="SELECT p.* from PRODUCTO_FTC p";
        $res=$this->EjecutaQuerySimple();
        while ($tsArray=ibase_fetch_object($res)) {
            $data[]=$tsArray;
        }
        return $data;
    }

    function factG($docs, $tipo){
        $docs = substr($docs , 1 );
        $usuario = $_SESSION['user']->NOMBRE;
        $doc = explode(",", $docs);
        $this->query="SELECT MAX(FOLIO) + 1 AS FOLIO FROM FTC_FACTURAS";
        $res=$this->EjecutaQuerySimple();
        $row = ibase_fetch_object($res);
        $folio = $row->FOLIO;
        $documento = 'LH'.$folio;
        $this->query="INSERT into ftc_facturas (idf, documento, SERIE, FOLIO, FORMADEPAGOSAT, VERSION, TIPO_CAMBIO, METODO_PAGO, REGIMEN_FISCAL, LUGAR_EXPEDICION, MONEDA, TIPO_COMPROBANTE, CONDICIONES_PAGO, SUBTOTAL, IVA, IEPS, DESC1, DESC2, TOTAL, SALDO_FINAL, CLIENTE, USO_CFDI, STATUS, USUARIO, FECHA_DOC, FECHAELAB, IDIMP, UUID)
            values (null, '$documento', 'LH', $folio, 'PUE', 4.0, 1, '$tipo', 616 , '14080', 'MXN', 'I', 'Contado', 0,0,0,0,0,0,0, '36', 'S01', '0', '$usuario', current_timestamp, current_timestamp, 0, null)";
        $this->grabaBD();

        for ($i=0; $i <count($doc) ; $i++){
            $docu = $doc[$i];
            $this->query="EXECUTE PROCEDURE Fact_Global ($docu, '01', '$usuario', '$documento', $folio)";
            $this->EjecutaQuerySimple();
        }

        $this->query="UPDATE FTC_FACTURAS SET 
                                SUBTOTAL = (SELECT SUM(SUBTOTAL) FROM FTC_FACTURAS_DETALLE WHERE DOCUMENTO = '$documento'),
                                TOTAL = (SELECT SUM(TOTAL) FROM FTC_FACTURAS_DETALLE WHERE DOCUMENTO = '$documento'),
                                desc1 = (SELECT SUM(desc1) FROM FTC_FACTURAS_DETALLE WHERE DOCUMENTO = '$documento')
                                where documento = '$documento'";
        $this->queryActualiza();

        $this->query="UPDATE ftc_ctrl_facturas set folio = $folio where idff = 1";
        $this->queryActualiza();
        return;
    }

    function ccat($val, $id){
        $this->query="UPDATE FTC_ARTICULOS SET CATEGORIA = '$val' where id = $id ";
        $res=$this->EjecutaQuerySimple();
        return array("status"=>'ok');
    }

    function actEnvio($docf, $correo, $m){
        $this->query="INSERT INTO FTC_LOG_ENVIO (ide, documento, direccion, fecha, mensaje) 
                        VALUES (null, '$docf', '$correo', current_timestamp, '$m')";
        $this->grabaBD();
        return;
    }

    function cancelAdmin($doc){
        $this->query="UPDATE FTC_FACTURAS SET STATUS = 8 WHERE DOCUMENTO = '$doc' and (UUID is null or UUID = '')";
        $this->queryActualiza();
        $this->query="UPDATE FTC_FACTURAS_DETALLE SET STATUS = 8 WHERE DOCUMENTO = '$doc' and (SELECT UUID FROM FTC_FACTURAS WHERE DOCUMENTO = '$doc' and (UUID is null or UUID = '')) is null ";
        $this->queryActualiza();

        $this->query="UPDATE FTC_NV SET METODO_PAGO = '', status = 'P' WHERE METODO_PAGO = '$doc'";
        $this->queryActualiza();

        return array("status"=>'ok', 'msg'=>'Cancelado');
    }

    function timbresCarga(){
        $this->query="SELECT COALESCE(SUM(CANTIDAD),0) AS timbres from FTC_CTR_TIMBRES WHERE VIGENCIA >=0";
        $res=$this->EjecutaQuerySimple();
        $row = ibase_fetch_row($res);
        return $timbres=$row[0];
    }

    function timbresUso(){
        $this->query="SELECT count(uuid) as timbres FROM FTC_FACTURAS WHERE FECHAELAB >= '15.03.2024' AND UUID IS NOT NULL";
        $res=$this->EjecutaQuerySimple();
        $row = ibase_fetch_row($res);
        return $timbres=$row[0];
    }   

}?>