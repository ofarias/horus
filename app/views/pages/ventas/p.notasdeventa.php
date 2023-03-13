<br /><br />
<!--
<style type="text/css">
    td.details-control {
        background: url('app/views/images/cuadre.jpg') no-repeat center center;
        cursor: pointer;
    }
    tr.shown td.details-control {
        background: url('app/views/images/cuadre.jpg') no-repeat center center;
    }
</style>
-->
<div class="row">
                <div class="col-lg-12">
                    <div class="panel panel-default">
                        <div class="panel-heading">
                           <font size="5px"> Notas de venta </font>
                           <br/>
                           Semana <input type="radio" name="p1" class="temp" value="s" <?php echo $p == 's'? 'checked':''?>> &nbsp;&nbsp;
                           Mes <input type="radio" name="p1" class="temp" value="m" <?php echo $p == 'm'? 'checked':''?> > &nbsp;&nbsp;
                           Año <input type="radio" name="p1" class="temp" value="a" <?php echo $p == 'a'? 'checked':''?> > &nbsp;&nbsp;
                           Todas <input type="radio" name="p1" class="temp" value="t"  <?php echo $p == 't'? 'checked':''?>> &nbsp;&nbsp;
                           Del &nbsp;&nbsp;<font color ="black"><input type="date" name="fi" id="fi" value="01.01.1990"></font> &nbsp;&nbsp; al &nbsp;&nbsp;<font color ="black"><input type="date" name="ff" value="01.01.1990" id="ff"></font>&nbsp;&nbsp;<label class="ir">Ir</label>
                        </div>
                           <div class="panel-body">
                            <div class="table-responsive">                            
                                <table class="table table-striped table-bordered table-hover" id="dataTables-nv">
                                    <thead>
                                        <tr>
                                            <th> S </th>
                                            <th> F </th>
                                            <th> Nota  </th>
                                            <th> Cliente </th>
                                            <th> Fecha Doc<br/><font color="blue">Fecha Elab</font> <br/> Nota Manual</th>
                                            <th> Status </th>
                                            <th> Productos <br/> Partidas </th>
                                            <th> Piezas </th>
                                            <th> Subtotal </th>
                                            <th> IVA </th>
                                            <th> IEPS </th>
                                            <th> Descuentos </th>
                                            <th> Total </th>
                                            <th> Saldo </th>
                                            <th> Forma de Pago </th>
                                            <th> Factura </th>
                                            <th> Vendedor </th><
                                            <th> Impresion </th>

                                        </tr>
                                    </thead>
                                  <tbody>
                                        <?php
                                        foreach ($info as $i):
                                            $status='';
                                            switch($i->STATUS){
                                                case 'P':
                                                    $status = 'Pendiente';
                                                    break;
                                                case 'F':
                                                    $status = 'Facturado';
                                                    break;
                                                case 'R':
                                                    $status = 'Facturado Parcial';
                                                    break;
                                                case 'C':
                                                    $status = 'Cancelada';
                                                    break;
                                                default:
                                                    $status= '';
                                                    break;
                                            }
                                        ?>
                                       <tr>
                                            <td WIDTH="1"><?php echo $i->SERIE?></td>
                                            <td WIDTH="1"><?php echo $i->FOLIO?></td>
                                            <td WIDTH="3" class="details-control" ><a class="detalles" nv="<?php echo $i->DOCUMENTO?>"><?php echo $i->DOCUMENTO?></a> <br/> <a class="copiar" doc="<?php echo $i->DOCUMENTO?>"><font color="blue">copiar</font></a><br/><font color="purple"><?php echo $i->NV_MANUAL?></font></td>
                                            <td ><?php echo '('.$i->CLIENTE.') '.$i->NOMBRE?><br/></td>
                                            <td><?php echo $i->FECHA_DOC?><br/><font color="blue"><?php echo $i->FECHAELAB?></font></td>
                                            <td WIDTH="3"><?php echo $status?></td>
                                            <td align="center" WIDTH="3"><?php echo $i->PROD?></td>
                                            <td align="center" WIDTH="3"><?php echo $i->PIEZAS?> </td>
                                            <td align="right"><?php echo '$ '.number_format($i->SUBTOTAL,2)?></td>
                                            <td align="right"><?php echo '$ '.number_format($i->IVA,2)?></td>
                                            <td align="right"><?php echo '$ '.number_format($i->IEPS,2)?></td>
                                            <td align="right"><?php echo '$ '.number_format($i->DESC1,2)?></td>
                                            <td align="right"><?php echo '$ '.number_format($i->TOTAL,2)?></td>
                                            <td align="right"><?php echo '$ '.number_format($i->SALDO_FINAL,2)?></td>
                                            <td align="center"><?php echo $i->FP?></td>
                                            <td align="center"><?php if(empty($i->METODO_PAGO)){?>
                                                <?php }else{?>
                                                    <a href="index.cobranza.php?action=envFac&docf=<?php echo $i->METODO_PAGO?>" onclick="window.open(this.href, this.target, 'width=1000, height=800'); return false;"> <font color="green"><b><?php echo $i->METODO_PAGO?></b></font></a>

                                                    <br/>
                                                    <?php if(isset($i->F_UUID)){?>
                                                        <a href="/Facturas/facturaPegaso/<?php echo $i->METODO_PAGO.'.xml'?>" download>  <img border='0' src='app/views/images/xml.jpg' width='25' height='30'></a>
                                                        <a href="index.php?action=imprimeFact&factura=<?php echo $i->METODO_PAGO?>" onclick="alert('Se ha descargado tu factura.')"><img border='0' src='app/views/images/pdf.jpg' width='25' height='30'></a>
                                                        <br/>
                                                        <?php if($i->F_FORMADEPAGOSAT =='PPD'){?>
                                                            <label class="genCep" factura="<?php echo $i->METODO_PAGO?>">Genera CEP</label>
                                                        <?php }else{?>
                                                            <label><?php echo $i->F_FORMADEPAGOSAT?></label>
                                                        <?php }?>    
                                                    <?php }else{?>
                                                        <a onclick="timbrar('<?php echo $i->METODO_PAGO?>')" >Timbrar</a>
                                                    <?php }?>
                                                <?php }?>
                                                <?php echo $i->FACTURAS?>
                                            </td>
                                            <td><?php echo $i->USUARIO?></td>
                                            <td><input type="button" name="" value="Imprimir N.V." class="btn-sm  btn-primary print" nv="<?php echo $i->DOCUMENTO?>"></td>
                                        </tr>               
                                        <?php endforeach; ?>

                                 </tbody>
                                 </table>
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

    var p = '';
    
    $(".genCep").click(function(){
        let doc = $(this).attr('factura')
        $.confirm({
            title: 'Generar Comprobante de Pago',
            columnClass: 'col-md-6',
            content: '¿Seguro que deseas generar el Comprobante de pago de la factura '+ doc + ' ? <br/>' +
            '<br/> <label> Banco Origen:&nbsp;&nbsp;&nbsp;</label> <input type="text" class="bancoO" oninput="this.value = this.value.toUpperCase()" >' +
            '<br/> <label> Cuenta Origen:&nbsp;&nbsp; <input type="text" placeholder="Deben ser 10 o 13 caracteres" class="cuentaO" >' +
            '<br/> <label> Banco Destino:&nbsp;&nbsp; <input type="text" class="bancoD" oninput="this.value = this.value.toUpperCase()">' +
            '<br/> <label> Cuenta Destino:&nbsp; <input type="text" placeholder="Deben ser 10 o 13 caracteres" class="cuentaD">' +
            '<br/> <label> Fecha Deposito:&nbsp; <input type="date" class="fecha" >' +
            '<br/> <label> Monto Deposito:&nbsp; <input type="text" class="monto">' +
            '<br/> <label> Tipo pago:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; <select class="tipo">'+
            '<option value="sel">Tipo de Pago</option>' +     
            '<option value="01">Efectivo</option>' +     
            '<option value="03">Transferencia</option>' +     
            '<option value="02">Cheque Nominativo</option>' +     
            '<option value="28">Tarjeta de Debito</option>' +     
            '<option value="04">Tarjeta de Credito</option>' +
            '<option value="99">Por Definir</option>' +     
            '</select>'    

            ,
            buttons: {
                Si: function () {
                    let bancoO = this.$content.find('.bancoO').val();
                    let cuentaO = this.$content.find('.cuentaO').val();
                    let bancoD = this.$content.find('.bancoD').val();
                    let cuentaD = this.$content.find('.cuentaD').val();
                    let fecha = this.$content.find('.fecha').val();
                    let monto = this.$content.find('.monto').val();
                    let tipo = this.$content.find('.tipo').val();
                    
                    if (cuentaO.length == 10 || cuentaO.length == 13){   
                    }else{
                        $.alert('La longitud de la cuenta origen debe de ser de 10 o 13 numeros')
                        return false 
                    }
                    if (cuentaD.length == 10 || cuentaD.length == 13){
                    }else{
                        $.alert('La longitud de la cuenta destino debe de ser de 10 o 13 numeros')
                        return false 
                    }
                    if(isNaN(monto)){
                       $.alert('Debe ingresar un monto correcto')
                       return false 
                    }
                    if(isNaN(cuentaO)){
                       $.alert('Favor de revisar la cuenta origen, solo se admiten numeros')
                       return false  
                    }
                    if(isNaN(cuentaD)){
                       $.alert('Favor de revisar la cuenta destino, solo se admiten numeros')
                       return false  
                    }
                    if(tipo == 'sel'){
                       $.alert('Tipo de pago no valido.')
                       return false    
                    }
                    $.ajax({
                        type: "POST",
                        url: 'index.v.php',
                        dataType: "json",
                        data: {genCepNV:1, doc, bancoO, cuentaO, bancoD, cuentaD, fecha, monto, tipo },
                        beforeSend: function () {
                            var popup = $('#pop');
                            popup.css({
                                'position': 'absolute',
                                'left': ($(window).width() / 2 - $(popup).width() / 2) + 'px',
                                'top': ($(window).height() / 2 - $(popup).height() / 2) + 'px'
                            });
                            popup.show();
                        },
                        success: function (data)
                        {
                            $('#pop').hide();
                            if (data.status == "ok") {
                                $.alert(data.mensaje)
                                //window.open('index.v.php?action=nv2&doc='+data.NNV+'&idf=0', '_self')
                            } else {
                                //$.alert('Algo salió mal, favor de verificarlo con el administrador de sistema, código de error: ' + data.response);
                                //console.log("XML:"+data.status);
                            }
                        }
                    });
                },
                Cancelar: {
                }
            }
        })
    })

    $(".ir").click(function(){
        var fi = document.getElementById('fi').value
        var ff = document.getElementById('ff').value
        var p = $(".temp:checked").val()
        if(fi != ""){
            p = 'p'
        }
        window.open("index.v.php?action=verNV&p="+p+"&fi="+ fi + "&ff="+ff, "_self" )
    })

    $(".detalles").click(function(){
        var nv = $(this).attr('nv')
        //alert('Detalles de la Nota ' + nv)
        window.open("index.v.php?action=nv2&doc="+nv+"&idf=0", '_blank')
        //window.open("index.v.php?action=nv2&doc="+nv+"&idf=0", "_self")
    })

    $(".copiar").click(function(){
        var doc = $(this).attr('doc')
        $.confirm({
            title: 'Copia de Nota de Venta',
            content: '¿Seguro que deseas copiar la nota de venta '+ doc+ ' ?',
            buttons: {
                Si: function () {
                    $.ajax({
                        type: "POST",
                        url: 'index.v.php',
                        dataType: "json",
                        data: {copiaNV:1, doc},
                        success: function (data)
                        {
                            if (data.status == "ok") {
                                setTimeout(alert(data.Mensaje),4000)
                                location.reload(); 
                            } else {
                                $.alert('Algo salió mal, favor de verificarlo con el administrador de sistema, código de error: ' + data.response);
                                console.log("XML:"+data.status);
                            }
                        }
                    });
                },
                Cancelar: {
                }
            }
        });
    })

    function timbrar(doc){
        $.ajax({
            url:'index.php',
            type:'post',
            dataType:'json',
            data:{buscaDoc:doc},
            success:function(data){
                setTimeout(alert(data.mensaje),4000)
                location.reload()
            },
            error:function(data){

            }
        })
    }

    $(".print").click(function(){
        var nv = $(this).attr('nv')
        //$.alert("Nota de venta " + nv )
        $.ajax({
            url:'index.v.php',
            type:'post',
            dataType:'json',
            data:{impNV:nv},
            success:function(data){
                if(data.status=='ok'){
                    window.open('/notas de venta/'+nv+'.pdf', 'download')
                }else{
                    $.alert('No se pudo generar la impresión.')
                }
            },
            error:function(){
                    $.alert('No se pudo generar la impresión.')
            }
        })
    })

    /*
    $(document).ready(function(){
        var table = $('#dataTables-nv')
            $('#dataTables-nv tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = table.row( tr );
         
                if ( row.child.isShown() ) {
                    // This row is already open - close it
                    row.child.hide();
                    tr.removeClass('shown');
                }
                else {
                    // Open this row
                    row.child( format(row.data()) ).show();
                    tr.addClass('shown');
                }
            });
    });
    */
</script>