
<div class="container">
        <!-- Marketing Icons Section -->
        <div class="row">
            <div class="col-lg-12">
                <h3 class="page-header">
                    <!--<img class="img-ico" src="app/views/images/logob.jpg">-->
                </h3>
            </div>
            <div>
                <label>Bienvenido: <?php echo $usuario?></label>
            </div>
            <br/>
        <div class="row">
            <div class="col-lg-12">
                <h3 class="page-header">
                    <!--<img class="img-ico" src="app/views/images/logob.jpg">-->
                </h3>
            </div>

             <?php 
                $asignacion = 0;
                $secuencia = 0;
                $admon = 0;
                $total = 0;
                $recep = 0;
                foreach ($logistica as $caja):
                if($caja->STATUS_RECEPCION == 0){
                    $asignacion = $caja->CAJA;
                }elseif ($caja->STATUS_RECEPCION == 1 ) {
                    $secuencia = $caja->CAJA;
                }elseif ($caja->STATUS_RECEPCION == 2) {
                    $admon = $caja->CAJA;
                }elseif ($caja->STATUS_RECEPCION == 3) {
                    $recep = $caja->CAJA;
                }
                $total = $admon +  $asignacion + $secuencia;
             ?> 
            <?php endforeach ?>

            <div class="col-md-12">
               <div class="panel panel-default">
                   <div class="panel-heading"> 
                       <h4>Recepcion de Logistica </h4>
                   </div>
                   <div class="panel-body">
                       <p>Total de Documento pendientes <?php echo $total?></p>
                       <p><font color="blue">En Asignacion de Unidad </font><font color="red">&nbsp;:&nbsp; <?php echo $asignacion?></font></p>
                       <p><font color="blue">En Secuencia </font><font color="red">&nbsp;:&nbsp;<?php echo $secuencia?></font></p>
                       <p><font color="blue">En Administracion de Ruta </font><font color="red">&nbsp;:&nbsp;<?php echo $admon?></font></p>
                       <p><font color="blue">Documentos Pendientes  por Recibir </font><font color="red">&nbsp;:&nbsp;<?php echo $recep?></font></p>
                       <center><a href="index.php?action=recibirLogistica&ruta=a" class="btn btn-default"><p class="btn btn-info"><?php echo $admon+$recep+$secuencia ?></p></a></center>
                   </div>
               </div>
            </div>
              <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4><i class="fa fa-list-alt"></i> Recibir Orden de compra</h4>
                    </div>
                    <div class="panel-body">
                        <p>Recepcion de producto </p>
                        <center><a href="index.php?action=recepcionOC" class="btn btn-default"><img src="app/views/images/File-warning-icon.png"></a></center>
                    </div>
                </div>
            </div>
             <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4>Recibir Mercancia </h4>
                    </div>
                    <div class="panel-body">
                        <p><center><a href="index.php?action=recibirMercancia" class="btn btn-default"> Recibir Facturas / PreFacturas</a></center></p>
                         <p><center><a href="index.php?action=verFTCNCpendientes&docnc=" class="btn btn-default">Ver Notas de Credito Pendientes</a></center></p>
                    </div>
                </div>
            </div>
               <?php if(count($Solicitudes)>0){?>
             <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading"> 
                        <h4>Ver Solicitudes de Bodega Automaticos</h4>
                    </div>
                    <div class="panel-body">
                        <p>Solicitudes a Bodega. <font color="red"><?php echo count($Solicitudes)?></font></p>
                        <center><a href="index.php?action=verSolBodega" class="btn btn-default"><img class="img-ico" src="app/views/images/order.png"></a></center>
                    </div>
                </div>
            </div>             
            <?php }?>
              <?php if(count($vales)>0){?>
             <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading"> 
                        <h4>Ver Vales</h4>
                    </div>
                    <div class="panel-body">
                        <p>Vales de Bodega. <font color="red"><?php echo count($vales)?></font></p>
                        <center><a href="index.php?action=verValesBodega" class="btn btn-default"><img class="img-ico" src="app/views/images/order.png"></a></center>
                    </div>
                </div>
            </div>             
            <?php }?>
            <div class="col-md-2 cu-panel-clie">
               <div class="panel panel-default">
                   <div class="panel-heading"> 
                       <h4>Ver Productos Ingresados</h4>
                   </div>
                   <div class="panel-body">
                       <center><a href="index.php?action=verIngresoBodega" class="btn btn-default"><img class="img-ico" src="app/views/images/Bodega/VerIngresoBodega.png" width="50" height="70"></a></center>
                   </div>
               </div>
           </div>
            <div class="col-md-2 cu-panel-clie">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4>Recepcion de producto </h4>
                    </div>
                    <div class="panel-body">
                        <center><a href="index.php?action=recepcionOC" class="btn btn-default"><img class="img-ico" src="app/views/images/Bodega/RecibirOC.png" width="50" height="70"></a></center>
                    </div>
                </div>
            </div>
            <div class="col-xs-12 col-md-2 cu-panel-clie">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4>Logistica / Bodega / CxC</h4>
                    </div>
                    <div class="panel-body">
                        <center><a href="index.php?action=seguimientoCajas" class="btn btn-default" target="popup" onclick="window.open(this.href, this.target, 'width=1200, height=900')"><img class="img-ico" src="app/views/images/Bodega/FoliosFac.png" width="50" height="70"></a></center>
                    </div>
                </div>
            </div>
              <div class="col-md-2 cu-panel-clie">
               <div class="panel panel-default">
                   <div class="panel-heading"> 
                       <h4>Alta de Productos a Bodega </h4>
                   </div>
                   <div class="panel-body">
                       <center><a href="index.php?action=IngresoBodega" class="btn btn-default"><img class="img-ico" src="app/views/images/Bodega/IngresarBodega.png" width="50" height="70"></a></center>
                   </div>
               </div>
           </div>
             <div class="col-md-2 cu-panel-clie">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4>Ver e Imprimir Recepciones</h4>
                    </div>
                    <div class="panel-body">
                        <p><br></p>
                        <center><a href="index.php?action=verRecepDev" class="btn btn-default"><img class="img-ico" src="app/views/images/Bodega/ImprimirRecep.png" width="50" height="70"></a></center>
                    </div>
                </div>
            </div>
            
            <div class="col-md-2 cu-panel-clie">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4>Aduana Bodega</h4>
                    </div>
                    <div class="panel-body">
                        <p><br></p>
                        <center><a href="index.php?action=verRecepSinProcesar" class="btn btn-default"><img class="img-ico" src="app/views/images/Bodega/AduanaBodega.png" width="50" height="70"></a></center>
                    </div>
                </div>
            </div>

           <div class="col-md-2 cu-panel-clie">
                <div class="panel panel-default">
                    <div class="panel-heading"> 
                        <h4>Inventario Fisico</h4>
                    </div>
                    <div class="panel-body">
                        <center><a href="index.php?action=verInventarioBodega" class="btn btn-default"><img class="img-ico" src="app/views/images/Bodega/InventarioFisico.png" width="50" height="70"></a></center>
                    </div>
                </div>
                </div> 

                 <div class="col-md-2 cu-panel-clie">
                <div class="panel panel-default">
                    <div class="panel-heading"> 
                        <h4>Orden Interna</h4>
                    </div>
                    <div class="panel-body">
                        <center><a href="index.php?action=nuevaOrdenInterna" class="btn btn-default"><img class="img-ico" src="app/views/images/Bodega/OrdenInterna.png" width="50" height="70"></a></center>
                    </div>
                </div>
                </div> 
            <?php if(count($oci)>0){?>
            <div class="col-md-2 cu-panel-clie">
                <div class="panel panel-default">
                    <div class="panel-heading"> 
                        <h4> Ver Ordenes de Compra Interna</h4>
                    </div>
                    <div class="panel-body">
                        <center><a href="index.php?action=verOCI" class="btn btn-default"><img class="img-ico" src="http://icons.iconarchive.com/icons/martz90/hex/72/car-icon.png"></a></center>
                    </div>
                </div>
                </div> 
            <?php }?>


              <div class="col-md-2 cu-panel-clie">
                <div class="panel panel-default">
                    <div class="panel-heading"> 
                        <h4>Recepcion de Orden de Compra Interna</h4>
                    </div>
                    <div class="panel-body">
                        <center><a href="index.php?action=recepcionOCBdg" class="btn btn-default"><img class="img-ico" src="app/views/images/Bodega/RecepOrdenInterna.png" width="50" height="70"></a></center>
                    </div>
                </div>
                </div> 

            
              <div class="col-md-2 cu-panel-clie">
                <div class="panel panel-default">
                    <div class="panel-heading"> 
                        <h4>Ver Devoluciones a Proveedor</h4>
                    </div>
                    <div class="panel-body">
                        <center><a href="index.php?action=verDevProv" class="btn btn-default"><img class="img-ico" src="app/views/images/Bodega/VerDev.png" width="50" height="70"></a></center>
                    </div>
                </div>
                </div> 
            

              <div class="col-md-2 cu-panel-clie">
                <div class="panel panel-default">
                    <div class="panel-heading"> 
                        <h4>Mermas</h4>
                    </div>
                    <div class="panel-body">
                        <center><a href="index.php?action=verMerma" class="btn btn-default"><img class="img-ico" src="app/views/images/Bodega/Merma.png" width="50" height="70"></a></center>
                    </div>
                </div>
                </div> 
                <div class="col-md-2 cu-panel-clie">
                <div class="panel panel-default">
                    <div class="panel-heading"> 
                        <h4>Inventario Bodega Mensual</h4>
                    </div>
                    <div class="panel-body">
                        <center><a href="index.php?action=invBodegaGral" class="btn btn-default"><img class="img-ico" src="app/views/images/Bodega/InvFisicoMens.png" width="50" height="70"></a></center>
                    </div>
                </div>
                </div> 
                
            <!--
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading"> 
                        <h4> Lista de Pedidos</h4>
                    </div>
                    <div class="panel-body">
                        <p>TODOS LOS PEDIDOS.</p>
                        <center><a href="index.php?action=lista_pedidos" class="btn btn-default"><img class="img-ico" src="app/views/images/order.png"></a></center>
                    </div>
                </div>
            </div> 
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading"> 
                        <h4>  Ordenes por Categorias:</h4>
                    </div>
                    <div class="panel-body">
                        <p>JARCERIA Y SEGURIDAD INDUSTRIAL / ACEROS Y PERFILES.</p>
                        <center><a href="index.php?action=ordcompCat&cat=2" class="btn btn-default"><img class="img-ico" src="app/views/images/order.png"></a></center>
                    </div>
                </div>
            </div>  

            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading"> 
                        <h4>  Ordenes por Categorias:</h4>
                    </div>
                    <div class="panel-body">
                        <p>PLOMERIA / FERRETERIA / H. MANUAL.</p>
                        <center><a href="index.php?action=ordcompCat&cat=3" class="btn btn-default"><img class="img-ico" src="app/views/images/order.png"></a></center>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading"> 
                        <h4>  Ordenes por Categorias:</h4>
                    </div>
                    <div class="panel-body">
                        <p>ADHESIVOS / CERRAJERIA Y HERRAJES / MEDICION.</p>
                        <center><a href="index.php?action=ordcompCat&cat=4" class="btn btn-default"><img class="img-ico" src="app/views/images/order.png"></a></center>
                    </div>
                </div>
            </div>  
            
            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading"> 
                        <h4>  Ordenes por Categorias:</h4>
                    </div>
                    <div class="panel-body">
                        <p>CONSTRUCCION Y PINTURAS / FIJACION Y SOPORTE.</p>
                        <center><a href="index.php?action=ordcompCat&cat=5" class="btn btn-default"><img class="img-ico" src="app/views/images/order.png"></a></center>
                    </div>
                </div>
            </div>

            <div class="col-md-4">
                <div class="panel panel-default">
                    <div class="panel-heading"> 
                        <h4> Ordenes por Categorias:</h4>
                    </div>
                    <div class="panel-body">
                        <p>HERRAMIENTA ELECTRICA / ACCESORIOS Y CONSTRUCCION DE HERRAMIENTA / ELECTRICO.</p>
                        <center><a href="index.php?action=ordcompCat&cat=6" class="btn btn-default"><img class="img-ico" src="app/views/images/order.png"></a></center>
                    </div>
                </div>
            </div>  
            -->
             <div class="col-md-2 cu-panel-clie">
                <div class="panel panel-default">
                    <div class="panel-heading">
                        <h4>Inventario Fisico Empaque</h4>
                    </div>
                    <div class="panel-body">
                        <p>Inventario Fisico (<font color= 'blue' size="3pxs"> <?php echo count($cajas)?></font>) Cajas</p>
                        <center><a href="index.php?action=invPatio" class="btn btn-default"><img class="img-ico" src="app/views/images/Bodega/InvEmpaque.png" width="50" height="70"></a></center>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css">
<link rel="stylesheet" href="/resources/demos/style.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.js"></script>
<script type="text/javascript">
     $("#buscar").click(function(){
            var id = document.getElementById("docv").value;
            if(id ==""){
                alert("Favor de capturar un documento.");
            }else{
                $.ajax({
                    url:'index.php',
                    type:'post',
                    dataType:'json',
                    data:{buscaDocv:id},
                    success:function(data){
                        var seg;
                        if(data.st == 'no'){
                            var s = '<font color="red"> No se encontro informacion del documento:  </font>';
                            var mensaje = id;
                        }else if(data.st == 'ok'){
                            var ventana = "'width=1800,height=1200'";
                            var s = 'Se encontro la informacion: ';
                            var mes = data.fechaCaja.substring(5,7);;
                            var anio = data.fechaCaja.substring(0,4);
                            var dia = data.fechaCaja.substring(8,10);
                            var seg ='<a href="index.php?action=seguimientoCajasDiaDetalle&anio='+ anio + '&mes='+ mes +'&dia='+ dia+'"  class="btn btn-info" target="popup" onclick="window.open(this.href, this.target,'+ ventana +' ); return false;" > Ver Seguimiento de Documentos </a>';
                            var mensaje = '<br/>Caja: ' + data.caja + '<br/> fecha del consecutivo: ' + data.fechaCaja + seg +'<br/> Status logistica: ' + data.logistica + '<br/> Status de la caja: ' + data.status;
                        }
                        var midiv = document.getElementById('resultado');
                         midiv.innerHTML = "<br/><p><font size='5pxs' color='blue'>&nbsp;&nbsp; " + s + " </font> <font size='5pxs' color='red'>"+ mensaje + "</font></p>";
                        //("status"=>'ok',"resultado"=>'ok', "idstatus"=>$status, "idprov"=>'('.$proveedor.')'.$nomprov,"ordenado"=>$ordenado,"rec_faltante"=>$faltante,  "idpalterno"=>'('.$cvealt.')'.$nomalt,"Ordenes"=>$data);
                    }
                });
            }
        });


</script>
    