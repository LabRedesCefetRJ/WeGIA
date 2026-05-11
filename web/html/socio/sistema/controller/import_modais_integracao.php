
  <!-- Modal perfil -->
<div class="modal fade" id="modalBoletofacil" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
<?php 
  require("../conexao.php");

  $stmt = $conexao->prepare("SELECT `api`, `token_api`, `sandbox`, `token_sandbox`, `val_min_boleto_uni`, `max_dias_pos_venc`, `juros(%)`, `multa(%)`, `val_max_parcela`, `val_min_parcela`, `agradecimento`, `dias_boleto_a_vista`, `dias_venc_carne_op1`, `dias_venc_carne_op2`, `dias_venc_carne_op3`, `dias_venc_carne_op4`, `dias_venc_carne_op5`, `dias_venc_carne_op6` FROM `infoboletofacil` WHERE 1");
  $stmt->execute();
  $resultado = $stmt->get_result();
  $boletofacil = $resultado->fetch_array(MYSQLI_ASSOC);

  $agradecimento = $boletofacil['agradecimento'];
  $url = $boletofacil['api'];
  $token = $boletofacil['token_api'];
  $valor_min_boleto_uni = $boletofacil['val_min_boleto_uni'];
  $max_dias_pos_venc = $boletofacil['max_dias_pos_venc'];
  $juros = $boletofacil['juros(%)'];
  $multa = $boletofacil['multa(%)'];
  $val_max_parcela = $boletofacil['val_max_parcela'];
  $val_min_parcela = $boletofacil['val_min_parcela'];


  // echo $boletofacil['agradecimento'];

?>
  <div class="modal-dialog" role="document">
    <div class="modal-content">
    <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                  <span aria-hidden="true">×</span></button>
                <h4 class="modal-title">Integração Boletofacil</h4>
              </div>
      <div class="modal-body">
        <div class="box box-info box-solid boletofacil_box">
            <div class="box-header">
              <h3 class="box-title">Dados de integração</h3>
            </div>
            <div class="box-body">
            <form id="frm_boletofacil">
            <div class="form-group col-xs-12">
          <label for="valor">URL API (ISSUE CHARGE - COBRANÇAS)</label>
          <input type="text" class="form-control"  value="<?php echo($url); ?>" name="url" required>
        </div>
        <div class="form-group col-xs-12">
          <label for="valor">TOKEN API</label>
          <input type="text" class="form-control" value="<?php echo($token); ?>" name="token" required>
        </div>
        <div class="form-group col-xs-6">
          <label for="valor">VAL. MIN. BOLETO UNITÁRIO</label>
          <input type="number" step="0.1"  class="form-control" value="<?php echo($valor_min_boleto_uni); ?>" name="val_min_boleto_uni" required>
        </div>
        <div class="form-group col-xs-6">
          <label for="valor">MÁX. DIAS PÓS VENC.</label>
          <input type="number" min="0" class="form-control" value="<?php echo($max_dias_pos_venc); ?>" name="max_dias_pos_venc" required>
        </div>
        <div class="form-group col-xs-6">
          <label for="valor">VAL. MIN. PARCELA</label>
          <input type="number" min="0" step="0.1"  class="form-control" value="<?php echo($val_min_parcela); ?>" name="val_min_parcela" required>
        </div>
        <div class="form-group col-xs-6">
          <label for="valor">VAL. MÁX. PARCELA</label>
          <input type="number" min="0" step="0.1"  class="form-control" value="<?php echo($val_max_parcela); ?>" name="val_max_parcela" required>
        </div>
        <div class="form-group col-xs-6">
          <label for="valor">JUROS % (Ex.: 0.2 = 20%)</label>
          <input type="number" min="0" max="1" step="any"  class="form-control" value="<?php echo($juros); ?>" name="juros" required>
        </div>
        <div class="form-group col-xs-6">
          <label for="valor">MULTA % (Ex.: 0.2 = 20%)</label>
          <input type="number" min="0" max="1" step="any" class="form-control" value="<?php echo($multa); ?>" name="multa" required>
        </div>
        <div class="form-group col-xs-12">
          <label for="valor">MENSAGEM DE AGRADECIMENTO</label>
          <textarea rows="5" name="agradecimento" class="form-control"><?php echo($agradecimento); ?></textarea>
        </div>
            </div>
            <!-- /.box-body -->
            <!-- Loading (remove the following to stop the loading)-->
            <!-- end loading -->
          </div>

      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Fechar</button>
        <button type="submit" class="btn btn-primary">Salvar</button>
      </div>
      </form>
    </div>
  </div>
</div>