<div class="container">
        <div class="row">
            <div class="col-lg-12">
                <h3 class="page-header"></h3>
            </div>
            <div>
                <label> Bienvenido: <?php echo $usuario?></label>
            </div>
            <?php if($_SESSION['user']->LETRA == 'G'){?>
            <p><input type="text" name="enviaNC" placeholder="Enviar Caja a NC" onchange="cajaNC(this.value)"></p>
            <?php }?>
            <br>
        <div class="col-md-2">    
            <div class="panel panel-default cu-panel-clie">
                <div class="panel-heading">
                  <center><a class="icoPre" title="BancosAyuda" href="app/views/DocsAyuda/ManualBancos.pdf" target="_blank"><img src="app/views/images/cuestion.png" alt="BancosAyuda" width="30" height="30"></a><h4>Catalogo de Bancos</h4></center>
                </div>
            <div class="panel-body">
                  <center><a href="index.coi.php?action=verBancos" class="btn btn-default"><img class="img-ico" src="app/views/images/Contabilidad/Bancos.png" width="50" height="70"></a></center>
            </div>
            </div>
        </div>   
        
        <div class="col-md-2">    
            <div class="panel panel-default cu-panel-clie">
                <div class="panel-heading">
                  <h4>Conciliacion</h4>
                </div> 
            <div class="panel-body">
                <br>
                  <center><a href="index.php?action=edoCta" class="btn btn-default"><img class="img-ico" src="app/views/images/Contabilidad/Consiliacion.png" width="50" height="70"></a></center>
            </div>
            </div>
        </div>   
        <div class="col-md-2">
        <div class="panel panel-default cu-panel-clie">
              <div class="panel-heading">
                  <h4><center>Estado de Cuenta Conciliado</center> </h4>
              </div>
              <div class="panel-body">
                  <center><a href="index.php?action=edoCta_docs" class="btn btn-default"><img class="img-ico" src="app/views/images/Contabilidad/EdoConciliado.png" width="50" height="70"></a></center>
              </div>
        </div>
        </div>
        <div class="col-md-2">
                <div class="panel panel-default cu-panel-clie">
                    <div class="panel-heading">
                        <h4> Catalogo de gastos</h4>
                    </div>
                    <div class="panel-body">
                        <center><a href="index.php?action=Catalogo_Gastos" class="btn btn-default"><img class="img-ico" src="app/views/images/Contabilidad/CatalogoGastos.png" width="50" height="70"></a></center>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="panel panel-default cu-panel-clie">
                    <div class="panel-heading">
                        <h4>Clasificaci??n de gastos</h4>
                    </div>
                    <div class="panel-body">
                        <center><a href="index.php?action=clasificacion_gastos" class="btn btn-default"><img class="img-ico" src="app/views/images/Contabilidad/ClasifGastos.png" width="50" height="70"></a></center>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="panel panel-default cu-panel-clie">
                    <div class="panel-heading">
                        <h4>Captura de gastos</h4>
                    </div>
                    <div class="panel-body">
                        <center><a href="index.php?action=form_capturagastos" class="btn btn-default"><img class="img-ico" src="app/views/images/Contabilidad/Gastos.png" ></a></center>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="panel panel-default cu-panel-clie">
                    <div class="panel-heading">
                        <h4>Consecutivo de Compras</h4>
                    </div>
                    <div class="panel-body">
                        <p></p>
                        <center><a href="index.php?action=verCompras" class="btn btn-default"><img class="img-ico" src="http://icons.iconarchive.com/icons/designbolts/seo/64/Pay-Per-Click-icon.png"></a></center>
                    </div>
                </div>   
            </div>
            <div class="col-md-2">
            <div class="panel panel-default cu-panel-clie">
                <div class="panel-heading">
                    <h4>Recepci&oacute;n de pagos</h4>
                </div>
                <div class="panel-body">
                    <center><a href="index.php?action=listadoXrecibir" class="btn btn-default"><img class="img-ico" src="app/views/images/Contabilidad/RePagos.png" width="50" height="70"></a></center>
                </div>
            </div>
        </div>
            <div class="col-md-2">
                 <div class="panel panel-default cu-panel-clie">
                    <div class="panel-heading">
                    <h4>Carga Cargos/Reg Directo Edo Cuenta</h4>
                    </div>
                <div class="panel-body">
                   <center><a href="index.php?action=form_capruracrdirecto" class="btn btn-default"><img class="img-ico" src="app/views/images/Contabilidad/CargaGastos.png" width="50" height="70"></a></center>
                </div>
                </div>
            </div>

         <div class="col-md-2">
                <div class="panel panel-default cu-panel-clie">
                    <div class="panel-heading">
                        <h4>Transferencias y Prestamos</h4>
                    </div>
                    <div class="panel-body">
                        <center><a href="index.php?action=transfer" class="btn btn-default"><img class="img-ico" src="app/views/images/Contabilidad/TransyPrest.png" width="50" height="70"></a></center>
                    </div>
                </div>
        </div>
        <div class="col-md-2">
                <div class="panel panel-default cu-panel-clie">
                    <div class="panel-heading">
                        <h4>Carga de deudores</h4>
                    </div>
                    <div class="panel-body">  
                        <center><a href="index.php?action=deudores" class="btn btn-default"><img class="img-ico" src="app/views/images/Contabilidad/CargaDeudores.png" width="50" height="70"></a></center>
                    </div>
                </div>
        </div>
                <div class="col-md-2">
          <div class="panel panel-default cu-panel-clie">
              <div class="panel-heading">
                  <h4>Ver Abonos de Estado de Cuenta</h4>
              </div>
              <div class="panel-body">
                  <center><a href="index.php?action=edoCta_docs" class="btn btn-default"><img src="app/views/images/Cobranza/dinero_v1.jpg"  width="89" height="69"></a></center>
              </div>
          </div>
        </div>
        <div class="col-xs-12 col-md-2">
                <div class="panel panel-default cu-panel-clie">
                    <div class="panel-heading">
                        <h4> Imprimir Aplicaciones</h4>
                    </div>
                    <div class="panel-body">
                        <center><a href="index.php?action=verAplicaciones" class="btn btn-default"><img src="app/views/images/dollar-collection-icon.png" width="89" height="69"></a></center>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="panel panel-default cu-panel-clie">
                    <div class="panel-heading">
                        <h4> CAPTURA DE ABONOS</h4>
                    </div>
                    <div class="panel-body">
                        <center><a href="index.php?action=selectBanco" class="btn btn-default"><img src="http://icons.iconarchive.com/icons/designbolts/seo/64/Pay-Per-Click-icon.png" width="89" height="69"></a></center>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="panel panel-default cu-panel-clie">
                    <div class="panel-heading">
                        <h4>Cancelacion de Pagos</h4>
                    </div>
                    <div class="panel-body">
                        <center><a href="index.php?action=buscaPagos" class="btn btn-default"><img src="http://icons.iconarchive.com/icons/designbolts/seo/64/Pay-Per-Click-icon.png" width="89" height="69"></a></center>
                    </div>
                </div>
            </div>
            <div class="col-md-2">    
            <div class="panel panel-default cu-panel-clie">
                <div class="panel-heading">
                  <center><a class="icoPre" title="Tipo Polizas"  target="_blank"><img src="app/views/images/cuestion.png" alt="TiposPolizas" width="30" height="30"></a><h4>Tipos Polizas</h4></center>
                </div>
            <div class="panel-body">
                  <center><a href="index.coi.php?action=tipoPoliza" class="btn btn-default"><img class="img-ico" src="app/views/images/Contabilidad/Bancos.png" width="50" height="70"></a></center>
            </div>
            </div>
        </div>
<form action="index.php" method="post" id="migrar">
    <input type="hidden" name="docf" id="doc" value="<?php echo $docf?>">
    <input type="hidden" name="refacturarFecha" value="">
    <input type="hidden" name="opcion" value="3">
    <input type="hidden" name="nfecha" value="">
    <input type="hidden" name="obs" placeholder="Observaciones" value="X" id="obs" size="250">
</form>
    
<script src="//code.jquery.com/jquery-1.10.2.js"></script>
<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
<link rel="stylesheet" href="//code.jquery.com/ui/1.12.0/themes/base/jquery-ui.css">
<link rel="stylesheet" href="/resources/demos/style.css">
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.0/jquery-ui.js"></script>
<script type="text/javascript">


        function cajaNC(idc){
            if(confirm('Desea Enviar la caja '+ idc +' para entrada al almacen?')){
                $.ajax({
                    url:'index.v.php',
                    type:'post',
                    dataType:'json',
                    data:{cajaNC:idc},
                    success:function(data){
                        if(data.status=='ok'){
                            alert('Listo, la prefactura aparecera en la pantalla de Recibir Mercancia, opcion Recibir Facturas / Prefacturas');
                        }else{
                            alert(data.mensaje);
                        }
                    }
                });
            }
        }
        function copiaFP(docf){
            var docf = docf.toUpperCase();
            $.ajax({
                url:'index.v.php',
                type:'post',
                dataType:'json',
                data:{conteoCopias:docf},
                success:function(data){
                    if(data.status=='ok'){
                        alert("Ya existen "+data.copias+" facturas, las cuales son: "+ data.facturas);
                        if(confirm('Copiar la factura' + docf)){
                           $.ajax({
                           url:'index.v.php',
                           type:'post',
                           dataType:'json',
                           data:{copiaFP:docf},
                           success:function(data){
                               alert('Se Genero la factura --> ' + data.docf + '  <--  para su revision y timbrado');
                           }
                           });    
                        }
                    }else if(data.status =='noExiste'){
                        alert("La factura " + docf + ', no existe o no podemos encontrarla, favor de revisar la informacion');
                        return;
                    }else{
                            if(confirm('Copiar la factura' + docf)){
                                $.ajax({
                                url:'index.v.php',
                                type:'post',
                                dataType:'json',
                                data:{copiaFP:docf},
                                success:function(data){
                                    alert('Se Genero la factura --> ' + data.docf + '  <--  para su revision y timbrado');
                                    }
                                });    
                            }
                        }           
                    }
            });
        }

        function creaCaja(docf){
            if(docf == ''){
                return;
            }
            var docf = docf.toUpperCase();
            
            if(confirm('Crear la caja para la facutra ' + docf)){
                $.ajax({
                    url:'index.php',
                    type:'post',
                    dataType:'json',
                    data:{creaCaja:docf},
                    success:function(data){
                        alert(data.mensaje);
                    }
                });
            }
        }

        function migrar(docf){
            var docf = docf.toUpperCase();
            if(docf == ''){
                return;
            }
            if(docf.substring(0,2) == 'FP'){
                alert('Solo se migran facturas de SAE, para copiar facturas de Pegaso favor de hacerlo desde copiar Factura.');
                return;
            }
            if(confirm("Se envia a migracion de facturas" + docf)){
                document.getElementById('doc').value=docf;
                var form=document.getElementById('migrar');
                form.submit();

            }else{
                alert('No se proceso la factura');
                document.getElementById("mf").value="";
            }
        }

        function factura(docf){
            var docf = docf.toUpperCase();
            if(docf == ''){
                return;
            }
            if(confirm('Busca: ' + docf )){
                $.ajax({
                url:'index.php',
                type:'post',
                dataType:'json',
                data:{verFactura:docf},
                success:function(data){
                    if(data.status == 'ok'){
                        alert('Si existe');
                    }
                    },
                error:function(data){
                    var verfact="index.php?action=verFactura&docf="+docf; 
                    window.open(verfact, 'popup', 'width=1200,height=820');
                    return false;
                    }
                })    
            }else{
                document.getElementById("vf").value="";
            }
        }

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